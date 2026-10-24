<?php
/**
 * Základní konfigurace webu create4you.cz
 * Upravte podle hostingu. Citlivé hodnoty lze přepsat v souboru config.local.php (není v gitu).
 */
return array_merge([
    'site_name'      => 'create4you',
    'site_url'       => 'https://create4you.cz',
    // Kam chodí poptávky z formuláře (prázdné = jen ukládat do administrace)
    'notify_email'   => 'info@create4you.cz',
    'mail_from'      => 'web@create4you.cz',
    'db_path'        => __DIR__ . '/../data/create4you.sqlite',
    'upload_dir'     => __DIR__ . '/../uploads',
    'upload_url'     => 'uploads',
    'max_upload_mb'  => 12,
    'image_max_px'   => 2000,
    'debug'          => false,
], is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : []);
