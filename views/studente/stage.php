<?php
$steps = [
    1 => ['label' => 'Candidatura',  'icon' => 'fa-user-check'],
    2 => ['label' => 'Convenzione',  'icon' => 'fa-file-signature'],
    3 => ['label' => 'Tirocinio',    'icon' => 'fa-briefcase'],
    4 => ['label' => 'Valutazione',  'icon' => 'fa-graduation-cap'],
];

$catLabels = [
    'candidatura' => 'Candidatura',
    'convenzione' => 'Convenzione',
    'valutazione' => 'Valutazione',
    'altro'       => 'Altro',
];

$icoDoc = function (string $nome): string {
    return match (strtolower(pathinfo($nome, PATHINFO_EXTENSION))) {
        'pdf'         => 'fa-file-pdf',
        'doc', 'docx' => 'fa-file-word',
        'jpg', 'jpeg', 'png' => 'fa-file-image',
        default       => 'fa-file-lines',
    };
};

$giudizioColori = [
    'insufficiente' => '#dc2626',
    'sufficiente'   => '#f59e0b',
    'buono'         => '#3b82f6',
    'ottimo'        => '#10b981',
];
?>

<div class="stu-page-title">Il mio stage</div>

<?php if (!$stage): ?>

<div class="stu-card">
    <div class="empty-stu">
        <i class="fas fa-briefcase"></i>
        <small>Non hai ancora uno stage attivo</small>
        <div class="text-muted mt-2" style="font-size:.75rem;">
            Quando la segreteria avvierà il tuo percorso di stage lo troverai qui.
        </div>
    </div>
</div>

<?php else:
    $currentStep = (int)$stage['step'];
    $monteOre    = (int)($stage['monte_ore'] ?? 0);
    $oreSvolte   = (int)($stage['ore_svolte'] ?? 0);
    $percOre     = $monteOre > 0 ? min(100, round($oreSvolte / $monteOre * 100)) : 0;
?>

