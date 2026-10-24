<?php
declare(strict_types=1);

const CATEGORIES = [
    'web'     => 'Weby na míru',
    'app'     => 'Aplikace & CRM',
    'eshop'   => 'E-shopy',
    'sablona' => 'Šablonové weby',
];

const STATUSES = [
    'done'     => 'Dokončeno',
    'progress' => 'Právě děláme',
];

const INQ_STATUSES = [
    'new'      => 'Nová',
    'progress' => 'Řeší se',
    'won'      => 'Zakázka',
    'closed'   => 'Uzavřeno',
];

const SETTING_DEFAULTS = [
    'contact_name' => 'Daniel Peterek',
    'contact_email' => 'info@create4you.cz',
    'contact_phone' => '',
    'contact_city' => 'Ostrava · celá ČR a SK',
    'company_name' => 'Daniel Peterek',
    'company_ico' => '08096589',
    'company_dic' => '',
    'company_addr' => 'Ostrava',
    'company_note' => 'Fyzická osoba podnikající dle živnostenského zákona.',
    'stat_projects' => '40',
    'stat_years' => '',
    'stat_response' => '24',
    'price_template' => '700',
    'price_template_note' => 'bez vstupní investice · min. 12 měsíců',
    'price_custom' => 'individuálně',
    'price_app' => 'individuálně',
    'price_eshop' => 'individuálně',
    'social_facebook' => '',
    'social_instagram' => '',
    'social_linkedin' => '',
];

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function setting(string $key, ?string $default = null): string
{
    $default ??= SETTING_DEFAULTS[$key] ?? '';
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query('SELECT key, value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return array_key_exists($key, $cache) && $cache[$key] !== '' ? $cache[$key] : $default;
}

function set_setting(string $key, string $value): void
{
    db()->prepare('INSERT INTO settings(key, value) VALUES(?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value')
        ->execute([$key, $value]);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Platnost formuláře vypršela. Obnovte stránku a zkuste to znovu.');
    }
}

function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function slugify(string $s): string
{
    $s = trim($s);
    $t = function_exists('transliterator_transliterate')
        ? transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s)
        : strtolower(strtr($s, [
            'á'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ň'=>'n','ó'=>'o','ř'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ů'=>'u','ý'=>'y','ž'=>'z',
            'Á'=>'a','Č'=>'c','Ď'=>'d','É'=>'e','Ě'=>'e','Í'=>'i','Ň'=>'n','Ó'=>'o','Ř'=>'r','Š'=>'s','Ť'=>'t','Ú'=>'u','Ů'=>'u','Ý'=>'y','Ž'=>'z',
        ]));
    $t = preg_replace('~[^a-z0-9]+~', '-', (string) $t);
    return trim((string) $t, '-') ?: 'projekt';
}

function unique_slug(string $base, int $ignoreId = 0): string
{
    $slug = $base;
    $i = 2;
    $st = db()->prepare('SELECT COUNT(*) FROM projects WHERE slug = ? AND id != ?');
    while (true) {
        $st->execute([$slug, $ignoreId]);
        if (!$st->fetchColumn()) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

function upload_url(string $file): string
{
    global $CONFIG;
    if ($file === '') {
        return '';
    }
    if (preg_match('~^(https?:)?//|^assets/~', $file)) {
        return $file;
    }
    return $CONFIG['upload_url'] . '/' . rawurlencode($file);
}

/**
 * Uloží nahraný obrázek: ověří, že jde o skutečný obrázek, zmenší ho a převede do WebP (pokud to GD umí).
 * Vrací název uloženého souboru, nebo null.
 */
function save_uploaded_image(array $f): ?string
{
    global $CONFIG;
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        return null;
    }
    if ($f['size'] > $CONFIG['max_upload_mb'] * 1024 * 1024) {
        throw new RuntimeException('Soubor je příliš velký (max ' . $CONFIG['max_upload_mb'] . ' MB).');
    }
    $info = @getimagesize($f['tmp_name']);
    $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($allowed[$info[2]])) {
        throw new RuntimeException('Nahrávejte prosím obrázky JPG, PNG, WebP nebo GIF.');
    }
    $dir = $CONFIG['upload_dir'];
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6));

    $src = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($f['tmp_name']),
        IMAGETYPE_PNG  => @imagecreatefrompng($f['tmp_name']),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($f['tmp_name']) : false,
        IMAGETYPE_GIF  => false, // GIF necháme beze změny (animace)
    };
    if (!$src) {
        $file = $name . '.' . $allowed[$info[2]];
        move_uploaded_file($f['tmp_name'], $dir . '/' . $file);
        return $file;
    }
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($f['tmp_name']);
        $rot = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 0] ?? 0;
        if ($rot) {
            $src = imagerotate($src, $rot, 0);
        }
    }
    $w = imagesx($src);
    $h = imagesy($src);
    $max = $CONFIG['image_max_px'];
    if ($w > $max || $h > $max) {
        $ratio = min($max / $w, $max / $h);
        $nw = (int) round($w * $ratio);
        $nh = (int) round($h * $ratio);
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($src);
        $src = $dst;
    } else {
        imagealphablending($src, false);
        imagesavealpha($src, true);
    }
    if (function_exists('imagewebp')) {
        $file = $name . '.webp';
        imagewebp($src, $dir . '/' . $file, 82);
    } else {
        $file = $name . '.jpg';
        imagejpeg($src, $dir . '/' . $file, 85);
    }
    imagedestroy($src);
    return $file;
}

/** Normalizuje pole souborů z <input type="file" multiple>. */
function uploaded_files(string $field): array
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
        return empty($_FILES[$field]) ? [] : [$_FILES[$field]];
    }
    $out = [];
    foreach ($_FILES[$field]['name'] as $i => $n) {
        $out[] = [
            'name'     => $n,
            'type'     => $_FILES[$field]['type'][$i],
            'tmp_name' => $_FILES[$field]['tmp_name'][$i],
            'error'    => $_FILES[$field]['error'][$i],
            'size'     => $_FILES[$field]['size'][$i],
        ];
    }
    return $out;
}

function delete_upload(string $file): void
{
    global $CONFIG;
    if ($file !== '' && !str_contains($file, '/') && !str_contains($file, '..')) {
        @unlink($CONFIG['upload_dir'] . '/' . $file);
    }
}

function nl2p(string $text): string
{
    $parts = preg_split("~\R{2,}~", trim($text)) ?: [];
    return implode('', array_map(fn ($p) => '<p>' . nl2br(e($p)) . '</p>', $parts));
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
