<?php

class AnnoScolastico {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTable(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS anni_scolastici (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                anno       VARCHAR(20) NOT NULL UNIQUE,
                attivo     TINYINT(1)  NOT NULL DEFAULT 0,
                created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM anni_scolastici ORDER BY anno DESC")->fetchAll();
    }

    public function getAttivo(): array|false {
        $stmt = $this->db->prepare("SELECT * FROM anni_scolastici WHERE attivo = 1 LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    }

    public function exists(string $anno): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM anni_scolastici WHERE anno = ?");
        $stmt->execute([$anno]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(string $anno): void {
        $this->db->prepare("INSERT INTO anni_scolastici (anno) VALUES (?)")->execute([$anno]);
    }

    public function setAttivo(int $id): void {
        $this->db->exec("UPDATE anni_scolastici SET attivo = 0");
        $this->db->prepare("UPDATE anni_scolastici SET attivo = 1 WHERE id = ?")->execute([$id]);
    }

    public function disattiva(int $id): void {
        $this->db->prepare("UPDATE anni_scolastici SET attivo = 0 WHERE id = ?")->execute([$id]);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM anni_scolastici WHERE id = ?")->execute([$id]);
    }
}
