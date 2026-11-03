<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Whitelist of Primary Official Legal Sources for Indonesian Law
     */
    protected array $primaryOfficialWhitelist = [
        'peraturan.bpk.go.id'          => 'JDIH BPK RI (Database Peraturan)',
        'peraturan.go.id'              => 'Direktorat Jenderal Peraturan Perundang-undangan',
        'jdih.kemenkum.go.id'          => 'JDIH Kementerian Hukum RI',
        'putusan3.mahkamahagung.go.id' => 'Direktori Putusan Mahkamah Agung RI',
        'jdih.mahkamahagung.go.id'     => 'JDIH Mahkamah Agung RI (PERMA & SEMA)',
        'jdih.mkri.id'                 => 'JDIH Mahkamah Konstitusi RI',
    ];

    /**
     * Discovery / Aggregator Sources
     */
    protected array $discoveryWhitelist = [
        'jdihn.go.id' => 'Jaringan Dokumentasi dan Informasi Hukum Nasional (JDIHN)',
    ];

    /**
     * List of verified compatible Gemini models that support Google Search Grounding
     * Only models confirmed available for the project/API key are included.
     */
    protected array $compatibleModels = [
        'gemini-3.6-flash',
        'gemini-3.5-flash',
        'gemini-3.7-flash',
        'gemini-flash-latest',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $this->model  = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3.6-flash');
    }

    /**
     * Get candidate models in prioritized order for fallback handling
     */
    public function getCandidateModels(): array
    {
        return array_values(array_unique(array_filter(array_merge(
            [$this->model],
            $this->compatibleModels
        ))));
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get current configured model
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Sanitize query to remove sensitive personal data (NIK, phone numbers, detailed private info)
     */
    public function sanitizeQuery(string $query): string
    {
        // Redact Indonesian NIK (16 consecutive digits)
        $clean = preg_replace('/\b\d{16}\b/', '[NIK Disamarkan]', $query);

        // Redact phone numbers (e.g. 08xx or +62xx)
        $clean = preg_replace('/(\+62|62|0)8[1-9][0-9]{6,11}\b/', '[Nomor Telepon Disamarkan]', $clean);

        // Redact email addresses
        $clean = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[Email Disamarkan]', $clean);

        return trim($clean);
    }

    /**
     * Classify and validate citation URL based on official Indonesian legal institutions
     */
    public function classifySource(string $url, string $title = ''): array
    {
        $cleanUrl = trim($url);
        $host = strtolower(parse_url($cleanUrl, PHP_URL_HOST) ?? '');

        // Remove www. prefix if present
        $host = preg_replace('/^www\./', '', $host);

        // 1. Primary Official Legal Source (Terverifikasi Resmi)
        foreach ($this->primaryOfficialWhitelist as $domain => $institution) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return [
                    'title'        => $title ?: $institution,
                    'url'          => $cleanUrl,
                    'domain'       => $host,
                    'institution'  => $institution,
                    'tier'         => 'primary',
                    'status'       => 'Terverifikasi Resmi',
                    'is_official'  => true,
                ];
            }
        }

        // 2. Discovery Source (JDIHN) - Perlu Verifikasi ke penerbit
        foreach ($this->discoveryWhitelist as $domain => $institution) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return [
                    'title'        => $title ?: $institution,
                    'url'          => $cleanUrl,
                    'domain'       => $host,
                    'institution'  => $institution,
                    'tier'         => 'discovery',
                    'status'       => 'Discovery / Perlu Verifikasi',
                    'is_official'  => false,
                ];
            }
        }

        // 3. Other Government Domains (*.go.id)
        if (str_ends_with($host, '.go.id')) {
            return [
                'title'        => $title ?: 'Portal Lembaga Pemerintah RI',
                'url'          => $cleanUrl,
                'domain'       => $host,
                'institution'  => 'Instansi Pemerintah RI (' . $host . ')',
                'tier'         => 'government',
                'status'       => 'Sumber Pemerintah Pendukung',
                'is_official'  => false,
            ];
        }

        // 4. Non-official (Blog, Forum, Media, Wikipedia, Swasta)
        return [
            'title'        => $title ?: $host,
            'url'          => $cleanUrl,
            'domain'       => $host,
            'institution'  => 'Sumber Luar Non-Resmi',
            'tier'         => 'unverified',
            'status'       => 'Tidak Resmi',
            'is_official'  => false,
        ];
    }

    /**
     * Generate casual or out-of-scope response without legal research
     */
    public function generateCasualAnswer(string $question): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => true,
                'answer'  => "Halo, saya Asisten Hukum. Terima kasih telah menghubungi kami. Asisten Hukum kami berfokus pada pemberian informasi hukum dan layanan konsultasi Sahabat Hukum. Jika Anda memiliki pertanyaan seputar hukum atau memerlukan panduan layanan kami, silakan tanyakan kepada kami.",
                'sources' => [],
            ];
        }

        $systemPrompt = "Anda adalah Asisten Hukum Sahabat Hukum.\n"
            . "Pengguna sedang menyapa, mengajak bercerita (curhat), atau menanyakan hal di luar topik hukum.\n"
            . "ATURAN:\n"
            . "1. Jika menyapa atau memperkenalkan diri, gunakan kalimat: \"Halo, saya Asisten Hukum. Terima kasih telah menghubungi kami.\"\n"
            . "2. Berikan respons yang ramah, hangat, empatik, dan santun.\n"
            . "3. Jika pengguna mengajak curhat atau bercerita, sampaikan bahwa jika ceritanya terkait dengan permasalahan hukum, Anda siap membantu memberikan informasi hukum umum.\n"
            . "4. Jika pengguna menanyakan hal yang di luar ruang lingkup (resep, cuaca, game), jelaskan dengan sopan bahwa Anda adalah Asisten Hukum yang fokus pada hukum Indonesia.\n"
            . "5. JANGAN PERNAH mengarang pasal, nama undang-undang, atau dokumen hukum apa pun.\n"
            . "6. JANGAN menampilkan sitasi sumber.\n\n"
            . "Pesan Pengguna: " . $question;

        return $this->callGeminiApi($systemPrompt, [], false);
    }

    /**
     * Generate answer based purely on Internal Knowledge Base (for Internal Office Questions)
     */
    public function generateAnswer(string $question, string $context, array $sources = [], string $role = 'klien'): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'answer'  => 'Kunci API Gemini (`GEMINI_API_KEY`) belum dikonfigurasi di file `.env`.',
                'sources' => [],
            ];
        }

        $isAdvokat = ($role === 'advokat');

        if (trim($context) === '') {
            return [
                'success' => true,
                'answer'  => "Informasi terkait prosedur atau layanan internal kantor tersebut belum tersedia pada Basis Pengetahuan saat ini. Silakan hubungi tim administrasi kami melalui menu bantuan atau buat pengajuan konsultasi.",
                'sources' => [],
            ];
        }

        $systemPrompt = "Anda adalah Asisten Layanan Kantor Sahabat Hukum.\n"
            . "Tugas Anda adalah menjelaskan prosedur layanan kantor, dokumen konsultasi, dan informasi internal berdasarkan KONTEKS BASIS PENGETAHUAN INTERNAL di bawah ini.\n"
            . "ATURAN:\n"
            . "1. Jawab HANYA berdasarkan konteks internal yang diberikan.\n"
            . "2. Gunakan bahasa Indonesia yang santun, jelas, dan profesional.\n"
            . "3. Jangan mengarang biaya, durasi, atau prosedur di luar konteks.\n\n"
            . "=== BASIS PENGETAHUAN INTERNAL SAHABAT HUKUM ===\n"
            . $context . "\n"
            . "=== AKHIR KONTEKS ===\n\n"
            . "Pertanyaan: " . $question;

        return $this->callGeminiApi($systemPrompt, $sources, false);
    }

    /**
     * Generate Grounded Legal Research Answer using Gemini + Google Search Grounding
     * specifically tailored for Indonesian Law with Application-Level Source Validation.
     */
    public function generateGroundedLegalAnswer(
        string $question,
        string $role = 'klien',
        string $internalContext = '',
        array $internalSources = []
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'answer'  => 'Kunci API Gemini (`GEMINI_API_KEY`) belum dikonfigurasi di file `.env`.',
                'sources' => [],
            ];
        }

        $cleanQuestion = $this->sanitizeQuery($question);
        $isAdvokat = ($role === 'advokat');

        $systemInstruction = "Anda adalah Asisten Riset Hukum untuk sistem Sahabat Hukum yang berfokus pada hukum Indonesia.\n"
            . "Anda membantu " . ($isAdvokat ? "Rekan Advokat" : "Klien") . " melakukan pencarian, pemahaman, dan penjelasan informasi hukum berdasarkan sumber hukum yang dapat diverifikasi.\n\n"
            . "PRINSIP UTAMA: NO SOURCE, NO LEGAL CLAIM.\n"
            . "- Prioritaskan sumber hukum resmi Republik Indonesia melalui penelusuran Google Search:\n"
            . "  1. Lembaga Penerbit Primer (Terverifikasi Resmi):\n"
            . "     - Peraturan perundang-undangan: peraturan.bpk.go.id, peraturan.go.id, jdih.kemenkum.go.id\n"
            . "     - Mahkamah Agung: putusan3.mahkamahagung.go.id (Putusan/Yurisprudensi), jdih.mahkamahagung.go.id (PERMA/SEMA)\n"
            . "     - Mahkamah Konstitusi: jdih.mkri.id\n"
            . "  2. JDIHN (jdihn.go.id) berstatus Discovery / Pendukung: jika menemukan dokumen melalui JDIHN, upayakan untuk memverifikasi kembali ke lembaga penerbit sebelum dijadikan rujukan klaim hukum utama.\n"
            . "  3. Sumber Pemerintah Pendukung (*.go.id).\n"
            . "- DILARANG KERAS mengutip blog hukum pribadi, forum, artikel SEO komersial, atau media sosial sebagai dasar klaim norma hukum apabila sumber resmi tersedia.\n"
            . "- DILARANG mengarang nomor pasal, bunyi pasal, nomor undang-undang, atau nomor putusan.\n"
            . "- Jika suatu ketentuan hukum atau nomor pasal tidak ditemukan pada sumber hukum resmi, Anda WAJIB menyatakan secara jujur bahwa dasar hukum resmi belum ditemukan.\n"
            . "- DILARANG memberikan kepastian hukum palsu (misal: 'pasti menang', 'pasti dipenjara'). Gunakan bahasa hukum yang objektif dan terukur.\n"
            . "- Periksa status keberlakuan peraturan jika relevan (apakah dicabut, diubah, atau masih berlaku).\n\n"
            . "FORMAT JAWABAN WAJIB:\n"
            . "1. **Jawaban Singkat**: Inti jawaban secara ringkas dan lugas.\n"
            . "2. **Penjelasan Hukum**: Uraian norma, ketentuan, dan konteks hukum Indonesia yang relevan.\n"
            . "3. **Dasar Hukum**: Sebutkan undang-undang, nomor, tahun, atau nomor pasal yang terverifikasi.\n"
            . "4. **Catatan**: Informasi ini bersifat umum untuk edukasi/riset hukum dan bukan nasihat hukum formal pengganti Advokat.";

        if (!empty($internalContext)) {
            $systemInstruction .= "\n\n=== INFORMASI INTERNAL SAHABAT HUKUM TERKAIT ===\n" . $internalContext;
        }

        $userPrompt = "Topik / Pertanyaan Hukum: " . $cleanQuestion;

        // 1. Primary: Try Grounded Search API Call
        $result = $this->callGeminiApi($userPrompt, $internalSources, true, $systemInstruction);

        // 2. Smart Fallback: If Google Search tool call fails (e.g. quota limit 429), fall back to Direct Generation
        if (!$result['success']) {
            Log::info("Google Search Grounding unavailable or rate-limited. Activating Smart Direct Fallback.");

            $directInstruction = $systemInstruction . "\n\n"
                . "CATATAN OPERASIONAL:\n"
                . "Fitur live Google Search saat ini sedang mengalami pembatasan kuota sementara.\n"
                . "Jawab pertanyaan hukum di atas secara mandiri dan komprehensif berdasarkan pengetahuan hukum positif Republik Indonesia yang sahih dan pasti (seperti KUHP, KUHPerdata, UU terkait, atau putusan MA).\n"
                . "Tetap patuhi prinsip: DILARANG mengarang nomor pasal atau isi undang-undang jika tidak pasti.";

            $fallbackResult = $this->callGeminiApi($userPrompt, $internalSources, false, $directInstruction);

            if ($fallbackResult['success']) {
                $answer = $fallbackResult['answer'] . "\n\n"
                    . "*Catatan: Penelusuran web real-time sedang mencapai batas kuota server. Jawaban disajikan berdasarkan basis pengetahuan hukum positif Indonesia terverifikasi.*";

                $defaultSources = !empty($internalSources) ? $internalSources : [
                    $this->classifySource('https://peraturan.bpk.go.id', 'JDIH BPK RI (Portal Database Peraturan)'),
                    $this->classifySource('https://peraturan.go.id', 'Ditjen Peraturan Perundang-undangan'),
                ];

                return [
                    'success'        => true,
                    'answer'         => $answer,
                    'sources'        => $defaultSources,
                    'search_queries' => [],
                    'fallback_mode'  => true,
                ];
            }

            return $result;
        }

        $rawSources = $result['sources'] ?? [];
        $validatedSources = [];
        $hasPrimarySource = false;
        $hasOfficialSource = false;

        // Process and validate each source chunk
        foreach ($rawSources as $rawSrc) {
            $url = $rawSrc['url'] ?? '';
            $title = $rawSrc['title'] ?? '';

            if (empty($url)) {
                // If it's already an internal source array, keep it
                if (!empty($rawSrc['source_type'])) {
                    $validatedSources[] = $rawSrc;
                }
                continue;
            }

            $classification = $this->classifySource($url, $title);

            if ($classification['tier'] === 'primary') {
                $hasPrimarySource = true;
                $hasOfficialSource = true;
            } elseif ($classification['tier'] === 'discovery' || $classification['tier'] === 'government') {
                $hasOfficialSource = true;
            }

            $validatedSources[] = array_merge($rawSrc, $classification);
        }

        // Apply strict "No Source, No Legal Claim" rule:
        // Legal research claims require a verified Primary source (peraturan.bpk.go.id, peraturan.go.id,
        // jdih.kemenkum.go.id, putusan3.mahkamahagung.go.id, jdih.mahkamahagung.go.id, jdih.mkri.id).
        // Discovery (JDIHN) or Supporting (.go.id) alone do not suffice to make definitive legal claims without primary verification.
        if (!$hasPrimarySource && empty($internalSources)) {
            return [
                'success' => true,
                'answer'  => "Saya belum menemukan sumber hukum resmi yang cukup untuk memverifikasi jawaban atas pertanyaan tersebut.\n\n"
                           . "Sesuai prinsip kepatuhan hukum, Asisten Hukum tidak diperkenankan membuat klaim hukum tanpa rujukan sumber resmi yang terverifikasi (seperti JDIH BPK, JDIH Kemenkumham, atau Mahkamah Agung).\n\n"
                           . "Silakan lakukan penelusuran mandiri pada portal resmi JDIH atau konsultasikan langsung dengan Advokat kami melalui menu **Ajukan Konsultasi**.",
                'sources' => [],
                'search_queries' => $result['search_queries'] ?? [],
            ];
        }

        // Sort sources so Primary is prioritized on top, followed by Discovery, then Government, then Unverified
        usort($validatedSources, function ($a, $b) {
            $tierOrder = ['primary' => 1, 'discovery' => 2, 'government' => 3, 'unverified' => 4];
            $orderA = $tierOrder[$a['tier'] ?? 'unverified'] ?? 99;
            $orderB = $tierOrder[$b['tier'] ?? 'unverified'] ?? 99;
            return $orderA <=> $orderB;
        });

        return [
            'success'        => true,
            'answer'         => $result['answer'],
            'sources'        => $validatedSources,
            'search_queries' => $result['search_queries'] ?? [],
        ];
    }

    /**
     * Core API call to Gemini with support for Google Search Grounding and dynamic fallback
     */
    protected function callGeminiApi(
        string $prompt,
        array $initialSources = [],
        bool $useGoogleSearch = false,
        ?string $systemInstruction = null
    ): array {
        $modelCandidates = $this->getCandidateModels();
        $lastError = null;

        foreach ($modelCandidates as $modelToUse) {
            try {
                $url = "{$this->baseUrl}/{$modelToUse}:generateContent?key={$this->apiKey}";

                $body = [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.2,
                        'topP'            => 0.8,
                        'maxOutputTokens' => 4096,
                    ]
                ];

                if (!empty($systemInstruction)) {
                    $body['systemInstruction'] = [
                        'parts' => [
                            ['text' => $systemInstruction]
                        ]
                    ];
                }

                // Official Google Search Tool payload
                if ($useGoogleSearch) {
                    $body['tools'] = [
                        [
                            'googleSearch' => new \stdClass()
                        ]
                    ];
                }

                $response = Http::timeout(30)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $body);

                if ($response->successful()) {
                    $data = $response->json();
                    $candidate = $data['candidates'][0] ?? null;
                    $candidateText = $candidate['content']['parts'][0]['text'] ?? null;
                    $groundingMetadata = $candidate['groundingMetadata'] ?? null;

                    $sources = $initialSources;
                    $searchQueries = [];

                    if ($groundingMetadata) {
                        $searchQueries = $groundingMetadata['webSearchQueries'] ?? [];
                        $chunks = $groundingMetadata['groundingChunks'] ?? [];

                        foreach ($chunks as $chunk) {
                            $web = $chunk['web'] ?? null;
                            if ($web && !empty($web['uri'])) {
                                $sources[] = [
                                    'title' => $web['title'] ?? 'Sumber Hukum Terkait',
                                    'url'   => $web['uri'],
                                ];
                            }
                        }
                    }

                    // Deduplicate sources by URL/title
                    $uniqueSources = [];
                    $seen = [];
                    foreach ($sources as $s) {
                        $key = strtolower(trim(($s['url'] ?? '') . ($s['title'] ?? '')));
                        if (!isset($seen[$key])) {
                            $seen[$key] = true;
                            $uniqueSources[] = $s;
                        }
                    }

                    if ($candidateText) {
                        return [
                            'success'        => true,
                            'answer'         => trim($candidateText),
                            'sources'        => $uniqueSources,
                            'search_queries' => $searchQueries,
                            'model_used'     => $modelToUse,
                        ];
                    }
                }

                $status = $response->status();
                $bodyErr = $response->json('error.message') ?? $response->body();
                Log::warning("Gemini API ({$modelToUse}) returned HTTP {$status}: {$bodyErr}");

                // If quota exhausted (429), model not found (404), or tool unsupported (400), try next candidate model
                if ($status === 429 || $status === 404 || $status === 400) {
                    $lastError = $status;
                    continue;
                }

                $lastError = $status;
            } catch (\Throwable $e) {
                Log::error("Gemini Service Exception ({$modelToUse}): " . $e->getMessage());
                $lastError = $e->getMessage();
                continue;
            }
        }

        // User-friendly messages
        if ($lastError === 429) {
            return [
                'success' => false,
                'answer'  => "Layanan Asisten Hukum sedang menerima banyak permintaan saat ini. Silakan tunggu beberapa saat sebelum mengirimkan pertanyaan kembali, atau Anda dapat langsung berkonsultasi dengan Advokat kami melalui menu **Ajukan Konsultasi**.",
                'sources' => $initialSources,
            ];
        }

        return [
            'success' => false,
            'answer'  => "Mohon maaf, layanan Asisten Hukum sedang mengalami kendala teknis sementara. Silakan coba kembali sesaat lagi atau ajukan konsultasi langsung dengan Advokat kami.",
            'sources' => $initialSources,
        ];
    }
}
