<?php $oggi = date('Y-m-d'); ?>

<div class="stu-page-title">Le mie rette</div>

<?php if (empty($rette)): ?>
    <div class="stu-card">
        <div class="empty-stu">
            <i class="fas fa-euro-sign"></i>
            <small>Nessuna retta registrata</small>
        </div>
    </div>
<?php else: foreach ($rette as $r):
    $tot      = (float)$r['importo_totale'];
    $daPagare = (float)$r['da_pagare'];
    $pagato   = $tot - $daPagare;
    $perc     = $tot > 0 ? round($pagato / $tot * 100) : 0;
?>
<div class="stu-card" style="margin-bottom:12px;">
    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin-bottom:10px;">
        Riepilogo retta
    </div>
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:12px;">
        <div>
            <div style="font-size:1.6rem;font-weight:800;color:#0c1a3a;line-height:1;">€ <?= number_format($tot, 2, ',', '.') ?></div>
            <div style="font-size:.74rem;color:#64748b;margin-top:3px;">
                <?= $r['rate_pagate'] ?>/<?= $r['tot_rate'] ?> rate · <?= ucfirst($r['cadenza']) ?>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:.68rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Da pagare</div>
            <div style="font-size:1.1rem;font-weight:800;color:#0c1a3a;">€ <?= number_format($daPagare, 2, ',', '.') ?></div>
        </div>
    </div>
    <div style="background:#e8eef8;border-radius:6px;height:6px;overflow:hidden;margin-bottom:5px;">
        <div style="height:100%;width:<?= $perc ?>%;background:#1e40af;border-radius:6px;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;">
        <span style="font-size:.65rem;color:#94a3b8;">Pagato <?= $perc ?>%</span>
        <span style="font-size:.65rem;color:#94a3b8;">€ <?= number_format($pagato, 2, ',', '.') ?> su <?= number_format($tot, 2, ',', '.') ?></span>
    </div>
</div>
<?php endforeach; ?>

<div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin:16px 0 8px;">
    Dettaglio rate
</div>

<?php foreach ($rate as $rp):
    $scaduta = !$rp['pagata'] && $rp['scadenza'] && $rp['scadenza'] < $oggi;
    if ($rp['pagata'])  { $lbl = 'Pagata';    $bg = '#e8eef8'; $col = '#1e40af'; $ico = 'fa-check-circle'; }
    elseif ($scaduta)   { $lbl = 'Scaduta';   $bg = '#f1f5f9'; $col = '#64748b'; $ico = 'fa-exclamation-circle'; }
    else                { $lbl = 'Da pagare'; $bg = '#f1f5f9'; $col = '#64748b'; $ico = 'fa-clock'; }
?>
<div class="stu-card d-flex align-items-center gap-3" style="margin-bottom:8px;">
    <div style="flex-shrink:0;width:44px;height:44px;border-radius:10px;background:<?= $bg ?>;color:<?= $col ?>;
                display:flex;align-items:center;justify-content:center;font-size:1rem;">
        <i class="fas <?= $ico ?>"></i>
    </div>
    <div class="flex-grow-1" style="min-width:0;">
        <div class="fw-bold" style="color:#0c1a3a;font-size:.9rem;">Rata #<?= $rp['numero'] ?></div>
        <div class="text-muted" style="font-size:.74rem;">
            <?php if ($rp['pagata']): ?>
                Pagata il <?= date('d/m/Y', strtotime($rp['data_pagamento'])) ?>
                <?php if ($rp['metodo']): ?> · <?= htmlspecialchars($rp['metodo']) ?><?php endif; ?>
            <?php elseif ($rp['scadenza']): ?>
                <?= $scaduta ? 'Scaduta il' : 'Scade il' ?> <?= date('d/m/Y', strtotime($rp['scadenza'])) ?>
            <?php else: ?>
                Senza scadenza
            <?php endif; ?>
        </div>
    </div>
    <div style="text-align:right;flex-shrink:0;">
        <div class="fw-bold" style="color:#0c1a3a;font-size:.92rem;">€ <?= number_format($rp['importo'], 2, ',', '.') ?></div>
        <div class="badge-soft mt-1" style="background:<?= $bg ?>;color:<?= $col ?>;font-size:.65rem;">
            <?= $lbl ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>
