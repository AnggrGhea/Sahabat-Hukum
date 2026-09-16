<?php

namespace App\Services;

use App\Models\KnowledgeSource;

class LegalRagService
{
    protected JdihBpkService $jdih;
    protected GeminiService $gemini;

    /**
     * Indonesian common stop words to filter out during tokenization
     */
    protected array $stopWords = [
        'yang', 'di', 'ke', 'dari', 'dan', 'ini', 'itu', 'untuk', 'pada',
        'adalah', 'sebagai', 'dengan', 'atau', 'karena', 'oleh', 'saat',
        'saya', 'kami', 'kita', 'anda', 'kamu', 'dia', 'mereka', 'akan',
        'bisa', 'dapat', 'ada', 'juga', 'sudah', 'telah', 'saja', 'lagi',
        'hanya', 'apakah', 'apa', 'bagaimana', 'mengapa', 'kenapa', 'kapan',
        'dimana', 'siapa', 'berapa', 'kah', 'lah', 'pun', 'tersebut', 'suatu',
        'secara', 'harus', 'agar', 'supaya', 'tentang', 'mengenai', 'bagi',
        'tolong', 'carikan', 'jelaskan', 'sebutkan',
    ];

    /**
     * Synonym & expansion mapping for Indonesian legal domain
     */
    protected array $synonyms = [
        'kdrt'           => ['kekerasan dalam rumah tangga', 'uu 23 tahun 2004', 'penghapusan kekerasan dalam rumah tangga'],
        'korban'         => ['perlindungan korban', 'hak korban'],
        'gugatan'        => ['perkara perdata', 'surat gugatan', 'hukum acara perdata', 'gugat'],
        'perjanjian'     => ['kontrak', 'syarat sah perjanjian', 'pasal 1320', 'kuhperdata', 'perikatan'],
        'surat kuasa'    => ['kuasa', 'pemberian kuasa', 'kuasa khusus', 'pasal 1792'],
        'advokat'        => ['pengacara', 'penasihat hukum', 'uu advokat', 'uu 18 tahun 2003'],
        'pengadilan'     => ['persidangan', 'sidang', 'sema no 2 tahun 2014', 'jangka waktu'],
        'pidana'         => ['tindak pidana', 'kuhp', 'asas legalitas', 'kejahatan'],
        'perdata'        => ['hukum perdata', 'kuhperdata', 'hukum acara perdata'],
        'penganiayaan'   => ['tindak pidana penganiayaan', 'pasal 351', 'kuhp'],
        'konsumen'       => ['perlindungan konsumen', 'uu 8 tahun 1999'],
        'perceraian'     => ['cerai', 'perkawinan', 'gugatan cerai', 'uu perkawinan'],
        'wanprestasi'    => ['ingkar janji', 'pasal 1243', 'ganti rugi', 'cidera janji'],
        'syarat'         => ['persyaratan', 'ketentuan'],
        'konsultasi'     => ['prosedur konsultasi', 'syarat konsultasi', 'dokumen konsultasi'],
    ];

    public function __construct(JdihBpkService $jdih, GeminiService $gemini)
    {
        $this->jdih = $jdih;
        $this->gemini = $gemini;
    }

