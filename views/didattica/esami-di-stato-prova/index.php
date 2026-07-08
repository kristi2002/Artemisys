<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

$mappaAnni = [];
foreach ($percorsi as $p) {
    $mappaAnni[$p['id']] = array_map(fn($a) => [
        'anno_id' => $a['id'],
        'numero'  => $a['numero'],
    ], $p['anni'] ?? []);
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-landmark me-2" style="color:#1e40af;"></i><?= htmlspecialchars($pageTitle ?? 'Esami di Stato Prova') ?>
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

<?php if (empty($percorsi)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body py-5 text-center text-muted">
        <i class="fas fa-route mb-3 d-block" style="font-size:2rem;color:#e2e8f0;"></i>
        Nessun percorso disponibile. Crea prima un percorso accademico.
    </div>
</div>
<?php else: ?>

<form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/store">

    <!-- ══ Sezione 1: Dati principali ══ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <i class="fas fa-file-alt me-2" style="color:#1e40af;"></i>Dati esame
            </h6>
        </div>
        <div class="card-body px-4 py-4">
            <div class="row g-4">
                <div class="col-lg-5">
                    <label class="form-label fw-semibold mb-2">Denominazione esame <span class="text-danger">*</span></label>
                    <input type="text" name="denominazione" class="form-control"
                           placeholder="es. Esame di stato finale" required maxlength="255">
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold mb-2">Riferimento <span class="text-danger">*</span></label>
                    <select name="riferimento_tipo" id="sel_riferimento" class="form-select"
                            onchange="aggiornaRiferimento()">
                        <option value="classe">Classe (anno di corso)</option>
                        <option value="percorso">Corso (intero percorso)</option>
                    </select>
                    <div class="form-text">Classe specifica o intero corso</div>
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-semibold mb-2">Percorso / Corso <span class="text-danger">*</span></label>
                    <select name="percorso_id" id="sel_percorso" class="form-select"
                            onchange="aggiornaAnni()" required>
                        <option value="">— Seleziona —</option>
                        <?php foreach ($percorsi as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nome']) ?> — A.S. <?= htmlspecialchars($p['anno_label'] ?? '—') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-4" id="wrap_classe">
                    <label class="form-label fw-semibold mb-2">Classe (anno di corso) <span class="text-danger">*</span></label>
                    <select name="percorso_anno_id" id="sel_anno" class="form-select" disabled>
                        <option value="">— Prima seleziona il percorso —</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold mb-2">Tipo</label>
                    <input type="text" name="tipo" class="form-control"
                           maxlength="100"
                           value="<?= htmlspecialchars($_POST['tipo'] ?? '') ?>">
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold mb-2">Codice corso</label>
                    <input type="text" name="cod_corso" class="form-control"
                           placeholder="es. 1087060 – I ED" maxlength="50"
                           value="<?= htmlspecialchars($_POST['cod_corso'] ?? '') ?>">
                    <div class="form-text">es. 1087060 – I ED</div>
                </div>
                <div class="col-lg-2">
                    <label class="form-label fw-semibold mb-2">Ore corso</label>
                    <input type="number" name="ore_corso" class="form-control"
                           min="0" maxlength="6"
                           value="<?= htmlspecialchars($_POST['ore_corso'] ?? '') ?>">
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold mb-2">Cod. did. reg.</label>
                    <input type="text" name="cod_did_reg" class="form-control"
                           maxlength="50"
                           value="<?= htmlspecialchars($_POST['cod_did_reg'] ?? '') ?>">
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-semibold mb-2">Ente gestore</label>
                    <input type="text" name="ente_gestore" class="form-control"
                           maxlength="255"
                           value="<?= htmlspecialchars($_POST['ente_gestore'] ?? '') ?>">
                </div>

                <div class="col-lg-4">
                    <label class="form-label fw-semibold mb-2">Note</label>
                    <textarea name="note" class="form-control" rows="3"
                              placeholder="Note opzionali..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Sezione 2: Calendario prove ══ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <i class="fas fa-calendar-alt me-2" style="color:#1e40af;"></i>Calendario prove
            </h6>
        </div>
        <div class="card-body px-4 py-4">
            <div class="row g-4">

                <!-- Prova 1 -->
                <div class="col-lg-6">
                    <div class="p-4 rounded-3 h-100" style="background:#f0f4ff;border:1px solid #c7d7f7;">
                        <div class="fw-semibold mb-3" style="color:#1e40af;">
                            <i class="fas fa-file-alt me-2"></i>Prova 1
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">Data</label>
                                <input type="date" name="prova_teorica[data]" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Ora inizio</label>
                                <input type="time" name="prova_teorica[ora_inizio]" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Ora fine</label>
                                <input type="time" name="prova_teorica[ora_fine]" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prova 2 -->
                <div class="col-lg-6">
                    <div class="p-4 rounded-3 h-100" style="background:#f0fdf8;border:1px solid #a7f3d0;">
                        <div class="fw-semibold mb-3" style="color:#059669;">
                            <i class="fas fa-file-alt me-2"></i>Prova 2
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">Data</label>
                                <input type="date" name="prova_pratica[data]" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Ora inizio</label>
                                <input type="time" name="prova_pratica[ora_inizio]" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Ora fine</label>
                                <input type="time" name="prova_pratica[ora_fine]" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══ Sezione 3: Commissione d'esame ══ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <i class="fas fa-user-shield me-2" style="color:#1e40af;"></i>Commissione d'esame
            </h6>
            <span id="badge-commissione-gen" class="badge" style="background:#e8eef8;color:#1e40af;font-size:.8rem;">
                0 docenti
            </span>
        </div>
        <div class="card-body px-4 py-4">

            <!-- Controlli aggiunta -->
            <div class="row g-3 align-items-end mb-4">
                <div class="col-lg-5">
                    <label class="form-label fw-semibold mb-2">Docente</label>
                    <select id="sel-ins-gen" class="form-select">
                        <option value="">— Seleziona docente —</option>
                        <?php foreach ($docenti as $d): ?>
                            <option value="<?= $d['id'] ?>"
                                    data-cognome="<?= htmlspecialchars($d['cognome'], ENT_QUOTES) ?>"
                                    data-nome="<?= htmlspecialchars($d['nome'], ENT_QUOTES) ?>">
                                <?= htmlspecialchars($d['cognome'] . ' ' . $d['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label fw-semibold mb-2">Ruolo</label>
                    <select id="sel-ruolo-gen" class="form-select">
                        <option value="commissario">Commissario</option>
                        <option value="presidente">Presidente</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-semibold mb-2">In rappresentanza</label>
                    <select id="sel-rappresentanza-gen" class="form-select">
                        <option value="">— Seleziona —</option>
                        <option value="AMMINISTRAZIONE REGIONALE">Amministrazione Regionale</option>
                        <option value="DIREZ.NE PROV. LE DEL LAVORO">Direz.ne Prov.le del Lavoro</option>
                        <option value="PROVVEDITORATO AGLI STUDI">Provveditorato agli Studi</option>
                        <option value="DOCENTI CORSO">Docenti Corso</option>
                        <option value="ASS. CATEGORIA - LAV. AUTON.">Ass. Categoria - Lav. Auton.</option>
                        <option value="OO. SS. DEI LAV. DIPENDENTI">OO. SS. dei Lav. Dipendenti</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label fw-semibold mb-2">&nbsp;</label>
                    <button type="button" class="btn btn-outline-primary w-100 d-block" onclick="aggiungiCommissario()">
                        <i class="fas fa-plus me-1"></i>Aggiungi
                    </button>
                </div>
            </div>

            <!-- Hidden inputs per il submit (popolati via JS) -->
            <div id="commissione-hidden-inputs"></div>

            <!-- Stato vuoto -->
            <p id="empty-comm-gen" class="text-muted fst-italic mb-0">
                <i class="fas fa-info-circle me-1"></i>Nessun docente aggiunto alla commissione.
            </p>

            <!-- Cards docenti -->
            <div id="wrap-tabella-comm-gen" style="display:none;">
                <div class="row g-3" id="lista-comm-cards"></div>
            </div>

        </div>
    </div>

    <!-- ══ Submit ══ -->
    <div class="d-flex align-items-center justify-content-between p-4 rounded-3"
         style="background:#f8fafc;border:1px solid #e2e8f0;">
        <p class="text-muted mb-0" style="font-size:.88rem;">
            <i class="fas fa-users me-2" style="color:#1e40af;"></i>
            Gli studenti verranno iscritti automaticamente all'esame al momento della creazione.
        </p>
        <button type="submit" class="btn btn-primary px-5">
            <i class="fas fa-plus me-2"></i>Crea esame di stato
        </button>
    </div>

</form>
<?php endif; ?>


<script>
const mappaAnni = <?= json_encode($mappaAnni) ?>;
const ordinali  = <?= json_encode($ordinali) ?>;

function aggiornaRiferimento() {
    const tipo    = document.getElementById('sel_riferimento').value;
    const wrap    = document.getElementById('wrap_classe');
    const selAnno = document.getElementById('sel_anno');
    if (tipo === 'percorso') {
        wrap.style.display = 'none';
        selAnno.disabled = true;
        selAnno.removeAttribute('required');
    } else {
        wrap.style.display = '';
        selAnno.disabled = !document.getElementById('sel_percorso').value;
        selAnno.setAttribute('required', 'required');
        aggiornaAnni();
    }
}

function aggiornaAnni() {
    const pid     = document.getElementById('sel_percorso').value;
    const selAnno = document.getElementById('sel_anno');
    const tipo    = document.getElementById('sel_riferimento').value;
    selAnno.innerHTML = '<option value="">— Seleziona classe —</option>';
    selAnno.disabled  = true;
    if (!pid || !mappaAnni[pid] || tipo === 'percorso') return;
    selAnno.disabled = false;
    mappaAnni[pid].forEach(a => {
        const label = ordinali[a.numero] ?? (a.numero + '° Anno');
        selAnno.innerHTML += `<option value="${a.anno_id}">${label}</option>`;
    });
}

aggiornaRiferimento();

/* ── Commissione client-side ── */
let _commissione = []; // [{ insId, cognome, nome, ruolo, inRappresentanza }]

function aggiornaHiddenInputs() {
    const wrap = document.getElementById('commissione-hidden-inputs');
    wrap.innerHTML = '';
    _commissione.forEach((m, i) => {
        wrap.innerHTML += `
            <input type="hidden" name="commissione[${i}][insegnante_id]"    value="${m.insId}">
            <input type="hidden" name="commissione[${i}][ruolo]"            value="${m.ruolo}">
            <input type="hidden" name="commissione[${i}][in_rappresentanza]" value="${m.inRappresentanza ?? ''}">`;
    });
}

function aggiornaBadge() {
    const n = _commissione.length;
    document.getElementById('badge-commissione-gen').textContent =
        n + ' docent' + (n === 1 ? 'e' : 'i');
}

function commCardHtml(idx, insId, cognome, nome, ruolo, inRappresentanza) {
    const isP      = ruolo === 'presidente';
    const badgeBg  = isP ? '#fef3c7' : '#e8eef8';
    const badgeClr = isP ? '#d97706' : '#1e40af';
    const label    = isP ? 'Presidente' : 'Commissario';
    const iconBg   = isP ? '#fde68a'   : '#dbeafe';
    const iconClr  = isP ? '#d97706'   : '#1e40af';
    const rappHtml = inRappresentanza
        ? `<div class="text-muted text-truncate" style="font-size:.74rem;">${inRappresentanza}</div>`
        : '';
    return `
    <div class="col-lg-4 col-md-6" id="comm-card-${idx}">
        <div class="d-flex align-items-center gap-3 p-3 rounded-3"
             style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div style="width:42px;height:42px;border-radius:50%;background:${iconBg};
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-user-tie" style="color:${iconClr};font-size:.9rem;"></i>
            </div>
            <div class="flex-grow-1 min-width-0">
                <div class="fw-semibold text-truncate" style="color:#0c1a3a;">${cognome} ${nome}</div>
                ${rappHtml}
                <span class="badge mt-1"
                      style="background:${badgeBg};color:${badgeClr};font-size:.72rem;">${label}</span>
            </div>
            <button type="button"
                    class="btn btn-sm btn-link text-danger p-0 flex-shrink-0"
                    title="Rimuovi" onclick="rimuoviCommissario(${idx})">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
    </div>`;
}

function renderCards() {
    const lista = document.getElementById('lista-comm-cards');
    lista.innerHTML = '';
    _commissione.forEach((m, i) => {
        lista.insertAdjacentHTML('beforeend', commCardHtml(i, m.insId, m.cognome, m.nome, m.ruolo, m.inRappresentanza));
    });
    document.getElementById('wrap-tabella-comm-gen').style.display =
        _commissione.length ? '' : 'none';
    document.getElementById('empty-comm-gen').style.display =
        _commissione.length ? 'none' : '';
    aggiornaBadge();
    aggiornaHiddenInputs();
}

function aggiungiCommissario() {
    const sel             = document.getElementById('sel-ins-gen');
    const insId           = parseInt(sel.value);
    if (!insId) return;
    const ruolo           = document.getElementById('sel-ruolo-gen').value;
    const selRapp         = document.getElementById('sel-rappresentanza-gen');
    const inRappresentanza = selRapp.value;
    const opt             = sel.options[sel.selectedIndex];
    const cognome         = opt.dataset.cognome;
    const nome            = opt.dataset.nome;

    // Impedisci duplicati
    if (_commissione.find(m => m.insId === insId)) return;

    _commissione.push({ insId, cognome, nome, ruolo, inRappresentanza });
    opt.remove();
    sel.value    = '';
    selRapp.value = '';

    renderCards();
}

function rimuoviCommissario(idx) {
    const m = _commissione[idx];
    if (!m) return;

    // Rimetti nella select
    const sel = document.getElementById('sel-ins-gen');
    if (sel) {
        const opt = document.createElement('option');
        opt.value           = m.insId;
        opt.dataset.cognome = m.cognome;
        opt.dataset.nome    = m.nome;
        opt.textContent     = `${m.cognome} ${m.nome}`;
        // Inserisci in ordine alfabetico
        const opts = Array.from(sel.options).filter(o => o.value);
        const before = opts.find(o => o.textContent.localeCompare(opt.textContent) > 0);
        before ? sel.insertBefore(opt, before) : sel.appendChild(opt);
    }

    _commissione.splice(idx, 1);
    renderCards();
}
</script>
