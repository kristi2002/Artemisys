<?php

require_once BASE_PATH . 'models/Studente.php';
require_once BASE_PATH . 'models/Percorso.php';
require_once BASE_PATH . 'models/Materia.php';
require_once BASE_PATH . 'models/AnnoScolastico.php';

class StudentiController {

    private Studente $model;
    private Percorso $percorsoModel;
    private Materia  $materiaModel;
    private AnnoScolastico $annoModel;

    public function __construct() {
        $this->model         = new Studente();
        $this->percorsoModel = new Percorso();
        $this->materiaModel  = new Materia();
        $this->annoModel     = new AnnoScolastico();
        $this->model->createTables();
    }

    public function index(): void {
        $page      = 'studenti';
        $pageTitle = 'Studenti';
        $search    = trim($_GET['q'] ?? '');
        $studenti  = $this->model->getAll($search);
        $totale    = $this->model->count();
        $success   = $_SESSION['flash_success'] ?? null;
        $error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/studenti/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function create(): void {
        $page      = 'studenti';
        $pageTitle = 'Nuovo Studente';

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/studenti/create.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'studenti');
            exit;
        }

        $nome    = trim($_POST['nome']    ?? '');
        $cognome = trim($_POST['cognome'] ?? '');

        if ($nome === '' || $cognome === '') {
            $_SESSION['flash_error'] = 'Nome e cognome sono obbligatori.';
            header('Location: ' . BASE_URL . 'studenti/create');
            exit;
        }

