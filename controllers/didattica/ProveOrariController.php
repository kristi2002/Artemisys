<?php

require_once BASE_PATH . 'models/EsameDiStato.php';

class ProveOrariController {

    private EsameDiStato $model;

    public function __construct() {
        $this->model = new EsameDiStato();
        $this->model->createTables();
    }

    public function index(): void {
        $pageTitle = 'Orari Prove Esami di Stato';
        $page      = 'prove-orari';

        $db   = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT p.id, p.esame_di_stato_id, p.data, p.ora_inizio, p.ora_fine,
                   p.tipo_prova, p.ordine,
                   e.denominazione AS esame_nome
            FROM esame_di_stato_prove p
            JOIN esami_di_stato e ON e.id = p.esame_di_stato_id
            ORDER BY p.data ASC, p.ora_inizio ASC
        ");
        $prove = $stmt->fetchAll();

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/prove-orari/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }
}
