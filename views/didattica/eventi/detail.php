<?php
$tipiLabel = [
    'workshop'=>'Workshop','seminario'=>'Seminario','open_day'=>'Open Day',
    'competizione'=>'Competizione','sociale'=>'Festa / Sociale','altro'=>'Altro',
];
$tipiColore = [
    'workshop'=>'#3b82f6','seminario'=>'#8b5cf6','open_day'=>'#f59e0b',
    'competizione'=>'#ef4444','sociale'=>'#ec4899','altro'=>'#64748b',
];
$tipiIcona = [
    'workshop'=>'fa-tools','seminario'=>'fa-chalkboard-teacher','open_day'=>'fa-door-open',
    'competizione'=>'fa-trophy','sociale'=>'fa-glass-cheers','altro'=>'fa-calendar-day',
];
$statiLabel = [
    'aperto'=>'Aperto','chiuso'=>'Iscrizioni chiuse','annullato'=>'Annullato','svolto'=>'Svolto',
];
$statiColore = [
    'aperto'=>'#10b981','chiuso'=>'#64748b','annullato'=>'#ef4444','svolto'=>'#3b82f6',
];

$tipoCol  = $tipiColore[$evento['tipo']]   ?? '#64748b';
$statoCol = $statiColore[$evento['stato']] ?? '#64748b';
$tipoIco  = $tipiIcona[$evento['tipo']]    ?? 'fa-calendar-day';

$isFull = $evento['max_iscritti'] && $evento['num_iscritti'] >= $evento['max_iscritti'];
$perc   = $evento['max_iscritti'] ? round($evento['num_iscritti'] / $evento['max_iscritti'] * 100) : 0;

$oggi   = date('Y-m-d');
$scaduto = $evento['scadenza_iscrizioni'] && $evento['scadenza_iscrizioni'] < $oggi;
$puoIscriversi = $evento['stato'] === 'aperto' && !$isFull && !$scaduto;
?>

<!-- Back -->
<div class="mb-3">
    <a href="<?= BASE_URL ?>eventi" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Tutti gli eventi
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

