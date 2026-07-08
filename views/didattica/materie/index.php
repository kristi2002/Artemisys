<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-atom me-2" style="color:#1e40af;"></i>Materie
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:0.75rem;font-weight:600;">
            <?= $totale ?>
        </span>
    </h4>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- Form aggiunta -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3" style="color:#0c1a3a;">
                    <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Nuova materia
                </h6>
                <form method="POST" action="<?= BASE_URL ?>materie/store">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Codice <span class="text-danger">*</span></label>
                        <input type="text"
                               name="codice"
                               id="inputCodice"
                               class="form-control text-uppercase"
                               placeholder="es. MAT"
                               maxlength="20"
                               required>
                        <div class="form-text">Codice univoco breve per la materia.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nome materia <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nome"
                               id="inputNome"
                               class="form-control"
                               placeholder="es. Matematica"
                               maxlength="150"
                               required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Codice regionale</label>
                        <input type="text"
                               name="codice_regionale"
                               class="form-control text-uppercase"
                               placeholder="es. A027"
                               maxlength="20">
                        <div class="form-text">Codice ministeriale/regionale (opzionale).</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Aggiungi materia
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Elenco materie -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">

            <!-- Barra ricerca -->
            <div class="card-header bg-white border-bottom py-3 px-4">
                <form method="GET" action="<?= BASE_URL ?>materie" class="d-flex gap-2">
                    <input type="text"
                           name="q"
                           class="form-control form-control-sm"
                           placeholder="Cerca per nome o codice..."
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($_GET['q'])): ?>
                        <a href="<?= BASE_URL ?>materie" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card-body p-0">
                <?php if (empty($materie)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-atom"></i>
                        </div>
                        <h5><?= !empty($_GET['q']) ? 'Nessun risultato' : 'Nessuna materia' ?></h5>
                        <p><?= !empty($_GET['q']) ? 'Prova con un termine diverso.' : 'Aggiungi la prima materia dal form.' ?></p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4" style="width:110px;">Codice</th>
                                <th>Nome</th>
                                <th style="width:130px;">Cod. Regionale</th>
                                <th class="text-end pe-4" style="width:80px;">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materie as $m): ?>
                                <tr>
                                    <td class="ps-4 align-middle">
                                        <span class="badge" style="background:#e8eef8;color:#1e40af;font-weight:700;letter-spacing:0.5px;font-size:0.78rem;">
                                            <?= htmlspecialchars($m['codice']) ?>
                                        </span>
                                    </td>
                                    <td class="align-middle fw-semibold" style="color:#0c1a3a;">
                                        <?= htmlspecialchars($m['nome']) ?>
                                    </td>
                                    <td class="align-middle text-muted small">
                                        <?= $m['codice_regionale'] ? htmlspecialchars($m['codice_regionale']) : '—' ?>
                                    </td>
                                    <td class="text-end pe-4 align-middle">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-elimina-materia"
                                                title="Elimina"
                                                data-id="<?= $m['id'] ?>"
                                                data-nome="<?= htmlspecialchars($m['nome'], ENT_QUOTES) ?>">
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
    </div>

</div>

<!-- ── Modal Elimina Materia ── -->
<div class="modal fade" id="modalEliminaMateria" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina materia
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-1" style="color:#374151;">
                    Stai per eliminare la materia <strong id="materiaNomeDaEliminare"></strong>.
                </p>
                <p class="text-muted small mb-0">L'operazione è irreversibile.</p>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" action="<?= BASE_URL ?>materie/delete" class="d-inline">
                    <input type="hidden" name="id" id="materiaIdDaEliminare">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-genera il codice dalle iniziali del nome
document.getElementById('inputNome').addEventListener('input', function () {
    const codiceInput = document.getElementById('inputCodice');
    if (codiceInput.dataset.manual === '1') return;

    const words = this.value.trim().split(/\s+/).filter(w => w);
    if (words.length === 0) { codiceInput.value = ''; return; }

    let codice = words.map(w => w[0].toUpperCase()).join('');
    codiceInput.value = codice;
});

document.getElementById('inputCodice').addEventListener('input', function () {
    this.dataset.manual = this.value ? '1' : '0';
    this.value = this.value.toUpperCase();
});

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-elimina-materia');
    if (!btn) return;
    document.getElementById('materiaNomeDaEliminare').textContent = btn.dataset.nome;
    document.getElementById('materiaIdDaEliminare').value         = btn.dataset.id;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaMateria')).show();
});
</script>
