<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';
require_admin();

$pdo = db();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$r = ['author' => '', 'role' => '', 'company' => '', 'text' => '', 'rating' => 5, 'photo' => '', 'project_id' => null, 'visible' => 1, 'is_demo' => 0];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM reviews WHERE id = ?');
    $st->execute([$id]);
    $r = $st->fetch() ?: redirect('reviews.php');
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['author', 'role', 'company', 'text'] as $k) {
        $r[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $r['rating'] = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
    $r['project_id'] = ((int) ($_POST['project_id'] ?? 0)) ?: null;
    $r['visible'] = isset($_POST['visible']) ? 1 : 0;

    if ($r['author'] === '' || $r['text'] === '') {
        $error = 'Vyplňte autora a text hodnocení.';
    } else {
        try {
            $photo = isset($_FILES['photo']) ? save_uploaded_image($_FILES['photo']) : null;
            if ($photo || isset($_POST['remove_photo'])) {
                delete_upload($r['photo']);
                $r['photo'] = $photo ?? '';
            }
            // Jakmile hodnocení upravíte, už není „ukázkové“
            $vals = [$r['author'], $r['role'], $r['company'], $r['text'], $r['rating'], $r['photo'], $r['project_id'], $r['visible']];
            if ($id) {
                $pdo->prepare('UPDATE reviews SET author=?, role=?, company=?, text=?, rating=?, photo=?, project_id=?, visible=?, is_demo=0 WHERE id=?')
                    ->execute([...$vals, $id]);
            } else {
                $pdo->prepare('INSERT INTO reviews(author, role, company, text, rating, photo, project_id, visible, sort)
                               VALUES(?,?,?,?,?,?,?,?, (SELECT COALESCE(MIN(sort), 0) - 1 FROM reviews))')->execute($vals);
            }
            flash('Hodnocení bylo uloženo.');
            redirect('reviews.php');
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }
}

$projects = $pdo->query('SELECT id, title FROM projects ORDER BY title')->fetchAll();
admin_header($id ? 'Upravit hodnocení' : 'Nové hodnocení', 'reviews.php');
?>
<?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
<?php if (!empty($r['is_demo'])): ?><div class="flash warn">Toto je ukázkové hodnocení. Přepište ho skutečnou zpětnou vazbou od klienta – po uložení zmizí označení „ukázka“.</div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="grid-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="col-main">
    <div class="card">
      <div class="row2">
        <label>Jméno autora *<input name="author" required value="<?= e($r['author']) ?>"></label>
        <label>Pozice<input name="role" value="<?= e($r['role']) ?>" placeholder="jednatel, majitelka…"></label>
      </div>
      <label>Firma<input name="company" value="<?= e($r['company']) ?>"></label>
      <label>Text hodnocení *<textarea name="text" rows="6" required><?= e($r['text']) ?></textarea></label>
      <div class="rating-input">
        <span>Hodnocení</span>
        <?php for ($i = 5; $i >= 1; $i--): ?>
          <input type="radio" id="st<?= $i ?>" name="rating" value="<?= $i ?>" <?= (int) $r['rating'] === $i ? 'checked' : '' ?>><label for="st<?= $i ?>">★</label>
        <?php endfor; ?>
      </div>
      <label>Související reference<select name="project_id"><option value="">— žádná —</option>
        <?php foreach ($projects as $pr): ?><option value="<?= (int) $pr['id'] ?>" <?= (int) $r['project_id'] === (int) $pr['id'] ? 'selected' : '' ?>><?= e($pr['title']) ?></option><?php endforeach; ?>
      </select></label>
    </div>
  </div>
  <div class="col-side">
    <div class="card">
      <label class="check"><input type="checkbox" name="visible" <?= $r['visible'] ? 'checked' : '' ?>> Zobrazit na webu</label>
      <h2>Fotka / logo</h2>
      <?php if ($r['photo']): ?><img class="avatar-prev" src="../<?= e(upload_url($r['photo'])) ?>" alt=""><label class="check"><input type="checkbox" name="remove_photo"> Odstranit</label><?php endif; ?>
      <label class="drop small"><input type="file" name="photo" accept="image/*" data-preview="ph-prev"><span>Nahrát fotku<br><small>čtverec, min. 200×200</small></span></label>
      <div class="previews" id="ph-prev"></div>
      <button class="btn primary wide">Uložit hodnocení</button>
    </div>
  </div>
</form>
<?php admin_footer();
