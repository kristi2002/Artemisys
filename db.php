<?php
// ── Configurazione Database ────────────────────────────────────────────────
define('DB_HOST', '31.11.39.150');
define('DB_NAME', 'Sql1920192_2');
define('DB_USER', 'Sql1920192');
define('DB_PASS', 'Walixcamel12@!');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4;port=3306",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die('<div style="font-family:monospace;padding:2rem;color:red;">
                 <strong>Errore DB:</strong> ' . htmlspecialchars($e->getMessage()) . '<br><br>
                 Controlla le credenziali in <code>includes/db.php</code> e assicurati che il database esista.<br>
                 Esegui prima <code>db/schema.sql</code> in phpMyAdmin.
                 </div>');
        }
    }
    return $pdo;
}
