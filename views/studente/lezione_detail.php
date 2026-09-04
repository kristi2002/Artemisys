<?php
$mesiIt = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
$oggi   = date('Y-m-d');

$dataLez = $lezione['data'] ?? null;
$g       = $dataLez ? (int)date('j', strtotime($dataLez)) : null;
$m       = $dataLez ? $mesiIt[(int)date('n', strtotime($dataLez)) - 1] : null;
$isOggi  = $dataLez === $oggi;
$svolta  = $dataLez && $dataLez < $oggi;

$icoAllegato = function (string $nome): string {
    return match (strtolower(pathinfo($nome, PATHINFO_EXTENSION))) {
        'pdf'                => 'fa-file-pdf',
        'doc', 'docx'        => 'fa-file-word',
        'xls', 'xlsx', 'csv' => 'fa-file-excel',
        'ppt', 'pptx'        => 'fa-file-powerpoint',
        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'fa-file-image',
        'zip', 'rar', '7z'   => 'fa-file-zipper',
        default              => 'fa-file-lines',
    };
};
?>

<a href="<?= BASE_URL ?>studente/lezioni" class="text-decoration-none d-inline-flex align-items-center gap-2 mb-2"
   style="color:#64748b;font-size:.8rem;font-weight:600;padding:14px 2px 0;">
    <i class="fas fa-arrow-left"></i> Le mie lezioni
</a>

<!-- ===== TESTATA ===== -->
<div class="stu-card">
    <div class="d-flex align-items-start gap-3">
        <?php if ($dataLez): ?>
        <div style="flex-shrink:0;width:52px;text-align:center;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#fff;">
            <div style="background:<?= $svolta ? '#94a3b8' : '#1e40af' ?>;color:#fff;padding:2px 0;font-size:.6rem;font-weight:700;text-transform:uppercase;"><?= $m ?></div>
            <div style="padding:4px 0;font-size:1.25rem;font-weight:800;color:#0c1a3a;line-height:1;"><?= $g ?></div>
        </div>
        <?php endif; ?>
        <div class="flex-grow-1" style="min-width:0;">
            <h1 style="font-size:1.1rem;font-weight:800;color:#0c1a3a;line-height:1.25;margin:0 0 4px;">
                <?= htmlspecialchars($lezione['titolo']) ?>
            </h1>
            <div class="pill-row mb-2">
                <?php if ($isOggi): ?>
                    <span class="badge-soft" style="background:#1e40af;color:#fff;">OGGI</span>
                <?php endif; ?>
                <?php if (!empty($lezione['online'])): ?>
                    <span class="badge-soft" style="background:#e8eef8;color:#1e40af;"><i class="fas fa-video me-1"></i>Online</span>
                <?php endif; ?>
                <span class="badge-soft" style="background:#f1f5f9;color:#475569;">
                    <?= htmlspecialchars($lezione['materia_nome']) ?>
                </span>
            </div>
            <div class="text-muted" style="font-size:.76rem;">
                <?= htmlspecialchars($lezione['percorso_nome']) ?>
                <?php if (!empty($lezione['anno_numero'])): ?> · <?= (int)$lezione['anno_numero'] ?>° anno<?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($lezione['online']) && !empty($lezione['link_online']) && !$svolta): ?>
    <a href="<?= htmlspecialchars($lezione['link_online']) ?>" target="_blank" rel="noopener"
       class="btn btn-primary w-100 mt-3" style="border-radius:10px;font-weight:600;font-size:.85rem;">
        <i class="fas fa-video me-2"></i>Entra in lezione
    </a>
    <?php endif; ?>
</div>

