<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

$tipoBadge = ['scritto' => '#3b82f6','orale' => '#10b981','pratico' => '#f59e0b'];
$colore    = $tipoBadge[$esame['tipo']] ?? '#64748b';
$nomeAnno  = $ordinali[$esame['anno_numero']] ?? $esame['anno_numero'].'° Anno';

$numValutati = 0;
$numAssenti  = 0;
$totale      = count($iscrizioni);
foreach ($iscrizioni as $i) {
    if ($i['assente']) $numAssenti++;
    elseif ($i['voto'] !== null) $numValutati++;
}
$mediaVoti = 0;
$votiList  = array_filter(array_column($iscrizioni, 'voto'), fn($v) => $v !== null);
if (!empty($votiList)) $mediaVoti = round(array_sum($votiList) / count($votiList), 2);
?>

<!-- Back -->
<?php $isDocente = ($_SESSION['user_ruolo'] ?? '') === 'docente'; ?>
<div class="mb-3">
    <a href="<?= BASE_URL ?><?= $isDocente ? 'miei-esami' : 'esami' ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i><?= $isDocente ? 'I miei esami' : 'Tutti gli esami' ?>
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

<div class="row g-4">

    <!-- ── Colonna sinistra: info esame ── -->
    <div class="col-lg-4">

        <!-- Info card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-file-alt me-2" style="color:#1e40af;"></i>Dettaglio esame
                </h6>
                <span class="badge" style="background:<?= $colore ?>1a;color:<?= $colore ?>;border:1px solid <?= $colore ?>33;">
                    <?= ucfirst($esame['tipo']) ?>
                </span>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1" style="color:#0c1a3a;"><?= htmlspecialchars($esame['titolo']) ?></h5>
                <div class="text-muted small mb-3">
                    <?= htmlspecialchars($esame['percorso_nome']) ?> — <?= $nomeAnno ?>
                    · A.S. <?= htmlspecialchars($esame['anno_label'] ?? '—') ?>
                </div>

                <table class="table table-sm table-borderless mb-0" style="font-size:.88rem;">
                    <tr>
                        <td class="ps-0 text-muted" style="width:90px;">Materia</td>
                        <td class="fw-semibold">
                            <?= htmlspecialchars($esame['materia_nome']) ?>
                            <span class="text-muted">(<?= htmlspecialchars($esame['materia_codice']) ?>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-0 text-muted">Data</td>
                        <td><?= $esame['data'] ? date('d/m/Y', strtotime($esame['data'])) : '—' ?></td>
                    </tr>
                    <?php if ($esame['ora_inizio']): ?>
                    <tr>
                        <td class="ps-0 text-muted">Ora inizio</td>
                        <td><?= substr($esame['ora_inizio'], 0, 5) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($esame['note']): ?>
                    <tr>
                        <td class="ps-0 text-muted">Note</td>
                        <td><?= nl2br(htmlspecialchars($esame['note'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Statistiche -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-chart-bar me-2" style="color:#1e40af;"></i>Statistiche
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="row g-0 text-center">
                    <div class="col-4 py-3 border-end">
                        <div class="fw-bold fs-5" style="color:#0c1a3a;"><?= $totale ?></div>
                        <div class="text-muted" style="font-size:.72rem;">Iscritti</div>
                    </div>
                    <div class="col-4 py-3 border-end">
                        <div class="fw-bold fs-5" style="color:#10b981;"><?= $numValutati ?></div>
                        <div class="text-muted" style="font-size:.72rem;">Valutati</div>
                    </div>
                    <div class="col-4 py-3">
                        <div class="fw-bold fs-5" style="color:#ef4444;"><?= $numAssenti ?></div>
                        <div class="text-muted" style="font-size:.72rem;">Assenti</div>
                    </div>
                </div>
                <?php if ($mediaVoti > 0): ?>
                <div class="border-top px-4 py-2 text-center">
                    <span class="text-muted small">Media voti: </span>
                    <span class="fw-bold" style="color:#1e40af;"><?= $mediaVoti ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Supervisori -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-user-shield me-2" style="color:#1e40af;"></i>Supervisori
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;">
                    <?= count($supervisori) ?>
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($supervisori)): ?>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle me-1"></i>Nessun supervisore assegnato.
                    </p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <?php foreach ($supervisori as $s): ?>
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3"
                             style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div style="width:32px;height:32px;border-radius:50%;background:#e8eef8;
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-user-tie" style="color:#1e40af;font-size:.85rem;"></i>
                            </div>
                            <div class="flex-grow-1" style="font-size:.88rem;">
                                <div class="fw-semibold" style="color:#0c1a3a;">
                                    <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                                </div>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>esami/remove-supervisore"
                                  onsubmit="return confirm('Rimuovere questo supervisore?')">
                                <input type="hidden" name="esame_id"      value="<?= $esame['id'] ?>">
                                <input type="hidden" name="insegnante_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Rimuovi">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($insDisponibili)): ?>
                <form method="POST" action="<?= BASE_URL ?>esami/add-supervisore" class="d-flex gap-2">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
                    <select name="insegnante_id" class="form-select form-select-sm" required>
                        <option value="">— Aggiungi docente —</option>
                        <?php foreach ($insDisponibili as $i): ?>
                            <option value="<?= $i['id'] ?>">
                                <?= htmlspecialchars($i['cognome'] . ' ' . $i['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
                <div class="text-muted mt-2" style="font-size:.7rem;">
                    Solo i docenti assegnati alla materia possono essere supervisori.
                </div>
                <?php elseif (empty($insMateria)): ?>
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Nessun docente è assegnato a questa materia.
                    </div>
                <?php else: ?>
                    <div class="text-muted small mb-0">
                        Tutti i docenti della materia sono già supervisori.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Documenti / Allegati -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-paperclip me-2" style="color:#1e40af;"></i>Documenti
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($allegati) ?></span>
            </div>
            <div class="card-body">
                <!-- Upload -->
                <form method="POST" action="<?= BASE_URL ?>esami/upload-allegato"
                      enctype="multipart/form-data" class="mb-3">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
                    <div class="input-group input-group-sm">
                        <input type="file" name="allegato" class="form-control"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i>Carica
                        </button>
                    </div>
                    <div class="form-text">PDF, DOC, DOCX, JPG, PNG — max 10 MB</div>
                </form>

                <?php if (empty($allegati)): ?>
                    <p class="text-muted small fst-italic mb-0">Nessun documento caricato.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($allegati as $all):
                            $ext  = strtolower(pathinfo($all['original_name'], PATHINFO_EXTENSION));
                            $icon = match($ext) {
                                'pdf'           => 'fa-file-pdf text-danger',
                                'doc','docx'    => 'fa-file-word text-primary',
                                'jpg','jpeg','png' => 'fa-file-image text-success',
                                default         => 'fa-file text-muted',
                            };
                        ?>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <a href="<?= ASSETS_URL ?>public/uploads/esami/<?= urlencode($all['filename']) ?>"
                                   target="_blank" class="text-decoration-none text-truncate" style="color:#0c1a3a;font-size:.85rem;max-width:75%;">
                                    <i class="fas <?= $icon ?> me-2"></i>
                                    <?= htmlspecialchars($all['original_name']) ?>
                                    <span class="text-muted ms-1" style="font-size:.72rem;">
                                        <?= date('d/m/Y', strtotime($all['created_at'])) ?>
                                    </span>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>esami/delete-allegato"
                                      onsubmit="return confirm('Eliminare questo documento?')">
                                    <input type="hidden" name="allegato_id" value="<?= $all['id'] ?>">
                                    <input type="hidden" name="esame_id"    value="<?= $esame['id'] ?>">
                                    <button type="submit" class="btn btn-link p-0 text-danger" style="font-size:.8rem;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Azioni -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>esami/delete"
                      onsubmit="return confirm('Eliminare questo esame e tutti i voti?')">
                    <input type="hidden" name="id" value="<?= $esame['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="fas fa-trash me-2"></i>Elimina esame
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- ── Colonna destra: iscritti e voti ── -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-users me-2" style="color:#1e40af;"></i>Iscritti
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= $totale ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($iscrizioni)): ?>
                    <div class="text-center text-muted py-5" style="font-size:.9rem;">
                        <i class="fas fa-users mb-2 d-block" style="font-size:2rem;color:#e2e8f0;"></i>
                        Nessuno studente iscritto
                    </div>
                <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>esami/saveVoti" id="formVoti">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:.88rem;">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="ps-4" style="width:45%;">Studente</th>
                                    <th style="width:13%;">Voto</th>
                                    <th style="width:10%;" class="text-center">Assente</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($iscrizioni as $iscr): ?>
                                <tr id="row-<?= $iscr['id'] ?>" class="<?= $iscr['assente'] ? 'table-danger' : '' ?>">
                                    <td class="ps-4 align-middle">
                                        <div class="fw-semibold" style="color:#0c1a3a;">
                                            <?= htmlspecialchars($iscr['cognome'] . ' ' . $iscr['nome']) ?>
                                        </div>
                                        <?php if ($iscr['codice_fiscale']): ?>
                                        <div class="text-muted" style="font-size:.75rem;">
                                            <?= htmlspecialchars($iscr['codice_fiscale']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <input type="number"
                                               name="voti[<?= $iscr['id'] ?>][voto]"
                                               class="form-control form-control-sm voto-input"
                                               style="width:80px;"
                                               min="0" max="100" step="0.5"
                                               value="<?= $iscr['voto'] !== null ? $iscr['voto'] : '' ?>"
                                               placeholder="—"
                                               <?= $iscr['assente'] ? 'disabled' : '' ?>>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input assente-check" type="checkbox"
                                                   name="voti[<?= $iscr['id'] ?>][assente]"
                                                   value="1"
                                                   id="assente_<?= $iscr['id'] ?>"
                                                   onchange="toggleAssente(this)"
                                                   <?= $iscr['assente'] ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <input type="text"
                                               name="voti[<?= $iscr['id'] ?>][note]"
                                               class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($iscr['note'] ?? '') ?>"
                                               placeholder="Note opzionali...">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between">
                        <span class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Le modifiche non salvate andranno perse
                        </span>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Salva voti
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
function toggleAssente(checkbox) {
    const row      = checkbox.closest('tr');
    const votoInp  = row.querySelector('.voto-input');

    if (checkbox.checked) {
        row.classList.add('table-danger');
        if (votoInp) { votoInp.value = ''; votoInp.disabled = true; }
    } else {
        row.classList.remove('table-danger');
        if (votoInp) { votoInp.disabled = false; }
    }
}
</script>
