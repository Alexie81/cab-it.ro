<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

/**
 * @return array<string, string>
 */
function cabit_ai_database_paths(): array
{
    $storageDirectory = cabit_ai_runtime_storage_directory();
    return [
        'base' => $storageDirectory . '/CAB_IT_CANONICAL_SMARTCHAT_20000.sqlite3',
        'smartchat1m' => $storageDirectory . '/CAB_IT_SMARTCHAT_CANONIC_1M.sqlite3',
        'smartchatwebsite1m' => $storageDirectory . '/CAB_IT_SMARTCHAT_WEBSITE_CANONIC_1M_COMPACT.sqlite3',
        'delta' => $storageDirectory . '/CAB_IT_DELTA_50000_INDUSTRII.sqlite3',
        'delta2' => $storageDirectory . '/CAB_IT_DELTA_50000_INDUSTRII_SET2.sqlite3',
        'delta3' => $storageDirectory . '/CAB_IT_DELTA_1000000_DOMENII.sqlite3',
        'delta4' => $storageDirectory . '/CAB_IT_VIRTUAL_50M.sqlite3',
        'site' => $storageDirectory . '/CAB_IT_SITE_ARTICLES_COMPACT.sqlite3',
        'prospects10b' => $storageDirectory . '/CAB_IT_VIRTUAL_10B_PROSPECTS.sqlite3',
    ];
}

function cabit_ai_smartchat_db(): PDO
{
    $databasePaths = cabit_ai_database_paths();
    $primarySchema = null;
    $primaryPath = null;
    foreach ($databasePaths as $schema => $path) {
        if (is_file($path)) {
            $primarySchema = $schema;
            $primaryPath = $path;
            break;
        }
    }
    if ($primaryPath === null || $primarySchema === null) {
        throw new RuntimeException('Baza SmartChat nu este disponibilă.');
    }
    $pdo = new PDO('sqlite:' . $primaryPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY,
    ]);
    $pdo->exec('PRAGMA query_only = ON');
    $pdo->exec('PRAGMA busy_timeout = 3000');
    $pdo->exec('PRAGMA temp_store = MEMORY');
    return $pdo;
}

function cabit_ai_attach_schema(PDO $pdo, string $schema): bool
{
    if (!preg_match('/^[a-z][a-z0-9_]*$/i', $schema)) {
        return false;
    }
    if (in_array($schema, cabit_ai_database_schemas($pdo), true)) {
        return true;
    }
    $path = cabit_ai_database_paths()[$schema] ?? '';
    if ($path === '' || !is_file($path)) {
        return false;
    }
    $pdo->exec('ATTACH DATABASE ' . $pdo->quote($path) . ' AS ' . $schema);
    return in_array($schema, cabit_ai_database_schemas($pdo), true);
}

/**
 * @return list<string>
 */
function cabit_ai_database_schemas(PDO $pdo): array
{
    $schemas = [];
    foreach ($pdo->query('PRAGMA database_list')->fetchAll() as $row) {
        $schema = (string) ($row['name'] ?? '');
        if ($schema !== 'temp' && preg_match('/^[a-z][a-z0-9_]*$/i', $schema)) {
            $schemas[] = $schema;
        }
    }
    return $schemas;
}

function cabit_ai_schema_has_table(PDO $pdo, string $schema, string $table): bool
{
    if (!preg_match('/^[a-z][a-z0-9_]*$/i', $schema) || !preg_match('/^[a-z][a-z0-9_]*$/i', $table)) {
        return false;
    }
    $statement = $pdo->prepare('SELECT 1 FROM ' . $schema . '.sqlite_master WHERE type IN (\'table\', \'view\') AND name = ? LIMIT 1');
    $statement->execute([$table]);
    return $statement->fetchColumn() !== false;
}

/**
 * @return list<string>
 */
function cabit_ai_table_columns(PDO $pdo, string $schema, string $table): array
{
    if (!preg_match('/^[a-z][a-z0-9_]*$/i', $schema) || !preg_match('/^[a-z][a-z0-9_]*$/i', $table)) {
        return [];
    }
    $columns = [];
    foreach ($pdo->query('PRAGMA ' . $schema . '.table_info(' . $table . ')')->fetchAll() as $row) {
        $name = (string) ($row['name'] ?? '');
        if ($name !== '') {
            $columns[] = $name;
        }
    }
    return $columns;
}

/**
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function cabit_ai_normalize_intent_row(array $row): array
{
    $canonicalAnswer = (string) ($row['canonical_answer_long'] ?? $row['long_answer'] ?? $row['answer'] ?? '');
    $canonicalAnswer = (string) preg_replace(
        [
            '/Relevanța locală este foarte ridicat(?![\p{L}])/u',
            '/Relevanța locală este ridicat(?![\p{L}])/u',
            '/Relevanța locală este mediu(?![\p{L}])/u',
            '/Relevanța locală este scăzut(?![\p{L}])/u',
        ],
        [
            'Relevanța locală este foarte ridicată',
            'Relevanța locală este ridicată',
            'Relevanța locală este medie',
            'Relevanța locală este scăzută',
        ],
        $canonicalAnswer
    );
    $canonicalAnswer = (string) preg_replace('/^(Pentru\s+[^,]+),\s+Pentru\s+/u', '$1, pentru ', $canonicalAnswer);
    $facts = trim((string) ($row['facts'] ?? ''));
    if ($facts === '') {
        $factParts = [];
        foreach (['topic', 'local_relevance', 'compliance_note'] as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '') {
                $factParts[] = $value;
            }
        }
        $facts = implode('; ', $factParts);
    }

    return [
        'intent' => (string) ($row['intent'] ?? ''),
        'category' => (string) ($row['category'] ?? $row['industry_name'] ?? $row['industry'] ?? ''),
        'title' => (string) ($row['title'] ?? ''),
        'canonical_answer_long' => $canonicalAnswer,
        'follow_up' => (string) ($row['follow_up'] ?? $row['followup'] ?? ''),
        'facts' => $facts,
        'response_rule' => (string) ($row['response_rule'] ?? $row['rule'] ?? ''),
        'risk' => (string) ($row['risk'] ?? 'low'),
        'retrieval_strategy' => (string) ($row['retrieval_strategy'] ?? 'industry_canonical'),
        'source_url' => (string) ($row['source_url'] ?? $row['source'] ?? ''),
        'tags' => (string) ($row['tags'] ?? ''),
        'industry_id' => (string) ($row['industry_id'] ?? ''),
        'industry' => (string) ($row['industry_name'] ?? $row['industry'] ?? ''),
        'topic' => (string) ($row['topic'] ?? ''),
        'local_relevance' => (string) ($row['local_relevance'] ?? ''),
        'compliance_note' => (string) ($row['compliance_note'] ?? ''),
    ];
}

/**
 * @return array<string, array<string, mixed>>
 */
function cabit_ai_intent_json_map(): array
{
    static $map = null;
    if (is_array($map)) {
        return $map;
    }
    $map = [];
    $paths = [
        dirname(__DIR__) . '/data/CAB_IT_intents_100_raspunsuri_ample.json',
        dirname(__DIR__) . '/data/intents.json',
        dirname(__DIR__) . '/data/CAB_IT_1000_INTENTII_PE_50_INDUSTRII.json',
        dirname(__DIR__) . '/data/CAB_IT_1000_INTENTII_INDUSTRII_SET2.json',
    ];
    foreach ($paths as $path) {
        $raw = is_file($path) ? file_get_contents($path) : false;
        if ($raw === false) {
            continue;
        }
        try {
            $rows = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            continue;
        }
        if (!is_array($rows)) {
            continue;
        }
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['intent']) || !is_string($row['intent'])) {
                continue;
            }
            $intent = $row['intent'];
            if (!isset($map[$intent])) {
                $map[$intent] = cabit_ai_normalize_intent_row($row);
            }
        }
    }
    $map['company_identity'] = [
        'intent' => 'company_identity',
        'category' => 'Companie',
        'title' => 'Despre CAB-IT Expert',
        'canonical_answer_long' => 'CAB-IT Expert este o agenție digitală din București, operată de CAB IT EXPERT SRL. Construim și îmbunătățim website-uri și magazine online, facem SEO și SEO local, gestionăm Google, Meta și TikTok Ads și implementăm tracking, automatizări și integrări AI. Lucrăm ca partener tehnic: pornim de la obiectivul afacerii, măsurăm ce contează și evităm promisiunile pe care nu le putem controla.',
        'follow_up' => 'Vrei să afli despre servicii, procesul de lucru sau proiectele CAB-IT?',
        'facts' => 'Agenție digitală din București; operator legal CAB IT EXPERT SRL; website, e-commerce, SEO, Ads, tracking, automatizări și AI.',
        'response_rule' => 'Prezintă compania direct. Nu transforma întrebarea despre identitate într-o listă de proiecte.',
        'risk' => 'low',
        'retrieval_strategy' => 'cab_it_official',
        'source_url' => 'https://cab-it.ro/despre-noi/',
        'tags' => 'cab-it,cabit,companie,agentie,digitala,bucuresti',
        'industry_id' => '',
        'industry' => '',
        'topic' => '',
        'local_relevance' => '',
        'compliance_note' => '',
    ];
    return $map;
}

/**
 * @return array<string, mixed>|null
 */
function cabit_ai_fetch_intent(?PDO $pdo, string $intent): ?array
{
    if ($pdo instanceof PDO) {
        foreach (cabit_ai_database_schemas($pdo) as $schema) {
            if (!cabit_ai_schema_has_table($pdo, $schema, 'intents')) {
                continue;
            }
            $statement = $pdo->prepare('SELECT * FROM ' . $schema . '.intents WHERE intent = ? LIMIT 1');
            $statement->execute([$intent]);
            $row = $statement->fetch();
            if (is_array($row)) {
                return cabit_ai_normalize_intent_row($row);
            }
        }
    }
    return cabit_ai_intent_json_map()[$intent] ?? null;
}

/**
 * @return list<string>
 */
function cabit_ai_search_tokens(string $message): array
{
    $message = mb_strtolower($message, 'UTF-8');
    preg_match_all('/[\p{L}\p{N}]{2,32}/u', $message, $matches);
    $stopWords = array_fill_keys([
        'acesta', 'aceasta', 'aceste', 'acestea', 'acest', 'acești', 'aceşti', 'aici', 'ale', 'am', 'are', 'aveți', 'aveti',
        'care', 'cât', 'cat', 'că', 'ca', 'ce', 'cel', 'cea', 'cele', 'cu', 'cum', 'dacă', 'daca', 'dar', 'de', 'despre', 'din',
        'doar', 'este', 'fie', 'fi', 'îmi', 'imi', 'în', 'in', 'la', 'mai', 'mă', 'ma', 'ne', 'nu', 'o', 'pe', 'pentru', 'pot',
        'poate', 'prin', 'să', 'sa', 'sau', 'se', 'și', 'si', 'sunt', 'un', 'una', 'unei', 'unui', 'vă', 'va', 'vreau', 'voi',
        'ul', 'meu', 'mea', 'mei', 'mele', 'tău', 'tau', 'ta', 'trebuie', 'spune', 'spuneti', 'puteti', 'poti', 'ajuta', 'nevoie'],
        true
    );
    $tokens = [];
    foreach (($matches[0] ?? []) as $token) {
        if (isset($stopWords[$token]) || isset($tokens[$token])) {
            continue;
        }
        $tokens[$token] = true;
        if (count($tokens) >= 12) {
            break;
        }
    }
    return array_keys($tokens);
}

function cabit_ai_searchable_text(string $value): string
{
    $value = mb_strtolower($value, 'UTF-8');
    $value = strtr($value, [
        'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
    ]);
    if (function_exists('transliterator_transliterate')) {
        $transliterated = transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC', $value);
        if (is_string($transliterated)) {
            $value = $transliterated;
        }
    }
    return (string) preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
}

/**
 * @return list<array{role:string,content:string}>
 */
function cabit_ai_parse_history(mixed $value): array
{
    if ($value === null) {
        return [];
    }
    if (!is_array($value) || !array_is_list($value) || count($value) > 80) {
        cabit_ai_error(422, 'invalid_history', 'Istoricul conversației nu este valid.');
    }
    $history = [];
    foreach ($value as $index => $item) {
        if (!is_array($item) || array_is_list($item)) {
            cabit_ai_error(422, 'invalid_history_item', 'O replică din istoric nu este validă.');
        }
        $role = $item['role'] ?? null;
        if (!is_string($role) || !in_array($role, ['user', 'assistant'], true)) {
            cabit_ai_error(422, 'invalid_history_role', 'Rolul unei replici din istoric nu este valid.');
        }
        $content = cabit_ai_clean_text($item['content'] ?? null, 'history_' . $index, 1, 2000);
        $history[] = ['role' => $role, 'content' => $content];
    }
    return $history;
}

/**
 * @param list<array{role:string,content:string}> $history
 */
function cabit_ai_previous_user_message(array $history): string
{
    for ($index = count($history) - 1; $index >= 0; $index--) {
        if ($history[$index]['role'] === 'user') {
            return $history[$index]['content'];
        }
    }
    return '';
}

/**
 * @param list<array{role:string,content:string}> $history
 */
function cabit_ai_previous_assistant_message(array $history): string
{
    for ($index = count($history) - 1; $index >= 0; $index--) {
        if ($history[$index]['role'] === 'assistant') {
            return $history[$index]['content'];
        }
    }
    return '';
}

/**
 * @param list<array{role:string,content:string}> $history
 * @return array<string, mixed>
 */
