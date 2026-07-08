<?php
require_once BASE_PATH . 'models/Insegnante.php';

class InsegnantiController {

    private Insegnante $model;
    private string $uploadDir;

    public function __construct() {
        $this->model     = new Insegnante();
        $this->uploadDir = BASE_PATH . 'uploads/insegnanti/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    // ── Lista ───────────────────────────────────────────────────────────────
    public function index(): void {
        $search      = trim($_GET['q'] ?? '');
        $insegnanti  = $this->model->getAll($search);
        $totale      = $this->model->count();
        $page        = 'insegnanti';
        $pageTitle   = 'Insegnanti';

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/insegnanti/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── Form creazione ───────────────────────────────────────────────────────
    public function create(): void {
        $error     = $_SESSION['flash_error'] ?? '';
        $old       = $_SESSION['flash_old']   ?? [];
        unset($_SESSION['flash_error'], $_SESSION['flash_old']);

        // Suggerisci username e password per pre-popolare i campi
        $suggestedPassword = $this->model->generatePassword();

        $page      = 'insegnanti';
        $pageTitle = 'Nuovo Insegnante';

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/insegnanti/create.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── Salva nuovo insegnante ───────────────────────────────────────────────
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'insegnanti/create');
            exit;
        }

        $nome     = trim($_POST['nome']     ?? '');
        $cognome  = trim($_POST['cognome']  ?? '');
        $email    = trim($_POST['email']    ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validazione
        $errors = [];
        if (empty($nome))     $errors[] = 'Il nome è obbligatorio.';
        if (empty($cognome))  $errors[] = 'Il cognome è obbligatorio.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Email non valida.';
        if (empty($username)) $errors[] = 'Lo username è obbligatorio.';
        if (strlen($password) < 8) $errors[] = 'La password deve avere almeno 8 caratteri.';

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            $_SESSION['flash_old']   = $_POST;
            header('Location: ' . BASE_URL . 'insegnanti/create');
            exit;
        }

        $data = [
            'nome'           => $nome,
            'cognome'        => $cognome,
            'email'          => $email,
            'username'       => $username,
            'telefono'       => trim($_POST['telefono']       ?? ''),
            'codice_fiscale' => strtoupper(trim($_POST['codice_fiscale'] ?? '')),
            'data_nascita'   => $_POST['data_nascita'] ?? '',
            'indirizzo'      => trim($_POST['indirizzo'] ?? ''),
            'materie'        => trim($_POST['materie']   ?? ''),
            'note'           => trim($_POST['note']      ?? ''),
        ];

        try {
            $id = $this->model->create($data, $password);

            // Upload foto (opzionale)
            if (!empty($_FILES['foto']['tmp_name'])) {
                $fotoPath = $this->handleFotoUpload($_FILES['foto'], $id);
                if ($fotoPath) {
                    $this->model->updateFoto($id, $fotoPath);
                }
            }

            // Salvo le credenziali in sessione per mostrarle nella pagina di dettaglio
            $_SESSION['new_credentials'] = [
                'username' => $username,
                'password' => $password,
                'nome'     => $nome . ' ' . $cognome,
            ];

            header('Location: ' . BASE_URL . 'insegnanti/detail/' . $id);
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Errore durante la creazione: ' . $e->getMessage();
            $_SESSION['flash_old']   = $_POST;
            header('Location: ' . BASE_URL . 'insegnanti/create');
            exit;
        }
    }

