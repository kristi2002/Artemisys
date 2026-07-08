<?php
$ordinali = [1=>'Primo Anno',2=>'Secondo Anno',3=>'Terzo Anno',4=>'Quarto Anno',5=>'Quinto Anno',
             6=>'Sesto Anno',7=>'Settimo Anno',8=>'Ottavo Anno',9=>'Nono Anno',10=>'Decimo Anno'];
$numeriUsati = array_column($percorso['anni'], 'numero');
$prossimo    = 1;
for ($i = 1; $i <= 10; $i++) {
    if (!in_array($i, $numeriUsati)) { $prossimo = $i; break; }
}
?>

<!-- Breadcrumb aggiuntivo -->
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>percorsi" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Percorsi
    </a>
    <span class="text-muted">/</span>
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <?= htmlspecialchars($percorso['nome']) ?>
    </h4>
    <span class="badge ms-1" style="background:#e8eef8;color:#1e40af;font-size:0.8rem;">
        A.S. <?= htmlspecialchars($percorso['anno_label']) ?>
    </span>
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

<!-- ── Card dettagli corso ── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-route me-2" style="color:#1e40af;"></i>Dettagli corso
        </h6>
    </div>
    <div class="card-body px-4 py-4">
        <div class="row g-3">

            <?php
            $infoBlock = function(string $label, ?string $val, string $icon = 'fa-info-circle'): string {
                if ($val === null || $val === '') return '';
                return '
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                            <i class="fas ' . $icon . ' me-1"></i>' . $label . '
                        </div>
                        <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">' . htmlspecialchars($val) . '</div>
                    </div>
                </div>';
            };
            ?>

            <?= $infoBlock('Anno scolastico', $percorso['anno_label'] ?? null, 'fa-calendar-alt') ?>

            <?php if (!empty($percorso['sede_nome'])): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                        <i class="fas fa-map-marker-alt me-1"></i>Sede
                    </div>
                    <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;"><?= htmlspecialchars($percorso['sede_nome']) ?></div>
                    <?php if (!empty($percorso['sede_via'])): ?>
                        <div class="text-muted" style="font-size:.8rem;"><?= htmlspecialchars($percorso['sede_via']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($percorso['sede_comune'])): ?>
                        <div class="text-muted" style="font-size:.8rem;"><?= htmlspecialchars($percorso['sede_comune']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($percorso['sede_telefono'])): ?>
                        <div class="text-muted" style="font-size:.8rem;"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($percorso['sede_telefono']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($percorso['data_inizio_anno']) || !empty($percorso['data_fine_anno'])): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                        <i class="fas fa-calendar-day me-1"></i>Anno accademico
                    </div>
                    <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                        <?= !empty($percorso['data_inizio_anno']) ? date('d/m/Y', strtotime($percorso['data_inizio_anno'])) : '—' ?>
                        &rarr;
                        <?= !empty($percorso['data_fine_anno'])   ? date('d/m/Y', strtotime($percorso['data_fine_anno']))   : '—' ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?= $infoBlock('Descrizione', $percorso['descrizione'] ?? null, 'fa-sticky-note') ?>

        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Aggiungi anno -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-3" style="color:#0c1a3a;">
                    <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Aggiungi anno di corso
                </h6>
                <?php if (count($numeriUsati) >= 10): ?>
                    <p class="text-muted small">Tutti gli anni (fino al 10°) sono già stati aggiunti.</p>
                <?php else: ?>
                    <form method="POST" action="<?= BASE_URL ?>percorsi/add-anno">
                        <input type="hidden" name="percorso_id" value="<?= $percorso['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Anno di corso</label>
                            <select name="numero" class="form-select" required>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <?php if (!in_array($i, $numeriUsati)): ?>
                                        <option value="<?= $i ?>" <?= $i === $prossimo ? 'selected' : '' ?>>
                                            <?= $ordinali[$i] ?? $i . '° Anno' ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Aggiungi anno
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Anni di corso -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    Anni di corso
                    <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;">
                        <?= count($percorso['anni']) ?>
                    </span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($percorso['anni'])): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-layer-group"></i></div>
                        <h5>Nessun anno aggiunto</h5>
                        <p>Aggiungi il primo anno di corso dal pannello.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4">Anno di corso</th>
                                <th>Materie</th>
                                <th class="text-end pe-4">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($percorso['anni'] as $anno): ?>
                                <tr style="cursor:pointer;" onclick="location.href='<?= BASE_URL ?>percorsi/anno/<?= $anno['id'] ?>'">
                                    <td class="ps-4 align-middle fw-semibold" style="color:#0c1a3a;">
                                        <i class="fas fa-layer-group me-2" style="color:#1e40af;"></i>
                                        <?= $ordinali[$anno['numero']] ?? $anno['numero'] . '° Anno' ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ((int)$anno['num_materie'] > 0): ?>
                                            <span class="badge" style="background:#e8eef8;color:#1e40af;">
                                                <?= $anno['num_materie'] ?> <?= $anno['num_materie'] == 1 ? 'materia' : 'materie' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Nessuna materia</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 align-middle" onclick="event.stopPropagation()">
                                        <form method="POST" action="<?= BASE_URL ?>percorsi/delete-anno" class="form-delete-anno">
                                            <input type="hidden" name="anno_id" value="<?= $anno['id'] ?>">
                                            <input type="hidden" name="percorso_id" value="<?= $percorso['id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Rimuovi"
                                                    onclick="apriModalEliminaAnno(this)"
                                                    data-anno="<?= htmlspecialchars($ordinali[$anno['numero']] ?? $anno['numero'] . '° Anno', ENT_QUOTES) ?>">
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

<!-- Modal conferma eliminazione anno di corso -->
<div class="modal fade" id="modalEliminaAnno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Rimuovi anno di corso
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi rimuovere <strong id="modal-anno-label"></strong> dal percorso?
                <br><span class="text-muted small">Verranno rimosse anche le materie associate a questo anno.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-anno">
                    <i class="fas fa-trash me-1"></i>Rimuovi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _formAnno = null;

function apriModalEliminaAnno(btn) {
    _formAnno = btn.closest('form');
    document.getElementById('modal-anno-label').textContent = btn.dataset.anno ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaAnno')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-conferma-elimina-anno').addEventListener('click', function () {
        if (_formAnno) {
            bootstrap.Modal.getInstance(document.getElementById('modalEliminaAnno')).hide();
            _formAnno.submit();
        }
    });
});
</script>