function cabit_ai_understand_conversation(string $message, array $history): array
{
    $subjectPatterns = [
        'website' => '/\b(site|site web|website|landing page|pagina web|web design)\b/u',
        'ecommerce' => '/\b(e commerce|ecommerce|magazin online|catalog|produse|checkout|cos de cumparaturi)\b/u',
        'seo' => '/\b(seo|seo local|search console|indexare|google organic|rezultate organice|cuvinte cheie|google maps|google business|vizibil(?:a|i|itate)?(?:\s+in)?\s+(?:raza|zona|local|localitate|oras|cartier)|raza (?:mea )?locala|clienti (?:locali|din apropiere|din zona))\b/u',
        'paid_ads' => '/\b(google ads|meta ads|facebook ads|instagram ads|tiktok ads|reclame|campanii platite|anunturi)\b/u',
        'tracking_analytics' => '/\b(ga4|google analytics|google tag manager|gtm|tracking|masurare|evenimente)\b/u',
        'conversions' => '/\b(conversie|conversii|optimizare conversii|rata de conversie|cro)\b/u',
        'traffic_acquisition' => '/\b(click|clickuri|clickurile|clic|clicuri|trafic|vizitatori)\b/u',
        'automation_ai' => '/\b(automatizare|automatizari|agent ai|agenti ai|inteligenta artificiala)\b/u',
        'website_audit' => '/\b(audit|analiza website|probleme site|probleme website|erori site)\b/u',
        'pricing' => '/\b(?:pret(?:ul|uri|urile)?|cost(?:ul|uri|urile)?|costa|tarif(?:ul|e|ele)?|buget(?:ul)?|ofert(?:a|e|ele|are))\b/u',
        'portfolio' => '/\b(portofoliu|proiecte|studii de caz)\b/u',
        'contact' => '/\b(contact|telefon|whatsapp|email|mail|patron|proprietar|fondator|administrator|director)\b/u',
    ];
    $objectivePatterns = [
        'phone_calls' => '/\b(apel|apelul|apeluri|telefonic|sunari)\b/u',
        'traffic_clicks' => '/\b(click|clickuri|clickurile|clic|clicuri|trafic|vizitatori)\b/u',
        'forms_leads' => '/\b(formular|formulare|cereri|lead|leaduri)\b/u',
        'sales_orders' => '/\b(comanda|comenzi|vanzare|vanzari|achizitii)\b/u',
        'appointments' => '/\b(programare|programari|rezervare|rezervari)\b/u',
        'google_visibility' => '/\b(vizibilitate|vizibil(?:a|i)?(?:\s+in)?\s+(?:raza|zona|local|localitate|oras|cartier)|raza (?:mea )?locala|clienti (?:locali|din apropiere|din zona)|apar in google|pozitii google|rezultate google|indexare)\b/u',
        'website_creation' => '/\b(vreau un site|vreau website|creare site|creare website|site nou|website nou)\b/u',
        'ecommerce_creation' => '/\b(vreau magazin online|creare magazin online|magazin online nou)\b/u',
    ];

    $subjects = [];
    $objectives = [];
    $domains = [];
    $historicalSubject = '';
    $historicalObjective = '';
    $currentSubjects = [];
    $currentObjectives = [];
    $turns = array_merge($history, [['role' => 'user', 'content' => $message]]);
    foreach ($turns as $turnIndex => $turn) {
        $content = (string) ($turn['content'] ?? '');
        $searchable = cabit_ai_searchable_text($content);
        foreach ($subjectPatterns as $subject => $pattern) {
            if (($turn['role'] ?? '') !== 'user') {
                continue;
            }
            if (preg_match($pattern, $searchable) !== 1) {
                continue;
            }
            $subjects = array_values(array_filter($subjects, static fn (string $value): bool => $value !== $subject));
            $subjects[] = $subject;
            if ($turnIndex < count($history)) {
                $historicalSubject = $subject;
            } else {
                $currentSubjects[] = $subject;
            }
        }
        foreach ($objectivePatterns as $objective => $pattern) {
            if (($turn['role'] ?? '') !== 'user') {
                continue;
            }
            if (preg_match($pattern, $searchable) !== 1) {
                continue;
            }
            $objectives = array_values(array_filter($objectives, static fn (string $value): bool => $value !== $objective));
            $objectives[] = $objective;
            if ($turnIndex < count($history)) {
                $historicalObjective = $objective;
            } else {
                $currentObjectives[] = $objective;
            }
        }
        preg_match_all('/\b(?:https?:\/\/)?(?:www\.)?[a-z0-9][a-z0-9-]*(?:\.[a-z0-9-]+)+(?:\/[^\s]*)?/iu', $content, $domainMatches);
        foreach (($domainMatches[0] ?? []) as $domain) {
            $domain = rtrim((string) $domain, '.,;:!?)]}');
            if ($domain !== '' && !in_array($domain, $domains, true)) {
                $domains[] = $domain;
            }
        }
    }

    $currentSearchable = cabit_ai_searchable_text($message);
    $currentTokenCount = count(preg_split('/\s+/', trim($currentSearchable), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    $previousAssistant = cabit_ai_previous_assistant_message($history);
    $isCorrection = preg_match('/^\s*(?:nu\s*[,;:]|nu asta\b|de fapt\b|adica\b|mai exact\b|ma refer\b|vreau de fapt\b)/iu', $message) === 1;
    $isPlainAnswer = preg_match('/^(?:da|d|ad|sigur|desigur|corect|asa este|nu|nu inca|inca nu|deloc)[\s.!]*$/u', $currentSearchable) === 1;
    $currentSubject = $currentSubjects !== [] ? (string) end($currentSubjects) : '';
    $currentObjective = $currentObjectives !== [] ? (string) end($currentObjectives) : '';

    if ($history === []) {
        $relation = 'new_conversation_turn';
    } elseif ($isCorrection) {
        $relation = 'correction_or_objective_change';
    } elseif ($isPlainAnswer && str_contains($previousAssistant, '?')) {
        $relation = 'answer_to_yes_no_question';
    } elseif (($currentSubject !== '' && $historicalSubject !== '' && $currentSubject !== $historicalSubject)
        || ($currentObjective !== '' && $historicalObjective !== '' && $currentObjective !== $historicalObjective)
    ) {
        $relation = 'objective_or_subject_change';
    } elseif ($currentTokenCount <= 12 && str_contains($previousAssistant, '?')) {
        $relation = 'answer_to_previous_question';
    } elseif (str_contains($message, '?')) {
        $relation = 'new_question_in_context';
    } else {
        $relation = 'conversation_continuation';
    }

    return [
        'turn_count' => count($turns),
        'relation' => $relation,
        'active_subject' => $subjects !== [] ? (string) end($subjects) : '',
        'active_objective' => $objectives !== [] ? (string) end($objectives) : '',
        'subjects_discussed' => $subjects,
        'objectives_discussed' => $objectives,
        'domains_mentioned' => $domains,
        'current_has_explicit_subject' => $currentSubject !== '',
        'current_has_explicit_objective' => $currentObjective !== '',
        'understanding_rule' => 'Înțelege mai întâi ultima replică folosind întreaga conversație activă; abia apoi selectează intenția, sursele și răspunsul.',
    ];
}

/**
 * @param list<array{role:string,content:string}> $history
 */
function cabit_ai_contextual_message(string $message, array $history): string
{
    $previousUser = cabit_ai_previous_user_message($history);
    if ($previousUser === '') {
        return $message;
    }
    $searchable = trim(cabit_ai_searchable_text($message));
    $tokenCount = $searchable === '' ? 0 : count(preg_split('/\s+/', $searchable) ?: []);
    $hasExplicitSubject = preg_match('/\b(e commerce|ecommerce|magazin online|shop online|website|site web|seo|google ads|meta ads|portofoliu|contact|pret|cost)\b/u', $searchable) === 1
        || cabit_ai_is_local_visibility_request($searchable);
    $isContinuation = $tokenCount <= 9 || preg_match('/\b(nu|adica|refer|ma refer|mai exact|limbaj|limbaje|platforma|platforme|acesta|aceasta|asta|acelea)\b/u', $searchable) === 1;
    if (!$isContinuation || $hasExplicitSubject) {
        return $message;
    }
    $contextParts = [$message];
    for ($index = count($history) - 1; $index >= 0; $index--) {
        $content = trim((string) ($history[$index]['content'] ?? ''));
        if ($content !== '') {
            $contextParts[] = mb_substr($content, 0, 900, 'UTF-8');
        }
        if (mb_strlen(implode(' ', $contextParts), 'UTF-8') >= 12000) {
            break;
        }
    }
    return mb_substr(trim(implode(' ', $contextParts)), 0, 12000, 'UTF-8');
}

/**
 * Keep industry and business-profile detection grounded only in what the
 * client actually said, never in a previous generated assistant answer.
 *
 * @param list<array{role:string,content:string}> $history
 */
function cabit_ai_user_context_message(string $message, array $history): string
{
    $contextParts = [$message];
    for ($index = count($history) - 1; $index >= 0; $index--) {
        if (($history[$index]['role'] ?? '') !== 'user') {
            continue;
        }
        $content = trim((string) ($history[$index]['content'] ?? ''));
        if ($content !== '') {
            $contextParts[] = mb_substr($content, 0, 900, 'UTF-8');
        }
        if (mb_strlen(implode(' ', $contextParts), 'UTF-8') >= 12000) {
            break;
        }
    }
    return mb_substr(trim(implode(' ', $contextParts)), 0, 12000, 'UTF-8');
}

function cabit_ai_has_price_signal(string $searchable): bool
{
    return preg_match(
        '/\b(?:cat (?:mai )?(?:costa|ar costa)|pret(?:ul|uri|urile)?|cost(?:ul|uri|urile)?|costa|tarif(?:ul|e|ele)?|buget(?:ul)?|ofert(?:a|e|ele|are))\b/u',
        $searchable
    ) === 1;
}

function cabit_ai_is_local_visibility_request(string $searchable): bool
{
    return preg_match(
        '/\b(?:seo local|google maps|google business|profil(?:ul)? google|vizibilitate locala|vizibil(?:a|i)?(?:\s+in)?\s+(?:raza|zona|local|localitate|oras|cartier)|raza (?:mea )?locala|clienti (?:locali|din apropiere|din zona)|apar(?:a|i)?\s+(?:in google maps|in zona|local))\b/u',
        $searchable
    ) === 1;
}

/**
 * @return array{reference:string, searches:string, conversions:string}
 */
function cabit_ai_local_business_profile(string $userContext): array
{
    $searchable = cabit_ai_searchable_text($userContext);
    $profiles = [
        '/\b(cofetarie|cofetaria|patiserie|patiseria)\b/u' => ['cofetăria ta', '„cofetărie”, „torturi la comandă”, „prăjituri” și produsele căutate în localitatea ta', 'apelurile, indicațiile de orientare, mesajele și comenzile'],
        '/\b(cafenea|cafeneaua|coffee shop)\b/u' => ['cafeneaua ta', '„cafenea”, tipurile de produse și căutările din apropiere', 'apelurile, indicațiile de orientare, rezervările și vizitele'],
        '/\b(restaurant|restaurantul|bistro|bistroul|pizzerie|pizzeria)\b/u' => ['restaurantul tău', 'tipul de bucătărie, meniul și căutările locale cu intenție de rezervare sau comandă', 'rezervările, apelurile, indicațiile de orientare și comenzile'],
        '/\b(cabinet stomatologic|clinica stomatologica|stomatolog|dentist)\b/u' => ['cabinetul tău stomatologic', 'serviciile stomatologice și căutările din localitatea sau cartierele deservite', 'apelurile, programările și cererile de indicații'],
        '/\b(clinica|cabinet medical|medic)\b/u' => ['clinica sau cabinetul tău', 'specialitățile, serviciile medicale și localitatea reală', 'apelurile și programările'],
        '/\b(service auto|atelier auto|mecanic auto)\b/u' => ['service-ul tău auto', 'reparațiile oferite, marca sau problema căutată și zona deservită', 'apelurile, solicitările de programare și indicațiile de orientare'],
        '/\b(salon|salonul|coafor|coaforul|frizerie|frizeria|barbershop)\b/u' => ['salonul tău', 'serviciile oferite și căutările din apropiere', 'apelurile, programările și indicațiile de orientare'],
        '/\b(avocat|cabinet de avocat|firma de avocatura)\b/u' => ['cabinetul tău', 'ariile de practică și localitatea în care oferi consultații', 'apelurile și cererile de consultație'],
        '/\b(contabil|contabilitate|firma de contabilitate)\b/u' => ['firma ta de contabilitate', 'serviciile contabile și zona în care lucrezi', 'apelurile și cererile de ofertă'],
        '/\b(veterinar|clinica veterinara|cabinet veterinar)\b/u' => ['cabinetul tău veterinar', 'serviciile veterinare, urgențele și căutările din apropiere', 'apelurile, programările și indicațiile de orientare'],
        '/\b(pensiune|hotel|cazare)\b/u' => ['unitatea ta de cazare', 'tipul de cazare, facilitățile și destinația', 'apelurile, cererile și rezervările'],
        '/\b(instalator|electrician|reparatii|service)\b/u' => ['afacerea ta de servicii', 'serviciile concrete și localitățile sau zonele acoperite', 'apelurile și cererile de intervenție'],
    ];
    foreach ($profiles as $pattern => $profile) {
        if (preg_match($pattern, $searchable) === 1) {
            return ['reference' => $profile[0], 'searches' => $profile[1], 'conversions' => $profile[2]];
        }
    }
    return [
        'reference' => 'afacerea ta',
        'searches' => 'serviciile sau produsele oferite și localitatea ori zona deservită',
        'conversions' => 'apelurile, mesajele, indicațiile de orientare și cererile reale',
    ];
}

function cabit_ai_locality_from_message(string $message): string
{
    $searchable = cabit_ai_searchable_text($message);
    $knownLocalities = [
        'bucuresti' => 'București',
        'cluj napoca' => 'Cluj-Napoca',
        'timisoara' => 'Timișoara',
        'iasi' => 'Iași',
        'constanta' => 'Constanța',
        'brasov' => 'Brașov',
        'craiova' => 'Craiova',
        'ploiesti' => 'Ploiești',
        'oradea' => 'Oradea',
        'sibiu' => 'Sibiu',
        'pitesti' => 'Pitești',
        'arad' => 'Arad',
    ];
    foreach ($knownLocalities as $searchName => $displayName) {
        if (preg_match('/\b' . preg_quote($searchName, '/') . '\b/u', $searchable) === 1) {
            return $displayName;
        }
    }

    if (preg_match('/\b(?:in|din|pentru)\s+([a-z][a-z -]{1,48}?)(?=\s+(?:si|iar|dar|nu|am|avem)\b|[,.;!?]|$)/u', $searchable, $matches) !== 1) {
        return '';
    }

    $locality = trim((string) ($matches[1] ?? ''));
    if (isset($knownLocalities[$locality])) {
        return $knownLocalities[$locality];
    }

    return mb_convert_case($locality, MB_CASE_TITLE, 'UTF-8');
}

/**
 * @param list<array{role:string,content:string}> $history
 */
function cabit_ai_rule_intent(string $message, array $history): ?string
{
    $current = cabit_ai_searchable_text($message);
    $previousAssistant = cabit_ai_searchable_text(cabit_ai_previous_assistant_message($history));
    $asksAboutCabit = preg_match('/\b(?:cine|ce)\s+(?:mai\s+)?(?:este|e)\s+(?:compania\s+)?(?:cab it|cabit)\b/u', $current) === 1
        || preg_match('/\b(?:despre|prezinta|descrie)\s+(?:compania\s+)?(?:cab it|cabit)\b/u', $current) === 1
        || preg_match('/\bcu\s+ce\s+se\s+ocupa\s+(?:cab it|cabit)\b/u', $current) === 1;
    if ($asksAboutCabit) {
        return 'company_identity';
    }
    $asksForContact = preg_match('/\b(contact[a-z]*|email[a-z]*|e mail|adresa de email|telefon[a-z]*|numar de telefon|sun|suna|sunam|sunati|apel|apelez|apelam|apelati|whatsapp[a-z]*|wapp)\b/u', $current) === 1
        || preg_match('/\b(?:vreau|doresc|as vrea|pot|putem)\s+(?:sa\s+)?(?:vorbesc|vorbim|discut|discutam)\s+cu\s+(?:cineva|o persoana|un consultant|un specialist|un operator|un agent)\b/u', $current) === 1
        || preg_match('/\b(?:vreau|doresc|as vrea|pot|putem)?\s*(?:sa\s+)?(?:iau|iei|ia|luam|luati)\s+legatura\s+cu\s+(?:cineva|o persoana|un consultant|un specialist|un operator|un agent)(?:\s+(?:de la|din)\s+cab\s*it(?:\s+expert)?)?\b/u', $current) === 1
        || preg_match('/\b(?:numar(?:ul)?|telefon(?:ul)?)\s+(?:de\s+)?(?:al\s+)?(?:patronului|proprietarului|fondatorului|administratorului|directorului)\b/u', $current) === 1
        || preg_match('/\b(?:vorbesc|vorbim|discut|discutam|legatura)\s+cu\s+(?:patronul|proprietarul|fondatorul|administratorul|directorul)\b/u', $current) === 1
        || preg_match('/\b(?:pune|puneti|pui)\s+(?:ma\s+)?in legatura\b/u', $current) === 1;
    if ($asksForContact) {
        return 'contact';
    }
    $isNativeAppQuery = preg_match('/\b(react native|aplicatie mobila|aplicatii mobile|android|ios)\b/u', $current) === 1
        && preg_match('/\b(site|website|web)\b/u', $current) !== 1;
    if ($isNativeAppQuery) {
        return 'react_native';
    }
    $mentionsPromotionProblem = preg_match('/\b(promovare|promovarea|reclama|reclame|campanie|campanii|marketing)\b/u', $current) === 1
        && preg_match('/\b(nu (?:imi |mai )?functioneaza|nu (?:imi |mai )?merge|nu (?:imi )?aduce|fara rezultate|slab|slaba|problema|probleme|nu vand|nu am vanzari|zero vanzari)\b/u', $current) === 1;
    if ($mentionsPromotionProblem) {
        return 'ads_no_results';
    }
    $mentionsAds = preg_match('/\b(google ads|meta ads|facebook ads|instagram ads|tiktok ads|reclame platite|campanie de reclame|campanie google|campanii google)\b/u', $current) === 1;
    if ($mentionsAds) {
        if (cabit_ai_has_price_signal($current)) {
            return 'ads_price';
        }
        return 'ads_general';
    }
    if (cabit_ai_is_local_visibility_request($current)) {
        return 'seo_local';
    }
    $mentionsSeo = preg_match('/\b(seo|optimizare seo|promovare organica|rezultate organice|vizibilitate in google)\b/u', $current) === 1;
    if ($mentionsSeo) {
        return 'seo_general';
    }
    $mentionsWebsite = preg_match('/\b(site|site web|website|website de prezentare|pagina web|web design)\b/u', $current) === 1;
    if ($mentionsWebsite) {
        if (cabit_ai_has_price_signal($current)) {
            return 'website_price';
        }
        if (preg_match('/\b(dureaza|durata|termen|cat timp|cand este gata)\b/u', $current) === 1) {
            return 'website_timeline';
        }
        $websiteWordCount = count(preg_split('/\s+/', trim($current)) ?: []);
        if (preg_match('/\bcum abordati\b/u', $current) === 1 || $websiteWordCount > 12) {
            return null;
        }
        return 'website_general';
    }
    $previousUser = cabit_ai_searchable_text(cabit_ai_previous_user_message($history));
    $previousTurnWasPortfolio = preg_match('/\b(arata(?: mi)?|vezi|vreau|proiecte(?:le)?|portofoliu|studii de caz)\b.{0,100}\b(proiecte(?:le)?|portofoliu|studii de caz)\b/u', $previousUser) === 1
        || preg_match('/\b(proiecte(?:le)?|portofoliu|studii de caz)\b/u', $previousAssistant) === 1
            && preg_match('/\b(ify|maison bebe|lael fashion|auto la domiciliu|nanu events|toate studiile de caz|magazin online)\b/u', $previousAssistant) === 1;
    $portfolioFilterTopic = preg_match('/\b(e commerce|ecommerce|magazin online|magazine online|prezentare|seo|promovare)\b/u', $current) === 1;
    $portfolioFilterReply = preg_match('/\b(doar|numai|proiecte(?:le)?|cele|prezentare|seo|promovare)\b/u', $current) === 1
        && count(preg_split('/\s+/', trim($current)) ?: []) <= 9;
    $answersPortfolioFilter = $previousTurnWasPortfolio && $portfolioFilterTopic && $portfolioFilterReply;
    $asksForFilteredPortfolio = preg_match('/\b(proiect|proiecte|portofoliu)\b/u', $current) === 1
        && preg_match('/\b(e commerce|ecommerce|magazine online|prezentare|seo|promovare)\b/u', $current) === 1;
    if ($answersPortfolioFilter || $asksForFilteredPortfolio) {
        return 'website_portfolio';
    }
    if (preg_match('/\b(portofoliu(?:l)?|studii de caz)\b/u', $current) === 1
        || preg_match('/\b(arata(?: mi)?|prezinta|vezi|vreau)\b.{0,80}\b(proiecte(?:le)?|lucrarile)\b/u', $current) === 1
    ) {
        return 'website_portfolio';
    }
    $asksEcommerce = preg_match('/\b(e commerce|ecommerce|magazin online|shop online)\b/u', $current) === 1;
    if ($asksEcommerce) {
        $previousUser = cabit_ai_searchable_text(cabit_ai_previous_user_message($history));
        $continuesPriceQuestion = cabit_ai_has_price_signal($previousUser)
            && preg_match('/\b(dar|magazin online|e commerce|ecommerce|shop online)\b/u', $current) === 1;
        if (cabit_ai_has_price_signal($current) || $continuesPriceQuestion) {
            return 'ecommerce_price';
        }
        if (preg_match('/\b(dureaza|durata|termen|cat timp|cand este gata|livrare proiect)\b/u', $current) === 1) {
            return 'ecommerce_timeline';
        }
        return 'ecommerce_general';
    }

    // A concrete catalogue size is itself a strong e-commerce signal, even
    // when the client omits the words "magazin online" in a short first turn.
    if (preg_match('/\b[0-9]{1,7}\s*(?:de\s*)?(?:produse|articole|sku)\b/u', $current) === 1) {
        return 'ecommerce_general';
    }

    // A short answer containing catalogue, payment or courier details belongs
    // to the e-commerce project when the assistant has just asked for them.
    $continuesEcommerceBrief = preg_match('/\b(cate produse|numar de produse|catalog|magazin online|metode de plata|plata|livrare|curier|comenzi)\b/u', $previousAssistant) === 1
        && preg_match('/\b([0-9]+\s*(?:de\s*)?produse?|produs|produse|catalog|categorie|categorii|varianta|variante|stoc|plata|card|ramburs|livrare|ridicare|curier|sameday|easybox|fan courier|cargus|awb)\b/u', $current) === 1;
    if ($continuesEcommerceBrief) {
        return 'ecommerce_general';
    }

    // A client may replace the courier selected earlier using only the new
    // carrier name (for example, "prefer FAN Courier" after Sameday). Keep
    // that short reply inside the active e-commerce brief instead of sending
    // it through the generic fallback.
    $requestsCourierChange = preg_match('/\b(fan(?:\s+courier)?|sameday|easybox|cargus)\b/u', $current) === 1;
    $recentEcommerceContext = cabit_ai_searchable_text(cabit_ai_previous_user_message($history) . ' ' . $previousAssistant);
    $hasRecentEcommerceContext = preg_match('/\b(e commerce|ecommerce|magazin online|produse|catalog|checkout|curier|sameday|easybox|fan courier|cargus|awb)\b/u', $recentEcommerceContext) === 1;
    if ($requestsCourierChange && $hasRecentEcommerceContext) {
        return 'ecommerce_general';
    }
    $answersCourierContract = preg_match('/\b(ai|aveti|exista).{0,40}\bcontract(?:ul)?(?:\s+activ)?\b|\bcontract(?:ul)?\s+activ\b/u', $previousAssistant) === 1
        && preg_match('/\b(da|nu|am|avem|deja|contract|activ)\b/u', $current) === 1;
    if ($answersCourierContract && $hasRecentEcommerceContext) {
        return 'ecommerce_general';
    }

    $answersSeoWebsiteQuestion = preg_match('/\b(ai deja un website|ai deja un site|care este url|care e url|adresa website|adresa site)\b/u', $previousAssistant) === 1
        && preg_match('/\b(?:https?:\/\/)?(?:www\.)?[a-z0-9][a-z0-9-]*(?:\.[a-z0-9-]+)+(?:\/[^\s]*)?/iu', $message) === 1;
    if ($answersSeoWebsiteQuestion) {
        return 'seo_general';
    }

    $combined = cabit_ai_searchable_text(cabit_ai_previous_user_message($history) . ' ' . $message);
    $technologyTerms = '/\b(limbaj|limbaje|programare|tehnologie|tehnologii|stack|framework|frameworkuri|cms|html|css|javascript|js|vanilla js|php|react|reactjs|nextjs|platforma|platforme|wordpress|woocommerce|shopify|webflow|wix|prestashop|magento)\b/u';
    $asksTechnology = preg_match($technologyTerms, $combined) === 1
        || preg_match('/\b(ce folositi|cu ce faceti|cum construiti|cum dezvoltati)\b/u', $combined) === 1;
    $isWebsiteTopic = preg_match('/\b(site|siteuri|website|websiteuri|web|wordpress|woocommerce|shopify|webflow|wix|prestashop|magento|react|reactjs|nextjs|ecommerce|magazin online)\b/u', $combined) === 1;
    return $asksTechnology && $isWebsiteTopic ? 'website_general' : null;
}

/**
 * @param list<string> $tokens
 * @return array{intent:string, question:string, confidence:float, match_type:string, fragments:list<string>}|null
 */
function cabit_ai_fts_match(PDO $pdo, array $tokens, array $explicitIndustryTokens = []): ?array
{
    if ($tokens === []) {
        return null;
    }
    foreach (['delta', 'delta2', 'delta3'] as $supplementalSchema) {
        cabit_ai_attach_schema($pdo, $supplementalSchema);
    }
    $ftsParts = [];
    foreach ($tokens as $token) {
        $variants = [$token];
        $tokenLength = mb_strlen($token, 'UTF-8');
        if ($tokenLength >= 8) {
            $variants[] = mb_substr($token, 0, $tokenLength - 2, 'UTF-8');
            $variants[] = mb_substr($token, 0, $tokenLength - 3, 'UTF-8');
        }
        foreach (array_unique($variants) as $variant) {
            $ftsParts[] = '"' . str_replace('"', '""', $variant) . '"*';
        }
    }
    $ftsQuery = implode(' OR ', $ftsParts);
    $rows = [];
    foreach (cabit_ai_database_schemas($pdo) as $schema) {
        if (!cabit_ai_schema_has_table($pdo, $schema, 'questions_fts')) {
            continue;
        }
        $columns = cabit_ai_table_columns($pdo, $schema, 'questions_fts');
        $categoryExpression = in_array('category', $columns, true)
            ? 'category'
            : (in_array('industry_name', $columns, true)
                ? 'industry_name AS category'
                : (in_array('industry_id', $columns, true)
                    ? 'COALESCE((SELECT industry_name FROM ' . $schema . '.industries AS industry_lookup WHERE industry_lookup.industry_id = questions_fts.industry_id), questions_fts.industry_id) AS category'
                    : "'' AS category"));
        $tagsExpression = in_array('tags', $columns, true)
            ? 'tags'
            : (in_array('topic', $columns, true) ? 'topic AS tags' : "'' AS tags");
        $weights = in_array('industry_name', $columns, true)
            ? '1.0, 1.8, 2.6, 5.0'
            : (in_array('industry_id', $columns, true) ? '1.0, 5.0, 1.8, 2.6' : '1.0, 5.0, 0.8, 0.6');
        $statement = $pdo->prepare(
            'SELECT question, intent, ' . $categoryExpression . ', ' . $tagsExpression . ', '
            . 'bm25(questions_fts, ' . $weights . ') AS rank_score, ' . $pdo->quote($schema) . ' AS database_schema '
            . 'FROM ' . $schema . '.questions_fts '
            . 'WHERE questions_fts MATCH :query '
            . 'ORDER BY rank_score LIMIT 16'
        );
        $statement->execute([':query' => $ftsQuery]);
        array_push($rows, ...$statement->fetchAll());
    }
    usort($rows, static fn (array $left, array $right): int => ((float) ($left['rank_score'] ?? 0.0)) <=> ((float) ($right['rank_score'] ?? 0.0)));
    $rows = array_slice($rows, 0, 24);
    if ($rows === []) {
        return null;
    }

    $intentWeights = [];
    $firstQuestionByIntent = [];
    $questionsByIntent = [];
    $categoryByIntent = [];
    $totalWeight = 0.0;
    foreach ($rows as $index => $row) {
        $intent = (string) ($row['intent'] ?? '');
        if (!preg_match('/^[a-z0-9_]{2,64}$/', $intent)) {
            continue;
        }
        $weight = 1.0 / (1.0 + ($index * 0.34));
        $intentWeights[$intent] = ($intentWeights[$intent] ?? 0.0) + $weight;
        $totalWeight += $weight;
        if (!isset($firstQuestionByIntent[$intent])) {
            $firstQuestionByIntent[$intent] = (string) ($row['question'] ?? '');
        }
        if (!isset($categoryByIntent[$intent])) {
            $categoryByIntent[$intent] = (string) ($row['category'] ?? '');
        }
        $candidateQuestion = trim((string) ($row['question'] ?? ''));
        if ($candidateQuestion !== '' && !in_array($candidateQuestion, $questionsByIntent[$intent] ?? [], true)) {
            $questionsByIntent[$intent][] = $candidateQuestion;
        }
    }
    if ($intentWeights === []) {
        return null;
    }

    // High-risk commercial requests receive a deterministic boost after the
    // FTS prefilter, so a generic timeline question cannot outrank a price or
    // contact intent merely because it shares more common words.
    $searchTerms = ' ' . cabit_ai_searchable_text(implode(' ', $tokens)) . ' ';
    $hasAny = static function (array $needles) use ($searchTerms): bool {
        foreach ($needles as $needle) {
            if (str_contains($searchTerms, ' ' . $needle . ' ')) {
                return true;
            }
        }
        return false;
    };

    $normalizedQueryTokens = array_values(array_filter(array_map(
        static fn (string $token): string => cabit_ai_searchable_text($token),
        $tokens
    )));
    $normalizedIndustryTokens = array_values(array_filter(array_map(
        static fn (string $token): string => cabit_ai_searchable_text($token),
        $explicitIndustryTokens !== [] ? $explicitIndustryTokens : $tokens
    )));
    $matchedIndustryPrefixes = [];
    foreach ($categoryByIntent as $candidateIntent => $category) {
        $industryTerms = preg_split('/\s+/', cabit_ai_searchable_text($category)) ?: [];
        $industryBoost = 0.0;
        foreach ($normalizedIndustryTokens as $queryToken) {
            foreach ($industryTerms as $industryTerm) {
                if ($queryToken === $industryTerm && mb_strlen($queryToken, 'UTF-8') >= 3) {
                    $industryBoost += 8.0;
                    continue 2;
                }
                if (mb_strlen($queryToken, 'UTF-8') < 5 || mb_strlen($industryTerm, 'UTF-8') < 5) {
                    continue;
                }
                $prefixLength = min(8, mb_strlen($queryToken, 'UTF-8'), mb_strlen($industryTerm, 'UTF-8'));
                if ($prefixLength >= 6 && mb_substr($queryToken, 0, $prefixLength, 'UTF-8') === mb_substr($industryTerm, 0, $prefixLength, 'UTF-8')) {
                    $industryBoost += 6.0;
                    continue 2;
                }
            }
        }
        if ($industryBoost > 0) {
            $intentWeights[$candidateIntent] += $industryBoost;
            $totalWeight += $industryBoost;
            if (preg_match('/^(.+)__[a-z0-9_]+$/', $candidateIntent, $industryIntentMatch) === 1) {
                $industryPrefix = (string) ($industryIntentMatch[1] ?? '');
                if ($industryPrefix !== '') {
                    $matchedIndustryPrefixes[$industryPrefix] = max($industryBoost, (float) ($matchedIndustryPrefixes[$industryPrefix] ?? 0.0));
                }
            }
        }
    }

    $topicSignals = [
        'booking_or_checkout' => ['rezervare', 'rezervari', 'programare', 'programari', 'checkout', 'comanda', 'comenzi'],
        'website_features' => ['functionalitate', 'functionalitati', 'functie', 'functii', 'dotari'],
        'website_structure' => ['structura', 'pagini', 'meniu', 'sectiuni'],
        'mobile_ux' => ['mobil', 'mobile', 'responsive', 'telefon'],
        'conversion_ux' => ['conversie', 'conversii', 'abandoneaza', 'abandoneaza', 'utilizator'],
        'pagespeed' => ['viteza', 'lent', 'lenta', 'pagespeed', 'lighthouse', 'lcp', 'inp', 'cls'],
        'local_seo' => ['seo local', 'local', 'localitate', 'oras', 'bucuresti'],
        'seo_content' => ['continut', 'cuvinte', 'keyword', 'articole', 'seo'],
        'google_ads' => ['google ads', 'adwords'],
        'meta_ads' => ['meta ads', 'facebook', 'instagram'],
        'social_media' => ['social media', 'postari', 'retele'],
        'analytics_tracking' => ['analytics', 'tracking', 'ga4', 'gtm', 'masurare'],
        'google_business' => ['google business', 'profil google', 'harti', 'maps'],
        'reviews_reputation' => ['recenzii', 'review', 'reputatie'],
        'trust_signals' => ['incredere', 'certificari', 'dovezi'],
        'crm_integrations' => ['crm', 'erp', 'api', 'integrare', 'integrari', 'sincronizare'],
        'automation' => ['automatizare', 'automatizari', 'automat'],
        'ai_chatbot' => ['chatbot', 'asistent ai', 'inteligenta artificiala'],
        'budget_priority' => ['buget', 'prioritate', 'investitie'],
        'audit_growth' => ['audit', 'crestere', 'imbunatatire'],
        'offer_presentation' => ['oferta', 'prezentare', 'servicii', 'produse'],
        'pricing_quote_flow' => ['pret', 'preturi', 'oferta', 'cotatie', 'rfq'],
        'lead_qualification' => ['lead', 'leaduri', 'calificare', 'formular'],
        'booking_availability' => ['rezervare', 'rezervari', 'programare', 'programari', 'disponibilitate'],
        'email_followup' => ['email', 'follow up', 'urmarire', 'revenire'],
        'local_search' => ['cautare locala', 'local', 'oras', 'zona'],
        'search_content' => ['continut', 'faq', 'intrebari', 'articole'],
        'google_profile' => ['google business', 'profil google', 'maps', 'harti'],
        'ads_strategy' => ['ads', 'reclame', 'campanie', 'campanii'],
        'creative_strategy' => ['creative', 'creativ', 'banner', 'video', 'mesaj reclame'],
        'compliance_trust' => ['compliance', 'conformitate', 'incredere', 'legal'],
        'review_system' => ['recenzii', 'review', 'reputatie'],
        'mobile_conversion' => ['mobil', 'mobile', 'conversie', 'telefon'],
        'accessibility' => ['accesibilitate', 'wcag', 'contrast', 'cititor ecran'],
        'performance_assets' => ['performanta', 'viteza', 'imagini', 'fonturi'],
        'measurement_model' => ['ga4', 'kpi', 'masurare', 'analytics', 'tracking'],
        'reminders_automation' => ['reminder', 'remindere', 'notificari', 'automatizare'],
        'ai_assistant_scope' => ['asistent ai', 'chatbot', 'agent ai'],
        'systems_integration' => ['integrare', 'integrari', 'crm', 'erp', 'api'],
        'digital_roadmap' => ['roadmap', 'plan digital', 'strategie digitala', 'strategia digitala', 'directie digitala', 'etape', 'prioritati'],
        'ab_testing' => ['a b test', 'ab test', 'testare varianta', 'testare variante'],
        'ai_lead_qualification' => ['calificare lead cu ai', 'calificare automata lead', 'lead scoring ai'],
        'analytics_plan' => ['plan analytics', 'plan masurare', 'ce masuram'],
        'appointment_reminders' => ['reminder programare', 'remindere programari', 'confirmare programare'],
        'auth_roles' => ['roluri utilizatori', 'permisiuni utilizatori', 'autentificare'],
        'backups' => ['backup', 'backupuri', 'copii siguranta'],
        'brand_positioning' => ['pozitionare brand', 'mesaj brand', 'pozitionarea'],
        'caching' => ['cache', 'caching'],
        'call_tracking' => ['call tracking', 'urmarire apeluri', 'masurare apeluri'],
        'chatbot' => ['chatbot website', 'chat bot'],
        'consent_privacy' => ['consimtamant', 'cookies', 'confidentialitate', 'gdpr'],
        'content_strategy' => ['strategie continut', 'plan continut'],
        'crm_pipeline' => ['pipeline crm', 'crm vanzari'],
        'dashboard_reporting' => ['dashboard', 'raportare', 'tablou bord'],
        'deposit_payment' => ['plata avans', 'avans online'],
        'document_upload' => ['incarcare documente', 'upload documente'],
        'e_signature' => ['semnatura electronica', 'semnare electronica'],
        'email_automation' => ['automatizare email', 'email automat'],
        'erp_integration' => ['integrare erp', 'erp'],
        'error_logging' => ['log erori', 'monitorizare erori'],
        'homepage_hero' => ['hero', 'primul ecran', 'prima sectiune'],
        'image_optimization' => ['optimizare imagini', 'imagini website'],
        'information_architecture' => ['structurez site', 'structura site', 'arhitectura informatiei', 'organizare pagini'],
        'internal_linking' => ['linkuri interne', 'internal linking'],
        'inventory_sync' => ['sincronizare stoc', 'stocuri', 'inventar'],
        'knowledge_base' => ['baza cunostinte', 'centru ajutor'],
        'lead_form' => ['formular lead', 'formular contact', 'formular cerere'],
        'lead_routing' => ['distribuire lead', 'asignare lead', 'rutare lead'],
        'local_pages' => ['pagini locale', 'pagini orase', 'pagini localitati'],
        'multilingual' => ['multilingv', 'mai multe limbi', 'traducere website'],
        'online_payments' => ['plati online', 'plata online', 'procesator plata'],
        'pricing_logic' => ['logica pret', 'stabilire pret', 'afisare pret'],
        'quote_calculator' => ['calculator oferta', 'configurator oferta', 'estimare automata'],
        'remarketing' => ['remarketing', 'retargeting'],
        'reputation_management' => ['management reputatie', 'reputatie online'],
        'roadmap_90_days' => ['roadmap 90 zile', 'plan 90 zile', 'primele 90 zile'],
        'schema_markup' => ['schema markup', 'structured data', 'date structurate'],
        'search_console' => ['search console', 'google search console'],
        'search_filter' => ['filtru cautare', 'cautare cu filtre', 'filtrare'],
        'security_headers' => ['security headers', 'headere securitate'],
        'semantic_search' => ['cautare semantica', 'semantic search'],
        'service_taxonomy' => ['categorii servicii', 'taxonomie servicii', 'grupare servicii'],
        'technical_seo_crawl' => ['crawl tehnic', 'audit tehnic seo', 'crawl seo'],
        'uptime_monitoring' => ['monitorizare uptime', 'disponibilitate website'],
        'video_optimization' => ['optimizare video', 'video website'],
        'whatsapp_tracking' => ['tracking whatsapp', 'masurare whatsapp', 'urmarire whatsapp'],
    ];
    foreach ($topicSignals as $topic => $signals) {
        if (!$hasAny($signals)) {
            continue;
        }
        foreach ($matchedIndustryPrefixes as $industryPrefix => $industryBoost) {
            $industryTopicIntent = $industryPrefix . '__' . $topic;
            if (!isset($intentWeights[$industryTopicIntent]) && cabit_ai_fetch_intent($pdo, $industryTopicIntent) !== null) {
                $intentWeights[$industryTopicIntent] = $industryBoost + 4.0;
                $firstQuestionByIntent[$industryTopicIntent] = '';
                $questionsByIntent[$industryTopicIntent] = [];
                $totalWeight += $industryBoost + 4.0;
            }
        }
        foreach (array_keys($intentWeights) as $candidateIntent) {
            if (str_ends_with($candidateIntent, '__' . $topic)) {
                $boost = match ($topic) {
                    'digital_roadmap' => 16.0,
                    'booking_or_checkout' => 14.0,
                    default => 7.0,
                };
                $intentWeights[$candidateIntent] += $boost;
                $totalWeight += $boost;
            }
        }
    }

    // Industry answers are eligible only when the user's text actually names
    // that industry. This prevents a generic objective such as "mai multe
    // apeluri" from inheriting an arbitrary industry from a large corpus.
    foreach (array_keys($intentWeights) as $candidateIntent) {
        if (preg_match('/^(.+)__[a-z0-9_]+$/', $candidateIntent, $industryIntentMatch) !== 1) {
            continue;
        }
        $industryPrefix = (string) ($industryIntentMatch[1] ?? '');
        if ($industryPrefix === '' || !isset($matchedIndustryPrefixes[$industryPrefix])) {
            unset($intentWeights[$candidateIntent], $firstQuestionByIntent[$candidateIntent], $questionsByIntent[$candidateIntent]);
        }
    }
    if ($intentWeights === []) {
        return null;
    }
    $totalWeight = array_sum($intentWeights);

    $commercialIntent = null;
    if ($hasAny(['contact', 'email', 'telefon', 'suna', 'sunati', 'apel', 'whatsapp'])) {
        $commercialIntent = 'contact';
    } elseif ($hasAny(['cost', 'costa', 'pret', 'pretul', 'preturi', 'tarif', 'buget'])) {
        if ($hasAny(['magazin', 'ecommerce', 'shop'])) {
            $commercialIntent = 'ecommerce_price';
        } elseif ($hasAny(['ads', 'reclame', 'google', 'meta', 'tiktok'])) {
            $commercialIntent = 'ads_price';
        } elseif ($hasAny(['website', 'site', 'prezentare', 'pagina', 'pagini'])) {
            $commercialIntent = 'website_price';
        } else {
            $commercialIntent = 'pricing_general';
        }
    } elseif ($hasAny(['portofoliu', 'proiect', 'proiecte', 'proiectul', 'proiectele'])) {
        $commercialIntent = 'website_portfolio';
    }
    if ($commercialIntent !== null) {
        $intentWeights[$commercialIntent] = ($intentWeights[$commercialIntent] ?? 0.0) + max(8.0, $totalWeight * 2.0);
        $firstQuestionByIntent[$commercialIntent] ??= '';
        $totalWeight += max(8.0, $totalWeight * 2.0);
    }

    arsort($intentWeights, SORT_NUMERIC);
    $intent = (string) array_key_first($intentWeights);
    $question = $firstQuestionByIntent[$intent] ?? '';
    $searchableQuestion = cabit_ai_searchable_text($question);
    $matchedTokens = 0;
    foreach ($tokens as $token) {
        if (str_contains($searchableQuestion, cabit_ai_searchable_text($token))) {
            $matchedTokens++;
        }
    }
    $coverage = $tokens === [] ? 0.0 : $matchedTokens / count($tokens);
    $share = $totalWeight > 0 ? ((float) $intentWeights[$intent] / $totalWeight) : 0.0;
    $confidence = 0.5 + (0.24 * $coverage) + (0.2 * $share);
    if (count($tokens) === 1) {
        $confidence = min($confidence, 0.72);
    }
    if ($commercialIntent !== null && $intent === $commercialIntent) {
        $confidence = max($confidence, 0.94);
    }
    return [
        'intent' => $intent,
        'question' => $question,
        'confidence' => round(max(0.45, min(0.93, $confidence)), 2),
        'match_type' => $commercialIntent !== null && $intent === $commercialIntent ? 'fts_commercial_rerank' : 'hybrid_fts',
        'fragments' => array_slice($questionsByIntent[$intent] ?? [], 0, 3),
    ];
}

/**
 * Search the supplemental compact SmartChat corpus. Commercial and explicit
 * conversation rules run before this lookup, so the corpus can add coverage
 * without replacing controlled CAB-IT facts.
 *
 * @return array{intent_row:array<string,mixed>,question:string,confidence:float,match_type:string,fragments:list<string>}|null
 */
function cabit_ai_smartchat_1m_match(PDO $pdo, string $query): ?array
{
    if (!cabit_ai_attach_schema($pdo, 'smartchat1m')
        || !cabit_ai_schema_has_table($pdo, 'smartchat1m', 'canonical_responses')
        || !cabit_ai_schema_has_table($pdo, 'smartchat1m', 'representative_utterances')
    ) {
        return null;
    }

    $normalized = trim(cabit_ai_searchable_text($query));
    if ($normalized === '') {
        return null;
    }

    $exact = $pdo->prepare(
        'SELECT responses.*, utterances.utterance, 1 AS exact_match '
        . 'FROM smartchat1m.representative_utterances AS utterances '
        . 'JOIN smartchat1m.canonical_responses AS responses ON responses.state_id = utterances.state_id '
        . 'WHERE utterances.normalized = :normalized LIMIT 1'
    );
    $exact->execute([':normalized' => $normalized]);
    $row = $exact->fetch();
    $fragments = [];
    $matchType = 'smartchat_1m_exact';
    $confidence = 0.98;

    if (!is_array($row)) {
        if (!cabit_ai_schema_has_table($pdo, 'smartchat1m', 'utterance_fts')) {
            return null;
        }
        $tokens = array_slice(cabit_ai_search_tokens($query), 0, 8);
        if ($tokens === []) {
            return null;
        }
        $ftsParts = [];
        foreach ($tokens as $token) {
            $ftsParts[] = '"' . str_replace('"', '""', $token) . '"*';
        }
        $statement = $pdo->prepare(
            'SELECT responses.*, utterances.utterance, bm25(utterance_fts, 7.0, 0.2) AS rank_score '
            . 'FROM smartchat1m.utterance_fts '
            . 'JOIN smartchat1m.representative_utterances AS utterances ON utterances.id = utterance_fts.rowid '
            . 'JOIN smartchat1m.canonical_responses AS responses ON responses.state_id = utterances.state_id '
            . 'WHERE utterance_fts MATCH :query ORDER BY rank_score ASC LIMIT 12'
        );
        $statement->execute([':query' => implode(' OR ', $ftsParts)]);
        $rows = $statement->fetchAll();
        if ($rows === []) {
            return null;
        }
        $row = $rows[0];
        foreach ($rows as $candidate) {
            $utterance = trim((string) ($candidate['utterance'] ?? ''));
            if ($utterance !== '' && !in_array($utterance, $fragments, true)) {
                $fragments[] = $utterance;
            }
            if (count($fragments) >= 3) {
                break;
            }
        }
        $matchType = 'smartchat_1m_fts';
        $confidence = count($tokens) === 1 ? 0.62 : 0.78;
    } else {
        $utterance = trim((string) ($row['utterance'] ?? ''));
        if ($utterance !== '') {
            $fragments[] = $utterance;
        }
    }

    $answer = trim((string) ($row['response_text'] ?? ''));
    $answerParts = preg_split('/\s*\n{2,}\s*(?:Ca pas următor,|Adaptare la context:)/u', $answer, 2);
    if (is_array($answerParts) && isset($answerParts[0])) {
        $answer = trim((string) $answerParts[0]);
    }
    $intent = trim((string) ($row['intent'] ?? ''));
    if ($answer === '' || !preg_match('/^[a-z0-9_]{2,64}$/', $intent)) {
        return null;
    }

    return [
        'intent_row' => cabit_ai_normalize_intent_row([
            'intent' => $intent,
            'category' => (string) ($row['category'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'canonical_answer_long' => $answer,
            'follow_up' => (string) ($row['follow_up'] ?? ''),
            'facts' => '',
            'response_rule' => 'Folosește răspunsul canonic în contextul conversației fără să înlocuiești Commercial Core.',
            'risk' => (string) ($row['risk'] ?? 'low'),
            'retrieval_strategy' => 'smartchat_1m_canonical',
            'source_url' => (string) ($row['source_url'] ?? ''),
            'tags' => (string) ($row['search_text'] ?? ''),
        ]),
        'question' => (string) ($row['utterance'] ?? ''),
        'confidence' => $confidence,
        'match_type' => $matchType,
        'fragments' => $fragments,
    ];
}

function cabit_ai_clean_website_canonical_answer(string $answer): string
{
    $answer = str_replace('**', '', trim($answer));
    $parts = preg_split('/\R{2,}/u', $answer) ?: [];
    $kept = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '' || preg_match('/^Adaptare la context\s*:/iu', $part) === 1) {
            continue;
        }
        $part = str_replace(
            [
                'CAB-IT pornește',
                'CAB-IT trebuie să răspundă practic și să lege recomandarea de',
                'Pentru acest subiect, ',
                'Nu recomandă funcții',
                'Răspunsul trebuie să fie transparent despre',
                'Pentru prețuri și termene actuale, Commercial Core are prioritate absolută.',
            ],
            [
                'Pornim',
                'Legăm recomandarea de',
                '',
                'Nu recomandăm funcții',
                'Îți explicăm transparent',
                'Prețurile și termenele se verifică în oferta CAB-IT actuală.',
            ],
            $part
        );
        $kept[] = $part;
        if (count($kept) >= 2) {
            break;
        }
    }
    return trim(implode("\n\n", $kept));
}

/**
 * Search the website-only canonical corpus. Controlled prices and contact
 * rules run first, so this source extends website coverage without replacing
 * Commercial Core or the existing general corpus.
 *
 * @return array{intent_row:array<string,mixed>,question:string,confidence:float,match_type:string,fragments:list<string>}|null
 */
function cabit_ai_smartchat_website_1m_match(PDO $pdo, string $query): ?array
{
    $searchable = trim(cabit_ai_searchable_text($query));
    if ($searchable === ''
        || preg_match('/\b(site|siteuri|website|websiteuri|web|pagina web|pagini web|landing page|wordpress|woocommerce|shopify|webflow|wix|prestashop|magento|html|css|javascript|php|apache|mod rewrite|hosting|domeniu|responsive|ux|ui)\b/u', $searchable) !== 1
        || !cabit_ai_attach_schema($pdo, 'smartchatwebsite1m')
        || !cabit_ai_schema_has_table($pdo, 'smartchatwebsite1m', 'runtime_variants')
        || !cabit_ai_schema_has_table($pdo, 'smartchatwebsite1m', 'canonical_responses')
    ) {
        return null;
    }

    $select =
        'SELECT variants.question, responses.intent, responses.context_id, responses.state_id, '
        . 'responses.response_text, responses.risk, intents.title, intents.category '
        . 'FROM smartchatwebsite1m.runtime_variants AS variants '
        . 'JOIN smartchatwebsite1m.canonical_responses AS responses ON responses.response_id = variants.response_id '
        . 'LEFT JOIN smartchatwebsite1m.intents AS intents ON intents.intent = responses.intent ';
    $exact = $pdo->prepare($select . 'WHERE variants.normalized_hash = :normalized_hash LIMIT 1');
    $exact->bindValue(':normalized_hash', substr(hash('sha256', $searchable, true), 0, 16), PDO::PARAM_LOB);
    $exact->execute();
    $row = $exact->fetch();
    $matchType = 'smartchat_website_1m_exact';
    $confidence = 0.98;

    if (!is_array($row)) {
        if (!cabit_ai_schema_has_table($pdo, 'smartchatwebsite1m', 'runtime_fts')) {
            return null;
        }
        $tokens = array_slice(cabit_ai_search_tokens($query), 0, 9);
        if ($tokens === []) {
            return null;
        }
        $genericWebsiteTokens = [
            'site', 'siteuri', 'website', 'websiteuri', 'pagina', 'pagini', 'web', 'cabit',
            'proiect', 'proiectul', 'abordati', 'functioneaza', 'faceti', 'face', 'folositi', 'vreau',
        ];
        $specificTokens = array_values(array_filter(
            $tokens,
            static fn (string $token): bool => !in_array(cabit_ai_searchable_text($token), $genericWebsiteTokens, true)
        ));
        $retrievalTokens = $specificTokens !== [] ? $specificTokens : $tokens;
        $ftsParts = [];
        foreach ($retrievalTokens as $token) {
            $ftsParts[] = '"' . str_replace('"', '""', cabit_ai_searchable_text($token)) . '"*';
        }
        $statement = $pdo->prepare(
            $select
            . 'JOIN smartchatwebsite1m.runtime_fts AS runtime_fts ON runtime_fts.rowid = variants.id '
            . 'WHERE runtime_fts MATCH :query ORDER BY bm25(runtime_fts, 6.0) ASC LIMIT 1'
        );
        $statement->execute([':query' => implode(count($ftsParts) <= 4 ? ' AND ' : ' OR ', $ftsParts)]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            return null;
        }
        $matchedQuestion = cabit_ai_searchable_text((string) ($row['question'] ?? ''));
        $hits = 0;
        foreach ($specificTokens as $token) {
            if (str_contains($matchedQuestion, cabit_ai_searchable_text($token))) {
                $hits++;
            }
        }
        if ($specificTokens !== [] && $hits === 0) {
            return null;
        }
        $matchType = 'smartchat_website_1m_fts';
        $confidence = count($specificTokens) <= 1 ? 0.72 : min(0.9, 0.72 + (0.06 * $hits));
    }

    $answer = cabit_ai_clean_website_canonical_answer((string) ($row['response_text'] ?? ''));
    $intent = trim((string) ($row['intent'] ?? ''));
    if ($answer === '' || !preg_match('/^[a-z0-9_]{2,80}$/', $intent)) {
        return null;
    }

    return [
        'intent_row' => cabit_ai_normalize_intent_row([
            'intent' => $intent,
            'category' => (string) ($row['category'] ?? 'Website'),
            'title' => (string) ($row['title'] ?? 'Website CAB-IT'),
            'canonical_answer_long' => $answer,
            'follow_up' => '',
            'response_rule' => 'Răspunde direct și natural pe baza canonului website, fără să repeți datele deja oferite de client.',
            'risk' => (string) ($row['risk'] ?? 'low'),
            'retrieval_strategy' => 'smartchat_website_1m_canonical',
            'source_url' => 'https://cab-it.ro/servicii/creare-site-web/',
            'tags' => (string) (($row['context_id'] ?? '') . ' ' . ($row['state_id'] ?? '')),
        ]),
        'question' => (string) ($row['question'] ?? ''),
        'confidence' => $confidence,
        'match_type' => $matchType,
        'fragments' => array_values(array_filter([(string) ($row['question'] ?? '')])),
    ];
}

/**
 * Resolve the virtual 50M source without materializing its question variants.
 * Runtime lookup is industry -> intent -> answer_ref over 25,000 canonical rows.
 *
 * @return array{intent_row:array<string,mixed>,question:string,confidence:float,match_type:string,fragments:list<string>}|null
 */
function cabit_ai_virtual_50m_match(PDO $pdo, string $query): ?array
{
    if (!cabit_ai_attach_schema($pdo, 'delta4')
        || !cabit_ai_schema_has_table($pdo, 'delta4', 'industries')
        || !cabit_ai_schema_has_table($pdo, 'delta4', 'canonical_fts')
    ) {
        return null;
    }
    $searchableQuery = trim(cabit_ai_searchable_text($query));
    if ($searchableQuery === '') {
        return null;
    }
    $queryTokens = preg_split('/\s+/', $searchableQuery) ?: [];
    $genericIndustryWords = array_fill_keys(['firma', 'firme', 'servicii', 'service', 'magazin', 'clinica', 'cabinet', 'companie', 'producator', 'centru'], true);
    $bestIndustry = null;
    $bestScore = 0.0;
    foreach ($pdo->query('SELECT industry_id, industry_name FROM delta4.industries')->fetchAll() as $industry) {
        $industryName = trim(cabit_ai_searchable_text((string) ($industry['industry_name'] ?? '')));
        if ($industryName === '') {
            continue;
        }
        $score = str_contains(' ' . $searchableQuery . ' ', ' ' . $industryName . ' ') ? 30.0 : 0.0;
        $industryTokens = preg_split('/\s+/', $industryName) ?: [];
        $distinctiveTerms = 0;
        $matchedDistinctiveTerms = 0;
        foreach ($industryTokens as $industryToken) {
            if (mb_strlen($industryToken, 'UTF-8') < 4) {
                continue;
            }
            $isGenericIndustryTerm = isset($genericIndustryWords[$industryToken]);
            if (!$isGenericIndustryTerm) {
                $distinctiveTerms++;
            }
            $termMatched = false;
            foreach ($queryTokens as $queryToken) {
                if (mb_strlen($queryToken, 'UTF-8') < 4 || mb_strlen($industryToken, 'UTF-8') < 4) {
                    continue;
                }
                $isExact = $queryToken === $industryToken;
                $prefixLength = min(8, mb_strlen($queryToken, 'UTF-8'), mb_strlen($industryToken, 'UTF-8'));
                $isPrefix = $prefixLength >= 6
                    && mb_substr($queryToken, 0, $prefixLength, 'UTF-8') === mb_substr($industryToken, 0, $prefixLength, 'UTF-8');
                if ($isExact || $isPrefix) {
                    $score += $isGenericIndustryTerm ? 1.0 : 8.0;
                    $termMatched = true;
                    break;
                }
            }
            if ($termMatched && !$isGenericIndustryTerm) {
                $matchedDistinctiveTerms++;
            }
        }
        if ($score < 30.0 && $distinctiveTerms > 1 && $matchedDistinctiveTerms < $distinctiveTerms) {
            $score = 0.0;
        }
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestIndustry = $industry;
        }
    }
    if (!is_array($bestIndustry) || $bestScore < 8.0) {
        return null;
    }

    $tokens = cabit_ai_search_tokens($query);
    if ($tokens === []) {
        return null;
    }
    $ftsParts = [];
    foreach ($tokens as $token) {
        $token = cabit_ai_searchable_text($token);
        if ($token !== '') {
            $ftsParts[] = '"' . str_replace('"', '""', $token) . '"*';
        }
    }
    if ($ftsParts === []) {
        return null;
    }
    $statement = $pdo->prepare(
        'SELECT answers.*, bm25(canonical_fts, 0.0, 1.0, 8.0, 1.5, 0.35) AS rank_score '
        . 'FROM delta4.canonical_fts '
        . 'JOIN delta4.canonical_answers AS answers ON answers.rowid = canonical_fts.rowid '
        . 'WHERE canonical_fts MATCH :query AND answers.industry_id = :industry_id '
        . 'ORDER BY rank_score LIMIT 1'
    );
    $statement->execute([
        ':query' => implode(' OR ', $ftsParts),
        ':industry_id' => (string) ($bestIndustry['industry_id'] ?? ''),
    ]);
    $row = $statement->fetch();
    if (!is_array($row)) {
        return null;
    }
    $answer = str_replace('**', '', (string) ($row['canonical_answer_long'] ?? ''));
    $answer = (string) preg_replace(
        '/\R{2}Într-o implementare CAB-IT aș separa partea deterministă.*?(?=\R{2}Pentru această industrie)/us',
        '',
        $answer
    );
    $answer = (string) preg_replace(
        '/\R{2}În implementarea CAB-IT aș porni de la un flux clar și măsurabil\..*?(?=\R{2}Nu aș introduce funcția|\R{2}Pentru această industrie)/us',
        '',
        $answer
    );
    $answer = (string) preg_replace(
        '/\R{2}Nu aș introduce funcția doar pentru că este modernă\..*?(?=\R{2}Pentru această industrie)/us',
        '',
        $answer
    );
    $intentRow = cabit_ai_normalize_intent_row([
        'intent' => (string) ($row['answer_ref'] ?? ''),
        'industry_id' => (string) ($row['industry_id'] ?? ''),
        'industry_name' => (string) ($row['industry_name'] ?? ''),
        'topic' => (string) ($row['intent'] ?? ''),
        'title' => (string) ($row['intent_title'] ?? ''),
        'category' => (string) ($row['category'] ?? ''),
        'canonical_answer_long' => $answer,
        'source_url' => (string) ($row['source_url'] ?? 'https://cab-it.ro/servicii/'),
        'retrieval_strategy' => 'virtual_50m_answer_ref',
        'response_rule' => 'Commercial Core CAB-IT are prioritate. Adaptează răspunsul la întrebare și nu inventa date lipsă.',
    ]);
    return [
        'intent_row' => $intentRow,
        'question' => $query,
        'confidence' => min(0.95, 0.72 + ($bestScore / 100)),
        'match_type' => 'virtual_50m_answer_ref',
        'fragments' => [$query],
    ];
}

/**
 * @return array<string, mixed>|null
 */
function cabit_ai_virtual_10b_prospect_match(PDO $pdo, string $profileQuery, string $currentMessage, ?string $ruleIntent = null): ?array
{
    $profileSearchable = trim(cabit_ai_searchable_text($profileQuery));
    $currentSearchable = trim(cabit_ai_searchable_text($currentMessage));
    if ($profileSearchable === '' || $currentSearchable === '') {
        return null;
    }

    $statePatterns = [
        'past_bad_experience' => '/\b(experienta proasta|am fost dezamagit|am lucrat cu alta agentie|agentie anterioara|am pierdut bani)\b/u',
        'comparing_agencies' => '/\b(compar agentii|compar mai multe agentii|alte agentii|aleg agentia|oferte de la agentii)\b/u',
        'exploring_first_site' => '/\b(nu (?:am|avem|are) (?:un )?(?:site|website)|primul (?:meu )?(?:site|website)|fara (?:site|website))\b/u',
        'old_site_rebuild' => '/\b(site vechi|website vechi|refac(?:em|ut)? site|redesign|site depasit)\b/u',
        'site_slow' => '/\b(site ul? (?:este|e) lent|website ul? (?:este|e) lent|se incarca greu|viteza slaba)\b/u',
        'site_no_tracking' => '/\b(fara tracking|nu (?:am|avem) tracking|nu (?:am|avem) analytics|nu masor|nu stim de unde vin)\b/u',
        'needs_more_leads' => '/(?:\b(?:am trafic dar|trafic fara|vizite fara).{0,35}\b(?:leaduri|cereri|clienti|vanzari)\b|\b(?:mai multe (?:leaduri|cereri|solicitari|clienti|apeluri|programari)|vreau leaduri|am nevoie de clienti)\b)/u',
        'social_only' => '/\b(doar (?:facebook|instagram|tiktok|social media)|numai (?:facebook|instagram|social media)|ma bazez pe social)\b/u',
        'google_profile_only' => '/\b(doar google business|numai google business|am profil google dar|google business fara)\b/u',
        'booking_problem' => '/\b(probleme cu programarile|programari pierdute|programari manuale|sistem de programari)\b/u',
        'quote_problem' => '/\b(cereri de oferta|ofertare|oferte manuale|solicitari de oferta)\b/u',
        'mobile_problem' => '/\b(nu merge pe telefon|probleme pe mobil|site ul? mobil|website ul? mobil)\b/u',
        'multi_location_growth' => '/\b(mai multe locatii|multiple locatii|mai multe sedii|extindere in mai multe orase)\b/u',
        'local_growth' => '/\b(seo local|clienti locali|clienti din apropiere|clienti din zona|mai vizibil local|vizibil in raza|vizibil in zona|raza mea locala|in orasul|in localitatea)\b/u',
        'small_budget_careful' => '/\b(buget mic|buget limitat|investitie mica|costuri atent|nu vreau sa risipesc)\b/u',
        'ready_to_scale' => '/\b(vreau sa scalez|gata de scalare|crestem rapid|extind afacerea|scalam)\b/u',
        'seo_curiosity' => '/\b(seo|rezultate organice|vizibilitate in google)\b/u',
        'ads_curiosity' => '/\b(google ads|meta ads|reclame platite|campanie de reclame)\b/u',
        'automation_curiosity' => '/\b(automatizare|automatizari|agent ai|chatbot ai)\b/u',
        'curious_about_cabit' => '/\b(cine (?:este|sunteti) cab it|ce face cab it|despre cab it|cum ma poate ajuta cab it)\b/u',
    ];
    $stateId = '';
    foreach ($statePatterns as $candidateState => $pattern) {
        if (preg_match($pattern, $profileSearchable) === 1) {
            $stateId = $candidateState;
            break;
        }
    }
    if ($stateId === '') {
        return null;
    }
    if (!cabit_ai_attach_schema($pdo, 'prospects10b')
        || !cabit_ai_schema_has_table($pdo, 'prospects10b', 'prospect_scenarios')
        || !cabit_ai_schema_has_table($pdo, 'prospects10b', 'canonical_answers')
    ) {
        return null;
    }

    static $businessTypes = null;
    if (!is_array($businessTypes)) {
        $businessTypes = $pdo->query('SELECT DISTINCT business_type FROM prospects10b.prospect_scenarios ORDER BY business_type')->fetchAll(PDO::FETCH_COLUMN);
    }
    $genericBusinessTokens = array_fill_keys(['firma', 'firme', 'service', 'servicii', 'centru', 'studio', 'agentie', 'magazin', 'producator', 'distribuitor', 'scoala', 'atelier', 'birou', 'cabinet', 'spatiu', 'operator'], true);
    $bestBusiness = '';
    $bestBusinessScore = 0.0;
    foreach ($businessTypes as $businessTypeValue) {
        $businessType = (string) $businessTypeValue;
        $businessSearchable = trim(cabit_ai_searchable_text($businessType));
        if ($businessSearchable === '') {
            continue;
        }
        $exact = str_contains($profileSearchable, $businessSearchable);
        $rawTokens = preg_split('/\s+/', $businessSearchable) ?: [];
        $distinctiveTokens = [];
        foreach ($rawTokens as $token) {
            if (mb_strlen($token, 'UTF-8') < 2 || isset($genericBusinessTokens[$token]) || in_array($token, ['de', 'si', 'pentru'], true)) {
                continue;
            }
            $distinctiveTokens[$token] = true;
        }
        $distinctiveTokens = array_keys($distinctiveTokens);
        $hits = 0;
        foreach ($distinctiveTokens as $token) {
            if (preg_match('/\b' . preg_quote($token, '/') . '\b/u', $profileSearchable) === 1) {
                $hits++;
            }
        }
        $requiredHits = count($distinctiveTokens) === 1 ? 1 : 2;
        if (!$exact && ($hits < $requiredHits || $distinctiveTokens === [])) {
            continue;
        }
        $coverage = $hits / max(1, count($distinctiveTokens));
        $score = ($exact ? 2.0 : 0.0) + $coverage + ($hits * 0.2);
        if ($score > $bestBusinessScore) {
            $bestBusinessScore = $score;
            $bestBusiness = $businessType;
        }
    }
    if ($bestBusiness === '') {
        return null;
    }

    $intentMap = [
        'website_general' => 'website_need', 'website_price' => 'website_price', 'website_custom' => 'website_custom',
        'website_portfolio' => 'portfolio', 'website_timeline' => 'website_timeline', 'website_audit' => 'website_audit_before',
        'seo_general' => 'seo_need', 'seo_not_google' => 'seo_technical', 'seo_local' => 'seo_local',
        'ads_general' => 'ads_need', 'ads_price' => 'ads_price', 'ads_tracking' => 'ads_tracking', 'ads_no_results' => 'ads_no_results',
        'ecommerce_general' => 'shop_need', 'ecommerce_price' => 'shop_price', 'ecommerce_products' => 'shop_products',
        'conversion_tracking' => 'conversion_definition', 'cro_general' => 'cro', 'automation_general' => 'automation_what',
    ];
    $intentId = $ruleIntent !== null ? (string) ($intentMap[$ruleIntent] ?? '') : '';
    if ($intentId === '') {
        $intentId = match (true) {
            preg_match('/\b(cum (?:ma|ne) (?:puteti|poti) ajuta|cu ce (?:ma|ne) (?:puteti|poti) ajuta|care (?:este|e) primul pas|de unde incep)\b/u', $currentSearchable) === 1
                => 'first_step',
            preg_match('/\b(?:site|website|pagina web)\b/u', $currentSearchable) === 1
                && cabit_ai_has_price_signal($currentSearchable)
                => 'website_price',
            preg_match('/\b(?:site|website|pagina web)\b/u', $currentSearchable) === 1
                && preg_match('/\b(?:cat dureaza|durata|termen|cand (?:este|e) gata)\b/u', $currentSearchable) === 1
                => 'website_timeline',
            preg_match('/\b(?:site|website|pagina web)\b/u', $currentSearchable) === 1
                => 'website_need',
            preg_match('/\b(?:seo local|google maps|google business)\b/u', $currentSearchable) === 1
                => 'seo_local',
            preg_match('/\b(?:seo|optimizare (?:in|pentru) google|rezultate organice|vizibilitate in google)\b/u', $currentSearchable) === 1
                => 'seo_need',
            preg_match('/\b(?:google ads|meta ads|facebook ads|reclame platite|campanie de reclame)\b/u', $currentSearchable) === 1
                && cabit_ai_has_price_signal($currentSearchable)
                => 'ads_price',
            preg_match('/\b(?:google ads|meta ads|facebook ads|reclame platite|campanie de reclame)\b/u', $currentSearchable) === 1
                => 'ads_need',
            preg_match('/\b(?:magazin online|ecommerce|e commerce)\b/u', $currentSearchable) === 1
                && cabit_ai_has_price_signal($currentSearchable)
                => 'shop_price',
            preg_match('/\b(?:magazin online|ecommerce|e commerce)\b/u', $currentSearchable) === 1
                => 'shop_need',
            preg_match('/\b(?:automatizare|automatizari|agent ai|chatbot ai)\b/u', $currentSearchable) === 1
                => 'automation_what',
            default => '',
        };
    }
    if ($intentId === '') {
        $intentTokens = cabit_ai_search_tokens($currentMessage);
        if ($intentTokens !== []) {
            $intentParts = array_map(static fn (string $token): string => '"' . str_replace('"', '""', cabit_ai_searchable_text($token)) . '"*', array_slice($intentTokens, 0, 10));
            $intentStatement = $pdo->prepare(
                'SELECT intent_id, bm25(intents_fts, 0.0, 5.0, 1.5) AS rank '
                . 'FROM prospects10b.intents_fts WHERE intents_fts MATCH :query ORDER BY rank ASC LIMIT 1'
            );
            $intentStatement->execute(['query' => implode(' OR ', $intentParts)]);
            $intentRow = $intentStatement->fetch();
            if (is_array($intentRow)) {
                $intentId = (string) ($intentRow['intent_id'] ?? '');
            }
        }
    }
    if ($intentId === '') {
        $intentId = 'first_step';
    }

    $statement = $pdo->prepare(
        'SELECT answers.answer_ref, answers.scenario_id, answers.intent_id, scenarios.business_type, scenarios.state_id, scenarios.scenario, '
        . 'answers.intent_title, answers.category, answers.canonical_answer_long '
        . 'FROM prospects10b.prospect_scenarios AS scenarios '
        . 'JOIN prospects10b.canonical_answers AS answers ON answers.scenario_id = scenarios.scenario_id '
        . 'WHERE scenarios.business_type = ? AND scenarios.state_id = ? AND answers.intent_id = ? LIMIT 1'
    );
    $statement->execute([$bestBusiness, $stateId, $intentId]);
    $answer = $statement->fetch();
    if (!is_array($answer)) {
        return null;
    }
    return [
        'answer_ref' => (string) $answer['answer_ref'],
        'business_type' => (string) $answer['business_type'],
        'state_id' => (string) $answer['state_id'],
        'scenario' => (string) $answer['scenario'],
        'intent_id' => (string) $answer['intent_id'],
        'intent_title' => (string) $answer['intent_title'],
        'category' => (string) $answer['category'],
        'canonical_answer_long' => (string) $answer['canonical_answer_long'],
        'confidence' => round(min(0.96, 0.82 + ($bestBusinessScore * 0.04)), 2),
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function cabit_ai_commercial_facts(): array
{
    $path = dirname(__DIR__) . '/data/CAB_IT_commercial_core.json';
    $raw = is_file($path) ? file_get_contents($path) : false;
    if ($raw === false) {
        return [];
    }
    try {
        $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return [];
    }
    if (!is_array($decoded) || !is_array($decoded['facts'] ?? null)) {
        return [];
    }
    $facts = [];
    foreach ($decoded['facts'] as $fact) {
        if (!is_array($fact)) {
            continue;
        }
        $facts[] = [
            'key' => (string) ($fact['key'] ?? ''),
            'value' => (string) ($fact['value'] ?? ''),
            'source_url' => (string) ($fact['source_url'] ?? ''),
            'priority' => (int) ($fact['priority'] ?? 0),
        ];
    }
    usort($facts, static fn (array $left, array $right): int => ((int) $right['priority']) <=> ((int) $left['priority']));
    return array_slice($facts, 0, 20);
}

/**
 * @return array<string, array<string, mixed>>
 */
function cabit_ai_industry_context_map(): array
{
    static $map = null;
    if (is_array($map)) {
        return $map;
    }
    $map = [];
    $paths = [
        dirname(__DIR__) . '/data/CAB_IT_50_INDUSTRII_CONTEXT.json',
        dirname(__DIR__) . '/data/CAB_IT_50_INDUSTRII_CONTEXT_SET2.json',
        dirname(__DIR__) . '/data/CAB_IT_100_DOMENII_CONTEXT_SET3.json',
    ];
    foreach ($paths as $path) {
        if (!is_file($path)) {
            continue;
        }
        try {
            $rows = json_decode((string) file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            continue;
        }
        if (!is_array($rows)) {
            continue;
        }
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $row['conversions'] = (string) ($row['conversions'] ?? $row['conversion'] ?? '');
            $row['trust'] = (string) ($row['trust'] ?? $row['trust_signals'] ?? '');
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($id !== '') {
                $map[$id] = $row;
            }
            if ($name !== '') {
                $map['name:' . cabit_ai_searchable_text($name)] = $row;
            }
        }
    }
    return $map;
}

/**
 * @param array<string, mixed> $intentRow
 * @return array<string, mixed>
 */
function cabit_ai_industry_context(array $intentRow): array
{
    $map = cabit_ai_industry_context_map();
    $industryId = trim((string) ($intentRow['industry_id'] ?? ''));
    if ($industryId !== '' && isset($map[$industryId])) {
        return $map[$industryId];
    }
    $industry = trim((string) ($intentRow['industry'] ?? $intentRow['category'] ?? ''));
    $key = 'name:' . cabit_ai_searchable_text($industry);
    return isset($map[$key]) && is_array($map[$key]) ? $map[$key] : [];
}

/**
 * @param array<string, mixed> $intentRow
 * @param list<string> $matchedQuestions
 * @return list<array<string, mixed>>
 */
function cabit_ai_retrieval_fragments(array $intentRow, string $answer, array $matchedQuestions = []): array
{
    $fragments = [];
    $sourceUrl = (string) ($intentRow['source_url'] ?? 'https://cab-it.ro/servicii/');
    $sourceHost = strtolower((string) parse_url($sourceUrl, PHP_URL_HOST));
    $isCabitSource = $sourceHost === 'cab-it.ro' || $sourceHost === 'www.cab-it.ro';
    $strategy = (string) ($intentRow['retrieval_strategy'] ?? '');
    $isCommercial = str_contains($strategy, 'commercial');
    $sourcePriority = $isCommercial ? 100 : ($isCabitSource ? 95 : 90);
    $sourceType = $isCommercial ? 'commercial_core' : ($isCabitSource ? 'cab_it_article' : 'official_documentation');
    $facts = trim((string) ($intentRow['facts'] ?? ''));
    if ($facts !== '') {
        $fragments[] = ['priority' => $sourcePriority, 'source_type' => $sourceType, 'source_url' => $sourceUrl, 'text' => $facts];
    }

    $paragraphs = preg_split('/(?:\R\s*){2,}/u', trim($answer)) ?: [];
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if ($paragraph === '') {
            continue;
        }
        $fragments[] = ['priority' => $sourcePriority, 'source_type' => $sourceType, 'source_url' => $sourceUrl, 'text' => mb_substr($paragraph, 0, 700, 'UTF-8')];
        if (count($fragments) >= 3) {
            break;
        }
    }

    $industry = cabit_ai_industry_context($intentRow);
    if ($industry !== []) {
        $industryText = implode('; ', array_filter([
            (string) ($industry['clients'] ?? ''),
            'Conversii: ' . (string) ($industry['conversions'] ?? ''),
            'Funcționalități: ' . (string) ($industry['features'] ?? ''),
            'Încredere: ' . (string) ($industry['trust'] ?? ''),
        ], static fn (string $value): bool => trim($value, " ;:") !== ''));
        if ($industryText !== '') {
            $fragments[] = ['priority' => 95, 'source_type' => 'industry_context', 'source_url' => $sourceUrl, 'text' => $industryText];
        }
    }

    if (count($fragments) < 3) {
        foreach ($matchedQuestions as $question) {
            $question = trim($question);
            if ($question === '') {
                continue;
            }
            $fragments[] = ['priority' => 90, 'source_type' => 'semantic_match', 'source_url' => $sourceUrl, 'text' => 'Întrebare asociată: ' . $question];
            if (count($fragments) >= 3) {
                break;
            }
        }
    }

    $responseRule = trim((string) ($intentRow['response_rule'] ?? ''));
    if (count($fragments) < 3 && $responseRule !== '') {
        $fragments[] = ['priority' => 90, 'source_type' => 'response_policy', 'source_url' => $sourceUrl, 'text' => $responseRule];
    }
    return array_slice($fragments, 0, 5);
}

/**
 * @param list<string> $tokens
 * @return list<array<string, mixed>>
 */
function cabit_ai_site_content_fragments(PDO $pdo, array $tokens): array
{
    if ($tokens === []
        || !cabit_ai_attach_schema($pdo, 'site')
        || !cabit_ai_schema_has_table($pdo, 'site', 'docs_fts')
        || !cabit_ai_schema_has_table($pdo, 'site', 'documents')
    ) {
        return [];
    }

    $parts = [];
    foreach (array_slice($tokens, 0, 12) as $token) {
        $token = trim(cabit_ai_searchable_text((string) $token));
        if ($token === '' || mb_strlen($token, 'UTF-8') < 2) {
            continue;
        }
        $parts[] = '"' . str_replace('"', '""', $token) . '"*';
    }
    if ($parts === []) {
        return [];
    }

    $statement = $pdo->prepare(
        'SELECT documents.title, documents.title AS heading, documents.description AS content, '
        . 'documents.url, documents.source_type, documents.priority, '
        . 'bm25(docs_fts, 5.0, 2.0, 0.2, 0.1) AS rank '
        . 'FROM site.docs_fts '
        . 'JOIN site.documents AS documents ON documents.id = docs_fts.rowid '
        . 'WHERE docs_fts MATCH :query '
        . 'ORDER BY documents.priority DESC, rank ASC '
        . 'LIMIT 40'
    );
    $statement->execute(['query' => implode(' OR ', array_values(array_unique($parts)))]);
    $rows = $statement->fetchAll();

    $rankedRows = [];
    foreach ($rows as $row) {
        $titleSearchable = cabit_ai_searchable_text((string) ($row['title'] ?? ''));
        $headingSearchable = cabit_ai_searchable_text((string) ($row['heading'] ?? ''));
        $contentSearchable = cabit_ai_searchable_text((string) ($row['content'] ?? ''));
        $titleHits = 0;
        $headingHits = 0;
        $allHits = 0;
        foreach ($tokens as $token) {
            $token = trim(cabit_ai_searchable_text((string) $token));
            if ($token === '' || mb_strlen($token, 'UTF-8') < 2) {
                continue;
            }
            $inTitle = str_contains($titleSearchable, $token);
            $inHeading = str_contains($headingSearchable, $token);
            $inContent = str_contains($contentSearchable, $token);
            if ($inTitle) {
                $titleHits++;
            }
            if ($inHeading) {
                $headingHits++;
            }
            if ($inTitle || $inHeading || $inContent) {
                $allHits++;
            }
        }
        $minimumHits = count($tokens) >= 3 ? 2 : 1;
        if ($allHits < $minimumHits) {
            continue;
        }
        $coverage = $allHits / max(1, count($tokens));
        $ftsStrength = min(12.0, max(0.0, -(float) ($row['rank'] ?? 0)));
        $row['_relevance'] = ($titleHits * 7.0) + ($headingHits * 3.5) + ($allHits * 1.4) + ($coverage * 5.0) + ($ftsStrength * 0.35);
        $rankedRows[] = $row;
    }
    usort($rankedRows, static function (array $left, array $right): int {
        $priorityOrder = ((int) ($right['priority'] ?? 0)) <=> ((int) ($left['priority'] ?? 0));
        if ($priorityOrder !== 0) {
            return $priorityOrder;
        }
        return ((float) ($right['_relevance'] ?? 0)) <=> ((float) ($left['_relevance'] ?? 0));
    });

    $cabitFragments = [];
    $officialFragments = [];
    $seen = [];
    foreach ($rankedRows as $row) {
        $url = trim((string) ($row['url'] ?? ''));
        $content = trim((string) ($row['content'] ?? ''));
        if ($url === '' || $content === '') {
            continue;
        }
        $key = hash('sha256', $url . "\n" . mb_substr($content, 0, 220, 'UTF-8'));
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $heading = trim((string) ($row['heading'] ?? ''));
        $title = trim((string) ($row['title'] ?? ''));
        $fragment = [
            'priority' => (int) ($row['priority'] ?? 95),
            'source_type' => (string) ($row['source_type'] ?? 'cab_it_article'),
            'source_url' => $url,
            'title' => $title,
            'heading' => $heading,
            'text' => mb_substr(($heading !== '' && $heading !== $title ? $heading . ': ' : '') . $content, 0, 760, 'UTF-8'),
        ];
        if ($fragment['source_type'] === 'official_reference') {
            if (count($officialFragments) < 1) {
                $officialFragments[] = $fragment;
            }
        } elseif (count($cabitFragments) < 3) {
            $cabitFragments[] = $fragment;
        }
        if (count($cabitFragments) >= 3 && count($officialFragments) >= 1) {
            break;
        }
    }
    return array_merge($cabitFragments, $officialFragments);
}

/**
 * @return array<string, mixed>|null
 */
function cabit_ai_site_article_fragment(PDO $pdo, string $url): ?array
{
    if ($url === ''
        || !cabit_ai_attach_schema($pdo, 'site')
        || !cabit_ai_schema_has_table($pdo, 'site', 'documents')
    ) {
        return null;
    }
    $statement = $pdo->prepare(
        'SELECT title, title AS heading, description AS content, url, source_type, priority FROM site.documents '
        . 'WHERE url = :url AND source_type = \'cab_it_article\' ORDER BY id ASC LIMIT 1'
    );
    $statement->execute(['url' => $url]);
    $row = $statement->fetch();
    if (!is_array($row) || trim((string) ($row['content'] ?? '')) === '') {
        return null;
    }
    return [
        'priority' => (int) ($row['priority'] ?? 95),
        'source_type' => 'cab_it_article',
        'source_url' => (string) $row['url'],
        'title' => (string) $row['title'],
        'heading' => (string) $row['heading'],
        'text' => mb_substr((string) $row['content'], 0, 760, 'UTF-8'),
        'editorial_recommendation' => true,
    ];
}

function cabit_ai_preferred_article_url(string $intent, string $activeSubject, string $message): string
{
    $searchable = cabit_ai_searchable_text($message);
    $isIndexingProblem = $intent === 'seo_not_google'
        || preg_match('/\b(nu apare|nu (?:este|e) in google|neindexat|indexare|url inspection|search console)\b/u', $searchable) === 1;
    if ($isIndexingProblem) {
        return 'https://cab-it.ro/blog/url-is-not-on-google-in-search-console-cum-afli-motivul-fara-sa-ceri-indexare-la-intamplare/';
    }
    if (str_contains($intent, 'website') || $activeSubject === 'website') {
        return 'https://cab-it.ro/blog/creare-site-web-pentru-firme/';
    }
    if (str_contains($intent, 'ads') || $activeSubject === 'paid_ads') {
        return 'https://cab-it.ro/blog/promovare-pe-google-campanii-care-aduc-clienti/';
    }
    if (in_array($activeSubject, ['tracking_analytics', 'conversions'], true)
        || str_contains($intent, 'tracking')
        || str_contains($intent, 'conversion')
    ) {
        return 'https://cab-it.ro/blog/tracking-conversii-in-google-ads-apeluri-whatsapp-si-formulare/';
    }
    if (str_contains($intent, 'seo') || $activeSubject === 'seo') {
        return 'https://cab-it.ro/blog/optimizare-seo-pentru-site/';
    }
    return '';
}

/**
 * Contextul compact este derivat din documentul master privat. Fișierul complet
 * rămâne în storage pentru mentenanță, iar modelul primește doar regulile utile.
 *
 * @return array<string, mixed>
 */
function cabit_ai_master_company_context(): array
{
    static $context = null;
    if (is_array($context)) {
        return $context;
    }
    $path = dirname(__DIR__) . '/data/CAB_IT_master_context_compact.json';
    if (!is_file($path)) {
        return $context = [];
    }
    try {
        $decoded = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
        return $context = is_array($decoded) ? $decoded : [];
    } catch (Throwable) {
        return $context = [];
    }
}

/**
 * @param list<array{role:string,content:string}> $history
 */
function cabit_ai_product_count(string $message, array $history): ?int
{
    $candidates = [$message];
    for ($index = count($history) - 1; $index >= 0; $index--) {
        if (($history[$index]['role'] ?? '') === 'user') {
            $candidates[] = (string) ($history[$index]['content'] ?? '');
        }
    }
    foreach ($candidates as $candidate) {
        $searchable = cabit_ai_searchable_text($candidate);
        if (preg_match('/\b([0-9]{1,7})\s*(?:de\s*)?(?:produse|articole|sku)\b/u', $searchable, $match) === 1) {
            return max(1, (int) $match[1]);
        }
    }
    return null;
}

/**
 * @return array{label:string,price:string,range:string}
 */
function cabit_ai_ecommerce_tier(?int $productCount): array
{
    if ($productCount !== null && $productCount > 500) {
        return ['label' => 'peste 500 de produse', 'price' => '3.199 lei, fără TVA', 'range' => 'over_500'];
    }
    if ($productCount !== null && $productCount >= 100) {
        return ['label' => 'între 100 și 500 de produse', 'price' => '2.399 lei, fără TVA', 'range' => '100_500'];
    }
    return ['label' => 'sub 100 de produse', 'price' => '1.799 lei, fără TVA', 'range' => 'under_100'];
}

/**
 * @param list<array{role:string,content:string}> $history
 * @return array{name:string,capabilities:string}|null
 */
function cabit_ai_requested_carrier(string $message, array $history): ?array
{
    $candidates = [$message];
    for ($index = count($history) - 1; $index >= 0; $index--) {
        if (($history[$index]['role'] ?? '') === 'user') {
            $candidates[] = (string) ($history[$index]['content'] ?? '');
        }
    }
    foreach ($candidates as $candidate) {
        $searchable = cabit_ai_searchable_text($candidate);
        if (preg_match('/\bfan(?:\s+courier)?\b/u', $searchable) === 1) {
            return ['name' => 'FAN Courier', 'capabilities' => 'calculul livrării, alegerea serviciului disponibil, generarea AWB-ului și urmărirea statusului expediției'];
        }
        if (preg_match('/\b(sameday|easybox)\b/u', $searchable) === 1) {
            return ['name' => 'Sameday', 'capabilities' => 'calculul livrării, alegerea livrării la adresă sau easybox atunci când serviciul o permite, generarea AWB-ului și urmărirea statusului expediției'];
        }
        if (preg_match('/\bcargus\b/u', $searchable) === 1) {
            return ['name' => 'Cargus', 'capabilities' => 'calculul livrării, alegerea serviciului disponibil, generarea AWB-ului și urmărirea statusului expediției'];
        }
    }
    return null;
}

/**
 * @return list<array<string, string>>
 */
function cabit_ai_reply_actions(string $intent): array
{
    if ($intent === 'contact') {
        return [
            ['type' => 'email', 'label' => 'Trimite un email', 'href' => 'mailto:contact@cab-it.ro'],
            ['type' => 'call', 'label' => 'Sună acum', 'href' => 'tel:+40771532949'],
            ['type' => 'whatsapp', 'label' => 'Scrie pe WhatsApp', 'href' => 'https://wa.me/40771532949?text=Bun%C4%83%21%20Doresc%20mai%20multe%20informa%C8%9Bii%20despre%20serviciile%20CAB-IT.'],
        ];
    }
    if ($intent === 'website_portfolio') {
        return [
            ['type' => 'link', 'label' => 'IFY.ro', 'href' => 'https://ify.ro'],
            ['type' => 'link', 'label' => 'Maison Bébé', 'href' => 'https://maison-bebe.ro'],
            ['type' => 'link', 'label' => 'Auto La Domiciliu', 'href' => 'https://autoladomiciliu.ro'],
            ['type' => 'link', 'label' => 'Nanu Events', 'href' => 'https://nanuevents.ro'],
            ['type' => 'link', 'label' => 'Traffic Pub', 'href' => 'https://trafficpub.ro'],
            ['type' => 'link', 'label' => 'Best TKD', 'href' => 'https://best-tkd.ro'],
            ['type' => 'link', 'label' => 'Lael Fashion', 'href' => 'https://laelfashion.ro'],
            ['type' => 'link', 'label' => 'Bilka Sistem', 'href' => 'https://bilka-sistem.ro'],
            ['type' => 'source', 'label' => 'Toate studiile de caz', 'href' => 'https://cab-it.ro/portofoliu/'],
        ];
    }
    return [];
}

/**
 * @param array<string, mixed> $intentRow
 * @return array<string, mixed>
 */
function cabit_ai_build_reply(array $intentRow, string $matchType, float $confidence, string $matchedQuestion = '', array $matchedQuestions = []): array
{
    $intent = (string) ($intentRow['intent'] ?? 'not_sure');
    $answer = trim((string) ($intentRow['canonical_answer_long'] ?? ''));
    $followUp = trim((string) ($intentRow['follow_up'] ?? ''));
    if ($followUp !== '') {
        $answer = trim((string) preg_replace('/(?:\R){2,}Ca pas următor,\s*[^\r\n]+$/ui', '', $answer));
    }
    if ($answer === '') {
        $answer = 'Spune-mi pe scurt ce vrei să obții, iar eu te ajut să alegi direcția potrivită.';
        $matchType = 'fallback';
        $confidence = 0.25;
    }
    $confidence = round(max(0.0, min(1.0, $confidence)), 2);
    // The server returns the verified canonical answer directly for every topic.
    $useLocalModel = false;
    $sourceUrl = (string) ($intentRow['source_url'] ?? 'https://cab-it.ro/servicii/');
    if (!filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
        $sourceUrl = 'https://cab-it.ro/servicii/';
    }

    $context = [
        'assistant_identity' => 'Asistentul CAB-IT răspunde ca un consultant al agenției CAB-IT Expert pentru website-uri, magazine online, SEO, promovare online, Google Ads și dezvoltări digitale personalizate.',
        'intent' => $intent,
        'category' => (string) ($intentRow['category'] ?? ''),
        'title' => (string) ($intentRow['title'] ?? ''),
        'canonical_answer' => $answer,
        'facts' => (string) ($intentRow['facts'] ?? ''),
        'response_rule' => (string) ($intentRow['response_rule'] ?? ''),
        'risk' => (string) ($intentRow['risk'] ?? 'low'),
        'retrieval_strategy' => (string) ($intentRow['retrieval_strategy'] ?? ''),
        'source_url' => $sourceUrl,
        'industry_id' => (string) ($intentRow['industry_id'] ?? ''),
        'industry' => (string) ($intentRow['industry'] ?? ''),
        'topic' => (string) ($intentRow['topic'] ?? ''),
        'local_relevance' => (string) ($intentRow['local_relevance'] ?? ''),
        'compliance_note' => (string) ($intentRow['compliance_note'] ?? ''),
    ];
    if ($matchedQuestion !== '') {
        $context['matched_question'] = $matchedQuestion;
    }
    if ($useLocalModel) {
        $context['master_company_context'] = cabit_ai_master_company_context();
        $context['commercial_facts'] = cabit_ai_commercial_facts();
        $industryContext = cabit_ai_industry_context($intentRow);
        if ($industryContext !== []) {
            $context['industry_context'] = $industryContext;
        }
        $context['retrieval_fragments'] = cabit_ai_retrieval_fragments($intentRow, $answer, $matchedQuestions);
        $context['generation_rule'] = 'Formulează natural răspunsul canonic folosind 3-5 fragmente relevante și ordinea surselor. Nu inventa prețuri, termene, garanții sau date de contact.';
    }

    return [
        'text' => $answer,
        'answer' => $answer,
        'intent' => $intent,
        'title' => (string) ($intentRow['title'] ?? ''),
        'confidence' => $confidence,
        'source' => [
            'label' => 'CAB-IT Expert',
            'url' => $sourceUrl,
        ],
        'source_url' => $sourceUrl,
        'followup' => $followUp,
        'context' => $context,
        'use_local_model' => $useLocalModel,
        'match_type' => $matchType,
        'actions' => cabit_ai_reply_actions($intent),
    ];
}

/**
 * Clientul poate propune indicii semantice înainte de retrieval. Serverul
 * păstrează doar câmpurile scurte și verifică industria/oferta în mesajele reale.
 *
 * @return array<string, string>
 */
function cabit_ai_parse_semantic_understanding(mixed $value, string $userContext): array
{
    if (!is_array($value)) {
        return [];
    }
    $allowed = ['subject', 'intent', 'industry', 'offer', 'objective', 'relation', 'confirmed_details', 'raw'];
    $result = [];
    foreach ($allowed as $key) {
        $field = $value[$key] ?? null;
        if (!is_string($field)) {
            continue;
        }
        $field = trim((string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $field));
        $field = (string) preg_replace('/\s+/u', ' ', $field);
        if ($field !== '') {
            $result[$key] = mb_substr($field, 0, $key === 'raw' ? 700 : 180, 'UTF-8');
        }
    }

    $userSearchable = cabit_ai_searchable_text($userContext);
    $generic = array_fill_keys(['afacere', 'firma', 'firme', 'client', 'clienti', 'service', 'servicii', 'promovare', 'digital', 'online'], true);
    foreach (['industry', 'offer', 'objective'] as $groundedKey) {
        if (!isset($result[$groundedKey])) {
            continue;
        }
        $supported = false;
        foreach (cabit_ai_search_tokens($result[$groundedKey]) as $token) {
            $token = cabit_ai_searchable_text($token);
            if (mb_strlen($token, 'UTF-8') < 4 || isset($generic[$token])) {
                continue;
            }
            if (preg_match('/\b' . preg_quote($token, '/') . '\b/u', $userSearchable) === 1) {
                $supported = true;
                break;
            }
        }
        if (!$supported) {
            unset($result[$groundedKey]);
        }
    }
    return $result;
}

function cabit_ai_semantic_rule_intent(array $understanding): ?string
{
    $semantic = cabit_ai_searchable_text(
        (string) ($understanding['subject'] ?? '') . ' ' . (string) ($understanding['intent'] ?? '')
    );
    return match (true) {
        cabit_ai_has_price_signal($semantic) && preg_match('/\b(site|website)\b/u', $semantic) === 1 => 'website_price',
        cabit_ai_has_price_signal($semantic) && preg_match('/\b(google ads|meta ads|reclame)\b/u', $semantic) === 1 => 'ads_price',
        cabit_ai_has_price_signal($semantic) && preg_match('/\b(magazin online|ecommerce)\b/u', $semantic) === 1 => 'ecommerce_price',
        preg_match('/\b(google ads|meta ads|facebook ads|reclame platite)\b/u', $semantic) === 1 => 'ads_general',
        cabit_ai_is_local_visibility_request($semantic) => 'seo_local',
        preg_match('/\b(seo|optimizare pentru google|rezultate organice)\b/u', $semantic) === 1 => 'seo_general',
        preg_match('/\b(magazin online|ecommerce|e commerce)\b/u', $semantic) === 1 => 'ecommerce_general',
        preg_match('/\b(site|website|pagina web|web design)\b/u', $semantic) === 1 => 'website_general',
        preg_match('/\b(automatizare|agent ai|chatbot)\b/u', $semantic) === 1 => 'automation_general',
        default => null,
    };
}

$payload = cabit_ai_require_post_json(262144);
cabit_ai_enforce_rate_limit('chat_reply_minute', 60, 60);
cabit_ai_enforce_rate_limit('chat_reply_daily', 600, 86400);

$message = cabit_ai_clean_text($payload['message'] ?? null, 'message', 1, 4000);
$history = cabit_ai_parse_history($payload['history'] ?? null);
$messageSearchable = cabit_ai_searchable_text($message);
$explicitArticleRequest = preg_match(
    '/(?:\b(?:cauta|gaseste|recomanda|arata|trimite|vreau|da mi)\b.{0,40}\b(?:articol|articole|ghid|ghiduri)\b|\b(?:articol|articole|ghid|ghiduri)\b.{0,30}\b(?:despre|pentru|seo|ads|website|ecommerce)\b)/u',
    $messageSearchable
) === 1;
$baseUserContextualMessage = cabit_ai_user_context_message($message, $history);
$semanticUnderstanding = cabit_ai_parse_semantic_understanding(
    $payload['semantic_understanding'] ?? null,
    $baseUserContextualMessage
);
$conversationUnderstanding = cabit_ai_understand_conversation($message, $history);
$contextualMessage = cabit_ai_contextual_message($message, $history);
$semanticRetrievalParts = [];
foreach (['industry', 'offer', 'subject', 'intent', 'objective'] as $semanticKey) {
    if (isset($semanticUnderstanding[$semanticKey])) {
        $semanticRetrievalParts[] = $semanticUnderstanding[$semanticKey];
    }
}
$userContextualMessage = trim($baseUserContextualMessage . ' ' . implode(' ', $semanticRetrievalParts));
if ($semanticUnderstanding !== []) {
    $conversationUnderstanding['semantic_understanding'] = $semanticUnderstanding;
}
$intentHint = $payload['intent_hint'] ?? null;
if ($intentHint !== null) {
    if (!is_string($intentHint) || !preg_match('/^[a-z0-9_]{2,64}$/', $intentHint)) {
        cabit_ai_error(422, 'invalid_intent_hint', 'Sugestia de intenție nu este validă.');
    }
}

$pdo = null;
$ruleIntent = is_string($intentHint) ? $intentHint : null;
try {
    $pdo = cabit_ai_smartchat_db();
} catch (Throwable) {
    // Răspunsul controlat din JSON rămâne disponibil dacă SQLite este temporar indisponibil.
}

if (is_string($intentHint)) {
    $intentRow = cabit_ai_fetch_intent($pdo, $intentHint);
    if ($intentRow === null) {
        cabit_ai_error(422, 'unknown_intent_hint', 'Sugestia de intenție nu este aprobată.');
    }
    $reply = cabit_ai_build_reply($intentRow, 'intent_hint', 0.99);
} else {
    $match = null;
    $ruleIntent = cabit_ai_rule_intent($message, $history);
    if ($ruleIntent === null && $semanticUnderstanding !== []) {
        $ruleIntent = cabit_ai_semantic_rule_intent($semanticUnderstanding);
    }
    if ($ruleIntent !== null) {
        $intentRow = cabit_ai_fetch_intent($pdo, $ruleIntent);
        if ($intentRow !== null) {
            $reply = cabit_ai_build_reply($intentRow, 'conversation_rule', 0.97);
        }
    }
    if (!isset($reply) && $pdo instanceof PDO) {
        try {
            $websiteMatch = cabit_ai_smartchat_website_1m_match($pdo, $userContextualMessage);
        } catch (Throwable) {
            $websiteMatch = null;
        }
        if (is_array($websiteMatch) && is_array($websiteMatch['intent_row'] ?? null)) {
            $reply = cabit_ai_build_reply(
                $websiteMatch['intent_row'],
                (string) ($websiteMatch['match_type'] ?? 'smartchat_website_1m_fts'),
                (float) ($websiteMatch['confidence'] ?? 0.75),
                (string) ($websiteMatch['question'] ?? ''),
                is_array($websiteMatch['fragments'] ?? null) ? $websiteMatch['fragments'] : []
            );
        }
    }
    if (!isset($reply) && $pdo instanceof PDO) {
        try {
            $virtualMatch = cabit_ai_virtual_50m_match($pdo, $userContextualMessage);
        } catch (Throwable) {
            $virtualMatch = null;
        }
        if (is_array($virtualMatch) && is_array($virtualMatch['intent_row'] ?? null)) {
            $reply = cabit_ai_build_reply(
                $virtualMatch['intent_row'],
                (string) ($virtualMatch['match_type'] ?? 'virtual_50m_answer_ref'),
                (float) ($virtualMatch['confidence'] ?? 0.8),
                (string) ($virtualMatch['question'] ?? ''),
                is_array($virtualMatch['fragments'] ?? null) ? $virtualMatch['fragments'] : []
            );
        }
    }
    if (!isset($reply) && $pdo instanceof PDO) {
        try {
            $smartchatMatch = cabit_ai_smartchat_1m_match($pdo, $userContextualMessage);
        } catch (Throwable) {
            $smartchatMatch = null;
        }
        if (is_array($smartchatMatch) && is_array($smartchatMatch['intent_row'] ?? null)) {
            $reply = cabit_ai_build_reply(
                $smartchatMatch['intent_row'],
                (string) ($smartchatMatch['match_type'] ?? 'smartchat_1m_fts'),
                (float) ($smartchatMatch['confidence'] ?? 0.75),
                (string) ($smartchatMatch['question'] ?? ''),
                is_array($smartchatMatch['fragments'] ?? null) ? $smartchatMatch['fragments'] : []
            );
        }
    }
    if (!isset($reply) && $pdo instanceof PDO) {
        try {
            $match = cabit_ai_fts_match(
                $pdo,
                cabit_ai_search_tokens($contextualMessage),
                cabit_ai_search_tokens($userContextualMessage)
            );
        } catch (Throwable) {
            $match = null;
        }
    }
    if (is_array($match)) {
        $intentRow = cabit_ai_fetch_intent($pdo, $match['intent']);
        if ($intentRow !== null) {
            $reply = cabit_ai_build_reply(
                $intentRow,
                (string) $match['match_type'],
                (float) $match['confidence'],
                (string) $match['question'],
                is_array($match['fragments'] ?? null) ? $match['fragments'] : []
            );
        }
    }
    if (!isset($reply)) {
        $intentRow = cabit_ai_fetch_intent($pdo, 'not_sure') ?? [
            'intent' => 'not_sure',
            'category' => 'Comercial',
            'title' => 'Clarificăm obiectivul',
            'canonical_answer_long' => 'Spune-mi pe scurt dacă problema principală este website-ul, vizibilitatea în Google, reclamele, conversiile sau automatizarea, iar eu te ajut să alegi direcția potrivită.',
            'follow_up' => 'Care este rezultatul principal pe care vrei să îl obții?',
            'facts' => '',
            'response_rule' => 'Nu inventa informații care nu apar în contextul controlat.',
            'risk' => 'low',
            'retrieval_strategy' => 'commercial_first',
            'source_url' => 'https://cab-it.ro/servicii/',
        ];
        $reply = cabit_ai_build_reply($intentRow, 'fallback', 0.35);
    }
}

$prospect10bMatch = null;
if ($pdo instanceof PDO) {
    try {
        $prospect10bMatch = cabit_ai_virtual_10b_prospect_match($pdo, $userContextualMessage, $message, $ruleIntent);
    } catch (Throwable) {
        $prospect10bMatch = null;
    }
}

if ($history !== [] && isset($reply['context']) && is_array($reply['context'])) {
    $reply['context']['conversation_history'] = $history;
}

$currentSearchable = cabit_ai_searchable_text($message);
$currentTokens = $currentSearchable === '' ? [] : (preg_split('/\s+/', $currentSearchable) ?: []);
$previousAssistant = cabit_ai_searchable_text(cabit_ai_previous_assistant_message($history));
$previousUserRaw = cabit_ai_previous_user_message($history);
$productCount = cabit_ai_product_count($message, $history);
$ecommerceTier = cabit_ai_ecommerce_tier($productCount);
$requestedCarrier = cabit_ai_requested_carrier($message, $history);
$previousUserSearchable = cabit_ai_searchable_text(cabit_ai_previous_user_message($history));
$asksForPriceNow = cabit_ai_has_price_signal($currentSearchable);
$continuesExplicitPriceQuestion = $productCount !== null
    && cabit_ai_has_price_signal($previousUserSearchable)
    && preg_match('/\b(cate produse|numar de produse|catalog)\b/u', $previousAssistant) === 1;
$shouldMentionEcommercePrice = $asksForPriceNow || $continuesExplicitPriceQuestion;
$isAmbiguousMultipleProjects = preg_match('/\b(?:am|avem)\s+mai\s+multe\s+proiecte(?:\s+diferite)?\b/u', $currentSearchable) === 1
    && preg_match('/\b(e commerce|ecommerce|magazin online|website|site web|seo|promovare|google ads|portofoliu)\b/u', $currentSearchable) !== 1;
$isPositiveAcknowledgement = preg_match('/^(?:(?:foarte\s+)?(?:bun|bine|frumos)|super|perfect|excelent|grozav|ok|okay|imi place|multumesc|mersi|am inteles|continua|mergi mai departe)(?:[\s,!.]+(?:continua|mergi mai departe|imi place|multumesc|mersi|e bine|este bine|asa da))*[\s!.]*$/u', $currentSearchable) === 1;
$isNegativeFeedback = preg_match('/\b(nu ai raspuns|nu raspunde|nu este bine|nu e bine|raspuns prost|gresit|ai gresit|nu asta|nu intelegi|nu ai inteles)\b/u', $currentSearchable) === 1;
$isPlainYesReply = preg_match('/^(?:da|d|ad|sigur|desigur|corect|asa este)[\s.!]*$/u', $currentSearchable) === 1;
$isPlainNoReply = preg_match('/^(?:nu|nu inca|inca nu|deloc)[\s.!]*$/u', $currentSearchable) === 1;
$isAffirmativeFollowup = $isPlainYesReply
    || preg_match('/^(?:da\s+)?(?:le\s+)?(?:am|avem)(?:\s+(?:deja|configurate|configurat|pe\s+(?:site|website)))?[\s.!]*$/u', $currentSearchable) === 1;
$isNegativeFollowup = $isPlainNoReply
    || preg_match('/^(?:nu\s+)?(?:nu\s+)?(?:le\s+)?(?:am|avem)(?:\s+(?:inca|configurate|configurat))?[\s.!]*$/u', $currentSearchable) === 1;
$previousAssistantRaw = cabit_ai_previous_assistant_message($history);
$currentTokenCount = count($currentTokens);
$answersOpenFollowup = $previousAssistantRaw !== ''
    && str_contains($previousAssistantRaw, '?')
    && $currentTokenCount >= 1
    && $currentTokenCount <= 12
    && in_array((string) ($conversationUnderstanding['relation'] ?? ''), ['answer_to_previous_question', 'answer_to_yes_no_question'], true);
$answersWebsiteAuditScope = preg_match('/\b(este website ul tau|este site ul tau|vrei doar o analiza publica|analiza publica a lui)\b/u', $previousAssistant) === 1
    && preg_match('/^(?:da|d|ad|sigur|desigur|corect|este al meu|e al meu|al meu|website ul meu|site ul meu)[\s.!]*$/u', $currentSearchable) === 1;
$auditWebsiteLabel = '';
if ($answersWebsiteAuditScope && preg_match('/\b(?:https?:\/\/)?(?:www\.)?[a-z0-9][a-z0-9-]*(?:\.[a-z0-9-]+)+(?:\/[^\s]*)?/iu', $previousUserRaw, $auditWebsiteMatch) === 1) {
    $auditWebsiteLabel = rtrim((string) ($auditWebsiteMatch[0] ?? ''), '.,;:!?)]}');
}
if ($answersWebsiteAuditScope && $auditWebsiteLabel === '') {
    $conversationDomains = is_array($conversationUnderstanding['domains_mentioned'] ?? null) ? $conversationUnderstanding['domains_mentioned'] : [];
    $auditWebsiteLabel = $conversationDomains !== [] ? (string) end($conversationDomains) : '';
}

if ($isAmbiguousMultipleProjects) {
    $reply['intent'] = 'conversation_clarification';
    $reply['title'] = 'Clarificarea proiectului';
    $reply['text'] = $reply['answer'] = 'Sigur. Ca să nu amestec informațiile între proiecte, spune-mi la care proiect vrei să continuăm sau enumeră-le pe scurt, împreună cu obiectivul fiecăruia.';
    $reply['followup'] = 'Vrei să le discutăm pe rând? Cu care proiect începem?';
    $reply['source'] = ['label' => 'CAB-IT Expert', 'url' => 'https://cab-it.ro/servicii/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/';
    $reply['actions'] = [];
    $reply['use_local_model'] = false;
    $reply['match_type'] = 'conversation_clarification';
    $reply['context'] = [
        'intent' => 'conversation_clarification',
        'canonical_answer' => $reply['text'],
        'response_rule' => 'Cere identificarea proiectului înainte de a continua. Nu presupune că utilizatorul cere portofoliul CAB-IT.',
        'source_url' => 'https://cab-it.ro/servicii/',
    ];
    if ($history !== []) {
        $reply['context']['conversation_history'] = $history;
    }
} elseif ($answersWebsiteAuditScope) {
    $websiteReference = $auditWebsiteLabel !== '' ? ' pentru „' . $auditWebsiteLabel . '”' : '';
    $reply['intent'] = 'website_audit_scope_confirmed';
    $reply['title'] = 'Analiză website confirmată';
    $reply['text'] = $reply['answer'] = 'Am înțeles: continuăm analiza' . $websiteReference . ' ca website-ul tău. Ca să-ți spun probleme reale, nu voi ghici: verificarea trebuie să acopere accesarea și indexarea, robots/noindex/canonical, sitemapul, structura paginilor, conținutul, mobilul, performanța și conversiile. Datele publice pot fi analizate fără acces; pentru interogări, clickuri și acoperirea exactă a indexării este necesar acces autorizat la Search Console.';
    $reply['followup'] = 'Începe cu auditul public, iar dacă ai Search Console putem continua apoi cu datele reale din cont.';
    $reply['source'] = ['label' => 'Audit CAB-IT', 'url' => 'https://cab-it.ro/#audit'];
    $reply['source_url'] = 'https://cab-it.ro/#audit';
    $reply['actions'] = [['type' => 'source', 'label' => 'Pornește auditul gratuit', 'href' => 'https://cab-it.ro/#audit']];
    $reply['use_local_model'] = false;
    $reply['match_type'] = 'conversation_scope_confirmation';
    $reply['context'] = [
        'intent' => 'website_audit_scope_confirmed',
        'canonical_answer' => $reply['text'],
        'website_url_supplied' => $auditWebsiteLabel,
        'response_rule' => 'Păstrează domeniul din conversație și nu inventa rezultate de audit care nu au fost măsurate.',
        'source_url' => 'https://cab-it.ro/#audit',
    ];
} elseif ($answersOpenFollowup
    && !$isNegativeFeedback
    && !$isPositiveAcknowledgement
    && !in_array((string) ($reply['intent'] ?? ''), ['ecommerce_general', 'website_general', 'website_custom', 'website_portfolio', 'contact'], true)
) {
    $questionMatches = [];
    preg_match_all('/(?:^|[\r\n.!]\s*)([^?]{3,220}\?)/u', $previousAssistantRaw, $questionMatches);
    $questions = is_array($questionMatches[1] ?? null) ? $questionMatches[1] : [];
    $previousQuestion = trim((string) ($questions !== [] ? end($questions) : 'întrebarea anterioară'));
    $retrievedCanonical = (string) ($reply['text'] ?? '');
    $retrievedIntent = (string) ($reply['intent'] ?? '');
    $clientSelection = trim($message);
    $reply['intent'] = 'conversation_followup_answer';
    $reply['title'] = 'Continuare contextuală';
    $reply['text'] = $reply['answer'] = 'Am înțeles: „' . $clientSelection . '”. Continui pe această variantă și adaptez recomandarea la obiectivul discutat, fără să reiau explicația anterioară.';
    $reply['followup'] = '';
    $reply['use_local_model'] = true;
    $reply['match_type'] = 'conversation_followup';
    $reply['context'] = [
        'intent' => 'conversation_followup_answer',
        'title' => 'Continuare contextuală',
        'canonical_answer' => $reply['text'],
        'conversation_mode' => 'answer_to_previous_question',
        'previous_user_objective' => $previousUserRaw,
        'previous_assistant_question' => $previousQuestion,
        'client_answer' => $clientSelection,
        'retrieved_intent' => $retrievedIntent,
        'retrieved_canonical' => $retrievedCanonical,
        'response_rule' => 'Detectează subiectul din istoricul recent. Confirmă alegerea clientului, răspunde concret pe baza ei și continuă natural; nu repeta răspunsul anterior și nu schimba industria.',
        'generation_rule' => 'Oferă 2-4 propoziții utile și cel mult o întrebare precisă doar dacă este necesară.',
        'source_url' => (string) ($reply['source_url'] ?? 'https://cab-it.ro/servicii/'),
        'conversation_history' => $history,
    ];
    $followupActiveSubject = (string) ($conversationUnderstanding['active_subject'] ?? '');
    $answersAdsOfferQuestion = $followupActiveSubject === 'paid_ads'
        && preg_match('/\b(ce serviciu|ce produs|serviciu sau produs|ce vrei sa promovezi)\b/u', $previousAssistant) === 1
        && $currentTokenCount >= 2;
    if ($answersAdsOfferQuestion) {
        $offerLabel = trim((string) preg_replace('/^(?:vreau|doresc|as vrea)(?:\s+sa)?(?:\s+promovez)?\s+/u', '', $currentSearchable));
        if ($offerLabel === '') {
            $offerLabel = trim($message);
        }
        $objectivesDiscussed = is_array($conversationUnderstanding['objectives_discussed'] ?? null)
            ? $conversationUnderstanding['objectives_discussed']
            : [];
        $hasCallsAndClicks = in_array('phone_calls', $objectivesDiscussed, true)
            && in_array('traffic_clicks', $objectivesDiscussed, true);
        if ($hasCallsAndClicks) {
            $reply['text'] = $reply['answer'] = 'Pentru ' . $offerLabel . ', Google Ads poate capta căutările locale ale oamenilor care au nevoie acum de reparație. Ai menționat însă întâi că vrei mai multe apeluri, apoi mai multe clickuri; cele două se pot măsura, dar campania are nevoie de un obiectiv principal ca să nu optimizăm doar trafic care nu devine client.';
            $reply['followup'] = 'Vrei să tratăm apelurile drept obiectiv principal, iar clickurile doar ca pas intermediar?';
            $reply['context']['objective_conflict'] = ['phone_calls', 'traffic_clicks'];
        } else {
            $reply['text'] = $reply['answer'] = 'Pentru ' . $offerLabel . ', campania Google Ads trebuie concentrată pe căutările locale ale oamenilor care au nevoie acum de reparație, cu anunțuri clare, extensie de apel și o pagină care explică serviciile, zona acoperită și programul. Măsurăm apelurile și cererile reale, apoi eliminăm căutările fără legătură și mutăm bugetul către cele care aduc clienți.';
            $reply['followup'] = 'În ce localitate oferi serviciul și vrei ca oamenii să sune direct sau să trimită o cerere?';
        }
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['declared_offer'] = $offerLabel;
        $reply['context']['response_rule'] = 'Clientul a spus ce serviciu promovează. Răspunde concret pentru acel serviciu și Google Ads local; nu descrie mecanica conversației și nu repeta întrebarea anterioară.';
    }
} elseif ($isPositiveAcknowledgement) {
    $acknowledgementSubject = (string) ($conversationUnderstanding['active_subject'] ?? '');
    $acknowledgementOffer = trim((string) preg_replace(
        '/^(?:vreau|doresc|as vrea)(?:\s+sa)?(?:\s+promovez)?\s+/u',
        '',
        cabit_ai_searchable_text($previousUserRaw)
    ));
    $reply['intent'] = 'conversation_acknowledgement';
    $reply['title'] = 'Continuarea conversației';
    if ($acknowledgementSubject === 'paid_ads' && $acknowledgementOffer !== '') {
        $acceptsCallsAsPrimary = preg_match('/\b(apelurile|apeluri).{0,45}\b(obiectiv principal|principale)\b/u', $previousAssistant) === 1;
        if ($acceptsCallsAsPrimary) {
            $reply['text'] = $reply['answer'] = 'Stabilim atunci apelurile ca obiectiv principal pentru campania de reparații trotinete, iar clickurile rămân un pas intermediar. Vom urmări căutările cu intenție locală, apelurile din anunț și din website și, pe cât posibil, apelurile care devin solicitări reale.';
        } else {
            $reply['text'] = $reply['answer'] = 'Continuăm cu planul Google Ads pentru ' . $acknowledgementOffer . '. Următorul pas este să delimităm zona în care poți prelua clienți și să alegem acțiunea după care optimizăm campania, nu doar numărul de clickuri.';
        }
        $reply['followup'] = 'În ce localitate sau zonă oferi serviciul?';
        $reply['source'] = ['label' => 'Google Ads CAB-IT', 'url' => 'https://cab-it.ro/servicii/reclame-platite/'];
        $reply['source_url'] = 'https://cab-it.ro/servicii/reclame-platite/';
    } else {
        $reply['text'] = $reply['answer'] = 'Sigur. Continui de la informațiile deja stabilite și trecem la următorul detaliu necesar.';
        $reply['followup'] = 'Ce rezultat sau cerință vrei să stabilim mai departe?';
        $reply['source'] = ['label' => 'CAB-IT Expert', 'url' => 'https://cab-it.ro/servicii/'];
        $reply['source_url'] = 'https://cab-it.ro/servicii/';
    }
    $reply['actions'] = [];
    $reply['use_local_model'] = true;
    $reply['match_type'] = 'conversation_acknowledgement';
    $reply['context'] = [
        'intent' => 'conversation_acknowledgement',
        'canonical_answer' => $reply['text'],
        'declared_offer' => $acknowledgementOffer,
        'response_rule' => 'Interpretează confirmarea folosind ultima întrebare și întregul context. Continuă concret cu oferta și obiectivul deja stabilite; nu schimba industria.',
        'source_url' => $reply['source_url'],
    ];
} elseif ($isNegativeFeedback) {
    $reply['intent'] = 'conversation_repair';
    $reply['title'] = 'Clarificarea răspunsului';
    $reply['text'] = $reply['answer'] = 'Îmi pare rău că răspunsul nu a fost potrivit. Nu voi repeta automat informația anterioară; spune-mi ce parte este greșită sau ce rezultat concret așteptai, iar eu refac răspunsul folosind contextul conversației.';
    $reply['followup'] = 'Ce informație vrei să corectez prima dată?';
    $reply['source'] = ['label' => 'CAB-IT Expert', 'url' => 'https://cab-it.ro/servicii/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/';
    $reply['actions'] = [];
    $reply['use_local_model'] = true;
    $reply['match_type'] = 'conversation_repair';
    $reply['context'] = [
        'intent' => 'conversation_repair',
        'canonical_answer' => $reply['text'],
        'response_rule' => 'Recunoaște problema, folosește contextul recent și cere o singură clarificare utilă. Nu repeta răspunsul anterior.',
        'source_url' => 'https://cab-it.ro/servicii/',
    ];
}

$declaredConversions = [
    ['pattern' => '/\b(apel|apelul|apeluri|telefon|telefonul|telefoane|telefonic|telefonice|sunari)\b/u', 'label' => 'apelurile telefonice', 'measurement' => 'clickurile click-to-call și, ideal, apelurile răspunse și leadurile calificate'],
    ['pattern' => '/\b(whatsapp|mesaje whatsapp)\b/u', 'label' => 'conversațiile pe WhatsApp', 'measurement' => 'clickurile către WhatsApp și conversațiile care devin leaduri'],
    ['pattern' => '/\b(formular|formulare|cereri|lead|leaduri)\b/u', 'label' => 'cererile trimise prin formular', 'measurement' => 'începerea, abandonul și trimiterea validă a formularului'],
    ['pattern' => '/\b(comanda|comenzi|vanzare|vanzari)\b/u', 'label' => 'comenzile', 'measurement' => 'pașii de e-commerce de la produs și coș până la achiziție'],
    ['pattern' => '/\b(programare|programari|rezervare|rezervari)\b/u', 'label' => 'programările', 'measurement' => 'selectarea intervalului, confirmarea și programările finalizate'],
];
$declaredConversion = null;
foreach ($declaredConversions as $conversionCandidate) {
    if (preg_match($conversionCandidate['pattern'], $currentSearchable) === 1) {
        $declaredConversion = $conversionCandidate;
        break;
    }
}
$isConversionFollowup = ($reply['intent'] ?? '') === 'conversation_followup_answer'
    && preg_match('/\b(conversia|conversie|actiunea principala|rezultatul principal)\b/u', $previousAssistant) === 1;
if ((in_array((string) ($reply['intent'] ?? ''), ['cro_general', 'ads_tracking', 'conversion_tracking'], true) || $isConversionFollowup)
    && is_array($declaredConversion)
) {
    $reply['text'] = $reply['answer'] = 'Am înțeles: conversia urmărită este ' . $declaredConversion['label'] . '. Atunci optimizarea trebuie construită în jurul traseului până la această acțiune: sursa și intenția traficului, mesajul și CTA-ul, experiența pe mobil, dovezile de încredere, fricțiunea înainte de acțiune și măsurarea corectă. În tracking trebuie urmărite ' . $declaredConversion['measurement'] . ', nu doar vizitele pe pagină.';
    $reply['followup'] = 'Ai deja GA4 și Google Tag Manager configurate pe website?';
    $reply['source'] = ['label' => 'Optimizare conversii CAB-IT', 'url' => 'https://cab-it.ro/servicii/optimizare-conversii/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/optimizare-conversii/';
    $reply['actions'] = [['type' => 'source', 'label' => 'Vezi optimizarea conversiilor', 'href' => 'https://cab-it.ro/servicii/optimizare-conversii/']];
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['declared_conversion'] = $declaredConversion['label'];
        $reply['context']['response_rule'] = 'Clientul a precizat deja conversia. Continuă direct cu optimizarea și măsurarea ei; nu întreba din nou care este conversia principală.';
    }
}

$explicitClickGoal = preg_match('/\b(?:click|clickuri|clickurile|clic|clicuri|trafic|vizitatori)\b/u', $currentSearchable) === 1;
$negatesClickGoal = preg_match('/^\s*nu\s+(?:mai\s+)?(?:am\s+nevoie|vreau|doresc|urmaresc).*?\b(?:click|clickuri|clickurile|clic|clicuri|trafic|vizitatori)\b/iu', $message) === 1;
if ($explicitClickGoal && !$negatesClickGoal) {
    $reply['intent'] = 'ads_click_goal';
    $reply['title'] = 'Mai multe clickuri relevante';
    $reply['text'] = $reply['answer'] = 'Am înțeles: obiectivul este să obții mai multe clickuri și trafic relevant către website. Pentru asta trebuie lucrat la acoperirea și relevanța audienței, termenii sau segmentarea folosită, mesajul anunțului, atractivitatea ofertei și costul pe click. Nu aș optimiza însă doar volumul: trebuie urmărit și câte clickuri devin apeluri, cereri sau vânzări, ca să nu plătești trafic fără valoare.';
    $reply['followup'] = 'Pe ce canal vrei mai multe clickuri: Google Ads, Meta Ads sau rezultate organice din Google?';
    $reply['source'] = ['label' => 'Promovare online CAB-IT', 'url' => 'https://cab-it.ro/servicii/reclame-platite/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/reclame-platite/';
    $reply['actions'] = [['type' => 'source', 'label' => 'Vezi promovarea online', 'href' => 'https://cab-it.ro/servicii/reclame-platite/']];
    $reply['use_local_model'] = true;
    $reply['match_type'] = $history === [] ? 'objective_entity' : 'conversation_objective_change';
    $existingContext = isset($reply['context']) && is_array($reply['context']) ? $reply['context'] : [];
    $reply['context'] = array_merge($existingContext, [
        'intent' => 'ads_click_goal',
        'title' => 'Mai multe clickuri relevante',
        'canonical_answer' => $reply['text'],
        'declared_objective' => 'mai multe clickuri și trafic relevant',
        'response_rule' => 'Clientul a schimbat sau a precizat obiectivul. Continuă pe obiectivul clickurilor relevante, fără să îl tratezi ca răspuns da/nu și fără să revii automat la apeluri.',
        'source_url' => 'https://cab-it.ro/servicii/reclame-platite/',
    ]);
} elseif (($reply['intent'] ?? '') === 'conversation_followup_answer'
    && preg_match('/\b(?:ga4|google tag manager|gtm|tracking|configurat|configurate)\b/u', $previousAssistant) === 1
    && ($isAffirmativeFollowup || $isNegativeFollowup)
) {
    if ($isAffirmativeFollowup) {
        $reply['text'] = $reply['answer'] = 'Perfect. Atunci putem folosi configurația existentă ca punct de plecare. Următorul pas este să verificăm dacă evenimentele și conversiile sunt înregistrate o singură dată, au parametrii corecți și pot fi atribuite sursei care a adus clientul.';
        $reply['followup'] = 'Ce acțiune este marcată acum drept conversie în GA4 sau Google Ads?';
    } else {
        $reply['text'] = $reply['answer'] = 'În regulă. Atunci primul pas este configurarea măsurării: GA4 pentru comportamentul de pe website, Google Tag Manager pentru evenimente și conversii separate pentru acțiunile importante. Abia după ce datele sunt corecte putem optimiza campaniile fără să ghicim.';
        $reply['followup'] = 'Vrei să urmărești mai întâi apelurile, formularele, comenzile sau altă acțiune?';
    }
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['response_rule'] = 'Interpretează răspunsul da/nu în raport cu întrebarea tehnică anterioară și continuă concret, fără formulări despre mecanica conversației.';
    }
}

$asksHowCabItCanHelp = preg_match('/\b(cum ma poti ajuta|cum ma ajuti|cum ne poti ajuta|cum ma puteti ajuta|cum ne puteti ajuta|ce poti face|ce puteti face|spune cum|ma puteti ajuta|ma poti ajuta|ne puteti ajuta|ne poti ajuta|puteti ajuta|poti ajuta)\b/u', $currentSearchable) === 1;
if ($asksHowCabItCanHelp) {
    $subjectsDiscussed = is_array($conversationUnderstanding['subjects_discussed'] ?? null)
        ? $conversationUnderstanding['subjects_discussed']
        : [];
    $activeSubject = (string) ($conversationUnderstanding['active_subject'] ?? '');
    $activeObjective = (string) ($conversationUnderstanding['active_objective'] ?? '');
    $conversationDomains = is_array($conversationUnderstanding['domains_mentioned'] ?? null)
        ? $conversationUnderstanding['domains_mentioned']
        : [];
    $knownDomain = $conversationDomains !== [] ? (string) end($conversationDomains) : '';
    $contextHasSeo = $activeSubject === 'seo' || in_array('seo', $subjectsDiscussed, true) || in_array('website_audit', $subjectsDiscussed, true);
    $contextHasAds = $activeSubject === 'paid_ads' || in_array('paid_ads', $subjectsDiscussed, true);
    $contextHasWebsite = in_array($activeSubject, ['website', 'ecommerce'], true)
        || in_array('website', $subjectsDiscussed, true)
        || in_array('ecommerce', $subjectsDiscussed, true);
    $contextHasTracking = in_array($activeSubject, ['tracking_analytics', 'conversions'], true)
        || in_array('tracking_analytics', $subjectsDiscussed, true)
        || in_array('conversions', $subjectsDiscussed, true);

    if ($contextHasSeo) {
        $domainPhrase = $knownDomain !== '' ? ' pentru „' . $knownDomain . '”' : '';
        $reply['text'] = $reply['answer'] = 'Da, te putem ajuta cu SEO' . $domainPhrase . '. Începem prin a verifica ce poate vedea și indexa Google, cum este organizat website-ul, ce caută oamenii care au nevoie de serviciile tale și unde se pierde vizibilitatea. Din audit stabilim apoi prioritățile reale: corecții tehnice, pagini de servicii, conținut, SEO local și măsurarea cererilor primite.';
        $reply['followup'] = $knownDomain !== ''
            ? 'Spune-mi serviciul principal și localitatea vizată, ca să legăm auditul de clienții pe care vrei să-i atragi.'
            : 'Ai deja un website? Dacă da, trimite-mi domeniul și spune-mi serviciul principal și localitatea vizată.';
        $sourceUrl = 'https://cab-it.ro/servicii/optimizare-seo/';
    } elseif ($contextHasAds) {
        $objectivePhrase = $activeObjective === 'phone_calls' ? ' și construim campania în jurul apelurilor' : '';
        $reply['text'] = $reply['answer'] = 'Da, te putem ajuta cu Google Ads' . $objectivePhrase . '. Alegem căutările care arată intenție reală, scriem anunțurile în jurul ofertei tale, trimitem oamenii către pagina potrivită și configurăm măsurarea conversiilor. Apoi optimizăm după apeluri, formulare sau vânzări, nu doar după numărul de clickuri.';
        $reply['followup'] = 'Ce serviciu sau produs vrei să promovezi și care este rezultatul principal urmărit?';
        $sourceUrl = 'https://cab-it.ro/servicii/reclame-platite/';
    } elseif ($contextHasWebsite) {
        $reply['text'] = $reply['answer'] = 'Da. Putem transforma cerința într-un website complet: stabilim paginile și traseul clientului, pregătim designul, dezvoltarea, formularele sau comenzile și măsurarea rezultatelor. Îl construim în jurul modului în care afacerea ta vinde, nu ca pe un șablon generic.';
        $reply['followup'] = 'Ai nevoie de un website de prezentare sau de un magazin online?';
        $sourceUrl = 'https://cab-it.ro/servicii/creare-website/';
    } elseif ($contextHasTracking) {
        $reply['text'] = $reply['answer'] = 'Da. Putem verifica traseul de la reclamă sau căutare până la acțiunea importantă și putem configura măsurarea corectă în GA4, Google Tag Manager și platformele de promovare. După ce știm ce surse aduc apeluri, formulare ori vânzări reale, putem optimiza fără presupuneri.';
        $reply['followup'] = 'Care este conversia principală pe care vrei să o măsori?';
        $sourceUrl = 'https://cab-it.ro/servicii/optimizare-conversii/';
    } else {
        $reply['text'] = $reply['answer'] = 'Te putem ajuta cu website-uri și magazine online, SEO, Google Ads, măsurarea conversiilor și automatizări. Ca să-ți spun concret ce merită făcut, pornesc de la situația actuală și de la rezultatul pe care îl urmărești, apoi îți propun pașii în ordinea impactului.';
        $reply['followup'] = 'Ce vrei să obții acum: mai multe apeluri, cereri, vânzări sau un website nou?';
        $sourceUrl = 'https://cab-it.ro/servicii/';
    }

    $reply['intent'] = 'conversation_context_help';
    $reply['title'] = 'Cum te poate ajuta CAB-IT';
    $reply['source'] = ['label' => 'Servicii CAB-IT', 'url' => $sourceUrl];
    $reply['source_url'] = $sourceUrl;
    $reply['actions'] = [];
    $reply['use_local_model'] = true;
    $reply['match_type'] = 'conversation_context_help';
    $reply['context'] = [
        'intent' => 'conversation_context_help',
        'canonical_answer' => $reply['text'],
        'active_subject' => $activeSubject,
        'active_objective' => $activeObjective,
        'website_url_supplied' => $knownDomain,
        'response_rule' => 'Răspunde concret la întrebarea despre ajutor folosind subiectul și obiectivul deja stabilite. Nu descrie mecanica conversației și nu schimba industria.',
        'source_url' => $sourceUrl,
        'conversation_history' => $history,
    ];
}

if (($reply['intent'] ?? '') === 'contact') {
    $contactActions = cabit_ai_reply_actions('contact');
    $asksForPrivateDecisionMakerNumber = preg_match('/\b(?:patron|patronul|patronului|proprietar|proprietarul|proprietarului|fondator|fondatorul|fondatorului|administrator|administratorul|administratorului|director|directorul|directorului)\b/u', $currentSearchable) === 1;
    if ($asksForPrivateDecisionMakerNumber) {
        $reply['text'] = $reply['answer'] = 'Nu pot furniza un număr personal care nu este public. Poți suna însă la numărul oficial CAB-IT, +40 771 532 949, și spune că vrei să vorbești cu administratorul sau cu persoana responsabilă de marketing.';
        $reply['followup'] = 'Apasă „Sună acum” pentru a iniția apelul.';
        $reply['actions'] = array_values(array_filter($contactActions, static fn (array $action): bool => ($action['type'] ?? '') === 'call'));
    } elseif (preg_match('/\b(whatsapp|wapp|mesaj)\b/u', $currentSearchable) === 1) {
        $reply['text'] = $reply['answer'] = 'Sigur. Poți continua direct pe WhatsApp cu echipa CAB-IT la +40 771 532 949.';
        $reply['followup'] = 'Apasă butonul de mai jos pentru a deschide conversația.';
        $reply['actions'] = array_values(array_filter($contactActions, static fn (array $action): bool => ($action['type'] ?? '') === 'whatsapp'));
    } elseif (preg_match('/\b(email|e mail|mail)\b/u', $currentSearchable) === 1) {
        $reply['text'] = $reply['answer'] = 'Sigur. Poți trimite detaliile proiectului la contact@cab-it.ro.';
        $reply['followup'] = 'Apasă butonul de mai jos pentru a deschide emailul.';
        $reply['actions'] = array_values(array_filter($contactActions, static fn (array $action): bool => ($action['type'] ?? '') === 'email'));
    } elseif (preg_match('/\b(apel|telefon|sun|suna|sunati|apelez)\b/u', $currentSearchable) === 1) {
        $reply['text'] = $reply['answer'] = 'Sigur. Poți suna acum echipa CAB-IT la +40 771 532 949.';
        $reply['followup'] = 'Apasă butonul de mai jos pentru a iniția apelul.';
        $reply['actions'] = array_values(array_filter($contactActions, static fn (array $action): bool => ($action['type'] ?? '') === 'call'));
    } else {
        $reply['text'] = $reply['answer'] = 'Sigur. Poți vorbi direct cu echipa CAB-IT la +40 771 532 949, prin WhatsApp la același număr sau prin email la contact@cab-it.ro.';
        $reply['followup'] = 'Alege mai jos varianta potrivită: apel, WhatsApp sau email.';
        $reply['actions'] = $contactActions;
    }
    $reply['use_local_model'] = true;
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['response_rule'] = 'Răspunde natural și scurt. Păstrează exact datele de contact verificate și invită clientul să folosească butonul potrivit.';
    }
}

if (($reply['intent'] ?? '') === 'website_price') {
    $reply['text'] = $reply['answer'] = "Prețurile publice CAB-IT pentru crearea website-urilor sunt:\n\n"
        . "• Website de prezentare: de la 999 lei, fără TVA — până la 5 pagini, design responsive, structură SEO de bază, formular și administrare.\n"
        . "• Magazin online cu mai puțin de 100 de produse: de la 1.799 lei, fără TVA.\n"
        . "• Magazin online cu 100–500 de produse: de la 2.399 lei, fără TVA.\n"
        . "• Magazin online cu peste 500 de produse: de la 3.199 lei, fără TVA.\n\n"
        . 'Acestea sunt praguri de pornire; integrările, importul de produse, variantele, funcționalitățile speciale și complexitatea pot modifica oferta finală.';
    $reply['followup'] = 'Ai nevoie de un site de prezentare sau de un magazin online și, dacă este magazin, câte produse va avea?';
    $reply['actions'] = [['type' => 'source', 'label' => 'Vezi toate prețurile', 'href' => 'https://cab-it.ro/preturi/']];
    $reply['use_local_model'] = false;
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['commercial_facts'] = cabit_ai_commercial_facts();
        $reply['context']['response_rule'] = 'Afișează toate pragurile publice pentru website și magazin online. Nu transforma prețurile de pornire în prețuri fixe.';
    }
}

if (($reply['intent'] ?? '') === 'ads_general') {
    $hasExplicitGoogleAds = preg_match('/\b(google ads|campanie google|campanii google)\b/u', $currentSearchable) === 1
        || preg_match('/\bgoogle ads\b/u', cabit_ai_searchable_text($userContextualMessage)) === 1;
    $platformLabel = $hasExplicitGoogleAds ? 'Google Ads' : 'reclame plătite';
    $declaredOffer = trim((string) ($semanticUnderstanding['offer'] ?? ''));
    if ($declaredOffer === '' && preg_match('/\b(?:promovez|promovam)\s+(.+?)(?=\s+(?:prin|pe)\s+google ads|\s+și\s+(?:să|vreau)|\s+si\s+(?:sa|vreau)|[.!?]|$)/iu', $message, $offerMatch) === 1) {
        $declaredOffer = trim((string) ($offerMatch[1] ?? ''));
    }
    if ($declaredOffer === '' && preg_match('/\bservice(?:ul)?\s+(?:de|pentru)\s+(.+?)(?=\s+(?:în|in)\s+[A-ZĂÂÎȘȚa-zăâîșț]|\s+(?:și|si)\s+vreau|[.!?]|$)/u', $message, $offerMatch) === 1) {
        $declaredOffer = trim((string) ($offerMatch[1] ?? ''));
    }
    $adsObjective = (string) ($conversationUnderstanding['active_objective'] ?? '');
    $adsLocation = '';
    $allUserSearchable = cabit_ai_searchable_text($baseUserContextualMessage);
    if (preg_match('/\bbucuresti\b/u', $allUserSearchable) === 1 && preg_match('/\bilfov\b/u', $allUserSearchable) === 1) {
        $adsLocation = ' în București și Ilfov';
    } elseif (preg_match('/\bbucuresti\b/u', $allUserSearchable) === 1) {
        $adsLocation = ' în București';
    } elseif (preg_match('/\bilfov\b/u', $allUserSearchable) === 1) {
        $adsLocation = ' în Ilfov';
    }
    if ($declaredOffer !== '') {
        $objectiveSentence = $adsObjective === 'phone_calls'
            ? 'Pentru că obiectivul este apelul, urmărim separat apelurile din anunț și cele inițiate din website, apoi optimizăm după solicitările reale, nu după clickuri.'
            : 'Măsurăm acțiunea comercială importantă și optimizăm după solicitări reale, nu doar după clickuri.';
        $reply['text'] = $reply['answer'] = 'Pentru ' . $declaredOffer . $adsLocation . ', campania Google Ads trebuie concentrată pe căutările locale cu intenție imediată, cu anunțuri clare, extensie de apel și o pagină dedicată serviciului. ' . $objectiveSentence;
        $reply['followup'] = 'Ai deja o pagină dedicată reparațiilor de trotinete pe website?';
    } else {
        $reply['text'] = $reply['answer'] = 'Da, te putem ajuta cu o campanie de ' . $platformLabel . '. Începem de la oferta și clienții potriviți, alegem căutările sau audiențele cu intenție reală, pregătim anunțurile și pagina de destinație, apoi configurăm trackingul. Optimizarea se face după apeluri, formulare sau vânzări reale, nu doar după clickuri.';
        $reply['followup'] = 'Ce serviciu sau produs vrei să promovezi și ce vrei să obții în primul rând?';
    }
    $reply['source'] = ['label' => 'Google Ads CAB-IT', 'url' => 'https://cab-it.ro/servicii/reclame-platite/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/reclame-platite/';
    $reply['actions'] = [];
    $reply['use_local_model'] = true;
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['declared_offer'] = $declaredOffer;
        $reply['context']['declared_objective'] = $adsObjective;
        $reply['context']['response_rule'] = 'Folosește serviciul și obiectivul deja declarate. Nu le întreba din nou. Continuă cu următorul detaliu lipsă și nu menționa prețul dacă nu a fost cerut.';
    }
}

if (($reply['intent'] ?? '') === 'seo_general') {
    $reply['text'] = $reply['answer'] = 'Da, te putem ajuta cu SEO. Mai întâi verificăm cum vede Google website-ul, ce caută clienții potriviți și ce pagini ar trebui să răspundă acelor căutări. Apoi prioritizăm problemele tehnice, structura, conținutul și SEO local, iar progresul îl măsurăm prin vizibilitate și cereri reale, fără promisiuni de poziții fixe.';
    $reply['followup'] = 'Ai deja un website? Dacă da, trimite-mi domeniul și spune-mi serviciul principal și localitatea în care lucrezi.';
    $reply['source'] = ['label' => 'SEO CAB-IT', 'url' => 'https://cab-it.ro/servicii/optimizare-seo/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/optimizare-seo/';
    $reply['actions'] = [];
    $reply['use_local_model'] = true;
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['response_rule'] = 'Răspunde natural și concret despre ajutorul SEO. Cere domeniul numai dacă nu a fost deja furnizat și nu inventa probleme înainte de audit.';
    }
}

if (($reply['intent'] ?? '') === 'ads_no_results' && ($reply['match_type'] ?? '') === 'conversation_rule') {
    $reply['text'] = $reply['answer'] = 'Dacă magazinul online există, dar promovarea nu produce rezultate, diagnosticul trebuie să separe patru etape: sursa și calitatea traficului, potrivirea dintre reclamă și ofertă, pagina de produs și checkoutul, apoi trackingul conversiilor. Nu recomand creșterea bugetului înainte să vedem unde se pierde utilizatorul.';
    $reply['followup'] = 'Pe ce canal promovezi și ce observi: trafic puțin, clickuri fără adăugări în coș sau coșuri fără comenzi?';
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['diagnostic_scope'] = ['traffic_source', 'audience_and_offer', 'product_and_checkout', 'conversion_tracking'];
    }
}
if (($reply['intent'] ?? '') === 'website_general'
    && ($reply['match_type'] ?? '') === 'conversation_rule'
    && count($currentTokens) <= 4
    && preg_match('/\b(ce tip de afacere|ce afacere|domeniul afacerii)\b/u', $previousAssistant) === 1
) {
    $businessLabel = trim($message);
    $reply['text'] = $reply['answer'] = 'Pentru acest tip de afacere (' . $businessLabel . '), website-ul ar trebui să pună în prim-plan produsele, comenzile sau precomenzile, livrarea ori ridicarea, programul și contactul rapid. Putem face un site de prezentare, un magazin online sau o soluție custom, în funcție de felul în care vrei să vinzi.';
    $reply['followup'] = 'Clienții trebuie să poată comanda și plăti online sau doar să vadă oferta și să te contacteze?';
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['business_type'] = $businessLabel;
    }
}

