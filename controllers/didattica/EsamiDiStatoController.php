<?php

require_once BASE_PATH . 'models/EsameDiStato.php';
require_once BASE_PATH . 'models/Percorso.php';
require_once BASE_PATH . 'models/Insegnante.php';

class EsamiDiStatoController {

    private EsameDiStato $model;
    private Percorso $percorsoModel;
    private Insegnante $insegnanteModel;

    public function __construct() {
        $this->model           = new EsameDiStato();
        $this->percorsoModel   = new Percorso();
        $this->insegnanteModel = new Insegnante();
        $this->model->createTables();
        $this->percorsoModel->createTables();
    }

    public function index(): void {
        $page      = 'esami-di-stato';
        $pageTitle = 'Esami di Stato';
        $search    = trim($_GET['q'] ?? '');
        $esami     = $this->model->getAll($search);
        $percorsi  = $this->percorsoModel->getAll();
        $docenti   = $this->insegnanteModel->getAll();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/esami-di-stato/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'esami-di-stato'); exit;
        }

        $denominazione   = trim($_POST['denominazione']    ?? '');
        $riferimentoTipo = $_POST['riferimento_tipo']      ?? 'classe';
        $percorsoId      = (int)($_POST['percorso_id']     ?? 0);
        $percorsoAnnoId  = (int)($_POST['percorso_anno_id'] ?? 0);
        $rappresentante  = (int)($_POST['rappresentante_id'] ?? 0);

        if ($denominazione === '' || !$percorsoId) {
            $_SESSION['flash_error'] = 'Denominazione e percorso sono obbligatori.';
            header('Location: ' . BASE_URL . 'esami-di-stato'); exit;
        }

        if ($riferimentoTipo === 'classe' && !$percorsoAnnoId) {
            $_SESSION['flash_error'] = 'Seleziona la classe (anno di corso) di riferimento.';
            header('Location: ' . BASE_URL . 'esami-di-stato'); exit;
        }

        if (!in_array($riferimentoTipo, ['percorso', 'classe'], true)) {
            $riferimentoTipo = 'classe';
        }

        $esameId = $this->model->create([
            'denominazione'      => $denominazione,
            'data'               => $_POST['data']       ?? '',
            'ora_inizio'         => $_POST['ora_inizio'] ?? '',
            'riferimento_tipo'   => $riferimentoTipo,
            'percorso_id'        => $percorsoId,
            'percorso_anno_id'   => $riferimentoTipo === 'classe' ? $percorsoAnnoId : null,
            'rappresentante_id'  => $rappresentante ?: null,
            'note'               => $_POST['note'] ?? '',
        ]);

        if ($rappresentante) {
            $this->model->addCommissario($esameId, $rappresentante, 'presidente');
        }

        $prove = [];
        $provaTeorica = $_POST['prova_teorica'] ?? [];
        $provaPratica = $_POST['prova_pratica'] ?? [];
        if (!empty($provaTeorica['data']) || !empty($provaTeorica['ora_inizio'])) {
            $prove[] = [
                'data'       => $provaTeorica['data']       ?? '',
                'ora_inizio' => $provaTeorica['ora_inizio'] ?? '',
                'ora_fine'   => $provaTeorica['ora_fine']   ?? '',
                'tipo_prova' => 'Teorica',
            ];
        }
        if (!empty($provaPratica['data']) || !empty($provaPratica['ora_inizio'])) {
            $prove[] = [
                'data'       => $provaPratica['data']       ?? '',
                'ora_inizio' => $provaPratica['ora_inizio'] ?? '',
                'ora_fine'   => $provaPratica['ora_fine']   ?? '',
                'tipo_prova' => 'Pratica',
            ];
        }
        if (!empty($prove)) {
            $this->model->saveProve($esameId, $prove);
        }

        $n = $this->model->iscriviStudentiAuto(
            $esameId,
            $riferimentoTipo,
            $percorsoId,
            $riferimentoTipo === 'classe' ? $percorsoAnnoId : null
        );

        $_SESSION['flash_success'] = "Esame di stato <strong>{$denominazione}</strong> creato con {$n} studenti iscritti.";
        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
        exit;
    }

    public function detail(int $id): void {
        $page  = 'esami-di-stato';
        $esame = $this->model->findById($id);
        if (!$esame) {
            header('Location: ' . BASE_URL . 'esami-di-stato'); exit;
        }

        $pageTitle      = $esame['denominazione'];
        $iscrizioni     = $this->model->getIscrizioni($id);
        $studentiDisp   = $this->model->getStudentiNonIscritti($id);
        $commissione    = $this->model->getCommissione($id);
        $allegati       = $this->model->getAllegati($id);
        $prove          = $this->model->getProve($id);
        $docenti        = $this->insegnanteModel->getAll();
        $commIds        = array_column($commissione, 'id');
        $docDisponibili = array_filter($docenti, fn($d) => !in_array($d['id'], $commIds));
        $success        = $_SESSION['flash_success'] ?? null;
        $error          = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/esami-di-stato/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function iscrivi(): void {
        $esameId    = (int)($_POST['esame_id']    ?? 0);
        $studenteId = (int)($_POST['studente_id'] ?? 0);

        if ($esameId && $studenteId) {
            if ($this->model->iscriviStudente($esameId, $studenteId)) {
                $_SESSION['flash_success'] = 'Studente iscritto all\'esame di stato.';
            } else {
                $_SESSION['flash_error'] = 'Iscrizione non riuscita (studente già iscritto).';
            }
        }
        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId); exit;
    }

    public function disiscrivi(): void {
        $iscrId  = (int)($_POST['iscr_id']   ?? 0);
        $esameId = (int)($_POST['esame_id']  ?? 0);
        if ($iscrId) {
            $this->model->disiscriviStudente($iscrId);
            $_SESSION['flash_success'] = 'Studente rimosso dall\'esame.';
        }
        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId); exit;
    }

    public function saveEsiti(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        $righe   = $_POST['esiti'] ?? [];

        if ($esameId > 0 && !empty($righe)) {
            $this->model->saveEsiti($righe);
            $_SESSION['flash_success'] = 'Esiti salvati.';
        }
        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
        exit;
    }

    public function addCommissario(): void {
        $esameId      = (int)($_POST['esame_id']      ?? 0);
        $insegnanteId = (int)($_POST['insegnante_id'] ?? 0);
        $ruolo        = $_POST['ruolo'] ?? 'commissario';
        $isAjax       = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($esameId && $insegnanteId) {
            $this->model->addCommissario($esameId, $insegnanteId, $ruolo);

            if ($isAjax) {
                $ins = $this->insegnanteModel->findById($insegnanteId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success'      => true,
                    'id'           => $insegnanteId,
                    'cognome'      => $ins['cognome'] ?? '',
                    'nome'         => $ins['nome']    ?? '',
                    'ruolo'        => $ruolo,
                ]);
                exit;
            }

            $_SESSION['flash_success'] = 'Docente aggiunto alla commissione.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }

        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId); exit;
    }

    public function removeCommissario(): void {
        $esameId      = (int)($_POST['esame_id']      ?? 0);
        $insegnanteId = (int)($_POST['insegnante_id'] ?? 0);
        $isAjax       = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($esameId && $insegnanteId) {
            $this->model->removeCommissario($esameId, $insegnanteId);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'insegnante_id' => $insegnanteId]);
                exit;
            }

            $_SESSION['flash_success'] = 'Docente rimosso dalla commissione.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }

        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId); exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Esame di stato eliminato.';
        }
        header('Location: ' . BASE_URL . 'esami-di-stato');
        exit;
    }

    public function uploadAllegato(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami-di-stato');
            exit;
        }

        $uploadDir = BASE_PATH . 'public/uploads/esami-di-stato/';
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
            header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
            exit;
        }

        $mime         = mime_content_type($_FILES['allegato']['tmp_name']);
        $originalName = basename($_FILES['allegato']['name']);
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($mime, $allowed) || !in_array($ext, ['pdf','doc','docx','jpg','jpeg','png'])) {
            $_SESSION['flash_error'] = 'Formato non consentito. Usa PDF, DOC, DOCX, JPG o PNG.';
            header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
            exit;
        }

        if ($_FILES['allegato']['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'File troppo grande (max 10 MB).';
            header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
            exit;
        }

        $filename = uniqid('eds_', true) . '.' . $ext;
        move_uploaded_file($_FILES['allegato']['tmp_name'], $uploadDir . $filename);
        $this->model->addAllegato($esameId, $filename, $originalName, $mime);

        $_SESSION['flash_success'] = 'Documento caricato.';
        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
        exit;
    }

    public function saveDocumento(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami-di-stato');
            exit;
        }

        $this->model->updateDatiDocumento($esameId, [
            'ente_gestore'  => trim($_POST['ente_gestore']  ?? ''),
            'sede_presso'   => trim($_POST['sede_presso']   ?? ''),
            'sede_via'      => trim($_POST['sede_via']      ?? ''),
            'sede_comune'   => trim($_POST['sede_comune']   ?? ''),
            'sede_telefono' => trim($_POST['sede_telefono'] ?? ''),
            'cod_corso'     => trim($_POST['cod_corso']     ?? ''),
            'cod_did_reg'   => trim($_POST['cod_did_reg']   ?? ''),
            'ore_corso'     => trim($_POST['ore_corso']     ?? ''),
        ]);

        $prove = $_POST['prove'] ?? [];
        if (is_array($prove)) {
            $this->model->saveProve($esameId, $prove);
        }

        $_SESSION['flash_success'] = 'Dati documento salvati.';
        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
        exit;
    }

    public function stampa(): void {
        $esameId = (int)($_GET['id'] ?? $_POST['esame_id'] ?? 0);
        $tipo    = $_GET['tipo'] ?? 'calesa';

        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami-di-stato');
            exit;
        }

        $esame = $this->model->findById($esameId);
        if (!$esame) {
            header('Location: ' . BASE_URL . 'esami-di-stato');
            exit;
        }

        try {
            require_once BASE_PATH . 'services/DocumentoEsameDiStato.php';
            $service = new DocumentoEsameDiStato();
            $docx    = $service->genera($esameId, $tipo);

            $filename = 'CAL-ESA_' . preg_replace('/[^\w\-]+/u', '_', $esame['denominazione']) . '.docx';
            $docx->download($filename);
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Errore generazione documento: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
            exit;
        }
    }

    public function deleteAllegato(): void {
        $id      = (int)($_POST['allegato_id'] ?? 0);
        $esameId = (int)($_POST['esame_id']    ?? 0);
        if ($id > 0) {
            $allegato = $this->model->findAllegato($id);
            if ($allegato) {
                $path = BASE_PATH . 'public/uploads/esami-di-stato/' . $allegato['filename'];
                if (file_exists($path)) unlink($path);
                $this->model->deleteAllegato($id);
                $_SESSION['flash_success'] = 'Documento eliminato.';
            }
        }
        header('Location: ' . BASE_URL . 'esami-di-stato/detail/' . $esameId);
        exit;
    }
}
