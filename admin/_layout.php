<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

function admin_header(string $title, string $active = ''): void
{
    $newInq = (int) db()->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
    $nav = [
        'index.php'     => ['Přehled', 'M3 12l9-8 9 8M5 10v10h14V10'],
        'projects.php'  => ['Reference', 'M3 7h18M3 12h18M3 17h12'],
        'reviews.php'   => ['Hodnocení', 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z'],
        'inquiries.php' => ['Poptávky', 'M4 6h16v12H4zM4 6l8 7 8-7'],
        'settings.php'  => ['Nastavení', 'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.7 1.7 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.8-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1.1-1.5 1.7 1.7 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.8 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1.1 1.7 1.7 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.8.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.8V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z'],
    ];
    ?><!doctype html>
<html lang="cs">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Administrace create4you</title>
<link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="shell">
  <aside class="side">
    <a class="brand" href="index.php"><span class="logo-dot"></span><span>create<b>4</b>you</span> <small>admin</small></a>
    <nav>
      <?php foreach ($nav as $href => [$label, $icon]): ?>
        <a href="<?= $href ?>" class="<?= $active === $href ? 'on' : '' ?>">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $icon ?>"/></svg>
          <?= $label ?>
          <?php if ($href === 'inquiries.php' && $newInq): ?><span class="pill"><?= $newInq ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <a href="../" target="_blank">↗ Zobrazit web</a>
      <a href="logout.php">Odhlásit se</a>
    </div>
  </aside>
  <main class="main">
    <header class="top"><h1><?= e($title) ?></h1></header>
    <?php if ($f = flash()): ?><div class="flash <?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>
<?php
}

function admin_footer(): void
{
    ?>
  </main>
</div>
<script src="admin.js"></script>
</body>
</html>
<?php
}
