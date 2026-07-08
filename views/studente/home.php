<?php
$mesiIt = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
$percPresenze = $stats['lezioni_tot'] > 0 ? round($stats['presenze'] / $stats['lezioni_tot'] * 100) : 0;
?>

<div class="stu-page-title">Ciao, <?= htmlspecialchars($studente['nome']) ?></div>

<!-- Stat tiles -->
<div class="row g-2 mb-3">
    <div class="col-4">
        <div class="stu-stat">
            <div class="ico" style="color:#1e40af;"><i class="fas fa-clipboard-check"></i></div>
            <div class="num"><?= $percPresenze ?>%</div>
            <div class="lbl">Presenze</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stu-stat">
            <div class="ico" style="color:#1e40af;"><i class="fas fa-chart-bar"></i></div>
            <div class="num"><?= $stats['media'] > 0 ? $stats['media'] : '—' ?></div>
            <div class="lbl">Media voti</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stu-stat">
            <div class="ico" style="color:#1e40af;"><i class="fas fa-pen-alt"></i></div>
            <div class="num"><?= $stats['voti_count'] ?></div>
            <div class="lbl">Esami</div>
        </div>
    </div>
</div>

<!-- Prossime lezioni -->
<div class="stu-card">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 fw-bold" style="color:#0c1a3a;font-size:.93rem;">
            <i class="fas fa-book-open me-2" style="color:#1e40af;"></i>Prossime lezioni
        </h6>
        <a href="<?= BASE_URL ?>studente/lezioni" class="text-decoration-none small fw-semibold" style="color:#1e40af;">Tutte</a>
    </div>
    <?php if (empty($prossLezioni)): ?>
        <div class="empty-stu">
            <i class="fas fa-coffee"></i>
            <small>Nessuna lezione in programma</small>
        </div>
    <?php else: foreach ($prossLezioni as $l):
        $g = (int)date('j', strtotime($l['data']));
        $m = $mesiIt[(int)date('n', strtotime($l['data']))-1];
    ?>
    <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
        <div style="flex-shrink:0;width:42px;text-align:center;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#fff;">
            <div style="background:#1e40af;color:#fff;padding:1px 0;font-size:.55rem;font-weight:700;text-transform:uppercase;"><?= $m ?></div>
            <div style="padding:3px 0;font-size:1rem;font-weight:800;color:#0c1a3a;line-height:1;"><?= $g ?></div>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;line-height:1.25;">
                <?= htmlspecialchars($l['titolo']) ?>
                <?php if ($l['online']): ?>
                <span class="badge-soft ms-1" style="background:#e8eef8;color:#1e40af;font-size:.65rem;">Online</span>
                <?php endif; ?>
            </div>
            <div class="text-muted" style="font-size:.74rem;"><?= htmlspecialchars($l['materia_nome']) ?></div>
        </div>
        <?php if (!empty($l['link_online']) && $l['online']): ?>
        <a href="<?= htmlspecialchars($l['link_online']) ?>" target="_blank" rel="noopener"
           class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.72rem;flex-shrink:0;">
            <i class="fas fa-video"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
</div>

<!-- Voti recenti -->
<div class="stu-card">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 fw-bold" style="color:#0c1a3a;font-size:.93rem;">
            <i class="fas fa-chart-bar me-2" style="color:#1e40af;"></i>Voti recenti
        </h6>
        <a href="<?= BASE_URL ?>studente/voti" class="text-decoration-none small fw-semibold" style="color:#1e40af;">Tutti</a>
    </div>
    <?php if (empty($voti)): ?>
        <div class="empty-stu">
            <i class="fas fa-pen-alt"></i>
            <small>Ancora nessun voto</small>
        </div>
    <?php else: foreach ($voti as $v): ?>
    <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
        <div style="flex-shrink:0;width:44px;height:44px;border-radius:10px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#1e40af;">
            <?= (float)$v['voto'] ?>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;line-height:1.25;">
                <?= htmlspecialchars($v['titolo']) ?>
            </div>
            <div class="text-muted" style="font-size:.74rem;">
                <?= htmlspecialchars($v['materia_nome']) ?>
                <?php if ($v['data']): ?> · <?= date('d/m/Y', strtotime($v['data'])) ?><?php endif; ?>
            </div>
        </div>
        <span class="badge-soft" style="background:#e8eef8;color:#1e40af;font-size:.68rem;">
            <?= ucfirst($v['tipo']) ?>
        </span>
    </div>
    <?php endforeach; endif; ?>
</div>

<!-- Bacheca -->
<?php if (!empty($bacheca)): ?>
<div class="stu-card">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 fw-bold" style="color:#0c1a3a;font-size:.93rem;">
            <i class="fas fa-bullhorn me-2" style="color:#1e40af;"></i>Bacheca
        </h6>
        <a href="<?= BASE_URL ?>bacheca" class="text-decoration-none small fw-semibold" style="color:#1e40af;">Tutte</a>
    </div>
    <?php foreach ($bacheca as $c): ?>
    <a href="<?= BASE_URL ?>bacheca/detail/<?= $c['id'] ?>" class="stu-card-link d-flex align-items-start gap-2 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
        <div style="flex-shrink:0;width:32px;height:32px;border-radius:8px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-bullhorn" style="color:#1e40af;font-size:.8rem;"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.85rem;line-height:1.25;">
                <?= htmlspecialchars($c['titolo']) ?>
            </div>
            <div class="text-muted" style="font-size:.72rem;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;">
                <?= htmlspecialchars($c['contenuto']) ?>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Quick links -->
<div class="row g-2 mb-3">
    <div class="col-6">
        <a href="<?= BASE_URL ?>studente/presenze" class="stu-card stu-card-link text-center mb-0" style="display:block;">
            <i class="fas fa-clipboard-check d-block" style="color:#1e40af;font-size:1.4rem;margin-bottom:6px;"></i>
            <div class="fw-bold" style="color:#0c1a3a;font-size:.85rem;">Le mie presenze</div>
        </a>
    </div>
    <div class="col-6">
        <a href="<?= BASE_URL ?>studente/rette" class="stu-card stu-card-link text-center mb-0" style="display:block;">
            <i class="fas fa-euro-sign d-block" style="color:#1e40af;font-size:1.4rem;margin-bottom:6px;"></i>
            <div class="fw-bold" style="color:#0c1a3a;font-size:.85rem;">Le mie rette</div>
        </a>
    </div>
</div>

<a href="<?= BASE_URL ?>studente/profilo" class="stu-card stu-card-link d-flex align-items-center gap-3" style="display:flex;">
    <div style="width:40px;height:40px;border-radius:10px;background:#e8eef8;display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-user-circle" style="color:#1e40af;"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-bold" style="color:#0c1a3a;font-size:.9rem;">Il mio profilo</div>
        <div class="text-muted" style="font-size:.74rem;">Dati anagrafici e percorso</div>
    </div>
    <i class="fas fa-chevron-right text-muted"></i>
</a>
