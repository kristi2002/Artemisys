<?php
$currentYear = (int)date('Y');
$suggerito   = $currentYear . '/' . ($currentYear + 1);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-calendar-alt me-2" style="color:#1e40af;"></i>Anno Scolastico
    </h4>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Form aggiunta -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-semibold mb-3" style="color:#0c1a3a;">Aggiungi anno scolastico</h6>
        <form method="POST" action="<?= BASE_URL ?>anno-scolastico/store" class="d-flex gap-2 align-items-end flex-wrap">
            <div>
                <label class="form-label small mb-1">Anno scolastico</label>
                <input type="text"
                       name="anno"
                       class="form-control"
                       placeholder="es. <?= $suggerito ?>"
                       value="<?= $suggerito ?>"
                       pattern="\d{4}/\d{4}"
                       required
                       style="width:160px;">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Aggiungi
            </button>
        </form>
    </div>
</div>

<!-- Lista anni -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($anni)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h5>Nessun anno scolastico</h5>
                <p>Aggiungi il primo anno scolastico usando il form qui sopra.</p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4">Anno Scolastico</th>
                        <th>Stato</th>
                        <th>Aggiunto il</th>
                        <th class="text-end pe-4">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anni as $a): ?>
                        <tr>
                            <td class="ps-4 fw-semibold align-middle">
                                <i class="fas fa-calendar-alt me-2" style="color:#1e40af;"></i>
                                <?= htmlspecialchars($a['anno']) ?>
                            </td>
                            <td class="align-middle">
                                <?php if ($a['attivo']): ?>
                                    <span class="badge" style="background:#dcfce7;color:#166534;">
                                        <i class="fas fa-check-circle me-1"></i>Attivo
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f1f5f9;color:#64748b;">
                                        Non attivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-muted small">
                                <?= date('d/m/Y', strtotime($a['created_at'])) ?>
                            </td>
                            <td class="text-end pe-4 align-middle">
                                <?php if ($a['attivo']): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-warning btn-disattiva-anno"
                                            title="Disattiva"
                                            data-id="<?= $a['id'] ?>"
                                            data-anno="<?= htmlspecialchars($a['anno'], ENT_QUOTES) ?>">
                                        <i class="fas fa-toggle-off"></i>
                                    </button>
                                <?php else: ?>
                                    <form method="POST" action="<?= BASE_URL ?>anno-scolastico/attivo" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success me-1" title="Imposta come attivo">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-elimina-anno"
                                            title="Elimina"
                                            data-id="<?= $a['id'] ?>"
                                            data-anno="<?= htmlspecialchars($a['anno'], ENT_QUOTES) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- ── Modal Disattiva Anno Scolastico ── -->
<div class="modal fade" id="modalDisattivaAnno" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-toggle-off me-2 text-warning"></i>Disattiva anno scolastico
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-1" style="color:#374151;">
                    Vuoi disattivare l'anno scolastico <strong id="annoLabelDisattiva"></strong>?
                </p>
                <p class="text-muted small mb-0">Nessun anno risulterà attivo finché non ne imposti un altro.</p>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" action="<?= BASE_URL ?>anno-scolastico/disattiva" class="d-inline">
                    <input type="hidden" name="id" id="annoIdDaDisattivare">
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-toggle-off me-1"></i>Disattiva
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Elimina Anno Scolastico ── -->
<div class="modal fade" id="modalEliminaAnno" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina anno scolastico
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-1" style="color:#374151;">
                    Stai per eliminare l'anno scolastico <strong id="annoLabel"></strong>.
                </p>
                <p class="text-muted small mb-0">L'operazione è irreversibile.</p>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" action="<?= BASE_URL ?>anno-scolastico/delete" class="d-inline">
                    <input type="hidden" name="id" id="annoIdDaEliminare">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function (e) {
    const btnDel = e.target.closest('.btn-elimina-anno');
    if (btnDel) {
        document.getElementById('annoLabel').textContent   = btnDel.dataset.anno;
        document.getElementById('annoIdDaEliminare').value = btnDel.dataset.id;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaAnno')).show();
        return;
    }

    const btnDis = e.target.closest('.btn-disattiva-anno');
    if (btnDis) {
        document.getElementById('annoLabelDisattiva').textContent = btnDis.dataset.anno;
        document.getElementById('annoIdDaDisattivare').value      = btnDis.dataset.id;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDisattivaAnno')).show();
    }
});
</script>
