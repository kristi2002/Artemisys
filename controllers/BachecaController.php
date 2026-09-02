<?php

require_once BASE_PATH . 'models/Comunicazione.php';

class BachecaController {

    private Comunicazione $model;
    private string $userRuolo;

    public function __construct() {
        $this->model = new Comunicazione();
        $this->model->createTables();
        $this->userRuolo = $_SESSION['user_ruolo'] ?? '';
    }

    private function loadStudente(): ?array {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) return null;
        $db = Database::getInstance()->getConnection();
        try { $db->exec("ALTER TABLE studenti ADD COLUMN user_id INT NULL"); } catch (Exception $e) {}
        $stmt = $db->prepare("SELECT * FROM studenti WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    private function targetForRole(): string {
        return match($this->userRuolo) {
            'docente'  => 'docenti',
            'studente' => 'studenti',
            default    => 'tutti', // admin vede tutto
        };
    }

    public function index(): void {
        $page      = 'bacheca';
        $pageTitle = 'Bacheca';
        $isAdmin   = $this->userRuolo === 'admin';
        $target    = $this->targetForRole();
        $comunicazioni = $this->model->getAll($target);
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        // Layout diverso per studente (mobile-first)
        if ($this->userRuolo === 'studente') {
            $studente = $this->loadStudente() ?? ['nome'=>'','cognome'=>''];
            require BASE_PATH . 'views/studente/_header.php';
            require BASE_PATH . 'views/studente/bacheca.php';
            require BASE_PATH . 'views/studente/_footer.php';
            return;
        }

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/bacheca/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($this->userRuolo !== 'admin') {
            header('Location: ' . BASE_URL . 'bacheca'); exit;
        }
        $titolo    = trim($_POST['titolo']    ?? '');
        $contenuto = trim($_POST['contenuto'] ?? '');
        if ($titolo === '' || $contenuto === '') {
            $_SESSION['flash_error'] = 'Titolo e contenuto sono obbligatori.';
            header('Location: ' . BASE_URL . 'bacheca'); exit;
        }
        $com = [
            'titolo'      => $titolo,
            'contenuto'   => $contenuto,
            'tipo'        => $_POST['tipo']   ?? 'generale',
            'target'      => $_POST['target'] ?? 'tutti',
            'autore_id'   => $_SESSION['user_id']   ?? null,
            'autore_nome' => $_SESSION['user_nome'] ?? null,
        ];
        $this->model->create($com);

        $messaggio = 'Comunicazione pubblicata.';

        // Notifica via email: solo se richiesta esplicitamente con la spunta.
        // La pubblicazione e' gia' andata a buon fine: un problema di invio
        // non deve far sembrare fallita l'operazione.
        if (!empty($_POST['notifica_email'])) {
            require_once BASE_PATH . 'services/Notifiche.php';
            $mailer = Mailer::getInstance();

            if (!$mailer->isEnabled()) {
                $messaggio .= ' Notifica email non inviata: invio non configurato.';
            } else {
                $inviate = (new Notifiche())->comunicazione($com);
                $messaggio .= $inviate > 0
                    ? " Notifica inviata a {$inviate} destinatari."
                    : ' Nessuna notifica inviata: controlla i log.';
            }
        }

        $_SESSION['flash_success'] = $messaggio;
        header('Location: ' . BASE_URL . 'bacheca'); exit;
    }

    public function detail(int $id): void {
        $page      = 'bacheca';
        $com       = $this->model->findById($id);
        if (!$com) { header('Location: ' . BASE_URL . 'bacheca'); exit; }
        $pageTitle = $com['titolo'];

        if ($this->userRuolo === 'studente') {
            $studente = $this->loadStudente() ?? ['nome'=>'','cognome'=>''];
            require BASE_PATH . 'views/studente/_header.php';
            require BASE_PATH . 'views/studente/bacheca_detail.php';
            require BASE_PATH . 'views/studente/_footer.php';
            return;
        }

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/bacheca/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function delete(): void {
        if ($this->userRuolo !== 'admin') {
            header('Location: ' . BASE_URL . 'bacheca'); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Comunicazione eliminata.';
        }
        header('Location: ' . BASE_URL . 'bacheca'); exit;
    }
}
