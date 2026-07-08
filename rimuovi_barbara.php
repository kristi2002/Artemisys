<?php
/**
 * Script one-shot: rimuove Giusepponi Barbara dalla commissione
 * di tutti gli esami di stato. Eliminare questo file dopo l'uso.
 */
define('BASE_PATH', __DIR__ . '/');
require_once BASE_PATH . 'config/database.php';

$pdo = Database::getInstance()->getConnection();

$stmt = $pdo->prepare('SELECT id FROM insegnanti WHERE cognome = ? AND nome = ?');
$stmt->execute(['Giusepponi', 'Barbara']);
$ins = $stmt->fetch();

if (!$ins) {
    echo '<p style="font-family:sans-serif;color:red;">Insegnante "Giusepponi Barbara" non trovata nel database.</p>';
    exit;
}

$cnt = $pdo->prepare('SELECT COUNT(*) FROM esame_di_stato_commissione WHERE insegnante_id = ?');
$cnt->execute([$ins['id']]);
$totale = (int)$cnt->fetchColumn();

$del = $pdo->prepare('DELETE FROM esame_di_stato_commissione WHERE insegnante_id = ?');
$del->execute([$ins['id']]);
$eliminate = $del->rowCount();

echo '<p style="font-family:sans-serif;color:green;">
    ✔ Trovate <strong>' . $totale . '</strong> righe — eliminate <strong>' . $eliminate . '</strong>.
    <br>Giusepponi Barbara è stata rimossa da tutte le commissioni.
    <br><br><strong>Puoi eliminare questo file dal server.</strong>
</p>';
