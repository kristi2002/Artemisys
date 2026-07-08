<?php

require_once BASE_PATH . 'models/Lezione.php';

class ImportazioniController {

    private Lezione $model;

    public function __construct() {
        $this->model = new Lezione();
        $this->model->createTables();
    }

    public function index() {
        $page      = 'importazioni';
        $pageTitle = 'Importazioni';
        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/importazioni/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    public function importLezioni(): void {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['righe'])) {
            echo json_encode(['ok' => false, 'errore' => 'Nessun dato ricevuto.']);
            exit;
        }

        $righe     = $input['righe'];
        $importate = 0;
        $errori    = [];

        foreach ($righe as $i => $r) {
            $numRiga = $i + 2; // riga 1 = header

            $percorso = trim($r['percorso'] ?? '');
            $anno     = (int)($r['anno'] ?? 0);
            $codice   = trim($r['codice_materia'] ?? '');
            $titolo   = trim($r['titolo'] ?? '');

            if ($percorso === '' || $anno <= 0 || $codice === '' || $titolo === '') {
                $errori[] = "Riga {$numRiga}: campi obbligatori mancanti (Percorso, Anno, Codice Materia, Titolo).";
                continue;
            }

            $pamId = $this->model->findPamId($percorso, $anno, $codice);
            if (!$pamId) {
                $errori[] = "Riga {$numRiga}: combinazione non trovata — Percorso \"{$percorso}\", Anno {$anno}, Materia \"{$codice}\".";
                continue;
            }

            $dataStr = trim($r['data'] ?? '');
            $dataDb  = null;
            if ($dataStr !== '') {
                $dataDb = $this->parseData($dataStr);
                if (!$dataDb) {
                    $errori[] = "Riga {$numRiga}: formato data non valido \"{$dataStr}\". Usa gg/mm/aaaa.";
                    continue;
                }
            }

            $onlineRaw = strtolower(trim($r['online'] ?? ''));
            $online    = in_array($onlineRaw, ['si', 'sì', 'yes', '1', 'true', 'online']) ? 1 : 0;

            $this->model->create([
                'percorso_anno_materia_id' => $pamId,
                'titolo'                   => $titolo,
                'data'                     => $dataDb,
                'durata_minuti'            => ($r['durata'] ?? '') !== '' ? (int)$r['durata'] : null,
                'note'                     => trim($r['note'] ?? '') ?: null,
                'online'                   => $online,
                'link_online'              => trim($r['link_online'] ?? '') ?: null,
                'argomento'                => trim($r['argomento'] ?? '') ?: null,
            ]);

            $importate++;
        }

        echo json_encode([
            'ok'        => true,
            'importate' => $importate,
            'errori'    => $errori,
            'totale'    => count($righe),
        ]);
        exit;
    }

    private function parseData(string $val): ?string {
        // gg/mm/aaaa o gg-mm-aaaa
        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        // aaaa-mm-gg (già ISO)
        if (preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }
        // Excel serial number (giorni dal 1/1/1900)
        if (is_numeric($val) && (int)$val > 30000 && (int)$val < 60000) {
            $unix = ((int)$val - 25569) * 86400;
            return date('Y-m-d', $unix);
        }
        return null;
    }
}
