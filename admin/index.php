<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';
require_admin();

$pdo = db();
$stats = [
    'Reference'        => (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
    'Hodnocení'        => (int) $pdo->query('SELECT COUNT(*) FROM reviews WHERE visible = 1')->fetchColumn(),
    'Nové poptávky'    => (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn(),
    'Poptávek celkem'  => (int) $pdo->query('SELECT COUNT(*) FROM inquiries')->fetchColumn(),
];
$demo = (int) $pdo->query('SELECT COUNT(*) FROM reviews WHERE is_demo = 1 AND visible = 1')->fetchColumn();
$noCover = (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE cover = '' AND visible = 1")->fetchColumn();
$latest = $pdo->query('SELECT * FROM inquiries ORDER BY id DESC LIMIT 6')->fetchAll();

admin_header('Přehled', 'index.php');
?>
<div class="stats">
  <?php foreach ($stats as $k => $v): ?>
    <div class="card stat"><span><?= e($k) ?></span><b><?= $v ?></b></div>
  <?php endforeach; ?>
</div>

<?php if ($demo || $noCover): ?>
<div class="card todo">
  <h2>Před spuštěním webu</h2>
  <ul>
    <?php if ($demo): ?><li>Na webu je <b><?= $demo ?></b> ukázkových hodnocení. <a href="reviews.php">Nahraďte je skutečnými</a> (na webu jsou označené jako „ukázka“).</li><?php endif; ?>
    <?php if ($noCover): ?><li><b><?= $noCover ?></b> referencí nemá titulní fotku – zobrazuje se generovaný náhled. <a href="projects.php">Nahrát fotky</a></li><?php endif; ?>
    <li>Zkontrolujte kontaktní údaje a fakturační údaje v <a href="settings.php">Nastavení</a>.</li>
  </ul>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-head"><h2>Poslední poptávky</h2><a class="btn" href="inquiries.php">Všechny poptávky</a></div>
  <?php if (!$latest): ?>
    <p class="muted">Zatím žádné poptávky. Jakmile někdo vyplní formulář na webu, objeví se tady.</p>
  <?php else: ?>
  <table class="table">
    <thead><tr><th>Datum</th><th>Jméno</th><th>Služby</th><th>Stav</th></tr></thead>
    <tbody>
    <?php foreach ($latest as $q): ?>
      <tr>
        <td><?= e(date('j. n. Y H:i', strtotime($q['created_at'] . ' UTC'))) ?></td>
        <td><a href="inquiries.php?id=<?= (int) $q['id'] ?>"><?= e($q['name']) ?></a><br><small class="muted"><?= e($q['email']) ?></small></td>
        <td><?= e($q['services']) ?></td>
        <td><span class="badge <?= e($q['status']) ?>"><?= e(INQ_STATUSES[$q['status']] ?? $q['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<div class="quick">
  <a class="card quick-link" href="project-edit.php">＋ Přidat referenci</a>
  <a class="card quick-link" href="review-edit.php">＋ Přidat hodnocení</a>
</div>
<?php admin_footer();
