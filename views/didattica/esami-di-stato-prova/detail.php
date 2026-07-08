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

$proveRows = $prove ?? [];
while (count($proveRows) < 2) {
    $proveRows[] = ['data' => '', 'ora_inizio' => '', 'ora_fine' => '', 'tipo_prova' => ''];
}
?>

<div class="mb-3">
    <a href="<?= BASE_URL ?>tutti-gli-esami-di-stato" class="btn btn-sm btn-outline-secondary">
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

<!-- ══════════════════════════════════════════════════════════════════
     DETTAGLIO ESAME — LARGHEZZA PIENA
═══════════════════════════════════════════════════════════════════ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0 fw-bold" style="color:#0c1a3a;">
                <i class="fas fa-landmark me-2" style="color:#1e40af;"></i><?= htmlspecialchars($esame['denominazione']) ?>
            </h5>
            <div class="text-muted small mt-1">
                <?= htmlspecialchars($esame['percorso_nome']) ?>
                · A.S. <?= htmlspecialchars($esame['anno_label'] ?? '—') ?>
                <?php if (!empty($esame['anno_formativo'])): ?>
                    · <span class="fw-semibold" style="color:#1e40af;"><?= htmlspecialchars($esame['anno_formativo']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge fs-6 px-3 py-2" style="background:<?= $colore ?>1a;color:<?= $colore ?>;border:1px solid <?= $colore ?>33;">
                <?= ucfirst(str_replace('_', ' ', $esame['stato'])) ?>
            </span>
            <button type="button" class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalModificaEsame">
                <i class="fas fa-pen me-1"></i>Modifica
            </button>
        </div>
    </div>
    <div class="card-body px-4 py-4">

        <!-- ── Info principali su 4 colonne ── -->
        <div class="row g-3 mb-4">

            <?php
            // Helper blocco info
            $info = function(string $label, ?string $val, string $icon = 'fa-info-circle') use (&$out): string {
                if (!$val) return '';
                return '
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="text-muted mb-1" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;">
                            <i class="fas ' . $icon . ' me-1"></i>' . $label . '
                        </div>
                        <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">' . htmlspecialchars($val) . '</div>
                    </div>
                </div>';
            };
            ?>

            <?= $info('Riferimento', ($riferimentoLabel[$esame['riferimento_tipo']] ?? $esame['riferimento_tipo']) . ($nomeAnno ? ' — ' . $nomeAnno : ''), 'fa-route') ?>
            <?= $info('Tipo', $esame['tipo'] ?? null, 'fa-tag') ?>
            <?= $info('Cod. corso', $esame['cod_corso'] ?? null, 'fa-barcode') ?>
            <?= $info('Cod. did. reg.', $esame['cod_did_reg'] ?? null, 'fa-hashtag') ?>
            <?= $info('Ore corso', isset($esame['ore_corso']) && $esame['ore_corso'] !== '' && $esame['ore_corso'] !== null ? $esame['ore_corso'] . ' ore' : null, 'fa-clock') ?>
            <?= $info('Ente gestore', $esame['ente_gestore'] ?? null, 'fa-building') ?>

            <?php if (!empty($esame['data_inizio_anno']) || !empty($esame['data_fine_anno'])): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="text-muted mb-1" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;">
                        <i class="fas fa-calendar-alt me-1"></i>Anno accademico
                    </div>
                    <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                        <?= !empty($esame['data_inizio_anno']) ? date('d/m/Y', strtotime($esame['data_inizio_anno'])) : '—' ?>
                        →
                        <?= !empty($esame['data_fine_anno'])   ? date('d/m/Y', strtotime($esame['data_fine_anno']))   : '—' ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($esame['data'])): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="text-muted mb-1" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;">
                        <i class="fas fa-calendar-day me-1"></i>Data esame
                    </div>
                    <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                        <?= date('d/m/Y', strtotime($esame['data'])) ?>
                        <?= $esame['ora_inizio'] ? ' · ' . substr($esame['ora_inizio'], 0, 5) : '' ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($esame['sede_presso']) || !empty($esame['sede_via'])): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="text-muted mb-1" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;">
                        <i class="fas fa-map-marker-alt me-1"></i>Sede
                    </div>
                    <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                        <?php if (!empty($esame['sede_presso'])): ?><div><?= htmlspecialchars($esame['sede_presso']) ?></div><?php endif; ?>
                        <?php if (!empty($esame['sede_via'])): ?><div class="text-muted fw-normal" style="font-size:.82rem;"><?= htmlspecialchars($esame['sede_via']) ?></div><?php endif; ?>
                        <?php if (!empty($esame['sede_comune'])): ?><div class="text-muted fw-normal" style="font-size:.82rem;"><?= htmlspecialchars($esame['sede_comune']) ?></div><?php endif; ?>
                        <?php if (!empty($esame['sede_telefono'])): ?><div class="text-muted fw-normal" style="font-size:.82rem;">Tel. <?= htmlspecialchars($esame['sede_telefono']) ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($esame['note']): ?>
            <div class="col-12">
                <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="text-muted mb-1" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;">
                        <i class="fas fa-sticky-note me-1"></i>Note
                    </div>
                    <div style="color:#374151;font-size:.88rem;"><?= nl2br(htmlspecialchars($esame['note'])) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Statistiche + Prove + Commissione su stessa riga ── -->
        <div class="row g-3">

            <!-- Statistiche -->
            <div class="col-lg-3 col-md-4">
                <div class="rounded-3 text-center" style="background:#f8fafc;border:1px solid #e2e8f0;overflow:hidden;">
                    <div class="row g-0">
                        <div class="col-4 py-3 border-end">
                            <div class="fw-bold fs-5" style="color:#0c1a3a;"><?= $totale ?></div>
                            <div class="text-muted" style="font-size:.68rem;">Iscritti</div>
                        </div>
                        <div class="col-4 py-3 border-end">
                            <div class="fw-bold fs-5" style="color:#10b981;"><?= $numValutati ?></div>
                            <div class="text-muted" style="font-size:.68rem;">Valutati</div>
                        </div>
                        <div class="col-4 py-3">
                            <div class="fw-bold fs-5" style="color:#ef4444;"><?= $numAssenti ?></div>
                            <div class="text-muted" style="font-size:.68rem;">Assenti</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commissione -->
            <div class="col-lg-6 col-md-4">
                <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="text-muted mb-2" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;">
                        <i class="fas fa-user-shield me-1"></i>Commissione
                        <span class="badge ms-1" style="background:#e8eef8;color:#1e40af;font-size:.68rem;"><?= count($commissione) ?></span>
                    </div>
                    <?php if (empty($commissione)): ?>
                        <div class="text-muted small fst-italic">Nessun docente in commissione.</div>
                    <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($commissione as $c):
                            $isP     = $c['ruolo'] === 'presidente';
                            $badgeBg = $isP ? '#fef3c7' : '#e8eef8';
                            $badgeClr= $isP ? '#d97706' : '#1e40af';
                        ?>
                        <div class="d-flex align-items-center gap-2 px-2 py-1 rounded-3"
                             style="background:white;border:1px solid #e2e8f0;font-size:.83rem;">
                            <i class="fas fa-user-tie" style="color:<?= $badgeClr ?>;font-size:.75rem;"></i>
                            <span style="color:#0c1a3a;"><?= htmlspecialchars($c['cognome'] . ' ' . $c['nome']) ?></span>
                            <span class="badge" style="background:<?= $badgeBg ?>;color:<?= $badgeClr ?>;font-size:.65rem;">
                                <?= $ruoloLabel[$c['ruolo']] ?? $c['ruolo'] ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /row statistiche+prove+commissione -->

        <!-- ── Sessioni d'esame ── -->
        <?php
        $haProve = !empty($proveRows[0]['data']) || !empty($proveRows[0]['ora_inizio'])
                || !empty($proveRows[1]['data']) || !empty($proveRows[1]['ora_inizio']);
        $provaLabels = [
            0 => ['titolo' => 'Prova 1', 'color' => '#1e40af', 'bg' => '#f0f4ff', 'border' => '#c7d7f7'],
            1 => ['titolo' => 'Prova 2', 'color' => '#059669', 'bg' => '#f0fdf8', 'border' => '#a7f3d0'],
        ];
        ?>
        <hr class="my-3">
        <div class="row g-3">
            <div class="col-12">
                <div class="fw-semibold mb-2" style="color:#0c1a3a;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">
                    <i class="fas fa-calendar-alt me-1" style="color:#1e40af;"></i>Sessioni d'esame
                </div>
            </div>
            <?php foreach ([0,1] as $i):
                $p   = $proveRows[$i] ?? [];
                $lbl = $provaLabels[$i];
                $hasData = !empty($p['data']) || !empty($p['ora_inizio']);
            ?>
            <div class="col-md-6">
                <div class="p-3 rounded-3 h-100" style="background:<?= $lbl['bg'] ?>;border:1px solid <?= $lbl['border'] ?>;">
                    <div class="fw-semibold mb-2" style="color:<?= $lbl['color'] ?>;font-size:.82rem;">
                        <i class="fas fa-clock me-1"></i><?= $lbl['titolo'] ?>
                    </div>
                    <?php if ($hasData): ?>
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        <?php if (!empty($p['data'])): ?>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;">Data</div>
                            <div class="fw-semibold" style="color:#0c1a3a;font-size:.92rem;">
                                <?= date('d/m/Y', strtotime($p['data'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($p['ora_inizio'])): ?>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;">Orario</div>
                            <div class="fw-semibold" style="color:#0c1a3a;font-size:.92rem;">
                                <?= substr($p['ora_inizio'],0,5) ?>
                                <?= !empty($p['ora_fine']) ? ' – ' . substr($p['ora_fine'],0,5) : '' ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>esami-di-stato-prova/scarica-foglio-presenze?id=<?= $esame['id'] ?>&prova=<?= $i + 1 ?>"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-clipboard-list me-1"></i>Foglio presenze
                    </a>
                    <?php else: ?>
                    <div class="text-muted" style="font-size:.82rem;">
                        <i class="fas fa-minus me-1"></i>Non configurata
                        <button type="button" class="btn btn-link btn-sm p-0 ms-2" style="font-size:.78rem;"
                                data-bs-toggle="modal" data-bs-target="#modalModificaEsame">
                            Aggiungi
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div><!-- /sessioni -->

    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     TABELLA STUDENTI — LARGHEZZA PIENA
═══════════════════════════════════════════════════════════════════ -->
<div class="card border-0 shadow-sm mb-4">
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
                <span style="font-size:.78rem;">Iscrivi gli studenti dal pannello qui sotto.</span>
            </div>
        <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/saveEsiti" id="formEsiti">
            <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:.88rem;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="ps-4" style="width:25%;">Studente</th>
                            <th style="width:10%;">Voto teorico</th>
                            <th style="width:10%;">Voto pratico</th>
                            <th style="width:8%;" class="text-muted">Finale</th>
                            <th style="width:14%;">Esito</th>
                            <th style="width:7%;" class="text-center">Assente</th>
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
                                    <option value="ammesso"     <?= $iscr['esito'] === 'ammesso'     ? 'selected' : '' ?>>Ammesso</option>
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
                    <i class="fas fa-info-circle me-1"></i>Le modifiche non salvate andranno perse
                </span>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Salva esiti
                </button>
            </div>
        </form>
        <?php foreach ($iscrizioni as $iscr): ?>
        <form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/disiscrivi"
              id="disiscrivi-<?= $iscr['id'] ?>" class="d-none">
            <input type="hidden" name="iscr_id"  value="<?= $iscr['id'] ?>">
            <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
        </form>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     DOCUMENTI ALLEGATI + ISCRIVI STUDENTE — AFFIANCATI
═══════════════════════════════════════════════════════════════════ -->
<div class="row g-4 mb-4">

    <!-- Documenti allegati -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-paperclip me-2" style="color:#1e40af;"></i>Documenti allegati
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($allegati) ?></span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/upload-allegato"
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
                                   target="_blank" class="text-decoration-none text-truncate"
                                   style="color:#0c1a3a;font-size:.85rem;max-width:75%;">
                                    <i class="fas <?= $icon ?> me-2"></i>
                                    <?= htmlspecialchars($all['original_name']) ?>
                                </a>
                                <button type="button" class="btn btn-link p-0 text-danger btn-elimina-allegato"
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
    </div>

    <!-- Iscrivi studente + Elimina esame -->
    <div class="col-lg-6">
        <?php if (!empty($studentiDisp)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-user-plus me-2" style="color:#1e40af;"></i>Iscrivi studente
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/iscrivi">
                    <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
                    <select name="studente_id" class="form-select form-select-sm mb-2" required>
                        <option value="">— Seleziona studente —</option>
                        <?php foreach ($studentiDisp as $s): ?>
                            <option value="<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                                <?php if ($s['codice_fiscale']): ?>(<?= htmlspecialchars($s['codice_fiscale']) ?>)<?php endif; ?>
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

</div><!-- /row documenti + iscrivi -->

<!-- ── Pulsanti download ── -->
<div class="d-flex flex-wrap gap-2 pt-2">
    <a href="<?= BASE_URL ?>esami-di-stato-prova/stampa?id=<?= $esame['id'] ?>"
       class="btn btn-primary">
        <i class="fas fa-file-word me-2"></i>Scarica Word (CAL-ESA)
    </a>
    <a href="<?= BASE_URL ?>esami-di-stato-prova/scarica-verbale?id=<?= $esame['id'] ?>"
       class="btn btn-outline-primary">
        <i class="fas fa-file-word me-2"></i>Scarica verbale esame di stato
    </a>
</div>

<script>
const _esameId = <?= $esame['id'] ?>;
const _ruoloLabel = <?= json_encode(['presidente' => 'Presidente', 'commissario' => 'Commissario', 'segretario' => 'Segretario']) ?>;

function modalAggiornaBadge() {
    const n = document.querySelectorAll('#modal-lista-comm .modal-comm-item').length;
    const badge = document.getElementById('modal-badge-comm');
    if (badge) badge.textContent = n;
    const empty = document.getElementById('modal-empty-comm');
    if (empty) empty.style.display = n ? 'none' : '';
}

async function modalAggiungiCommissario() {
    const sel              = document.getElementById('modal-sel-docente');
    const insId            = parseInt(sel.value);
    if (!insId) return;
    const ruolo            = document.getElementById('modal-sel-ruolo').value;
    const inRappresentanza = document.getElementById('modal-sel-rappresentanza').value;
    const opt              = sel.options[sel.selectedIndex];
    const cognome          = opt.dataset.cognome;
    const nome             = opt.dataset.nome;

    const body = new URLSearchParams({ esame_id: _esameId, insegnante_id: insId, ruolo, in_rappresentanza: inRappresentanza });
    try {
        const r    = await fetch('<?= BASE_URL ?>esami-di-stato-prova/add-commissario', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
        });
        const data = await r.json();
        if (!data.success) { alert(data.error ?? 'Errore durante l\'aggiunta.'); return; }

        // Rimuovi dalla select docenti
        opt.remove();
        sel.value = '';
        document.getElementById('modal-sel-rappresentanza').value = '';

        // Aggiungi alla lista
        const badgeBg  = ruolo === 'presidente' ? '#fef3c7' : (ruolo === 'segretario' ? '#f0fdf8' : '#e8eef8');
        const badgeClr = ruolo === 'presidente' ? '#d97706' : (ruolo === 'segretario' ? '#059669' : '#1e40af');
        const label    = _ruoloLabel[ruolo] ?? ruolo;
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center gap-2 p-2 mb-2 rounded-3 modal-comm-item';
        div.style.cssText = 'background:#f8fafc;border:1px solid #e2e8f0;';
        const nomeCompleto = cognome + ' ' + nome;
        const rappHtml = inRappresentanza
            ? `<div class="text-muted" style="font-size:.75rem;">${inRappresentanza}</div>`
            : '';
        div.innerHTML = `
            <i class="fas fa-user-tie" style="color:${badgeClr};font-size:.85rem;"></i>
            <div class="flex-grow-1">
                <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;">${nomeCompleto}</div>
                ${rappHtml}
            </div>
            <span class="badge" style="background:${badgeBg};color:${badgeClr};font-size:.7rem;">${label}</span>
            <button type="button"
                    class="btn btn-sm btn-link text-danger p-0 btn-modal-rimuovi-comm"
                    data-ins-id="${insId}"
                    data-nome="${nomeCompleto}">
                <i class="fas fa-times"></i>
            </button>`;
        document.getElementById('modal-lista-comm').appendChild(div);
        modalAggiornaBadge();
    } catch { alert('Errore di rete. Riprova.'); }
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-modal-rimuovi-comm');
    if (!btn) return;
    const insId       = parseInt(btn.dataset.insId);
    const nomeCompleto = btn.dataset.nome;
    const body = new URLSearchParams({ esame_id: _esameId, insegnante_id: insId });
    fetch('<?= BASE_URL ?>esami-di-stato-prova/remove-commissario', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { alert(data.error ?? 'Errore durante la rimozione.'); return; }
        btn.closest('.modal-comm-item').remove();
        const sel = document.getElementById('modal-sel-docente');
        const opt = document.createElement('option');
        opt.value = insId;
        opt.textContent = nomeCompleto;
        sel.appendChild(opt);
        modalAggiornaBadge();
    })
    .catch(() => alert('Errore di rete. Riprova.'));
});

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

