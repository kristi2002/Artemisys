<?php

require_once BASE_PATH . 'models/Diploma.php';

class DiplomiController {

    private Diploma $model;
    private string $uploadDir;

    public function __construct() {
        $this->model = new Diploma();
        $this->model->createTables();
        $this->uploadDir = BASE_PATH . 'public/uploads/diplomi/';
        if (!is_dir($this->uploadDir)) @mkdir($this->uploadDir, 0775, true);
    }

    public function index(): void {
        $page      = 'diplomi';
        $pageTitle = 'Diplomi/Certificati';
        $diploma   = $this->model->get();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/diplomi/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function save(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'diplomi'); exit;
        }
        $this->model->update($_POST);
        $_SESSION['flash_success'] = 'Template salvato.';
        header('Location: ' . BASE_URL . 'diplomi');
        exit;
    }

    public function uploadImage(): void {
        $type   = $_POST['type'] ?? '';
        $field  = match($type) {
            'timbro' => 'timbro_path',
            'firma1' => 'firma1_path',
            'firma2' => 'firma2_path',
            default  => null,
        };

        if (!$field) {
            $_SESSION['flash_error'] = 'Tipo immagine non valido.';
            header('Location: ' . BASE_URL . 'diplomi'); exit;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Errore caricamento.';
            header('Location: ' . BASE_URL . 'diplomi'); exit;
        }

        $allowed = ['png', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            $_SESSION['flash_error'] = 'Solo PNG o JPG.';
            header('Location: ' . BASE_URL . 'diplomi'); exit;
        }

        // Rimuovi vecchio
        $current = $this->model->get();
        if (!empty($current[$field]) && file_exists($this->uploadDir . $current[$field])) {
            @unlink($this->uploadDir . $current[$field]);
        }

        $newName = $type . '_' . uniqid('', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $this->uploadDir . $newName)) {
            $this->model->setImage($field, $newName);
            $_SESSION['flash_success'] = ucfirst($type) . ' caricato.';
        } else {
            $_SESSION['flash_error'] = 'Errore salvataggio.';
        }

        header('Location: ' . BASE_URL . 'diplomi'); exit;
    }

    public function removeImage(): void {
        $type   = $_POST['type'] ?? '';
        $field  = match($type) {
            'timbro' => 'timbro_path',
            'firma1' => 'firma1_path',
            'firma2' => 'firma2_path',
            default  => null,
        };
        if (!$field) {
            header('Location: ' . BASE_URL . 'diplomi'); exit;
        }

        $current = $this->model->get();
        if (!empty($current[$field])) {
            $path = $this->uploadDir . $current[$field];
            if (file_exists($path)) @unlink($path);
            $this->model->setImage($field, null);
            $_SESSION['flash_success'] = 'Immagine rimossa.';
        }

        header('Location: ' . BASE_URL . 'diplomi'); exit;
    }
}
