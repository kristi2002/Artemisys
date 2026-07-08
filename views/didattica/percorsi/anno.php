<?php
$ordinali = [1=>'Primo Anno',2=>'Secondo Anno',3=>'Terzo Anno',4=>'Quarto Anno',5=>'Quinto Anno',
             6=>'Sesto Anno',7=>'Settimo Anno',8=>'Ottavo Anno',9=>'Nono Anno',10=>'Decimo Anno'];
$nomeAnno = $ordinali[$anno['numero']] ?? $anno['numero'] . '° Anno';

// Conteggio insegnanti distinti tra le materie di questo anno
$_insSet = [];
foreach (($materieInsegnanti ?? []) as $lista) {
    foreach ((array)$lista as $n) { $_insSet[$n] = true; }
}
$numInsegnanti = count($_insSet);
?>

<!-- Breadcrumb / header -->
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="<?= BASE_URL ?>percorsi" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Percorsi
    </a>
    <span class="text-muted">/</span>
    <a href="<?= BASE_URL ?>percorsi/detail/<?= $anno['percorso_id'] ?>" class="text-decoration-none fw-semibold" style="color:#1e40af;">
        <?= htmlspecialchars($anno['percorso_nome']) ?>
    </a>
    <span class="text-muted">/</span>
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;"><?= $nomeAnno ?></h4>
    <span class="badge ms-1" style="background:#e8eef8;color:#1e40af;font-size:0.8rem;">
        A.S. <?= htmlspecialchars($anno['anno_label']) ?>
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

<!-- ── Stat cards ── -->
<div class="row g-3 mb-4">
    <?php
    $statCard = function(string $icon, int $num, string $label) {
        ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                         style="width:46px;height:46px;background:#eef2ff;">
                        <i class="fas <?= $icon ?>" style="color:#1e40af;font-size:1.05rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:#0c1a3a;font-size:1.35rem;line-height:1;"><?= $num ?></div>
                        <div class="text-muted small"><?= $label ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    };
    $statCard('fa-atom',                count($materieAssegnate), count($materieAssegnate) === 1 ? 'Materia' : 'Materie');
    $statCard('fa-user-graduate',       count($studenti),         count($studenti) === 1 ? 'Studente' : 'Studenti');
    $statCard('fa-chalkboard-teacher',  $numInsegnanti,           $numInsegnanti === 1 ? 'Insegnante' : 'Insegnanti');
    ?>
</div>

<!-- ── Materie ── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-atom me-2" style="color:#1e40af;"></i>Materie del <?= $nomeAnno ?>
            <span class="badge ms-1" style="background:#e8eef8;color:#1e40af;"><?= count($materieAssegnate) ?></span>
        </h6>
        <?php if (empty($materieDisponibili)): ?>
            <span class="text-muted small fst-italic">
                <?= empty($materieAssegnate)
                    ? 'Nessuna materia disponibile — <a href="' . BASE_URL . 'materie">aggiungile prima</a>'
                    : 'Tutte le materie sono già assegnate' ?>
            </span>
        <?php else: ?>
            <button type="button" class="btn btn-sm btn-primary"
                    data-bs-toggle="modal" data-bs-target="#modalAggiungiMateria">
                <i class="fas fa-plus me-1"></i>Aggiungi materia
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($materieAssegnate)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-atom"></i></div>
                <h5>Nessuna materia assegnata</h5>
                <p>Aggiungi le materie di questo anno con il pulsante <strong>Aggiungi materia</strong>.</p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4" style="width:90px;">Codice</th>
                        <th>Materia</th>
                        <th>Insegnanti</th>
                        <th style="width:110px;">Cod. Regionale</th>
                        <th class="text-end pe-4" style="width:70px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materieAssegnate as $m):
                        $ins = $materieInsegnanti[$m['assoc_id']] ?? [];
                    ?>
                        <tr style="cursor:pointer;" onclick="location.href='<?= BASE_URL ?>percorsi/materia/<?= $m['assoc_id'] ?>'">
                            <td class="ps-4 align-middle">
                                <span class="badge" style="background:#e8eef8;color:#1e40af;font-weight:700;letter-spacing:0.5px;">
                                    <?= htmlspecialchars($m['codice']) ?>
                                </span>
                            </td>
                            <td class="align-middle fw-semibold" style="color:#0c1a3a;">
                                <?= htmlspecialchars($m['nome']) ?>
                            </td>
                            <td class="align-middle" style="font-size:0.85rem;color:#374151;">
                                <?= empty($ins) ? '<span class="text-muted fst-italic">—</span>' : htmlspecialchars(implode(', ', $ins)) ?>
                            </td>
                            <td class="align-middle text-muted small">
                                <?= $m['codice_regionale'] ? htmlspecialchars($m['codice_regionale']) : '—' ?>
                            </td>
                            <td class="text-end pe-4 align-middle" onclick="event.stopPropagation()">
                                <form method="POST" action="<?= BASE_URL ?>percorsi/delete-materia">
                                    <input type="hidden" name="assoc_id" value="<?= $m['assoc_id'] ?>">
                                    <input type="hidden" name="anno_id" value="<?= $anno['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Rimuovi"
                                            onclick="apriModalEliminaMateria(this)"
                                            data-materia="<?= htmlspecialchars($m['nome'], ENT_QUOTES) ?>">
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

