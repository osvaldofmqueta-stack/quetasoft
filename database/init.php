<?php
function getDb(): PDO {
    $dbPath = __DIR__ . '/superescola.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec("PRAGMA journal_mode=WAL;");

    $db->exec("CREATE TABLE IF NOT EXISTS leads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT,
        telefone TEXT NOT NULL,
        escola TEXT NOT NULL,
        mensagem TEXT,
        estado TEXT DEFAULT 'novo',
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    return $db;
}
