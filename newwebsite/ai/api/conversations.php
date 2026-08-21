<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

const CABIT_AI_CONVERSATION_RETENTION_DAYS = 90;

final class CabitAiConversationNotFound extends RuntimeException
{
}

final class CabitAiConversationConflict extends RuntimeException
{
    public function __construct(public readonly int $currentRevision)
    {
        parent::__construct('Versiunea conversației s-a schimbat.');
    }
}

function cabit_ai_conversation_id(mixed $value): string
{
    if (!is_string($value)) {
        cabit_ai_error(422, 'invalid_conversation_id', 'Identificatorul conversației nu este valid.');
    }
    $value = strtolower(trim($value));
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value)) {
        cabit_ai_error(422, 'invalid_conversation_id', 'Identificatorul conversației nu este valid.');
    }
    return $value;
}

function cabit_ai_delete_token(mixed $value): string
{
    if (!is_string($value)) {
        cabit_ai_error(422, 'invalid_delete_token', 'Tokenul conversației nu este valid.');
    }
    $value = trim($value);
    if (!preg_match('/^[A-Za-z0-9_-]{40,80}$/', $value)) {
        cabit_ai_error(422, 'invalid_delete_token', 'Tokenul conversației nu este valid.');
    }
    return $value;
}

function cabit_ai_conversation_path(string $conversationId): string
{
    return cabit_ai_conversation_directory() . '/' . $conversationId . '.json';
}

function cabit_ai_conversation_expiry(string $from): string
{
    return (new DateTimeImmutable($from))
        ->setTimezone(new DateTimeZone('UTC'))
        ->modify('+' . CABIT_AI_CONVERSATION_RETENTION_DAYS . ' days')
        ->format(DateTimeInterface::ATOM);
}

/** @return resource */
function cabit_ai_lock_conversation(string $conversationId)
{
    $path = cabit_ai_conversation_directory() . '/.locks/' . $conversationId . '.lock';
    $handle = fopen($path, 'c+b');
    if ($handle === false) {
        throw new RuntimeException('Conversația nu poate fi blocată.');
    }
    @chmod($path, 0600);
    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        throw new RuntimeException('Conversația nu poate fi blocată.');
    }
    return $handle;
}

/** @param resource $handle */
function cabit_ai_unlock_conversation($handle): void
{
    flock($handle, LOCK_UN);
    fclose($handle);
}

