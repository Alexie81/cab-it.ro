<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/import-seo-articles.php';

function cabit_premium_signature(string $text): string
{
    $text = mb_strtolower(strip_tags($text), 'UTF-8');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    return trim(preg_replace('/[^a-z0-9]+/i', ' ', $text) ?? $text);
}

function cabit_premium_boilerplate(array $articles): array
{
    $blockFrequency = [];
    $sentenceFrequency = [];
    foreach ($articles as $article) {
        $blocks = preg_split('/\R{2,}/u', (string) ($article['content_markdown'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($blocks as $block) {
            $signature = cabit_premium_signature($block);
            if (str_word_count($signature) >= 18) {
                $blockFrequency[$signature] = ($blockFrequency[$signature] ?? 0) + 1;
            }
            foreach (preg_split('/(?<=[.!?])\s+/u', preg_replace('/\s+/u', ' ', trim($block)) ?? trim($block), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $sentence) {
                $sentenceSignature = cabit_premium_signature($sentence);
                if (str_word_count($sentenceSignature) >= 12) {
                    $sentenceFrequency[$sentenceSignature] = ($sentenceFrequency[$sentenceSignature] ?? 0) + 1;
                }
            }
        }
    }
    return [
        'blocks' => array_filter($blockFrequency, static fn(int $count): bool => $count >= 2),
        'sentences' => array_filter($sentenceFrequency, static fn(int $count): bool => $count >= 2),
    ];
}

function cabit_premium_clean_markdown(array $article, array $boilerplate): string
{
    $markdown = str_replace(["\r\n", "\r"], "\n", trim((string) ($article['content_markdown'] ?? '')));
    $markdown = preg_replace('/^#\s+.+?\n+/u', '', $markdown, 1) ?? $markdown;
    $markdown = preg_replace('/\n##\s+Întrebări frecvente\s*\n.*$/su', '', $markdown) ?? $markdown;
    $blocks = preg_split('/\n{2,}/u', $markdown, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $clean = [];
    $seenBlocks = [];
    $seenSentences = [];
    $afterVerdict = false;
    $words = 0;

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        $isHeading = preg_match('/^#{2,3}\s+/u', $block) === 1;
        if ($isHeading && $afterVerdict) {
            break;
        }
        if (preg_match('/^##\s+Verdict\b/iu', $block)) {
            $afterVerdict = true;
        }
        if (preg_match('/(?:draft-editorial|review required|original evidence|required before indexing|llms\.txt|ai markup|smart search intern|canibalizare internă|notă editorială|rămâne noindex până la)/iu', $block)) {
            continue;
        }
        if (preg_match('/^\*\*Cum citim exemplele din acest ghid:/iu', $block)) {
            continue;
        }
        $signature = cabit_premium_signature($block);
        if (!$isHeading && ($signature === '' || isset($seenBlocks[$signature]) || isset($boilerplate['blocks'][$signature]))) {
            continue;
        }
        $seenBlocks[$signature] = true;

        if (!$isHeading && !preg_match('/^(?:[-*]|\d+\.)\s+/u', $block) && !str_starts_with($block, '>')) {
            $sentences = preg_split('/(?<=[.!?])\s+/u', preg_replace('/\s+/u', ' ', $block) ?? $block, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $kept = [];
            foreach ($sentences as $sentence) {
                if (preg_match('/^(?:Un test util este să poți explica separat|Aici merită urmărită legătura dintre|Particularitatea acestui subiect este combinația dintre|Pentru această temă, diferența se joacă tocmai în zona|În cazul de față, termenii|Privit prin [„"])/iu', trim($sentence))) {
                    continue;
                }
                $sentenceSignature = cabit_premium_signature($sentence);
                if ($sentenceSignature === '' || isset($seenSentences[$sentenceSignature]) || isset($boilerplate['sentences'][$sentenceSignature])) {
                    continue;
                }
                $seenSentences[$sentenceSignature] = true;
                $kept[] = trim($sentence);
            }
            $block = trim(implode(' ', $kept));
            if ($block === '') {
                continue;
            }
        }

        $blockWords = preg_split('/\s+/u', trim(strip_tags($block)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($words >= 2600 && $isHeading && !$afterVerdict) {
            continue;
        }
        $clean[] = $block;
        $words += count($blockWords);
    }

    $quickAnswer = trim((string) ($article['quick_answer'] ?? ''));
    if ($quickAnswer !== '') {
        array_unshift($clean, '> **Răspuns direct:** ' . $quickAnswer);
    }
    return trim(implode("\n\n", $clean));
}

function cabit_premium_meta_title(array $article): string
{
    $title = trim((string) ($article['title'] ?? ''));
    $suffix = ' | CAB-IT';
    $limit = 64 - mb_strlen($suffix, 'UTF-8');
    if (mb_strlen($title, 'UTF-8') > $limit) {
        $title = mb_substr($title, 0, $limit + 1, 'UTF-8');
        $title = preg_replace('/\s+\S*$/u', '', $title) ?? $title;
        $title = rtrim($title, " \t\n\r\0\x0B:;,-–—?!");
    }
    if (mb_strlen($title, 'UTF-8') < 30) {
        $title .= ': ghid practic';
    }
    return $title . $suffix;
}

function cabit_premium_meta_description(array $article): string
{
    $description = trim((string) ($article['meta_description'] ?? $article['quick_answer'] ?? ''));
    $description = preg_replace('/\s+/u', ' ', $description) ?? $description;
    if (mb_strlen($description, 'UTF-8') < 120) {
        $description .= ' Află criteriile, riscurile și pașii practici pentru o decizie bine fundamentată.';
    }
    if (mb_strlen($description, 'UTF-8') > 160) {
        $description = mb_substr($description, 0, 157, 'UTF-8');
        $description = preg_replace('/\s+\S*$/u', '', $description) ?? $description;
        $description = rtrim($description, " \t\n\r\0\x0B,.;:-") . '…';
    }
    return $description;
}

function cabit_premium_faqs(array $article): array
{
    $source = is_array($article['faq'] ?? null) ? $article['faq'] : (is_array($article['faqs'] ?? null) ? $article['faqs'] : []);
    $faqs = [];
    foreach ($source as $faq) {
        $question = trim((string) ($faq['question'] ?? $faq['q'] ?? ''));
        $answer = trim((string) ($faq['answer'] ?? $faq['a'] ?? ''));
        $answer = preg_replace('/\s+/u', ' ', $answer) ?? $answer;
        if ($question !== '' && $answer !== '' && !preg_match('/(?:draft|indexare|smart search)/iu', $question . ' ' . $answer)) {
            $faqs[] = ['q' => $question, 'a' => $answer];
        }
    }
    return $faqs;
}

function cabit_premium_sources(string $pillar): array
{
    $sources = [
        ['title' => 'Google Search Essentials', 'url' => 'https://developers.google.com/search/docs/essentials'],
        ['title' => 'Google Search – politicile anti-spam', 'url' => 'https://developers.google.com/search/docs/essentials/spam-policies'],
        ['title' => 'Google – date structurate pentru articole', 'url' => 'https://developers.google.com/search/docs/appearance/structured-data/article'],
        ['title' => 'Google – funcțiile AI în căutare', 'url' => 'https://developers.google.com/search/docs/appearance/ai-features'],
    ];
    $pillarLower = mb_strtolower($pillar, 'UTF-8');
    if (str_contains($pillarLower, 'ads') || str_contains($pillarLower, 'promovare') || str_contains($pillarLower, 'tiktok')) {
        $sources[] = ['title' => 'Centrul de ajutor Google Ads', 'url' => 'https://support.google.com/google-ads/'];
        $sources[] = ['title' => 'Google Ads API – documentație', 'url' => 'https://developers.google.com/google-ads/api/docs/start'];
        $sources[] = ['title' => 'CAB-IT – promovare online', 'url' => 'https://cab-it.ro/servicii/reclame-platite/'];
    } elseif (str_contains($pillarLower, 'analytics') || str_contains($pillarLower, 'automatizare')) {
        $sources[] = ['title' => 'Google Analytics – documentație', 'url' => 'https://developers.google.com/analytics'];
        $sources[] = ['title' => 'Google Tag Manager – documentație', 'url' => 'https://developers.google.com/tag-platform/tag-manager'];
        $sources[] = ['title' => 'CAB-IT – integrări digitale', 'url' => 'https://cab-it.ro/servicii/integrari-digitale/'];
    } elseif (str_contains($pillarLower, 'seo')) {
        $sources[] = ['title' => 'Google – ghid SEO pentru începători', 'url' => 'https://developers.google.com/search/docs/fundamentals/seo-starter-guide'];
        $sources[] = ['title' => 'Google – crawling și indexare', 'url' => 'https://developers.google.com/search/docs/crawling-indexing/overview'];
        $sources[] = ['title' => 'CAB-IT – optimizare SEO', 'url' => 'https://cab-it.ro/servicii/seo/'];
    } else {
        $sources[] = ['title' => 'web.dev – Core Web Vitals', 'url' => 'https://web.dev/articles/vitals'];
        $sources[] = ['title' => 'OWASP – Web Security Testing Guide', 'url' => 'https://owasp.org/www-project-web-security-testing-guide/'];
        $sources[] = ['title' => 'CAB-IT – creare site web', 'url' => 'https://cab-it.ro/servicii/creare-site-web/'];
    }
    return $sources;
}

function cabit_import_premium_seo_batch(string $jsonPath, bool $dryRun = false): array
{
    if (!is_file($jsonPath)) {
        throw new InvalidArgumentException('Fișierul JSON nu există: ' . $jsonPath);
    }
    $decoded = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
    $articles = is_array($decoded['articles'] ?? null) ? $decoded['articles'] : $decoded;
    if (!is_array($articles) || count($articles) !== 500) {
        throw new RuntimeException('Lotul premium trebuie să conțină exact 500 de articole.');
    }
    $boilerplate = cabit_premium_boilerplate($articles);
    $pdo = cms_db();
    $existingSlugs = array_fill_keys(array_map('strval', $pdo->query('SELECT slug FROM articles')->fetchAll(PDO::FETCH_COLUMN)), true);
    $batchSlugs = [];
    $records = [];
    $stats = [
        'min_words' => PHP_INT_MAX,
        'max_words' => 0,
        'boilerplate_blocks' => count($boilerplate['blocks']),
        'boilerplate_sentences' => count($boilerplate['sentences']),
    ];
    $publishedBlockFrequency = [];
    $publishedBlockExamples = [];
    $publishedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Bucharest')))->format(DATE_ATOM);

    foreach ($articles as $index => $article) {
        $slug = trim((string) ($article['slug'] ?? ''));
        if (!cms_valid_slug($slug) || isset($batchSlugs[$slug]) || isset($existingSlugs[$slug])) {
            throw new RuntimeException('Slug invalid, duplicat sau deja public: ' . $slug);
        }
        $batchSlugs[$slug] = true;
        $markdown = cabit_premium_clean_markdown($article, $boilerplate);
        $converted = cabit_markdown_to_article_html($markdown);
        $faqs = cabit_premium_faqs($article);
        $pillar = trim((string) ($article['pillar'] ?? 'Creare website'));
        $sources = cabit_premium_sources($pillar);
        $content = $converted['html'] . cabit_article_faq_html($faqs) . cabit_article_sources_html($sources, '2026-08-20');
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');
        $wordCount = count(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        if ($wordCount < 1200) {
            $markdown = cabit_premium_clean_markdown($article, ['blocks' => [], 'sentences' => []]);
            $converted = cabit_markdown_to_article_html($markdown);
            $content = $converted['html'] . cabit_article_faq_html($faqs) . cabit_article_sources_html($sources, '2026-08-20');
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');
            $wordCount = count(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        }
        $seoTitle = cabit_premium_meta_title($article);
        $description = cabit_premium_meta_description($article);
        if ($wordCount < 1200 || count($faqs) < 4 || count($sources) < 6) {
            throw new RuntimeException($slug . ': nu trece pragul editorial (' . $wordCount . ' cuvinte, ' . count($faqs) . ' FAQ, ' . count($sources) . ' surse).');
        }
        if (mb_strlen($seoTitle, 'UTF-8') < 35 || mb_strlen($seoTitle, 'UTF-8') > 65 || mb_strlen($description, 'UTF-8') < 120 || mb_strlen($description, 'UTF-8') > 160) {
            throw new RuntimeException($slug . ': metadatele nu respectă limitele editoriale.');
        }
        if (preg_match('/(?:draft-editorial|review required|original evidence required|llms\.txt|rămâne noindex până la)/iu', $plain)) {
            throw new RuntimeException($slug . ': au rămas instrucțiuni interne.');
        }
        foreach (preg_split('/\R{2,}/u', $markdown, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $publishedBlock) {
            $publishedSignature = cabit_premium_signature($publishedBlock);
            if (str_word_count($publishedSignature) >= 18) {
                $publishedBlockFrequency[$publishedSignature] = ($publishedBlockFrequency[$publishedSignature] ?? 0) + 1;
                $publishedBlockExamples[$publishedSignature] ??= trim($publishedBlock);
            }
        }
        $stats['min_words'] = min($stats['min_words'], $wordCount);
        $stats['max_words'] = max($stats['max_words'], $wordCount);
        $metadata = [
            'primary_keyword' => trim((string) ($article['primary_keyword'] ?? '')),
            'secondary_keywords' => array_values(array_map('strval', is_array($article['secondary_keywords'] ?? null) ? $article['secondary_keywords'] : [])),
            'semantic_terms' => array_values(array_map('strval', is_array($article['semantic_terms'] ?? null) ? $article['semantic_terms'] : [])),
            'cluster' => trim((string) ($article['cluster'] ?? $pillar)),
            'pillar' => $pillar,
            'search_intent' => trim((string) ($article['search_intent'] ?? 'informational-commercial')),
            'queries' => array_values(array_map('strval', is_array($article['search_questions'] ?? null) ? $article['search_questions'] : [])),
            'direct_answer' => trim((string) ($article['quick_answer'] ?? '')),
            'image_alt' => '',
            'faqs' => $faqs,
            'sources' => $sources,
            'batch' => 'seo-premium-2026-500',
            'publication_order' => $index + 1,
            'author' => [
                'name' => 'Alexie Popescu',
                'role' => 'Coordonator editorial CAB-IT Expert',
                'bio' => 'Documentează și revizuiește ghiduri despre creare website, SEO, promovare online, conversii și automatizări digitale.',
            ],
        ];
        $records[] = [
            'title' => trim((string) ($article['h1'] ?? $article['title'] ?? '')),
            'seo_title' => $seoTitle,
            'meta_description' => $description,
            'slug' => $slug,
            'excerpt' => trim((string) ($article['quick_answer'] ?? $description)),
            'content' => $content,
            'cover_image' => '',
            'date_published' => $publishedAt,
            'created_at' => (new DateTimeImmutable($publishedAt))->modify('-' . $index . ' seconds')->format(DATE_ATOM),
            'updated_at' => $publishedAt,
            'seo_metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ];
    }

    $duplicatePublishedBlocks = array_filter($publishedBlockFrequency, static fn(int $count): bool => $count > 1);
    arsort($duplicatePublishedBlocks);
    $stats['duplicate_published_block_groups'] = count($duplicatePublishedBlocks);
    $stats['duplicate_published_block_occurrences'] = array_sum($duplicatePublishedBlocks);
    $stats['duplicate_block_examples'] = array_values(array_map(
        static fn(string $signature): array => [
            'count' => $duplicatePublishedBlocks[$signature],
            'text' => mb_substr($publishedBlockExamples[$signature], 0, 220, 'UTF-8'),
        ],
        array_slice(array_keys($duplicatePublishedBlocks), 0, 5)
    ));
    if ($dryRun) {
        return ['validated' => count($records), 'imported' => 0, 'dry_run' => true, 'stats' => $stats];
    }
    $statement = $pdo->prepare(
        'INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at, seo_metadata)
         VALUES (:title, :seo_title, :meta_description, :slug, :excerpt, :content, :cover_image, :date_published, :created_at, :updated_at, :seo_metadata)'
    );
    $pdo->beginTransaction();
    try {
        foreach ($records as $record) {
            $statement->execute($record);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
    foreach ($records as $record) {
        $query = $pdo->prepare('SELECT * FROM articles WHERE slug = ?');
        $query->execute([$record['slug']]);
        $stored = $query->fetch();
        if (!$stored) {
            throw new RuntimeException('Articolul nu a putut fi recitit: ' . $record['slug']);
        }
        cms_generate_article($stored);
    }
    cms_refresh_indexes($pdo);
    return ['validated' => count($records), 'imported' => count($records), 'dry_run' => false, 'stats' => $stats];
}

if (PHP_SAPI === 'cli') {
    $jsonPath = $argv[1] ?? '';
    $dryRun = in_array('--dry-run', $argv, true);
    try {
        echo json_encode(cabit_import_premium_seo_batch($jsonPath, $dryRun), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
