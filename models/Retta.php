<?php

class Retta {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rette (
                id              INT AUTO_INCREMENT PRIMARY KEY,
                studente_id     INT NOT NULL,
                importo_totale  DECIMAL(10,2) NOT NULL,
                num_rate        INT NOT NULL DEFAULT 1,
                cadenza         ENUM('mensile','bimestrale','trimestrale') NOT NULL DEFAULT 'mensile',
                note            TEXT NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rate_pagamento (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                retta_id       INT NOT NULL,
                numero         INT NOT NULL,
                importo        DECIMAL(10,2) NOT NULL,
                scadenza       DATE NULL,
                pagata         TINYINT(1) NOT NULL DEFAULT 0,
                data_pagamento DATE NULL,
                metodo         VARCHAR(50) NULL,
                note           VARCHAR(255) NULL,
                file_pdf       VARCHAR(255) NULL,
                file_originale VARCHAR(255) NULL,
                created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (retta_id) REFERENCES rette(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");
    }

    public function getAll(string $search = ''): array {
        $sql = "
            SELECT r.*, s.cognome, s.nome,
                   COUNT(rp.id) AS tot_rate,
                   COALESCE(SUM(CASE WHEN rp.pagata = 1 THEN 1 ELSE 0 END), 0) AS rate_pagate,
                   COALESCE(SUM(CASE WHEN rp.pagata = 1 THEN rp.importo ELSE 0 END), 0) AS pagato,
                   COALESCE(SUM(CASE WHEN rp.pagata = 0 THEN rp.importo ELSE 0 END), 0) AS da_pagare
            FROM rette r
            JOIN studenti s ON s.id = r.studente_id
            LEFT JOIN rate_pagamento rp ON rp.retta_id = r.id
        ";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE s.cognome LIKE ? OR s.nome LIKE ?";
            $like = "%{$search}%";
            $params = [$like, $like];
        }
        $sql .= " GROUP BY r.id ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT r.*, s.cognome, s.nome, s.codice_fiscale
            FROM rette r
            JOIN studenti s ON s.id = r.studente_id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getRate(int $rettaId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM rate_pagamento WHERE retta_id = ? ORDER BY numero ASC
        ");
        $stmt->execute([$rettaId]);
        return $stmt->fetchAll();
    }

    public function findRata(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM rate_pagamento WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO rette (studente_id, importo_totale, num_rate, cadenza, note)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $data['studente_id'],
            $data['importo_totale'],
            $data['num_rate'],
            $data['cadenza'],
            $data['note'] ?: null,
        ]);
        $rettaId = (int)$this->db->lastInsertId();

        $this->generaRate(
            $rettaId,
            (float)$data['importo_totale'],
            (int)$data['num_rate'],
            $data['data_prima_scadenza'] ?: null,
            $data['cadenza']
        );

        return $rettaId;
    }

    private function generaRate(int $rettaId, float $totale, int $num, ?string $primaSc, string $cadenza): void {
        $importoBase = round($totale / $num, 2);
        $somma       = $importoBase * ($num - 1);
        $ultimo      = round($totale - $somma, 2);

        $mesiPasso = match($cadenza) {
            'bimestrale'  => 2,
            'trimestrale' => 3,
            default       => 1,
        };

        $ins = $this->db->prepare("
            INSERT INTO rate_pagamento (retta_id, numero, importo, scadenza)
            VALUES (?, ?, ?, ?)
        ");

        for ($i = 1; $i <= $num; $i++) {
            $imp = $i === $num ? $ultimo : $importoBase;
            $sc  = null;
            if ($primaSc) {
                $offset = ($i - 1) * $mesiPasso;
                $sc = date('Y-m-d', strtotime($primaSc . ' +' . $offset . ' months'));
            }
            $ins->execute([$rettaId, $i, $imp, $sc]);
        }
    }

    public function markPagata(int $rataId, string $dataPagamento, string $metodo, string $note = ''): void {
        $this->db->prepare("
            UPDATE rate_pagamento
            SET pagata = 1, data_pagamento = ?, metodo = ?, note = ?
            WHERE id = ?
        ")->execute([
            $dataPagamento ?: date('Y-m-d'),
            $metodo ?: null,
            $note ?: null,
            $rataId,
        ]);
    }

    public function markNonPagata(int $rataId): void {
        $this->db->prepare("
            UPDATE rate_pagamento
            SET pagata = 0, data_pagamento = NULL, metodo = NULL
            WHERE id = ?
        ")->execute([$rataId]);
    }

    public function setFile(int $rataId, string $fileName, string $fileOriginale): void {
        $this->db->prepare("
            UPDATE rate_pagamento SET file_pdf = ?, file_originale = ? WHERE id = ?
        ")->execute([$fileName, $fileOriginale, $rataId]);
    }

    public function removeFile(int $rataId): void {
        $this->db->prepare("
            UPDATE rate_pagamento SET file_pdf = NULL, file_originale = NULL WHERE id = ?
        ")->execute([$rataId]);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM rette WHERE id = ?")->execute([$id]);
    }

    public function getStudenti(): array {
        return $this->db->query("
            SELECT id, cognome, nome FROM studenti ORDER BY cognome ASC, nome ASC
        ")->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM rette")->fetchColumn();
    }
}
