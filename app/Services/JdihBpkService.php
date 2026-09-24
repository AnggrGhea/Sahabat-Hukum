<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * @deprecated Digantikan oleh Google Search Grounding resmi via GeminiService.
 * Dipertahankan untuk kebutuhan backward-compatibility dan kemudahan rollback.
 */
class JdihBpkService
{
    protected string $baseUrl = 'https://peraturan.bpk.go.id';
    protected int $timeout = 5; // seconds

    /**
     * Stop words and question words to strip when creating search keywords for JDIH BPK
     */
    protected array $searchStopWords = [
        'apa', 'apakah', 'bagaimana', 'mengapa', 'kenapa', 'kapan', 'dimana', 'siapa',
        'pasal', 'berapa', 'aturan', 'mengatur', 'tentang', 'mengenai', 'terkait', 'soal',
        'yang', 'di', 'ke', 'dari', 'dan', 'ini', 'itu', 'untuk', 'pada', 'adalah',
        'sebagai', 'dengan', 'atau', 'karena', 'oleh', 'saat', 'saya', 'kami', 'kita',
        'anda', 'kamu', 'dia', 'mereka', 'akan', 'bisa', 'dapat', 'ada', 'juga', 'sudah',
        'telah', 'saja', 'lagi', 'hanya', 'tolong', 'carikan', 'dasar', 'hukum', 'hukumnya',
        'peraturan', 'undang', 'undang-undang', 'uu', 'regulasi', 'ketentuan', 'bunyi',
    ];

    /**
     * Domain expansion mapping for criminal and national legal subjects
     */
    protected array $expansionMap = [
        'penganiayaan'   => ['penganiayaan KUHP', 'tindak pidana penganiayaan', 'Kitab Undang-Undang Hukum Pidana'],
        'penipuan'       => ['penipuan KUHP', 'tindak pidana penipuan', 'Kitab Undang-Undang Hukum Pidana'],
        'ditipu'         => ['penipuan KUHP', 'tindak pidana penipuan', 'Kitab Undang-Undang Hukum Pidana'],
        'pencurian'      => ['pencurian KUHP', 'tindak pidana pencurian'],
        'pembunuhan'     => ['pembunuhan KUHP', 'tindak pidana pembunuhan'],
        'penggelapan'    => ['penggelapan KUHP', 'tindak pidana penggelapan'],
        'pemerasan'      => ['pemerasan KUHP', 'tindak pidana pemerasan'],
        'korupsi'        => ['tindak pidana korupsi', 'pemberantasan korupsi'],
        'narkotika'      => ['tindak pidana narkotika', 'narkotika'],
        'konsumen'       => ['perlindungan konsumen', 'UU perlindungan konsumen'],
        'wanprestasi'    => ['wanprestasi KUHPerdata', 'ganti rugi wanprestasi'],
        'perceraian'     => ['perkawinan perceraian', 'UU perkawinan'],
    ];

