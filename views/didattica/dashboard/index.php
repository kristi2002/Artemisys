<?php
$ordinali = [1=>'1°',2=>'2°',3=>'3°',4=>'4°',5=>'5°',6=>'6°',7=>'7°',8=>'8°',9=>'9°',10=>'10°'];
$tipoBadge = ['scritto'=>'#3b82f6','orale'=>'#10b981','pratico'=>'#f59e0b'];
$stepLabels = [1=>'Candidatura',2=>'Convenzione',3=>'Tirocinio',4=>'Valutazione'];
$stepColors = [1=>'#3b82f6',2=>'#8b5cf6',3=>'#f59e0b',4=>'#10b981'];

$giorniIt = ['Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica'];
$mesiIt   = ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
             'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];
$dataOggi = $giorniIt[(int)date('N')-1] . ' ' . date('d') . ' '
          . $mesiIt[(int)date('n')-1] . ' ' . date('Y');

$oggi = date('Y-m-d');
?>

<!-- Header con saluto -->
<div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#0c1a3a 0%,#1e40af 60%,#3b82f6 100%);color:#fff;">
    <div class="card-body py-4 px-4 d-flex align-items-center gap-4 flex-wrap">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-tachometer-alt" style="font-size:1.7rem;"></i>
        </div>
        <div class="flex-grow-1">
            <h4 class="mb-1 fw-bold">Buongiorno, <?= htmlspecialchars($_SESSION['user_nome'] ?? 'Admin') ?>!</h4>
            <div style="opacity:.85;font-size:.92rem;">
                Ecco una panoramica della scuola.
            </div>
        </div>
        <div class="text-end" style="font-size:.85rem;opacity:.9;">
            <i class="fas fa-calendar-day me-1"></i><?= $dataOggi ?>
        </div>
    </div>
</div>

