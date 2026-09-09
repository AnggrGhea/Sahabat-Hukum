<?php

namespace App\Services;

use App\Models\KnowledgeSource;

class LegalRagService
{
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
    ];

    /**
     * Retrieve relevant legal documents from Knowledge Base
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

        // 2. Extract keywords from query
        $keywords = $this->extractKeywords($normalizedQuery);

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
                $score += 20;
            }

            // Exact query match in description or content
            if (str_contains($contentLower, $cleanQueryLower)) {
                $score += 10;
            }

            // Match individual keywords with word boundaries
            $kwMatches = 0;
            foreach ($keywords as $kw) {
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

            // Only consider document if it has a meaningful relevance score (at least 3.0)
            if ($score >= 3.0 && $kwMatches > 0) {
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

        // 5. If no documents matched by keywords, try broader fallback search
        if (empty($scored)) {
            return [
                'context' => '',
                'sources' => [],
                'count'   => 0,
            ];
        }

        // 6. Select top documents
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
            ];

            $contextText .= "--- DOKUMEN [{$index}]: {$doc->title} ({$doc->source_type}) ---\n";
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
     * Tokenize query into meaningful search keywords
     */
    protected function extractKeywords(string $text): array
    {
        // Lowercase and strip punctuation
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
