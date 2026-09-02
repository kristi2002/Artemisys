<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Artemisys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>public/css/style.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Artemisys</h3>
                <p class="subtitle">Piattaforma Gestione Scolastica</p>
            </div>
            <div class="login-form">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>auth/login">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i> Username
                        </label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username"
                               placeholder="Inserisci username" required autofocus autocomplete="username">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i> Password
                        </label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password"
                               placeholder="Inserisci password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Accedi
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>auth/password-dimenticata" class="small text-decoration-none">
                        <i class="fas fa-key me-1"></i>Password dimenticata?
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
