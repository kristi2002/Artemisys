<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];
$mesiIt   = [1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',
             7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre'];

$mappaAnni = [];
foreach ($percorsoAnni as $pa) {
    $mappaAnni[$pa['percorso_id']][] = ['id' => $pa['id'], 'numero' => $pa['numero']];
}

$palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#ec4899','#14b8a6','#6366f1'];
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

$baseUrl = BASE_URL . 'calendario-presenze?anno_id=' . $annoId;
?>

<style>
.cal-wrap { user-select:none; }
.cal-header { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:16px; }
.cal-nav { display:flex; align-items:center; gap:8px; }
.cal-nav a { display:flex; align-items:center; justify-content:center; width:36px; height:36px;
             border-radius:8px; border:1px solid #e2e8f0; color:#1e40af; text-decoration:none; transition:.15s; }
.cal-nav a:hover { background:#e8eef8; }
.cal-month-label { font-size:1.2rem; font-weight:700; color:#0c1a3a; min-width:200px; text-align:center; }
.cal-filters { display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap; }
.cal-filters .flt-group label { display:block; font-size:.72rem; font-weight:700; color:#64748b;
                                 text-transform:uppercase; letter-spacing:.04em; margin-bottom:4px; }
.cal-filters select { padding:7px 10px; border:1px solid #cbd5e1; border-radius:8px;
                      font-size:.88rem; color:#374151; background:#fff; }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
.cal-dow { text-align:center; font-size:.7rem; font-weight:700; color:#94a3b8;
           padding:8px 0; text-transform:uppercase; letter-spacing:.06em; }
.cal-cell { min-height:120px; border-radius:8px; border:1px solid #eef2f7;
            padding:8px 6px 6px; background:#fff; transition:.12s; }
.cal-cell:hover { border-color:#bfdbfe; }
.cal-cell.other-month { background:#f9fafb; border-color:#f1f5f9; }
.cal-cell.other-month .cal-day-num { color:#d1d5db; }
.cal-cell.today { border:2px solid #1e40af; background:#eff6ff; }
.cal-cell.today .cal-day-num { background:#1e40af; color:#fff; border-radius:50%; width:22px; height:22px;
                                display:flex; align-items:center; justify-content:center; }
.cal-day-num { font-size:.78rem; font-weight:600; color:#6b7280; margin-bottom:5px;
               width:22px; height:22px; display:flex; align-items:center; justify-content:center; }
.cal-pill { display:block; border-radius:5px; padding:3px 6px; margin-bottom:3px;
            font-size:.7rem; font-weight:600; color:#fff; text-decoration:none;
            overflow:hidden; cursor:pointer; transition:.1s; line-height:1.35; }
.cal-pill:hover { opacity:.82; color:#fff; box-shadow:0 2px 6px rgba(0,0,0,.15); }
.cal-pill .pill-mat { display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cal-pill .pill-sub { display:block; font-weight:400; opacity:.88; font-size:.65rem;
                      white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cal-empty { display:flex; flex-direction:column; align-items:center; justify-content:center;
             height:320px; border-radius:12px; border:2px dashed #e2e8f0; color:#94a3b8; gap:10px; }
.cal-legend { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
.cal-legend-item { display:flex; align-items:center; gap:5px; font-size:.78rem; color:#374151; }
.cal-legend-dot { width:10px; height:10px; border-radius:3px; flex-shrink:0; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-calendar-check me-2" style="color:#1e40af;"></i>Calendario Presenze
    </h4>
</div>

<div class="cal-wrap">

    <div class="cal-header">

        <!-- Filtri -->
        <form method="GET" action="<?= BASE_URL ?>calendario-presenze" class="cal-filters" id="formFiltri">
            <input type="hidden" name="mese" value="<?= $mese ?>">
            <input type="hidden" name="anno" value="<?= $anno ?>">
            <div class="flt-group">
                <label>Percorso</label>
                <select name="percorso_id" id="sel_percorso" onchange="aggiornaAnni()" style="min-width:220px;">
                    <option value="">— Seleziona percorso —</option>
                    <?php foreach ($percorsi as $p):
                        $selP = $annoCorrente && $annoCorrente['percorso_id'] == $p['id'];
                    ?>
                        <option value="<?= $p['id'] ?>" data-as="<?= $p['anno_scolastico_id'] ?>"
                                <?= $selP ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nome']) ?> — <?= htmlspecialchars($p['anno_label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flt-group">
                <label>Anno di corso</label>
                <select name="anno_id" id="sel_anno" style="min-width:130px;" <?= !$annoId ? 'disabled' : '' ?>>
                    <option value="">— Anno —</option>
                    <?php if ($annoCorrente):
                        foreach ($percorsoAnni as $pa):
                            if ($pa['percorso_id'] != $annoCorrente['percorso_id']) continue; ?>
                            <option value="<?= $pa['id'] ?>" <?= $pa['id'] == $annoId ? 'selected' : '' ?>>
                                <?= $ordinali[$pa['numero']] ?? $pa['numero'].'° Anno' ?>
                            </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:36px;">
                <i class="fas fa-search me-1"></i>Vai
            </button>
        </form>

        <!-- Navigazione mese -->
        <div class="cal-nav">
            <a href="<?= $baseUrl ?>&mese=<?= $mesePrev ?>&anno=<?= $annoPrev ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
            <div class="cal-month-label"><?= $mesiIt[$mese] ?> <?= $anno ?></div>
            <a href="<?= $baseUrl ?>&mese=<?= $meseNext ?>&anno=<?= $annoNext ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

    </div>

    <?php if (!$annoId): ?>

        <div class="cal-empty">
            <i class="fas fa-calendar-alt" style="font-size:2.5rem;color:#e2e8f0;"></i>
            <span style="font-size:.95rem;">Seleziona un percorso e un anno di corso</span>
        </div>

    <?php else: ?>

        <!-- Legenda materie -->
        <?php if (!empty($coloreMateria)):
            $materieViste = [];
            foreach ($lezioniPerGiorno as $lz) foreach ($lz as $l)
                $materieViste[$l['materia_id']] = $l['materia_nome'];
        ?>
        <div class="cal-legend">
            <?php foreach ($materieViste as $mid => $mnome): ?>
                <div class="cal-legend-item">
                    <div class="cal-legend-dot" style="background:<?= $coloreMateria[$mid] ?>;"></div>
                    <span><?= htmlspecialchars($mnome) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Griglia -->
        <div class="cal-grid">

            <?php foreach (['Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $d): ?>
                <div class="cal-dow"><?= $d ?></div>
            <?php endforeach; ?>

            <?php
            // Giorni mese precedente
            $giorniMesePrev = (int)date('t', mktime(0,0,0,$mesePrev,1,$annoPrev));
            for ($i = $giornoPartenza - 1; $i > 0; $i--): ?>
                <div class="cal-cell other-month">
                    <div class="cal-day-num"><?= $giorniMesePrev - $i + 1 ?></div>
                </div>
            <?php endfor;

            // Giorni mese corrente
            for ($g = 1; $g <= $giorniNelMese; $g++):
                $isOggi  = ($g === $oggi && $mese === $oggiMese && $anno === $oggiAnno);
                $lezGiorno = $lezioniPerGiorno[$g] ?? [];
            ?>
                <div class="cal-cell <?= $isOggi ? 'today' : '' ?>">
                    <div class="cal-day-num"><?= $g ?></div>
                    <?php foreach ($lezGiorno as $l):
                        $col    = $coloreMateria[$l['materia_id']] ?? '#3b82f6';
                        $dur    = '';
                        if ($l['durata_minuti']) {
                            $d = (int)$l['durata_minuti'];
                            $dur = ($d % 60 === 0) ? ($d/60).'h' : floor($d/60).'h '.($d%60).'m';
                        }
                        $prezStr = $l['totale'] > 0 ? $l['presenti'].'/'.$l['totale'] : '';
                    ?>
                        <a href="<?= BASE_URL ?>percorsi/lezione/<?= $l['id'] ?>"
                           class="cal-pill" style="background:<?= $col ?>;"
                           title="<?= htmlspecialchars($l['materia_nome'].' — '.$l['titolo']) ?>">
                            <span class="pill-mat"><?= htmlspecialchars($l['materia_nome']) ?></span>
                            <span class="pill-sub">
                                <?= htmlspecialchars($l['titolo']) ?>
                                <?= $dur     ? ' · '.$dur     : '' ?>
                                <?= $prezStr ? ' · '.$prezStr : '' ?>
                                <?= $l['online'] ? ' · 🌐' : '' ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endfor;

            // Celle finali
            $totCelle = $giornoPartenza - 1 + $giorniNelMese;
            $rimanenti = (7 - ($totCelle % 7)) % 7;
            for ($i = 1; $i <= $rimanenti; $i++): ?>
                <div class="cal-cell other-month">
                    <div class="cal-day-num"><?= $i ?></div>
                </div>
            <?php endfor; ?>

        </div><!-- /cal-grid -->

        <div class="text-muted mt-3" style="font-size:.8rem;">
            <?php $tot = array_sum(array_map('count', $lezioniPerGiorno)); ?>
            <?= $tot > 0
                ? $tot . ' ' . ($tot === 1 ? 'lezione' : 'lezioni') . ' in ' . $mesiIt[$mese] . ' ' . $anno
                : 'Nessuna lezione in ' . $mesiIt[$mese] . ' ' . $anno ?>
        </div>

    <?php endif; ?>

</div>

<script>
const mappaAnni = <?= json_encode($mappaAnni) ?>;
const ordinali  = <?= json_encode($ordinali) ?>;

function aggiornaAnni() {
    const sel     = document.getElementById('sel_percorso');
    const selAnno = document.getElementById('sel_anno');
    const pid     = sel.value;
    selAnno.innerHTML = '<option value="">— Anno —</option>';
    if (!pid || !mappaAnni[pid]) { selAnno.disabled = true; return; }
    selAnno.disabled = false;
    mappaAnni[pid].forEach(a => {
        const label = ordinali[a.numero] ?? (a.numero + '° Anno');
        selAnno.innerHTML += `<option value="${a.id}">${label}</option>`;
    });
}
</script>
