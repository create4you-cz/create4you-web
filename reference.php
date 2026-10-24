<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$pdo = db();
$st = $pdo->prepare('SELECT * FROM projects WHERE slug = ? AND visible = 1');
$st->execute([(string) ($_GET['slug'] ?? '')]);
$p = $st->fetch();
if (!$p) {
    http_response_code(404);
    site_head('Reference nenalezena · create4you', 'Tato reference neexistuje.');
    site_nav(false);
    echo '<main class="section"><div class="wrap center-box"><h1>Reference nenalezena</h1><p class="muted">Možná byla přesunuta nebo skryta.</p><a class="btn btn-primary" href="./#reference"><span>Zpět na reference</span></a></div></main>';
    site_footer();
    exit;
}

$imgs = $pdo->prepare('SELECT * FROM project_images WHERE project_id = ? ORDER BY sort, id');
$imgs->execute([$p['id']]);
$images = $imgs->fetchAll();

$rev = $pdo->prepare('SELECT * FROM reviews WHERE project_id = ? AND visible = 1 ORDER BY sort');
$rev->execute([$p['id']]);
$reviews = $rev->fetchAll();

$all = $pdo->query('SELECT * FROM projects WHERE visible = 1 ORDER BY sort, id DESC')->fetchAll();
$idx = array_search($p['id'], array_column($all, 'id'));
$next = $all[($idx + 1) % count($all)];

site_head(
    $p['title'] . ' – reference · create4you',
    $p['excerpt'] ?: 'Reference ' . $p['title'] . ' od create4you.',
    'reference.php?slug=' . $p['slug'],
    $p['cover'] ? $CONFIG['site_url'] . '/' . upload_url($p['cover']) : ''
);
site_nav(false);
$tags = array_filter(array_map('trim', explode(',', $p['tags'])));
?>
<main class="case" style="--accent:<?= e($p['accent']) ?>">
  <section class="case-hero">
    <div class="mesh soft" aria-hidden="true"><i></i><i></i></div>
    <div class="wrap">
      <a class="back" href="./#reference">← Všechny reference</a>
      <span class="kicker"><?= e(CATEGORIES[$p['category']] ?? '') ?><?= $p['status'] === 'progress' ? ' · <span class="live-dot"></span> právě děláme' : '' ?></span>
      <h1 class="split"><span class="line"><?= e($p['title']) ?></span></h1>
      <p class="lead reveal"><?= e($p['excerpt']) ?></p>
      <dl class="case-meta reveal">
        <?php if ($p['client']): ?><div><dt>Klient</dt><dd><?= e($p['client']) ?></dd></div><?php endif; ?>
        <?php if ($p['year']): ?><div><dt>Rok</dt><dd><?= e($p['year']) ?></dd></div><?php endif; ?>
        <div><dt>Stav</dt><dd><?= e(STATUSES[$p['status']] ?? '') ?></dd></div>
        <?php if ($p['url']): ?><div><dt>Odkaz</dt><dd><a href="<?= e($p['url']) ?>" target="_blank" rel="noopener"><?= e(preg_replace('~^https?://(www\.)?~', '', rtrim($p['url'], '/'))) ?> ↗</a></dd></div><?php endif; ?>
      </dl>
    </div>
  </section>

  <section class="wrap case-cover reveal"><?= project_visual($p, 'large') ?></section>

  <section class="wrap case-body">
    <div class="case-text reveal"><?= nl2p($p['body']) ?></div>
    <aside class="reveal">
      <?php if ($tags): ?><h4>Co jsme dodali</h4><ul class="chips"><?php foreach ($tags as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php $links = project_links($p); if ($links): ?>
        <h4><?= count($links) > 1 ? 'Jazykové verze a odkazy' : 'Odkaz' ?></h4>
        <div class="case-links">
          <?php foreach ($links as [$label, $url]): ?><a class="btn btn-ghost wide magnetic" href="<?= e($url) ?>" target="_blank" rel="noopener"><span><?= e($label) ?> ↗</span></a><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <a class="btn btn-primary wide magnetic" href="./#kontakt"><span>Chci něco podobného</span></a>
    </aside>
  </section>

  <?php if ($images): ?>
  <section class="wrap gallery">
    <?php foreach ($images as $i => $img): ?>
      <figure class="reveal">
        <a href="<?= e(upload_url($img['file'])) ?>" data-lightbox="<?= $i ?>" data-cursor="Zvětšit">
          <img src="<?= e(upload_url($img['file'])) ?>" alt="<?= e($img['caption'] ?: $p['title']) ?>" loading="lazy">
        </a>
        <?php if ($img['caption']): ?><figcaption><?= e($img['caption']) ?></figcaption><?php endif; ?>
      </figure>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <?php foreach ($reviews as $r): ?>
  <section class="wrap case-quote reveal">
    <?= stars((float) $r['rating']) ?>
    <blockquote>„<?= e($r['text']) ?>“</blockquote>
    <p><b><?= e($r['author']) ?></b> · <?= e(trim($r['role'] . ', ' . $r['company'], ', ')) ?><?= $r['is_demo'] ? ' <em class="demo-tag">ukázka</em>' : '' ?></p>
  </section>
  <?php endforeach; ?>

  <?php if ($next['id'] !== $p['id']): ?>
  <a class="next-case" href="reference.php?slug=<?= e($next['slug']) ?>" data-cursor="Další" style="--accent:<?= e($next['accent']) ?>">
    <div class="wrap"><small>Další reference</small><span><?= e($next['title']) ?> →</span></div>
  </a>
  <?php endif; ?>
</main>

<div class="lightbox" hidden>
  <button class="lb-close" aria-label="Zavřít">×</button>
  <button class="lb-prev" aria-label="Předchozí">‹</button>
  <img alt="">
  <button class="lb-next" aria-label="Další">›</button>
</div>
<?php site_footer();
