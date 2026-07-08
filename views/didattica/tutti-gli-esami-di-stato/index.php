<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

$riferimentoLabel = ['percorso' => 'Corso', 'classe' => 'Classe'];
$statoBadge = [
    'programmato' => '#3b82f6',
    'in_corso'    => '#f59e0b',
    'completato'  => '#10b981',
    'annullato'   => '#ef4444',
];
?>

<?php if ($success ?? null): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error ?? null): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-list me-2" style="color:#1e40af;"></i>Tutti gli Esami di Stato
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:.75rem;">
            <?= count($esami) ?>
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

<form method="GET" action="<?= BASE_URL ?>tutti-gli-esami-di-stato" class="mb-3">
    <div class="input-group input-group-sm" style="max-width:420px;">
        <span class="input-group-text bg-white border-end-0">
            <i class="fas fa-search text-muted"></i>
        </span>
        <input type="text" name="q" class="form-control border-start-0"
               placeholder="Cerca per denominazione, percorso..."
               value="<?= htmlspecialchars($search) ?>">
        <?php if ($search): ?>
            <a href="<?= BASE_URL ?>tutti-gli-esami-di-stato" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-landmark me-2" style="color:#1e40af;"></i>Tutti gli esami di stato
        </h6>
        <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($esami) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($esami)): ?>
            <div class="empty-state" style="padding:2.5rem 0;">
                <div class="empty-state-icon"><i class="fas fa-landmark"></i></div>
                <h5>Nessun esame di stato</h5>
                <p>Non sono presenti esami di stato nel sistema.</p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4">Esame</th>
                        <th>Riferimento</th>
                        <th style="width:100px;">Data</th>
                        <th style="width:90px;">Stato</th>
                        <th style="width:80px;" class="text-center">Iscritti</th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($esami as $e):
                        $nomeAnno = $e['anno_numero']
                            ? ($ordinali[$e['anno_numero']] ?? $e['anno_numero'].'° Anno')
                            : null;
                        $colore = $statoBadge[$e['stato']] ?? '#64748b';
                    ?>
                        <tr style="cursor:pointer;"
                            onclick="window.location='<?= BASE_URL ?>esami-di-stato-prova/detail/<?= $e['id'] ?>'">
                            <td class="ps-4 align-middle">
                                <div class="fw-semibold" style="color:#0c1a3a;">
                                    <?= htmlspecialchars($e['denominazione']) ?>
                                </div>
                                <div class="text-muted small">
                                    <?= htmlspecialchars($e['percorso_nome']) ?>
                                    · A.S. <?= htmlspecialchars($e['anno_label'] ?? '—') ?>
                                </div>
                                <?php if ($e['rapp_cognome']): ?>
                                <div class="text-muted" style="font-size:.72rem;">
                                    <i class="fas fa-user-tie me-1"></i>
                                    <?= htmlspecialchars($e['rapp_cognome'] . ' ' . $e['rapp_nome']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle" style="font-size:.88rem;">
                                <span class="badge" style="background:#e8eef8;color:#1e40af;font-size:.72rem;">
                                    <?= $riferimentoLabel[$e['riferimento_tipo']] ?? $e['riferimento_tipo'] ?>
                                </span>
                                <?php if ($e['riferimento_tipo'] === 'classe' && $nomeAnno): ?>
                                    <div class="text-muted small mt-1"><?= $nomeAnno ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-muted small">
                                <?php if ($e['data']): ?>
                                    <?= date('d/m/Y', strtotime($e['data'])) ?>
                                    <?php if ($e['ora_inizio']): ?>
                                        <div><?= substr($e['ora_inizio'], 0, 5) ?></div>
                                    <?php endif; ?>
                                <?php else: echo '—'; endif; ?>
                            </td>
                            <td class="align-middle">
                                <span class="badge" style="background:<?= $colore ?>1a;color:<?= $colore ?>;border:1px solid <?= $colore ?>33;font-size:.72rem;">
                                    <?= ucfirst(str_replace('_', ' ', $e['stato'])) ?>
                                </span>
                            </td>
                            <td class="align-middle text-center" style="font-size:.85rem;">
                                <span class="fw-semibold" style="color:#0c1a3a;"><?= $e['num_iscritti'] ?></span>
                                <?php if ($e['num_valutati'] > 0): ?>
                                    <div class="text-muted" style="font-size:.72rem;"><?= $e['num_valutati'] ?> val.</div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle pe-3">
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-elimina-esame"
                                        data-id="<?= $e['id'] ?>"
                                        data-nome="<?= htmlspecialchars($e['denominazione'], ENT_QUOTES) ?>"
                                        onclick="event.stopPropagation();">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- ── Modal Elimina Esame ── -->
<div class="modal fade" id="modalEliminaEsame" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina esame di stato
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="mb-1" style="color:#374151;">
                    Stai per eliminare <strong id="esameNomeDaEliminare"></strong>.
                </p>
                <p class="text-muted small mb-0">Verranno eliminati tutti i dati collegati. L'operazione è irreversibile.</p>
            </div>
            <div class="modal-footer border-0 px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <form method="POST" action="<?= BASE_URL ?>tutti-gli-esami-di-stato/delete" class="d-inline">
                    <input type="hidden" name="id" id="esameIdDaEliminare">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-elimina-esame').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            document.getElementById('esameNomeDaEliminare').textContent = btn.dataset.nome;
            document.getElementById('esameIdDaEliminare').value         = btn.dataset.id;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaEsame')).show();
        });
    });
});
</script>

