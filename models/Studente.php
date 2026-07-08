<?php

class Studente {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Creazione tabelle ────────────────────────────────────────────────────
    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS studenti (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                nome           VARCHAR(100) NOT NULL,
                cognome        VARCHAR(100) NOT NULL,
                email          VARCHAR(150) NULL,
                telefono       VARCHAR(30)  NULL,
                data_nascita   DATE         NULL,
                codice_fiscale VARCHAR(20)  NULL,
                indirizzo      TEXT         NULL,
                foto           VARCHAR(255) NULL,
                note           TEXT         NULL,
                attivo         TINYINT(1)   NOT NULL DEFAULT 1,
                created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        // Iscrizione a uno o più percorsi/edizioni
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS studente_iscrizioni (
                id           INT AUTO_INCREMENT PRIMARY KEY,
                studente_id  INT NOT NULL,
                percorso_id  INT NOT NULL,
                data_inizio  DATE NULL,
                note         VARCHAR(255) NULL,
                created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_studente_percorso (studente_id, percorso_id)
            ) ENGINE=InnoDB
        ");

        // Anno di corso dello studente per ogni anno scolastico + percorso (storicità multi-corso)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS studente_anni (
                id                  INT AUTO_INCREMENT PRIMARY KEY,
                studente_id         INT NOT NULL,
                percorso_anno_id    INT NOT NULL,
                anno_scolastico_id  INT NOT NULL,
                created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_studente_anno_percorso (studente_id, anno_scolastico_id, percorso_anno_id)
            ) ENGINE=InnoDB
        ");
        // Migrazione: allarga il vincolo unico se esiste ancora quello vecchio
        try {
            $this->db->exec("ALTER TABLE studente_anni DROP INDEX unique_studente_anno_sc");
        } catch (\Throwable $e) {}
        try {
            $this->db->exec("ALTER TABLE studente_anni ADD UNIQUE KEY unique_studente_anno_percorso (studente_id, anno_scolastico_id, percorso_anno_id)");
        } catch (\Throwable $e) {}

