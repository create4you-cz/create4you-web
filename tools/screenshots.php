<?php
/**
 * Pořídí screenshoty (počítač + mobil) všech referencí s vyplněným odkazem a uloží je jako titulní obrázky.
 * Použití:  php tools/screenshots.php            – jen reference bez screenshotu
 *           php tools/screenshots.php --all      – přefotit všechny
 *           php tools/screenshots.php accuwise   – jen konkrétní reference (slug)
 * Vyžaduje Node.js a Playwright (npm i playwright && npx playwright install chromium).
 */
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$args = array_slice($argv, 1);
$all = in_array('--all', $args, true);
$only = array_values(array_filter($args, fn ($a) => $a[0] !== '-'));
$pdo = db();

foreach ($pdo->query("SELECT * FROM projects WHERE url != ''")->fetchAll() as $p) {
    if ($only && !in_array($p['slug'], $only, true)) continue;
    foreach (['cover' => 'desktop', 'cover_mobile' => 'mobile'] as $col => $mode) {
        if ($p[$col] && !$all && !$only) continue;
        $tmp = sys_get_temp_dir() . '/c4y-' . $p['slug'] . "-$mode.png";
        echo "→ {$p['title']} ($mode) … ";
        exec('node ' . escapeshellarg(__DIR__ . '/shot.js') . ' ' . escapeshellarg($p['url']) . ' ' . escapeshellarg($tmp) . ' ' . $mode . ' 2>&1', $out, $code);
        if ($code !== 0 || !is_file($tmp)) {
            echo "chyba: " . implode(' ', $out) . "\n";
            $out = [];
            continue;
        }
        $img = imagecreatefrompng($tmp);
        if ($mode === 'mobile') { // zmenšit z 2× na rozumnou velikost
            $img = imagescale($img, 600);
        }
        $file = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.webp';
        imagewebp($img, $CONFIG['upload_dir'] . '/' . $file, 80);
        imagedestroy($img);
        unlink($tmp);
        delete_upload($p[$col]);
        $pdo->prepare("UPDATE projects SET $col = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$file, $p['id']]);
        echo "ok\n";
    }
}
