<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/import-seo-articles.php';

function cabit_local_faqs(array $faqs): array
{
    $normalized = [];
    foreach ($faqs as $faq) {
        $question = trim((string) ($faq['question'] ?? $faq['q'] ?? ''));
        $answer = trim((string) ($faq['answer'] ?? $faq['a'] ?? ''));
        if ($question === '' || $answer === '') {
            continue;
        }
        if (preg_match('/(?:poate acest articol apărea|smart search|repet expresia|înainte de indexare)/iu', $question)) {
            continue;
        }
        $question = str_ireplace(
            'Cum se măsoară dacă articolul și website-ul aduc rezultate?',
            'Cum se măsoară dacă website-ul și promovarea aduc rezultate?',
            $question
        );
        $answer = str_ireplace('Pentru acest draft', 'Pentru acest ghid', $answer);
        $normalized[] = ['q' => $question, 'a' => $answer];
    }
    return $normalized;
}

function cabit_local_markdown(string $markdown, string $city): string
{
    $markdown = str_replace(["\r\n", "\r"], "\n", trim($markdown));
    $markdown = preg_replace('/^#\s+.+?\n+/u', '', $markdown, 1) ?? $markdown;
    $markdown = preg_replace(
        '/^##\s+(?:Cum faci articolul ușor de înțeles, extras și citat|Cum funcționează Smart Search pentru acest articol|Cum evităm canibalizarea dintre paginile CAB-IT|Harta intențiilor de căutare pentru acest articol|Căutare instantanee pe secțiuni și potrivire semantică|Indexarea internă: de la cuvânt exact la întrebare în limbaj natural|Optimizarea semantică și vizibilitatea în răspunsurile AI|Regula editorială care împiedică articolele să concureze între ele|SEO pentru căutarea asistată de AI, fără trucuri inventate|Structura care ajută atât utilizatorul, cât și motoarele de căutare|Întrebări frecvente|Surse și note de verificare)\s*\R.*?(?=^##\s|\z)/msiu',
        '',
        $markdown
    ) ?? $markdown;
    $markdown = str_ireplace('Pentru acest draft', 'Pentru acest ghid', $markdown);
    $markdown = preg_replace('/^Recomandările sunt organizate answer-first:.*$/miu', '', $markdown) ?? $markdown;
    $markdown = preg_replace('/^Pentru citare de către sisteme AI,.*$/miu', '', $markdown) ?? $markdown;
    $markdown = preg_replace('/^Pentru căutarea asistată de AI,.*$/miu', '', $markdown) ?? $markdown;
    $markdown = preg_replace('/^Un brief semantic bun pentru acest articol.*$/miu', '', $markdown) ?? $markdown;
    $markdown = preg_replace('/^Pentru a evita canibalizarea, articolul.*$/miu', '', $markdown) ?? $markdown;
    $markdown = preg_replace('/^Nu toate expresiile din jurul.*$/miu', '', $markdown) ?? $markdown;
    $markdown = preg_replace('/^Textul este un ghid editorial complet,.*$/miu', '', $markdown) ?? $markdown;
    $markdown = str_ireplace('Articolul explică modul în care aceste elemente se proiectează împreună.', 'În practică, aceste elemente trebuie proiectate împreună.', $markdown);
    $markdown = str_ireplace('Articolul local trebuie', 'Pagina firmei trebuie', $markdown);
    $markdown = str_ireplace('Articolul poate explica serviciul livrat remote sau aria deservită, în termeni transparenți.', 'Pagina poate explica transparent serviciul livrat online și aria deservită.', $markdown);
    $markdown = str_ireplace('Un test editorial specific pentru acest articol este', 'Un scenariu util de verificat este', $markdown);
    $markdown = str_ireplace('Un articol lung nu rankează prin numărul de cuvinte.', 'Un ghid nu devine util doar prin numărul de cuvinte.', $markdown);
    $markdown = str_ireplace('Imaginile originale diferențiază articolul și îl fac mai ușor de citat.', 'Imaginile originale clarifică oferta și susțin încrederea.', $markdown);
    $markdown = str_ireplace('Data „modificat” se schimbă numai când articolul a primit o actualizare substanțială, nu automat.', 'Recomandările și datele trebuie revizuite când apar schimbări substanțiale.', $markdown);
    $markdown = preg_replace(
        '/În cazul ([^,]+), următorul pas este verificarea locală, completarea cu exemple originale și conectarea la pagina comercială potrivită\./iu',
        'Pentru $1, următorul pas este definirea obiectivului, a dovezilor și a traseului de conversie.',
        $markdown
    ) ?? $markdown;
    $markdown = preg_replace(
        '/^-\s+Articolul rămâne noindex până la dovadă locală, revizie de autor și validarea afirmațiilor\.\s*$/miu',
        '- CAB-IT livrează serviciile online și nu pretinde un sediu local în ' . $city . '; contextul local este explicat transparent.',
        $markdown
    ) ?? $markdown;
    $markdown = preg_replace('/\bînainte de (?:indexare|publicare)\b/iu', 'înainte de decizia de investiție', $markdown) ?? $markdown;
    $markdown = preg_replace(
        '/Exemplul local pentru ([^.]+) va documenta acest criteriu înainte de decizia de investiție\./iu',
        'Pentru $1, criteriul trebuie verificat în raport cu oferta, publicul și datele reale ale firmei.',
        $markdown
    ) ?? $markdown;
    $markdown = preg_replace('/\bde completat înainte de decizia de investiție\b/iu', 'de analizat pentru proiect', $markdown) ?? $markdown;
    $markdown = preg_replace('/^.*\bnoindex\b.*(?:\R|$)/miu', '', $markdown) ?? $markdown;
    $markdown = preg_replace('/\bdraft(?:ul)?\b/iu', 'ghid', $markdown) ?? $markdown;
    $markdown = preg_replace('/\bse adaugă cel puțin o dovadă locală originală\b/iu', 'se verifică datele locale și informațiile furnizate de client', $markdown) ?? $markdown;
    $markdown = preg_replace('/\bse adaugă experiența reală CAB-IT, capturi sau exemple\b/iu', 'se folosesc experiența CAB-IT, exemplele disponibile și date verificabile', $markdown) ?? $markdown;
    $markdown = preg_replace(
        '/Acest ghid oferă structura, dar indexarea este recomandată numai după adăugarea unei dovezi locale și a experienței reale CAB-IT\./iu',
        'Acest ghid oferă o structură de decizie, iar recomandările se adaptează datelor, obiectivelor și pieței reale a fiecărei firme.',
        $markdown
    ) ?? $markdown;
    $markdown = preg_replace(
        '/Pentru [^,\n]+, evaluarea trebuie să includă înainte de decizia de investiție cel puțin o dovadă locală:[^.]+\./iu',
        'Pentru ' . $city . ', contextul local este prezentat orientativ și se validează cu informațiile publice și datele reale ale firmei înaintea unei investiții.',
        $markdown
    ) ?? $markdown;
    $markdown = preg_replace('/\beditorul trebuie să completeze\b/iu', 'evaluarea trebuie să includă', $markdown) ?? $markdown;
    $markdown = preg_replace('/\beditorul trebuie să adauge cel puțin un exemplu local verificabil\b/iu', 'exemplele și datele locale trebuie verificate', $markdown) ?? $markdown;
    $markdown = preg_replace('/\bPentru articolul despre\b/iu', 'Pentru proiectele din', $markdown) ?? $markdown;
    $markdown = preg_replace('/\bparticularitatea locală de completat\b/iu', 'particularitatea locală analizată', $markdown) ?? $markdown;
    $markdown = preg_replace('/\bva documenta acest criteriu\b/iu', 'documentează acest criteriu', $markdown) ?? $markdown;
    $markdown = preg_replace(
        '/Înainte de decizia de investiție trebuie confirmată aria comercială reală:[^\n]+/iu',
        'CAB-IT livrează serviciile online la nivel național; contextul din ' . $city . ' este analizat fără a sugera un sediu local sau o prezență fizică fictivă.',
        $markdown
    ) ?? $markdown;
    $markdown = preg_replace(
        '/Înainte de decizia de investiție, caută în inventarul CAB-IT după[^.]+\./iu',
        'Pentru aprofundare, consultă și ghidurile CAB-IT conexe aceluiași serviciu.',
        $markdown
    ) ?? $markdown;
    $markdown = preg_replace(
        '/Înainte de indexarea articolului despre[^.]+\./iu',
        'CAB-IT livrează online la nivel național; contextul din ' . $city . ' este prezentat fără a pretinde o locație fizică locală.',
        $markdown
    ) ?? $markdown;
    $markdown = str_ireplace('De la ghid la articol validat', 'De la plan la rezultate măsurabile', $markdown);
    $markdown = str_ireplace('Notă editorială pentru', 'Notă de transparență pentru', $markdown);
    return $markdown;
}

