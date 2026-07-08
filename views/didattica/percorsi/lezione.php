<?php
$ordinali = [1=>'Primo Anno',2=>'Secondo Anno',3=>'Terzo Anno',4=>'Quarto Anno',5=>'Quinto Anno',
             6=>'Sesto Anno',7=>'Settimo Anno',8=>'Ottavo Anno',9=>'Nono Anno',10=>'Decimo Anno'];
$nomeAnno    = $ordinali[$lezione['anno_numero']] ?? $lezione['anno_numero'] . '° Anno';
$presentiCount = count(array_filter($presenze, fn($v) => $v === true));
$assentieCount = count($studenti) - $presentiCount;
$isOnline      = (int)($lezione['online'] ?? 0);
$lezioneFutura = $lezione['data'] && $lezione['data'] > date('Y-m-d');
?>

<!-- Breadcrumb -->
<?php $isDocente = ($_SESSION['user_ruolo'] ?? '') === 'docente'; ?>
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <?php if ($isDocente): ?>
    <a href="<?= BASE_URL ?>mie-lezioni" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Le mie lezioni
    </a>
    <?php else: ?>
    <a href="<?= BASE_URL ?>percorsi/materia/<?= $lezione['pam_id'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i><?= htmlspecialchars($lezione['materia_nome']) ?>
    </a>
    <?php endif; ?>
    <span class="text-muted">/</span>
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;"><?= htmlspecialchars($lezione['titolo']) ?></h4>
    <?php if ($lezione['data']): ?>
        <span class="badge ms-1" style="background:#e8eef8;color:#1e40af;font-size:0.8rem;">
            <?= date('d/m/Y', strtotime($lezione['data'])) ?>
        </span>
    <?php endif; ?>
    <?php if ($isOnline): ?>
        <span class="badge ms-1 bg-success">Online</span>
    <?php else: ?>
        <span class="badge ms-1" style="background:#f1f5f9;color:#64748b;">In presenza</span>
    <?php endif; ?>
    <?php if ($isOnline && !empty($lezione['link_online'])): ?>
        <a href="<?= htmlspecialchars($lezione['link_online']) ?>" target="_blank" rel="noopener"
           class="btn btn-success btn-sm ms-auto">
            <i class="fas fa-video me-1"></i>Entra in lezione
        </a>
    <?php endif; ?>
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

    <!-- ── COLONNA SINISTRA ── -->
    <div class="col-lg-4">

        <!-- Info + Online toggle -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0" style="color:#0c1a3a;">
                        <i class="fas fa-info-circle me-2" style="color:#1e40af;"></i>Dettagli
                    </h6>
                    <!-- Switch Online -->
                    <form method="POST" action="<?= BASE_URL ?>percorsi/toggle-online" id="formOnline">
                        <input type="hidden" name="lezione_id" value="<?= $lezione['id'] ?>">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted">Online</span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                       <?= $isOnline ? 'checked' : '' ?>
                                       onchange="document.getElementById('formOnline').submit()"
                                       style="width:2.5em;height:1.3em;cursor:pointer;">
                            </div>
                        </div>
                    </form>
                </div>
                <table class="table table-sm table-borderless mb-0" style="font-size:0.9rem;">
                    <tr>
                        <td class="text-muted ps-0" style="width:90px;">Percorso</td>
                        <td class="fw-semibold" style="color:#0c1a3a;"><?= htmlspecialchars($lezione['percorso_nome']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Anno</td>
                        <td><?= $nomeAnno ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Materia</td>
                        <td><?= htmlspecialchars($lezione['materia_nome']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">A.S.</td>
                        <td><?= htmlspecialchars($lezione['anno_label']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Data</td>
                        <td><?= $lezione['data'] ? date('d/m/Y', strtotime($lezione['data'])) : '—' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Durata</td>
                        <td><?php if ($lezione['durata_minuti']): $d = (int)$lezione['durata_minuti'];
                            echo ($d % 60 === 0) ? ($d/60).'h' : floor($d/60).'h '.($d%60).'m';
                        else: echo '—'; endif; ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Insegnanti della lezione -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-chalkboard-teacher me-2" style="color:#1e40af;"></i>Insegnanti
                </h6>
                <span id="badge-lez-ins" class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($insegnantiLezione) ?></span>
            </div>
            <div class="card-body">
                <p id="empty-lez-ins" class="text-muted small fst-italic mb-3" <?= !empty($insegnantiLezione) ? 'style="display:none;"' : '' ?>>
                    Nessun insegnante assegnato.
                </p>
                <ul id="lista-lez-ins" class="list-group list-group-flush mb-3">
                    <?php foreach ($insegnantiLezione as $ins): ?>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center lez-ins-item"
                            data-id="<?= $ins['id'] ?>">
                            <span style="color:#0c1a3a;font-size:0.9rem;">
                                <i class="fas fa-user-tie me-2 text-muted" style="font-size:0.8rem;"></i>
                                <?= htmlspecialchars($ins['cognome'] . ' ' . $ins['nome']) ?>
                            </span>
                            <button type="button" class="btn btn-link p-0 text-danger" style="font-size:0.8rem;"
                                    onclick="apriModalRimuoviLezIns(this)"
                                    data-id="<?= $ins['id'] ?>"
                                    data-nome="<?= htmlspecialchars($ins['cognome'] . ' ' . $ins['nome'], ENT_QUOTES) ?>">
                                <i class="fas fa-times"></i>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (!empty($insegnantiDisp)): ?>
                    <form id="form-add-lez-ins" method="POST" action="<?= BASE_URL ?>percorsi/add-lezione-insegnante">
                        <input type="hidden" name="lezione_id" value="<?= $lezione['id'] ?>">
                        <div class="input-group input-group-sm">
                            <select name="insegnante_id" class="form-select" required>
                                <option value="">— Aggiungi —</option>
                                <?php foreach ($insegnantiDisp as $ins): ?>
                                    <option value="<?= $ins['id'] ?>">
                                        <?= htmlspecialchars($ins['cognome'] . ' ' . $ins['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i></button>
                        </div>
                    </form>
                <?php else: ?>
                    <p id="tutti-lez-ins" class="text-muted small fst-italic mb-0">Tutti gli insegnanti sono assegnati.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Argomento, Note, Online -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-pen-nib me-2" style="color:#1e40af;"></i>Argomento & Note
                </h6>
            </div>
            <div class="card-body">
                <form id="form-dettagli" method="POST" action="<?= BASE_URL ?>percorsi/update-lezione-dettagli">
                    <input type="hidden" name="lezione_id" value="<?= $lezione['id'] ?>">
                    <?php if ($isOnline): ?>
                        <input type="hidden" name="online" value="1">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Titolo lezione</label>
                        <input type="text" name="titolo" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($lezione['titolo']) ?>"
                               required maxlength="255">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold mb-1">Data</label>
                            <input type="date" name="data" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($lezione['data'] ?? '') ?>">
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold mb-1">Durata (ore)</label>
                            <input type="number" name="durata_ore" class="form-control form-control-sm"
                                   value="<?= $lezione['durata_minuti'] ? $lezione['durata_minuti']/60 : '' ?>"
                                   min="0.5" max="12" step="0.5">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Argomento trattato</label>
                        <textarea name="argomento" class="form-control form-control-sm" rows="4"
                                  placeholder="Descrivi l'argomento trattato in questa lezione..."><?= htmlspecialchars($lezione['argomento'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">
                            <i class="fas fa-link me-1" style="color:#3b82f6;"></i>Link videolezione
                            <?php if (!$isOnline): ?>
                                <span class="text-muted fst-italic" style="font-size:.7rem;">(attiva "Online" per renderlo accessibile)</span>
                            <?php endif; ?>
                        </label>
                        <input type="url" name="link_online" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($lezione['link_online'] ?? '') ?>"
                               placeholder="https://meet.google.com/..., https://classroom.google.com/...">
                        <div class="text-muted" style="font-size:.7rem;">
                            Google Meet, Classroom, Zoom, Teams...
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Note</label>
                        <textarea name="note" class="form-control form-control-sm" rows="3"
                                  placeholder="Note aggiuntive..."><?= htmlspecialchars($lezione['note'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i>Salva
                    </button>
                </form>
            </div>
        </div>

        <!-- Riepilogo presenze -->
        <?php if (!empty($studenti)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="fw-semibold mb-3" style="color:#0c1a3a;">
                    <i class="fas fa-chart-pie me-2" style="color:#1e40af;"></i>Riepilogo
                </h6>
                <div class="d-flex justify-content-around">
                    <div>
                        <div id="riepilogo-presenti" class="fw-bold fs-4" style="color:#16a34a;"><?= $presentiCount ?></div>
                        <div class="small text-muted">Presenti</div>
                    </div>
                    <div>
                        <div id="riepilogo-assenti" class="fw-bold fs-4" style="color:#dc2626;"><?= $assentieCount ?></div>
                        <div class="small text-muted">Assenti</div>
                    </div>
                    <div>
                        <div class="fw-bold fs-4" style="color:#1e40af;"><?= count($studenti) ?></div>
                        <div class="small text-muted">Totale</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- ── COLONNA DESTRA ── -->
    <div class="col-lg-8">

        <!-- Presenze studenti -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-users me-2" style="color:#1e40af;"></i>Presenze
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($studenti) ?> studenti</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($studenti)): ?>
                    <div class="empty-state" style="padding:2rem 0;">
                        <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                        <h5>Nessuno studente</h5>
                        <p>Non ci sono studenti iscritti a questo anno di corso.</p>
                    </div>
                <?php elseif ($lezioneFutura): ?>
                    <div class="text-center py-5 px-4" style="background:#fffbeb;">
                        <i class="fas fa-hourglass-half mb-2 d-block" style="font-size:2rem;color:#f59e0b;"></i>
                        <h5 class="fw-semibold mb-1" style="color:#92400e;">Lezione non ancora svolta</h5>
                        <p class="text-muted mb-0" style="font-size:.88rem;">
                            Le presenze potranno essere registrate a partire dal
                            <strong><?= date('d/m/Y', strtotime($lezione['data'])) ?></strong>.
                        </p>
                    </div>
                <?php else: ?>
                    <form id="form-presenze" method="POST" action="<?= BASE_URL ?>percorsi/set-presenza">
                        <input type="hidden" name="lezione_id" value="<?= $lezione['id'] ?>">
                        <?php foreach ($studenti as $s): ?>
                            <input type="hidden" name="tutti_studenti[]" value="<?= $s['id'] ?>">
                        <?php endforeach; ?>
                        <table class="table table-hover mb-0">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="ps-4">Studente</th>
                                    <th class="text-center" style="width:120px;">
                                        Presente
                                        <button type="button" class="btn btn-link p-0 ms-1"
                                                style="font-size:0.75rem;color:#1e40af;"
                                                onclick="toggleAll(true)" title="Tutti presenti">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                        <button type="button" class="btn btn-link p-0"
                                                style="font-size:0.75rem;color:#dc2626;"
                                                onclick="toggleAll(false)" title="Tutti assenti">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studenti as $s):
                                    $isPresente = $presenze[$s['id']] ?? true; ?>
                                    <tr>
                                        <td class="ps-4 align-middle" style="color:#0c1a3a;">
                                            <i class="fas fa-user-graduate me-2 text-muted" style="font-size:0.85rem;"></i>
                                            <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="form-check form-switch d-flex justify-content-center align-items-center mb-0">
                                                <input class="form-check-input presenza-toggle" type="checkbox"
                                                       name="presenti[]" value="<?= $s['id'] ?>"
                                                       <?= $isPresente ? 'checked' : '' ?>
                                                       style="width:2.5em;height:1.3em;cursor:pointer;">
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="px-4 py-3 border-top" style="background:#fafbff;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salva presenze
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Allegati -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-paperclip me-2" style="color:#1e40af;"></i>Allegati
                </h6>
                <span id="badge-allegati" class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($allegati) ?></span>
            </div>
            <div class="card-body">
                <!-- Upload -->
                <form id="form-upload-allegato" method="POST" action="<?= BASE_URL ?>percorsi/upload-allegato"
                      enctype="multipart/form-data" class="mb-3">
                    <input type="hidden" name="lezione_id" value="<?= $lezione['id'] ?>">
                    <div class="input-group input-group-sm">
                        <input type="file" name="allegato" id="input-allegato" class="form-control"
                               accept=".pdf,.doc,.docx" required>
                        <button type="submit" class="btn btn-primary" id="btn-upload-allegato">
                            <i class="fas fa-upload me-1"></i>Carica
                        </button>
                    </div>
                    <div class="form-text">PDF, DOC, DOCX — max 10 MB</div>
                </form>

                <!-- Lista allegati -->
                <p id="empty-allegati" class="text-muted small fst-italic mb-0" <?= !empty($allegati) ? 'style="display:none;"' : '' ?>>
                    Nessun allegato.
                </p>
                <ul id="lista-allegati" class="list-group list-group-flush">
                    <?php foreach ($allegati as $all):
                        $ext  = strtolower(pathinfo($all['original_name'], PATHINFO_EXTENSION));
                        $icon = $ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
                    ?>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center all-item"
                            data-id="<?= $all['id'] ?>">
                            <a href="<?= ASSETS_URL ?>public/uploads/lezioni/<?= urlencode($all['filename']) ?>"
                               target="_blank" class="text-decoration-none" style="color:#0c1a3a;font-size:0.9rem;">
                                <i class="fas <?= $icon ?> me-2"></i>
                                <?= htmlspecialchars($all['original_name']) ?>
                                <span class="text-muted ms-2" style="font-size:0.78rem;">
                                    <?= date('d/m/Y', strtotime($all['created_at'])) ?>
                                </span>
                            </a>
                            <button type="button" class="btn btn-link p-0 text-danger" style="font-size:0.8rem;"
                                    onclick="apriModalEliminaAllegato(this)"
                                    data-id="<?= $all['id'] ?>"
                                    data-nome="<?= htmlspecialchars($all['original_name'], ENT_QUOTES) ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>

</div>

<!-- Modal conferma eliminazione allegato -->
<div class="modal fade" id="modalEliminaAllegato" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina allegato
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi eliminare <strong id="modal-allegato-nome"></strong>?
                <br><span class="text-muted small">L'operazione non può essere annullata.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-allegato">
                    <i class="fas fa-trash me-1"></i>Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal conferma rimozione insegnante dalla lezione -->
<div class="modal fade" id="modalRimuoviLezIns" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-user-times me-2 text-danger"></i>Rimuovi insegnante
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi rimuovere <strong id="modal-lez-ins-nome"></strong> da questa lezione?
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-rimuovi-lez-ins">
                    <i class="fas fa-user-times me-1"></i>Rimuovi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAll(presente) {
    document.querySelectorAll('.presenza-toggle').forEach(cb => cb.checked = presente);
    aggiornaRiepilogoLive();
}

function aggiornaRiepilogoLive() {
    const totale   = document.querySelectorAll('.presenza-toggle').length;
    const presenti = document.querySelectorAll('.presenza-toggle:checked').length;
    const assenti  = totale - presenti;
    const elP = document.getElementById('riepilogo-presenti');
    const elA = document.getElementById('riepilogo-assenti');
    if (elP) elP.textContent = presenti;
    if (elA) elA.textContent = assenti;
}

function flashBannerLezione(type, msg) {
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const div  = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show`;
    div.setAttribute('role', 'alert');
    div.innerHTML = `<i class="fas ${icon} me-2"></i>${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>`;
    const anchor = document.querySelector('.row.g-4') ?? document.body;
    anchor.parentNode.insertBefore(div, anchor);
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(div).close(), 4000);
}

document.addEventListener('DOMContentLoaded', function () {

    // Aggiorna riepilogo live al cambio di ogni toggle
    document.querySelectorAll('.presenza-toggle').forEach(cb => {
        cb.addEventListener('change', aggiornaRiepilogoLive);
    });

    // Intercetta submit del form dettagli
    const formDettagli = document.getElementById('form-dettagli');
    if (formDettagli) {
        formDettagli.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = formDettagli.querySelector('button[type="submit"]');
            const labelOrig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Salvataggio…';

            try {
                const r    = await fetch(formDettagli.action, {
                    method  : 'POST',
                    headers : { 'X-Requested-With': 'XMLHttpRequest' },
                    body    : new FormData(formDettagli),
                });
                const data = await r.json();
                if (!data.success) {
                    flashBannerLezione('danger', data.error ?? 'Errore durante il salvataggio.');
                } else {
                    flashBannerLezione('success', 'Dettagli salvati.');
                }
            } catch {
                flashBannerLezione('danger', 'Errore di rete. Riprova.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = labelOrig;
            }
        });
    }

    // Intercetta submit del form presenze
    const formPresenze = document.getElementById('form-presenze');
    if (!formPresenze) return;

    formPresenze.addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn = formPresenze.querySelector('button[type="submit"]');
        const labelOrig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvataggio…';

        try {
            const r    = await fetch(formPresenze.action, {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest' },
                body    : new FormData(formPresenze),
            });
            const data = await r.json();

            if (!data.success) {
                flashBannerLezione('danger', data.error ?? 'Errore durante il salvataggio.');
            } else {
                // Aggiorna il riepilogo con i dati confermati dal server
                const elP = document.getElementById('riepilogo-presenti');
                const elA = document.getElementById('riepilogo-assenti');
                if (elP) elP.textContent = data.presenti_count;
                if (elA) elA.textContent = data.totale - data.presenti_count;
                flashBannerLezione('success', 'Presenze salvate.');
            }
        } catch {
            flashBannerLezione('danger', 'Errore di rete. Riprova.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = labelOrig;
        }
    });
});

/* ── helpers UI ────────────────────────────────────────────────────────── */
function flashBannerLez(type, msg) {
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const div  = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show`;
    div.setAttribute('role', 'alert');
    div.innerHTML = `<i class="fas ${icon} me-2"></i>${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>`;
    const anchor = document.querySelector('.row.g-4') ?? document.body;
    anchor.parentNode.insertBefore(div, anchor);
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(div).close(), 4000);
}

function aggiornaStatoAllegati() {
    const n = document.querySelectorAll('#lista-allegati .all-item').length;
    const badge = document.getElementById('badge-allegati');
    if (badge) badge.textContent = n;
    const empty = document.getElementById('empty-allegati');
    if (empty) empty.style.display = n === 0 ? '' : 'none';
}

/* ── allegato: stato eliminazione ──────────────────────────────────────── */
let _allegIdDaEliminare   = null;
let _allegNomeDaEliminare = '';

function apriModalEliminaAllegato(btn) {
    _allegIdDaEliminare   = parseInt(btn.dataset.id);
    _allegNomeDaEliminare = btn.dataset.nome ?? '';
    document.getElementById('modal-allegato-nome').textContent = _allegNomeDaEliminare;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaAllegato')).show();
}

let _lezInsIdDaRimuovere   = null;
let _lezInsNomeDaRimuovere = '';
const LEZIONE_ID = <?= (int)$lezione['id'] ?>;

function apriModalRimuoviLezIns(btn) {
    _lezInsIdDaRimuovere   = parseInt(btn.dataset.id);
    _lezInsNomeDaRimuovere = btn.dataset.nome ?? '';
    document.getElementById('modal-lez-ins-nome').textContent = _lezInsNomeDaRimuovere;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRimuoviLezIns')).show();
}

function aggiornaStatoLezIns() {
    const n = document.querySelectorAll('#lista-lez-ins .lez-ins-item').length;
    document.getElementById('badge-lez-ins').textContent = n;
    const empty = document.getElementById('empty-lez-ins');
    if (empty) empty.style.display = n === 0 ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    /* ── Upload allegato ─────────────────────────────────────────────────── */
    const formUpload = document.getElementById('form-upload-allegato');
    if (formUpload) {
        formUpload.addEventListener('submit', async function (e) {
            e.preventDefault();
            const fileInput = document.getElementById('input-allegato');
            if (!fileInput.files.length) return;

            const btn = document.getElementById('btn-upload-allegato');
            const labelOrig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Caricamento…';

            try {
                const r    = await fetch(formUpload.action, {
                    method  : 'POST',
                    headers : { 'X-Requested-With': 'XMLHttpRequest' },
                    body    : new FormData(formUpload),
                });
                const data = await r.json();

                if (!data.success) {
                    flashBannerLez('danger', data.error ?? 'Errore durante il caricamento.');
                } else {
                    const ext  = data.ext.toLowerCase();
                    const icon = ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
                    const oggi = new Date().toLocaleDateString('it-IT', { day:'2-digit', month:'2-digit', year:'numeric' });
                    const assetsUrl = '<?= ASSETS_URL ?>';
                    const li = document.createElement('li');
                    li.className = 'list-group-item px-0 py-2 d-flex justify-content-between align-items-center all-item';
                    li.dataset.id = data.id;
                    li.innerHTML = `
                        <a href="${assetsUrl}public/uploads/lezioni/${encodeURIComponent(data.filename)}"
                           target="_blank" class="text-decoration-none" style="color:#0c1a3a;font-size:0.9rem;">
                            <i class="fas ${icon} me-2"></i>
                            ${data.original_name.replace(/</g,'&lt;')}
                            <span class="text-muted ms-2" style="font-size:0.78rem;">${oggi}</span>
                        </a>
                        <button type="button" class="btn btn-link p-0 text-danger" style="font-size:0.8rem;"
                                onclick="apriModalEliminaAllegato(this)"
                                data-id="${data.id}"
                                data-nome="${data.original_name.replace(/"/g,'&quot;')}">
                            <i class="fas fa-trash"></i>
                        </button>`;
                    document.getElementById('lista-allegati').appendChild(li);
                    fileInput.value = '';
                    aggiornaStatoAllegati();
                    flashBannerLez('success', `"${data.original_name}" caricato con successo.`);
                }
            } catch {
                flashBannerLez('danger', 'Errore di rete. Riprova.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = labelOrig;
            }
        });
    }

    /* ── Eliminazione allegato ────────────────────────────────────────────── */
    document.getElementById('btn-conferma-elimina-allegato').addEventListener('click', async function () {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminaAllegato')).hide();
        const lezioneId = <?= (int)$lezione['id'] ?>;
        const body = new URLSearchParams({ allegato_id: _allegIdDaEliminare, lezione_id: lezioneId });
        try {
            const r    = await fetch('<?= BASE_URL ?>percorsi/delete-allegato', {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : body.toString(),
            });
            const data = await r.json();
            if (!data.success) { flashBannerLez('danger', data.error ?? 'Errore durante l\'eliminazione.'); return; }
            document.querySelector(`#lista-allegati .all-item[data-id="${_allegIdDaEliminare}"]`)?.remove();
            aggiornaStatoAllegati();
            flashBannerLez('success', `"${_allegNomeDaEliminare}" eliminato.`);
        } catch {
            flashBannerLez('danger', 'Errore di rete. Riprova.');
        }
    });

    document.getElementById('btn-conferma-rimuovi-lez-ins').addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('modalRimuoviLezIns')).hide();
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = '<?= BASE_URL ?>percorsi/remove-lezione-insegnante';
        f.innerHTML = `<input type="hidden" name="lezione_id" value="${LEZIONE_ID}">
                       <input type="hidden" name="insegnante_id" value="${_lezInsIdDaRimuovere}">`;
        document.body.appendChild(f);
        f.submit();
    });
});
</script>
