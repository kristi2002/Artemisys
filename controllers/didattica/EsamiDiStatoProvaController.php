<?php

require_once BASE_PATH . 'models/EsameDiStato.php';
require_once BASE_PATH . 'models/Percorso.php';
require_once BASE_PATH . 'models/Insegnante.php';

class EsamiDiStatoProvaController {

    private EsameDiStato $model;
    private Percorso $percorsoModel;
    private Insegnante $insegnanteModel;

    private $db;

    public function __construct() {
        $this->model           = new EsameDiStato();
        $this->percorsoModel   = new Percorso();
        $this->insegnanteModel = new Insegnante();
        $this->model->createTables();
        $this->percorsoModel->createTables();
        $this->db = Database::getInstance()->getConnection();
        $this->createCommissioneTable();
    }

    private function createCommissioneTable(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS commissione_esami_prova (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                insegnante_id INT NOT NULL,
                ruolo         ENUM('presidente','commissario') NOT NULL DEFAULT 'commissario',
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY u_ins (insegnante_id)
            ) ENGINE=InnoDB
        ");
    }

    private function getCommissione(): array {
        $stmt = $this->db->query("
            SELECT c.id, c.insegnante_id, c.ruolo, i.cognome, i.nome
            FROM commissione_esami_prova c
            JOIN insegnanti i ON i.id = c.insegnante_id
            ORDER BY FIELD(c.ruolo,'presidente','commissario'), i.cognome, i.nome
        ");
        return $stmt->fetchAll();
    }

    public function index(): void {
        $page        = 'esami-di-stato-prova';
        $pageTitle   = 'Esami di Stato Prova';
        $search      = trim($_GET['q'] ?? '');
        $esami       = $this->model->getAll($search);
        $percorsi    = $this->percorsoModel->getAll();
        $docenti     = $this->insegnanteModel->getAll();
        $commissione = $this->getCommissione();
        $commIds     = array_column($commissione, 'insegnante_id') ?: array_map(fn($c) => $c['id'], $commissione);
        // rebuild commIds from join
        $stmt = $this->db->query("SELECT insegnante_id FROM commissione_esami_prova");
        $commIds = array_column($stmt->fetchAll(), 'insegnante_id');
        $docDisponibili = array_filter($docenti, fn($d) => !in_array($d['id'], $commIds));
        $success     = $_SESSION['flash_success'] ?? null;
        $error       = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/esami-di-stato-prova/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function addCommissarioGenerale(): void {
        $insegnanteId = (int)($_POST['insegnante_id'] ?? 0);
        $ruolo        = $_POST['ruolo'] ?? 'commissario';
        $isAjax       = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if (!in_array($ruolo, ['presidente', 'commissario'], true)) $ruolo = 'commissario';

        if ($insegnanteId) {
            try {
                $this->db->prepare("
                    INSERT INTO commissione_esami_prova (insegnante_id, ruolo) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE ruolo = VALUES(ruolo)
                ")->execute([$insegnanteId, $ruolo]);

                if ($isAjax) {
                    $ins = $this->insegnanteModel->findById($insegnanteId);
                    // get new row id
                    $stmt = $this->db->prepare("SELECT id FROM commissione_esami_prova WHERE insegnante_id = ?");
                    $stmt->execute([$insegnanteId]);
                    $rowId = (int)$stmt->fetchColumn();
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'id'      => $rowId,
                        'ins_id'  => $insegnanteId,
                        'cognome' => $ins['cognome'] ?? '',
                        'nome'    => $ins['nome']    ?? '',
                        'ruolo'   => $ruolo,
                    ]);
                    exit;
                }
                $_SESSION['flash_success'] = 'Docente aggiunto alla commissione.';
            } catch (\PDOException $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Errore inserimento.']);
                    exit;
                }
                $_SESSION['flash_error'] = 'Errore durante l\'inserimento.';
            }
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Docente non selezionato.']);
            exit;
        }

        header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
    }

