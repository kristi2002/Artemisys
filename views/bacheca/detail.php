<?php
$tipoColore = [
    'avviso'    => '#3b82f6', 'urgente' => '#ef4444',
    'evento'    => '#f59e0b', 'generale'=> '#64748b',
];
$col = $tipoColore[$com['tipo']] ?? '#64748b';
?>

<div class="mb-3">
    <a href="<?= BASE_URL ?>bacheca" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Bacheca
    </a>
</div>

<div class="card border-0 shadow-sm" style="border-top:4px solid <?= $col ?> !important;">
    <div class="card-body p-4">
        <span class="badge" style="background:<?= $col ?>1a;color:<?= $col ?>;border:1px solid <?= $col ?>33;">
            <?= ucfirst($com['tipo']) ?>
        </span>
        <h2 class="fw-bold mt-3 mb-2" style="color:#0c1a3a;"><?= htmlspecialchars($com['titolo']) ?></h2>
        <div class="text-muted small mb-4">
            <i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($com['created_at'])) ?>
            <?php if ($com['autore_nome']): ?>
                · <i class="fas fa-user me-1"></i><?= htmlspecialchars($com['autore_nome']) ?>
            <?php endif; ?>
        </div>
        <div style="font-size:1rem;line-height:1.7;color:#374151;">
            <?= nl2br(htmlspecialchars($com['contenuto'])) ?>
        </div>
    </div>
</div>
