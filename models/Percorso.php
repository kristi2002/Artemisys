<?php

class Percorso {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS percorsi_accademici (
                id                 INT AUTO_INCREMENT PRIMARY KEY,
                nome               VARCHAR(200) NOT NULL,
                codice_corso       VARCHAR(50) NULL,
                anno_scolastico_id INT NOT NULL,
                descrizione        TEXT NULL,
                created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
        // Auto-migrazione per installazioni esistenti (idempotente su MySQL/MariaDB)
        $this->ensureColumn('percorsi_accademici', 'codice_corso', 'codice_corso VARCHAR(50) NULL AFTER nome');
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS percorso_anni (
                id           INT AUTO_INCREMENT PRIMARY KEY,
                percorso_id  INT NOT NULL,
                numero       TINYINT NOT NULL,
                codice_corso VARCHAR(50) NULL,
                created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_percorso_anno (percorso_id, numero)
            ) ENGINE=InnoDB
        ");
        $this->ensureColumn('percorso_anni', 'codice_corso', 'codice_corso VARCHAR(50) NULL AFTER numero');
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS percorso_anno_materie (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                anno_id    INT NOT NULL,
                materia_id INT NOT NULL,
                ordine     TINYINT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_anno_materia (anno_id, materia_id)
            ) ENGINE=InnoDB
        ");
    }

