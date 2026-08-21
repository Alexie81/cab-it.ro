<?php
declare(strict_types=1);

$sourceDirectory = $argv[1] ?? sys_get_temp_dir();
$sourceDirectory = rtrim((string) $sourceDirectory, "\\/");
$scenarioPath = $sourceDirectory . DIRECTORY_SEPARATOR . 'CAB_IT_1000_SCENARII_PROSPECTI_10B.json';
$intentPath = $sourceDirectory . DIRECTORY_SEPARATOR . 'CAB_IT_100_INTENTII_PROSPECTI_10B.json';
$answerPath = $sourceDirectory . DIRECTORY_SEPARATOR . 'CAB_IT_100000_RASPUNSURI_CANONICE_10B.jsonl';
foreach ([$scenarioPath, $intentPath, $answerPath] as $requiredPath) {
    if (!is_file($requiredPath)) {
        throw new RuntimeException('Lipsește fișierul: ' . $requiredPath);
    }
}

$root = realpath(dirname(__DIR__));
if ($root === false) {
    throw new RuntimeException('Nu am putut identifica rădăcina website-ului.');
}
$storage = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'ai';
$target = $storage . DIRECTORY_SEPARATOR . 'CAB_IT_VIRTUAL_10B_PROSPECTS.sqlite3';
$temporary = $target . '.building';
if (is_file($temporary)) {
    unlink($temporary);
}

$scenarios = json_decode((string) file_get_contents($scenarioPath), true, 512, JSON_THROW_ON_ERROR);
$intents = json_decode((string) file_get_contents($intentPath), true, 512, JSON_THROW_ON_ERROR);
if (!is_array($scenarios) || count($scenarios) !== 1000 || !is_array($intents) || count($intents) !== 100) {
    throw new RuntimeException('Numărul de scenarii sau intenții nu corespunde manifestului 10B.');
}

$pdo = new PDO('sqlite:' . $temporary, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA page_size = 8192');
$pdo->exec('PRAGMA journal_mode = OFF');
$pdo->exec('PRAGMA synchronous = OFF');
$pdo->exec('PRAGMA temp_store = MEMORY');
$pdo->exec('CREATE TABLE metadata (key TEXT PRIMARY KEY, value TEXT NOT NULL)');
$pdo->exec('CREATE TABLE prospect_scenarios (
    scenario_id TEXT PRIMARY KEY,
    business_type TEXT NOT NULL,
    state_id TEXT NOT NULL,
    scenario TEXT NOT NULL
)');
$pdo->exec('CREATE TABLE client_intents (
    intent_id TEXT PRIMARY KEY,
    title TEXT NOT NULL,
    category TEXT NOT NULL
)');
$pdo->exec('CREATE TABLE canonical_answers (
    answer_ref TEXT PRIMARY KEY,
    scenario_id TEXT NOT NULL,
    intent_id TEXT NOT NULL,
    business_type TEXT NOT NULL,
    state_id TEXT NOT NULL,
    scenario TEXT NOT NULL,
    intent_title TEXT NOT NULL,
    category TEXT NOT NULL,
    canonical_answer_long TEXT NOT NULL,
    UNIQUE(scenario_id, intent_id),
    FOREIGN KEY(scenario_id) REFERENCES prospect_scenarios(scenario_id),
    FOREIGN KEY(intent_id) REFERENCES client_intents(intent_id)
) WITHOUT ROWID');
$pdo->exec("CREATE VIRTUAL TABLE scenarios_fts USING fts5(
    scenario_id UNINDEXED,
    business_type,
    state_id,
    scenario,
    tokenize='unicode61 remove_diacritics 2'
)");
$pdo->exec("CREATE VIRTUAL TABLE intents_fts USING fts5(
    intent_id UNINDEXED,
    title,
    category,
    tokenize='unicode61 remove_diacritics 2'
)");
$pdo->exec('CREATE INDEX answers_scenario_idx ON canonical_answers(scenario_id)');
$pdo->exec('CREATE INDEX answers_intent_idx ON canonical_answers(intent_id)');
$pdo->exec('CREATE INDEX scenarios_business_state_idx ON prospect_scenarios(business_type, state_id)');

$scenarioIds = [];
$scenarioMeta = [];
$intentIds = [];
$insertScenario = $pdo->prepare('INSERT INTO prospect_scenarios(scenario_id,business_type,state_id,scenario) VALUES(?,?,?,?)');
$insertScenarioFts = $pdo->prepare('INSERT INTO scenarios_fts(scenario_id,business_type,state_id,scenario) VALUES(?,?,?,?)');
$insertIntent = $pdo->prepare('INSERT INTO client_intents(intent_id,title,category) VALUES(?,?,?)');
$insertIntentFts = $pdo->prepare('INSERT INTO intents_fts(intent_id,title,category) VALUES(?,?,?)');
$insertAnswer = $pdo->prepare('INSERT INTO canonical_answers(answer_ref,scenario_id,intent_id,business_type,state_id,scenario,intent_title,category,canonical_answer_long) VALUES(?,?,?,?,?,?,?,?,?)');

$pdo->beginTransaction();
foreach ($scenarios as $scenario) {
    $scenarioId = trim((string) ($scenario['scenario_id'] ?? ''));
    $businessType = trim((string) ($scenario['business_type'] ?? ''));
    $stateId = trim((string) ($scenario['state_id'] ?? ''));
    $scenarioText = trim((string) ($scenario['scenario'] ?? ''));
    if ($scenarioId === '' || $businessType === '' || $stateId === '' || $scenarioText === '' || isset($scenarioIds[$scenarioId])) {
        throw new RuntimeException('Scenariu invalid sau duplicat: ' . $scenarioId);
    }
    $scenarioIds[$scenarioId] = true;
    $scenarioMeta[$scenarioId] = ['business_type' => $businessType, 'state_id' => $stateId, 'scenario' => $scenarioText];
    $insertScenario->execute([$scenarioId, $businessType, $stateId, $scenarioText]);
    $insertScenarioFts->execute([$scenarioId, $businessType, $stateId, $scenarioText]);
}
foreach ($intents as $intent) {
    $intentId = trim((string) ($intent['intent_id'] ?? ''));
    $title = trim((string) ($intent['title'] ?? ''));
    $category = trim((string) ($intent['category'] ?? ''));
    if ($intentId === '' || $title === '' || $category === '' || isset($intentIds[$intentId])) {
        throw new RuntimeException('Intenție invalidă sau duplicată: ' . $intentId);
    }
    $intentIds[$intentId] = true;
    $insertIntent->execute([$intentId, $title, $category]);
    $insertIntentFts->execute([$intentId, $title, $category]);
}

$handle = fopen($answerPath, 'rb');
if ($handle === false) {
    throw new RuntimeException('Nu am putut deschide răspunsurile canonice.');
}
$answerCount = 0;
try {
    while (($line = fgets($handle)) !== false) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $answer = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        $answerRef = trim((string) ($answer['answer_ref'] ?? ''));
        $scenarioId = trim((string) ($answer['scenario_id'] ?? ''));
        $intentId = trim((string) ($answer['intent_id'] ?? ''));
        $canonical = trim((string) ($answer['canonical_answer_long'] ?? ''));
        if ($answerRef === '' || !isset($scenarioIds[$scenarioId]) || !isset($intentIds[$intentId]) || $canonical === '') {
            throw new RuntimeException('Răspuns invalid la linia ' . ($answerCount + 1) . '.');
        }
        $insertAnswer->execute([
            $answerRef,
            $scenarioId,
            $intentId,
            (string) $scenarioMeta[$scenarioId]['business_type'],
            (string) $scenarioMeta[$scenarioId]['state_id'],
            (string) $scenarioMeta[$scenarioId]['scenario'],
            trim((string) ($answer['intent_title'] ?? '')),
            trim((string) ($answer['category'] ?? '')),
            $canonical,
        ]);
        $answerCount++;
        if ($answerCount % 5000 === 0) {
            $pdo->commit();
            $pdo->beginTransaction();
        }
    }
} finally {
    fclose($handle);
}
$pdo->commit();
if ($answerCount !== 100000) {
    throw new RuntimeException('Au fost importate ' . $answerCount . ' răspunsuri, nu 100.000.');
}

