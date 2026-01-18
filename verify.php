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
    <title data-i18n="verify.meta.title">Verify donation • Save The Chewbies</title>
    <meta
      name="description"
      content="Verify your Tiltify donation with a code."
      data-i18n-attr-content="verify.meta.description"
    />

    <meta property="og:title" content="Verify donation • Save The Chewbies" data-i18n-attr-content="verify.meta.title" />
    <meta
      property="og:description"
      content="Verify your Tiltify donation with a code."
      data-i18n-attr-content="verify.meta.description"
    />
    <meta property="og:image" content="./assets/icon.png" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="Verify donation • Save The Chewbies" data-i18n-attr-content="verify.meta.title" />
    <meta
      name="twitter:description"
      content="Verify your Tiltify donation with a code."
      data-i18n-attr-content="verify.meta.description"
    />
    <meta name="twitter:image" content="./assets/icon.png" />
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
            <div class="brand-tagline" data-i18n="verify.brandTagline">Donation verification</div>
          </div>
        </div>

        <nav class="nav" aria-label="Primary">
          <a class="nav-link" href="./index.html" data-i18n="nav.home">Home</a>
          <a class="nav-link" href="./donate.html" data-i18n="nav.donate">Donate</a>
          <a class="nav-link" href="./privacy.html" data-i18n="nav.privacy">Privacy</a>
          <a class="nav-link" href="./privacy.html#contact" data-i18n="nav.contact">Contact</a>
        </nav>
      </div>
    </header>

    <main class="container">
      <section class="page-header">
        <h1 data-i18n="verify.title">Verify your donation</h1>
        <p class="lead"><span data-i18n="verify.lead">If you started a verified donation flow, paste your verification code here.</span></p>
      </section>

      <section class="card">
        <form method="get" action="./verify.php" class="actions">
          <label>
            <span class="note" data-i18n="verify.form.label">Verification code</span><br />
            <input name="code" value="<?php echo h($code); ?>" style="padding:10px 12px;border-radius:12px;border:1px solid rgba(255,255,255,0.16);background:rgba(0,0,0,0.25);color:rgba(255,255,255,0.92);min-width:min(520px,100%);" />
          </label>
          <button class="button" type="submit" data-i18n="verify.form.submit">Check</button>
        </form>
        <?php if ($autoFilledFromCookie): ?>
          <p class="note" style="margin-top:10px;">
            <span data-i18n="verify.prefilled">We pre-filled your most recent verification code from this browser.</span>
          </p>
        <?php endif; ?>
      </section>

      <?php if ($code === ''): ?>
        <section class="callout">
          <h2 data-i18n="verify.noCode.title">Don’t have a code?</h2>
          <p>
            <span data-i18n="verify.noCode.body">Start a verified donation from the Donate page (this uses platform webhooks instead of receipts).</span>
          </p>
        </section>
      <?php elseif ($status === null): ?>
        <section class="callout">
          <h2 data-i18n="verify.notFound.title">Not found</h2>
          <p class="note"><span data-i18n="verify.notFound.body">That code isn’t known on the server (yet). If you just donated, wait a minute and try again.</span></p>
        </section>
      <?php else: ?>
        <section class="callout">
          <h2><span data-i18n="verify.statusLabel">Status:</span> <?php echo h($status); ?></h2>
          <p class="note">
            <span data-i18n="verify.status.charity">Charity:</span> <?php echo h($charity ?? ''); ?><br />
            <span data-i18n="verify.status.started">Started:</span> <?php echo h($createdAt ?? ''); ?><br />
            <span data-i18n="verify.status.verified">Verified:</span> <?php echo h($verifiedAt ?? ''); ?>
          </p>
        </section>
      <?php endif; ?>

      <section class="card">
        <h2 data-i18n="verify.how.title">How verification works</h2>
        <p>
          <span data-i18n="verify.how.body">This uses a donation platform’s webhook relay mechanism. That means the platform tells our server when your donation completes, and we mark the code as verified.</span>
        </p>
      </section>
    </main>

    <footer class="site-footer">
      <div class="container footer-inner">
        <div>
          <div class="footer-title" data-i18n="site.name">Save The Chewbies</div>
          <div class="footer-sub">© <span id="year"></span></div>
        </div>
        <div class="footer-links">
          <a class="nav-link" href="./privacy.html" data-i18n="footer.privacy">Privacy</a>
          <a class="nav-link" href="./privacy.html#contact" data-i18n="footer.contact">Contact</a>
          <a class="nav-link" href="./acknowledgements.html" data-i18n="footer.ack">Acknowledgements</a>
        </div>
      </div>
    </footer>

    <script src="./assets/i18n.js"></script>
    <script>
      document.getElementById('year').textContent = new Date().getFullYear();
    </script>
  </body>
</html>
