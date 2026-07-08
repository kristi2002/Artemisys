<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

function fmtDurata($min) {
    if (!$min) return '';
    $m = (int)$min;
    return $m % 60 === 0 ? ($m/60).'h' : floor($m/60).'h '.($m%60).'m';
}

function rigaLezione($l, $ordinali) {
    $nomeAnno = $ordinali[$l['anno_numero']] ?? $l['anno_numero'].'° Anno';
    $dur = fmtDurata($l['durata_minuti']);
    $prez = $l['totale'] > 0 ? $l['presenti'].'/'.$l['totale'] : '';
    ?>
    <div class="d-flex align-items-center gap-3 py-2 px-3 border-bottom"
         style="cursor:pointer;transition:.15s;"
         onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'"
         onclick="window.location='<?= BASE_URL ?>percorsi/lezione/<?= $l['id'] ?>'">

        <!-- Data -->
        <div style="flex-shrink:0;width:48px;text-align:center;border-right:1px solid #e2e8f0;padding-right:12px;">
            <?php if ($l['data']): ?>
            <div style="font-size:.65rem;text-transform:uppercase;color:#94a3b8;font-weight:700;">
                <?= date('M', strtotime($l['data'])) ?>
            </div>
            <div style="font-size:1.3rem;font-weight:800;color:#0c1a3a;line-height:1;">
                <?= date('d', strtotime($l['data'])) ?>
            </div>
            <?php else: ?>
            <i class="fas fa-calendar-times text-muted"></i>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap" style="color:#0c1a3a;font-size:.95rem;">
                <?= htmlspecialchars($l['titolo']) ?>
                <?php if ($l['online']): ?>
                    <span class="badge" style="background:#3b82f61a;color:#3b82f6;font-size:.65rem;">
                        <i class="fas fa-globe me-1"></i>Online
                    </span>
                <?php endif; ?>
                <?php if (!empty($l['link_online']) && $l['online']): ?>
                <a href="<?= htmlspecialchars($l['link_online']) ?>" target="_blank" rel="noopener"
                   onclick="event.stopPropagation()"
                   class="badge text-decoration-none"
                   style="background:#10b9811a;color:#10b981;border:1px solid #10b98133;font-size:.65rem;">
                    <i class="fas fa-video me-1"></i>Apri
                </a>
                <?php endif; ?>
            </div>
            <div class="text-muted" style="font-size:.78rem;">
                <i class="fas fa-atom me-1"></i><?= htmlspecialchars($l['materia_nome']) ?>
                · <?= htmlspecialchars($l['percorso_nome']) ?> — <?= $nomeAnno ?>
                <?php if ($l['anno_label']): ?> · A.S. <?= htmlspecialchars($l['anno_label']) ?><?php endif; ?>
            </div>
        </div>

        <!-- Durata -->
        <?php if ($dur): ?>
        <div class="text-muted small" style="flex-shrink:0;">
            <i class="fas fa-clock me-1"></i><?= $dur ?>
        </div>
        <?php endif; ?>

        <!-- Presenze -->
        <?php if ($prez): ?>
        <div style="flex-shrink:0;text-align:center;min-width:60px;">
            <div class="fw-bold" style="color:#10b981;font-size:.85rem;"><?= $prez ?></div>
            <div class="text-muted" style="font-size:.65rem;">presenze</div>
        </div>
        <?php endif; ?>

        <i class="fas fa-chevron-right text-muted ms-2"></i>
    </div>
    <?php
}
?>

<!-- Saluto -->
<div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%);color:#fff;">
    <div class="card-body py-4 px-4 d-flex align-items-center gap-4 flex-wrap">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-chalkboard-teacher" style="font-size:1.6rem;"></i>
        </div>
        <div class="flex-grow-1">
            <h4 class="mb-1 fw-bold">Ciao, <?= htmlspecialchars($insegnante['nome']) ?>!</h4>
            <div style="opacity:.85;font-size:.9rem;">
                <?= count($lezioniOggi) ?> lezioni oggi · <?= count($lezioniProssime) ?> in arrivo · <?= count($lezioniPassate) ?> svolte
            </div>
        </div>
        <?php
        $giorniIt = ['Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica'];
        $mesiItDoc = ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
                      'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];
        $oggiData = $giorniIt[(int)date('N')-1] . ' ' . date('d') . ' '
                  . $mesiItDoc[(int)date('n')-1] . ' ' . date('Y');
        ?>
        <div class="text-end" style="font-size:.85rem;opacity:.9;">
            <i class="fas fa-calendar-day me-1"></i>
            <?= $oggiData ?>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- LEZIONI OGGI -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-bolt me-2" style="color:#f59e0b;"></i>Lezioni di oggi
                </h6>
                <span class="badge" style="background:#fef3c7;color:#92400e;"><?= count($lezioniOggi) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lezioniOggi)): ?>
                    <div class="text-center text-muted py-4" style="font-size:.9rem;">
                        <i class="fas fa-coffee mb-2 d-block" style="font-size:1.5rem;color:#cbd5e1;"></i>
                        Nessuna lezione oggi.
                    </div>
                <?php else: foreach ($lezioniOggi as $l) rigaLezione($l, $ordinali); endif; ?>
            </div>
        </div>
    </div>

    <!-- PROSSIME -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-arrow-right me-2" style="color:#3b82f6;"></i>Prossime lezioni
                </h6>
                <span class="badge" style="background:#dbeafe;color:#1e40af;"><?= count($lezioniProssime) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lezioniProssime)): ?>
                    <div class="text-center text-muted py-4" style="font-size:.85rem;">
                        Nessuna lezione in programma.
                    </div>
                <?php else:
                    $i = 0;
                    foreach (array_slice(array_values($lezioniProssime), 0, 8) as $l):
                        rigaLezione($l, $ordinali);
                    endforeach;
                    if (count($lezioniProssime) > 8): ?>
                    <div class="text-center py-2 small text-muted">+ <?= count($lezioniProssime) - 8 ?> altre</div>
                <?php endif; endif; ?>
            </div>
        </div>
    </div>

    <!-- SVOLTE -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-check-circle me-2" style="color:#10b981;"></i>Lezioni svolte
                </h6>
                <span class="badge" style="background:#d1fae5;color:#065f46;"><?= count($lezioniPassate) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lezioniPassate)): ?>
                    <div class="text-center text-muted py-4" style="font-size:.85rem;">
                        Nessuna lezione svolta ancora.
                    </div>
                <?php else:
                    foreach (array_slice(array_values($lezioniPassate), 0, 8) as $l):
                        rigaLezione($l, $ordinali);
                    endforeach;
                    if (count($lezioniPassate) > 8): ?>
                    <div class="text-center py-2 small text-muted">+ <?= count($lezioniPassate) - 8 ?> precedenti</div>
                <?php endif; endif; ?>
            </div>
        </div>
    </div>

</div>