    /**
     * High-level pipeline: analyzes question intent, orchestrates tiered retrieval,
     * and generates role-tailored answer via Gemini LLM with strict anti-hallucination.
     *
     * @param string $question
     * @param string $role ('klien' | 'advokat')
     * @param array $history
     * @return array
     */
    public function ask(string $question, string $role = 'klien', array $history = []): array
    {
        $cleanQuery = trim($question);
        if ($cleanQuery === '') {
            return [
                'success' => false,
                'answer'  => 'Pertanyaan tidak boleh kosong.',
                'sources' => [],
            ];
        }

        // 1. Analyze Intent
        $intent = $this->classifyIntent($cleanQuery, $history);

        // A. NON_LEGAL_CASUAL: Never retrieve KB, never retrieve JDIH, never return legal sources
        if ($intent === 'NON_LEGAL_CASUAL') {
            return $this->gemini->generateCasualAnswer($cleanQuery);
        }

        // B. CONVERSATIONAL_VENTING: Empathic response, invite story without making legal claims
        if ($intent === 'CONVERSATIONAL_VENTING') {
            return [
                'success' => true,
                'answer'  => "Saya memahami situasi yang sedang Anda hadapi. Silakan ceritakan lebih lanjut mengenai kronologi kejadian tersebut.\n\n"
                           . "Apabila Anda membutuhkan informasi hukum mengenai langkah penyelesaian (misalnya apakah peristiwa tersebut memenuhi unsur pelanggaran/tindak pidana, opsi pelaporan, atau langkah hukum yang tersedia), silakan sampaikan agar saya dapat membantu mencarikan dasar hukum resmi yang relevan.",
                'sources' => [],
            ];
        }

        // C. LEGAL_SERVICE_OFFICIAL: Dedicated official Mahkamah Agung e-Court context
        if ($intent === 'LEGAL_SERVICE_OFFICIAL') {
            $ecourtData = $this->getOfficialEcourtContext();
            return $this->gemini->generateAnswer($cleanQuery, $ecourtData['context'], $ecourtData['sources'], $role);
        }

        // D. INTERNAL_SERVICE: Query Internal Knowledge Base only (no JDIH)
        if ($intent === 'INTERNAL_SERVICE') {
            $internalRes = $this->retrieve($cleanQuery, 3);
            return $this->gemini->generateAnswer($cleanQuery, $internalRes['context'], $internalRes['sources'], $role);
        }

        // E. LEGAL_RESEARCH: Retrieve Official Legal Sources (JDIH BPK) + relevant Internal KB
        $internalContext = '';
        $internalSources = [];
        $jdihContext = '';
        $jdihSources = [];

        // Check if Internal KB has verified reference material explicitly matching this legal topic
        if ($this->hasInternalMatchIntent($cleanQuery)) {
            $internalRes = $this->retrieve($cleanQuery, 2);
            if (!empty($internalRes['context'])) {
                $internalContext = $internalRes['context'];
                $internalSources = $internalRes['sources'];
            }
        }

        // Query JDIH BPK (Official Regulations)
        $jdihResults = $this->jdih->search($cleanQuery, 3);
        if (!empty($jdihResults)) {
            $jdihContextLines = [];
            foreach ($jdihResults as $idx => $reg) {
                $num = $idx + 1;
                $regTitle = $reg['title'] ?? 'Peraturan';
                $regUrl = $reg['url'] ?? '';
                $regPdf = $reg['pdf_url'] ?? '';
                $regStatus = $reg['status'] ?? 'Berlaku';
                $regAbstract = $reg['abstract'] ?? '';

                $block = "--- SUMBER HUKUM RESMI JDIH BPK [{$num}]: {$regTitle} ---\n"
                       . "Status Keberlakuan: {$regStatus}\n"
                       . "Tautan Resmi JDIH BPK: {$regUrl}\n";
                if ($regPdf) {
                    $block .= "Unduh Berkas Resmi (PDF): {$regPdf}\n";
                }
                if ($regAbstract) {
                    $block .= "Abstrak / Catatan Resmi: {$regAbstract}\n";
                }
                if (!empty($reg['file_snippets'])) {
                    $block .= "Kutipan Teks Resmi Berkas Peraturan (PDF JDIH BPK):\n";
                    foreach ($reg['file_snippets'] as $snip) {
                        $block .= "- {$snip}\n";
                    }
                } else {
                    $block .= "Catatan Verifikasi Berkas: Teks bunyi pasal/ayat spesifik belum terindeks langsung dalam kutipan berkas resmi JDIH BPK ini. JANGAN PERNAH mengarang isi pasal atau nomor pasal berdasarkan asumsi. Tampilkan sumber/metadata yang berhasil ditemukan di atas, dan jelaskan dengan jujur kepada pengguna bahwa ketentuan pasal spesifik belum dapat diverifikasi dari berkas resmi yang tersedia.\n";
                }
                $jdihContextLines[] = $block;

                $jdihSources[] = [
                    'title'       => $regTitle,
                    'source_type' => 'JDIH BPK',
                    'url'         => $regUrl,
                    'pdf_url'     => $regPdf,
                    'status'      => $regStatus,
                ];
            }
            $jdihContext = implode("\n\n", $jdihContextLines);
        }

        // Combine Contexts
        $combinedContext = '';
        if ($jdihContext !== '' && $internalContext !== '') {
            $combinedContext = "=== SUMBER PERATURAN PERUNDANG-UNDANGAN RESMI (JDIH BPK) ===\n"
                             . $jdihContext . "\n\n"
                             . "=== BASIS PENGETAHUAN INTERNAL SAHABAT HUKUM ===\n"
                             . $internalContext;
        } elseif ($jdihContext !== '') {
            $combinedContext = $jdihContext;
        } elseif ($internalContext !== '') {
            $combinedContext = $internalContext;
        }

        // Combine Sources without duplicate titles
        $combinedSources = [];
        $seenTitles = [];
        foreach (array_merge($jdihSources, $internalSources) as $src) {
            $key = strtolower(trim($src['title'] ?? ''));
            if (!isset($seenTitles[$key])) {
                $seenTitles[$key] = true;
                $combinedSources[] = $src;
            }
        }

        // Generation: Send to Gemini LLM with role-aware instructions
        return $this->gemini->generateAnswer($cleanQuery, $combinedContext, $combinedSources, $role);
    }

