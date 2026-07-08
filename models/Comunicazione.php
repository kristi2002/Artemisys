<?php

class Comunicazione {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS comunicazioni (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                titolo        VARCHAR(255) NOT NULL,
                contenuto     TEXT NOT NULL,
                tipo          ENUM('avviso','urgente','evento','generale') NOT NULL DEFAULT 'generale',
                target        ENUM('tutti','studenti','docenti') NOT NULL DEFAULT 'tutti',
                autore_id     INT NULL,
                autore_nome   VARCHAR(150) NULL,
                pubblicata    TINYINT(1) NOT NULL DEFAULT 1,
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
    }

    public function getAll(string $target = 'tutti'): array {
        if ($target === 'tutti') {
            $stmt = $this->db->query("
                SELECT * FROM comunicazioni
                WHERE pubblicata = 1
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll();
        }
        $stmt = $this->db->prepare("
            SELECT * FROM comunicazioni
            WHERE pubblicata = 1 AND (target = 'tutti' OR target = ?)
            ORDER BY created_at DESC
        ");
        $stmt->execute([$target]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM comunicazioni WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO comunicazioni (titolo, contenuto, tipo, target, autore_id, autore_nome)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['titolo'],
            $data['contenuto'],
            $data['tipo']   ?? 'generale',
            $data['target'] ?? 'tutti',
            $data['autore_id']   ?? null,
            $data['autore_nome'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM comunicazioni WHERE id = ?")->execute([$id]);
    }

    public function count(string $target = 'tutti'): int {
        if ($target === 'tutti') {
            return (int)$this->db->query("SELECT COUNT(*) FROM comunicazioni WHERE pubblicata = 1")->fetchColumn();
        }
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM comunicazioni
            WHERE pubblicata = 1 AND (target = 'tutti' OR target = ?)
        ");
        $stmt->execute([$target]);
        return (int)$stmt->fetchColumn();
    }
}
