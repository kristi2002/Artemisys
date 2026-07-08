<?php
$ruolo = $_SESSION['user_ruolo'] ?? 'admin';
?>

<!-- Alert credenziali (mostrato dopo la creazione) -->
<?php if (!empty($newCredentials)): ?>
<div id="credentials-alert-data"
     data-username="<?= htmlspecialchars($newCredentials['username']) ?>"
     data-password="<?= htmlspecialchars($newCredentials['password']) ?>"
     data-nome="<?= htmlspecialchars($newCredentials['nome']) ?>">
</div>
<?php endif; ?>

<!-- Flash messages -->
<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($successMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($errorMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <a href="<?= BASE_URL ?>insegnanti" class="text-muted small text-decoration-none">
        <i class="fas fa-arrow-left me-1"></i>Torna alla lista
    </a>
</div>

<!-- Card Profilo Header -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <!-- Foto / Avatar -->
            <div class="position-relative">
                <?php if (!empty($insegnante['foto'])): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($insegnante['foto']) ?>"
                         alt="" class="ins-avatar-xl">
                <?php else: ?>
                    <div class="ins-avatar-initials ins-avatar-xl">
                        <?= strtoupper(substr($insegnante['nome'], 0, 1) . substr($insegnante['cognome'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <?php if ($ruolo === 'admin'): ?>
                    <button type="button" class="btn-foto-edit" title="Cambia foto" data-bs-toggle="modal" data-bs-target="#modalFoto">
                        <i class="fas fa-camera"></i>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Info principali -->
            <div class="flex-grow-1">
                <h4 class="mb-1 fw-bold" style="color:#0c1a3a;">
                    <?= htmlspecialchars($insegnante['nome'] . ' ' . $insegnante['cognome']) ?>
                </h4>
                <div class="text-muted mb-2">
                    <i class="fas fa-at me-1"></i><?= htmlspecialchars($insegnante['username']) ?>
                    &nbsp;·&nbsp;
                    <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($insegnante['email']) ?>
                </div>
                <?php if (!empty($insegnante['materie'])): ?>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (explode(',', $insegnante['materie']) as $m): ?>
                            <span class="badge-materia"><?= htmlspecialchars(trim($m)) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Stato + azioni -->
            <div class="d-flex flex-column align-items-end gap-2">
                <?php if ($insegnante['attivo']): ?>
                    <span class="badge-status attivo"><i class="fas fa-circle me-1"></i>Attivo</span>
                <?php else: ?>
                    <span class="badge-status disattivo"><i class="fas fa-circle me-1"></i>Disattivo</span>
                <?php endif; ?>

                <?php if ($ruolo === 'admin'): ?>
                    <form method="POST" action="<?= BASE_URL ?>insegnanti/toggle-status">
                        <input type="hidden" name="id" value="<?= $insegnante['id'] ?>">
                        <button type="submit" class="btn btn-sm <?= $insegnante['attivo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                            <?= $insegnante['attivo'] ? '<i class="fas fa-ban me-1"></i>Disattiva' : '<i class="fas fa-check me-1"></i>Attiva' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info veloci -->
        <hr class="my-3">
        <div class="row g-3 text-sm">
            <?php if (!empty($insegnante['telefono'])): ?>
                <div class="col-auto">
                    <span class="text-muted"><i class="fas fa-phone me-1"></i></span>
                    <span><?= htmlspecialchars($insegnante['telefono']) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($insegnante['data_nascita'])): ?>
                <div class="col-auto">
                    <span class="text-muted"><i class="fas fa-birthday-cake me-1"></i></span>
                    <span><?= date('d/m/Y', strtotime($insegnante['data_nascita'])) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($insegnante['codice_fiscale'])): ?>
                <div class="col-auto">
                    <span class="text-muted"><i class="fas fa-id-card me-1"></i></span>
                    <span class="font-monospace"><?= htmlspecialchars($insegnante['codice_fiscale']) ?></span>
                </div>
            <?php endif; ?>
            <div class="col-auto ms-auto">
                <span class="text-muted small">Iscritto il <?= date('d/m/Y', strtotime($insegnante['created_at'])) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- MODIFICA DATI ANAGRAFICI -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="fas fa-id-card me-2 text-primary"></i>Dati Anagrafici</h6>
            </div>
            <div class="card-body">
                <form id="form-dati-anagrafici" method="POST" action="<?= BASE_URL ?>insegnanti/update">
                    <input type="hidden" name="id"      value="<?= $insegnante['id'] ?>">
                    <input type="hidden" name="section" value="dati">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome"
                                   value="<?= htmlspecialchars($insegnante['nome']) ?>" required
                                   <?= $ruolo !== 'admin' ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cognome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="cognome"
                                   value="<?= htmlspecialchars($insegnante['cognome']) ?>" required
                                   <?= $ruolo !== 'admin' ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email"
                                   value="<?= htmlspecialchars($insegnante['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-at text-muted"></i></span>
                                <input type="text" class="form-control" name="username"
                                       value="<?= htmlspecialchars($insegnante['username']) ?>" required
                                       <?= $ruolo !== 'admin' ? 'readonly' : '' ?>>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefono</label>
                            <input type="tel" class="form-control" name="telefono"
                                   value="<?= htmlspecialchars($insegnante['telefono'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Data di Nascita</label>
                            <input type="date" class="form-control" name="data_nascita"
                                   value="<?= htmlspecialchars($insegnante['data_nascita'] ?? '') ?>"
                                   <?= $ruolo !== 'admin' ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Codice Fiscale</label>
                            <input type="text" class="form-control text-uppercase" name="codice_fiscale"
                                   value="<?= htmlspecialchars($insegnante['codice_fiscale'] ?? '') ?>"
                                   maxlength="16"
                                   <?= $ruolo !== 'admin' ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Indirizzo</label>
                            <input type="text" class="form-control" name="indirizzo"
                                   value="<?= htmlspecialchars($insegnante['indirizzo'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Materie Insegnate</label>
                            <input type="text" class="form-control" name="materie"
                                   value="<?= htmlspecialchars($insegnante['materie'] ?? '') ?>"
                                   placeholder="Es. Matematica, Fisica (separare con virgola)"
                                   <?= $ruolo !== 'admin' ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Note</label>
                            <textarea class="form-control" name="note" rows="3"><?= htmlspecialchars($insegnante['note'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salva Modifiche
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- COLONNA DESTRA: password + info -->
    <div class="col-lg-4">

        <!-- Cambio Password -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-key me-2 text-primary"></i>Cambia Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>insegnanti/update">
                    <input type="hidden" name="id"      value="<?= $insegnante['id'] ?>">
                    <input type="hidden" name="section" value="password">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nuova Password</label>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace" name="new_password"
                                   placeholder="min. 8 caratteri" minlength="8" id="inp-new-pwd">
                            <button type="button" class="btn btn-outline-secondary" id="btn-regen-detail" title="Genera">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <small class="text-muted">Sarà richiesto di cambiarla al primo accesso</small>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-save me-2"></i>Aggiorna Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Info Account -->
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Info Account</h6>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Username</span>
                    <span class="font-monospace"><?= htmlspecialchars($insegnante['username']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ruolo</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary">Docente</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Stato</span>
                    <?php if ($insegnante['attivo']): ?>
                        <span class="badge-status attivo"><i class="fas fa-circle me-1"></i>Attivo</span>
                    <?php else: ?>
                        <span class="badge-status disattivo"><i class="fas fa-circle me-1"></i>Disattivo</span>
                    <?php endif; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Creato il</span>
                    <span class="small"><?= date('d/m/Y', strtotime($insegnante['created_at'])) ?></span>
                </div>
                <div class="info-row border-0">
                    <span class="info-label">Aggiornato</span>
                    <span class="small"><?= date('d/m/Y H:i', strtotime($insegnante['updated_at'])) ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Cambio Foto -->
<?php if ($ruolo === 'admin'): ?>
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Aggiorna Foto Profilo</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= BASE_URL ?>insegnanti/update" enctype="multipart/form-data">
                    <input type="hidden" name="id"      value="<?= $insegnante['id'] ?>">
                    <input type="hidden" name="section" value="foto">

                    <div class="text-center mb-3">
                        <?php if (!empty($insegnante['foto'])): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($insegnante['foto']) ?>"
                                 class="ins-avatar-xl mb-2" id="modal-foto-preview">
                        <?php else: ?>
                            <div class="ins-avatar-initials ins-avatar-xl mb-2" id="modal-foto-preview">
                                <?= strtoupper(substr($insegnante['nome'], 0, 1) . substr($insegnante['cognome'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <label for="modal-foto-input" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-upload me-2"></i>Seleziona Foto
                    </label>
                    <input type="file" id="modal-foto-input" name="foto"
                           accept="image/jpeg,image/png,image/webp" class="d-none">
                    <small class="text-muted d-block text-center mb-3">JPG, PNG o WEBP · max 3 MB</small>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Salva Foto
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// ── Alert credenziali dopo creazione ───────────────────────────────────────
var credEl = document.getElementById('credentials-alert-data');
if (credEl) {
    var username = credEl.dataset.username;
    var password = credEl.dataset.password;
    var nome     = credEl.dataset.nome;

    // Piccolo delay per far caricare la pagina
    setTimeout(function() {
        var msg =
            '✅ Insegnante creato con successo!\n\n' +
            '━━━━━━━━━━━━━━━━━━━━━━\n' +
            '  CREDENZIALI DI ACCESSO\n' +
            '━━━━━━━━━━━━━━━━━━━━━━\n\n' +
            '  Docente:   ' + nome + '\n' +
            '  Username:  ' + username + '\n' +
            '  Password:  ' + password + '\n\n' +
            '━━━━━━━━━━━━━━━━━━━━━━\n' +
            'Comunicare queste credenziali all\'insegnante.\n' +
            'La password dovrà essere cambiata al primo accesso.';
        alert(msg);
    }, 300);
}

// ── Rigenera password nel dettaglio ────────────────────────────────────────
var btnRegen = document.getElementById('btn-regen-detail');
if (btnRegen) {
    btnRegen.addEventListener('click', function() {
        var chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#!';
        var pwd = '';
        for (var i = 0; i < 10; i++) {
            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('inp-new-pwd').value = pwd;
    });
}

// ── Preview foto nel modal ─────────────────────────────────────────────────
var modalInput = document.getElementById('modal-foto-input');
if (modalInput) {
    modalInput.addEventListener('change', function() {
        if (!this.files || !this.files[0]) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            var prev = document.getElementById('modal-foto-preview');
            if (prev.tagName === 'IMG') {
                prev.src = e.target.result;
            } else {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'ins-avatar-xl mb-2';
                img.id = 'modal-foto-preview';
                prev.replaceWith(img);
            }
        };
        reader.readAsDataURL(this.files[0]);
    });
}

// Maiuscolo CF
var cfInput = document.querySelector('input[name="codice_fiscale"]');
if (cfInput) {
    cfInput.addEventListener('input', function() { this.value = this.value.toUpperCase(); });
}

// ── Helper banner flash ────────────────────────────────────────────────────
function flashBannerIns(type, msg) {
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const div  = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show mb-4`;
    div.setAttribute('role', 'alert');
    div.innerHTML = `<i class="fas ${icon} me-2"></i>${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>`;
    // Inserisce dopo il breadcrumb (prima del .row.g-4)
    const anchor = document.querySelector('.row.g-4') ?? document.body;
    anchor.parentNode.insertBefore(div, anchor);
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(div).close(), 4000);
}

// ── AJAX form dati anagrafici ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-dati-anagrafici');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const labelOrig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvataggio…';

        try {
            const r    = await fetch(form.action, {
                method  : 'POST',
                headers : { 'X-Requested-With': 'XMLHttpRequest' },
                body    : new FormData(form),
            });
            const data = await r.json();

            if (!data.success) {
                flashBannerIns('danger', data.error ?? 'Errore durante il salvataggio.');
            } else {
                // Aggiorna il nome nell'header della pagina senza reload
                const h4 = document.querySelector('.card-body h4');
                if (h4) h4.textContent = data.nome_cognome;

                // Aggiorna i badge materie
                const materieTxt = form.querySelector('input[name="materie"]').value.trim();
                const badgesWrap  = document.querySelector('.d-flex.flex-wrap.gap-1');
                if (badgesWrap) {
                    badgesWrap.innerHTML = materieTxt
                        ? materieTxt.split(',').map(m =>
                            `<span class="badge-materia">${m.trim().replace(/</g,'&lt;')}</span>`
                          ).join('')
                        : '';
                }

                flashBannerIns('success', 'Dati aggiornati con successo.');
            }
        } catch {
            flashBannerIns('danger', 'Errore di rete. Riprova.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = labelOrig;
        }
    });
});
</script>
