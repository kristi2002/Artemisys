<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-euro-sign me-2" style="color:#1e40af;"></i>Rette e Pagamenti
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:.75rem;">
            <?= count($rette) ?>
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

<div class="row g-4">

    <!-- ── Form crea retta ── -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Nuova retta
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>rette/store">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Studente <span class="text-danger">*</span></label>
                        <select name="studente_id" class="form-select form-select-sm" required>
                            <option value="">— Seleziona studente —</option>
                            <?php foreach ($studenti as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Importo totale (€) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">€</span>
                            <input type="number" name="importo_totale" class="form-control"
                                   min="0" step="0.01" placeholder="es. 3500.00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Numero rate <span class="text-danger">*</span></label>
                        <input type="number" name="num_rate" class="form-control form-control-sm"
                               min="1" max="60" value="1" required id="inpNumRate"
                               oninput="aggiornaPreview()">
                        <div class="text-muted small mt-1">Quanti pagamenti vuole fare lo studente</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Cadenza</label>
                        <select name="cadenza" class="form-select form-select-sm">
                            <option value="mensile">Mensile</option>
                            <option value="bimestrale">Bimestrale</option>
                            <option value="trimestrale">Trimestrale</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Data prima scadenza</label>
                        <input type="date" name="data_prima_scadenza" class="form-control form-control-sm">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Note</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2"
                                  placeholder="Eventuali note..."></textarea>
                    </div>

                    <div id="preview-rate" class="mb-3 p-2 rounded-3" style="background:#f8fafc;display:none;">
                        <div class="text-muted small fw-semibold mb-1">Anteprima rate</div>
                        <div id="preview-content" style="font-size:.78rem;color:#374151;"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Crea retta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Lista rette ── -->
    <div class="col-lg-8">

        <form method="GET" action="<?= BASE_URL ?>rette" class="mb-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" name="q" class="form-control border-start-0"
                       placeholder="Cerca per studente..."
                       value="<?= htmlspecialchars($search) ?>">
                <?php if ($search): ?>
                    <a href="<?= BASE_URL ?>rette" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-list me-2" style="color:#1e40af;"></i>Tutte le rette
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($rette) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($rette)): ?>
                    <div class="empty-state" style="padding:2.5rem 0;">
                        <div class="empty-state-icon"><i class="fas fa-euro-sign"></i></div>
                        <h5>Nessuna retta</h5>
                        <p>Crea la prima retta dal form qui a sinistra.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4">Studente</th>
                                <th style="width:110px;">Totale</th>
                                <th style="width:130px;">Stato</th>
                                <th style="width:90px;" class="text-end pe-3">Da pagare</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rette as $r):
                                $perc = $r['tot_rate'] > 0 ? round($r['rate_pagate'] / $r['tot_rate'] * 100) : 0;
                                $color = $perc === 100 ? '#10b981' : ($perc >= 50 ? '#3b82f6' : '#f59e0b');
                            ?>
                                <tr style="cursor:pointer;"
                                    onclick="window.location='<?= BASE_URL ?>rette/detail/<?= $r['id'] ?>'">
                                    <td class="ps-4 align-middle">
                                        <div class="fw-semibold" style="color:#0c1a3a;">
                                            <?= htmlspecialchars($r['cognome'] . ' ' . $r['nome']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= $r['num_rate'] ?> rate · <?= ucfirst($r['cadenza']) ?>
                                        </div>
                                    </td>
                                    <td class="align-middle fw-semibold" style="color:#0c1a3a;">
                                        € <?= number_format($r['importo_totale'], 2, ',', '.') ?>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;border-radius:6px;">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width:<?= $perc ?>%;background:<?= $color ?>;"
                                                     aria-valuenow="<?= $perc ?>"></div>
                                            </div>
                                            <span class="small fw-semibold" style="color:<?= $color ?>;font-size:.72rem;">
                                                <?= $r['rate_pagate'] ?>/<?= $r['tot_rate'] ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="align-middle text-end pe-3 fw-semibold"
                                        style="color:<?= $r['da_pagare'] > 0 ? '#ef4444' : '#10b981' ?>;font-size:.88rem;">
                                        € <?= number_format($r['da_pagare'], 2, ',', '.') ?>
                                    </td>
                                    <td class="align-middle pe-3" onclick="event.stopPropagation()">
                                        <form method="POST" action="<?= BASE_URL ?>rette/delete">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="apriModalEliminaRetta(this)"
                                                    data-studente="<?= htmlspecialchars($r['cognome'] . ' ' . $r['nome'], ENT_QUOTES) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal conferma eliminazione retta -->
<div class="modal fade" id="modalEliminaRettaLista" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina retta
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi eliminare la retta di <strong id="modal-retta-studente"></strong> e tutte le rate associate?
                <br><span class="text-muted small">L'operazione non può essere annullata.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-retta-lista">
                    <i class="fas fa-trash me-1"></i>Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _formRettaLista = null;

function apriModalEliminaRetta(btn) {
    _formRettaLista = btn.closest('form');
    document.getElementById('modal-retta-studente').textContent = btn.dataset.studente ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaRettaLista')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-conferma-elimina-retta-lista').addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminaRettaLista')).hide();
        if (_formRettaLista) _formRettaLista.submit();
    });
});

function aggiornaPreview() {
    const num     = parseInt(document.getElementById('inpNumRate').value) || 0;
    const importo = parseFloat(document.querySelector('[name="importo_totale"]').value) || 0;
    const preview = document.getElementById('preview-rate');
    const content = document.getElementById('preview-content');

    if (num > 0 && importo > 0) {
        const base = (importo / num).toFixed(2);
        content.innerHTML = `<strong>${num}</strong> rate da circa <strong>€ ${base}</strong> ciascuna`;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}
document.querySelector('[name="importo_totale"]').addEventListener('input', aggiornaPreview);
</script>