    // ── Dettaglio / Modifica ─────────────────────────────────────────────────
    public function detail(string $id): void {
        $insegnante = $this->model->findById((int)$id);
        if (!$insegnante) {
            header('Location: ' . BASE_URL . 'insegnanti');
            exit;
        }

        $newCredentials = $_SESSION['new_credentials'] ?? null;
        $successMsg     = $_SESSION['flash_success']   ?? '';
        $errorMsg       = $_SESSION['flash_error']     ?? '';
        unset($_SESSION['new_credentials'], $_SESSION['flash_success'], $_SESSION['flash_error']);

        $page      = 'insegnanti';
        $pageTitle = $insegnante['nome'] . ' ' . $insegnante['cognome'];

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/insegnanti/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── Aggiorna insegnante ──────────────────────────────────────────────────
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'insegnanti');
            exit;
        }

        $id      = (int)($_POST['id'] ?? 0);
        $section = $_POST['section'] ?? 'dati';
        $isAjax  = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if (!$id) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'error' => 'ID mancante.']); exit; }
            header('Location: ' . BASE_URL . 'insegnanti'); exit;
        }

        try {
            if ($section === 'password') {
                $newPwd = trim($_POST['new_password'] ?? '');
                if (strlen($newPwd) < 8) {
                    $_SESSION['flash_error'] = 'La password deve avere almeno 8 caratteri.';
                } else {
                    $this->model->changePassword($id, $newPwd);
                    $_SESSION['flash_success'] = 'Password aggiornata con successo.';
                }
            } elseif ($section === 'foto') {
                if (!empty($_FILES['foto']['tmp_name'])) {
                    $fotoPath = $this->handleFotoUpload($_FILES['foto'], $id);
                    if ($fotoPath) {
                        $this->model->updateFoto($id, $fotoPath);
                        $_SESSION['flash_success'] = 'Foto aggiornata con successo.';
                    } else {
                        $_SESSION['flash_error'] = 'Errore nel caricamento della foto.';
                    }
                }
            } else {
                // Dati anagrafici
                $data = [
                    'nome'           => trim($_POST['nome']           ?? ''),
                    'cognome'        => trim($_POST['cognome']         ?? ''),
                    'email'          => trim($_POST['email']           ?? ''),
                    'username'       => trim($_POST['username']        ?? ''),
                    'telefono'       => trim($_POST['telefono']        ?? ''),
                    'codice_fiscale' => strtoupper(trim($_POST['codice_fiscale'] ?? '')),
                    'data_nascita'   => $_POST['data_nascita']         ?? '',
                    'indirizzo'      => trim($_POST['indirizzo']       ?? ''),
                    'materie'        => trim($_POST['materie']         ?? ''),
                    'note'           => trim($_POST['note']            ?? ''),
                ];

                if (empty($data['nome']) || empty($data['cognome']) || empty($data['email'])) {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Nome, cognome ed email sono obbligatori.']);
                        exit;
                    }
                    $_SESSION['flash_error'] = 'Nome, cognome ed email sono obbligatori.';
                } else {
                    $this->model->update($id, $data);
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success'      => true,
                            'nome'         => $data['nome'],
                            'cognome'      => $data['cognome'],
                            'nome_cognome' => $data['nome'] . ' ' . $data['cognome'],
                        ]);
                        exit;
                    }
                    $_SESSION['flash_success'] = 'Dati aggiornati con successo.';
                }
            }
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            $_SESSION['flash_error'] = 'Errore: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'insegnanti/detail/' . $id);
        exit;
    }

    // ── Toggle stato ─────────────────────────────────────────────────────────
    public function toggleStatus(): void {
        $id     = (int)($_POST['id'] ?? 0);
        $nuovo  = $this->model->toggleStatus($id);
        $label  = $nuovo ? 'attivato' : 'disattivato';
        $_SESSION['flash_success'] = "Insegnante {$label} con successo.";
        header('Location: ' . BASE_URL . 'insegnanti/detail/' . $id);
        exit;
    }

    // ── Upload foto helper ───────────────────────────────────────────────────
    private function handleFotoUpload(array $file, int $id): ?string {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 3 * 1024 * 1024; // 3 MB

        if (!in_array($file['type'], $allowed)) return null;
        if ($file['size'] > $maxSize)           return null;
        if ($file['error'] !== UPLOAD_ERR_OK)   return null;

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'ins_' . $id . '_' . time() . '.' . strtolower($ext);
        $dest     = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/insegnanti/' . $filename;
        }
        return null;
    }
}