    /**
     * Search official regulations on JDIH BPK on-demand with Query Expansion & Local Re-ranking
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 3): array
    {
        $keywords = $this->prepareKeywords($query);
        if (empty($keywords)) {
            return [];
        }

        $cacheKey = 'jdih_search_' . md5($keywords . '_' . $limit);

        return Cache::remember($cacheKey, 86400, function () use ($query, $keywords, $limit) {
            // 1. Primary search with base keywords
            $primaryResults = $this->fetchFromJdih($keywords, 5);

            // 2. Evaluate if query expansion is needed
            // If primary results are sparse or dominated by local regulations for a general/national legal query
            $candidates = $primaryResults;
            $needsExpansion = $this->shouldExpandQuery($query, $primaryResults);

            if ($needsExpansion) {
                $expandedKeywordsList = $this->getExpandedKeywords($query);
                $expansionAttempt = 0;
                foreach ($expandedKeywordsList as $expKeywords) {
                    if ($expKeywords === $keywords) {
                        continue;
                    }

                    $expansionAttempt++;
                    $secondaryResults = $this->fetchFromJdih($expKeywords, 5);
                    if (!empty($secondaryResults)) {
                        $candidates = $this->mergeCandidates($candidates, $secondaryResults);
                    }

                    // Stop if we already have sufficient national statutes or attempted 1 targeted expansion
                    if ($this->hasStrongNationalCandidates($candidates) || $expansionAttempt >= 1) {
                        break;
                    }
                }
            }

            // 3. Apply Local Re-ranking
            return $this->reRankResults($candidates, $query, $limit);
        });
    }

    /**
     * Determine if query expansion is beneficial
     */
    protected function shouldExpandQuery(string $query, array $currentResults): bool
    {
        if (empty($currentResults)) {
            return true;
        }

        $cleanQueryLower = strtolower($query);
        $userSpecificallyAskedUu = str_contains($cleanQueryLower, 'uu') || str_contains($cleanQueryLower, 'undang-undang') || str_contains($cleanQueryLower, 'undang undang');

        // If user specifically asked for a national statute / UU, expand if no strong UU in top candidates
        if ($userSpecificallyAskedUu && !$this->hasStrongNationalCandidates($currentResults)) {
            return true;
        }

        $focalSubjects = ['penganiayaan', 'konsumen', 'perceraian', 'wanprestasi', 'penipuan', 'ditipu', 'pencurian', 'pembunuhan'];

        // If query has a focal subject, check if top candidates actually match that focal subject
        foreach ($focalSubjects as $subj) {
            if (str_contains($cleanQueryLower, $subj)) {
                $hasFocalInTop = false;
                foreach (array_slice($currentResults, 0, 2) as $res) {
                    $titleSubj = strtolower(($res['title'] ?? '') . ' ' . ($res['subject'] ?? ''));
                    if (str_contains($titleSubj, $subj)) {
                        $hasFocalInTop = true;
                        break;
                    }
                }
                if (!$hasFocalInTop) {
                    return true;
                }
            }
        }

        $isNationalOrCriminal = $this->isNationalOrCriminalTopic($query);
        if (!$isNationalOrCriminal) {
            return false;
        }

        // Check if current top result is a local regulation (Perda/Perbup) while query is national/criminal
        $topDoc = $currentResults[0] ?? null;
        if ($topDoc) {
            $regLower = strtolower($topDoc['regulation'] ?? '');
            $titleLower = strtolower($topDoc['title'] ?? '');
            $urlLower = strtolower($topDoc['url'] ?? '');
            if (str_contains($regLower, 'perda') || str_contains($regLower, 'perbup') || str_contains($regLower, 'perwali') || str_contains($regLower, 'pergub') || str_contains($titleLower, 'peraturan daerah') || str_contains($urlLower, '/perbup-') || str_contains($urlLower, '/pergub-') || str_contains($urlLower, '/perda-')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if query pertains to national criminal law or national legal codes
     */
    protected function isNationalOrCriminalTopic(string $query): bool
    {
        $q = strtolower($query);
        $criminalOrNationalKeywords = [
            'pidana', 'kuhp', 'penganiayaan', 'pencurian', 'penipuan', 'ditipu', 'pembunuhan',
            'penggelapan', 'pemerasan', 'kejahatan', 'delik', 'kuhperdata', 'wanprestasi',
            'asas legalitas', 'tindak pidana',
        ];

        foreach ($criminalOrNationalKeywords as $kw) {
            if (str_contains($q, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get targeted expanded keywords for search
     */
    protected function getExpandedKeywords(string $query): array
    {
        $q = strtolower($query);
        $expansions = [];

        // Dedicated high-precision statutory queries for JDIH BPK
        if (str_contains($q, 'penganiayaan')) {
            $expansions[] = 'penganiayaan KUHP';
            $expansions[] = 'UU 1 2023 Kitab Undang-Undang Hukum Pidana';
            $expansions[] = 'UU No 1 Tahun 1946 Peraturan Hukum Pidana';
        }

        if (str_contains($q, 'perceraian') || str_contains($q, 'cerai')) {
            $expansions[] = 'UU 1 1974 Perkawinan';
            $expansions[] = 'Undang-Undang Perkawinan';
        }

        if (str_contains($q, 'konsumen')) {
            $expansions[] = 'UU 8 1999 Perlindungan Konsumen';
        }

        foreach ($this->expansionMap as $trigger => $list) {
            if (str_contains($q, $trigger)) {
                foreach ($list as $item) {
                    $expansions[] = $item;
                }
            }
        }

        if (empty($expansions) && $this->isNationalOrCriminalTopic($query)) {
            $base = $this->prepareKeywords($query);
            $expansions[] = $base . ' KUHP';
        }

        return array_unique($expansions);
    }

    /**
     * Check if candidate list contains strong national statutes
     */
    protected function hasStrongNationalCandidates(array $candidates): bool
    {
        foreach ($candidates as $c) {
            $reg = strtolower($c['regulation'] ?? '');
            $title = strtolower($c['title'] ?? '');
            $url = strtolower($c['url'] ?? '');

            $isBylaw = str_contains($reg, 'perbup') || str_contains($reg, 'perda') || str_contains($reg, 'pergub') ||
                       str_contains($url, '/perbup-') || str_contains($url, '/perda-') || str_contains($url, '/pergub-');

            if ($isBylaw) {
                continue;
            }

            if (str_contains($reg, 'undang-undang') || str_contains($reg, 'uu') || str_contains($url, '/uu-') || str_contains($url, '/uu/') || str_contains($title, 'undang-undang')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Merge candidate lists avoiding duplicate URLs
     */
    protected function mergeCandidates(array $listA, array $listB): array
    {
        $merged = $listA;
        $seenUrls = [];

        foreach ($listA as $item) {
            $url = $item['url'] ?? '';
            if ($url) {
                $seenUrls[$url] = true;
            }
        }

        foreach ($listB as $item) {
            $url = $item['url'] ?? '';
            if (!isset($seenUrls[$url])) {
                $seenUrls[$url] = true;
                $merged[] = $item;
            }
        }

        return $merged;
    }

    /**
     * Perform HTTP GET to JDIH BPK and parse search results
     */
    protected function fetchFromJdih(string $keywords, int $limit): array
    {
        try {
            $searchUrl = "{$this->baseUrl}/Search?keywords=" . urlencode($keywords);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'id,en-US;q=0.9,en;q=0.8',
                ])
                ->get($searchUrl);

            if (!$response->successful()) {
                Log::warning("JDIH BPK Search returned status {$response->status()} for keywords: {$keywords}");
                return [];
            }

            return $this->parseSearchResults($response->body(), $limit);
        } catch (\Throwable $e) {
            Log::error("JDIH BPK Search Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Local Re-ranking Algorithm
     *
     * Scores candidates based on:
     * 1. Hierarchy of regulation (UU/Perpu > PP > Perpres > PERMA/SEMA > Permen > Perda)
     * 2. Title & Subject exact and token matches
     * 3. Topic alignment (national criminal statutes vs municipal bylaws)
     * 4. Penalty for unrelated municipal/animal bylaws matching criminal queries
     */
    public function reRankResults(array $results, string $query, int $limit = 3): array
    {
        if (empty($results)) {
            return [];
        }

        $cleanQueryLower = strtolower($query);
        $isCriminalOrNational = $this->isNationalOrCriminalTopic($query);
        $userSpecificallyAskedPerda = str_contains($cleanQueryLower, 'perda') || str_contains($cleanQueryLower, 'peraturan daerah');
        $userSpecificallyAskedUu = str_contains($cleanQueryLower, 'uu') || str_contains($cleanQueryLower, 'undang-undang') || str_contains($cleanQueryLower, 'undang undang');

        $queryKeywords = explode(' ', $this->prepareKeywords($query));

        $scored = [];

        foreach ($results as $item) {
            $score = 0;
            $titleLower = strtolower($item['title'] ?? '');
            $regLower = strtolower($item['regulation'] ?? '');
            $subjLower = strtolower($item['subject'] ?? '');

            // 1. Hierarchy of Regulation
            if (str_contains($regLower, 'undang-undang') || str_contains($regLower, 'uu') || str_contains($regLower, 'kitab undang-undang')) {
                $score += $userSpecificallyAskedUu ? 40 : 25;
            } elseif (str_contains($regLower, 'peraturan pemerintah')) {
                $score += 15;
            } elseif (str_contains($regLower, 'peraturan presiden')) {
                $score += 12;
            } elseif (str_contains($regLower, 'peraturan mahkamah agung') || str_contains($regLower, 'perma') || str_contains($regLower, 'surat edaran mahkamah agung')) {
                $score += 18;
            } elseif (str_contains($regLower, 'peraturan menteri') || str_contains($regLower, 'permen')) {
                $score += 8;
            } elseif (str_contains($regLower, 'perda') || str_contains($regLower, 'peraturan daerah') || str_contains($regLower, 'perbup') || str_contains($regLower, 'perwali') || str_contains($regLower, 'pergub')) {
                if ($userSpecificallyAskedPerda) {
                    $score += 15;
                } elseif ($userSpecificallyAskedUu) {
                    $score -= 40; // Heavy penalty if user asked for a national statute / UU
                } else {
                    $score += 2;
                }
            }

            // 2. Direct Concept & Title Match
            if (str_contains($titleLower, $cleanQueryLower)) {
                $score += 30;
            }

            foreach ($queryKeywords as $kw) {
                if (strlen($kw) >= 3) {
                    if (str_contains($titleLower, $kw)) {
                        $score += 8;
                    }
                    if (str_contains($subjLower, $kw)) {
                        $score += 6;
                    }
                }
            }

            // Focal Subject Alignment:
            // If the query targets a specific legal subject (e.g. penganiayaan, konsumen, perceraian, wanprestasi),
            // the document MUST relate to that specific subject. Generic statutes matching only "tindak pidana"
            // (such as Tindak Pidana Imigrasi or Pencucian Uang) are penalized.
            $focalKeywords = [
                'penganiayaan' => ['penganiayaan'],
                'konsumen'     => ['konsumen', 'perlindungan konsumen'],
                'perceraian'   => ['perceraian', 'perkawinan'],
                'wanprestasi'  => ['wanprestasi', 'perikatan'],
                'penipuan'     => ['penipuan', 'tipu', 'kuhp', 'hukum pidana'],
                'ditipu'       => ['penipuan', 'tipu', 'kuhp', 'hukum pidana'],
                'pencurian'    => ['pencurian', 'kuhp'],
                'pembunuhan'   => ['pembunuhan', 'kuhp'],
                'penggelapan'  => ['penggelapan', 'kuhp'],
                'narkotika'    => ['narkotika'],
                'korupsi'      => ['korupsi'],
            ];
            foreach ($focalKeywords as $focalKey => $matchTargets) {
                if (str_contains($cleanQueryLower, $focalKey)) {
                    $hasFocalInDoc = false;
                    $docContent = $titleLower . ' ' . $subjLower . ' ' . strtolower(implode(' ', $item['file_snippets'] ?? []));
                    foreach ($matchTargets as $target) {
                        if (str_contains($docContent, $target)) {
                            $hasFocalInDoc = true;
                            break;
                        }
                    }

                    if (!$hasFocalInDoc) {
                        $score -= 60;
                    } else {
                        $score += 35;
                    }
                }
            }

            // 3. National Criminal Alignment vs Municipal Bylaws Penalty
            if ($isCriminalOrNational) {
                // Bonus for direct criminal codes or penalties
                if (str_contains($titleLower, 'hukum pidana') || str_contains($titleLower, 'kuhp') || str_contains($subjLower, 'hukum pidana')) {
                    $score += 25;
                }
                if (str_contains($titleLower, 'penyesuaian batasan tindak pidana')) {
                    $score += 15;
                }

                // Penalty: Local Perda about unrelated municipal topics (peternakan, hewan, sampah, pasar, parkir)
                // that accidentally matched a criminal query (e.g. "bebas dari penganiayaan hewan")
                $isMunicipalBylaw = str_contains($regLower, 'perda') || str_contains($regLower, 'perbup') || str_contains($regLower, 'perwali');
                $isUnrelatedSubject = str_contains($subjLower, 'peternakan') || str_contains($subjLower, 'hewan') ||
                                      str_contains($subjLower, 'ternak') || str_contains($subjLower, 'sampah') ||
                                      str_contains($subjLower, 'parkir') || str_contains($subjLower, 'bale mediasi') ||
                                      str_contains($subjLower, 'rpjmd') || str_contains($subjLower, 'pasar');

                if ($isMunicipalBylaw && $isUnrelatedSubject && !$userSpecificallyAskedPerda) {
                    $score -= 40; // Penalize so it is not prioritized over criminal statutes
                }
            }

            // 4. File snippets relevance
            if (!empty($item['file_snippets'])) {
                $score += 5;
            }

            $item['_score'] = $score;
            $scored[] = $item;
        }

        // Sort descending by score
        usort($scored, function ($a, $b) {
            return ($b['_score'] ?? 0) <=> ($a['_score'] ?? 0);
        });

        // Filter out heavily penalized items if better items exist
        $filtered = array_filter($scored, function ($item) use ($scored) {
            // If top item has high positive score, reject negative score items
            if (($scored[0]['_score'] ?? 0) > 10 && ($item['_score'] ?? 0) < 0) {
                return false;
            }
            return true;
        });

        $final = empty($filtered) ? $scored : array_values($filtered);

        return array_slice($final, 0, $limit);
    }

    /**
     * Clean query into high-signal legal keywords for JDIH search engine
     */
    public function prepareKeywords(string $query): string
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', strtolower($query));
        $words = preg_split('/\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);

        $filtered = [];
        foreach ($words as $w) {
            $w = trim($w);
            if (strlen($w) >= 3 && !in_array($w, $this->searchStopWords)) {
                $filtered[] = $w;
            }
        }

        if (empty($filtered)) {
            foreach ($words as $w) {
                if (strlen($w) >= 3) {
                    $filtered[] = $w;
                }
            }
        }

        return implode(' ', array_slice($filtered, 0, 5));
    }

    /**
     * Parse HTML search results from JDIH BPK
     */
    protected function parseSearchResults(string $html, int $limit): array
    {
        $results = [];

        // Match card segments containing /Details/
        if (!preg_match_all('/<a\s+href="(\/Details\/\d+\/[^"]+)"[^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $seenUrls = [];

        foreach ($matches as $match) {
            if (count($results) >= $limit) {
                break;
            }

            $detailPath = $match[1];
            $subjectTitle = trim(strip_tags($match[2]));

            if (isset($seenUrls[$detailPath])) {
                continue;
            }
            $seenUrls[$detailPath] = true;

            $fullUrl = $this->baseUrl . $detailPath;

            // Find snippet around this match to extract regulation number, status, download link
            $pos = strpos($html, $detailPath);
            $start = max(0, $pos - 400);
            $length = 1500;
            $snippet = substr($html, $start, $length);

            // Extract regulation name (e.g. "Undang-undang (UU) No. 8 Tahun 1999")
            $regName = '';
            if (preg_match('/<div class="[^"]*text-gray-600[^"]*">\s*(.*?)\s*<\/div>/is', $snippet, $regMatch)) {
                $regName = trim(strip_tags($regMatch[1]));
            }

            // Extract status or validity (e.g. "Berlaku mulai ...")
            $status = 'Berlaku';
            if (preg_match('/<span class="text-muted">\s*&#x2022;\s*(.*?)\s*<\/span>/is', $snippet, $statMatch)) {
                $status = trim(strip_tags($statMatch[1]));
            }

            // Extract PDF Download link
            $pdfUrl = null;
            if (preg_match('/href="(\/Download\/\d+\/[^"]+\.pdf)"/i', $snippet, $pdfMatch)) {
                $pdfUrl = $this->baseUrl . $pdfMatch[1];
            }

            // Extract PDF text snippets ("Hasil pencarian pada file")
            $fileSnippets = [];
            if (preg_match('/Hasil pencarian pada file:<\/div>\s*<div[^>]*>(.*?)<\/figure>/is', $snippet, $fileMatch)) {
                if (preg_match_all('/<div class="mb-8">\s*<p class="fs-7">\s*(.*?)\s*<\/p>\s*<\/div>/is', $fileMatch[1], $pMatches)) {
                    foreach ($pMatches[1] as $pText) {
                        $cleanP = trim(preg_replace('/\s+/', ' ', strip_tags($pText)));
                        if ($cleanP) {
                            $fileSnippets[] = $cleanP;
                        }
                    }
                }
            }

            // Extract Abstract if present in modal
            $abstractText = '';
            if (preg_match('/id="abstrak-\d+"[^>]*>(.*?)<\/div>\s*<\/div>\s*<\/div>/is', $html, $abstrakMatch)) {
                $abstractText = trim(preg_replace('/\s+/', ' ', strip_tags($abstrakMatch[1])));
                // Clean common repetitive strings
                $abstractText = str_replace(['ABSTRAK PERATURAN', 'Tutup', 'Close'], '', $abstractText);
                $abstractText = trim($abstractText);
            }

            $compositeTitle = $regName ? "{$regName} tentang {$subjectTitle}" : $subjectTitle;

            $results[] = [
                'title'         => $compositeTitle,
                'regulation'    => $regName ?: $subjectTitle,
                'subject'       => $subjectTitle,
                'status'        => $status,
                'url'           => $fullUrl,
                'pdf_url'       => $pdfUrl,
                'file_snippets' => $fileSnippets,
                'abstract'      => $abstractText,
                'source_type'   => 'JDIH BPK (Peraturan Resmi)',
            ];
        }

        return $results;
    }

    /**
     * Fetch additional details from a specific JDIH BPK detail URL
     * Strictly verifies that the domain belongs to peraturan.bpk.go.id to prevent SSRF.
     */
    public function getDetails(string $url): ?array
    {
        $parsed = parse_url($url);
        $host = strtolower($parsed['host'] ?? '');
        if ($host !== 'peraturan.bpk.go.id') {
            Log::warning("Blocked non-JDIH URL request: {$url}");
            return null;
        }

        $cacheKey = 'jdih_detail_' . md5($url);

        return Cache::remember($cacheKey, 86400, function () use ($url) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)',
                    ])
                    ->get($url);

                if (!$response->successful()) {
                    return null;
                }

                $html = $response->body();
                $details = [];

                // Extract metadata key-values
                if (preg_match_all('/<div class="col-lg-3 fw-bold">\s*(.*?)\s*<\/div>\s*<div class="col-lg-9">\s*(.*?)\s*<\/div>/is', $html, $pairs, PREG_SET_ORDER)) {
                    foreach ($pairs as $p) {
                        $key = trim(strip_tags($p[1]));
                        $val = trim(strip_tags($p[2]));
                        if ($key && $val) {
                            $details[$key] = $val;
                        }
                    }
                }

                return $details;
            } catch (\Throwable $e) {
                Log::error("JDIH BPK Detail Exception: " . $e->getMessage());
                return null;
            }
        });
    }
}
