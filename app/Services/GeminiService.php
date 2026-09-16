<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $this->model  = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-2.5-flash');
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Generate answer using Gemini API with provided context and user question
    /**
     * Generate casual or out-of-scope response without any legal retrieval context
     *
     * @param string $question
     * @return array
     */
    public function generateCasualAnswer(string $question): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => true,
                'answer'  => "Halo, saya Asisten Hukum. Terima kasih telah menghubungi kami. Asisten Hukum kami berfokus pada pemberian informasi hukum dan layanan konsultasi. Jika Anda memiliki pertanyaan seputar hukum atau memerlukan panduan layanan konsultasi kami, silakan tanyakan kepada kami.",
                'sources' => [],
            ];
        }

        $systemPrompt = "Anda adalah Asisten Hukum.\n"
            . "Pengguna sedang menyapa, mengajak bercerita (curhat), atau menanyakan hal di luar topik hukum.\n"
            . "ATURAN:\n"
            . "1. Jika menyapa atau memperkenalkan diri, gunakan kalimat: \"Halo, saya Asisten Hukum. Terima kasih telah menghubungi kami.\"\n"
            . "2. Berikan respons yang ramah, hangat, empatik, dan santun.\n"
            . "3. Jika pengguna mengajak curhat atau bercerita, izinkan pengguna bercerita dengan nyaman dan sampaikan bahwa jika ceritanya terkait dengan masalah hukum, Anda siap membantu memberikan informasi hukum umum.\n"
            . "4. Jika pengguna menanyakan hal yang sama sekali di luar ruang lingkup (misal resep masakan, cuaca, game), jelaskan dengan sopan dan singkat bahwa Anda adalah Asisten Hukum yang fokus pada informasi hukum.\n"
            . "5. JANGAN PERNAH menyebutkan atau mengarang nomor pasal, nama peraturan, atau dokumen hukum apa pun untuk respons ini.\n"
            . "6. JANGAN menampilkan sumber hukum apa pun.\n\n"
            . "Pesan Pengguna: " . $question;

        return $this->callGeminiApi($systemPrompt, []);
    }

    /**
     * Generate answer using Gemini API with provided context, sources, and role
     *
     * @param string $question
     * @param string $context
     * @param array $sources
     * @param string $role
     * @return array
     */
    public function generateAnswer(string $question, string $context, array $sources = [], string $role = 'klien'): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'answer'  => 'Kunci API Gemini (`GEMINI_API_KEY`) belum dikonfigurasi di file `.env`. Silakan masukkan API key Anda pada file `.env` untuk mengaktifkan fitur Asisten Hukum.',
                'sources' => [],
            ];
        }

        $isAdvokat = ($role === 'advokat');

        // If no relevant context was found
        if (trim($context) === '') {
            if ($isAdvokat) {
                return [
                    'success' => true,
                    'answer'  => "Mohon maaf Rekan Advokat, dasar hukum resmi terkait pertanyaan Anda belum berhasil diverifikasi pada sumber resmi yang terindeks saat ini.\n\n"
                               . "Sesuai prinsip \"No Source, No Legal Claim\", sistem tidak diperkenankan berspekulasi mengenai norma atau nomor pasal.\n"
                               . "Silakan lakukan penelusuran mandiri melalui portal resmi JDIH BPK (peraturan.bpk.go.id) atau JDIH Mahkamah Agung.",
                    'sources' => [],
                ];
            }

            return [
                'success' => true,
                'answer'  => "Saya belum menemukan sumber hukum resmi yang cukup untuk memverifikasi informasi tersebut.\n\n"
                           . "Untuk memastikan penerapan hukum pada kondisi permasalahan Anda, silakan konsultasikan langsung dengan Advokat kami melalui menu **Ajukan Konsultasi**.\n\n"
                           . "*Catatan: Asisten Hukum hanya menyampaikan informasi berdasarkan sumber hukum resmi yang terverifikasi dan bukan pengganti konsultasi langsung dengan Advokat.*",
                'sources' => [],
            ];
        }

        if ($isAdvokat) {
            $systemPrompt = "Anda adalah \"Asisten Riset Hukum\" untuk Advokat.\n"
                . "Tugas Anda adalah memberikan referensi dan analisis dasar hukum berbasis pada KONTEKS HUKUM resmi dan terverifikasi yang diberikan.\n"
                . "PRINSIP UTAMA: NO SOURCE, NO LEGAL CLAIM.\n\n"
                . "ATURAN KETAT:\n"
                . "1. Jawablah HANYA berdasarkan KONTEKS HUKUM resmi di bawah ini.\n"
                . "2. Sebutkan nama peraturan perundang-undangan, nomor, tahun, dan status keberlakuan secara presisi sesuai konteks.\n"
                . "3. JANGAN PERNAH mengarang, berspekulasi, atau memalsukan nomor pasal, ayat, atau bunyi norma yang tidak ada dalam konteks yang diberikan.\n"
                . "4. Jika pertanyaan menanyakan pasal spesifik namun bunyi pasal tersebut tidak tercantum dalam berkas/kutipan konteks resmi, Anda WAJIB menyatakan secara jujur: \"Ketentuan atau bunyi pasal spesifik belum dapat diverifikasi dari berkas resmi yang tersedia saat ini.\"\n"
                . "5. Cantumkan tautan sumber resmi (JDIH BPK atau Mahkamah Agung) yang tersedia dalam konteks.\n"
                . "6. Bedakan secara tegas antara teks norma hukum positif dengan analisis praktis.\n\n"
                . "=== KONTEKS HUKUM RESMI / BASIS PENGETAHUAN ===\n"
                . $context . "\n"
                . "=== AKHIR KONTEKS HUKUM ===\n\n"
                . "Pertanyaan Advokat: " . $question;
        } else {
            $systemPrompt = "Anda adalah Asisten Hukum untuk Klien.\n"
                . "Tugas Anda adalah memberikan informasi hukum umum secara edukatif, santun, objektif, dan mudah dipahami.\n"
                . "PRINSIP UTAMA: NO SOURCE, NO LEGAL CLAIM.\n\n"
                . "ATURAN KETAT:\n"
                . "1. Jika menyapa atau memperkenalkan diri di awal jawaban, gunakan kalimat pembuka: \"Halo, saya Asisten Hukum. Terima kasih telah menghubungi kami.\"\n"
                . "2. Gunakan bahasa Indonesia yang santun, jelas, dan mudah dipahami oleh masyarakat awam.\n"
                . "3. Jawablah HANYA berdasarkan KONTEKS HUKUM di bawah ini. JANGAN PERNAH mengarang pasal atau nomor undang-undang di luar konteks.\n"
                . "4. Jika pasal atau teks spesifik tidak ada dalam kutipan konteks, jelaskan inti peraturannya dan sampaikan bahwa rincian pasal belum terverifikasi pada dokumen yang tersedia.\n"
                . "5. JANGAN memberikan kepastian hasil perkara atau putusan pengadilan.\n"
                . "6. Jika masalah memerlukan tindakan hukum nyata, sarankan klien berkonsultasi langsung dengan Advokat melalui tombol 'Ajukan Konsultasi'.\n"
                . "7. Pada bagian akhir jawaban, sebutkan ringkasan sumber hukum yang relevan secara jelas.\n"
                . "8. Berikan catatan penutup bahwa jawaban ini merupakan informasi umum dan bukan nasihat hukum formal pengganti Advokat.\n\n"
                . "=== KONTEKS HUKUM (KNOWLEDGE BASE) ===\n"
                . $context . "\n"
                . "=== AKHIR KONTEKS HUKUM ===\n\n"
                . "Pertanyaan Klien: " . $question;
        }

        return $this->callGeminiApi($systemPrompt, $sources);
    }

    /**
     * Core API call to Gemini with token overflow safety & finishReason checks
     *
     * @param string $systemPrompt
     * @param array $sources
     * @return array
     */
    protected function callGeminiApi(string $systemPrompt, array $sources = []): array
    {
        try {
            $modelToUse = $this->model;
            $url = "{$this->baseUrl}/{$modelToUse}:generateContent?key={$this->apiKey}";

            $curlOptions = [
                'curl' => [
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_SESSIONID_CACHE => false,
                ],
            ];

            $response = Http::timeout(20)
                ->withOptions($curlOptions)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.2,
                        'topP'            => 0.8,
                        'maxOutputTokens' => 4096,
                    ]
                ]);

            // If 429 rate limit hit and fallback model is available, attempt single fallback
            if ($response->status() === 429 && $modelToUse !== 'gemini-3.5-flash-lite') {
                Log::warning("Gemini 429 Rate Limit on {$modelToUse}. Attempting graceful fallback to gemini-3.5-flash-lite...");
                $fallbackUrl = "{$this->baseUrl}/gemini-3.5-flash-lite:generateContent?key={$this->apiKey}";
                $response = Http::timeout(20)
                    ->withOptions($curlOptions)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($fallbackUrl, [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [['text' => $systemPrompt]]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature'     => 0.2,
                            'topP'            => 0.8,
                            'maxOutputTokens' => 4096,
                        ]
                    ]);
            }

            if ($response->successful()) {
                $data = $response->json();
                $candidate = $data['candidates'][0] ?? null;
                $candidateText = $candidate['content']['parts'][0]['text'] ?? null;
                $finishReason = $candidate['finishReason'] ?? null;

                if ($finishReason === 'MAX_TOKENS') {
                    Log::warning("Gemini generation reached MAX_TOKENS limit (4096).");
                }

                if ($candidateText) {
                    return [
                        'success' => true,
                        'answer'  => trim($candidateText),
                        'sources' => $sources,
                    ];
                }

                return [
                    'success' => false,
                    'answer'  => 'Tidak ada jawaban yang dihasilkan oleh sistem. Silakan ulangi pertanyaan Anda.',
                    'sources' => $sources,
                ];
            }

            // Developer Log: Detailed technical error
            $status = $response->status();
            $errorMessage = $response->json('error.message') ?? $response->body();
            Log::error("Gemini API Error (HTTP {$status}): {$errorMessage}");

            // User-friendly messages without technical codes (e.g. no raw HTTP 429)
            if ($status === 429) {
                return [
                    'success' => false,
                    'answer'  => "Layanan Asisten Hukum sedang menerima banyak permintaan saat ini. Silakan tunggu beberapa saat sebelum mengirimkan pertanyaan kembali, atau Anda dapat langsung berkonsultasi dengan Advokat kami melalui menu **Ajukan Konsultasi**.",
                    'sources' => $sources,
                ];
            }

            return [
                'success' => false,
                'answer'  => "Mohon maaf, layanan Asisten Hukum sedang mengalami kendala teknis sementara. Silakan coba kembali sesaat lagi atau ajukan konsultasi langsung dengan Advokat kami.",
                'sources' => $sources,
            ];
        } catch (\Throwable $e) {
            // Developer Log
            Log::error("Gemini Service Exception: " . $e->getMessage());

            // User-friendly error message
            return [
                'success' => false,
                'answer'  => "Mohon maaf, terjadi kendala saat memproses jawaban. Silakan coba sesaat lagi atau ajukan konsultasi langsung dengan Advokat kami.",
                'sources' => $sources,
            ];
        }
    }
}
