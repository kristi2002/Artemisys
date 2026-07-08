<?php

require_once BASE_PATH . 'models/EsameDiStato.php';
require_once BASE_PATH . 'models/Percorso.php';

class TuttiGliEsamiDiStatoController {

    private EsameDiStato $model;
    private Percorso $percorsoModel;

    public function __construct() {
        $this->model         = new EsameDiStato();
        $this->percorsoModel = new Percorso();
        $this->model->createTables();
        $this->percorsoModel->createTables();
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Esame di stato eliminato.';
        }
        header('Location: ' . BASE_URL . 'tutti-gli-esami-di-stato');
        exit;
    }

    public function index(): void {
        $page      = 'tutti-gli-esami-di-stato';
        $pageTitle = 'Tutti gli Esami di Stato';
        $search    = trim($_GET['q'] ?? '');
        $esami     = $this->model->getAll($search);
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/tutti-gli-esami-di-stato/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }
}
