<?php

require_once BASE_PATH . 'models/Evento.php';

class EventiController {

    private Evento $model;

    public function __construct() {
        $this->model = new Evento();
        $this->model->createTables();
    }

    public function index(): void {
        $page      = 'eventi';
        $pageTitle = 'Eventi';
        $search    = trim($_GET['q']     ?? '');
        $stato     = trim($_GET['stato'] ?? '');
        $tipo      = trim($_GET['tipo']  ?? '');
        $eventi    = $this->model->getAll($search, $stato, $tipo);
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/eventi/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'eventi'); exit;
        }

        $titolo     = trim($_POST['titolo']      ?? '');
        $dataEvento = $_POST['data_evento']      ?? '';

        if ($titolo === '' || $dataEvento === '') {
            $_SESSION['flash_error'] = 'Titolo e data sono obbligatori.';
            header('Location: ' . BASE_URL . 'eventi'); exit;
        }

        $id = $this->model->create([
            'titolo'              => $titolo,
            'descrizione'         => $_POST['descrizione']         ?? '',
            'tipo'                => $_POST['tipo']                ?? 'altro',
            'data_evento'         => $dataEvento,
            'ora_inizio'          => $_POST['ora_inizio']          ?? '',
            'ora_fine'            => $_POST['ora_fine']            ?? '',
            'luogo'               => $_POST['luogo']               ?? '',
            'max_iscritti'        => !empty($_POST['max_iscritti']) ? (int)$_POST['max_iscritti'] : null,
            'scadenza_iscrizioni' => $_POST['scadenza_iscrizioni'] ?? '',
            'relatore'            => $_POST['relatore']            ?? '',
            'stato'               => $_POST['stato']               ?? 'aperto',
        ]);

        $_SESSION['flash_success'] = 'Evento creato.';
        header('Location: ' . BASE_URL . 'eventi/detail/' . $id);
        exit;
    }

    public function detail(int $id): void {
        $page    = 'eventi';
        $evento  = $this->model->findById($id);
        if (!$evento) { header('Location: ' . BASE_URL . 'eventi'); exit; }

        $pageTitle    = $evento['titolo'];
        $iscrizioni   = $this->model->getIscrizioni($id);
        $studentiDisp = $this->model->getStudentiNonIscritti($id);
        $success      = $_SESSION['flash_success'] ?? null;
        $error        = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/eventi/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function update(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->update($id, [
                'titolo'              => $_POST['titolo'] ?? '',
                'descrizione'         => $_POST['descrizione'] ?? '',
                'tipo'                => $_POST['tipo'] ?? 'altro',
                'data_evento'         => $_POST['data_evento'] ?? '',
                'ora_inizio'          => $_POST['ora_inizio'] ?? '',
                'ora_fine'            => $_POST['ora_fine'] ?? '',
                'luogo'               => $_POST['luogo'] ?? '',
                'max_iscritti'        => !empty($_POST['max_iscritti']) ? (int)$_POST['max_iscritti'] : null,
                'scadenza_iscrizioni' => $_POST['scadenza_iscrizioni'] ?? '',
                'relatore'            => $_POST['relatore'] ?? '',
                'stato'               => $_POST['stato']   ?? 'aperto',
            ]);
            $_SESSION['flash_success'] = 'Evento aggiornato.';
        }
        header('Location: ' . BASE_URL . 'eventi/detail/' . $id); exit;
    }

    public function setStato(): void {
        $id     = (int)($_POST['id']    ?? 0);
        $stato  = $_POST['stato']       ?? '';
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        if ($id > 0 && $stato) {
            $this->model->setStato($id, $stato);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'stato' => $stato]);
                exit;
            }
            $_SESSION['flash_success'] = 'Stato aggiornato.';
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false]);
                exit;
            }
        }
        header('Location: ' . BASE_URL . 'eventi/detail/' . $id); exit;
    }

    public function iscrivi(): void {
        $eventoId   = (int)($_POST['evento_id']   ?? 0);
        $studenteId = (int)($_POST['studente_id'] ?? 0);
        $isAjax     = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($eventoId && $studenteId) {
            if ($this->model->iscriviStudente($eventoId, $studenteId)) {
                if ($isAjax) {
                    // Recupera i dati dello studente appena iscritto
                    require_once BASE_PATH . 'models/Studente.php';
                    $sm      = new Studente();
                    $studente = $sm->findById($studenteId);
                    // Recupera l'id dell'iscrizione appena inserita
                    $iscrId  = (int)$this->model->getLastIscrId($eventoId, $studenteId);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success'        => true,
                        'iscr_id'        => $iscrId,
                        'studente_id'    => $studenteId,
                        'cognome'        => $studente['cognome'] ?? '',
                        'nome'           => $studente['nome']    ?? '',
                        'email'          => $studente['email']   ?? '',
                        'codice_fiscale' => $studente['codice_fiscale'] ?? '',
                    ]);
                    exit;
                }
                $_SESSION['flash_success'] = 'Studente iscritto all\'evento.';
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Iscrizione non riuscita (capienza esaurita o già iscritto).']);
                    exit;
                }
                $_SESSION['flash_error'] = 'Iscrizione non riuscita (capienza esaurita o già iscritto).';
            }
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
                exit;
            }
        }
        header('Location: ' . BASE_URL . 'eventi/detail/' . $eventoId); exit;
    }

    public function disiscrivi(): void {
        $iscrId   = (int)($_POST['iscr_id']   ?? 0);
        $eventoId = (int)($_POST['evento_id'] ?? 0);
        $isAjax   = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        if ($iscrId) {
            $this->model->disiscriviStudente($iscrId);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            $_SESSION['flash_success'] = 'Studente rimosso.';
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'ID non valido.']);
                exit;
            }
        }
        header('Location: ' . BASE_URL . 'eventi/detail/' . $eventoId); exit;
    }

    public function setPresenza(): void {
        $iscrId   = (int)($_POST['iscr_id']   ?? 0);
        $presente = !empty($_POST['presente']);
        $eventoId = (int)($_POST['evento_id'] ?? 0);
        if ($iscrId) {
            $this->model->setPresenza($iscrId, $presente);
        }
        header('Location: ' . BASE_URL . 'eventi/detail/' . $eventoId); exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Evento eliminato.';
        }
        header('Location: ' . BASE_URL . 'eventi'); exit;
    }
}