if (in_array((string) ($reply['intent'] ?? ''), ['website_general', 'website_custom'], true)
    && preg_match('/\b(ce trebuie sa poata face|ce vrei sa poata face|ce functionalitati|ce actiune face|pas cu pas)\b/u', $previousAssistant) === 1
    && preg_match('/\b(rezervare|rezervari|programare|programari)\b/u', $currentSearchable) === 1
) {
    $reply['text'] = $reply['answer'] = 'Da, putem include un sistem de rezervări online. Clientul poate alege serviciul, data și intervalul disponibil, își completează datele și primește confirmarea; tu poți administra programările și disponibilitatea dintr-un panou. Opțional, putem adăuga confirmări prin email sau WhatsApp, notificări, anulare/reprogramare și plată online.';
    $reply['followup'] = 'Rezervarea trebuie confirmată automat sau vrei să o aprobi înainte, după verificarea disponibilității?';
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['requested_functionality'] = 'online_booking';
        $reply['context']['response_rule'] = 'Explică fluxul concret de rezervare și continuă clarificarea cerinței. Nu repeta răspunsul tehnic anterior.';
    }
}

if (($reply['intent'] ?? '') === 'ecommerce_general' && ($reply['match_type'] ?? '') === 'conversation_rule') {
    $hasCurrentCarrier = preg_match('/\b(fan(?:\s+courier)?|sameday|easybox|cargus)\b/u', $currentSearchable) === 1;
    $answersCourierContractFollowup = preg_match('/\b(ai|aveti|exista).{0,40}\bcontract(?:ul)?(?:\s+activ)?\b|\bcontract(?:ul)?\s+activ\b/u', $previousAssistant) === 1
        && preg_match('/\b(da|nu|am|avem|deja|contract|activ)\b/u', $currentSearchable) === 1;
    $hasExplicitEcommerceRequest = preg_match('/\b(e commerce|ecommerce|magazin online|shop online)\b/u', $currentSearchable) === 1
        || ($productCount !== null && preg_match('/\b(produse|articole|sku)\b/u', $currentSearchable) === 1);
    $hasCurrentBriefDetails = preg_match('/\b([0-9]+\s*(?:de\s*)?(?:produse?|articole|sku)|produs|produse|sameday|easybox|fan courier|cargus|awb|curier)\b/u', $currentSearchable) === 1;
    $recentEcommerceContext = cabit_ai_searchable_text(cabit_ai_previous_user_message($history) . ' ' . $previousAssistant);
    $continuesCourierBrief = (
        $hasCurrentBriefDetails
        && (
            preg_match('/\b(cate produse|numar de produse|metode de plata|livrare|curier|catalog|comenzi)\b/u', $previousAssistant) === 1
            || $hasExplicitEcommerceRequest
        )
    ) || (
        $hasCurrentCarrier
        && preg_match('/\b(e commerce|ecommerce|magazin online|produse|catalog|checkout|curier|sameday|easybox|fan courier|cargus|awb)\b/u', $recentEcommerceContext) === 1
    );
    if ($answersCourierContractFollowup && is_array($requestedCarrier)) {
        $hasActiveContract = preg_match('/\b(nu|n am|nu am|fara)\b/u', $currentSearchable) !== 1;
        if ($hasActiveContract) {
            $reply['text'] = $reply['answer'] = 'Perfect. Dacă ai deja un contract activ cu ' . $requestedCarrier['name'] . ', putem configura integrarea pe baza credențialelor și documentației API disponibile în contul tău. Putem include ' . $requestedCarrier['capabilities'] . ', în limitele serviciilor activate în contract.';
            $reply['followup'] = 'Vrei să includem atât livrarea la adresă, cât și opțiunile de punct fix disponibile?';
        } else {
            $reply['text'] = $reply['answer'] = 'Integrarea cu ' . $requestedCarrier['name'] . ' este posibilă, însă pentru funcțiile automate va fi necesar un contract activ și accesul API oferit de curier. Putem pregăti magazinul și stabili exact pașii după ce confirmăm serviciile disponibile.';
            $reply['followup'] = 'Vrei să includem această integrare în ofertă și să clarificăm apoi accesul API cu ' . $requestedCarrier['name'] . '?';
        }
    } elseif ($continuesCourierBrief) {
        $catalogSubject = $productCount !== null ? 'un catalog de ' . $productCount . ' de produse' : 'catalogul descris';
        $replyText = 'Pentru ' . $catalogSubject . ', putem construi un magazin online ușor de administrat, cu categorii, variante, stoc, coș și checkout.';
        if ($shouldMentionEcommercePrice) {
            $replyText = 'Pentru ' . $catalogSubject . ', pachetul CAB-IT pornește de la ' . $ecommerceTier['price'] . '. Putem construi un magazin online ușor de administrat, cu categorii, variante, stoc, coș și checkout.';
        }
        if (preg_match('/\b(react|reactjs)\b/u', $currentSearchable) === 1) {
            $replyText .= ' Putem folosi React.js pentru interfață atunci când complexitatea proiectului îl justifică, împreună cu backendul și administrarea potrivite catalogului.';
        }
        if (is_array($requestedCarrier)) {
            $replyText .= ' Da, integrarea cu ' . $requestedCarrier['name'] . ' este posibilă și poate include ' . $requestedCarrier['capabilities'] . '; funcțiile exacte depind de serviciile, documentația și accesul API active în contractul tău cu ' . $requestedCarrier['name'] . '.';
            $reply['followup'] = 'Vrei livrare la adresă, punct fix sau ambele și ai deja un contract activ cu ' . $requestedCarrier['name'] . '?';
        } else {
            $reply['followup'] = 'Produsele au variante și vrei și plată online cu cardul, pe lângă ramburs?';
        }
        $reply['text'] = $reply['answer'] = $replyText;
    } else {
        $intro = 'Pentru proiectul de magazin online descris, recomandarea pornește de la catalog, opțiunile produselor, plata, livrarea sau ridicarea, stocul și fluxul comenzilor.';
        $reply['text'] = $reply['answer'] = $intro . "\n\n" . (string) $reply['text'];
    }
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['product_count'] = $productCount;
        if ($shouldMentionEcommercePrice) {
            $reply['context']['ecommerce_price_tier'] = $ecommerceTier;
        } else {
            unset($reply['context']['ecommerce_price_tier']);
        }
        if (is_array($requestedCarrier)) {
            $reply['context']['requested_carrier'] = $requestedCarrier;
        }
    }
}

