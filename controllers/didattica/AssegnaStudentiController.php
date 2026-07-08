<?php

require_once BASE_PATH . 'models/Percorso.php';
require_once BASE_PATH . 'models/AnnoScolastico.php';
require_once BASE_PATH . 'models/Studente.php';

class AssegnaStudentiController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index(): void {
        $page      = 'assegna-studenti';
        $pageTitle = 'Assegna Studenti a Percorso';

        $percorsi = $this->db->query("
            SELECT p.id, p.nome, a.anno AS anno_label, p.anno_scolastico_id
            FROM percorsi_accademici p
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            ORDER BY a.anno DESC, p.nome ASC
        ")->fetchAll();

        $percorsoAnni = $this->db->query("
            SELECT pa.id, pa.numero, pa.percorso_id
            FROM percorso_anni pa
            ORDER BY pa.percorso_id ASC, pa.numero ASC
        ")->fetchAll();

        $studentiRaw = $this->db->query("
            SELECT s.id, s.cognome, s.nome
            FROM studenti s
            WHERE s.attivo = 1
            ORDER BY s.cognome ASC, s.nome ASC
        ")->fetchAll();

        // Arricchisce con iscrizioni multiple
        $iscRaw = $this->db->query("
            SELECT si.studente_id, p.nome AS percorso_nome, a.anno AS anno_label
            FROM studente_iscrizioni si
            JOIN percorsi_accademici p ON p.id = si.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
        ")->fetchAll();
        $iscMap = [];
        foreach ($iscRaw as $row) {
            $iscMap[(int)$row['studente_id']][] = $row;
        }
        $studenti = [];
        foreach ($studentiRaw as $s) {
            $s['iscrizioni'] = $iscMap[(int)$s['id']] ?? [];
            $studenti[] = $s;
        }

        $success = $_SESSION['flash_success'] ?? null;
        $error   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/assegna_studenti.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'assegna-studenti');
            exit;
        }

        $percorsoId     = (int)($_POST['percorso_id']      ?? 0);
        $percorsoAnnoId = (int)($_POST['percorso_anno_id'] ?? 0);
        $annoScId       = (int)($_POST['anno_scolastico_id'] ?? 0);
        $selezionati    = array_map('intval', $_POST['studenti_ids'] ?? []);

        if (!$percorsoId || !$percorsoAnnoId || !$annoScId) {
            $_SESSION['flash_error'] = 'Seleziona percorso e anno di corso.';
            header('Location: ' . BASE_URL . 'assegna-studenti');
            exit;
        }

        $stmtIsc = $this->db->prepare("
            INSERT IGNORE INTO studente_iscrizioni (studente_id, percorso_id)
            VALUES (?, ?)
        ");
        $stmtAnno = $this->db->prepare("
            INSERT INTO studente_anni (studente_id, percorso_anno_id, anno_scolastico_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE percorso_anno_id = VALUES(percorso_anno_id)
        ");

        $ok = 0;
        foreach ($selezionati as $studenteId) {
            $stmtIsc->execute([$studenteId, $percorsoId]);
            $stmtAnno->execute([$studenteId, $percorsoAnnoId, $annoScId]);
            $ok++;
        }

        $_SESSION['flash_success'] = "{$ok} " . ($ok === 1 ? 'studente assegnato' : 'studenti assegnati') . '.';
        header('Location: ' . BASE_URL . 'assegna-studenti');
        exit;
    }
}
