<?php

require_once BASE_PATH . 'models/Percorso.php';
require_once BASE_PATH . 'models/AnnoScolastico.php';
require_once BASE_PATH . 'models/Lezione.php';
require_once BASE_PATH . 'models/Insegnante.php';
require_once BASE_PATH . 'models/Studente.php';
require_once BASE_PATH . 'models/Sede.php';

class PercorsiController {

    private Percorso $model;
    private AnnoScolastico $annoModel;
    private Lezione $lezioneModel;
    private Studente $studenteModel;
    private Insegnante $insegnanteModel;
    private Sede $sedeModel;

    public function __construct() {
        $this->model            = new Percorso();
        $this->annoModel        = new AnnoScolastico();
        $this->lezioneModel     = new Lezione();
        $this->studenteModel    = new Studente();
        $this->insegnanteModel  = new Insegnante();
        $this->sedeModel        = new Sede();
        $this->model->createTables();
        $this->annoModel->createTable();
        $this->lezioneModel->createTables();
    }

    public function index(): void {
        $page      = 'percorsi';
        $pageTitle = 'Percorsi Anni Accademici';
        $percorsi  = $this->model->getAll();
        $anni      = $this->annoModel->getAll();
        $sedi      = $this->sedeModel->getAll();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/percorsi/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function detail(int $id): void {
        $page      = 'percorsi';
        $percorso  = $this->model->findById($id);
        if (!$percorso) {
            header('Location: ' . BASE_URL . 'percorsi');
            exit;
        }
        $pageTitle = $percorso['nome'];
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/percorsi/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function annoDetail(int $annoId): void {
        $page      = 'percorsi';
        $anno      = $this->model->findAnnoById($annoId);
        if (!$anno) {
            header('Location: ' . BASE_URL . 'percorsi');
            exit;
        }
        $pageTitle          = $anno['percorso_nome'];
        $materieAssegnate   = $this->model->getMaterieAnno($annoId);
        $materieDisponibili = $this->model->getMaterieDisponibili($annoId);
        $materieInsegnanti  = $this->lezioneModel->getInsegnantiPerMaterie(array_column($materieAssegnate, 'assoc_id'));
        $studenti           = $this->studenteModel->getByPercorsoAnno($annoId);
        $success            = $_SESSION['flash_success'] ?? null;
        $error              = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/percorsi/anno.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── Scarica Scrutinio Word ───────────────────────────────────────────────
    public function scaricaScrutinio(): void {
        $annoId = (int)($_GET['anno_id'] ?? 0);
        if (!$annoId) { header('Location: ' . BASE_URL . 'percorsi'); exit; }

        $anno     = $this->model->findAnnoById($annoId);
        if (!$anno) { header('Location: ' . BASE_URL . 'percorsi'); exit; }

        $studenti        = $this->studenteModel->getByPercorsoAnno($annoId);
        $materieAssegnate= $this->model->getMaterieAnno($annoId);

        $tplPath = BASE_PATH . 'documenti/templates/scrutinio_originale.docx';
        if (!file_exists($tplPath)) {
            $_SESSION['flash_error'] = 'Template scrutinio non trovato.';
            header('Location: ' . BASE_URL . 'percorsi/anno/' . $annoId); exit;
        }

        $zip = new ZipArchive();
        $zip->open($tplPath);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $x = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_XML1, 'UTF-8');

        // ── Anno formativo (es. 2025/2026) ───────────────────────────────────
        $annoLabel = $anno['anno_label'] ?? '';
        if ($annoLabel) {
            // Nel template compare come "2025" e "2026" spezzati in run separati
            // Cerchiamo la stringa anno_label nel formato "AAAA/AAAA"
            $xml = str_replace('>2025<', '>' . $x(substr($annoLabel, 0, 4)) . '<', $xml);
            $xml = str_replace('>2026<', '>' . $x(substr($annoLabel, -4)) . '<', $xml);
        }

        // ── Nome percorso / denominazione ────────────────────────────────────
        $nomePercorso = $anno['percorso_nome'] ?? '';
        if ($nomePercorso) {
            $xml = str_replace(
                '>OPERATORE DELL\'ACCONCIATURA 1820<',
                '>' . $x($nomePercorso) . '<',
                $xml
            );
        }

        // ── Elenco studenti: trova la tabella con "COGNOME" e "NOME" e inietta le righe ──
        // La tabella allievi ha righe numerate con COGNOME e NOME
        // Sostituiamo il blocco dei nomi esistenti (da ACCETTURA a SPINELLI) con quelli reali
        // Strategia: troviamo le righe dati della tabella allievi e le sostituiamo

        // Costruiamo le righe XML per gli studenti reali
        $makeRun = fn(string $t): string =>
            '<w:r><w:rPr><w:sz w:val="14"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $t . '</w:t></w:r>';

        // Trova la sezione con "ACCETTURA" (primo studente del template) per capire il formato riga
        $firstStudentPos = strpos($xml, 'ACCETTURA');
        if ($firstStudentPos !== false) {
            // Trova l'inizio del <w:tr> che contiene il primo studente
            $trStart = strrpos(substr($xml, 0, $firstStudentPos), '<w:tr ');
            if ($trStart === false) $trStart = strrpos(substr($xml, 0, $firstStudentPos), '<w:tr>');

            // Trova la fine dell'ultimo studente del template (SPINELLI)
            $lastStudentPos = strpos($xml, 'SPINELLI');
            if ($lastStudentPos !== false) {
                $trEnd = strpos($xml, '</w:tr>', $lastStudentPos) + strlen('</w:tr>');

                // Estrai una riga template (il primo studente) da clonare
                $templateRowEnd = strpos($xml, '</w:tr>', $trStart) + strlen('</w:tr>');
                $templateRow    = substr($xml, $trStart, $templateRowEnd - $trStart);

                // Costruisci le nuove righe per gli studenti reali
                $newRows = '';
                foreach ($studenti as $idx => $s) {
                    $n       = $idx + 1;
                    $cognome = strtoupper(trim($s['cognome'] ?? ''));
                    $nome    = strtoupper(trim($s['nome']   ?? ''));
                    $sesso   = strtoupper(trim($s['sesso']  ?? ''));

                    // Clona la riga template e sostituisci i placeholder visibili
                    $row = $templateRow;

                    // Sostituisci numero riga (primo <w:t> nella riga)
                    $row = preg_replace('/<w:t>1<\/w:t>/', '<w:t>' . $n . '</w:t>', $row, 1);
                    // Sostituisci ACCETTURA con cognome
                    $row = str_replace('>ACCETTURA MICHELA<', '>' . $x($cognome . ' ' . $nome) . '<', $row);
                    $row = str_replace('>ACCETTURA<', '>' . $x($cognome) . '<', $row);
                    $row = str_replace('>MICHELA<',   '>' . $x($nome)    . '<', $row);
                    // Sesso
                    $row = str_replace('>F<', '>' . $x($sesso ?: 'F') . '<', $row);
                    // Luogo/data nascita
                    $luogo = $x($s['luogo_nascita'] ?? '');
                    $data  = !empty($s['data_nascita']) ? date('d/m/Y', strtotime($s['data_nascita'])) : '';
                    $row = str_replace('>SAN BENEDETTO DEL TRONTO<', '>' . $luogo . '<', $row);
                    $row = str_replace('>29/12/2004<', '>' . $x($data) . '<', $row);

                    $newRows .= $row;
                }

                // Rimpiazza il blocco studenti nel XML
                if ($newRows) {
                    $xml = substr($xml, 0, $trStart) . $newRows . substr($xml, $trEnd);
                }
            }
        }

        // ── Scrivi e servi ────────────────────────────────────────────────────
        $tmpPath = sys_get_temp_dir() . '/scrutinio_' . $annoId . '_' . time() . '.docx';
        copy($tplPath, $tmpPath);
        $zipOut = new ZipArchive();
        $zipOut->open($tmpPath, ZipArchive::CREATE);
        $zipOut->addFromString('word/document.xml', $xml);
        $zipOut->close();

        $ordinali  = [1=>'Primo Anno',2=>'Secondo Anno',3=>'Terzo Anno',4=>'Quarto Anno',5=>'Quinto Anno',
                      6=>'Sesto Anno',7=>'Settimo Anno',8=>'Ottavo Anno',9=>'Nono Anno',10=>'Decimo Anno'];
        $nomeAnno  = $ordinali[$anno['numero']] ?? ($anno['numero'] . ' Anno');
        $filename  = 'Scrutinio_' . str_replace(' ', '_', $nomeAnno) . '_' . ($annoLabel ?: date('Y')) . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpPath));
        header('Cache-Control: no-cache');
        readfile($tmpPath);
        unlink($tmpPath);
        exit;
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'percorsi'); exit;
        }
        $id   = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $anno = (int)($_POST['anno_scolastico_id'] ?? 0);

        if ($id <= 0 || $nome === '' || $anno === 0) {
            $_SESSION['flash_error'] = 'Nome e anno scolastico sono obbligatori.';
            header('Location: ' . BASE_URL . 'percorsi'); exit;
        }

        $this->model->update($id, [
            'nome'               => $nome,
            'anno_scolastico_id' => $anno,
            'sede_id'            => !empty($_POST['sede_id']) ? (int)$_POST['sede_id'] : null,
            'descrizione'        => trim($_POST['descrizione'] ?? ''),
            'data_inizio_anno'   => !empty($_POST['data_inizio_anno']) ? $_POST['data_inizio_anno'] : null,
            'data_fine_anno'     => !empty($_POST['data_fine_anno'])   ? $_POST['data_fine_anno']   : null,
        ]);

        $_SESSION['flash_success'] = "Percorso <strong>{$nome}</strong> aggiornato.";
        header('Location: ' . BASE_URL . 'percorsi'); exit;
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'percorsi');
            exit;
        }

        $nome               = trim($_POST['nome'] ?? '');
        $anno_scolastico_id = (int)($_POST['anno_scolastico_id'] ?? 0);

        if ($nome === '' || $anno_scolastico_id === 0) {
            $_SESSION['flash_error'] = 'Nome e anno scolastico sono obbligatori.';
            header('Location: ' . BASE_URL . 'percorsi');
            exit;
        }

        $id = $this->model->create([
            'nome'               => $nome,
            'anno_scolastico_id' => $anno_scolastico_id,
            'descrizione'        => $_POST['descrizione'] ?? '',
            'sede_id'            => !empty($_POST['sede_id']) ? (int)$_POST['sede_id'] : null,
            'data_inizio_anno'   => !empty($_POST['data_inizio_anno']) ? $_POST['data_inizio_anno'] : null,
            'data_fine_anno'     => !empty($_POST['data_fine_anno'])   ? $_POST['data_fine_anno']   : null,
        ]);

        $_SESSION['flash_success'] = "Percorso <strong>{$nome}</strong> creato. Ora aggiungi gli anni di corso.";
        header('Location: ' . BASE_URL . 'percorsi/detail/' . $id);
        exit;
    }

    public function addAnno(): void {
        $percorsoId = (int)($_POST['percorso_id'] ?? 0);
        $numero     = (int)($_POST['numero'] ?? 0);

        if ($percorsoId > 0 && $numero > 0) {
            $this->model->addAnno($percorsoId, $numero);
            $_SESSION['flash_success'] = ordinal($numero) . ' anno aggiunto.';
        }
        header('Location: ' . BASE_URL . 'percorsi/detail/' . $percorsoId);
        exit;
    }

    public function deleteAnno(): void {
        $annoId     = (int)($_POST['anno_id'] ?? 0);
        $percorsoId = (int)($_POST['percorso_id'] ?? 0);

        if ($annoId > 0) {
            $this->model->deleteAnno($annoId);
            $_SESSION['flash_success'] = 'Anno rimosso.';
        }
        header('Location: ' . BASE_URL . 'percorsi/detail/' . $percorsoId);
        exit;
    }

    public function materiaDetail(int $pamId): void {
        $page      = 'percorsi';
        $pam       = $this->model->getMateriaAssoc($pamId);
        if (!$pam) {
            header('Location: ' . BASE_URL . 'percorsi');
            exit;
        }
        $pageTitle             = $pam['materia_nome'] . ' — ' . $pam['anno_nome'];
        $insegnanti            = $this->lezioneModel->getInsegnanti($pamId);
        $insegnantiIds         = array_column($insegnanti, 'id');
        $tuttiInsegnanti       = (new Insegnante())->getAll();
        $insegnantiDisponibili = array_filter($tuttiInsegnanti, fn($i) => !in_array($i['id'], $insegnantiIds));
        $argomenti             = $this->lezioneModel->getArgomenti($pamId);
        $lezioni               = $this->lezioneModel->getByMateria($pamId);
        $lezioniInsegnanti     = $this->lezioneModel->getInsegnantiPerLezioni(array_column($lezioni, 'id'));
        $success            = $_SESSION['flash_success'] ?? null;
        $error              = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/percorsi/materia.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function addInsegnante(): void {
        $pamId        = (int)($_POST['pam_id'] ?? 0);
        $insegnanteId = (int)($_POST['insegnante_id'] ?? 0);
        $isAjax       = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($pamId > 0 && $insegnanteId > 0) {
            $this->lezioneModel->addInsegnante($pamId, $insegnanteId);
            if ($isAjax) {
                $ins = $this->insegnanteModel->findById($insegnanteId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'id'       => $insegnanteId,
                    'cognome'  => $ins['cognome'] ?? '',
                    'nome'     => $ins['nome']    ?? '',
                ]);
                exit;
            }
            $_SESSION['flash_success'] = 'Insegnante aggiunto.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/materia/' . $pamId);
        exit;
    }

    public function removeInsegnante(): void {
        $pamId        = (int)($_POST['pam_id'] ?? 0);
        $insegnanteId = (int)($_POST['insegnante_id'] ?? 0);
        $isAjax       = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($pamId > 0 && $insegnanteId > 0) {
            $this->lezioneModel->removeInsegnante($pamId, $insegnanteId);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'insegnante_id' => $insegnanteId]);
                exit;
            }
            $_SESSION['flash_success'] = 'Insegnante rimosso.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/materia/' . $pamId);
        exit;
    }

    public function addArgomento(): void {
        $pamId  = (int)($_POST['pam_id'] ?? 0);
        $titolo = trim($_POST['titolo'] ?? '');
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($pamId > 0 && $titolo !== '') {
            $id = $this->lezioneModel->addArgomento($pamId, $titolo, $_POST['descrizione'] ?? '');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $id, 'titolo' => $titolo]);
                exit;
            }
            $_SESSION['flash_success'] = 'Argomento aggiunto.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/materia/' . $pamId);
        exit;
    }

    public function deleteArgomento(): void {
        $id     = (int)($_POST['id'] ?? 0);
        $pamId  = (int)($_POST['pam_id'] ?? 0);
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($id > 0) {
            $this->lezioneModel->deleteArgomento($id);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $id]);
                exit;
            }
            $_SESSION['flash_success'] = 'Argomento rimosso.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/materia/' . $pamId);
        exit;
    }

    public function addLezione(): void {
        $pamId  = (int)($_POST['pam_id'] ?? 0);
        $titolo = trim($_POST['titolo'] ?? '');
        if ($pamId > 0 && $titolo !== '') {
            $oreInput = $_POST['durata_ore'] ?? '';
            $lezioneId = $this->lezioneModel->create([
                'percorso_anno_materia_id' => $pamId,
                'titolo'                   => $titolo,
                'data'                     => $_POST['data'] ?? '',
                'durata_minuti'            => $oreInput !== '' ? (int)round((float)$oreInput * 60) : null,
                'note'                     => $_POST['note'] ?? '',
                'online'                   => isset($_POST['online']) ? 1 : 0,
                'link_online'              => trim($_POST['link_online'] ?? ''),
            ]);
            $insegnanteId = (int)($_POST['insegnante_id'] ?? 0);
            if ($insegnanteId > 0) {
                $this->lezioneModel->addLezioneInsegnante($lezioneId, $insegnanteId);
            }
            $_SESSION['flash_success'] = 'Lezione aggiunta.';
        }
        header('Location: ' . BASE_URL . 'percorsi/materia/' . $pamId);
        exit;
    }

    public function deleteLezione(): void {
        $id    = (int)($_POST['id'] ?? 0);
        $pamId = (int)($_POST['pam_id'] ?? 0);
        if ($id > 0) {
            $this->lezioneModel->delete($id);
            $_SESSION['flash_success'] = 'Lezione rimossa.';
        }
        header('Location: ' . BASE_URL . 'percorsi/materia/' . $pamId);
        exit;
    }

    public function lezioneDetail(int $lezioneId): void {
        $page    = 'percorsi';
        $lezione = $this->lezioneModel->findById($lezioneId);
        if (!$lezione) {
            header('Location: ' . BASE_URL . 'percorsi');
            exit;
        }
        $pageTitle              = $lezione['titolo'];
        $studenti               = $this->lezioneModel->getStudentiAnno($lezione['anno_id']);
        $presenze               = $this->lezioneModel->getPresenze($lezioneId);
        $insegnantiLezione      = $this->lezioneModel->getLezioneInsegnanti($lezioneId);
        $insegnantiLezioneIds   = array_column($insegnantiLezione, 'id');
        $insegnantiMateria      = $this->lezioneModel->getInsegnanti((int)$lezione['pam_id']);
        $insegnantiDisp         = array_filter($insegnantiMateria, fn($i) => !in_array($i['id'], $insegnantiLezioneIds));
        $allegati               = $this->lezioneModel->getAllegati($lezioneId);
        $success                = $_SESSION['flash_success'] ?? null;
        $error                  = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/percorsi/lezione.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function addLezioneInsegnante(): void {
        $lezioneId    = (int)($_POST['lezione_id'] ?? 0);
        $insegnanteId = (int)($_POST['insegnante_id'] ?? 0);
        $isAjax       = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($lezioneId > 0 && $insegnanteId > 0) {
            $this->lezioneModel->addLezioneInsegnante($lezioneId, $insegnanteId);
            if ($isAjax) {
                $ins = $this->insegnanteModel->findById($insegnanteId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'id'      => $insegnanteId,
                    'cognome' => $ins['cognome'] ?? '',
                    'nome'    => $ins['nome']    ?? '',
                ]);
                exit;
            }
            $_SESSION['flash_success'] = 'Insegnante aggiunto alla lezione.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/lezione/' . $lezioneId);
        exit;
    }

    public function removeLezioneInsegnante(): void {
        $lezioneId    = (int)($_POST['lezione_id'] ?? 0);
        $insegnanteId = (int)($_POST['insegnante_id'] ?? 0);
        $isAjax       = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($lezioneId > 0 && $insegnanteId > 0) {
            $this->lezioneModel->removeLezioneInsegnante($lezioneId, $insegnanteId);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'insegnante_id' => $insegnanteId]);
                exit;
            }
            $_SESSION['flash_success'] = 'Insegnante rimosso dalla lezione.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/lezione/' . $lezioneId);
        exit;
    }

    public function updateLezioneDettagli(): void {
        $lezioneId = (int)($_POST['lezione_id'] ?? 0);
        $isAjax    = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($lezioneId > 0) {
            $titolo   = trim($_POST['titolo'] ?? '');
            $oreInput = $_POST['durata_ore'] ?? '';
            $durata   = $oreInput !== '' ? (int)round((float)$oreInput * 60) : null;
            $this->lezioneModel->updateDettagli(
                $lezioneId,
                $titolo !== '' ? $titolo : 'Lezione',
                trim($_POST['data']        ?? ''),
                $durata,
                trim($_POST['argomento']   ?? ''),
                trim($_POST['note']        ?? ''),
                isset($_POST['online']) ? 1 : 0,
                trim($_POST['link_online'] ?? '')
            );
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            $_SESSION['flash_success'] = 'Dettagli salvati.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/lezione/' . $lezioneId);
        exit;
    }

    public function toggleOnline(): void {
        $lezioneId = (int)($_POST['lezione_id'] ?? 0);
        if ($lezioneId > 0) {
            $this->lezioneModel->toggleOnline($lezioneId);
        }
        header('Location: ' . BASE_URL . 'percorsi/lezione/' . $lezioneId);
        exit;
    }

    public function uploadAllegato(): void {
        $lezioneId = (int)($_POST['lezione_id'] ?? 0);
        $isAjax    = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        $jsonError = function(string $msg) use ($isAjax, $lezioneId) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'error' => $msg]); exit; }
            $_SESSION['flash_error'] = $msg;
            header('Location: ' . BASE_URL . 'percorsi/lezione/' . $lezioneId); exit;
        };

        if ($lezioneId <= 0) { $jsonError('ID lezione mancante.'); }

        $uploadDir = BASE_PATH . 'public/uploads/lezioni/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (empty($_FILES['allegato']['name'])) { $jsonError('Nessun file selezionato.'); }

        $mime         = mime_content_type($_FILES['allegato']['tmp_name']);
        $originalName = basename($_FILES['allegato']['name']);
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($mime, $allowed) || !in_array($ext, ['pdf','doc','docx'])) {
            $jsonError('Formato non consentito. Usa PDF, DOC o DOCX.');
        }
        if ($_FILES['allegato']['size'] > 10 * 1024 * 1024) { $jsonError('File troppo grande (max 10 MB).'); }

        $filename = uniqid('lez_', true) . '.' . $ext;
        move_uploaded_file($_FILES['allegato']['tmp_name'], $uploadDir . $filename);
        $newId = $this->lezioneModel->addAllegato($lezioneId, $filename, $originalName, $mime);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'       => true,
                'id'            => $newId,
                'filename'      => $filename,
                'original_name' => $originalName,
                'ext'           => $ext,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
            exit;
        }

        $_SESSION['flash_success'] = 'Allegato caricato.';
        header('Location: ' . BASE_URL . 'percorsi/lezione/' . $lezioneId);
        exit;
    }

    public function deleteAllegato(): void {
        $id        = (int)($_POST['allegato_id'] ?? 0);
        $lezioneId = (int)($_POST['lezione_id']  ?? 0);
        $isAjax    = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($id > 0) {
            $allegato = $this->lezioneModel->findAllegato($id);
            if ($allegato) {
                $path = BASE_PATH . 'public/uploads/lezioni/' . $allegato['filename'];
                if (file_exists($path)) unlink($path);
                $this->lezioneModel->deleteAllegato($id);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'id' => $id]);
                    exit;
                }
                $_SESSION['flash_success'] = 'Allegato eliminato.';
            }
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Allegato non trovato.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/lezione/' . $lezioneId);
        exit;
    }

    public function setPresenza(): void {
        $lezioneId     = (int)($_POST['lezione_id'] ?? 0);
        $tuttiStudenti = array_map('intval', $_POST['tutti_studenti'] ?? []);
        $presentiIds   = array_map('intval', $_POST['presenti'] ?? []);
        $isAjax        = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($lezioneId > 0 && !empty($tuttiStudenti)) {
            $this->lezioneModel->savePresenze($lezioneId, $tuttiStudenti, $presentiIds);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'        => true,
                    'presenti_count' => count($presentiIds),
                    'totale'         => count($tuttiStudenti),
                ]);
                exit;
            }
            $_SESSION['flash_success'] = 'Presenze salvate.';
        } elseif ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
            exit;
        }
        header('Location: ' . BASE_URL . 'percorsi/lezione/' . $lezioneId);
        exit;
    }

    public function addMateria(): void {
        $annoId    = (int)($_POST['anno_id'] ?? 0);
        $materiaId = (int)($_POST['materia_id'] ?? 0);

        if ($annoId > 0 && $materiaId > 0) {
            $this->model->addMateria($annoId, $materiaId);
            $_SESSION['flash_success'] = 'Materia aggiunta.';
        }
        header('Location: ' . BASE_URL . 'percorsi/anno/' . $annoId);
        exit;
    }

    public function deleteMateria(): void {
        $assocId = (int)($_POST['assoc_id'] ?? 0);
        $annoId  = (int)($_POST['anno_id'] ?? 0);

        if ($assocId > 0) {
            $this->model->deleteMateria($assocId);
            $_SESSION['flash_success'] = 'Materia rimossa.';
        }
        header('Location: ' . BASE_URL . 'percorsi/anno/' . $annoId);
        exit;
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['flash_success'] = 'Percorso eliminato.';
        }
        header('Location: ' . BASE_URL . 'percorsi');
        exit;
    }
}

function ordinal(int $n): string {
    $map = [1=>'Primo',2=>'Secondo',3=>'Terzo',4=>'Quarto',5=>'Quinto',
            6=>'Sesto',7=>'Settimo',8=>'Ottavo',9=>'Nono',10=>'Decimo'];
    return ($map[$n] ?? $n . '°');
}
