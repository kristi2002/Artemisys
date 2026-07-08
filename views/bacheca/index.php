<?php
$tipoColore = [
    'avviso'    => '#3b82f6',
    'urgente'   => '#ef4444',
    'evento'    => '#f59e0b',
    'generale'  => '#64748b',
];
$tipoIcona = [
    'avviso'    => 'fa-info-circle',
    'urgente'   => 'fa-exclamation-triangle',
    'evento'    => 'fa-calendar-day',
    'generale'  => 'fa-bullhorn',
];
$targetLabel = [
    'tutti'    => 'Tutti',
    'studenti' => 'Studenti',
    'docenti'  => 'Docenti',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-bullhorn me-2" style="color:#1e40af;"></i>Bacheca
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:.75rem;">
            <?= count($comunicazioni) ?>
        </span>
    </h4>
    <?php if ($isAdmin): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuova">
        <i class="fas fa-plus me-2"></i>Nuova comunicazione
    </button>
    <?php endif; ?>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($comunicazioni)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="empty-state" style="padding:3rem 0;">
                <div class="empty-state-icon"><i class="fas fa-bullhorn"></i></div>
                <h5>Nessuna comunicazione</h5>
                <p>Non ci sono avvisi pubblicati al momento.</p>
            </div>
        </div>
    </div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($comunicazioni as $c):
        $col = $tipoColore[$c['tipo']] ?? '#64748b';
        $ico = $tipoIcona[$c['tipo']]  ?? 'fa-bullhorn';
    ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-left:4px solid <?= $col ?> !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-3">
                    <div style="flex-shrink:0;width:42px;height:42px;border-radius:10px;background:<?= $col ?>1a;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fas <?= $ico ?>" style="color:<?= $col ?>;font-size:1.1rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-start gap-2 mb-1">
                            <h6 class="mb-0 fw-bold" style="color:#0c1a3a;">
                                <?= htmlspecialchars($c['titolo']) ?>
                            </h6>
                            <span class="badge" style="background:<?= $col ?>1a;color:<?= $col ?>;border:1px solid <?= $col ?>33;font-size:.65rem;">
                                <?= ucfirst($c['tipo']) ?>
                            </span>
                            <span class="badge text-bg-light" style="font-size:.65rem;">
                                <i class="fas fa-users me-1"></i><?= $targetLabel[$c['target']] ?>
                            </span>
                            <?php if ($isAdmin): ?>
                            <form method="POST" action="<?= BASE_URL ?>bacheca/delete" class="ms-auto form-delete-comm">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                        title="Elimina"
                                        onclick="apriModalElimina(this)"
                                        data-titolo="<?= htmlspecialchars($c['titolo'], ENT_QUOTES) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <p class="mb-2" style="color:#475569;font-size:.9rem;line-height:1.5;">
                            <?= nl2br(htmlspecialchars($c['contenuto'])) ?>
                        </p>
                        <div class="text-muted small" style="font-size:.72rem;">
                            <i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?>
                            <?php if ($c['autore_nome']): ?>
                                · <i class="fas fa-user ms-1 me-1"></i><?= htmlspecialchars($c['autore_nome']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<div class="modal fade" id="modalNuova" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>bacheca/store">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-bullhorn me-2 text-primary"></i>Nuova comunicazione
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Titolo <span class="text-danger">*</span></label>
                            <input type="text" name="titolo" class="form-control" required maxlength="255"
                                   placeholder="es. Vacanze di Natale">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tipo</label>
                            <select name="tipo" class="form-select form-select-sm">
                                <option value="generale">Generale</option>
                                <option value="avviso">Avviso</option>
                                <option value="urgente">Urgente</option>
                                <option value="evento">Evento</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Destinatari</label>
                            <select name="target" class="form-select form-select-sm">
                                <option value="tutti">Tutti</option>
                                <option value="studenti">Solo studenti</option>
                                <option value="docenti">Solo docenti</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Contenuto <span class="text-danger">*</span></label>
                            <textarea name="contenuto" class="form-control" rows="5" required
                                      placeholder="Scrivi qui il messaggio..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Pubblica
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal conferma eliminazione comunicazione -->
<div class="modal fade" id="modalEliminaComm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina comunicazione
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi eliminare la comunicazione <strong id="modal-comm-titolo"></strong>?
                <br><span class="text-muted small">L'operazione non può essere annullata.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina">
                    <i class="fas fa-trash me-1"></i>Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _formDaInviare = null;

function apriModalElimina(btn) {
    _formDaInviare = btn.closest('form');
    document.getElementById('modal-comm-titolo').textContent = btn.dataset.titolo ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaComm')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-conferma-elimina').addEventListener('click', function () {
        if (_formDaInviare) {
            bootstrap.Modal.getInstance(document.getElementById('modalEliminaComm')).hide();
            _formDaInviare.submit();
        }
    });
});
</script>
<?php endif; ?>