    /**
     * Classify user query intent to ensure correct routing
     *
     * @param string $query
     * @param array $history
     * @return string ('NON_LEGAL_CASUAL' | 'CONVERSATIONAL_VENTING' | 'INTERNAL_SERVICE' | 'LEGAL_SERVICE_OFFICIAL' | 'LEGAL_RESEARCH')
     */
    public function classifyIntent(string $query, array $history = []): string
    {
        $q = strtolower(trim($query));
        if ($q === '') {
            return 'NON_LEGAL_CASUAL';
        }

        // 1. Check for official electronic judicial services (e-Court / Mahkamah Agung)
        $ecourtKeywords = [
            'e-court', 'ecourt', 'e-filing', 'efiling', 'e-payment', 'epayment',
            'e-summons', 'esummons', 'e-litigasi', 'elitigasi', 'peradilan elektronik',
            'sidang elektronik', 'pendaftaran perkara elektronik', 'gugatan elektronik',
            'perkara secara elektronik', 'daftar ecourt', 'daftar e-court',
        ];
        foreach ($ecourtKeywords as $ekw) {
            if (str_contains($q, $ekw)) {
                return 'LEGAL_SERVICE_OFFICIAL';
            }
        }

        // 2. Check if user is asking an explicit legal question
        $hasLegalQuestion = $this->hasExplicitLegalQuestion($q);

        // 3. Check for Internal Office Services (Sahabat Hukum procedures, requirements, fees)
        $isInternal = $this->needsInternalKb($q);
        if ($isInternal && !$hasLegalQuestion) {
            return 'INTERNAL_SERVICE';
        }

        // 4. Check for pure casual, greetings, or out-of-scope topics
        if ($this->isNonLegalQuery($q)) {
            return 'NON_LEGAL_CASUAL';
        }

        // 5. Check for conversational venting without an explicit legal question
        if ($this->isConversationalVenting($q, $hasLegalQuestion)) {
            return 'CONVERSATIONAL_VENTING';
        }

        // 6. Explicit legal question or statutory inquiry
        if ($hasLegalQuestion || $this->needsLegalStatute($q)) {
            return 'LEGAL_RESEARCH';
        }

        // 7. Internal knowledge match
        if ($this->hasInternalMatchIntent($q)) {
            return 'INTERNAL_SERVICE';
        }

        // Fallback default: casual / clarify rather than fetching random bylaws
        return 'NON_LEGAL_CASUAL';
    }

