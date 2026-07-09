<?php
$ordinali = [1=>'Primo Anno',2=>'Secondo Anno',3=>'Terzo Anno',4=>'Quarto Anno',5=>'Quinto Anno',
             6=>'Sesto Anno',7=>'Settimo Anno',8=>'Ottavo Anno',9=>'Nono Anno',10=>'Decimo Anno'];
$nomeAnno = $ordinali[$pam['anno_numero']] ?? $pam['anno_numero'] . '° Anno';
?>

<!-- Breadcrumb -->
<?php $isDocente = ($_SESSION['user_ruolo'] ?? '') === 'docente'; ?>
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <?php if ($isDocente): ?>
    <a href="<?= BASE_URL ?>mie-materie" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Le mie materie
    </a>
    <span class="text-muted">/</span>
    <?php else: ?>
    <a href="<?= BASE_URL ?>percorsi" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Percorsi
    </a>
    <span class="text-muted">/</span>
    <a href="<?= BASE_URL ?>percorsi/detail/<?= $pam['percorso_id'] ?>" class="text-decoration-none fw-semibold" style="color:#1e40af;">
        <?= htmlspecialchars($pam['percorso_nome']) ?>
    </a>
    <span class="text-muted">/</span>
    <a href="<?= BASE_URL ?>percorsi/anno/<?= $pam['anno_id'] ?>" class="text-decoration-none fw-semibold" style="color:#1e40af;">
        <?= $nomeAnno ?>
    </a>
    <span class="text-muted">/</span>
    <?php endif; ?>
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;"><?= htmlspecialchars($pam['materia_nome']) ?></h4>
    <span class="badge ms-1" style="background:#e8eef8;color:#1e40af;font-size:0.8rem;">
        A.S. <?= htmlspecialchars($pam['anno_label']) ?>
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