function cabit_ai_probabilistic_cleanup(?string $skipConversationId = null): void
{
    try {
        if (random_int(1, 40) !== 1) {
            return;
        }
        $directory = cabit_ai_conversation_directory();
        $cutoff = time() - (CABIT_AI_CONVERSATION_RETENTION_DAYS * 86400);
        $examined = 0;
        $deleted = 0;

        foreach (new DirectoryIterator($directory) as $entry) {
            if ($entry->isDot() || !$entry->isFile() || strtolower($entry->getExtension()) !== 'json') {
                continue;
            }
            if (++$examined > 100 || $deleted >= 3) {
                break;
            }
            $conversationId = strtolower($entry->getBasename('.json'));
            if ($conversationId === $skipConversationId || !preg_match('/^[0-9a-f-]{36}$/', $conversationId)) {
                continue;
            }

            $lockPath = $directory . '/.locks/' . $conversationId . '.lock';
            $lock = @fopen($lockPath, 'c+b');
            if ($lock === false) {
                continue;
            }
            @chmod($lockPath, 0600);
            if (!flock($lock, LOCK_EX | LOCK_NB)) {
                fclose($lock);
                continue;
            }
            try {
                $path = cabit_ai_conversation_path($conversationId);
                if (!is_file($path)) {
                    continue;
                }
                $expired = ((int) @filemtime($path)) > 0 && (int) @filemtime($path) <= $cutoff;
                try {
                    $document = cabit_ai_read_json_file($path);
                    $expiresAt = (string) ($document['expires_at'] ?? '');
                    if ($expiresAt !== '') {
                        $expiry = new DateTimeImmutable($expiresAt);
                        $expired = $expiry->getTimestamp() <= time();
                    }
                } catch (Throwable) {
                    // A legacy/corrupt file is removed only when its mtime also
                    // exceeds the declared retention period.
                }
                if ($expired && @unlink($path)) {
                    $deleted++;
                }
            } finally {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    } catch (Throwable) {
        // Cleanup is best-effort and must never block a legitimate chat request.
    }
}

/**
 * @param array<string, mixed>|null $incoming
 * @param array<string, mixed>|null $current
 * @return array<string, mixed>
 */
function cabit_ai_consent(?array $incoming, ?array $current = null): array
{
    $consent = [
        'server_storage' => true,
        'improvement' => (bool) ($current['improvement'] ?? false),
        'notice_version' => (string) ($current['notice_version'] ?? '2026-08-21'),
        'updated_at' => (string) ($current['updated_at'] ?? cabit_ai_now()),
    ];
    if ($incoming === null) {
        return $consent;
    }
    if (array_key_exists('improvement', $incoming)) {
        if (!is_bool($incoming['improvement'])) {
            cabit_ai_error(422, 'invalid_consent', 'Opțiunea pentru îmbunătățirea asistentului nu este validă.');
        }
        $consent['improvement'] = $incoming['improvement'];
    }
    if (array_key_exists('notice_version', $incoming)) {
        $consent['notice_version'] = cabit_ai_clean_text($incoming['notice_version'], 'notice_version', 1, 40, true);
    }
    $consent['updated_at'] = cabit_ai_now();
    return $consent;
}

function cabit_ai_client_datetime(mixed $value): string
{
    if ($value === null || $value === '') {
        return cabit_ai_now();
    }
    if (!is_string($value) || strlen($value) > 64) {
        cabit_ai_error(422, 'invalid_message_date', 'Data mesajului nu este validă.');
    }
    try {
        return (new DateTimeImmutable($value))->setTimezone(new DateTimeZone('UTC'))->format(DateTimeInterface::ATOM);
    } catch (Throwable) {
        cabit_ai_error(422, 'invalid_message_date', 'Data mesajului nu este validă.');
    }
}

/**
 * @param array<int, mixed> $messages
 * @return list<array<string, string>>
 */
function cabit_ai_normalize_messages(array $messages): array
{
    if (count($messages) > 80) {
        cabit_ai_error(413, 'too_many_messages', 'O conversație poate păstra maximum 80 de mesaje.');
    }

    $normalized = [];
    $seenIds = [];
    $totalCharacters = 0;
    foreach ($messages as $index => $message) {
        if (!is_array($message) || array_is_list($message)) {
            cabit_ai_error(422, 'invalid_message', 'Mesajul de la poziția ' . ($index + 1) . ' nu este valid.');
        }
        $role = $message['role'] ?? null;
        if (!is_string($role) || !in_array($role, ['user', 'assistant'], true)) {
            cabit_ai_error(422, 'invalid_message_role', 'Rolul mesajului de la poziția ' . ($index + 1) . ' nu este valid.');
        }
        $content = cabit_ai_clean_text($message['content'] ?? null, 'message', 1, 8000);
        $totalCharacters += mb_strlen($content, 'UTF-8');
        if ($totalCharacters > 240000) {
            cabit_ai_error(413, 'conversation_too_large', 'Conversația a atins limita de stocare. Pornește o conversație nouă.');
        }

        $messageId = $message['id'] ?? null;
        if ($messageId === null || $messageId === '') {
            $messageId = cabit_ai_uuid();
        }
        if (!is_string($messageId) || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,79}$/', $messageId)) {
            cabit_ai_error(422, 'invalid_message_id', 'Identificatorul unui mesaj nu este valid.');
        }
        if (isset($seenIds[$messageId])) {
            cabit_ai_error(422, 'duplicate_message_id', 'Conversația conține mesaje duplicate.');
        }
        $seenIds[$messageId] = true;

        $normalized[] = [
            'id' => $messageId,
            'role' => $role,
            'content' => $content,
            'created_at' => cabit_ai_client_datetime($message['created_at'] ?? null),
        ];
    }
    return $normalized;
}

/** @param list<array<string, string>> $messages */
function cabit_ai_title_from_messages(array $messages): string
{
    foreach ($messages as $message) {
        if ($message['role'] !== 'user') {
            continue;
        }
        $title = (string) preg_replace('/\s+/u', ' ', trim($message['content']));
        if (mb_strlen($title, 'UTF-8') > 72) {
            return rtrim(mb_substr($title, 0, 69, 'UTF-8')) . '…';
        }
        return $title;
    }
    return 'Conversație nouă';
}

/**
 * @param array<string, mixed> $document
 * @return array<string, mixed>
 */
function cabit_ai_public_conversation(array $document, bool $includeDeleteToken = false, ?string $deleteToken = null): array
{
    $result = [
        'id' => (string) $document['id'],
        'title' => (string) $document['title'],
        'revision' => (int) $document['revision'],
        'created_at' => (string) $document['created_at'],
        'updated_at' => (string) $document['updated_at'],
        'retention_days' => (int) ($document['retention_days'] ?? CABIT_AI_CONVERSATION_RETENTION_DAYS),
        'expires_at' => (string) ($document['expires_at'] ?? ''),
        'message_count' => count((array) ($document['messages'] ?? [])),
        'improvement_consent' => (bool) (($document['consent']['improvement'] ?? false)),
    ];
    if ($includeDeleteToken && $deleteToken !== null) {
        $result['delete_token'] = $deleteToken;
    }
    return $result;
}

$payload = cabit_ai_require_post_json();
$action = $payload['action'] ?? null;
if (!is_string($action) || !in_array($action, ['create', 'sync', 'delete'], true)) {
    cabit_ai_error(422, 'invalid_action', 'Acțiunea conversației nu este validă.');
}

cabit_ai_enforce_rate_limit('conversation_all', 90, 60);
cabit_ai_enforce_rate_limit('conversation_daily', 1200, 86400);

try {
    if ($action === 'create') {
        cabit_ai_enforce_rate_limit('conversation_create', 20, 60);
        cabit_ai_probabilistic_cleanup();
        $incomingConsent = $payload['consent'] ?? null;
        if ($incomingConsent !== null && (!is_array($incomingConsent) || array_is_list($incomingConsent))) {
            cabit_ai_error(422, 'invalid_consent', 'Opțiunile de confidențialitate nu sunt valide.');
        }
        $title = 'Conversație nouă';
        if (array_key_exists('title', $payload) && $payload['title'] !== null && $payload['title'] !== '') {
            $title = cabit_ai_clean_text($payload['title'], 'title', 1, 120, true);
        }

        $deleteToken = cabit_ai_base64url_encode(random_bytes(32));
        $createdAt = cabit_ai_now();
        $document = [];
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $conversationId = cabit_ai_uuid();
            $path = cabit_ai_conversation_path($conversationId);
            $lock = cabit_ai_lock_conversation($conversationId);
            try {
                if (is_file($path)) {
                    continue;
                }
                $document = [
                    'schema_version' => 1,
                    'id' => $conversationId,
                    'delete_token_hash' => cabit_ai_token_hash($conversationId, $deleteToken),
                    'revision' => 1,
                    'title' => $title,
                    'consent' => cabit_ai_consent($incomingConsent),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'retention_days' => CABIT_AI_CONVERSATION_RETENTION_DAYS,
                    'expires_at' => cabit_ai_conversation_expiry($createdAt),
                    'messages' => [],
                ];
                cabit_ai_atomic_write_json($path, $document);
                break;
            } finally {
                cabit_ai_unlock_conversation($lock);
            }
        }
        if ($document === []) {
            throw new RuntimeException('Nu a putut fi creat un identificator unic.');
        }
        cabit_ai_json_response(201, [
            'ok' => true,
            'conversation' => cabit_ai_public_conversation($document, true, $deleteToken),
        ]);
    }

    $conversationId = cabit_ai_conversation_id($payload['conversation_id'] ?? null);
    $deleteToken = cabit_ai_delete_token($payload['delete_token'] ?? null);
    $path = cabit_ai_conversation_path($conversationId);

    if ($action === 'delete') {
        cabit_ai_enforce_rate_limit('conversation_delete', 20, 60);
        cabit_ai_probabilistic_cleanup($conversationId);
        $lock = cabit_ai_lock_conversation($conversationId);
        try {
            if (!is_file($path)) {
                $deleted = true;
            } else {
                $document = cabit_ai_read_json_file($path);
                $storedHash = (string) ($document['delete_token_hash'] ?? '');
                if ($storedHash === '' || !hash_equals($storedHash, cabit_ai_token_hash($conversationId, $deleteToken))) {
                    throw new CabitAiConversationNotFound();
                }
                if (!unlink($path)) {
                    throw new RuntimeException('Conversația nu poate fi ștearsă.');
                }
                $deleted = true;
            }
        } finally {
            cabit_ai_unlock_conversation($lock);
        }
        cabit_ai_json_response(200, ['ok' => true, 'deleted' => $deleted]);
    }

    cabit_ai_enforce_rate_limit('conversation_sync', 60, 60);
    cabit_ai_probabilistic_cleanup($conversationId);
    $messages = $payload['messages'] ?? null;
    if (!is_array($messages) || !array_is_list($messages)) {
        cabit_ai_error(422, 'invalid_messages', 'Lista mesajelor nu este validă.');
    }
    $messages = cabit_ai_normalize_messages($messages);

    $baseRevision = $payload['base_revision'] ?? null;
    if ($baseRevision !== null && (!is_int($baseRevision) || $baseRevision < 1)) {
        cabit_ai_error(422, 'invalid_revision', 'Versiunea conversației nu este validă.');
    }
    $incomingConsent = $payload['consent'] ?? null;
    if ($incomingConsent !== null && (!is_array($incomingConsent) || array_is_list($incomingConsent))) {
        cabit_ai_error(422, 'invalid_consent', 'Opțiunile de confidențialitate nu sunt valide.');
    }
    $incomingTitle = null;
    if (array_key_exists('title', $payload) && $payload['title'] !== null && $payload['title'] !== '') {
        $incomingTitle = cabit_ai_clean_text($payload['title'], 'title', 1, 120, true);
    }

    $lock = cabit_ai_lock_conversation($conversationId);
    try {
        if (!is_file($path)) {
            throw new CabitAiConversationNotFound();
        }
        $document = cabit_ai_read_json_file($path);
        $storedHash = (string) ($document['delete_token_hash'] ?? '');
        if ($storedHash === '' || !hash_equals($storedHash, cabit_ai_token_hash($conversationId, $deleteToken))) {
            throw new CabitAiConversationNotFound();
        }
        $currentRevision = (int) ($document['revision'] ?? 0);
        if ($baseRevision !== null && $baseRevision !== $currentRevision) {
            throw new CabitAiConversationConflict($currentRevision);
        }

        $document['title'] = $incomingTitle ?? cabit_ai_title_from_messages($messages);
        $document['messages'] = $messages;
        $document['consent'] = cabit_ai_consent($incomingConsent, is_array($document['consent'] ?? null) ? $document['consent'] : null);
        $document['revision'] = $currentRevision + 1;
        $document['updated_at'] = cabit_ai_now();
        $document['retention_days'] = CABIT_AI_CONVERSATION_RETENTION_DAYS;
        $document['expires_at'] = cabit_ai_conversation_expiry((string) $document['updated_at']);
        cabit_ai_atomic_write_json($path, $document);
    } finally {
        cabit_ai_unlock_conversation($lock);
    }

    cabit_ai_json_response(200, [
        'ok' => true,
        'conversation' => cabit_ai_public_conversation($document),
    ]);
} catch (CabitAiConversationConflict $error) {
    cabit_ai_error(409, 'revision_conflict', 'Conversația a fost modificată într-o altă filă.', ['current_revision' => $error->currentRevision]);
} catch (CabitAiConversationNotFound) {
    cabit_ai_error(404, 'conversation_not_found', 'Conversația nu există sau tokenul nu este valid.');
} catch (LengthException) {
    cabit_ai_error(413, 'conversation_too_large', 'Conversația a atins limita de stocare. Pornește o conversație nouă.');
} catch (Throwable $error) {
    error_log('CABIT AI conversation error: ' . $error::class);
    cabit_ai_error(500, 'storage_error', 'Conversația nu a putut fi salvată. Încearcă din nou.');
}