    /**
     * Check if query contains an explicit question seeking legal recourse, statutes, or official reporting
     */
    public function hasExplicitLegalQuestion(string $query): bool
    {
        $q = strtolower($query);
        $explicitPatterns = [
            'apakah bisa dilaporkan', 'dapat dilaporkan', 'bisa dilaporkan', 'bisa saya laporkan',
            'cara melaporkan', 'melaporkannya', 'lapor polisi', 'melapor ke polisi', 'lapor ke polisi',
            'apakah bisa digugat', 'dapat digugat', 'bisa digugat', 'menggugatnya',
            'apakah melanggar hukum', 'apakah perbuatan melawan hukum', 'apakah tindak pidana',
            'apakah ini pidana', 'apakah penipuan', 'jerat hukum', 'ancaman pidana', 'sanksi pidana',
            'dasar hukum', 'dasar hukumnya', 'hukumnya apa', 'bagaimana hukumnya', 'menurut hukum',
            'pasal berapa', 'pasal apa', 'pasal yang mengatur', 'uu apa', 'undang-undang apa',
            'aturan apa', 'regulasi apa', 'ketentuan pasal', 'bunyi pasal', 'delik apa',
        ];

        foreach ($explicitPatterns as $pattern) {
            if (str_contains($q, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if query is conversational sharing/venting of a problem without asking for legal provisions
     */
    public function isConversationalVenting(string $query, bool $hasExplicitLegalQuestion = false): bool
    {
        if ($hasExplicitLegalQuestion) {
            return false;
        }

        $q = strtolower($query);
        $ventingMarkers = [
            'curhat', 'saya ditipu', 'kena tipu', 'masalah dengan', 'ada masalah',
            'lagi ada masalah', 'punya masalah', 'diancam', 'dipukul', 'bertengkar',
            'dituduh', 'kehilangan uang', 'dirugikan teman', 'tetangga saya', 'suami saya',
            'istri saya', 'keluarga saya', 'teman saya', 'motor saya ditabrak', 'mobil saya ditabrak',
            'saya merasa dirugikan', 'sedang bingung mau cerita',
        ];

        foreach ($ventingMarkers as $vm) {
            if (str_contains($q, $vm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Provide verified official Mahkamah Agung e-Court context and official source attribution
     */
    protected function getOfficialEcourtContext(): array
    {
        $context = "--- SUMBER RESMI MAHKAMAH AGUNG RI: SISTEM PERADILAN ELEKTRONIK (e-Court) ---\n"
            . "Dasar Hukum: Peraturan Mahkamah Agung (PERMA) No. 1 Tahun 2019 jo. PERMA No. 7 Tahun 2022 tentang Administrasi Perkara dan Persidangan di Pengadilan Secara Elektronik.\n"
            . "Portal Resmi: https://ecourt.mahkamahagung.go.id\n"
            . "Penerbit: Mahkamah Agung Republik Indonesia\n"
            . "Status Regulasi: Berlaku Resmi\n\n"
            . "Layanan Utama e-Court Mahkamah Agung mencakup 4 (empat) pilar utama:\n"
            . "1. e-Filing (Pendaftaran Perkara Secara Elektronik):\n"
            . "   - Pendaftaran dilakukan melalui portal resmi https://ecourt.mahkamahagung.go.id.\n"
            . "   - Pengguna Layanan terdiri dari dua kategori:\n"
            . "     a. Pengguna Terdaftar: Khusus Advokat yang telah terverifikasi dengan Nomor KTA dan Berita Acara Sumpah dari Pengadilan Tinggi.\n"
            . "     b. Pengguna Lainnya: Perorangan (Masyarakat umum / non-advokat), Badan Hukum, atau Pemerintah/Instansi. Pendaftaran akun dilakukan secara online dan verifikasi identitas (KTP/SK) dilakukan di Meja e-Court pada Pengadilan Negeri atau Pengadilan Agama setempat.\n"
            . "   - Tahapan e-Filing:\n"
            . "     1) Memilih Pengadilan Negeri / Pengadilan Agama yang berwenang memeriksa perkara.\n"
            . "     2) Mengisi data Penggugat/Pemohon dan Tergugat/Termohon.\n"
            . "     3) Mengunggah dokumen gugatan/permohonan (format PDF dan RTF/Doc), surat kuasa khusus (jika menggunakan kuasa), serta bukti awal.\n"
            . "2. e-Payment (Pembayaran Panjar Biaya Perkara Elektronik):\n"
            . "   - Setelah berkas diunggah, sistem e-Court secara otomatis menerbitkan e-SKUM (Surat Kuasa Untuk Membayar elektronik) berisi rincian taksiran panjar biaya perkara.\n"
            . "   - Pembayaran dilakukan melalui Nomor Rekening Virtual (Virtual Account) bank mitra Mahkamah Agung.\n"
            . "   - Setelah pembayaran terverifikasi otomatis, perkara mendapatkan Nomor Perkara resmi di sistem informasi perkara pengadilan.\n"
            . "3. e-Summons (Panggilan Sidang Secara Elektronik):\n"
            . "   - Penyampaian relas panggilan sidang, pemberitahuan penetapan hari sidang, dan putusan dikirimkan langsung ke domisili elektronik (alamat email terverifikasi) para pihak.\n"
            . "4. e-Litigasi (Persidangan Elektronik):\n"
            . "   - Pelaksanaan sidang online untuk agenda penyampaian jawaban, replik, duplik, serta kesimpulan secara daring tanpa harus hadir fisik di ruang sidang, sesuai kalender sidang yang ditetapkan Majelis Hakim.\n\n"
            . "Pedoman Verifikasi: Penjelasan harus bersumber pada ketentuan Mahkamah Agung RI di atas. Jangan membuat tahapan yang tidak ada dalam ketentuan PERMA No. 1 Tahun 2019 jo. PERMA No. 7 Tahun 2022.";

        $sources = [
            [
                'title'       => 'Mahkamah Agung RI — Layanan e-Court (Perma No. 1 Tahun 2019 jo. Perma No. 7 Tahun 2022)',
                'source_type' => 'Mahkamah Agung RI',
                'url'         => 'https://ecourt.mahkamahagung.go.id',
                'pdf_url'     => 'https://jdih.mahkamahagung.go.id',
                'status'      => 'Berlaku',
            ]
        ];

        return [
            'context' => $context,
            'sources' => $sources,
        ];
    }

    /**
     * Check if query specifically targets statutory regulations, articles, or penal rules
     */
    public function needsLegalStatute(string $query): bool
    {
        $q = strtolower($query);
        $statuteKeywords = [
            'pasal', 'ayat', 'uu', 'undang-undang', 'undang undang', 'peraturan',
            'dasar hukum', 'dasar hukumnya', 'dasar hukum apa', 'hukumnya apa',
            'perpu', 'peraturan pemerintah', 'pp', 'perpres', 'permen', 'perda',
            'sanksi', 'pidana', 'delik', 'penganiayaan', 'perlindungan konsumen',
            'perceraian', 'pencurian', 'penipuan', 'narkotika', 'korupsi',
            'kuhp', 'kuhperdata', 'hir', 'rbg', 'wanprestasi', 'perikatan',
            'kejahatan', 'pelanggaran', 'asas legalitas', 'ganti rugi',
        ];

        foreach ($statuteKeywords as $kw) {
            if (str_contains($q, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if query targets internal office procedures, consultations, or document requirements
     */
    public function needsInternalKb(string $query): bool
    {
        $q = strtolower($query);
        $internalKeywords = [
            'konsultasi', 'layanan sahabat hukum', 'prosedur konsultasi', 'tahapan konsultasi',
            'syarat konsultasi', 'persyaratan konsultasi', 'dokumen konsultasi', 'berkas konsultasi',
            'dokumen yang disiapkan', 'kantor sahabat hukum', 'biaya konsultasi', 'jadwal konsultasi',
            'ajukan konsultasi', 'cara konsultasi', 'alur pengajuan konsultasi', 'sahabat hukum',
        ];

        foreach ($internalKeywords as $kw) {
            if (str_contains($q, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if query has an internal match intent
     */
    protected function hasInternalMatchIntent(string $query): bool
    {
        $q = strtolower($query);
        return str_contains($q, 'surat kuasa') ||
               str_contains($q, 'gugatan perdata') ||
               str_contains($q, 'advokat') ||
               str_contains($q, 'sema') ||
               str_contains($q, 'lama proses') ||
               str_contains($q, 'kdrt');
    }

    /**
     * Check if query is completely unrelated to legal domain (e.g. casual chat, cooking, weather)
     */
    public function isNonLegalQuery(string $query): bool
    {
        $q = strtolower(trim($query));

        // Exact or close match for casual expressions
        $casualPhrases = [
            'curhat dong', 'aku mau curhat', 'mau curhat', 'curhat ya', 'boleh curhat',
            'aku lagi sedih', 'lagi sedih', 'sedih banget', 'boleh cerita', 'cerita dong',
            'aku bingung', 'lagi bingung', 'kamu siapa', 'siapa kamu', 'kamu bisa apa',
            'bisa bantu apa', 'halo', 'hai', 'hei', 'assalamualaikum', 'selamat pagi',
            'selamat siang', 'selamat sore', 'selamat malam', 'pagi', 'siang', 'sore', 'malam',
            'terima kasih', 'makasih', 'makasi', 'thanks', 'thank you', 'bye', 'sampai jumpa',
            'p', 'tes', 'test', 'halo asisten',
        ];

        foreach ($casualPhrases as $cp) {
            if ($q === $cp || preg_match('/^' . preg_quote($cp, '/') . '[\s\.\?!]*$/iu', $q)) {
                return true;
            }
        }

        $nonLegalMarkers = [
            'memasak', 'nasi goreng', 'resep', 'masakan', 'kue', 'makanan', 'minuman', 'kuliner',
            'cuaca hari ini', 'ramalan bintang', 'zodiak', 'skor sepak bola', 'sepak bola',
            'game online', 'lirik lagu', 'chord gitar', 'chord lagu', 'rekomendasi film',
            'film bagus', 'nonton film', 'lagu enak', 'bikin kopi', 'cara membuat kue',
        ];

        foreach ($nonLegalMarkers as $marker) {
            if (str_contains($q, $marker)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Retrieve relevant legal documents from Internal Knowledge Base
     *
     * @param string $query
     * @param int $maxDocuments
     * @return array
     */
    public function retrieve(string $query, int $maxDocuments = 3): array
    {
        $normalizedQuery = trim($query);
        if ($normalizedQuery === '') {
            return [
                'context' => '',
                'sources' => [],
                'count'   => 0,
            ];
        }

        // 1. Get active knowledge sources
        $sources = KnowledgeSource::where('status', 'Aktif')->get();
        if ($sources->isEmpty()) {
            return [
                'context' => '',
                'sources' => [],
                'count'   => 0,
            ];
        }

        // 2. Extract keywords and synonym expansions
        $keywords = $this->extractKeywords($normalizedQuery);
        $expandedKeywords = $this->expandSynonyms($keywords, strtolower($normalizedQuery));

        // 3. Score each document
        $scored = [];
        $cleanQueryLower = strtolower($normalizedQuery);

        foreach ($sources as $doc) {
            $score = 0;
            $titleLower = strtolower($doc->title);
            $contentLower = strtolower($doc->content ?? '');
            $descLower = strtolower($doc->description ?? '');

            // Exact query match in title
            if (str_contains($titleLower, $cleanQueryLower)) {
                $score += 25;
            }

            // Exact query match in description or content
            if (str_contains($contentLower, $cleanQueryLower)) {
                $score += 12;
            }

            // Match individual keywords with word boundaries
            $kwMatches = 0;
            foreach ($expandedKeywords as $kw) {
                $pattern = '/\b' . preg_quote($kw, '/') . '\b/iu';

                if (preg_match($pattern, $titleLower)) {
                    $score += 6;
                    $kwMatches++;
                }
                if (preg_match($pattern, $descLower)) {
                    $score += 3;
                    $kwMatches++;
                }
                if (preg_match_all($pattern, $contentLower, $cMatches)) {
                    $count = count($cMatches[0]);
                    $score += min($count, 4) * 1.5;
                    $kwMatches++;
                }
            }

            // Only consider document if score is meaningful and matches relevant subject
            if ($score >= 8.0 && $kwMatches > 0) {
                $scored[] = [
                    'doc'   => $doc,
                    'score' => $score,
                ];
            }
        }

        // 4. Sort by score descending
        usort($scored, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        if (empty($scored)) {
            return [
                'context' => '',
                'sources' => [],
                'count'   => 0,
            ];
        }

        // 5. Select top documents
        $topMatches = array_slice($scored, 0, $maxDocuments);

        $contextText = '';
        $sourceNames = [];

        foreach ($topMatches as $idx => $match) {
            /** @var KnowledgeSource $doc */
            $doc = $match['doc'];
            $index = $idx + 1;

            $sourceNames[] = [
                'id'          => $doc->id,
                'title'       => $doc->title,
                'source_type' => $doc->source_type,
                'url'         => null,
                'status'      => $doc->status,
            ];

            $contextText .= "--- DOKUMEN INTERNAL [{$index}]: {$doc->title} ({$doc->source_type}) ---\n";
            if (!empty($doc->description)) {
                $contextText .= "Ringkasan: {$doc->description}\n";
            }
            $contextText .= "Isi:\n{$doc->content}\n\n";
        }

        return [
            'context' => trim($contextText),
            'sources' => $sourceNames,
            'count'   => count($sourceNames),
        ];
    }

    /**
     * Expand keywords using domain legal synonyms
     */
    protected function expandSynonyms(array $keywords, string $fullText): array
    {
        $all = $keywords;

        foreach ($this->synonyms as $trigger => $syns) {
            if (str_contains($fullText, $trigger) || in_array($trigger, $keywords)) {
                foreach ($syns as $syn) {
                    $synWords = preg_split('/\s+/', $syn, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($synWords as $sw) {
                        if (!in_array($sw, $this->stopWords) && strlen($sw) >= 3) {
                            $all[] = strtolower($sw);
                        }
                    }
                }
            }
        }

        return array_unique($all);
    }

    /**
     * Tokenize query into meaningful search keywords
     */
    protected function extractKeywords(string $text): array
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', strtolower($text));
        $words = preg_split('/\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);

        $keywords = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 3 && !in_array($word, $this->stopWords)) {
                $keywords[] = $word;
            }
        }

        return array_unique($keywords);
    }
}
