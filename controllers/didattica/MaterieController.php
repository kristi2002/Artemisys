<?php

require_once BASE_PATH . 'models/Materia.php';

class MaterieController {

    private Materia $model;

    public function __construct() {
        $this->model = new Materia();
        $this->model->createTable();
    }

    public function index(): void {
        $page      = 'materie';
        $pageTitle = 'Materie';
        $search    = trim($_GET['q'] ?? '');
        $materie   = $this->model->getAll($search);
        $totale    = $this->model->count();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/materie/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'materie');
            exit;
        }

        $codice           = strtoupper(trim($_POST['codice'] ?? ''));
        $nome             = trim($_POST['nome'] ?? '');
        $codice_regionale = strtoupper(trim($_POST['codice_regionale'] ?? ''));

        if ($codice === '' || $nome === '') {
            $_SESSION['flash_error'] = 'Codice e nome sono obbligatori.';
            header('Location: ' . BASE_URL . 'materie');
            exit;
        }

        if ($this->model->codiceExists($codice)) {
            $_SESSION['flash_error'] = "Il codice <strong>{$codice}</strong> è già in uso.";
            header('Location: ' . BASE_URL . 'materie');
            exit;
        }

        $this->model->create([
            'codice'           => $codice,
            'nome'             => $nome,
            'codice_regionale' => $codice_regionale,
        ]);

        $_SESSION['flash_success'] = "Materia <strong>{$nome}</strong> aggiunta con successo.";
        header('Location: ' . BASE_URL . 'materie');
        exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Materia eliminata.';
        }
        header('Location: ' . BASE_URL . 'materie');
        exit;
    }
}
