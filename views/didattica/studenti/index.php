<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-user-graduate me-2" style="color:#1e40af;"></i>Studenti
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:0.75rem;font-weight:600;">
            <?= $totale ?>
        </span>
    </h4>
    <a href="<?= BASE_URL ?>studenti/create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Nuovo studente
    </a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <form method="GET" action="<?= BASE_URL ?>studenti" class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-sm"
                   placeholder="Cerca per nome, cognome, email, codice fiscale..."
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-search"></i>
            </button>
            <?php if (!empty($_GET['q'])): ?>
                <a href="<?= BASE_URL ?>studenti" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <?php if (empty($studenti)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-user-graduate"></i></div>
                <h5><?= !empty($_GET['q']) ? 'Nessun risultato' : 'Nessuno studente' ?></h5>
                <p>
                    <?= !empty($_GET['q'])
                        ? 'Prova con un termine diverso.'
                        : '<a href="' . BASE_URL . 'studenti/create">Aggiungi il primo studente</a>.' ?>
                </p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4">Studente</th>
                        <th>Percorso</th>
                        <th style="width:110px;">Anno corrente</th>
                        <th style="width:80px;">Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($studenti as $s): ?>
                        <tr style="cursor:pointer;" onclick="location.href='<?= BASE_URL ?>studenti/detail/<?= $s['id'] ?>'">
                            <td class="ps-4 align-middle">
                                <div class="fw-semibold" style="color:#0c1a3a;">
                                    <?= htmlspecialchars($s['cognome'] . ' ' . $s['nome']) ?>
                                </div>
                                <div class="text-muted small"><?= htmlspecialchars($s['email'] ?? '') ?></div>
                            </td>
                            <td class="align-middle">
                                <?php if (!empty($s['iscrizioni'])): ?>
                                    <?php foreach ($s['iscrizioni'] as $isc): ?>
                                        <div class="small fw-semibold" style="color:#0c1a3a;"><?= htmlspecialchars($isc['percorso_nome']) ?></div>
                                        <div class="text-muted small">A.S. <?= htmlspecialchars($isc['anno_label'] ?? '') ?></div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Non iscritto</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <span class="text-muted small">—</span>
                            </td>
                            <td class="align-middle">
                                <?php if ($s['attivo']): ?>
                                    <span class="badge" style="background:#dcfce7;color:#166534;">Attivo</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#fee2e2;color:#991b1b;">Inattivo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
