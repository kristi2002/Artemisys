<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

$riferimentoLabel = ['percorso' => 'Corso (intero percorso)', 'classe' => 'Classe (anno di corso)'];
$ruoloLabel = ['presidente' => 'Presidente', 'commissario' => 'Commissario', 'segretario' => 'Segretario'];
$statoBadge = [
    'programmato' => '#3b82f6',
    'in_corso'    => '#f59e0b',
    'completato'  => '#10b981',
    'annullato'   => '#ef4444',
];

$nomeAnno = $esame['anno_numero']
    ? ($ordinali[$esame['anno_numero']] ?? $esame['anno_numero'].'° Anno')
    : null;
$colore   = $statoBadge[$esame['stato']] ?? '#64748b';

$totale = count($iscrizioni);
$numValutati = 0;
$numAssenti  = 0;
foreach ($iscrizioni as $i) {
    if ($i['esito'] === 'assente') $numAssenti++;
    elseif ($i['esito']) $numValutati++;
}
?>

<div class="mb-3">
    <a href="<?= BASE_URL ?>esami-di-stato" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Tutti gli esami di stato
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

    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-landmark me-2" style="color:#1e40af;"></i>Dettaglio esame
                </h6>
                <span class="badge" style="background:<?= $colore ?>1a;color:<?= $colore ?>;border:1px solid <?= $colore ?>33;">
                    <?= ucfirst(str_replace('_', ' ', $esame['stato'])) ?>
                </span>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1" style="color:#0c1a3a;"><?= htmlspecialchars($esame['denominazione']) ?></h5>
                <div class="text-muted small mb-3">
                    <?= htmlspecialchars($esame['percorso_nome']) ?>
                    · A.S. <?= htmlspecialchars($esame['anno_label'] ?? '—') ?>
                </div>

                <table class="table table-sm table-borderless mb-0" style="font-size:.88rem;">
                    <tr>
                        <td class="ps-0 text-muted" style="width:110px;">Riferimento</td>
                        <td class="fw-semibold">
                            <?= $riferimentoLabel[$esame['riferimento_tipo']] ?? $esame['riferimento_tipo'] ?>
                            <?php if ($esame['riferimento_tipo'] === 'classe' && $nomeAnno): ?>
                                <div class="text-muted fw-normal small"><?= $nomeAnno ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-0 text-muted">Data</td>
                        <td><?= $esame['data'] ? date('d/m/Y', strtotime($esame['data'])) : '—' ?></td>
                    </tr>
                    <?php if ($esame['ora_inizio']): ?>
                    <tr>
                        <td class="ps-0 text-muted">Orario</td>
                        <td><?= substr($esame['ora_inizio'], 0, 5) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($esame['rapp_cognome']): ?>
                    <tr>
                        <td class="ps-0 text-muted">Rappresentante</td>
                        <td><?= htmlspecialchars($esame['rapp_cognome'] . ' ' . $esame['rapp_nome']) ?></td>
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
            </div>
        </div>

        <!-- Dati documento Word -->
        <?php
        $proveRows = $prove ?? [];
        while (count($proveRows) < 3) {
            $proveRows[] = ['data' => '', 'ora_inizio' => '', 'ora_fine' => '', 'tipo_prova' => 'P/S/O'];
        }
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-file-word me-2" style="color:#1e40af;"></i>Stampa documento
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Compila i dati per il modello CAL-ESA. I campi percorso, anno scolastico e docenti
                    vengono presi automaticamente dal database.
                </p>
                <!-- Form salvataggio dati documento (senza commissione annidata) -->
                <form id="form-save-documento" method="POST" action="<?= BASE_URL ?>esami-di-stato/save-documento">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">

                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Ente gestore</label>
                        <input type="text" name="ente_gestore" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($esame['ente_gestore'] ?? '') ?>">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold mb-1">Cod. corso</label>
                            <input type="text" name="cod_corso" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($esame['cod_corso'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold mb-1">Cod. did. reg.</label>
                            <input type="text" name="cod_did_reg" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($esame['cod_did_reg'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Ore corso</label>
                        <input type="number" name="ore_corso" class="form-control form-control-sm"
                               min="0" value="<?= htmlspecialchars($esame['ore_corso'] ?? '') ?>">
                    </div>
                    <label class="form-label small fw-semibold mb-2">Calendario prove</label>
                    <?php
                    $proveLabels = [
                        0 => ['label' => 'Prova teorica', 'icon' => 'fa-book',  'color' => '#1e40af'],
                        1 => ['label' => 'Prova pratica', 'icon' => 'fa-tools', 'color' => '#059669'],
                    ];
                    foreach (array_slice($proveRows, 0, 2) as $i => $prova):
                        $lbl = $proveLabels[$i];
                    ?>
                    <div class="border rounded-3 p-2 mb-2" style="background:#f8fafc;">
                        <div class="fw-semibold mb-2" style="font-size:.78rem;color:<?= $lbl['color'] ?>;">
                            <i class="fas <?= $lbl['icon'] ?> me-1"></i><?= $lbl['label'] ?>
                        </div>
                        <input type="hidden" name="prove[<?= $i ?>][tipo_prova]"
                               value="<?= htmlspecialchars($prova['tipo_prova'] ?: $lbl['label']) ?>">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small mb-0" style="font-size:.72rem;color:#64748b;">Data</label>
                                <input type="date" name="prove[<?= $i ?>][data]" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($prova['data'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-0" style="font-size:.72rem;color:#64748b;">Inizio</label>
                                <input type="time" name="prove[<?= $i ?>][ora_inizio]" class="form-control form-control-sm"
                                       value="<?= !empty($prova['ora_inizio']) ? substr($prova['ora_inizio'], 0, 5) : '' ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-0" style="font-size:.72rem;color:#64748b;">Fine</label>
                                <input type="time" name="prove[<?= $i ?>][ora_fine]" class="form-control form-control-sm"
                                       value="<?= !empty($prova['ora_fine']) ? substr($prova['ora_fine'], 0, 5) : '' ?>">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </form>

                <!-- Commissione docenti — form separati per evitare nidificazione -->
                <hr class="my-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="form-label small fw-semibold mb-0">
                        <i class="fas fa-user-shield me-1" style="color:#1e40af;"></i>Commissione
                    </span>
                    <span id="badge-commissione" class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($commissione) ?></span>
                </div>

                <p id="empty-commissione" class="text-muted small mb-2" <?= !empty($commissione) ? 'style="display:none;"' : '' ?>>
                    <i class="fas fa-info-circle me-1"></i>Nessun docente in commissione.
                </p>

                <div id="lista-commissione" class="d-flex flex-column gap-2 mb-3">
                    <?php foreach ($commissione as $c): ?>
                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 comm-item" data-id="<?= $c['id'] ?>"
                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div style="width:28px;height:28px;border-radius:50%;background:#e8eef8;
                                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-user-tie" style="color:#1e40af;font-size:.78rem;"></i>
                        </div>
                        <div class="flex-grow-1" style="font-size:.83rem;">
                            <div class="fw-semibold" style="color:#0c1a3a;">
                                <?= htmlspecialchars($c['cognome'] . ' ' . $c['nome']) ?>
                            </div>
                            <div class="text-muted" style="font-size:.7rem;">
                                <?= $ruoloLabel[$c['ruolo']] ?? $c['ruolo'] ?>
                            </div>
                        </div>
                        <?php if ($c['ruolo'] !== 'presidente'): ?>
                        <button type="button"
                                class="btn btn-sm btn-link text-danger p-0"
                                title="Rimuovi"
                                onclick="rimuoviCommissario(this, <?= $c['id'] ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($docDisponibili)): ?>
                <form id="add-comm-form" method="POST" action="<?= BASE_URL ?>esami-di-stato/add-commissario" class="mb-3">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
                    <div class="d-flex gap-2 mb-2">
                        <select name="insegnante_id" class="form-select form-select-sm" required>
                            <option value="">— Aggiungi docente —</option>
                            <?php foreach ($docDisponibili as $d): ?>
                                <option value="<?= $d['id'] ?>">
                                    <?= htmlspecialchars($d['cognome'] . ' ' . $d['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <select name="ruolo" class="form-select form-select-sm">
                        <option value="commissario">Commissario</option>
                        <option value="presidente">Presidente</option>
                        <option value="segretario">Segretario</option>
                    </select>
                </form>
                <?php endif; ?>

                <!-- Form nascosti per rimozione commissari (fuori da qualsiasi form) -->
                <?php foreach ($commissione as $c): if ($c['ruolo'] === 'presidente') continue; ?>
                <form id="rm-comm-<?= $c['id'] ?>"
                      method="POST"
                      action="<?= BASE_URL ?>esami-di-stato/remove-commissario"
                      class="d-none">
                    <input type="hidden" name="esame_id"      value="<?= $esame['id'] ?>">
                    <input type="hidden" name="insegnante_id" value="<?= $c['id'] ?>">
                </form>
                <?php endforeach; ?>

                <hr class="mt-0 mb-3">
                <button type="submit" form="form-save-documento" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="fas fa-save me-1"></i>Salva dati documento
                </button>

                <a href="<?= BASE_URL ?>esami-di-stato/stampa?id=<?= $esame['id'] ?>"
                   class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-file-word me-1"></i>Scarica Word (CAL-ESA)
                </a>
            </div>
        </div>

        <!-- Documenti -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-paperclip me-2" style="color:#1e40af;"></i>Documenti
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($allegati) ?></span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato/upload-allegato"
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
                                'pdf'              => 'fa-file-pdf text-danger',
                                'doc','docx'       => 'fa-file-word text-primary',
                                'jpg','jpeg','png' => 'fa-file-image text-success',
                                default            => 'fa-file text-muted',
                            };
                        ?>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <a href="<?= ASSETS_URL ?>public/uploads/esami-di-stato/<?= urlencode($all['filename']) ?>"
                                   target="_blank" class="text-decoration-none text-truncate" style="color:#0c1a3a;font-size:.85rem;max-width:75%;">
                                    <i class="fas <?= $icon ?> me-2"></i>
                                    <?= htmlspecialchars($all['original_name']) ?>
                                </a>
                                <button type="button"
                                        class="btn btn-link p-0 text-danger btn-elimina-allegato"
                                        style="font-size:.8rem;"
                                        data-id="<?= $all['id'] ?>"
                                        data-nome="<?= htmlspecialchars($all['original_name'], ENT_QUOTES) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Iscrivi studente -->
        <?php if (!empty($studentiDisp)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-user-plus me-2" style="color:#1e40af;"></i>Iscrivi studente
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato/iscrivi">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
                    <select name="studente_id" class="form-select form-select-sm mb-2" required>
                        <option value="">— Seleziona studente —</option>
                        <?php foreach ($studentiDisp as $s): ?>
                            <option value="<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                                <?php if ($s['codice_fiscale']): ?>
                                    (<?= htmlspecialchars($s['codice_fiscale']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-plus me-1"></i>Iscrivi
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <button type="button" class="btn btn-outline-danger btn-sm w-100"
                        data-bs-toggle="modal" data-bs-target="#modalEliminaEsameDetail">
                    <i class="fas fa-trash me-2"></i>Elimina esame di stato
                </button>
            </div>
        </div>

    </div>

    <!-- Iscritti ed esiti -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-users me-2" style="color:#1e40af;"></i>Studenti iscritti
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= $totale ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($iscrizioni)): ?>
                    <div class="text-center text-muted py-5" style="font-size:.9rem;">
                        <i class="fas fa-users mb-2 d-block" style="font-size:2rem;color:#e2e8f0;"></i>
                        Nessuno studente iscritto.<br>
                        <span style="font-size:.78rem;">Iscrivi gli studenti dal pannello a sinistra.</span>
                    </div>
                <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato/saveEsiti" id="formEsiti">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:.88rem;">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="ps-4" style="width:30%;">Studente</th>
                                    <th style="width:11%;">Voto teorico</th>
                                    <th style="width:11%;">Voto pratico</th>
                                    <th style="width:11%;" class="text-muted">Finale</th>
                                    <th style="width:16%;">Esito</th>
                                    <th style="width:8%;" class="text-center">Assente</th>
                                    <th>Note</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($iscrizioni as $iscr): ?>
                                <?php
                                    $vT = $iscr['voto_teorico'] ?? null;
                                    $vP = $iscr['voto_pratico'] ?? null;
                                    $vF = ($vT !== null && $vP !== null)
                                        ? round(((float)$vT + (float)$vP) / 2, 2)
                                        : ($vT ?? $vP);
                                ?>
                                <tr id="row-<?= $iscr['id'] ?>" class="<?= $iscr['esito'] === 'assente' ? 'table-danger' : '' ?>">
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
                                               name="esiti[<?= $iscr['id'] ?>][voto_teorico]"
                                               class="form-control form-control-sm voto-input voto-teorico"
                                               style="width:75px;"
                                               min="0" max="100" step="0.5"
                                               value="<?= $vT !== null ? $vT : '' ?>"
                                               placeholder="—"
                                               oninput="aggiornaFinale(this)"
                                               <?= $iscr['esito'] === 'assente' ? 'disabled' : '' ?>>
                                    </td>
                                    <td class="align-middle">
                                        <input type="number"
                                               name="esiti[<?= $iscr['id'] ?>][voto_pratico]"
                                               class="form-control form-control-sm voto-input voto-pratico"
                                               style="width:75px;"
                                               min="0" max="100" step="0.5"
                                               value="<?= $vP !== null ? $vP : '' ?>"
                                               placeholder="—"
                                               oninput="aggiornaFinale(this)"
                                               <?= $iscr['esito'] === 'assente' ? 'disabled' : '' ?>>
                                    </td>
                                    <td class="align-middle text-muted small">
                                        <span class="voto-finale-display">
                                            <?= $vF !== null ? $vF : '—' ?>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <select name="esiti[<?= $iscr['id'] ?>][esito]"
                                                class="form-select form-select-sm esito-select"
                                                <?= $iscr['esito'] === 'assente' ? 'disabled' : '' ?>>
                                            <option value="">—</option>
                                            <option value="ammesso"     <?= $iscr['esito'] === 'ammesso' ? 'selected' : '' ?>>Ammesso</option>
                                            <option value="non_ammesso" <?= $iscr['esito'] === 'non_ammesso' ? 'selected' : '' ?>>Non ammesso</option>
                                        </select>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input assente-check" type="checkbox"
                                                   name="esiti[<?= $iscr['id'] ?>][assente]"
                                                   value="1"
                                                   onchange="toggleAssente(this)"
                                                   <?= $iscr['esito'] === 'assente' ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <input type="text"
                                               name="esiti[<?= $iscr['id'] ?>][note]"
                                               class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($iscr['note'] ?? '') ?>"
                                               placeholder="Note opzionali...">
                                    </td>
                                    <td class="align-middle text-end pe-3">
                                        <button type="button"
                                                class="btn btn-sm btn-link text-danger p-0 btn-disiscrivi-studente"
                                                title="Rimuovi"
                                                data-iscr-id="<?= $iscr['id'] ?>"
                                                data-nome="<?= htmlspecialchars($iscr['cognome'] . ' ' . $iscr['nome'], ENT_QUOTES) ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
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
                            <i class="fas fa-save me-2"></i>Salva esiti
                        </button>
                    </div>
                </form>
                <?php foreach ($iscrizioni as $iscr): ?>
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato/disiscrivi"
                      id="disiscrivi-<?= $iscr['id'] ?>" class="d-none">
                    <input type="hidden" name="iscr_id"  value="<?= $iscr['id'] ?>">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
                </form>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
function aggiornaFinale(input) {
    const row  = input.closest('tr');
    const vT   = parseFloat(row.querySelector('.voto-teorico')?.value);
    const vP   = parseFloat(row.querySelector('.voto-pratico')?.value);
    const span = row.querySelector('.voto-finale-display');
    if (!span) return;
    if (!isNaN(vT) && !isNaN(vP)) {
        span.textContent = Math.round((vT + vP) / 2 * 100) / 100;
    } else if (!isNaN(vT)) {
        span.textContent = vT;
    } else if (!isNaN(vP)) {
        span.textContent = vP;
    } else {
        span.textContent = '—';
    }
}

function toggleAssente(checkbox) {
    const row      = checkbox.closest('tr');
    const votiInp  = row.querySelectorAll('.voto-input');
    const esitoSel = row.querySelector('.esito-select');
    const span     = row.querySelector('.voto-finale-display');

    if (checkbox.checked) {
        row.classList.add('table-danger');
        votiInp.forEach(i => { i.value = ''; i.disabled = true; });
        if (esitoSel) { esitoSel.value = ''; esitoSel.disabled = true; }
        if (span) span.textContent = '—';
    } else {
        row.classList.remove('table-danger');
        votiInp.forEach(i => { i.disabled = false; });
        if (esitoSel) esitoSel.disabled = false;
    }
}

/* ── AJAX Commissione ─────────────────────────────────────────────────── */
const ruoloLabel = <?= json_encode(['presidente' => 'Presidente', 'commissario' => 'Commissario', 'segretario' => 'Segretario']) ?>;

/* ── Helpers UI ───────────────────────────────────────────────────────── */

/**
 * Mostra un banner flash (success / danger) identico a quelli PHP,
 * in cima alla pagina, con auto-dismiss dopo 4 s.
 */
function flashBanner(type, msg) {
    const icon  = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const div   = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show`;
    div.setAttribute('role', 'alert');
    div.innerHTML = `<i class="fas ${icon} me-2"></i>${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>`;

    // Inserisce prima del primo .row della pagina
    const anchor = document.querySelector('.row.g-4') ?? document.body;
    anchor.parentNode.insertBefore(div, anchor);

    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(div);
        bsAlert.close();
    }, 4000);
}

/**
 * Modal di conferma generico con stile Bootstrap del sito.
 * Restituisce una Promise<boolean>.
 */
function confermaModal({ titolo = 'Conferma', testo = 'Sei sicuro?', btnLabel = 'Conferma', btnClass = 'btn-danger' } = {}) {
    return new Promise(resolve => {
        // Riusa il modal se esiste già
        let modal = document.getElementById('modal-conferma-comm');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'modal-conferma-comm';
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            modal.setAttribute('aria-hidden', 'true');
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                            <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                                <span id="mcc-titolo"></span>
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                        </div>
                        <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;">
                            <span id="mcc-testo"></span>
                        </div>
                        <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Annulla
                            </button>
                            <button type="button" class="btn btn-sm" id="mcc-conferma">
                                <!-- testo dinamico -->
                            </button>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
        }

        modal.querySelector('#mcc-titolo').textContent = titolo;
        modal.querySelector('#mcc-testo').textContent  = testo;
        const btnConf = modal.querySelector('#mcc-conferma');
        btnConf.className = `btn btn-sm ${btnClass}`;
        btnConf.innerHTML = `<i class="fas fa-check me-1"></i>${btnLabel}`;

        // Rimuove vecchi listener clonando il bottone
        const fresh = btnConf.cloneNode(true);
        btnConf.replaceWith(fresh);

        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);

        fresh.addEventListener('click', () => {
            bsModal.hide();
            resolve(true);
        });
        modal.addEventListener('hidden.bs.modal', () => resolve(false), { once: true });

        bsModal.show();
    });
}

/* ── Logica commissione ───────────────────────────────────────────────── */

function commHtml(id, cognome, nome, ruolo) {
    const label = ruoloLabel[ruolo] ?? ruolo;
    const btnRimuovi = ruolo !== 'presidente' ? `
        <button type="button"
                class="btn btn-sm btn-link text-danger p-0"
                title="Rimuovi"
                onclick="rimuoviCommissario(this, ${id})">
            <i class="fas fa-times"></i>
        </button>` : '';
    return `
        <div class="d-flex align-items-center gap-2 p-2 rounded-3 comm-item" data-id="${id}"
             style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div style="width:28px;height:28px;border-radius:50%;background:#e8eef8;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-user-tie" style="color:#1e40af;font-size:.78rem;"></i>
            </div>
            <div class="flex-grow-1" style="font-size:.83rem;">
                <div class="fw-semibold" style="color:#0c1a3a;">${cognome} ${nome}</div>
                <div class="text-muted" style="font-size:.7rem;">${label}</div>
            </div>
            ${btnRimuovi}
        </div>`;
}

function aggiornaCountCommissione() {
    const count = document.querySelectorAll('#lista-commissione .comm-item').length;
    document.getElementById('badge-commissione').textContent = count;
}

async function rimuoviCommissario(btn, insegnanteId) {
    const confermato = await confermaModal({
        titolo   : 'Rimuovi dalla commissione',
        testo    : 'Vuoi rimuovere questo docente dalla commissione?',
        btnLabel : 'Rimuovi',
        btnClass : 'btn-danger',
    });
    if (!confermato) return;

    const esameId = <?= $esame['id'] ?>;
    const body    = new URLSearchParams({ esame_id: esameId, insegnante_id: insegnanteId });

    try {
        const r    = await fetch('<?= BASE_URL ?>esami-di-stato/remove-commissario', {
            method  : 'POST',
            headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body    : body.toString(),
        });
        const data = await r.json();
        if (!data.success) { flashBanner('danger', data.error ?? 'Errore durante la rimozione.'); return; }

        // Salva il nome prima di rimuovere l'elemento
        const item    = document.querySelector(`#lista-commissione .comm-item[data-id="${insegnanteId}"]`);
        const nomeTxt = item?.querySelector('.fw-semibold')?.textContent?.trim() ?? insegnanteId;
        if (item) item.remove();

        // Rimette il docente nella select
        const sel = document.querySelector('#add-comm-form select[name="insegnante_id"]');
        if (sel) {
            const opt       = document.createElement('option');
            opt.value       = insegnanteId;
            opt.textContent = nomeTxt;
            sel.appendChild(opt);
        }

        // Mostra "nessun docente" se lista vuota
        const lista = document.getElementById('lista-commissione');
        if (lista && lista.children.length === 0) {
            document.getElementById('empty-commissione').style.display = '';
        }

        aggiornaCountCommissione();
        flashBanner('success', 'Docente rimosso dalla commissione.');
    } catch {
        flashBanner('danger', 'Errore di rete. Riprova.');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const addForm = document.getElementById('add-comm-form');
    if (!addForm) return;

    addForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const esameId      = <?= $esame['id'] ?>;
        const sel          = addForm.querySelector('select[name="insegnante_id"]');
        const ruoloSel     = addForm.querySelector('select[name="ruolo"]');
        const insegnanteId = parseInt(sel.value);
        const ruolo        = ruoloSel.value;

        if (!insegnanteId) return;

        const body = new URLSearchParams({ esame_id: esameId, insegnante_id: insegnanteId, ruolo: ruolo });

        try {
            const r    = await fetch('<?= BASE_URL ?>esami-di-stato/add-commissario', {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : body.toString(),
            });
            const data = await r.json();
            if (!data.success) { flashBanner('danger', data.error ?? 'Errore durante l\'aggiunta.'); return; }

            // Aggiunge il commissario alla lista
            const lista = document.getElementById('lista-commissione');
            lista.insertAdjacentHTML('beforeend', commHtml(data.id, data.cognome, data.nome, data.ruolo));

            // Nasconde "nessun docente"
            document.getElementById('empty-commissione').style.display = 'none';

            // Rimuove il docente dalla select e resetta
            sel.querySelector(`option[value="${insegnanteId}"]`)?.remove();
            sel.value = '';

            aggiornaCountCommissione();
            flashBanner('success', `${data.cognome} ${data.nome} aggiunto alla commissione.`);
        } catch {
            flashBanner('danger', 'Errore di rete. Riprova.');
        }
    });
});

/* ── Modal eliminazioni ── */
document.addEventListener('click', function (e) {

    // Elimina allegato
    const btnAll = e.target.closest('.btn-elimina-allegato');
    if (btnAll) {
        document.getElementById('allegatoNomeDaEliminare').textContent = btnAll.dataset.nome;
        document.getElementById('allegatoIdDaEliminare').value         = btnAll.dataset.id;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaAllegato')).show();
        return;
    }

    // Disiscrivi studente
    const btnDis = e.target.closest('.btn-disiscrivi-studente');
    if (btnDis) {
        document.getElementById('studenteNomeDaDisiscriv').textContent = btnDis.dataset.nome;
        document.getElementById('iscrIdDaDisiscriv').value             = btnDis.dataset.iscrId;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDisiscrivi')).show();
    }
});
</script>

<!-- ── Modal Elimina Esame (dalla pagina detail) ── -->
<div class="modal fade" id="modalEliminaEsameDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina esame di stato
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-1" style="color:#374151;">
                    Stai per eliminare <strong><?= htmlspecialchars($esame['denominazione']) ?></strong>.
                </p>
                <p class="text-muted small mb-0">Verranno eliminati tutti i dati collegati. L'operazione è irreversibile.</p>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato/delete" class="d-inline">
                    <input type="hidden" name="id" value="<?= $esame['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Elimina Allegato ── -->
<div class="modal fade" id="modalEliminaAllegato" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-trash me-2 text-danger"></i>Elimina documento
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-1" style="color:#374151;">
                    Stai per eliminare il documento <strong id="allegatoNomeDaEliminare"></strong>.
                </p>
                <p class="text-muted small mb-0">L'operazione è irreversibile.</p>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato/delete-allegato" class="d-inline">
                    <input type="hidden" name="allegato_id" id="allegatoIdDaEliminare">
                    <input type="hidden" name="esame_id"    value="<?= $esame['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Disiscrivi Studente ── -->
<div class="modal fade" id="modalDisiscrivi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-user-minus me-2 text-danger"></i>Rimuovi studente
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0" style="color:#374151;">
                    Vuoi rimuovere <strong id="studenteNomeDaDisiscriv"></strong> da questo esame?
                </p>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato/disiscrivi" class="d-inline">
                    <input type="hidden" name="iscr_id"  id="iscrIdDaDisiscriv">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-user-minus me-1"></i>Rimuovi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
