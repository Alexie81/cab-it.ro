<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/import-seo-articles.php';

const CABIT_FINAL_BATCH = 'final-definitiv-ro-2026-500';
const CABIT_FINAL_IMAGE_DIR = 'assets/img/blog/final-definitiv-2026';

function cabit_final_signature(string $text): string
{
    $text = mb_strtolower(strip_tags($text), 'UTF-8');
    $text = strtr($text, ['ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't']);
    return trim((string) preg_replace('/[^a-z0-9]+/u', ' ', $text));
}

function cabit_final_words(string $text): array
{
    $signature = cabit_final_signature($text);
    return $signature === '' ? [] : (preg_split('/\s+/u', $signature, -1, PREG_SPLIT_NO_EMPTY) ?: []);
}

function cabit_final_word_count(string $text): int
{
    return count(cabit_final_words($text));
}

function cabit_final_trim_sentence(string $text, int $limit): string
{
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return rtrim($text, " \t\n\r\0\x0B,;:-") . (preg_match('/[.!?…]$/u', $text) ? '' : '.');
    }
    $text = mb_substr($text, 0, $limit + 1, 'UTF-8');
    $text = preg_replace('/\s+\S*$/u', '', $text) ?? $text;
    return rtrim($text, " \t\n\r\0\x0B,;:-") . '…';
}

function cabit_final_trim_complete_answer(string $text, int $limit): string
{
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return rtrim($text, " \t\n\r\0\x0B,;:-") . (preg_match('/[.!?]$/u', $text) ? '' : '.');
    }
    $candidate = mb_substr($text, 0, $limit, 'UTF-8');
    if (preg_match('/^(.{120,}[.!?])(?:\s|$)/us', $candidate, $match)) {
        return trim($match[1]);
    }
    $candidate = preg_replace('/\s+\S*$/u', '', $candidate) ?? $candidate;
    return rtrim($candidate, " \t\n\r\0\x0B,;:-") . '.';
}

function cabit_final_query_map(string $csvPath): array
{
    if (!is_file($csvPath)) {
        throw new InvalidArgumentException('Harta interogărilor nu există: ' . $csvPath);
    }
    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Harta interogărilor nu poate fi citită.');
    }
    $header = fgetcsv($handle);
    if (!is_array($header)) {
        fclose($handle);
        throw new RuntimeException('Harta interogărilor nu are antet.');
    }
    $header = array_map(static fn(string $value): string => preg_replace('/^\xEF\xBB\xBF/', '', trim($value)) ?? trim($value), $header);
    $rows = [];
    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) !== count($header)) {
            continue;
        }
        $row = array_combine($header, $data);
        $articleId = (int) ($row['article_id'] ?? 0);
        $query = trim((string) ($row['query'] ?? ''));
        if ($articleId > 0 && $query !== '') {
            $rows[$articleId][] = $query;
        }
    }
    fclose($handle);
    foreach ($rows as &$queries) {
        $queries = array_values(array_unique($queries));
    }
    unset($queries);
    return $rows;
}

function cabit_final_boilerplate(array $articles): array
{
    $blocks = [];
    $sentences = [];
    foreach ($articles as $article) {
        $markdown = str_replace(["\r\n", "\r"], "\n", (string) ($article['content_markdown'] ?? ''));
        foreach (preg_split('/\n{2,}/u', $markdown, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $block) {
            if (preg_match('/^#{1,6}\s+/u', trim($block))) {
                continue;
            }
            $signature = cabit_final_signature($block);
            if (cabit_final_word_count($signature) >= 24) {
                $blocks[$signature] = ($blocks[$signature] ?? 0) + 1;
            }
            foreach (preg_split('/(?<=[.!?])\s+/u', preg_replace('/\s+/u', ' ', trim($block)) ?? trim($block), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $sentence) {
                $sentenceSignature = cabit_final_signature($sentence);
                if (cabit_final_word_count($sentenceSignature) >= 18) {
                    $sentences[$sentenceSignature] = ($sentences[$sentenceSignature] ?? 0) + 1;
                }
            }
        }
    }
    return [
        'blocks' => array_filter($blocks, static fn(int $count): bool => $count >= 2),
        'sentences' => array_filter($sentences, static fn(int $count): bool => $count >= 2),
    ];
}

function cabit_final_section_map(string $markdown): array
{
    $markdown = str_replace(["\r\n", "\r"], "\n", trim($markdown));
    $markdown = preg_replace('/^#\s+.+?\n+/u', '', $markdown, 1) ?? $markdown;
    preg_match_all('/^##\s+(.+?)\s*$\n(.*?)(?=^##\s+|\z)/msu', $markdown, $matches, PREG_SET_ORDER);
    $sections = [];
    foreach ($matches as $match) {
        $heading = trim($match[1]);
        $body = trim($match[2]);
        if ($body !== '') {
            $sections[] = ['heading' => $heading, 'body' => $body];
        }
    }
    return $sections;
}

function cabit_final_excluded_heading(string $heading): bool
{
    return preg_match('/^(?:Răspunsul pe scurt|Exemplu ipotetic\b|Întrebări practice\b|Patru situații\b|Întrebări frecvente\b|Surse consultate\b)/iu', trim($heading)) === 1;
}

function cabit_final_paragraph_score(string $paragraph, array $article): int
{
    $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags($paragraph)));
    $words = cabit_final_word_count($plain);
    if ($words < 24 || $words > 190) {
        return -500;
    }
    $score = min(90, $words);
    $specific = array_unique(array_filter(array_merge(
        cabit_final_words((string) ($article['primary_keyword'] ?? '')),
        cabit_final_words((string) ($article['cluster'] ?? '')),
        cabit_final_words((string) ($article['title'] ?? ''))
    ), static fn(string $word): bool => strlen($word) >= 4));
    $signature = ' ' . cabit_final_signature($plain) . ' ';
    foreach ($specific as $term) {
        if (str_contains($signature, ' ' . $term . ' ')) {
            $score += 7;
        }
    }
    if (preg_match('/\b(?:GA4|GTIN|Merchant Center|Search Console|DebugView|WooCommerce|Shopify|Google Ads|XML|CSV|API|SEO)\b/u', $plain)) {
        $score += 35;
    }
    if (preg_match('/\b\d+(?:[.,]\d+)?%?\b/u', $plain)) {
        $score += 12;
    }
    if (preg_match('/^(?:[-*]\s|\d+\.\s)/m', $paragraph)) {
        $score += 28;
    }
    if (preg_match('/(?:În cazul|Pentru tema|La subiectul|În articolul|În scenariul din|Pentru un proiect ipotetic)/iu', $plain)) {
        $score -= 28;
    }
    if (substr_count($plain, '„') >= 2) {
        $score -= 12;
    }
    return $score;
}

