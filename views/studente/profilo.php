<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];
?>

<div class="stu-page-title">Il mio profilo</div>

<!-- Avatar grande -->
<div class="stu-card text-center" style="padding:24px 16px;">
    <div style="width:100px;height:100px;margin:0 auto 12px;border-radius:50%;
                background:linear-gradient(135deg,#1e40af,#3b82f6);
                display:flex;align-items:center;justify-content:center;color:#fff;
                box-shadow:0 4px 12px rgba(30,64,175,.3);">
        <?php if (!empty($studente['foto'])): ?>
            <img src="<?= ASSETS_URL ?>public/uploads/studenti/<?= htmlspecialchars($studente['foto']) ?>"
                 style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
        <?php else: ?>
            <i class="fas fa-user-graduate" style="font-size:2.5rem;"></i>
        <?php endif; ?>
    </div>
    <h5 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <?= htmlspecialchars($studente['nome'] . ' ' . $studente['cognome']) ?>
    </h5>
    <div class="text-muted" style="font-size:.82rem;">Studente</div>
</div>

<!-- Corsi attivi -->
<?php if (!empty($percorsiCorrenti)): ?>
    <?php foreach ($percorsiCorrenti as $pc): ?>
    <div class="stu-card" style="border-left:4px solid #3b82f6;">
        <div style="font-size:.7rem;text-transform:uppercase;color:#94a3b8;letter-spacing:.05em;font-weight:700;">
            Corso attivo
        </div>
        <div class="fw-bold mt-1" style="color:#0c1a3a;font-size:1rem;">
            <?= htmlspecialchars($pc['percorso_nome']) ?>
        </div>
        <div class="text-muted" style="font-size:.85rem;">
            <?php if (!empty($pc['numero'])): ?>
                <?= $ordinali[$pc['numero']] ?? $pc['numero'].'° Anno' ?>
            <?php endif; ?>
            <?php if (!empty($pc['anno_label'])): ?>
                · A.S. <?= htmlspecialchars($pc['anno_label']) ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php elseif (!empty($percorsoCorrente)): ?>
<div class="stu-card" style="border-left:4px solid #3b82f6;">
    <div style="font-size:.7rem;text-transform:uppercase;color:#94a3b8;letter-spacing:.05em;font-weight:700;">
        Percorso attuale
    </div>
    <div class="fw-bold mt-1" style="color:#0c1a3a;font-size:1rem;">
        <?= htmlspecialchars($percorsoCorrente['percorso_nome']) ?>
    </div>
    <div class="text-muted" style="font-size:.85rem;">
        <?= $ordinali[$percorsoCorrente['numero']] ?? $percorsoCorrente['numero'].'° Anno' ?>
        <?php if ($percorsoCorrente['anno_label']): ?>
            · A.S. <?= htmlspecialchars($percorsoCorrente['anno_label']) ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Dati -->
<div class="stu-card">
    <h6 class="fw-bold mb-3" style="color:#0c1a3a;font-size:.92rem;">
        <i class="fas fa-id-card me-2" style="color:#1e40af;"></i>Dati personali
    </h6>
    <?php
    $info = [
        ['Email',          'fa-envelope',   $studente['email']],
        ['Telefono',       'fa-phone',      $studente['telefono']],
        ['Codice fiscale', 'fa-id-badge',   $studente['codice_fiscale']],
        ['Data nascita',   'fa-cake-candles', $studente['data_nascita'] ? date('d/m/Y', strtotime($studente['data_nascita'])) : null],
        ['Indirizzo',      'fa-map-marker-alt', $studente['indirizzo']],
    ];
    foreach ($info as [$lbl, $ico, $val]):
        if (!$val) continue;
    ?>
    <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid #f1f5f9;">
        <div style="flex-shrink:0;width:32px;text-align:center;">
            <i class="fas <?= $ico ?>" style="color:#94a3b8;"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">
                <?= $lbl ?>
            </div>
            <div style="color:#0c1a3a;font-size:.88rem;"><?= htmlspecialchars($val) ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Logout -->
<a href="<?= BASE_URL ?>auth/logout" class="stu-card stu-card-link d-flex align-items-center justify-content-center gap-2" style="color:#ef4444;font-weight:600;">
    <i class="fas fa-sign-out-alt"></i> Esci
</a>

<div class="text-center text-muted mt-3" style="font-size:.7rem;">
    Artemisys · Versione studente
</div>
