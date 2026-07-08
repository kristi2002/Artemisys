<div class="stu-page-title">Bacheca</div>

<?php if (empty($comunicazioni)): ?>
    <div class="stu-card">
        <div class="empty-stu">
            <i class="fas fa-bullhorn"></i>
            <small>Nessuna comunicazione</small>
        </div>
    </div>
<?php else: foreach ($comunicazioni as $c): ?>
<a href="<?= BASE_URL ?>bacheca/detail/<?= $c['id'] ?>" class="stu-card stu-card-link"
   style="display:block;border-left:3px solid #1e40af;margin-bottom:10px;">
    <div class="d-flex align-items-start gap-3">
        <div style="flex-shrink:0;width:36px;height:36px;border-radius:9px;background:#e8eef8;
                    display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-bullhorn" style="color:#1e40af;font-size:.82rem;"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;flex-wrap:wrap;">
                <span class="badge-soft" style="background:#e8eef8;color:#1e40af;font-size:.65rem;">
                    <?= ucfirst($c['tipo']) ?>
                </span>
                <span class="text-muted" style="font-size:.68rem;">
                    <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?>
                </span>
            </div>
            <div class="fw-bold" style="color:#0c1a3a;font-size:.9rem;line-height:1.25;">
                <?= htmlspecialchars($c['titolo']) ?>
            </div>
            <div class="text-muted mt-1" style="font-size:.76rem;line-height:1.4;
                 display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                <?= htmlspecialchars($c['contenuto']) ?>
            </div>
        </div>
        <i class="fas fa-chevron-right text-muted align-self-center" style="font-size:.75rem;"></i>
    </div>
</a>
<?php endforeach; endif; ?>
