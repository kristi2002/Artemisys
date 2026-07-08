<?php

class Evento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS eventi (
                id                  INT AUTO_INCREMENT PRIMARY KEY,
                titolo              VARCHAR(255) NOT NULL,
                descrizione         TEXT NULL,
                tipo                ENUM('workshop','seminario','open_day','competizione','sociale','altro')
                                    NOT NULL DEFAULT 'altro',
                data_evento         DATE NOT NULL,
                ora_inizio          TIME NULL,
                ora_fine            TIME NULL,
                luogo               VARCHAR(255) NULL,
                max_iscritti        INT NULL,
                scadenza_iscrizioni DATE NULL,
                relatore            VARCHAR(255) NULL,
                stato               ENUM('aperto','chiuso','annullato','svolto')
                                    NOT NULL DEFAULT 'aperto',
                immagine            VARCHAR(255) NULL,
                created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS evento_iscrizioni (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                evento_id   INT NOT NULL,
                studente_id INT NOT NULL,
                presente    TINYINT(1) NOT NULL DEFAULT 0,
                note        VARCHAR(255) NULL,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY u_evento_studente (evento_id, studente_id),
                FOREIGN KEY (evento_id) REFERENCES eventi(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");
    }

    public function getAll(string $search = '', string $stato = '', string $tipo = ''): array {
        $sql = "
            SELECT e.*,
                   COUNT(ei.id) AS num_iscritti,
                   COALESCE(SUM(ei.presente), 0) AS num_presenti
            FROM eventi e
            LEFT JOIN evento_iscrizioni ei ON ei.evento_id = e.id
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (e.titolo LIKE ? OR e.luogo LIKE ? OR e.relatore LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if ($stato !== '') {
            $sql .= " AND e.stato = ?";
            $params[] = $stato;
        }
        if ($tipo !== '') {
            $sql .= " AND e.tipo = ?";
            $params[] = $tipo;
        }

        $sql .= " GROUP BY e.id ORDER BY e.data_evento DESC, e.ora_inizio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT e.*,
                   COUNT(ei.id) AS num_iscritti,
                   COALESCE(SUM(ei.presente), 0) AS num_presenti
            FROM eventi e
            LEFT JOIN evento_iscrizioni ei ON ei.evento_id = e.id
            WHERE e.id = ?
            GROUP BY e.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getIscrizioni(int $eventoId): array {
        $stmt = $this->db->prepare("
            SELECT ei.*, s.cognome, s.nome, s.codice_fiscale, s.email
            FROM evento_iscrizioni ei
            JOIN studenti s ON s.id = ei.studente_id
            WHERE ei.evento_id = ?
            ORDER BY s.cognome ASC, s.nome ASC
        ");
        $stmt->execute([$eventoId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO eventi
                (titolo, descrizione, tipo, data_evento, ora_inizio, ora_fine,
                 luogo, max_iscritti, scadenza_iscrizioni, relatore, stato)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['titolo'],
            $data['descrizione']         ?: null,
            $data['tipo'],
            $data['data_evento'],
            $data['ora_inizio']          ?: null,
            $data['ora_fine']            ?: null,
            $data['luogo']               ?: null,
            $data['max_iscritti']        ?: null,
            $data['scadenza_iscrizioni'] ?: null,
            $data['relatore']            ?: null,
            $data['stato'] ?? 'aperto',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $this->db->prepare("
            UPDATE eventi SET
                titolo              = ?,
                descrizione         = ?,
                tipo                = ?,
                data_evento         = ?,
                ora_inizio          = ?,
                ora_fine            = ?,
                luogo               = ?,
                max_iscritti        = ?,
                scadenza_iscrizioni = ?,
                relatore            = ?,
                stato               = ?
            WHERE id = ?
        ")->execute([
            $data['titolo'],
            $data['descrizione']         ?: null,
            $data['tipo'],
            $data['data_evento'],
            $data['ora_inizio']          ?: null,
            $data['ora_fine']            ?: null,
            $data['luogo']               ?: null,
            $data['max_iscritti']        ?: null,
            $data['scadenza_iscrizioni'] ?: null,
            $data['relatore']            ?: null,
            $data['stato'] ?? 'aperto',
            $id,
        ]);
    }

    public function setStato(int $id, string $stato): void {
        if (!in_array($stato, ['aperto','chiuso','annullato','svolto'], true)) return;
        $this->db->prepare("UPDATE eventi SET stato = ? WHERE id = ?")->execute([$stato, $id]);
    }

    public function iscriviStudente(int $eventoId, int $studenteId): bool {
        // Controlla capienza
        $ev = $this->findById($eventoId);
        if (!$ev) return false;
        if ($ev['max_iscritti'] !== null && $ev['num_iscritti'] >= $ev['max_iscritti']) return false;

        try {
            $this->db->prepare("
                INSERT INTO evento_iscrizioni (evento_id, studente_id) VALUES (?, ?)
            ")->execute([$eventoId, $studenteId]);
            return true;
        } catch (PDOException $e) {
            return false; // duplicato
        }
    }

    public function getLastIscrId(int $eventoId, int $studenteId): int {
        $stmt = $this->db->prepare("
            SELECT id FROM evento_iscrizioni
            WHERE evento_id = ? AND studente_id = ?
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$eventoId, $studenteId]);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    public function disiscriviStudente(int $iscrId): void {
        $this->db->prepare("DELETE FROM evento_iscrizioni WHERE id = ?")->execute([$iscrId]);
    }

    public function setPresenza(int $iscrId, bool $presente): void {
        $this->db->prepare("UPDATE evento_iscrizioni SET presente = ? WHERE id = ?")
                 ->execute([$presente ? 1 : 0, $iscrId]);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM eventi WHERE id = ?")->execute([$id]);
    }

    public function getStudenti(): array {
        return $this->db->query("
            SELECT id, cognome, nome FROM studenti ORDER BY cognome ASC, nome ASC
        ")->fetchAll();
    }

    public function getStudentiNonIscritti(int $eventoId): array {
        $stmt = $this->db->prepare("
            SELECT s.id, s.cognome, s.nome
            FROM studenti s
            WHERE s.id NOT IN (SELECT studente_id FROM evento_iscrizioni WHERE evento_id = ?)
            ORDER BY s.cognome ASC, s.nome ASC
        ");
        $stmt->execute([$eventoId]);
        return $stmt->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM eventi")->fetchColumn();
    }
}
