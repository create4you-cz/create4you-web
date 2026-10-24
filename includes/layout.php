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

/**
 * Náhled projektu v rámečku notebooku a telefonu.
 * Se screenshotem: obrázek se při najetí myší roluje (ideální je screenshot celé stránky).
 * Bez screenshotu: typografický plakát v barvách projektu.
 */
function project_visual(array $p, string $class = ''): string
{
    $host = $p['url'] ? preg_replace('~^https?://(www\.)?~', '', rtrim($p['url'], '/')) : '';
    $desk = $p['cover'] ?? '';
    $mob  = $p['cover_mobile'] ?? '';
    $screen = static fn (string $file, string $alt) => '<div class="scroller"><img src="' . e(upload_url($file)) . '" alt="' . e($alt) . '" loading="lazy"></div>';

    if ($desk) {
        $laptop = $screen($desk, $p['title'] . ' – počítač');
    } else {
        $tags = array_slice(array_filter(array_map('trim', explode(',', $p['tags']))), 0, 3);
        $laptop = '<div class="poster"><span class="poster-cat">' . e(CATEGORIES[$p['category']] ?? '') . '</span>'
                . '<strong>' . e($p['title']) . '</strong>'
                . '<span class="poster-tags">' . implode('', array_map(fn ($t) => '<i>' . e($t) . '</i>', $tags)) . '</span></div>';
    }
    $phone = $mob ? $screen($mob, $p['title'] . ' – mobil') : ($desk ? $screen($desk, '') : '<div class="poster small"><strong>' . e($p['title']) . '</strong></div>');

    return '<div class="devices ' . $class . '" style="--accent:' . e($p['accent']) . '">'
         . '<div class="laptop"><div class="lp-screen"><div class="lp-bar"><i></i><i></i><i></i>' . ($host ? '<span>' . e($host) . '</span>' : '') . '</div>' . $laptop . '</div><div class="lp-base"></div></div>'
         . '<div class="phone"><div class="ph-screen">' . $phone . '</div></div>'
         . '</div>';
}

function stars(float $rating): string
{
    $full = (int) round($rating);
    return '<span class="stars" aria-label="' . e(number_format($rating, 1, ',', '')) . ' z 5">' . str_repeat('★', $full) . '<span class="off">' . str_repeat('★', 5 - $full) . '</span></span>';
}