<!-- ── STATISTICHE ── -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['icon'=>'fa-user-graduate',    'label'=>'Studenti',         'value'=>$stats['studenti'],      'color'=>'#3b82f6', 'href'=>'studenti'],
        ['icon'=>'fa-chalkboard-teacher','label'=>'Docenti',         'value'=>$stats['docenti'],       'color'=>'#8b5cf6', 'href'=>'insegnanti'],
        ['icon'=>'fa-route',            'label'=>'Percorsi',         'value'=>$stats['percorsi'],      'color'=>'#06b6d4', 'href'=>'percorsi'],
        ['icon'=>'fa-atom',             'label'=>'Materie',          'value'=>$stats['materie'],       'color'=>'#10b981', 'href'=>'materie'],
        ['icon'=>'fa-book-open',        'label'=>'Lezioni del mese', 'value'=>$stats['lezioni_mese'],  'color'=>'#f59e0b', 'href'=>'lezioni'],
        ['icon'=>'fa-file-alt',         'label'=>'Esami in arrivo',  'value'=>$stats['esami_futuri'],  'color'=>'#ef4444', 'href'=>'esami'],
        ['icon'=>'fa-briefcase',        'label'=>'Stage attivi',     'value'=>$stats['stage_attivi'],  'color'=>'#ec4899', 'href'=>'stage'],
        ['icon'=>'fa-calendar-day',     'label'=>'Eventi aperti',    'value'=>$stats['eventi_aperti'], 'color'=>'#14b8a6', 'href'=>'eventi'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-xl-3 col-md-6">
        <a href="<?= BASE_URL . $c['href'] ?>"
           class="card border-0 shadow-sm h-100 text-decoration-none"
           style="border-left:4px solid <?= $c['color'] ?> !important;transition:.15s;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 16px rgba(0,0,0,.08)'"
           onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:10px;background:<?= $c['color'] ?>1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:1.3rem;"></i>
                </div>
                <div>
                    <div style="font-size:1.7rem;font-weight:800;color:#0c1a3a;line-height:1;">
                        <?= $c['value'] ?>
                    </div>
                    <div class="text-muted" style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
                        <?= $c['label'] ?>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── LISTE RAPIDE ── -->
<div class="row g-4 mb-4">

    <!-- Prossime lezioni -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-book-open me-2" style="color:#f59e0b;"></i>Prossime lezioni
                </h6>
                <a href="<?= BASE_URL ?>lezioni" class="text-decoration-none small fw-semibold" style="color:#1e40af;">
                    Tutte <i class="fas fa-arrow-right ms-1" style="font-size:.7rem;"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($prossLezioni)): ?>
                    <div class="text-center text-muted py-4" style="font-size:.85rem;">
                        Nessuna lezione in programma.
                    </div>
                <?php else: foreach ($prossLezioni as $l):
                    $isOggi = $l['data'] === $oggi;
                    $g = (int)date('j', strtotime($l['data']));
                    $m = $mesiIt[(int)date('n', strtotime($l['data']))-1];
                ?>
                <a href="<?= BASE_URL ?>percorsi/lezione/<?= $l['id'] ?>"
                   class="d-flex align-items-center gap-3 py-2 px-3 border-bottom text-decoration-none"
                   style="color:inherit;<?= $isOggi ? 'background:#fffbeb;' : '' ?>"
                   onmouseover="this.style.background='<?= $isOggi ? '#fef3c7' : '#f8fafc' ?>'"
                   onmouseout="this.style.background='<?= $isOggi ? '#fffbeb' : '#fff' ?>'">
                    <div style="flex-shrink:0;width:40px;text-align:center;border-right:1px solid #e2e8f0;padding-right:10px;">
                        <div style="font-size:.62rem;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?= substr($m,0,3) ?></div>
                        <div style="font-size:1.1rem;font-weight:800;color:<?= $isOggi ? '#f59e0b' : '#0c1a3a' ?>;line-height:1;"><?= $g ?></div>
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;">
                            <?= htmlspecialchars($l['titolo']) ?>
                            <?php if ($isOggi): ?>
                                <span class="badge ms-1" style="background:#f59e0b1a;color:#92400e;font-size:.6rem;">OGGI</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= htmlspecialchars($l['materia_nome']) ?> · <?= htmlspecialchars($l['percorso_nome']) ?> · <?= ($ordinali[$l['anno_numero']] ?? $l['anno_numero']) ?> Anno
                        </div>
                    </div>
                </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Prossimi esami -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-file-alt me-2" style="color:#ef4444;"></i>Prossimi esami
                </h6>
                <a href="<?= BASE_URL ?>esami" class="text-decoration-none small fw-semibold" style="color:#1e40af;">
                    Tutti <i class="fas fa-arrow-right ms-1" style="font-size:.7rem;"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($prossEsami)): ?>
                    <div class="text-center text-muted py-4" style="font-size:.85rem;">
                        Nessun esame in arrivo.
                    </div>
                <?php else: foreach ($prossEsami as $e):
                    $col = $tipoBadge[$e['tipo']] ?? '#64748b';
                ?>
                <a href="<?= BASE_URL ?>esami/detail/<?= $e['id'] ?>"
                   class="d-flex align-items-center gap-3 py-2 px-3 border-bottom text-decoration-none"
                   style="color:inherit;"
                   onmouseover="this.style.background='#f8fafc'"
                   onmouseout="this.style.background='#fff'">
                    <div style="flex-shrink:0;width:40px;text-align:center;border-right:1px solid #e2e8f0;padding-right:10px;">
                        <div style="font-size:.62rem;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?= date('M', strtotime($e['data'])) ?></div>
                        <div style="font-size:1.1rem;font-weight:800;color:#0c1a3a;line-height:1;"><?= date('d', strtotime($e['data'])) ?></div>
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;">
                            <?= htmlspecialchars($e['titolo']) ?>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= htmlspecialchars($e['materia_nome']) ?> · <?= htmlspecialchars($e['percorso_nome']) ?>
                        </div>
                    </div>
                    <span class="badge" style="background:<?= $col ?>1a;color:<?= $col ?>;border:1px solid <?= $col ?>33;font-size:.65rem;">
                        <?= ucfirst($e['tipo']) ?>
                    </span>
                </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Rette in scadenza -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-euro-sign me-2" style="color:#10b981;"></i>Rette in scadenza
                </h6>
                <a href="<?= BASE_URL ?>rette" class="text-decoration-none small fw-semibold" style="color:#1e40af;">
                    Tutte <i class="fas fa-arrow-right ms-1" style="font-size:.7rem;"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($retteScadenza)): ?>
                    <div class="text-center text-muted py-4" style="font-size:.85rem;">
                        Nessuna rata in scadenza nei prossimi 30 giorni.
                    </div>
                <?php else: foreach ($retteScadenza as $r):
                    $scaduta = $r['scadenza'] < $oggi;
                ?>
                <a href="<?= BASE_URL ?>rette/detail/<?= $r['retta_id'] ?>"
                   class="d-flex align-items-center gap-3 py-2 px-3 border-bottom text-decoration-none"
                   style="color:inherit;<?= $scaduta ? 'background:#fef2f2;' : '' ?>"
                   onmouseover="this.style.background='<?= $scaduta ? '#fee2e2' : '#f8fafc' ?>'"
                   onmouseout="this.style.background='<?= $scaduta ? '#fef2f2' : '#fff' ?>'">
                    <div style="flex-shrink:0;width:40px;text-align:center;">
                        <i class="fas <?= $scaduta ? 'fa-exclamation-triangle' : 'fa-clock' ?>"
                           style="color:<?= $scaduta ? '#ef4444' : '#f59e0b' ?>;font-size:1.1rem;"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;">
                            <?= htmlspecialchars($r['cognome'] . ' ' . $r['nome']) ?>
                            <span class="text-muted" style="font-size:.72rem;">— Rata #<?= $r['numero'] ?></span>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= $scaduta ? '<span style="color:#ef4444;font-weight:600;">SCADUTA il</span>' : 'Scade il' ?>
                            <?= date('d/m/Y', strtotime($r['scadenza'])) ?>
                        </div>
                    </div>
                    <div class="fw-bold" style="color:<?= $scaduta ? '#ef4444' : '#0c1a3a' ?>;font-size:.9rem;">
                        € <?= number_format($r['importo'], 2, ',', '.') ?>
                    </div>
                </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Stage da valutare / in chiusura -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-briefcase me-2" style="color:#ec4899;"></i>Stage in fase finale
                </h6>
                <a href="<?= BASE_URL ?>stage" class="text-decoration-none small fw-semibold" style="color:#1e40af;">
                    Tutti <i class="fas fa-arrow-right ms-1" style="font-size:.7rem;"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($stageDaValutare)): ?>
                    <div class="text-center text-muted py-4" style="font-size:.85rem;">
                        Nessuno stage in fase finale.
                    </div>
                <?php else: foreach ($stageDaValutare as $st):
                    $col = $stepColors[$st['step']] ?? '#64748b';
                ?>
                <a href="<?= BASE_URL ?>stage/detail/<?= $st['id'] ?>"
                   class="d-flex align-items-center gap-3 py-2 px-3 border-bottom text-decoration-none"
                   style="color:inherit;"
                   onmouseover="this.style.background='#f8fafc'"
                   onmouseout="this.style.background='#fff'">
                    <div style="flex-shrink:0;width:32px;height:32px;border-radius:50%;background:<?= $col ?>1a;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-graduation-cap" style="color:<?= $col ?>;font-size:.9rem;"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;">
                            <?= htmlspecialchars($st['cognome'] . ' ' . $st['nome']) ?>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= htmlspecialchars($st['azienda']) ?>
                            <?= $st['data_fine'] ? ' · fine ' . date('d/m/Y', strtotime($st['data_fine'])) : '' ?>
                        </div>
                    </div>
                    <span class="badge" style="background:<?= $col ?>1a;color:<?= $col ?>;border:1px solid <?= $col ?>33;font-size:.65rem;">
                        Step <?= $st['step'] ?> · <?= $stepLabels[$st['step']] ?>
                    </span>
                </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── EVENTI PROSSIMI ── -->
