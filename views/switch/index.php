<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleziona Modulo - Artemisys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>public/css/style.css" rel="stylesheet">
</head>
<body class="switch-body">

    <!-- Topbar -->
    <div class="switch-topbar">
        <div class="brand">
            <i class="fas fa-graduation-cap"></i>
            Artemisys
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">
                <i class="fas fa-user me-1"></i>
                <?= htmlspecialchars($_SESSION['user_nome'] ?? '') ?>
                <span class="badge ms-1" style="font-size:0.7rem;background:#e8eef8;color:#1e40af;text-transform:capitalize;">
                    <?= htmlspecialchars($_SESSION['user_ruolo'] ?? '') ?>
                </span>
            </span>
            <a href="<?= BASE_URL ?>auth/logout" class="btn btn-sm" style="border:1px solid #c7d7f8;color:#1e40af;">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>

    <div class="switch-container">
        <div class="switch-header">
            <h2>Seleziona il Modulo</h2>
            <p>Scegli l'area della piattaforma a cui vuoi accedere</p>
        </div>

        <div class="row g-4 justify-content-center">

            <!-- DIDATTICA -->
            <div class="col-md-5">
                <a href="<?= BASE_URL ?>home" class="module-card">
                    <div class="module-icon didattica">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>Didattica</h3>
                    <p>Gestione insegnanti, studenti, lezioni, presenze e importazione dati scolastici.</p>
                    <div class="module-features">
                        <span><i class="fas fa-check me-1"></i>Insegnanti</span>
                        <span><i class="fas fa-check me-1"></i>Studenti</span>
                        <span><i class="fas fa-check me-1"></i>Lezioni</span>
                        <span><i class="fas fa-check me-1"></i>Presenze</span>
                    </div>
                    <div class="module-enter">
                        Accedi <i class="fas fa-arrow-right ms-2"></i>
                    </div>
                </a>
            </div>

            <!-- ORIENTAMENTO -->
            <div class="col-md-5">
                <div class="module-card disabled-card">
                    <div class="module-icon orientamento">
                        <i class="fas fa-compass"></i>
                    </div>
                    <h3>Orientamento</h3>
                    <p>Strumenti di orientamento scolastico e professionale per studenti e famiglie.</p>
                    <div class="module-features">
                        <span><i class="fas fa-clock me-1"></i>Percorsi</span>
                        <span><i class="fas fa-clock me-1"></i>Colloqui</span>
                        <span><i class="fas fa-clock me-1"></i>Report</span>
                    </div>
                    <div>
                        <span class="coming-soon-badge">In arrivo</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
