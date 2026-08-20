<?php
declare(strict_types=1);

require_once __DIR__ . '/import-local-seo-batch.php';

function cabit_audit_words(string $content): array
{
    $text = mb_strtolower(strip_tags($content), 'UTF-8');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('/[^a-z0-9]+/i', ' ', $text) ?? $text;
    return preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
}

function cabit_audit_shingles(string $content, int $size = 5): array
{
    $words = cabit_audit_words($content);
    $shingles = [];
    $limit = count($words) - $size;
    for ($index = 0; $index <= $limit; $index++) {
        $shingles[implode(' ', array_slice($words, $index, $size))] = true;
    }
    return $shingles;
}

function cabit_audit_similarity(array $left, array $right): float
{
    if ($left === [] || $right === []) {
        return 0.0;
    }
    $intersection = count(array_intersect_key($left, $right));
    $union = count($left) + count($right) - $intersection;
    return $union > 0 ? $intersection / $union : 0.0;
}

function cabit_audit_selection(string $articlesDirectory, array $slugs): array
{
    $pdo = cms_db();
    $existing = [];
    foreach ($pdo->query('SELECT slug, content FROM articles') as $row) {
        $existing[] = [
            'slug' => (string) $row['slug'],
            'shingles' => cabit_audit_shingles((string) $row['content']),
        ];
    }

    $candidates = [];
    foreach ($slugs as $slug) {
        if (!cms_valid_slug($slug)) {
            throw new RuntimeException('Slug invalid: ' . $slug);
        }
        $path = rtrim($articlesDirectory, '/\\') . DIRECTORY_SEPARATOR . $slug . '.json';
        if (!is_file($path)) {
            throw new RuntimeException('Articol inexistent: ' . $slug);
        }
        $article = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $markdown = cabit_local_markdown((string) ($article['content_markdown'] ?? ''), (string) ($article['city'] ?? 'România'));
        $faqs = cabit_local_faqs(is_array($article['faqs'] ?? null) ? $article['faqs'] : []);
        $sources = is_array($article['fact_check_sources'] ?? null) ? $article['fact_check_sources'] : [];
        $plainWords = cabit_audit_words($markdown);
        $candidates[$slug] = [
            'article' => $article,
            'markdown' => $markdown,
            'shingles' => cabit_audit_shingles($markdown),
            'word_count' => count($plainWords),
            'faq_count' => count($faqs),
            'source_count' => count($sources),
        ];
    }

    $reports = [];
    $passed = true;
    foreach ($candidates as $slug => $candidate) {
        $maxExisting = ['slug' => '', 'similarity' => 0.0];
        foreach ($existing as $existingArticle) {
            if ($existingArticle['slug'] === $slug) {
                continue;
            }
            $similarity = cabit_audit_similarity($candidate['shingles'], $existingArticle['shingles']);
            if ($similarity > $maxExisting['similarity']) {
                $maxExisting = ['slug' => $existingArticle['slug'], 'similarity' => $similarity];
            }
        }
        $maxBatch = ['slug' => '', 'similarity' => 0.0];
        foreach ($candidates as $otherSlug => $otherCandidate) {
            if ($otherSlug === $slug) {
                continue;
            }
            $similarity = cabit_audit_similarity($candidate['shingles'], $otherCandidate['shingles']);
            if ($similarity > $maxBatch['similarity']) {
                $maxBatch = ['slug' => $otherSlug, 'similarity' => $similarity];
            }
        }
        $article = $candidate['article'];
        $image = CABIT_PUBLIC_ROOT . '/assets/img/blog/seo-local-2026/' . $slug . '.webp';
        $forbidden = preg_match('/\b(?:noindex|draft(?:ul)?|smart search pentru acest articol|înainte de indexare|înainte de publicare|test editorial)\b/iu', $candidate['markdown']) === 1;
        $checks = [
            'word_count' => $candidate['word_count'] >= 1800,
            'faq_count' => $candidate['faq_count'] >= 4,
            'source_count' => $candidate['source_count'] >= 5,
            'meta_title' => mb_strlen((string) ($article['meta_title'] ?? ''), 'UTF-8') >= 35 && mb_strlen((string) ($article['meta_title'] ?? ''), 'UTF-8') <= 65,
            'meta_description' => mb_strlen((string) ($article['meta_description'] ?? ''), 'UTF-8') >= 120 && mb_strlen((string) ($article['meta_description'] ?? ''), 'UTF-8') <= 160,
            'forbidden_editorial_text' => !$forbidden,
            'similarity_existing' => $maxExisting['similarity'] < 0.45,
            'similarity_batch' => $maxBatch['similarity'] < 0.45,
        ];
        $articlePassed = !in_array(false, $checks, true);
        $passed = $passed && $articlePassed;
        $reports[] = [
            'slug' => $slug,
            'title' => (string) ($article['title'] ?? ''),
            'city' => (string) ($article['city'] ?? ''),
            'pillar' => (string) ($article['pillar'] ?? ''),
            'word_count' => $candidate['word_count'],
            'faq_count' => $candidate['faq_count'],
            'source_count' => $candidate['source_count'],
            'has_image' => is_file($image),
            'closest_existing' => $maxExisting['slug'],
            'similarity_existing' => round($maxExisting['similarity'], 4),
            'closest_batch' => $maxBatch['slug'],
            'similarity_batch' => round($maxBatch['similarity'], 4),
            'checks' => $checks,
            'passed' => $articlePassed,
        ];
    }

    return [
        'passed' => $passed,
        'selected' => count($reports),
        'existing_articles_compared' => count($existing),
        'similarity_method' => 'Jaccard pe secvențe de 5 cuvinte; prag 0.45',
        'articles' => $reports,
    ];
}

if (PHP_SAPI === 'cli') {
    $articlesDirectory = $argv[1] ?? (__DIR__ . '/data/seo-local-2026/articles');
    $slugs = [];
    foreach ($argv as $argument) {
        if (str_starts_with($argument, '--slugs=')) {
            $slugs = array_values(array_filter(array_map('trim', explode(',', substr($argument, 8)))));
        }
    }
    if ($slugs === []) {
        fwrite(STDERR, "Folosește --slugs=slug-1,slug-2\n");
        exit(1);
    }
    try {
        $report = cabit_audit_selection($articlesDirectory, $slugs);
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
        exit($report['passed'] ? 0 : 2);
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
