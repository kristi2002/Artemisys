<?php
$steps = [
    1 => [
        'label'    => 'Candidatura',
        'sublabel' => 'Studente e azienda selezionati',
        'icon'     => 'fas fa-user-check',
        'color'    => '#3b82f6',
    ],
    2 => [
        'label'    => 'Convenzione',
        'sublabel' => 'Documenti firmati, tutor assegnati',
        'icon'     => 'fas fa-file-signature',
        'color'    => '#8b5cf6',
    ],
    3 => [
        'label'    => 'Tirocinio',
        'sublabel' => 'Stage in corso, ore tracciate',
        'icon'     => 'fas fa-briefcase',
        'color'    => '#f59e0b',
    ],
    4 => [
        'label'    => 'Valutazione',
        'sublabel' => 'Giudizio finale e attestato',
        'icon'     => 'fas fa-graduation-cap',
        'color'    => '#10b981',
    ],
];

$currentStep = (int)$stage['step'];

$giudiziLabel = [
    'insufficiente' => 'Insufficiente',
    'sufficiente'   => 'Sufficiente',
    'buono'         => 'Buono',
    'ottimo'        => 'Ottimo',
];
$giudiziColor = [
    'insufficiente' => '#ef4444',
    'sufficiente'   => '#f59e0b',
    'buono'         => '#3b82f6',
    'ottimo'        => '#10b981',
];
?>

