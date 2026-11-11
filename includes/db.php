<?php
require_once __DIR__ . '/../config.php';

/**
 * Returnează conexiunea PDO (singleton). Toate interogările din aplicație
 * folosesc prepared statements, deci datele introduse nu ajung niciodată
 * concatenate în SQL (NFR-03).
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    if (DB_DRIVER === 'sqlite') {
        $pdo = new PDO('sqlite:' . SQLITE_PATH, null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

/** Execută o interogare parametrizată și întoarce statement-ul. */
function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
