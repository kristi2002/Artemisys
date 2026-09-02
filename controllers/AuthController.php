<?php
require_once BASE_PATH . 'models/User.php';

class AuthController {

    public function login() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = 'Inserisci username e password.';
            } else {
                $userModel = new User();
                $user = $userModel->findByUsername($username);

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id']       = $user['id'];
                    $_SESSION['user_nome']     = $user['nome'] . ' ' . $user['cognome'];
                    $_SESSION['user_username'] = $user['username'];
                    $_SESSION['user_ruolo']    = $user['ruolo']; // admin, docente, studente
                    header('Location: ' . BASE_URL . 'switch');
                    exit;
                } else {
                    $error = 'Credenziali non valide.';
                }
            }
        }

        require BASE_PATH . 'views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }

    // ── Passo 1: richiesta del link di recupero ──────────────────────────────
    public function passwordDimenticata(): void {
        $error   = '';
        $success = '';
        $csrf    = $this->csrfToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!hash_equals($_SESSION['csrf_reset'] ?? '', $_POST['csrf'] ?? '')) {
                $error = 'Sessione scaduta, riprova.';
            } else {
                $email = trim($_POST['email'] ?? '');

                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Inserisci un indirizzo email valido.';
                } else {
                    $this->inviaLinkRecupero($email);

                    // Risposta identica sia che l'email esista sia che non
                    // esista: altrimenti il form diventa un modo per scoprire
                    // quali indirizzi sono registrati.
                    $success = 'Se l\'indirizzo è registrato, riceverai a breve '
                             . 'un\'email con le istruzioni per reimpostare la password. '
                             . 'Controlla anche la cartella spam.';
                }
            }
        }

        require BASE_PATH . 'views/auth/password_dimenticata.php';
    }

    /**
     * Genera il token e spedisce il messaggio.
     * Non comunica nulla al chiamante di proposito: qualunque esito, l'utente
     * vede sempre lo stesso messaggio.
     */
    private function inviaLinkRecupero(string $email): void {
        require_once BASE_PATH . 'models/PasswordReset.php';
        require_once BASE_PATH . 'services/Mailer.php';

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            // Indirizzo sconosciuto: nessuna email, nessuna traccia visibile.
            return;
        }

        $resetModel = new PasswordReset();

        if ($resetModel->troppiTentativi((int)$user['id'])) {
            error_log('[Auth] troppe richieste di recupero per user_id=' . $user['id']);
            return;
        }

        $token = $resetModel->crea((int)$user['id'], $_SERVER['REMOTE_ADDR'] ?? null);
        $link  = Mailer::appUrl(ltrim(BASE_URL, '/') . 'auth/reimposta-password?token=' . $token);

        $nome = htmlspecialchars(trim($user['nome'] . ' ' . $user['cognome']), ENT_QUOTES, 'UTF-8');

        $corpo = '<p>Ciao ' . $nome . ',</p>'
               . '<p>Abbiamo ricevuto una richiesta di reimpostazione della password '
               . 'per il tuo account Artemisys (<strong>' . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . '</strong>).</p>'
               . '<p>Per scegliere una nuova password clicca sul pulsante qui sotto. '
               . 'Il link resta valido <strong>60 minuti</strong> e può essere usato una sola volta.</p>'
               . Mailer::bottone('Reimposta la password', $link)
               . '<p style="font-size:13px;color:#7b8794;">Se il pulsante non funziona, copia questo indirizzo nel browser:<br>'
               . '<span style="word-break:break-all;">' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</span></p>'
               . '<p style="font-size:13px;color:#7b8794;">Se non hai richiesto tu il cambio password puoi ignorare '
               . 'questo messaggio: la password attuale resta valida.</p>';

        $mailer = Mailer::getInstance();
        $ok = $mailer->send(
            $user['email'],
            'Reimposta la tua password Artemisys',
            Mailer::layout('Recupero password', $corpo),
            trim($user['nome'] . ' ' . $user['cognome'])
        );

        if (!$ok) {
            // L'utente vede comunque il messaggio generico: l'errore va nei log,
            // altrimenti resterebbe invisibile a tutti.
            error_log('[Auth] invio email di recupero fallito per user_id=' . $user['id']
                    . ' — ' . $mailer->lastError());
        }
    }

    // ── Passo 2: scelta della nuova password ─────────────────────────────────
    public function reimpostaPassword(): void {
        require_once BASE_PATH . 'models/PasswordReset.php';

        $error   = '';
        $success = '';
        $csrf    = $this->csrfToken();

        $token = $_SERVER['REQUEST_METHOD'] === 'POST'
            ? trim($_POST['token'] ?? '')
            : trim($_GET['token']  ?? '');

        $resetModel = new PasswordReset();
        $riga = $token !== '' ? $resetModel->trovaValido($token) : null;

        if (!$riga) {
            $error   = 'Il link non è valido o è scaduto. Richiedine uno nuovo.';
            $tokenOk = false;
            require BASE_PATH . 'views/auth/reimposta_password.php';
            return;
        }

        $tokenOk = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!hash_equals($_SESSION['csrf_reset'] ?? '', $_POST['csrf'] ?? '')) {
                $error = 'Sessione scaduta, riprova.';
            } else {
                $nuova    = trim($_POST['nuova_password']    ?? '');
                $conferma = trim($_POST['conferma_password'] ?? '');

                $errors = [];
                if (mb_strlen($nuova) < 8) {
                    $errors[] = 'La password deve avere almeno 8 caratteri.';
                }
                if ($nuova !== $conferma) {
                    $errors[] = 'Le due password non coincidono.';
                }

                if (!empty($errors)) {
                    $error = implode('<br>', $errors);
                } else {
                    $userModel = new User();
                    $userModel->updatePassword((int)$riga['user_id'], $nuova);
                    $resetModel->segnaUsato((int)$riga['id']);

                    // Chi arriva qui potrebbe avere una sessione vecchia aperta:
                    // la si azzera, così si riparte dal login con la nuova password.
                    $_SESSION = [];
                    session_regenerate_id(true);

                    $success = 'Password aggiornata. Ora puoi accedere con le nuove credenziali.';
                    $tokenOk = false; // nasconde il form: non serve più
                }
            }
        }

        require BASE_PATH . 'views/auth/reimposta_password.php';
    }

    // ── CSRF (stesso schema usato da StudenteController) ─────────────────────
    private function csrfToken(): string {
        if (empty($_SESSION['csrf_reset'])) {
            $_SESSION['csrf_reset'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_reset'];
    }
}
