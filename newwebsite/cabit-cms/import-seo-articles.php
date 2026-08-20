<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function cabit_markdown_inline(string $text): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $escaped = preg_replace_callback(
        '~\[([^\]]+)\]\(((?:https?://|/)[^)\s]+)\)~u',
        static function (array $match): string {
            $url = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $external = str_starts_with($url, 'http') && !str_starts_with($url, CABIT_SITE_URL . '/');
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' . ($external ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' . $match[1] . '</a>';
        },
        $escaped
    ) ?? $escaped;
    $escaped = preg_replace('~`([^`]+)`~u', '<code>$1</code>', $escaped) ?? $escaped;
    $escaped = preg_replace('~\*\*(.+?)\*\*~u', '<strong>$1</strong>', $escaped) ?? $escaped;
    return $escaped;
}

function cabit_heading_id(string $heading): string
{
    $heading = preg_replace('~\[(.*?)\]\([^)]+\)~u', '$1', $heading) ?? $heading;
    $heading = str_replace(['**', '`'], '', $heading);
    $heading = strtr(mb_strtolower($heading, 'UTF-8'), [
        'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
    ]);
    $heading = preg_replace('/[^a-z0-9]+/u', '-', $heading) ?? '';
    return trim($heading, '-') ?: 'sectiune';
}

function cabit_markdown_table_cells(string $line): array
{
    $line = trim($line);
    $line = trim($line, '|');
    return array_map('trim', explode('|', $line));
}

function cabit_markdown_to_article_html(string $markdown): array
{
    $markdown = str_replace(["\r\n", "\r"], "\n", trim($markdown));
    $markdown = preg_replace('~\n##\s+Întrebări frecvente\s*\n.*$~su', '', $markdown) ?? $markdown;
    $lines = explode("\n", $markdown);
    $html = [];
    $toc = [];
    $headingCounts = [];
    $paragraph = [];
    $listType = null;
    $listItems = [];

    $flushParagraph = static function () use (&$paragraph, &$html): void {
        if (!$paragraph) {
            return;
        }
        $text = trim(implode(' ', array_map('trim', $paragraph)));
        if ($text !== '') {
            $html[] = '<p>' . cabit_markdown_inline($text) . '</p>';
        }
        $paragraph = [];
    };
    $flushList = static function () use (&$listType, &$listItems, &$html): void {
        if ($listType === null || !$listItems) {
            $listType = null;
            $listItems = [];
            return;
        }
        $items = implode('', array_map(static fn(string $item): string => '<li>' . cabit_markdown_inline($item) . '</li>', $listItems));
        $html[] = '<' . $listType . '>' . $items . '</' . $listType . '>';
        $listType = null;
        $listItems = [];
    };

    for ($index = 0, $count = count($lines); $index < $count; $index++) {
        $line = rtrim($lines[$index]);
        if (trim($line) === '') {
            $flushParagraph();
            $flushList();
            continue;
        }
        if (
            str_starts_with(trim($line), '|')
            && $index + 1 < $count
            && preg_match('/^\s*\|?(?:\s*:?-{3,}:?\s*\|)+\s*$/u', trim($lines[$index + 1]))
        ) {
            $flushParagraph();
            $flushList();
            $headers = cabit_markdown_table_cells($line);
            $index += 2;
            $rows = [];
            while ($index < $count && str_starts_with(trim($lines[$index]), '|')) {
                $rows[] = cabit_markdown_table_cells($lines[$index]);
                $index++;
            }
            $index--;
            $head = implode('', array_map(static fn(string $cell): string => '<th scope="col">' . cabit_markdown_inline($cell) . '</th>', $headers));
            $bodyRows = '';
            foreach ($rows as $row) {
                $cells = '';
                foreach ($headers as $cellIndex => $_header) {
                    $cells .= '<td>' . cabit_markdown_inline((string) ($row[$cellIndex] ?? '')) . '</td>';
                }
                $bodyRows .= '<tr>' . $cells . '</tr>';
            }
            $html[] = '<div class="cabit-article-table" role="region" aria-label="Tabel comparativ" tabindex="0"><table><thead><tr>' . $head . '</tr></thead><tbody>' . $bodyRows . '</tbody></table></div>';
            continue;
        }
        if (preg_match('/^>\s?(.*)$/u', $line, $match)) {
            $flushParagraph();
            $flushList();
            $quote = trim($match[1]);
            while ($index + 1 < $count && preg_match('/^>\s?(.*)$/u', $lines[$index + 1], $next)) {
                $quote .= ' ' . trim($next[1]);
                $index++;
            }
            $quote = preg_replace('/^\*\*(?:Răspuns direct|Pe scurt):\*\*\s*/ui', '', $quote) ?? $quote;
            $html[] = '<aside class="cabit-answer-box"><span>Răspuns rapid</span><p>' . cabit_markdown_inline($quote) . '</p></aside>';
            continue;
        }
        if (preg_match('/^(#{2,3})\s+(.+)$/u', $line, $match)) {
            $flushParagraph();
            $flushList();
            $level = strlen($match[1]);
            $heading = trim($match[2]);
            $baseId = cabit_heading_id($heading);
            $headingCounts[$baseId] = ($headingCounts[$baseId] ?? 0) + 1;
            $id = $baseId . ($headingCounts[$baseId] > 1 ? '-' . $headingCounts[$baseId] : '');
            $html[] = '<h' . $level . ' id="' . $id . '">' . cabit_markdown_inline($heading) . '</h' . $level . '>';
            if ($level === 2 && !in_array($baseId, ['intrebari-frecvente', 'surse-oficiale-si-documentatie'], true)) {
                $toc[] = ['id' => $id, 'title' => preg_replace('~\*\*(.*?)\*\*~u', '$1', $heading) ?? $heading];
            }
            continue;
        }
        if (preg_match('/^-\s+(.+)$/u', $line, $match)) {
            $flushParagraph();
            if ($listType !== null && $listType !== 'ul') {
                $flushList();
            }
            $listType = 'ul';
            $listItems[] = trim($match[1]);
            continue;
        }
        if (preg_match('/^\d+\.\s+(.+)$/u', $line, $match)) {
            $flushParagraph();
            if ($listType !== null && $listType !== 'ol') {
                $flushList();
            }
            $listType = 'ol';
            $listItems[] = trim($match[1]);
            continue;
        }
        $flushList();
        $paragraph[] = $line;
    }
    $flushParagraph();
    $flushList();

    $body = implode("\n", $html);
    if ($toc) {
        $tocItems = implode('', array_map(static fn(array $item): string => '<li><a href="#' . $item['id'] . '">' . cabit_markdown_inline($item['title']) . '</a></li>', $toc));
        $tocHtml = '<nav class="cabit-article-toc" aria-label="Cuprinsul articolului"><strong>Cuprins</strong><ol>' . $tocItems . '</ol></nav>';
        $answerEnd = strpos($body, '</aside>');
        $body = $answerEnd !== false
            ? substr_replace($body, '</aside>' . $tocHtml, $answerEnd, strlen('</aside>'))
            : $tocHtml . $body;
    }
    return ['html' => $body, 'toc' => $toc];
}