if (in_array((string) ($reply['intent'] ?? ''), ['ecommerce_price', 'ecommerce_products'], true)) {
    if ($productCount === null) {
        $reply['text'] = $reply['answer'] = 'Pentru un magazin online, prețul pornește de la 1.799 lei fără TVA pentru un catalog sub 100 de produse, de la 2.399 lei fără TVA pentru 100–500 de produse și de la 3.199 lei fără TVA pentru peste 500 de produse. Integrările, importul, variantele și funcționalitățile speciale pot modifica oferta finală.';
    } else {
        $reply['text'] = $reply['answer'] = 'Pentru catalogul indicat, de ' . $productCount . ' de produse, pachetul CAB-IT pornește de la ' . $ecommerceTier['price'] . '. Acest prag corespunde intervalului „' . $ecommerceTier['label'] . '”; integrările, importul, variantele și funcționalitățile speciale pot modifica oferta finală.';
    }
    if (preg_match('/\b(react|reactjs)\b/u', $currentSearchable) === 1) {
        $reply['text'] = $reply['answer'] = rtrim((string) $reply['text']) . ' Putem folosi React.js pentru interfață atunci când complexitatea proiectului îl justifică, împreună cu backendul și administrarea potrivite catalogului.';
    }
    $reply['followup'] = 'Ce metode de plată, curier și integrări sunt necesare?';
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['product_count'] = $productCount;
        $reply['context']['ecommerce_price_tier'] = $ecommerceTier;
    }
}

