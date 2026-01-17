<?php

declare(strict_types=1);

// Debug script to see what Tiltify API actually returns

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config = is_file($configFile) ? require $configFile : null;

header('Content-Type: text/plain; charset=utf-8');

if (!is_array($config)) {
    echo "ERROR: config.php not found or invalid\n";
    exit;
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

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headerLines = array_merge([
        'Content-Type: application/json',
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

function tiltify_access_token(array $config): string
{
    $clientId = (string)($config['tiltify_client_id'] ?? '');
    $clientSecret = (string)($config['tiltify_client_secret'] ?? '');

    if ($clientId === '' || $clientSecret === '') {
        throw new RuntimeException('Missing Tiltify client ID or secret in config.php');
    }

    $resp = http_post_json('https://v5api.tiltify.com/oauth/token', [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials',
        'scope' => 'public',
    ]);

    if ($resp['status'] !== 200 || !is_array($resp['json'])) {
        throw new RuntimeException('Failed to get Tiltify access token: HTTP ' . $resp['status']);
    }

    $token = $resp['json']['access_token'] ?? '';
    if (!is_string($token) || $token === '') {
        throw new RuntimeException('Invalid token response from Tiltify');
    }

    return $token;
}

$campaignId = (string)($config['tiltify_campaign_id'] ?? '');
if ($campaignId === '') {
    echo "ERROR: tiltify_campaign_id not set in config.php\n";
    exit;
}

echo "=== TILTIFY API DEBUG ===\n\n";
echo "Campaign ID: $campaignId\n\n";

try {
    echo "Step 1: Getting access token...\n";
    $token = tiltify_access_token($config);
    echo "✓ Got access token (length: " . strlen($token) . ")\n\n";

    echo "Step 2: Fetching campaign data...\n";
    $headers = ['Authorization: Bearer ' . $token];
    $url = 'https://v5api.tiltify.com/api/public/campaigns/' . rawurlencode($campaignId);
    echo "URL: $url\n\n";
    
    $resp = http_request_json('GET', $url, $headers);
    
    echo "HTTP Status: {$resp['status']}\n\n";
    
    if ($resp['status'] >= 200 && $resp['status'] < 300 && is_array($resp['json'])) {
        echo "=== FULL JSON RESPONSE ===\n";
        echo json_encode($resp['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        
        echo "=== EXTRACTED DATA ===\n";
        $data = $resp['json']['data'] ?? null;
        if (is_array($data)) {
            $attributes = $data['attributes'] ?? null;
            if (is_array($attributes)) {
                echo "Available attributes:\n";
                foreach (array_keys($attributes) as $key) {
                    echo "  - $key\n";
                }
                echo "\n";
                
                // Check specific fields
                echo "Checking for total fields:\n";
                $totalFields = ['total_raised', 'amount_raised', 'raised_amount', 'total_amount_raised'];
                foreach ($totalFields as $field) {
                    if (isset($attributes[$field])) {
                        $value = $attributes[$field];
                        echo "  ✓ $field = " . json_encode($value) . "\n";
                    } else {
                        echo "  ✗ $field (not found)\n";
                    }
                }
            } else {
                echo "WARNING: data.attributes is not an array or missing\n";
            }
        } else {
            echo "WARNING: data field is not an array or missing\n";
        }
    } else {
        echo "ERROR: Failed to fetch campaign\n";
        echo "Raw response:\n";
        echo $resp['raw'] . "\n";
    }
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
