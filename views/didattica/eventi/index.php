<?php
$tipiLabel = [
    'workshop'    => 'Workshop',
    'seminario'   => 'Seminario',
    'open_day'    => 'Open Day',
    'competizione'=> 'Competizione',
    'sociale'     => 'Festa / Sociale',
    'altro'       => 'Altro',
];
$tipiColore = [
    'workshop'     => '#3b82f6',
    'seminario'    => '#8b5cf6',
    'open_day'     => '#f59e0b',
    'competizione' => '#ef4444',
    'sociale'      => '#ec4899',
    'altro'        => '#64748b',
];
$tipiIcona = [
    'workshop'    => 'fa-tools',
    'seminario'   => 'fa-chalkboard-teacher',
    'open_day'    => 'fa-door-open',
    'competizione'=> 'fa-trophy',
    'sociale'     => 'fa-glass-cheers',
    'altro'       => 'fa-calendar-day',
];
$statiLabel = [
    'aperto'    => 'Aperto',
    'chiuso'    => 'Iscrizioni chiuse',
    'annullato' => 'Annullato',
    'svolto'    => 'Svolto',
];
$statiColore = [
    'aperto'    => '#10b981',
    'chiuso'    => '#64748b',
    'annullato' => '#ef4444',
    'svolto'    => '#3b82f6',
];
$mesiIt = [1=>'Gen',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mag',6=>'Giu',7=>'Lug',8=>'Ago',9=>'Set',10=>'Ott',11=>'Nov',12=>'Dic'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-calendar-day me-2" style="color:#1e40af;"></i>Eventi
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:.75rem;">
            <?= count($eventi) ?>
        </span>
    </h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovoEvento">
        <i class="fas fa-plus me-2"></i>Nuovo evento
    </button>
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

<!-- Filtri -->
<form method="GET" action="<?= BASE_URL ?>eventi" class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0"
                           placeholder="Cerca per titolo, luogo, relatore..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Tutti i tipi</option>
                    <?php foreach ($tipiLabel as $k => $l): ?>
                        <option value="<?= $k ?>" <?= $tipo === $k ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="stato" class="form-select form-select-sm">
                    <option value="">Tutti gli stati</option>
                    <?php foreach ($statiLabel as $k => $l): ?>
                        <option value="<?= $k ?>" <?= $stato === $k ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filtra
                </button>
                <?php if ($search || $stato || $tipo): ?>
                    <a href="<?= BASE_URL ?>eventi" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<!-- Griglia eventi -->
<?php if (empty($eventi)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="empty-state" style="padding:3rem 0;">
                <div class="empty-state-icon"><i class="fas fa-calendar-day"></i></div>
                <h5>Nessun evento</h5>
                <p>Crea il primo evento con il pulsante "Nuovo evento".</p>
            </div>
        </div>
    </div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($eventi as $e):
        $tipoCol = $tipiColore[$e['tipo']] ?? '#64748b';
        $statoCol = $statiColore[$e['stato']] ?? '#64748b';
        $tipoIco = $tipiIcona[$e['tipo']] ?? 'fa-calendar-day';
        $g = (int)date('j', strtotime($e['data_evento']));
        $m = $mesiIt[(int)date('n', strtotime($e['data_evento']))];
        $perc = $e['max_iscritti'] ? round($e['num_iscritti'] / $e['max_iscritti'] * 100) : 0;
        $isFull = $e['max_iscritti'] && $e['num_iscritti'] >= $e['max_iscritti'];
    ?>
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="cursor:pointer;border-top:3px solid <?= $tipoCol ?> !important;"
             onclick="window.location='<?= BASE_URL ?>eventi/detail/<?= $e['id'] ?>'">
            <div class="card-body p-3">

                <!-- Header con data e stato -->
                <div class="d-flex align-items-start gap-3 mb-3">
                    <!-- Calendario -->
                    <div style="flex-shrink:0;width:54px;text-align:center;border:1px solid <?= $tipoCol ?>33;border-radius:8px;overflow:hidden;background:#fff;">
                        <div style="background:<?= $tipoCol ?>;color:#fff;padding:2px 0;font-size:.65rem;font-weight:700;text-transform:uppercase;">
                            <?= $m ?>
                        </div>
                        <div style="padding:6px 0;font-size:1.4rem;font-weight:800;color:#0c1a3a;line-height:1;">
                            <?= $g ?>
                        </div>
                    </div>
                    <!-- Titolo + tipo -->
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="d-flex align-items-center gap-1 mb-1">
                            <span class="badge"
                                  style="background:<?= $tipoCol ?>1a;color:<?= $tipoCol ?>;border:1px solid <?= $tipoCol ?>33;font-size:.65rem;">
                                <i class="fas <?= $tipoIco ?> me-1"></i><?= $tipiLabel[$e['tipo']] ?>
                            </span>
                            <span class="badge"
                                  style="background:<?= $statoCol ?>1a;color:<?= $statoCol ?>;border:1px solid <?= $statoCol ?>33;font-size:.65rem;">
                                <?= $statiLabel[$e['stato']] ?>
                            </span>
                        </div>
                        <h6 class="mb-1 fw-bold" style="color:#0c1a3a;line-height:1.25;">
                            <?= htmlspecialchars($e['titolo']) ?>
                        </h6>
                        <?php if ($e['ora_inizio']): ?>
                        <div class="text-muted" style="font-size:.78rem;">
                            <i class="fas fa-clock me-1"></i><?= substr($e['ora_inizio'], 0, 5) ?>
                            <?php if ($e['ora_fine']): ?>
                                — <?= substr($e['ora_fine'], 0, 5) ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Descrizione -->
                <?php if ($e['descrizione']): ?>
                <p class="text-muted mb-3" style="font-size:.82rem;line-height:1.45;
                   display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    <?= htmlspecialchars($e['descrizione']) ?>
                </p>
                <?php endif; ?>

                <!-- Info -->
                <div class="d-flex flex-column gap-1 mb-3" style="font-size:.78rem;color:#475569;">
                    <?php if ($e['luogo']): ?>
                    <div><i class="fas fa-map-marker-alt me-2 text-muted" style="width:14px;"></i><?= htmlspecialchars($e['luogo']) ?></div>
                    <?php endif; ?>
                    <?php if ($e['relatore']): ?>
                    <div><i class="fas fa-user-tie me-2 text-muted" style="width:14px;"></i><?= htmlspecialchars($e['relatore']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Capienza -->
                <div class="d-flex align-items-center justify-content-between"
                     style="padding-top:.7rem;border-top:1px solid #f1f5f9;">
                    <div>
                        <i class="fas fa-users me-1" style="color:<?= $isFull ? '#ef4444' : '#3b82f6' ?>;"></i>
                        <span class="fw-semibold" style="color:#0c1a3a;font-size:.85rem;">
                            <?= $e['num_iscritti'] ?>
                            <?= $e['max_iscritti'] ? ' / ' . $e['max_iscritti'] : '' ?>
                        </span>
                        <span class="text-muted small ms-1">iscritti</span>
                    </div>
                    <?php if ($isFull): ?>
                        <span class="badge bg-danger-subtle text-danger" style="font-size:.65rem;">SOLD OUT</span>
                    <?php elseif ($e['max_iscritti']): ?>
                        <span class="badge text-bg-light" style="font-size:.65rem;"><?= $perc ?>%</span>
                    <?php endif; ?>
                </div>

                <?php if ($e['max_iscritti']): ?>
                <div class="progress mt-2" style="height:4px;">
                    <div class="progress-bar" role="progressbar"
                         style="width:<?= min(100, $perc) ?>%;background:<?= $isFull ? '#ef4444' : $tipoCol ?>;"></div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Modal Nuovo Evento ── -->
<div class="modal fade" id="modalNuovoEvento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>eventi/store">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-plus me-2 text-primary"></i>Nuovo evento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Titolo <span class="text-danger">*</span></label>
                            <input type="text" name="titolo" class="form-control form-control-sm"
                                   placeholder="es. Workshop colorimetria avanzata" required maxlength="255">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tipo</label>
                            <select name="tipo" class="form-select form-select-sm">
                                <?php foreach ($tipiLabel as $k => $l): ?>
                                    <option value="<?= $k ?>"><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Descrizione</label>
                            <textarea name="descrizione" class="form-control form-control-sm" rows="3"
                                      placeholder="Breve descrizione dell'evento..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Data <span class="text-danger">*</span></label>
                            <input type="date" name="data_evento" class="form-control form-control-sm" required
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Ora inizio</label>
                            <input type="time" name="ora_inizio" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Ora fine</label>
                            <input type="time" name="ora_fine" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Luogo</label>
                            <input type="text" name="luogo" class="form-control form-control-sm"
                                   placeholder="es. Aula Magna, Sede di Milano..." maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Relatore / Ospite</label>
                            <input type="text" name="relatore" class="form-control form-control-sm"
                                   placeholder="Nome relatore" maxlength="255">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Posti massimi</label>
                            <input type="number" name="max_iscritti" class="form-control form-control-sm"
                                   min="1" placeholder="Lascia vuoto = illimitato">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Scadenza iscrizioni</label>
                            <input type="date" name="scadenza_iscrizioni" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Stato</label>
                            <select name="stato" class="form-select form-select-sm">
                                <?php foreach ($statiLabel as $k => $l): ?>
                                    <option value="<?= $k ?>" <?= $k === 'aperto' ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Crea evento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