$asksWebsiteProblems = preg_match('/\b(probleme|problemele|audit|analiza|analizeaza|verifica|verificare|erori)\b/u', $currentSearchable) === 1;
$hasCurrentWebsiteDomain = preg_match('/\b(?:https?:\/\/)?(?:www\.)?[a-z0-9][a-z0-9-]*(?:\.[a-z0-9-]+)+(?:\/[^\s]*)?/iu', $message, $requestedWebsiteMatch) === 1;
$conversationDomains = is_array($conversationUnderstanding['domains_mentioned'] ?? null) ? $conversationUnderstanding['domains_mentioned'] : [];
$usesWebsiteFromContext = preg_match('/\b(lui|acestui site|acestui website|site ul|website ul|domeniul|adresa de mai sus|cel de mai sus)\b/u', $currentSearchable) === 1;
$contextWebsiteDomain = !$hasCurrentWebsiteDomain && $usesWebsiteFromContext && $conversationDomains !== []
    ? (string) end($conversationDomains)
    : '';
$asksWebsiteProblemsForDomain = $asksWebsiteProblems && ($hasCurrentWebsiteDomain || $contextWebsiteDomain !== '');
if ($asksWebsiteProblemsForDomain) {
    $websiteLabel = $hasCurrentWebsiteDomain
        ? rtrim((string) ($requestedWebsiteMatch[0] ?? trim($message)), '.,;:!?)]}')
        : $contextWebsiteDomain;
    $reply['intent'] = 'website_audit_scope_request';
    $reply['title'] = 'Clarificare audit website';
    $reply['text'] = $reply['answer'] = 'Am notat adresa „' . $websiteLabel . '”. Pot începe cu un audit public al accesării și indexării, structurii, conținutului, performanței, experienței mobile și semnalelor de conversie. Pentru interogări, clickuri și acoperirea exactă a indexării este necesar acces autorizat la Google Search Console; simpla menționare a domeniului nu presupune că îți aparține.';
    $reply['followup'] = 'Este website-ul tău sau vrei doar o analiză publică a lui ca reper?';
    $reply['source'] = ['label' => 'Audit CAB-IT', 'url' => 'https://cab-it.ro/#audit'];
    $reply['source_url'] = 'https://cab-it.ro/#audit';
    $reply['actions'] = [['type' => 'source', 'label' => 'Pornește auditul gratuit', 'href' => 'https://cab-it.ro/#audit']];
    $reply['use_local_model'] = false;
    $reply['match_type'] = 'domain_audit_scope';
    $reply['context'] = [
        'intent' => 'website_audit_scope_request',
        'canonical_answer' => $reply['text'],
        'website_url_supplied' => $websiteLabel,
        'response_rule' => 'Clarifică dreptul și profunzimea analizei înainte de a afirma probleme concrete. Nu inventa rezultate de audit.',
        'source_url' => 'https://cab-it.ro/#audit',
    ];
}

