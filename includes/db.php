<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    global $CONFIG;
    if ($pdo) {
        return $pdo;
    }
    $path = $CONFIG['db_path'];
    $isNew = !is_file($path);
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    migrate($pdo);
    if ($isNew) {
        require_once __DIR__ . '/seed.php';
        seed($pdo);
    }
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec(<<<SQL
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL DEFAULT '',
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        category TEXT NOT NULL DEFAULT 'web',
        client TEXT NOT NULL DEFAULT '',
        url TEXT NOT NULL DEFAULT '',
        year TEXT NOT NULL DEFAULT '',
        status TEXT NOT NULL DEFAULT 'done',
        excerpt TEXT NOT NULL DEFAULT '',
        body TEXT NOT NULL DEFAULT '',
        tags TEXT NOT NULL DEFAULT '',
        accent TEXT NOT NULL DEFAULT '#7c5cff',
        cover TEXT NOT NULL DEFAULT '',
        featured INTEGER NOT NULL DEFAULT 0,
        visible INTEGER NOT NULL DEFAULT 1,
        sort INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS project_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
        file TEXT NOT NULL,
        caption TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0
    );
    CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        author TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT '',
        company TEXT NOT NULL DEFAULT '',
        text TEXT NOT NULL,
        rating INTEGER NOT NULL DEFAULT 5,
        photo TEXT NOT NULL DEFAULT '',
        project_id INTEGER REFERENCES projects(id) ON DELETE SET NULL,
        is_demo INTEGER NOT NULL DEFAULT 0,
        visible INTEGER NOT NULL DEFAULT 1,
        sort INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS inquiries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL DEFAULT '',
        company TEXT NOT NULL DEFAULT '',
        services TEXT NOT NULL DEFAULT '',
        budget TEXT NOT NULL DEFAULT '',
        message TEXT NOT NULL DEFAULT '',
        status TEXT NOT NULL DEFAULT 'new',
        note TEXT NOT NULL DEFAULT '',
        ip TEXT NOT NULL DEFAULT '',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL DEFAULT ''
    );
    CREATE TABLE IF NOT EXISTS login_attempts (
        ip TEXT NOT NULL,
        at INTEGER NOT NULL
    );
    SQL);

    // Dodatečné sloupce pro starší databáze
    $cols = array_column($pdo->query('PRAGMA table_info(projects)')->fetchAll(), 'name');
    if (!in_array('cover_mobile', $cols, true)) {
        $pdo->exec("ALTER TABLE projects ADD COLUMN cover_mobile TEXT NOT NULL DEFAULT ''");
    }
    if (!in_array('links', $cols, true)) {
        // Další odkazy, jeden na řádek ve tvaru „Popisek | https://…“ (např. jazykové verze)
        $pdo->exec("ALTER TABLE projects ADD COLUMN links TEXT NOT NULL DEFAULT ''");
    }
}
