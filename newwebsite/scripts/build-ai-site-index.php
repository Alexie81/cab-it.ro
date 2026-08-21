<?php
declare(strict_types=1);

$root = realpath(dirname(__DIR__));
if ($root === false) {
    throw new RuntimeException('Nu am putut identifica rădăcina website-ului.');
}

$storage = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'ai';
if (!is_dir($storage) && !mkdir($storage, 0775, true) && !is_dir($storage)) {
    throw new RuntimeException('Nu am putut crea directorul pentru indexul AI.');
}

$target = $storage . DIRECTORY_SEPARATOR . 'CAB_IT_SITE_CONTENT.sqlite3';
$temporary = $target . '.building';
if (is_file($temporary)) {
    unlink($temporary);
}

$pdo = new PDO('sqlite:' . $temporary, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA journal_mode = OFF');
$pdo->exec('PRAGMA synchronous = OFF');
$pdo->exec('PRAGMA temp_store = MEMORY');
$pdo->exec('CREATE TABLE metadata (key TEXT PRIMARY KEY, value TEXT NOT NULL)');
$pdo->exec('CREATE TABLE documents (
    id INTEGER PRIMARY KEY,
    url TEXT NOT NULL UNIQUE,
    path TEXT NOT NULL,
    source_type TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL DEFAULT \'\',
    published_at TEXT NOT NULL DEFAULT \'\',
    modified_at TEXT NOT NULL DEFAULT \'\',
    content_hash TEXT NOT NULL
)');
$pdo->exec('CREATE TABLE chunks (
    id INTEGER PRIMARY KEY,
    document_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    heading TEXT NOT NULL DEFAULT \'\',
    content TEXT NOT NULL,
    url TEXT NOT NULL,
    source_type TEXT NOT NULL,
    priority INTEGER NOT NULL,
    FOREIGN KEY(document_id) REFERENCES documents(id)
)');
$pdo->exec("CREATE VIRTUAL TABLE chunks_fts USING fts5(
    title,
    heading,
    content,
    url UNINDEXED,
    source_type UNINDEXED,
    content='chunks',
    content_rowid='id',
    tokenize='unicode61 remove_diacritics 2'
)");
$pdo->exec('CREATE INDEX chunks_document_idx ON chunks(document_id)');
$pdo->exec('CREATE INDEX chunks_source_priority_idx ON chunks(source_type, priority DESC)');

$insertDocument = $pdo->prepare('INSERT INTO documents(url,path,source_type,title,description,published_at,modified_at,content_hash) VALUES(?,?,?,?,?,?,?,?)');
$insertChunk = $pdo->prepare('INSERT INTO chunks(document_id,title,heading,content,url,source_type,priority) VALUES(?,?,?,?,?,?,?)');
$insertOfficial = $pdo->prepare('INSERT INTO chunks(document_id,title,heading,content,url,source_type,priority) VALUES(?,?,?,?,?,?,90)');

function clean_text(?string $value): string
{
    $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim((string) preg_replace('/\s+/u', ' ', $value));
}

function meta_content(DOMXPath $xpath, string $name, string $attribute = 'name'): string
{
    $nodes = $xpath->query('//meta[translate(@' . $attribute . ',"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="' . strtolower($name) . '"]/@content');
    return $nodes !== false && $nodes->length > 0 ? clean_text($nodes->item(0)?->nodeValue) : '';
}

function node_is_navigation(DOMNode $node): bool
{
    $skipClasses = 'breadcrumb|toc|related|sticky-aside|author-card|final-cta|article-sources|article-date|sidebar|pagination|share|cookie|consent';
    for ($current = $node; $current instanceof DOMNode; $current = $current->parentNode) {
        $tag = strtolower($current->nodeName);
        if (in_array($tag, ['nav', 'footer', 'form', 'script', 'style', 'template', 'noscript'], true)) {
            return true;
        }
        if ($current instanceof DOMElement) {
            $classes = strtolower($current->getAttribute('class'));
            if ($classes !== '' && preg_match('/(?:' . $skipClasses . ')/', $classes) === 1) {
                return true;
            }
        }
    }
    return false;
}

/** @return list<array{heading:string,content:string}> */
function extract_chunks(DOMXPath $xpath, string $title, string $description): array
{
    $chunks = [];
    $heading = $title;
    $buffer = $description !== '' ? [$description] : [];
    $flush = static function () use (&$chunks, &$buffer, &$heading): void {
        $content = clean_text(implode(' ', $buffer));
        if (mb_strlen($content, 'UTF-8') >= 80) {
            $chunks[] = ['heading' => $heading, 'content' => mb_substr($content, 0, 1200, 'UTF-8')];
        }
        $buffer = [];
    };

    $nodes = $xpath->query('//main//*[self::h1 or self::h2 or self::h3 or self::p or self::li]');
    if ($nodes === false || $nodes->length === 0) {
        $nodes = $xpath->query('//body//*[self::h1 or self::h2 or self::h3 or self::p or self::li]');
    }
    if ($nodes === false) {
        return [];
    }

    foreach ($nodes as $node) {
        if (node_is_navigation($node)) {
            continue;
        }
        $text = clean_text($node->textContent);
        if ($text === '') {
            continue;
        }
        $tag = strtolower($node->nodeName);
        if (in_array($tag, ['h1', 'h2', 'h3'], true)) {
            $flush();
            $heading = $text;
            continue;
        }
        if (mb_strlen($text, 'UTF-8') < 35) {
            continue;
        }
        $candidate = clean_text(implode(' ', array_merge($buffer, [$text])));
        if ($buffer !== [] && mb_strlen($candidate, 'UTF-8') > 900) {
            $flush();
        }
        $buffer[] = $text;
    }
    $flush();
    return $chunks;
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$files = [];
foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getFilename()) !== 'index.html') {
        continue;
    }
    $path = $file->getPathname();
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
    if (preg_match('#^(?:404/|assets/|cabit-cms/|storage/|tmp/|vendor/)#', $relative) === 1) {
        continue;
    }
    $files[] = $path;
}
sort($files, SORT_STRING);