function cabit_final_rewrite_opening(string $text, array $article, int $variant): string
{
    $title = preg_quote(trim((string) ($article['title'] ?? '')), '/');
    $keyword = preg_quote(trim((string) ($article['primary_keyword'] ?? '')), '/');
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    $text = preg_replace('/^(?:În cazul|Pentru)\s+[„"](?:' . $keyword . '|' . $title . ')[”"],?\s*/iu', '', $text) ?? $text;
    $text = preg_replace('/^(?:Pentru tema|La subiectul|În articolul)\s+[„"](?:' . $title . '|' . $keyword . ')[”"],?\s*/iu', '', $text) ?? $text;
    $text = preg_replace('/^(?:Într-un exemplu ipotetic|În scenariul)\s+din\s+[\p{L}\s-]+,\s*/iu', '', $text) ?? $text;
    $text = preg_replace('/\b(?:pentru un proiect ipotetic|într-un exemplu ipotetic)\s+din\s+[\p{L}\s-]+\b/iu', 'într-un test controlat', $text) ?? $text;
    $text = preg_replace('/\baplică:\s*(?=[a-zăâîșț])/iu', '', $text) ?? $text;
    $text = preg_replace('/\baceeași simptome\b/iu', 'aceleași simptome', $text) ?? $text;
    if ($text !== '') {
        $text = mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($text, 1, null, 'UTF-8');
    }
    return $text;
}