    public function removeCommissarioGenerale(): void {
        $id     = (int)($_POST['commissario_id'] ?? 0);
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($id) {
            $this->db->prepare("DELETE FROM commissione_esami_prova WHERE id = ?")->execute([$id]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            $_SESSION['flash_success'] = 'Docente rimosso dalla commissione.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'ID non valido.']);
            exit;
        }

        header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
        }

        $denominazione   = trim($_POST['denominazione']    ?? '');
        $riferimentoTipo = $_POST['riferimento_tipo']      ?? 'classe';
        $percorsoId      = (int)($_POST['percorso_id']     ?? 0);
        $percorsoAnnoId  = (int)($_POST['percorso_anno_id'] ?? 0);
        $rappresentante  = (int)($_POST['rappresentante_id'] ?? 0);

        if ($denominazione === '' || !$percorsoId) {
            $_SESSION['flash_error'] = 'Denominazione e percorso sono obbligatori.';
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
        }

        if ($riferimentoTipo === 'classe' && !$percorsoAnnoId) {
            $_SESSION['flash_error'] = 'Seleziona la classe (anno di corso) di riferimento.';
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
        }

        if (!in_array($riferimentoTipo, ['percorso', 'classe'], true)) {
            $riferimentoTipo = 'classe';
        }

        $esameId = $this->model->create([
            'denominazione'      => $denominazione,
            'anno_formativo'     => trim($_POST['anno_formativo'] ?? ''),
            'tipo'               => trim($_POST['tipo']           ?? ''),
            'cod_corso'          => trim($_POST['cod_corso']  ?? ''),
            'ore_corso'          => trim($_POST['ore_corso']    ?? ''),
            'cod_did_reg'        => trim($_POST['cod_did_reg']   ?? ''),
            'ente_gestore'       => trim($_POST['ente_gestore']  ?? ''),
            'sede_presso'        => trim($_POST['sede_presso']   ?? ''),
            'sede_via'           => trim($_POST['sede_via']      ?? ''),
            'sede_comune'        => trim($_POST['sede_comune']   ?? ''),
            'sede_telefono'      => trim($_POST['sede_telefono']    ?? ''),
            'data_inizio_anno'   => trim($_POST['data_inizio_anno'] ?? ''),
            'data_fine_anno'     => trim($_POST['data_fine_anno']   ?? ''),
            'data'               => $_POST['data']       ?? '',
            'ora_inizio'         => $_POST['ora_inizio'] ?? '',
            'riferimento_tipo'   => $riferimentoTipo,
            'percorso_id'        => $percorsoId,
            'percorso_anno_id'   => $riferimentoTipo === 'classe' ? $percorsoAnnoId : null,
            'rappresentante_id'  => null,
            'note'               => $_POST['note'] ?? '',
        ]);

        // Inserisci commissione
        $commissione = $_POST['commissione'] ?? [];
        foreach ($commissione as $membro) {
            $insId            = (int)($membro['insegnante_id'] ?? 0);
            $ruolo            = $membro['ruolo'] ?? 'commissario';
            $inRappresentanza = trim($membro['in_rappresentanza'] ?? '');
            if ($insId && in_array($ruolo, ['presidente', 'commissario', 'segretario'], true)) {
                $this->model->addCommissario($esameId, $insId, $ruolo, $inRappresentanza ?: null);
            }
        }

        $prove = [];
        $provaTeorica = $_POST['prova_teorica'] ?? [];
        $provaPratica = $_POST['prova_pratica'] ?? [];
        if (!empty($provaTeorica['data']) || !empty($provaTeorica['ora_inizio']) || !empty($provaTeorica['tipo_prova'])) {
            $prove[] = [
                'data'       => $provaTeorica['data']       ?? '',
                'ora_inizio' => $provaTeorica['ora_inizio'] ?? '',
                'ora_fine'   => $provaTeorica['ora_fine']   ?? '',
                'tipo_prova' => 'P/S/O',
            ];
        }
        if (!empty($provaPratica['data']) || !empty($provaPratica['ora_inizio']) || !empty($provaPratica['tipo_prova'])) {
            $prove[] = [
                'data'       => $provaPratica['data']       ?? '',
                'ora_inizio' => $provaPratica['ora_inizio'] ?? '',
                'ora_fine'   => $provaPratica['ora_fine']   ?? '',
                'tipo_prova' => 'P/S/O',
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
        header('Location: ' . BASE_URL . 'tutti-gli-esami-di-stato');
        exit;
    }

    public function detail(int $id): void {
        $page  = 'esami-di-stato-prova';
        $esame = $this->model->findById($id);
        if (!$esame) {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
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
        require BASE_PATH . 'views/didattica/esami-di-stato-prova/detail.php';
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
        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId); exit;
    }

    public function disiscrivi(): void {
        $iscrId  = (int)($_POST['iscr_id']   ?? 0);
        $esameId = (int)($_POST['esame_id']  ?? 0);
        if ($iscrId) {
            $this->model->disiscriviStudente($iscrId);
            $_SESSION['flash_success'] = 'Studente rimosso dall\'esame.';
        }
        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId); exit;
    }

    public function saveEsiti(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        $righe   = $_POST['esiti'] ?? [];

        if ($esameId > 0 && !empty($righe)) {
            $this->model->saveEsiti($righe);
            $_SESSION['flash_success'] = 'Esiti salvati.';
        }
        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
        exit;
    }

    public function addCommissario(): void {
        $esameId         = (int)($_POST['esame_id']        ?? 0);
        $insegnanteId    = (int)($_POST['insegnante_id']   ?? 0);
        $ruolo           = $_POST['ruolo']                 ?? 'commissario';
        $inRappresentanza = trim($_POST['in_rappresentanza'] ?? '');
        $isAjax          = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($esameId && $insegnanteId) {
            $this->model->addCommissario($esameId, $insegnanteId, $ruolo, $inRappresentanza ?: null);

            if ($isAjax) {
                $ins = $this->insegnanteModel->findById($insegnanteId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success'          => true,
                    'id'               => $insegnanteId,
                    'cognome'          => $ins['cognome'] ?? '',
                    'nome'             => $ins['nome']    ?? '',
                    'ruolo'            => $ruolo,
                    'in_rappresentanza'=> $inRappresentanza,
                ]);
                exit;
            }

            $_SESSION['flash_success'] = 'Docente aggiunto alla commissione.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }

        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId); exit;
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

        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId); exit;
    }

    public function update(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        if ($esameId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
        }

        $denominazione = trim($_POST['denominazione'] ?? '');
        if ($denominazione === '') {
            $_SESSION['flash_error'] = 'La denominazione è obbligatoria.';
            header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId); exit;
        }

        $this->model->update($esameId, [
            'denominazione'    => $denominazione,
            'tipo'             => trim($_POST['tipo']             ?? ''),
            'cod_corso'        => trim($_POST['cod_corso']        ?? ''),
            'cod_did_reg'      => trim($_POST['cod_did_reg']      ?? ''),
            'ore_corso'        => trim($_POST['ore_corso']        ?? ''),
            'ente_gestore'     => trim($_POST['ente_gestore']     ?? ''),
            'data'             => $_POST['data']             ?? '',
            'ora_inizio'       => $_POST['ora_inizio']       ?? '',
            'data_inizio_anno' => $_POST['data_inizio_anno'] ?? '',
            'data_fine_anno'   => $_POST['data_fine_anno']   ?? '',
            'stato'            => $_POST['stato']            ?? 'programmato',
            'note'             => trim($_POST['note']        ?? ''),
        ]);

        // Prove
        $prove = [];
        $p1 = $_POST['prova_1'] ?? [];
        $p2 = $_POST['prova_2'] ?? [];
        if (!empty($p1['data']) || !empty($p1['ora_inizio'])) {
            $prove[] = [
                'data'       => $p1['data']       ?? '',
                'ora_inizio' => $p1['ora_inizio'] ?? '',
                'ora_fine'   => $p1['ora_fine']   ?? '',
                'tipo_prova' => 'P/S/O',
            ];
        }
        if (!empty($p2['data']) || !empty($p2['ora_inizio'])) {
            $prove[] = [
                'data'       => $p2['data']       ?? '',
                'ora_inizio' => $p2['ora_inizio'] ?? '',
                'ora_fine'   => $p2['ora_fine']   ?? '',
                'tipo_prova' => 'P/S/O',
            ];
        }
        $this->model->saveProve($esameId, $prove);

        $_SESSION['flash_success'] = 'Dettagli esame aggiornati.';
        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
        exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Esame di stato eliminato.';
        }
        header('Location: ' . BASE_URL . 'esami-di-stato-prova');
        exit;
    }

    public function uploadAllegato(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova');
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
            header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
            exit;
        }

        $mime         = mime_content_type($_FILES['allegato']['tmp_name']);
        $originalName = basename($_FILES['allegato']['name']);
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($mime, $allowed) || !in_array($ext, ['pdf','doc','docx','jpg','jpeg','png'])) {
            $_SESSION['flash_error'] = 'Formato non consentito. Usa PDF, DOC, DOCX, JPG o PNG.';
            header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
            exit;
        }

        if ($_FILES['allegato']['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'File troppo grande (max 10 MB).';
            header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
            exit;
        }

        $filename = uniqid('eds_', true) . '.' . $ext;
        move_uploaded_file($_FILES['allegato']['tmp_name'], $uploadDir . $filename);
        $this->model->addAllegato($esameId, $filename, $originalName, $mime);

        $_SESSION['flash_success'] = 'Documento caricato.';
        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
        exit;
    }

    public function saveDocumento(): void {
        $esameId = (int)($_POST['esame_id'] ?? 0);
        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova');
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
        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
        exit;
    }

    public function stampa(): void {
        $esameId = (int)($_GET['id'] ?? $_POST['esame_id'] ?? 0);
        $tipo    = $_GET['tipo'] ?? 'calesa';

        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova');
            exit;
        }

        $esame = $this->model->findById($esameId);
        if (!$esame) {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova');
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
            header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
            exit;
        }
    }

    public function scaricaVerbale2(): void {
        $path = BASE_PATH . 'documenti/VERBALE ESAME DI STATO PULITO.odt';
        if (!file_exists($path)) {
            $_SESSION['flash_error'] = 'File verbale non trovato.';
            $esameId = (int)($_GET['id'] ?? 0);
            header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
            exit;
        }
        header('Content-Type: application/vnd.oasis.opendocument.text');
        header('Content-Disposition: attachment; filename="Verbale Esame di Stato.odt"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');
        readfile($path);
        exit;
    }

    public function scaricaVerbale(): void {
        $esameId = (int)($_GET['id'] ?? 0);

        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
        }

        $esame = $this->model->findById($esameId);
        if (!$esame) {
            $_SESSION['flash_error'] = 'Esame non trovato.';
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
        }

        $prove       = $this->model->getProve($esameId);
        $commissione = $this->model->getCommissione($esameId);
        $iscrizioni  = $this->model->getIscrizioni($esameId);

        try {
            require_once BASE_PATH . 'services/VerbaleEsameDiStato.php';
            $service = new VerbaleEsameDiStato();
            $odt     = $service->genera($esame, $prove, $commissione, $iscrizioni);

            $nomeFile = 'Verbale_' . preg_replace('/[^\w\-]+/u', '_', $esame['denominazione']) . '.odt';
            header('Content-Type: application/vnd.oasis.opendocument.text');
            header('Content-Disposition: attachment; filename="' . $nomeFile . '"');
            header('Content-Length: ' . strlen($odt));
            header('Cache-Control: no-cache');
            echo $odt;
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Errore generazione verbale: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
        }
        exit;
    }

    public function scaricaFoglioPresenze(): void {
        $esameId = (int)($_GET['id'] ?? 0);

        if ($esameId <= 0) {
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
        }
        $esame = $this->model->findById($esameId);
        if (!$esame) {
            $_SESSION['flash_error'] = 'Esame non trovato.';
            header('Location: ' . BASE_URL . 'esami-di-stato-prova'); exit;
        }

        $prove       = $this->model->getProve($esameId);
        $commissione = $this->model->getCommissione($esameId);
        $provaNum    = max(1, min(2, (int)($_GET['prova'] ?? 1))); // 1 o 2

        // File originale (con layout ufficiale) come base
        $templatePath = BASE_PATH . 'documenti/templates/foglio_presenze_originale.docx';
        if (!file_exists($templatePath)) {
            $_SESSION['flash_error'] = 'Template foglio presenze non trovato.';
            header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId); exit;
        }

        // ── helpers ───────────────────────────────────────────────────────────
        $x    = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_XML1, 'UTF-8');
        $fmtT = fn($t)  => $t ? substr($t, 0, 5) : '';

        // ── Dati esame ────────────────────────────────────────────────────────
        $annoForm  = $x($esame['anno_label']    ?? '');
        $codCorso  = $x($esame['cod_corso']     ?? '');
        $tipol     = $x($esame['tipo']          ?? '');
        $ore       = $x($esame['ore_corso']     ?? '');
        $codDidReg = $x($esame['cod_did_reg']   ?? '');
        $denom     = $x($esame['denominazione'] ?? '');
        $enteGest  = $x($esame['ente_gestore']  ?? '');
        $sede      = $x(trim(
            ($esame['sede_presso'] ?? '') . ' ' .
            ($esame['sede_via']    ?? '') . ' ' .
            ($esame['sede_comune'] ?? '')
        ));

        // ── Giorno e orario dalla prova selezionata ───────────────────────────
        $prova = $prove[$provaNum - 1] ?? [];

        $ggIT = ['Monday'=>'Lunedì','Tuesday'=>'Martedì','Wednesday'=>'Mercoledì',
                 'Thursday'=>'Giovedì','Friday'=>'Venerdì','Saturday'=>'Sabato','Sunday'=>'Domenica'];
        $mmIT = [1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',
                 7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre'];

        $giornoEsame = '';
        if (!empty($prova['data'])) {
            $ts = strtotime($prova['data']);
            $giornoEsame = $x(
                ($ggIT[date('l',$ts)] ?? date('l',$ts)) . ' ' .
                date('j',$ts) . ' ' . $mmIT[(int)date('n',$ts)] . ' ' . date('Y',$ts)
            );
        }
        $orarioMat = !empty($prova['ora_inizio'])
            ? $x($fmtT($prova['ora_inizio']) .
                 (!empty($prova['ora_fine']) ? '-' . $fmtT($prova['ora_fine']) : ''))
            : '';
        $orarioPom = '';

        // ── Leggi XML dal file originale ──────────────────────────────────────
        $zip = new ZipArchive();
        $zip->open($templatePath);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        // ── Helper: inietta un run di testo nell'N-esimo paragrafo vuoto
        //    trovato dopo una stringa ancora di ancoraggio.
        //    Un "paragrafo vuoto" termina con </w:pPr></w:p> (nessun run tra pPr e /p).
        $injectAfter = function(string $xml, string $anchor, string $value, int $skip = 1) use ($x): string {
            if ($value === '') return $xml;
            $pos = strpos($xml, $anchor);
            if ($pos === false) return $xml;
            $needle = '</w:pPr></w:p>';
            $found  = $pos;
            for ($i = 0; $i < $skip; $i++) {
                $found = strpos($xml, $needle, $found + 1);
                if ($found === false) return $xml;
            }
            $run = '<w:r><w:rPr><w:rFonts w:ascii="Verdana" w:hAnsi="Verdana" w:cs="Verdana"/>'
                 . '<w:sz w:val="18"/></w:rPr>'
                 . '<w:t xml:space="preserve">' . $value . '</w:t></w:r>';
            $insertAt = $found + strlen('</w:pPr>');
            return substr($xml, 0, $insertAt) . $run . substr($xml, $insertAt);
        };

        // ── Anno formativo (testo fisso "2023-2024" nel documento) ────────────
        if ($annoForm !== '') {
            $xml = str_replace('<w:t>2023-2024</w:t>', '<w:t>' . $annoForm . '</w:t>', $xml);
        }

        // ── Campi dati corso ──────────────────────────────────────────────────
        // Cella VALORE dopo ciascuna etichetta: cerco la prima </w:pPr></w:p> dopo l'etichetta
        $xml = $injectAfter($xml, 'Cod. corso</w:t>',    $codCorso, 1);
        $xml = $injectAfter($xml, 'Tipol:</w:t>',         $tipol,   1);
        $xml = $injectAfter($xml, '- Ore:</w:t>',         $ore,     1);
        $xml = $injectAfter($xml, 'All.:</w:t>',          '',         1); // non in DB
        $xml = $injectAfter($xml, '- Cod.Did.Reg:</w:t>', $codDidReg, 1);
        $xml = $injectAfter($xml, 'Denominazione:</w:t>', $denom,     1);
        $xml = $injectAfter($xml, 'Ente Gestore:</w:t>',  $enteGest,  1);

        // ── Calendario: la riga dati è la seconda <w:tr> dopo "Calendario degli esami:"
        //    Schema celle nella riga: [checkbox w=284] [giorno w=3685] [mat w=3119] [pom w=3261]
        //    Contando i </w:pPr></w:p> vuoti dal punto di ancoraggio:
        //    skip=1 → altra cella dell'intestazione, skip=2 → w=284, skip=3 → w=3685 (giorno)
        //    skip=4 → w=3119 (mattina), skip=5 → w=3261 (pomeriggio)
        $xml = $injectAfter($xml, 'Calendario degli esami:</w:t>', $giornoEsame, 3);
        $xml = $injectAfter($xml, 'Calendario degli esami:</w:t>', $orarioMat,   4);
        $xml = $injectAfter($xml, 'Calendario degli esami:</w:t>', $orarioPom,   5);

        // ── Sede di esame ─────────────────────────────────────────────────────
        $xml = $injectAfter($xml, 'Sede di esame:</w:t>', $sede, 1);

        // ── Commissione: inietta nomi nella colonna COMPONENTI ────────────────
        $makeCommRun = fn(string $t): string =>
            '<w:r><w:rPr><w:rFonts w:ascii="Verdana" w:hAnsi="Verdana" w:cs="Verdana"/>'
            . '<w:sz w:val="16"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $t . '</w:t></w:r>';

        // Mappa valore DB → testo univoco ricercabile nel XML Word
        $rappSearch = [
            'AMMINISTRAZIONE REGIONALE'    => 'AMMINISTRAZIONE REGIONALE',
            'DIREZ.NE PROV. LE DEL LAVORO' => 'DIREZ.NE PROV.',
            'PROVVEDITORATO AGLI STUDI'    => 'PROVVEDITORATO AGLI STUDI',
            'DOCENTI CORSO'                => 'DOCENTI  CORSO',  // doppio spazio nel Word
            'ASS. CATEGORIA - LAV. AUTON.' => 'ASS. CATEGORIA',
            'OO. SS. DEI LAV. DIPENDENTI'  => 'OO. SS.',
        ];

        // Trova e isola la tabella commissione
        $commTblPos = strpos($xml, 'COMPOSIZIONE');
        $commTblPos = $commTblPos !== false ? strpos($xml, '<w:tbl>', $commTblPos) : false;

        if ($commTblPos !== false && !empty($commissione)) {
            $commTblEnd = strpos($xml, '</w:tbl>', $commTblPos) + strlen('</w:tbl>');
            $commTbl    = substr($xml, $commTblPos, $commTblEnd - $commTblPos);

            // Deduplicazione: ogni insegnante appare una sola volta (per insegnante_id)
            $commUnica = [];
            $visti = [];
            foreach ($commissione as $m) {
                $uid = (int)($m['insegnante_id'] ?? 0);
                if ($uid > 0 && isset($visti[$uid])) continue;
                if ($uid > 0) $visti[$uid] = true;
                $commUnica[] = $m;
            }

            // Estrai le righe dati (salta header row 0)
            preg_match_all('/<w:tr[ >].*?<\/w:tr>/s', $commTbl, $allRows);
            $dataRows = array_slice($allRows[0], 1);

            // ── Passo 1: abbina per in_rappresentanza ──────────────────────────
            // Conta quante volte ogni istituto è già stato assegnato (per duplicati es. DOCENTI CORSO)
            $usedByRapp = []; // needle → contatore

            // Mappa: indice riga → nome membro da iniettare
            $rowFill = [];

            $senzaRapp = []; // membri senza in_rappresentanza, da piazzare dopo

            foreach ($commUnica as $m) {
                $nomeM = $x(trim(($m['cognome'] ?? '') . ' ' . ($m['nome'] ?? '')));
                if (!$nomeM) continue;
                $rapp   = $m['in_rappresentanza'] ?? '';
                $needle = $rapp !== '' ? ($rappSearch[$rapp] ?? null) : null;

                if ($needle === null) {
                    $senzaRapp[] = $nomeM;
                    continue;
                }

                // Trova l'N-esima riga del Word che contiene questo istituto
                $occurrence = $usedByRapp[$needle] ?? 0;
                $found = 0;
                foreach ($dataRows as $rIdx => $rowXml) {
                    if (strpos($rowXml, $needle) !== false) {
                        if ($found === $occurrence) {
                            $rowFill[$rIdx] = $nomeM;
                            break;
                        }
                        $found++;
                    }
                }
                $usedByRapp[$needle] = ($usedByRapp[$needle] ?? 0) + 1;
            }

            // ── Passo 2: membra senza in_rappresentanza → prima riga libera ───
            foreach ($senzaRapp as $nomeM) {
                foreach ($dataRows as $rIdx => $rowXml) {
                    if (!isset($rowFill[$rIdx])) {
                        $rowFill[$rIdx] = $nomeM;
                        break;
                    }
                }
            }

            // ── Passo 3: applica le sostituzioni (sostituzione posizionale) ───
            // Ricostruisce $commTbl riga per riga per evitare che str_replace
            // sostituisca righe XML identiche (es. due DOCENTI CORSO vuoti).
            preg_match_all('/<w:tr[ >].*?<\/w:tr>/s', $commTbl, $allRowsFull);
            $allFullRows   = $allRowsFull[0];          // tutte le righe, inclusa header
            $headerRow     = $allFullRows[0];
            $rebuiltRows   = [$headerRow];

            foreach ($dataRows as $rIdx => $rowXml) {
                if (isset($rowFill[$rIdx])) {
                    $nomeM  = $rowFill[$rIdx];
                    $newRow = preg_replace(
                        '/(<\/w:pPr>)(<\/w:p>)/',
                        '$1' . $makeCommRun($nomeM) . '$2',
                        $rowXml, 1
                    );
                    $rebuiltRows[] = $newRow;
                } else {
                    $rebuiltRows[] = $rowXml;
                }
            }

            // Rimonta la tabella sostituendo solo il blocco <w:tr>…</w:tr> interno
            $newCommTbl = preg_replace(
                '/<w:tr[ >].*?<\/w:tr>/s',
                '__ROW__',
                $commTbl
            );
            foreach ($rebuiltRows as $row) {
                $newCommTbl = preg_replace('/__ROW__/', $row, $newCommTbl, 1);
            }

            $xml = substr($xml, 0, $commTblPos) . $newCommTbl . substr($xml, $commTblEnd);
        }

        // ── Crea file temporaneo e invia ──────────────────────────────────────
        $tmpPath = sys_get_temp_dir() . '/fp_' . $esameId . '_' . time() . '.docx';
        copy($templatePath, $tmpPath);
        $zout = new ZipArchive();
        $zout->open($tmpPath);
        $zout->addFromString('word/document.xml', $xml);
        $zout->close();

        $fname = 'Foglio_Presenze_' .
                 preg_replace('/[^\w\-]+/u', '_', $esame['denominazione'] ?? 'esame') . '.docx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fname . '"');
        header('Content-Length: ' . filesize($tmpPath));
        header('Cache-Control: no-cache');
        readfile($tmpPath);
        unlink($tmpPath);
        exit;
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
        header('Location: ' . BASE_URL . 'esami-di-stato-prova/detail/' . $esameId);
        exit;
    }
}
