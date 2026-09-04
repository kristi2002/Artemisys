<?php
$icoDoc = function (string $nome): string {
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

<div class="stu-page-title">Documenti</div>

<!-- ===== TAB ===== -->
<ul class="nav nav-pills mb-3" id="tabDocumenti" role="tablist" style="gap:8px;">
    <li class="nav-item" role="presentation">
        <button class="nav-link<?= $tabAttiva === 'disponibili' ? ' active' : '' ?>" id="tab-disponibili-btn"
                data-bs-toggle="pill" data-bs-target="#tab-disponibili" type="button" role="tab"
                aria-selected="<?= $tabAttiva === 'disponibili' ? 'true' : 'false' ?>"
                style="border-radius:10px;font-size:.82rem;font-weight:600;padding:8px 14px;">
            <i class="fas fa-download me-1"></i>Disponibili
            <?php if (!empty($disponibili)): ?><span class="ms-1">(<?= count($disponibili) ?>)</span><?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link<?= $tabAttiva === 'miei' ? ' active' : '' ?>" id="tab-miei-btn"
                data-bs-toggle="pill" data-bs-target="#tab-miei" type="button" role="tab"
                aria-selected="<?= $tabAttiva === 'miei' ? 'true' : 'false' ?>"
                style="border-radius:10px;font-size:.82rem;font-weight:600;padding:8px 14px;">
            <i class="fas fa-folder me-1"></i>I miei documenti
            <?php if (!empty($mieiDocumenti)): ?><span class="ms-1">(<?= count($mieiDocumenti) ?>)</span><?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- ===== DOCUMENTI DISPONIBILI ===== -->
    <div class="tab-pane fade<?= $tabAttiva === 'disponibili' ? ' show active' : '' ?>" id="tab-disponibili" role="tabpanel">
        <?php if (empty($disponibili)): ?>
        <div class="stu-card">
            <div class="empty-stu">
                <i class="fas fa-folder-open"></i>
                <small>Nessun documento disponibile al momento</small>
            </div>
        </div>
        <?php else: ?>
        <div class="stu-card">
            <?php foreach ($disponibili as $d): ?>
            <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
                <div style="flex-shrink:0;width:38px;height:38px;border-radius:9px;background:#e8eef8;
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fas <?= $icoDoc($d['original_name']) ?>" style="color:#1e40af;font-size:.92rem;"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-semibold text-truncate" style="color:#0c1a3a;font-size:.86rem;">
                        <?= htmlspecialchars($d['original_name']) ?>
                    </div>
                    <div class="text-muted text-truncate" style="font-size:.73rem;">
                        <?= htmlspecialchars($d['titolo']) ?> · <?= htmlspecialchars($d['contesto']) ?>
                    </div>
                    <div class="pill-row mt-1">
                        <span class="badge-soft" style="background:#f1f5f9;color:#475569;">
                            <?= htmlspecialchars($d['etichetta']) ?>
                        </span>
                        <?php if (!empty($d['created_at'])): ?>
                        <span class="badge-soft" style="background:#f8fafc;color:#94a3b8;">
                            <?= date('d/m/Y', strtotime($d['created_at'])) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1" style="flex-shrink:0;">
                    <a href="<?= htmlspecialchars($d['url']) ?>" target="_blank" rel="noopener"
                       class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.72rem;" title="Scarica">
                        <i class="fas fa-download"></i>
                    </a>
                    <a href="<?= htmlspecialchars($d['vai_a']) ?>" class="btn btn-sm btn-light" style="border-radius:8px;font-size:.72rem;" title="Vai alla scheda">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== I MIEI DOCUMENTI ===== -->
    <div class="tab-pane fade<?= $tabAttiva === 'miei' ? ' show active' : '' ?>" id="tab-miei" role="tabpanel">

        <!-- Caricamento -->
        <div class="stu-card">
            <h6 class="fw-bold mb-3" style="color:#0c1a3a;font-size:.93rem;">
                <i class="fas fa-cloud-arrow-up me-2" style="color:#1e40af;"></i>Carica un documento
            </h6>
            <form method="POST" action="<?= BASE_URL ?>studente/documenti-upload" enctype="multipart/form-data">
                <div class="mb-2">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;color:#475569;">File</label>
                    <input type="file" name="documento" class="form-control" required
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="border-radius:10px;font-size:.84rem;">
                    <div class="form-text" style="font-size:.7rem;">PDF, DOC, DOCX, JPG o PNG · max 10 MB</div>
                </div>

                <div class="mb-2">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;color:#475569;">Etichetta</label>
                    <select name="etichetta" id="selEtichetta" class="form-select" style="border-radius:10px;font-size:.84rem;">
                        <?php foreach ($etichette as $k => $label): ?>
                        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2" id="boxEtichettaAltro" style="display:none;">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;color:#475569;">Specifica l'etichetta</label>
                    <input type="text" name="etichetta_altro" maxlength="100" class="form-control"
                           placeholder="Es. Certificato di residenza" style="border-radius:10px;font-size:.84rem;">
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;color:#475569;">Descrizione <span class="text-muted fw-normal">(facoltativa)</span></label>
                    <input type="text" name="descrizione" maxlength="255" class="form-control"
                           placeholder="Una breve nota" style="border-radius:10px;font-size:.84rem;">
                </div>

                <button type="submit" class="btn btn-primary w-100" style="border-radius:10px;font-weight:600;font-size:.85rem;">
                    <i class="fas fa-upload me-2"></i>Carica documento
                </button>
            </form>
        </div>

        <!-- Elenco -->
        <?php if (empty($mieiDocumenti)): ?>
        <div class="stu-card">
            <div class="empty-stu">
                <i class="fas fa-folder"></i>
                <small>Non hai ancora caricato documenti</small>
            </div>
        </div>
        <?php else: ?>
        <div class="stu-card">
            <h6 class="fw-bold mb-2" style="color:#0c1a3a;font-size:.93rem;">
                <i class="fas fa-folder me-2" style="color:#1e40af;"></i>Caricati da me
            </h6>
            <?php foreach ($mieiDocumenti as $doc): ?>
            <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f1f5f9 !important;">
                <div style="flex-shrink:0;width:38px;height:38px;border-radius:9px;background:#e8eef8;
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fas <?= $icoDoc($doc['original_name']) ?>" style="color:#1e40af;font-size:.92rem;"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-semibold text-truncate" style="color:#0c1a3a;font-size:.86rem;">
                        <?= htmlspecialchars($doc['original_name']) ?>
                    </div>
                    <?php if (!empty($doc['descrizione'])): ?>
                    <div class="text-muted text-truncate" style="font-size:.73rem;">
                        <?= htmlspecialchars($doc['descrizione']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="pill-row mt-1">
                        <span class="badge-soft" style="background:#e8eef8;color:#1e40af;">
                            <i class="fas fa-tag me-1"></i><?= htmlspecialchars(StudenteDocumento::labelOf($doc)) ?>
                        </span>
                        <span class="badge-soft" style="background:#f8fafc;color:#94a3b8;">
                            <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                        </span>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1" style="flex-shrink:0;">
                    <a href="<?= BASE_URL ?>studente/documento/<?= (int)$doc['id'] ?>"
                       class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.72rem;" title="Scarica">
                        <i class="fas fa-download"></i>
                    </a>
                    <?php if ($doc['caricato_da'] === 'studente'): ?>
                    <form method="POST" action="<?= BASE_URL ?>studente/documenti-delete"
                          onsubmit="return confirm('Eliminare questo documento?');">
                        <input type="hidden" name="documento_id" value="<?= (int)$doc['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-light w-100"
                                style="border-radius:8px;font-size:.72rem;color:#dc2626;" title="Elimina">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- /tab-miei -->
</div><!-- /tab-content -->

<script>
(function () {
    var sel = document.getElementById('selEtichetta');
    var box = document.getElementById('boxEtichettaAltro');
    if (!sel || !box) return;
    function toggle() { box.style.display = sel.value === 'altro' ? '' : 'none'; }
    sel.addEventListener('change', toggle);
    toggle();
})();
</script>
