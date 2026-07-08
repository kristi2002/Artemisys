<?php

require_once BASE_PATH . 'models/AnnoScolastico.php';

class AnnoScolasticoController {

    private AnnoScolastico $model;

    public function __construct() {
        $this->model = new AnnoScolastico();
        $this->model->createTable();
    }

    public function index(): void {
        $page      = 'anno-scolastico';
        $pageTitle = 'Anno Scolastico';
        $anni      = $this->model->getAll();
        $annoAttivo = $this->model->getAttivo();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/anno-scolastico/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'anno-scolastico');
            exit;
        }

        $anno = trim($_POST['anno'] ?? '');

        if (!preg_match('/^\d{4}\/\d{4}$/', $anno)) {
            $_SESSION['flash_error'] = 'Formato non valido. Usa il formato AAAA/AAAA (es. 2025/2026).';
            header('Location: ' . BASE_URL . 'anno-scolastico');
            exit;
        }

        [$y1, $y2] = explode('/', $anno);
        if ((int)$y2 !== (int)$y1 + 1) {
            $_SESSION['flash_error'] = 'Il secondo anno deve essere il successivo al primo (es. 2025/2026).';
            header('Location: ' . BASE_URL . 'anno-scolastico');
            exit;
        }

        if ($this->model->exists($anno)) {
            $_SESSION['flash_error'] = "L'anno scolastico {$anno} esiste già.";
            header('Location: ' . BASE_URL . 'anno-scolastico');
            exit;
        }

        $this->model->create($anno);
        $_SESSION['flash_success'] = "Anno scolastico {$anno} aggiunto con successo.";
        header('Location: ' . BASE_URL . 'anno-scolastico');
        exit;
    }

    public function setAttivo(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->setAttivo($id);
            $_SESSION['flash_success'] = 'Anno scolastico attivo aggiornato.';
        }
        header('Location: ' . BASE_URL . 'anno-scolastico');
        exit;
    }

    public function disattiva(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->disattiva($id);
            $_SESSION['flash_success'] = 'Anno scolastico disattivato.';
        }
        header('Location: ' . BASE_URL . 'anno-scolastico');
        exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Anno scolastico eliminato.';
        }
        header('Location: ' . BASE_URL . 'anno-scolastico');
        exit;
    }
}
