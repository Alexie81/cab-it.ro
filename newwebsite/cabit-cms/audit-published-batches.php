<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function cabit_batch_words(string $html): int
{
    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
    return count(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: []);
}

function cabit_audit_published_batches(): array
{
    $pdo = cms_db();
    $rows = $pdo->query('SELECT * FROM articles ORDER BY id')->fetchAll();
    $search = json_decode((string) file_get_contents(CABIT_PUBLIC_ROOT . '/blog-search-index.json'), true, 512, JSON_THROW_ON_ERROR);
    $searchSlugs = [];
    foreach ($search['articles'] ?? [] as $entry) {
        $searchSlugs[(string) ($entry['slug'] ?? '')] = true;
    }
    $sitemap = (string) file_get_contents(CABIT_PUBLIC_ROOT . '/sitemap-articles.xml');
    $counts = [];
    $errors = [];
    $warnings = [];
    $audited = 0;
    $minWords = [];
    $maxWords = [];

    foreach ($rows as $row) {
        $metadata = cms_article_metadata($row);
        $batch = (string) ($metadata['batch'] ?? '');
        if (!in_array($batch, ['seo-local-2026-250', 'seo-premium-2026-500'], true)) {
            continue;
        }
        $audited++;
        $counts[$batch] = ($counts[$batch] ?? 0) + 1;
        $slug = (string) $row['slug'];
        $path = CABIT_PUBLIC_ROOT . '/blog/' . $slug . '/index.html';
        $gzipPath = $path . '.gz';
        if (!is_file($path) || !is_file($gzipPath)) {
            $errors[] = $slug . ': lipsesc fișierele HTML/GZIP';
            continue;
        }
        $html = (string) file_get_contents($path);
        $decoded = gzdecode((string) file_get_contents($gzipPath));
        if ($decoded !== $html) {
            $errors[] = $slug . ': fișierul GZIP nu corespunde paginii';
        }
        $expectedCanonical = CABIT_SITE_URL . '/blog/' . $slug . '/';
        if (!str_contains($html, '<link rel="canonical" href="' . $expectedCanonical . '">')) {
            $errors[] = $slug . ': canonical incorect';
        }
        if (!preg_match('/<meta name="robots" content="index, follow,/i', $html)) {
            $errors[] = $slug . ': directivă robots neindexabilă';
        }
        if (preg_match_all('/<h1\b/i', $html) !== 1) {
            $errors[] = $slug . ': număr H1 diferit de 1';
        }
        if (!str_contains($html, '"@type":"BlogPosting"') || !str_contains($html, '"@type":"BreadcrumbList"')) {
            $errors[] = $slug . ': schema BlogPosting/BreadcrumbList lipsește';
        }
        $faqs = is_array($metadata['faqs'] ?? null) ? $metadata['faqs'] : [];
        $sources = is_array($metadata['sources'] ?? null) ? $metadata['sources'] : [];
        if (count($faqs) < 4 || count($sources) < 5) {
            $errors[] = $slug . ': FAQ sau surse insuficiente';
        }
        if (!isset($searchSlugs[$slug])) {
            $errors[] = $slug . ': lipsește din Smart Search';
        }
        if (!str_contains($sitemap, '<loc>' . $expectedCanonical . '</loc>')) {
            $errors[] = $slug . ': lipsește din sitemap';
        }
        if (preg_match('/(?:draft-editorial|review required|original evidence required|rămâne noindex până la)/iu', strip_tags((string) $row['content']))) {
            $errors[] = $slug . ': au rămas instrucțiuni interne';
        }
        $words = cabit_batch_words((string) $row['content']);
        $minWords[$batch] = min($minWords[$batch] ?? PHP_INT_MAX, $words);
        $maxWords[$batch] = max($maxWords[$batch] ?? 0, $words);
        if ($batch === 'seo-local-2026-250' && $words < 1800) {
            $errors[] = $slug . ': sub 1.800 de cuvinte';
        }
        if ($batch === 'seo-premium-2026-500' && $words < 1200) {
            $errors[] = $slug . ': sub 1.200 de cuvinte';
        }

        if (preg_match_all('~href="(/[^"#?]+)"~', (string) $row['content'], $links)) {
            foreach (array_unique($links[1]) as $urlPath) {
                $local = CABIT_PUBLIC_ROOT . ($urlPath === '/' ? '/index.html' : rtrim($urlPath, '/') . '/index.html');
                if (!is_file($local)) {
                    $warnings[] = $slug . ': legătură internă fără pagină locală ' . $urlPath;
                }
            }
        }
    }

    $expected = ['seo-local-2026-250' => 250, 'seo-premium-2026-500' => 500];
    foreach ($expected as $batch => $expectedCount) {
        if (($counts[$batch] ?? 0) !== $expectedCount) {
            $errors[] = $batch . ': ' . ($counts[$batch] ?? 0) . ' articole în loc de ' . $expectedCount;
        }
    }
    return [
        'passed' => $errors === [],
        'audited' => $audited,
        'counts' => $counts,
        'word_range' => array_map(static fn(string $batch): array => [
            'min' => $minWords[$batch] ?? 0,
            'max' => $maxWords[$batch] ?? 0,
        ], array_keys($expected)),
        'smart_search_total' => count($searchSlugs),
        'errors' => $errors,
        'warnings' => array_values(array_unique($warnings)),
    ];
}

if (PHP_SAPI === 'cli') {
    $report = cabit_audit_published_batches();
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    exit($report['passed'] ? 0 : 2);
}
