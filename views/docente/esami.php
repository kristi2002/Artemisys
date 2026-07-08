<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];
$tipoBadge = ['scritto'=>'#3b82f6','orale'=>'#10b981','pratico'=>'#f59e0b'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-file-alt me-2" style="color:#1e40af;"></i>I miei esami
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:.75rem;">
            <?= count($esami) ?>
        </span>
    </h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($esami)): ?>
            <div class="empty-state" style="padding:3rem 0;">
                <div class="empty-state-icon"><i class="fas fa-file-alt"></i></div>
                <h5>Nessun esame</h5>
                <p>Non ci sono esami nelle tue materie.</p>
            </div>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4">Esame</th>
                        <th>Materia</th>
                        <th style="width:100px;">Data</th>
                        <th style="width:80px;">Tipo</th>
                        <th style="width:90px;" class="text-center">Iscritti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($esami as $e):
                        $nomeAnno = $ordinali[$e['anno_numero']] ?? $e['anno_numero'].'° Anno';
                        $colore   = $tipoBadge[$e['tipo']] ?? '#64748b';
                    ?>
                        <tr style="cursor:pointer;"
                            onclick="window.location='<?= BASE_URL ?>esami/detail/<?= $e['id'] ?>'">
                            <td class="ps-4 align-middle">
                                <div class="fw-semibold" style="color:#0c1a3a;">
                                    <?= htmlspecialchars($e['titolo']) ?>
                                </div>
                                <div class="text-muted small">
                                    <?= htmlspecialchars($e['percorso_nome']) ?> — <?= $nomeAnno ?>
                                    <?php if ($e['anno_label']): ?> · A.S. <?= htmlspecialchars($e['anno_label']) ?><?php endif; ?>
                                </div>
                            </td>
                            <td class="align-middle" style="font-size:.88rem;color:#374151;">
                                <?= htmlspecialchars($e['materia_nome']) ?>
                            </td>
                            <td class="align-middle text-muted small">
                                <?= $e['data'] ? date('d/m/Y', strtotime($e['data'])) : '—' ?>
                            </td>
                            <td class="align-middle">
                                <span class="badge" style="background:<?= $colore ?>1a;color:<?= $colore ?>;border:1px solid <?= $colore ?>33;font-size:.75rem;">
                                    <?= ucfirst($e['tipo']) ?>
                                </span>
                            </td>
                            <td class="align-middle text-center" style="font-size:.85rem;">
                                <span class="fw-semibold"><?= $e['num_iscritti'] ?></span>
                                <?php if ($e['num_valutati'] > 0): ?>
                                    <div class="text-muted" style="font-size:.72rem;"><?= $e['num_valutati'] ?> val.</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
