<?php
/**
 * Script one-shot: assegna studenti a percorso e anno di corso.
 * Aprire: http://localhost/Artemisys/sql/assegna_studenti.php
 * ELIMINARE il file dopo l'uso.
 */

$pdo = new PDO(
    'mysql:host=localhost;dbname=artemisys;charset=utf8mb4',
    'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

// ── Dati base ─────────────────────────────────────────────────────────────────
$percorsi = $pdo->query("
    SELECT p.id, p.nome, a.anno AS anno_label, p.anno_scolastico_id
    FROM percorsi_accademici p
    LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
    ORDER BY a.anno DESC, p.nome ASC
")->fetchAll();

$percorsoAnni = $pdo->query("
    SELECT pa.id, pa.numero, pa.percorso_id
    FROM percorso_anni pa
    ORDER BY pa.percorso_id ASC, pa.numero ASC
")->fetchAll();

$studenti = $pdo->query("
    SELECT s.id, s.cognome, s.nome,
           p.nome       AS percorso_attuale,
           a.anno       AS anno_sc_attuale,
           pa.numero    AS anno_corso_attuale
    FROM studenti s
    LEFT JOIN studente_iscrizioni si ON si.studente_id = s.id
    LEFT JOIN percorsi_accademici p  ON p.id = si.percorso_id
    LEFT JOIN anni_scolastici a      ON a.id = p.anno_scolastico_id
    LEFT JOIN studente_anni san      ON san.studente_id = s.id
                                    AND san.anno_scolastico_id = p.anno_scolastico_id
    LEFT JOIN percorso_anni pa       ON pa.id = san.percorso_anno_id
    WHERE s.attivo = 1
    ORDER BY s.cognome ASC, s.nome ASC
")->fetchAll();

$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

// ── Esecuzione ────────────────────────────────────────────────────────────────
$risultati = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['percorso_anno_id'])) {
    $percorsoId     = (int)$_POST['percorso_id'];
    $percorsoAnnoId = (int)$_POST['percorso_anno_id'];
    $annoScId       = (int)$_POST['anno_scolastico_id'];
    $selezionati    = array_map('intval', $_POST['studenti_ids'] ?? []);

    $stmtIsc = $pdo->prepare("
        INSERT INTO studente_iscrizioni (studente_id, percorso_id)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE percorso_id = VALUES(percorso_id)
    ");
    $stmtAnno = $pdo->prepare("
        INSERT INTO studente_anni (studente_id, percorso_anno_id, anno_scolastico_id)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE percorso_anno_id = VALUES(percorso_anno_id)
    ");

    foreach ($studenti as $s) {
        if (!in_array((int)$s['id'], $selezionati)) {
            $risultati[] = ['nome' => $s['cognome'].' '.$s['nome'], 'stato' => 'saltato'];
            continue;
        }
        try {
            $stmtIsc->execute([$s['id'], $percorsoId]);
            $stmtAnno->execute([$s['id'], $percorsoAnnoId, $annoScId]);
            $risultati[] = ['nome' => $s['cognome'].' '.$s['nome'], 'stato' => 'ok'];
        } catch (Exception $e) {
            $risultati[] = ['nome' => $s['cognome'].' '.$s['nome'], 'stato' => 'errore', 'err' => $e->getMessage()];
        }
    }
    $done = true;
}

// Mappa percorso → anni per JS
$mappaAnni = [];
foreach ($percorsoAnni as $pa) {
    $mappaAnni[$pa['percorso_id']][] = ['id' => $pa['id'], 'numero' => $pa['numero']];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Assegna Studenti</title>
<style>
  *{box-sizing:border-box}
  body{font-family:sans-serif;max-width:780px;margin:40px auto;padding:0 20px;color:#1e293b}
  h2{color:#1e40af;margin-bottom:4px}
  .sub{color:#64748b;font-size:.9rem;margin-bottom:28px}
  label.lbl{display:block;font-weight:600;font-size:.85rem;margin-bottom:4px;margin-top:18px}
  select{width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:.95rem}
  .btn{margin-top:24px;background:#1e40af;color:#fff;border:none;padding:10px 28px;
       border-radius:6px;font-size:1rem;cursor:pointer;width:100%}
  .btn:hover{background:#1d3a9f}
  .student-list{margin-top:16px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden}
  .student-row{display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid #f1f5f9;font-size:.9rem}
  .student-row:last-child{border-bottom:none}
  .student-row:hover{background:#f8fafc}
  .badge-warn{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;
              font-size:.75rem;padding:2px 8px;border-radius:20px;white-space:nowrap}
  .badge-new{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;
             font-size:.75rem;padding:2px 8px;border-radius:20px}
  .ctrl{display:flex;gap:12px;margin-top:12px;font-size:.85rem}
  .ctrl a{color:#1e40af;cursor:pointer;text-decoration:underline}
  table{width:100%;border-collapse:collapse;margin-top:20px}
  th,td{border:1px solid #e2e8f0;padding:8px 12px;text-align:left;font-size:.88rem}
  th{background:#f1f5f9}
  .ok{color:#16a34a;font-weight:600}
  .err{color:#dc2626}
  .skip{color:#94a3b8}
  .warn-box{background:#fffbeb;padding:14px;border-radius:8px;border:1px solid #fcd34d;
            margin-top:24px;font-size:.88rem}
</style>
</head>
<body>

<h2>Assegna studenti al percorso</h2>
<p class="sub">Seleziona il percorso e l'anno di corso, poi scegli quali studenti assegnare.</p>

<?php if (!$done): ?>

<form method="POST">
    <label class="lbl">Percorso</label>
    <select name="percorso_id" id="sel_percorso" required onchange="aggiornaAnni()">
        <option value="">— Seleziona percorso —</option>
        <?php foreach ($percorsi as $p): ?>
            <option value="<?= $p['id'] ?>"
                    data-as="<?= $p['anno_scolastico_id'] ?>">
                <?= htmlspecialchars($p['nome']) ?> — A.S. <?= htmlspecialchars($p['anno_label']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label class="lbl">Anno di corso</label>
    <select name="percorso_anno_id" id="sel_anno" required disabled>
        <option value="">— Prima seleziona il percorso —</option>
    </select>
    <input type="hidden" name="anno_scolastico_id" id="hidden_as" value="">

    <label class="lbl">Studenti (<?= count($studenti) ?>)</label>
    <div class="ctrl">
        <a onclick="setTutti(true)">Seleziona tutti</a>
        <a onclick="setTutti(false)">Deseleziona tutti</a>
        <a onclick="setSoloNuovi()">Solo senza percorso</a>
    </div>
    <div class="student-list" style="margin-top:8px;">
        <?php foreach ($studenti as $s):
            $haPercorso = !empty($s['percorso_attuale']);
            $defaultChecked = !$haPercorso;
        ?>
            <div class="student-row">
                <input type="checkbox"
                       name="studenti_ids[]"
                       value="<?= $s['id'] ?>"
                       class="stud-check <?= $haPercorso ? 'has-percorso' : 'no-percorso' ?>"
                       <?= $defaultChecked ? 'checked' : '' ?>
                       style="width:16px;height:16px;cursor:pointer;flex-shrink:0;">
                <span style="flex:1;font-weight:500;">
                    <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                </span>
                <?php if ($haPercorso): ?>
                    <span class="badge-warn">
                        <?= htmlspecialchars($s['percorso_attuale']) ?>
                        <?php if ($s['anno_corso_attuale']): ?>
                            — <?= $ordinali[$s['anno_corso_attuale']] ?? $s['anno_corso_attuale'].'° Anno' ?>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <span class="badge-new">Nuovo</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="submit" class="btn">Assegna studenti selezionati</button>
</form>

<script>
const mappaAnni = <?= json_encode($mappaAnni) ?>;
const ordinali  = <?= json_encode($ordinali) ?>;

function aggiornaAnni() {
    const sel     = document.getElementById('sel_percorso');
    const selAnno = document.getElementById('sel_anno');
    const hiddenAs = document.getElementById('hidden_as');
    const opt     = sel.options[sel.selectedIndex];
    const pid     = sel.value;

    hiddenAs.value = opt ? opt.dataset.as : '';
    selAnno.innerHTML = '';

    if (!pid || !mappaAnni[pid]) {
        selAnno.disabled = true;
        selAnno.innerHTML = '<option value="">— Prima seleziona il percorso —</option>';
        return;
    }
    selAnno.disabled = false;
    selAnno.innerHTML = '<option value="">— Seleziona anno —</option>';
    mappaAnni[pid].forEach(a => {
        const label = ordinali[a.numero] ?? (a.numero + '° Anno');
        selAnno.innerHTML += `<option value="${a.id}">${label}</option>`;
    });
}

function setTutti(v) {
    document.querySelectorAll('.stud-check').forEach(c => c.checked = v);
}
function setSoloNuovi() {
    document.querySelectorAll('.stud-check').forEach(c => {
        c.checked = c.classList.contains('no-percorso');
    });
}
</script>

<?php else: ?>

<?php
$ok   = count(array_filter($risultati, fn($r) => $r['stato'] === 'ok'));
$skip = count(array_filter($risultati, fn($r) => $r['stato'] === 'saltato'));
$ko   = count(array_filter($risultati, fn($r) => $r['stato'] === 'errore'));
?>

<table>
    <tr><th>Studente</th><th>Esito</th></tr>
    <?php foreach ($risultati as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['nome']) ?></td>
            <td class="<?= $r['stato'] === 'ok' ? 'ok' : ($r['stato'] === 'saltato' ? 'skip' : 'err') ?>">
                <?php if ($r['stato'] === 'ok')      echo 'Assegnato';
                elseif ($r['stato'] === 'saltato')   echo 'Saltato';
                else echo 'Errore: ' . htmlspecialchars($r['err'] ?? ''); ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<p style="margin-top:14px;font-size:.95rem;">
    <strong style="color:#16a34a;"><?= $ok ?> assegnati</strong>
    <?= $skip > 0 ? " · <span style='color:#94a3b8'>{$skip} saltati</span>" : '' ?>
    <?= $ko   > 0 ? " · <span style='color:#dc2626'>{$ko} errori</span>"   : '' ?>
</p>

<div class="warn-box">
    <strong>Fatto!</strong> Elimina questo file: <code>sql/assegna_studenti.php</code>
</div>

<?php endif; ?>

</body>
</html>
