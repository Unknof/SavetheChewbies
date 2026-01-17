<?php

declare(strict_types=1);

// Lightweight endpoint for front-end milestone display.
// Returns current campaign total (best-effort) and lets the UI decide which milestones to reveal.

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config = is_file($configFile) ? require $configFile : null;

header('Content-Type: application/json; charset=utf-8');

if (!is_array($config)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server not configured (missing config.php)']);
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

function http_request_json(string $method, string $url, array $headers = []): array
{
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('curl_init failed');
    }

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headerLines = array_merge([
        'Accept: application/json',
    ], $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerLines);

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

function http_post_json(string $url, array $body, array $headers = []): array
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

    $resp = http_post_json(
        'https://v5api.tiltify.com/oauth/token',
        [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
            // Keep existing scopes used elsewhere; add public read.
            'scope' => 'public webhooks:write',
        ]
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

function extract_campaign_total(?array $json): ?float
{
    if (!is_array($json)) {
        return null;
    }

    $candidates = [
        $json['data']['total_raised']['value'] ?? null,
        $json['data']['amount_raised']['value'] ?? null,
        $json['data']['raised_amount']['value'] ?? null,
        $json['data']['total_amount_raised']['value'] ?? null,
        $json['data']['total_raised'] ?? null,
        $json['data']['amount_raised'] ?? null,
        // Fallback: old API structure with attributes
        $json['data']['attributes']['total_raised']['value'] ?? null,
        $json['data']['attributes']['amount_raised']['value'] ?? null,
    ];

    foreach ($candidates as $v) {
        if (is_int($v) || is_float($v)) {
            return (float)$v;
        }
        if (is_string($v) && is_numeric($v)) {
            return (float)$v;
        }
        if (is_array($v) && isset($v['value']) && (is_numeric($v['value']) || is_int($v['value']) || is_float($v['value']))) {
            return (float)$v['value'];
        }
    }

    return null;
}

function extract_campaign_goal(?array $json): ?float
{
    if (!is_array($json)) {
        return null;
    }

    $candidates = [
        $json['data']['goal']['value'] ?? null,
        $json['data']['fundraising_goal']['value'] ?? null,
        $json['data']['goal_amount']['value'] ?? null,
        $json['data']['goal'] ?? null,
        $json['data']['fundraising_goal'] ?? null,
        $json['data']['goal_amount'] ?? null,
        // Fallback: old API structure with attributes
        $json['data']['attributes']['goal']['value'] ?? null,
        $json['data']['attributes']['fundraising_goal']['value'] ?? null,
    ];

    foreach ($candidates as $v) {
        if (is_int($v) || is_float($v)) {
            return (float)$v;
        }
        if (is_string($v) && is_numeric($v)) {
            return (float)$v;
        }
        if (is_array($v) && isset($v['value']) && (is_numeric($v['value']) || is_int($v['value']) || is_float($v['value']))) {
            return (float)$v['value'];
        }
    }

    return null;
}

function extract_campaign_currency(?array $json): ?string
{
    if (!is_array($json)) {
        return null;
    }

    $candidates = [
        $json['data']['currency_code'] ?? null,
        $json['data']['currency'] ?? null,
        $json['data']['total_raised']['currency'] ?? null,
        $json['data']['amount_raised']['currency'] ?? null,
        $json['data']['raised_amount']['currency'] ?? null,
        // Fallback: old API structure with attributes
        $json['data']['attributes']['currency_code'] ?? null,
        $json['data']['attributes']['total_raised']['currency'] ?? null,
    ];

    foreach ($candidates as $v) {
        if (is_string($v) && $v !== '') {
            return $v;
        }
    }

    return null;
}

// Optional override for testing without touching the API.
$override = $config['milestone_total_override'] ?? null;
if (is_int($override) || is_float($override) || (is_string($override) && is_numeric($override))) {
    echo json_encode([
        'ok' => true,
        'source' => 'override',
        'total' => (float)$override,
        'goal' => null,
        'currency' => 'EUR',
        'updated_at' => gmdate('c'),
    ]);
    exit;
}

$campaignId = (string)($config['tiltify_campaign_id'] ?? '');
if ($campaignId === '') {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => 'Missing tiltify_campaign_id in config.php (or set milestone_total_override for testing).',
    ]);
    exit;
}

try {
    $token = '';
    try {
        $token = tiltify_access_token($config);
    } catch (Throwable $e) {
        // Non-fatal; the public endpoint may work without auth.
        $token = '';
    }

    $headers = [];
    if ($token !== '') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $resp = http_request_json('GET', 'https://v5api.tiltify.com/api/public/campaigns/' . rawurlencode($campaignId), $headers);
    if ($resp['status'] < 200 || $resp['status'] >= 300 || !is_array($resp['json'])) {
        throw new RuntimeException('Tiltify campaign fetch failed: HTTP ' . $resp['status']);
    }

    $total = extract_campaign_total($resp['json']);
    $goal = extract_campaign_goal($resp['json']);
    $currency = extract_campaign_currency($resp['json']);

    // Some campaign responses may omit raised totals when they are still 0.
    // Treat a missing total as 0 (non-fatal), but still surface a warning.
    $warning = null;
    if ($total === null) {
        $total = 0.0;
        $warning = 'Could not extract campaign total; assuming 0.';
    }

    // Normalize to non-negative values.
    $total = max(0.0, (float)$total);
    if ($goal !== null) {
        $goal = max(0.0, (float)$goal);
    }

    echo json_encode([
        'ok' => true,
        'source' => 'tiltify',
        'total' => $total,
        'goal' => $goal,
        'currency' => $currency,
        'updated_at' => gmdate('c'),
        'warning' => $warning,
    ]);
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
}
