<?php

declare(strict_types=1);

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config = is_file($configFile) ? require $configFile : null;

function text_response(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo $message;
    exit;
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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

function tail_jsonl(string $path, int $maxBytes = 262144, int $maxLines = 200): array
{
    if (!is_file($path)) {
        return [];
    }

    $size = filesize($path);
    if (!is_int($size) || $size <= 0) {
        return [];
    }

    $fp = fopen($path, 'rb');
    if ($fp === false) {
        return [];
    }

    $start = max(0, $size - $maxBytes);
    if (fseek($fp, $start) !== 0) {
        fclose($fp);
        return [];
    }

    $chunk = stream_get_contents($fp);
    fclose($fp);

    if (!is_string($chunk) || $chunk === '') {
        return [];
    }

    $lines = preg_split('/\r\n|\n|\r/', $chunk);
    if (!is_array($lines)) {
        return [];
    }

    // If we started mid-file, the first line might be partial.
    if ($start > 0) {
        array_shift($lines);
    }

    $lines = array_values(array_filter($lines, static fn($l) => is_string($l) && trim($l) !== ''));
    if (count($lines) > $maxLines) {
        $lines = array_slice($lines, -$maxLines);
    }

    $events = [];
    foreach ($lines as $line) {
        $decoded = json_decode($line, true);
        if (is_array($decoded)) {
            $events[] = $decoded;
        }
    }

    return $events;
}

if (!is_array($config)) {
  $exists = is_file($configFile);
  if (!$exists) {
    text_response(500, 'Server not configured. Create config.php from config.example.php');
  }
  text_response(500, 'Invalid config.php (must return a PHP array). Common fix: ensure config.php contains a top-level return [ ... ]; like config.example.php.');
}

$adminKey = (string)($config['admin_key'] ?? '');
if ($adminKey === '') {
    text_response(500, 'Missing admin_key in config.php');
}

$providedKey = '';
if (isset($_GET['key']) && is_string($_GET['key'])) {
    $providedKey = (string)$_GET['key'];
}

if ($providedKey === '' && isset($_SERVER['HTTP_X_STC_ADMIN_KEY'])) {
    $providedKey = (string)$_SERVER['HTTP_X_STC_ADMIN_KEY'];
}

if ($providedKey === '' || !hash_equals($adminKey, $providedKey)) {
    header('WWW-Authenticate: Basic realm="SaveTheChewbies Admin"');
    text_response(401, 'Unauthorized');
}

$dataDir = (string)($config['data_dir'] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'data'));
$relaysPath = $dataDir . DIRECTORY_SEPARATOR . 'relays.json';
$donationsLog = $dataDir . DIRECTORY_SEPARATOR . 'donations.jsonl';

$relays = read_json_file($relaysPath);
$events = tail_jsonl($donationsLog, 262144, 200);

$filterRelay = isset($_GET['relay']) && is_string($_GET['relay']) ? (string)$_GET['relay'] : '';
if ($filterRelay !== '') {
    $events = array_values(array_filter($events, static function ($e) use ($filterRelay) {
        return is_array($e) && isset($e['relay_key_id']) && (string)$e['relay_key_id'] === $filterRelay;
    }));
}

