<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password dimenticata - Artemisys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>public/css/style.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-key"></i>
                </div>
                <h3>Password dimenticata</h3>
                <p class="subtitle">Ti inviamo un link per reimpostarla</p>
            </div>
            <div class="login-form">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-envelope-circle-check me-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                    <a href="<?= BASE_URL ?>auth/login" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-arrow-left me-2"></i>Torna al login
                    </a>
                <?php else: ?>
                    <p class="text-muted small mb-3">
                        Inserisci l'indirizzo email associato al tuo account.
                        Riceverai un link valido 60 minuti.
                    </p>

                    <form method="POST" action="<?= BASE_URL ?>auth/password-dimenticata">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i> Email
                            </label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email"
                                   placeholder="nome@esempio.it" required autofocus autocomplete="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-paper-plane me-2"></i>Invia il link
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>auth/login" class="small text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Torna al login
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
