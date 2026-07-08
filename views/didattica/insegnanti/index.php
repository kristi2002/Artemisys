<?php
$ruolo = $_SESSION['user_ruolo'] ?? 'admin';
?>

<!-- Header pagina -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold" style="color:#0c1a3a;">
            <i class="fas fa-chalkboard-teacher me-2" style="color:#1e40af;"></i>Insegnanti
        </h4>
        <span class="text-muted small"><?= $totale ?> insegnant<?= $totale === 1 ? 'e' : 'i' ?> registrat<?= $totale === 1 ? 'o' : 'i' ?></span>
    </div>
    <?php if ($ruolo === 'admin'): ?>
        <a href="<?= BASE_URL ?>insegnanti/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuovo Insegnante
        </a>
    <?php endif; ?>
</div>

<!-- Barra di ricerca -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>insegnanti" class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0"
                       name="q" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Cerca per nome, cognome, email, materia...">
            </div>
            <button type="submit" class="btn btn-primary px-4">Cerca</button>
            <?php if (!empty($search)): ?>
                <a href="<?= BASE_URL ?>insegnanti" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabella insegnanti -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($insegnanti)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h5>Nessun insegnante trovato</h5>
                <p><?= !empty($search) ? 'Nessun risultato per "' . htmlspecialchars($search) . '".' : 'Inizia aggiungendo il primo insegnante.' ?></p>
                <?php if ($ruolo === 'admin' && empty($search)): ?>
                    <a href="<?= BASE_URL ?>insegnanti/create" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-2"></i>Aggiungi Insegnante
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:48px;"></th>
                            <th>Insegnante</th>
                            <th>Email</th>
                            <th>Materie</th>
                            <th>Telefono</th>
                            <th>Stato</th>
                            <th class="text-end pe-4">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($insegnanti as $ins): ?>
                            <tr>
                                <td class="ps-3">
                                    <?php if (!empty($ins['foto'])): ?>
                                        <img src="<?= BASE_URL . htmlspecialchars($ins['foto']) ?>"
                                             alt="" class="ins-avatar">
                                    <?php else: ?>
                                        <div class="ins-avatar-initials">
                                            <?= strtoupper(substr($ins['nome'], 0, 1) . substr($ins['cognome'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="color:#0c1a3a;">
                                        <?= htmlspecialchars($ins['cognome'] . ' ' . $ins['nome']) ?>
                                    </div>
                                    <small class="text-muted">@<?= htmlspecialchars($ins['username']) ?></small>
                                </td>
                                <td class="text-muted small"><?= htmlspecialchars($ins['email']) ?></td>
                                <td>
                                    <?php if (!empty($ins['materie'])): ?>
                                        <?php foreach (array_slice(explode(',', $ins['materie']), 0, 3) as $m): ?>
                                            <span class="badge-materia"><?= htmlspecialchars(trim($m)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= !empty($ins['telefono']) ? htmlspecialchars($ins['telefono']) : '—' ?>
                                </td>
                                <td>
                                    <?php if ($ins['attivo']): ?>
                                        <span class="badge-status attivo"><i class="fas fa-circle me-1"></i>Attivo</span>
                                    <?php else: ?>
                                        <span class="badge-status disattivo"><i class="fas fa-circle me-1"></i>Disattivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>insegnanti/detail/<?= $ins['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Dettaglio
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
