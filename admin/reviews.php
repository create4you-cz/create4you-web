<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';
require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'delete' && $id) {
        $st = $pdo->prepare('SELECT photo FROM reviews WHERE id = ?');
        $st->execute([$id]);
        delete_upload((string) $st->fetchColumn());
        $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
        flash('Hodnocení bylo smazáno.');
    } elseif ($action === 'toggle' && $id) {
        $pdo->prepare('UPDATE reviews SET visible = 1 - visible WHERE id = ?')->execute([$id]);
    } elseif ($action === 'order' && !empty($_POST['ids'])) {
        $st = $pdo->prepare('UPDATE reviews SET sort = ? WHERE id = ?');
        foreach (explode(',', (string) $_POST['ids']) as $i => $rid) {
            $st->execute([$i, (int) $rid]);
        }
        exit('ok');
    }
    redirect('reviews.php');
}

$rows = $pdo->query('SELECT r.*, p.title AS project_title FROM reviews r LEFT JOIN projects p ON p.id = r.project_id ORDER BY r.sort, r.id DESC')->fetchAll();
admin_header('Hodnocení klientů', 'reviews.php');
?>
<div class="card">
  <div class="card-head">
    <p class="muted">Hodnocení se na webu zobrazují v pohyblivém pásu a průměr hvězdiček v úvodu stránky.</p>
    <a class="btn primary" href="review-edit.php">＋ Nové hodnocení</a>
  </div>
  <table class="table sortable" data-endpoint="reviews.php" data-csrf="<?= e(csrf_token()) ?>">
    <thead><tr><th></th><th>Autor</th><th>Text</th><th>Hvězdy</th><th>Na webu</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr data-id="<?= (int) $r['id'] ?>" draggable="true">
        <td class="grip">⋮⋮</td>
        <td><b><?= e($r['author']) ?></b><?= $r['is_demo'] ? ' <span class="badge demo">ukázka</span>' : '' ?><br><small class="muted"><?= e(trim($r['role'] . ', ' . $r['company'], ', ')) ?></small></td>
        <td class="clip"><?= e(mb_strimwidth($r['text'], 0, 120, '…')) ?><?= $r['project_title'] ? '<br><small class="muted">↳ ' . e($r['project_title']) . '</small>' : '' ?></td>
        <td class="stars"><?= str_repeat('★', (int) $r['rating']) ?></td>
        <td><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="switch <?= $r['visible'] ? 'on' : '' ?>"><span></span></button></form></td>
        <td class="actions">
          <a class="btn sm" href="review-edit.php?id=<?= (int) $r['id'] ?>">Upravit</a>
          <form method="post" onsubmit="return confirm('Smazat hodnocení?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn sm danger">Smazat</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php admin_footer();
