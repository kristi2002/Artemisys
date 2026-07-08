<?php

require_once BASE_PATH . 'models/Sede.php';

class SediController {
    private Sede $model;

    public function __construct() {
        $this->model = new Sede();
    }

    public function index(): void {
        $pageTitle = 'Sedi';
        $page      = 'sedi';
        $sedi      = $this->model->getAll();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/sedi/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'sedi');
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') {
            $_SESSION['flash_error'] = 'Il nome della sede è obbligatorio.';
            header('Location: ' . BASE_URL . 'sedi');
            exit;
        }

        $this->model->create([
            'nome'      => $nome,
            'via'       => trim($_POST['via']       ?? ''),
            'comune'    => trim($_POST['comune']    ?? ''),
            'cap'       => trim($_POST['cap']       ?? ''),
            'provincia' => trim($_POST['provincia'] ?? ''),
            'telefono'  => trim($_POST['telefono']  ?? ''),
            'email'     => trim($_POST['email']     ?? ''),
            'note'      => trim($_POST['note']      ?? ''),
        ]);

        $_SESSION['flash_success'] = 'Sede creata con successo.';
        header('Location: ' . BASE_URL . 'sedi');
        exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Sede eliminata.';
        }
        header('Location: ' . BASE_URL . 'sedi');
        exit;
    }
}
