<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/cabit-cms/config.php';

const CABIT_AI_API_MAX_JSON_BYTES = 524288;
const CABIT_AI_CONVERSATION_MAX_BYTES = 393216;

function cabit_ai_send_security_headers(): void
{
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
    header('Cross-Origin-Resource-Policy: same-origin');
    header('Referrer-Policy: no-referrer');
}

/**
 * @param array<string, mixed> $payload
 */
function cabit_ai_json_response(int $status, array $payload): never
{
    http_response_code($status);
    try {
        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        );
    } catch (JsonException) {
        http_response_code(500);
        echo '{"ok":false,"error":{"code":"response_error","message":"Răspunsul nu a putut fi generat."}}';
    }
    exit;
}

/**
 * @param array<string, mixed> $details
 */
function cabit_ai_error(int $status, string $code, string $message, array $details = []): never
{
    $error = ['code' => $code, 'message' => $message];
    if ($details !== []) {
        $error['details'] = $details;
    }
    cabit_ai_json_response($status, ['ok' => false, 'error' => $error]);
}

function cabit_ai_normalize_origin(string $value): ?string
{
    $parts = parse_url(trim($value));
    if (!is_array($parts)) {
        return null;
    }
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
    if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
        return null;
    }
    if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
        return null;
    }
    $path = (string) ($parts['path'] ?? '');
    if ($path !== '' && $path !== '/') {
        return null;
    }
    $port = isset($parts['port']) ? (int) $parts['port'] : null;
    if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
        $port = null;
    }
    return $scheme . '://' . $host . ($port !== null ? ':' . $port : '');
}

/**
 * @return list<string>
 */
function cabit_ai_allowed_origins(): array
{
    $origins = [];
    $configured = cabit_ai_normalize_origin(CABIT_SITE_URL);
    if ($configured !== null) {
        $origins[] = $configured;
    }

    // Never trust an arbitrary Host header as an allowed origin. Loopback is
    // accepted only for local development; production relies on CABIT_SITE_URL
    // and the explicit CABIT_CHAT_ALLOWED_ORIGINS environment allowlist.
    $host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
    $remoteAddress = strtolower(trim((string) ($_SERVER['REMOTE_ADDR'] ?? '')));
    $isLoopbackClient = in_array($remoteAddress, ['127.0.0.1', '::1'], true);
    $isLoopbackHost = preg_match('/^(?:localhost|127(?:\.[0-9]{1,3}){3}|\[::1\])(?::[0-9]{1,5})?$/i', $host) === 1;
    if ($isLoopbackClient && $isLoopbackHost) {
        $scheme = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
        $current = cabit_ai_normalize_origin($scheme . '://' . $host);
        if ($current !== null) {
            $origins[] = $current;
        }
    }

    $extraOrigins = trim((string) getenv('CABIT_CHAT_ALLOWED_ORIGINS'));
    if ($extraOrigins !== '') {
        foreach (explode(',', $extraOrigins) as $extraOrigin) {
            $normalized = cabit_ai_normalize_origin($extraOrigin);
            if ($normalized !== null) {
                $origins[] = $normalized;
            }
        }
    }

    return array_values(array_unique($origins));
}

function cabit_ai_require_same_origin(): void
{
    $origin = cabit_ai_normalize_origin((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
    if ($origin === null || !in_array($origin, cabit_ai_allowed_origins(), true)) {
        cabit_ai_error(403, 'origin_not_allowed', 'Cererea nu provine de pe website-ul CAB-IT.');
    }
}

/**
 * @return array<string, mixed>
 */
function cabit_ai_require_post_json(int $maxBytes = CABIT_AI_API_MAX_JSON_BYTES): array
{
    cabit_ai_send_security_headers();

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST') {
        header('Allow: POST');
        cabit_ai_error(405, 'method_not_allowed', 'Endpointul acceptă doar cereri POST.');
    }
    if (!hash_equals('1', trim((string) ($_SERVER['HTTP_X_CABIT_CHAT'] ?? '')))) {
        cabit_ai_error(403, 'missing_client_header', 'Cererea nu poate fi validată.');
    }
    cabit_ai_require_same_origin();

    $contentType = strtolower(trim(explode(';', (string) ($_SERVER['CONTENT_TYPE'] ?? ''), 2)[0]));
    if ($contentType !== 'application/json') {
        cabit_ai_error(415, 'unsupported_media_type', 'Trimite datele în format application/json.');
    }

    $declaredLength = trim((string) ($_SERVER['CONTENT_LENGTH'] ?? ''));
    if ($declaredLength !== '' && ctype_digit($declaredLength) && (int) $declaredLength > $maxBytes) {
        cabit_ai_error(413, 'payload_too_large', 'Mesajul trimis este prea mare.');
    }

    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        cabit_ai_error(400, 'empty_payload', 'Lipsește conținutul cererii.');
    }
    if (strlen($raw) > $maxBytes) {
        cabit_ai_error(413, 'payload_too_large', 'Mesajul trimis este prea mare.');
    }
    if (!mb_check_encoding($raw, 'UTF-8')) {
        cabit_ai_error(400, 'invalid_encoding', 'Conținutul trebuie să fie UTF-8 valid.');
    }

    try {
        $payload = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        cabit_ai_error(400, 'invalid_json', 'Conținutul JSON nu este valid.');
    }
    if (!is_array($payload) || array_is_list($payload)) {
        cabit_ai_error(400, 'invalid_payload', 'Conținutul trebuie să fie un obiect JSON.');
    }
    return $payload;
}

