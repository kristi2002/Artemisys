<?php
$tipoColore = [
    'avviso'=>'#3b82f6','urgente'=>'#ef4444','evento'=>'#f59e0b','generale'=>'#64748b',
];
$col = $tipoColore[$com['tipo']] ?? '#64748b';
?>

<div class="mt-2 mb-2">
    <a href="<?= BASE_URL ?>bacheca" class="text-decoration-none" style="color:#1e40af;font-size:.85rem;font-weight:600;">
        <i class="fas fa-arrow-left me-1"></i>Bacheca
    </a>
</div>

<div class="stu-card" style="border-top:4px solid <?= $col ?>;">
    <span class="badge-soft" style="background:<?= $col ?>1a;color:<?= $col ?>;">
        <?= ucfirst($com['tipo']) ?>
    </span>
    <h4 class="fw-bold mt-2 mb-2" style="color:#0c1a3a;line-height:1.25;">
        <?= htmlspecialchars($com['titolo']) ?>
    </h4>
    <div class="text-muted small mb-3" style="font-size:.78rem;">
        <i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($com['created_at'])) ?>
        <?php if ($com['autore_nome']): ?>
            · <i class="fas fa-user ms-1 me-1"></i><?= htmlspecialchars($com['autore_nome']) ?>
        <?php endif; ?>
    </div>
    <div style="font-size:.95rem;line-height:1.65;color:#374151;">
        <?= nl2br(htmlspecialchars($com['contenuto'])) ?>
    </div>
</div>
