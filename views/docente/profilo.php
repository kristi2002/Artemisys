<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">
        <i class="fas fa-user-circle me-2" style="color:#1e40af;"></i>Il mio profilo
    </h4>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div style="width:120px;height:120px;border-radius:50%;background:#e8eef8;
                            margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;
                            overflow:hidden;">
                    <?php if (!empty($insegnante['foto'])): ?>
                        <img src="<?= ASSETS_URL ?>public/uploads/insegnanti/<?= $insegnante['foto'] ?>"
                             style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <i class="fas fa-user" style="font-size:3rem;color:#1e40af;"></i>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold mb-1" style="color:#0c1a3a;">
                    <?= htmlspecialchars($insegnante['nome'] . ' ' . $insegnante['cognome']) ?>
                </h5>
                <div class="text-muted small">
                    <i class="fas fa-chalkboard-teacher me-1"></i>Docente
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold" style="color:#0c1a3a;">
                    <i class="fas fa-id-card me-2" style="color:#1e40af;"></i>Informazioni
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0" style="font-size:.9rem;">
                    <tr>
                        <td class="text-muted" style="width:160px;">Username</td>
                        <td class="fw-semibold"><?= htmlspecialchars($insegnante['username']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td><?= htmlspecialchars($insegnante['email'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Telefono</td>
                        <td><?= htmlspecialchars($insegnante['telefono'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Codice fiscale</td>
                        <td><?= htmlspecialchars($insegnante['codice_fiscale'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Data nascita</td>
                        <td><?= $insegnante['data_nascita'] ? date('d/m/Y', strtotime($insegnante['data_nascita'])) : '—' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Indirizzo</td>
                        <td><?= htmlspecialchars($insegnante['indirizzo'] ?? '—') ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="alert alert-info mt-3 small">
            <i class="fas fa-info-circle me-2"></i>
            Per modificare i tuoi dati contatta l'amministrazione.
        </div>
    </div>
</div>
