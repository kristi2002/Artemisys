<?php

class Sede {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM sedi ORDER BY nome ASC")->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM sedi WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    public function create(array $data): int {
        $this->db->prepare("
            INSERT INTO sedi (nome, via, comune, cap, provincia, telefono, email, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['nome'],
            $data['via']       ?: null,
            $data['comune']    ?: null,
            $data['cap']       ?: null,
            $data['provincia'] ?: null,
            $data['telefono']  ?: null,
            $data['email']     ?: null,
            $data['note']      ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $this->db->prepare("
            UPDATE sedi SET nome=?, via=?, comune=?, cap=?, provincia=?, telefono=?, email=?, note=?
            WHERE id=?
        ")->execute([
            $data['nome'],
            $data['via']       ?: null,
            $data['comune']    ?: null,
            $data['cap']       ?: null,
            $data['provincia'] ?: null,
            $data['telefono']  ?: null,
            $data['email']     ?: null,
            $data['note']      ?: null,
            $id,
        ]);
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM sedi WHERE id = ?")->execute([$id]);
    }
}
