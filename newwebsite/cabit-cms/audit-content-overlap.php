<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function cabit_overlap_normalize(string $value): string
{
    $value = mb_strtolower(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'UTF-8');
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if ($ascii !== false) {
        $value = $ascii;
    }
    return trim(preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? '');
}

function cabit_overlap_tokens(string $value): array
{
    static $stop = [
        'acest' => true, 'aceasta' => true, 'aceste' => true, 'acesti' => true, 'ale' => true, 'al' => true,
        'a' => true, 'ai' => true, 'ca' => true, 'care' => true, 'ce' => true, 'cel' => true, 'cea' => true,
        'cu' => true, 'cum' => true, 'de' => true, 'din' => true, 'in' => true, 'la' => true, 'o' => true,
        'pe' => true, 'pentru' => true, 'prin' => true, 'sau' => true, 'si' => true, 'un' => true, 'unei' => true,
        'unui' => true, 'este' => true, 'sunt' => true, '2026' => true,
    ];
    $tokens = preg_split('/\s+/', cabit_overlap_normalize($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $set = [];
    foreach ($tokens as $token) {
        if (strlen($token) > 2 && !isset($stop[$token])) {
            $set[$token] = true;
        }
    }
    return $set;
}

function cabit_overlap_jaccard(array $left, array $right): float
{
    if ($left === [] || $right === []) {
        return 0.0;
    }
    $intersection = count(array_intersect_key($left, $right));
    $union = count($left) + count($right) - $intersection;
    return $union > 0 ? $intersection / $union : 0.0;
}

function cabit_audit_content_overlap(): array
{
    $rows = [];
    $primaryGroups = [];
    $bodyGroups = [];
    $paragraphGroups = [];

    foreach (cms_db()->query('SELECT slug, title, content, seo_metadata FROM articles ORDER BY id') as $row) {
        $metadata = cms_article_metadata($row);
        $batch = (string) ($metadata['batch'] ?? '');
        if (!in_array($batch, ['seo-local-2026-250', 'seo-premium-2026-500'], true)) {
            continue;
        }
        $primary = cabit_overlap_normalize((string) ($metadata['primary_keyword'] ?? ''));
        $city = cabit_overlap_normalize((string) ($metadata['city'] ?? ''));
        $content = (string) $row['content'];
        $entry = [
            'slug' => (string) $row['slug'],
            'title' => (string) $row['title'],
            'batch' => $batch,
            'city' => $city,
            'cluster' => cabit_overlap_normalize((string) ($metadata['cluster'] ?? '')),
            'primary' => $primary,
            'title_tokens' => cabit_overlap_tokens((string) $row['title']),
            'primary_tokens' => cabit_overlap_tokens($primary),
        ];
        $rows[] = $entry;
        if ($primary !== '') {
            $primaryGroups[$primary][] = $entry['slug'];
        }
        $bodySignature = hash('sha256', cabit_overlap_normalize($content));
        $bodyGroups[$bodySignature][] = $entry['slug'];

        if (preg_match_all('~<p\b[^>]*>(.*?)</p>~is', $content, $matches)) {
            foreach ($matches[1] as $paragraph) {
                $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($paragraph), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
                $normalized = cabit_overlap_normalize($plain);
                if (mb_strlen($normalized, 'UTF-8') < 150 || mb_strlen($normalized, 'UTF-8') > 1100) {
                    continue;
                }
                if (preg_match('/cab it expert colaboreaza online|sursele consultate|informatiile au caracter general|cere auditul gratuit/iu', $normalized)) {
                    continue;
                }
                $signature = hash('sha256', $normalized);
                if (!isset($paragraphGroups[$signature])) {
                    $paragraphGroups[$signature] = ['text' => $plain, 'slugs' => []];
                }
                $paragraphGroups[$signature]['slugs'][$entry['slug']] = true;
            }
        }
    }

    $duplicatePrimary = [];
    foreach ($primaryGroups as $primary => $slugs) {
        if (count($slugs) > 1) {
            $duplicatePrimary[] = ['primary_keyword' => $primary, 'count' => count($slugs), 'slugs' => $slugs];
        }
    }

    $duplicateBodies = [];
    foreach ($bodyGroups as $slugs) {
        if (count($slugs) > 1) {
            $duplicateBodies[] = $slugs;
        }
    }

    $riskPairs = [];
    $count = count($rows);
    for ($leftIndex = 0; $leftIndex < $count; $leftIndex++) {
        for ($rightIndex = $leftIndex + 1; $rightIndex < $count; $rightIndex++) {
            $left = $rows[$leftIndex];
            $right = $rows[$rightIndex];
            $sameGeography = $left['city'] === $right['city'];
            if (!$sameGeography) {
                continue;
            }
            $titleSimilarity = cabit_overlap_jaccard($left['title_tokens'], $right['title_tokens']);
            $primarySimilarity = cabit_overlap_jaccard($left['primary_tokens'], $right['primary_tokens']);
            $sameCluster = $left['cluster'] !== '' && $left['cluster'] === $right['cluster'];
            if ($primarySimilarity >= 0.86 || ($sameCluster && $titleSimilarity >= 0.72)) {
                $riskPairs[] = [
                    'left' => $left['slug'],
                    'right' => $right['slug'],
                    'title_similarity' => round($titleSimilarity, 3),
                    'primary_similarity' => round($primarySimilarity, 3),
                    'same_cluster' => $sameCluster,
                    'city' => $left['city'],
                ];
            }
        }
    }
    usort($riskPairs, static fn(array $left, array $right): int =>
        max($right['title_similarity'], $right['primary_similarity']) <=> max($left['title_similarity'], $left['primary_similarity'])
    );

    $repeatedParagraphs = [];
    foreach ($paragraphGroups as $group) {
        $slugs = array_keys($group['slugs']);
        if (count($slugs) >= 3) {
            $repeatedParagraphs[] = [
                'count' => count($slugs),
                'text' => mb_substr($group['text'], 0, 260, 'UTF-8'),
                'slugs' => array_slice($slugs, 0, 8),
            ];
        }
    }
    usort($repeatedParagraphs, static fn(array $left, array $right): int => $right['count'] <=> $left['count']);

    return [
        'passed' => $duplicatePrimary === [] && $duplicateBodies === [] && $riskPairs === [] && $repeatedParagraphs === [],
        'audited' => count($rows),
        'duplicate_primary_keywords' => $duplicatePrimary,
        'duplicate_full_bodies' => $duplicateBodies,
        'potential_cannibalization_pairs' => array_slice($riskPairs, 0, 50),
        'repeated_long_paragraph_groups' => array_slice($repeatedParagraphs, 0, 50),
        'method' => [
            'intent' => 'Cuvânt-cheie principal exact + Jaccard pe titlu și intenție, comparat doar în aceeași geografie.',
            'robotic_content' => 'Corp integral duplicat + paragrafe normalizate de minimum 150 de caractere repetate în minimum 3 articole.',
        ],
    ];
}

if (PHP_SAPI === 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $report = cabit_audit_content_overlap();
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    exit($report['passed'] ? 0 : 2);
}
