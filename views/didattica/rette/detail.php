<?php
$totale       = (float)$retta['importo_totale'];
$pagato       = 0;
$daPagare     = 0;
$ratePagate   = 0;
$rateScadute  = 0;
$oggi         = date('Y-m-d');

foreach ($rate as $r) {
    if ($r['pagata']) {
        $pagato     += $r['importo'];
        $ratePagate++;
    } else {
        $daPagare += $r['importo'];
        if ($r['scadenza'] && $r['scadenza'] < $oggi) $rateScadute++;
    }
}

$percentuale = $totale > 0 ? round($pagato / $totale * 100) : 0;
?>

<!-- Back -->
<div class="mb-3">
    <a href="<?= BASE_URL ?>rette" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Tutte le rette
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

<!-- Intestazione studente -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3 px-4 d-flex align-items-center gap-3 flex-wrap">
        <div style="width:48px;height:48px;border-radius:50%;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-user-graduate" style="color:#1e40af;font-size:1.3rem;"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold" style="color:#0c1a3a;">
                <?= htmlspecialchars($retta['cognome'] . ' ' . $retta['nome']) ?>
            </h5>
            <div class="text-muted small">
                <?= $retta['num_rate'] ?> rate · <?= ucfirst($retta['cadenza']) ?>
                <?= $retta['note'] ? ' · ' . htmlspecialchars($retta['note']) : '' ?>
            </div>
        </div>
        <div class="ms-auto text-end">
            <div class="text-muted small">Importo totale</div>
            <div class="fw-bold fs-4" style="color:#0c1a3a;">
                € <?= number_format($totale, 2, ',', '.') ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Statistiche + barra pagato -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold" style="color:#0c1a3a;">Avanzamento pagamenti</span>
                    <span class="fw-bold" style="color:<?= $percentuale === 100 ? '#10b981' : '#3b82f6' ?>;">
                        <?= $percentuale ?>%
                    </span>
                </div>
                <div class="progress mb-3" style="height:12px;border-radius:8px;">
                    <div class="progress-bar" role="progressbar"
                         style="width:<?= $percentuale ?>%;background:<?= $percentuale === 100 ? '#10b981' : '#3b82f6' ?>;border-radius:8px;"></div>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-md-3 col-6">
                        <div class="text-muted small">Pagato</div>
                        <div class="fw-bold fs-5" style="color:#10b981;">
                            € <?= number_format($pagato, 2, ',', '.') ?>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-muted small">Da pagare</div>
                        <div class="fw-bold fs-5" style="color:<?= $daPagare > 0 ? '#ef4444' : '#10b981' ?>;">
                            € <?= number_format($daPagare, 2, ',', '.') ?>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-muted small">Rate pagate</div>
                        <div class="fw-bold fs-5" style="color:#3b82f6;">
                            <?= $ratePagate ?> / <?= count($rate) ?>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-muted small">Scadute</div>
                        <div class="fw-bold fs-5" style="color:<?= $rateScadute > 0 ? '#ef4444' : '#94a3b8' ?>;">
                            <?= $rateScadute ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista rate -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-list-ol me-2" style="color:#1e40af;"></i>Rate
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.88rem;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4" style="width:60px;">N°</th>
                                <th style="width:110px;">Importo</th>
                                <th style="width:130px;">Scadenza</th>
                                <th style="width:130px;">Stato</th>
                                <th style="width:130px;">Pagamento</th>
                                <th>Ricevuta</th>
                                <th style="width:140px;" class="text-end pe-3">Azione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rate as $r):
                                $scaduta = !$r['pagata'] && $r['scadenza'] && $r['scadenza'] < $oggi;
                                $bg = $r['pagata'] ? '#f0fdf4' : ($scaduta ? '#fef2f2' : '#fff');
                            ?>
                            <tr style="background:<?= $bg ?>;">
                                <td class="ps-4 align-middle fw-bold" style="color:#0c1a3a;">
                                    #<?= $r['numero'] ?>
                                </td>
                                <td class="align-middle fw-semibold" style="color:#0c1a3a;">
                                    € <?= number_format($r['importo'], 2, ',', '.') ?>
                                </td>
                                <td class="align-middle text-muted">
                                    <?= $r['scadenza'] ? date('d/m/Y', strtotime($r['scadenza'])) : '—' ?>
                                </td>
                                <td class="align-middle">
                                    <?php if ($r['pagata']): ?>
                                        <span class="badge" style="background:#10b9811a;color:#10b981;border:1px solid #10b98133;">
                                            <i class="fas fa-check-circle me-1"></i>Pagata
                                        </span>
                                    <?php elseif ($scaduta): ?>
                                        <span class="badge" style="background:#ef44441a;color:#ef4444;border:1px solid #ef444433;">
                                            <i class="fas fa-exclamation-circle me-1"></i>Scaduta
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;">
                                            <i class="fas fa-clock me-1"></i>Da pagare
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle small text-muted">
                                    <?php if ($r['pagata']): ?>
                                        <div><?= date('d/m/Y', strtotime($r['data_pagamento'])) ?></div>
                                        <?php if ($r['metodo']): ?>
                                            <div style="font-size:.72rem;"><?= htmlspecialchars($r['metodo']) ?></div>
                                        <?php endif; ?>
                                    <?php else: echo '—'; endif; ?>
                                </td>
                                <td class="align-middle">
                                    <?php if ($r['file_pdf']): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="<?= ASSETS_URL ?>public/uploads/rate/<?= $r['file_pdf'] ?>"
                                               target="_blank" class="text-primary text-decoration-none" style="font-size:.82rem;">
                                                <i class="fas fa-file-pdf me-1"></i>
                                                <?= htmlspecialchars($r['file_originale'] ?? 'ricevuta') ?>
                                            </a>
                                            <form method="POST" action="<?= BASE_URL ?>rette/deleteFile" class="d-inline"
                                                  onsubmit="return confirm('Rimuovere il file?')">
                                                <input type="hidden" name="rata_id" value="<?= $r['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"
                                                        title="Rimuovi">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="document.getElementById('upload-<?= $r['id'] ?>').click()"
                                                style="font-size:.75rem;">
                                            <i class="fas fa-upload me-1"></i>Carica
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>rette/uploadFile"
                                              enctype="multipart/form-data" class="d-none" id="form-up-<?= $r['id'] ?>">
                                            <input type="hidden" name="rata_id" value="<?= $r['id'] ?>">
                                            <input type="file" name="file" id="upload-<?= $r['id'] ?>"
                                                   accept=".pdf,.jpg,.jpeg,.png"
                                                   onchange="document.getElementById('form-up-<?= $r['id'] ?>').submit()">
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle text-end pe-3">
                                    <?php if ($r['pagata']): ?>
                                        <form method="POST" action="<?= BASE_URL ?>rette/unpay" class="form-unpay">
                                            <input type="hidden" name="rata_id" value="<?= $r['id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="apriUnpay(this, <?= $r['numero'] ?>)">
                                                <i class="fas fa-undo me-1"></i>Annulla
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-success"
                                                onclick="apriPay(<?= $r['id'] ?>, <?= $r['numero'] ?>, <?= $r['importo'] ?>)">
                                            <i class="fas fa-check me-1"></i>Paga
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Elimina retta -->
    <div class="col-12">
        <div class="border-top pt-3">
            <form id="form-elimina-retta" method="POST" action="<?= BASE_URL ?>rette/delete">
                <input type="hidden" name="id" value="<?= $retta['id'] ?>">
                <button type="button" class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal" data-bs-target="#modalEliminaRetta">
                    <i class="fas fa-trash me-2"></i>Elimina retta
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Elimina Retta ── -->
<div class="modal fade" id="modalEliminaRetta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina retta
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi eliminare l'intera retta <strong><?= htmlspecialchars($retta['titolo'] ?? 'questa retta') ?></strong> e tutte le rate associate?
                <br><span class="text-muted small">L'operazione non può essere annullata.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-retta">
                    <i class="fas fa-trash me-1"></i>Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-conferma-elimina-retta').addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminaRetta')).hide();
        document.getElementById('form-elimina-retta').submit();
    });
});
</script>