<style>
/* ── Sezioni materia ── */
.ins-card {
    background:#fff;
    border:1px solid #e2e8f0;
    transition:box-shadow .18s ease, transform .18s ease, border-color .18s ease;
}
.ins-card:hover {
    border-color:#c7d2fe;
    box-shadow:0 6px 18px rgba(30,64,175,.10);
    transform:translateY(-2px);
}
.ins-avatar {
    width:44px;height:44px;min-width:44px;
    border-radius:50%;
    background:linear-gradient(135deg,#1e40af,#3b82f6);
    color:#fff;font-weight:700;font-size:.95rem;letter-spacing:.03em;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 2px 6px rgba(30,64,175,.25);
}
.btn-remove-item {
    border:none;background:transparent;color:#cbd5e1;
    width:26px;height:26px;border-radius:50%;line-height:1;
    display:flex;align-items:center;justify-content:center;
    transition:background .15s ease,color .15s ease;
}
.btn-remove-item:hover { background:#fee2e2;color:#dc2626; }
.programma-list { counter-reset:arg; }
.programma-list .arg-item { counter-increment:arg; }
.arg-card {
    background:#f8fafc;
    border:1px solid #e2e8f0;
    transition:box-shadow .18s ease, border-color .18s ease;
}
.arg-card:hover { border-color:#c7d2fe; box-shadow:0 3px 10px rgba(30,64,175,.08); }
.arg-num::before { content:counter(arg); }
.arg-num {
    width:28px;height:28px;min-width:28px;
    border-radius:50%;
    background:#e8eef8;color:#1e40af;
    font-weight:700;font-size:.8rem;
    display:flex;align-items:center;justify-content:center;
}
.section-empty { text-align:center;padding:1.5rem 0;color:#94a3b8; }
.section-empty i { font-size:1.8rem;color:#cbd5e1;display:block;margin-bottom:.5rem; }
</style>

<!-- ── Sezione Insegnanti ── -->
<div class="card border-0 shadow-sm mb-4" id="sezione-insegnanti">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-chalkboard-teacher me-2" style="color:#1e40af;"></i>Insegnanti
            <span id="badge-insegnanti" class="badge ms-2" style="background:#e8eef8;color:#1e40af;"><?= count($insegnanti) ?></span>
        </h6>
        <?php if (!empty($insegnantiDisponibili)): ?>
            <form id="form-add-insegnante" method="POST" action="<?= BASE_URL ?>percorsi/add-insegnante" class="mb-0">
                <input type="hidden" name="pam_id" value="<?= $pam['id'] ?>">
                <div class="input-group input-group-sm" style="max-width:320px;">
                    <select name="insegnante_id" class="form-select" required>
                        <option value="">— Aggiungi insegnante —</option>
                        <?php foreach ($insegnantiDisponibili as $ins): ?>
                            <option value="<?= $ins['id'] ?>">
                                <?= htmlspecialchars($ins['cognome'] . ' ' . $ins['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <p id="tutti-assegnati" class="text-muted small fst-italic mb-0">Tutti gli insegnanti sono già assegnati.</p>
        <?php endif; ?>
    </div>
    <div class="card-body px-4 py-4">
        <div id="empty-insegnanti" class="section-empty" <?= !empty($insegnanti) ? 'style="display:none;"' : '' ?>>
            <i class="fas fa-user-slash"></i>
            Nessun insegnante assegnato a questa materia.
        </div>
        <div id="lista-insegnanti" class="row g-3">
            <?php foreach ($insegnanti as $ins):
                $iniziali = mb_strtoupper(mb_substr($ins['nome'], 0, 1) . mb_substr($ins['cognome'], 0, 1));
            ?>
                <div class="col-xl-3 col-lg-4 col-sm-6 ins-item" data-id="<?= $ins['id'] ?>">
                    <div class="ins-card d-flex align-items-center gap-3 p-3 rounded-3 h-100">
                        <div class="ins-avatar"><?= htmlspecialchars($iniziali) ?></div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="fw-semibold text-truncate" style="color:#0c1a3a;font-size:.92rem;">
                                <?= htmlspecialchars($ins['cognome'] . ' ' . $ins['nome']) ?>
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">
                                <i class="fas fa-user-tie me-1"></i>Insegnante
                            </div>
                        </div>
                        <button type="button" class="btn-remove-item" title="Rimuovi"
                                onclick="apriModalRimuoviInsegnante(this)"
                                data-id="<?= $ins['id'] ?>"
                                data-nome="<?= htmlspecialchars($ins['cognome'] . ' ' . $ins['nome'], ENT_QUOTES) ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Sezione Programma ── -->
<div class="card border-0 shadow-sm mb-4" id="sezione-programma">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-list-ul me-2" style="color:#1e40af;"></i>Programma
            <span id="badge-argomenti" class="badge ms-2" style="background:#e8eef8;color:#1e40af;"><?= count($argomenti) ?></span>
        </h6>
        <form id="form-add-argomento" method="POST" action="<?= BASE_URL ?>percorsi/add-argomento" class="mb-0">
            <input type="hidden" name="pam_id" value="<?= $pam['id'] ?>">
            <div class="input-group input-group-sm" style="max-width:320px;">
                <input type="text" name="titolo" class="form-control" placeholder="Nuovo argomento..." required maxlength="255">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </form>
    </div>
    <div class="card-body px-4 py-4">
        <div id="empty-argomenti" class="section-empty" <?= !empty($argomenti) ? 'style="display:none;"' : '' ?>>
            <i class="fas fa-clipboard-list"></i>
            Nessun argomento nel programma. Aggiungi il primo dall'header qui sopra.
        </div>
        <ol id="lista-argomenti" class="row g-2 list-unstyled mb-0 programma-list">
            <?php foreach ($argomenti as $arg): ?>
                <li class="col-md-6 arg-item" data-id="<?= $arg['id'] ?>">
                    <div class="arg-card d-flex align-items-center gap-3 px-3 py-2 rounded-3 h-100">
                        <span class="arg-num"></span>
                        <span class="flex-grow-1" style="color:#0c1a3a;font-size:.9rem;"><?= htmlspecialchars($arg['titolo']) ?></span>
                        <button type="button" class="btn-remove-item" title="Rimuovi"
                                onclick="eliminaArgomento(this)"
                                data-id="<?= $arg['id'] ?>"
                                data-titolo="<?= htmlspecialchars($arg['titolo'], ENT_QUOTES) ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>

<!-- ── Sezione Lezioni ── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-book-open me-2" style="color:#1e40af;"></i>Lezioni
            <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;"><?= count($lezioni) ?></span>
        </h6>
        <button class="btn btn-sm btn-primary px-3" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseNuovaLezione"
                aria-expanded="<?= empty($lezioni) ? 'true' : 'false' ?>" aria-controls="collapseNuovaLezione">
            <i class="fas fa-plus me-1"></i>Nuova lezione
        </button>
    </div>

    <!-- Form nuova lezione (collassabile) -->
    <div class="collapse<?= empty($lezioni) ? ' show' : '' ?>" id="collapseNuovaLezione">
    <div class="card-body border-bottom" style="background:#fafbff;">
                <form method="POST" action="<?= BASE_URL ?>percorsi/add-lezione">
                    <input type="hidden" name="pam_id" value="<?= $pam['id'] ?>">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Titolo <span class="text-danger">*</span></label>
                            <input type="text" name="titolo" class="form-control form-control-sm"
                                   placeholder="es. Introduzione agli shampoo" required maxlength="255">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Data</label>
                            <input type="date" name="data" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Ora inizio</label>
                            <input type="time" name="ora_inizio" id="oraInizioNew" class="form-control form-control-sm"
                                   oninput="calcDurataNew()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Ora fine</label>
                            <input type="time" name="ora_fine" id="oraFineNew" class="form-control form-control-sm"
                                   oninput="calcDurataNew()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Durata (ore)</label>
                            <input type="number" name="durata_ore" id="durataNew" class="form-control form-control-sm"
                                   placeholder="3" min="0.5" max="12" step="0.5">
                        </div>
                        <div class="col-12 d-flex align-items-center gap-2">
                            <span class="small fw-semibold text-muted">Online</span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="online" id="chkOnlineNew"
                                       style="width:2.2em;height:1.2em;cursor:pointer;"
                                       onchange="document.getElementById('linkOnlineWrapNew').style.display=this.checked?'block':'none'">
                            </div>
                        </div>
                        <div class="col-12" id="linkOnlineWrapNew" style="display:none;">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="fas fa-link me-1" style="color:#3b82f6;"></i>Link videolezione
                            </label>
                            <input type="url" name="link_online" class="form-control form-control-sm"
                                   placeholder="https://meet.google.com/..., https://classroom.google.com/...">
                            <div class="text-muted" style="font-size:.7rem;">
                                Google Meet, Google Classroom, Zoom, Teams, ecc.
                            </div>
                        </div>
                        <?php if (!empty($insegnanti)): ?>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">Insegnante</label>
                            <select name="insegnante_id" class="form-select form-select-sm">
                                <option value="">— Nessuno —</option>
                                <?php foreach ($insegnanti as $ins): ?>
                                    <option value="<?= $ins['id'] ?>">
                                        <?= htmlspecialchars($ins['cognome'] . ' ' . $ins['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-<?= !empty($insegnanti) ? '6' : '12' ?>">
                            <label class="form-label small fw-semibold mb-1">Note</label>
                            <input type="text" name="note" class="form-control form-control-sm"
                                   placeholder="Note (opzionale)" maxlength="500">
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Aggiungi lezione
                            </button>
                        </div>
                    </div>
                </form>
            </div>
    </div>

    <!-- Lista lezioni -->
            <div class="card-body p-0">
                <?php if (empty($lezioni)): ?>
                    <div class="empty-state" style="padding:2rem 0;">
                        <div class="empty-state-icon"><i class="fas fa-book-open"></i></div>
                        <h5>Nessuna lezione</h5>
                        <p>Aggiungi la prima lezione dal form qui sopra.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4">Titolo</th>
                                <th style="width:110px;">Data</th>
                                <th style="width:120px;">Orario</th>
                                <th style="width:80px;">Durata</th>
                                <th>Insegnanti</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lezioni as $lez):
                                $insLez = $lezioniInsegnanti[$lez['id']] ?? [];
                                $isOnline = (int)($lez['online'] ?? 0);
                            ?>
                                <tr style="cursor:pointer;" onclick="window.location='<?= BASE_URL ?>percorsi/lezione/<?= $lez['id'] ?>'">
                                    <td class="ps-4 align-middle" style="color:#0c1a3a;">
                                        <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap">
                                            <?= htmlspecialchars($lez['titolo']) ?>
                                            <?php if ($isOnline): ?>
                                                <span class="badge bg-success" style="font-size:0.7rem;">
                                                    <i class="fas fa-globe me-1"></i>Online
                                                </span>
                                                <?php if (!empty($lez['link_online'])): ?>
                                                <a href="<?= htmlspecialchars($lez['link_online']) ?>" target="_blank" rel="noopener"
                                                   onclick="event.stopPropagation()"
                                                   class="badge text-decoration-none"
                                                   style="background:#3b82f61a;color:#3b82f6;border:1px solid #3b82f633;font-size:.7rem;">
                                                    <i class="fas fa-external-link-alt me-1"></i>Apri link
                                                </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($lez['note']): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($lez['note']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-muted small">
                                        <?= $lez['data'] ? date('d/m/Y', strtotime($lez['data'])) : '—' ?>
                                    </td>
                                    <td class="align-middle text-muted small">
                                        <?php
                                        $oi = !empty($lez['ora_inizio']) ? substr($lez['ora_inizio'], 0, 5) : '';
                                        $of = !empty($lez['ora_fine'])   ? substr($lez['ora_fine'], 0, 5)   : '';
                                        if ($oi && $of)      echo '<i class="far fa-clock me-1"></i>' . htmlspecialchars($oi . '–' . $of);
                                        elseif ($oi)         echo '<i class="far fa-clock me-1"></i>' . htmlspecialchars($oi);
                                        else                 echo '—';
                                        ?>
                                    </td>
                                    <td class="align-middle text-muted small">
                                        <?php if ($lez['durata_minuti']): $d = (int)$lez['durata_minuti'];
                                            echo ($d % 60 === 0) ? ($d/60).'h' : floor($d/60).'h '.($d%60).'m';
                                        else: echo '—'; endif; ?>
                                    </td>
                                    <td class="align-middle" style="font-size:0.85rem;color:#374151;">
                                        <?php if (empty($insLez)): ?>
                                            <span class="text-muted fst-italic">—</span>
                                        <?php else: ?>
                                            <?= htmlspecialchars(implode(', ', $insLez)) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle pe-3" onclick="event.stopPropagation()">
                                        <form method="POST" action="<?= BASE_URL ?>percorsi/delete-lezione">
                                            <input type="hidden" name="id" value="<?= $lez['id'] ?>">
                                            <input type="hidden" name="pam_id" value="<?= $pam['id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="apriModalEliminaLezione(this)"
                                                    data-titolo="<?= htmlspecialchars($lez['titolo'], ENT_QUOTES) ?>">
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

<!-- Modal conferma eliminazione lezione -->
<div class="modal fade" id="modalEliminaLezione" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina lezione
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi eliminare la lezione <strong id="modal-lezione-titolo"></strong>?
                <br><span class="text-muted small">L'operazione non può essere annullata.</span>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-lezione">
                    <i class="fas fa-trash me-1"></i>Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal conferma eliminazione argomento -->
<div class="modal fade" id="modalEliminaArgomento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Rimuovi argomento
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi rimuovere <strong id="modal-arg-titolo"></strong> dal programma?
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-arg">
                    <i class="fas fa-trash me-1"></i>Rimuovi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal conferma rimozione insegnante -->
<div class="modal fade" id="modalRimuoviInsegnante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-user-times me-2 text-danger"></i>Rimuovi insegnante
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                Vuoi rimuovere <strong id="modal-ins-nome"></strong> da questa materia?
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-rimuovi-ins">
                    <i class="fas fa-user-times me-1"></i>Rimuovi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/* ── Calcola in automatico la durata (ore) da ora inizio/fine ─────────────── */
function calcDurataNew() {
    var i = document.getElementById('oraInizioNew');
    var f = document.getElementById('oraFineNew');
    var d = document.getElementById('durataNew');
    if (!i || !f || !d || !i.value || !f.value) return;
    var a = i.value.split(':'), b = f.value.split(':');
    var mins = (parseInt(b[0]) * 60 + parseInt(b[1])) - (parseInt(a[0]) * 60 + parseInt(a[1]));
    if (mins < 0) mins += 24 * 60; // gestisce l'orario che supera la mezzanotte
    d.value = Math.round((mins / 60) * 100) / 100;
}

/* ── helpers UI ────────────────────────────────────────────────────────── */
function flashBannerMateria(type, msg) {
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const div  = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show`;
    div.setAttribute('role', 'alert');
    div.innerHTML = `<i class="fas ${icon} me-2"></i>${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>`;
    const anchor = document.getElementById('sezione-insegnanti') ?? document.body;
    anchor.parentNode.insertBefore(div, anchor);
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(div).close(), 4000);
}

/* ── stato rimozione insegnante ────────────────────────────────────────── */
let _insIdDaRimuovere  = null;
let _insNomeDaRimuovere = '';
const PAM_ID = <?= (int)$pam['id'] ?>;

function apriModalRimuoviInsegnante(btn) {
    _insIdDaRimuovere  = parseInt(btn.dataset.id);
    _insNomeDaRimuovere = btn.dataset.nome ?? '';
    document.getElementById('modal-ins-nome').textContent = _insNomeDaRimuovere;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRimuoviInsegnante')).show();
}

/* ── stato eliminazione lezione ────────────────────────────────────────── */
let _formLezione = null;

function apriModalEliminaLezione(btn) {
    _formLezione = btn.closest('form');
    document.getElementById('modal-lezione-titolo').textContent = btn.dataset.titolo ?? '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaLezione')).show();
}

/* ── stato eliminazione argomento ───────────────────────────────────────── */
let _argIdDaEliminare    = null;
let _argTitoloDaEliminare = '';

function eliminaArgomento(btn) {
    _argIdDaEliminare     = parseInt(btn.dataset.id);
    _argTitoloDaEliminare = btn.dataset.titolo ?? '';
    document.getElementById('modal-arg-titolo').textContent = _argTitoloDaEliminare;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaArgomento')).show();
}

function aggiornaStatoArgomenti() {
    const items = document.querySelectorAll('#lista-argomenti .arg-item');
    document.getElementById('badge-argomenti').textContent = items.length;
    const empty = document.getElementById('empty-argomenti');
    if (empty) empty.style.display = items.length === 0 ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {

    /* ── Eliminazione lezione ────────────────────────────────────────────── */
    document.getElementById('btn-conferma-elimina-lezione').addEventListener('click', function () {
        if (_formLezione) {
            bootstrap.Modal.getInstance(document.getElementById('modalEliminaLezione')).hide();
            _formLezione.submit();
        }
    });

    /* ── Eliminazione argomento ───────────────────────────────────────── */
    document.getElementById('btn-conferma-elimina-arg').addEventListener('click', async function () {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminaArgomento')).hide();
        const body = new URLSearchParams({ id: _argIdDaEliminare, pam_id: PAM_ID });
        try {
            const r    = await fetch('<?= BASE_URL ?>percorsi/delete-argomento', {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : body.toString(),
            });
            const data = await r.json();
            if (!data.success) { flashBannerMateria('danger', data.error ?? 'Errore durante la rimozione.'); return; }
            document.querySelector(`#lista-argomenti .arg-item[data-id="${_argIdDaEliminare}"]`)?.remove();
            aggiornaStatoArgomenti();
            flashBannerMateria('success', `"${_argTitoloDaEliminare}" rimosso dal programma.`);
        } catch {
            flashBannerMateria('danger', 'Errore di rete. Riprova.');
        }
    });

    /* ── Aggiunta argomento ───────────────────────────────────────────── */
    const formArg = document.getElementById('form-add-argomento');
    if (formArg) {
        formArg.addEventListener('submit', async function (e) {
            e.preventDefault();
            const input  = formArg.querySelector('input[name="titolo"]');
            const titolo = input.value.trim();
            if (!titolo) return;
            const btn = formArg.querySelector('button[type="submit"]');
            btn.disabled = true;

            const body = new URLSearchParams({ pam_id: PAM_ID, titolo });
            try {
                const r    = await fetch('<?= BASE_URL ?>percorsi/add-argomento', {
                    method  : 'POST',
                    headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body    : body.toString(),
                });
                const data = await r.json();
                if (!data.success) { flashBannerMateria('danger', data.error ?? 'Errore durante l\'aggiunta.'); return; }

                const lista = document.getElementById('lista-argomenti');
                const li    = document.createElement('li');
                li.className = 'col-md-6 arg-item';
                li.dataset.id = data.id;
                li.innerHTML = `
                    <div class="arg-card d-flex align-items-center gap-3 px-3 py-2 rounded-3 h-100">
                        <span class="arg-num"></span>
                        <span class="flex-grow-1" style="color:#0c1a3a;font-size:.9rem;">${data.titolo.replace(/</g,'&lt;')}</span>
                        <button type="button" class="btn-remove-item" title="Rimuovi"
                                onclick="eliminaArgomento(this)"
                                data-id="${data.id}"
                                data-titolo="${data.titolo.replace(/"/g,'&quot;')}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>`;
                lista.appendChild(li);

                input.value = '';
                aggiornaStatoArgomenti();
                flashBannerMateria('success', `"${data.titolo}" aggiunto al programma.`);
            } catch {
                flashBannerMateria('danger', 'Errore di rete. Riprova.');
            } finally {
                btn.disabled = false;
            }
        });
    }

    /* ── Rimozione insegnante ─────────────────────────────────────────── */
    document.getElementById('btn-conferma-rimuovi-ins').addEventListener('click', async function () {
        bootstrap.Modal.getInstance(document.getElementById('modalRimuoviInsegnante')).hide();

        const body = new URLSearchParams({ pam_id: PAM_ID, insegnante_id: _insIdDaRimuovere });
        try {
            const r    = await fetch('<?= BASE_URL ?>percorsi/remove-insegnante', {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : body.toString(),
            });
            const data = await r.json();
            if (!data.success) { flashBannerMateria('danger', data.error ?? 'Errore durante la rimozione.'); return; }

            // Rimuove la riga dalla lista
            document.querySelector(`#lista-insegnanti .ins-item[data-id="${_insIdDaRimuovere}"]`)?.remove();

            // Restitituisce l'opzione nella select, se esiste
            const sel = document.querySelector('#form-add-insegnante select[name="insegnante_id"]');
            if (sel) {
                const opt       = document.createElement('option');
                opt.value       = _insIdDaRimuovere;
                opt.textContent = _insNomeDaRimuovere;
                sel.appendChild(opt);
                // Nasconde "tutti assegnati" se era visibile
                const tuttiMsg = document.getElementById('tutti-assegnati');
                if (tuttiMsg) tuttiMsg.style.display = 'none';
            }

            // Aggiorna badge e messaggio vuoto
            aggiornaStatoInsegnanti();
            flashBannerMateria('success', `${_insNomeDaRimuovere} rimosso dalla materia.`);
        } catch {
            flashBannerMateria('danger', 'Errore di rete. Riprova.');
        }
    });

    /* ── Aggiunta insegnante ──────────────────────────────────────────── */
    const addForm = document.getElementById('form-add-insegnante');
    if (!addForm) return;

    addForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const sel          = addForm.querySelector('select[name="insegnante_id"]');
        const insegnanteId = parseInt(sel.value);
        if (!insegnanteId) return;

        const nomeSel = sel.options[sel.selectedIndex]?.text?.trim() ?? '';
        const body    = new URLSearchParams({ pam_id: PAM_ID, insegnante_id: insegnanteId });

        try {
            const r    = await fetch('<?= BASE_URL ?>percorsi/add-insegnante', {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : body.toString(),
            });
            const data = await r.json();
            if (!data.success) { flashBannerMateria('danger', data.error ?? 'Errore durante l\'aggiunta.'); return; }

            const nomeTxt  = `${data.cognome} ${data.nome}`;
            const iniziali = ((data.nome?.[0] ?? '') + (data.cognome?.[0] ?? '')).toUpperCase();

            // Aggiunge la card alla griglia
            const lista = document.getElementById('lista-insegnanti');
            const col   = document.createElement('div');
            col.className = 'col-xl-3 col-lg-4 col-sm-6 ins-item';
            col.dataset.id = data.id;
            col.innerHTML = `
                <div class="ins-card d-flex align-items-center gap-3 p-3 rounded-3 h-100">
                    <div class="ins-avatar">${iniziali}</div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold text-truncate" style="color:#0c1a3a;font-size:.92rem;">${nomeTxt}</div>
                        <div class="text-muted" style="font-size:.75rem;">
                            <i class="fas fa-user-tie me-1"></i>Insegnante
                        </div>
                    </div>
                    <button type="button" class="btn-remove-item" title="Rimuovi"
                            onclick="apriModalRimuoviInsegnante(this)"
                            data-id="${data.id}"
                            data-nome="${nomeTxt}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
            lista.appendChild(col);

            // Rimuove l'opzione dalla select
            sel.querySelector(`option[value="${insegnanteId}"]`)?.remove();
            sel.value = '';

            // Se non ci sono più opzioni (oltre il placeholder), mostra "tutti assegnati"
            if (sel.options.length <= 1) {
                addForm.style.display = 'none';
                let tuttiMsg = document.getElementById('tutti-assegnati');
                if (!tuttiMsg) {
                    tuttiMsg = document.createElement('p');
                    tuttiMsg.id = 'tutti-assegnati';
                    tuttiMsg.className = 'text-muted small fst-italic mb-0';
                    tuttiMsg.textContent = 'Tutti gli insegnanti sono già assegnati.';
                    addForm.parentNode.insertBefore(tuttiMsg, addForm.nextSibling);
                } else {
                    tuttiMsg.style.display = '';
                }
            }

            aggiornaStatoInsegnanti();
            flashBannerMateria('success', `${nomeTxt} aggiunto alla materia.`);
        } catch {
            flashBannerMateria('danger', 'Errore di rete. Riprova.');
        }
    });
});

function aggiornaStatoInsegnanti() {
    const items  = document.querySelectorAll('#lista-insegnanti .ins-item');
    document.getElementById('badge-insegnanti').textContent = items.length;
    const empty  = document.getElementById('empty-insegnanti');
    if (empty) empty.style.display = items.length === 0 ? '' : 'none';
}
</script>