        $id = $this->model->create($_POST);
        $_SESSION['flash_success'] = "Studente <strong>{$nome} {$cognome}</strong> creato.";
        header('Location: ' . BASE_URL . 'studenti/detail/' . $id);
        exit;
    }

    public function detail(int $id): void {
        $page     = 'studenti';
        $studente = $this->model->findById($id);
        if (!$studente) {
            header('Location: ' . BASE_URL . 'studenti');
            exit;
        }

        $pageTitle  = $studente['cognome'] . ' ' . $studente['nome'];
        $iscrizioni = $this->model->getIscrizioni($id);   // array (multi-corso)
        $storiAnni  = $this->model->getStoriaAnni($id);
        $percorsi   = $this->percorsoModel->getAll();
        $annoAttivo = $this->annoModel->getAttivo();

        // Per ogni iscrizione costruisce il blocco dati (anni, materie, anni-percorso)
        $blocchiCorsi = [];
        $idPercorsiIscritti = array_column($iscrizioni, 'percorso_id');

        foreach ($iscrizioni as $isc) {
            $anniPercorso  = $this->percorsoModel->getAnni($isc['percorso_id']);
            $annoCorrente  = null;
            $materieAnno   = [];

            if ($annoAttivo) {
                // Cerca l'anno di corso per questo percorso
                foreach ($this->model->getAnniAttuali($id, $annoAttivo['id']) as $ac) {
                    if ($ac['percorso_id'] == $isc['percorso_id']) {
                        $annoCorrente = $ac;
                        break;
                    }
                }
                if ($annoCorrente) {
                    $materieAnno = $this->model->getMaterieAnno($annoCorrente['percorso_anno_id'], $id);
                }
            }

            $blocchiCorsi[] = [
                'iscrizione'   => $isc,
                'anniPercorso' => $anniPercorso,
                'annoCorrente' => $annoCorrente,
                'materieAnno'  => $materieAnno,
            ];
        }

        // Materie bonus (globali per anno scolastico)
        $materieBonus       = $annoAttivo ? $this->model->getMaterieBonus($id, $annoAttivo['id']) : [];
        $idGiaBonus         = array_column($materieBonus, 'materia_id');
        $idTutteMaterie     = [];
        foreach ($blocchiCorsi as $b) {
            foreach ($b['materieAnno'] as $m) { $idTutteMaterie[] = $m['id']; }
        }
        $tutteMaterie       = $this->materiaModel->getAll();
        $materieDisponibili = array_filter($tutteMaterie, fn($m) =>
            !in_array($m['id'], $idTutteMaterie) && !in_array($m['id'], $idGiaBonus)
        );

        // Percorsi disponibili per aggiungere una nuova iscrizione
        $percorsiDisponibili = array_filter($percorsi, fn($p) =>
            !in_array($p['id'], $idPercorsiIscritti)
        );

        // Compatibilità con vecchio codice (pagella ecc.)
        $iscrizione   = $iscrizioni[0] ?? false;
        $annoCorrente = $blocchiCorsi[0]['annoCorrente'] ?? null;
        $materieAnno  = $blocchiCorsi[0]['materieAnno']  ?? [];
        $anniPercorso = $blocchiCorsi[0]['anniPercorso'] ?? [];

        // Dati per il modal modifica storico
        $anniScolastici = $this->annoModel->getAll();
        // Mappa percorso_id → anni di corso (per il dropdown dell'edit)
        $anniPerPercorso = [];
        foreach ($blocchiCorsi as $b) {
            $anniPerPercorso[$b['iscrizione']['percorso_id']] = $b['anniPercorso'];
        }

        $success = $_SESSION['flash_success'] ?? null;
        $error   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/studenti/detail.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function deleteAnnoStorico(): void {
        $storId     = (int)($_POST['storico_id'] ?? 0);
        $studenteId = (int)($_POST['studente_id'] ?? 0);
        if ($storId > 0) {
            $this->model->deleteAnnoStorico($storId);
            $_SESSION['flash_success'] = 'Voce storico eliminata.';
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }

    public function updateAnnoStorico(): void {
        $storId        = (int)($_POST['storico_id']        ?? 0);
        $studenteId    = (int)($_POST['studente_id']       ?? 0);
        $percorsoAnnoId = (int)($_POST['percorso_anno_id'] ?? 0);
        $annoScolId    = (int)($_POST['anno_scolastico_id'] ?? 0);
        if ($storId > 0 && $percorsoAnnoId > 0 && $annoScolId > 0) {
            $this->model->updateAnnoStorico($storId, $percorsoAnnoId, $annoScolId);
            $_SESSION['flash_success'] = 'Voce storico aggiornata.';
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }

    public function update(): void {
        $id     = (int)($_POST['id'] ?? 0);
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        $nome    = trim($_POST['nome']    ?? '');
        $cognome = trim($_POST['cognome'] ?? '');

        if ($nome === '' || $cognome === '') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Nome e cognome sono obbligatori.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Nome e cognome sono obbligatori.';
            header('Location: ' . BASE_URL . 'studenti/detail/' . $id);
            exit;
        }

        if ($id > 0) {
            $this->model->update($id, $_POST);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'nome' => $nome, 'cognome' => $cognome, 'nome_cognome' => $cognome . ' ' . $nome]);
                exit;
            }
            $_SESSION['flash_success'] = 'Anagrafica aggiornata.';
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $id);
        exit;
    }

    public function downloadSchedaAllievo(): void {
        $template = BASE_PATH . 'documenti/templates/scheda_allievo_template.docx';
        if (!file_exists($template)) {
            http_response_code(404);
            echo 'Template non trovato.';
            exit;
        }

        // ── Raccoglie dati DB ────────────────────────────────────────────────
        $studenteId    = (int)($_POST['studente_id'] ?? 0);
        $percorsoIdGet = (int)($_POST['percorso_id'] ?? 0);
        $studente      = $studenteId ? $this->model->findById($studenteId) : null;

        $nomeStudente   = $studente ? trim($studente['cognome'] . ' ' . $studente['nome']) : '';
        $titoloCorsoDB  = '';
        $codiceCorso    = '';
        $organismoGest  = '';
        $oreCorso       = '';
        $annoLabel      = '';
        $nomeAnno       = '';

        if ($percorsoIdGet) {
            require_once BASE_PATH . 'models/EsameDiStato.php';
            $esameModel = new EsameDiStato();
            $storicoCompleto = $this->model->getStoriaAnni($studenteId);
            $percorsoAnnoId  = null;
            foreach ($storicoCompleto as $sa) {
                if ($sa['percorso_id'] == $percorsoIdGet) {
                    $percorsoAnnoId = $sa['percorso_anno_id'];
                    break;
                }
            }
            $esame = $esameModel->findByPercorso($percorsoIdGet, $percorsoAnnoId);
            if ($esame) {
                $titoloCorsoDB = $esame['denominazione']  ?? '';
                $codiceCorso   = $esame['cod_corso']      ?? '';
                $organismoGest = $esame['ente_gestore']   ?? '';
                $oreCorso      = (string)($esame['ore_corso'] ?? '');
                $annoLabel     = $esame['anno_label']     ?? '';
                $ordinali      = [1=>'1° Anno',2=>'2° Anno',3=>'3° Anno',4=>'4° Anno',5=>'5° Anno'];
                $nomeAnno      = $ordinali[$esame['anno_numero'] ?? 0] ?? '';
            }
        }

        // ── Raccoglie dati form (POST) ───────────────────────────────────────
        $p = fn(string $k) => trim($_POST[$k] ?? '');

        $annoFormativo = trim(($nomeAnno ? $nomeAnno . ' ' : '') . $annoLabel);

        // Materie/assenze: col 1 ha 4 righe nel Word, col 2 e 3 una riga ciascuna
        $materieWord = [];
        for ($r = 0; $r < 4; $r++) $materieWord[] = $p("tab_materia_{$r}_0");
        $materieWord[] = $p('tab_materia_0_1');
        $materieWord[] = $p('tab_materia_0_2');
        $assenzeWord = [$p('tab_assenze_0_0'), $p('tab_assenze_0_1'), $p('tab_assenze_0_2')];

        // ── Apre il template e sostituisce tutti i placeholder ───────────────
        $zip = new ZipArchive();
        if ($zip->open($template) !== true) {
            http_response_code(500); echo 'Impossibile aprire il template.'; exit;
        }
        $newXml = $zip->getFromName('word/document.xml');
        $zip->close();

        $x = fn(string $v) => htmlspecialchars($v, ENT_XML1, 'UTF-8');

        $phReplace = [
            '{{STUDENTE}}'           => $x($nomeStudente),
            '{{TITOLO_CORSO}}'       => $x($titoloCorsoDB),
            '{{CODICE}}'             => $x($codiceCorso),
            '{{NUMERO_SCHEDA}}'      => $x($p('numero_scheda')),
            '{{ANNO_FORM}}'          => $x($annoFormativo),
            '{{ORGANISMO}}'          => $x($organismoGest),
            '{{ORE_CORSO}}'          => $x($oreCorso),
            '{{OBIETTIVO}}'          => $x($p('obiettivo_prefissato')),
            '{{MATERIE_AREA}}'       => $x($p('materie_area_didattica')),
            '{{VERIFICHE}}'          => $x($p('verifiche_didattiche')),
            '{{AMMISSIONE}}'         => $x($p('ammissione_esami_finali')),
            '{{COMPETENZE}}'         => $x($p('competenze_acquisite')),
            '{{VALUTAZIONE_FINALE}}' => $x($p('valutazione_finale_team')),
            '{{GIUDIZIO_TEAM}}'      => $x($p('giudizio_centesimi') ?: '______'),
            '{{GIUDIZIO_AUTO}}'      => $x($p('autovalutazione_giudizio') ?: '_______'),
            '{{AUTO_TESTO}}'         => $x($p('autovalutazione_testo')),
            '{{FIRMA_ALLIEVO}}'      => $x($p('autovalutazione_firma_allievo')),
            '{{FIRMA_TEAM_1}}'       => $x(trim(($p('firma_team_docenti_1') ?: '') . '  ' . ($p('firma_team_docenti_2') ?: ''))),
            '{{FIRMA_TEAM_2}}'       => '',
            '{{FIRMA_COORD}}'        => $x($p('firma_coordinatore')),
            // ── Scheda 3 – Stage ─────────────────────────────────────────────
            '{{STAGE_AZIENDA}}'      => $x($p('stage_azienda')),
            '{{STAGE_TUTOR}}'        => $x($p('stage_tutor')),
            '{{STAGE_DAL_1}}'        => $x($p('stage_dal_1')),
            '{{STAGE_AL_1}}'         => $x($p('stage_al_1')),
            '{{STAGE_DAL_2}}'        => $x($p('stage_dal_2')),
            '{{STAGE_AL_2}}'         => $x($p('stage_al_2')),
            '{{STAGE_DAL_3}}'        => $x($p('stage_dal_3')),
            '{{STAGE_AL_3}}'         => $x($p('stage_al_3')),
            '{{STAGE_ORE_1}}'        => $x($p('stage_ore_1')),
            '{{STAGE_ORE_2}}'        => $x($p('stage_ore_2')),
            '{{STAGE_ORE_3}}'        => $x($p('stage_ore_3')),
            '{{STAGE_NOTE_DOC_1}}'   => $x($p('stage_note_docente_1')),
            '{{STAGE_NOTE_DOC_2}}'   => $x($p('stage_note_docente_2')),
            '{{STAGE_NOTE_DOC_3}}'   => $x($p('stage_note_docente_3')),
            '{{STAGE_NOTE_TUT_1}}'   => $x($p('stage_note_tutor_1')),
            '{{STAGE_NOTE_TUT_2}}'   => $x($p('stage_note_tutor_2')),
            '{{STAGE_NOTE_TUT_3}}'   => $x($p('stage_note_tutor_3')),
            '{{STAGE_GIUDIZIO}}'     => $x($p('stage_giudizio') ?: '________'),
            '{{STAGE_VAL_TUTOR}}'    => $x($p('stage_valutazione_tutor')) . ' ',
            '{{STAGE_FIRMA_DOC}}'    => $x($p('stage_firma_docente')),
            '{{STAGE_FIRMA_TUT}}'    => $x($p('stage_firma_tutor')),
            // ── Scheda 4 – Relazione ──────────────────────────────────────────
            '{{REL_OGGETTO}}'        => $x($p('relazione_oggetto')),
            '{{REL_DITTA}}'          => $x($p('relazione_ditta')),
            '{{REL_TUTOR}}'          => $x($p('relazione_tutor')),
            '{{REL_VAL_COMM}}'       => $x($p('relazione_valutazione_commissione')),
            '{{REL_GIUDIZIO}}'       => $x($p('relazione_giudizio') ?: '________'),
            '{{REL_PRESIDENTE}}'     => $x($p('relazione_presidente')),
            '{{REL_MEMBRO_1}}'       => $x($p('relazione_membro_1')),
            '{{REL_MEMBRO_2}}'       => $x($p('relazione_membro_2')),
            // ── Scheda 5 – Prova pratica ──────────────────────────────────────
            '{{PRATICA_COMP_1}}'     => $x($p('pratica_competenze_1')),
            '{{PRATICA_ATT_1}}'      => $x($p('pratica_attivita_1')),
            '{{PRATICA_INFO_1}}'     => $x($p('pratica_informazioni_1')),
            '{{PRATICA_PROG_1}}'     => $x($p('pratica_programmazione_1')),
            '{{PRATICA_ESEC_1}}'     => $x($p('pratica_esecuzione_1')),
            '{{PRATICA_CTRL_1}}'     => $x($p('pratica_controllo_1')),
            '{{PRATICA_REG_1}}'      => $x($p('pratica_regolazione_1')),
            '{{PRATICA_COMP_2}}'     => $x($p('pratica_competenze_2')),
            '{{PRATICA_ATT_2}}'      => $x($p('pratica_attivita_2')),
            '{{PRATICA_INFO_2}}'     => $x($p('pratica_informazioni_2')),
            '{{PRATICA_PROG_2}}'     => $x($p('pratica_programmazione_2')),
            '{{PRATICA_ESEC_2}}'     => $x($p('pratica_esecuzione_2')),
            '{{PRATICA_CTRL_2}}'     => $x($p('pratica_controllo_2')),
            '{{PRATICA_REG_2}}'      => $x($p('pratica_regolazione_2')),
            '{{PRATICA_VAL_COMM}}'   => $x($p('pratica_valutazione_commissione')),
            '{{PRATICA_GIUDIZIO}}'   => $x($p('pratica_giudizio') ?: '________'),
            '{{PRATICA_PRESIDENTE}}' => $x($p('pratica_presidente')),
            '{{PRATICA_MEMBRO_1}}'   => $x($p('pratica_membro_1')),
            '{{PRATICA_MEMBRO_2}}'   => $x($p('pratica_membro_2')),
            // ── Scheda 6 – Prova teorica ──────────────────────────────────────
            '{{TEORICA_MAX_A}}'      => $x($p('teorica_max_a') ?: '_______'),
            '{{TEORICA_MAX_B}}'      => $x($p('teorica_max_b') ?: '_______'),
            '{{TEORICA_A_1}}'        => $x($p('teorica_a_1')),
            '{{TEORICA_B_1}}'        => $x($p('teorica_b_1')),
            '{{TEORICA_A_2}}'        => $x($p('teorica_a_2')),
            '{{TEORICA_B_2}}'        => $x($p('teorica_b_2')),
            '{{TEORICA_A_3}}'        => $x($p('teorica_a_3')),
            '{{TEORICA_B_3}}'        => $x($p('teorica_b_3')),
            '{{TEORICA_A_4}}'        => $x($p('teorica_a_4')),
            '{{TEORICA_B_4}}'        => $x($p('teorica_b_4')),
            '{{TEORICA_A_5}}'        => $x($p('teorica_a_5')),
            '{{TEORICA_B_5}}'        => $x($p('teorica_b_5')),
            '{{TEORICA_A_6}}'        => $x($p('teorica_a_6')),
            '{{TEORICA_B_6}}'        => $x($p('teorica_b_6')),
            '{{TEORICA_A_7}}'        => $x($p('teorica_a_7')),
            '{{TEORICA_B_7}}'        => $x($p('teorica_b_7')),
            '{{TEORICA_GIUDIZIO_A}}' => $x($p('teorica_giudizio_a') ?: '_______'),
            '{{TEORICA_GIUDIZIO_B}}' => $x($p('teorica_giudizio_b') ?: '_______'),
            '{{TEORICA_VAL_COMM}}'   => $x($p('teorica_valutazione_commissione')),
            '{{TEORICA_GIUDIZIO}}'   => $x($p('teorica_giudizio') ?: '________'),
            '{{TEORICA_PRESIDENTE}}' => $x($p('teorica_presidente')),
            '{{TEORICA_MEMBRO_1}}'   => $x($p('teorica_membro_1')),
            '{{TEORICA_MEMBRO_2}}'   => $x($p('teorica_membro_2')),
            // ── Tabella materie ───────────────────────────────────────────────
            '{{MAT_0_0}}'            => $x($materieWord[0] ?? ''),
            '{{MAT_1_0}}'            => $x($materieWord[1] ?? ''),
            '{{MAT_2_0}}'            => $x($materieWord[2] ?? ''),
            '{{MAT_3_0}}'            => $x($materieWord[3] ?? ''),
            '{{ASS_0_0}}'            => $x($assenzeWord[0] ?? ''),
            '{{MAT_0_1}}'            => $x($materieWord[4] ?? ''),
            '{{ASS_0_1}}'            => $x($assenzeWord[1] ?? ''),
            '{{MAT_0_2}}'            => $x($materieWord[5] ?? ''),
            '{{ASS_0_2}}'            => $x($assenzeWord[2] ?? ''),
        ];
        $newXml = str_replace(array_keys($phReplace), array_values($phReplace), $newXml);

        // ── Crea il docx in memoria copiando tutti i file del template ──────
        $tmpFile = tempnam(sys_get_temp_dir(), 'scheda_') . '.docx';

        $zipIn  = new ZipArchive();
        $zipOut = new ZipArchive();
        $zipIn->open($template);
        $zipOut->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        for ($i = 0; $i < $zipIn->numFiles; $i++) {
            $entryName = $zipIn->getNameIndex($i);
            if ($entryName === 'word/document.xml') {
                $zipOut->addFromString($entryName, $newXml);
            } else {
                $zipOut->addFromString($entryName, $zipIn->getFromIndex($i));
            }
        }
        $zipIn->close();
        $zipOut->close();

        $filename = 'SCHEDA ALLIEVO' . ($nomeStudente ? ' - ' . $nomeStudente : '') . '.docx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: no-cache');
        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }

    public function pagella(int $id): void {
        $studente = $this->model->findById($id);
        if (!$studente) {
            header('Location: ' . BASE_URL . 'studenti');
            exit;
        }

        $iscrizioni  = $this->model->getIscrizioni($id);
        $annoAttivo  = $this->annoModel->getAttivo();
        $anniAttuali = $annoAttivo ? $this->model->getAnniAttuali($id, $annoAttivo['id']) : [];

        // Filtra per percorso_id se specificato in GET
        $percorsoIdFiltro = (int)($_GET['percorso_id'] ?? 0);

        // Carica l'esame di stato collegato al percorso E all'anno di corso dello studente
        require_once BASE_PATH . 'models/EsameDiStato.php';
        $esameModel = new EsameDiStato();
        $esameDiStato = false;
        if ($percorsoIdFiltro) {
            // Cerca l'anno di corso dello studente per questo percorso nello storico completo
            // (non solo nell'anno attivo globale, perché il percorso può avere un A.S. diverso)
            $percorsoAnnoIdStudente = null;
            $storicoCompleto = $this->model->getStoriaAnni($id);
            foreach ($storicoCompleto as $sa) {
                if ($sa['percorso_id'] == $percorsoIdFiltro) {
                    $percorsoAnnoIdStudente = $sa['percorso_anno_id'];
                    break;
                }
            }
            $esameDiStato = $esameModel->findByPercorso($percorsoIdFiltro, $percorsoAnnoIdStudente);
        }

        // Costruisce un blocco per ogni percorso (o solo quello filtrato)
        // Usa lo storico per trovare il percorso_anno_id anche se l'A.S. non è quello attivo
        $storicoCompleto = $this->model->getStoriaAnni($id);

        $blocci = [];
        foreach ($iscrizioni as $isc) {
            if ($percorsoIdFiltro && $isc['percorso_id'] != $percorsoIdFiltro) continue;

            // Prima cerca nell'anno attivo globale
            $annoC = null;
            foreach ($anniAttuali as $ac) {
                if ($ac['percorso_id'] == $isc['percorso_id']) { $annoC = $ac; break; }
            }

            // Fallback: cerca nello storico completo (per percorsi con A.S. diverso da quello attivo)
            if (!$annoC) {
                foreach ($storicoCompleto as $sa) {
                    if ($sa['percorso_id'] == $isc['percorso_id']) {
                        $annoC = ['percorso_anno_id' => $sa['percorso_anno_id'],
                                  'anno_numero'      => $sa['anno_numero'],
                                  'percorso_id'      => $sa['percorso_id']];
                        break;
                    }
                }
            }

            $blocci[] = [
                'iscrizione'  => $isc,
                'annoCorrente'=> $annoC,
                'materieAnno' => $annoC ? $this->model->getMaterieAnno($annoC['percorso_anno_id'], $id) : [],
            ];
        }

        // Compatibilità con il template pagella esistente (primo percorso)
        $iscrizione   = $iscrizioni[0] ?? false;
        $annoCorrente = $blocci[0]['annoCorrente'] ?? null;
        $materieAnno  = $blocci[0]['materieAnno']  ?? [];

        $page      = 'studenti';
        $pageTitle = 'Scheda di valutazione — ' . $studente['cognome'] . ' ' . $studente['nome'];
        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/studenti/pagella.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── Scarica Pagella Word ─────────────────────────────────────────────────
    public function scaricaPagella(): void {
        $studenteId    = (int)($_GET['studente_id']    ?? 0);
        $percorsoId    = (int)($_GET['percorso_id']    ?? 0);
        $annoId        = (int)($_GET['anno_id']        ?? 0);
        $percorsoAnnoId= (int)($_GET['percorso_anno_id'] ?? 0);

        if (!$studenteId || !$percorsoId) {
            header('Location: ' . BASE_URL . 'studenti'); exit;
        }

        $studente = $this->model->findById($studenteId);
        if (!$studente) {
            header('Location: ' . BASE_URL . 'studenti'); exit;
        }

        // Se percorso_anno_id non passato lo ricavo dallo storico
        if (!$percorsoAnnoId) {
            foreach ($this->model->getStoriaAnni($studenteId) as $sa) {
                if ($sa['percorso_id'] == $percorsoId) {
                    $percorsoAnnoId = (int)$sa['percorso_anno_id'];
                    break;
                }
            }
        }

        // Materie con voti
        $materie = $percorsoAnnoId
            ? $this->model->getMaterieAnno($percorsoAnnoId, $studenteId)
            : [];

        // ── Template ─────────────────────────────────────────────────────────
        $tplPath = BASE_PATH . 'documenti/templates/pagella_originale.docx';
        if (!file_exists($tplPath)) {
            $_SESSION['flash_error'] = 'Template pagella non trovato.';
            header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId); exit;
        }

        // Leggi XML
        $zip = new ZipArchive();
        $zip->open($tplPath);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $x = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_XML1, 'UTF-8');

        // ── Sostituisci nome studente ─────────────────────────────────────────
        // Nel template compare come due run separati "CIOBANU" e "SILVIA"
        // Li unifichiamo in un singolo testo COGNOME NOME
        $cognome = strtoupper(trim($studente['cognome'] ?? ''));
        $nome    = strtoupper(trim($studente['nome']    ?? ''));
        $nomeCompleto = $x($cognome . ' ' . $nome);

        // Sostituisce il blocco dei due run con un unico run col nome corretto
        $xml = preg_replace(
            '/<w:t>CIOBANU<\/w:t>(<\/w:r><w:r>[^<]*<\/w:r>)*<\/w:r><w:r>[^<]*<\/w:r><w:r>[^<]*<w:t[^>]*>SILVIA<\/w:t><\/w:r>/',
            '<w:t>' . $nomeCompleto . '</w:t></w:r>',
            $xml
        );
        // Fallback semplice se il pattern non combacia
        if (strpos($xml, 'CIOBANU') !== false) {
            $xml = str_replace('<w:t>CIOBANU</w:t>', '<w:t>' . $x($cognome) . '</w:t>', $xml);
            $xml = str_replace('<w:t>SILVIA</w:t>',  '<w:t>' . $x($nome)    . '</w:t>', $xml);
        }

        // ── Inietta voti nella tabella materie ───────────────────────────────
        // Per ogni materia presente nel documento cerca il nome nella cella sinistra
        // e inietta la media dei voti nella cella destra (vuota)
        foreach ($materie as $mat) {
            $votiValori = array_map(fn($v) => (float)$v['voto'], $mat['voti'] ?? []);
            if (empty($votiValori)) continue;
            $media = round(array_sum($votiValori) / count($votiValori), 2);
            $mediaStr = $x(number_format($media, 2, ',', ''));

            // Cerca il testo della materia (può essere spezzato in più run)
            // Usa le prime 6 lettere come anchor sicuro
            $anchor = substr(strtoupper($mat['nome']), 0, 6);
            $pos = stripos($xml, $anchor);
            if ($pos === false) continue;

            // Trova la fine della riga </w:tr> dopo l'anchor
            $trEnd = strpos($xml, '</w:tr>', $pos);
            if ($trEnd === false) continue;

            $rowXml = substr($xml, $pos, $trEnd - $pos + strlen('</w:tr>'));

            // Nella cella destra (ultima <w:tc>) inietta la media
            // La cella destra ha un <w:p>...<w:pPr>...</w:pPr></w:p> vuoto
            $newRow = preg_replace(
                '/(<w:pPr>[^<]*(?:<[^\/][^>]*\/?>(?!w:pPr)[^<]*)*<\/w:pPr>)(<\/w:p>)(?=<\/w:tc><\/w:tr>)/',
                '$1<w:r><w:rPr><w:rFonts w:ascii="Arial"/><w:sz w:val="24"/></w:rPr>'
                . '<w:t>' . $mediaStr . '</w:t></w:r>$2',
                $rowXml, 1
            );

            if ($newRow !== $rowXml) {
                $xml = substr($xml, 0, $pos) . $newRow . substr($xml, $trEnd + strlen('</w:tr>'));
            }
        }

        // ── Scrivi e servi ────────────────────────────────────────────────────
        $tmpPath = sys_get_temp_dir() . '/pagella_' . $studenteId . '_' . time() . '.docx';
        copy($tplPath, $tmpPath);

        $zipOut = new ZipArchive();
        $zipOut->open($tmpPath, ZipArchive::CREATE);
        $zipOut->addFromString('word/document.xml', $xml);
        $zipOut->close();

        $filename = 'Pagella_' . $cognome . '_' . $nome . '.docx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpPath));
        header('Cache-Control: no-cache');
        readfile($tmpPath);
        unlink($tmpPath);
        exit;
    }

    public function setIscrizione(): void {
        $this->addIscrizione();
    }

    public function addIscrizione(): void {
        $studenteId = (int)($_POST['studente_id'] ?? 0);
        $percorsoId = (int)($_POST['percorso_id'] ?? 0);
        $isAjax     = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($studenteId > 0 && $percorsoId > 0) {
            $this->model->addIscrizione($studenteId, $percorsoId, $_POST['data_inizio'] ?? '');

            // Auto-assegna il 1° anno del percorso e aggiunge voce allo storico
            $anni         = $this->percorsoModel->getAnni($percorsoId);
            $percorsoInfo = $this->percorsoModel->findById($percorsoId);
            // Usa l'anno scolastico del percorso (non quello attivo globalmente)
            $annoScolId   = $percorsoInfo['anno_scolastico_id'] ?? null;
            // Fallback: anno attivo globale se il percorso non ha un anno scolastico
            if (!$annoScolId) {
                $annoAttivo = $this->annoModel->getAttivo();
                $annoScolId = $annoAttivo['id'] ?? null;
            }
            if (!empty($anni) && $annoScolId) {
                $primoAnno = $anni[0];
                $this->model->setAnno($studenteId, $primoAnno['id'], $annoScolId);
            }

            if ($isAjax) {
                // Restituisce i dati del percorso appena aggiunto
                $percorso = $this->percorsoModel->findById($percorsoId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success'    => true,
                    'percorso_id'   => $percorsoId,
                    'percorso_nome' => $percorso['nome'] ?? '',
                    'anno_label'    => $percorso['anno_label'] ?? '',
                    'anno_scolastico_id' => $percorso['anno_scolastico_id'] ?? 0,
                    'anni'          => $anni,
                ]);
                exit;
            }
            $_SESSION['flash_success'] = 'Iscrizione aggiunta.';
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
                exit;
            }
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }

    public function deleteIscrizione(): void {
        $studenteId = (int)($_POST['studente_id'] ?? 0);
        $percorsoId = (int)($_POST['percorso_id'] ?? 0);
        $isAjax     = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($studenteId > 0 && $percorsoId > 0) {
            $this->model->deleteIscrizione($studenteId, $percorsoId);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            $_SESSION['flash_success'] = 'Iscrizione rimossa.';
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }

    public function setAnno(): void {
        $studenteId       = (int)($_POST['studente_id'] ?? 0);
        $percorsoAnnoId   = (int)($_POST['percorso_anno_id'] ?? 0);
        $annoScolasticoId = (int)($_POST['anno_scolastico_id'] ?? 0);
        $isAjax           = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($studenteId > 0 && $percorsoAnnoId > 0 && $annoScolasticoId > 0) {
            $this->model->setAnno($studenteId, $percorsoAnnoId, $annoScolasticoId);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            $_SESSION['flash_success'] = 'Anno di corso aggiornato.';
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
                exit;
            }
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }

    public function addBonus(): void {
        $studenteId       = (int)($_POST['studente_id'] ?? 0);
        $materiaId        = (int)($_POST['materia_id'] ?? 0);
        $annoScolasticoId = (int)($_POST['anno_scolastico_id'] ?? 0);

        if ($studenteId > 0 && $materiaId > 0 && $annoScolasticoId > 0) {
            $this->model->addBonus($studenteId, $materiaId, $annoScolasticoId, $_POST['note'] ?? '');
            $_SESSION['flash_success'] = 'Materia bonus aggiunta.';
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }

    public function addVoto(): void {
        $studenteId = (int)($_POST['studente_id'] ?? 0);
        $pamId      = (int)($_POST['pam_id'] ?? 0);
        $voto       = (float)str_replace(',', '.', $_POST['voto'] ?? '');
        $tipo       = $_POST['tipo'] ?? 'finale';
        $isAjax     = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($studenteId > 0 && $pamId > 0 && $voto >= 0 && $voto <= 10) {
            $id = $this->model->addVoto($studenteId, $pamId, $voto, $tipo, $_POST['data'] ?? '', $_POST['note'] ?? '');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $id, 'voto' => $voto, 'tipo' => $tipo]);
                exit;
            }
            $_SESSION['flash_success'] = 'Voto aggiunto.';
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Voto non valido (deve essere tra 0 e 10).']);
                exit;
            }
            $_SESSION['flash_error'] = 'Voto non valido (deve essere tra 0 e 10).';
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }

    public function deleteVoto(): void {
        $id         = (int)($_POST['id'] ?? 0);
        $studenteId = (int)($_POST['studente_id'] ?? 0);
        $isAjax     = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if ($id > 0) {
            $this->model->deleteVoto($id);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $id]);
                exit;
            }
            $_SESSION['flash_success'] = 'Voto rimosso.';
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'ID non valido.']);
                exit;
            }
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }

    public function deleteBonus(): void {
        $id         = (int)($_POST['id'] ?? 0);
        $studenteId = (int)($_POST['studente_id'] ?? 0);
        if ($id > 0) {
            $this->model->deleteBonus($id);
            $_SESSION['flash_success'] = 'Materia bonus rimossa.';
        }
        header('Location: ' . BASE_URL . 'studenti/detail/' . $studenteId);
        exit;
    }
}
