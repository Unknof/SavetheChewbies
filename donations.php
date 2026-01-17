<?php

declare(strict_types=1);

// Public endpoint to display recent donations
// Returns the latest verified donations with donor names and messages

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config = is_file($configFile) ? require $configFile : null;

header('Content-Type: application/json; charset=utf-8');

if (!is_array($config)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server not configured']);
    exit;
}

function read_donations_log(string $path, int $limit = 50): array
{
    if (!is_file($path)) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }

    // Reverse to get most recent first
    $lines = array_reverse($lines);

    $donations = [];
    foreach ($lines as $line) {
        if (count($donations) >= $limit) {
            break;
        }

        $decoded = json_decode($line, true);
        if (!is_array($decoded)) {
            continue;
        }

        $donations[] = $decoded;
    }

    return $donations;
}

function format_donation_for_display(array $entry): ?array
{
    $donation = $entry['donation'] ?? null;
    if (!is_array($donation)) {
        return null;
    }

    // Only show completed donations
    $paymentStatus = (string)($donation['payment_status'] ?? '');
    if (strcasecmp($paymentStatus, 'completed') !== 0) {
        return null;
    }

    $amount = $donation['amount'] ?? null;
    $amountValue = null;
    $currency = null;

    if (is_array($amount)) {
        $amountValue = $amount['value'] ?? null;
        $currency = $amount['currency'] ?? null;
    }

    // Skip if no valid amount
    if ($amountValue === null || !is_numeric($amountValue)) {
        return null;
    }

    $donorName = $donation['donor_name'] ?? null;
    $donorMessage = $donation['donor_message'] ?? null;

    // Default to "Anonymous" if no name provided
    if (!is_string($donorName) || trim($donorName) === '') {
        $donorName = 'Anonymous';
    }

    $completedAt = $donation['completed_at'] ?? null;
    if (is_string($completedAt) && $completedAt !== '') {
        // Parse and reformat timestamp if valid
        $timestamp = strtotime($completedAt);
        if ($timestamp !== false) {
            $completedAt = gmdate('c', $timestamp);
        }
    }

    return [
        'donor_name' => $donorName,
        'donor_message' => $donorMessage,
        'amount' => (float)$amountValue,
        'currency' => $currency ?? 'USD',
        'completed_at' => $completedAt,
    ];
}

$dataDir = (string)($config['data_dir'] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'data'));
$donationsLog = $dataDir . DIRECTORY_SEPARATOR . 'donations.jsonl';

try {
    // Read recent donations
    $limit = 100; // Read up to 100 recent entries
    $entries = read_donations_log($donationsLog, $limit);

    $displayDonations = [];
    foreach ($entries as $entry) {
        $formatted = format_donation_for_display($entry);
        if ($formatted !== null) {
            $displayDonations[] = $formatted;
        }
    }

    // Limit to most recent 20 for display
    $displayDonations = array_slice($displayDonations, 0, 20);

    echo json_encode([
        'ok' => true,
        'donations' => $displayDonations,
        'count' => count($displayDonations),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
}
