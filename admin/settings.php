<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';
require_admin();

$fields = [
    'Kontakt' => [
        'contact_name' => 'Jméno',
        'contact_email' => 'E-mail',
        'contact_phone' => 'Telefon',
        'contact_city' => 'Město / působnost',
    ],
    'Fakturační údaje (patička)' => [
        'company_name' => 'Obchodní jméno',
        'company_ico' => 'IČO',
        'company_dic' => 'DIČ (nechte prázdné, pokud nejste plátce DPH)',
        'company_addr' => 'Sídlo',
        'company_note' => 'Poznámka k zápisu',
    ],
    'Čísla v úvodu webu' => [
        'stat_projects' => 'Počet projektů',
        'stat_years' => 'Let zkušeností (prázdné = od roku 2019)',
        'stat_response' => 'Odpověď do (hodin)',
    ],
    'Ceník' => [
        'price_template' => 'Šablonový web – Kč / měsíc',
        'price_template_note' => 'Šablonový web – doplňující text',
        'price_custom' => 'Web na míru – cena',
        'price_app' => 'Aplikace & CRM – cena',
        'price_eshop' => 'E-shop – cena',
    ],
    'Sociální sítě' => [
        'social_facebook' => 'Facebook URL',
        'social_instagram' => 'Instagram URL',
        'social_linkedin' => 'LinkedIn URL',
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'password') {
        $u = db()->prepare('SELECT * FROM users WHERE id = ?');
        $u->execute([$_SESSION['admin_id']]);
        $user = $u->fetch();
        $new = (string) ($_POST['new_password'] ?? '');
        if (!$user || !password_verify((string) ($_POST['old_password'] ?? ''), $user['password_hash'])) {
            flash('Současné heslo nesouhlasí.', 'err');
        } elseif (mb_strlen($new) < 10) {
            flash('Nové heslo musí mít alespoň 10 znaků.', 'err');
        } else {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            flash('Heslo bylo změněno.');
        }
    } else {
        foreach ($fields as $group) {
            foreach ($group as $key => $_) {
                set_setting($key, trim((string) ($_POST[$key] ?? '')));
            }
        }
        flash('Nastavení bylo uloženo.');
    }
    redirect('settings.php');
}

admin_header('Nastavení', 'settings.php');
?>
<form method="post" class="settings">
  <?= csrf_field() ?>
  <?php foreach ($fields as $group => $items): ?>
    <div class="card">
      <h2><?= e($group) ?></h2>
      <div class="row2">
      <?php foreach ($items as $key => $label): ?>
        <label><?= e($label) ?><input name="<?= $key ?>" value="<?= e(setting($key, '')) ?>" placeholder="<?= e(SETTING_DEFAULTS[$key] ?? '') ?>"></label>
      <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <p class="muted">Prázdná pole používají výchozí hodnotu (zobrazenou šedě).</p>
  <button class="btn primary">Uložit nastavení</button>
</form>

<form method="post" class="card" style="margin-top:24px">
  <?= csrf_field() ?><input type="hidden" name="action" value="password">
  <h2>Změna hesla</h2>
  <div class="row2">
    <label>Současné heslo<input type="password" name="old_password" required autocomplete="current-password"></label>
    <label>Nové heslo (min. 10 znaků)<input type="password" name="new_password" required minlength="10" autocomplete="new-password"></label>
  </div>
  <button class="btn">Změnit heslo</button>
</form>
<?php admin_footer();