    // ── Aggiunge una colonna solo se non esiste già (MySQL 8 + MariaDB) ──────
    private function ensureColumn(string $table, string $column, string $definition): void {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
        );
        $stmt->execute([$table, $column]);
        if (!(int)$stmt->fetchColumn()) {
            $this->db->exec("ALTER TABLE `{$table}` ADD COLUMN {$definition}");
        }
    }

    // ── Tutti i percorsi con anno scolastico (con filtri opzionali) ─────────
    public function getAll(array $filters = []): array {
        $where  = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = "(p.nome LIKE ? OR p.codice_corso LIKE ? OR p.descrizione LIKE ?)";
            $like     = '%' . $filters['q'] . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if (!empty($filters['anno_scolastico_id'])) {
            $where[]  = "p.anno_scolastico_id = ?";
            $params[] = (int)$filters['anno_scolastico_id'];
        }
        if (!empty($filters['sede_id'])) {
            $where[]  = "p.sede_id = ?";
            $params[] = (int)$filters['sede_id'];
        }
        if (($filters['stato'] ?? '') === 'attivi') {
            $where[] = "a.attivo = 1";
        } elseif (($filters['stato'] ?? '') === 'passati') {
            $where[] = "(a.attivo = 0 OR a.attivo IS NULL)";
        }

        $sql = "SELECT p.*, a.anno AS anno_label, a.attivo AS anno_attivo,
                       s.nome AS sede_nome, s.via AS sede_via, s.comune AS sede_comune, s.telefono AS sede_telefono
                FROM percorsi_accademici p
                LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
                LEFT JOIN sedi s ON s.id = p.sede_id";
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY a.anno DESC, p.nome ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['anni'] = $this->getAnni((int)$row['id']);
        }
        return $rows;
    }

    // ── Conteggi per le stat chips (totale / attivi / passati) ──────────────
    public function counts(): array {
        $row = $this->db->query("
            SELECT COUNT(*) AS totale,
                   SUM(CASE WHEN a.attivo = 1 THEN 1 ELSE 0 END) AS attivi
            FROM percorsi_accademici p
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
        ")->fetch();
        $tot = (int)($row['totale'] ?? 0);
        $att = (int)($row['attivi'] ?? 0);
        return ['totale' => $tot, 'attivi' => $att, 'passati' => $tot - $att];
    }

    // ── Singolo percorso ─────────────────────────────────────────────────────
    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT p.*, a.anno AS anno_label,
                   s.nome AS sede_nome, s.via AS sede_via, s.comune AS sede_comune, s.telefono AS sede_telefono
            FROM percorsi_accademici p
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            LEFT JOIN sedi s ON s.id = p.sede_id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['anni'] = $this->getAnni($id);
        }
        return $row;
    }

    // ── Anni di un percorso (con conteggio materie) ──────────────────────────
    public function getAnni(int $percorsoId): array {
        $stmt = $this->db->prepare("
            SELECT pa.*,
                   COUNT(pam.id) AS num_materie
            FROM percorso_anni pa
            LEFT JOIN percorso_anno_materie pam ON pam.anno_id = pa.id
            WHERE pa.percorso_id = ?
            GROUP BY pa.id
            ORDER BY pa.numero ASC
        ");
        $stmt->execute([$percorsoId]);
        return $stmt->fetchAll();
    }

    // ── Singolo anno di corso ────────────────────────────────────────────────
    public function findAnnoById(int $annoId): array|false {
        $stmt = $this->db->prepare("
            SELECT pa.*, p.nome AS percorso_nome, p.id AS percorso_id,
                   a.anno AS anno_label
            FROM percorso_anni pa
            JOIN percorsi_accademici p ON p.id = pa.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            WHERE pa.id = ?
        ");
        $stmt->execute([$annoId]);
        return $stmt->fetch();
    }

    // ── Materie assegnate a un anno ──────────────────────────────────────────
    public function getMaterieAnno(int $annoId): array {
        $stmt = $this->db->prepare("
            SELECT m.*, pam.id AS assoc_id, pam.ordine
            FROM percorso_anno_materie pam
            JOIN materie m ON m.id = pam.materia_id
            WHERE pam.anno_id = ?
            ORDER BY pam.ordine ASC, m.nome ASC
        ");
        $stmt->execute([$annoId]);
        return $stmt->fetchAll();
    }

    // ── Singola associazione anno-materia (per la pagina dettaglio) ──────────
    public function getMateriaAssoc(int $pamId): array|false {
        $stmt = $this->db->prepare("
            SELECT pam.*,
                   m.nome    AS materia_nome,
                   m.codice  AS materia_codice,
                   m.codice_regionale,
                   pa.numero AS anno_numero,
                   p.nome    AS percorso_nome,
                   p.id      AS percorso_id,
                   a.anno    AS anno_label
            FROM percorso_anno_materie pam
            JOIN materie m             ON m.id  = pam.materia_id
            JOIN percorso_anni pa      ON pa.id = pam.anno_id
            JOIN percorsi_accademici p ON p.id  = pa.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            WHERE pam.id = ?
        ");
        $stmt->execute([$pamId]);
        $row = $stmt->fetch();
        if ($row) {
            $ordinali = [1=>'Primo Anno',2=>'Secondo Anno',3=>'Terzo Anno',4=>'Quarto Anno',
                         5=>'Quinto Anno',6=>'Sesto Anno',7=>'Settimo Anno',8=>'Ottavo Anno',
                         9=>'Nono Anno',10=>'Decimo Anno'];
            $row['anno_nome'] = $ordinali[$row['anno_numero']] ?? $row['anno_numero'] . '° Anno';
        }
        return $row;
    }

    // ── Materie NON ancora assegnate a un anno ───────────────────────────────
    public function getMaterieDisponibili(int $annoId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM materie
            WHERE id NOT IN (
                SELECT materia_id FROM percorso_anno_materie WHERE anno_id = ?
            )
            ORDER BY nome ASC
        ");
        $stmt->execute([$annoId]);
        return $stmt->fetchAll();
    }

    // ── Aggiorna percorso ────────────────────────────────────────────────────
    public function update(int $id, array $data): void {
        $this->db->prepare("
            UPDATE percorsi_accademici
            SET nome               = ?,
                codice_corso       = ?,
                anno_scolastico_id = ?,
                sede_id            = ?,
                descrizione        = ?,
                data_inizio_anno   = ?,
                data_fine_anno     = ?
            WHERE id = ?
        ")->execute([
            $data['nome'],
            !empty($data['codice_corso'])     ? $data['codice_corso']     : null,
            $data['anno_scolastico_id'],
            !empty($data['sede_id'])          ? (int)$data['sede_id']     : null,
            !empty($data['descrizione'])      ? $data['descrizione']      : null,
            !empty($data['data_inizio_anno']) ? $data['data_inizio_anno'] : null,
            !empty($data['data_fine_anno'])   ? $data['data_fine_anno']   : null,
            $id,
        ]);
    }

    // ── Crea percorso ────────────────────────────────────────────────────────
    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO percorsi_accademici (nome, codice_corso, anno_scolastico_id, descrizione, sede_id, data_inizio_anno, data_fine_anno)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['nome'],
            !empty($data['codice_corso'])     ? $data['codice_corso']     : null,
            $data['anno_scolastico_id'],
            !empty($data['descrizione'])      ? $data['descrizione']      : null,
            !empty($data['sede_id'])          ? (int)$data['sede_id']     : null,
            !empty($data['data_inizio_anno']) ? $data['data_inizio_anno'] : null,
            !empty($data['data_fine_anno'])   ? $data['data_fine_anno']   : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ── Aggiungi anno al percorso ────────────────────────────────────────────
    public function addAnno(int $percorsoId, int $numero, ?string $codiceCorso = null): void {
        $this->db->prepare("
            INSERT IGNORE INTO percorso_anni (percorso_id, numero, codice_corso) VALUES (?, ?, ?)
        ")->execute([$percorsoId, $numero, $codiceCorso !== '' ? $codiceCorso : null]);
    }

    // ── Aggiorna il codice corso di un anno ──────────────────────────────────
    public function updateAnnoCodice(int $annoId, ?string $codiceCorso): void {
        $this->db->prepare("
            UPDATE percorso_anni SET codice_corso = ? WHERE id = ?
        ")->execute([$codiceCorso !== '' ? $codiceCorso : null, $annoId]);
    }

    // ── Rimuovi anno (e le sue materie) ─────────────────────────────────────
    public function deleteAnno(int $annoId): void {
        $this->db->prepare("DELETE FROM percorso_anno_materie WHERE anno_id = ?")->execute([$annoId]);
        $this->db->prepare("DELETE FROM percorso_anni WHERE id = ?")->execute([$annoId]);
    }

    // ── Aggiungi materia a un anno ───────────────────────────────────────────
    public function addMateria(int $annoId, int $materiaId): void {
        $stmt = $this->db->prepare("SELECT MAX(ordine) FROM percorso_anno_materie WHERE anno_id = ?");
        $stmt->execute([$annoId]);
        $maxOrdine = (int)$stmt->fetchColumn();

        $this->db->prepare("
            INSERT IGNORE INTO percorso_anno_materie (anno_id, materia_id, ordine)
            VALUES (?, ?, ?)
        ")->execute([$annoId, $materiaId, $maxOrdine + 1]);
    }

    // ── Rimuovi materia da un anno ───────────────────────────────────────────
    public function deleteMateria(int $assocId): void {
        $this->db->prepare("DELETE FROM percorso_anno_materie WHERE id = ?")->execute([$assocId]);
    }

    // ── Elimina percorso (e relativi anni e materie) ─────────────────────────
    public function delete(int $id): void {
        $anni = $this->getAnni($id);
        foreach ($anni as $a) {
            $this->db->prepare("DELETE FROM percorso_anno_materie WHERE anno_id = ?")->execute([$a['id']]);
        }
        $this->db->prepare("DELETE FROM percorso_anni WHERE percorso_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM percorsi_accademici WHERE id = ?")->execute([$id]);
    }

    // ── Prossimo numero anno disponibile ─────────────────────────────────────
    public function nextNumero(int $percorsoId): int {
        $anni   = $this->getAnni($percorsoId);
        $numeri = array_column($anni, 'numero');
        for ($i = 1; $i <= 10; $i++) {
            if (!in_array($i, $numeri)) return $i;
        }
        return count($anni) + 1;
    }
}
