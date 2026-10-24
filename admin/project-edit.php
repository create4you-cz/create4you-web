<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';
require_admin();

$pdo = db();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$p = [
    'id' => 0, 'title' => '', 'slug' => '', 'category' => 'web', 'client' => '', 'url' => '', 'year' => date('Y'),
    'status' => 'done', 'excerpt' => '', 'body' => '', 'tags' => '', 'accent' => '#7c5cff', 'cover' => '',
    'featured' => 0, 'visible' => 1, 'cover_mobile' => '',
];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch() ?: redirect('projects.php');
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // Smazání jednoho obrázku z galerie
    if (!empty($_POST['delete_image'])) {
        $st = $pdo->prepare('SELECT file FROM project_images WHERE id = ? AND project_id = ?');
        $st->execute([(int) $_POST['delete_image'], $id]);
        if ($f = $st->fetchColumn()) {
            delete_upload($f);
            $pdo->prepare('DELETE FROM project_images WHERE id = ?')->execute([(int) $_POST['delete_image']]);
        }
        flash('Obrázek byl odstraněn.');
        redirect('project-edit.php?id=' . $id . '#galerie');
    }

    foreach (['title', 'category', 'client', 'url', 'year', 'status', 'excerpt', 'body', 'tags', 'accent'] as $k) {
        $p[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $p['featured'] = isset($_POST['featured']) ? 1 : 0;
    $p['visible']  = isset($_POST['visible']) ? 1 : 0;
    if (!isset(CATEGORIES[$p['category']])) {
        $p['category'] = 'web';
    }
    if (!isset(STATUSES[$p['status']])) {
        $p['status'] = 'done';
    }
    if (!preg_match('~^#[0-9a-fA-F]{6}$~', $p['accent'])) {
        $p['accent'] = '#7c5cff';
    }
    if ($p['url'] !== '' && !preg_match('~^https?://~i', $p['url'])) {
        $p['url'] = 'https://' . $p['url'];
    }

    if ($p['title'] === '') {
        $error = 'Vyplňte název reference.';
    } else {
        try {
            $pdo->beginTransaction();
            $newCover = isset($_FILES['cover']) ? save_uploaded_image($_FILES['cover']) : null;
            if ($newCover || isset($_POST['remove_cover'])) {
                delete_upload($p['cover']);
                $p['cover'] = $newCover ?? '';
            }
            $newMob = isset($_FILES['cover_mobile']) ? save_uploaded_image($_FILES['cover_mobile']) : null;
            if ($newMob || isset($_POST['remove_cover_mobile'])) {
                delete_upload($p['cover_mobile']);
                $p['cover_mobile'] = $newMob ?? '';
            }
            $slug = unique_slug(slugify($_POST['slug'] ?: $p['title']), $id);
            $vals = [$slug, $p['title'], $p['category'], $p['client'], $p['url'], $p['year'], $p['status'], $p['excerpt'],
                     $p['body'], $p['tags'], $p['accent'], $p['cover'], $p['featured'], $p['visible'], $p['cover_mobile']];
            if ($id) {
                $pdo->prepare('UPDATE projects SET slug=?, title=?, category=?, client=?, url=?, year=?, status=?, excerpt=?, body=?, tags=?,
                               accent=?, cover=?, featured=?, visible=?, cover_mobile=?, updated_at=CURRENT_TIMESTAMP WHERE id=?')
                    ->execute([...$vals, $id]);
            } else {
                $pdo->prepare('INSERT INTO projects(slug, title, category, client, url, year, status, excerpt, body, tags, accent, cover, featured, visible, cover_mobile, sort)
                               VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, (SELECT COALESCE(MIN(sort), 0) - 1 FROM projects))')
                    ->execute($vals);
                $id = (int) $pdo->lastInsertId();
            }
            // Galerie
            $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort), 0) FROM project_images WHERE project_id = ' . $id)->fetchColumn();
            $ins = $pdo->prepare('INSERT INTO project_images(project_id, file, sort) VALUES(?, ?, ?)');
            foreach (uploaded_files('gallery') as $f) {
                if ($file = save_uploaded_image($f)) {
                    $ins->execute([$id, $file, ++$sort]);
                }
            }
            // Popisky obrázků
            $cap = $pdo->prepare('UPDATE project_images SET caption = ? WHERE id = ? AND project_id = ?');
            foreach ((array) ($_POST['caption'] ?? []) as $imgId => $c) {
                $cap->execute([trim((string) $c), (int) $imgId, $id]);
            }
            $pdo->commit();
            flash('Reference byla uložena.');
            redirect('project-edit.php?id=' . $id);
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            $error = $ex->getMessage();
        }
    }
}

$images = [];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM project_images WHERE project_id = ? ORDER BY sort, id');
    $st->execute([$id]);
    $images = $st->fetchAll();
}

