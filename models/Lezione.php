<?php

class Lezione {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        // Aggiunge insegnante_id a percorso_anno_materie se non esiste
        try {
            $this->db->exec("ALTER TABLE percorso_anno_materie ADD COLUMN insegnante_id INT NULL");
        } catch (Exception $e) {}

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS argomenti (
                id                       INT AUTO_INCREMENT PRIMARY KEY,
                percorso_anno_materia_id INT NOT NULL,
                titolo                   VARCHAR(255) NOT NULL,
                descrizione              TEXT NULL,
                ordine                   SMALLINT DEFAULT 0,
                created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS lezioni (
                id                       INT AUTO_INCREMENT PRIMARY KEY,
                percorso_anno_materia_id INT NULL,
                titolo                   VARCHAR(255) NOT NULL,
                data                     DATE NULL,
                durata_minuti            SMALLINT NULL,
                note                     TEXT NULL,
                created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS lezione_presenze (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                lezione_id  INT NOT NULL,
                studente_id INT NOT NULL,
                presente    TINYINT(1) NOT NULL DEFAULT 1,
                note        VARCHAR(255) NULL,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_presenza (lezione_id, studente_id)
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS pam_insegnanti (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                pam_id        INT NOT NULL,
                insegnante_id INT NOT NULL,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_pam_ins (pam_id, insegnante_id)
            ) ENGINE=InnoDB
        ");

        try { $this->db->exec("ALTER TABLE lezioni ADD COLUMN argomento TEXT NULL"); } catch (Exception $e) {}
        try { $this->db->exec("ALTER TABLE lezioni ADD COLUMN online TINYINT(1) NOT NULL DEFAULT 0"); } catch (Exception $e) {}
        try { $this->db->exec("ALTER TABLE lezioni ADD COLUMN link_online VARCHAR(500) NULL"); } catch (Exception $e) {}
        try { $this->db->exec("ALTER TABLE lezioni ADD COLUMN ora_inizio TIME NULL"); } catch (Exception $e) {}
        try { $this->db->exec("ALTER TABLE lezioni ADD COLUMN ora_fine TIME NULL"); } catch (Exception $e) {}

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS lezione_insegnanti (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                lezione_id    INT NOT NULL,
                insegnante_id INT NOT NULL,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_lez_ins (lezione_id, insegnante_id)
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS lezione_allegati (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                lezione_id    INT NOT NULL,
                filename      VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                mime_type     VARCHAR(100) NULL,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
    }

    // ── Lezioni di una materia/anno ──────────────────────────────────────────
    public function getByMateria(int $pamId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM lezioni
            WHERE percorso_anno_materia_id = ?
            ORDER BY data ASC, created_at ASC
        ");
        $stmt->execute([$pamId]);
        return $stmt->fetchAll();
    }

    // ── Tutte le lezioni (sidebar) ───────────────────────────────────────────
    public function getAll(string $search = '', array $filters = []): array {
        $sql = "
            SELECT l.*,
                   m.id     AS materia_id,
                   m.nome   AS materia_nome,
                   m.codice AS materia_codice,
                   pa.numero AS anno_numero,
                   p.id     AS percorso_id,
                   p.nome   AS percorso_nome,
                   a.anno   AS anno_label
            FROM lezioni l
            LEFT JOIN percorso_anno_materie pam ON pam.id = l.percorso_anno_materia_id
            LEFT JOIN materie m   ON m.id = pam.materia_id
            LEFT JOIN percorso_anni pa ON pa.id = pam.anno_id
            LEFT JOIN percorsi_accademici p ON p.id = pa.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (l.titolo LIKE ? OR m.nome LIKE ? OR p.nome LIKE ?)";
            $like = "%{$search}%";
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['data_da'])) {
            $sql .= " AND l.data >= ?";
            $params[] = $filters['data_da'];
        }
        if (!empty($filters['data_a'])) {
            $sql .= " AND l.data <= ?";
            $params[] = $filters['data_a'];
        }
        if (!empty($filters['materia_id'])) {
            $sql .= " AND m.id = ?";
            $params[] = (int)$filters['materia_id'];
        }
        if (!empty($filters['percorso_id'])) {
            $sql .= " AND p.id = ?";
            $params[] = (int)$filters['percorso_id'];
        }
        if (isset($filters['online']) && $filters['online'] !== '') {
            $sql .= " AND l.online = ?";
            $params[] = (int)$filters['online'];
        }

        $sql .= " ORDER BY l.data DESC, l.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO lezioni (percorso_anno_materia_id, titolo, data, ora_inizio, ora_fine, durata_minuti, note, online, link_online, argomento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['percorso_anno_materia_id'] ?: null,
            $data['titolo'],
            $data['data'] ?: null,
            !empty($data['ora_inizio']) ? $data['ora_inizio'] : null,
            !empty($data['ora_fine'])   ? $data['ora_fine']   : null,
            $data['durata_minuti'] ?: null,
            $data['note'] ?: null,
            $data['online']      ?? 0,
            $data['link_online'] ?: null,
            $data['argomento']   ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getInsegnantiPerMaterie(array $pamIds): array {
        if (empty($pamIds)) return [];
        $ph   = implode(',', array_fill(0, count($pamIds), '?'));
        $stmt = $this->db->prepare("
            SELECT pi.pam_id, i.cognome, i.nome
            FROM pam_insegnanti pi
            JOIN insegnanti i ON i.id = pi.insegnante_id
            WHERE pi.pam_id IN ($ph)
            ORDER BY i.cognome ASC, i.nome ASC
        ");
        $stmt->execute($pamIds);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int)$row['pam_id']][] = $row['cognome'] . ' ' . $row['nome'];
        }
        return $out;
    }

    public function getInsegnantiPerLezioni(array $lezioneIds): array {
        if (empty($lezioneIds)) return [];
        $ph   = implode(',', array_fill(0, count($lezioneIds), '?'));
        $stmt = $this->db->prepare("
            SELECT li.lezione_id, i.cognome, i.nome
            FROM lezione_insegnanti li
            JOIN insegnanti i ON i.id = li.insegnante_id
            WHERE li.lezione_id IN ($ph)
            ORDER BY i.cognome ASC, i.nome ASC
        ");
        $stmt->execute($lezioneIds);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int)$row['lezione_id']][] = $row['cognome'] . ' ' . $row['nome'];
        }
        return $out;
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM lezione_presenze WHERE lezione_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM lezioni WHERE id = ?")->execute([$id]);
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT l.*,
                   pam.id AS pam_id,
                   pam.anno_id,
                   m.nome  AS materia_nome,
                   pa.numero AS anno_numero,
                   pa.percorso_id,
                   p.nome  AS percorso_nome,
                   a.anno  AS anno_label
            FROM lezioni l
            JOIN percorso_anno_materie pam ON pam.id = l.percorso_anno_materia_id
            JOIN materie m   ON m.id = pam.materia_id
            JOIN percorso_anni pa ON pa.id = pam.anno_id
            JOIN percorsi_accademici p ON p.id = pa.percorso_id
            JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getStudentiAnno(int $annoId): array {
        $stmt = $this->db->prepare("
            SELECT s.id, s.nome, s.cognome
            FROM studenti s
            JOIN studente_anni sa ON sa.studente_id = s.id
            WHERE sa.percorso_anno_id = ?
            ORDER BY s.cognome ASC, s.nome ASC
        ");
        $stmt->execute([$annoId]);
        return $stmt->fetchAll();
    }

    public function getPresenze(int $lezioneId): array {
        $stmt = $this->db->prepare("SELECT studente_id, presente FROM lezione_presenze WHERE lezione_id = ?");
        $stmt->execute([$lezioneId]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int)$row['studente_id']] = (bool)$row['presente'];
        }
        return $out;
    }

    public function savePresenze(int $lezioneId, array $tuttiStudenti, array $presentiIds): void {
        $this->db->prepare("DELETE FROM lezione_presenze WHERE lezione_id = ?")->execute([$lezioneId]);
        $stmt = $this->db->prepare("INSERT INTO lezione_presenze (lezione_id, studente_id, presente) VALUES (?, ?, ?)");
        foreach ($tuttiStudenti as $sId) {
            $stmt->execute([$lezioneId, $sId, in_array($sId, $presentiIds) ? 1 : 0]);
        }
    }

    // ── Argomenti ────────────────────────────────────────────────────────────
    public function getArgomenti(int $pamId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM argomenti WHERE percorso_anno_materia_id = ? ORDER BY ordine ASC, id ASC
        ");
        $stmt->execute([$pamId]);
        return $stmt->fetchAll();
    }

    public function addArgomento(int $pamId, string $titolo, string $descrizione = ''): int {
        $stmt = $this->db->prepare("SELECT MAX(ordine) FROM argomenti WHERE percorso_anno_materia_id = ?");
        $stmt->execute([$pamId]);
        $max = (int)$stmt->fetchColumn();
        $this->db->prepare("
            INSERT INTO argomenti (percorso_anno_materia_id, titolo, descrizione, ordine)
            VALUES (?, ?, ?, ?)
        ")->execute([$pamId, $titolo, $descrizione ?: null, $max + 1]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteArgomento(int $id): void {
        $this->db->prepare("DELETE FROM argomenti WHERE id = ?")->execute([$id]);
    }

    // ── Insegnanti assegnati (multipli) ──────────────────────────────────────
    public function getInsegnanti(int $pamId): array {
        $stmt = $this->db->prepare("
            SELECT i.* FROM insegnanti i
            JOIN pam_insegnanti pi ON pi.insegnante_id = i.id
            WHERE pi.pam_id = ?
            ORDER BY i.cognome ASC, i.nome ASC
        ");
        $stmt->execute([$pamId]);
        return $stmt->fetchAll();
    }

    public function addInsegnante(int $pamId, int $insegnanteId): void {
        $this->db->prepare("
            INSERT IGNORE INTO pam_insegnanti (pam_id, insegnante_id) VALUES (?, ?)
        ")->execute([$pamId, $insegnanteId]);
    }

    public function removeInsegnante(int $pamId, int $insegnanteId): void {
        $this->db->prepare("
            DELETE FROM pam_insegnanti WHERE pam_id = ? AND insegnante_id = ?
        ")->execute([$pamId, $insegnanteId]);
    }

    // ── Insegnanti della lezione ──────────────────────────────────────────────
    public function getLezioneInsegnanti(int $lezioneId): array {
        $stmt = $this->db->prepare("
            SELECT i.* FROM insegnanti i
            JOIN lezione_insegnanti li ON li.insegnante_id = i.id
            WHERE li.lezione_id = ?
            ORDER BY i.cognome ASC, i.nome ASC
        ");
        $stmt->execute([$lezioneId]);
        return $stmt->fetchAll();
    }

    public function addLezioneInsegnante(int $lezioneId, int $insegnanteId): void {
        $this->db->prepare("
            INSERT IGNORE INTO lezione_insegnanti (lezione_id, insegnante_id) VALUES (?, ?)
        ")->execute([$lezioneId, $insegnanteId]);
    }

    public function removeLezioneInsegnante(int $lezioneId, int $insegnanteId): void {
        $this->db->prepare("
            DELETE FROM lezione_insegnanti WHERE lezione_id = ? AND insegnante_id = ?
        ")->execute([$lezioneId, $insegnanteId]);
    }

    // ── Dettagli lezione (argomento, note, online) ────────────────────────────
    public function updateDettagli(int $lezioneId, string $titolo, string $data, ?int $durata, string $argomento, string $note, int $online, string $linkOnline = '', string $oraInizio = '', string $oraFine = ''): void {
        $this->db->prepare("
            UPDATE lezioni SET titolo = ?, data = ?, ora_inizio = ?, ora_fine = ?, durata_minuti = ?, argomento = ?, note = ?, online = ?, link_online = ? WHERE id = ?
        ")->execute([$titolo, $data ?: null, $oraInizio ?: null, $oraFine ?: null, $durata, $argomento ?: null, $note ?: null, $online, $linkOnline ?: null, $lezioneId]);
    }

    public function toggleOnline(int $lezioneId): void {
        $this->db->prepare("
            UPDATE lezioni SET online = IF(online = 1, 0, 1) WHERE id = ?
        ")->execute([$lezioneId]);
    }

    // ── Allegati ──────────────────────────────────────────────────────────────
    public function getAllegati(int $lezioneId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM lezione_allegati WHERE lezione_id = ? ORDER BY created_at ASC
        ");
        $stmt->execute([$lezioneId]);
        return $stmt->fetchAll();
    }

    public function addAllegato(int $lezioneId, string $filename, string $originalName, string $mimeType): int {
        $this->db->prepare("
            INSERT INTO lezione_allegati (lezione_id, filename, original_name, mime_type)
            VALUES (?, ?, ?, ?)
        ")->execute([$lezioneId, $filename, $originalName, $mimeType]);
        return (int)$this->db->lastInsertId();
    }

    public function findAllegato(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM lezione_allegati WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function deleteAllegato(int $id): void {
        $this->db->prepare("DELETE FROM lezione_allegati WHERE id = ?")->execute([$id]);
    }

    public function findPamId(string $percorsoNome, int $annoNumero, string $materiaCodice): ?int {
        $stmt = $this->db->prepare("
            SELECT pam.id
            FROM percorso_anno_materie pam
            JOIN percorso_anni pa      ON pa.id = pam.anno_id
            JOIN percorsi_accademici p ON p.id  = pa.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            JOIN materie m             ON m.id  = pam.materia_id
            WHERE (LOWER(TRIM(p.nome)) = LOWER(TRIM(?))
                   OR LOWER(TRIM(CONCAT(p.nome, ' ', a.anno))) = LOWER(TRIM(?)))
              AND pa.numero = ?
              AND LOWER(TRIM(m.codice)) = LOWER(TRIM(?))
            LIMIT 1
        ");
        $stmt->execute([$percorsoNome, $percorsoNome, $annoNumero, $materiaCodice]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }
}
