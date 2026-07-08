<?php

require_once BASE_PATH . 'models/Lezione.php';
require_once BASE_PATH . 'models/Materia.php';
require_once BASE_PATH . 'models/Percorso.php';

class LezioniController {

    private Lezione $model;
    private Materia $materiaModel;
    private Percorso $percorsoModel;

    public function __construct() {
        $this->model         = new Lezione();
        $this->materiaModel  = new Materia();
        $this->percorsoModel = new Percorso();
        $this->model->createTables();
    }

    public function index(): void {
        $page      = 'lezioni';
        $pageTitle = 'Lezioni';
        $search    = trim($_GET['q'] ?? '');
        $filters   = [
            'data_da'     => trim($_GET['data_da']     ?? ''),
            'data_a'      => trim($_GET['data_a']      ?? ''),
            'materia_id'  => trim($_GET['materia_id']  ?? ''),
            'percorso_id' => trim($_GET['percorso_id'] ?? ''),
            'online'      => $_GET['online'] ?? '',
        ];
        $lezioni   = $this->model->getAll($search, $filters);
        $materie   = $this->materiaModel->getAll();
        $percorsi  = $this->percorsoModel->getAll();

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/lezioni/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }
}
