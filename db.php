<?php
$dbFile = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        year INTEGER NOT NULL,
        month INTEGER NOT NULL,
        name TEXT DEFAULT '',
        reservation_date TEXT DEFAULT '',
        is_paid INTEGER DEFAULT 0,
        note TEXT DEFAULT '',
        UNIQUE(year, month)
    )");
} catch (PDOException $e) {
    die("Greška sa bazom: " . $e->getMessage());
}