$documentCount = 0;
$chunkCount = 0;
$officialReferenceCount = 0;
$pdo->beginTransaction();
foreach ($files as $path) {
    $html = file_get_contents($path);
    if (!is_string($html) || $html === '') {
        continue;
    }
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) {
        continue;
    }
    $xpath = new DOMXPath($document);
    $robots = strtolower(meta_content($xpath, 'robots'));
    if (str_contains($robots, 'noindex')) {
        continue;
    }
    $canonicalNodes = $xpath->query('//link[translate(@rel,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="canonical"]/@href');
    $canonical = $canonicalNodes !== false && $canonicalNodes->length > 0 ? clean_text($canonicalNodes->item(0)?->nodeValue) : '';
    if ($canonical === '' || !preg_match('#^https://(?:www\.)?cab-it\.ro/#i', $canonical)) {
        continue;
    }
    $titleNodes = $xpath->query('//title');
    $title = $titleNodes !== false && $titleNodes->length > 0 ? clean_text($titleNodes->item(0)?->textContent) : '';
    $description = meta_content($xpath, 'description');
    if ($title === '') {
        continue;
    }
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
    $sourceType = str_starts_with($relative, 'blog/') ? 'cab_it_article' : 'cab_it_page';
    $publishedAt = meta_content($xpath, 'article:published_time', 'property');
    $modifiedAt = meta_content($xpath, 'article:modified_time', 'property');
    $chunks = extract_chunks($xpath, $title, $description);
    if ($chunks === []) {
        continue;
    }
    $contentHash = hash('sha256', implode("\n", array_column($chunks, 'content')));
    $insertDocument->execute([$canonical, $relative, $sourceType, $title, $description, $publishedAt, $modifiedAt, $contentHash]);
    $documentId = (int) $pdo->lastInsertId();
    $documentCount++;
    foreach ($chunks as $chunk) {
        $insertChunk->execute([$documentId, $title, $chunk['heading'], $chunk['content'], $canonical, $sourceType, 95]);
        $chunkCount++;
    }

    $officialNodes = $xpath->query('//*[contains(concat(" ",normalize-space(@class)," ")," cabit-article-sources ")]//a[@href]');
    if ($officialNodes !== false) {
        foreach ($officialNodes as $officialNode) {
            if (!$officialNode instanceof DOMElement) {
                continue;
            }
            $officialUrl = clean_text($officialNode->getAttribute('href'));
            $officialTitle = clean_text($officialNode->textContent);
            $host = strtolower((string) parse_url($officialUrl, PHP_URL_HOST));
            if ($officialUrl === '' || $officialTitle === '' || $host === '' || in_array($host, ['cab-it.ro', 'www.cab-it.ro'], true)) {
                continue;
            }
            $insertOfficial->execute([$documentId, $title, 'Documentație oficială asociată', 'Referință oficială asociată articolului „' . $title . '”: ' . $officialTitle, $officialUrl, 'official_reference']);
            $officialReferenceCount++;
        }
    }
}
$pdo->commit();

$pdo->exec("INSERT INTO chunks_fts(chunks_fts) VALUES('rebuild')");
$pdo->exec("INSERT INTO chunks_fts(chunks_fts) VALUES('optimize')");
$metadata = $pdo->prepare('INSERT INTO metadata(key,value) VALUES(?,?)');
foreach ([
    'built_at' => gmdate('c'),
    'documents' => (string) $documentCount,
    'content_chunks' => (string) $chunkCount,
    'official_references' => (string) $officialReferenceCount,
    'source_priority' => 'commercial_core:100,cab_it_page_or_article:95,official_reference:90',
] as $key => $value) {
    $metadata->execute([$key, $value]);
}
$integrity = (string) $pdo->query('PRAGMA integrity_check')->fetchColumn();
$insertDocument = null;
$insertChunk = null;
$insertOfficial = null;
$metadata = null;
$pdo = null;
if ($integrity !== 'ok') {
    throw new RuntimeException('Indexul generat nu a trecut verificarea de integritate: ' . $integrity);
}
if (is_file($target)) {
    unlink($target);
}
if (!rename($temporary, $target)) {
    throw new RuntimeException('Nu am putut publica indexul nou al site-ului.');
}

echo json_encode([
    'ok' => true,
    'database' => $target,
    'documents' => $documentCount,
    'content_chunks' => $chunkCount,
    'official_references' => $officialReferenceCount,
    'bytes' => filesize($target),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