function cabit_final_clean_paragraph(
    string $paragraph,
    array $article,
    array $boilerplate,
    array &$globalSentences,
    array &$localSentences,
    int $variant
): string {
    $paragraph = trim($paragraph);
    if ($paragraph === '' || preg_match('/^#{1,6}\s+/u', $paragraph)) {
        return '';
    }
    if (preg_match('/^(?:[-*]\s|\d+\.\s)/m', $paragraph)) {
        $lines = [];
        foreach (preg_split('/\R/u', $paragraph, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $line) {
            $line = trim($line);
            $signature = cabit_final_signature($line);
            if ($signature !== '' && !isset($localSentences[$signature])) {
                $localSentences[$signature] = true;
                $lines[] = $line;
            }
        }
        return implode("\n", array_slice($lines, 0, 10));
    }
    $sentences = preg_split('/(?<=[.!?])\s+/u', preg_replace('/\s+/u', ' ', $paragraph) ?? $paragraph, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $kept = [];
    foreach ($sentences as $sentence) {
        $sentence = cabit_final_rewrite_opening($sentence, $article, $variant + count($kept));
        $signature = cabit_final_signature($sentence);
        $wordCount = cabit_final_word_count($signature);
        if ($wordCount < 5 || isset($localSentences[$signature])) {
            continue;
        }
        if ($wordCount >= 18 && (isset($boilerplate['sentences'][$signature]) || isset($globalSentences[$signature]))) {
            continue;
        }
        if (preg_match('/(?:articol ipotetic|proiect ipotetic|scenariul din|memoria echipei)/iu', $sentence)) {
            continue;
        }
        $localSentences[$signature] = true;
        if ($wordCount >= 18) {
            $globalSentences[$signature] = true;
        }
        $kept[] = $sentence;
    }
    return trim(implode(' ', array_slice($kept, 0, 6)));
}

function cabit_final_topic_defaults(array $article): array
{
    $context = mb_strtolower((string) ($article['title'] ?? '') . ' ' . (string) ($article['cluster'] ?? '') . ' ' . (string) ($article['primary_keyword'] ?? ''), 'UTF-8');
    if (str_contains($context, 'sitemap')) {
        return ['URL-urile canonice și indexabile', 'conținutul sitemap-ului', 'trimite sitemap-ul corect și inspectează un eșantion de URL-uri', 'statusul procesării și numărul de pagini indexate', 'URL-uri blocate, duplicate sau redirecționate'];
    }
    if (str_contains($context, 'price competitiveness')) {
        return ['prețul final livrat clientului', 'marja de contribuție pe produs', 'compară produse echivalente și costul total, nu doar prețul afișat', 'conversia și profitul după costurile de achiziție', 'reducerea prețului fără spațiu de profit'];
    }
    if (str_contains($context, 'tva') || str_contains($context, 'marj')) {
        return ['prețul net și prețul cu TVA', 'toate costurile variabile ale comenzii', 'calculează marja de contribuție pe produs și pe comandă', 'profitul după TVA, livrare, retururi și promovare', 'amestecarea valorilor nete cu cele brute'];
    }
    $titleContext = mb_strtolower((string) ($article['title'] ?? '') . ' ' . (string) ($article['primary_keyword'] ?? ''), 'UTF-8');
    if (preg_match('/\b(?:catalog|categorie|stoc|produs nou|alegerea produs|ciclu căutare produs)\b/u', $titleContext)) {
        return ['cererea demonstrabilă', 'marja și viteza de rotație a stocului', 'testează o selecție restrânsă înainte să extinzi catalogul', 'conversia, profitul și produsele fără mișcare', 'extinderea catalogului fără cerere validată'];
    }
    if (str_contains($context, 'merchant') || str_contains($context, 'shopping') || str_contains($context, 'feed')) {
        return ['datele produsului din sursă', 'pagina finală văzută de client', 'corectează o singură familie de erori și retestează produsele afectate', 'produsele eligibile, respingerile și clicurile utile', 'diferențe de preț, stoc sau identificatori'];
    }
    if (preg_match('/\b(?:ga4|analytics|debugview|explorations|event|ecommerce measurement)\b/u', $context)) {
        return ['evenimentul trimis din website', 'parametrii și consimțământul asociat', 'validează evenimentul în DebugView înainte să îl folosești în rapoarte', 'evenimentele unice, conversiile și valoarea atribuită', 'evenimente duplicate sau parametri lipsă'];
    }
    if (str_contains($context, 'search console') || str_contains($context, 'indexare') || str_contains($context, 'crawl')) {
        return ['indexabilitatea URL-ului', 'canonical-ul și răspunsul serverului', 'inspectează URL-ul și corectează mai întâi cauza tehnică', 'clicurile, afișările și paginile indexate pe același interval', 'blocaje robots, canonical greșit sau conținut duplicat'];
    }
    if (preg_match('/\b(?:furnizor|sourcing|importator|distribuitor)\b/u', $context)) {
        return ['calitatea și disponibilitatea produsului', 'condițiile comerciale ale furnizorului', 'testează un lot mic înainte să blochezi capital în stoc', 'marja, retururile și timpul real de aprovizionare', 'dependența de o singură sursă'];
    }
    if (preg_match('/\b(?:catalog|categorie|stoc|produs nou|alegerea produs)\b/u', $context)) {
        return ['cererea demonstrabilă', 'marja și viteza de rotație a stocului', 'testează o selecție restrânsă înainte să extinzi catalogul', 'conversia, profitul și produsele fără mișcare', 'extinderea catalogului fără cerere validată'];
    }
    if (preg_match('/\b(?:conținut produs|continut produs|seo ecommerce|pagină de produs|pagina de produs)\b/u', $context)) {
        return ['intenția căutării', 'informația unică și verificabilă despre produs', 'rescrie pagina pentru decizia clientului, nu pentru densitatea unui cuvânt-cheie', 'clicurile organice, conversia și interogările relevante', 'descrieri duplicate sau afirmații fără dovadă'];
    }
    if (preg_match('/\b(?:lansare|prima vânzare|prima vanzare|magazin nou)\b/u', $context)) {
        return ['funcționarea completă a comenzii', 'măsurarea și mesajele trimise clientului', 'testează traseul de la produs la confirmarea comenzii', 'comenzile valide, rata de finalizare și erorile din checkout', 'promovarea unui flux care nu a fost testat cap-coadă'];
    }
    return ['obiectivul comercial', 'datele și implementarea care îl susțin', 'aplică o singură schimbare și verifică efectul înainte de extindere', 'rezultatul comercial pe același interval', 'modificări simultane care ascund cauza'];
}

function cabit_final_direct_answer(array $article): string
{
    $keyword = trim((string) ($article['primary_keyword'] ?? $article['title'] ?? 'subiectul analizat'));
    [$controlOne, $controlTwo, $action, $metric, $risk] = cabit_final_topic_defaults($article);
    $answer = 'Pentru „' . $keyword . '”, ';
    if ($controlOne !== '') {
        $answer .= 'verifică mai întâi ' . $controlOne;
        $answer .= $controlTwo !== '' ? ' și ' . $controlTwo . '. ' : '. ';
    } else {
        $answer .= 'clarifică obiectivul, starea inițială și criteriul de succes înainte de implementare. ';
    }
    $details = [];
    if ($action !== '') {
        $details[] = 'aplică o singură schimbare — ' . $action;
    }
    if ($metric !== '') {
        $details[] = 'urmărește ' . $metric . ' pe același interval';
    }
    if ($risk !== '') {
        $details[] = 'dacă apare „' . $risk . '”, izolează cauza înainte de alte modificări';
    }
    if (!$details) {
        $details[] = 'modifică o singură variabilă și compară rezultatul cu aceeași stare de referință';
    }
    $answer .= 'În analiza „' . $keyword . '”, ' . implode('; ', $details) . '. Documentează testul și extinde implementarea numai după ce rezultatul poate fi reprodus.';
    return cabit_final_trim_complete_answer($answer, 500);
}

function cabit_final_clean_markdown(array $article, array $boilerplate, array &$globalSentences, array &$globalBlocks): string
{
    $sections = cabit_final_section_map((string) ($article['content_markdown'] ?? ''));
    $selected = [];
    $localSentences = [];
    $variant = abs((int) crc32((string) ($article['slug'] ?? $article['title'] ?? '')));
    $wordCount = 0;

    foreach ($sections as $sectionIndex => $section) {
        $heading = trim((string) $section['heading']);
        if (cabit_final_excluded_heading($heading)) {
            continue;
        }
        $blocks = preg_split('/\n{2,}/u', trim((string) $section['body']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        usort($blocks, static fn(string $left, string $right): int => cabit_final_paragraph_score($right, $article) <=> cabit_final_paragraph_score($left, $article));
        $cleanBlocks = [];
        foreach ($blocks as $blockIndex => $block) {
            $sourceSignature = cabit_final_signature($block);
            if (isset($boilerplate['blocks'][$sourceSignature])) {
                continue;
            }
            if (cabit_final_paragraph_score($block, $article) < 0) {
                continue;
            }
            $cleaned = cabit_final_clean_paragraph($block, $article, $boilerplate, $globalSentences, $localSentences, $variant + $sectionIndex + $blockIndex);
            if ($cleaned === '') {
                continue;
            }
            $cleanedSignature = cabit_final_signature($cleaned);
            if (cabit_final_word_count($cleanedSignature) >= 24 && isset($globalBlocks[$cleanedSignature])) {
                continue;
            }
            if (cabit_final_word_count($cleanedSignature) >= 24) {
                $globalBlocks[$cleanedSignature] = true;
            }
            $cleanBlocks[] = $cleaned;
            if (count($cleanBlocks) >= (preg_match('/Checklist|Plan|Matrice|pași/iu', $heading) ? 2 : 1)) {
                break;
            }
        }
        if (!$cleanBlocks) {
            continue;
        }
        $selected[] = '## ' . $heading . "\n\n" . implode("\n\n", $cleanBlocks);
        $wordCount += cabit_final_word_count(implode(' ', $cleanBlocks));
        if ($wordCount >= 1450 && count($selected) >= 9) {
            break;
        }
    }

    $direct = cabit_final_direct_answer($article);
    $markdown = '> **Răspuns direct:** ' . $direct . "\n\n" . implode("\n\n", $selected);
    return trim($markdown);
}

function cabit_final_faqs(array $article, string $directAnswer): array
{
    $keyword = trim((string) ($article['primary_keyword'] ?? $article['title'] ?? 'acest subiect'));
    $sourceFaqs = is_array($article['faq'] ?? null) ? $article['faq'] : [];
    $answers = [];
    foreach ($sourceFaqs as $faq) {
        $answer = trim((string) ($faq['answer'] ?? $faq['a'] ?? ''));
        $answer = preg_replace('/^Pentru\s+[„"].+?[”"],?\s*/iu', '', $answer) ?? $answer;
        $answer = preg_replace('/\b(?:În [\p{L}\s-]+,|Într-un exemplu ipotetic din [\p{L}\s-]+,)\s*/iu', '', $answer) ?? $answer;
        $answer = preg_replace('/\s+/u', ' ', $answer) ?? $answer;
        if (cabit_final_word_count($answer) >= 12) {
            $answers[] = cabit_final_trim_sentence($answer, 330);
        }
    }
    $fallback = [
        $directAnswer,
        'Verifică aceeași configurație înainte și după schimbare, pe același interval și cu aceleași surse de date. Un rezultat poate fi considerat stabil numai dacă poate fi reprodus.',
        'Cele mai frecvente erori apar când sunt modificate simultan datele, website-ul și promovarea. Separă intervențiile, notează momentul schimbării și verifică sursa fiecărui indicator.',
        'Cere ajutor specializat când accesurile, implementarea sau rapoartele nu pot fi validate intern ori când o eroare afectează indexarea, măsurarea, produsele sau bugetul de promovare.',
    ];
    $answers = array_values(array_replace($fallback, array_slice($answers, 0, 4)));
    return [
        ['q' => 'Care este primul pas pentru ' . $keyword . '?', 'a' => $answers[0]],
        ['q' => 'Cum verifici că ' . $keyword . ' funcționează corect?', 'a' => $answers[1]],
        ['q' => 'Ce greșeli trebuie evitate la ' . $keyword . '?', 'a' => $answers[2]],
        ['q' => 'Când este util ajutorul unei agenții pentru ' . $keyword . '?', 'a' => $answers[3]],
    ];
}

function cabit_final_sources(array $article): array
{
    $sources = [];
    foreach (is_array($article['sources'] ?? null) ? $article['sources'] : [] as $source) {
        $url = trim((string) ($source['url'] ?? ''));
        $title = trim((string) ($source['title'] ?? ''));
        if ($title !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            $sources[] = ['title' => $title, 'url' => $url];
        }
    }
    return array_values(array_unique($sources, SORT_REGULAR));
}

function cabit_final_meta_title(array $article): string
{
    $title = trim((string) ($article['title'] ?? 'Ghid practic'));
    $suffix = ' | CAB-IT';
    $max = 65 - mb_strlen($suffix, 'UTF-8');
    if (mb_strlen($title, 'UTF-8') > $max) {
        $title = mb_substr($title, 0, $max + 1, 'UTF-8');
        $title = preg_replace('/\s+\S*$/u', '', $title) ?? $title;
        $title = rtrim($title, " \t\n\r\0\x0B:;,-–—?!");
    }
    return $title . $suffix;
}

function cabit_final_meta_description(array $article): string
{
    $keyword = trim((string) ($article['primary_keyword'] ?? $article['title'] ?? 'subiectul analizat'));
    $cluster = trim((string) ($article['cluster'] ?? 'marketing digital'));
    $templates = [
        'Ghid practic despre %s: configurare, verificări, greșeli frecvente și pași clari pentru decizii mai bune în %s.',
        'Află cum abordezi %s: ce verifici, ce riscuri eviți și cum măsori rezultatul pentru %s.',
        '%s explicat clar: date necesare, controlul implementării, erori de evitat și recomandări aplicabile pentru %s.',
    ];
    $description = sprintf($templates[abs((int) crc32((string) ($article['slug'] ?? $keyword))) % count($templates)], $keyword, $cluster);
    if (mb_strlen($description, 'UTF-8') < 120) {
        $description .= ' Include checklist, FAQ și surse oficiale.';
    }
    return cabit_final_trim_sentence($description, 158);
}

function cabit_final_tokens(string $text): array
{
    $stop = array_fill_keys(explode(' ', 'acest aceasta aceste pentru despre care cand cum este sunt unui unei din prin fara ghid romania online google'), true);
    return array_values(array_unique(array_filter(cabit_final_words($text), static fn(string $word): bool => strlen($word) >= 4 && !isset($stop[$word]))));
}

function cabit_final_jaccard(string $left, string $right): float
{
    $a = cabit_final_tokens($left);
    $b = cabit_final_tokens($right);
    if (!$a || !$b) {
        return 0.0;
    }
    $intersection = count(array_intersect($a, $b));
    $union = count(array_unique(array_merge($a, $b)));
    return $union > 0 ? $intersection / $union : 0.0;
}

function cabit_final_image_score(array $article): int
{
    $text = mb_strtolower((string) ($article['title'] ?? '') . ' ' . (string) ($article['primary_keyword'] ?? ''), 'UTF-8');
    $score = 0;
    $weights = [
        '/\b(?:audit|checklist|configur|implement|lans|strategie|diagnostic)\w*/u' => 8,
        '/\b(?:cost|profit|marj|vânz|convers|trafic|performan)\w*/u' => 7,
        '/\b(?:eroare|respins|suspend|scădere|pierdere|problem)\w*/u' => 6,
        '/\b(?:Merchant Center|GA4|Search Console|ecommerce|produs|furnizor)\b/iu' => 5,
        '/\b(?:ce este|diferenț|versus|cum alegi|când merită)\b/iu' => 4,
    ];
    foreach ($weights as $pattern => $weight) {
        if (preg_match($pattern, $text)) {
            $score += $weight;
        }
    }
    return $score;
}

function cabit_final_select_images(array $articles): array
{
    $clusters = [];
    foreach ($articles as $article) {
        $clusters[(string) ($article['cluster'] ?? 'Alte ghiduri')][] = $article;
    }
    $selected = [];
    foreach ($clusters as $cluster => $items) {
        usort($items, static fn(array $left, array $right): int => cabit_final_image_score($right) <=> cabit_final_image_score($left) ?: ((int) $left['id'] <=> (int) $right['id']));
        foreach (array_slice($items, 0, 6) as $article) {
            $selected[(int) $article['id']] = true;
        }
    }
    if (count($selected) !== 150) {
        throw new RuntimeException('Selecția vizuală trebuie să conțină exact 150 de articole; rezultat: ' . count($selected));
    }
    return $selected;
}

function cabit_final_image_concept(array $article): string
{
    $title = mb_strtolower((string) ($article['title'] ?? ''), 'UTF-8');
    $cluster = mb_strtolower((string) ($article['cluster'] ?? ''), 'UTF-8');
    if (str_contains($title, 'sitemap')) return 'a clean sitemap node network being inspected by a search crawler lens, with verified and excluded URL branches';
    if (preg_match('/\b(?:gtin|mpn|identificator|cod produs)\b/u', $title)) return 'product packages with barcode tags and a verification scanner separating valid from invalid identifiers';
    if (preg_match('/\b(?:imagine|fotograf|photo)\b/u', $title)) return 'a product photography studio feeding optimized product cards into a digital commerce catalog';
    if (preg_match('/\b(?:livrare|transport|shipping|retur)\b/u', $title)) return 'an ecommerce parcel moving through a mapped delivery and return route with checkpoints';
    if (preg_match('/\b(?:eroare|respins|suspend|problem|diagnostic)\b/u', $title)) return 'a diagnostic station inspecting product data cards, isolating a failed item and confirming repaired items';
    if (preg_match('/\b(?:preț|pret|cost|marj|profit|tva)\b/u', $title)) return 'a precise balance scale comparing a product, price components, margin and profitability charts';
    if (preg_match('/\b(?:feed|xml|csv|api|sincron)\b/u', $title)) return 'a structured product-data pipeline with XML-like blocks flowing from a catalog database into verified shopping cards';
    if (preg_match('/\b(?:debugview|event|eveniment|tracking|tag manager)\b/u', $title)) return 'a real-time event stream passing through validation checkpoints into an analytics dashboard and conversion marker';
    if (preg_match('/\b(?:raport|exploration|analiz|performan|metric)\b/u', $title)) return 'a layered analytics workbench with funnels, segments and trend charts revealing an actionable insight';
    if (preg_match('/\b(?:lead|formular|cerere|crm)\b/u', $title)) return 'a website inquiry moving through a qualification funnel into an organized CRM pipeline';
    if (preg_match('/\b(?:index|crawl|canonical|search console|url inspection)\b/u', $title)) return 'a search crawler scanning a website architecture, highlighting canonical and indexable pages';
    if (preg_match('/\b(?:interog|query|căutare|cautare|keyword)\b/u', $title)) return 'a search lens turning real query signals into ranked product opportunities and a clear growth path';
    if (preg_match('/\b(?:furnizor|sourcing|importator|distribuitor)\b/u', $title)) return 'a supplier selection table with product samples, delivery boxes, quality checks and a risk-versus-margin balance';
    if (preg_match('/\b(?:catalog|categorie|stoc)\b/u', $title)) return 'a modular ecommerce catalog connected to a tidy warehouse inventory, category tree and stock signals';
    if (preg_match('/\b(?:lansare|prima vânzare|prima vanzare|magazin nou)\b/u', $title)) return 'a polished ecommerce storefront launching through a tested checkout funnel toward the first verified order';
    if (str_contains($cluster, 'merchant')) return 'a product catalog flowing from a Romanian ecommerce storefront through validation gates into a modern shopping discovery surface';
    if (str_contains($cluster, 'analytics') || str_contains($cluster, 'ga4')) return 'a privacy-aware analytics system connecting website events, ecommerce actions and a clear decision dashboard';
    if (str_contains($cluster, 'search console')) return 'a technical search visibility map connecting pages, crawl signals, queries and organic performance';
    if (str_contains($cluster, 'produs') || str_contains($cluster, 'ecommerce')) return 'a product research desk combining demand signals, supplier options, catalog decisions and profitable growth';
    return 'an interconnected ecommerce decision system combining verified data, website quality, search visibility and commercial results';
}

function cabit_final_image_prompt(array $article): string
{
    $cluster = trim((string) ($article['cluster'] ?? 'strategie digitală'));
    $title = trim((string) ($article['title'] ?? 'Ghid CAB-IT'));
    return 'Editorial hero illustration for a Romanian digital agency article titled “' . $title . '”, topic: ' . $cluster . '. Show ' . cabit_final_image_concept($article) . '. Make the subject unmistakable and different from the other articles. Premium 3D isometric editorial style, deep navy and CAB-IT teal palette with subtle white accents, crisp studio lighting, sophisticated composition, useful business context, 1200x630 landscape, generous safe margins, no people, no logos, no brand marks, no letters, no words, no UI text, no watermarks.';
}

function cabit_final_manifest(array $articles, array $selected): array
{
    $manifest = [];
    foreach ($articles as $article) {
        $id = (int) ($article['id'] ?? 0);
        if (!isset($selected[$id])) {
            continue;
        }
        $slug = trim((string) $article['slug']);
        $manifest[] = [
            'article_id' => $id,
            'slug' => $slug,
            'title' => trim((string) $article['title']),
            'cluster' => trim((string) $article['cluster']),
            'target' => CABIT_FINAL_IMAGE_DIR . '/' . $slug . '.webp',
            'prompt' => cabit_final_image_prompt($article),
        ];
    }
    return $manifest;
}

function cabit_final_related_articles(array $article, array $idToArticle): array
{
    $related = [];
    foreach (is_array($article['internal_links'] ?? null) ? $article['internal_links'] : [] as $link) {
        $url = trim((string) ($link['url'] ?? ''));
        $anchor = trim((string) ($link['anchor'] ?? 'Ghid conex'));
        if ($url !== '' && $anchor !== '') {
            $related[] = ['label' => 'Resursă CAB-IT', 'title' => $anchor, 'url' => $url];
        }
    }
    $id = (int) ($article['id'] ?? 0);
    foreach ([$id - 1, $id + 1, $id - 2, $id + 2] as $candidateId) {
        if (!isset($idToArticle[$candidateId]) || (string) ($idToArticle[$candidateId]['cluster'] ?? '') !== (string) ($article['cluster'] ?? '')) {
            continue;
        }
        $candidate = $idToArticle[$candidateId];
        $related[] = ['label' => 'Ghid din același subiect', 'title' => (string) $candidate['title'], 'url' => '/blog/' . $candidate['slug'] . '/'];
    }
    $unique = [];
    foreach ($related as $item) {
        $unique[(string) $item['url']] = $item;
    }
    return array_slice(array_values($unique), 0, 5);
}

function cabit_import_final_500(string $jsonPath, string $queryMapPath, string $manifestPath, bool $dryRun = false): array
{
    if (!is_file($jsonPath)) {
        throw new InvalidArgumentException('Fișierul JSON nu există: ' . $jsonPath);
    }
    $decoded = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
    $articles = is_array($decoded['articles'] ?? null) ? $decoded['articles'] : $decoded;
    if (!is_array($articles) || count($articles) !== 500) {
        throw new RuntimeException('Lotul trebuie să conțină exact 500 de articole.');
    }
    $queriesByArticle = cabit_final_query_map($queryMapPath);
    $selectedImages = cabit_final_select_images($articles);
    $manifest = cabit_final_manifest($articles, $selectedImages);
    if ($manifestPath !== '') {
        $manifestDirectory = dirname($manifestPath);
        if (!is_dir($manifestDirectory) && !mkdir($manifestDirectory, 0755, true) && !is_dir($manifestDirectory)) {
            throw new RuntimeException('Directorul manifestului nu poate fi creat.');
        }
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    }

    $boilerplate = cabit_final_boilerplate($articles);
    $pdo = cms_db();
    $existing = $pdo->query('SELECT slug, title, seo_metadata FROM articles')->fetchAll();
    $existingSlugs = [];
    $existingTitles = [];
    $existingKeywords = [];
    foreach ($existing as $row) {
        $metadata = json_decode((string) ($row['seo_metadata'] ?? '{}'), true);
        if (($metadata['batch'] ?? '') === CABIT_FINAL_BATCH) {
            continue;
        }
        $existingSlugs[(string) $row['slug']] = true;
        $existingTitles[] = (string) $row['title'];
        $keyword = cabit_final_signature((string) ($metadata['primary_keyword'] ?? ''));
        if ($keyword !== '') {
            $existingKeywords[$keyword] = true;
        }
    }
    $idToArticle = [];
    foreach ($articles as $article) {
        $idToArticle[(int) $article['id']] = $article;
    }

    $seenSlugs = [];
    $seenTitles = [];
    $seenKeywords = [];
    $globalSentences = [];
    $globalBlocks = [];
    $publishedBlocks = [];
    $publishedSentences = [];
    $publishedBlockExamples = [];
    $publishedSentenceExamples = [];
    $records = [];
    $previews = [];
    $stats = [
        'articles_received' => count($articles),
        'existing_articles' => count($existing),
        'queries_total' => 0,
        'queries_min' => PHP_INT_MAX,
        'queries_max' => 0,
        'min_words' => PHP_INT_MAX,
        'max_words' => 0,
        'total_words' => 0,
        'exact_slug_collisions' => 0,
        'exact_title_collisions' => 0,
        'exact_keyword_collisions' => 0,
        'near_title_collisions' => 0,
        'max_existing_title_similarity' => 0.0,
        'boilerplate_blocks_detected' => count($boilerplate['blocks']),
        'boilerplate_sentences_detected' => count($boilerplate['sentences']),
        'images_selected' => count($manifest),
    ];
    $publishedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Bucharest')))->format(DATE_ATOM);

    foreach ($articles as $index => $article) {
        $id = (int) ($article['id'] ?? 0);
        $slug = trim((string) ($article['slug'] ?? ''));
        $title = trim((string) ($article['h1'] ?? $article['title'] ?? ''));
        $keyword = trim((string) ($article['primary_keyword'] ?? ''));
        $titleSignature = cabit_final_signature($title);
        $keywordSignature = cabit_final_signature($keyword);
        if (!cms_valid_slug($slug) || $id <= 0 || $title === '' || $keyword === '') {
            throw new RuntimeException('Articol incomplet sau cu slug invalid la poziția ' . ($index + 1));
        }
        if (isset($existingSlugs[$slug]) || isset($seenSlugs[$slug])) {
            $stats['exact_slug_collisions']++;
        }
        if (isset($seenTitles[$titleSignature])) {
            $stats['exact_title_collisions']++;
        }
        if (isset($existingKeywords[$keywordSignature]) || isset($seenKeywords[$keywordSignature])) {
            $stats['exact_keyword_collisions']++;
        }
        foreach ($existingTitles as $existingTitle) {
            $similarity = cabit_final_jaccard($title, $existingTitle);
            $stats['max_existing_title_similarity'] = max($stats['max_existing_title_similarity'], $similarity);
            if ($similarity >= 0.9) {
                $stats['near_title_collisions']++;
                break;
            }
        }
        $seenSlugs[$slug] = true;
        $seenTitles[$titleSignature] = true;
        $seenKeywords[$keywordSignature] = true;

        $queries = array_values(array_unique(array_merge(
            array_map('strval', is_array($article['smart_search']['queries'] ?? null) ? $article['smart_search']['queries'] : []),
            $queriesByArticle[$id] ?? []
        )));
        if (count($queries) !== 20) {
            throw new RuntimeException($slug . ': trebuie să aibă exact 20 de interogări, are ' . count($queries));
        }
        $stats['queries_total'] += count($queries);
        $stats['queries_min'] = min($stats['queries_min'], count($queries));
        $stats['queries_max'] = max($stats['queries_max'], count($queries));

        $markdown = cabit_final_clean_markdown($article, $boilerplate, $globalSentences, $globalBlocks);
        $directAnswer = cabit_final_direct_answer($article);
        $faqs = cabit_final_faqs($article, $directAnswer);
        $sources = cabit_final_sources($article);
        $converted = cabit_markdown_to_article_html($markdown);
        $content = $converted['html'] . cabit_article_faq_html($faqs) . cabit_article_sources_html($sources, '2026-08-20');
        $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags($content)));
        $wordCount = cabit_final_word_count($plain);
        if ($wordCount < 400 || $wordCount > 2200 || count($faqs) !== 4 || count($sources) < 4) {
            throw new RuntimeException($slug . ': prag editorial ratat (' . $wordCount . ' cuvinte, ' . count($faqs) . ' FAQ, ' . count($sources) . ' surse).');
        }
        if (preg_match('/(?:proiect ipotetic|scenariul din|toate criteriile de acceptanță|review required|draft-editorial)/iu', $plain)) {
            throw new RuntimeException($slug . ': au rămas formulări artificiale sau instrucțiuni interne.');
        }
        foreach (preg_split('/\n{2,}/u', $markdown, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $block) {
            if (preg_match('/^#{1,6}\s+/u', trim($block))) {
                continue;
            }
            $signature = cabit_final_signature($block);
            if (cabit_final_word_count($signature) >= 24) {
                $publishedBlocks[$signature] = ($publishedBlocks[$signature] ?? 0) + 1;
                $publishedBlockExamples[$signature] ??= trim($block);
            }
            foreach (preg_split('/(?<=[.!?])\s+/u', preg_replace('/\s+/u', ' ', trim($block)) ?? trim($block), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $sentence) {
                $signature = cabit_final_signature($sentence);
                if (cabit_final_word_count($signature) >= 18) {
                    $publishedSentences[$signature] = ($publishedSentences[$signature] ?? 0) + 1;
                    $publishedSentenceExamples[$signature] ??= trim($sentence);
                }
            }
        }

        $stats['min_words'] = min($stats['min_words'], $wordCount);
        $stats['max_words'] = max($stats['max_words'], $wordCount);
        $stats['total_words'] += $wordCount;
        $metaTitle = cabit_final_meta_title($article);
        $metaDescription = cabit_final_meta_description($article);
        if (mb_strlen($metaTitle, 'UTF-8') < 30 || mb_strlen($metaTitle, 'UTF-8') > 65 || mb_strlen($metaDescription, 'UTF-8') < 110 || mb_strlen($metaDescription, 'UTF-8') > 160) {
            throw new RuntimeException($slug . ': metadate în afara limitelor editoriale.');
        }
        $boostTerms = array_values(array_unique(array_merge(
            array_map('strval', is_array($article['smart_search']['boost_terms'] ?? null) ? $article['smart_search']['boost_terms'] : []),
            [$keyword, (string) $article['cluster']]
        )));
        $imageRelative = CABIT_FINAL_IMAGE_DIR . '/' . $slug . '.webp';
        $coverImage = isset($selectedImages[$id]) && is_file(CABIT_PUBLIC_ROOT . '/' . $imageRelative) ? $imageRelative : '';
        $metadata = [
            'primary_keyword' => $keyword,
            'secondary_keywords' => array_values(array_slice(array_unique(array_merge($boostTerms, array_slice($queries, 0, 8))), 0, 16)),
            'semantic_terms' => array_values(array_slice(array_unique(array_merge($boostTerms, cabit_final_tokens($title . ' ' . $article['cluster']))), 0, 24)),
            'boost_terms' => $boostTerms,
            'cluster' => trim((string) $article['cluster']),
            'pillar' => trim((string) $article['cluster']),
            'search_intent' => trim((string) ($article['search_intent'] ?? 'informational')),
            'queries' => $queries,
            'long_tail_queries' => $queries,
            'questions_answered' => array_values(array_map(static fn(array $faq): string => $faq['q'], $faqs)),
            'direct_answer' => $directAnswer,
            'entities' => array_values(array_slice(array_unique(array_merge(['CAB-IT Expert'], array_filter($boostTerms, static fn(string $term): bool => mb_strlen($term, 'UTF-8') >= 3))), 0, 18)),
            'image_alt' => 'Ilustrație pentru ' . $title,
            'faqs' => $faqs,
            'sources' => $sources,
            'related_articles' => cabit_final_related_articles($article, $idToArticle),
            'schema_types' => array_values(array_unique(array_merge(['BlogPosting', 'FAQPage', 'BreadcrumbList'], array_map('strval', is_array($article['schema_types'] ?? null) ? $article['schema_types'] : [])))),
            'batch' => CABIT_FINAL_BATCH,
            'publication_order' => $index + 1,
            'query_map_count' => count($queries),
            'image_selected' => isset($selectedImages[$id]),
            'author' => [
                'name' => 'Alexie Popescu',
                'role' => 'Fondatorul proiectului CAB-IT Expert și coordonator editorial',
                'bio' => 'Documentează și revizuiește ghiduri despre website-uri, SEO, e-commerce, promovare online, analiză și conversii.',
            ],
        ];
        $records[] = [
            'title' => $title,
            'seo_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'slug' => $slug,
            'excerpt' => cabit_final_trim_sentence($directAnswer, 250),
            'content' => $content,
            'cover_image' => $coverImage,
            'date_published' => $publishedAt,
            'created_at' => (new DateTimeImmutable($publishedAt))->modify('-' . $index . ' seconds')->format(DATE_ATOM),
            'updated_at' => $publishedAt,
            'seo_metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ];
        if (in_array($index, [0, 124, 249, 374, 499], true)) {
            $previews[] = [
                'position' => $index + 1,
                'title' => $title,
                'words' => $wordCount,
                'direct_answer' => $directAnswer,
                'headings' => $converted['headings'] ?? [],
                'content_preview' => mb_substr(trim((string) preg_replace('/\s+/u', ' ', strip_tags($converted['html']))), 0, 900, 'UTF-8'),
            ];
        }
    }

    $duplicateBlocks = array_filter($publishedBlocks, static fn(int $count): bool => $count > 1);
    $duplicateSentences = array_filter($publishedSentences, static fn(int $count): bool => $count > 1);
    $stats['duplicate_block_groups_after'] = count($duplicateBlocks);
    $stats['duplicate_sentence_groups_after'] = count($duplicateSentences);
    arsort($duplicateBlocks);
    arsort($duplicateSentences);
    $stats['duplicate_block_examples'] = array_values(array_map(
        static fn(string $signature): array => ['count' => $duplicateBlocks[$signature], 'text' => mb_substr($publishedBlockExamples[$signature], 0, 260, 'UTF-8')],
        array_slice(array_keys($duplicateBlocks), 0, 8)
    ));
    $stats['duplicate_sentence_examples'] = array_values(array_map(
        static fn(string $signature): array => ['count' => $duplicateSentences[$signature], 'text' => mb_substr($publishedSentenceExamples[$signature], 0, 260, 'UTF-8')],
        array_slice(array_keys($duplicateSentences), 0, 8)
    ));
    $stats['average_words'] = round($stats['total_words'] / max(1, count($records)), 1);
    unset($stats['total_words']);
    if ($stats['exact_slug_collisions'] > 0 || $stats['exact_title_collisions'] > 0 || $stats['exact_keyword_collisions'] > 0 || $stats['near_title_collisions'] > 0 || $stats['duplicate_block_groups_after'] > 0 || $stats['duplicate_sentence_groups_after'] > 0 || $stats['queries_total'] !== 10000) {
        throw new RuntimeException('Auditul final nu a trecut: ' . json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    if ($dryRun) {
        return ['validated' => count($records), 'imported' => 0, 'dry_run' => true, 'stats' => $stats, 'previews' => $previews, 'image_manifest' => $manifestPath];
    }

    $statement = $pdo->prepare(
        'INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at, seo_metadata)
         VALUES (:title, :seo_title, :meta_description, :slug, :excerpt, :content, :cover_image, :date_published, :created_at, :updated_at, :seo_metadata)
         ON CONFLICT(slug) DO UPDATE SET
            title = excluded.title,
            seo_title = excluded.seo_title,
            meta_description = excluded.meta_description,
            excerpt = excluded.excerpt,
            content = excluded.content,
            cover_image = excluded.cover_image,
            date_published = excluded.date_published,
            created_at = excluded.created_at,
            updated_at = excluded.updated_at,
            seo_metadata = excluded.seo_metadata'
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
    cms_update_blog_index($pdo);
    cms_update_sitemap($pdo);
    return ['validated' => count($records), 'imported' => count($records), 'dry_run' => false, 'stats' => $stats, 'image_manifest' => $manifestPath];
}

if (PHP_SAPI === 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $jsonPath = $argv[1] ?? '';
    $queryMapPath = $argv[2] ?? '';
    $manifestPath = $argv[3] ?? '';
    $dryRun = in_array('--dry-run', $argv, true);
    try {
        echo json_encode(cabit_import_final_500($jsonPath, $queryMapPath, $manifestPath, $dryRun), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
