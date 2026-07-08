<?php
$tipoIcon = [
    'Teorica' => 'fa-book',
    'Pratica' => 'fa-tools',
    'P/S/O'   => 'fa-tasks',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-clock me-2" style="color:#1e40af;"></i>
        <?= htmlspecialchars($pageTitle) ?>
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:.75rem;">
            <?= count($prove) ?>
        </span>
    </h4>
</div>

<?php if (empty($prove)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3 d-block" style="color:#cbd5e1;"></i>
            Nessuna prova registrata.
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f0f4ff;">
                    <tr>
                        <th class="px-4 py-3" style="color:#1e40af;font-weight:600;">#</th>
                        <th class="py-3" style="color:#1e40af;font-weight:600;">Esame</th>
                        <th class="py-3" style="color:#1e40af;font-weight:600;">Tipo</th>
                        <th class="py-3" style="color:#1e40af;font-weight:600;">Data</th>
                        <th class="py-3" style="color:#1e40af;font-weight:600;">
                            <i class="fas fa-play me-1" style="color:#10b981;"></i>Ora inizio
                        </th>
                        <th class="py-3" style="color:#1e40af;font-weight:600;">
                            <i class="fas fa-stop me-1" style="color:#ef4444;"></i>Ora fine
                        </th>
                        <th class="py-3" style="color:#1e40af;font-weight:600;">Durata</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prove as $i => $p): ?>
                        <?php
                            $durata = '';
                            if ($p['ora_inizio'] && $p['ora_fine']) {
                                [$h1,$m1] = array_map('intval', explode(':', $p['ora_inizio']));
                                [$h2,$m2] = array_map('intval', explode(':', $p['ora_fine']));
                                $minTot = ($h2 * 60 + $m2) - ($h1 * 60 + $m1);
                                if ($minTot > 0) {
                                    $durata = floor($minTot / 60) . 'h ' . ($minTot % 60) . 'min';
                                }
                            }
                            $icon = $tipoIcon[$p['tipo_prova']] ?? 'fa-file-alt';
                        ?>
                        <tr>
                            <td class="px-4 text-muted" style="font-size:.85rem;"><?= $i + 1 ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>esami-di-stato-prova/detail/<?= $p['esame_di_stato_id'] ?>"
                                   class="fw-semibold text-decoration-none" style="color:#0c1a3a;">
                                    <?= htmlspecialchars($p['esame_nome']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge rounded-pill" style="background:#e8eef8;color:#1e40af;">
                                    <i class="fas <?= $icon ?> me-1"></i><?= htmlspecialchars($p['tipo_prova']) ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?= $p['data'] ? date('d/m/Y', strtotime($p['data'])) : '—' ?>
                            </td>
                            <td>
                                <?php if ($p['ora_inizio']): ?>
                                    <span class="fw-bold" style="color:#10b981;font-size:1.05rem;">
                                        <?= htmlspecialchars(substr($p['ora_inizio'], 0, 5)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['ora_fine']): ?>
                                    <span class="fw-bold" style="color:#ef4444;font-size:1.05rem;">
                                        <?= htmlspecialchars(substr($p['ora_fine'], 0, 5)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size:.9rem;">
                                <?= $durata ?: '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
