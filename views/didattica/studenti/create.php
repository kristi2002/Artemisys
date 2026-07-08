<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= BASE_URL ?>studenti" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Studenti
    </a>
    <span class="text-muted">/</span>
    <h4 class="mb-0 fw-bold" style="color:#0c1a3a;">Nuovo studente</h4>
</div>

<div class="col-lg-7">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>studenti/store">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control" required maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Cognome <span class="text-danger">*</span></label>
                        <input type="text" name="cognome" class="form-control" required maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" maxlength="150">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Telefono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Data di nascita</label>
                        <input type="date" name="data_nascita" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Codice fiscale</label>
                        <input type="text" name="codice_fiscale" class="form-control text-uppercase" maxlength="20">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Indirizzo</label>
                        <input type="text" name="indirizzo" class="form-control" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Note</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Crea studente
                    </button>
                    <a href="<?= BASE_URL ?>studenti" class="btn btn-outline-secondary">Annulla</a>
                </div>
            </form>
        </div>
    </div>
</div>
