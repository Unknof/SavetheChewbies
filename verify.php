<?php

declare(strict_types=1);

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config = is_file($configFile) ? require $configFile : null;

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

$code = isset($_GET['code']) ? (string)$_GET['code'] : '';
$autoFilledFromCookie = false;
if ($code === '' && isset($_COOKIE['stc_verify_code']) && is_string($_COOKIE['stc_verify_code'])) {
  $code = (string)$_COOKIE['stc_verify_code'];
  $autoFilledFromCookie = ($code !== '');
}

$dataDir = is_array($config) && isset($config['data_dir']) ? (string)$config['data_dir'] : (__DIR__ . DIRECTORY_SEPARATOR . 'data');
$relaysPath = $dataDir . DIRECTORY_SEPARATOR . 'relays.json';
$relays = read_json_file($relaysPath);

$status = null;
$charity = null;
$createdAt = null;
$verifiedAt = null;

if ($code !== '' && isset($relays[$code]) && is_array($relays[$code])) {
    $status = (string)($relays[$code]['status'] ?? 'pending');
    $charity = (string)($relays[$code]['charity'] ?? '');
    $createdAt = (string)($relays[$code]['created_at'] ?? '');
    $verifiedAt = (string)($relays[$code]['verified_at'] ?? '');
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Verify donation • Save The Chewbies</title>
    <link rel="stylesheet" href="./assets/styles.css" />
  </head>
  <body>
    <header class="site-header">
      <div class="container header-inner">
        <div class="brand">
          <div class="brand-mark" aria-hidden="true">STC</div>
          <div class="brand-text">
            <div class="brand-name">Save The Chewbies</div>
            <div class="brand-tagline">Donation verification</div>
          </div>
        </div>

        <nav class="nav" aria-label="Primary">
          <a class="nav-link" href="./index.html">Home</a>
          <a class="nav-link" href="./donate.html">Donate</a>
        </nav>
      </div>
    </header>

    <main class="container">
      <section class="page-header">
        <h1>Verify your donation</h1>
        <p class="lead">If you started a verified donation flow, paste your verification code here.</p>
      </section>

      <section class="card">
        <form method="get" action="./verify.php" class="actions">
          <label>
            <span class="note">Verification code</span><br />
            <input name="code" value="<?php echo h($code); ?>" style="padding:10px 12px;border-radius:12px;border:1px solid rgba(255,255,255,0.16);background:rgba(0,0,0,0.25);color:rgba(255,255,255,0.92);min-width:min(520px,100%);" />
          </label>
          <button class="button" type="submit">Check</button>
        </form>
        <?php if ($autoFilledFromCookie): ?>
          <p class="note" style="margin-top:10px;">
            We pre-filled your most recent verification code from this browser.
          </p>
        <?php endif; ?>
      </section>

      <?php if ($code === ''): ?>
        <section class="callout">
          <h2>Don’t have a code?</h2>
          <p>
            Start a verified donation from the Donate page (this uses platform webhooks instead of receipts).
          </p>
        </section>
      <?php elseif ($status === null): ?>
        <section class="callout">
          <h2>Not found</h2>
          <p class="note">That code isn’t known on the server (yet). If you just donated, wait a minute and try again.</p>
        </section>
      <?php else: ?>
        <section class="callout">
          <h2>Status: <?php echo h($status); ?></h2>
          <p class="note">
            Charity: <?php echo h($charity ?? ''); ?><br />
            Started: <?php echo h($createdAt ?? ''); ?><br />
            Verified: <?php echo h($verifiedAt ?? ''); ?>
          </p>
        </section>
      <?php endif; ?>

      <section class="card">
        <h2>How verification works</h2>
        <p>
          This uses a donation platform’s webhook relay mechanism. That means the platform tells our server
          when your donation completes, and we mark the code as verified.
        </p>
      </section>
    </main>
  </body>
</html>
