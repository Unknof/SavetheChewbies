<?php

declare(strict_types=1);

// Lightweight endpoint to fetch Tiltify campaign milestones (incentives) for front-end rendering.

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config = is_file($configFile) ? require $configFile : null;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

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
            // Keep existing scopes used elsewhere.
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

function extract_amount_value(mixed $v): ?float
{
    if (is_int($v) || is_float($v)) {
        return (float)$v;
    }
    if (is_string($v) && is_numeric($v)) {
        return (float)$v;
    }
    if (is_array($v)) {
        foreach (['value', 'amount', 'total', 'target'] as $k) {
            if (isset($v[$k]) && (is_numeric($v[$k]) || is_int($v[$k]) || is_float($v[$k]))) {
                return (float)$v[$k];
            }
        }
    }
    return null;
}

function extract_currency_code(mixed $v): ?string
{
    if (is_array($v)) {
        foreach (['currency', 'currency_code', 'currencyCode'] as $k) {
            if (isset($v[$k]) && is_string($v[$k]) && $v[$k] !== '') {
                return (string)$v[$k];
            }
        }
    }
    return null;
}

$campaignId = (string)($config['tiltify_campaign_id'] ?? '');
if ($campaignId === '') {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => 'Missing tiltify_campaign_id in config.php.',
    ]);
    exit;
}

$dataDir = (string)($config['data_dir'] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'data'));
ensure_dir($dataDir);

$cachePath = $dataDir . DIRECTORY_SEPARATOR . 'tiltify_milestones_cache.json';
$cacheTtlSeconds = 300;
$forceRefresh = isset($_GET['refresh']) && (string)$_GET['refresh'] === '1';

try {
    if (!$forceRefresh) {
        $cached = read_json_file($cachePath);
        $cachedAt = isset($cached['cached_at']) ? (int)$cached['cached_at'] : 0;
        if ($cachedAt > 0 && ($cachedAt + $cacheTtlSeconds) > time() && isset($cached['response']) && is_array($cached['response'])) {
            echo json_encode($cached['response']);
            exit;
        }
    }

    $token = '';
    try {
        $token = tiltify_access_token($config);
    } catch (Throwable) {
        // Non-fatal; public endpoints may work without auth.
        $token = '';
    }

    $headers = [];
    if ($token !== '') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $includeDisabled = isset($_GET['include_disabled']) && (string)$_GET['include_disabled'] === '1';
    $qs = http_build_query([
        'limit' => 100,
        'include_disabled' => $includeDisabled ? 'true' : null,
    ]);
    $qs = preg_replace('/(&?[^=]+=$)/', '', (string)$qs);

    $url = 'https://v5api.tiltify.com/api/public/campaigns/' . rawurlencode($campaignId) . '/milestones' . ($qs ? ('?' . $qs) : '');
    $resp = http_request_json('GET', $url, $headers);

    if ($resp['status'] < 200 || $resp['status'] >= 300 || !is_array($resp['json'])) {
        throw new RuntimeException('Tiltify milestones fetch failed: HTTP ' . $resp['status']);
    }

    $data = $resp['json']['data'] ?? null;
    if (!is_array($data)) {
        throw new RuntimeException('Unexpected milestones response shape');
    }

    $milestones = [];
    $currency = null;

    foreach ($data as $row) {
        if (!is_array($row)) {
            continue;
        }

        $amount = null;
        $amountCandidates = [
            $row['amount'] ?? null,
            $row['goal'] ?? null,
            $row['target_amount'] ?? null,
            $row['threshold'] ?? null,
        ];
        foreach ($amountCandidates as $cand) {
            $amount = extract_amount_value($cand);
            if ($amount !== null) {
                if ($currency === null) {
                    $currency = extract_currency_code($cand);
                }
                break;
            }
        }

        $name = isset($row['name']) && is_string($row['name']) ? trim($row['name']) : '';
        if ($name === '') {
            $name = isset($row['title']) && is_string($row['title']) ? trim($row['title']) : '';
        }

        $description = '';
        foreach (['description', 'short_description', 'shortDescription'] as $dk) {
            if (isset($row[$dk]) && is_string($row[$dk])) {
                $description = trim((string)$row[$dk]);
                break;
            }
        }

        if ($amount === null || $name === '') {
            continue;
        }

        $milestones[] = [
            'id' => $row['id'] ?? null,
            'name' => $name,
            'description' => $description,
            'amount' => $amount,
        ];
    }

    usort($milestones, static function (array $a, array $b): int {
        $at = (float)($a['amount'] ?? 0);
        $bt = (float)($b['amount'] ?? 0);
        if ($at < $bt) return -1;
        if ($at > $bt) return 1;
        return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
    });

    $response = [
        'ok' => true,
        'source' => 'tiltify',
        'campaign_id' => $campaignId,
        'currency' => $currency,
        'milestones' => $milestones,
        'updated_at' => gmdate('c'),
    ];

    write_json_file_atomic($cachePath, [
        'cached_at' => time(),
        'response' => $response,
    ]);

    echo json_encode($response);
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
}
