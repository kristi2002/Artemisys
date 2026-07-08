<?php

require_once BASE_PATH . 'models/Retta.php';

class RetteController {

    private Retta $model;

    public function __construct() {
        $this->model = new Retta();
        $this->model->createTables();
    }

    public function index(): void {
        $page      = 'rette';
        $pageTitle = 'Rette e Pagamenti';
        $search    = trim($_GET['q'] ?? '');
        $rette     = $this->model->getAll($search);
        $studenti  = $this->model->getStudenti();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/rette/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'rette'); exit;
        }

        $studenteId = (int)($_POST['studente_id'] ?? 0);
        $importo    = (float)str_replace(',', '.', $_POST['importo_totale'] ?? '0');
        $numRate    = max(1, min(60, (int)($_POST['num_rate'] ?? 1)));
        $cadenza    = in_array($_POST['cadenza'] ?? '', ['mensile','bimestrale','trimestrale'], true)
                      ? $_POST['cadenza'] : 'mensile';

        if (!$studenteId || $importo <= 0) {
            $_SESSION['flash_error'] = 'Studente e importo sono obbligatori.';
            header('Location: ' . BASE_URL . 'rette'); exit;
        }

        $id = $this->model->create([
            'studente_id'         => $studenteId,
            'importo_totale'      => $importo,
            'num_rate'            => $numRate,
            'cadenza'             => $cadenza,
            'data_prima_scadenza' => $_POST['data_prima_scadenza'] ?? '',
            'note'                => $_POST['note'] ?? '',
        ]);

        $_SESSION['flash_success'] = "Retta creata con {$numRate} rate.";
        header('Location: ' . BASE_URL . 'rette/detail/' . $id);
        exit;
    }

    public function detail(int $id): void {
        $page  = 'rette';
        $retta = $this->model->findById($id);
        if (!$retta) { header('Location: ' . BASE_URL . 'rette'); exit; }

        $pageTitle = 'Retta — ' . $retta['cognome'] . ' ' . $retta['nome'];
        $rate      = $this->model->getRate($id);
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/rette/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function pay(): void {
        $rataId = (int)($_POST['rata_id'] ?? 0);
        $rata   = $this->model->findRata($rataId);
        if ($rata) {
            $this->model->markPagata(
                $rataId,
                $_POST['data_pagamento'] ?? date('Y-m-d'),
                $_POST['metodo']         ?? '',
                $_POST['note']           ?? ''
            );
            $_SESSION['flash_success'] = "Rata #{$rata['numero']} segnata come pagata.";
            header('Location: ' . BASE_URL . 'rette/detail/' . $rata['retta_id']); exit;
        }
        header('Location: ' . BASE_URL . 'rette'); exit;
    }

    public function unpay(): void {
        $rataId = (int)($_POST['rata_id'] ?? 0);
        $rata   = $this->model->findRata($rataId);
        if ($rata) {
            $this->model->markNonPagata($rataId);
            $_SESSION['flash_success'] = "Rata #{$rata['numero']} riportata come non pagata.";
            header('Location: ' . BASE_URL . 'rette/detail/' . $rata['retta_id']); exit;
        }
        header('Location: ' . BASE_URL . 'rette'); exit;
    }

    public function uploadFile(): void {
        $rataId = (int)($_POST['rata_id'] ?? 0);
        $rata   = $this->model->findRata($rataId);
        if (!$rata) { header('Location: ' . BASE_URL . 'rette'); exit; }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Errore durante il caricamento del file.';
            header('Location: ' . BASE_URL . 'rette/detail/' . $rata['retta_id']); exit;
        }

        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            $_SESSION['flash_error'] = 'Formato non valido (solo PDF, JPG, PNG).';
            header('Location: ' . BASE_URL . 'rette/detail/' . $rata['retta_id']); exit;
        }

        $dir = BASE_PATH . 'public/uploads/rate/';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $newName  = 'rata_' . uniqid('', true) . '.' . $ext;
        $destPath = $dir . $newName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
            // Rimuovi vecchio file se presente
            if ($rata['file_pdf'] && file_exists($dir . $rata['file_pdf'])) {
                @unlink($dir . $rata['file_pdf']);
            }
            $this->model->setFile($rataId, $newName, basename($_FILES['file']['name']));
            $_SESSION['flash_success'] = 'File caricato.';
        } else {
            $_SESSION['flash_error'] = 'Errore salvataggio file.';
        }

        header('Location: ' . BASE_URL . 'rette/detail/' . $rata['retta_id']); exit;
    }

    public function deleteFile(): void {
        $rataId = (int)($_POST['rata_id'] ?? 0);
        $rata   = $this->model->findRata($rataId);
        if ($rata && $rata['file_pdf']) {
            $path = BASE_PATH . 'public/uploads/rate/' . $rata['file_pdf'];
            if (file_exists($path)) @unlink($path);
            $this->model->removeFile($rataId);
            $_SESSION['flash_success'] = 'File rimosso.';
        }
        if ($rata) {
            header('Location: ' . BASE_URL . 'rette/detail/' . $rata['retta_id']); exit;
        }
        header('Location: ' . BASE_URL . 'rette'); exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Rimuovi file fisici
            $rate = $this->model->getRate($id);
            $dir  = BASE_PATH . 'public/uploads/rate/';
            foreach ($rate as $r) {
                if ($r['file_pdf'] && file_exists($dir . $r['file_pdf'])) {
                    @unlink($dir . $r['file_pdf']);
                }
            }
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Retta eliminata.';
        }
        header('Location: ' . BASE_URL . 'rette'); exit;
    }
}
