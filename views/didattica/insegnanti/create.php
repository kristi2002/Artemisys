<!-- Header pagina -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold" style="color:#0c1a3a;">
            <i class="fas fa-user-plus me-2" style="color:#1e40af;"></i>Nuovo Insegnante
        </h4>
        <a href="<?= BASE_URL ?>insegnanti" class="text-muted small text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Torna alla lista
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>insegnanti/store" enctype="multipart/form-data" id="form-create">
    <div class="row g-4">

        <!-- COLONNA SINISTRA: dati anagrafici + didattici -->
        <div class="col-lg-8">

            <!-- Dati Anagrafici -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-id-card me-2 text-primary"></i>Dati Anagrafici</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome"
                                   value="<?= htmlspecialchars($old['nome'] ?? '') ?>"
                                   placeholder="Es. Mario" required id="inp-nome">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cognome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="cognome"
                                   value="<?= htmlspecialchars($old['cognome'] ?? '') ?>"
                                   placeholder="Es. Rossi" required id="inp-cognome">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email"
                                   value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                                   placeholder="mario.rossi@scuola.it" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefono</label>
                            <input type="tel" class="form-control" name="telefono"
                                   value="<?= htmlspecialchars($old['telefono'] ?? '') ?>"
                                   placeholder="+39 333 0000000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Data di Nascita</label>
                            <input type="date" class="form-control" name="data_nascita"
                                   value="<?= htmlspecialchars($old['data_nascita'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Codice Fiscale</label>
                            <input type="text" class="form-control text-uppercase" name="codice_fiscale"
                                   value="<?= htmlspecialchars($old['codice_fiscale'] ?? '') ?>"
                                   placeholder="RSSMRA80A01H501Z" maxlength="16">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Indirizzo</label>
                            <input type="text" class="form-control" name="indirizzo"
                                   value="<?= htmlspecialchars($old['indirizzo'] ?? '') ?>"
                                   placeholder="Via, numero, città">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Didattiche -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-book-open me-2 text-primary"></i>Info Didattiche</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Materie Insegnate</label>
                            <input type="text" class="form-control" name="materie"
                                   value="<?= htmlspecialchars($old['materie'] ?? '') ?>"
                                   placeholder="Es. Matematica, Fisica, Informatica (separare con virgola)">
                            <small class="text-muted">Separare le materie con una virgola</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Note</label>
                            <textarea class="form-control" name="note" rows="3"
                                      placeholder="Note aggiuntive..."><?= htmlspecialchars($old['note'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credenziali di Accesso -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-key me-2 text-primary"></i>Credenziali di Accesso</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 mb-3" style="background:#eff6ff;">
                        <i class="fas fa-info-circle me-2" style="color:#1e40af;"></i>
                        <small>Lo username viene generato automaticamente dal nome e cognome. La password può essere modificata dall'insegnante al primo accesso.</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-at text-muted"></i></span>
                                <input type="text" class="form-control" name="username"
                                       value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                                       placeholder="generato automaticamente" required id="inp-username">
                            </div>
                            <small class="text-muted">Generato automaticamente da nome e cognome</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" name="password"
                                       value="<?= htmlspecialchars($old['password'] ?? $suggestedPassword) ?>"
                                       required minlength="8" id="inp-password">
                                <button type="button" class="btn btn-outline-secondary" id="btn-regen-pwd" title="Rigenera">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimo 8 caratteri — comunicare all'insegnante</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- COLONNA DESTRA: foto + anteprima + azioni -->
        <div class="col-lg-4">

            <!-- Upload Foto -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-camera me-2 text-primary"></i>Foto Profilo</h6>
                </div>
                <div class="card-body text-center">
                    <div class="foto-preview-wrap mb-3" id="foto-preview-wrap">
                        <div class="ins-avatar-initials ins-avatar-lg" id="avatar-initials">
                            <i class="fas fa-user"></i>
                        </div>
                        <img src="" alt="" class="ins-foto-preview d-none" id="foto-preview">
                    </div>
                    <label for="foto-input" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-upload me-2"></i>Carica Foto
                    </label>
                    <input type="file" id="foto-input" name="foto" accept="image/jpeg,image/png,image/webp" class="d-none">
                    <small class="text-muted d-block">JPG, PNG o WEBP · max 3 MB</small>
                </div>
            </div>

            <!-- Riepilogo / Azioni -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Crea Insegnante
                        </button>
                        <a href="<?= BASE_URL ?>insegnanti" class="btn btn-outline-secondary">
                            Annulla
                        </a>
                    </div>
                    <hr class="my-3">
                    <small class="text-muted">
                        <i class="fas fa-bell me-1"></i>
                        Dopo la creazione verranno mostrate le credenziali da comunicare all'insegnante.
                    </small>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
// Auto-genera username da nome + cognome
function generateUsername(nome, cognome) {
    function removeAccents(str) {
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }
    if (!nome || !cognome) return '';
    var u = removeAccents(nome.charAt(0)) + '.' + removeAccents(cognome);
    return u.replace(/[^a-z0-9.]/g, '');
}

var nomeInp     = document.getElementById('inp-nome');
var cognomeInp  = document.getElementById('inp-cognome');
var usernameInp = document.getElementById('inp-username');
var userManual  = false;

function updateUsername() {
    if (!userManual) {
        usernameInp.value = generateUsername(nomeInp.value.trim(), cognomeInp.value.trim());
    }
}
nomeInp.addEventListener('input', updateUsername);
cognomeInp.addEventListener('input', updateUsername);
usernameInp.addEventListener('input', function() { userManual = this.value.length > 0; });

// Aggiorna iniziali avatar
function updateInitials() {
    var n = nomeInp.value.trim();
    var c = cognomeInp.value.trim();
    var init = document.getElementById('avatar-initials');
    if (n || c) {
        init.textContent = ((n ? n[0] : '') + (c ? c[0] : '')).toUpperCase();
    } else {
        init.innerHTML = '<i class="fas fa-user"></i>';
    }
}
nomeInp.addEventListener('input', updateInitials);
cognomeInp.addEventListener('input', updateInitials);

// Preview foto
document.getElementById('foto-input').addEventListener('change', function() {
    if (!this.files || !this.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('avatar-initials').classList.add('d-none');
        var preview = document.getElementById('foto-preview');
        preview.src = e.target.result;
        preview.classList.remove('d-none');
    };
    reader.readAsDataURL(this.files[0]);
});

// Rigenera password
document.getElementById('btn-regen-pwd').addEventListener('click', function() {
    var chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#!';
    var pwd = '';
    for (var i = 0; i < 10; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('inp-password').value = pwd;
});

// Maiuscolo automatico codice fiscale
document.querySelector('input[name="codice_fiscale"]').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