function cabit_article_faq_html(array $faqs): string
{
    $items = '';
    foreach ($faqs as $faq) {
        $question = trim((string) ($faq['q'] ?? ''));
        $answer = trim((string) ($faq['a'] ?? ''));
        if ($question === '' || $answer === '') {
            continue;
        }
        $items .= '<details><summary>' . htmlspecialchars($question, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</summary><p>' . htmlspecialchars($answer, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p></details>';
    }
    return $items === '' ? '' : '<section class="cabit-article-faq" id="intrebari-frecvente" aria-label="Întrebări frecvente"><h2>Întrebări frecvente</h2>' . $items . '</section>';
}

function cabit_article_sources_html(array $sources, string $verifiedDate): string
{
    $seen = [];
    $items = '';
    foreach ($sources as $source) {
        $name = trim((string) ($source['name'] ?? $source['title'] ?? ''));
        $url = trim((string) ($source['url'] ?? ''));
        if ($name === '' || !preg_match('~^https://~i', $url) || isset($seen[$url])) {
            continue;
        }
        $seen[$url] = true;
        $items .= '<li><a href="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a></li>';
    }
    if ($items === '') {
        return '';
    }
    return '<section class="cabit-article-sources" id="surse-oficiale-si-documentatie" aria-label="Surse oficiale"><h2>Surse oficiale și documentație</h2><p>Informațiile despre platforme au fost verificate la <time datetime="' . $verifiedDate . '">' . date('d.m.Y', strtotime($verifiedDate)) . '</time>.</p><ul>' . $items . '</ul></section>';
}

function cabit_related_articles(array $articles, int $currentIndex): array
{
    $cluster = (string) $articles[$currentIndex]['cluster'];
    $clusterIndexes = [];
    foreach ($articles as $index => $article) {
        if ((string) $article['cluster'] === $cluster && $index !== $currentIndex) {
            $clusterIndexes[] = $index;
        }
    }
    usort($clusterIndexes, static fn(int $left, int $right): int => ((int) $articles[$left]['publication_order']) <=> ((int) $articles[$right]['publication_order']));
    $currentOrder = (int) $articles[$currentIndex]['publication_order'];
    usort($clusterIndexes, static function (int $left, int $right) use ($articles, $currentOrder): int {
        return abs((int) $articles[$left]['publication_order'] - $currentOrder) <=> abs((int) $articles[$right]['publication_order'] - $currentOrder);
    });
    return array_map(static fn(int $index): array => [
        'label' => 'Ghid ' . $cluster,
        'title' => (string) $articles[$index]['title'],
        'url' => '/blog/' . $articles[$index]['slug'] . '/',
    ], array_slice($clusterIndexes, 0, 3));
}

function cabit_import_seo_articles(string $jsonPath, string $publishDate = '', bool $dryRun = false): array
{
    if (!is_file($jsonPath)) {
        throw new InvalidArgumentException('Fișierul JSON nu există: ' . $jsonPath);
    }
    $decoded = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
    $articles = $decoded['articles'] ?? null;
    if (!is_array($articles) || count($articles) !== 50) {
        throw new RuntimeException('Importul trebuie să conțină exact 50 de articole.');
    }
    usort($articles, static fn(array $left, array $right): int => ((int) $left['publication_order']) <=> ((int) $right['publication_order']));
    $publishDate = $publishDate !== '' ? $publishDate : date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishDate)) {
        throw new InvalidArgumentException('Data publicării trebuie să fie YYYY-MM-DD.');
    }

    $slugs = [];
    $records = [];
    $baseTimestamp = strtotime($publishDate . ' 18:00:00 Europe/Bucharest');
    foreach ($articles as $index => $article) {
        $slug = trim((string) ($article['slug'] ?? ''));
        if (!cms_valid_slug($slug) || isset($slugs[$slug])) {
            throw new RuntimeException('Slug invalid sau duplicat: ' . $slug);
        }
        $slugs[$slug] = true;
        $imagePath = '/assets/img/blog/seo-2026/' . $slug . '.webp';
        if (!is_file(CABIT_PUBLIC_ROOT . $imagePath)) {
            $imagePath = '';
        }
        $converted = cabit_markdown_to_article_html((string) ($article['content_markdown'] ?? ''));
        $faqs = is_array($article['faqs'] ?? null) ? $article['faqs'] : [];
        $sources = is_array($article['fact_check_sources'] ?? null) ? $article['fact_check_sources'] : [];
        $content = $converted['html']
            . cabit_article_faq_html($faqs)
            . cabit_article_sources_html($sources, $publishDate);
        $metadata = [
            'primary_keyword' => (string) ($article['primary_keyword'] ?? ''),
            'secondary_keywords' => array_values(array_map('strval', is_array($article['secondary_keywords'] ?? null) ? $article['secondary_keywords'] : [])),
            'cluster' => (string) ($article['cluster'] ?? ''),
            'search_intent' => (string) ($article['search_intent'] ?? ''),
            'image_alt' => $imagePath !== '' ? (string) ($article['hero_image_alt'] ?? $article['title'] ?? '') : '',
            'faqs' => $faqs,
            'sources' => $sources,
            'publication_order' => (int) ($article['publication_order'] ?? ($index + 1)),
            'related_articles' => cabit_related_articles($articles, $index),
            'author' => [
                'name' => 'Alexie Popescu',
                'role' => 'Coordonator editorial CAB-IT Expert',
                'bio' => 'Documentează și revizuiește ghiduri despre creare website, SEO, promovare online, măsurarea conversiilor și automatizări digitale.',
            ],
        ];
        $records[] = [
            'title' => trim((string) ($article['h1'] ?? $article['title'] ?? '')),
            'seo_title' => trim((string) ($article['meta_title'] ?? $article['title'] ?? '')),
            'meta_description' => trim((string) ($article['meta_description'] ?? '')),
            'slug' => $slug,
            'excerpt' => trim((string) ($article['excerpt'] ?? '')),
            'content' => $content,
            'cover_image' => $imagePath,
            'date_published' => $publishDate,
            'created_at' => date('c', $baseTimestamp - ((int) $metadata['publication_order'] * 60)),
            'updated_at' => date('c', $baseTimestamp),
            'seo_metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ];
    }
    if ($dryRun) {
        return ['imported' => 0, 'validated' => count($records), 'dry_run' => true, 'slugs' => array_column($records, 'slug')];
    }

    $pdo = cms_db();
    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            'INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at, seo_metadata)
             VALUES (:title, :seo_title, :meta_description, :slug, :excerpt, :content, :cover_image, :date_published, :created_at, :updated_at, :seo_metadata)
             ON CONFLICT(slug) DO UPDATE SET title = excluded.title, seo_title = excluded.seo_title, meta_description = excluded.meta_description,
             excerpt = excluded.excerpt, content = excluded.content, cover_image = excluded.cover_image, date_published = excluded.date_published,
             created_at = excluded.created_at, updated_at = excluded.updated_at, seo_metadata = excluded.seo_metadata'
        );
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
    return ['imported' => count($records), 'validated' => count($records), 'dry_run' => false, 'slugs' => array_column($records, 'slug')];
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    $jsonPath = $argv[1] ?? '';
    $publishDate = $argv[2] ?? date('Y-m-d');
    $dryRun = in_array('--dry-run', $argv, true);
    try {
        $result = cabit_import_seo_articles($jsonPath, $publishDate, $dryRun);
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
