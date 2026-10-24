<?php
declare(strict_types=1);

$CONFIG = require __DIR__ . '/config.php';

if ($CONFIG['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}
date_default_timezone_set('Europe/Prague');
mb_internal_encoding('UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_name('c4y');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