function cabit_ai_now(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM);
}

function cabit_ai_uuid(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
}

function cabit_ai_base64url_encode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function cabit_ai_ensure_private_directory(string $directory): void
{
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Directorul privat nu poate fi creat.');
    }
    @chmod($directory, 0700);
}

function cabit_ai_runtime_storage_directory(): string
{
    $override = trim((string) getenv('CABIT_AI_STORAGE_DIR'));
    $directory = $override !== '' ? $override : CABIT_STORAGE_DIR . '/ai';
    cabit_ai_ensure_private_directory($directory);
    return rtrim($directory, '/\\');
}

function cabit_ai_conversation_directory(): string
{
    $override = trim((string) getenv('CABIT_AI_CONVERSATION_DIR'));
    $directory = $override !== '' ? $override : CABIT_STORAGE_DIR . '/assistant-conversations';
    cabit_ai_ensure_private_directory($directory);
    cabit_ai_ensure_private_directory($directory . '/.locks');
    return rtrim($directory, '/\\');
}

function cabit_ai_secret(): string
{
    static $secret = null;
    if (is_string($secret)) {
        return $secret;
    }

    $fromEnvironment = (string) getenv('CABIT_CHAT_HMAC_SECRET');
    if (strlen($fromEnvironment) >= 32) {
        $secret = $fromEnvironment;
        return $secret;
    }

    $path = cabit_ai_runtime_storage_directory() . '/.chat-secret';
    $handle = fopen($path, 'c+b');
    if ($handle === false) {
        throw new RuntimeException('Secretul API nu poate fi deschis.');
    }
    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Secretul API nu poate fi blocat.');
        }
        rewind($handle);
        $stored = trim((string) stream_get_contents($handle));
        if (strlen($stored) < 43) {
            $stored = cabit_ai_base64url_encode(random_bytes(32));
            if (!ftruncate($handle, 0) || !rewind($handle) || fwrite($handle, $stored . "\n") === false || !fflush($handle)) {
                throw new RuntimeException('Secretul API nu poate fi creat.');
            }
        }
        $secret = $stored;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
    @chmod($path, 0600);
    return $secret;
}

function cabit_ai_token_hash(string $conversationId, string $token): string
{
    return hash_hmac('sha256', $conversationId . "\0" . $token, cabit_ai_secret());
}

function cabit_ai_runtime_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = cabit_ai_runtime_storage_directory() . '/assistant-runtime.sqlite';
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (
        bucket TEXT NOT NULL,
        identity_hash TEXT NOT NULL,
        window_start INTEGER NOT NULL,
        hit_count INTEGER NOT NULL,
        PRIMARY KEY (bucket, identity_hash)
    )');
    @chmod($path, 0600);
    return $pdo;
}

function cabit_ai_enforce_rate_limit(string $bucket, int $limit, int $windowSeconds): void
{
    if (!preg_match('/^[a-z0-9_-]{1,48}$/', $bucket) || $limit < 1 || $windowSeconds < 1) {
        throw new InvalidArgumentException('Configurație rate-limit invalidă.');
    }

    $remoteAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $identity = hash_hmac('sha256', $remoteAddress, cabit_ai_secret());
    $now = time();
    $windowStart = intdiv($now, $windowSeconds) * $windowSeconds;
    $allowed = false;

    try {
        $pdo = cabit_ai_runtime_db();
        $pdo->exec('BEGIN IMMEDIATE');
        $select = $pdo->prepare('SELECT window_start, hit_count FROM rate_limits WHERE bucket = ? AND identity_hash = ?');
        $select->execute([$bucket, $identity]);
        $row = $select->fetch();

        if (!$row || (int) $row['window_start'] !== $windowStart) {
            $replace = $pdo->prepare('INSERT OR REPLACE INTO rate_limits (bucket, identity_hash, window_start, hit_count) VALUES (?, ?, ?, 1)');
            $replace->execute([$bucket, $identity, $windowStart]);
            $allowed = true;
        } elseif ((int) $row['hit_count'] < $limit) {
            $update = $pdo->prepare('UPDATE rate_limits SET hit_count = hit_count + 1 WHERE bucket = ? AND identity_hash = ?');
            $update->execute([$bucket, $identity]);
            $allowed = true;
        }

        if (random_int(1, 100) === 1) {
            $cleanup = $pdo->prepare('DELETE FROM rate_limits WHERE window_start < ?');
            $cleanup->execute([$now - 172800]);
        }
        $pdo->exec('COMMIT');
    } catch (Throwable $error) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
        error_log('CABIT AI rate-limit error: ' . $error::class);
        cabit_ai_error(503, 'rate_limit_unavailable', 'Serviciul este temporar indisponibil. Încearcă din nou.');
    }

    if (!$allowed) {
        $retryAfter = max(1, ($windowStart + $windowSeconds) - $now);
        header('Retry-After: ' . $retryAfter);
        cabit_ai_error(429, 'rate_limited', 'Ai trimis prea multe cereri. Reîncearcă în câteva momente.', ['retry_after' => $retryAfter]);
    }
}

