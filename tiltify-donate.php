<?php

declare(strict_types=1);

// Starts a "verified" donation flow using Tiltify Webhook Relays.
// 1) Generates a relay key via Tiltify API
// 2) Redirects the user to Tiltify donate form with ?relay=<client_key>
// 3) User returns to /verify.php?code=<relay_key_id>

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

$configExists = is_file($configFile);
$config = $configExists ? require $configFile : null;

if (!is_array($config)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    if (!$configExists) {
        echo "Server not configured. Create config.php from config.example.php";
    } else {
        $t = gettype($config);
        echo "Invalid config.php (must return a PHP array). config.php currently returns type: {$t}. "
            . "Common fix: ensure config.php contains a top-level `return [ ... ];` like config.example.php.";
    }
    exit;
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

function http_post_json(string $url, array $body, array $headers): array
{
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('curl_init failed');
    }

    $payload = json_encode($body, JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        throw new RuntimeException('Failed to encode JSON');
    }

    $headerLines = array_merge([
        'Content-Type: application/json',
        'Accept: application/json',
    ], $headers);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerLines);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('cURL error: ' . $err);
    }

    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($resp, true);
    return [
        'status' => (int)$code,
        'raw' => $resp,
        'json' => is_array($decoded) ? $decoded : null,
    ];
}

function extract_client_key_from_response(?array $json): string
{
    if (!is_array($json)) {
        return '';
    }

    $candidates = [
        $json['client_key'] ?? null,
        $json['clientKey'] ?? null,
        $json['data']['client_key'] ?? null,
        $json['data']['clientKey'] ?? null,
        $json['data']['attributes']['client_key'] ?? null,
        $json['data']['attributes']['clientKey'] ?? null,
        $json['webhook_relay_key']['client_key'] ?? null,
        $json['webhook_relay_key']['clientKey'] ?? null,
        $json['webhook_relay_key']['attributes']['client_key'] ?? null,
        $json['webhook_relay_key']['attributes']['clientKey'] ?? null,
    ];

    foreach ($candidates as $v) {
        if (is_string($v) && $v !== '') {
            return $v;
        }
    }

    return '';
}

function summarize_json_shape(?array $json): string
{
    if (!is_array($json)) {
        return 'non-JSON or non-object response';
    }

    $topKeys = array_keys($json);
    sort($topKeys);
    $summary = 'top-level keys: ' . implode(', ', array_slice($topKeys, 0, 20));
    if (count($topKeys) > 20) {
        $summary .= ', ...';
    }

    if (isset($json['data']) && is_array($json['data'])) {
        $dataKeys = array_keys($json['data']);
        sort($dataKeys);
        $summary .= ' | data keys: ' . implode(', ', array_slice($dataKeys, 0, 20));
        if (count($dataKeys) > 20) {
            $summary .= ', ...';
        }
    }

    return $summary;
}

function tiltify_access_token(array $config): string
{
    $dataDir = (string)($config['data_dir'] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'data'));
    ensure_dir($dataDir);

    $tokenPath = $dataDir . DIRECTORY_SEPARATOR . 'tiltify_token.json';
    $cached = read_json_file($tokenPath);

    $now = time();
    if (isset($cached['access_token'], $cached['expires_at']) && is_string($cached['access_token'])) {
        if ((int)$cached['expires_at'] - 60 > $now) {
            return $cached['access_token'];
        }
    }

    $clientId = (string)($config['tiltify_client_id'] ?? '');
    $clientSecret = (string)($config['tiltify_client_secret'] ?? '');
    if ($clientId === '' || $clientSecret === '') {
        throw new RuntimeException('Missing Tiltify client_id/client_secret in config.php');
    }

    // Client credentials flow (server-to-server)
    $resp = http_post_json(
        'https://v5api.tiltify.com/oauth/token',
        [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
            'scope' => 'public webhooks:write',
        ],
        []
    );

    if ($resp['status'] < 200 || $resp['status'] >= 300 || !is_array($resp['json'])) {
        throw new RuntimeException('Failed to get access token: HTTP ' . $resp['status'] . ' ' . $resp['raw']);
    }

    $token = (string)($resp['json']['access_token'] ?? '');
    $expiresIn = (int)($resp['json']['expires_in'] ?? 0);
    if ($token === '' || $expiresIn <= 0) {
        throw new RuntimeException('Invalid access token response');
    }

    write_json_file_atomic($tokenPath, [
        'access_token' => $token,
        'expires_at' => $now + $expiresIn,
        'scope' => (string)($resp['json']['scope'] ?? ''),
        'created_at' => (string)($resp['json']['created_at'] ?? ''),
    ]);

    return $token;
}

function random_code(int $bytes = 12): string
{
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
}

function is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443') {
        return true;
    }
    return false;
}

$donationUrl = (string)($config['tiltify_donation_url'] ?? '');
$relayId = (string)($config['tiltify_webhook_relay_id'] ?? '');

if ($donationUrl === '' || $relayId === '') {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Missing tiltify_donation_url or tiltify_webhook_relay_id in config.php";
    exit;
}

$charity = isset($_GET['charity']) ? (string)$_GET['charity'] : '';
if ($charity === '') {
    $charity = 'default';
}

// This becomes meta.relay_key_id in webhook payloads.
$relayKeyId = 'stc_' . random_code(14);

// Metadata must be a string; keep it non-identifying.
$metadata = base64_encode(json_encode([
    'charity' => $charity,
    'source' => 'savethechew.biz',
], JSON_UNESCAPED_SLASHES) ?: '{}');

try {
    $token = tiltify_access_token($config);

    $resp = http_post_json(
        'https://v5api.tiltify.com/api/private/webhook_relays/' . rawurlencode($relayId) . '/webhook_relay_keys',
        [
            'id' => $relayKeyId,
            'metadata' => $metadata,
        ],
        [
            'Authorization: Bearer ' . $token,
        ]
    );

    if ($resp['status'] < 200 || $resp['status'] >= 300 || !is_array($resp['json'])) {
        throw new RuntimeException('Failed to create relay key: HTTP ' . $resp['status'] . ' ' . $resp['raw']);
    }

    $clientKey = extract_client_key_from_response($resp['json']);
    if ($clientKey === '') {
        throw new RuntimeException('Missing client_key in response (' . summarize_json_shape($resp['json']) . ')');
    }

    // Store relay status locally.
    $dataDir = (string)($config['data_dir'] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'data'));
    ensure_dir($dataDir);

    $relaysPath = $dataDir . DIRECTORY_SEPARATOR . 'relays.json';
    $relays = read_json_file($relaysPath);
    $relays[$relayKeyId] = [
        'status' => 'pending',
        'created_at' => gmdate('c'),
        'charity' => $charity,
    ];
    write_json_file_atomic($relaysPath, $relays);

    // Store the verification code for the user so /verify.php can auto-fill it after the redirect.
    // (This avoids relying on a custom header that browsers won’t expose to users.)
    setcookie('stc_verify_code', $relayKeyId, [
        'expires' => time() + 60 * 60 * 6,
        'path' => '/',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // Redirect user to Tiltify donate form.
    $sep = (str_contains($donationUrl, '?')) ? '&' : '?';
    $url = $donationUrl . $sep . 'relay=' . rawurlencode($clientKey);

    // Show user a return link containing the relay key id.
    // (We also redirect immediately; they can bookmark/copy the code if needed.)
    header('Location: ' . $url, true, 302);
    header('X-STC-Verify-Code: ' . $relayKeyId);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Donation start failed: " . $e->getMessage();
    exit;
}
