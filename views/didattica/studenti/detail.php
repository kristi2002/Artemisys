<?php
$ordinali = [1=>'Primo Anno',2=>'Secondo Anno',3=>'Terzo Anno',4=>'Quarto Anno',5=>'Quinto Anno',
             6=>'Sesto Anno',7=>'Settimo Anno',8=>'Ottavo Anno',9=>'Nono Anno',10=>'Decimo Anno'];
?>

<!-- Header -->
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="<?= BASE_URL ?>studenti" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Studenti
    </a>
    <span class="text-muted">/</span>
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <?= htmlspecialchars($studente['cognome'] . ' ' . $studente['nome']) ?>
    </h4>
    <?php if ($studente['attivo']): ?>
        <span class="badge" style="background:#dcfce7;color:#166534;">Attivo</span>
    <?php else: ?>
        <span class="badge" style="background:#fee2e2;color:#991b1b;">Inattivo</span>
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

    <!-- ===== ANAGRAFICA ===== -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-id-card me-2" style="color:#1e40af;"></i>Anagrafica
                </h6>
            </div>
            <div class="card-body">
                <form id="form-dati-studente" method="POST" action="<?= BASE_URL ?>studenti/update">
                    <input type="hidden" name="id" value="<?= $studente['id'] ?>">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Nome *</label>
                            <input type="text" name="nome" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($studente['nome']) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Cognome *</label>
                            <input type="text" name="cognome" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($studente['cognome']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($studente['email'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Telefono</label>
                            <input type="text" name="telefono" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($studente['telefono'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Data nascita</label>
                            <input type="date" name="data_nascita" class="form-control form-control-sm"
                                   value="<?= $studente['data_nascita'] ?? '' ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Luogo di nascita</label>
                            <input type="text" name="luogo_nascita" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($studente['luogo_nascita'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Codice fiscale</label>
                            <input type="text" name="codice_fiscale" class="form-control form-control-sm text-uppercase"
                                   value="<?= htmlspecialchars($studente['codice_fiscale'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Indirizzo</label>
                            <input type="text" name="indirizzo" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($studente['indirizzo'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Note</label>
                            <textarea name="note" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($studente['note'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="attivo" id="chkAttivo" class="form-check-input"
                                       <?= $studente['attivo'] ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="chkAttivo">Studente attivo</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="btn-salva-studente" class="btn btn-primary btn-sm mt-3 w-100">
                        <i class="fas fa-save me-1"></i>Salva anagrafica
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== DIDATTICA ===== -->
    <div class="col-lg-7">

        <!-- ── Corsi iscritti ───────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-route me-2" style="color:#1e40af;"></i>Corsi iscritti
                    <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;" id="badge-corsi"><?= count($iscrizioni) ?></span>
                </h6>
                <?php if (!empty($percorsiDisponibili)): ?>
                <button type="button" class="btn btn-primary btn-sm" id="btn-mostra-aggiungi-corso">
                    <i class="fas fa-plus me-1"></i>Aggiungi corso
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">

                <?php if (empty($iscrizioni)): ?>
                    <p class="text-muted small fst-italic mb-0" id="empty-corsi">Nessun corso iscritto.</p>
                <?php else: ?>
                    <p class="text-muted small fst-italic mb-0" id="empty-corsi" style="display:none;">Nessun corso iscritto.</p>
                <?php endif; ?>

                <!-- Lista corsi iscritti -->
                <ul class="list-group list-group-flush mb-0" id="lista-corsi">
                    <?php foreach ($iscrizioni as $isc): ?>
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center corso-item"
                        data-percorso-id="<?= $isc['percorso_id'] ?>"
                        data-percorso-nome="<?= htmlspecialchars($isc['percorso_nome'], ENT_QUOTES) ?>">
                        <div>
                            <div class="fw-semibold" style="color:#0c1a3a;font-size:.9rem;">
                                <?= htmlspecialchars($isc['percorso_nome']) ?>
                            </div>
                            <div class="text-muted" style="font-size:.78rem;">
                                A.S. <?= htmlspecialchars($isc['anno_label'] ?? '—') ?>
                                <?php if ($isc['data_inizio']): ?>
                                    · Dal <?= date('d/m/Y', strtotime($isc['data_inizio'])) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="button" class="btn btn-link p-0 text-danger"
                                onclick="apriModalRimuoviCorso(this)"
                                data-percorso-id="<?= $isc['percorso_id'] ?>"
                                data-percorso-nome="<?= htmlspecialchars($isc['percorso_nome'], ENT_QUOTES) ?>"
                                title="Rimuovi iscrizione">
                            <i class="fas fa-trash-alt" style="font-size:.8rem;"></i>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Form aggiungi corso (nascosto di default) -->
                <?php if (!empty($percorsiDisponibili)): ?>
                <div id="form-aggiungi-corso-wrap" style="display:none;" class="mt-3 pt-3 border-top">
                    <form id="form-aggiungi-corso" method="POST" action="<?= BASE_URL ?>studenti/add-iscrizione"
                          class="d-flex gap-2 flex-wrap align-items-end">
                        <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
                        <div class="flex-grow-1">
                            <label class="form-label small fw-semibold mb-1">Percorso</label>
                            <select name="percorso_id" class="form-select form-select-sm" required>
                                <option value="">— Seleziona —</option>
                                <?php foreach ($percorsiDisponibili as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['nome']) ?> — A.S. <?= htmlspecialchars($p['anno_label'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="width:140px;">
                            <label class="form-label small fw-semibold mb-1">Data inizio</label>
                            <input type="date" name="data_inizio" class="form-control form-control-sm">
                        </div>
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Aggiungi
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="btn-annulla-aggiungi-corso">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- ── Un blocco per ogni corso iscritto ────────────────────────── -->
        <?php foreach ($blocchiCorsi as $idx => $blocco):
            $isc          = $blocco['iscrizione'];
            $anniP        = $blocco['anniPercorso'];
            $annoC        = $blocco['annoCorrente'];
            $materieC     = $blocco['materieAnno'];
        ?>

        <div class="corso-blocco mb-4" data-percorso-id="<?= $isc['percorso_id'] ?>">

            <!-- Intestazione blocco -->
            <div class="d-flex align-items-center gap-2 mb-2 px-1 flex-wrap">
                <span class="badge" style="background:#e8eef8;color:#1e40af;font-size:.72rem;">
                    <i class="fas fa-route me-1"></i><?= htmlspecialchars($isc['percorso_nome']) ?>
                </span>
                <?php if ($annoC): ?>
                    <span class="badge" style="background:#dcfce7;color:#166534;font-size:.72rem;">
                        <?= $ordinali[$annoC['anno_numero']] ?? $annoC['anno_numero'] . '° Anno' ?>
                    </span>
                <?php endif; ?>
                <div class="ms-auto">
                    <a href="<?= BASE_URL ?>studenti/pagella/<?= $studente['id'] ?>?percorso_id=<?= $isc['percorso_id'] ?>"
                       class="btn btn-sm btn-outline-primary" style="font-size:.78rem;">
                        <i class="fas fa-star me-1"></i>Scheda di valutazione - esame finale
                    </a>
                </div>
            </div>


            <!-- Materie del corso -->
            <?php if (!empty($materieC)): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;font-size:.88rem;">
                        <i class="fas fa-atom me-2" style="color:#1e40af;"></i>Materie del corso
                    </h6>
                    <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($materieC) ?></span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead style="background:#f8fafc;font-size:0.78rem;">
                            <tr>
                                <th class="ps-4" style="width:80px;">Codice</th>
                                <th>Materia</th>
                                <th style="width:170px;">Voti</th>
                                <th style="width:170px;">Aggiungi voto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materieC as $m):
                                $voti  = $m['voti'] ?? [];
                                $media = count($voti) ? round(array_sum(array_column($voti, 'voto')) / count($voti), 2) : null;
                            ?>
                            <tr class="align-middle">
                                <td class="ps-4">
                                    <span class="badge" style="background:#e8eef8;color:#1e40af;font-weight:700;">
                                        <?= htmlspecialchars($m['codice']) ?>
                                    </span>
                                </td>
                                <td style="color:#0c1a3a;font-size:.88rem;">
                                    <?= htmlspecialchars($m['nome']) ?>
                                    <?php if ($media !== null): ?>
                                        <span class="ms-1 badge" style="background:#fef3c7;color:#92400e;font-weight:700;">
                                            media: <?= $media ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($voti)): ?>
                                        <span class="text-muted" style="font-size:.78rem;">—</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($voti as $v): ?>
                                            <span class="badge d-flex align-items-center gap-1"
                                                  style="background:#e8eef8;color:#0c1a3a;font-size:.8rem;"
                                                  title="<?= htmlspecialchars($v['tipo']) ?><?= $v['data'] ? ' — ' . date('d/m/Y', strtotime($v['data'])) : '' ?>">
                                                <strong><?= number_format($v['voto'], 2) ?></strong>
                                                <span style="font-size:.65rem;opacity:.7;"><?= ucfirst($v['tipo']) ?></span>
                                                <form method="POST" action="<?= BASE_URL ?>studenti/delete-voto" class="d-inline voto-form">
                                                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                                    <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
                                                    <button type="button" class="btn p-0 border-0 bg-transparent text-danger"
                                                            style="font-size:.65rem;line-height:1;"
                                                            onclick="apriModalEliminaVoto(this)"
                                                            data-voto="<?= number_format($v['voto'], 2) ?>"
                                                            data-tipo="<?= htmlspecialchars(ucfirst($v['tipo'])) ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </span>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= BASE_URL ?>studenti/add-voto"
                                          class="d-flex gap-1 align-items-center form-add-voto">
                                        <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
                                        <input type="hidden" name="pam_id" value="<?= $m['pam_id'] ?>">
                                        <input type="number" name="voto" step="0.5" min="0" max="10"
                                               class="form-control form-control-sm"
                                               style="width:60px;" placeholder="0-10" required>
                                        <select name="tipo" class="form-select form-select-sm" style="width:80px;font-size:.75rem;">
                                            <option value="finale">Finale</option>
                                            <option value="scritto">Scritto</option>
                                            <option value="orale">Orale</option>
                                            <option value="pratico">Pratico</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /.corso-blocco -->
        <?php endforeach; ?>

        <!-- ── Materie bonus (globali) ──────────────────────────────────── -->
        <?php if ($annoAttivo && !empty($iscrizioni)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-star me-2" style="color:#f59e0b;"></i>Materie bonus
                </h6>
                <span class="badge" style="background:#fef3c7;color:#92400e;"><?= count($materieBonus) ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($materieDisponibili)): ?>
                <form method="POST" action="<?= BASE_URL ?>studenti/add-bonus" class="d-flex gap-2 mb-3 align-items-end">
                    <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
                    <input type="hidden" name="anno_scolastico_id" value="<?= $annoAttivo['id'] ?>">
                    <div class="flex-grow-1">
                        <select name="materia_id" class="form-select form-select-sm" required>
                            <option value="">— Aggiungi materia bonus —</option>
                            <?php foreach ($materieDisponibili as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    <?= htmlspecialchars($m['nome']) ?> (<?= htmlspecialchars($m['codice']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus me-1"></i>Aggiungi
                    </button>
                </form>
                <?php endif; ?>
                <?php if (empty($materieBonus)): ?>
                    <p class="text-muted small fst-italic mb-0">Nessuna materia bonus assegnata.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($materieBonus as $b): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span>
                                    <span class="badge me-2" style="background:#fef3c7;color:#92400e;font-weight:700;">
                                        <?= htmlspecialchars($b['materia_codice']) ?>
                                    </span>
                                    <?= htmlspecialchars($b['materia_nome']) ?>
                                </span>
                                <form method="POST" action="<?= BASE_URL ?>studenti/delete-bonus"
                                      onsubmit="return confirm('Rimuovere questa materia bonus?')">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Storico anni ─────────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-history me-2" style="color:#1e40af;"></i>Storico anni accademici
                </h6>
                <?php if (!empty($iscrizioni)): ?>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAggiungiStorico">
                    <i class="fas fa-plus me-1"></i>Aggiungi voce
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($storiAnni)): ?>
                    <p class="text-muted small fst-italic px-4 py-3 mb-0">Nessuna voce nello storico.</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="ps-4">Anno scolastico</th>
                            <th>Percorso</th>
                            <th>Anno di corso</th>
                            <th class="pe-3 text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($storiAnni as $sa): ?>
                            <tr>
                                <td class="ps-4 align-middle">
                                    <span class="badge" style="background:#e8eef8;color:#1e40af;font-weight:600;">
                                        <?= htmlspecialchars($sa['anno_label'] ?? '—') ?>
                                    </span>
                                </td>
                                <td class="align-middle small" style="color:#0c1a3a;">
                                    <?= htmlspecialchars($sa['percorso_nome']) ?>
                                </td>
                                <td class="align-middle">
                                    <?= $ordinali[$sa['anno_numero']] ?? $sa['anno_numero'] . '° Anno' ?>
                                </td>
                                <td class="pe-3 text-end align-middle">
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 me-1"
                                            onclick="apriModalModificaStorico(<?= htmlspecialchars(json_encode($sa), ENT_QUOTES) ?>)"
                                            title="Modifica">
                                        <i class="fas fa-pen" style="font-size:.75rem;"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                            onclick="apriModalEliminaStorico(<?= $sa['id'] ?>, '<?= htmlspecialchars(($sa['anno_label'] ?? '—') . ' - ' . $sa['percorso_nome'], ENT_QUOTES) ?>')"
                                            title="Elimina">
                                        <i class="fas fa-trash" style="font-size:.75rem;"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottone Pagella -->
        <div class="mt-3 text-end">
            <button type="button" class="btn btn-sm"
                    style="background:#1e40af;color:#fff;"
                    data-bs-toggle="modal" data-bs-target="#modalScaricaPagella">
                <i class="fas fa-graduation-cap me-1"></i>Pagella
            </button>
        </div>

        <!-- Modal AGGIUNGI voce storico -->
        <div class="modal fade" id="modalAggiungiStorico" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                        <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                            <i class="fas fa-plus me-2" style="color:#1e40af;"></i>Aggiungi voce storico
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?= BASE_URL ?>studenti/set-anno">
                        <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Percorso</label>
                                <select name="percorso_id_storico" id="add-storico-percorso" class="form-select form-select-sm" required>
                                    <option value="">— Seleziona percorso —</option>
                                    <?php foreach ($iscrizioni as $isc): ?>
                                        <option value="<?= $isc['percorso_id'] ?>"
                                                data-anno-scol="<?= $isc['anno_scolastico_id'] ?? '' ?>">
                                            <?= htmlspecialchars($isc['percorso_nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Anno scolastico</label>
                                <select name="anno_scolastico_id" id="add-storico-anno-scol" class="form-select form-select-sm" required>
                                    <?php foreach ($anniScolastici as $as): ?>
                                        <option value="<?= $as['id'] ?>"><?= htmlspecialchars($as['anno']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Anno di corso</label>
                                <select name="percorso_anno_id" id="add-storico-percorso-anno" class="form-select form-select-sm" required>
                                    <option value="">— prima seleziona il percorso —</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Salva</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal MODIFICA storico -->
        <div class="modal fade" id="modalModificaStorico" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                        <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                            <i class="fas fa-pen me-2" style="color:#1e40af;"></i>Modifica voce storico
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?= BASE_URL ?>studenti/update-anno-storico">
                        <input type="hidden" name="storico_id" id="mod-storico-id">
                        <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Anno scolastico</label>
                                <select name="anno_scolastico_id" id="mod-anno-scol" class="form-select form-select-sm" required>
                                    <?php foreach ($anniScolastici as $as): ?>
                                        <option value="<?= $as['id'] ?>"><?= htmlspecialchars($as['anno']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Anno di corso</label>
                                <select name="percorso_anno_id" id="mod-percorso-anno" class="form-select form-select-sm" required>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Salva</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal ELIMINA storico -->
        <div class="modal fade" id="modalEliminaStorico" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                        <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                            <i class="fas fa-trash me-2 text-danger"></i>Elimina voce storico
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 py-3" style="font-size:.9rem;">
                        Vuoi eliminare la voce <strong id="elimina-storico-label"></strong>?
                    </div>
                    <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                        <form method="POST" action="<?= BASE_URL ?>studenti/delete-anno-storico">
                            <input type="hidden" name="storico_id" id="elimina-storico-id">
                            <input type="hidden" name="studente_id" value="<?= $studente['id'] ?>">
                            <button type="button" class="btn btn-secondary btn-sm me-1" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash me-1"></i>Elimina</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        const _anniPerPercorso = <?= json_encode($anniPerPercorso) ?>;
        const _ordinali = <?= json_encode($ordinali) ?>;

        function apriModalModificaStorico(sa) {
            document.getElementById('mod-storico-id').value = sa.id;

            // Anno scolastico
            const selAs = document.getElementById('mod-anno-scol');
            for (let o of selAs.options) o.selected = (o.value == sa.anno_scolastico_id);

            // Anno di corso: carica anni del percorso
            const selPa = document.getElementById('mod-percorso-anno');
            selPa.innerHTML = '';
            const anni = _anniPerPercorso[sa.percorso_id] ?? [];
            anni.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = (_ordinali[a.numero] ?? a.numero + '° Anno');
                opt.selected = (a.id == sa.percorso_anno_id);
                selPa.appendChild(opt);
            });

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalModificaStorico')).show();
        }

        function apriModalEliminaStorico(id, label) {
            document.getElementById('elimina-storico-id').value = id;
            document.getElementById('elimina-storico-label').textContent = label;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaStorico')).show();
        }

        // Popola anni di corso quando si sceglie il percorso nel modal "Aggiungi voce storico"
        document.getElementById('add-storico-percorso')?.addEventListener('change', function () {
            const percorsoId = this.value;
            const selPa      = document.getElementById('add-storico-percorso-anno');
            const selAs      = document.getElementById('add-storico-anno-scol');
            selPa.innerHTML  = '<option value="">— seleziona —</option>';

            if (!percorsoId) return;

            // Pre-seleziona l'anno scolastico del percorso
            const opt = this.options[this.selectedIndex];
            const annoScolId = opt.dataset.annoScol;
            if (annoScolId) {
                for (let o of selAs.options) o.selected = (o.value == annoScolId);
            }

            // Popola anni di corso
            const anni = _anniPerPercorso[percorsoId] ?? [];
            anni.forEach(a => {
                const o = document.createElement('option');
                o.value = a.id;
                o.textContent = (_ordinali[a.numero] ?? a.numero + '° Anno');
                selPa.appendChild(o);
            });
        });
        </script>

    </div>
</div>

<?php
// ── Media & Pagella — un blocco per ogni corso con materie ──────────────
$colorFor = function(?float $v): string {
    if ($v === null) return '#94a3b8';
    if ($v >= 8)   return '#16a34a';
    if ($v >= 6)   return '#1e40af';
    if ($v >= 5)   return '#f59e0b';
    return '#dc2626';
};

foreach ($blocchiCorsi as $blocco):
    $bAnnoC   = $blocco['annoCorrente'];
    $bMaterie = $blocco['materieAnno'];
    $bIsc     = $blocco['iscrizione'];
    if (!$bAnnoC || empty($bMaterie)) continue;

    $righePagella = [];
    $sommaMedie   = 0;
    $countConVoti = 0;
    foreach ($bMaterie as $mat) {
        $voti   = $mat['voti'] ?? [];
        $valori = array_map(fn($v) => (float)$v['voto'], $voti);
        $media  = !empty($valori) ? round(array_sum($valori) / count($valori), 2) : null;
        if ($media !== null) { $sommaMedie += $media; $countConVoti++; }
        $righePagella[] = [
            'codice'  => $mat['codice'] ?? '',
            'nome'    => $mat['nome'],
            'numVoti' => count($voti),
            'media'   => $media,
            'voti'    => $valori,
        ];
    }
    $mediaGenerale = $countConVoti > 0 ? round($sommaMedie / $countConVoti, 2) : null;
?>
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
            <i class="fas fa-chart-line me-2" style="color:#1e40af;"></i>
            Media voti — <?= htmlspecialchars($bIsc['percorso_nome']) ?>
            <span class="text-muted fw-normal small ms-1">
                (<?= $ordinali[$bAnnoC['anno_numero']] ?? $bAnnoC['anno_numero'] . '° Anno' ?>)
            </span>
            <?php if ($annoAttivo): ?>
                <span class="text-muted fw-normal small ms-1">· A.S. <?= htmlspecialchars($annoAttivo['anno']) ?></span>
            <?php endif; ?>
        </h6>
        <div class="d-flex align-items-center gap-3">
            <?php if ($mediaGenerale !== null): ?>
                <div class="text-end">
                    <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Media generale</div>
                    <div class="fw-bold" style="font-size:1.4rem;color:<?= $colorFor($mediaGenerale) ?>;line-height:1;">
                        <?= number_format($mediaGenerale, 2, ',', '') ?>
                    </div>
                </div>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>studenti/pagella/<?= $studente['id'] ?>?percorso_id=<?= $bIsc['percorso_id'] ?>" target="_blank"
               class="btn btn-sm btn-primary">
                <i class="fas fa-file-pdf me-1"></i>Stampa pagella
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead style="background:#f8fafc;">
                <tr>
                    <th class="ps-4" style="width:90px;">Codice</th>
                    <th>Materia</th>
                    <th class="text-center" style="width:90px;">Voti</th>
                    <th>Dettaglio voti</th>
                    <th class="text-end pe-4" style="width:120px;">Media</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($righePagella as $r): ?>
                    <tr>
                        <td class="ps-4 align-middle">
                            <?php if ($r['codice']): ?>
                                <span class="badge" style="background:#e8eef8;color:#1e40af;font-weight:700;">
                                    <?= htmlspecialchars($r['codice']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="align-middle fw-semibold" style="color:#0c1a3a;">
                            <?= htmlspecialchars($r['nome']) ?>
                        </td>
                        <td class="align-middle text-center text-muted small">
                            <?= $r['numVoti'] ?>
                        </td>
                        <td class="align-middle">
                            <?php if (empty($r['voti'])): ?>
                                <span class="text-muted fst-italic small">Nessun voto</span>
                            <?php else: ?>
                                <?php foreach ($r['voti'] as $v): ?>
                                    <span class="badge me-1"
                                          style="background:<?= $colorFor((float)$v) ?>1a;color:<?= $colorFor((float)$v) ?>;border:1px solid <?= $colorFor((float)$v) ?>33;">
                                        <?= rtrim(rtrim(number_format($v, 2, ',', ''), '0'), ',') ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4 align-middle fw-bold" style="color:<?= $colorFor($r['media']) ?>;">
                            <?= $r['media'] !== null ? number_format($r['media'], 2, ',', '') : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<!-- ════ Modal SCARICA PAGELLA ════════════════════════════════════════════ -->
<div class="modal fade" id="modalScaricaPagella" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-graduation-cap me-2" style="color:#1e40af;"></i>
                    Scarica Pagella — <?= htmlspecialchars($studente['cognome'] . ' ' . $studente['nome']) ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Di quale corso si tratta?</label>
                    <select id="pagella-sel-corso" class="form-select form-select-sm">
                        <option value="">— Seleziona corso —</option>
                        <?php foreach ($iscrizioni as $isc): ?>
                            <option value="<?= $isc['percorso_id'] ?>">
                                <?= htmlspecialchars($isc['percorso_nome']) ?>
                                <?php if (!empty($isc['anno_label'])): ?>
                                    — A.S. <?= htmlspecialchars($isc['anno_label']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Di quale anno scolastico?</label>
                    <select id="pagella-sel-anno" class="form-select form-select-sm">
                        <option value="">— Seleziona anno —</option>
                        <?php foreach ($anniScolastici as $as): ?>
                            <option value="<?= $as['id'] ?>"><?= htmlspecialchars($as['anno']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Anno di corso?</label>
                    <select id="pagella-sel-anno-corso" class="form-select form-select-sm">
                        <option value="">— Prima seleziona il corso —</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-sm" style="background:#1e40af;color:#fff;"
                        id="btn-conferma-pagella">
                    <i class="fas fa-download me-1"></i>Scarica
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Popola l'anno di corso quando si sceglie il corso
document.getElementById('pagella-sel-corso').addEventListener('change', function () {
    const sel   = document.getElementById('pagella-sel-anno-corso');
    sel.innerHTML = '<option value="">— Seleziona anno di corso —</option>';
    const anni  = _anniPerPercorso[this.value] ?? [];
    anni.forEach(a => {
        const opt = document.createElement('option');
        opt.value = a.id;
        opt.textContent = _ordinali[a.numero] ?? (a.numero + '° Anno');
        sel.appendChild(opt);
    });
});

document.getElementById('btn-conferma-pagella').addEventListener('click', function () {
    const corsoId     = document.getElementById('pagella-sel-corso').value;
    const annoId      = document.getElementById('pagella-sel-anno').value;
    const annoCorsoId = document.getElementById('pagella-sel-anno-corso').value;

    if (!corsoId)     { alert('Seleziona il corso.'); return; }
    if (!annoId)      { alert('Seleziona l\'anno scolastico.'); return; }
    if (!annoCorsoId) { alert('Seleziona l\'anno di corso.'); return; }

    bootstrap.Modal.getInstance(document.getElementById('modalScaricaPagella')).hide();

    const url = '<?= BASE_URL ?>studenti/scarica-pagella'
              + '?studente_id=<?= (int)$studente['id'] ?>'
              + '&percorso_id='      + corsoId
              + '&anno_id='          + annoId
              + '&percorso_anno_id=' + annoCorsoId;

    window.location.href = url;
});
</script>

<!-- Modal rimuovi corso -->
<div class="modal fade" id="modalRimuoviCorso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash-alt me-2 text-danger"></i>Rimuovi iscrizione
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:.9rem;color:#374151;">
                Sei sicuro di voler rimuovere l'iscrizione al corso <strong id="modal-corso-nome"></strong>?<br>
                <span class="text-muted" style="font-size:.82rem;">Verranno rimossi anche gli anni di corso associati.</span>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-rimuovi-corso">
                    <i class="fas fa-trash-alt me-1"></i>Rimuovi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal elimina voto -->
<div class="modal fade" id="modalEliminaVoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background:#f8fafc;">
                <h6 class="modal-title fw-semibold mb-0" style="color:#0c1a3a;">
                    <i class="fas fa-trash-alt me-2 text-danger"></i>Elimina voto
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:.9rem;color:#374151;">
                Sei sicuro di voler eliminare il voto <strong id="modal-voto-label"></strong>?<br>
                <span class="text-muted" style="font-size:.82rem;">L'operazione non è reversibile.</span>
            </div>
            <div class="modal-footer border-0" style="background:#f8fafc;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-conferma-elimina-voto">
                    <i class="fas fa-trash-alt me-1"></i>Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _formVotoDaEliminare = null;

function apriModalEliminaVoto(btn) {
    _formVotoDaEliminare = btn.closest('form');
    const voto = btn.dataset.voto;
    const tipo = btn.dataset.tipo;
    document.getElementById('modal-voto-label').textContent = voto + ' (' + tipo + ')';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEliminaVoto')).show();
}

document.querySelectorAll('.form-add-voto').forEach(function (form) {
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn  = form.querySelector('button[type="submit"]');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const r    = await fetch('<?= BASE_URL ?>studenti/add-voto', {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest' },
                body    : new FormData(form),
            });
            const data = await r.json();
            if (!data.success) {
                flashBannerStud('danger', data.error ?? 'Errore durante il salvataggio.');
                return;
            }

            // Aggiunge il badge del voto nella cella "Voti" della stessa riga
            const row      = form.closest('tr');
            const votiCell = row.querySelector('td:nth-child(3)');
            const dash     = votiCell.querySelector('.text-muted');
            if (dash) dash.remove();

            let wrap = votiCell.querySelector('.d-flex');
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.className = 'd-flex flex-wrap gap-1';
                votiCell.appendChild(wrap);
            }

            const votoFmt = parseFloat(data.voto).toFixed(2);
            const tipoFmt = data.tipo.charAt(0).toUpperCase() + data.tipo.slice(1);
            const pamId   = form.querySelector('input[name="pam_id"]').value;
            const studId  = form.querySelector('input[name="studente_id"]').value;

            const span = document.createElement('span');
            span.className = 'badge d-flex align-items-center gap-1';
            span.style.cssText = 'background:#e8eef8;color:#0c1a3a;font-size:0.8rem;';
            span.innerHTML = `
                <strong>${votoFmt}</strong>
                <span style="font-size:0.65rem;opacity:0.7;">${tipoFmt}</span>
                <form method="POST" action="<?= BASE_URL ?>studenti/delete-voto" class="d-inline voto-form">
                    <input type="hidden" name="id" value="${data.id}">
                    <input type="hidden" name="studente_id" value="${studId}">
                    <button type="button" class="btn p-0 border-0 bg-transparent text-danger"
                            style="font-size:0.65rem;line-height:1;"
                            onclick="apriModalEliminaVoto(this)"
                            data-voto="${votoFmt}"
                            data-tipo="${tipoFmt}">
                        <i class="fas fa-times"></i>
                    </button>
                </form>`;
            wrap.appendChild(span);

            // Resetta il campo voto
            form.querySelector('input[name="voto"]').value = '';
            flashBannerStud('success', 'Voto aggiunto.');
        } catch {
            flashBannerStud('danger', 'Errore di rete. Riprova.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });
});

document.getElementById('btn-conferma-elimina-voto').addEventListener('click', async function () {
    bootstrap.Modal.getInstance(document.getElementById('modalEliminaVoto')).hide();
    if (!_formVotoDaEliminare) return;

    const id      = _formVotoDaEliminare.querySelector('input[name="id"]').value;
    const studId  = _formVotoDaEliminare.querySelector('input[name="studente_id"]').value;
    const body    = new URLSearchParams({ id, studente_id: studId });

    try {
        const r    = await fetch('<?= BASE_URL ?>studenti/delete-voto', {
            method  : 'POST',
            headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body    : body.toString(),
        });
        const data = await r.json();
        if (!data.success) { flashBannerStud('danger', data.error ?? 'Errore durante l\'eliminazione.'); return; }

        // Rimuove il badge dalla cella voti
        const badge = _formVotoDaEliminare.closest('span.badge');
        const wrap  = badge?.parentElement;
        badge?.remove();

        // Se non ci sono più voti rimette il trattino
        if (wrap && wrap.querySelectorAll('span.badge').length === 0) {
            wrap.remove();
            const td = _formVotoDaEliminare.closest('td');
            if (td) {
                const dash = document.createElement('span');
                dash.className = 'text-muted';
                dash.style.fontSize = '0.78rem';
                dash.textContent = '—';
                td.appendChild(dash);
            }
        }

        flashBannerStud('success', 'Voto eliminato.');
    } catch {
        flashBannerStud('danger', 'Errore di rete. Riprova.');
    }
});

function flashBannerStud(type, msg) {
    const div = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show`;
    div.setAttribute('role', 'alert');
    div.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    const anchor = document.querySelector('.row.g-4');
    anchor ? anchor.parentNode.insertBefore(div, anchor) : document.body.prepend(div);
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(div).close(), 4000);
}


/* ── Mostra/nascondi form aggiungi corso ──────────────────────────────────── */
const btnMostra = document.getElementById('btn-mostra-aggiungi-corso');
const wrapAggiungi = document.getElementById('form-aggiungi-corso-wrap');
if (btnMostra && wrapAggiungi) {
    btnMostra.addEventListener('click', () => { wrapAggiungi.style.display = ''; btnMostra.style.display = 'none'; });
    document.getElementById('btn-annulla-aggiungi-corso')?.addEventListener('click', () => {
        wrapAggiungi.style.display = 'none'; btnMostra.style.display = '';
    });
}

/* ── Aggiungi iscrizione corso (AJAX) ─────────────────────────────────────── */
const formAggiungiCorso = document.getElementById('form-aggiungi-corso');
if (formAggiungiCorso) {
    formAggiungiCorso.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn  = this.querySelector('button[type="submit"]');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
        try {
            const r    = await fetch('<?= BASE_URL ?>studenti/add-iscrizione', {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest' },
                body    : new FormData(this),
            });
            const data = await r.json();
            if (!data.success) { flashBannerStud('danger', data.error ?? 'Errore.'); return; }
            // Ricarica la pagina per mostrare il nuovo blocco corso con materie
            location.reload();
        } catch { flashBannerStud('danger', 'Errore di rete. Riprova.'); }
        finally { btn.disabled = false; btn.innerHTML = orig; }
    });
}

/* ── Rimozione iscrizione corso ───────────────────────────────────────────── */
let _corsoDaRimuovere = null;
let _corsoNomeDaRimuovere = '';

function apriModalRimuoviCorso(btn) {
    _corsoDaRimuovere     = parseInt(btn.dataset.percorsoId);
    _corsoNomeDaRimuovere = btn.dataset.percorsoNome ?? '';
    document.getElementById('modal-corso-nome').textContent = _corsoNomeDaRimuovere;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRimuoviCorso')).show();
}

document.getElementById('btn-conferma-rimuovi-corso').addEventListener('click', async function () {
    bootstrap.Modal.getInstance(document.getElementById('modalRimuoviCorso')).hide();
    const body = new URLSearchParams({ studente_id: <?= (int)$studente['id'] ?>, percorso_id: _corsoDaRimuovere });
    try {
        const r    = await fetch('<?= BASE_URL ?>studenti/delete-iscrizione', {
            method  : 'POST',
            headers : { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body    : body.toString(),
        });
        const data = await r.json();
        if (!data.success) { flashBannerStud('danger', 'Errore durante la rimozione.'); return; }

        // Rimuove la riga dalla lista corsi
        document.querySelector(`#lista-corsi .corso-item[data-percorso-id="${_corsoDaRimuovere}"]`)?.remove();
        // Rimuove il blocco corso
        document.querySelector(`.corso-blocco[data-percorso-id="${_corsoDaRimuovere}"]`)?.remove();
        // Aggiorna badge
        const badge = document.getElementById('badge-corsi');
        if (badge) badge.textContent = parseInt(badge.textContent) - 1;
        // Mostra empty state se non ci sono più corsi
        const lista = document.getElementById('lista-corsi');
        if (lista && lista.querySelectorAll('.corso-item').length === 0) {
            document.getElementById('empty-corsi').style.display = '';
        }
        flashBannerStud('success', `Iscrizione a "${_corsoNomeDaRimuovere}" rimossa.`);
    } catch { flashBannerStud('danger', 'Errore di rete. Riprova.'); }
});

document.getElementById('form-dati-studente').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn  = document.getElementById('btn-salva-studente');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Salvataggio…';

    try {
        const r    = await fetch('<?= BASE_URL ?>studenti/update', {
            method  : 'POST',
            headers : { 'X-Requested-With': 'XMLHttpRequest' },
            body    : new FormData(this),
        });
        const data = await r.json();
        if (!data.success) {
            flashBannerStud('danger', data.error ?? 'Errore durante il salvataggio.');
            return;
        }
        // Aggiorna il nome nell'header
        const h4 = document.querySelector('h4.fw-bold');
        if (h4) h4.textContent = data.nome_cognome;
        flashBannerStud('success', 'Anagrafica aggiornata.');
    } catch {
        flashBannerStud('danger', 'Errore di rete. Riprova.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
});
</script>