const ruoloLabel = <?= json_encode(['presidente' => 'Presidente', 'commissario' => 'Commissario', 'segretario' => 'Segretario']) ?>;

function flashBanner(type, msg) {
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const div  = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show`;
    div.setAttribute('role', 'alert');
    div.innerHTML = `<i class="fas ${icon} me-2"></i>${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>`;
    const anchor = document.querySelector('.card.border-0.shadow-sm') ?? document.body;
    anchor.parentNode.insertBefore(div, anchor);
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(div).close(), 4000);
}

function confermaModal({ titolo = 'Conferma', testo = 'Sei sicuro?', btnLabel = 'Conferma', btnClass = 'btn-danger' } = {}) {
    return new Promise(resolve => {
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
                            <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;"><span id="mcc-titolo"></span></h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body px-4 py-3" style="font-size:.9rem;color:#374151;"><span id="mcc-testo"></span></div>
                        <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Annulla</button>
                            <button type="button" class="btn btn-sm" id="mcc-conferma"></button>
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
        const fresh = btnConf.cloneNode(true);
        btnConf.replaceWith(fresh);
        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
        fresh.addEventListener('click', () => { bsModal.hide(); resolve(true); });
        modal.addEventListener('hidden.bs.modal', () => resolve(false), { once: true });
        bsModal.show();
    });
}

async function rimuoviCommissario(btn, insegnanteId) {
    const confermato = await confermaModal({
        titolo: 'Rimuovi dalla commissione',
        testo: 'Vuoi rimuovere questo docente dalla commissione?',
        btnLabel: 'Rimuovi', btnClass: 'btn-danger',
    });
    if (!confermato) return;

    const esameId = <?= $esame['id'] ?>;
    const body    = new URLSearchParams({ esame_id: esameId, insegnante_id: insegnanteId });

    try {
        const r    = await fetch('<?= BASE_URL ?>esami-di-stato-prova/remove-commissario', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
        });
        const data = await r.json();
        if (!data.success) { flashBanner('danger', data.error ?? 'Errore durante la rimozione.'); return; }

        const item    = document.querySelector(`#lista-commissione .comm-item[data-id="${insegnanteId}"]`);
        const nomeTxt = item?.querySelector('.fw-semibold')?.textContent?.trim() ?? insegnanteId;
        if (item) item.remove();

        const sel = document.querySelector('#add-comm-form select[name="insegnante_id"]');
        if (sel) {
            const opt = document.createElement('option');
            opt.value = insegnanteId;
            opt.textContent = nomeTxt;
            sel.appendChild(opt);
        }

        const lista = document.getElementById('lista-commissione');
        if (lista && lista.children.length === 0) {
            document.getElementById('empty-commissione').style.display = '';
        }

        aggiornaCountCommissione();
        flashBanner('success', 'Docente rimosso dalla commissione.');
    } catch { flashBanner('danger', 'Errore di rete. Riprova.'); }
}