<style>
/* ── Stepper ── */
.stage-stepper { display:flex; align-items:flex-start; justify-content:center; gap:0; margin-bottom:2rem; }
.stage-step { display:flex; flex-direction:column; align-items:center; flex:1; position:relative; }
.stage-step-connector {
    position:absolute; top:22px; left:calc(50% + 22px); right:calc(-50% + 22px);
    height:3px; background:#e2e8f0; z-index:0; transition:.3s;
}
.stage-step-connector.done { background:var(--step-color); }
.stage-step-circle {
    width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; z-index:1; position:relative; border:3px solid #e2e8f0;
    background:#fff; color:#94a3b8; transition:.25s;
}
.stage-step.done    .stage-step-circle { background:var(--step-color); border-color:var(--step-color); color:#fff; }
.stage-step.current .stage-step-circle {
    background:var(--step-color); border-color:var(--step-color); color:#fff;
    box-shadow: 0 0 0 5px color-mix(in srgb, var(--step-color) 20%, transparent);
}
.stage-step-label { font-size:.75rem; font-weight:700; color:#94a3b8; margin-top:8px; text-align:center; transition:.25s; }
.stage-step.done    .stage-step-label,
.stage-step.current .stage-step-label { color:var(--step-color); }
.stage-step-sublabel { font-size:.66rem; color:#cbd5e1; text-align:center; max-width:90px; line-height:1.3; }
.stage-step.current .stage-step-sublabel { color:#94a3b8; }

/* ── Step content card ── */
.step-card { border-radius:12px; border:0; box-shadow:0 1px 8px rgba(0,0,0,.07); }
.step-card .card-header { border-radius:12px 12px 0 0 !important; }
</style>

<!-- Back -->
<div class="mb-3">
    <a href="<?= BASE_URL ?>stage" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Tutti gli stage
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

<!-- Intestazione studente -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3 px-4 d-flex align-items-center gap-3">
        <div style="width:48px;height:48px;border-radius:50%;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-user-graduate" style="color:#1e40af;font-size:1.3rem;"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold" style="color:#0c1a3a;">
                <?= htmlspecialchars($stage['cognome'] . ' ' . $stage['nome']) ?>
            </h5>
            <div class="text-muted small">
                <?= htmlspecialchars($stage['azienda']) ?>
                <?= $stage['citta'] ? ' · ' . htmlspecialchars($stage['citta']) : '' ?>
                <?= $stage['settore'] ? ' · ' . htmlspecialchars($stage['settore']) : '' ?>
            </div>
        </div>
        <div class="ms-auto d-flex gap-2">
            <?php if ($currentStep > 1): ?>
            <form method="POST" action="<?= BASE_URL ?>stage/prevStep" class="d-inline">
                <input type="hidden" name="id" value="<?= $stage['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary"
                        title="Torna allo step precedente">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </form>
            <?php endif; ?>
            <?php if ($currentStep < 4): ?>
            <form method="POST" action="<?= BASE_URL ?>stage/nextStep" class="d-inline"
                  onsubmit="return confirm('Avanzare allo step successivo?')">
                <input type="hidden" name="id" value="<?= $stage['id'] ?>">
                <button type="submit" class="btn btn-sm btn-primary">
                    <?= $steps[$currentStep + 1]['label'] ?> <i class="fas fa-chevron-right ms-1"></i>
                </button>
            </form>
            <?php else: ?>
                <span class="badge" style="background:#10b9811a;color:#10b981;border:1px solid #10b98133;font-size:.8rem;padding:.5rem .9rem;">
                    <i class="fas fa-check-circle me-1"></i>Completato
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Stepper ── -->
<div class="stage-stepper mb-4">
    <?php foreach ($steps as $n => $s):
        $state = $n < $currentStep ? 'done' : ($n === $currentStep ? 'current' : 'future');
    ?>
        <div class="stage-step <?= $state ?>" style="--step-color:<?= $s['color'] ?>;">
            <?php if ($n < 4): ?>
                <div class="stage-step-connector <?= $n < $currentStep ? 'done' : '' ?>"
                     style="<?= $n < $currentStep ? '--step-color:'.$s['color'] : '' ?>"></div>
            <?php endif; ?>
            <div class="stage-step-circle">
                <?php if ($n < $currentStep): ?>
                    <i class="fas fa-check"></i>
                <?php else: ?>
                    <i class="<?= $s['icon'] ?>"></i>
                <?php endif; ?>
            </div>
            <div class="stage-step-label"><?= $s['label'] ?></div>
            <div class="stage-step-sublabel"><?= $s['sublabel'] ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ── Contenuto step corrente ── -->
<?php
$currentInfo = $steps[$currentStep];
$stepColor   = $currentInfo['color'];
?>

<form method="POST" action="<?= BASE_URL ?>stage/update">
    <input type="hidden" name="id" value="<?= $stage['id'] ?>">
    <input type="hidden" name="step" value="<?= $currentStep ?>">

    <div class="card step-card mb-4">
        <div class="card-header py-3 px-4 d-flex align-items-center gap-2"
             style="background:<?= $stepColor ?>0d;border-bottom:2px solid <?= $stepColor ?>22;">
            <i class="<?= $currentInfo['icon'] ?>" style="color:<?= $stepColor ?>;font-size:1.1rem;"></i>
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                Step <?= $currentStep ?> — <?= $currentInfo['label'] ?>
            </h6>
        </div>
        <div class="card-body">

            <?php if ($currentStep === 1): ?>
            <!-- ───── STEP 1: CANDIDATURA ───── -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Azienda / Struttura <span class="text-danger">*</span></label>
                    <input type="text" name="azienda" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($stage['azienda']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Città</label>
                    <input type="text" name="citta" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($stage['citta'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Settore</label>
                    <input type="text" name="settore" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($stage['settore'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Note candidatura</label>
                    <textarea name="note_candidatura" class="form-control form-control-sm" rows="3"
                              placeholder="Motivazioni, requisiti, note iniziali..."><?= htmlspecialchars($stage['note_candidatura'] ?? '') ?></textarea>
                </div>
            </div>

            <?php elseif ($currentStep === 2): ?>
            <!-- ───── STEP 2: CONVENZIONE ───── -->
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Data convenzione</label>
                    <input type="date" name="data_convenzione" class="form-control form-control-sm"
                           value="<?= $stage['data_convenzione'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Data inizio stage</label>
                    <input type="date" name="data_inizio" class="form-control form-control-sm"
                           value="<?= $stage['data_inizio'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Data fine stage</label>
                    <input type="date" name="data_fine" class="form-control form-control-sm"
                           value="<?= $stage['data_fine'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Monte ore previsto</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="monte_ore" class="form-control form-control-sm"
                               min="1" max="2000" value="<?= $stage['monte_ore'] ?? 150 ?>">
                        <span class="input-group-text">ore</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tutor aziendale</label>
                    <input type="text" name="tutor_aziendale" class="form-control form-control-sm"
                           placeholder="Nome e cognome" value="<?= htmlspecialchars($stage['tutor_aziendale'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tutor scolastico</label>
                    <input type="text" name="tutor_scolastico" class="form-control form-control-sm"
                           placeholder="Nome e cognome" value="<?= htmlspecialchars($stage['tutor_scolastico'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Note convenzione</label>
                    <textarea name="note_convenzione" class="form-control form-control-sm" rows="3"
                              placeholder="Note sull'accordo, clausole particolari..."><?= htmlspecialchars($stage['note_convenzione'] ?? '') ?></textarea>
                </div>
            </div>

            <?php elseif ($currentStep === 3): ?>
            <!-- ───── STEP 3: TIROCINIO ───── -->
            <div class="row g-3 mb-3">
                <!-- Riepilogo info -->
                <div class="col-12">
                    <div class="p-3 rounded-3" style="background:#f8fafc;">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="text-muted small">Periodo</div>
                                <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                                    <?= $stage['data_inizio'] ? date('d/m/Y', strtotime($stage['data_inizio'])) : '—' ?>
                                    <?= $stage['data_fine'] ? ' → ' . date('d/m/Y', strtotime($stage['data_fine'])) : '' ?>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Tutor aziendale</div>
                                <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                                    <?= htmlspecialchars($stage['tutor_aziendale'] ?? '—') ?>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Tutor scolastico</div>
                                <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                                    <?= htmlspecialchars($stage['tutor_scolastico'] ?? '—') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avanzamento ore -->
            <?php
            $oreTarget  = max(1, (int)$stage['monte_ore']);
            $oreSvolte  = (int)$stage['ore_svolte'];
            $percOre    = min(100, round($oreSvolte / $oreTarget * 100));
            $barColor   = $percOre >= 100 ? '#10b981' : ($percOre >= 50 ? '#f59e0b' : '#3b82f6');
            ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Ore svolte</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="ore_svolte" class="form-control"
                               min="0" max="9999"
                               value="<?= $oreSvolte ?>"
                               id="inpOreSvolte"
                               oninput="aggiornaProgressione(this.value)">
                        <span class="input-group-text">/ <?= $oreTarget ?> ore</span>
                    </div>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="w-100">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Completamento</span>
                            <span class="small fw-bold" id="percLabel" style="color:<?= $barColor ?>;"><?= $percOre ?>%</span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:8px;">
                            <div class="progress-bar" id="progressBar"
                                 role="progressbar"
                                 style="width:<?= $percOre ?>%;background:<?= $barColor ?>;border-radius:8px;"
                                 aria-valuenow="<?= $percOre ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Note tirocinio</label>
                    <textarea name="note_tirocinio" class="form-control form-control-sm" rows="4"
                              placeholder="Attività svolte, osservazioni del tutor, episodi rilevanti..."><?= htmlspecialchars($stage['note_tirocinio'] ?? '') ?></textarea>
                </div>
            </div>

            <?php elseif ($currentStep === 4): ?>
            <!-- ───── STEP 4: VALUTAZIONE ───── -->
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="p-3 rounded-3" style="background:#f8fafc;">
                        <div class="row g-3 text-center">
                            <div class="col-3">
                                <div class="text-muted small">Azienda</div>
                                <div class="fw-semibold" style="font-size:.9rem;">
                                    <?= htmlspecialchars($stage['azienda']) ?>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Ore svolte</div>
                                <div class="fw-semibold" style="font-size:.9rem;">
                                    <?= (int)$stage['ore_svolte'] ?> / <?= $stage['monte_ore'] ?>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Tutor aziendale</div>
                                <div class="fw-semibold" style="font-size:.9rem;">
                                    <?= htmlspecialchars($stage['tutor_aziendale'] ?? '—') ?>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Tutor scolastico</div>
                                <div class="fw-semibold" style="font-size:.9rem;">
                                    <?= htmlspecialchars($stage['tutor_scolastico'] ?? '—') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Voto finale</label>
                    <input type="number" name="voto_finale" class="form-control form-control-sm"
                           min="0" max="100" step="0.5"
                           placeholder="es. 8.5"
                           value="<?= $stage['voto_finale'] !== null ? $stage['voto_finale'] : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Giudizio</label>
                    <select name="giudizio" class="form-select form-select-sm">
                        <option value="">— Seleziona —</option>
                        <?php foreach ($giudiziLabel as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= $stage['giudizio'] === $val ? 'selected' : '' ?>>
                                <?= $lbl ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Note valutazione</label>
                    <textarea name="note_valutazione" class="form-control form-control-sm" rows="4"
                              placeholder="Commento finale, punti di forza, aree di miglioramento..."><?= htmlspecialchars($stage['note_valutazione'] ?? '') ?></textarea>
                </div>
                <?php if ($stage['giudizio']): ?>
                <div class="col-12">
                    <div class="p-3 rounded-3 text-center"
                         style="background:<?= $giudiziColor[$stage['giudizio']] ?>0f;border:1px solid <?= $giudiziColor[$stage['giudizio']] ?>22;">
                        <div style="font-size:2rem;font-weight:800;color:<?= $giudiziColor[$stage['giudizio']] ?>;">
                            <?= strtoupper($giudiziLabel[$stage['giudizio']]) ?>
                        </div>
                        <?php if ($stage['voto_finale'] !== null): ?>
                        <div class="text-muted small">Voto: <?= $stage['voto_finale'] ?>/100</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
        <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Salva
            </button>
        </div>
    </div>
</form>

<!-- Documenti dello stage -->
<?php $allegatiConv = array_values(array_filter($allegati, fn($a) => $a['categoria'] === 'convenzione')); ?>
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-file-signature me-2" style="color:#1e40af;"></i>Documento di convenzione
        </h6>
        <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($allegatiConv) ?></span>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>stage/upload-allegato"
              enctype="multipart/form-data" class="mb-3">
            <input type="hidden" name="stage_id"  value="<?= $stage['id'] ?>">
            <input type="hidden" name="categoria" value="convenzione">
            <div class="input-group input-group-sm">
                <input type="file" name="allegato" class="form-control"
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i>Carica
                </button>
            </div>
            <div class="form-text">PDF, DOC, DOCX, JPG, PNG — max 10 MB</div>
        </form>

        <?php if (empty($allegatiConv)): ?>
            <p class="text-muted small fst-italic mb-0">Nessun documento di convenzione caricato.</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($allegatiConv as $all):
                    $ext  = strtolower(pathinfo($all['original_name'], PATHINFO_EXTENSION));
                    $icon = match($ext) {
                        'pdf'              => 'fa-file-pdf text-danger',
                        'doc','docx'       => 'fa-file-word text-primary',
                        'jpg','jpeg','png' => 'fa-file-image text-success',
                        default            => 'fa-file text-muted',
                    };
                ?>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <a href="<?= ASSETS_URL ?>public/uploads/stage/<?= urlencode($all['filename']) ?>"
                           target="_blank" class="text-decoration-none text-truncate" style="color:#0c1a3a;font-size:.88rem;max-width:75%;">
                            <i class="fas <?= $icon ?> me-2"></i>
                            <?= htmlspecialchars($all['original_name']) ?>
                            <span class="text-muted ms-2" style="font-size:.72rem;">
                                <?= date('d/m/Y', strtotime($all['created_at'])) ?>
                            </span>
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>stage/delete-allegato"
                              onsubmit="return confirm('Eliminare questo documento?')">
                            <input type="hidden" name="allegato_id" value="<?= $all['id'] ?>">
                            <input type="hidden" name="stage_id"    value="<?= $stage['id'] ?>">
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

<!-- Cronologia step completati (sopra lo step corrente) -->
<?php if ($currentStep > 1): ?>
<div class="mt-2">
    <h6 class="text-muted small fw-semibold text-uppercase mb-3" style="letter-spacing:.05em;">
        Step precedenti
    </h6>
    <div class="accordion" id="accordionPrev">
        <?php for ($n = 1; $n < $currentStep; $n++):
            $si = $steps[$n]; ?>
        <div class="accordion-item border-0 mb-2" style="border-radius:10px;overflow:hidden;box-shadow:0 1px 5px rgba(0,0,0,.06);">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-3" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapse<?= $n ?>"
                        style="font-size:.9rem;font-weight:600;">
                    <i class="<?= $si['icon'] ?> me-2" style="color:<?= $si['color'] ?>;"></i>
                    Step <?= $n ?> — <?= $si['label'] ?>
                    <span class="ms-2 badge" style="background:<?= $si['color'] ?>1a;color:<?= $si['color'] ?>;border:1px solid <?= $si['color'] ?>33;font-size:.7rem;">
                        <i class="fas fa-check me-1"></i>Completato
                    </span>
                </button>
            </h2>
            <div id="collapse<?= $n ?>" class="accordion-collapse collapse">
                <div class="accordion-body" style="font-size:.88rem;color:#374151;">
                    <?php if ($n === 1): ?>
                        <p class="mb-1"><strong>Azienda:</strong> <?= htmlspecialchars($stage['azienda']) ?>
                            <?= $stage['citta'] ? ' · ' . htmlspecialchars($stage['citta']) : '' ?></p>
                        <?php if ($stage['settore']): ?>
                        <p class="mb-1"><strong>Settore:</strong> <?= htmlspecialchars($stage['settore']) ?></p>
                        <?php endif; ?>
                        <?php if ($stage['note_candidatura']): ?>
                        <p class="mb-0"><strong>Note:</strong> <?= nl2br(htmlspecialchars($stage['note_candidatura'])) ?></p>
                        <?php endif; ?>
                    <?php elseif ($n === 2): ?>
                        <?php if ($stage['data_convenzione']): ?>
                        <p class="mb-1"><strong>Data convenzione:</strong> <?= date('d/m/Y', strtotime($stage['data_convenzione'])) ?></p>
                        <?php endif; ?>
                        <?php if ($stage['data_inizio']): ?>
                        <p class="mb-1"><strong>Periodo:</strong>
                            <?= date('d/m/Y', strtotime($stage['data_inizio'])) ?>
                            <?= $stage['data_fine'] ? ' → ' . date('d/m/Y', strtotime($stage['data_fine'])) : '' ?>
                        </p>
                        <?php endif; ?>
                        <p class="mb-1"><strong>Monte ore:</strong> <?= $stage['monte_ore'] ?></p>
                        <?php if ($stage['tutor_aziendale']): ?>
                        <p class="mb-1"><strong>Tutor az.:</strong> <?= htmlspecialchars($stage['tutor_aziendale']) ?></p>
                        <?php endif; ?>
                        <?php if ($stage['tutor_scolastico']): ?>
                        <p class="mb-1"><strong>Tutor sc.:</strong> <?= htmlspecialchars($stage['tutor_scolastico']) ?></p>
                        <?php endif; ?>
                        <?php if ($stage['note_convenzione']): ?>
                        <p class="mb-0"><strong>Note:</strong> <?= nl2br(htmlspecialchars($stage['note_convenzione'])) ?></p>
                        <?php endif; ?>
                    <?php elseif ($n === 3): ?>
                        <p class="mb-1"><strong>Ore svolte:</strong> <?= (int)$stage['ore_svolte'] ?> / <?= $stage['monte_ore'] ?></p>
                        <?php if ($stage['note_tirocinio']): ?>
                        <p class="mb-0"><strong>Note:</strong> <?= nl2br(htmlspecialchars($stage['note_tirocinio'])) ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<!-- Elimina in fondo -->
<div class="mt-4 pt-2 border-top">
    <form method="POST" action="<?= BASE_URL ?>stage/delete"
          onsubmit="return confirm('Eliminare questo stage definitivamente?')">
        <input type="hidden" name="id" value="<?= $stage['id'] ?>">
        <button type="submit" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-trash me-2"></i>Elimina stage
        </button>
    </form>
</div>

<script>
const oreTarget = <?= max(1, (int)$stage['monte_ore']) ?>;

function aggiornaProgressione(val) {
    const v    = Math.max(0, parseInt(val) || 0);
    const perc = Math.min(100, Math.round(v / oreTarget * 100));
    const color = perc >= 100 ? '#10b981' : (perc >= 50 ? '#f59e0b' : '#3b82f6');
    const bar   = document.getElementById('progressBar');
    const lbl   = document.getElementById('percLabel');
    if (bar) { bar.style.width = perc + '%'; bar.style.background = color; }
    if (lbl) { lbl.textContent = perc + '%'; lbl.style.color = color; }
}
</script>
