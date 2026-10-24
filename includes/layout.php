<?php
declare(strict_types=1);

function site_head(string $title, string $description, string $canonical = '', string $image = ''): void
{
    global $CONFIG;
    $canonical = $CONFIG['site_url'] . '/' . ltrim($canonical, '/');
    $image = $image ?: $CONFIG['site_url'] . '/assets/img/og.jpg';
    ?><!doctype html>
<html lang="cs">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="cs_CZ">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($image) ?>">
<meta name="theme-color" content="#0b0b0f">
<link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
<script>try{var t=localStorage.getItem('theme');if(t)document.documentElement.dataset.theme=t}catch(e){}</script>
</head>
<body>
<div class="progress" aria-hidden="true"></div>
<div class="cursor" aria-hidden="true"><span></span></div>
<?php
}

function site_nav(bool $home = true): void
{
    $p = $home ? '' : './';
    $links = ['sluzby' => 'Služby', 'ukazky' => 'Ukázky', 'reference' => 'Reference', 'sablony' => 'Web do 48 h', 'eshopy' => 'E-shopy', 'cenik' => 'Ceník'];
    ?>
<header class="nav" id="top">
  <div class="wrap nav-in">
    <a class="logo" href="<?= $p ?: './' ?>" aria-label="create4you – úvod">
      <span class="logo-mark" aria-hidden="true"><i></i><i></i></span><span>create<b>4</b>you</span>
    </a>
    <nav class="nav-links" aria-label="Hlavní navigace">
      <?php foreach ($links as $id => $label): ?><a href="<?= $p ?>#<?= $id ?>" data-spy="<?= $id ?>"><?= $label ?></a><?php endforeach; ?>
      <span class="nav-ink" aria-hidden="true"></span>
    </nav>
    <div class="nav-cta">
      <button class="theme-toggle" type="button" aria-label="Přepnout světlý / tmavý režim">
        <svg class="i-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        <svg class="i-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
      </button>
      <a class="btn btn-primary magnetic" href="<?= $p ?>#kontakt"><span>Nezávazná poptávka</span></a>
      <button class="burger" type="button" aria-label="Menu" aria-expanded="false"><span></span><span></span></button>
    </div>
  </div>
</header>
<?php
}

function site_footer(): void
{
    $since = 2019;
    $socials = array_filter([
        'Facebook'  => setting('social_facebook'),
        'Instagram' => setting('social_instagram'),
        'LinkedIn'  => setting('social_linkedin'),
    ]);
    ?>
<footer class="footer">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <a class="logo" href="./"><span class="logo-mark" aria-hidden="true"><i></i><i></i></span><span>create<b>4</b>you</span></a>
        <p class="muted">Weby, aplikace a e-shopy na míru. Od domény a e-mailů až po správu – vše pod jednou střechou.</p>
      </div>
      <div>
        <h4>Služby</h4>
        <a href="./#sluzby">Weby na míru</a>
        <a href="./#sluzby">Aplikace &amp; CRM</a>
        <a href="./#sablony">Web do 48 hodin</a>
        <a href="./#eshopy">E-shopy &amp; Shoptet</a>
      </div>
      <div>
        <h4>Kontakt</h4>
        <a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a>
        <?php if (setting('contact_phone')): ?><a href="tel:<?= e(preg_replace('~\s+~', '', setting('contact_phone'))) ?>"><?= e(setting('contact_phone')) ?></a><?php endif; ?>
        <span class="muted"><?= e(setting('contact_city')) ?></span>
        <?php foreach ($socials as $name => $url): ?><a href="<?= e($url) ?>" target="_blank" rel="noopener"><?= $name ?></a><?php endforeach; ?>
      </div>
      <div>
        <h4>Provozovatel</h4>
        <span><?= e(setting('company_name')) ?></span>
        <span class="muted">IČO: <?= e(setting('company_ico')) ?><?= setting('company_dic') ? ' · DIČ: ' . e(setting('company_dic')) : '' ?></span>
        <span class="muted"><?= e(setting('company_addr')) ?></span>
        <span class="muted small"><?= e(setting('company_note')) ?></span>
      </div>
    </div>
    <div class="wordmark" aria-hidden="true">create4you</div>
    <div class="foot-bottom">
      <span>© <?= $since ?>–<?= date('Y') ?> create4you.cz</span>
      <span><a href="zasady.php">Ochrana osobních údajů</a> · <a href="admin/" rel="nofollow">Administrace</a></span>
    </div>
  </div>
</footer>
<script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>
<script src="assets/js/demos.js?v=<?= filemtime(__DIR__ . '/../assets/js/demos.js') ?>" defer></script>
</body>
</html>
<?php
}

/** Cesta k souboru na disku pro obrázek z assets/ nebo z uploads/. */
function image_path(string $file): ?string
{
    global $CONFIG;
    if ($file === '') {
        return null;
    }
    $path = str_starts_with($file, 'assets/') ? __DIR__ . '/../' . $file : $CONFIG['upload_dir'] . '/' . $file;
    return is_file($path) ? $path : null;
}

