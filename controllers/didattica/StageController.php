<?php

require_once BASE_PATH . 'models/Stage.php';

class StageController {

    private Stage $model;

    public function __construct() {
        $this->model = new Stage();
        $this->model->createTables();
    }

    public function index(): void {
        $page      = 'stage';
        $pageTitle = 'Stage';
        $search    = trim($_GET['q'] ?? '');
        $stages    = $this->model->getAll($search);
        $studenti  = $this->model->getStudentiDisponibili();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/stage/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'stage'); exit;
        }

        $studenteId = (int)($_POST['studente_id'] ?? 0);
        $azienda    = trim($_POST['azienda'] ?? '');

        if (!$studenteId || $azienda === '') {
            $_SESSION['flash_error'] = 'Studente e azienda sono obbligatori.';
            header('Location: ' . BASE_URL . 'stage'); exit;
        }

        $id = $this->model->create([
            'studente_id'      => $studenteId,
            'azienda'          => $azienda,
            'citta'            => $_POST['citta']            ?? '',
            'settore'          => $_POST['settore']          ?? '',
            'note_candidatura' => $_POST['note_candidatura'] ?? '',
        ]);

        $_SESSION['flash_success'] = 'Stage creato.';
        header('Location: ' . BASE_URL . 'stage/detail/' . $id);
        exit;
    }

    public function detail(int $id): void {
        $page   = 'stage';
        $stage  = $this->model->findById($id);
        if (!$stage) {
            header('Location: ' . BASE_URL . 'stage'); exit;
        }
        $pageTitle = 'Stage — ' . $stage['cognome'] . ' ' . $stage['nome'];
        $allegati  = $this->model->getAllegati($id);
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/stage/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function update(): void {
        $id   = (int)($_POST['id']   ?? 0);
        $step = (int)($_POST['step'] ?? 1);

        if ($id > 0) {
            $this->model->updateStep($id, $step, $_POST);
            $_SESSION['flash_success'] = 'Dati aggiornati.';
        }
        header('Location: ' . BASE_URL . 'stage/detail/' . $id); exit;
    }

    public function nextStep(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->model->nextStep($id);
        header('Location: ' . BASE_URL . 'stage/detail/' . $id); exit;
    }

    public function prevStep(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->model->prevStep($id);
        header('Location: ' . BASE_URL . 'stage/detail/' . $id); exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Stage eliminato.';
        }
        header('Location: ' . BASE_URL . 'stage'); exit;
    }

    public function uploadAllegato(): void {
        $stageId   = (int)($_POST['stage_id']  ?? 0);
        $categoria = trim($_POST['categoria']  ?? 'convenzione');
        if ($stageId <= 0) {
            header('Location: ' . BASE_URL . 'stage'); exit;
        }

        $allowedCat = ['convenzione','candidatura','valutazione','altro'];
        if (!in_array($categoria, $allowedCat, true)) $categoria = 'altro';

        $uploadDir = BASE_PATH . 'public/uploads/stage/';
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
            header('Location: ' . BASE_URL . 'stage/detail/' . $stageId); exit;
        }

        $mime         = mime_content_type($_FILES['allegato']['tmp_name']);
        $originalName = basename($_FILES['allegato']['name']);
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($mime, $allowed) || !in_array($ext, ['pdf','doc','docx','jpg','jpeg','png'])) {
            $_SESSION['flash_error'] = 'Formato non consentito. Usa PDF, DOC, DOCX, JPG o PNG.';
            header('Location: ' . BASE_URL . 'stage/detail/' . $stageId); exit;
        }

        if ($_FILES['allegato']['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'File troppo grande (max 10 MB).';
            header('Location: ' . BASE_URL . 'stage/detail/' . $stageId); exit;
        }

        $filename = uniqid('stage_', true) . '.' . $ext;
        move_uploaded_file($_FILES['allegato']['tmp_name'], $uploadDir . $filename);
        $this->model->addAllegato($stageId, $categoria, $filename, $originalName, $mime);

        $_SESSION['flash_success'] = 'Documento caricato.';
        header('Location: ' . BASE_URL . 'stage/detail/' . $stageId); exit;
    }

    public function deleteAllegato(): void {
        $id      = (int)($_POST['allegato_id'] ?? 0);
        $stageId = (int)($_POST['stage_id']    ?? 0);
        if ($id > 0) {
            $allegato = $this->model->findAllegato($id);
            if ($allegato) {
                $path = BASE_PATH . 'public/uploads/stage/' . $allegato['filename'];
                if (file_exists($path)) unlink($path);
                $this->model->deleteAllegato($id);
                $_SESSION['flash_success'] = 'Documento eliminato.';
            }
        }
        header('Location: ' . BASE_URL . 'stage/detail/' . $stageId); exit;
    }
}
