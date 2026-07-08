<?php
$ordinali     = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
                 6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];
$percorsoId   = (int)($_GET['percorso_id'] ?? ($iscrizioni[0]['percorso_id'] ?? 0));

// Trova l'iscrizione relativa al percorso filtrato (o la prima disponibile)
$iscrizioneCorrента = null;
foreach ($iscrizioni as $isc) {
    if (!$percorsoId || $isc['percorso_id'] == $percorsoId) {
        $iscrizioneCorrента = $isc;
        break;
    }
}
$iscrizioneCorrента = $iscrizioneCorrента ?? ($iscrizioni[0] ?? null);

$percorsoNome = $iscrizioneCorrента['percorso_nome'] ?? '—';
// Anno scolastico dal percorso stesso (non dall'anno attivo globale)
$annoLabel    = $iscrizioneCorrента['anno_label']    ?? ($annoAttivo['anno'] ?? '—');
$nomeAnno     = $annoCorrente ? ($ordinali[$annoCorrente['anno_numero']] ?? '') : '—';
?>

<div class="mb-3">
    <a href="<?= BASE_URL ?>studenti/detail/<?= $studente['id'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Torna al profilo
    </a>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     INTESTAZIONE
═══════════════════════════════════════════════════════════════════ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0 fw-bold" style="color:#0c1a3a;">
                <i class="fas fa-star me-2" style="color:#1e40af;"></i>Scheda di valutazione — Esame finale
            </h5>
            <div class="text-muted small mt-1">
                <?= htmlspecialchars($studente['cognome'] . ' ' . $studente['nome']) ?>
                · <?= htmlspecialchars($percorsoNome) ?>
                <?php if ($nomeAnno !== '—'): ?> · <?= htmlspecialchars($nomeAnno) ?><?php endif; ?>
                · A.S. <?= htmlspecialchars($annoLabel) ?>
            </div>
        </div>
    </div>
    <div class="card-body px-4 py-4">

        <!-- Info studente su 4 colonne -->
        <div class="row g-3">
            <?php
            $infoBox = function(string $label, ?string $val, string $icon) {
                if (!$val) return '';
                return '<div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="text-muted mb-1" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;">
                            <i class="fas ' . $icon . ' me-1"></i>' . $label . '
                        </div>
                        <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">' . htmlspecialchars($val) . '</div>
                    </div>
                </div>';
            };
            echo $infoBox('Studente', $studente['cognome'] . ' ' . $studente['nome'], 'fa-user');
            echo $infoBox('Percorso', $percorsoNome, 'fa-route');
            echo $infoBox('Anno di corso', $nomeAnno !== '—' ? $nomeAnno : null, 'fa-graduation-cap');
            echo $infoBox('Anno scolastico', $annoLabel, 'fa-calendar');
            echo $infoBox('Codice fiscale', $studente['codice_fiscale'] ?? null, 'fa-id-card');
            echo $infoBox('Data di nascita', $studente['data_nascita'] ? date('d/m/Y', strtotime($studente['data_nascita'])) : null, 'fa-birthday-cake');
            echo $infoBox('Email', $studente['email'] ?? null, 'fa-envelope');
            ?>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     FORM DETTAGLI