/** Rozměry obrázku [šířka, výška], nebo null. */
function image_size(string $file): ?array
{
    $path = image_path($file);
    $info = $path ? @getimagesize($path) : false;
    return $info ? [$info[0], $info[1]] : null;
}

/** Barva horního okraje obrázku (pro stavový řádek telefonu), např. #ffffff. */
function image_top_color(string $file): string
{
    $path = image_path($file);
    $img = $path ? @imagecreatefromstring((string) file_get_contents($path)) : false;
    if (!$img) {
        return '#ffffff';
    }
    $c = imagecolorsforindex($img, imagecolorat($img, (int) (imagesx($img) / 2), 1));
    imagedestroy($img);
    return sprintf('#%02x%02x%02x', $c['red'], $c['green'], $c['blue']);
}

/** Je barva tmavá? (pro barvu textu ve stavovém řádku) */
function is_dark(string $hex): bool
{
    [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
    return (0.299 * $r + 0.587 * $g + 0.114 * $b) < 140;
}

/**
 * Náhled projektu: okno prohlížeče se screenshotem webu (v přesném poměru stran – nic se neořízne)
 * a telefon s mobilní verzí. Delší screenshot (celá stránka) se v okně při najetí roluje.
 * Bez screenshotu: typografický plakát v barvách projektu.
 */
function project_visual(array $p, string $class = ''): string
{
    $host = $p['url'] ? preg_replace('~^https?://(www\.)?~', '', rtrim($p['url'], '/')) : '';
    $desk = $p['cover'] ?? '';
    $mob  = $p['cover_mobile'] ?? '';

    // Okno prohlížeče
    if ($desk && ($size = image_size($desk))) {
        $ratio = max($size[0] / $size[1], 1.6); // vyšší než 16:10 → roluje se
        $screen = '<div class="scroller" style="aspect-ratio:' . round($ratio, 4) . '"><img src="' . e(upload_url($desk)) . '" alt="' . e($p['title']) . ' – počítač" loading="lazy"></div>';
    } else {
        $tags = array_slice(array_filter(array_map('trim', explode(',', $p['tags']))), 0, 3);
        $screen = '<div class="poster"><span class="poster-cat">' . e(CATEGORIES[$p['category']] ?? '') . '</span>'
                . '<strong>' . e($p['title']) . '</strong>'
                . '<span class="poster-tags">' . implode('', array_map(fn ($t) => '<i>' . e($t) . '</i>', $tags)) . '</span></div>';
    }
    $browser = '<div class="browser"><div class="br-bar"><i></i><i></i><i></i><span class="br-url">'
             . '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0110 0v3M5 11h14v10H5z" fill="none" stroke="currentColor" stroke-width="2"/></svg>'
             . e($host ?: mb_strtolower($p['title'])) . '</span></div>' . $screen . '</div>';

    // Telefon – jen se skutečným mobilním screenshotem
    $phone = '';
    if ($mob && ($ms = image_size($mob))) {
        $top = image_top_color($mob);
        $ratio = max($ms[0] / $ms[1], 0.46); // delší screenshot se roluje
        $phone = '<div class="phone"><div class="ph-frame"><div class="ph-screen">'
               . '<div class="ph-status ' . (is_dark($top) ? 'light' : '') . '" style="background:' . e($top) . '"><b>9:41</b><span class="ph-island"></span>'
               . '<span class="ph-icons"><svg viewBox="0 0 18 12"><rect x="0" y="8" width="3" height="4" rx="1"/><rect x="5" y="5" width="3" height="7" rx="1"/><rect x="10" y="2" width="3" height="10" rx="1"/><rect x="15" y="0" width="3" height="12" rx="1"/></svg>'
               . '<svg viewBox="0 0 26 12"><rect x=".5" y=".5" width="22" height="11" rx="3" fill="none" stroke="currentColor" opacity=".45"/><rect x="2" y="2" width="16" height="8" rx="1.8"/><rect x="23.6" y="4" width="1.6" height="4" rx=".8" opacity=".45"/></svg></span></div>'
               . '<div class="scroller" style="aspect-ratio:' . round($ratio, 4) . '"><img src="' . e(upload_url($mob)) . '" alt="' . e($p['title']) . ' – mobil" loading="lazy"></div>'
               . '<span class="ph-home"></span></div></div></div>';
    }

    return '<div class="devices ' . ($phone ? 'has-phone ' : '') . $class . '" style="--accent:' . e($p['accent']) . '">' . $browser . $phone . '</div>';
}

/** Fotka z assets/img/photos/<název>.(webp|jpg|png), nebo prázdný řetězec, pokud zatím chybí. */
function photo_url(string $name): string
{
    foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
        $f = "assets/img/photos/$name.$ext";
        if (is_file(__DIR__ . '/../' . $f)) {
            return $f;
        }
    }
    return '';
}

function stars(float $rating): string
{
    $full = (int) round($rating);
    return '<span class="stars" aria-label="' . e(number_format($rating, 1, ',', '')) . ' z 5">' . str_repeat('★', $full) . '<span class="off">' . str_repeat('★', 5 - $full) . '</span></span>';
}