<?php if (!empty($prossEventi)): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <h6 class="fw-semibold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.05em;font-size:.78rem;">
            <i class="fas fa-calendar-day me-2"></i>Eventi prossimi
        </h6>
    </div>
    <?php foreach ($prossEventi as $e):
        $g = (int)date('j', strtotime($e['data_evento']));
        $m = substr($mesiIt[(int)date('n', strtotime($e['data_evento']))-1], 0, 3);
    ?>
    <div class="col-md-6 col-lg-3">
        <a href="<?= BASE_URL ?>eventi/detail/<?= $e['id'] ?>"
           class="card border-0 shadow-sm h-100 text-decoration-none"
           onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="flex-shrink:0;width:46px;text-align:center;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#fff;">
                    <div style="background:#1e40af;color:#fff;padding:1px 0;font-size:.6rem;font-weight:700;text-transform:uppercase;"><?= $m ?></div>
                    <div style="padding:4px 0;font-size:1.15rem;font-weight:800;color:#0c1a3a;line-height:1;"><?= $g ?></div>
                </div>
                <div style="min-width:0;">
                    <div class="fw-semibold" style="color:#0c1a3a;font-size:.85rem;line-height:1.2;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($e['titolo']) ?>
                    </div>
                    <div class="text-muted" style="font-size:.7rem;">
                        <i class="fas fa-users me-1"></i><?= $e['num_iscritti'] ?> iscritti
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── AZIONI RAPIDE ── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-3">
        <h6 class="fw-semibold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.05em;font-size:.78rem;">
            <i class="fas fa-bolt me-2"></i>Azioni rapide
        </h6>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>studenti/create" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user-plus me-1"></i>Nuovo studente
            </a>
            <a href="<?= BASE_URL ?>insegnanti/create" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chalkboard-teacher me-1"></i>Nuovo docente
            </a>
            <a href="<?= BASE_URL ?>esami" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-file-alt me-1"></i>Nuovo esame
            </a>
            <a href="<?= BASE_URL ?>eventi" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-calendar-plus me-1"></i>Nuovo evento
            </a>
            <a href="<?= BASE_URL ?>stage" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-briefcase me-1"></i>Nuovo stage
            </a>
            <a href="<?= BASE_URL ?>rette" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-euro-sign me-1"></i>Nuova retta
            </a>
            <a href="<?= BASE_URL ?>calendario-presenze" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-calendar-check me-1"></i>Calendario
            </a>
        </div>
    </div>
</div>