<!-- ===== AZIENDA ===== -->
<div class="stu-card">
    <div class="d-flex align-items-start gap-3">
        <div style="flex-shrink:0;width:46px;height:46px;border-radius:11px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-building" style="color:#1e40af;font-size:1.1rem;"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <h1 style="font-size:1.05rem;font-weight:800;color:#0c1a3a;line-height:1.25;margin:0 0 4px;">
                <?= htmlspecialchars($stage['azienda']) ?>
            </h1>
            <div class="pill-row">
                <?php if (!empty($stage['citta'])): ?>
                <span class="badge-soft" style="background:#f1f5f9;color:#475569;">
                    <i class="fas fa-location-dot me-1"></i><?= htmlspecialchars($stage['citta']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($stage['settore'])): ?>
                <span class="badge-soft" style="background:#f1f5f9;color:#475569;">
                    <?= htmlspecialchars($stage['settore']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== AVANZAMENTO ===== -->
<div class="stu-card">
    <h6 class="fw-bold mb-3" style="color:#0c1a3a;font-size:.93rem;">
        <i class="fas fa-diagram-project me-2" style="color:#1e40af;"></i>Avanzamento
    </h6>
    <div class="d-flex align-items-start" style="gap:0;">
        <?php foreach ($steps as $n => $s):
            $done    = $n <  $currentStep;
            $current = $n === $currentStep;
            $colore  = ($done || $current) ? '#1e40af' : '#e2e8f0';
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;">
            <?php if ($n > 1): ?>
            <div style="position:absolute;top:17px;right:50%;width:100%;height:3px;
                        background:<?= $done || $current ? '#1e40af' : '#e2e8f0' ?>;z-index:0;"></div>
            <?php endif; ?>
            <div style="position:relative;z-index:1;width:34px;height:34px;border-radius:50%;
                        background:<?= ($done || $current) ? '#1e40af' : '#fff' ?>;
                        border:2px solid <?= $colore ?>;
                        color:<?= ($done || $current) ? '#fff' : '#cbd5e1' ?>;
                        display:flex;align-items:center;justify-content:center;font-size:.8rem;
                        <?= $current ? 'box-shadow:0 0 0 4px rgba(30,64,175,.18);' : '' ?>">
                <i class="fas <?= $done ? 'fa-check' : $s['icon'] ?>"></i>
            </div>
            <div style="margin-top:6px;font-size:.66rem;font-weight:700;text-align:center;
                        color:<?= ($done || $current) ? '#1e40af' : '#94a3b8' ?>;">
                <?= $s['label'] ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== ORE ===== -->
<?php if ($monteOre > 0 || $oreSvolte > 0): ?>
<div class="stu-card">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 fw-bold" style="color:#0c1a3a;font-size:.93rem;">
            <i class="fas fa-clock me-2" style="color:#1e40af;"></i>Ore svolte
        </h6>
        <span class="fw-bold" style="color:#1e40af;font-size:.9rem;">
            <?= $oreSvolte ?><?= $monteOre > 0 ? ' / ' . $monteOre : '' ?> h
        </span>
    </div>
    <?php if ($monteOre > 0): ?>
    <div class="progress" style="height:9px;border-radius:6px;background:#f1f5f9;">
        <div class="progress-bar" role="progressbar" style="width:<?= $percOre ?>%;background:#1e40af;border-radius:6px;"
             aria-valuenow="<?= $percOre ?>" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="text-muted mt-1" style="font-size:.72rem;"><?= $percOre ?>% del monte ore</div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ===== DETTAGLI ===== -->
<div class="stu-card">
    <div class="row g-3">
        <?php if (!empty($stage['data_inizio'])): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Inizio</div>
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= date('d/m/Y', strtotime($stage['data_inizio'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($stage['data_fine'])): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Fine</div>
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= date('d/m/Y', strtotime($stage['data_fine'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($stage['tutor_aziendale'])): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Tutor aziendale</div>
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= htmlspecialchars($stage['tutor_aziendale']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($stage['tutor_scolastico'])): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Tutor scolastico</div>
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= htmlspecialchars($stage['tutor_scolastico']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($stage['data_convenzione'])): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Convenzione</div>
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= date('d/m/Y', strtotime($stage['data_convenzione'])) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== VALUTAZIONE ===== -->
<?php if ($stage['voto_finale'] !== null || !empty($stage['giudizio'])): ?>
<div class="stu-card">
    <h6 class="fw-bold mb-3" style="color:#0c1a3a;font-size:.93rem;">
        <i class="fas fa-graduation-cap me-2" style="color:#1e40af;"></i>Valutazione finale
    </h6>
    <div class="d-flex align-items-center gap-3">
        <?php if ($stage['voto_finale'] !== null): ?>
        <div style="width:56px;height:56px;border-radius:12px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;
                    font-weight:800;font-size:1.2rem;color:#1e40af;">
            <?= (float)$stage['voto_finale'] ?>
        </div>
        <?php endif; ?>
        <div class="flex-grow-1">
            <?php if (!empty($stage['giudizio'])): ?>
            <span class="badge-soft" style="background:<?= $giudizioColori[$stage['giudizio']] ?? '#64748b' ?>;color:#fff;font-size:.75rem;">
                <?= ucfirst($stage['giudizio']) ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($stage['note_valutazione'])): ?>
            <div style="color:#475569;font-size:.83rem;line-height:1.5;margin-top:6px;">
                <?= nl2br(htmlspecialchars($stage['note_valutazione'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== DOCUMENTI ===== -->
<div class="stu-card">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 fw-bold" style="color:#0c1a3a;font-size:.93rem;">
            <i class="fas fa-paperclip me-2" style="color:#1e40af;"></i>Documenti dello stage
        </h6>
        <?php if (!empty($allegati)): ?>
        <span class="badge-soft" style="background:#e8eef8;color:#1e40af;"><?= count($allegati) ?></span>
        <?php endif; ?>
    </div>

    <?php if (empty($allegati)): ?>
        <div class="empty-stu">
            <i class="fas fa-folder-open"></i>
            <small>Nessun documento caricato</small>
        </div>
    <?php else: foreach ($allegati as $a): ?>
    <a href="<?= ASSETS_URL ?>public/uploads/stage/<?= rawurlencode($a['filename']) ?>"
       target="_blank" rel="noopener"
       class="stu-card-link d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
        <div style="flex-shrink:0;width:36px;height:36px;border-radius:9px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;">
            <i class="fas <?= $icoDoc($a['original_name']) ?>" style="color:#1e40af;font-size:.9rem;"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold text-truncate" style="color:#0c1a3a;font-size:.85rem;">
                <?= htmlspecialchars($a['original_name']) ?>
            </div>
            <div class="pill-row mt-1">
                <span class="badge-soft" style="background:#f1f5f9;color:#475569;">
                    <?= htmlspecialchars($catLabels[$a['categoria']] ?? ucfirst($a['categoria'])) ?>
                </span>
                <span class="badge-soft" style="background:#f8fafc;color:#94a3b8;">
                    <?= date('d/m/Y', strtotime($a['created_at'])) ?>
                </span>
            </div>
        </div>
        <i class="fas fa-download text-muted" style="font-size:.8rem;"></i>
    </a>
    <?php endforeach; endif; ?>
</div>

<?php endif; ?>
