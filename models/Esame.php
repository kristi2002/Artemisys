<?php

class Esame {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esami (
                id                       INT AUTO_INCREMENT PRIMARY KEY,
                percorso_anno_materia_id INT NOT NULL,
                titolo                   VARCHAR(255) NOT NULL,
                data                     DATE NULL,
                ora_inizio               TIME NULL,
                tipo                     ENUM('scritto','orale','pratico') NOT NULL DEFAULT 'scritto',
                note                     TEXT NULL,
                created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esame_iscrizioni (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                esame_id    INT NOT NULL,
                studente_id INT NOT NULL,
                voto        DECIMAL(4,2) NULL,
                assente     TINYINT(1)   NOT NULL DEFAULT 0,
                note        VARCHAR(255) NULL,
                created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_esame_studente (esame_id, studente_id)
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esame_supervisori (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                esame_id      INT NOT NULL,
                insegnante_id INT NOT NULL,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_esame_ins (esame_id, insegnante_id),
                FOREIGN KEY (esame_id) REFERENCES esami(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esame_allegati (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                esame_id      INT NOT NULL,
                filename      VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                mime_type     VARCHAR(100) NULL,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (esame_id) REFERENCES esami(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");
    }

    // ── Allegati ─────────────────────────────────────────────────────────────
    public function getAllegati(int $esameId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM esame_allegati
            WHERE esame_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$esameId]);
        return $stmt->fetchAll();
    }

    public function findAllegato(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM esame_allegati WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addAllegato(int $esameId, string $filename, string $originalName, ?string $mime): void {
        $this->db->prepare("
            INSERT INTO esame_allegati (esame_id, filename, original_name, mime_type)
            VALUES (?, ?, ?, ?)
        ")->execute([$esameId, $filename, $originalName, $mime]);
    }

    public function deleteAllegato(int $id): void {
        $this->db->prepare("DELETE FROM esame_allegati WHERE id = ?")->execute([$id]);
    }

    public function getSupervisori(int $esameId): array {
        $stmt = $this->db->prepare("
            SELECT es.id AS link_id, i.id, i.cognome, i.nome
            FROM esame_supervisori es
            JOIN insegnanti i ON i.id = es.insegnante_id
            WHERE es.esame_id = ?
            ORDER BY i.cognome ASC, i.nome ASC
        ");
        $stmt->execute([$esameId]);
        return $stmt->fetchAll();
    }

    public function getInsegnantiMateria(int $pamId): array {
        $stmt = $this->db->prepare("
            SELECT i.id, i.cognome, i.nome
            FROM pam_insegnanti pi
            JOIN insegnanti i ON i.id = pi.insegnante_id
            WHERE pi.pam_id = ?
            ORDER BY i.cognome ASC, i.nome ASC
        ");
        $stmt->execute([$pamId]);
        return $stmt->fetchAll();
    }

    public function addSupervisore(int $esameId, int $insegnanteId): void {
        try {
            $this->db->prepare("
                INSERT INTO esame_supervisori (esame_id, insegnante_id) VALUES (?, ?)
            ")->execute([$esameId, $insegnanteId]);
        } catch (PDOException $e) { /* duplicato */ }
    }

    public function removeSupervisore(int $esameId, int $insegnanteId): void {
        $this->db->prepare("
            DELETE FROM esame_supervisori WHERE esame_id = ? AND insegnante_id = ?
        ")->execute([$esameId, $insegnanteId]);
    }

    // ── Lista esami ───────────────────────────────────────────────────────────
    public function getAll(string $search = ''): array {
        $sql = "
            SELECT e.*,
                   m.nome     AS materia_nome,
                   p.nome     AS percorso_nome,
                   pa.numero  AS anno_numero,
                   a.anno     AS anno_label,
                   COUNT(DISTINCT ei.id)                           AS num_iscritti,
                   COUNT(DISTINCT CASE WHEN ei.voto IS NOT NULL
                         AND ei.assente = 0 THEN ei.id END)        AS num_valutati,
                   COUNT(DISTINCT CASE WHEN ei.assente = 1
                         THEN ei.id END)                           AS num_assenti
            FROM esami e
            JOIN percorso_anno_materie pam ON pam.id = e.percorso_anno_materia_id
            JOIN materie m                 ON m.id   = pam.materia_id
            JOIN percorso_anni pa          ON pa.id  = pam.anno_id
            JOIN percorsi_accademici p     ON p.id   = pa.percorso_id
            LEFT JOIN anni_scolastici a    ON a.id   = p.anno_scolastico_id
            LEFT JOIN esame_iscrizioni ei  ON ei.esame_id = e.id
        ";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE e.titolo LIKE ? OR m.nome LIKE ? OR p.nome LIKE ?";
            $like = "%{$search}%";
            $params = [$like, $like, $like];
        }
        $sql .= " GROUP BY e.id ORDER BY e.data DESC, e.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Singolo esame ─────────────────────────────────────────────────────────
    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT e.*,
                   m.nome    AS materia_nome,
                   m.codice  AS materia_codice,
                   p.nome    AS percorso_nome,
                   pa.numero AS anno_numero,
                   pa.id     AS anno_id,
                   a.anno    AS anno_label,
                   p.id      AS percorso_id
            FROM esami e
            JOIN percorso_anno_materie pam ON pam.id = e.percorso_anno_materia_id
            JOIN materie m                 ON m.id   = pam.materia_id
            JOIN percorso_anni pa          ON pa.id  = pam.anno_id
            JOIN percorsi_accademici p     ON p.id   = pa.percorso_id
            LEFT JOIN anni_scolastici a    ON a.id   = p.anno_scolastico_id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Iscritti all'esame ────────────────────────────────────────────────────
    public function getIscrizioni(int $esameId): array {
        $stmt = $this->db->prepare("
            SELECT ei.*, s.cognome, s.nome, s.codice_fiscale
            FROM esame_iscrizioni ei
            JOIN studenti s ON s.id = ei.studente_id
            WHERE ei.esame_id = ?
            ORDER BY s.cognome ASC, s.nome ASC
        ");
        $stmt->execute([$esameId]);
        return $stmt->fetchAll();
    }

    // ── Crea esame ────────────────────────────────────────────────────────────
    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO esami (percorso_anno_materia_id, titolo, data, ora_inizio, tipo, note)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['pam_id'],
            $data['titolo'],
            $data['data']       ?: null,
            $data['ora_inizio'] ?: null,
            $data['tipo'],
            $data['note']       ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ── Iscrivi studenti (auto da studente_anni) ──────────────────────────────
    public function iscriviClasseAuto(int $esameId, int $annoId): int {
        $stmt = $this->db->prepare("
            SELECT sa.studente_id
            FROM studente_anni sa
            WHERE sa.percorso_anno_id = ?
        ");
        $stmt->execute([$annoId]);
        $studenti = $stmt->fetchAll();

        $ins = $this->db->prepare("
            INSERT IGNORE INTO esame_iscrizioni (esame_id, studente_id)
            VALUES (?, ?)
        ");
        foreach ($studenti as $s) {
            $ins->execute([$esameId, $s['studente_id']]);
        }
        return count($studenti);
    }

    // ── Salva voti ────────────────────────────────────────────────────────────
    public function saveVoti(array $righe): void {
        $stmt = $this->db->prepare("
            UPDATE esame_iscrizioni
            SET voto = ?, assente = ?, note = ?
            WHERE id = ?
        ");
        foreach ($righe as $id => $dati) {
            $stmt->execute([
                $dati['voto']    !== '' ? $dati['voto']    : null,
                $dati['assente'] ?? 0,
                $dati['note']    ?: null,
                (int)$id,
            ]);
        }
    }

    // ── Elimina esame ─────────────────────────────────────────────────────────
    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM esame_iscrizioni WHERE esame_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM esami WHERE id = ?")->execute([$id]);
    }

    // ── Dati per i select a cascata (percorso → anno → materia) ──────────────
    public function getPamList(): array {
        return $this->db->query("
            SELECT pam.id AS pam_id, m.nome AS materia_nome, m.codice,
                   pa.id AS anno_id, pa.numero AS anno_numero,
                   p.id AS percorso_id, p.nome AS percorso_nome,
                   a.anno AS anno_label
            FROM percorso_anno_materie pam
            JOIN materie m             ON m.id  = pam.materia_id
            JOIN percorso_anni pa      ON pa.id = pam.anno_id
            JOIN percorsi_accademici p ON p.id  = pa.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            ORDER BY a.anno DESC, p.nome ASC, pa.numero ASC, m.nome ASC
        ")->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM esami")->fetchColumn();
    }
}
