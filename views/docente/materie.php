<?php
$ordinali = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno',
             6=>'6° Anno',7=>'7° Anno',8=>'8° Anno',9=>'9° Anno',10=>'10° Anno'];

$palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#ec4899'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-book-open me-2" style="color:#1e40af;"></i>Le mie materie
        <span class="badge ms-2" style="background:#e8eef8;color:#1e40af;font-size:.75rem;">
            <?= count($materie) ?>
        </span>
    </h4>
</div>

<?php if (empty($materie)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="empty-state" style="padding:3rem 0;">
                <div class="empty-state-icon"><i class="fas fa-book"></i></div>
                <h5>Nessuna materia assegnata</h5>
                <p>Contatta l'amministrazione per essere assegnato alle materie.</p>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="row g-3">
    <?php foreach ($materie as $i => $m):
        $col = $palette[$i % count($palette)];
        $nomeAnno = $ordinali[$m['anno_numero']] ?? $m['anno_numero'].'° Anno';
    ?>
    <div class="col-lg-4 col-md-6">
        <a href="<?= BASE_URL ?>percorsi/materia/<?= $m['pam_id'] ?>"
           class="card border-0 shadow-sm h-100 text-decoration-none"
           style="border-left:4px solid <?= $col ?> !important;transition:.15s;"
           onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div style="width:36px;height:36px;border-radius:8px;background:<?= $col ?>1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-atom" style="color:<?= $col ?>;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold" style="color:#0c1a3a;line-height:1.25;">
                            <?= htmlspecialchars($m['materia_nome']) ?>
                        </h6>
                        <div class="text-muted" style="font-size:.72rem;">
                            <?= htmlspecialchars($m['materia_codice']) ?>
                        </div>
                    </div>
                </div>

                <div class="text-muted small mb-2" style="font-size:.78rem;line-height:1.5;">
                    <i class="fas fa-route me-1"></i><?= htmlspecialchars($m['percorso_nome']) ?><br>
                    <i class="fas fa-graduation-cap me-1"></i><?= $nomeAnno ?>
                    <?php if ($m['anno_label']): ?> · A.S. <?= htmlspecialchars($m['anno_label']) ?><?php endif; ?>
                </div>

                <div class="d-flex align-items-center justify-content-between border-top pt-2"
                     style="font-size:.8rem;">
                    <span class="text-muted">
                        <i class="fas fa-book me-1"></i><?= $m['num_lezioni'] ?> lezioni
                    </span>
                    <span class="fw-semibold" style="color:<?= $col ?>;">
                        Apri <i class="fas fa-arrow-right ms-1"></i>
                    </span>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>
