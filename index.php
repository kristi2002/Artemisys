<?php
// In produzione lascia APP_DEBUG non impostato (o = 0): niente errori a schermo.
if (getenv('APP_DEBUG') === '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

session_start();

define('BASE_PATH', __DIR__ . '/');

// URL auto-configurati in base alla cartella di installazione
$baseDir  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$baseRoot = $baseDir === '' ? '/' : $baseDir . '/';

// ASSETS_URL → per file statici (CSS, JS, immagini, uploads)
//   es. /artemisys/public/css/style.css
define('ASSETS_URL', $baseRoot);

// BASE_URL → per le route MVC, usa PATH_INFO (funziona senza mod_rewrite)
//   es. /artemisys/index.php/auth/login
// Se in futuro attivi mod_rewrite, basta rimuovere "index.php/" qui sotto.
define('BASE_URL', $baseRoot . 'index.php/');

require_once BASE_PATH . 'config/database.php';

// Leggi URL da $_GET['url'] (rewrite mode) oppure da PATH_INFO (fallback)
$url = '';
if (!empty($_GET['url'])) {
    $url = $_GET['url'];
} elseif (!empty($_SERVER['PATH_INFO'])) {
    $url = ltrim($_SERVER['PATH_INFO'], '/');
}
$url   = rtrim($url, '/');
$url   = filter_var($url, FILTER_SANITIZE_URL);
$parts = explode('/', $url);

$controller = !empty($parts[0]) ? $parts[0] : 'switch';
$action = isset($parts[1]) ? $parts[1] : 'index';
$param = isset($parts[2]) ? $parts[2] : null;

// Se non loggato, forza login
if (!isset($_SESSION['user_id']) && $controller !== 'auth') {
    header('Location: ' . BASE_URL . 'auth/login');
    exit;
}

// Se loggato e va su auth/login, redirect
if (isset($_SESSION['user_id']) && $controller === 'auth' && $action === 'login') {
    $r = $_SESSION['user_ruolo'] ?? '';
    $dest = match($r) {
        'docente'  => 'mie-lezioni',
        'studente' => 'studente',
        default    => 'home',
    };
    header('Location: ' . BASE_URL . $dest);
    exit;
}

// Se docente entra su switch, mandalo direttamente alla sua home
if (isset($_SESSION['user_id']) && ($_SESSION['user_ruolo'] ?? '') === 'docente'
    && $controller === 'switch') {
    header('Location: ' . BASE_URL . 'mie-lezioni');
    exit;
}

// Blocca i docenti dalle pagine riservate all'admin
if (isset($_SESSION['user_id']) && ($_SESSION['user_ruolo'] ?? '') === 'docente') {
    $bloccatePerDocente = [
        'insegnanti', 'studenti', 'assegna-studenti',
        'anno-scolastico', 'materie', 'importazioni',
        'rette', 'diplomi', 'stage', 'calendario-presenze',
        'esami-di-stato-prova', 'tutti-gli-esami-di-stato', 'home',
    ];
    if (in_array($controller, $bloccatePerDocente, true)) {
        header('Location: ' . BASE_URL . 'mie-lezioni');
        exit;
    }
}

// Blocca gli studenti dalle pagine riservate
if (isset($_SESSION['user_id']) && ($_SESSION['user_ruolo'] ?? '') === 'studente') {
    $consentitePerStudente = ['studente', 'bacheca', 'auth'];
    if (!in_array($controller, $consentitePerStudente, true)) {
        header('Location: ' . BASE_URL . 'studente');
        exit;
    }
}

// Lo studente che entra su switch va alla sua home
if (isset($_SESSION['user_id']) && ($_SESSION['user_ruolo'] ?? '') === 'studente'
    && $controller === 'switch') {
    header('Location: ' . BASE_URL . 'studente');
    exit;
}

// Routing
switch ($controller) {

    // ===== HOME ADMIN (Dashboard) =====
    case 'home':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/DashboardController.php';
        $ctrl = new DashboardController();
        $ctrl->index();
        break;

    // ===== BACHECA (tutti i ruoli) =====
    case 'bacheca':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/BachecaController.php';
        $ctrl = new BachecaController();
        if ($action === 'store')                $ctrl->store();
        elseif ($action === 'detail' && $param) $ctrl->detail((int)$param);
        elseif ($action === 'delete')           $ctrl->delete();
        else                                    $ctrl->index();
        break;

    // ===== AREA STUDENTE =====
    case 'studente':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/studente/StudenteController.php';
        $ctrl = new StudenteController();
        if ($action === 'lezioni')        $ctrl->lezioni();
        elseif ($action === 'voti')       $ctrl->voti();
        elseif ($action === 'presenze')   $ctrl->presenze();
        elseif ($action === 'eventi')     $ctrl->eventi();
        elseif ($action === 'iscriviti')  $ctrl->iscriviti();
        elseif ($action === 'rette')      $ctrl->rette();
        elseif ($action === 'profilo')    $ctrl->profilo();
        else                              $ctrl->home();
        break;

    // ===== AREA DOCENTE =====
    case 'mie-lezioni':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/docente/DocenteController.php';
        $ctrl = new DocenteController();
        $ctrl->lezioni();
        break;

    case 'mie-materie':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/docente/DocenteController.php';
        $ctrl = new DocenteController();
        $ctrl->materie();
        break;

    case 'mio-calendario':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/docente/DocenteController.php';
        $ctrl = new DocenteController();
        $ctrl->calendario();
        break;

    case 'miei-esami':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/docente/DocenteController.php';
        $ctrl = new DocenteController();
        $ctrl->esami();
        break;

    case 'profilo':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/docente/DocenteController.php';
        $ctrl = new DocenteController();
        $ctrl->profilo();
        break;

    // ===== AUTH =====
    case 'auth':
        require_once BASE_PATH . 'controllers/AuthController.php';
        $ctrl = new AuthController();
        if ($action === 'login') $ctrl->login();
        elseif ($action === 'logout') $ctrl->logout();
        // Recupero password: raggiungibili senza sessione (il controller 'auth'
        // è già escluso dal blocco di login qui sopra).
        elseif ($action === 'password-dimenticata')  $ctrl->passwordDimenticata();
        elseif ($action === 'reimposta-password')    $ctrl->reimpostaPassword();
        break;

    // ===== SWITCH MODULI =====
    case 'switch':
        require_once BASE_PATH . 'controllers/SwitchController.php';
        $ctrl = new SwitchController();
        $ctrl->index();
        break;

    // =============================================
    // MODULO DIDATTICA
    // =============================================
    case 'insegnanti':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/InsegnantiController.php';
        $ctrl = new InsegnantiController();
        if ($action === 'create')                 $ctrl->create();
        elseif ($action === 'store')              $ctrl->store();
        elseif ($action === 'detail' && $param)   $ctrl->detail($param);
        elseif ($action === 'update')             $ctrl->update();
        elseif ($action === 'toggle-status')      $ctrl->toggleStatus();
        elseif ($action === 'upload-foto')        $ctrl->uploadFoto();
        else                                      $ctrl->index();
        break;

    case 'studenti':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/StudentiController.php';
        $ctrl = new StudentiController();
        if ($action === 'create')                   $ctrl->create();
        elseif ($action === 'store')                $ctrl->store();
        elseif ($action === 'detail' && $param)     $ctrl->detail((int)$param);
        elseif ($action === 'pagella' && $param)    $ctrl->pagella((int)$param);
        elseif ($action === 'download-scheda-allievo') $ctrl->downloadSchedaAllievo();
        elseif ($action === 'scarica-pagella')          $ctrl->scaricaPagella();
        elseif ($action === 'update')               $ctrl->update();
        elseif ($action === 'set-iscrizione')       $ctrl->setIscrizione();
        elseif ($action === 'add-iscrizione')       $ctrl->addIscrizione();
        elseif ($action === 'delete-iscrizione')    $ctrl->deleteIscrizione();
        elseif ($action === 'delete-anno-storico')  $ctrl->deleteAnnoStorico();
        elseif ($action === 'update-anno-storico')  $ctrl->updateAnnoStorico();
        elseif ($action === 'set-anno')             $ctrl->setAnno();
        elseif ($action === 'add-bonus')            $ctrl->addBonus();
        elseif ($action === 'delete-bonus')         $ctrl->deleteBonus();
        elseif ($action === 'add-voto')             $ctrl->addVoto();
        elseif ($action === 'delete-voto')          $ctrl->deleteVoto();
        else                                        $ctrl->index();
        break;

    case 'anno-scolastico':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/AnnoScolasticoController.php';
        $ctrl = new AnnoScolasticoController();
        if ($action === 'store')          $ctrl->store();
        elseif ($action === 'attivo')     $ctrl->setAttivo();
        elseif ($action === 'disattiva')  $ctrl->disattiva();
        elseif ($action === 'delete')     $ctrl->delete();
        else                              $ctrl->index();
        break;

    case 'materie':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/MaterieController.php';
        $ctrl = new MaterieController();
        if ($action === 'store')      $ctrl->store();
        elseif ($action === 'delete') $ctrl->delete();
        else                          $ctrl->index();
        break;

    case 'sedi':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/SediController.php';
        $ctrl = new SediController();
        if ($action === 'store')      $ctrl->store();
        elseif ($action === 'delete') $ctrl->delete();
        else                          $ctrl->index();
        break;

    case 'percorsi':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/PercorsiController.php';
        $ctrl = new PercorsiController();
        if ($action === 'store')                  $ctrl->store();
        elseif ($action === 'create')            $ctrl->create();
        elseif ($action === 'detail' && $param)  $ctrl->detail((int)$param);
        elseif ($action === 'anno' && $param)    $ctrl->annoDetail((int)$param);
        elseif ($action === 'materia' && $param) $ctrl->materiaDetail((int)$param);
        elseif ($action === 'add-anno')          $ctrl->addAnno();
        elseif ($action === 'delete-anno')       $ctrl->deleteAnno();
        elseif ($action === 'update-anno-codice')$ctrl->updateAnnoCodice();
        elseif ($action === 'add-materia')       $ctrl->addMateria();
        elseif ($action === 'delete-materia')    $ctrl->deleteMateria();
        elseif ($action === 'add-insegnante')    $ctrl->addInsegnante();
        elseif ($action === 'remove-insegnante') $ctrl->removeInsegnante();
        elseif ($action === 'add-argomento')     $ctrl->addArgomento();
        elseif ($action === 'delete-argomento')  $ctrl->deleteArgomento();
        elseif ($action === 'add-lezione')       $ctrl->addLezione();
        elseif ($action === 'delete-lezione')    $ctrl->deleteLezione();
        elseif ($action === 'lezione' && $param)        $ctrl->lezioneDetail((int)$param);
        elseif ($action === 'set-presenza')             $ctrl->setPresenza();
        elseif ($action === 'add-lezione-insegnante')   $ctrl->addLezioneInsegnante();
        elseif ($action === 'remove-lezione-insegnante')$ctrl->removeLezioneInsegnante();
        elseif ($action === 'update-lezione-dettagli')  $ctrl->updateLezioneDettagli();
        elseif ($action === 'toggle-online')            $ctrl->toggleOnline();
        elseif ($action === 'upload-allegato')          $ctrl->uploadAllegato();
        elseif ($action === 'delete-allegato')          $ctrl->deleteAllegato();
        elseif ($action === 'update')            $ctrl->update();
        elseif ($action === 'delete')            $ctrl->delete();
        elseif ($action === 'scarica-scrutinio') $ctrl->scaricaScrutinio();
        else                                     $ctrl->index();
        break;

    case 'assegna-studenti':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/AssegnaStudentiController.php';
        $ctrl = new AssegnaStudentiController();
        if ($action === 'store') $ctrl->store();
        else                     $ctrl->index();
        break;

    case 'lezioni':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/LezioniController.php';
        $ctrl = new LezioniController();
        $ctrl->index();
        break;

    case 'calendario-presenze':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/CalendarioController.php';
        $ctrl = new CalendarioController();
        $ctrl->index();
        break;

    case 'esami':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/EsamiController.php';
        $ctrl = new EsamiController();
        if ($action === 'store')                  $ctrl->store();
        elseif ($action === 'detail' && $param)   $ctrl->detail((int)$param);
        elseif ($action === 'saveVoti')           $ctrl->saveVoti();
        elseif ($action === 'add-supervisore')    $ctrl->addSupervisore();
        elseif ($action === 'remove-supervisore') $ctrl->removeSupervisore();
        elseif ($action === 'upload-allegato')    $ctrl->uploadAllegato();
        elseif ($action === 'delete-allegato')    $ctrl->deleteAllegato();
        elseif ($action === 'delete')             $ctrl->delete();
        else                                      $ctrl->index();
        break;

    case 'tutti-gli-esami-di-stato':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/TuttiGliEsamiDiStatoController.php';
        $ctrl = new TuttiGliEsamiDiStatoController();
        if ($action === 'delete') $ctrl->delete();
        else                      $ctrl->index();
        break;

    case 'esami-di-stato-prova':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/EsamiDiStatoProvaController.php';
        $ctrl = new EsamiDiStatoProvaController();
        if ($action === 'store')                       $ctrl->store();
        elseif ($action === 'detail' && $param)        $ctrl->detail((int)$param);
        elseif ($action === 'iscrivi')                 $ctrl->iscrivi();
        elseif ($action === 'disiscrivi')              $ctrl->disiscrivi();
        elseif ($action === 'saveEsiti')               $ctrl->saveEsiti();
        elseif ($action === 'save-documento')          $ctrl->saveDocumento();
        elseif ($action === 'stampa')                  $ctrl->stampa();
        elseif ($action === 'add-commissario')         $ctrl->addCommissario();
        elseif ($action === 'remove-commissario')      $ctrl->removeCommissario();
        elseif ($action === 'add-commissario-gen')     $ctrl->addCommissarioGenerale();
        elseif ($action === 'remove-commissario-gen')  $ctrl->removeCommissarioGenerale();
        elseif ($action === 'upload-allegato')         $ctrl->uploadAllegato();
        elseif ($action === 'delete-allegato')         $ctrl->deleteAllegato();
        elseif ($action === 'scarica-verbale')           $ctrl->scaricaVerbale();
        elseif ($action === 'scarica-foglio-presenze')   $ctrl->scaricaFoglioPresenze();
        elseif ($action === 'update')                  $ctrl->update();
        elseif ($action === 'delete')                  $ctrl->delete();
        else                                           $ctrl->index();
        break;

    case 'rette':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/RetteController.php';
        $ctrl = new RetteController();
        if ($action === 'store')                $ctrl->store();
        elseif ($action === 'detail' && $param) $ctrl->detail((int)$param);
        elseif ($action === 'pay')              $ctrl->pay();
        elseif ($action === 'unpay')            $ctrl->unpay();
        elseif ($action === 'uploadFile')       $ctrl->uploadFile();
        elseif ($action === 'deleteFile')       $ctrl->deleteFile();
        elseif ($action === 'delete')           $ctrl->delete();
        else                                    $ctrl->index();
        break;

    case 'diplomi':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/DiplomiController.php';
        $ctrl = new DiplomiController();
        if ($action === 'save')              $ctrl->save();
        elseif ($action === 'uploadImage')   $ctrl->uploadImage();
        elseif ($action === 'removeImage')   $ctrl->removeImage();
        else                                 $ctrl->index();
        break;

    case 'eventi':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/EventiController.php';
        $ctrl = new EventiController();
        if ($action === 'store')                $ctrl->store();
        elseif ($action === 'detail' && $param) $ctrl->detail((int)$param);
        elseif ($action === 'update')           $ctrl->update();
        elseif ($action === 'setStato')         $ctrl->setStato();
        elseif ($action === 'iscrivi')          $ctrl->iscrivi();
        elseif ($action === 'disiscrivi')       $ctrl->disiscrivi();
        elseif ($action === 'setPresenza')      $ctrl->setPresenza();
        elseif ($action === 'delete')           $ctrl->delete();
        else                                    $ctrl->index();
        break;

    case 'stage':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/StageController.php';
        $ctrl = new StageController();
        if ($action === 'store')                  $ctrl->store();
        elseif ($action === 'detail' && $param)   $ctrl->detail((int)$param);
        elseif ($action === 'update')             $ctrl->update();
        elseif ($action === 'nextStep')           $ctrl->nextStep();
        elseif ($action === 'prevStep')           $ctrl->prevStep();
        elseif ($action === 'upload-allegato')    $ctrl->uploadAllegato();
        elseif ($action === 'delete-allegato')    $ctrl->deleteAllegato();
        elseif ($action === 'delete')             $ctrl->delete();
        else                                      $ctrl->index();
        break;

    case 'importazioni':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/ImportazioniController.php';
        $ctrl = new ImportazioniController();
        if ($action === 'import-lezioni') $ctrl->importLezioni();
        else                              $ctrl->index();
        break;

    case 'prove-orari':
        $_SESSION['current_module'] = 'didattica';
        require_once BASE_PATH . 'controllers/didattica/ProveOrariController.php';
        $ctrl = new ProveOrariController();
        $ctrl->index();
        break;

    // =============================================
    // MODULO ORIENTAMENTO (coming soon)
    // =============================================
    case 'orientamento':
        $_SESSION['current_module'] = 'orientamento';
        header('Location: ' . BASE_URL . 'switch');
        exit;

    default:
        header('Location: ' . BASE_URL . 'switch');
        exit;
}
