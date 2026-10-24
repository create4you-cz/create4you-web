<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$ajax = ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';

function respond(bool $ok, string $msg, bool $ajax): never
{
    if ($ajax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($ok ? 200 : 422);
        echo json_encode(['ok' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    } else {
        flash($msg, $ok ? 'ok' : 'err');
        header('Location: ../#kontakt');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
csrf_check();

// Ochrana proti spamu: skryté pole + minimální doba vyplnění
if (!empty($_POST['website']) || (time() - (int) ($_POST['ts'] ?? 0)) < 3) {
    respond(true, 'Děkujeme, poptávka byla odeslána.', $ajax);
}

$clean = fn (string $k, int $max) => mb_substr(trim(strip_tags((string) ($_POST[$k] ?? ''))), 0, $max);
$name    = $clean('name', 120);
$email   = $clean('email', 160);
$phone   = $clean('phone', 40);
$company = $clean('company', 160);
$message = $clean('message', 5000);
$budget  = $clean('budget', 40);
$services = implode(', ', array_map(fn ($s) => mb_substr(strip_tags((string) $s), 0, 40), array_slice((array) ($_POST['services'] ?? []), 0, 8)));

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Vyplňte prosím jméno a platný e-mail.', $ajax);
}

$pdo = db();
// Jednoduchý limit: max. 5 poptávek z jedné IP za hodinu
$st = $pdo->prepare("SELECT COUNT(*) FROM inquiries WHERE ip = ? AND created_at > datetime('now', '-1 hour')");
$st->execute([client_ip()]);
if ((int) $st->fetchColumn() >= 5) {
    respond(false, 'Odeslali jste už několik poptávek. Zkuste to prosím později, nebo nám napište e-mail.', $ajax);
}

$pdo->prepare('INSERT INTO inquiries(name, email, phone, company, services, budget, message, ip) VALUES(?,?,?,?,?,?,?,?)')
    ->execute([$name, $email, $phone, $company, $services, $budget, $message, client_ip()]);

if ($CONFIG['notify_email']) {
    $body = "Nová poptávka z webu create4you.cz\n\n"
          . "Jméno: $name\nFirma: $company\nE-mail: $email\nTelefon: $phone\n"
          . "Zájem o: $services\nRozpočet: $budget\n\nZpráva:\n$message\n\n"
          . 'Administrace: ' . $CONFIG['site_url'] . '/admin/inquiries.php';
    $headers = [
        'From' => 'create4you <' . $CONFIG['mail_from'] . '>',
        'Reply-To' => $email,
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];
    @mail($CONFIG['notify_email'], '=?UTF-8?B?' . base64_encode('Nová poptávka: ' . $name) . '?=', $body, $headers);
}

respond(true, 'Děkujeme! Poptávka dorazila, ozveme se do ' . setting('stat_response') . ' hodin.', $ajax);
