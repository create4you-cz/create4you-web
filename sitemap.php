<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
$base = rtrim($CONFIG['site_url'], '/');
$rows = db()->query('SELECT slug, updated_at FROM projects WHERE visible = 1')->fetchAll();
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc><?= e($base) ?>/</loc><priority>1.0</priority></url>
<?php foreach ($rows as $r): ?>
  <url><loc><?= e($base . '/reference.php?slug=' . rawurlencode($r['slug'])) ?></loc><lastmod><?= e(substr($r['updated_at'], 0, 10)) ?></lastmod></url>
<?php endforeach; ?>
  <url><loc><?= e($base) ?>/zasady.php</loc><priority>0.2</priority></url>
</urlset>
