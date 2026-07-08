<?php
$totLez = 0; $totPre = 0;
foreach ($perMateria as $m) { $totLez += $m['tot_lezioni']; $totPre += $m['presenze_count']; }
$percTot = $totLez > 0 ? round($totPre / $totLez * 100) : 0;
?>

<div class="stu-page-title">Le mie presenze</div>

<?php if ($totLez === 0): ?>
    <div class="stu-card">
        <div class="empty-stu">
            <i class="fas fa-clipboard-check"></i>
            <small>Nessuna lezione registrata</small>
        </div>
    </div>
<?php else: ?>

<!-- Riepilogo totale -->
<div class="stu-card" style="margin-bottom:12px;">
    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin-bottom:10px;">
        Riepilogo generale
    </div>
    <div style="display:flex;align-items:center;gap:16px;">
        <div style="flex:1;">
            <div style="font-size:2rem;font-weight:800;color:#0c1a3a;line-height:1;"><?= $percTot ?>%</div>
            <div style="font-size:.78rem;color:#64748b;margin-top:2px;"><?= $totPre ?> presenze su <?= $totLez ?> lezioni</div>
        </div>
        <div style="display:flex;gap:8px;">
            <div style="text-align:center;background:#f8fafc;border-radius:10px;padding:10px 14px;">
                <div style="font-size:1.1rem;font-weight:800;color:#0c1a3a;"><?= $totPre ?></div>
                <div style="font-size:.62rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-top:2px;">Presenze</div>
            </div>
            <div style="text-align:center;background:#f8fafc;border-radius:10px;padding:10px 14px;">
                <div style="font-size:1.1rem;font-weight:800;color:#0c1a3a;"><?= $totLez - $totPre ?></div>
                <div style="font-size:.62rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-top:2px;">Assenze</div>
            </div>
        </div>
    </div>
    <div style="margin-top:12px;background:#e8eef8;border-radius:8px;height:8px;overflow:hidden;">
        <div style="height:100%;width:<?= $percTot ?>%;background:#1e40af;border-radius:8px;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:5px;">
        <span style="font-size:.65rem;color:#94a3b8;">0%</span>
        <span style="font-size:.65rem;color:#94a3b8;font-weight:600;">Soglia minima: 75%</span>
        <span style="font-size:.65rem;color:#94a3b8;">100%</span>
    </div>
</div>

<!-- Separatore -->
<div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin:16px 0 8px;">
    Dettaglio per materia
</div>

<!-- Lista materie -->
<?php foreach ($perMateria as $m):
    $tot  = (int)$m['tot_lezioni'];
    if ($tot === 0) continue;
    $pre  = (int)$m['presenze_count'];
    $ass  = $tot - $pre;
    $perc = round($pre / $tot * 100);
    $ok   = $perc >= 75;
?>
<div class="stu-card" style="margin-bottom:10px;padding:14px 16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;gap:8px;">
        <div style="font-weight:600;color:#0c1a3a;font-size:.88rem;flex:1;min-width:0;">
            <i class="fas fa-atom me-2" style="color:#1e40af;font-size:.75rem;"></i><?= htmlspecialchars($m['materia_nome']) ?>
        </div>
        <div style="background:#e8eef8;color:#1e40af;border-radius:20px;padding:2px 10px;
                    font-size:.75rem;font-weight:700;white-space:nowrap;flex-shrink:0;">
            <?= $perc ?>%
        </div>
    </div>
    <div style="background:#e8eef8;border-radius:6px;height:5px;overflow:hidden;margin-bottom:8px;">
        <div style="height:100%;width:<?= $perc ?>%;background:#1e40af;border-radius:6px;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:.7rem;color:#64748b;"><?= $pre ?> pres. · <?= $ass ?> ass. · <?= $tot ?> tot.</span>
        <?php if (!$ok): ?>
            <span style="font-size:.65rem;color:#64748b;background:#f1f5f9;border-radius:20px;padding:2px 8px;">
                <i class="fas fa-exclamation-circle me-1"></i>sotto soglia
            </span>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- Nota -->
<div style="margin-top:4px;padding:10px 14px;background:#f8fafc;border-radius:10px;
            display:flex;align-items:center;gap:8px;border:1px solid #e2e8f0;">
    <i class="fas fa-info-circle" style="color:#1e40af;font-size:.8rem;flex-shrink:0;"></i>
    <span style="font-size:.72rem;color:#64748b;line-height:1.5;">
        La frequenza minima richiesta è del <strong style="color:#0c1a3a;">75%</strong>. Le materie sotto soglia sono evidenziate.
    </span>
</div>

<?php endif; ?>
