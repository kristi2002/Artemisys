<?php

class Materia {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTable(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS materie (
                id           INT AUTO_INCREMENT PRIMARY KEY,
                codice       VARCHAR(20)  NOT NULL UNIQUE,
                nome         VARCHAR(150) NOT NULL,
                codice_regionale VARCHAR(20) NULL,
                created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
    }

    public function getAll(string $search = ''): array {
        $sql = "SELECT * FROM materie";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE nome LIKE ? OR codice LIKE ? OR codice_regionale LIKE ?";
            $like = "%{$search}%";
            $params = [$like, $like, $like];
        }
        $sql .= " ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM materie")->fetchColumn();
    }

    public function codiceExists(string $codice, int $excludeId = 0): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM materie WHERE codice = ? AND id != ?");
        $stmt->execute([$codice, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): void {
        $this->db->prepare("
            INSERT INTO materie (codice, nome, codice_regionale)
            VALUES (?, ?, ?)
        ")->execute([
            strtoupper($data['codice']),
            $data['nome'],
            !empty($data['codice_regionale']) ? strtoupper($data['codice_regionale']) : null,
        ]);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM materie WHERE id = ?")->execute([$id]);
    }

    // Genera un codice suggerito dalle iniziali del nome
    public function suggerisciCodice(string $nome): string {
        $words = preg_split('/\s+/', trim($nome));
        $base  = '';
        foreach ($words as $w) {
            $base .= strtoupper(mb_substr($w, 0, 1));
        }
        $base = $base ?: 'MAT';

        $candidate = $base;
        $i = 2;
        while ($this->codiceExists($candidate)) {
            $candidate = $base . $i;
            $i++;
        }
        return $candidate;
    }
}
