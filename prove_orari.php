<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orari Prove — esame_di_stato_prove</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f1f5f9; color: #1e293b; padding: 2rem; }
        h1 { font-size: 1.4rem; margin-bottom: 1.5rem; color: #0c1a3a; }
        table { width: 100%; border-collapse: collapse; background: #fff;
                box-shadow: 0 1px 4px rgba(0,0,0,.1); border-radius: 8px; overflow: hidden; }
        thead { background: #1e40af; color: #fff; }
        thead th { padding: .75rem 1rem; text-align: left; font-size: .85rem; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:hover { background: #e8eef8; }
        tbody td { padding: .65rem 1rem; font-size: .88rem; border-bottom: 1px solid #e2e8f0; }
        .null { color: #94a3b8; font-style: italic; }
        .error { background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 6px; }
    </style>
</head>
<body>

<h1>Tabella <code>esame_di_stato_prove</code> — colonne ora_inizio e ora_fine</h1>

<?php
$host   = '127.0.0.1';
$dbname = 'artemisys';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    $rows = $pdo->query("SELECT id, esame_di_stato_id, tipo_prova, data, ora_inizio, ora_fine FROM esame_di_stato_prove ORDER BY esame_di_stato_id ASC, ordine ASC")->fetchAll();

    if (empty($rows)):
?>
    <p style="margin-top:1rem;color:#64748b;">Nessuna riga trovata nella tabella.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Esame</th>
                <th>Tipo prova</th>
                <th>Data</th>
                <th>Ora inizio</th>
                <th>Ora fine</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['esame_di_stato_id'] ?></td>
                <td><?= htmlspecialchars($r['tipo_prova']) ?></td>
                <td><?= $r['data'] ? date('d/m/Y', strtotime($r['data'])) : '<span class="null">—</span>' ?></td>
                <td><?= $r['ora_inizio'] ? substr($r['ora_inizio'], 0, 5) : '<span class="null">—</span>' ?></td>
                <td><?= $r['ora_fine']   ? substr($r['ora_fine'],   0, 5) : '<span class="null">—</span>' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p style="margin-top:.75rem;font-size:.8rem;color:#64748b;"><?= count($rows) ?> righe trovate.</p>
<?php
    endif;
} catch (PDOException $e) {
    echo '<p class="error">Errore DB: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>

</body>
</html>
