<?php

declare(strict_types=1);

// Tiltify Webhook endpoint
// - Verifies X-Tiltify-Signature
// - Records relay donation updates

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config = is_file($configFile) ? require $configFile : null;

if (!is_array($config)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Server not configured. Create config.php from config.example.php";
    exit;
}

function header_value(string $name): ?string
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    foreach ($headers as $key => $value) {
        if (strcasecmp((string)$key, $name) === 0) {
            return is_array($value) ? implode(',', $value) : (string)$value;
        }
    }

    // Fallback for environments without getallheaders
    $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$serverKey])) {
        return (string)$_SERVER[$serverKey];
    }

    return null;
}

function ensure_dir(string $dir): void
{
    if (is_dir($dir)) {
        return;
    }
    if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Failed to create data directory');
    }
}

function write_json_file_atomic(string $path, array $data): void
{
    $tmp = $path . '.tmp';
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        throw new RuntimeException('Failed to encode JSON');
    }
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        throw new RuntimeException('Failed to write temp file');
    }
    if (!rename($tmp, $path)) {
        throw new RuntimeException('Failed to replace file');
    }
}

function read_json_file(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function verify_tiltify_signature(string $signingKey, string $signatureB64, string $timestamp, string $rawBody): bool
{
    // Timestamp should be recent (Tiltify suggests ~1 minute); allow some clock skew.
    $ts = strtotime($timestamp);
    if ($ts === false) {
        return false;
    }
    $now = time();
    if (abs($now - $ts) > 300) {
        return false;
    }

    $payload = $timestamp . '.' . $rawBody;
    $expected = base64_encode(hash_hmac('sha256', $payload, $signingKey, true));

    return hash_equals($expected, $signatureB64);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false) {
    http_response_code(400);
    exit;
}

$signature = header_value('X-Tiltify-Signature');
$timestamp = header_value('X-Tiltify-Timestamp');

if (!$signature || !$timestamp) {
    http_response_code(400);
    exit;
}

$signingKey = (string)($config['tiltify_webhook_signing_key'] ?? '');
if ($signingKey === '') {
    http_response_code(500);
    exit;
}

if (!verify_tiltify_signature($signingKey, $signature, $timestamp, $rawBody)) {
    http_response_code(401);
    exit;
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    exit;
}

$meta = $payload['meta'] ?? null;
$data = $payload['data'] ?? null;
if (!is_array($meta) || !is_array($data)) {
    http_response_code(200);
    exit;
}

$eventType = (string)($meta['event_type'] ?? '');
$relayKeyId = (string)($meta['relay_key_id'] ?? '');

// We only care about relay events for user verification.
if ($relayKeyId === '') {
    http_response_code(200);
    exit;
}

$dataDir = (string)($config['data_dir'] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'data'));
ensure_dir($dataDir);

$relaysPath = $dataDir . DIRECTORY_SEPARATOR . 'relays.json';
$donationsLog = $dataDir . DIRECTORY_SEPARATOR . 'donations.jsonl';

$relays = read_json_file($relaysPath);
if (!isset($relays[$relayKeyId]) || !is_array($relays[$relayKeyId])) {
    // Unknown relay key; still accept the webhook to avoid deactivation.
    http_response_code(200);
    exit;
}

$completedAt = $data['completed_at'] ?? null;
$paymentStatus = (string)($data['payment_status'] ?? '');

$status = $relays[$relayKeyId]['status'] ?? 'pending';
if (is_string($status) === false) {
    $status = 'pending';
}

// Heuristic: treat as completed if completed_at exists OR payment_status says completed.
$isCompleted = ($completedAt !== null && $completedAt !== '') || strcasecmp($paymentStatus, 'completed') === 0;
$isCancelled = str_contains($eventType, 'cancel') || strcasecmp($paymentStatus, 'cancelled') === 0 || strcasecmp($paymentStatus, 'canceled') === 0;

if ($isCancelled) {
    $relays[$relayKeyId]['status'] = 'cancelled';
} elseif ($isCompleted) {
    $relays[$relayKeyId]['status'] = 'verified';
    $relays[$relayKeyId]['verified_at'] = gmdate('c');
}

$relays[$relayKeyId]['last_event_type'] = $eventType;
$relays[$relayKeyId]['last_event_at'] = (string)($meta['attempted_at'] ?? gmdate('c'));

// Append donation event to log (JSON lines) for auditing.
$logEntry = [
    'received_at' => gmdate('c'),
    'relay_key_id' => $relayKeyId,
    'event_type' => $eventType,
    'donation' => [
        'id' => $data['id'] ?? null,
        'campaign_id' => $data['campaign_id'] ?? null,
        'cause_id' => $data['cause_id'] ?? null,
        'amount' => $data['amount'] ?? null,
        'donor_name' => $data['donor_name'] ?? null,
        'completed_at' => $data['completed_at'] ?? null,
        'payment_status' => $data['payment_status'] ?? null,
    ],
];
file_put_contents($donationsLog, json_encode($logEntry, JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX);

write_json_file_atomic($relaysPath, $relays);

http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true]);
