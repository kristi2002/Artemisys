<?php
/**
 * Script one-shot: crea utenti e profili insegnanti.
 * Aprire dal browser: http://localhost/Artemisys/sql/crea_docenti.php
 * ELIMINARE il file dopo l'esecuzione.
 */

$pdo = new PDO(
    'mysql:host=localhost;dbname=artemisys;charset=utf8mb4',
    'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$docenti = [
    ['cognome' => 'Giusepponi',  'nome' => 'Barbara'],
    ['cognome' => 'Scuriatti',   'nome' => 'Letizia'],
    ['cognome' => 'Bruzzichessi','nome' => 'Jessica'],
    ['cognome' => 'Fraternalli', 'nome' => 'Vittoria'],
    ['cognome' => "D'Ezio",      'nome' => 'Matteo'],
    ['cognome' => 'Sani',        'nome' => 'Federico'],
    ['cognome' => 'Parisi',      'nome' => 'Massimo'],
    ['cognome' => 'Arena',       'nome' => 'Ilaria'],
    ['cognome' => 'Barontini',   'nome' => 'Debora'],
];

function makeUsername(PDO $pdo, string $nome, string $cognome): string {
    $map = ['à'=>'a','á'=>'a','è'=>'e','é'=>'e','ì'=>'i','í'=>'i',
            'ò'=>'o','ó'=>'o','ù'=>'u','ú'=>'u','ñ'=>'n','ç'=>'c',"'"=>''];
    $clean = fn(string $s) => preg_replace('/[^a-z0-9.]/', '',
        strtr(mb_strtolower($s), $map));

    $base = $clean(mb_substr($nome, 0, 1)) . '.' . $clean($cognome);
    $username = $base;
    $i = 2;
    while ((int)$pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?')
                    ->execute([$username]) &&
           (int)$pdo->query("SELECT COUNT(*) FROM users WHERE username = " . $pdo->quote($username))->fetchColumn() > 0) {
        $username = $base . $i++;
    }
    return $username;
}

function makePassword(string $cognome): string {
    $map = ["'"=>'', 'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u'];
    $clean = strtr($cognome, $map);
    return $clean . '2025';
}

echo '<!DOCTYPE html><html lang="it"><head><meta charset="utf-8">
<title>Creazione Docenti</title>
<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:0 20px}
table{width:100%;border-collapse:collapse;margin-top:20px}
th,td{border:1px solid #ccc;padding:8px 12px;text-align:left}
th{background:#f0f0f0}.ok{color:green}.err{color:red}.warn{color:orange}</style>
</head><body>';
echo '<h2>Creazione Docenti Artemisys</h2>';
echo '<table><tr><th>Cognome</th><th>Nome</th><th>Username</th><th>Password</th><th>Stato</th></tr>';

foreach ($docenti as $d) {
    $username = makeUsername($pdo, $d['nome'], $d['cognome']);
    $plainPwd = makePassword($d['cognome']);
    $hash     = password_hash($plainPwd, PASSWORD_DEFAULT);
    $email    = strtolower(preg_replace("/[^a-zA-Z]/", '', $d['nome'][0]) . '.' .
                preg_replace("/[^a-zA-Z]/", '', $d['cognome'])) . '@artemisys.it';

    // Controlla se esiste già
    $exists = (int)$pdo->query(
        "SELECT COUNT(*) FROM users WHERE username = " . $pdo->quote($username)
    )->fetchColumn();

    if ($exists) {
        echo "<tr><td>{$d['cognome']}</td><td>{$d['nome']}</td><td>{$username}</td>
              <td>{$plainPwd}</td><td class='warn'>già presente, saltato</td></tr>";
        continue;
    }

    try {
        $pdo->beginTransaction();

        $pdo->prepare("INSERT INTO users (nome, cognome, username, email, password, ruolo, attivo)
                       VALUES (?, ?, ?, ?, ?, 'docente', 1)")
            ->execute([$d['nome'], $d['cognome'], $username, $email, $hash]);
        $userId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO insegnanti (user_id, nome, cognome, email)
                       VALUES (?, ?, ?, ?)")
            ->execute([$userId, $d['nome'], $d['cognome'], $email]);

        $pdo->commit();
        echo "<tr><td>{$d['cognome']}</td><td>{$d['nome']}</td><td>{$username}</td>
              <td><strong>{$plainPwd}</strong></td><td class='ok'>creato</td></tr>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<tr><td>{$d['cognome']}</td><td>{$d['nome']}</td><td>{$username}</td>
              <td>{$plainPwd}</td><td class='err'>errore: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
}

echo '</table>';
echo '<p style="margin-top:30px;padding:12px;background:#fff3cd;border:1px solid #ffc107">
      <strong>⚠ Importante:</strong> elimina questo file dopo aver verificato i risultati.<br>
      Percorso: <code>sql/crea_docenti.php</code></p>';
echo '</body></html>';