function cabit_ai_clean_text(mixed $value, string $field, int $minimum, int $maximum, bool $collapseWhitespace = false): string
{
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) {
        cabit_ai_error(422, 'invalid_' . $field, 'Câmpul „' . $field . '” nu este valid.');
    }
    $value = str_replace(["\r\n", "\r"], "\n", trim($value));
    if ($collapseWhitespace) {
        $value = (string) preg_replace('/\s+/u', ' ', $value);
    }
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
        cabit_ai_error(422, 'invalid_' . $field, 'Câmpul „' . $field . '” conține caractere nepermise.');
    }
    $length = mb_strlen($value, 'UTF-8');
    if ($length < $minimum || $length > $maximum) {
        cabit_ai_error(422, 'invalid_' . $field, 'Câmpul „' . $field . '” trebuie să aibă între ' . $minimum . ' și ' . $maximum . ' caractere.');
    }
    return $value;
}

/**
 * Caller must hold the corresponding conversation lock.
 *
 * @param array<string, mixed> $data
 */
function cabit_ai_atomic_write_json(string $path, array $data): void
{
    try {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        ) . "\n";
    } catch (JsonException $error) {
        throw new RuntimeException('Conversația nu poate fi serializată.', 0, $error);
    }
    if (strlen($json) > CABIT_AI_CONVERSATION_MAX_BYTES) {
        throw new LengthException('Conversația a atins limita de stocare.');
    }

    cabit_ai_ensure_private_directory(dirname($path));
    $temporary = dirname($path) . '/.' . basename($path) . '.' . bin2hex(random_bytes(8)) . '.tmp';
    $handle = fopen($temporary, 'xb');
    if ($handle === false) {
        throw new RuntimeException('Fișierul temporar nu poate fi creat.');
    }
    $writeError = null;
    try {
        $offset = 0;
        $length = strlen($json);
        while ($offset < $length) {
            $written = fwrite($handle, substr($json, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Conversația nu poate fi scrisă.');
            }
            $offset += $written;
        }
        if (!fflush($handle)) {
            throw new RuntimeException('Conversația nu poate fi sincronizată pe disc.');
        }
        if (function_exists('fsync')) {
            @fsync($handle);
        }
    } catch (Throwable $error) {
        $writeError = $error;
    } finally {
        fclose($handle);
    }
    if ($writeError instanceof Throwable) {
        @unlink($temporary);
        throw $writeError;
    }
    @chmod($temporary, 0600);

    if (@rename($temporary, $path)) {
        @chmod($path, 0600);
        return;
    }

    // Fallback for Windows filesystems that cannot replace an existing file with rename().
    $backup = $path . '.replace-backup';
    @unlink($backup);
    $hadOriginal = is_file($path);
    if ($hadOriginal && !@rename($path, $backup)) {
        @unlink($temporary);
        throw new RuntimeException('Fișierul existent nu poate fi înlocuit.');
    }
    if (!@rename($temporary, $path)) {
        if ($hadOriginal) {
            @rename($backup, $path);
        }
        @unlink($temporary);
        throw new RuntimeException('Actualizarea conversației a eșuat.');
    }
    @unlink($backup);
    @chmod($path, 0600);
}

/**
 * @return array<string, mixed>
 */
function cabit_ai_read_json_file(string $path): array
{
    $size = @filesize($path);
    if ($size === false || $size < 2 || $size > CABIT_AI_CONVERSATION_MAX_BYTES) {
        throw new RuntimeException('Fișierul conversației este invalid.');
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Conversația nu poate fi citită.');
    }
    try {
        $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        throw new RuntimeException('Fișierul conversației este corupt.', 0, $error);
    }
    if (!is_array($decoded) || array_is_list($decoded)) {
        throw new RuntimeException('Structura conversației este invalidă.');
    }
    return $decoded;
}
