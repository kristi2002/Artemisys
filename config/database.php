<?php

class Database {
    private static $instance = null;
    private $pdo;

    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $charset = 'utf8mb4';

    private function __construct() {
        // Config via variabili d'ambiente (Coolify / Docker).
        // Fallback ai valori XAMPP locali per lo sviluppo.
        $this->host     = getenv('DB_HOST')     ?: '127.0.0.1';
        $this->port     = getenv('DB_PORT')     ?: '3306';
        $this->dbname   = getenv('DB_NAME')     ?: 'artemisys';
        $this->username = getenv('DB_USER')     ?: 'root';
        $this->password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->host,
            $this->port,
            $this->dbname,
            $this->charset
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            // Non esporre il messaggio in produzione
            if (getenv('APP_DEBUG') === '1') {
                die('Errore connessione database: ' . $e->getMessage());
            }
            die('Errore connessione database.');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
