<?php
$mesiIt = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
$oggi = date('Y-m-d');
?>

<div class="stu-page-title">Le mie lezioni</div>

<?php if (empty($lezioni)): ?>
    <div class="stu-card">
        <div class="empty-stu">
            <i class="fas fa-book-open"></i>
            <small>Nessuna lezione disponibile</small>
        </div>
    </div>
<?php else:
    $futuro  = array_filter($lezioni, fn($l) => $l['data'] >= $oggi);
    $passate = array_filter($lezioni, fn($l) => $l['data'] < $oggi);
?>

<?php if (!empty($futuro)): ?>
<div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin:4px 0 8px;">
    In arrivo
</div>
<?php foreach ($futuro as $l):
    $g = (int)date('j', strtotime($l['data']));
    $m = $mesiIt[(int)date('n', strtotime($l['data']))-1];
    $isOggi = $l['data'] === $oggi;
?>
<div class="stu-card d-flex align-items-center gap-3" style="margin-bottom:8px;<?= $isOggi ? 'border:1.5px solid #1e40af;' : '' ?>">
    <div style="flex-shrink:0;width:46px;text-align:center;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#fff;">
        <div style="background:#1e40af;color:#fff;padding:1px 0;font-size:.58rem;font-weight:700;text-transform:uppercase;"><?= $m ?></div>
        <div style="padding:3px 0;font-size:1.1rem;font-weight:800;color:#0c1a3a;line-height:1;"><?= $g ?></div>
    </div>
    <div class="flex-grow-1" style="min-width:0;">
        <div class="fw-bold" style="color:#0c1a3a;font-size:.9rem;line-height:1.25;">
            <?= htmlspecialchars($l['titolo']) ?>
            <?php if ($isOggi): ?>
            <span class="badge-soft ms-1" style="background:#1e40af;color:#fff;font-size:.62rem;">OGGI</span>
            <?php endif; ?>
            <?php if ($l['online']): ?>
            <span class="badge-soft ms-1" style="background:#e8eef8;color:#1e40af;font-size:.62rem;">Online</span>
            <?php endif; ?>
        </div>
        <div class="text-muted" style="font-size:.76rem;"><?= htmlspecialchars($l['materia_nome']) ?></div>
        <?php if ($l['durata_minuti']): ?>
        <div class="text-muted" style="font-size:.7rem;"><i class="fas fa-clock me-1"></i><?= round($l['durata_minuti']/60, 1) ?>h</div>
        <?php endif; ?>
        <?php if (!empty($l['link_online']) && $l['online']): ?>
        <a href="<?= htmlspecialchars($l['link_online']) ?>" target="_blank" rel="noopener"
           class="btn btn-sm btn-outline-primary mt-2" style="font-size:.76rem;border-radius:8px;">
            <i class="fas fa-video me-1"></i>Entra in lezione
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; endif; ?>

<?php if (!empty($passate)): ?>
<div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin:16px 0 8px;">
    Già svolte
</div>
<?php foreach ($passate as $l):
    $g = (int)date('j', strtotime($l['data']));
    $m = $mesiIt[(int)date('n', strtotime($l['data']))-1];
    $presenza = $l['mia_presenza'];
?>
<div class="stu-card d-flex align-items-center gap-3" style="margin-bottom:8px;opacity:.88;">
    <div style="flex-shrink:0;width:46px;text-align:center;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#fff;">
        <div style="background:#94a3b8;color:#fff;padding:1px 0;font-size:.58rem;font-weight:700;text-transform:uppercase;"><?= $m ?></div>
        <div style="padding:3px 0;font-size:1.1rem;font-weight:800;color:#475569;line-height:1;"><?= $g ?></div>
    </div>
    <div class="flex-grow-1" style="min-width:0;">
        <div class="fw-semibold" style="color:#475569;font-size:.9rem;line-height:1.25;">
            <?= htmlspecialchars($l['titolo']) ?>
        </div>
        <div class="text-muted" style="font-size:.76rem;"><?= htmlspecialchars($l['materia_nome']) ?></div>
    </div>
    <?php if ($presenza === '1'): ?>
        <span class="badge-soft" style="background:#e8eef8;color:#1e40af;font-size:.68rem;">
            <i class="fas fa-check me-1"></i>Presente
        </span>
    <?php elseif ($presenza === '0'): ?>
        <span class="badge-soft" style="background:#f1f5f9;color:#64748b;font-size:.68rem;">
            <i class="fas fa-times me-1"></i>Assente
        </span>
    <?php else: ?>
        <span class="badge-soft" style="background:#f1f5f9;color:#94a3b8;font-size:.68rem;">—</span>
    <?php endif; ?>
</div>
<?php endforeach; endif; ?>

<?php endif; ?>
