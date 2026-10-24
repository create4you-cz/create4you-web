<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';
require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
        flash('Poptávka byla smazána.');
        redirect('inquiries.php');
    }
    $status = isset(INQ_STATUSES[$_POST['status'] ?? '']) ? $_POST['status'] : 'new';
    $pdo->prepare('UPDATE inquiries SET status = ?, note = ? WHERE id = ?')
        ->execute([$status, trim((string) ($_POST['note'] ?? '')), $id]);
    flash('Poptávka byla aktualizována.');
    redirect('inquiries.php?id=' . $id);
}

$detail = null;
if (!empty($_GET['id'])) {
    $st = $pdo->prepare('SELECT * FROM inquiries WHERE id = ?');
    $st->execute([(int) $_GET['id']]);
    $detail = $st->fetch() ?: null;
}

$filter = $_GET['status'] ?? '';
if (isset(INQ_STATUSES[$filter])) {
    $st = $pdo->prepare('SELECT * FROM inquiries WHERE status = ? ORDER BY id DESC');
    $st->execute([$filter]);
    $rows = $st->fetchAll();
} else {
    $rows = $pdo->query('SELECT * FROM inquiries ORDER BY id DESC')->fetchAll();
}

admin_header('Poptávky', 'inquiries.php');
?>
<?php if ($detail): ?>
<div class="card inquiry">
  <div class="card-head">
    <h2><?= e($detail['name']) ?> <?= $detail['company'] ? '· ' . e($detail['company']) : '' ?></h2>
    <a class="btn sm" href="inquiries.php">← Zpět na seznam</a>
  </div>
  <dl>
    <dt>E-mail</dt><dd><a href="mailto:<?= e($detail['email']) ?>"><?= e($detail['email']) ?></a></dd>
    <?php if ($detail['phone']): ?><dt>Telefon</dt><dd><a href="tel:<?= e(preg_replace('~\s+~', '', $detail['phone'])) ?>"><?= e($detail['phone']) ?></a></dd><?php endif; ?>
    <dt>Zájem o</dt><dd><?= e($detail['services'] ?: '—') ?></dd>
    <dt>Rozpočet</dt><dd><?= e($detail['budget'] ?: '—') ?></dd>
    <dt>Přijato</dt><dd><?= e(date('j. n. Y H:i', strtotime($detail['created_at'] . ' UTC'))) ?></dd>
  </dl>
  <div class="msg"><?= nl2p($detail['message']) ?: '<p class="muted">Bez zprávy.</p>' ?></div>
  <form method="post" class="inq-form">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $detail['id'] ?>">
    <label>Stav<select name="status"><?php foreach (INQ_STATUSES as $k => $v): ?><option value="<?= $k ?>" <?= $detail['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></label>
    <label>Interní poznámka<textarea name="note" rows="3"><?= e($detail['note']) ?></textarea></label>
    <div class="btns">
      <button class="btn primary">Uložit</button>
      <a class="btn" href="mailto:<?= e($detail['email']) ?>?subject=<?= rawurlencode('Re: Vaše poptávka – create4you') ?>">Odpovědět e-mailem</a>
      <button class="btn danger" name="action" value="delete" onclick="return confirm('Smazat poptávku?')">Smazat</button>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="tabs">
    <a href="inquiries.php" class="<?= $filter === '' ? 'on' : '' ?>">Vše</a>
    <?php foreach (INQ_STATUSES as $k => $v): ?><a href="?status=<?= $k ?>" class="<?= $filter === $k ? 'on' : '' ?>"><?= $v ?></a><?php endforeach; ?>
  </div>
  <?php if (!$rows): ?><p class="muted">Žádné poptávky.</p><?php else: ?>
  <table class="table">
    <thead><tr><th>Datum</th><th>Kontakt</th><th>Zájem o</th><th>Rozpočet</th><th>Stav</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $q): ?>
      <tr class="<?= $q['status'] === 'new' ? 'unread' : '' ?>">
        <td><?= e(date('j. n. Y H:i', strtotime($q['created_at'] . ' UTC'))) ?></td>
        <td><a href="?id=<?= (int) $q['id'] ?>"><b><?= e($q['name']) ?></b></a><br><small class="muted"><?= e($q['email']) ?></small></td>
        <td><?= e($q['services']) ?></td>
        <td><?= e($q['budget']) ?></td>
        <td><span class="badge <?= e($q['status']) ?>"><?= e(INQ_STATUSES[$q['status']] ?? '') ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php admin_footer();