═══════════════════════════════════════════════════════════════════ -->
<form method="POST" action="">

    <!-- Dettagli del corso -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <i class="fas fa-route me-2" style="color:#1e40af;"></i>Dettagli del corso
            </h6>
            <?php if ($esameDiStato): ?>
            <a href="<?= BASE_URL ?>esami-di-stato-prova/detail/<?= $esameDiStato['id'] ?>"
               class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i>Vai all'esame di stato
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body px-4 py-4">
            <?php if (!$esameDiStato): ?>
                <div class="alert alert-warning mb-0" style="font-size:.88rem;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Nessun esame di stato trovato per l'anno di corso attuale dello studente in questo percorso.
                    Verifica che esista un esame di stato associato all'anno di corso corretto.
                </div>
            <?php else: ?>
            <div class="row g-3">
                <div class="col-lg-8">
                    <label class="form-label small fw-semibold">Titolo del corso</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($esameDiStato['denominazione'] ?? '') ?>" readonly style="background:#f8fafc;">
                </div>
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">Codice</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($esameDiStato['cod_corso'] ?? '') ?>" readonly style="background:#f8fafc;">
                </div>
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">Numero di scheda</label>
                    <input type="text" class="form-control" name="numero_scheda" placeholder="Inserisci il numero di scheda">
                </div>
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">Anno formativo</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars(trim(($nomeAnno !== '—' ? $nomeAnno . ' ' : '') . $annoLabel)) ?>" readonly style="background:#f8fafc;">
                </div>
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">Organismo gestore</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($esameDiStato['ente_gestore'] ?? '') ?>" readonly style="background:#f8fafc;">
                </div>
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">Ore corso</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($esameDiStato['ore_corso'] ?? '') ?>" readonly style="background:#f8fafc;">
                </div>
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">Ore assenza</label>
                    <input type="number" class="form-control" name="ore_assenza" min="0" placeholder="Inserisci le ore di assenza">
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Valutazione del team dei docenti -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <i class="fas fa-chalkboard-teacher me-2" style="color:#1e40af;"></i>Valutazione del team dei docenti
                <span class="text-muted fw-normal small ms-1">(max 10/100)</span>
            </h6>
        </div>
        <div class="card-body px-4 py-4">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small fw-semibold">Obiettivo prefissato</label>
                    <textarea class="form-control" name="obiettivo_prefissato" rows="6" placeholder="Descrivi l'obiettivo prefissato..."></textarea>
                </div>
                <div class="col-lg-6">
                    <label class="form-label small fw-semibold">Materie e/o area didattica</label>
                    <input type="text" class="form-control" name="materie_area_didattica" placeholder="Inserisci materie o area didattica...">
                </div>
                <div class="col-lg-6">
                    <label class="form-label small fw-semibold">Verifiche didattiche</label>
                    <input type="text" class="form-control" name="verifiche_didattiche" placeholder="Inserisci le verifiche didattiche...">
                </div>

                <!-- Ammissione esami finali -->
                <div class="col-12 mt-2">
                    <label class="form-label small fw-semibold">Ammissione esami finali</label>
                    <input type="text" class="form-control" name="ammissione_esami_finali" placeholder="">
                </div>

                <!-- Tabella Materie / Assenze -->
                <div class="col-12">
                    <label class="form-label small fw-semibold mb-2">Materie e assenze</label>
                    <?php
                    $materieTabella = [];
                    foreach ($blocci as $b) {
                        if (!$percorsoId || $b['iscrizione']['percorso_id'] == $percorsoId) {
                            $materieTabella = $b['materieAnno'] ?? [];
                            break;
                        }
                    }
                    // Calcola quante righe servono: ceil(materie / 3), minimo 3
                    $numRighe = max(3, (int)ceil(count($materieTabella) / 3));
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" style="font-size:.85rem;">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="text-center py-2" style="width:16.66%;color:#1e40af;">Materie</th>
                                    <th class="text-center py-2" style="width:16.66%;">Assenze</th>
                                    <th class="text-center py-2" style="width:16.66%;color:#1e40af;">Materie</th>
                                    <th class="text-center py-2" style="width:16.66%;">Assenze</th>
                                    <th class="text-center py-2" style="width:16.66%;color:#1e40af;">Materie</th>
                                    <th class="text-center py-2" style="width:16.66%;">Assenze</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($r = 0; $r < $numRighe; $r++): ?>
                                <tr>
                                    <?php for ($c = 0; $c < 3; $c++): ?>
                                    <td class="p-1" style="vertical-align:middle;">
                                        <input type="text" class="form-control form-control-sm"
                                               name="tab_materia_<?= $r ?>_<?= $c ?>"
                                               style="font-size:.8rem;">
                                    </td>
                                    <td class="p-1" style="vertical-align:middle;">
                                        <input type="text" class="form-control form-control-sm"
                                               name="tab_assenze_<?= $r ?>_<?= $c ?>"
                                               placeholder="" style="font-size:.8rem;">
                                    </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($materieTabella)): ?>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-info-circle me-1"></i>Nessuna materia trovata per questo anno di corso.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Competenze acquisite -->
                <div class="col-12">
                    <label class="form-label small fw-semibold">Competenze acquisite dall'allievo</label>
                    <input type="text" class="form-control" name="competenze_acquisite" placeholder="">
                </div>

                <!-- Valutazione finale del team docenti -->
                <div class="col-12">
                    <label class="form-label small fw-semibold">Valutazione finale del team docenti</label>
                    <textarea class="form-control" name="valutazione_finale_team" rows="3" placeholder=""></textarea>
                </div>

                <!-- Giudizio /100 -->
                <div class="col-lg-3">
                    <label class="form-label small fw-semibold">Giudizio <span class="text-muted fw-normal">/ 100</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="giudizio_centesimi" min="0" max="100" step="0.5" placeholder="">
                        <span class="input-group-text">/100</span>
                    </div>
                </div>

                <!-- Firme del team -->
                <div class="col-12 mt-2">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Il Team dei docenti</label>
                            <input type="text" class="form-control mb-2" name="firma_team_docenti_1" placeholder="Nome e cognome">
                            <input type="text" class="form-control" name="firma_team_docenti_2" placeholder="Nome e cognome">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Il Coordinatore del corso</label>
                            <input type="text" class="form-control" name="firma_coordinatore" placeholder="Nome e cognome">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Autovalutazione -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <i class="fas fa-user-check me-2" style="color:#1e40af;"></i>Autovalutazione
                <span class="text-muted fw-normal small ms-1">(max 5/100)</span>
            </h6>
        </div>
        <div class="card-body px-4 py-4">

            <!-- Suggerimenti (solo informativi) -->
            <div class="p-3 rounded-3 mb-4" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.85rem;color:#374151;">
                <div class="fw-semibold mb-2" style="color:#0c1a3a;">Suggerimenti <span class="fw-normal">(non vincolanti):</span></div>
                <ol type="a" class="mb-0 ps-3" style="line-height:1.9;">
                    <li><span class="text-decoration-underline">preparazione professionale</span>: abilità, capacità;</li>
                    <li><span class="text-decoration-underline">preparazione culturale</span>: conoscenze, capacità di intervento</li>
                    <li>
                        <span class="text-decoration-underline">sbocchi occupazionali</span>:
                        <ul class="list-unstyled ms-3 mb-0">
                            <li>- libera professione</li>
                            <li>- impiego privato</li>
                            <li>- impiego pubblico</li>
                            <li>- altro</li>
                        </ul>
                    </li>
                    <li>il corso: organizzazione, tempo libero, struttura ed impianti, alternanza studio-lavoro ed i contatti con le aziende, i docenti, le materie ed i programmi, suggerimenti e proposte.</li>
                </ol>
            </div>

            <!-- Testo libero in un unico textarea -->
            <div class="mb-3">
                <textarea class="form-control" name="autovalutazione_testo" rows="7"
                          placeholder="Scrivi qui l'autovalutazione..."></textarea>
            </div>

            <!-- Giudizio e firma allievo -->
            <div class="row g-3 align-items-end mt-2">
                <div class="col-lg-3">
                    <label class="form-label small fw-semibold">Giudizio <span class="text-muted fw-normal">/ 100</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="autovalutazione_giudizio" min="0" max="5" step="0.5" placeholder="">
                        <span class="input-group-text">/100</span>
                    </div>
                </div>
                <div class="col-lg-5"></div>
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold">L'allievo</label>
                    <input type="text" class="form-control" name="autovalutazione_firma_allievo" placeholder="Nome e cognome">
                </div>
            </div>

        </div>
    </div>

    <!-- Valutazione sullo stage -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <i class="fas fa-briefcase me-2" style="color:#1e40af;"></i>Valutazione sullo stage
                <span class="text-muted fw-normal small ms-1">(max 5/100)</span>
            </h6>
        </div>
        <div class="card-body px-4 py-4">

            <!-- Obiettivi -->
            <div class="p-3 rounded-3 mb-4" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.85rem;color:#374151;">
                <span class="text-decoration-underline fw-semibold">Obiettivi previsti e conseguiti con lo stage</span>:
                Consolidamento delle competenze acquisite durante le ore di lezioni teoriche, con la finalità di acquisire maggiore esperienza nel mondo del lavoro e far fronte alle esigenze del mercato.
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label small fw-semibold">Azienda in cui è stato effettuato lo stage</label>
                    <input type="text" class="form-control" name="stage_azienda" placeholder="">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Docente/Tutor aziendale incaricato a seguire l'allievo nello stage</label>
                    <input type="text" class="form-control" name="stage_tutor" placeholder="Nome e cognome">
                </div>
            </div>

            <!-- Tabella date / presenze / annotazioni -->
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="font-size:.85rem;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="text-center py-2" style="width:12%;">Data</th>
                            <th class="text-center py-2" style="width:20%;">Ore presenza</th>
                            <th class="text-center py-2">Annotazioni docente</th>
                            <th class="text-center py-2">Annotazioni tutor aziendale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                        <tr>
                            <td class="p-2" style="vertical-align:top;">
                                <div class="text-muted small mb-1">dal</div>
                                <input type="date" class="form-control form-control-sm mb-2" name="stage_dal_<?= $i ?>">
                                <div class="text-muted small mb-1">al</div>
                                <input type="date" class="form-control form-control-sm" name="stage_al_<?= $i ?>">
                            </td>
                            <td class="p-2" style="vertical-align:top;">
                                <input type="text" class="form-control form-control-sm mb-1" name="stage_ore_<?= $i ?>" placeholder="Ore">
                                <div class="text-muted" style="font-size:.75rem;">Vedi registro stage allegato</div>
                            </td>
                            <td class="p-2">
                                <textarea class="form-control form-control-sm border-0" name="stage_note_docente_<?= $i ?>" rows="3" placeholder="" style="resize:none;"></textarea>
                            </td>
                            <td class="p-2">
                                <textarea class="form-control form-control-sm border-0" name="stage_note_tutor_<?= $i ?>" rows="3" placeholder="" style="resize:none;"></textarea>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <!-- Valutazione tutor / Giudizio / Firme stage -->
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <label class="form-label small fw-semibold">
                        Valutazione del tutor sullo stage in termini di attenzione, apprendimento, abilità, conoscenze, capacità di intervento, altro
                    </label>
                    <input type="text" class="form-control" name="stage_valutazione_tutor" placeholder="">
                </div>
                <div class="col-12">
                    <div class="p-2 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.85rem;font-weight:600;color:#374151;">
                        VEDI ALLEGATO
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-semibold">Giudizio <span class="text-muted fw-normal">/ 100</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="stage_giudizio" min="0" max="5" step="0.5" placeholder="">
                        <span class="input-group-text">/100</span>
                    </div>
                </div>
                <div class="col-lg-9"></div>
                <div class="col-lg-5">
                    <label class="form-label small fw-semibold">Il docente</label>
                    <input type="text" class="form-control" name="stage_firma_docente" placeholder="Nome e cognome">
                </div>
                <div class="col-lg-5 offset-lg-2">
                    <label class="form-label small fw-semibold">Il tutor</label>
                    <input type="text" class="form-control" name="stage_firma_tutor" placeholder="Nome e cognome">
                </div>
            </div>

        </div>
    </div>

    <!-- Valutazione - relazione sullo stage -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 text-center">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <span class="text-decoration-underline">Valutazione - relazione sullo stage</span>
                <span class="text-muted fw-normal small ms-1">(Max. 10/100)</span>
            </h6>
        </div>
        <div class="card-body px-4 py-4">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small fw-semibold">Oggetto e/o titolo della relazione</label>
                    <input type="text" class="form-control" name="relazione_oggetto" placeholder="">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Ditta in cui è stato realizzato lo stage</label>
                    <input type="text" class="form-control" name="relazione_ditta" placeholder="">
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.85rem;color:#374151;">
                        <span class="fw-semibold">Suggerimenti:</span>
                        <span class="text-decoration-underline">organizzazione del lavoro</span>,
                        <span class="text-decoration-underline">organizzazione produttiva</span>,
                        <span class="text-decoration-underline">reparti di lavorazione</span>,
                        <span class="text-decoration-underline">profili professionali</span>,
                        <span class="text-decoration-underline">ruolo professionale del qualificato</span>,
                        <span class="text-decoration-underline">il mercato del lavoro</span>,
                        <span class="text-decoration-underline">la tutela sindacale</span>,
                        altro.
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Docente/Tutor aziendale incaricato di seguire l'allievo nello stage</label>
                    <input type="text" class="form-control" name="relazione_tutor" placeholder="Nome e cognome">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Valutazione della Commissione esaminatrice</label>
                    <textarea class="form-control" name="relazione_valutazione_commissione" rows="5" placeholder=""></textarea>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-semibold">Giudizio <span class="text-muted fw-normal">/ 100</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="relazione_giudizio" min="0" max="10" step="0.5" placeholder="">
                        <span class="input-group-text">/100</span>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <div class="fst-italic fw-semibold mb-3" style="font-size:.88rem;color:#374151;text-decoration:underline;">
                        La Commissione Esaminatrice
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Il Presidente</label>
                            <input type="text" class="form-control" name="relazione_presidente" placeholder="Nome e cognome">
                        </div>
                        <div class="col-12"></div>
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Membro</label>
                            <input type="text" class="form-control" name="relazione_membro_1" placeholder="Nome e cognome">
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Membro</label>
                            <input type="text" class="form-control" name="relazione_membro_2" placeholder="Nome e cognome">
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <div class="p-3 rounded-3" style="background:#fff8e1;border:1px solid #ffe082;font-size:.82rem;color:#374151;">
                        <strong>N.B.</strong> - <span class="text-decoration-underline">Nel caso che lo stage non sia stato effettuato le schede 3 e 4 saranno barrate e la Commissione ripartirà il punteggio complessivo di 15/100 tra le schede 5 e 6</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Valutazione prova pratica esami finali -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 text-center">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <span class="text-decoration-underline">Valutazione prova pratica esami finali</span>
                <span class="text-muted fw-normal small ms-1">(Max. 40/100)</span>
            </h6>
        </div>
        <div class="card-body px-4 py-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" style="font-size:.82rem;">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="text-center py-2" style="width:14%;"><span class="text-decoration-underline">Competenze acquisite</span></th>
                                    <th class="text-center py-2" style="width:12%;">Attività</th>
                                    <th class="text-center py-2" style="width:13%;">Informazioni</th>
                                    <th class="text-center py-2" style="width:15%;">Program-<br>mazione<br><span class="fw-normal">max .../100</span></th>
                                    <th class="text-center py-2" style="width:15%;">Esecuzione<br><span class="fw-normal">max .../100</span></th>
                                    <th class="text-center py-2" style="width:15%;">Controllo<br><span class="fw-normal">max .../100</span></th>
                                    <th class="text-center py-2" style="width:16%;"><span class="text-decoration-underline">Regolazione</span><br><span class="fw-normal">max .../100</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i <= 2; $i++): ?>
                                <tr style="height:48px;">
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="pratica_competenze_<?= $i ?>" placeholder=""></td>
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="pratica_attivita_<?= $i ?>" placeholder=""></td>
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="pratica_informazioni_<?= $i ?>" placeholder=""></td>
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="pratica_programmazione_<?= $i ?>" placeholder=""></td>
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="pratica_esecuzione_<?= $i ?>" placeholder=""></td>
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="pratica_controllo_<?= $i ?>" placeholder=""></td>
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="pratica_regolazione_<?= $i ?>" placeholder=""></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Valutazione della Commissione esaminatrice</label>
                    <textarea class="form-control" name="pratica_valutazione_commissione" rows="4" placeholder=""></textarea>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-semibold">Giudizio <span class="text-muted fw-normal">/ 100</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="pratica_giudizio" min="0" max="40" step="0.5" placeholder="">
                        <span class="input-group-text">/100</span>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <div class="fst-italic fw-semibold mb-3" style="font-size:.88rem;color:#374151;">
                        <span class="text-decoration-underline">La Commissione Esaminatrice</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Il Presidente</label>
                            <input type="text" class="form-control" name="pratica_presidente" placeholder="Nome e cognome">
                        </div>
                        <div class="col-12"></div>
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Membro</label>
                            <input type="text" class="form-control" name="pratica_membro_1" placeholder="Nome e cognome">
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Membro</label>
                            <input type="text" class="form-control" name="pratica_membro_2" placeholder="Nome e cognome">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Valutazione prova teorica esami finali -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 text-center">
            <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                <span class="text-decoration-underline">Valutazione prova teorica esami finali</span>
                <span class="text-muted fw-normal small ms-1">(Max. 30/100)</span>
            </h6>
        </div>
        <div class="card-body px-4 py-4">
            <div class="row g-3">
                <!-- Tabella a due colonne -->
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" style="font-size:.85rem;">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th class="py-2 px-3" style="width:50%;">
                                        <span class="text-decoration-underline">A) Area tecnico-professionale</span> - Max.
                                        <input type="number" class="form-control form-control-sm d-inline-block border-0 p-0" name="teorica_max_a" min="0" max="100" style="width:50px;background:transparent;">
                                        /100
                                    </th>
                                    <th class="py-2 px-3" style="width:50%;">
                                        <span class="text-decoration-underline">B) Area socio-culturale (materie)</span> - Max.
                                        <input type="number" class="form-control form-control-sm d-inline-block border-0 p-0" name="teorica_max_b" min="0" max="100" style="width:50px;background:transparent;">
                                        /100
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i <= 7; $i++): ?>
                                <tr style="height:36px;">
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="teorica_a_<?= $i ?>" placeholder=""></td>
                                    <td class="p-1"><input type="text" class="form-control form-control-sm border-0" name="teorica_b_<?= $i ?>" placeholder=""></td>
                                </tr>
                                <?php endfor; ?>
                                <tr>
                                    <td class="p-2">
                                        <span class="small fw-semibold">Giudizio:</span>
                                        <div class="input-group input-group-sm d-inline-flex" style="width:120px;">
                                            <input type="number" class="form-control border-0" name="teorica_giudizio_a" min="0" max="100" step="0.5" placeholder="">
                                            <span class="input-group-text border-0 bg-transparent">/100</span>
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <span class="small fw-semibold">Giudizio:</span>
                                        <div class="input-group input-group-sm d-inline-flex" style="width:120px;">
                                            <input type="number" class="form-control border-0" name="teorica_giudizio_b" min="0" max="100" step="0.5" placeholder="">
                                            <span class="input-group-text border-0 bg-transparent">/100</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold">Valutazione della <span class="text-decoration-underline">Commissione esaminatrice</span></label>
                    <textarea class="form-control" name="teorica_valutazione_commissione" rows="4" placeholder=""></textarea>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small fw-semibold">Giudizio <span class="text-muted fw-normal">/ 100</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="teorica_giudizio" min="0" max="30" step="0.5" placeholder="">
                        <span class="input-group-text">/100</span>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <div class="fst-italic fw-semibold mb-3" style="font-size:.88rem;color:#374151;text-decoration:underline;">
                        La Commissione Esaminatrice
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Il Presidente</label>
                            <input type="text" class="form-control" name="teorica_presidente" placeholder="">
                        </div>
                        <div class="col-12"></div>
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Membro</label>
                            <input type="text" class="form-control" name="teorica_membro_1" placeholder="">
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label small fw-semibold">Membro</label>
                            <input type="text" class="form-control" name="teorica_membro_2" placeholder="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
    <input type="hidden" name="percorso_id" value="<?= $percorsoId ?>">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button type="submit" name="azione" value="download"
                formaction="<?= BASE_URL ?>studenti/download-scheda-allievo"
                formmethod="POST" formtarget="_blank"
                class="btn btn-outline-success">
            <i class="fas fa-file-word me-2"></i>Scarica Scheda Allievo (Word)
        </button>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>studenti/detail/<?= $studente['id'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i>Annulla
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Salva scheda
            </button>
        </div>
    </div>

</form>
