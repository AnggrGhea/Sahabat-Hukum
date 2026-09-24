<?php

namespace Tests\Feature;

use App\Models\KnowledgeSource;
use App\Models\User;
use App\Services\GeminiService;
use App\Services\LegalRagService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LegalAssistantTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.api_key' => 'fake-test-key']);
        config(['services.gemini.model' => 'gemini-3.6-flash']);
    }

    /**
     * Test intent classification for internal, legal research, mixed, and casual
     */
    public function test_intent_classification(): void
    {
        $rag = app(LegalRagService::class);

        // Casual
        $this->assertEquals('NON_LEGAL_CASUAL', $rag->classifyIntent('Halo, selamat pagi apa kabar?'));
        $this->assertEquals('NON_LEGAL_CASUAL', $rag->classifyIntent('Resep membuat nasi goreng enak'));

        // Internal Service
        $this->assertEquals('INTERNAL_SERVICE', $rag->classifyIntent('Berapa biaya konsultasi di kantor Sahabat Hukum?'));
        $this->assertEquals('INTERNAL_SERVICE', $rag->classifyIntent('Bagaimana jam kerja dan jadwal operasional kantor?'));

        // Legal Research
        $this->assertEquals('LEGAL_RESEARCH', $rag->classifyIntent('Apa unsur pidana pasal 362 KUHP tentang pencurian?'));
        $this->assertEquals('LEGAL_RESEARCH', $rag->classifyIntent('Putusan Mahkamah Agung mengenai wanprestasi perjanjian'));
        $this->assertEquals('LEGAL_RESEARCH', $rag->classifyIntent('Apakah UU Cipta Kerja mengubah ketentuan pesangon?'));

        // Mixed
        $this->assertEquals('MIXED_SERVICE_AND_LEGAL', $rag->classifyIntent('Saya mau gugat cerai di Sahabat Hukum, apa syarat hukumnya menurut hukum Islam?'));
    }

    /**
     * Test source classification according to official hierarchy
     */
    public function test_source_classification_hierarchy(): void
    {
        $gemini = app(GeminiService::class);

        // 1. Primary Official Legal Sources (Terverifikasi Resmi)
        $primaryUrls = [
            'https://peraturan.bpk.go.id/Details/123/uu-no-1-2023',
            'https://peraturan.go.id/id/uu-no-1-tahun-2024',
            'https://jdih.kemenkum.go.id/dokumen/baca/uu-1-2024',
            'https://putusan3.mahkamahagung.go.id/direktori/putusan/123.html',
            'https://jdih.mahkamahagung.go.id/peraturan/detail/123',
            'https://jdih.mkri.id/id/peraturan/123',
        ];

        foreach ($primaryUrls as $url) {
            $class = $gemini->classifySource($url);
            $this->assertEquals('primary', $class['tier'], "URL {$url} should be primary");
            $this->assertEquals('Terverifikasi Resmi', $class['status']);
            $this->assertTrue($class['is_official']);
        }

        // 2. Discovery / Aggregator (JDIHN) - Perlu Verifikasi
        $discoveryClass = $gemini->classifySource('https://jdihn.go.id/dokumen/detail/uu-1-2023');
        $this->assertEquals('discovery', $discoveryClass['tier']);
        $this->assertEquals('Discovery / Perlu Verifikasi', $discoveryClass['status']);
        $this->assertFalse($discoveryClass['is_official']);

        // 3. Supporting Government (.go.id)
        $govClass = $gemini->classifySource('https://kominfo.go.id/berita/uu-ite');
        $this->assertEquals('government', $govClass['tier']);
        $this->assertEquals('Sumber Pemerintah Pendukung', $govClass['status']);
        $this->assertFalse($govClass['is_official']);

        // 4. Non-official (Blog, Forum, Media)
        $externalUrls = [
            'https://www.hukumonline.com/klinik/detail/123',
            'https://id.wikipedia.org/wiki/Pencurian',
            'https://news.detik.com/berita/123',
            'https://firmahukumxyz.com/artikel',
        ];

        foreach ($externalUrls as $url) {
            $class = $gemini->classifySource($url);
            $this->assertEquals('unverified', $class['tier'], "URL {$url} should be unverified");
            $this->assertEquals('Tidak Resmi', $class['status']);
            $this->assertFalse($class['is_official']);
        }
    }

    /**
     * Test personal privacy data sanitization (NIK, phone, email)
     */
    public function test_privacy_query_sanitization(): void
    {
        $gemini = app(GeminiService::class);

        $dirtyQuery = "Nama saya Budi NIK 3201012345678001 nomor telp 081234567890 dan email budi@gmail.com tolong cek pasal penipuan.";
        $cleanQuery = $gemini->sanitizeQuery($dirtyQuery);

        $this->assertStringNotContainsString('3201012345678001', $cleanQuery);
        $this->assertStringNotContainsString('081234567890', $cleanQuery);
        $this->assertStringNotContainsString('budi@gmail.com', $cleanQuery);
        $this->assertStringContainsString('[NIK Disamarkan]', $cleanQuery);
        $this->assertStringContainsString('[Nomor Telepon Disamarkan]', $cleanQuery);
        $this->assertStringContainsString('[Email Disamarkan]', $cleanQuery);
        $this->assertStringContainsString('pasal penipuan', $cleanQuery);
    }

    /**
     * Test strict "No Source, No Legal Claim" fallback when no official source is returned
     */
    public function test_no_source_no_legal_claim_fallback(): void
    {
        // Mock Gemini returning response without grounding or with only unverified sources
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Berdasarkan pasal yang ada, hukumannya 5 tahun penjara.']
                            ]
                        ],
                        'groundingMetadata' => [
                            'webSearchQueries' => ['pasal penipuan'],
                            'groundingChunks'  => [
                                [
                                    'web' => [
                                        'uri'   => 'https://bloghukumrandom.com/artikel/penipuan',
                                        'title' => 'Blog Hukum Random'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $gemini = app(GeminiService::class);
        $result = $gemini->generateGroundedLegalAnswer('Apa hukuman penipuan?', 'klien');

        // Must reject unverified source and trigger fallback
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Saya belum menemukan sumber hukum resmi yang cukup untuk memverifikasi jawaban', $result['answer']);
        $this->assertEmpty($result['sources']);
    }

    /**
     * Test fallback triggers if only discovery (JDIHN) source is found without primary source
     */
    public function test_discovery_alone_cannot_replace_primary_source_fallback(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Klaim norma hukum tanpa verifikasi primer.']
                            ]
                        ],
                        'groundingMetadata' => [
                            'webSearchQueries' => ['pasal 123'],
                            'groundingChunks'  => [
                                [
                                    'web' => [
                                        'uri'   => 'https://jdihn.go.id/dokumen/detail/123',
                                        'title' => 'Dokumen di JDIHN'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $gemini = app(GeminiService::class);
        $result = $gemini->generateGroundedLegalAnswer('Apa pasal 123?', 'klien');

        // Without primary source, fallback must trigger
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Saya belum menemukan sumber hukum resmi yang cukup untuk memverifikasi jawaban', $result['answer']);
        $this->assertEmpty($result['sources']);
    }

    /**
     * Test valid grounded legal research when official source is present
     */
    public function test_grounded_legal_research_success(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "1. **Jawaban Singkat**: Pasal 362 KUHP mengatur tindak pidana pencurian.\n2. **Penjelasan Hukum**: Mengambil barang sesuatu yang seluruhnya atau sebagian kepunyaan orang lain dengan maksud untuk dimiliki secara melawan hukum.\n3. **Dasar Hukum**: Pasal 362 Kitab Undang-Undang Hukum Pidana (KUHP).\n4. **Catatan**: Informasi ini bersifat edukasi."]
                            ]
                        ],
                        'groundingMetadata' => [
                            'webSearchQueries' => ['pasal 362 kuhp pencurian'],
                            'groundingChunks'  => [
                                [
                                    'web' => [
                                        'uri'   => 'https://peraturan.bpk.go.id/Details/123/kuhp',
                                        'title' => 'Kitab Undang-Undang Hukum Pidana - JDIH BPK'
                                    ]
                                ],
                                [
                                    'web' => [
                                        'uri'   => 'https://jdihn.go.id/dokumen/detail/kuhp',
                                        'title' => 'KUHP di JDIHN'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $gemini = app(GeminiService::class);
        $result = $gemini->generateGroundedLegalAnswer('Apa itu pasal 362 KUHP?', 'advokat');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Pasal 362 KUHP', $result['answer']);
        $this->assertCount(2, $result['sources']);

        // Check primary source
        $this->assertEquals('primary', $result['sources'][0]['tier']);
        $this->assertEquals('Terverifikasi Resmi', $result['sources'][0]['status']);

        // Check discovery source
        $this->assertEquals('discovery', $result['sources'][1]['tier']);
        $this->assertEquals('Discovery / Perlu Verifikasi', $result['sources'][1]['status']);
    }

    /**
     * Test dynamic model candidates ordering
     */
    public function test_candidate_models_ordering(): void
    {
        $gemini = app(GeminiService::class);
        $candidates = $gemini->getCandidateModels();

        $this->assertNotEmpty($candidates);
        $this->assertEquals('gemini-3.6-flash', $candidates[0]);
        $this->assertContains('gemini-flash-latest', $candidates);
        $this->assertNotContains('gemini-2.0-flash', $candidates);
        $this->assertNotContains('gemini-2.5-flash', $candidates);
    }

    /**
     * Test HTTP endpoints for Advokat Assistant
     */
    public function test_advokat_assistant_endpoint(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Jawaban hukum simulasi untuk advokat.']
                            ]
                        ],
                        'groundingMetadata' => [
                            'webSearchQueries' => ['hukum pidana'],
                            'groundingChunks'  => [
                                [
                                    'web' => [
                                        'uri'   => 'https://peraturan.bpk.go.id/Details/1/uu-pidana',
                                        'title' => 'UU Pidana BPK'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $advokat = User::factory()->create([
            'role' => 'advokat',
        ]);

        $response = $this->actingAs($advokat)->postJson('/advokat/asisten-hukum/chat', [
            'message' => 'Apa aturan hukum pidana terbaru?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'answer',
            'sources',
            'time',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertCount(1, $response->json('sources'));
        $this->assertEquals('Terverifikasi Resmi', $response->json('sources.0.status'));
    }

    /**
     * Test HTTP endpoints for Klien Assistant
     */
    public function test_klien_assistant_endpoint(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Halo, saya Asisten Hukum. Terima kasih telah menghubungi kami.']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $klien = User::factory()->create([
            'role' => 'klien',
        ]);

        $response = $this->actingAs($klien)->postJson('/klien/asisten-hukum/chat', [
            'message' => 'Halo selamat pagi',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'answer',
            'sources',
            'time',
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertStringContainsString('Halo, saya Asisten Hukum', $response->json('answer'));
    }
}