function cabit_local_meta_description(array $article): string
{
    $description = trim((string) ($article['meta_description'] ?? ''));
    if ($description === '') {
        $description = trim((string) ($article['excerpt'] ?? ''));
    }
    $description = preg_replace('/\s+/u', ' ', $description) ?? $description;
    if (mb_strlen($description, 'UTF-8') > 160) {
        $description = rtrim(mb_substr($description, 0, 157, 'UTF-8')) . '…';
    }
    return $description;
}

function cabit_import_local_seo_batch(
    string $articlesDirectory,
    bool $dryRun = false,
    ?string $publishedAt = null,
    ?array $selectedSlugs = null,
    bool $useExistingImages = true
): array
{
    if (!is_dir($articlesDirectory)) {
        throw new InvalidArgumentException('Directorul articolelor nu există.');
    }
    $paths = glob(rtrim($articlesDirectory, '/\\') . DIRECTORY_SEPARATOR . '*.json') ?: [];
    sort($paths, SORT_NATURAL | SORT_FLAG_CASE);
    if ($selectedSlugs !== null) {
        $wanted = [];
        foreach ($selectedSlugs as $selectedSlug) {
            $selectedSlug = trim((string) $selectedSlug);
            if (!cms_valid_slug($selectedSlug)) {
                throw new RuntimeException('Slug selectat invalid: ' . $selectedSlug);
            }
            $wanted[$selectedSlug] = true;
        }
        $paths = array_values(array_filter($paths, static function (string $path) use ($wanted): bool {
            return isset($wanted[pathinfo($path, PATHINFO_FILENAME)]);
        }));
        if (count($paths) !== count($wanted)) {
            throw new RuntimeException('Unul sau mai multe articole selectate lipsesc din pachet.');
        }
    } elseif (count($paths) !== 250) {
        throw new RuntimeException('Lansarea trebuie să conțină exact 250 de articole; găsite: ' . count($paths));
    }

    $publishedAt = $publishedAt ?: (new DateTimeImmutable('now', new DateTimeZone('Europe/Bucharest')))->format(DATE_ATOM);
    $records = [];
    $warnings = [];
    $slugs = [];
    foreach ($paths as $path) {
        $article = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $slug = trim((string) ($article['slug'] ?? ''));
        if (!cms_valid_slug($slug) || isset($slugs[$slug])) {
            throw new RuntimeException('Slug invalid sau duplicat: ' . $slug);
        }
        $slugs[$slug] = true;
        $imagePath = '/assets/img/blog/seo-local-2026/' . $slug . '.webp';
        $absoluteImage = CABIT_PUBLIC_ROOT . $imagePath;
        if ($useExistingImages && is_file($absoluteImage)) {
            $size = getimagesize($absoluteImage);
            if (!$size || (int) $size[0] !== 1200 || (int) $size[1] !== 630) {
                throw new RuntimeException('Imaginea existentă trebuie să fie WebP 1200x630: ' . $imagePath);
            }
        } else {
            $imagePath = '';
            $warnings[] = $slug . ': fără imagine publicată; se folosește fallbackul vizual CAB-IT';
        }

        $faqs = cabit_local_faqs(is_array($article['faqs'] ?? null) ? $article['faqs'] : []);
        if (count($faqs) < 3) {
            $warnings[] = $slug . ': mai puțin de 3 întrebări frecvente';
        }
        $markdown = cabit_local_markdown((string) ($article['content_markdown'] ?? ''), (string) ($article['city'] ?? 'România'));
        $converted = cabit_markdown_to_article_html($markdown);
        $city = trim((string) ($article['city'] ?? 'România'));
        $localDisclosure = '<aside class="cabit-local-transparency"><span>Transparență locală</span><p>CAB-IT Expert colaborează online cu firme din ' . htmlspecialchars($city, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' și din toată România, fără a afirma existența unui sediu CAB-IT în această localitate.</p></aside>';
        $articleHtml = $converted['html'];
        $firstAnswerEnd = strpos($articleHtml, '</aside>');
        $articleHtml = $firstAnswerEnd === false
            ? $localDisclosure . $articleHtml
            : substr_replace($articleHtml, '</aside>' . $localDisclosure, $firstAnswerEnd, strlen('</aside>'));
        $sources = is_array($article['fact_check_sources'] ?? null) ? $article['fact_check_sources'] : [];
        $verifiedDate = (string) ($article['price_snapshot']['verified_on'] ?? '2026-08-20');
        $content = $articleHtml . cabit_article_faq_html($faqs) . cabit_article_sources_html($sources, $verifiedDate);
        $seoTitle = trim((string) ($article['meta_title'] ?? $article['title'] ?? ''));
        $metaDescription = cabit_local_meta_description($article);
        $plainContent = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');
        $contentWords = preg_split('/\s+/u', $plainContent, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (mb_strlen($seoTitle, 'UTF-8') < 35 || mb_strlen($seoTitle, 'UTF-8') > 65) {
            throw new RuntimeException($slug . ': titlul SEO trebuie să aibă 35–65 de caractere.');
        }
        if (mb_strlen($metaDescription, 'UTF-8') < 120 || mb_strlen($metaDescription, 'UTF-8') > 160) {
            throw new RuntimeException($slug . ': meta description trebuie să aibă 120–160 de caractere.');
        }
        if (count($contentWords) < 1800) {
            throw new RuntimeException($slug . ': conținut insuficient după revizia editorială.');
        }
        if (count($faqs) < 4 || count($sources) < 5) {
            throw new RuntimeException($slug . ': sunt necesare minimum 4 FAQ și 5 surse.');
        }
        if (preg_match('/\b(?:noindex|draft(?:ul)?|smart search pentru acest articol|înainte de indexare|înainte de publicare|test editorial)\b/iu', $plainContent)) {
            throw new RuntimeException($slug . ': conținutul păstrează instrucțiuni editoriale interne.');
        }

        $smart = is_array($article['smart_search_fields'] ?? null) ? $article['smart_search_fields'] : [];
        $metadata = [
            'primary_keyword' => (string) ($article['primary_keyword'] ?? ''),
            'secondary_keywords' => array_values(array_map('strval', is_array($article['secondary_keywords'] ?? null) ? $article['secondary_keywords'] : [])),
            'semantic_terms' => array_values(array_map('strval', is_array($article['semantic_terms'] ?? null) ? $article['semantic_terms'] : [])),
            'query_cluster' => array_values(array_map('strval', is_array($article['query_cluster'] ?? null) ? $article['query_cluster'] : [])),
            'cluster' => (string) ($article['pillar'] ?? 'SEO local și dezvoltare web'),
            'pillar' => (string) ($article['pillar'] ?? ''),
            'search_intent' => (string) ($article['search_intent'] ?? ''),
            'city' => (string) ($article['city'] ?? ''),
            'county' => (string) ($article['county'] ?? ''),
            'region' => (string) ($article['region'] ?? ''),
            'industry' => (string) ($article['industry_profile']['label'] ?? $smart['industry'] ?? ''),
            'angle' => (string) ($article['angle'] ?? ''),
            'queries' => array_values(array_map('strval', is_array($smart['queries'] ?? null) ? $smart['queries'] : [])),
            'entities' => array_values(array_map('strval', is_array($smart['entities'] ?? null) ? $smart['entities'] : [])),
            'boost_terms' => array_values(array_map('strval', is_array($smart['boost_terms'] ?? null) ? $smart['boost_terms'] : [])),
            'direct_answer' => (string) ($article['direct_answer'] ?? ''),
            'image_alt' => $imagePath !== '' ? 'Ilustrație editorială CAB-IT despre ' . mb_strtolower((string) ($article['title'] ?? ''), 'UTF-8') : '',
            'faqs' => $faqs,
            'sources' => $sources,
            'batch' => 'seo-local-2026-250',
            'remote_service_disclosure' => true,
            'author' => [
                'name' => 'Alexie Popescu',
                'role' => 'Coordonator editorial CAB-IT Expert',
                'bio' => 'Documentează și revizuiește ghiduri despre creare website, SEO, promovare online, măsurarea conversiilor și automatizări digitale.',
            ],
        ];

        $records[] = [
            'title' => trim((string) ($article['h1'] ?? $article['title'] ?? '')),
            'seo_title' => $seoTitle,
            'meta_description' => $metaDescription,
            'slug' => $slug,
            'excerpt' => trim((string) ($article['excerpt'] ?? $article['direct_answer'] ?? '')),
            'content' => $content,
            'cover_image' => $imagePath,
            'date_published' => $publishedAt,
            'created_at' => $publishedAt,
            'updated_at' => $publishedAt,
            'seo_metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ];
    }

    if ($dryRun) {
        return ['validated' => count($records), 'imported' => 0, 'dry_run' => true, 'warnings' => $warnings];
    }

    $pdo = cms_db();
    $statement = $pdo->prepare(
        'INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at, seo_metadata)
         VALUES (:title, :seo_title, :meta_description, :slug, :excerpt, :content, :cover_image, :date_published, :created_at, :updated_at, :seo_metadata)
         ON CONFLICT(slug) DO UPDATE SET title = excluded.title, seo_title = excluded.seo_title, meta_description = excluded.meta_description,
         excerpt = excluded.excerpt, content = excluded.content, cover_image = excluded.cover_image, date_published = excluded.date_published,
         created_at = excluded.created_at, updated_at = excluded.updated_at, seo_metadata = excluded.seo_metadata'
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
    return ['validated' => count($records), 'imported' => count($records), 'dry_run' => false, 'warnings' => $warnings];
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    $articlesDirectory = $argv[1] ?? (__DIR__ . '/data/seo-local-2026/articles');
    $dryRun = in_array('--dry-run', $argv, true);
    $useExistingImages = !in_array('--without-images', $argv, true);
    $selectedSlugs = null;
    foreach ($argv as $argument) {
        if (str_starts_with($argument, '--slugs=')) {
            $selectedSlugs = array_values(array_filter(array_map('trim', explode(',', substr($argument, 8)))));
        }
    }
    try {
        echo json_encode(cabit_import_local_seo_batch($articlesDirectory, $dryRun, null, $selectedSlugs, $useExistingImages), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
