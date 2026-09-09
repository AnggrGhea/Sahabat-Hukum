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
     *
     * @param string $question
     * @param string $context
     * @param array $sources
     * @return array
     */
    public function generateAnswer(string $question, string $context, array $sources = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'answer'  => 'Kunci API Gemini (`GEMINI_API_KEY`) belum dikonfigurasi di file `.env`. Silakan masukkan API key Anda pada file `.env` untuk mengaktifkan fitur Asisten Hukum.',
                'sources' => [],
            ];
        }

        // If no relevant context was found in RAG
        if (trim($context) === '') {
            return [
                'success' => true,
                'answer'  => "Mohon maaf, informasi terkait pertanyaan Anda saat ini belum tercantum dalam Basis Pengetahuan hukum kami.\n\n"
                           . "Untuk mendapatkan telaah hukum yang akurat dan pendampingan sesuai dengan permasalahan Anda, silakan hubungi tim advokat kami dengan menekan tombol **Ajukan Konsultasi** di atas.\n\n"
                           . "*Catatan: Asisten Hukum hanya memberikan informasi hukum umum berdasarkan regulasi yang tersedia pada sistem.*",
                'sources' => [],
            ];
        }

        $systemPrompt = "Anda adalah \"Asisten Hukum Sahabat Hukum\", asisten kecerdasan buatan resmi dari kantor hukum Sahabat Hukum.\n"
            . "Tugas Anda adalah memberikan informasi hukum umum kepada klien secara profesional, beretika, ringkas, dan mudah dipahami.\n\n"
            . "ATURAN MUTLAK:\n"
            . "1. Jawablah HANYA berdasarkan data yang tertulis pada KONTEKS HUKUM di bawah ini.\n"
            . "2. JANGAN PERNAH mengarang pasal, nomor undang-undang, sanksi, atau ketentuan hukum yang tidak ada dalam konteks.\n"
            . "3. Jika informasi yang ditanyakan klien tidak terjawab oleh konteks yang tersedia, sampaikan dengan santun bahwa informasi tersebut belum tersedia di Basis Pengetahuan kami dan sarankan klien untuk berkonsultasi langsung dengan Advokat melalui tombol 'Ajukan Konsultasi'.\n"
            . "4. Gunakan bahasa Indonesia yang baik, sopan, dan terstruktur (bisa menggunakan poin-poin jika menjelaskan tahapan/syarat).\n"
            . "5. Di akhir jawaban, cantumkan sumber hukum yang relevan secara jelas (misal: SEMA No. 2 Tahun 2014, Pasal 1792 KUHPerdata, dll).\n"
            . "6. Berikan catatan penutup bahwa informasi ini merupakan informasi hukum umum dan bukan pengganti konsultasi langsung dengan Advokat.\n\n"
            . "=== KONTEKS HUKUM (KNOWLEDGE BASE) ===\n"
            . $context . "\n"
            . "=== AKHIR KONTEKS HUKUM ===\n\n"
            . "Pertanyaan Klien: " . $question;

        try {
            $url = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(30)
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
                        'maxOutputTokens' => 1500,
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $candidateText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($candidateText) {
                    return [
                        'success' => true,
                        'answer'  => trim($candidateText),
                        'sources' => $sources,
                    ];
                }

                return [
                    'success' => false,
                    'answer'  => 'Tidak ada jawaban yang dihasilkan oleh Gemini API. Silakan ulangi pertanyaan Anda.',
                    'sources' => $sources,
                ];
            }

            // Log error without leaking the key
            $status = $response->status();
            $errorMessage = $response->json('error.message') ?? $response->body();
            Log::error("Gemini API Error (HTTP {$status}): {$errorMessage}");

            return [
                'success' => false,
                'answer'  => "Terjadi kendala saat menghubungi layanan Gemini API (HTTP {$status}). Pastikan API Key valid dan kuota API masih tersedia. Error: {$errorMessage}",
                'sources' => $sources,
            ];
        } catch (\Throwable $e) {
            Log::error("Gemini Service Exception: " . $e->getMessage());

            return [
                'success' => false,
                'answer'  => 'Terjadi kesalahan saat memproses jawaban: ' . $e->getMessage(),
                'sources' => $sources,
            ];
        }
    }
}
