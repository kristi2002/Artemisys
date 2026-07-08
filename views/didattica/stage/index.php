<?php
$stepLabels = [1=>'Candidatura',2=>'Convenzione',3=>'Tirocinio',4=>'Valutazione'];
$stepColors = [1=>'#3b82f6',2=>'#8b5cf6',3=>'#f59e0b',4=>'#10b981'];
$giudiziBadge = [
    'insufficiente' => '#ef4444',
    'sufficiente'   => '#f59e0b',
    'buono'         => '#3b82f6',
    'ottimo'        => '#10b981',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-briefcase me-2" style="color:#1e40af;"></i>Stage
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:.75rem;">
            <?= count($stages) ?>
        </span>
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

<div class="row g-4">

    <!-- ── Form nuovo stage ── -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Nuovo stage
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>stage/store">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Studente <span class="text-danger">*</span></label>
                        <select name="studente_id" class="form-select form-select-sm" required>
                            <option value="">— Seleziona studente —</option>
                            <?php foreach ($studenti as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Azienda / Struttura <span class="text-danger">*</span></label>
                        <input type="text" name="azienda" class="form-control form-control-sm"
                               placeholder="es. Salone Rossi" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Città</label>
                        <input type="text" name="citta" class="form-control form-control-sm"
                               placeholder="es. Milano" maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Settore</label>
                        <input type="text" name="settore" class="form-control form-control-sm"
                               placeholder="es. Taglio, Colorazione..." maxlength="150">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Note candidatura</label>
                        <textarea name="note_candidatura" class="form-control form-control-sm" rows="2"
                                  placeholder="Motivazioni, note iniziali..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Avvia stage
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Lista stage ── -->
    <div class="col-lg-8">

        <!-- Ricerca -->
        <form method="GET" action="<?= BASE_URL ?>stage" class="mb-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" name="q" class="form-control border-start-0"
                       placeholder="Cerca per studente, azienda..."
                       value="<?= htmlspecialchars($search) ?>">
                <?php if ($search): ?>
                    <a href="<?= BASE_URL ?>stage" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-list me-2" style="color:#1e40af;"></i>Tutti gli stage
                </h6>
                <span class="badge" style="background:#e8eef8;color:#1e40af;"><?= count($stages) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($stages)): ?>
                    <div class="empty-state" style="padding:2.5rem 0;">
                        <div class="empty-state-icon"><i class="fas fa-briefcase"></i></div>
                        <h5>Nessuno stage</h5>
                        <p>Avvia il primo stage dal form qui a sinistra.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="ps-4">Studente</th>
                                <th>Azienda</th>
                                <th style="width:110px;">Step</th>
                                <th style="width:90px;">Periodo</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stages as $st):
                                $stepColor = $stepColors[$st['step']] ?? '#64748b';
                                $stepLabel = $stepLabels[$st['step']] ?? '—';
                            ?>
                                <tr style="cursor:pointer;"
                                    onclick="window.location='<?= BASE_URL ?>stage/detail/<?= $st['id'] ?>'">
                                    <td class="ps-4 align-middle">
                                        <div class="fw-semibold" style="color:#0c1a3a;">
                                            <?= htmlspecialchars($st['cognome'] . ' ' . $st['nome']) ?>
                                        </div>
                                        <?php if ($st['settore']): ?>
                                        <div class="text-muted small"><?= htmlspecialchars($st['settore']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle" style="font-size:.88rem;color:#374151;">
                                        <?= htmlspecialchars($st['azienda']) ?>
                                        <?php if ($st['citta']): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($st['citta']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <!-- Mini stepper pill -->
                                        <div class="d-flex align-items-center gap-1">
                                            <?php for ($i = 1; $i <= 4; $i++):
                                                $done    = $i < $st['step'];
                                                $current = $i === (int)$st['step'];
                                                $bg = $done || $current ? $stepColors[$i] : '#e2e8f0';
                                            ?>
                                                <div style="width:12px;height:12px;border-radius:50%;background:<?= $bg ?>;
                                                     border:2px solid <?= ($current ? $stepColors[$i] : ($done ? $stepColors[$i] : '#e2e8f0')) ?>;
                                                     <?= $current ? 'box-shadow:0 0 0 2px '.$stepColors[$i].'33;' : '' ?>">
                                                </div>
                                                <?php if ($i < 4): ?>
                                                    <div style="width:8px;height:2px;background:<?= ($i < $st['step'] ? $stepColors[$i] : '#e2e8f0') ?>;"></div>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <div style="font-size:.7rem;color:<?= $stepColor ?>;margin-top:3px;font-weight:600;">
                                            <?= $stepLabel ?>
                                        </div>
                                    </td>
                                    <td class="align-middle text-muted small">
                                        <?php if ($st['data_inizio']): ?>
                                            <?= date('d/m/Y', strtotime($st['data_inizio'])) ?>
                                        <?php else: echo '—'; endif; ?>
                                    </td>
                                    <td class="align-middle pe-3" onclick="event.stopPropagation()">
                                        <form method="POST" action="<?= BASE_URL ?>stage/delete"
                                              onsubmit="return confirm('Eliminare questo stage?')">
                                            <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
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
    </div>
</div>