<!-- Header evento -->
<div class="card border-0 shadow-sm mb-4" style="border-top:4px solid <?= $tipoCol ?> !important;">
    <div class="card-body p-4">
        <div class="d-flex align-items-start gap-2 mb-2 flex-wrap">
            <span class="badge" style="background:<?= $tipoCol ?>1a;color:<?= $tipoCol ?>;border:1px solid <?= $tipoCol ?>33;">
                <i class="fas <?= $tipoIco ?> me-1"></i><?= $tipiLabel[$evento['tipo']] ?>
            </span>
            <span class="badge" style="background:<?= $statoCol ?>1a;color:<?= $statoCol ?>;border:1px solid <?= $statoCol ?>33;">
                <?= $statiLabel[$evento['stato']] ?>
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-auto"
                    data-bs-toggle="modal" data-bs-target="#modalModifica">
                <i class="fas fa-edit me-1"></i>Modifica
            </button>
        </div>
        <h3 class="fw-bold mb-2" style="color:#0c1a3a;"><?= htmlspecialchars($evento['titolo']) ?></h3>
        <?php if ($evento['descrizione']): ?>
        <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($evento['descrizione'])) ?></p>
        <?php endif; ?>

        <div class="row g-3" style="font-size:.9rem;">
            <div class="col-md-3">
                <div class="text-muted small">Data</div>
                <div class="fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-calendar-day me-1 text-muted"></i>
                    <?= date('d/m/Y', strtotime($evento['data_evento'])) ?>
                </div>
            </div>
            <?php if ($evento['ora_inizio']): ?>
            <div class="col-md-3">
                <div class="text-muted small">Orario</div>
                <div class="fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-clock me-1 text-muted"></i>
                    <?= substr($evento['ora_inizio'], 0, 5) ?>
                    <?php if ($evento['ora_fine']): ?> – <?= substr($evento['ora_fine'], 0, 5) ?><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($evento['luogo']): ?>
            <div class="col-md-3">
                <div class="text-muted small">Luogo</div>
                <div class="fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                    <?= htmlspecialchars($evento['luogo']) ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($evento['relatore']): ?>
            <div class="col-md-3">
                <div class="text-muted small">Relatore</div>
                <div class="fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-user-tie me-1 text-muted"></i>
                    <?= htmlspecialchars($evento['relatore']) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($evento['scadenza_iscrizioni']): ?>
        <div class="mt-3 p-2 rounded-3" style="background:<?= $scaduto ? '#fef2f2' : '#fffbeb' ?>;
             border:1px solid <?= $scaduto ? '#fecaca' : '#fde68a' ?>;font-size:.82rem;">
            <i class="fas fa-hourglass-end me-1" style="color:<?= $scaduto ? '#ef4444' : '#f59e0b' ?>;"></i>
            <?= $scaduto ? 'Iscrizioni chiuse il' : 'Iscrizioni aperte fino al' ?>
            <strong><?= date('d/m/Y', strtotime($evento['scadenza_iscrizioni'])) ?></strong>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">

    <!-- Colonna sinistra: capienza + cambio stato -->
    <div class="col-lg-4">

        <!-- Capienza -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-users me-2" style="color:#1e40af;"></i>Capienza
                </h6>
            </div>
            <div class="card-body text-center py-4">
                <div class="display-5 fw-bold mb-1" style="color:<?= $isFull ? '#ef4444' : $tipoCol ?>;">
                    <?= $evento['num_iscritti'] ?>
                    <?php if ($evento['max_iscritti']): ?>
                        <span class="text-muted" style="font-size:1.5rem;">/<?= $evento['max_iscritti'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small mb-3">iscritti<?= $evento['max_iscritti'] ? ' su ' . $evento['max_iscritti'] . ' posti' : '' ?></div>
                <?php if ($evento['max_iscritti']): ?>
                    <div class="progress mb-2" style="height:8px;">
                        <div class="progress-bar" role="progressbar"
                             style="width:<?= min(100, $perc) ?>%;background:<?= $isFull ? '#ef4444' : $tipoCol ?>;"></div>
                    </div>
                    <div class="small fw-semibold" style="color:<?= $isFull ? '#ef4444' : $tipoCol ?>;">
                        <?= $isFull ? 'POSTI ESAURITI' : $perc . '% riempimento' ?>
                    </div>
                <?php else: ?>
                    <div class="badge bg-info-subtle text-info">Posti illimitati</div>
                <?php endif; ?>

                <?php if ($evento['num_iscritti'] > 0): ?>
                <div class="border-top mt-3 pt-3">
                    <div class="text-muted small">Presenti all'evento</div>
                    <div class="fw-bold fs-5" style="color:#10b981;">
                        <?= $evento['num_presenti'] ?> / <?= $evento['num_iscritti'] ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cambio stato -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-toggle-on me-2" style="color:#1e40af;"></i>Stato evento
                </h6>
            </div>
            <div class="card-body">
                <select id="selectStatoEvento" class="form-select form-select-sm mb-2">
                    <?php foreach ($statiLabel as $k => $l): ?>
                        <option value="<?= $k ?>" <?= $evento['stato'] === $k ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="statoFeedback" class="small d-none"></div>
                <div class="text-muted small" id="statoHint">
                    <i class="fas fa-info-circle me-1"></i>
                    Imposta "Iscrizioni chiuse" per bloccare nuove iscrizioni.
                </div>
            </div>
        </div>

        <!-- Iscrivi studente -->
        <?php if ($puoIscriversi && !empty($studentiDisp)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-user-plus me-2" style="color:#1e40af;"></i>Iscrivi studente
                </h6>
            </div>
            <div class="card-body">
                <form id="form-iscrivi-studente" method="POST" action="<?= BASE_URL ?>eventi/iscrivi">
                    <input type="hidden" name="evento_id" value="<?= $evento['id'] ?>">
                    <select id="select-studente-iscrivi" name="studente_id" class="form-select form-select-sm mb-2" required>
                        <option value="">— Seleziona studente —</option>
                        <?php foreach ($studentiDisp as $s): ?>
                            <option value="<?= $s['id'] ?>"
                                    data-cognome="<?= htmlspecialchars($s['cognome'], ENT_QUOTES) ?>"
                                    data-nome="<?= htmlspecialchars($s['nome'], ENT_QUOTES) ?>">
                                <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="iscrivi-feedback" class="small mb-2 d-none"></div>
                    <button type="submit" id="btn-iscrivi-studente" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-plus me-1"></i>Iscrivi
                    </button>
                </form>
            </div>
        </div>
        <?php elseif (!$puoIscriversi): ?>
        <div class="alert alert-warning small">
            <i class="fas fa-lock me-2"></i>
            <?= $isFull       ? 'Posti esauriti.'
              : ($scaduto     ? 'Iscrizioni chiuse per scadenza.'
              : 'Le iscrizioni non sono aperte.') ?>
        </div>
        <?php endif; ?>

        <!-- Elimina -->
        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                data-bs-toggle="modal" data-bs-target="#modalEliminaEvento">
            <i class="fas fa-trash me-2"></i>Elimina evento
        </button>
    </div>

    <!-- Colonna destra: lista iscritti -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-list me-2" style="color:#1e40af;"></i>Iscritti
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;">
                    <?= count($iscrizioni) ?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($iscrizioni)): ?>
                    <div class="text-center text-muted py-5" style="font-size:.9rem;">
                        <i class="fas fa-users mb-2 d-block" style="font-size:2rem;color:#e2e8f0;"></i>
                        Nessun iscritto.<br>
                        <span style="font-size:.78rem;">Iscrivi gli studenti dal pannello a sinistra.</span>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table id="tabella-iscritti" class="table table-hover mb-0" style="font-size:.88rem;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4" style="width:40px;">#</th>
                                <th>Studente</th>
                                <th style="width:140px;">Email</th>
                                <th style="width:90px;" class="text-center">Presente</th>
                                <th style="width:60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($iscrizioni as $i => $iscr): ?>
                            <tr>
                                <td class="ps-4 align-middle text-muted small"><?= $i+1 ?></td>
                                <td class="align-middle">
                                    <div class="fw-semibold" style="color:#0c1a3a;">
                                        <?= htmlspecialchars($iscr['cognome'] . ' ' . $iscr['nome']) ?>
                                    </div>
                                    <?php if ($iscr['codice_fiscale']): ?>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        <?= htmlspecialchars($iscr['codice_fiscale']) ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle text-muted small">
                                    <?= htmlspecialchars($iscr['email'] ?? '—') ?>
                                </td>
                                <td class="align-middle text-center">
                                    <form method="POST" action="<?= BASE_URL ?>eventi/setPresenza">
                                        <input type="hidden" name="iscr_id"   value="<?= $iscr['id'] ?>">
                                        <input type="hidden" name="evento_id" value="<?= $evento['id'] ?>">
                                        <input type="hidden" name="presente"  value="<?= $iscr['presente'] ? 0 : 1 ?>">
                                        <button type="submit"
                                                class="btn btn-sm <?= $iscr['presente'] ? 'btn-success' : 'btn-outline-secondary' ?>"
                                                style="min-width:36px;">
                                            <i class="fas <?= $iscr['presente'] ? 'fa-check' : 'fa-minus' ?>"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="align-middle text-end pe-3">
                                    <button type="button"
                                            class="btn btn-sm btn-link text-danger p-0 btn-rimuovi-iscritto"
                                            title="Rimuovi iscritto"
                                            data-iscr-id="<?= $iscr['id'] ?>"
                                            data-evento-id="<?= $evento['id'] ?>"
                                            data-nome="<?= htmlspecialchars($iscr['cognome'] . ' ' . $iscr['nome'], ENT_QUOTES) ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── Modal Elimina Evento ── -->
<div class="modal fade" id="modalEliminaEvento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina evento
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-1" style="color:#374151;">
                    Stai per eliminare l'evento
                    <strong><?= htmlspecialchars($evento['titolo']) ?></strong>.
                </p>
                <p class="text-muted small mb-0">Verranno eliminate anche tutte le iscrizioni collegate. L'operazione è irreversibile.</p>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" action="<?= BASE_URL ?>eventi/delete" class="d-inline">
                    <input type="hidden" name="id" value="<?= $evento['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Modifica Evento ── -->
<div class="modal fade" id="modalModifica" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>eventi/update">
                <input type="hidden" name="id" value="<?= $evento['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2 text-primary"></i>Modifica evento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Titolo <span class="text-danger">*</span></label>
                            <input type="text" name="titolo" class="form-control form-control-sm" required maxlength="255"
                                   value="<?= htmlspecialchars($evento['titolo']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tipo</label>
                            <select name="tipo" class="form-select form-select-sm">
                                <?php foreach ($tipiLabel as $k => $l): ?>
                                    <option value="<?= $k ?>" <?= $evento['tipo'] === $k ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Descrizione</label>
                            <textarea name="descrizione" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($evento['descrizione'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Data <span class="text-danger">*</span></label>
                            <input type="date" name="data_evento" class="form-control form-control-sm" required
                                   value="<?= $evento['data_evento'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Ora inizio</label>
                            <input type="time" name="ora_inizio" class="form-control form-control-sm"
                                   value="<?= $evento['ora_inizio'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Ora fine</label>
                            <input type="time" name="ora_fine" class="form-control form-control-sm"
                                   value="<?= $evento['ora_fine'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Luogo</label>
                            <input type="text" name="luogo" class="form-control form-control-sm" maxlength="255"
                                   value="<?= htmlspecialchars($evento['luogo'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Relatore</label>
                            <input type="text" name="relatore" class="form-control form-control-sm" maxlength="255"
                                   value="<?= htmlspecialchars($evento['relatore'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Posti massimi</label>
                            <input type="number" name="max_iscritti" class="form-control form-control-sm" min="1"
                                   value="<?= $evento['max_iscritti'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Scadenza iscrizioni</label>
                            <input type="date" name="scadenza_iscrizioni" class="form-control form-control-sm"
                                   value="<?= $evento['scadenza_iscrizioni'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Stato</label>
                            <select name="stato" class="form-select form-select-sm">
                                <?php foreach ($statiLabel as $k => $l): ?>
                                    <option value="<?= $k ?>" <?= $evento['stato'] === $k ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Salva modifiche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Rimuovi Iscritto ── -->
<div class="modal fade" id="modalRimuoviIscritto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-user-minus me-2 text-danger"></i>Rimuovi iscritto
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0" style="color:#374151;">
                    Vuoi disiscrivere <strong id="nomeIscrittoDaRimuovere"></strong> da questo evento?
                </p>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-rimuovi-iscritto">
                    <i class="fas fa-user-minus me-1"></i>Rimuovi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const statiColore = <?= json_encode($statiColore) ?>;
    const statiLabel  = <?= json_encode($statiLabel)  ?>;

    const select   = document.getElementById('selectStatoEvento');
    const feedback = document.getElementById('statoFeedback');
    const hint     = document.getElementById('statoHint');

    // Badge stato nell'header evento
    const badgeStato = document.querySelector('.badge[style*="<?= $statiColore[$evento['stato']] ?? '#64748b' ?>"]');

    function showFeedback(ok, msg) {
        feedback.className = 'small ' + (ok ? 'text-success' : 'text-danger');
        feedback.innerHTML = '<i class="fas fa-' + (ok ? 'check-circle' : 'exclamation-circle') + ' me-1"></i>' + msg;
        feedback.classList.remove('d-none');
        hint.classList.add('d-none');
        setTimeout(() => {
            feedback.classList.add('d-none');
            hint.classList.remove('d-none');
        }, 3000);
    }

    select.addEventListener('change', async function () {
        const nuovoStato = this.value;
        const origStato  = this.dataset.prev ?? '<?= $evento['stato'] ?>';
        this.dataset.prev = nuovoStato;
        this.disabled = true;

        const fd = new FormData();
        fd.append('id',    '<?= $evento['id'] ?>');
        fd.append('stato', nuovoStato);

        try {
            const r    = await fetch('<?= BASE_URL ?>eventi/setStato', {
                method : 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body   : fd,
            });
            const data = await r.json();

            if (data.success) {
                // Aggiorna il badge stato nell'header
                const col = statiColore[nuovoStato] ?? '#64748b';
                const lbl = statiLabel[nuovoStato]  ?? nuovoStato;
                document.querySelectorAll('.badge').forEach(b => {
                    if (b.dataset.statoTarget !== undefined || Object.values(statiLabel).some(l => b.textContent.trim() === l)) {
                        if (Object.values(statiLabel).some(l => b.textContent.trim() === l)) {
                            b.textContent = lbl;
                            b.style.background = col + '1a';
                            b.style.color       = col;
                            b.style.border      = '1px solid ' + col + '33';
                        }
                    }
                });
                showFeedback(true, 'Stato aggiornato.');
            } else {
                this.value = origStato;
                this.dataset.prev = origStato;
                showFeedback(false, 'Errore nel salvataggio.');
            }
        } catch {
            this.value = origStato;
            this.dataset.prev = origStato;
            showFeedback(false, 'Errore di rete. Riprova.');
        } finally {
            this.disabled = false;
        }
    });
})();

/* ── Iscrivi studente ── */
(function () {
    const form     = document.getElementById('form-iscrivi-studente');
    if (!form) return;
    const select   = document.getElementById('select-studente-iscrivi');
    const btn      = document.getElementById('btn-iscrivi-studente');
    const feedback = document.getElementById('iscrivi-feedback');
    const tbody    = document.querySelector('#tabella-iscritti tbody');
    const badgeCount = document.querySelector('.card-header .badge[style*="e8eef8"]');

    // Contenitore "nessun iscritto" (se presente)
    const emptyMsg = document.querySelector('#tabella-iscritti')?.closest('.card-body')?.querySelector('.text-center.text-muted');

    function showFeedback(ok, msg) {
        feedback.className = 'small mb-2 ' + (ok ? 'text-success' : 'text-danger');
        feedback.innerHTML = '<i class="fas fa-' + (ok ? 'check-circle' : 'exclamation-circle') + ' me-1"></i>' + msg;
        feedback.classList.remove('d-none');
        if (ok) setTimeout(() => feedback.classList.add('d-none'), 3000);
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!select.value) return;

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Iscrivo…';

        try {
            const r    = await fetch('<?= BASE_URL ?>eventi/iscrivi', {
                method : 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body   : new FormData(this),
            });
            const data = await r.json();

            if (!data.success) {
                showFeedback(false, data.error ?? 'Errore.');
                return;
            }

            // Rimuovi lo studente dalla select
            const opt = select.querySelector(`option[value="${data.studente_id}"]`);
            opt?.remove();
            select.value = '';

            // Nascondi messaggio "nessun iscritto" se visibile
            emptyMsg?.closest('.text-center')?.classList?.add('d-none');

            // Aggiorna badge contatore
            if (badgeCount) badgeCount.textContent = parseInt(badgeCount.textContent || '0') + 1;

            // Inserisci nuova riga in tabella
            if (tbody) {
                const rowNum = tbody.querySelectorAll('tr').length + 1;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-4 align-middle text-muted small">${rowNum}</td>
                    <td class="align-middle">
                        <div class="fw-semibold" style="color:#0c1a3a;">${data.cognome} ${data.nome}</div>
                        ${data.codice_fiscale ? `<div class="text-muted" style="font-size:.72rem;">${data.codice_fiscale}</div>` : ''}
                    </td>
                    <td class="align-middle text-muted small">${data.email || '—'}</td>
                    <td class="align-middle text-center">
                        <form method="POST" action="<?= BASE_URL ?>eventi/setPresenza">
                            <input type="hidden" name="iscr_id"   value="${data.iscr_id}">
                            <input type="hidden" name="evento_id" value="<?= $evento['id'] ?>">
                            <input type="hidden" name="presente"  value="1">
                            <button type="submit" class="btn btn-sm btn-outline-secondary" style="min-width:36px;">
                                <i class="fas fa-minus"></i>
                            </button>
                        </form>
                    </td>
                    <td class="align-middle text-end pe-3">
                        <button type="button"
                                class="btn btn-sm btn-link text-danger p-0 btn-rimuovi-iscritto"
                                title="Rimuovi iscritto"
                                data-iscr-id="${data.iscr_id}"
                                data-evento-id="<?= $evento['id'] ?>"
                                data-nome="${data.cognome} ${data.nome}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>`;
                tbody.appendChild(tr);
            }

            showFeedback(true, data.cognome + ' ' + data.nome + ' iscritto/a.');

        } catch {
            showFeedback(false, 'Errore di rete. Riprova.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    });
})();

/* ── Rimuovi iscritto ── */
(function () {
    let _iscrId    = null;
    let _eventoId  = null;
    let _rigaDaRimuovere = null;

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-rimuovi-iscritto');
        if (!btn) return;
        _iscrId          = btn.dataset.iscrId;
        _eventoId        = btn.dataset.eventoId;
        _rigaDaRimuovere = btn.closest('tr');
        document.getElementById('nomeIscrittoDaRimuovere').textContent = btn.dataset.nome;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRimuoviIscritto')).show();
    });

    document.getElementById('btn-conferma-rimuovi-iscritto').addEventListener('click', async function () {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalRimuoviIscritto'));
        modal.hide();

        const fd = new FormData();
        fd.append('iscr_id',   _iscrId);
        fd.append('evento_id', _eventoId);

        try {
            const r    = await fetch('<?= BASE_URL ?>eventi/disiscrivi', {
                method : 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body   : fd,
            });
            const data = await r.json();
            if (data.success) {
                _rigaDaRimuovere?.remove();
                // Aggiorna contatore badge iscritti
                const badge = document.querySelector('.card-header .badge[style*="e8eef8"]');
                if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent) - 1);
            } else {
                alert(data.error ?? 'Errore nella rimozione.');
            }
        } catch {
            alert('Errore di rete. Riprova.');
        }
    });
})();
</script>