$counts = [
    'pending' => 0,
    'verified' => 0,
    'cancelled' => 0,
    'other' => 0,
];
if (is_array($relays)) {
    foreach ($relays as $relayId => $row) {
        if (!is_array($row)) {
            $counts['other']++;
            continue;
        }
        $status = (string)($row['status'] ?? 'pending');
        if (isset($counts[$status])) {
            $counts[$status]++;
        } else {
            $counts['other']++;
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title data-i18n="admin.title">Admin • Save The Chewbies</title>
    <meta
      name="description"
      content="Admin dashboard for webhook relay and verification status."
      data-i18n-attr-content="admin.meta.description"
    />
    <link rel="icon" href="./assets/icon.png" type="image/png" />
    <link rel="apple-touch-icon" href="./assets/icon.png" />
    <link rel="stylesheet" href="./assets/styles.css" />
  </head>
  <body>
    <header class="site-header">
      <div class="container header-inner">
        <div class="brand">
          <div class="brand-mark" aria-hidden="true"><img src="./assets/icon.png" alt="" width="42" height="42" /></div>
          <div class="brand-text">
            <div class="brand-name" data-i18n="site.name">Save The Chewbies</div>
            <div class="brand-tagline" data-i18n="admin.brandTagline">Admin</div>
          </div>
        </div>
        <nav class="nav" aria-label="Primary">
          <a class="nav-link" href="./index.html" data-i18n="nav.home">Home</a>
          <a class="nav-link" href="./donate.html" data-i18n="nav.donate">Donate</a>
        </nav>
      </div>
    </header>

    <main class="container">
      <section class="page-header">
        <h1 data-i18n="admin.header.title">Webhook admin</h1>
        <p class="lead"><span data-i18n="admin.header.lead">Shows relay statuses and the most recent webhook events seen by this server.</span></p>
      </section>

      <section class="card">
        <h2>Relay status</h2>
        <p class="note">
          Pending: <?php echo (int)$counts['pending']; ?> •
          Verified: <?php echo (int)$counts['verified']; ?> •
          Cancelled: <?php echo (int)$counts['cancelled']; ?> •
          Other: <?php echo (int)$counts['other']; ?>
        </p>
        <p class="note">
          Data dir: <?php echo h($dataDir); ?>
        </p>
      </section>

      <section class="card">
        <h2>Filter</h2>
        <form method="get" class="actions">
          <input type="hidden" name="key" value="<?php echo h($providedKey); ?>" />
          <label>
            <span class="note">Relay key id</span><br />
            <input name="relay" value="<?php echo h($filterRelay); ?>" style="padding:10px 12px;border-radius:12px;border:1px solid rgba(255,255,255,0.16);background:rgba(0,0,0,0.25);color:rgba(255,255,255,0.92);min-width:min(520px,100%);" />
          </label>
          <button class="button" type="submit">Apply</button>
          <a class="button button-secondary" href="./admin.php?key=<?php echo rawurlencode($providedKey); ?>">Clear</a>
        </form>
      </section>

      <section class="card">
        <h2>Recent webhook events</h2>
        <?php if (count($events) === 0): ?>
          <p class="note">No events found yet. Confirm Tiltify is configured to call your endpoint and try a test event.</p>
        <?php else: ?>
          <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:820px;">
              <thead>
                <tr>
                  <th style="text-align:left;padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.14);">received_at</th>
                  <th style="text-align:left;padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.14);">relay_key_id</th>
                  <th style="text-align:left;padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.14);">event_type</th>
                  <th style="text-align:left;padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.14);">payment_status</th>
                  <th style="text-align:left;padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.14);">amount</th>
                  <th style="text-align:left;padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.14);">donor_name</th>
                  <th style="text-align:left;padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.14);">donor_message</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_reverse($events) as $e):
                    $don = isset($e['donation']) && is_array($e['donation']) ? $e['donation'] : [];
                    $relayId = isset($e['relay_key_id']) ? (string)$e['relay_key_id'] : '';
                    $filterLink = './admin.php?key=' . rawurlencode($providedKey) . '&relay=' . rawurlencode($relayId);

                    $donorMessageRaw = (string)($don['donor_message'] ?? '');
                    $donorMessagePreview = $donorMessageRaw;
                    if (mb_strlen($donorMessagePreview, 'UTF-8') > 160) {
                        $donorMessagePreview = mb_substr($donorMessagePreview, 0, 160, 'UTF-8') . '…';
                    }
                ?>
                  <tr>
                    <td style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);" class="note"><?php echo h((string)($e['received_at'] ?? '')); ?></td>
                    <td style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);" class="note"><a href="<?php echo h($filterLink); ?>" class="text-link"><?php echo h($relayId); ?></a></td>
                    <td style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);" class="note"><?php echo h((string)($e['event_type'] ?? '')); ?></td>
                    <td style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);" class="note"><?php echo h((string)($don['payment_status'] ?? '')); ?></td>
                    <td style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);" class="note"><?php echo h(is_array($don['amount'] ?? null) ? json_encode($don['amount'], JSON_UNESCAPED_SLASHES) : (string)($don['amount'] ?? '')); ?></td>
                    <td style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);" class="note"><?php echo h((string)($don['donor_name'] ?? '')); ?></td>
                    <td style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);" class="note" title="<?php echo h($donorMessageRaw); ?>"><?php echo h($donorMessagePreview); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <p class="note" style="margin-top:12px;">
          Source: <?php echo h($donationsLog); ?>
        </p>
      </section>
    </main>
    <script src="./assets/i18n.js"></script>
  </body>
</html>