if (($reply['intent'] ?? '') === 'website_portfolio') {
    $portfolioProjects = [
        ['name' => 'IFY.ro', 'description' => 'magazin online pentru produse printate 3D', 'groups' => ['ecommerce']],
        ['name' => 'Maison Bébé', 'description' => 'magazin online pentru un boutique premium', 'groups' => ['ecommerce']],
        ['name' => 'Auto La Domiciliu', 'description' => 'website de servicii, SEO și campanii plătite', 'groups' => ['presentation', 'marketing']],
        ['name' => 'Nanu Events', 'description' => 'website de prezentare pentru servicii de evenimente', 'groups' => ['presentation']],
        ['name' => 'Traffic Pub', 'description' => 'website, SEO local și promovare digitală', 'groups' => ['presentation', 'marketing']],
        ['name' => 'Best TKD', 'description' => 'website și administrare pentru programe sportive', 'groups' => ['presentation']],
        ['name' => 'Lael Fashion', 'description' => 'magazin online și structură SEO', 'groups' => ['ecommerce', 'marketing']],
        ['name' => 'Bilka Sistem', 'description' => 'website, audit SEO și campanii Google Ads', 'groups' => ['presentation', 'marketing']],
    ];
    $portfolioFilter = 'all';
    if (preg_match('/\b(e commerce|ecommerce|magazine online)\b/u', $currentSearchable) === 1) {
        $portfolioFilter = 'ecommerce';
    } elseif (preg_match('/\b(seo|promovare)\b/u', $currentSearchable) === 1) {
        $portfolioFilter = 'marketing';
    } elseif (preg_match('/\bprezentare\b/u', $currentSearchable) === 1) {
        $portfolioFilter = 'presentation';
    }
    $visibleProjects = $portfolioFilter === 'all' ? $portfolioProjects : array_values(array_filter(
        $portfolioProjects,
        static fn (array $project): bool => in_array($portfolioFilter, $project['groups'], true)
    ));
    $projectLines = array_map(static fn (array $project): string => '• ' . $project['name'] . ' — ' . $project['description'], $visibleProjects);
    $filterTitle = match ($portfolioFilter) {
        'ecommerce' => 'proiectele e-commerce publice realizate de CAB-IT',
        'marketing' => 'proiectele publice CAB-IT care includ SEO sau promovare',
        'presentation' => 'website-urile de prezentare publice realizate de CAB-IT',
        default => 'câteva proiecte publice realizate de CAB-IT',
    };
    $reply['text'] = $reply['answer'] = "Sigur. Iată " . $filterTitle . ":\n\n"
        . implode("\n", $projectLines)
        . "\n\nPoți deschide direct fiecare website din butoanele de mai jos.";
    $reply['followup'] = 'Vrei să-ți arăt doar proiectele e-commerce, website-urile de prezentare sau proiectele cu SEO și promovare?';
    $allowedProjectNames = array_column($visibleProjects, 'name');
    $reply['actions'] = array_values(array_filter(
        cabit_ai_reply_actions('website_portfolio'),
        static fn (array $action): bool => ($action['type'] ?? '') === 'source' || in_array((string) ($action['label'] ?? ''), $allowedProjectNames, true)
    ));
    $reply['use_local_model'] = true;
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['portfolio_links'] = $reply['actions'];
    }
}

