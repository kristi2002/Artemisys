<?php

class DashboardController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index(): void {
        $page      = 'home';
        $pageTitle = 'Dashboard';

        // ── Conteggi numerici ───────────────────────────────────────────────
        $stats = [
            'studenti'     => $this->safeCount("SELECT COUNT(*) FROM studenti"),
            'docenti'      => $this->safeCount("SELECT COUNT(*) FROM insegnanti"),
            'percorsi'     => $this->safeCount("SELECT COUNT(*) FROM percorsi_accademici"),
            'materie'      => $this->safeCount("SELECT COUNT(*) FROM materie"),
            'lezioni_mese' => $this->safeCount("SELECT COUNT(*) FROM lezioni
                                                WHERE YEAR(data) = YEAR(CURDATE())
                                                AND MONTH(data) = MONTH(CURDATE())"),
            'esami_futuri' => $this->safeCount("SELECT COUNT(*) FROM esami
                                                WHERE data IS NOT NULL AND data >= CURDATE()"),
            'stage_attivi' => $this->safeCount("SELECT COUNT(*) FROM stage WHERE step < 4"),
            'eventi_aperti'=> $this->safeCount("SELECT COUNT(*) FROM eventi WHERE stato = 'aperto'"),
        ];

        // ── Prossime lezioni (entro 14 giorni) ──────────────────────────────
        $prossLezioni = $this->safeFetch("
            SELECT l.id, l.titolo, l.data,
                   m.nome AS materia_nome,
                   p.nome AS percorso_nome,
                   pa.numero AS anno_numero
            FROM lezioni l
            JOIN percorso_anno_materie pam ON pam.id = l.percorso_anno_materia_id
            JOIN materie m                ON m.id  = pam.materia_id
            JOIN percorso_anni pa         ON pa.id = pam.anno_id
            JOIN percorsi_accademici p    ON p.id  = pa.percorso_id
            WHERE l.data IS NOT NULL AND l.data >= CURDATE()
              AND l.data <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
            ORDER BY l.data ASC
            LIMIT 6
        ");

        // ── Prossimi esami ──────────────────────────────────────────────────
        $prossEsami = $this->safeFetch("
            SELECT e.id, e.titolo, e.data, e.tipo,
                   m.nome AS materia_nome,
                   p.nome AS percorso_nome,
                   pa.numero AS anno_numero
            FROM esami e
            JOIN percorso_anno_materie pam ON pam.id = e.percorso_anno_materia_id
            JOIN materie m                ON m.id  = pam.materia_id
            JOIN percorso_anni pa         ON pa.id = pam.anno_id
            JOIN percorsi_accademici p    ON p.id  = pa.percorso_id
            WHERE e.data IS NOT NULL AND e.data >= CURDATE()
            ORDER BY e.data ASC
            LIMIT 5
        ");

        // ── Rette in scadenza / scadute ─────────────────────────────────────
        $retteScadenza = $this->safeFetch("
            SELECT rp.id, rp.numero, rp.importo, rp.scadenza,
                   r.id AS retta_id,
                   s.cognome, s.nome
            FROM rate_pagamento rp
            JOIN rette r    ON r.id = rp.retta_id
            JOIN studenti s ON s.id = r.studente_id
            WHERE rp.pagata = 0 AND rp.scadenza IS NOT NULL
              AND rp.scadenza <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY rp.scadenza ASC
            LIMIT 6
        ");

        // ── Stage in fase di valutazione (step 4) o quasi ───────────────────
        $stageDaValutare = $this->safeFetch("
            SELECT st.id, st.azienda, st.step, st.data_fine,
                   s.cognome, s.nome
            FROM stage st
            JOIN studenti s ON s.id = st.studente_id
            WHERE st.step >= 3
            ORDER BY st.step DESC, st.data_fine DESC
            LIMIT 5
        ");

        // ── Eventi prossimi ─────────────────────────────────────────────────
        $prossEventi = $this->safeFetch("
            SELECT e.*, COUNT(ei.id) AS num_iscritti
            FROM eventi e
            LEFT JOIN evento_iscrizioni ei ON ei.evento_id = e.id
            WHERE e.data_evento >= CURDATE()
            GROUP BY e.id
            ORDER BY e.data_evento ASC
            LIMIT 4
        ");

        require BASE_PATH . 'views/layout/header.php';
        require BASE_PATH . 'views/layout/sidebar.php';
        require BASE_PATH . 'views/didattica/dashboard/index.php';
        require BASE_PATH . 'views/layout/footer.php';
    }

    private function safeCount(string $sql): int {
        try { return (int)$this->db->query($sql)->fetchColumn(); }
        catch (Exception $e) { return 0; }
    }

    private function safeFetch(string $sql): array {
        try { return $this->db->query($sql)->fetchAll() ?: []; }
        catch (Exception $e) { return []; }
    }
}
