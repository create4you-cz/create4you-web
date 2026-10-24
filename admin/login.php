<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = db();
$hasUser = (bool) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$error = '';

if (is_admin()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim((string) ($_POST['email'] ?? ''));
    $pass  = (string) ($_POST['password'] ?? '');

    if (!$hasUser) {
        // První spuštění: založení administrátorského účtu
        $name = trim((string) ($_POST['name'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Zadejte platný e-mail.';
        } elseif (mb_strlen($pass) < 10) {
            $error = 'Heslo musí mít alespoň 10 znaků.';
        } elseif ($pass !== ($_POST['password2'] ?? '')) {
            $error = 'Hesla se neshodují.';
        } else {
            $pdo->prepare('INSERT INTO users(email, name, password_hash) VALUES(?, ?, ?)')
                ->execute([mb_strtolower($email), $name, password_hash($pass, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $pdo->lastInsertId();
            flash('Účet byl vytvořen. Vítejte v administraci!');
            redirect('index.php');
        }
    } else {
        $ip = client_ip();
        $pdo->prepare('DELETE FROM login_attempts WHERE at < ?')->execute([time() - 900]);
        $st = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ?');
        $st->execute([$ip]);
        if ((int) $st->fetchColumn() >= 8) {
            $error = 'Příliš mnoho pokusů. Zkuste to znovu za 15 minut.';
        } else {
            $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $st->execute([mb_strtolower($email)]);
            $u = $st->fetch();
            if ($u && password_verify($pass, $u['password_hash'])) {
                if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                    $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                        ->execute([password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
                }
                $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int) $u['id'];
                redirect('index.php');
            }
            $pdo->prepare('INSERT INTO login_attempts(ip, at) VALUES(?, ?)')->execute([$ip, time()]);
            $error = 'Nesprávný e-mail nebo heslo.';
        }
    }
}
?><!doctype html>
<html lang="cs">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $hasUser ? 'Přihlášení' : 'Založení účtu' ?> · create4you</title>
<link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body class="auth">
  <form class="auth-card" method="post" autocomplete="on">
    <a class="brand" href="../"><span class="logo-dot"></span><span>create<b>4</b>you</span></a>
    <h1><?= $hasUser ? 'Přihlášení do administrace' : 'Vítejte! Založte si admin účet' ?></h1>
    <?php if (!$hasUser): ?><p class="muted">Tento krok proběhne jen jednou. Účet slouží ke správě referencí, hodnocení a poptávek.</p><?php endif; ?>
    <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <?php if (!$hasUser): ?>
      <label>Jméno<input name="name" value="<?= e($_POST['name'] ?? 'Daniel Peterek') ?>"></label>
    <?php endif; ?>
    <label>E-mail<input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>"></label>
    <label>Heslo<input type="password" name="password" required <?= $hasUser ? '' : 'minlength="10"' ?> autocomplete="<?= $hasUser ? 'current-password' : 'new-password' ?>"></label>
    <?php if (!$hasUser): ?>
      <label>Heslo znovu<input type="password" name="password2" required minlength="10" autocomplete="new-password"></label>
    <?php endif; ?>
    <button class="btn primary wide"><?= $hasUser ? 'Přihlásit se' : 'Vytvořit účet' ?></button>
  </form>
</body>
</html>
