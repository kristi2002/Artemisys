<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

// Costruisco mappe per i select a cascata
$percorsiMap  = [];  // id → {nome, anno_label}
$mappaAnni    = [];  // percorso_id → [{anno_id, numero}]
$mappaMaterie = [];  // anno_id → [{pam_id, materia_nome, codice}]

foreach ($pamList as $row) {
    if (!isset($percorsiMap[$row['percorso_id']])) {
        $percorsiMap[$row['percorso_id']] = [
            'nome'       => $row['percorso_nome'],
            'anno_label' => $row['anno_label'],
        ];
    }
    $annoKey = $row['percorso_id'];
    $existing = array_column($mappaAnni[$annoKey] ?? [], 'anno_id');
    if (!in_array($row['anno_id'], $existing)) {
        $mappaAnni[$annoKey][] = ['anno_id' => $row['anno_id'], 'numero' => $row['anno_numero']];
    }
    $mappaMaterie[$row['anno_id']][] = [
        'pam_id' => $row['pam_id'],
        'nome'   => $row['materia_nome'],
        'codice' => $row['codice'],
    ];
}

$tipoBadge = ['scritto' => '#3b82f6','orale' => '#10b981','pratico' => '#f59e0b'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-file-alt me-2" style="color:#1e40af;"></i><?= htmlspecialchars($pageTitle ?? 'Esami') ?>
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

<div class="row g-4">

    <!-- ── Form crea esame ── -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Nuovo esame
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($pamList)): ?>
                    <p class="text-muted small">Nessun percorso/materia disponibile.</p>
                <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>esami/store">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Percorso <span class="text-danger">*</span></label>
                        <select id="sel_percorso" class="form-select form-select-sm" onchange="aggiornaAnni()">
                            <option value="">— Seleziona —</option>
                            <?php foreach ($percorsiMap as $pid => $p): ?>
                                <option value="<?= $pid ?>">
                                    <?= htmlspecialchars($p['nome']) ?> — <?= htmlspecialchars($p['anno_label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Anno di corso <span class="text-danger">*</span></label>
                        <select id="sel_anno" class="form-select form-select-sm" onchange="aggiornaMaterie()" disabled>
                            <option value="">— Prima seleziona il percorso —</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Materia <span class="text-danger">*</span></label>
                        <select id="sel_materia" name="pam_id" class="form-select form-select-sm" required disabled>
                            <option value="">— Prima seleziona l'anno —</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Titolo <span class="text-danger">*</span></label>
                        <input type="text" name="titolo" class="form-control form-control-sm"
                               placeholder="es. Esame di fine corso" required maxlength="255">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold">Data</label>
                            <input type="date" name="data" class="form-control form-control-sm">
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold">Ora inizio</label>
                            <input type="time" name="ora_inizio" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tipo</label>
                        <select name="tipo" class="form-select form-select-sm">
                            <option value="scritto">Scritto</option>
                            <option value="orale">Orale</option>
                            <option value="pratico">Pratico</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Note</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2"
                                  placeholder="Note opzionali..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Crea esame
                    </button>
                    <p class="text-muted small text-center mt-2 mb-0">
                        <i class="fas fa-users me-1"></i>Gli studenti della classe verranno iscritti automaticamente
                    </p>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Lista esami ── -->
    <div class="col-lg-8">

        <!-- Ricerca -->
        <form method="GET" action="<?= BASE_URL ?><?= ($page ?? '') === 'miei-esami' ? 'miei-esami' : 'esami' ?>" class="mb-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" name="q" class="form-control border-start-0"
                       placeholder="Cerca per titolo, materia, percorso..."
                       value="<?= htmlspecialchars($search) ?>">
                <?php if ($search): ?>
                    <a href="<?= BASE_URL ?><?= ($page ?? '') === 'miei-esami' ? 'miei-esami' : 'esami' ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-list me-2" style="color:#1e40af;"></i>Tutti gli esami
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($esami) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($esami)): ?>
                    <div class="empty-state" style="padding:2.5rem 0;">
                        <div class="empty-state-icon"><i class="fas fa-file-alt"></i></div>
                        <h5>Nessun esame</h5>
                        <p>Crea il primo esame dal form qui a sinistra.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4">Esame</th>
                                <th>Materia</th>
                                <th style="width:100px;">Data</th>
                                <th style="width:80px;">Tipo</th>
                                <th style="width:80px;" class="text-center">Iscritti</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($esami as $e):
                                $nomeAnno = $ordinali[$e['anno_numero']] ?? $e['anno_numero'].'° Anno';
                                $colore   = $tipoBadge[$e['tipo']] ?? '#64748b';
                            ?>
                                <tr style="cursor:pointer;"
                                    onclick="window.location='<?= BASE_URL ?>esami/detail/<?= $e['id'] ?>'">
                                    <td class="ps-4 align-middle">
                                        <div class="fw-semibold" style="color:#0c1a3a;">
                                            <?= htmlspecialchars($e['titolo']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars($e['percorso_nome']) ?> — <?= $nomeAnno ?>
                                            · A.S. <?= htmlspecialchars($e['anno_label']) ?>
                                        </div>
                                    </td>
                                    <td class="align-middle" style="font-size:.88rem;color:#374151;">
                                        <?= htmlspecialchars($e['materia_nome']) ?>
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
                                        <span class="badge" style="background:<?= $colore ?>1a;color:<?= $colore ?>;border:1px solid <?= $colore ?>33;font-size:.75rem;">
                                            <?= ucfirst($e['tipo']) ?>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center" style="font-size:.85rem;">
                                        <span class="fw-semibold" style="color:#0c1a3a;"><?= $e['num_iscritti'] ?></span>
                                        <?php if ($e['num_valutati'] > 0): ?>
                                            <div class="text-muted" style="font-size:.72rem;"><?= $e['num_valutati'] ?> val.</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle pe-3" onclick="event.stopPropagation()">
                                        <form method="POST" action="<?= BASE_URL ?>esami/delete"
                                              onsubmit="return confirm('Eliminare questo esame e tutti i voti?')">
                                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
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

<script>
const mappaAnni    = <?= json_encode($mappaAnni) ?>;
const mappaMaterie = <?= json_encode($mappaMaterie) ?>;
const ordinali     = <?= json_encode($ordinali) ?>;

function aggiornaAnni() {
    const pid      = document.getElementById('sel_percorso').value;
    const selAnno  = document.getElementById('sel_anno');
    const selMat   = document.getElementById('sel_materia');

    selAnno.innerHTML  = '<option value="">— Seleziona anno —</option>';
    selMat.innerHTML   = '<option value="">— Prima seleziona l\'anno —</option>';
    selAnno.disabled   = true;
    selMat.disabled    = true;

    if (!pid || !mappaAnni[pid]) return;
    selAnno.disabled = false;
    mappaAnni[pid].forEach(a => {
        const label = ordinali[a.numero] ?? (a.numero + '° Anno');
        selAnno.innerHTML += `<option value="${a.anno_id}">${label}</option>`;
    });
}

function aggiornaMaterie() {
    const annoId = document.getElementById('sel_anno').value;
    const selMat = document.getElementById('sel_materia');

    selMat.innerHTML = '<option value="">— Seleziona materia —</option>';
    selMat.disabled  = true;

    if (!annoId || !mappaMaterie[annoId]) return;
    selMat.disabled = false;
    mappaMaterie[annoId].forEach(m => {
        selMat.innerHTML += `<option value="${m.pam_id}">${m.nome} (${m.codice})</option>`;
    });
}
</script>