function aggiornaCountCommissione() {
    const count = document.querySelectorAll('#lista-commissione .comm-item').length;
    const badge = document.getElementById('badge-commissione');
    if (badge) badge.textContent = count;
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
            const r    = await fetch('<?= BASE_URL ?>esami-di-stato-prova/add-commissario', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
            });
            const data = await r.json();
            if (!data.success) { flashBanner('danger', data.error ?? 'Errore durante l\'aggiunta.'); return; }

            const lista = document.getElementById('lista-commissione');
            if (lista) lista.insertAdjacentHTML('beforeend', commHtml(data.id, data.cognome, data.nome, data.ruolo));

            document.getElementById('empty-commissione')?.style && (document.getElementById('empty-commissione').style.display = 'none');
            sel.querySelector(`option[value="${insegnanteId}"]`)?.remove();
            sel.value = '';
            aggiornaCountCommissione();
            flashBanner('success', `${data.cognome} ${data.nome} aggiunto alla commissione.`);
        } catch { flashBanner('danger', 'Errore di rete. Riprova.'); }
    });
});

function commHtml(id, cognome, nome, ruolo) {
    const label = ruoloLabel[ruolo] ?? ruolo;
    const btnRimuovi = ruolo !== 'presidente' ? `
        <button type="button" class="btn btn-sm btn-link text-danger p-0" title="Rimuovi"
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

document.addEventListener('click', function (e) {
    const btnAll = e.target.closest('.btn-elimina-allegato');
    if (btnAll) {
        document.getElementById('allegatoNomeDaEliminare').textContent = btnAll.dataset.nome;
        document.getElementById('allegatoIdDaEliminare').value         = btnAll.dataset.id;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaAllegato')).show();
        return;
    }
    const btnDis = e.target.closest('.btn-disiscrivi-studente');
    if (btnDis) {
        document.getElementById('studenteNomeDaDisiscriv').textContent = btnDis.dataset.nome;
        document.getElementById('iscrIdDaDisiscriv').value             = btnDis.dataset.iscrId;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDisiscrivi')).show();
    }
});
</script>

<!-- ── Modal Modifica Esame ── -->
<div class="modal fade" id="modalModificaEsame" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-pen me-2" style="color:#1e40af;"></i>Modifica dettagli esame
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/update">
                <input type="hidden" name="esame_id" value="<?= $esame['id'] ?>">
                <div class="modal-body px-4 py-4" style="overflow-y: auto; max-height: calc(100vh - 160px);">

                    <!-- Dati principali -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="fw-semibold mb-2" style="color:#0c1a3a;font-size:.85rem;text-transform:uppercase;letter-spacing:.04em;">
                                <i class="fas fa-file-alt me-1" style="color:#1e40af;"></i>Dati principali
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Denominazione <span class="text-danger">*</span></label>
                            <input type="text" name="denominazione" class="form-control"
                                   value="<?= htmlspecialchars($esame['denominazione']) ?>" required maxlength="255">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small fw-semibold">Tipo</label>
                            <input type="text" name="tipo" class="form-control"
                                   value="<?= htmlspecialchars($esame['tipo'] ?? '') ?>" maxlength="100">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small fw-semibold">Stato</label>
                            <select name="stato" class="form-select">
                                <option value="programmato"  <?= ($esame['stato'] ?? '') === 'programmato'  ? 'selected' : '' ?>>Programmato</option>
                                <option value="in_corso"     <?= ($esame['stato'] ?? '') === 'in_corso'     ? 'selected' : '' ?>>In corso</option>
                                <option value="completato"   <?= ($esame['stato'] ?? '') === 'completato'   ? 'selected' : '' ?>>Completato</option>
                                <option value="annullato"    <?= ($esame['stato'] ?? '') === 'annullato'    ? 'selected' : '' ?>>Annullato</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small fw-semibold">Codice corso</label>
                            <input type="text" name="cod_corso" class="form-control"
                                   value="<?= htmlspecialchars($esame['cod_corso'] ?? '') ?>" maxlength="50">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label small fw-semibold">Cod. did. reg.</label>
                            <input type="text" name="cod_did_reg" class="form-control"
                                   value="<?= htmlspecialchars($esame['cod_did_reg'] ?? '') ?>" maxlength="50">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label small fw-semibold">Ore corso</label>
                            <input type="number" name="ore_corso" class="form-control" min="0"
                                   value="<?= htmlspecialchars($esame['ore_corso'] ?? '') ?>">
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label small fw-semibold">Ente gestore</label>
                            <input type="text" name="ente_gestore" class="form-control"
                                   value="<?= htmlspecialchars($esame['ente_gestore'] ?? '') ?>" maxlength="255">
                        </div>
                    </div>


                    <hr class="my-3">

                    <!-- Prove -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="fw-semibold mb-2" style="color:#0c1a3a;font-size:.85rem;text-transform:uppercase;letter-spacing:.04em;">
                                <i class="fas fa-file-alt me-1" style="color:#1e40af;"></i>Prove
                            </div>
                        </div>
                        <?php
                        $prova1 = $proveRows[0] ?? [];
                        $prova2 = $proveRows[1] ?? [];
                        ?>
                        <!-- Prova 1 -->
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3" style="background:#f0f4ff;border:1px solid #c7d7f7;">
                                <div class="fw-semibold mb-2" style="color:#1e40af;font-size:.85rem;">Prova 1</div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold mb-1">Data</label>
                                        <input type="date" name="prova_1[data]" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($prova1['data'] ?? '') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1">Ora inizio</label>
                                        <input type="time" name="prova_1[ora_inizio]" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($prova1['ora_inizio'] ?? '') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1">Ora fine</label>
                                        <input type="time" name="prova_1[ora_fine]" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($prova1['ora_fine'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Prova 2 -->
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3" style="background:#f0fdf8;border:1px solid #a7f3d0;">
                                <div class="fw-semibold mb-2" style="color:#059669;font-size:.85rem;">Prova 2</div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold mb-1">Data</label>
                                        <input type="date" name="prova_2[data]" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($prova2['data'] ?? '') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1">Ora inizio</label>
                                        <input type="time" name="prova_2[ora_inizio]" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($prova2['ora_inizio'] ?? '') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1">Ora fine</label>
                                        <input type="time" name="prova_2[ora_fine]" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($prova2['ora_fine'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Commissione -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="fw-semibold mb-3" style="color:#0c1a3a;font-size:.85rem;text-transform:uppercase;letter-spacing:.04em;">
                                <i class="fas fa-user-shield me-1" style="color:#1e40af;"></i>Commissione d'esame
                                <span id="modal-badge-comm" class="badge ms-1" style="background:#e8eef8;color:#1e40af;font-size:.7rem;"><?= count($commissione) ?></span>
                            </div>

                            <!-- Aggiungi membro -->
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <select id="modal-sel-docente" class="form-select form-select-sm">
                                        <option value="">— Seleziona docente —</option>
                                        <?php
                                        $commInsIds = array_column($commissione, 'id');
                                        foreach ($docenti as $d):
                                            if (in_array($d['id'], $commInsIds)) continue;
                                        ?>
                                            <option value="<?= $d['id'] ?>"
                                                    data-cognome="<?= htmlspecialchars($d['cognome'], ENT_QUOTES) ?>"
                                                    data-nome="<?= htmlspecialchars($d['nome'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($d['cognome'] . ' ' . $d['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="modal-sel-ruolo" class="form-select form-select-sm">
                                        <option value="commissario">Commissario</option>
                                        <option value="presidente">Presidente</option>
                                        <option value="segretario">Segretario</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select id="modal-sel-rappresentanza" class="form-select form-select-sm">
                                        <option value="">— In rappresentanza —</option>
                                        <option value="AMMINISTRAZIONE REGIONALE">Amministrazione Regionale</option>
                                        <option value="DIREZ.NE PROV. LE DEL LAVORO">Direz.ne Prov.le del Lavoro</option>
                                        <option value="PROVVEDITORATO AGLI STUDI">Provveditorato agli Studi</option>
                                        <option value="DOCENTI CORSO">Docenti Corso</option>
                                        <option value="ASS. CATEGORIA - LAV. AUTON.">Ass. Categoria - Lav. Auton.</option>
                                        <option value="OO. SS. DEI LAV. DIPENDENTI">OO. SS. dei Lav. Dipendenti</option>
                                    </select>
                                </div>
                                <div class="col-md-1 d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="modalAggiungiCommissario()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Lista membri -->
                            <div id="modal-lista-comm">
                                <?php if (empty($commissione)): ?>
                                    <p id="modal-empty-comm" class="text-muted small fst-italic mb-0">
                                        <i class="fas fa-info-circle me-1"></i>Nessun docente in commissione.
                                    </p>
                                <?php else: ?>
                                    <p id="modal-empty-comm" class="text-muted small fst-italic mb-0" style="display:none;">
                                        <i class="fas fa-info-circle me-1"></i>Nessun docente in commissione.
                                    </p>
                                    <?php foreach ($commissione as $c):
                                        $isP     = $c['ruolo'] === 'presidente';
                                        $badgeBg = $isP ? '#fef3c7' : ($c['ruolo'] === 'segretario' ? '#f0fdf8' : '#e8eef8');
                                        $badgeClr= $isP ? '#d97706' : ($c['ruolo'] === 'segretario' ? '#059669' : '#1e40af');
                                        $ruoloLbl= $ruoloLabel[$c['ruolo']] ?? ucfirst($c['ruolo']);
                                    ?>
                                    <div class="d-flex align-items-center gap-2 p-2 mb-2 rounded-3 modal-comm-item"
                                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                                        <i class="fas fa-user-tie" style="color:<?= $badgeClr ?>;font-size:.85rem;"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="color:#0c1a3a;font-size:.88rem;">
                                                <?= htmlspecialchars($c['cognome'] . ' ' . $c['nome']) ?>
                                            </div>
                                            <?php if (!empty($c['in_rappresentanza'])): ?>
                                            <div class="text-muted" style="font-size:.75rem;">
                                                <?= htmlspecialchars($c['in_rappresentanza']) ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge" style="background:<?= $badgeBg ?>;color:<?= $badgeClr ?>;font-size:.7rem;">
                                            <?= $ruoloLbl ?>
                                        </span>
                                        <button type="button"
                                                class="btn btn-sm btn-link text-danger p-0 btn-modal-rimuovi-comm"
                                                data-ins-id="<?= $c['id'] ?>"
                                                data-nome="<?= htmlspecialchars($c['cognome'] . ' ' . $c['nome']) ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Note -->
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Note</label>
                            <textarea name="note" class="form-control" rows="3"><?= htmlspecialchars($esame['note'] ?? '') ?></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annulla
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i>Salva modifiche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Elimina Esame ── -->
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
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/delete" class="d-inline">
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
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/delete-allegato" class="d-inline">
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
                <form method="POST" action="<?= BASE_URL ?>esami-di-stato-prova/disiscrivi" class="d-inline">
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
