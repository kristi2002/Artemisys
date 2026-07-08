<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

$stato = $filtri['stato'] ?? '';
// Costruisce l'URL della segmented mantenendo gli altri filtri
$segUrl = function(string $s) use ($filtri) {
    $q = array_filter([
        'q'     => $filtri['q'] ?? '',
        'anno'  => $filtri['anno_scolastico_id'] ?: '',
        'sede'  => $filtri['sede_id'] ?: '',
        'stato' => $s,
    ], fn($v) => $v !== '' && $v !== 0);
    return BASE_URL . 'percorsi' . ($q ? '?' . http_build_query($q) : '');
};
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-route me-2" style="color:#1e40af;"></i>Percorsi Accademici
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:0.75rem;font-weight:600;">
            <?= count($percorsi) ?>
        </span>
    </h4>
    <a href="<?= BASE_URL ?>percorsi/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Crea percorso
    </a>
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

<!-- ── Card Filtri ── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body px-4 py-3">
        <form method="GET" action="<?= BASE_URL ?>percorsi" class="row g-3 align-items-end">
            <!-- mantiene lo stato attivo/passato durante la ricerca -->
            <?php if ($stato !== ''): ?>
                <input type="hidden" name="stato" value="<?= htmlspecialchars($stato) ?>">
            <?php endif; ?>

            <div class="col-lg-4 col-md-6">
                <label class="form-label small fw-semibold text-muted">Cerca</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0"
                           placeholder="Nome o codice corso..."
                           value="<?= htmlspecialchars($filtri['q'] ?? '') ?>">
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-muted">Anno scolastico</label>
                <select name="anno" class="form-select">
                    <option value="">Tutti gli anni</option>
                    <?php foreach ($anni as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($filtri['anno_scolastico_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['anno']) ?><?= $a['attivo'] ? ' (attivo)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-muted">Sede</label>
                <select name="sede" class="form-select">
                    <option value="">Tutte le sedi</option>
                    <?php foreach ($sedi as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($filtri['sede_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-2 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filtra
                </button>
                <a href="<?= BASE_URL ?>percorsi" class="btn btn-outline-secondary" title="Azzera filtri">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>

        <!-- Segmented stato -->
        <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top flex-wrap">
            <div class="btn-group btn-group-sm" role="group">
                <a href="<?= $segUrl('') ?>" class="btn <?= $stato === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Tutti <span class="badge bg-white text-dark ms-1"><?= $conteggi['totale'] ?></span>
                </a>
                <a href="<?= $segUrl('attivi') ?>" class="btn <?= $stato === 'attivi' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Attivi <span class="badge bg-white text-dark ms-1"><?= $conteggi['attivi'] ?></span>
                </a>
                <a href="<?= $segUrl('passati') ?>" class="btn <?= $stato === 'passati' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Passati <span class="badge bg-white text-dark ms-1"><?= $conteggi['passati'] ?></span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ── Elenco percorsi ── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-list me-2" style="color:#1e40af;"></i>
            <?= $stato === 'attivi' ? 'Percorsi attivi' : ($stato === 'passati' ? 'Percorsi passati' : 'Tutte le edizioni') ?>
        </h6>
        <span class="badge" style="background:#e8eef8;color:#1e40af;">
            <?= count($percorsi) ?> <?= count($percorsi) === 1 ? 'percorso' : 'percorsi' ?>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($percorsi)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-route"></i></div>
                <h5>Nessun percorso trovato</h5>
                <p>
                    <?= ($filtri['q'] ?? '') !== '' || ($filtri['anno_scolastico_id'] ?? 0) || ($filtri['sede_id'] ?? 0) || $stato !== ''
                        ? 'Nessun risultato per i filtri selezionati. <a href="' . BASE_URL . 'percorsi">Azzera i filtri.</a>'
                        : 'Crea il primo percorso con il pulsante <strong>Crea percorso</strong>.' ?>
                </p>
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
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-semibold" style="color:#0c1a3a;">
                                        <?= htmlspecialchars($p['nome']) ?>
                                    </span>
                                    <?php if (!empty($p['codice_corso'])): ?>
                                        <span class="badge" style="background:#eef2ff;color:#1e40af;font-family:'Segoe UI',monospace;font-weight:700;letter-spacing:.5px;">
                                            <?= htmlspecialchars($p['codice_corso']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($p['anno_attivo'])): ?>
                                        <span class="badge bg-success-subtle text-success">Attivo</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($p['descrizione']): ?>
                                    <div class="text-muted small mt-1"><?= htmlspecialchars($p['descrizione']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <span class="badge" style="background:#e8eef8;color:#1e40af;font-weight:600;">
                                    <?= htmlspecialchars($p['anno_label'] ?? '—') ?>
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
                        <div class="col-lg-8">
                            <label class="form-label small fw-semibold">Nome percorso <span class="text-danger">*</span></label>
                            <input type="text" name="nome" id="mod-nome" class="form-control" maxlength="200" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label small fw-semibold">Codice corso</label>
                            <input type="text" name="codice_corso" id="mod-codice" class="form-control" maxlength="50" placeholder="es. OSS-25">
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
    document.getElementById('mod-codice').value        = p.codice_corso ?? '';
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
