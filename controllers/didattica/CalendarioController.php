<?php

class CalendarioController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index(): void {
        $page      = 'calendario-presenze';
        $pageTitle = 'Calendario Presenze';

        $annoId = (int)($_GET['anno_id'] ?? 0);
        $mese   = (int)($_GET['mese']    ?? date('n'));
        $anno   = (int)($_GET['anno']    ?? date('Y'));

        // Normalizza mese/anno
        if ($mese < 1)  { $mese = 12; $anno--; }
        if ($mese > 12) { $mese = 1;  $anno++; }

        // Percorsi + anni per i filtri
        $percorsi = $this->db->query("
            SELECT p.id, p.nome, a.anno AS anno_label, p.anno_scolastico_id
            FROM percorsi_accademici p
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            ORDER BY a.anno DESC, p.nome ASC
        ")->fetchAll();

        $percorsoAnni = $this->db->query("
            SELECT pa.id, pa.numero, pa.percorso_id
            FROM percorso_anni pa
            ORDER BY pa.percorso_id ASC, pa.numero ASC
        ")->fetchAll();

        // Lezioni del mese selezionato
        $lezioniPerGiorno = [];
        $annoCorrente     = null;

        if ($annoId > 0) {
            $annoCorrente = $this->db->prepare("
                SELECT pa.*, p.nome AS percorso_nome, a.anno AS anno_label
                FROM percorso_anni pa
                JOIN percorsi_accademici p ON p.id = pa.percorso_id
                LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
                WHERE pa.id = ?
            ");
            $annoCorrente->execute([$annoId]);
            $annoCorrente = $annoCorrente->fetch();

            $stmt = $this->db->prepare("
                SELECT l.id, l.titolo, l.data, l.online, l.durata_minuti,
                       m.nome AS materia_nome, m.id AS materia_id,
                       COUNT(CASE WHEN lp.presente = 1 THEN 1 END) AS presenti,
                       COUNT(lp.studente_id)                        AS totale
                FROM lezioni l
                JOIN percorso_anno_materie pam ON pam.id = l.percorso_anno_materia_id
                JOIN materie m                 ON m.id   = pam.materia_id
                LEFT JOIN lezione_presenze lp  ON lp.lezione_id = l.id
                WHERE pam.anno_id = ?
                  AND l.data IS NOT NULL
                  AND YEAR(l.data) = ? AND MONTH(l.data) = ?
                GROUP BY l.id
                ORDER BY l.data ASC, l.created_at ASC
            ");
            $stmt->execute([$annoId, $anno, $mese]);

            foreach ($stmt->fetchAll() as $l) {
                $giorno = (int)date('j', strtotime($l['data']));
                $lezioniPerGiorno[$giorno][] = $l;
            }
        }

        // Dati calendario
        $primoGiorno      = mktime(0, 0, 0, $mese, 1, $anno);
        $giorniNelMese    = (int)date('t', $primoGiorno);
        $giornoPartenza   = (int)date('N', $primoGiorno); // 1=Lun … 7=Dom

        $mesePrev = $mese - 1 ?: 12;
        $annoPrev = $mese - 1 ? $anno : $anno - 1;
        $meseNext = $mese % 12 + 1;
        $annoNext = $mese < 12  ? $anno : $anno + 1;

        $oggi = (int)date('j');
        $oggiMese = (int)date('n');
        $oggiAnno = (int)date('Y');

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/calendario/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }
}