<!-- ── Studenti iscritti a questo anno di corso ── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-user-graduate me-2" style="color:#1e40af;"></i>Studenti del <?= $nomeAnno ?>
            <span class="badge ms-1" style="background:#e8eef8;color:#1e40af;"><?= count($studenti) ?></span>
        </h6>
        <?php if (!empty($studenti)): ?>
            <a href="<?= BASE_URL ?>percorsi/scarica-scrutinio?anno_id=<?= $anno['id'] ?>"
               class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download me-1"></i>Scarica scrutinio
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($studenti)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-user-graduate"></i></div>
                <h5>Nessuno studente assegnato</h5>
                <p>Assegna gli studenti a questo anno di corso dalla pagina <a href="<?= BASE_URL ?>assegna-studenti">Assegna Studenti</a>.</p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4">Cognome e Nome</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th style="width:120px;">A.S.</th>
                        <th class="text-end pe-4" style="width:100px;">Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($studenti as $s): ?>
                        <tr style="cursor:pointer;" onclick="location.href='<?= BASE_URL ?>studenti/detail/<?= $s['id'] ?>'">
                            <td class="ps-4 align-middle fw-semibold" style="color:#0c1a3a;">
                                <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                            </td>
                            <td class="align-middle text-muted small">
                                <?= $s['email'] ? htmlspecialchars($s['email']) : '—' ?>
                            </td>
                            <td class="align-middle text-muted small">
                                <?= $s['telefono'] ? htmlspecialchars($s['telefono']) : '—' ?>
                            </td>
                            <td class="align-middle">
                                <span class="badge" style="background:#e8eef8;color:#1e40af;">
                                    <?= htmlspecialchars($s['anno_label'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="text-end pe-4 align-middle">
                                <?php if (!empty($s['attivo'])): ?>
                                    <span class="badge bg-success-subtle text-success">Attivo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Inattivo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: aggiungi materia -->
<?php if (!empty($materieDisponibili)): ?>
<div class="modal fade" id="modalAggiungiMateria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Aggiungi materia al <?= $nomeAnno ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>percorsi/add-materia">
                <input type="hidden" name="anno_id" value="<?= $anno['id'] ?>">
                <div class="modal-body px-4 py-4">
                    <label class="form-label small fw-semibold">Materia <span class="text-danger">*</span></label>
                    <select name="materia_id" class="form-select" required>
                        <option value="">— Seleziona —</option>
                        <?php foreach ($materieDisponibili as $m): ?>
                            <option value="<?= $m['id'] ?>">
                                <?= htmlspecialchars($m['nome']) ?>
                                <?= $m['codice'] ? '(' . htmlspecialchars($m['codice']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annulla
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Aggiungi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal conferma rimozione materia dall'anno -->
<div class="modal fade" id="modalEliminaMateria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Rimuovi materia
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi rimuovere <strong id="modal-materia-label"></strong> da questo anno di corso?
                <br><span class="text-muted small">La materia non verrà eliminata dal sistema, solo dissociata da questo anno.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-materia">
                    <i class="fas fa-trash me-1"></i>Rimuovi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _formMateria = null;

function apriModalEliminaMateria(btn) {
    _formMateria = btn.closest('form');
    document.getElementById('modal-materia-label').textContent = btn.dataset.materia ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaMateria')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-conferma-elimina-materia').addEventListener('click', function () {
        if (_formMateria) {
            bootstrap.Modal.getInstance(document.getElementById('modalEliminaMateria')).hide();
            _formMateria.submit();
        }
    });
});
</script>