        // Materie bonus (aggiuntive rispetto a quelle dell'anno)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS studente_materie_bonus (
                id                 INT AUTO_INCREMENT PRIMARY KEY,
                studente_id        INT NOT NULL,
                materia_id         INT NOT NULL,
                anno_scolastico_id INT NOT NULL,
                note               VARCHAR(255) NULL,
                created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_bonus (studente_id, materia_id, anno_scolastico_id)
            ) ENGINE=InnoDB
        ");

        // Voti: studente_id + percorso_anno_materia_id = chiave univoca del voto
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS voti (
                id                       INT AUTO_INCREMENT PRIMARY KEY,
                studente_id              INT            NOT NULL,
                percorso_anno_materia_id INT            NOT NULL,
                voto                     DECIMAL(4,2)   NOT NULL,
                tipo                     ENUM('scritto','orale','pratico','finale') NOT NULL DEFAULT 'finale',
                data                     DATE           NULL,
                note                     VARCHAR(255)   NULL,
                created_at               TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_studente_pam (studente_id, percorso_anno_materia_id)
            ) ENGINE=InnoDB
        ");

    }

    // ── Voti ─────────────────────────────────────────────────────────────────
    public function getVotiMateria(int $studenteId, int $pamId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM voti
            WHERE studente_id = ? AND percorso_anno_materia_id = ?
            ORDER BY data ASC, created_at ASC
        ");
        $stmt->execute([$studenteId, $pamId]);
        return $stmt->fetchAll();
    }

    public function getVotiAnno(int $studenteId, int $percorsoAnnoId): array {
        $stmt = $this->db->prepare("
            SELECT v.*, m.nome AS materia_nome, m.codice AS materia_codice
            FROM voti v
            JOIN percorso_anno_materie pam ON pam.id = v.percorso_anno_materia_id
            JOIN materie m ON m.id = pam.materia_id
            WHERE v.studente_id = ? AND pam.anno_id = ?
            ORDER BY m.nome ASC, v.data ASC
        ");
        $stmt->execute([$studenteId, $percorsoAnnoId]);
        return $stmt->fetchAll();
    }

    public function addVoto(int $studenteId, int $pamId, float $voto, string $tipo, string $data = '', string $note = ''): int {
        $this->db->prepare("
            INSERT INTO voti (studente_id, percorso_anno_materia_id, voto, tipo, data, note)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([$studenteId, $pamId, $voto, $tipo, $data ?: null, $note ?: null]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteVoto(int $id): void {
        $this->db->prepare("DELETE FROM voti WHERE id = ?")->execute([$id]);
    }

    // ── Lista studenti ───────────────────────────────────────────────────────
    public function getAll(string $search = ''): array {
        $sql = "SELECT DISTINCT s.* FROM studenti s";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE s.nome LIKE ? OR s.cognome LIKE ? OR s.email LIKE ? OR s.codice_fiscale LIKE ?";
            $like = "%{$search}%";
            $params = [$like, $like, $like, $like];
        }
        $sql .= " ORDER BY s.cognome ASC, s.nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $studenti = $stmt->fetchAll();

        // Arricchisce ogni studente con le iscrizioni attive
        foreach ($studenti as &$s) {
            $s['iscrizioni'] = $this->getIscrizioni($s['id']);
        }
        return $studenti;
    }

    // ── Singolo studente ─────────────────────────────────────────────────────
    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM studenti WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Crea studente ────────────────────────────────────────────────────────
    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO studenti (nome, cognome, email, telefono, data_nascita, codice_fiscale, indirizzo, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['nome'],
            $data['cognome'],
            $data['email']          ?: null,
            $data['telefono']       ?: null,
            $data['data_nascita']   ?: null,
            strtoupper($data['codice_fiscale'] ?? '') ?: null,
            $data['indirizzo']      ?: null,
            $data['note']           ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ── Aggiorna anagrafica ──────────────────────────────────────────────────
    public function update(int $id, array $data): void {
        $this->db->prepare("
            UPDATE studenti SET nome=?, cognome=?, email=?, telefono=?,
                data_nascita=?, codice_fiscale=?, indirizzo=?, note=?, attivo=?
            WHERE id=?
        ")->execute([
            $data['nome'],
            $data['cognome'],
            $data['email']          ?: null,
            $data['telefono']       ?: null,
            $data['data_nascita']   ?: null,
            strtoupper($data['codice_fiscale'] ?? '') ?: null,
            $data['indirizzo']      ?: null,
            $data['note']           ?: null,
            isset($data['attivo']) ? 1 : 0,
            $id,
        ]);
    }

    // ── Iscrizioni a percorsi (multi-corso) ─────────────────────────────────
    public function getIscrizioni(int $studenteId): array {
        $stmt = $this->db->prepare("
            SELECT si.*, p.nome AS percorso_nome, a.anno AS anno_label, p.anno_scolastico_id
            FROM studente_iscrizioni si
            JOIN percorsi_accademici p ON p.id = si.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            WHERE si.studente_id = ?
            ORDER BY si.created_at ASC
        ");
        $stmt->execute([$studenteId]);
        return $stmt->fetchAll();
    }

    /** @deprecated Usa getIscrizioni() — mantenuto per compatibilità */
    public function getIscrizione(int $studenteId): array|false {
        $rows = $this->getIscrizioni($studenteId);
        return $rows[0] ?? false;
    }

    public function addIscrizione(int $studenteId, int $percorsoId, string $dataInizio = ''): void {
        $this->db->prepare("
            INSERT IGNORE INTO studente_iscrizioni (studente_id, percorso_id, data_inizio)
            VALUES (?, ?, ?)
        ")->execute([$studenteId, $percorsoId, $dataInizio ?: null]);
    }

    public function deleteIscrizione(int $studenteId, int $percorsoId): void {
        $this->db->prepare("
            DELETE FROM studente_iscrizioni WHERE studente_id = ? AND percorso_id = ?
        ")->execute([$studenteId, $percorsoId]);
        // Rimuove anche l'anno di corso associato a quel percorso
        $this->db->prepare("
            DELETE san FROM studente_anni san
            JOIN percorso_anni pa ON pa.id = san.percorso_anno_id
            WHERE san.studente_id = ? AND pa.percorso_id = ?
        ")->execute([$studenteId, $percorsoId]);
    }

    /** @deprecated Usa addIscrizione() — mantenuto per compatibilità */
    public function setIscrizione(int $studenteId, int $percorsoId, string $dataInizio = ''): void {
        $this->addIscrizione($studenteId, $percorsoId, $dataInizio);
    }

    // ── Anno di corso (storia) ────────────────────────────────────────────────
    public function getStoriaAnni(int $studenteId): array {
        $stmt = $this->db->prepare("
            SELECT san.*, pa.numero AS anno_numero, pa.percorso_id,
                   a.anno AS anno_label, p.nome AS percorso_nome
            FROM studente_anni san
            JOIN percorso_anni pa       ON pa.id = san.percorso_anno_id
            JOIN percorsi_accademici p  ON p.id  = pa.percorso_id
            LEFT JOIN anni_scolastici a ON a.id  = san.anno_scolastico_id
            WHERE san.studente_id = ?
            ORDER BY a.anno DESC
        ");
        $stmt->execute([$studenteId]);
        return $stmt->fetchAll();
    }

    public function deleteAnnoStorico(int $id): void {
        $this->db->prepare("DELETE FROM studente_anni WHERE id = ?")->execute([$id]);
    }

    public function updateAnnoStorico(int $id, int $percorsoAnnoId, int $annoScolasticoId): void {
        $this->db->prepare("
            UPDATE studente_anni SET percorso_anno_id = ?, anno_scolastico_id = ? WHERE id = ?
        ")->execute([$percorsoAnnoId, $annoScolasticoId, $id]);
    }

    /** Restituisce tutti gli anni di corso attivi per lo studente (uno per percorso) */
    public function getAnniAttuali(int $studenteId, int $annoScolasticoId): array {
        $stmt = $this->db->prepare("
            SELECT san.*, pa.numero AS anno_numero, pa.percorso_id
            FROM studente_anni san
            JOIN percorso_anni pa ON pa.id = san.percorso_anno_id
            WHERE san.studente_id = ? AND san.anno_scolastico_id = ?
            ORDER BY san.created_at ASC
        ");
        $stmt->execute([$studenteId, $annoScolasticoId]);
        return $stmt->fetchAll();
    }

    /** @deprecated Usa getAnniAttuali() — restituisce solo il primo */
    public function getAnnoAttuale(int $studenteId, int $annoScolasticoId): array|false {
        $rows = $this->getAnniAttuali($studenteId, $annoScolasticoId);
        return $rows[0] ?? false;
    }

    // ── Studenti iscritti a uno specifico anno di corso ──────────────────────
    public function getByPercorsoAnno(int $percorsoAnnoId): array {
        $stmt = $this->db->prepare("
            SELECT s.*, a.anno AS anno_label
            FROM studente_anni san
            JOIN studenti s              ON s.id = san.studente_id
            LEFT JOIN anni_scolastici a  ON a.id = san.anno_scolastico_id
            WHERE san.percorso_anno_id = ?
            ORDER BY s.cognome ASC, s.nome ASC
        ");
        $stmt->execute([$percorsoAnnoId]);
        return $stmt->fetchAll();
    }

    public function setAnno(int $studenteId, int $percorsoAnnoId, int $annoScolasticoId): void {
        $this->db->prepare("
            INSERT INTO studente_anni (studente_id, percorso_anno_id, anno_scolastico_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE percorso_anno_id = VALUES(percorso_anno_id)
        ")->execute([$studenteId, $percorsoAnnoId, $annoScolasticoId]);
    }

    public function removeAnno(int $studenteId, int $percorsoAnnoId, int $annoScolasticoId): void {
        $this->db->prepare("
            DELETE FROM studente_anni
            WHERE studente_id = ? AND percorso_anno_id = ? AND anno_scolastico_id = ?
        ")->execute([$studenteId, $percorsoAnnoId, $annoScolasticoId]);
    }

    // ── Materie dello studente (ereditate dall'anno + bonus) ─────────────────
    public function getMaterieAnno(int $percorsoAnnoId, int $studenteId = 0): array {
        $stmt = $this->db->prepare("
            SELECT m.*, pam.id AS pam_id
            FROM percorso_anno_materie pam
            JOIN materie m ON m.id = pam.materia_id
            WHERE pam.anno_id = ?
            ORDER BY m.nome ASC
        ");
        $stmt->execute([$percorsoAnnoId]);
        $materie = $stmt->fetchAll();

        if ($studenteId > 0) {
            foreach ($materie as &$mat) {
                $mat['voti'] = $this->getVotiMateria($studenteId, $mat['pam_id']);
            }
        }
        return $materie;
    }

    // ── Materie bonus ─────────────────────────────────────────────────────────
    public function getMaterieBonus(int $studenteId, int $annoScolasticoId): array {
        $stmt = $this->db->prepare("
            SELECT smb.*, m.nome AS materia_nome, m.codice AS materia_codice
            FROM studente_materie_bonus smb
            JOIN materie m ON m.id = smb.materia_id
            WHERE smb.studente_id = ? AND smb.anno_scolastico_id = ?
            ORDER BY m.nome ASC
        ");
        $stmt->execute([$studenteId, $annoScolasticoId]);
        return $stmt->fetchAll();
    }

    public function addBonus(int $studenteId, int $materiaId, int $annoScolasticoId, string $note = ''): void {
        $this->db->prepare("
            INSERT IGNORE INTO studente_materie_bonus (studente_id, materia_id, anno_scolastico_id, note)
            VALUES (?, ?, ?, ?)
        ")->execute([$studenteId, $materiaId, $annoScolasticoId, $note ?: null]);
    }

    public function deleteBonus(int $id): void {
        $this->db->prepare("DELETE FROM studente_materie_bonus WHERE id = ?")->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM studenti")->fetchColumn();
    }
}