admin_header($id ? 'Upravit referenci' : 'Nová reference', 'projects.php');
?>
<?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="grid-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="col-main">
    <div class="card">
      <label>Název *<input name="title" required value="<?= e($p['title']) ?>" placeholder="např. AccuWise – firemní web"></label>
      <div class="row2">
        <label>Klient<input name="client" value="<?= e($p['client']) ?>" placeholder="Název firmy"></label>
        <label>Odkaz na web / aplikaci<input name="url" value="<?= e($p['url']) ?>" placeholder="https://"></label>
      </div>
      <label>Krátký popis (karta v portfoliu)<textarea name="excerpt" rows="2" maxlength="220"><?= e($p['excerpt']) ?></textarea></label>
      <label>Detailní popis (případová studie)<textarea name="body" rows="9" placeholder="Co klient potřeboval, co jsme udělali, výsledek… Prázdný řádek = nový odstavec."><?= e($p['body']) ?></textarea></label>
      <label>Štítky (oddělené čárkou)<input name="tags" value="<?= e($p['tags']) ?>" placeholder="Web na míru, E-maily, Admin, SEO"></label>
    </div>

    <div class="card" id="galerie">
      <h2>Fotogalerie</h2>
      <?php if ($images): ?>
        <div class="gallery-admin">
          <?php foreach ($images as $img): ?>
            <figure>
              <img src="../<?= e(upload_url($img['file'])) ?>" alt="">
              <input name="caption[<?= (int) $img['id'] ?>]" value="<?= e($img['caption']) ?>" placeholder="Popisek">
              <button class="btn sm danger" name="delete_image" value="<?= (int) $img['id'] ?>" formnovalidate onclick="return confirm('Odstranit obrázek?')">Odstranit</button>
            </figure>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <label class="drop">
        <input type="file" name="gallery[]" accept="image/*" multiple data-preview="gal-prev">
        <span>Přetáhněte sem fotky nebo klikněte pro výběr<br><small>JPG, PNG, WebP · automaticky se zmenší a převedou do WebP</small></span>
      </label>
      <div class="previews" id="gal-prev"></div>
    </div>
  </div>

  <div class="col-side">
    <div class="card">
      <label class="check"><input type="checkbox" name="visible" <?= $p['visible'] ? 'checked' : '' ?>> Zobrazit na webu</label>
      <label class="check"><input type="checkbox" name="featured" <?= $p['featured'] ? 'checked' : '' ?>> ★ Zvýrazněná (velká dlaždice)</label>
      <label>Kategorie<select name="category"><?php foreach (CATEGORIES as $k => $v): ?><option value="<?= $k ?>" <?= $p['category'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></label>
      <label>Stav<select name="status"><?php foreach (STATUSES as $k => $v): ?><option value="<?= $k ?>" <?= $p['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></label>
      <div class="row2">
        <label>Rok<input name="year" value="<?= e($p['year']) ?>"></label>
        <label>Barva<input type="color" name="accent" value="<?= e($p['accent']) ?>"></label>
      </div>
      <label>URL adresa (slug)<input name="slug" value="<?= e($p['slug']) ?>" placeholder="vytvoří se z názvu"></label>
      <button class="btn primary wide">Uložit referenci</button>
      <?php if ($id && $p['visible']): ?><a class="btn wide" target="_blank" href="../reference.php?slug=<?= e($p['slug']) ?>">↗ Zobrazit na webu</a><?php endif; ?>
    </div>
    <div class="card">
      <h2>Screenshot – počítač</h2>
      <?php if ($p['cover']): ?>
        <img class="cover-prev" src="../<?= e(upload_url($p['cover'])) ?>" alt="">
        <label class="check"><input type="checkbox" name="remove_cover"> Odstranit screenshot</label>
      <?php endif; ?>
      <label class="drop small">
        <input type="file" name="cover" accept="image/*" data-preview="cover-prev">
        <span><?= $p['cover'] ? 'Nahrát jiný' : 'Nahrát screenshot' ?><br><small>ideálně celá stránka, šířka 1440 px – na webu se při najetí roluje</small></span>
      </label>
      <div class="previews" id="cover-prev"></div>
    </div>
    <div class="card">
      <h2>Screenshot – mobil</h2>
      <?php if ($p['cover_mobile']): ?>
        <img class="cover-prev mobile" src="../<?= e(upload_url($p['cover_mobile'])) ?>" alt="">
        <label class="check"><input type="checkbox" name="remove_cover_mobile"> Odstranit</label>
      <?php endif; ?>
      <label class="drop small">
        <input type="file" name="cover_mobile" accept="image/*" data-preview="mob-prev">
        <span><?= $p['cover_mobile'] ? 'Nahrát jiný' : 'Nahrát mobilní screenshot' ?><br><small>šířka 390 px, zobrazí se v telefonu</small></span>
      </label>
      <div class="previews" id="mob-prev"></div>
    </div>
  </div>
</form>
<?php admin_footer();
