<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-map-marker-alt me-2" style="color:#1e40af;"></i>Sedi
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:0.75rem;font-weight:600;">
            <?= count($sedi) ?>
        </span>
    </h4>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form creazione -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Nuova sede
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>sedi/store">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nome sede <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control"
                               placeholder="es. Sede centrale"
                               maxlength="200" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Via</label>
                        <input type="text" name="via" class="form-control"
                               placeholder="es. Via Roma, 1" maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Comune</label>
                        <input type="text" name="comune" class="form-control"
                               placeholder="es. Ancona" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Telefono</label>
                        <input type="text" name="telefono" class="form-control"
                               placeholder="es. 071 123456" maxlength="30">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-plus me-2"></i>Crea sede
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Elenco sedi -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-list me-2" style="color:#1e40af;"></i>Elenco sedi
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;">
                    <?= count($sedi) ?> <?= count($sedi) === 1 ? 'sede' : 'sedi' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($sedi)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-map-marker-alt fa-3x mb-3" style="color:#cbd5e1;"></i>
                        <h6 class="fw-semibold">Nessuna sede presente</h6>
                        <p class="small mb-0">Aggiungi la prima sede dal form.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($sedi as $s): ?>
                            <div class="list-group-item px-4 py-3">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold mb-1" style="color:#0c1a3a;">
                                            <i class="fas fa-map-marker-alt me-2" style="color:#1e40af;"></i>
                                            <?= htmlspecialchars($s['nome']) ?>
                                        </div>
                                        <?php if ($s['via'] || $s['comune']): ?>
                                            <div class="text-muted small mb-1">
                                                <i class="fas fa-location-dot me-1"></i>
                                                <?= htmlspecialchars(implode(', ', array_filter([
                                                    $s['via'],
                                                    $s['comune'],
                                                ]))) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="d-flex flex-wrap gap-3 mt-1">
                                            <?php if ($s['telefono']): ?>
                                                <span class="small text-muted">
                                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($s['telefono']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <form method="POST" action="<?= BASE_URL ?>sedi/delete">
                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Elimina"
                                                    onclick="apriModalEliminaSede(this)"
                                                    data-nome="<?= htmlspecialchars($s['nome'], ENT_QUOTES) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal conferma eliminazione -->
<div class="modal fade" id="modalEliminaSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina sede
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi eliminare la sede <strong id="modal-sede-nome"></strong>?
                <br><span class="text-muted small">L'operazione non può essere annullata.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-sede">
                    <i class="fas fa-trash me-1"></i>Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _formSede = null;

function apriModalEliminaSede(btn) {
    _formSede = btn.closest('form');
    document.getElementById('modal-sede-nome').textContent = btn.dataset.nome ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaSede')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-conferma-elimina-sede').addEventListener('click', function () {
        if (_formSede) {
            bootstrap.Modal.getInstance(document.getElementById('modalEliminaSede')).hide();
            _formSede.submit();
        }
    });
});
</script>
