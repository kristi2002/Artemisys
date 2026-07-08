<?php

require_once BASE_PATH . 'models/Esame.php';

class EsamiController {

    private Esame $model;

    public function __construct() {
        $this->model = new Esame();
        $this->model->createTables();
    }

    public function index(): void {
        $page      = 'esami';
        $pageTitle = 'Esami';
        $search    = trim($_GET['q'] ?? '');
        $esami     = $this->model->getAll($search);
        $pamList   = $this->model->getPamList();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/esami/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'esami'); exit;
        }

        $pamId  = (int)($_POST['pam_id']  ?? 0);
        $titolo = trim($_POST['titolo']   ?? '');

        if (!$pamId || $titolo === '') {
            $_SESSION['flash_error'] = 'Materia e titolo sono obbligatori.';
            header('Location: ' . BASE_URL . 'esami'); exit;
        }

        $esameId = $this->model->create([
            'pam_id'     => $pamId,
            'titolo'     => $titolo,
            'data'       => $_POST['data']       ?? '',
            'ora_inizio' => $_POST['ora_inizio'] ?? '',
            'tipo'       => $_POST['tipo']       ?? 'scritto',
            'note'       => $_POST['note']       ?? '',
        ]);

        // Recupera anno_id dal pam per iscrivere automaticamente gli studenti
        $db   = Database::getInstance()->getConnection();
        $pam  = $db->prepare("SELECT anno_id FROM percorso_anno_materie WHERE id = ?");
        $pam->execute([$pamId]);
        $pam  = $pam->fetch();

        $n = 0;
        if ($pam) {
            $n = $this->model->iscriviClasseAuto($esameId, $pam['anno_id']);
        }

        // Auto-aggiungi il docente come supervisore se è loggato come docente
        if (($_SESSION['user_ruolo'] ?? '') === 'docente') {
            $userId = (int)$_SESSION['user_id'];
            $stmt = $db->prepare("SELECT id FROM insegnanti WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $insId = $stmt->fetchColumn();
            if ($insId) {
                $this->model->addSupervisore($esameId, (int)$insId);
            }
        }

        $_SESSION['flash_success'] = "Esame <strong>{$titolo}</strong> creato con {$n} studenti iscritti.";
        header('Location: ' . BASE_URL . 'esami/detail/' . $esameId);
        exit;
    }

    public function addSupervisore(): void {
        $esameId       = (int)($_POST['esame_id']      ?? 0);
        $insegnanteId  = (int)($_POST['insegnante_id'] ?? 0);
        if ($esameId && $insegnanteId) {
            $this->model->addSupervisore($esameId, $insegnanteId);
            $_SESSION['flash_success'] = 'Supervisore aggiunto.';
        }
        header('Location: ' . BASE_URL . 'esami/detail/' . $esameId); exit;
    }

    public function removeSupervisore(): void {
        $esameId       = (int)($_POST['esame_id']      ?? 0);
        $insegnanteId  = (int)($_POST['insegnante_id'] ?? 0);
        if ($esameId && $insegnanteId) {
            $this->model->removeSupervisore($esameId, $insegnanteId);
            $_SESSION['flash_success'] = 'Supervisore rimosso.';
        }
        header('Location: ' . BASE_URL . 'esami/detail/' . $esameId); exit;
    }

    public function detail(int $id): void {
        $page   = 'esami';
        $esame  = $this->model->findById($id);
        if (!$esame) {
            header('Location: ' . BASE_URL . 'esami'); exit;
        }
        $pageTitle    = $esame['titolo'];
        $iscrizioni   = $this->model->getIscrizioni($id);
        $supervisori  = $this->model->getSupervisori($id);
        $insMateria   = $this->model->getInsegnantiMateria((int)$esame['percorso_anno_materia_id']);
        $superIds     = array_column($supervisori, 'id');
        $insDisponibili = array_filter($insMateria, fn($i) => !in_array($i['id'], $superIds));
        $allegati     = $this->model->getAllegati($id);
        $success    = $_SESSION['flash_success'] ?? null;
        $error      = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/esami/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function saveVoti(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        $righe   = $_POST['voti'] ?? [];

        if ($esameId > 0 && !empty($righe)) {
            $this->model->saveVoti($righe);
            $_SESSION['flash_success'] = 'Voti salvati.';
        }
        header('Location: ' . BASE_URL . 'esami/detail/' . $esameId);
        exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Esame eliminato.';
        }
        header('Location: ' . BASE_URL . 'esami');
        exit;
    }

    public function uploadAllegato(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami');
            exit;
        }

        $uploadDir = BASE_PATH . 'public/uploads/esami/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowed = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 'image/png',
        ];

        if (empty($_FILES['allegato']['name'])) {
            $_SESSION['flash_error'] = 'Nessun file selezionato.';
            header('Location: ' . BASE_URL . 'esami/detail/' . $esameId);
            exit;
        }

        $mime         = mime_content_type($_FILES['allegato']['tmp_name']);
        $originalName = basename($_FILES['allegato']['name']);
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($mime, $allowed) || !in_array($ext, ['pdf','doc','docx','jpg','jpeg','png'])) {
            $_SESSION['flash_error'] = 'Formato non consentito. Usa PDF, DOC, DOCX, JPG o PNG.';
            header('Location: ' . BASE_URL . 'esami/detail/' . $esameId);
            exit;
        }

        if ($_FILES['allegato']['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'File troppo grande (max 10 MB).';
            header('Location: ' . BASE_URL . 'esami/detail/' . $esameId);
            exit;
        }

        $filename = uniqid('esame_', true) . '.' . $ext;
        move_uploaded_file($_FILES['allegato']['tmp_name'], $uploadDir . $filename);
        $this->model->addAllegato($esameId, $filename, $originalName, $mime);

        $_SESSION['flash_success'] = 'Documento caricato.';
        header('Location: ' . BASE_URL . 'esami/detail/' . $esameId);
        exit;
    }

    public function deleteAllegato(): void {
        $id      = (int)($_POST['allegato_id'] ?? 0);
        $esameId = (int)($_POST['esame_id']    ?? 0);
        if ($id > 0) {
            $allegato = $this->model->findAllegato($id);
            if ($allegato) {
                $path = BASE_PATH . 'public/uploads/esami/' . $allegato['filename'];
                if (file_exists($path)) unlink($path);
                $this->model->deleteAllegato($id);
                $_SESSION['flash_success'] = 'Documento eliminato.';
            }
        }
        header('Location: ' . BASE_URL . 'esami/detail/' . $esameId);
        exit;
    }
}
