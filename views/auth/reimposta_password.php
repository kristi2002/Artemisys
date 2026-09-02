<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Il token sta nell'URL: senza questo, il browser lo passerebbe ai CDN
         esterni nell'header Referer. -->
    <meta name="referrer" content="no-referrer">
    <title>Reimposta password - Artemisys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>public/css/style.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-lock-open"></i>
                </div>
                <h3>Nuova password</h3>
                <p class="subtitle">Scegli una password per il tuo account</p>
            </div>
            <div class="login-form">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tokenOk)): ?>
                    <form method="POST" action="<?= BASE_URL ?>auth/reimposta-password">
                        <input type="hidden" name="csrf"  value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="mb-3">
                            <label for="nuova_password" class="form-label">
                                <i class="fas fa-lock me-1"></i> Nuova password
                            </label>
                            <input type="password" class="form-control form-control-lg"
                                   id="nuova_password" name="nuova_password"
                                   placeholder="Almeno 8 caratteri" required autofocus
                                   minlength="8" autocomplete="new-password">
                        </div>

                        <div class="mb-4">
                            <label for="conferma_password" class="form-label">
                                <i class="fas fa-lock me-1"></i> Conferma password
                            </label>
                            <input type="password" class="form-control form-control-lg"
                                   id="conferma_password" name="conferma_password"
                                   placeholder="Ripeti la password" required
                                   minlength="8" autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-floppy-disk me-2"></i>Salva la password
                        </button>
                    </form>
                <?php else: ?>
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>auth/login" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Vai al login
                        </a>
                        <?php if (empty($success)): ?>
                            <a href="<?= BASE_URL ?>auth/password-dimenticata" class="btn btn-outline-secondary">
                                <i class="fas fa-rotate-right me-2"></i>Richiedi un nuovo link
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
