<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$pdo = db();
$projects = $pdo->query('SELECT * FROM projects WHERE visible = 1 ORDER BY sort, id DESC')->fetchAll();
$reviews  = $pdo->query('SELECT r.*, p.title AS project_title, p.slug AS project_slug FROM reviews r
                         LEFT JOIN projects p ON p.id = r.project_id AND p.visible = 1
                         WHERE r.visible = 1 ORDER BY r.sort, r.id DESC')->fetchAll();
$realReviews = array_filter($reviews, fn ($r) => !$r['is_demo']);
$avg = $realReviews ? array_sum(array_column($realReviews, 'rating')) / count($realReviews) : 5.0;
$years = setting('stat_years') ?: (string) (date('Y') - 2019);
$priceTpl = setting('price_template');
$usedCats = array_unique(array_column($projects, 'category'));

site_head(
    'create4you – weby, aplikace a e-shopy na míru | Web do 48 hodin',
    'Tvoříme weby na míru, aplikace a CRM pro firmy, e-shopy na Shoptetu i na míru. Šablonový web hotový do 48 hodin za ' . $priceTpl . ' Kč měsíčně včetně domény, e-mailů a správy.'
);
site_nav();
?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ProfessionalService',
    'name' => 'create4you',
    'url' => $CONFIG['site_url'],
    'email' => setting('contact_email'),
    'telephone' => setting('contact_phone') ?: null,
    'founder' => setting('contact_name'),
    'areaServed' => ['CZ', 'SK'],
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Ostrava', 'addressCountry' => 'CZ'],
    'priceRange' => 'od ' . $priceTpl . ' Kč/měs',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<main>
<!-- ============ HERO ============ -->
<section class="hero">
  <div class="mesh" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
  <div class="grid-lines" aria-hidden="true"></div>
  <div class="wrap hero-in">
    <div class="hero-copy">
      <a class="eyebrow reveal" href="#sablony"><span class="dot"></span> Nově: web do 48 hodin za <?= e($priceTpl) ?> Kč/měs <span aria-hidden="true">→</span></a>
      <h1 class="split">
        <span class="line">Digitální řešení,</span>
        <span class="line">která <em class="rotator" aria-live="polite"><span>pracují za vás.</span><span>prodávají.</span><span>šetří čas.</span><span>dávají smysl.</span></em></span>
      </h1>
      <p class="lead reveal">Weby na míru, aplikace a CRM pro řízení firmy, e-shopy na Shoptetu i na míru. Od domény a firemních e-mailů až po administraci a správu – <strong>vše pod jednou střechou</strong>.</p>
      <div class="hero-ctas reveal">
        <a class="btn btn-primary btn-lg magnetic" href="#kontakt"><span>Chci nezávaznou nabídku</span></a>
        <a class="btn btn-ghost btn-lg magnetic" href="#reference"><span>Ukázat reference</span></a>
      </div>
      <ul class="proof reveal">
        <li><b><?= number_format($avg, 1, ',', '') ?></b> <?= stars($avg) ?><small>hodnocení klientů</small></li>
        <li><b><span><span data-count="<?= (int) setting('stat_projects') ?>">0</span>+</span></b><small>dokončených projektů</small></li>
        <li><b><span data-count="<?= (int) $years ?>">0</span> let</b><small>zkušeností</small></li>
      </ul>
    </div>

    <div class="hero-visual" data-tilt-group aria-hidden="true">
      <div class="float f-dash tilt">
        <div class="fd-head"><span>Přehled zakázek</span><em>Živě</em></div>
        <div class="fd-kpis">
          <div><small>Tržby</small><b>248 600 Kč</b><i class="up">+18 %</i></div>
          <div><small>Zakázky</small><b>34</b><i class="up">+6</i></div>
        </div>
        <svg class="fd-chart" viewBox="0 0 300 90" preserveAspectRatio="none">
          <defs><linearGradient id="g1" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="currentColor" stop-opacity=".35"/><stop offset="1" stop-color="currentColor" stop-opacity="0"/></linearGradient></defs>
          <path class="area" d="M0 80 C40 70 60 40 100 48 S160 20 200 30 S260 6 300 10 V90 H0Z" fill="url(#g1)"/>
          <path class="stroke" d="M0 80 C40 70 60 40 100 48 S160 20 200 30 S260 6 300 10" fill="none" stroke="currentColor" stroke-width="3"/>
        </svg>
        <div class="fd-table">
          <div><em style="--c:#4f8bff">NS</em><span><b>Novák stavby</b><small>Web + e-maily</small></span><strong>38 000 Kč</strong><i class="chip ok">Hotovo</i></div>
          <div><em style="--c:#2fd6a3">ÚB</em><span><b>Účetní Bílá</b><small>CRM pro kancelář</small></span><strong>126 000 Kč</strong><i class="chip run">Vývoj</i></div>
          <div><em style="--c:#ff7a2f">KM</em><span><b>Kavárna U Mlýna</b><small>Web do 48 h</small></span><strong>700 Kč/m</strong><i class="chip new">Nová</i></div>
        </div>
      </div>
      <div class="float f-pay tilt"><span class="ok-ic">✓</span><div><b>Platba přijata</b><small>Faktura 2026-118 · 12 400 Kč</small></div></div>
      <div class="float f-lead tilt"><span class="av">JK</span><div><b>Nová poptávka</b><small>„Potřebujeme e-shop na Shoptetu…“</small></div><i>teď</i></div>
      <div class="float f-48 tilt"><b>48 h</b><small>od objednávky<br>po spuštění</small></div>
    </div>
  </div>
  <a class="scroll-hint" href="#sluzby" aria-label="Pokračovat dolů"><span></span></a>
</section>

<!-- ============ MARQUEE ============ -->
<div class="marquee" aria-label="Reference a technologie">
  <div class="mq-track">
    <?php for ($k = 0; $k < 2; $k++): ?>
    <div class="mq-group" <?= $k ? 'aria-hidden="true"' : '' ?>>
      <?php foreach ($projects as $p): ?><span><?= e($p['title']) ?></span><i>✦</i><?php endforeach; ?>
      <span>Shoptet</span><i>✦</i><span>Domény .cz .sk .eu</span><i>✦</i><span>Firemní e-maily</span><i>✦</i><span>Aplikace na míru</span><i>✦</i>
    </div>
    <?php endfor; ?>
  </div>
</div>

<!-- ============ SLUŽBY ============ -->
<section class="section" id="sluzby">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="kicker">01 — Služby</span>
      <h2>Co pro vás <em>vytvoříme</em></h2>
      <p>Neprodáváme jen „web“. Dodáme kompletní řešení – a staráme se o něj i po spuštění.</p>
    </div>

    <div class="bento">
      <article class="tile t-web spot reveal">
        <div class="t-ic"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M7 6.5h.01M10 6.5h.01"/></svg></div>
        <h3>Weby na míru</h3>
        <p>Firemní prezentace s vlastním designem, rychlým načítáním a administrací, ve které si obsah upravíte sami. Včetně zřízení e-mailů, domény a komunikace.</p>
        <ul class="chips"><li>Design na míru</li><li>Administrace</li><li>SEO</li><li>E-maily</li><li>Hosting</li></ul>
        <div class="t-mock site-mock" aria-hidden="true">
          <div class="wm-bar"><i></i><i></i><i></i><span>www.horak-montaze.cz</span></div>
          <div class="sm-scroll">
            <div class="sm-page">
              <div class="sm-nav"><b>HORÁK<span>montáže</span></b><i>Služby</i><i>Realizace</i><i>Kontakt</i><em>Poptávka</em></div>
              <div class="sm-hero"><div><small>Ocelové konstrukce · Ostrava</small><h5>Svařujeme, montujeme, dodáváme včas.</h5><span class="sm-btn">Nezávazná poptávka →</span></div><div class="sm-photo"><i></i><i></i><i></i></div></div>
              <div class="sm-stats"><div><b>320+</b><small>zakázek</small></div><div><b>15 let</b><small>praxe</small></div><div><b>24 h</b><small>reakce</small></div></div>
              <div class="sm-cards"><div><span>🔩</span><b>Ocelové konstrukce</b><small>Haly, přístřešky, schodiště</small></div><div><span>🔥</span><b>Svařování MIG/TIG</b><small>Certifikovaní svářeči</small></div><div><span>🏗️</span><b>Montáže na stavbě</b><small>Celá ČR a SK</small></div></div>
              <div class="sm-review">★★★★★ <span>„Konstrukce hotová o týden dřív, než jsme čekali.“</span></div>
              <div class="sm-foot"><b>Napište nám</b><span>info@horak-montaze.cz</span></div>
            </div>
          </div>
        </div>
      </article>

      <article class="tile t-app spot reveal">
        <div class="t-ic"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg></div>
        <h3>Aplikace &amp; CRM na míru</h3>
        <p>Systémy pro řízení firem, CRM pro kanceláře, klientské portály, platby, fakturace, AI nástroje. Přesně podle toho, jak pracujete – ne naopak.</p>
        <div class="mini-app" aria-hidden="true">
          <div class="ma-row"><em style="--c:#4f8bff">JN</em><span><b>Rekonstrukce bytu</b><small>Termín 12. 11.</small></span><i class="chip run">Probíhá</i></div>
          <div class="ma-row hot"><em style="--c:#2fd6a3">PB</em><span><b>Daňové přiznání</b><small>Klient: Bílá</small></span><i class="chip new">Nové</i></div>
          <div class="ma-row"><em style="--c:#ff7a2f">MH</em><span><b>Montáž haly B</b><small>Fakturováno</small></span><i class="chip ok">Hotovo</i></div>
          <div class="ma-bar"><span>Vytížení týmu</span><i><b></b></i><strong>78 %</strong></div>
        </div>
      </article>

      <article class="tile t-48 spot reveal">
        <span class="badge-lime">Nejoblíbenější</span>
        <h3>Šablonový web<br>do 48 hodin</h3>
        <div class="price-big"><b><?= e($priceTpl) ?></b><span>Kč / měsíc</span></div>
        <p>Včetně domény .cz / .sk / .eu, e-mailů, hostingu a kompletní správy.</p>
        <a class="link-arrow" href="#sablony">Jak to funguje <span>→</span></a>
      </article>

      <article class="tile t-shop spot reveal">
        <div class="t-ic"><svg viewBox="0 0 24 24"><path d="M3 4h2l2.4 11.2a2 2 0 002 1.6h7.7a2 2 0 002-1.5L21 8H6"/><circle cx="10" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg></div>
        <h3>E-shopy</h3>
        <p>Na platformě <b>Shoptet</b> pro rychlý start, nebo plně <b>na míru</b> s napojením na sklad, účetnictví a dopravce.</p>
        <div class="mini-shop" aria-hidden="true">
          <div><i style="--c:#2f6df6"></i><b>Hrnek</b><small>490 Kč</small></div>
          <div><i style="--c:#e0661b"></i><b>Miska</b><small>360 Kč</small></div>
          <div><i style="--c:#2fae7a"></i><b>Váza</b><small>890 Kč</small></div>
        </div>
        <a class="link-arrow" href="#eshopy">Porovnat možnosti <span>→</span></a>
      </article>

      <article class="tile t-care spot reveal">
        <h3>Kompletní servis</h3>
        <div class="domain" aria-hidden="true">
          <div class="dm-input"><span class="dm-type" data-domains="vasefirma,novak-stavby,kavarna-mlyn,studio-aura"></span><span class="dm-caret"></span></div>
          <div class="dm-res"><span>.cz <b>✓ volná</b></span><span>.sk <b>✓ volná</b></span><span>.eu <b>✓ volná</b></span></div>
        </div>
        <ul class="checks">
          <li>Registrace domén .cz .sk .eu</li>
          <li>Firemní e-maily a jejich nastavení</li>
          <li>Hosting, SSL a denní zálohy</li>
          <li>Admin přístupy a školení</li>
          <li>Průběžná správa a úpravy</li>
        </ul>
      </article>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/demos.php'; ?>

<!-- ============ REFERENCE ============ -->
<section class="section" id="reference">
  <div class="wrap">
    <div class="sec-head row reveal">
      <div>
        <span class="kicker">03 — Reference</span>
        <h2>Na čem jsme <em>pracovali</em></h2>
      </div>
      <div class="filters" role="tablist" aria-label="Filtr referencí">
        <button class="on" data-filter="all" role="tab" aria-selected="true">Vše <sup><?= count($projects) ?></sup></button>
        <?php foreach (CATEGORIES as $k => $label): if (!in_array($k, $usedCats, true)) continue; ?>
          <button data-filter="<?= $k ?>" role="tab" aria-selected="false"><?= e($label) ?> <sup><?= count(array_filter($projects, fn ($p) => $p['category'] === $k)) ?></sup></button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="work-grid">
      <?php foreach ($projects as $p): ?>
        <a class="work <?= $p['featured'] ? 'big' : '' ?> reveal" data-cat="<?= e($p['category']) ?>" href="reference.php?slug=<?= e($p['slug']) ?>" data-cursor="Detail" style="--accent:<?= e($p['accent']) ?>">
          <div class="work-media"><?= project_visual($p) ?></div>
          <div class="work-info">
            <div>
              <span class="work-cat"><?= e(CATEGORIES[$p['category']] ?? '') ?><?= $p['year'] ? ' · ' . e($p['year']) : '' ?></span>
              <h3><?= e($p['title']) ?></h3>
              <p><?= e($p['excerpt']) ?></p>
            </div>
            <span class="work-go" aria-hidden="true">↗</span>
          </div>
          <?php if ($p['status'] === 'progress'): ?><span class="live-badge"><i></i>Právě děláme</span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ ŠABLONOVÉ WEBY 48 H ============ -->
<section class="section s-48" id="sablony">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="kicker">04 — Web do 48 hodin</span>
      <h2>Profesionální web <em>bez vstupní investice</em></h2>
      <p>Pro živnostníky, řemeslníky, kanceláře a malé firmy. Vyberete styl, pošlete texty a fotky – my se postaráme o zbytek.</p>
    </div>

    <div class="tpl-layout">
      <div class="tpl-demo reveal">
        <div class="tpl-tabs" role="tablist">
          <button class="on" data-tpl="remeslo" role="tab">Řemeslo</button>
          <button data-tpl="sluzby" role="tab">Služby &amp; kancelář</button>
          <button data-tpl="gastro" role="tab">Kavárna &amp; gastro</button>
          <button data-tpl="beauty" role="tab">Beauty &amp; zdraví</button>
        </div>
        <div class="tpl-frame" data-style="remeslo">
          <div class="tf-bar"><i></i><i></i><i></i><span class="tf-url">www.<b>vasefirma</b>.cz</span></div>
          <div class="tf-page">
            <div class="tf-nav"><b class="tf-logo">Novák</b><span></span><span></span><span></span><em>Kontakt</em></div>
            <div class="tf-hero">
              <div>
                <small class="tf-kicker">Ostrava a okolí</small>
                <h4 class="tf-title">Kvalitní práce, na kterou se můžete spolehnout</h4>
                <p class="tf-sub">Rychlé termíny, férové ceny a záruka na každou zakázku.</p>
                <span class="tf-btn">Nezávazná poptávka</span>
              </div>
              <div class="tf-img"></div>
            </div>
            <div class="tf-cards"><i></i><i></i><i></i></div>
          </div>
        </div>
        <p class="tpl-note">Interaktivní ukázka – barvy, písma i rozložení vždy přizpůsobíme vaší značce.</p>
      </div>

      <div class="tpl-side">
        <ol class="timeline reveal">
          <li><b>Den 0</b><span>Vyberete styl a pošlete podklady – texty, logo, fotky.</span></li>
          <li><b>Do 24 h</b><span>Připravíme web, nastavíme doménu a e-maily.</span></li>
          <li><b>Do 48 h</b><span>Odsouhlasíte úpravy a web jde online.</span></li>
          <li><b>Dál</b><span>Staráme se o provoz, zálohy a drobné změny.</span></li>
        </ol>
        <div class="tpl-price spot reveal">
          <div class="price-big"><b><?= e($priceTpl) ?></b><span>Kč / měsíc</span></div>
          <p class="muted small"><?= e(setting('price_template_note')) ?></p>
          <ul class="checks two">
            <li>Doména .cz / .sk / .eu</li><li>Firemní e-maily</li><li>Hosting + SSL</li><li>Mobilní verze</li>
            <li>Kontaktní formulář</li><li>Základní SEO</li><li>Denní zálohy</li><li>Správa a úpravy</li>
          </ul>
          <a class="btn btn-primary wide magnetic" href="#kontakt" data-pick="Šablonový web 48 h"><span>Chci web do 48 hodin</span></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ E-SHOPY ============ -->
<section class="section" id="eshopy">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="kicker">05 — E-shopy</span>
      <h2>E-shop, který <em>prodává</em></h2>
      <p>Poradíme, která cesta dává smysl pro váš sortiment a rozpočet. Nemusíte se rozhodovat sami.</p>
    </div>
    <div class="vs">
      <article class="vs-card spot reveal">
        <span class="vs-tag">Rychlý start</span>
        <h3>E-shop na Shoptetu</h3>
        <p>Ověřená česká platforma. Spustíme ji, nastavíme šablonu podle vaší značky, platby, dopravu, napojení na Heureku a Zboží.cz.</p>
        <ul class="checks">
          <li>Spuštění řádově v týdnech</li>
          <li>Úprava šablony a grafiky na míru</li>
          <li>Platební brány, dopravci, feedy</li>
          <li>Import produktů a migrace</li>
          <li>Zaškolení a následná podpora</li>
        </ul>
      </article>
      <div class="vs-or" aria-hidden="true">nebo</div>
      <article class="vs-card alt spot reveal">
        <span class="vs-tag">Bez kompromisů</span>
        <h3>E-shop na míru</h3>
        <p>Pro specifické procesy, B2B ceníky, konfigurátory produktů nebo napojení na vlastní systémy. Bez měsíčních poplatků za platformu.</p>
        <ul class="checks">
          <li>Vlastní design a nákupní proces</li>
          <li>Napojení na sklad, ERP, účetnictví</li>
          <li>B2B, velkoobchod, individuální ceny</li>
          <li>Administrace přesně pro vás</li>
          <li>Kód i data patří vám</li>
        </ul>
      </article>
    </div>
  </div>
</section>

<!-- ============ PROCES ============ -->
<section class="section" id="proces">
  <div class="wrap process">
    <div class="sec-head sticky-head reveal">
      <span class="kicker">06 — Jak pracujeme</span>
      <h2>Jasný postup, <em>žádná překvapení</em></h2>
      <p>Mluvíte přímo s tím, kdo váš projekt dělá. Žádné přeposílání mezi obchodníky.</p>
      <a class="btn btn-ghost magnetic" href="#kontakt"><span>Začít konzultací zdarma</span></a>
    </div>
    <div class="stack">
      <article class="stack-card" style="--i:0"><span class="num">01</span><h3>Konzultace</h3><p>Probereme, co potřebujete, jak fungujete a co má web nebo aplikace řešit. Nezávazně a zdarma.</p></article>
      <article class="stack-card" style="--i:1"><span class="num">02</span><h3>Návrh a nabídka</h3><p>Připravíme návrh řešení s jasnou cenou a termínem. Bez skrytých poplatků.</p></article>
      <article class="stack-card" style="--i:2"><span class="num">03</span><h3>Vývoj</h3><p>Průběžně ukazujeme, jak projekt roste. Zpětnou vazbu zapracujeme hned, ne až na konci.</p></article>
      <article class="stack-card" style="--i:3"><span class="num">04</span><h3>Spuštění a správa</h3><p>Nastavíme doménu, e-maily a přístupy, zaškolíme vás a dál se o vše staráme.</p></article>
    </div>
  </div>
</section>

<!-- ============ CENÍK ============ -->
<section class="section" id="cenik">
  <div class="wrap">
    <div class="sec-head center reveal">
      <span class="kicker">07 — Ceník</span>
      <h2>Férové ceny, <em>jasně předem</em></h2>
      <p>U projektů na míru dostanete pevnou cenovou nabídku ještě před začátkem práce.</p>
    </div>
    <div class="pricing">
      <article class="plan featured spot reveal">
        <span class="badge-lime">Hotovo do 48 h</span>
        <h3>Šablonový web</h3>
        <div class="plan-price"><b><?= e($priceTpl) ?></b> Kč<small>/měsíc</small></div>
        <ul class="checks"><li>Doména, e-maily, hosting</li><li>Mobilní verze, SSL</li><li>Správa a drobné úpravy</li></ul>
        <a class="btn btn-primary wide magnetic" href="#kontakt" data-pick="Šablonový web 48 h"><span>Objednat</span></a>
      </article>
      <article class="plan spot reveal">
        <h3>Web na míru</h3>
        <div class="plan-price"><b class="txt"><?= e(setting('price_custom')) ?></b></div>
        <ul class="checks"><li>Vlastní design a funkce</li><li>Administrace obsahu</li><li>E-maily a komunikace</li></ul>
        <a class="btn btn-ghost wide magnetic" href="#kontakt" data-pick="Web na míru"><span>Poptat</span></a>
      </article>
      <article class="plan spot reveal">
        <h3>Aplikace &amp; CRM</h3>
        <div class="plan-price"><b class="txt"><?= e(setting('price_app')) ?></b></div>
        <ul class="checks"><li>Analýza procesů</li><li>Vývoj přesně na míru</li><li>Provoz a rozvoj</li></ul>
        <a class="btn btn-ghost wide magnetic" href="#kontakt" data-pick="Aplikace / CRM"><span>Poptat</span></a>
      </article>
      <article class="plan spot reveal">
        <h3>E-shop</h3>
        <div class="plan-price"><b class="txt"><?= e(setting('price_eshop')) ?></b></div>
        <ul class="checks"><li>Shoptet nebo na míru</li><li>Platby a doprava</li><li>Napojení na systémy</li></ul>
        <a class="btn btn-ghost wide magnetic" href="#kontakt" data-pick="E-shop"><span>Poptat</span></a>
      </article>
    </div>
  </div>
</section>

<!-- ============ HODNOCENÍ ============ -->
<?php if ($reviews): ?>
<section class="section" id="hodnoceni">
  <div class="wrap">
    <div class="sec-head row reveal">
      <div>
        <span class="kicker">08 — Hodnocení</span>
        <h2>Co říkají <em>klienti</em></h2>
      </div>
      <div class="rating-sum">
        <b><?= number_format($avg, 1, ',', '') ?></b>
        <div><?= stars($avg) ?><small><?= count($realReviews) ?: count($reviews) ?> hodnocení</small></div>
      </div>
    </div>
  </div>
  <?php
  $rows = count($reviews) >= 6 ? [array_slice($reviews, 0, (int) ceil(count($reviews) / 2)), array_slice($reviews, (int) ceil(count($reviews) / 2))] : [$reviews];
  foreach ($rows as $ri => $row): ?>
  <div class="reviews-mq <?= $ri ? 'rev' : '' ?>">
    <div class="rq-track">
      <?php for ($k = 0; $k < 2; $k++): foreach ($row as $r): ?>
        <figure class="review" <?= $k ? 'aria-hidden="true"' : '' ?>>
          <?= stars((float) $r['rating']) ?>
          <blockquote>„<?= e($r['text']) ?>“</blockquote>
          <figcaption>
            <?php if ($r['photo']): ?><img src="<?= e(upload_url($r['photo'])) ?>" alt="" loading="lazy"><?php else: ?><span class="avatar"><?= e(mb_substr($r['author'], 0, 1)) ?></span><?php endif; ?>
            <span><b><?= e($r['author']) ?></b><small><?= e(trim($r['role'] . ', ' . $r['company'], ', ')) ?></small></span>
            <?php if ($r['is_demo']): ?><em class="demo-tag" title="Ukázkové hodnocení – bude nahrazeno skutečným">ukázka</em><?php endif; ?>
          </figcaption>
        </figure>
      <?php endforeach; endfor; ?>
    </div>
  </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- ============ FAQ ============ -->
<section class="section" id="faq">
  <div class="wrap faq-wrap">
    <div class="sec-head reveal">
      <span class="kicker">09 — Časté dotazy</span>
      <h2>Máte <em>otázky?</em></h2>
      <p>Nenašli jste odpověď? <a href="#kontakt">Napište nám</a>, ozveme se do <?= e(setting('stat_response')) ?> hodin.</p>
    </div>
    <div class="faq">
      <details class="reveal" open><summary>Co přesně zahrnuje šablonový web za <?= e($priceTpl) ?> Kč měsíčně?</summary><p>Hotový web podle vybraného stylu upravený na vaši značku, doménu .cz, .sk nebo .eu, firemní e-maily, hosting s SSL certifikátem, denní zálohy a průběžnou správu včetně drobných úprav obsahu. Žádná vstupní investice.</p></details>
      <details class="reveal"><summary>Opravdu bude web hotový do 48 hodin?</summary><p>Ano – jakmile od vás máme podklady (texty, logo, fotky). Pokud podklady nemáte, pomůžeme vám je připravit; pak počítejte s pár dny navíc.</p></details>
      <details class="reveal"><summary>Jaký je rozdíl mezi šablonovým webem a webem na míru?</summary><p>Šablonový web stojí na ověřeném základu, který přizpůsobíme vaší značce – je rychlý a cenově dostupný. Web na míru má vlastní design, funkce a administraci navrženou přesně pro vaše potřeby.</p></details>
      <details class="reveal"><summary>Umíte aplikaci napojit na naše stávající systémy?</summary><p>Ano. Aplikace a CRM běžně napojujeme na účetnictví, platební brány, bankovní výpisy, e-maily, sklad nebo e-shop. Vše probereme při konzultaci.</p></details>
      <details class="reveal"><summary>Shoptet, nebo e-shop na míru?</summary><p>Pro většinu obchodů je Shoptet nejrychlejší a nejvýhodnější start. E-shop na míru doporučujeme při specifických procesech, B2B prodeji nebo napojení na vlastní systémy. Rádi poradíme, co dává smysl právě vám.</p></details>
      <details class="reveal"><summary>Komu patří web a doména?</summary><p>Doménu registrujeme na vás. U webů a aplikací na míru vám patří i obsah a data. Podmínky vždy uvádíme předem ve smlouvě.</p></details>
    </div>
  </div>
</section>

<!-- ============ KONTAKT ============ -->
<section class="section s-contact" id="kontakt">
  <div class="mesh soft" aria-hidden="true"><i></i><i></i><i></i></div>
  <div class="wrap contact">
    <div class="contact-copy reveal">
      <span class="kicker">10 — Kontakt</span>
      <h2>Pojďme vytvořit<br>něco <em>pro vás</em>.</h2>
      <p>Napište, co potřebujete. Ozveme se do <?= e(setting('stat_response')) ?> hodin s návrhem dalšího postupu – nezávazně a zdarma.</p>
      <div class="person">
        <span class="avatar lg"><?= e(mb_substr(setting('contact_name'), 0, 1)) ?></span>
        <div><b><?= e(setting('contact_name')) ?></b><small>zakladatel create4you</small></div>
      </div>
      <ul class="contact-list">
        <li><a href="mailto:<?= e(setting('contact_email')) ?>" class="magnetic"><span>✉</span> <?= e(setting('contact_email')) ?></a></li>
        <?php if (setting('contact_phone')): ?><li><a href="tel:<?= e(preg_replace('~\s+~', '', setting('contact_phone'))) ?>" class="magnetic"><span>☏</span> <?= e(setting('contact_phone')) ?></a></li><?php endif; ?>
        <li><span class="muted">⌖ <?= e(setting('contact_city')) ?></span></li>
      </ul>
    </div>

    <form class="cform reveal" id="cform" action="api/contact.php" method="post" novalidate>
      <?= csrf_field() ?>
      <fieldset>
        <legend>Co potřebujete?</legend>
        <div class="pick" data-name="services">
          <?php foreach (['Web na míru', 'Šablonový web 48 h', 'Aplikace / CRM', 'E-shop', 'Shoptet', 'Správa / e-maily'] as $s): ?>
            <label><input type="checkbox" name="services[]" value="<?= e($s) ?>"><span><?= e($s) ?></span></label>
          <?php endforeach; ?>
        </div>
      </fieldset>
      <fieldset>
        <legend>Orientační rozpočet</legend>
        <div class="pick">
          <?php foreach (['Do 20 tis.', '20–60 tis.', '60–150 tis.', '150 tis. +', 'Nevím'] as $b): ?>
            <label><input type="radio" name="budget" value="<?= e($b) ?>"><span><?= e($b) ?></span></label>
          <?php endforeach; ?>
        </div>
      </fieldset>
      <div class="f2">
        <label class="fl"><input name="name" required autocomplete="name" placeholder=" "><span>Jméno *</span></label>
        <label class="fl"><input name="company" autocomplete="organization" placeholder=" "><span>Firma</span></label>
        <label class="fl"><input type="email" name="email" required autocomplete="email" placeholder=" "><span>E-mail *</span></label>
        <label class="fl"><input type="tel" name="phone" autocomplete="tel" placeholder=" "><span>Telefon</span></label>
      </div>
      <label class="fl"><textarea name="message" rows="4" placeholder=" "></textarea><span>Popište stručně, co máte na mysli</span></label>
      <label class="hp" aria-hidden="true">Nevyplňujte<input name="website" tabindex="-1" autocomplete="off"></label>
      <input type="hidden" name="ts" value="<?= time() ?>">
      <button class="btn btn-primary btn-lg wide magnetic" type="submit"><span>Odeslat poptávku</span></button>
      <p class="form-note">Odesláním souhlasíte se <a href="zasady.php">zpracováním osobních údajů</a> za účelem vyřízení poptávky.</p>
      <div class="form-msg" role="status" aria-live="polite"></div>
    </form>
  </div>
</section>
</main>
<?php site_footer();
