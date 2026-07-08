<?php
$ordinali = [1=>'Primo Anno',2=>'Secondo Anno',3=>'Terzo Anno',4=>'Quarto Anno',5=>'Quinto Anno',
             6=>'Sesto Anno',7=>'Settimo Anno',8=>'Ottavo Anno',9=>'Nono Anno',10=>'Decimo Anno'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-book-open me-2" style="color:#1e40af;"></i>Lezioni
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:0.75rem;font-weight:600;">
            <?= count($lezioni) ?>
        </span>
    </h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <?php
            $hasFilters = !empty($_GET['q']) || !empty($_GET['data_da']) || !empty($_GET['data_a'])
                       || !empty($_GET['materia_id']) || !empty($_GET['percorso_id'])
                       || (isset($_GET['online']) && $_GET['online'] !== '');
        ?>
        <form method="GET" action="<?= BASE_URL ?>lezioni">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1 text-muted">Cerca</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Titolo, materia, percorso..."
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1 text-muted">Data da</label>
                    <input type="date" name="data_da" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['data_da'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1 text-muted">Data a</label>
                    <input type="date" name="data_a" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['data_a'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1 text-muted">Materia</label>
                    <select name="materia_id" class="form-select form-select-sm">
                        <option value="">Tutte</option>
                        <?php foreach ($materie as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= ($_GET['materia_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1 text-muted">Percorso</label>
                    <select name="percorso_id" class="form-select form-select-sm">
                        <option value="">Tutti</option>
                        <?php foreach ($percorsi as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($_GET['percorso_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1 text-muted">Modalità</label>
                    <select name="online" class="form-select form-select-sm">
                        <option value="">Tutte</option>
                        <option value="1" <?= (string)($_GET['online'] ?? '') === '1' ? 'selected' : '' ?>>Online</option>
                        <option value="0" <?= (string)($_GET['online'] ?? '') === '0' ? 'selected' : '' ?>>In presenza</option>
                    </select>
                </div>
                <div class="col-md-10 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter me-1"></i>Filtra
                    </button>
                    <?php if ($hasFilters): ?>
                        <a href="<?= BASE_URL ?>lezioni" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <?php if (empty($lezioni)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-book-open"></i></div>
                <h5><?= $hasFilters ? 'Nessun risultato' : 'Nessuna lezione' ?></h5>
                <p>
                    <?= $hasFilters
                        ? 'Prova a modificare i filtri o resettali.'
                        : 'Le lezioni vengono create dal dettaglio di ogni materia.' ?>
                </p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4">Titolo</th>
                        <th>Percorso / Anno / Materia</th>
                        <th style="width:110px;">Data</th>
                        <th style="width:90px;">Durata</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lezioni as $lez): ?>
                        <tr style="cursor:pointer;"
                            onclick="location.href='<?= BASE_URL ?>percorsi/lezione/<?= $lez['id'] ?>'">
                            <td class="ps-4 align-middle">
                                <div class="fw-semibold" style="color:#0c1a3a;"><?= htmlspecialchars($lez['titolo']) ?></div>
                                <?php if ($lez['note']): ?>
                                    <div class="text-muted small"><?= htmlspecialchars($lez['note']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <?php if ($lez['percorso_nome']): ?>
                                    <div class="small fw-semibold" style="color:#0c1a3a;"><?= htmlspecialchars($lez['percorso_nome']) ?></div>
                                    <div class="text-muted small">
                                        <?= $ordinali[$lez['anno_numero']] ?? '—' ?> &middot;
                                        <span class="badge" style="background:#e8eef8;color:#1e40af;font-size:0.7rem;">
                                            <?= htmlspecialchars($lez['materia_codice']) ?>
                                        </span>
                                        <?= htmlspecialchars($lez['materia_nome']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-muted small">
                                <?= $lez['data'] ? date('d/m/Y', strtotime($lez['data'])) : '—' ?>
                            </td>
                            <td class="align-middle text-muted small">
                                <?= $lez['durata_minuti'] ? $lez['durata_minuti'] . ' min' : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
