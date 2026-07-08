<?php

class StudenteController {

    private $db;
    private ?array $studente;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();

        // Garantisci colonna user_id su studenti
        try { $this->db->exec("ALTER TABLE studenti ADD COLUMN user_id INT NULL"); } catch (Exception $e) {}
        try { $this->db->exec("CREATE INDEX idx_studenti_user ON studenti(user_id)"); } catch (Exception $e) {}

        $this->studente = $this->loadStudente();
    }

    private function loadStudente(): ?array {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) return null;
        $stmt = $this->db->prepare("SELECT * FROM studenti WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function guard(): void {
        if (!$this->studente) {
            $_SESSION['flash_error'] = 'Profilo studente non collegato.';
            header('Location: ' . BASE_URL . 'auth/logout');
            exit;
        }
    }

    private function getCurrentAnnoId(): ?int {
        $stmt = $this->db->prepare("
            SELECT percorso_anno_id FROM studente_anni
            WHERE studente_id = ?
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$this->studente['id']]);
        $r = $stmt->fetchColumn();
        return $r ? (int)$r : null;
    }

    // ── Home ─────────────────────────────────────────────────────────────────
    public function home(): void {
        $this->guard();
        $page = 'studente-home';
        $studente = $this->studente;
        $annoId = $this->getCurrentAnnoId();

        // Prossime lezioni
        $prossLezioni = [];
        $voti = [];
        $stats = ['lezioni_tot'=>0,'presenze'=>0,'voti_count'=>0,'media'=>0];

        if ($annoId) {
            $stmt = $this->db->prepare("
                SELECT l.id, l.titolo, l.data, l.online, l.link_online,
                       m.nome AS materia_nome
                FROM lezioni l
                JOIN percorso_anno_materie pam ON pam.id = l.percorso_anno_materia_id
                JOIN materie m                ON m.id  = pam.materia_id
                WHERE pam.anno_id = ?
                  AND l.data IS NOT NULL AND l.data >= CURDATE()
                ORDER BY l.data ASC
                LIMIT 4
            ");
            $stmt->execute([$annoId]);
            $prossLezioni = $stmt->fetchAll();

            // Statistiche presenze
            $stmt = $this->db->prepare("
                SELECT
                    (SELECT COUNT(*) FROM lezioni l
                       JOIN percorso_anno_materie pam ON pam.id=l.percorso_anno_materia_id
                       WHERE pam.anno_id=? AND l.data IS NOT NULL AND l.data <= CURDATE()) AS lez_tot,
                    (SELECT COUNT(*) FROM lezione_presenze lp
                       JOIN lezioni l ON l.id=lp.lezione_id
                       JOIN percorso_anno_materie pam ON pam.id=l.percorso_anno_materia_id
                       WHERE pam.anno_id=? AND lp.studente_id=? AND lp.presente=1) AS presenti
            ");
            $stmt->execute([$annoId, $annoId, $studente['id']]);
            $r = $stmt->fetch();
            $stats['lezioni_tot'] = (int)$r['lez_tot'];
            $stats['presenze']    = (int)$r['presenti'];
        }

        // Voti
        $stmt = $this->db->prepare("
            SELECT ei.voto, ei.assente, e.titolo, e.data, e.tipo,
                   m.nome AS materia_nome
            FROM esame_iscrizioni ei
            JOIN esami e ON e.id = ei.esame_id
            JOIN percorso_anno_materie pam ON pam.id = e.percorso_anno_materia_id
            JOIN materie m ON m.id = pam.materia_id
            WHERE ei.studente_id = ? AND ei.voto IS NOT NULL
            ORDER BY e.data DESC
            LIMIT 5
        ");
        $stmt->execute([$studente['id']]);
        $voti = $stmt->fetchAll();
        $stats['voti_count'] = count($voti);
        if ($voti) {
            $stats['media'] = round(array_sum(array_column($voti, 'voto')) / count($voti), 2);
        }

        // Bacheca recente
        require_once BASE_PATH . 'models/Comunicazione.php';
        $comModel = new Comunicazione();
        $comModel->createTables();
        $bachecaCount = $comModel->count('studenti');
        $stmt = $this->db->query("
            SELECT * FROM comunicazioni
            WHERE pubblicata = 1 AND (target = 'tutti' OR target = 'studenti')
            ORDER BY created_at DESC LIMIT 3
        ");
        $bacheca = $stmt->fetchAll();

        require BASE_PATH . 'views/studente/_header.php';
        require BASE_PATH . 'views/studente/home.php';
        require BASE_PATH . 'views/studente/_footer.php';
    }

    public function lezioni(): void {
        $this->guard();
        $page = 'studente-lezioni';
        $studente = $this->studente;
        $annoId = $this->getCurrentAnnoId();
        $lezioni = [];

        if ($annoId) {
            $stmt = $this->db->prepare("
                SELECT l.*, m.nome AS materia_nome,
                       lp.presente AS mia_presenza
                FROM lezioni l
                JOIN percorso_anno_materie pam ON pam.id = l.percorso_anno_materia_id
                JOIN materie m                ON m.id  = pam.materia_id
                LEFT JOIN lezione_presenze lp ON lp.lezione_id = l.id AND lp.studente_id = ?
                WHERE pam.anno_id = ? AND l.data IS NOT NULL
                ORDER BY l.data DESC
            ");
            $stmt->execute([$studente['id'], $annoId]);
            $lezioni = $stmt->fetchAll();
        }

        require BASE_PATH . 'views/studente/_header.php';
        require BASE_PATH . 'views/studente/lezioni.php';
        require BASE_PATH . 'views/studente/_footer.php';
    }

    public function voti(): void {
        $this->guard();
        $page = 'studente-voti';
        $studente = $this->studente;

        $stmt = $this->db->prepare("
            SELECT ei.*, e.titolo, e.data, e.tipo,
                   m.nome AS materia_nome
            FROM esame_iscrizioni ei
            JOIN esami e ON e.id = ei.esame_id
            JOIN percorso_anno_materie pam ON pam.id = e.percorso_anno_materia_id
            JOIN materie m ON m.id = pam.materia_id
            WHERE ei.studente_id = ?
            ORDER BY e.data DESC, e.created_at DESC
        ");
        $stmt->execute([$studente['id']]);
        $voti = $stmt->fetchAll();

        // Raggruppa per materia
        $perMateria = [];
        foreach ($voti as $v) {
            $perMateria[$v['materia_nome']][] = $v;
        }

        require BASE_PATH . 'views/studente/_header.php';
        require BASE_PATH . 'views/studente/voti.php';
        require BASE_PATH . 'views/studente/_footer.php';
    }

    public function presenze(): void {
        $this->guard();
        $page = 'studente-presenze';
        $studente = $this->studente;
        $annoId = $this->getCurrentAnnoId();
        $perMateria = [];

        if ($annoId) {
            $stmt = $this->db->prepare("
                SELECT m.nome AS materia_nome,
                       COUNT(l.id) AS tot_lezioni,
                       COALESCE(SUM(CASE WHEN lp.presente = 1 THEN 1 ELSE 0 END), 0) AS presenze_count
                FROM percorso_anno_materie pam
                JOIN materie m ON m.id = pam.materia_id
                LEFT JOIN lezioni l ON l.percorso_anno_materia_id = pam.id
                                    AND l.data IS NOT NULL AND l.data <= CURDATE()
                LEFT JOIN lezione_presenze lp ON lp.lezione_id = l.id AND lp.studente_id = ?
                WHERE pam.anno_id = ?
                GROUP BY pam.id
                ORDER BY m.nome ASC
            ");
            $stmt->execute([$studente['id'], $annoId]);
            $perMateria = $stmt->fetchAll();
        }

        require BASE_PATH . 'views/studente/_header.php';
        require BASE_PATH . 'views/studente/presenze.php';
        require BASE_PATH . 'views/studente/_footer.php';
    }

    public function eventi(): void {
        $this->guard();
        $page = 'studente-eventi';
        $studente = $this->studente;

        $stmt = $this->db->prepare("
            SELECT e.*,
                   COUNT(DISTINCT ei.id) AS num_iscritti,
                   MAX(CASE WHEN ei.studente_id = ? THEN 1 ELSE 0 END) AS sono_iscritto
            FROM eventi e
            LEFT JOIN evento_iscrizioni ei ON ei.evento_id = e.id
            WHERE e.stato IN ('aperto','chiuso','svolto')
            GROUP BY e.id
            ORDER BY e.data_evento DESC
            LIMIT 30
        ");
        $stmt->execute([$studente['id']]);
        $eventi = $stmt->fetchAll();

        require BASE_PATH . 'views/studente/_header.php';
        require BASE_PATH . 'views/studente/eventi.php';
        require BASE_PATH . 'views/studente/_footer.php';
    }

    public function iscriviti(): void {
        $this->guard();
        $eventoId = (int)($_POST['evento_id'] ?? 0);
        if ($eventoId) {
            require_once BASE_PATH . 'models/Evento.php';
            $em = new Evento();
            $em->createTables();
            $ev = $em->findById($eventoId);
            if ($ev && $ev['stato'] === 'aperto') {
                if ($em->iscriviStudente($eventoId, $this->studente['id'])) {
                    $_SESSION['flash_success'] = 'Iscrizione confermata!';
                } else {
                    $_SESSION['flash_error'] = 'Iscrizione non riuscita (capienza esaurita o già iscritto).';
                }
            } else {
                $_SESSION['flash_error'] = 'Evento non disponibile.';
            }
        }
        header('Location: ' . BASE_URL . 'studente/eventi'); exit;
    }

    public function rette(): void {
        $this->guard();
        $page = 'studente-rette';
        $studente = $this->studente;

        $stmt = $this->db->prepare("
            SELECT r.*,
                   COUNT(rp.id) AS tot_rate,
                   SUM(CASE WHEN rp.pagata = 1 THEN 1 ELSE 0 END) AS rate_pagate,
                   SUM(CASE WHEN rp.pagata = 0 THEN rp.importo ELSE 0 END) AS da_pagare
            FROM rette r
            LEFT JOIN rate_pagamento rp ON rp.retta_id = r.id
            WHERE r.studente_id = ?
            GROUP BY r.id
        ");
        $stmt->execute([$studente['id']]);
        $rette = $stmt->fetchAll();

        $rate = [];
        if (!empty($rette)) {
            $stmt = $this->db->prepare("
                SELECT rp.* FROM rate_pagamento rp
                JOIN rette r ON r.id = rp.retta_id
                WHERE r.studente_id = ?
                ORDER BY rp.scadenza ASC, rp.numero ASC
            ");
            $stmt->execute([$studente['id']]);
            $rate = $stmt->fetchAll();
        }

        require BASE_PATH . 'views/studente/_header.php';
        require BASE_PATH . 'views/studente/rette.php';
        require BASE_PATH . 'views/studente/_footer.php';
    }

    public function profilo(): void {
        $this->guard();
        $page = 'studente-profilo';
        $studente = $this->studente;

        // Tutti i percorsi/anni correnti (multi-corso)
        $stmt = $this->db->prepare("
            SELECT pa.numero, p.nome AS percorso_nome, a.anno AS anno_label
            FROM studente_anni sa
            JOIN percorso_anni pa ON pa.id = sa.percorso_anno_id
            JOIN percorsi_accademici p ON p.id = pa.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            WHERE sa.studente_id = ?
            ORDER BY sa.id ASC
        ");
        $stmt->execute([$studente['id']]);
        $percorsiCorrenti = $stmt->fetchAll();
        $percorsoCorrente = $percorsiCorrenti[0] ?? false; // compatibilità

        require BASE_PATH . 'views/studente/_header.php';
        require BASE_PATH . 'views/studente/profilo.php';
        require BASE_PATH . 'views/studente/_footer.php';
    }
}
