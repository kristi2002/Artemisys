<?php
// File temporaneo di diagnostica — DA CANCELLARE dopo aver risolto
// Carica via FileZilla in /artemisys/debug.php e apri il browser su:
// https://www.camerinowork.com/artemisys/debug.php

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTICA ARTEMISYS ===\n\n";

echo "PHP version:        " . PHP_VERSION . "\n";
echo "Server:             " . ($_SERVER['SERVER_SOFTWARE'] ?? '?') . "\n";
echo "DOCUMENT_ROOT:      " . ($_SERVER['DOCUMENT_ROOT'] ?? '?') . "\n";
echo "SCRIPT_NAME:        " . ($_SERVER['SCRIPT_NAME'] ?? '?') . "\n";
echo "REQUEST_URI:        " . ($_SERVER['REQUEST_URI'] ?? '?') . "\n";
echo "HTTP_HOST:          " . ($_SERVER['HTTP_HOST'] ?? '?') . "\n";
echo "HTTPS:              " . ($_SERVER['HTTPS'] ?? '(off)') . "\n";
echo "\n";

// BASE_URL come lo calcola index.php
$baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_URL = $baseDir === '' ? '/' : $baseDir . '/';
echo "BASE_URL calcolato: " . $BASE_URL . "\n\n";

// mod_rewrite
echo "=== MOD_REWRITE ===\n";
if (function_exists('apache_get_modules')) {
    $mods = apache_get_modules();
    echo in_array('mod_rewrite', $mods) ? "✅ mod_rewrite attivo\n" : "❌ mod_rewrite NON attivo\n";
} else {
    echo "(apache_get_modules non disponibile, controllo via getenv)\n";
    $rw = getenv('HTTP_MOD_REWRITE') ?: getenv('MOD_REWRITE');
    echo $rw ? "Probabile: attivo\n" : "Indeterminato (chiedi al supporto hosting)\n";
}
echo "\n";

// File essenziali
echo "=== FILE ESSENZIALI ===\n";
$files = [
    '.htaccess'                => __DIR__ . '/.htaccess',
    'index.php'                => __DIR__ . '/index.php',
    'config/database.php'      => __DIR__ . '/config/database.php',
    'controllers/AuthController.php' => __DIR__ . '/controllers/AuthController.php',
    'views/auth/login.php'     => __DIR__ . '/views/auth/login.php',
    'public/css/style.css'     => __DIR__ . '/public/css/style.css',
];
foreach ($files as $name => $path) {
    echo (file_exists($path) ? "✅" : "❌") . " " . $name
       . (file_exists($path) ? "  (" . filesize($path) . " byte)" : "  MANCANTE")
       . "\n";
}
echo "\n";

// Permessi cartelle upload
echo "=== PERMESSI ===\n";
$dirs = ['uploads', 'public/uploads', 'public/uploads/lezioni', 'public/uploads/esami', 'public/uploads/stage'];
foreach ($dirs as $d) {
    $p = __DIR__ . '/' . $d;
    if (file_exists($p)) {
        echo "✅ $d → " . substr(sprintf('%o', fileperms($p)), -4)
           . (is_writable($p) ? " (scrivibile)" : " (NON scrivibile)") . "\n";
    } else {
        echo "⚠️  $d → non esiste (verrà creato all'occorrenza)\n";
    }
}
echo "\n";

// Database
echo "=== DATABASE ===\n";
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT VERSION() AS v");
        $row = $stmt->fetch();
        echo "✅ Connessione OK\n";
        echo "   MySQL: " . ($row['v'] ?? '?') . "\n";

        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "   Tabelle trovate: " . count($tables) . "\n";
        if (count($tables) === 0) {
            echo "   ⚠️  Database vuoto! Devi importare sql/artemisys.sql da phpMyAdmin\n";
        } else {
            $expected = ['users', 'studenti', 'insegnanti', 'lezioni', 'percorsi_accademici'];
            $missing  = array_diff($expected, $tables);
            if (empty($missing)) {
                echo "   ✅ Tabelle principali presenti\n";
            } else {
                echo "   ❌ Mancano: " . implode(', ', $missing) . "\n";
            }

            $u = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            echo "   Utenti registrati: $u\n";
        }
    } catch (Throwable $e) {
        echo "❌ Errore connessione: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ config/database.php non trovato\n";
}
echo "\n";

echo "=== CONTENUTO .htaccess ===\n";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo file_get_contents(__DIR__ . '/.htaccess');
} else {
    echo "❌ .htaccess MANCANTE — è quasi sicuramente questo il problema!\n";
}

echo "\n\n=== FINE DIAGNOSTICA ===\n";
echo "⚠️  CANCELLA QUESTO FILE dopo aver risolto (espone info sensibili).\n";