if (in_array((string) ($reply['intent'] ?? ''), ['seo_general', 'conversation_followup_answer'], true)
    && preg_match('/\b(ai deja un website|ai deja un site|care este url|care e url|adresa website|adresa site)\b/u', $previousAssistant) === 1
    && preg_match('/\b(?:https?:\/\/)?(?:www\.)?[a-z0-9][a-z0-9-]*(?:\.[a-z0-9-]+)+(?:\/[^\s]*)?/iu', $message) === 1
) {
    preg_match('/\b(?:https?:\/\/)?(?:www\.)?[a-z0-9][a-z0-9-]*(?:\.[a-z0-9-]+)+(?:\/[^\s]*)?/iu', $message, $websiteMatch);
    $websiteLabel = rtrim((string) ($websiteMatch[0] ?? trim($message)), '.,;:!?)]}');
    $confirmsOwnership = preg_match('/\b(da|am|al meu|site ul meu|website ul meu)\b/u', $currentSearchable) === 1;
    $reply['intent'] = 'seo_website_received';
    $reply['title'] = 'Website primit pentru SEO';
    $reply['text'] = $reply['answer'] = ($confirmsOwnership ? 'Perfect, am notat „' . $websiteLabel . '” ca website-ul tău. ' : 'Am notat adresa „' . $websiteLabel . '”. ')
        . 'Pentru a continua SEO corect, primul pas este un audit public al accesării și indexării, structurii, conținutului, performanței și semnalelor on-page. Nu voi presupune probleme înainte de verificare; pentru interogări, clickuri și acoperirea exactă a indexării este necesar acces autorizat la Google Search Console.';
    $reply['followup'] = 'Vrei să începem cu auditul public SEO al domeniului?';
    $reply['source'] = ['label' => 'Audit SEO CAB-IT', 'url' => 'https://cab-it.ro/#audit'];
    $reply['source_url'] = 'https://cab-it.ro/#audit';
    $reply['actions'] = [['type' => 'source', 'label' => 'Pornește auditul gratuit', 'href' => 'https://cab-it.ro/#audit']];
    $reply['match_type'] = 'conversation_domain_entity';
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['intent'] = 'seo_website_received';
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['website_url_supplied'] = $websiteLabel;
        $reply['context']['response_rule'] = 'Domeniul este răspunsul clientului la întrebarea despre website. Confirmă-l și continuă SEO pe acel domeniu; nu răspunde despre mecanica conversației și nu inventa rezultate de audit.';
    }
}

$previousAskedTechnicalSetup = preg_match('/\b(?:ga4|google tag manager|gtm|tracking|configurat|configurate)\b/u', $previousAssistant) === 1;
$previousAskedAuditStart = preg_match('/\b(?:vrei sa incepem|incepem cu auditul|auditul public|pornim auditul)\b/u', $previousAssistant) === 1;
if (($reply['intent'] ?? '') === 'conversation_followup_answer'
    && ($isAffirmativeFollowup || $isNegativeFollowup)
    && !$previousAskedTechnicalSetup
    && !$answersWebsiteAuditScope
) {
    $subjectsDiscussed = is_array($conversationUnderstanding['subjects_discussed'] ?? null)
        ? $conversationUnderstanding['subjects_discussed']
        : [];
    $activeSubject = (string) ($conversationUnderstanding['active_subject'] ?? '');
    $conversationDomains = is_array($conversationUnderstanding['domains_mentioned'] ?? null)
        ? $conversationUnderstanding['domains_mentioned']
        : [];
    $knownDomain = $conversationDomains !== [] ? (string) end($conversationDomains) : '';

    if ($isNegativeFollowup) {
        $reply['text'] = $reply['answer'] = 'În regulă. Nu continui pe varianta aceea. Spune-mi ce vrei să schimbăm sau care este rezultatul pe care îl urmărești acum și adaptez recomandarea de acolo.';
        $reply['followup'] = '';
    } elseif ($previousAskedAuditStart || $activeSubject === 'seo' || in_array('seo', $subjectsDiscussed, true)) {
        if ($knownDomain !== '') {
            $reply['text'] = $reply['answer'] = 'Perfect. Continuăm cu evaluarea SEO pentru „' . $knownDomain . '”. Auditul public ne arată ce poate fi verificat fără acces, iar pentru interogări, clickuri și acoperirea exactă a indexării vom folosi ulterior datele autorizate din Search Console.';
            $reply['followup'] = 'Care este serviciul principal și localitatea pentru care vrei să atragi clienți?';
        } else {
            $reply['text'] = $reply['answer'] = 'Perfect. Începem cu evaluarea SEO, dar am nevoie de domeniul website-ului ca să verificăm situația reală și să nu ghicim.';
            $reply['followup'] = 'Care este adresa website-ului?';
        }
    } elseif ($activeSubject === 'paid_ads' || in_array('paid_ads', $subjectsDiscussed, true)) {
        $reply['text'] = $reply['answer'] = 'Perfect. Atunci continuăm cu campania. Ca să alegem structura potrivită, trebuie să legăm oferta de obiectivul măsurabil și de pagina pe care ajunge clientul.';
        $reply['followup'] = 'Ce serviciu sau produs promovezi și urmărești apeluri, formulare ori vânzări?';
    } else {
        $reply['text'] = $reply['answer'] = 'Perfect. Continui de la ce am stabilit și trecem la următorul pas concret.';
        $reply['followup'] = 'Care este detaliul principal pe care vrei să-l stabilim acum?';
    }
    $reply['use_local_model'] = true;
    $reply['actions'] = [];
    if (isset($reply['context']) && is_array($reply['context'])) {
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['response_rule'] = 'Interpretează confirmarea în contextul întregii conversații și continuă concret. Nu spune că păstrezi răspunsul sau că adaptezi conversația.';
    }
}

