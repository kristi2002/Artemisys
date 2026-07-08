<?php
$mesiIt = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
$tipoLabel = [
    'workshop'=>'Workshop','seminario'=>'Seminario','open_day'=>'Open Day',
    'competizione'=>'Competizione','sociale'=>'Festa','altro'=>'Altro',
];
$tipoIcona = [
    'workshop'=>'fa-tools','seminario'=>'fa-chalkboard-teacher','open_day'=>'fa-door-open',
    'competizione'=>'fa-trophy','sociale'=>'fa-glass-cheers','altro'=>'fa-calendar-day',
];
$oggi = date('Y-m-d');
?>

<div class="stu-page-title">Eventi</div>

<?php if (empty($eventi)): ?>
    <div class="stu-card">
        <div class="empty-stu">
            <i class="fas fa-calendar-day"></i>
            <small>Nessun evento disponibile</small>
        </div>
    </div>
<?php else: foreach ($eventi as $e):
    $ico      = $tipoIcona[$e['tipo']] ?? 'fa-calendar-day';
    $g        = (int)date('j', strtotime($e['data_evento']));
    $m        = $mesiIt[(int)date('n', strtotime($e['data_evento']))-1];
    $isFull   = $e['max_iscritti'] && $e['num_iscritti'] >= $e['max_iscritti'];
    $iscritto = (int)$e['sono_iscritto'] === 1;
    $aperto   = $e['stato'] === 'aperto';
    $passato  = $e['data_evento'] < $oggi;
    $puoIscriversi = $aperto && !$isFull && !$iscritto && !$passato;
?>
<div class="stu-card" style="margin-bottom:10px;border-left:3px solid #1e40af;">
    <div class="d-flex align-items-start gap-3 mb-2">
        <div style="flex-shrink:0;width:48px;text-align:center;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#fff;">
            <div style="background:#1e40af;color:#fff;padding:1px 0;font-size:.58rem;font-weight:700;text-transform:uppercase;"><?= $m ?></div>
            <div style="padding:4px 0;font-size:1.15rem;font-weight:800;color:#0c1a3a;line-height:1;"><?= $g ?></div>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <span class="badge-soft" style="background:#e8eef8;color:#1e40af;font-size:.65rem;">
                <i class="fas <?= $ico ?> me-1"></i><?= $tipoLabel[$e['tipo']] ?? ucfirst($e['tipo']) ?>
            </span>
            <h6 class="mb-0 fw-bold mt-1" style="color:#0c1a3a;font-size:.92rem;line-height:1.25;">
                <?= htmlspecialchars($e['titolo']) ?>
            </h6>
            <div class="text-muted" style="font-size:.74rem;margin-top:2px;">
                <?php if ($e['ora_inizio']): ?>
                <i class="fas fa-clock me-1"></i><?= substr($e['ora_inizio'], 0, 5) ?>
                <?php endif; ?>
                <?php if ($e['luogo']): ?>
                <i class="fas fa-map-marker-alt ms-2 me-1"></i><?= htmlspecialchars($e['luogo']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($e['descrizione']): ?>
    <p class="mb-2" style="font-size:.8rem;color:#475569;line-height:1.4;
       display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
        <?= htmlspecialchars($e['descrizione']) ?>
    </p>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between pt-2" style="border-top:1px solid #f1f5f9;">
        <div style="font-size:.72rem;color:#64748b;">
            <i class="fas fa-users me-1"></i>
            <?= $e['num_iscritti'] ?><?= $e['max_iscritti'] ? ' / ' . $e['max_iscritti'] : '' ?> iscritti
        </div>
        <?php if ($iscritto): ?>
            <span class="badge-soft" style="background:#e8eef8;color:#1e40af;font-size:.68rem;">
                <i class="fas fa-check me-1"></i>Iscritto
            </span>
        <?php elseif ($puoIscriversi): ?>
            <form method="POST" action="<?= BASE_URL ?>studente/iscriviti">
                <input type="hidden" name="evento_id" value="<?= $e['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.76rem;">
                    <i class="fas fa-plus me-1"></i>Iscriviti
                </button>
            </form>
        <?php elseif ($isFull): ?>
            <span class="badge-soft" style="background:#f1f5f9;color:#64748b;font-size:.68rem;">Completo</span>
        <?php elseif ($passato): ?>
            <span class="badge-soft" style="background:#f1f5f9;color:#94a3b8;font-size:.68rem;">Concluso</span>
        <?php else: ?>
            <span class="badge-soft" style="background:#f1f5f9;color:#64748b;font-size:.68rem;">Chiuso</span>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; endif; ?>
