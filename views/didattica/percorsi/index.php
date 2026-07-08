<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-route me-2" style="color:#1e40af;"></i>Percorsi Anni Accademici
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:0.75rem;font-weight:600;">
            <?= count($percorsi) ?>
        </span>
    </h4>
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

<!-- Form creazione -->
<div class="col-lg-5 mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="fw-semibold mb-3" style="color:#0c1a3a;">
                <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Nuovo percorso
            </h6>
            <form method="POST" action="<?= BASE_URL ?>percorsi/store">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nome percorso <span class="text-danger">*</span></label>
                    <input type="text"
                           name="nome"
                           class="form-control"
                           placeholder="es. Operatore Socio Sanitario"
                           maxlength="200"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Anno scolastico <span class="text-danger">*</span></label>
                    <?php if (empty($anni)): ?>
                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Nessun anno scolastico disponibile.
                            <a href="<?= BASE_URL ?>anno-scolastico">Aggiungine uno.</a>
                        </div>
                    <?php else: ?>
                        <select name="anno_scolastico_id" class="form-select" required>
                            <option value="">— Seleziona —</option>
                            <?php foreach ($anni as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= $a['attivo'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['anno']) ?>
                                    <?= $a['attivo'] ? '(attivo)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Sede</label>
                    <?php if (empty($sedi)): ?>
                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Nessuna sede disponibile.
                            <a href="<?= BASE_URL ?>sedi">Aggiungine una.</a>
                        </div>
                    <?php else: ?>
                        <select name="sede_id" class="form-select">
                            <option value="">— Seleziona sede —</option>
                            <?php foreach ($sedi as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['nome']) ?>
                                    <?= $s['comune'] ? '— ' . htmlspecialchars($s['comune']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Inizio anno accademico</label>
                        <input type="date" name="data_inizio_anno" class="form-control"
                               value="<?= htmlspecialchars($_POST['data_inizio_anno'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Fine anno accademico</label>
                        <input type="date" name="data_fine_anno" class="form-control"
                               value="<?= htmlspecialchars($_POST['data_fine_anno'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Descrizione</label>
                    <textarea name="descrizione" class="form-control" rows="2"
                              placeholder="Note opzionali..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100" <?= empty($anni) ? 'disabled' : '' ?>>
                    <i class="fas fa-plus me-2"></i>Crea percorso
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Elenco percorsi -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-list me-2" style="color:#1e40af;"></i>Tutte le edizioni
        </h6>
        <span class="badge" style="background:#e8eef8;color:#1e40af;">
            <?= count($percorsi) ?> <?= count($percorsi) === 1 ? 'percorso' : 'percorsi' ?>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($percorsi)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-route"></i></div>
                <h5>Nessun percorso creato</h5>
                <p>Crea il primo percorso dal form qui sopra.</p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4">Percorso</th>
                        <th style="width:130px;">Anno Sc.</th>
                        <th>Sede</th>
                        <th>Anni di corso</th>
                        <th class="text-end pe-4" style="width:100px;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($percorsi as $p): ?>
                        <tr style="cursor:pointer;" onclick="window.location='<?= BASE_URL ?>percorsi/detail/<?= $p['id'] ?>'">
                            <td class="ps-4 align-middle">
                                <div class="fw-semibold" style="color:#0c1a3a;">
                                    <?= htmlspecialchars($p['nome']) ?>
                                </div>
                                <?php if ($p['descrizione']): ?>
                                    <div class="text-muted small"><?= htmlspecialchars($p['descrizione']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <span class="badge" style="background:#e8eef8;color:#1e40af;font-weight:600;">
                                    <?= htmlspecialchars($p['anno_label']) ?>
                                </span>
                            </td>
                            <td class="align-middle" style="font-size:.85rem;">
                                <?php if (!empty($p['sede_nome'])): ?>
                                    <div class="fw-semibold" style="color:#0c1a3a;">
                                        <i class="fas fa-map-marker-alt me-1" style="color:#1e40af;font-size:.75rem;"></i>
                                        <?= htmlspecialchars($p['sede_nome']) ?>
                                    </div>
                                    <?php if (!empty($p['sede_comune'])): ?>
                                        <div class="text-muted" style="font-size:.78rem;"><?= htmlspecialchars($p['sede_comune']) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <?php if (empty($p['anni'])): ?>
                                    <span class="text-muted small fst-italic">Nessun anno</span>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($p['anni'] as $anno): ?>
                                            <span class="badge" style="background:#f0f4ff;color:#1e40af;font-weight:600;font-size:0.72rem;">
                                                <?= $ordinali[$anno['numero']] ?? $anno['numero'] . '° Anno' ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 align-middle" onclick="event.stopPropagation()">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Modifica"
                                            onclick="apriModalModificaPercorso(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form method="POST" action="<?= BASE_URL ?>percorsi/delete">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Elimina"
                                                onclick="apriModalEliminaPercorso(this)"
                                                data-nome="<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal modifica percorso -->
<div class="modal fade" id="modalModificaPercorso" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-pen me-2" style="color:#1e40af;"></i>Modifica percorso
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>percorsi/update">
                <input type="hidden" name="id" id="mod-id">
                <div class="modal-body px-4 py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nome percorso <span class="text-danger">*</span></label>
                            <input type="text" name="nome" id="mod-nome" class="form-control" maxlength="200" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Anno scolastico <span class="text-danger">*</span></label>
                            <select name="anno_scolastico_id" id="mod-anno" class="form-select" required>
                                <option value="">— Seleziona —</option>
                                <?php foreach ($anni as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['anno']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Sede</label>
                            <select name="sede_id" id="mod-sede" class="form-select">
                                <option value="">— Nessuna sede —</option>
                                <?php foreach ($sedi as $s): ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= htmlspecialchars($s['nome']) ?>
                                        <?= $s['comune'] ? '— ' . htmlspecialchars($s['comune']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Inizio anno accademico</label>
                            <input type="date" name="data_inizio_anno" id="mod-data-inizio" class="form-control">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Fine anno accademico</label>
                            <input type="date" name="data_fine_anno" id="mod-data-fine" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Descrizione</label>
                            <textarea name="descrizione" id="mod-descrizione" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annulla
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i>Salva modifiche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal conferma eliminazione percorso -->
<div class="modal fade" id="modalEliminaPercorso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina percorso
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi eliminare il percorso <strong id="modal-percorso-nome"></strong>?
                <br><span class="text-muted small">Verranno eliminati anche tutti gli anni di corso associati. L'operazione non può essere annullata.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-percorso">
                    <i class="fas fa-trash me-1"></i>Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function apriModalModificaPercorso(p) {
    document.getElementById('mod-id').value            = p.id;
    document.getElementById('mod-nome').value          = p.nome ?? '';
    document.getElementById('mod-anno').value          = p.anno_scolastico_id ?? '';
    document.getElementById('mod-sede').value          = p.sede_id ?? '';
    document.getElementById('mod-data-inizio').value   = p.data_inizio_anno ?? '';
    document.getElementById('mod-data-fine').value     = p.data_fine_anno ?? '';
    document.getElementById('mod-descrizione').value   = p.descrizione ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalModificaPercorso')).show();
}

let _formPercorso = null;

function apriModalEliminaPercorso(btn) {
    _formPercorso = btn.closest('form');
    document.getElementById('modal-percorso-nome').textContent = btn.dataset.nome ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaPercorso')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-conferma-elimina-percorso').addEventListener('click', function () {
        if (_formPercorso) {
            bootstrap.Modal.getInstance(document.getElementById('modalEliminaPercorso')).hide();
            _formPercorso.submit();
        }
    });
});
</script>
