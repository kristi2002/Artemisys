<?php
/**
 * Script per creare account utente per tutti gli studenti.
 * Username = cognome.nome (lowercase, senza spazi)
 * Password = Cognome2025
 *
 * Da eseguire UNA VOLTA:  php crea_studenti.php  oppure aprire da browser.
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

// Garantisci colonna user_id
try { $db->exec("ALTER TABLE studenti ADD COLUMN user_id INT NULL"); } catch (Exception $e) {}

$studenti = $db->query("SELECT id, nome, cognome, email FROM studenti WHERE attivo = 1")->fetchAll();

$creati = 0;
$gia    = 0;

foreach ($studenti as $s) {
    // Skip se ha già user_id
    if (!empty($s['user_id'])) { $gia++; continue; }

    $nomeNorm    = strtolower(preg_replace('/[^a-zA-Z]/', '', $s['nome']));
    $cognomeNorm = strtolower(preg_replace('/[^a-zA-Z]/', '', $s['cognome']));

    $username = $cognomeNorm . '.' . $nomeNorm;
    $email    = $s['email'] ?: ($username . '@artemisys.local');
    $password = ucfirst($cognomeNorm) . '2025';
    $hash     = password_hash($password, PASSWORD_BCRYPT);

    // Evita duplicati username/email
    $check = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $check->execute([$username, $email]);
    if ($check->fetch()) {
        echo "⏭  Salto {$s['cognome']} {$s['nome']} (username/email già esistente)<br>\n";
        continue;
    }

    $ins = $db->prepare("
        INSERT INTO users (nome, cognome, username, email, password, ruolo, attivo)
        VALUES (?, ?, ?, ?, ?, 'studente', 1)
    ");
    $ins->execute([$s['nome'], $s['cognome'], $username, $email, $hash]);
    $userId = (int)$db->lastInsertId();

    $db->prepare("UPDATE studenti SET user_id = ? WHERE id = ?")->execute([$userId, $s['id']]);

    echo "✅ {$s['cognome']} {$s['nome']} → username: <code>{$username}</code>, password: <code>{$password}</code><br>\n";
    $creati++;
}

echo "<hr>";
echo "<h3>Riepilogo</h3>";
echo "Creati: <strong>{$creati}</strong><br>";
echo "Già presenti: <strong>{$gia}</strong><br>";
echo "Totale studenti: <strong>" . count($studenti) . "</strong>";
