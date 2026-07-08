<?php
$mediaTotale = 0; $totVoti = 0;
foreach ($perMateria as $list) foreach ($list as $v) {
    if ($v['voto'] !== null && !$v['assente']) { $mediaTotale += $v['voto']; $totVoti++; }
}
$media = $totVoti > 0 ? round($mediaTotale / $totVoti, 2) : null;
?>

<div class="stu-page-title">I miei voti</div>

<?php if ($media !== null): ?>
<div class="stu-card" style="margin-bottom:12px;">
    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin-bottom:8px;">
        Media generale
    </div>
    <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:56px;height:56px;border-radius:14px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;
                    font-size:1.35rem;font-weight:900;color:#1e40af;flex-shrink:0;">
            <?= $media ?>
        </div>
        <div>
            <div style="font-size:.85rem;font-weight:600;color:#0c1a3a;"><?= $totVoti ?> esami valutati</div>
            <div style="font-size:.72rem;color:#64748b;margin-top:2px;">su tutte le materie</div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($perMateria)): ?>
    <div class="stu-card">
        <div class="empty-stu">
            <i class="fas fa-pen-alt"></i>
            <small>Nessun voto registrato</small>
        </div>
    </div>
<?php else: foreach ($perMateria as $materia => $voti):
    $sum = 0; $cnt = 0;
    foreach ($voti as $v) if ($v['voto'] !== null && !$v['assente']) { $sum += $v['voto']; $cnt++; }
    $mMat = $cnt > 0 ? round($sum / $cnt, 2) : null;
?>
<div class="stu-card" style="margin-bottom:10px;">
    <div class="d-flex align-items-center justify-content-between mb-2 pb-2" style="border-bottom:1px solid #f1f5f9;">
        <h6 class="mb-0 fw-bold" style="color:#0c1a3a;font-size:.9rem;">
            <i class="fas fa-atom me-2" style="color:#1e40af;font-size:.8rem;"></i><?= htmlspecialchars($materia) ?>
        </h6>
        <?php if ($mMat !== null): ?>
        <div class="badge-soft" style="background:#e8eef8;color:#1e40af;font-size:.75rem;padding:.3rem .65rem;">
            Media <?= $mMat ?>
        </div>
        <?php endif; ?>
    </div>

    <?php foreach ($voti as $v):
        if ($v['assente']): ?>
    <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
        <div style="flex-shrink:0;width:44px;height:44px;border-radius:10px;background:#f1f5f9;color:#94a3b8;
                    display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.72rem;text-transform:uppercase;">
            ASS
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= htmlspecialchars($v['titolo']) ?></div>
            <div class="text-muted" style="font-size:.74rem;">
                <?= $v['data'] ? date('d/m/Y', strtotime($v['data'])) : '' ?>
                <span class="badge-soft ms-1" style="background:#e8eef8;color:#1e40af;font-size:.65rem;"><?= ucfirst($v['tipo']) ?></span>
            </div>
        </div>
        <span style="font-size:.7rem;color:#64748b;">Assente</span>
    </div>

    <?php elseif ($v['voto'] !== null): ?>
    <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
        <div style="flex-shrink:0;width:44px;height:44px;border-radius:10px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#1e40af;">
            <?= (float)$v['voto'] ?>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= htmlspecialchars($v['titolo']) ?></div>
            <div class="text-muted" style="font-size:.74rem;">
                <?= $v['data'] ? date('d/m/Y', strtotime($v['data'])) : '' ?>
                <span class="badge-soft ms-1" style="background:#e8eef8;color:#1e40af;font-size:.65rem;"><?= ucfirst($v['tipo']) ?></span>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
        <div style="flex-shrink:0;width:44px;height:44px;border-radius:10px;background:#f8fafc;color:#94a3b8;
                    display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-hourglass-half" style="font-size:.9rem;"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= htmlspecialchars($v['titolo']) ?></div>
            <div class="text-muted" style="font-size:.74rem;">In attesa di valutazione</div>
        </div>
    </div>
    <?php endif; endforeach; ?>
</div>
<?php endforeach; endif; ?>
