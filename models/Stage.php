<?php

class Stage {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS stage (
                id               INT AUTO_INCREMENT PRIMARY KEY,
                studente_id      INT NOT NULL,
                azienda          VARCHAR(255) NOT NULL,
                citta            VARCHAR(100) NULL,
                settore          VARCHAR(150) NULL,
                tutor_aziendale  VARCHAR(255) NULL,
                tutor_scolastico VARCHAR(255) NULL,
                data_inizio      DATE NULL,
                data_fine        DATE NULL,
                monte_ore        INT NULL DEFAULT 150,
                step             TINYINT NOT NULL DEFAULT 1,
                note_candidatura TEXT NULL,
                data_convenzione DATE NULL,
                note_convenzione TEXT NULL,
                ore_svolte       INT NOT NULL DEFAULT 0,
                note_tirocinio   TEXT NULL,
                voto_finale      DECIMAL(4,2) NULL,
                giudizio         ENUM('insufficiente','sufficiente','buono','ottimo') NULL,
                note_valutazione TEXT NULL,
                created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS stage_allegati (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                stage_id      INT NOT NULL,
                categoria     ENUM('convenzione','candidatura','valutazione','altro') NOT NULL DEFAULT 'convenzione',
                filename      VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                mime_type     VARCHAR(100) NULL,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (stage_id) REFERENCES stage(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");
    }

    // ── Allegati ─────────────────────────────────────────────────────────────
    public function getAllegati(int $stageId, string $categoria = ''): array {
        if ($categoria !== '') {
            $stmt = $this->db->prepare("
                SELECT * FROM stage_allegati
                WHERE stage_id = ? AND categoria = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$stageId, $categoria]);
        } else {
            $stmt = $this->db->prepare("
                SELECT * FROM stage_allegati
                WHERE stage_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$stageId]);
        }
        return $stmt->fetchAll();
    }

    public function findAllegato(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM stage_allegati WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addAllegato(int $stageId, string $categoria, string $filename, string $originalName, ?string $mime): void {
        $this->db->prepare("
            INSERT INTO stage_allegati (stage_id, categoria, filename, original_name, mime_type)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([$stageId, $categoria, $filename, $originalName, $mime]);
    }

    public function deleteAllegato(int $id): void {
        $this->db->prepare("DELETE FROM stage_allegati WHERE id = ?")->execute([$id]);
    }

    public function getAll(string $search = ''): array {
        $sql = "
            SELECT st.*, s.cognome, s.nome, s.codice_fiscale
            FROM stage st
            JOIN studenti s ON s.id = st.studente_id
        ";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE s.cognome LIKE ? OR s.nome LIKE ? OR st.azienda LIKE ?";
            $like = "%{$search}%";
            $params = [$like, $like, $like];
        }
        $sql .= " ORDER BY st.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT st.*, s.cognome, s.nome, s.codice_fiscale,
                   s.email, s.data_nascita
            FROM stage st
            JOIN studenti s ON s.id = st.studente_id
            WHERE st.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO stage (studente_id, azienda, citta, settore, note_candidatura)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $data['studente_id'],
            $data['azienda'],
            $data['citta']            ?: null,
            $data['settore']          ?: null,
            $data['note_candidatura'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStep(int $id, int $step, array $data): void {
        match ($step) {
            1 => $this->db->prepare("
                    UPDATE stage SET azienda=?, citta=?, settore=?, note_candidatura=?
                    WHERE id=?
                 ")->execute([$data['azienda'], $data['citta'] ?: null, $data['settore'] ?: null,
                               $data['note_candidatura'] ?: null, $id]),

            2 => $this->db->prepare("
                    UPDATE stage SET tutor_aziendale=?, tutor_scolastico=?,
                                     data_inizio=?, data_fine=?, monte_ore=?,
                                     data_convenzione=?, note_convenzione=?
                    WHERE id=?
                 ")->execute([$data['tutor_aziendale'] ?: null, $data['tutor_scolastico'] ?: null,
                               $data['data_inizio'] ?: null, $data['data_fine'] ?: null,
                               $data['monte_ore']   ?: null,
                               $data['data_convenzione'] ?: null, $data['note_convenzione'] ?: null, $id]),

            3 => $this->db->prepare("
                    UPDATE stage SET ore_svolte=?, note_tirocinio=?
                    WHERE id=?
                 ")->execute([(int)$data['ore_svolte'], $data['note_tirocinio'] ?: null, $id]),

            4 => $this->db->prepare("
                    UPDATE stage SET voto_finale=?, giudizio=?, note_valutazione=?
                    WHERE id=?
                 ")->execute([$data['voto_finale'] !== '' ? $data['voto_finale'] : null,
                               $data['giudizio'] ?: null, $data['note_valutazione'] ?: null, $id]),

            default => null,
        };
    }

    public function nextStep(int $id): void {
        $this->db->prepare("UPDATE stage SET step = LEAST(step + 1, 4) WHERE id = ?")
                 ->execute([$id]);
    }

    public function prevStep(int $id): void {
        $this->db->prepare("UPDATE stage SET step = GREATEST(step - 1, 1) WHERE id = ?")
                 ->execute([$id]);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM stage WHERE id = ?")->execute([$id]);
    }

    public function getStudentiDisponibili(): array {
        return $this->db->query("
            SELECT s.id, s.cognome, s.nome
            FROM studenti s
            ORDER BY s.cognome ASC, s.nome ASC
        ")->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM stage")->fetchColumn();
    }
}