$answersEcommerceDeliveryChoice = preg_match('/\b(?:atat|ambele|livrarea la adresa|punct fix|easybox)\b/u', $previousAssistant) === 1
    && preg_match('/\b(?:vrei|includem|alegi|preferi)\b/u', $previousAssistant) === 1
    && $isAffirmativeFollowup;
if ($answersEcommerceDeliveryChoice) {
    $carrierForHandoff = cabit_ai_requested_carrier($message, $history);
    $carrierLabel = is_array($carrierForHandoff) ? (string) ($carrierForHandoff['name'] ?? 'curierul ales') : 'curierul ales';
    $catalogPhrase = $productCount !== null ? ' pentru catalogul de ' . $productCount . ' de produse' : '';
    $reply['intent'] = 'ecommerce_brief_ready';
    $reply['title'] = 'Brief pregătit pentru specialist';
    $reply['text'] = $reply['answer'] = 'Perfect, includem ambele variante: livrare la adresă și punct fix sau easybox, în funcție de serviciile active în contractul tău cu ' . $carrierLabel . '. Avem acum informațiile esențiale' . $catalogPhrase . ', integrarea de curier și opțiunile de livrare. Un specialist CAB-IT poate verifica accesul API, confirma configurația și pregăti oferta finală.';
    $reply['followup'] = 'Poți suna acum pentru a continua direct cu proiectul.';
    $reply['source'] = ['label' => 'Specialist CAB-IT', 'url' => 'https://cab-it.ro/contact/'];
    $reply['source_url'] = 'https://cab-it.ro/contact/';
    $reply['actions'] = [['type' => 'call', 'kind' => 'call', 'label' => 'Sună acum', 'href' => 'tel:+40771532949']];
    $reply['use_local_model'] = true;
    $reply['match_type'] = 'qualified_handoff';
    $reply['context'] = [
        'intent' => 'ecommerce_brief_ready',
        'canonical_answer' => $reply['text'],
        'product_count' => $productCount,
        'requested_carrier' => $carrierForHandoff,
        'delivery_choice' => 'address_and_pickup_point',
        'response_rule' => 'Confirmă alegerea ambelor variante de livrare și recomandă discuția cu specialistul. Nu repeta întrebările deja clarificate.',
        'source_url' => 'https://cab-it.ro/contact/',
        'conversation_history' => $history,
    ];
}

$asksAboutShopify = preg_match('/\bshopify\b/u', $currentSearchable) === 1;
if ($asksAboutShopify) {
    $reply['intent'] = 'website_shopify';
    $reply['title'] = 'Website pe Shopify';
    $reply['text'] = $reply['answer'] = 'Da, CAB-IT poate realiza un website pe Shopify. Platforma este potrivită în special pentru un magazin online în care vrei să administrezi simplu produsele, comenzile, plățile și livrarea. Înainte de implementare stabilim catalogul, variantele de produs, metodele de plată, curierii și integrările necesare, apoi alegem dacă este suficientă o temă configurată corect sau proiectul are nevoie de personalizare.';
    $reply['followup'] = 'Este un magazin online nou sau ai deja un cont Shopify și aproximativ câte produse vei avea?';
    $reply['source'] = ['label' => 'Creare website CAB-IT', 'url' => 'https://cab-it.ro/servicii/creare-site-web/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/creare-site-web/';
    $reply['actions'] = [];
    $reply['confidence'] = 0.99;
    $reply['match_type'] = 'platform_capability';
    $reply['use_local_model'] = false;
    if (!isset($reply['context']) || !is_array($reply['context'])) {
        $reply['context'] = [];
    }
    $reply['context']['intent'] = 'website_shopify';
    $reply['context']['canonical_answer'] = $reply['text'];
    $reply['context']['platform'] = 'Shopify';
    $reply['context']['response_rule'] = 'Confirmă direct capabilitatea Shopify și explică pe scurt criteriile reale de implementare. Nu devia către stack-ul custom decât dacă este cerut.';
}

if (cabit_ai_is_local_visibility_request($currentSearchable)) {
    $localProfile = cabit_ai_local_business_profile($baseUserContextualMessage);
    $shopifyInContext = preg_match('/\bshopify\b/u', cabit_ai_searchable_text($baseUserContextualMessage)) === 1;
    $platformSentence = $shopifyInContext
        ? 'Dacă website-ul va fi construit în Shopify, optimizăm în continuare paginile, datele locale și legătura cu profilul Google; nu trebuie schimbată platforma doar pentru SEO local.'
        : 'Pe website optimizăm paginile relevante, datele locale și legătura cu profilul Google, fără pagini artificiale în care se schimbă doar numele orașului.';
    $reply['intent'] = 'seo_local_business';
    $reply['title'] = 'Vizibilitate locală pentru afacere';
    $reply['text'] = $reply['answer'] = 'Da. Pentru ' . $localProfile['reference'] . ', vizibilitatea locală trebuie construită în jurul oamenilor din apropiere care caută ' . $localProfile['searches'] . '. Primul pas este un profil Google Business complet și corect, cu adresă, program, categorie, produse sau servicii, fotografii reale și recenzii. ' . $platformSentence . ' Măsurăm ' . $localProfile['conversions'] . ', nu doar afișările.';
    $reply['followup'] = 'În ce localitate și zonă se află afacerea și ai deja un profil Google Business?';
    $reply['source'] = ['label' => 'SEO local CAB-IT', 'url' => 'https://cab-it.ro/servicii/seo-local/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/seo-local/';
    $reply['actions'] = [];
    $reply['confidence'] = 0.99;
    $reply['match_type'] = 'contextual_local_visibility';
    $reply['use_local_model'] = false;
    if (!isset($reply['context']) || !is_array($reply['context'])) {
        $reply['context'] = [];
    }
    $reply['context']['intent'] = 'seo_local_business';
    $reply['context']['canonical_answer'] = $reply['text'];
    $reply['context']['local_business_profile'] = $localProfile;
    $reply['context']['response_rule'] = 'Prioritizează obiectivul local explicit din ultima replică. Folosește industria declarată și contextul platformei, fără să repeți întrebări la care clientul a răspuns deja.';
}

$activeSubjectForLocalFollowup = (string) ($conversationUnderstanding['active_subject'] ?? '');
$previousAskedLocalSeoSetup = preg_match(
    '/\b(?:in ce localitate|localitate si zona|ai deja un profil google(?: my)? business|profil google(?: my)? business)\b/u',
    $previousAssistant
) === 1;
$mentionsGoogleProfile = preg_match('/\b(?:profil|google(?: my)? business|business profile)\b/u', $currentSearchable) === 1;
$hasNoGoogleProfile = ($mentionsGoogleProfile && preg_match(
    '/\b(?:nu\s+(?:am|avem|exista)|inca\s+nu|fara)\b.{0,70}\b(?:profil|google(?: my)? business|business profile)\b/u',
    $currentSearchable
) === 1) || ($previousAskedLocalSeoSetup && preg_match('/\b(?:nu\s+(?:il\s+)?(?:am|avem)|inca\s+nu|nu\s+exista)\b/u', $currentSearchable) === 1);
$hasGoogleProfile = !$hasNoGoogleProfile && (($mentionsGoogleProfile && preg_match(
    '/\b(?:da|am|avem|exista)\b.{0,70}\b(?:profil|google(?: my)? business|business profile)\b/u',
    $currentSearchable
) === 1) || ($previousAskedLocalSeoSetup && preg_match('/\b(?:da[, ]+)?(?:il\s+)?(?:am|avem)(?:\s+deja)?\b/u', $currentSearchable) === 1));
$localityFromFollowup = cabit_ai_locality_from_message($message);
$answersLocalSeoSetup = $history !== []
    && $activeSubjectForLocalFollowup === 'seo'
    && $previousAskedLocalSeoSetup
    && ($localityFromFollowup !== '' || $mentionsGoogleProfile);

if ($answersLocalSeoSetup) {
    $localProfile = cabit_ai_local_business_profile($baseUserContextualMessage);
    $localityPhrase = $localityFromFollowup !== '' ? ' din ' . $localityFromFollowup : '';
    $reply['intent'] = 'seo_local_profile_setup';
    $reply['title'] = 'Configurare SEO local și Google Business';

    if ($hasNoGoogleProfile) {
        $reply['text'] = $reply['answer'] = 'Pentru ' . $localProfile['reference'] . $localityPhrase . ', faptul că nu există încă un profil Google Business înseamnă că pornim cu baza: îl creăm sau îl revendicăm, alegem categoria corectă, adăugăm adresa, programul, telefonul, website-ul, produsele importante și fotografii reale, apoi finalizăm verificarea. După aceea îl legăm de website și optimizăm paginile pentru căutările locale relevante, astfel încât profilul și site-ul să transmită aceleași informații.';
        $reply['followup'] = $localityFromFollowup === 'București'
            ? 'În ce sector sau cartier se află cofetăria și clienții pot ridica produsele de la locație ori lucrezi doar cu livrare?'
            : 'Care este zona exactă și clienții pot veni la locație ori lucrezi doar cu livrare sau deplasare?';
    } elseif ($hasGoogleProfile) {
        $reply['text'] = $reply['answer'] = 'Am notat că ' . $localProfile['reference'] . $localityPhrase . ' are deja un profil Google Business. Următorul pas este să verificăm categoria, adresa și zona deservită, programul, produsele, fotografiile, recenziile și legătura spre website, apoi să le aliniem cu paginile locale ale site-ului. Așa putem vedea ce lipsește înainte să schimbăm conținutul sau să publicăm pagini noi.';
        $reply['followup'] = 'Trimite linkul profilului Google Business și spune-mi sectorul sau zona principală pe care vrei să o acoperi.';
    } else {
        $locationStatement = $localityFromFollowup !== '' ? 'Am notat localitatea: ' . $localityFromFollowup . '. ' : '';
        $reply['text'] = $reply['answer'] = $locationStatement . 'Pentru ' . $localProfile['reference'] . ', următorul pas este să stabilim dacă profilul Google Business există deja și ce zonă deservește, apoi îl aliniem cu website-ul, produsele, programul și datele de contact.';
        $reply['followup'] = 'Ai deja un profil Google Business și care este sectorul, cartierul sau zona principală?';
    }

    $reply['source'] = ['label' => 'SEO local CAB-IT', 'url' => 'https://cab-it.ro/servicii/seo-local/'];
    $reply['source_url'] = 'https://cab-it.ro/servicii/seo-local/';
    $reply['actions'] = [];
    $reply['confidence'] = 0.99;
    $reply['match_type'] = 'conversation_local_seo_setup';
    $reply['use_local_model'] = false;
    if (!isset($reply['context']) || !is_array($reply['context'])) {
        $reply['context'] = [];
    }
    $reply['context']['intent'] = 'seo_local_profile_setup';
    $reply['context']['canonical_answer'] = $reply['text'];
    $reply['context']['locality'] = $localityFromFollowup;
    $reply['context']['google_business_profile'] = $hasNoGoogleProfile ? 'missing' : ($hasGoogleProfile ? 'existing' : 'unknown');
    $reply['context']['local_business_profile'] = $localProfile;
    $reply['context']['response_rule'] = 'Extrage localitatea și starea profilului Google Business din răspunsul clientului, păstrează industria și oferă următorul pas concret. Nu confirma mecanic și nu repeta întrebarea anterioară.';
}

$needsSpecialistBecauseUnclear = (($reply['match_type'] ?? '') === 'fallback' && (float) ($reply['confidence'] ?? 0) <= 0.45)
    || ($reply['intent'] ?? '') === 'conversation_repair';
if ($needsSpecialistBecauseUnclear) {
    $reply['text'] = $reply['answer'] = 'Nu sunt suficient de sigur că am înțeles corect ce vrei și nu vreau să-ți dau un răspuns ales la întâmplare. Poți reformula în câteva cuvinte, iar dacă preferi să rezolvi direct, poți discuta acum cu un specialist CAB-IT.';
    $reply['followup'] = 'Spune-mi rezultatul urmărit sau folosește butonul de apel.';
    $reply['source'] = ['label' => 'Specialist CAB-IT', 'url' => 'https://cab-it.ro/contact/'];
    $reply['source_url'] = 'https://cab-it.ro/contact/';
    $reply['actions'] = [['type' => 'call', 'kind' => 'call', 'label' => 'Sună acum', 'href' => 'tel:+40771532949']];
    $reply['use_local_model'] = false;
    $reply['match_type'] = 'specialist_handoff_unclear';
    if (!isset($reply['context']) || !is_array($reply['context'])) {
        $reply['context'] = [];
    }
    $reply['context']['canonical_answer'] = $reply['text'];
    $reply['context']['response_rule'] = 'Nu presupune intenția. Oferă o reformulare simplă sau contactul direct cu un specialist CAB-IT.';
}

$siteFragments = [];
$preferredArticleFragment = null;
$includeOfficialReference = false;
$siteSearchExcludedIntents = ['contact', 'website_price', 'pricing_general', 'ads_price', 'ecommerce_price', 'ecommerce_brief_ready', 'conversation_repair', 'not_sure'];
$skipSiteSearch = in_array((string) ($reply['intent'] ?? ''), $siteSearchExcludedIntents, true)
    || ($reply['match_type'] ?? '') === 'specialist_handoff_unclear';
if ($pdo instanceof PDO && !$skipSiteSearch) {
    try {
        $activeSubjectForSearch = (string) ($conversationUnderstanding['active_subject'] ?? '');
        $preferredArticleUrl = cabit_ai_preferred_article_url(
            (string) ($reply['intent'] ?? ''),
            $activeSubjectForSearch,
            $userContextualMessage
        );
        $preferredArticleFragment = cabit_ai_site_article_fragment($pdo, $preferredArticleUrl);
        $seoNeedsTechnicalDiagnosis = preg_match(
            '/\b(nu apare|nu (?:este|e) in google|neindexat|indexare|url inspection|search console|crawl|sitemap|robots|canonical)\b/u',
            cabit_ai_searchable_text($userContextualMessage)
        ) === 1;
        $includeOfficialReference = $seoNeedsTechnicalDiagnosis;
        $siteTopicExpansion = match (true) {
            $explicitArticleRequest && preg_match('/\bseo\b/u', $messageSearchable) === 1
                => 'optimizare SEO pentru site audit SEO indexare conținut ghid practic',
            $explicitArticleRequest && preg_match('/\b(?:google ads|reclame|promovare)\b/u', $messageSearchable) === 1
                => 'Google Ads campanii conversii tracking ghid practic',
            $seoNeedsTechnicalDiagnosis
                => 'audit SEO Search Console indexare robots canonical sitemap conținut',
            in_array((string) ($reply['intent'] ?? ''), ['seo_general', 'seo_website_received'], true) || $activeSubjectForSearch === 'seo'
                => 'SEO local afacere servicii clienți pagini servicii Google Business',
            str_contains((string) ($reply['intent'] ?? ''), 'ads') || $activeSubjectForSearch === 'paid_ads'
                => 'Google Ads campanie conversii tracking landing page',
            str_contains((string) ($reply['intent'] ?? ''), 'ecommerce') || $activeSubjectForSearch === 'ecommerce'
                => 'magazin online catalog produse checkout plată livrare',
            str_contains((string) ($reply['intent'] ?? ''), 'website') || $activeSubjectForSearch === 'website'
                => 'creare website structură pagini formular design responsive',
            in_array($activeSubjectForSearch, ['tracking_analytics', 'conversions'], true)
                => 'GA4 Google Tag Manager tracking conversii măsurare',
            default => (string) ($reply['title'] ?? ''),
        };
        $siteSearchText = $userContextualMessage . ' ' . $siteTopicExpansion;
        $siteFragments = cabit_ai_site_content_fragments($pdo, cabit_ai_search_tokens($siteSearchText));
        if (is_array($preferredArticleFragment)) {
            $preferredUrl = (string) ($preferredArticleFragment['source_url'] ?? '');
            $siteFragments = array_values(array_filter(
                $siteFragments,
                static fn (array $fragment): bool => (string) ($fragment['source_url'] ?? '') !== $preferredUrl
                    || ($fragment['source_type'] ?? '') === 'official_reference'
            ));
            array_unshift($siteFragments, $preferredArticleFragment);
        }
    } catch (Throwable) {
        $siteFragments = [];
        $preferredArticleFragment = null;
    }
}
if (!isset($reply['context']) || !is_array($reply['context'])) {
    $reply['context'] = [];
}
if (is_array($prospect10bMatch)) {
    $reply['context']['prospect_profile'] = [
        'dataset' => 'CAB-IT 10B virtual prospect Q&A',
        'answer_ref' => (string) ($prospect10bMatch['answer_ref'] ?? ''),
        'business_type' => (string) ($prospect10bMatch['business_type'] ?? ''),
        'state_id' => (string) ($prospect10bMatch['state_id'] ?? ''),
        'scenario' => (string) ($prospect10bMatch['scenario'] ?? ''),
        'intent_id' => (string) ($prospect10bMatch['intent_id'] ?? ''),
        'intent_title' => (string) ($prospect10bMatch['intent_title'] ?? ''),
        'confidence' => (float) ($prospect10bMatch['confidence'] ?? 0),
    ];
}
$existingFragments = is_array($reply['context']['retrieval_fragments'] ?? null)
    ? $reply['context']['retrieval_fragments']
    : [];
$highestExistingPriority = 0;
foreach ($existingFragments as $existingFragment) {
    $highestExistingPriority = max($highestExistingPriority, (int) ($existingFragment['priority'] ?? 0));
}
$controlledCommercialIntents = ['contact', 'website_price', 'pricing_general', 'ads_price', 'ecommerce_price'];
$canonicalPriority = $highestExistingPriority >= 100 || in_array((string) ($reply['intent'] ?? ''), $controlledCommercialIntents, true) ? 100 : 95;
$canonicalType = $canonicalPriority === 100 ? 'commercial_core' : 'canonical_answer';
$combinedFragments = [[
    'priority' => $canonicalPriority,
    'source_type' => $canonicalType,
    'source_url' => (string) ($reply['source_url'] ?? 'https://cab-it.ro/servicii/'),
    'title' => (string) ($reply['title'] ?? 'Răspuns CAB-IT'),
    'heading' => 'Răspuns canonic verificat',
    'text' => mb_substr((string) ($reply['text'] ?? ''), 0, 900, 'UTF-8'),
]];

if ($canonicalPriority === 100) {
    foreach ($existingFragments as $existingFragment) {
        if ((int) ($existingFragment['priority'] ?? 0) === 100
            && trim((string) ($existingFragment['text'] ?? '')) !== ''
            && trim((string) ($existingFragment['text'] ?? '')) !== trim((string) ($reply['text'] ?? ''))
        ) {
            $combinedFragments[] = $existingFragment;
            break;
        }
    }
}
$cabitSiteFragments = array_values(array_filter($siteFragments, static fn (array $fragment): bool => ($fragment['source_type'] ?? '') !== 'official_reference'));
$officialSiteFragments = array_values(array_filter($siteFragments, static fn (array $fragment): bool => ($fragment['source_type'] ?? '') === 'official_reference'));
$siteArticleLimit = is_array($prospect10bMatch) ? 2 : 3;
foreach (array_slice($cabitSiteFragments, 0, $siteArticleLimit) as $siteFragment) {
    $combinedFragments[] = $siteFragment;
}
if (is_array($prospect10bMatch)) {
    $combinedFragments[] = [
        'priority' => 94,
        'source_type' => 'prospect_canonical_10b',
        'source_url' => 'https://cab-it.ro/servicii/',
        'title' => (string) ($prospect10bMatch['intent_title'] ?? 'Context prospect CAB-IT'),
        'heading' => (string) ($prospect10bMatch['scenario'] ?? ''),
        'text' => mb_substr((string) ($prospect10bMatch['canonical_answer_long'] ?? ''), 0, 1000, 'UTF-8'),
        'answer_ref' => (string) ($prospect10bMatch['answer_ref'] ?? ''),
    ];
}
if ($includeOfficialReference) {
    foreach (array_slice($officialSiteFragments, 0, 1) as $officialFragment) {
        $combinedFragments[] = $officialFragment;
    }
}
foreach ($existingFragments as $existingFragment) {
    if (count($combinedFragments) >= 5) {
        break;
    }
    $combinedFragments[] = $existingFragment;
}

$uniqueFragments = [];
$seenFragments = [];
foreach ($combinedFragments as $fragment) {
    $textKey = trim(cabit_ai_searchable_text((string) ($fragment['text'] ?? '')));
    $urlKey = trim((string) ($fragment['source_url'] ?? ''));
    if ($textKey === '') {
        continue;
    }
    $key = hash('sha256', $urlKey . "\n" . mb_substr($textKey, 0, 260, 'UTF-8'));
    if (isset($seenFragments[$key])) {
        continue;
    }
    $seenFragments[$key] = true;
    $uniqueFragments[] = $fragment;
    if (count($uniqueFragments) >= 5) {
        break;
    }
}
$reply['context']['retrieval_fragments'] = $uniqueFragments;
$reply['context']['retrieval_pipeline'] = [
    'canonical_answer' => 1,
    'source_priority' => ['commercial_core' => 100, 'cab_it_articles_and_pages' => 95, 'official_documentation' => 90],
    'semantic_model' => 'SQLite FTS5 + reguli conversaționale CAB-IT',
    'generator' => 'răspuns canonic contextual',
    'prospect_dataset' => 'CAB-IT compact normalizat',
];

$articleRecommendations = [];
foreach ($siteFragments as $fragment) {
    if (($fragment['source_type'] ?? '') !== 'cab_it_article') {
        continue;
    }
    $title = trim((string) ($fragment['title'] ?? ''));
    $url = trim((string) ($fragment['source_url'] ?? ''));
    if ($title === '' || $url === '') {
        continue;
    }
    $articleRecommendations[$url] = ['title' => $title, 'url' => $url];
    if (count($articleRecommendations) >= ($explicitArticleRequest ? 3 : 1)) {
        break;
    }
}
$excludedRecommendationIntents = $siteSearchExcludedIntents;
if ($articleRecommendations !== [] && !in_array((string) ($reply['intent'] ?? ''), $excludedRecommendationIntents, true)) {
    if ($explicitArticleRequest) {
        $articleList = array_values($articleRecommendations);
        $reply['text'] = $reply['answer'] = count($articleList) === 1
            ? 'Am găsit un articol CAB-IT relevant pentru subiectul cerut: „' . (string) $articleList[0]['title'] . '”.'
            : 'Am găsit ' . count($articleList) . ' articole CAB-IT relevante pentru subiectul cerut. Le poți deschide direct de mai jos.';
        $reply['intent'] = 'article_search';
        $reply['title'] = 'Articole CAB-IT recomandate';
        $reply['followup'] = '';
        $reply['source'] = ['label' => (string) $articleList[0]['title'], 'url' => (string) $articleList[0]['url']];
        $reply['source_url'] = (string) $articleList[0]['url'];
        $reply['actions'] = [];
        $reply['use_local_model'] = false;
        $reply['match_type'] = 'article_search';
        $reply['context']['canonical_answer'] = $reply['text'];
        $reply['context']['response_rule'] = 'Răspunde direct cu articole CAB-IT relevante. Nu transforma cererea de lectură într-o calificare comercială.';
    }
    $reply['context']['recommended_articles'] = array_values($articleRecommendations);
    $existingActions = is_array($reply['actions'] ?? null) ? $reply['actions'] : [];
    foreach (array_values($articleRecommendations) as $article) {
        $label = 'Citește: ' . mb_substr((string) $article['title'], 0, 68, 'UTF-8');
        $existingActions[] = ['type' => 'article', 'kind' => 'article', 'label' => $label, 'href' => (string) $article['url']];
    }
    $reply['actions'] = $existingActions;
}

// Câmpurile de nivel superior păstrează compatibilitatea cu adaptoare simple;
// obiectul `reply` este contractul complet pentru interfața asistentului.
if (!isset($reply['context']) || !is_array($reply['context'])) {
    $reply['context'] = [];
}
$reply['context']['conversation_understanding'] = $conversationUnderstanding;
$reply['context']['conversation_history'] = $history;
$reply['use_local_model'] = false;
$compactResponse = trim((string) ($_SERVER['HTTP_X_CABIT_COMPACT'] ?? '')) === '1';
cabit_ai_json_response(200, $compactResponse
    ? ['ok' => true, 'reply' => $reply]
    : array_merge(['ok' => true, 'reply' => $reply], $reply));
