<?php
$mesiIt = [1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',
           7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre'];

$palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#ec4899'];
$coloreMateria = [];
$colIdx = 0;
foreach ($lezioniPerGiorno as $lezioni) {
    foreach ($lezioni as $l) {
        if (!isset($coloreMateria[$l['materia_id']])) {
            $coloreMateria[$l['materia_id']] = $palette[$colIdx % count($palette)];
            $colIdx++;
        }
    }
}

$baseUrl = BASE_URL . 'mio-calendario';
?>

<style>
.cal-wrap { user-select:none; }
.cal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.cal-nav { display:flex; align-items:center; gap:8px; }
.cal-nav a { display:flex; align-items:center; justify-content:center; width:36px; height:36px;
             border-radius:8px; border:1px solid #e2e8f0; color:#1e40af; text-decoration:none; }
.cal-nav a:hover { background:#e8eef8; }
.cal-month-label { font-size:1.2rem; font-weight:700; color:#0c1a3a; min-width:200px; text-align:center; }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
.cal-dow { text-align:center; font-size:.7rem; font-weight:700; color:#94a3b8;
           padding:8px 0; text-transform:uppercase; letter-spacing:.06em; }
.cal-cell { min-height:100px; border-radius:8px; border:1px solid #eef2f7;
            padding:6px 5px; background:#fff; }
.cal-cell.other-month { background:#f9fafb; }
.cal-cell.other-month .cal-day-num { color:#d1d5db; }
.cal-cell.today { border:2px solid #1e40af; background:#eff6ff; }
.cal-cell.today .cal-day-num { background:#1e40af; color:#fff; border-radius:50%; width:22px; height:22px;
                                display:flex; align-items:center; justify-content:center; }
.cal-day-num { font-size:.78rem; font-weight:600; color:#6b7280; margin-bottom:4px;
               width:22px; height:22px; display:flex; align-items:center; justify-content:center; }
.cal-pill { display:block; border-radius:5px; padding:2px 5px; margin-bottom:2px;
            font-size:.68rem; font-weight:600; color:#fff; text-decoration:none;
            overflow:hidden; cursor:pointer; line-height:1.3; }
.cal-pill:hover { opacity:.85; color:#fff; }
.cal-pill .pill-mat { display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-calendar me-2" style="color:#1e40af;"></i>Mio calendario
    </h4>
</div>

<div class="cal-wrap">
    <div class="cal-header">
        <div></div>
        <div class="cal-nav">
            <a href="<?= $baseUrl ?>?mese=<?= $mesePrev ?>&anno=<?= $annoPrev ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
            <div class="cal-month-label"><?= $mesiIt[$mese] ?> <?= $anno ?></div>
            <a href="<?= $baseUrl ?>?mese=<?= $meseNext ?>&anno=<?= $annoNext ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <div></div>
    </div>

    <div class="cal-grid">
        <?php foreach (['Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $d): ?>
            <div class="cal-dow"><?= $d ?></div>
        <?php endforeach; ?>

        <?php
        $giorniMesePrev = (int)date('t', mktime(0,0,0,$mesePrev,1,$annoPrev));
        for ($i = $giornoPartenza - 1; $i > 0; $i--): ?>
            <div class="cal-cell other-month">
                <div class="cal-day-num"><?= $giorniMesePrev - $i + 1 ?></div>
            </div>
        <?php endfor;

        for ($g = 1; $g <= $giorniNelMese; $g++):
            $isOggi = ($g === $oggi && $mese === $oggiMese && $anno === $oggiAnno);
            $lezGiorno = $lezioniPerGiorno[$g] ?? [];
        ?>
            <div class="cal-cell <?= $isOggi ? 'today' : '' ?>">
                <div class="cal-day-num"><?= $g ?></div>
                <?php foreach ($lezGiorno as $l):
                    $col = $coloreMateria[$l['materia_id']] ?? '#3b82f6';
                ?>
                    <a href="<?= BASE_URL ?>percorsi/lezione/<?= $l['id'] ?>"
                       class="cal-pill" style="background:<?= $col ?>;"
                       title="<?= htmlspecialchars($l['materia_nome'].' — '.$l['titolo']) ?>">
                        <span class="pill-mat">
                            <?= htmlspecialchars($l['materia_nome']) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endfor;

        $totCelle = $giornoPartenza - 1 + $giorniNelMese;
        $rim = (7 - ($totCelle % 7)) % 7;
        for ($i = 1; $i <= $rim; $i++): ?>
            <div class="cal-cell other-month">
                <div class="cal-day-num"><?= $i ?></div>
            </div>
        <?php endfor; ?>
    </div>

    <div class="text-muted mt-3" style="font-size:.8rem;">
        <?php $tot = array_sum(array_map('count', $lezioniPerGiorno)); ?>
        <?= $tot > 0 ? $tot . ' ' . ($tot === 1 ? 'lezione' : 'lezioni') . ' tue in ' . $mesiIt[$mese] . ' ' . $anno
                    : 'Nessuna tua lezione in ' . $mesiIt[$mese] . ' ' . $anno ?>
    </div>
</div>