$pdo->exec("INSERT INTO scenarios_fts(scenarios_fts) VALUES('optimize')");
$pdo->exec("INSERT INTO intents_fts(intents_fts) VALUES('optimize')");
$metadata = $pdo->prepare('INSERT INTO metadata(key,value) VALUES(?,?)');
foreach ([
    'dataset' => 'CAB-IT 10B virtual prospect Q&A',
    'built_at' => gmdate('c'),
    'total_virtual_questions' => '10000000000',
    'id_start' => '551130001',
    'id_end' => '10551130000',
    'scenario_count' => '1000',
    'business_type_count' => '50',
    'state_count' => '20',
    'intent_count' => '100',
    'canonical_answer_count' => (string) $answerCount,
    'routing' => 'explicit business profile + explicit prospect state + current intent -> answer_ref',
] as $key => $value) {
    $metadata->execute([$key, $value]);
}
$integrity = (string) $pdo->query('PRAGMA integrity_check')->fetchColumn();
$foreignKeyErrors = (int) $pdo->query('SELECT COUNT(*) FROM pragma_foreign_key_check')->fetchColumn();
$storedAnswers = (int) $pdo->query('SELECT COUNT(*) FROM canonical_answers')->fetchColumn();

$insertScenario = null;
$insertScenarioFts = null;
$insertIntent = null;
$insertIntentFts = null;
$insertAnswer = null;
$metadata = null;
$pdo = null;
if ($integrity !== 'ok' || $foreignKeyErrors !== 0 || $storedAnswers !== 100000) {
    throw new RuntimeException('Baza 10B nu a trecut validarea finală.');
}
if (is_file($target)) {
    unlink($target);
}
if (!rename($temporary, $target)) {
    throw new RuntimeException('Nu am putut publica baza compactă 10B.');
}

echo json_encode([
    'ok' => true,
    'database' => $target,
    'scenarios' => count($scenarioIds),
    'intents' => count($intentIds),
    'canonical_answers' => $answerCount,
    'virtual_questions' => 10000000000,
    'integrity' => $integrity,
    'bytes' => filesize($target),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
