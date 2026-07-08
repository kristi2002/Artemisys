<?php

class EsameDiStato {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esami_di_stato (
                id                INT AUTO_INCREMENT PRIMARY KEY,
                denominazione     VARCHAR(255) NOT NULL,
                data              DATE NULL,
                ora_inizio        TIME NULL,
                riferimento_tipo  ENUM('percorso','classe') NOT NULL DEFAULT 'classe',
                percorso_id       INT NOT NULL,
                percorso_anno_id  INT NULL,
                rappresentante_id INT NULL,
                stato             ENUM('programmato','in_corso','completato','annullato')
                                  NOT NULL DEFAULT 'programmato',
                note              TEXT NULL,
                created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esame_di_stato_commissione (
                id                INT AUTO_INCREMENT PRIMARY KEY,
                esame_di_stato_id INT NOT NULL,
                insegnante_id     INT NOT NULL,
                ruolo             ENUM('presidente','commissario','segretario')
                                  NOT NULL DEFAULT 'commissario',
                created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY u_eds_ins (esame_di_stato_id, insegnante_id)
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esame_di_stato_iscrizioni (
                id                INT AUTO_INCREMENT PRIMARY KEY,
                esame_di_stato_id INT NOT NULL,
                studente_id       INT NOT NULL,
                voto_finale       DECIMAL(4,2) NULL,
                esito             ENUM('ammesso','non_ammesso','assente') NULL,
                note              VARCHAR(255) NULL,
                created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY u_eds_studente (esame_di_stato_id, studente_id)
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esame_di_stato_allegati (
                id                INT AUTO_INCREMENT PRIMARY KEY,
                esame_di_stato_id INT NOT NULL,
                filename          VARCHAR(255) NOT NULL,
                original_name     VARCHAR(255) NOT NULL,
                mime_type         VARCHAR(100) NULL,
                created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS esame_di_stato_prove (
                id                INT AUTO_INCREMENT PRIMARY KEY,
                esame_di_stato_id INT NOT NULL,
                data              DATE NULL,
                ora_inizio        TIME NULL,
                ora_fine          TIME NULL,
                tipo_prova        VARCHAR(20) NOT NULL DEFAULT 'P/S/O',
                ordine            TINYINT NOT NULL DEFAULT 1,
                created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->ensureColumn('esami_di_stato', 'ente_gestore',  'VARCHAR(255) NULL');
        $this->ensureColumn('esami_di_stato', 'sede_presso',   'VARCHAR(255) NULL');
        $this->ensureColumn('esami_di_stato', 'sede_via',      'VARCHAR(255) NULL');
        $this->ensureColumn('esami_di_stato', 'sede_comune',   'VARCHAR(100) NULL');
        $this->ensureColumn('esami_di_stato', 'sede_telefono', 'VARCHAR(30) NULL');
        $this->ensureColumn('esami_di_stato', 'cod_corso',     'VARCHAR(50) NULL');
        $this->ensureColumn('esami_di_stato', 'cod_did_reg',   'VARCHAR(50) NULL');
        $this->ensureColumn('esami_di_stato', 'ore_corso',     'INT NULL');

        $this->ensureColumn('esame_di_stato_iscrizioni', 'voto_teorico', 'DECIMAL(4,2) NULL');
        $this->ensureColumn('esame_di_stato_iscrizioni', 'voto_pratico', 'DECIMAL(4,2) NULL');
        $this->ensureColumn('esame_di_stato_commissione', 'in_rappresentanza', 'VARCHAR(100) NULL');

        // Normalizza tipo_prova: imposta sempre P/S/O
        try {
            $this->db->exec("UPDATE esame_di_stato_prove SET tipo_prova = 'P/S/O' WHERE tipo_prova != 'P/S/O'");
        } catch (\Throwable $e) {}
    }

    private function ensureColumn(string $table, string $column, string $definition): void {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        if ((int)$stmt->fetchColumn() === 0) {
            $this->db->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        }
    }

    public function getAll(string $search = ''): array {
        $sql = "
            SELECT e.*,
                   p.nome    AS percorso_nome,
                   a.anno    AS anno_label,
                   pa.numero AS anno_numero,
                   ri.cognome AS rapp_cognome,
                   ri.nome    AS rapp_nome,
                   COUNT(DISTINCT ei.id) AS num_iscritti,
                   COUNT(DISTINCT CASE WHEN ei.esito IS NOT NULL
                         AND ei.esito != 'assente' THEN ei.id END) AS num_valutati
            FROM esami_di_stato e
            JOIN percorsi_accademici p     ON p.id  = e.percorso_id
            LEFT JOIN anni_scolastici a    ON a.id  = p.anno_scolastico_id
            LEFT JOIN percorso_anni pa     ON pa.id = e.percorso_anno_id
            LEFT JOIN insegnanti ri        ON ri.id = e.rappresentante_id
            LEFT JOIN esame_di_stato_iscrizioni ei ON ei.esame_di_stato_id = e.id
        ";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE e.denominazione LIKE ? OR p.nome LIKE ?";
            $like = "%{$search}%";
            $params = [$like, $like];
        }
        $sql .= " GROUP BY e.id ORDER BY e.data DESC, e.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT e.*,
                   p.nome             AS percorso_nome,
                   p.data_inizio_anno AS percorso_data_inizio,
                   p.data_fine_anno   AS percorso_data_fine,
                   a.anno             AS anno_label,
                   pa.numero          AS anno_numero,
                   ri.cognome         AS rapp_cognome,
                   ri.nome            AS rapp_nome,
                   s.nome             AS sede_nome,
                   s.via              AS sede_via,
                   s.comune           AS sede_comune,
                   s.telefono         AS sede_telefono
            FROM esami_di_stato e
            JOIN percorsi_accademici p     ON p.id  = e.percorso_id
            LEFT JOIN anni_scolastici a    ON a.id  = p.anno_scolastico_id
            LEFT JOIN percorso_anni pa     ON pa.id = e.percorso_anno_id
            LEFT JOIN insegnanti ri        ON ri.id = e.rappresentante_id
            LEFT JOIN sedi s               ON s.id  = p.sede_id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByPercorso(int $percorsoId, ?int $percorsoAnnoId = null): array|false {
        $sql = "
            SELECT e.*,
                   p.nome    AS percorso_nome,
                   a.anno    AS anno_label,
                   pa.numero AS anno_numero
            FROM esami_di_stato e
            JOIN percorsi_accademici p  ON p.id = e.percorso_id
            LEFT JOIN anni_scolastici a ON a.id = p.anno_scolastico_id
            LEFT JOIN percorso_anni pa  ON pa.id = e.percorso_anno_id
            WHERE e.percorso_id = ?
        ";
        $params = [$percorsoId];

        // Se lo studente ha un anno di corso specifico, filtra per quello
        if ($percorsoAnnoId) {
            $sql .= " AND e.percorso_anno_id = ?";
            $params[] = $percorsoAnnoId;
        }

        $sql .= " ORDER BY e.created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO esami_di_stato
                (denominazione, anno_formativo, tipo, cod_corso, cod_did_reg, ore_corso,
                 ente_gestore, sede_presso, sede_via, sede_comune, sede_telefono,
                 data_inizio_anno, data_fine_anno,
                 data, ora_inizio, riferimento_tipo, percorso_id,
                 percorso_anno_id, rappresentante_id, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['denominazione'],
            $data['anno_formativo']  ?: null,
            $data['tipo']            ?: null,
            $data['cod_corso']       ?: null,
            $data['cod_did_reg']     ?: null,
            $data['ore_corso']       ?: null,
            $data['ente_gestore']    ?: null,
            $data['sede_presso']     ?: null,
            $data['sede_via']        ?: null,
            $data['sede_comune']     ?: null,
            $data['sede_telefono']   ?: null,
            $data['data_inizio_anno'] ?: null,
            $data['data_fine_anno']   ?: null,
            $data['data']           ?: null,
            $data['ora_inizio']     ?: null,
            $data['riferimento_tipo'],
            $data['percorso_id'],
            $data['percorso_anno_id']  ?: null,
            $data['rappresentante_id'] ?: null,
            $data['note']              ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function iscriviStudentiAuto(int $esameId, string $tipo, int $percorsoId, ?int $percorsoAnnoId): int {
        if ($tipo === 'classe' && $percorsoAnnoId) {
            $stmt = $this->db->prepare("
                SELECT studente_id FROM studente_anni WHERE percorso_anno_id = ?
            ");
            $stmt->execute([$percorsoAnnoId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT DISTINCT studente_id FROM studente_iscrizioni WHERE percorso_id = ?
            ");
            $stmt->execute([$percorsoId]);
        }

        $studenti = $stmt->fetchAll();
        $ins = $this->db->prepare("
            INSERT IGNORE INTO esame_di_stato_iscrizioni (esame_di_stato_id, studente_id)
            VALUES (?, ?)
        ");
        foreach ($studenti as $s) {
            $ins->execute([$esameId, $s['studente_id']]);
        }
        return count($studenti);
    }

    public function getIscrizioni(int $esameId): array {
        $stmt = $this->db->prepare("
            SELECT ei.*, s.cognome, s.nome, s.codice_fiscale,
                   s.data_nascita, s.indirizzo
            FROM esame_di_stato_iscrizioni ei
            JOIN studenti s ON s.id = ei.studente_id
            WHERE ei.esame_di_stato_id = ?
            ORDER BY s.cognome ASC, s.nome ASC
        ");
        $stmt->execute([$esameId]);
        return $stmt->fetchAll();
    }

    public function getStudentiNonIscritti(int $esameId): array {
        $stmt = $this->db->prepare("
            SELECT s.id, s.cognome, s.nome, s.codice_fiscale
            FROM studenti s
            WHERE s.id NOT IN (
                SELECT studente_id FROM esame_di_stato_iscrizioni WHERE esame_di_stato_id = ?
            )
            ORDER BY s.cognome ASC, s.nome ASC
        ");
        $stmt->execute([$esameId]);
        return $stmt->fetchAll();
    }

    public function iscriviStudente(int $esameId, int $studenteId): bool {
        try {
            $this->db->prepare("
                INSERT INTO esame_di_stato_iscrizioni (esame_di_stato_id, studente_id)
                VALUES (?, ?)
            ")->execute([$esameId, $studenteId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function disiscriviStudente(int $iscrId): void {
        $this->db->prepare("DELETE FROM esame_di_stato_iscrizioni WHERE id = ?")->execute([$iscrId]);
    }

    public function saveEsiti(array $righe): void {
        $stmt = $this->db->prepare("
            UPDATE esame_di_stato_iscrizioni
            SET voto_teorico = ?, voto_pratico = ?, voto_finale = ?, esito = ?, note = ?
            WHERE id = ?
        ");
        foreach ($righe as $id => $dati) {
            $esito = $dati['esito'] ?? null;
            if (($dati['assente'] ?? 0) == 1) {
                $esito = 'assente';
            }
            $votoTeorico = ($dati['voto_teorico'] ?? '') !== '' ? $dati['voto_teorico'] : null;
            $votoPratico = ($dati['voto_pratico'] ?? '') !== '' ? $dati['voto_pratico'] : null;
            // voto_finale = media dei due voti se entrambi presenti, altrimenti quello disponibile
            $votoFinale = null;
            if ($votoTeorico !== null && $votoPratico !== null) {
                $votoFinale = round(((float)$votoTeorico + (float)$votoPratico) / 2, 2);
            } elseif ($votoTeorico !== null) {
                $votoFinale = $votoTeorico;
            } elseif ($votoPratico !== null) {
                $votoFinale = $votoPratico;
            }
            $stmt->execute([
                $votoTeorico,
                $votoPratico,
                $votoFinale,
                $esito ?: null,
                $dati['note'] ?: null,
                (int)$id,
            ]);
        }
    }

    public function getCommissione(int $esameId): array {
        $stmt = $this->db->prepare("
            SELECT ec.id AS link_id, ec.ruolo, ec.in_rappresentanza, i.id, i.cognome, i.nome
            FROM esame_di_stato_commissione ec
            JOIN insegnanti i ON i.id = ec.insegnante_id
            WHERE ec.esame_di_stato_id = ?
            ORDER BY FIELD(ec.ruolo, 'presidente', 'segretario', 'commissario'),
                     i.cognome ASC, i.nome ASC
        ");
        $stmt->execute([$esameId]);
        return $stmt->fetchAll();
    }

    public function addCommissario(int $esameId, int $insegnanteId, string $ruolo = 'commissario', ?string $inRappresentanza = null): void {
        try {
            $this->db->prepare("
                INSERT INTO esame_di_stato_commissione (esame_di_stato_id, insegnante_id, ruolo, in_rappresentanza)
                VALUES (?, ?, ?, ?)
            ")->execute([$esameId, $insegnanteId, $ruolo, $inRappresentanza ?: null]);
        } catch (PDOException $e) { /* duplicato */ }
    }

    public function removeCommissario(int $esameId, int $insegnanteId): void {
        $this->db->prepare("
            DELETE FROM esame_di_stato_commissione
            WHERE esame_di_stato_id = ? AND insegnante_id = ?
        ")->execute([$esameId, $insegnanteId]);
    }

    public function getAllegati(int $esameId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM esame_di_stato_allegati
            WHERE esame_di_stato_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$esameId]);
        return $stmt->fetchAll();
    }

    public function findAllegato(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM esame_di_stato_allegati WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addAllegato(int $esameId, string $filename, string $originalName, ?string $mime): void {
        $this->db->prepare("
            INSERT INTO esame_di_stato_allegati (esame_di_stato_id, filename, original_name, mime_type)
            VALUES (?, ?, ?, ?)
        ")->execute([$esameId, $filename, $originalName, $mime]);
    }

    public function deleteAllegato(int $id): void {
        $this->db->prepare("DELETE FROM esame_di_stato_allegati WHERE id = ?")->execute([$id]);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM esame_di_stato_iscrizioni WHERE esame_di_stato_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM esame_di_stato_commissione WHERE esame_di_stato_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM esame_di_stato_allegati WHERE esame_di_stato_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM esami_di_stato WHERE id = ?")->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM esami_di_stato")->fetchColumn();
    }

    public function update(int $id, array $data): void {
        $this->db->prepare("
            UPDATE esami_di_stato SET
                denominazione    = ?,
                tipo             = ?,
                cod_corso        = ?,
                cod_did_reg      = ?,
                ore_corso        = ?,
                ente_gestore     = ?,
                data             = ?,
                ora_inizio       = ?,
                data_inizio_anno = ?,
                data_fine_anno   = ?,
                stato            = ?,
                note             = ?
            WHERE id = ?
        ")->execute([
            $data['denominazione']    ?: null,
            $data['tipo']             ?: null,
            $data['cod_corso']        ?: null,
            $data['cod_did_reg']      ?: null,
            $data['ore_corso'] !== '' ? (int)$data['ore_corso'] : null,
            $data['ente_gestore']     ?: null,
            $data['data']             ?: null,
            $data['ora_inizio']       ?: null,
            $data['data_inizio_anno'] ?: null,
            $data['data_fine_anno']   ?: null,
            $data['stato']            ?: 'programmato',
            $data['note']             ?: null,
            $id,
        ]);
    }

    public function updateDatiDocumento(int $id, array $data): void {
        $this->db->prepare("
            UPDATE esami_di_stato SET
                ente_gestore  = ?, sede_presso = ?, sede_via = ?,
                sede_comune   = ?, sede_telefono = ?,
                cod_corso     = ?, cod_did_reg = ?,
                ore_corso     = ?
            WHERE id = ?
        ")->execute([
            $data['ente_gestore']  ?: null,
            $data['sede_presso']   ?: null,
            $data['sede_via']      ?: null,
            $data['sede_comune']   ?: null,
            $data['sede_telefono'] ?: null,
            $data['cod_corso']     ?: null,
            $data['cod_did_reg']   ?: null,
            $data['ore_corso']     !== '' ? (int)$data['ore_corso'] : null,
            $id,
        ]);
    }

    public function getProve(int $esameId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM esame_di_stato_prove
            WHERE esame_di_stato_id = ?
            ORDER BY ordine ASC, id ASC
        ");
        $stmt->execute([$esameId]);
        return $stmt->fetchAll();
    }

    public function saveProve(int $esameId, array $prove): void {
        $this->db->prepare("DELETE FROM esame_di_stato_prove WHERE esame_di_stato_id = ?")
                 ->execute([$esameId]);

        $stmt = $this->db->prepare("
            INSERT INTO esame_di_stato_prove
                (esame_di_stato_id, data, ora_inizio, ora_fine, tipo_prova, ordine)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $ordine = 1;
        foreach ($prove as $prova) {
            $hasData = !empty($prova['data']) || !empty($prova['tipo_prova']);
            if (!$hasData) {
                continue;
            }
            $stmt->execute([
                $esameId,
                $prova['data']       ?: null,
                $prova['ora_inizio'] ?: null,
                $prova['ora_fine']   ?: null,
                $prova['tipo_prova'] ?: 'P/S/O',
                $ordine++,
            ]);
        }
    }
}
