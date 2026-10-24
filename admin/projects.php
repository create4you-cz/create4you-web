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
        $p = $pdo->prepare('SELECT cover FROM projects WHERE id = ?');
        $p->execute([$id]);
        delete_upload((string) $p->fetchColumn());
        $imgs = $pdo->prepare('SELECT file FROM project_images WHERE project_id = ?');
        $imgs->execute([$id]);
        foreach ($imgs->fetchAll(PDO::FETCH_COLUMN) as $f) {
            delete_upload($f);
        }
        $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
        flash('Reference byla smazána.');
    } elseif ($action === 'toggle' && $id) {
        $pdo->prepare('UPDATE projects SET visible = 1 - visible WHERE id = ?')->execute([$id]);
    } elseif ($action === 'order' && !empty($_POST['ids'])) {
        $st = $pdo->prepare('UPDATE projects SET sort = ? WHERE id = ?');
        foreach (explode(',', (string) $_POST['ids']) as $i => $pid) {
            $st->execute([$i, (int) $pid]);
        }
        exit('ok');
    }
    redirect('projects.php');
}

$rows = $pdo->query('SELECT p.*, (SELECT COUNT(*) FROM project_images i WHERE i.project_id = p.id) AS img_count
                     FROM projects p ORDER BY sort, id DESC')->fetchAll();

admin_header('Reference', 'projects.php');
?>
<div class="card">
  <div class="card-head">
    <p class="muted">Přetažením řádků změníte pořadí na webu. ★ = zvýrazněná reference (větší dlaždice).</p>
    <a class="btn primary" href="project-edit.php">＋ Nová reference</a>
  </div>
  <table class="table sortable" data-endpoint="projects.php" data-csrf="<?= e(csrf_token()) ?>">
    <thead><tr><th></th><th>Náhled</th><th>Název</th><th>Kategorie</th><th>Stav</th><th>Fotky</th><th>Na webu</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $p): ?>
      <tr data-id="<?= (int) $p['id'] ?>" draggable="true">
        <td class="grip" title="Přetáhnout">⋮⋮</td>
        <td><?php if ($p['cover']): ?><img class="thumb" src="../<?= e(upload_url($p['cover'])) ?>" alt=""><?php else: ?><span class="thumb ph" style="--c:<?= e($p['accent']) ?>"><?= e(mb_substr($p['title'], 0, 1)) ?></span><?php endif; ?></td>
        <td><a href="project-edit.php?id=<?= (int) $p['id'] ?>"><b><?= e($p['title']) ?></b></a><?= $p['featured'] ? ' <span title="Zvýrazněná">★</span>' : '' ?><br><small class="muted"><?= e($p['url'] ?: $p['client']) ?></small></td>
        <td><?= e(CATEGORIES[$p['category']] ?? $p['category']) ?></td>
        <td><span class="badge <?= e($p['status']) ?>"><?= e(STATUSES[$p['status']] ?? '') ?></span></td>
        <td><?= (int) $p['img_count'] + ($p['cover'] ? 1 : 0) ?></td>
        <td>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><input type="hidden" name="action" value="toggle">
            <button class="switch <?= $p['visible'] ? 'on' : '' ?>" title="Zobrazit / skrýt"><span></span></button></form>
        </td>
        <td class="actions">
          <a class="btn sm" href="project-edit.php?id=<?= (int) $p['id'] ?>">Upravit</a>
          <form method="post" onsubmit="return confirm('Opravdu smazat referenci včetně fotek?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn sm danger">Smazat</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php admin_footer();
