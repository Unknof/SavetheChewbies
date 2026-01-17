<?php

declare(strict_types=1);

// Script to fetch donations from Tiltify API and populate/update donations.jsonl
// Run this manually to backfill historical donations

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

$dataDir = (string)($config['data_dir'] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'data'));
if (!is_dir($dataDir)) {
    if (!mkdir($dataDir, 0775, true)) {
        echo "ERROR: Could not create data directory: $dataDir\n";
        exit;
    }
}

$donationsLog = $dataDir . DIRECTORY_SEPARATOR . 'donations.jsonl';

echo "=== FETCHING DONATIONS FROM TILTIFY ===\n\n";
echo "Campaign ID: $campaignId\n\n";

try {
    echo "Step 1: Getting access token...\n";
    $token = tiltify_access_token($config);
    echo "✓ Got access token\n\n";

    echo "Step 2: Fetching donations from Tiltify...\n";
    $headers = ['Authorization: Bearer ' . $token];
    
    $allDonations = [];
    $url = 'https://v5api.tiltify.com/api/public/campaigns/' . rawurlencode($campaignId) . '/donations';
    
    // Tiltify API might paginate, so we loop if needed
    $page = 1;
    while ($url && $page <= 10) { // Safety limit of 10 pages
        echo "  Fetching page $page...\n";
        $resp = http_request_json('GET', $url, $headers);
        
        if ($resp['status'] < 200 || $resp['status'] >= 300 || !is_array($resp['json'])) {
            throw new RuntimeException('Tiltify donations fetch failed: HTTP ' . $resp['status']);
        }

        $data = $resp['json']['data'] ?? [];
        if (!is_array($data)) {
            break;
        }

        echo "  Found " . count($data) . " donations on this page\n";
        $allDonations = array_merge($allDonations, $data);

        // Check for pagination
        $links = $resp['json']['links'] ?? [];
        $url = is_array($links) ? ($links['next'] ?? null) : null;
        
        if (!is_string($url) || $url === '') {
            break;
        }
        
        $page++;
    }

    echo "\n✓ Total donations fetched: " . count($allDonations) . "\n\n";

    if (count($allDonations) === 0) {
        echo "No donations found for this campaign.\n";
        exit;
    }

    echo "Step 3: Converting to log format...\n";
    
    $newLog = fopen($donationsLog . '.new', 'w');
    if ($newLog === false) {
        throw new RuntimeException('Could not create new log file');
    }

    $processed = 0;
    foreach ($allDonations as $donation) {
        if (!is_array($donation)) {
            continue;
        }

        $donorName = null;
        $donorComment = null;

        // Extract donor name
        if (isset($donation['donor_name']) && is_string($donation['donor_name'])) {
            $donorName = trim($donation['donor_name']);
        }
        if (empty($donorName) && isset($donation['donor']['name']) && is_string($donation['donor']['name'])) {
            $donorName = trim($donation['donor']['name']);
        }

        // Extract donor message/comment
        if (isset($donation['donor_comment']) && is_string($donation['donor_comment'])) {
            $donorComment = trim($donation['donor_comment']);
        }
        if (empty($donorComment) && isset($donation['comment']) && is_string($donation['comment'])) {
            $donorComment = trim($donation['comment']);
        }

        $logEntry = [
            'received_at' => gmdate('c'),
            'relay_key_id' => 'api-fetch',
            'known_relay' => false,
            'event_type' => 'api:donation:fetched',
            'donation' => [
                'id' => $donation['id'] ?? null,
                'campaign_id' => $donation['campaign_id'] ?? null,
                'cause_id' => $donation['cause_id'] ?? null,
                'amount' => $donation['amount'] ?? null,
                'completed_at' => $donation['completed_at'] ?? null,
                'payment_status' => $donation['completed_at'] ? 'completed' : 'pending',
                'donor_name' => $donorName !== '' ? $donorName : null,
                'donor_message' => $donorComment !== '' ? $donorComment : null,
            ],
        ];

        fwrite($newLog, json_encode($logEntry, JSON_UNESCAPED_SLASHES) . "\n");
        $processed++;

        // Show sample
        if ($processed <= 3) {
            $amt = is_array($donation['amount']) ? ($donation['amount']['value'] ?? '?') : '?';
            $curr = is_array($donation['amount']) ? ($donation['amount']['currency'] ?? '?') : '?';
            $name = $donorName ?: 'Anonymous';
            echo "  - $name: $curr $amt";
            if ($donorComment) {
                echo " (message: " . substr($donorComment, 0, 50) . "...)";
            }
            echo "\n";
        }
    }

    fclose($newLog);

    echo "\n✓ Processed $processed donations\n\n";

    echo "Step 4: Replacing donations.jsonl...\n";
    
    // Backup old log
    if (file_exists($donationsLog)) {
        $backup = $donationsLog . '.backup-' . date('Y-m-d-His');
        if (!rename($donationsLog, $backup)) {
            echo "WARNING: Could not backup old log\n";
        } else {
            echo "  Old log backed up to: $backup\n";
        }
    }

    if (!rename($donationsLog . '.new', $donationsLog)) {
        throw new RuntimeException('Could not replace donations log');
    }

    echo "✓ Donations log updated!\n\n";
    echo "DONE. Your website should now show real donations from Tiltify.\n";

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