<!-- ===== INFO ===== -->
<div class="stu-card">
    <div class="row g-3">
        <?php if ($dataLez): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Data</div>
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= date('d/m/Y', strtotime($dataLez)) ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($lezione['ora_inizio'])): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Orario</div>
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;">
                <?= substr($lezione['ora_inizio'], 0, 5) ?><?php if (!empty($lezione['ora_fine'])): ?> – <?= substr($lezione['ora_fine'], 0, 5) ?><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($lezione['durata_minuti'])): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Durata</div>
            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;"><?= round($lezione['durata_minuti'] / 60, 1) ?>h</div>
        </div>
        <?php endif; ?>

        <?php if ($svolta): ?>
        <div class="col-6">
            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Presenza</div>
            <div style="margin-top:2px;">
                <?php if ($lezione['mia_presenza'] === '1' || $lezione['mia_presenza'] === 1): ?>
                    <span class="badge-soft" style="background:#e8eef8;color:#1e40af;"><i class="fas fa-check me-1"></i>Presente</span>
                <?php elseif ($lezione['mia_presenza'] === '0' || $lezione['mia_presenza'] === 0): ?>
                    <span class="badge-soft" style="background:#f1f5f9;color:#64748b;"><i class="fas fa-times me-1"></i>Assente</span>
                <?php else: ?>
                    <span class="badge-soft" style="background:#f1f5f9;color:#94a3b8;">Non registrata</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($docenti)): ?>
    <div class="mt-3 pt-3" style="border-top:1px solid #f1f5f9;">
        <div class="text-muted mb-2" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">
            <?= count($docenti) > 1 ? 'Docenti' : 'Docente' ?>
        </div>
        <div class="pill-row">
            <?php foreach ($docenti as $d): ?>
            <span class="badge-soft" style="background:#f1f5f9;color:#475569;">
                <i class="fas fa-chalkboard-user me-1"></i><?= htmlspecialchars($d['nome'] . ' ' . $d['cognome']) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($lezione['nota_presenza'])): ?>
    <div class="mt-3 pt-3" style="border-top:1px solid #f1f5f9;">
        <div class="text-muted mb-1" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Nota del docente</div>
        <div style="color:#475569;font-size:.85rem;"><?= htmlspecialchars($lezione['nota_presenza']) ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- ===== ARGOMENTO ===== -->
<?php if (!empty($lezione['argomento'])): ?>
<div class="stu-card">
    <h6 class="fw-bold mb-2" style="color:#0c1a3a;font-size:.93rem;">
        <i class="fas fa-list-check me-2" style="color:#1e40af;"></i>Argomento
    </h6>
    <div style="color:#475569;font-size:.86rem;line-height:1.55;"><?= nl2br(htmlspecialchars($lezione['argomento'])) ?></div>
</div>
<?php endif; ?>

<!-- ===== NOTE ===== -->
<?php if (!empty($lezione['note'])): ?>
<div class="stu-card">
    <h6 class="fw-bold mb-2" style="color:#0c1a3a;font-size:.93rem;">
        <i class="fas fa-note-sticky me-2" style="color:#1e40af;"></i>Note
    </h6>
    <div style="color:#475569;font-size:.86rem;line-height:1.55;"><?= nl2br(htmlspecialchars($lezione['note'])) ?></div>
</div>
<?php endif; ?>

<!-- ===== MATERIALI ===== -->
<div class="stu-card">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 fw-bold" style="color:#0c1a3a;font-size:.93rem;">
            <i class="fas fa-paperclip me-2" style="color:#1e40af;"></i>Materiale didattico
        </h6>
        <?php if (!empty($allegati)): ?>
        <span class="badge-soft" style="background:#e8eef8;color:#1e40af;"><?= count($allegati) ?></span>
        <?php endif; ?>
    </div>

    <?php if (empty($allegati)): ?>
        <div class="empty-stu">
            <i class="fas fa-folder-open"></i>
            <small>Nessun materiale per questa lezione</small>
        </div>
    <?php else: foreach ($allegati as $a): ?>
    <a href="<?= ASSETS_URL ?>public/uploads/lezioni/<?= rawurlencode($a['filename']) ?>"
       target="_blank" rel="noopener"
       class="stu-card-link d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
        <div style="flex-shrink:0;width:36px;height:36px;border-radius:9px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;">
            <i class="fas <?= $icoAllegato($a['original_name']) ?>" style="color:#1e40af;font-size:.9rem;"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="fw-semibold text-truncate" style="color:#0c1a3a;font-size:.85rem;">
                <?= htmlspecialchars($a['original_name']) ?>
            </div>
            <div class="text-muted" style="font-size:.72rem;"><?= date('d/m/Y', strtotime($a['created_at'])) ?></div>
        </div>
        <i class="fas fa-download text-muted" style="font-size:.8rem;"></i>
    </a>
    <?php endforeach; endif; ?>
</div>
