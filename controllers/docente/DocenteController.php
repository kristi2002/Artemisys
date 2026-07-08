<?php

class DocenteController {

    private $db;
    private ?int $insegnanteId;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->insegnanteId = $this->getInsegnanteIdFromUser();
    }

    private function getInsegnanteIdFromUser(): ?int {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) return null;
        $stmt = $this->db->prepare("SELECT id FROM insegnanti WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    private function guard(): void {
        if (!$this->insegnanteId) {
            $_SESSION['flash_error'] = 'Profilo docente non collegato.';
            header('Location: ' . BASE_URL . 'auth/logout');
            exit;
        }
    }

    // ── Le mie lezioni (home docente) ────────────────────────────────────────
    public function lezioni(): void {
        $this->guard();
        $page      = 'mie-lezioni';
        $pageTitle = 'Le mie lezioni';
        $insId     = $this->insegnanteId;

        $stmt = $this->db->prepare("
            SELECT l.*, m.nome AS materia_nome, m.codice AS materia_codice,
                   p.nome AS percorso_nome, pa.numero AS anno_numero,
                   asc1.anno AS anno_label,
                   COUNT(CASE WHEN lp.presente = 1 THEN 1 END) AS presenti,
                   COUNT(lp.studente_id) AS totale
            FROM lezione_insegnanti li
            JOIN lezioni l                ON l.id = li.lezione_id
            JOIN percorso_anno_materie pam ON pam.id = l.percorso_anno_materia_id
            JOIN materie m                ON m.id  = pam.materia_id
            JOIN percorso_anni pa         ON pa.id = pam.anno_id
            JOIN percorsi_accademici p    ON p.id  = pa.percorso_id
            LEFT JOIN anni_scolastici asc1 ON asc1.id = p.anno_scolastico_id
            LEFT JOIN lezione_presenze lp ON lp.lezione_id = l.id
            WHERE li.insegnante_id = ?
            GROUP BY l.id
            ORDER BY l.data DESC, l.created_at DESC
        ");
        $stmt->execute([$insId]);
        $lezioni = $stmt->fetchAll();

        $oggi = date('Y-m-d');
        $lezioniOggi    = array_filter($lezioni, fn($l) => $l['data'] === $oggi);
        $lezioniProssime = array_filter($lezioni, fn($l) => $l['data'] && $l['data'] > $oggi);
        $lezioniPassate  = array_filter($lezioni, fn($l) => $l['data'] && $l['data'] < $oggi);

        $insegnante = $this->db->prepare("SELECT * FROM insegnanti WHERE id = ?");
        $insegnante->execute([$insId]);
        $insegnante = $insegnante->fetch();

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/docente/lezioni.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── Le mie materie ───────────────────────────────────────────────────────
    public function materie(): void {
        $this->guard();
        $page      = 'mie-materie';
        $pageTitle = 'Le mie materie';
        $insId     = $this->insegnanteId;

        $stmt = $this->db->prepare("
            SELECT pam.id AS pam_id, m.nome AS materia_nome, m.codice AS materia_codice,
                   p.id AS percorso_id, p.nome AS percorso_nome,
                   pa.numero AS anno_numero, pa.id AS anno_id,
                   asc1.anno AS anno_label,
                   COUNT(DISTINCT l.id) AS num_lezioni
            FROM pam_insegnanti pi
            JOIN percorso_anno_materie pam ON pam.id = pi.pam_id
            JOIN materie m                ON m.id  = pam.materia_id
            JOIN percorso_anni pa         ON pa.id = pam.anno_id
            JOIN percorsi_accademici p    ON p.id  = pa.percorso_id
            LEFT JOIN anni_scolastici asc1 ON asc1.id = p.anno_scolastico_id
            LEFT JOIN lezioni l           ON l.percorso_anno_materia_id = pam.id
            WHERE pi.insegnante_id = ?
            GROUP BY pam.id
            ORDER BY asc1.anno DESC, p.nome ASC, pa.numero ASC, m.nome ASC
        ");
        $stmt->execute([$insId]);
        $materie = $stmt->fetchAll();

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/docente/materie.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── Mio calendario ───────────────────────────────────────────────────────
    public function calendario(): void {
        $this->guard();
        $page      = 'mio-calendario';
        $pageTitle = 'Mio calendario';
        $insId     = $this->insegnanteId;

        $mese = (int)($_GET['mese'] ?? date('n'));
        $anno = (int)($_GET['anno'] ?? date('Y'));
        if ($mese < 1)  { $mese = 12; $anno--; }
        if ($mese > 12) { $mese = 1;  $anno++; }

        $stmt = $this->db->prepare("
            SELECT l.id, l.titolo, l.data, l.online, l.durata_minuti,
                   m.nome AS materia_nome, m.id AS materia_id,
                   p.nome AS percorso_nome, pa.numero AS anno_numero
            FROM lezione_insegnanti li
            JOIN lezioni l                ON l.id = li.lezione_id
            JOIN percorso_anno_materie pam ON pam.id = l.percorso_anno_materia_id
            JOIN materie m                ON m.id  = pam.materia_id
            JOIN percorso_anni pa         ON pa.id = pam.anno_id
            JOIN percorsi_accademici p    ON p.id  = pa.percorso_id
            WHERE li.insegnante_id = ?
              AND l.data IS NOT NULL
              AND YEAR(l.data) = ? AND MONTH(l.data) = ?
            ORDER BY l.data ASC, l.created_at ASC
        ");
        $stmt->execute([$insId, $anno, $mese]);

        $lezioniPerGiorno = [];
        foreach ($stmt->fetchAll() as $l) {
            $g = (int)date('j', strtotime($l['data']));
            $lezioniPerGiorno[$g][] = $l;
        }

        $primoGiorno    = mktime(0, 0, 0, $mese, 1, $anno);
        $giorniNelMese  = (int)date('t', $primoGiorno);
        $giornoPartenza = (int)date('N', $primoGiorno);

        $mesePrev = $mese - 1 ?: 12;
        $annoPrev = $mese - 1 ? $anno : $anno - 1;
        $meseNext = $mese % 12 + 1;
        $annoNext = $mese < 12 ? $anno : $anno + 1;

        $oggi     = (int)date('j');
        $oggiMese = (int)date('n');
        $oggiAnno = (int)date('Y');

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/docente/calendario.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── I miei esami ─────────────────────────────────────────────────────────
    public function esami(): void {
        $this->guard();
        require_once BASE_PATH . 'models/Esame.php';
        (new Esame())->createTables();

        $page      = 'miei-esami';
        $pageTitle = 'I miei esami';
        $insId     = $this->insegnanteId;
        $search    = trim($_GET['q'] ?? '');

        // Esami filtrati alle materie del docente
        $sql = "
            SELECT e.*,
                   m.nome    AS materia_nome,
                   p.nome    AS percorso_nome,
                   pa.numero AS anno_numero,
                   a.anno    AS anno_label,
                   COUNT(DISTINCT ei.id) AS num_iscritti,
                   COUNT(DISTINCT CASE WHEN ei.voto IS NOT NULL
                         AND ei.assente = 0 THEN ei.id END) AS num_valutati,
                   COUNT(DISTINCT CASE WHEN ei.assente = 1 THEN ei.id END) AS num_assenti
            FROM esami e
            JOIN percorso_anno_materie pam ON pam.id = e.percorso_anno_materia_id
            JOIN pam_insegnanti pi         ON pi.pam_id = pam.id AND pi.insegnante_id = ?
            JOIN materie m                 ON m.id  = pam.materia_id
            JOIN percorso_anni pa          ON pa.id = pam.anno_id
            JOIN percorsi_accademici p     ON p.id  = pa.percorso_id
            LEFT JOIN anni_scolastici a    ON a.id  = p.anno_scolastico_id
            LEFT JOIN esame_iscrizioni ei  ON ei.esame_id = e.id
        ";
        $params = [$insId];
        if ($search !== '') {
            $sql .= " WHERE (e.titolo LIKE ? OR m.nome LIKE ? OR p.nome LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $sql .= " GROUP BY e.id ORDER BY e.data DESC, e.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $esami = $stmt->fetchAll();

        // Pam disponibili per la creazione (solo le sue materie)
        $stmt = $this->db->prepare("
            SELECT pam.id AS pam_id, m.nome AS materia_nome, m.codice,
                   pa.id AS anno_id, pa.numero AS anno_numero,
                   p.id AS percorso_id, p.nome AS percorso_nome,
                   a.anno AS anno_label
            FROM pam_insegnanti pi
            JOIN percorso_anno_materie pam ON pam.id = pi.pam_id
            JOIN materie m                 ON m.id  = pam.materia_id
            JOIN percorso_anni pa          ON pa.id = pam.anno_id
            JOIN percorsi_accademici p     ON p.id  = pa.percorso_id
            LEFT JOIN anni_scolastici a    ON a.id = p.anno_scolastico_id
            WHERE pi.insegnante_id = ?
            ORDER BY a.anno DESC, p.nome ASC, pa.numero ASC, m.nome ASC
        ");
        $stmt->execute([$insId]);
        $pamList = $stmt->fetchAll();

        $success = $_SESSION['flash_success'] ?? null;
        $error   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/esami/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    // ── Profilo docente ──────────────────────────────────────────────────────
    public function profilo(): void {
        $this->guard();
        $page      = 'profilo-docente';
        $pageTitle = 'Profilo';
        $insId     = $this->insegnanteId;

        $stmt = $this->db->prepare("
            SELECT i.*, u.username, u.email AS user_email
            FROM insegnanti i
            JOIN users u ON u.id = i.user_id
            WHERE i.id = ?
        ");
        $stmt->execute([$insId]);
        $insegnante = $stmt->fetch();

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/docente/profilo.php';
        require BASE_PATH . 'views/layout/footer.php';
    }
}
