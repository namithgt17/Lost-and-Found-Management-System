<?php
require __DIR__ . '/config.php';
$csrf = csrf_token();

// Leave / change USN
if (isset($_GET['change'])) { unset($_SESSION['usn']); header('Location: index.php'); exit; }

// USN entry (no password, no login - just identifies the submitter)
$gateError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'enter') {
    $u = strtoupper(trim($_POST['usn'] ?? ''));
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) $gateError = 'Session expired. Try again.';
    elseif (!preg_match('/^[0-9][A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{3}$/', $u)) $gateError = 'Invalid USN.';
    else { $_SESSION['usn'] = $u; header('Location: index.php'); exit; }
}
$usn = $_SESSION['usn'] ?? '';

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$old   = $_SESSION['old'] ?? [];     unset($_SESSION['old']);
function old($k, $type) { global $old; return (($old['type'] ?? '') === $type) ? e($old[$k] ?? '') : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#0a0a0a">
  <title>Dr. Ambedkar Institute of Technology - Lost &amp; Found</title>
  <link rel="preload" href="fonts/HelveticaNeue-Roman.woff" as="font" type="font/woff" crossorigin>
  <link rel="preload" href="fonts/HelveticaNeue-Bold.woff" as="font" type="font/woff" crossorigin>
  <link rel="icon" type="image/png" href="favicon.png?v=<?= filemtime(__DIR__ . '/favicon.png') ?>">
  <link rel="apple-touch-icon" href="favicon.png?v=<?= filemtime(__DIR__ . '/favicon.png') ?>">
  <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
</head>
<body id="top" class="<?= $usn ? 'in' : 'gate-page' ?>">
<div class="window-sunlight" aria-hidden="true"></div>

<?php if (!$usn): ?>
<!-- ============ USN ENTRY SCREEN ============ -->
<main class="gate">
  <div class="gate-box">
    <p class="eyebrow">Lost &amp; Found</p>
    <h1>Dr. Ambedkar Institute of Technology</h1>
    <form method="post" action="index.php">
      <input type="hidden" name="action" value="enter">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <label>Enter your USN to continue
        <input name="usn" required autofocus autocomplete="off" autocapitalize="characters" maxlength="10" pattern="[0-9][A-Za-z]{2}[0-9]{2}[A-Za-z]{2}[0-9]{3}" placeholder="e.g. 1DA22CS001" oninput="this.value = this.value.toUpperCase()" style="text-transform: uppercase;">
      </label>
      <?php if ($gateError): ?><p class="gate-err"><?= e($gateError) ?></p><?php endif; ?>
      <button class="btn solid block" type="submit">Continue</button>
    </form>
    <p class="hint">No password needed. Your USN is saved with the items you add.</p>
  </div>
</main>

<?php else:
$lost = $found = []; $dbError = false;
try {
    $lost  = db()->query("SELECT * FROM lost_items ORDER BY id DESC")->fetchAll();
    $found = db()->query("SELECT * FROM found_items ORDER BY id DESC")->fetchAll();
} catch (Throwable $ex) { $dbError = true; }

function render_form(string $type, string $csrf, string $usn): void {
    $isLost = $type === 'lost'; ?>
    <form class="panel" id="<?= $type ?>-form" action="submit.php" method="post" enctype="multipart/form-data">
      <h2><?= $isLost ? 'Add a Lost Item' : 'Add a Found Item' ?></h2>
      <input type="hidden" name="type" value="<?= $type ?>">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <label>Name of the Object
        <input name="object_name" required maxlength="150" value="<?= old('object_name', $type) ?>">
      </label>
      <label><?= $isLost ? 'Last Seen Location' : 'Location Found' ?>
        <input name="location_found" required maxlength="200" value="<?= old('location_found', $type) ?>">
      </label>
      <?php if (!$isLost): ?>
      <label>Collect Location
        <input name="collect_location" required maxlength="200" placeholder="Where the owner can collect it" value="<?= old('collect_location', $type) ?>">
      </label>
      <?php endif; ?>
      <label><?= $isLost ? 'If found, contact this number' : 'Phone Number' ?>
        <input name="phone" type="tel" inputmode="tel" required maxlength="15" placeholder="10-digit number" value="<?= old('phone', $type) ?>">
      </label>
      <label>Photo
        <input name="photo" type="file" accept="image/*">
      </label>
      <div class="row">
        <button type="submit" class="btn solid">Submit</button>
        <a class="btn" href="#top">Cancel</a>
      </div>
    </form>
<?php }

function render_feed(array $items, string $label): void {
    $isLost = $label === 'lost'; ?>
    <div class="items">
    <?php if (!$items): ?><p class="empty">No <?= e($label) ?> items yet.</p><?php endif;
    foreach ($items as $i => $it): ?>
      <article class="card" style="--i:<?= min($i, 10) ?>">
        <?php if ($it['photo']): ?>
          <a class="imgwrap" href="<?= e($it['photo']) ?>" target="_blank" rel="noopener"><img src="<?= e($it['photo']) ?>" alt="<?= e($it['object_name']) ?>" loading="lazy" decoding="async"></a>
        <?php else: ?><div class="noimg">No photo</div><?php endif; ?>
        <div class="body">
          <h3><?= e($it['object_name']) ?></h3>
          <dl>
            <dt><?= $isLost ? 'Last Seen Location' : 'Location Found' ?></dt><dd><?= e($it['location_found']) ?></dd>
            <?php if (!$isLost && !empty($it['collect_location'])): ?>
            <dt>Collect Location</dt><dd><?= e($it['collect_location']) ?></dd>
            <?php endif; ?>
            <dt><?= $isLost ? 'If found, contact' : 'Phone' ?></dt><dd><a href="tel:<?= e($it['phone']) ?>"><?= e($it['phone']) ?></a></dd>
          </dl>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
<?php } ?>

<!-- ============ MAIN ============ -->
<header class="hero">
  <div class="wrap">
    <div class="topbar">
      <span class="who">Logged in</span>
      <a href="index.php?change=1">Change USN</a>
    </div>
    <h1>Dr. Ambedkar Institute of Technology</h1>
    <p class="sub">Lost &amp; Found</p>
    <div class="row actions">
      <a class="btn solid" href="#lost-form">Add a Lost Item</a>
      <a class="btn" href="#found-form">Add a Found Item</a>
    </div>
  </div>
</header>

<main class="wrap">
  <?php if ($flash): ?><div class="flash <?= $flash['ok'] ? 'ok' : 'err' ?>" role="status"><?= e($flash['msg']) ?></div><?php endif; ?>
  <?php if ($dbError): ?><div class="flash err">Could not connect to the database. Check config.php and database.sql.</div><?php endif; ?>

  <section class="forms">
    <?php render_form('lost', $csrf, $usn); render_form('found', $csrf, $usn); ?>
  </section>

  <div class="feed-controls">
    <div class="tab-group" role="tablist">
      <button type="button" class="tab-btn active" data-target="lost-section">Lost Items <span class="badge"><?= count($lost) ?></span></button>
      <button type="button" class="tab-btn" data-target="found-section">Found Items <span class="badge"><?= count($found) ?></span></button>
      <button type="button" class="tab-btn" data-target="all">All Items <span class="badge"><?= count($lost) + count($found) ?></span></button>
    </div>
    <div class="search-wrap">
      <input type="text" id="feed-search" placeholder="Search by item name or location..." autocomplete="off">
    </div>
  </div>

  <section id="lost-section" class="feed-section">
    <h2 class="section-title">Lost Items <span><?= count($lost) ?></span></h2>
    <?php render_feed($lost, 'lost'); ?>
  </section>

  <section id="found-section" class="feed-section" style="display: none;">
    <h2 class="section-title">Found Items <span><?= count($found) ?></span></h2>
    <?php render_feed($found, 'found'); ?>
  </section>
</main>
<?php endif; ?>

<footer>
  <div class="wrap">
    <p class="team">Team Blaze Forge</p>
    <ul>
      <li><a href="https://www.linkedin.com/in/namith-g-t-8164a842a?utm_source=share_via&amp;utm_content=profile&amp;utm_medium=member_android" target="_blank" rel="noopener">Namith G T</a></li>
      <li>Aarush Pandey</li>
      <li>Parth</li>
      <li>Pradeep</li>
    </ul>
  </div>
</footer>

<script>
(function() {
  const tabs = document.querySelectorAll('.tab-btn');
  const lostSec = document.getElementById('lost-section');
  const foundSec = document.getElementById('found-section');
  const searchInput = document.getElementById('feed-search');

  function switchTab(target) {
    tabs.forEach(t => t.classList.toggle('active', t.dataset.target === target));
    if (target === 'all') {
      if (lostSec) lostSec.style.display = 'block';
      if (foundSec) foundSec.style.display = 'block';
    } else if (target === 'found-section') {
      if (lostSec) lostSec.style.display = 'none';
      if (foundSec) foundSec.style.display = 'block';
    } else {
      if (lostSec) lostSec.style.display = 'block';
      if (foundSec) foundSec.style.display = 'none';
    }
  }

  tabs.forEach(btn => {
    btn.addEventListener('click', () => switchTab(btn.dataset.target));
  });

  // If user clicks "Add a Found Item", also auto switch to found tab
  document.querySelectorAll('a[href="#found-form"]').forEach(a => {
    a.addEventListener('click', () => switchTab('found-section'));
  });
  document.querySelectorAll('a[href="#lost-form"]').forEach(a => {
    a.addEventListener('click', () => switchTab('lost-section'));
  });

  // Instant live search filter
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.toLowerCase().trim();
      document.querySelectorAll('.card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }
})();
</script>

</body>
</html>
