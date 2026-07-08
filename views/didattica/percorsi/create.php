<!-- Header -->
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="<?= BASE_URL ?>percorsi" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Percorsi
    </a>
    <span class="text-muted">/</span>
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-plus-circle me-2" style="color:#1e40af;"></i>Crea percorso
    </h4>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-route me-2" style="color:#1e40af;"></i>Nuovo percorso accademico
                </h6>
            </div>
            <form method="POST" action="<?= BASE_URL ?>percorsi/store">
                <div class="card-body px-4 py-4">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label class="form-label small fw-semibold">Nome percorso <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control"
                                   placeholder="es. Operatore Socio Sanitario" maxlength="200"
                                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label small fw-semibold">Codice corso</label>
                            <input type="text" name="codice_corso" class="form-control"
                                   placeholder="es. OSS-25" maxlength="50"
                                   value="<?= htmlspecialchars($_POST['codice_corso'] ?? '') ?>">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Anno scolastico <span class="text-danger">*</span></label>
                            <?php if (empty($anni)): ?>
                                <div class="alert alert-warning py-2 small mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Nessun anno scolastico disponibile.
                                    <a href="<?= BASE_URL ?>anno-scolastico">Aggiungine uno.</a>
                                </div>
                            <?php else: ?>
                                <select name="anno_scolastico_id" class="form-select" required>
                                    <option value="">— Seleziona —</option>
                                    <?php foreach ($anni as $a): ?>
                                        <option value="<?= $a['id'] ?>" <?= $a['attivo'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($a['anno']) ?><?= $a['attivo'] ? ' (attivo)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Sede</label>
                            <?php if (empty($sedi)): ?>
                                <div class="alert alert-warning py-2 small mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Nessuna sede disponibile.
                                    <a href="<?= BASE_URL ?>sedi">Aggiungine una.</a>
                                </div>
                            <?php else: ?>
                                <select name="sede_id" class="form-select">
                                    <option value="">— Seleziona sede —</option>
                                    <?php foreach ($sedi as $s): ?>
                                        <option value="<?= $s['id'] ?>">
                                            <?= htmlspecialchars($s['nome']) ?>
                                            <?= $s['comune'] ? '— ' . htmlspecialchars($s['comune']) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Inizio anno accademico</label>
                            <input type="date" name="data_inizio_anno" class="form-control"
                                   value="<?= htmlspecialchars($_POST['data_inizio_anno'] ?? '') ?>">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label small fw-semibold">Fine anno accademico</label>
                            <input type="date" name="data_fine_anno" class="form-control"
                                   value="<?= htmlspecialchars($_POST['data_fine_anno'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Descrizione</label>
                            <textarea name="descrizione" class="form-control" rows="3"
                                      placeholder="Note opzionali..."><?= htmlspecialchars($_POST['descrizione'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top px-4 py-3 d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>percorsi" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Annulla
                    </a>
                    <button type="submit" class="btn btn-primary" <?= empty($anni) ? 'disabled' : '' ?>>
                        <i class="fas fa-plus me-2"></i>Crea percorso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