<!-- ── Modal Pagamento ── -->
<div class="modal fade" id="modalPay" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= BASE_URL ?>rette/pay">
                <input type="hidden" name="rata_id" id="payRataId">
                <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                    <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                        <i class="fas fa-check-circle me-2 text-success"></i>
                        Conferma pagamento — <span id="payRataLabel"></span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3 p-3 rounded-3 text-center" style="background:#f0fdf4;">
                        <div class="text-muted small">Importo</div>
                        <div class="fw-bold fs-3" style="color:#10b981;" id="payImporto">€ 0,00</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Data pagamento</label>
                            <input type="date" name="data_pagamento" class="form-control form-control-sm"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Metodo</label>
                            <select name="metodo" class="form-select form-select-sm">
                                <option value="">— Non specificato —</option>
                                <option value="Bonifico">Bonifico</option>
                                <option value="Contanti">Contanti</option>
                                <option value="Carta">Carta</option>
                                <option value="Assegno">Assegno</option>
                                <option value="Altro">Altro</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Note</label>
                            <textarea name="note" class="form-control form-control-sm" rows="2"
                                      placeholder="Riferimento, causale..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annulla
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check me-2"></i>Conferma pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Annulla Pagamento ── -->
<div class="modal fade" id="modalUnpay" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-undo me-2 text-warning"></i>Annulla pagamento
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi annullare il pagamento della <strong id="unpayRataLabel"></strong>?
                <br><span class="text-muted small">La rata tornerà allo stato "non pagata".</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Chiudi
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="btn-conferma-unpay">
                    <i class="fas fa-undo me-1"></i>Annulla pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _formUnpay = null;

function apriPay(rataId, numero, importo) {
    document.getElementById('payRataId').value           = rataId;
    document.getElementById('payRataLabel').textContent  = 'Rata #' + numero;
    document.getElementById('payImporto').textContent    = '€ ' + parseFloat(importo).toFixed(2).replace('.', ',');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPay')).show();
}

function apriUnpay(btn, numero) {
    _formUnpay = btn.closest('form');
    document.getElementById('unpayRataLabel').textContent = 'Rata #' + numero;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUnpay')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-conferma-unpay').addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('modalUnpay')).hide();
        if (_formUnpay) _formUnpay.submit();
    });
});
</script>
