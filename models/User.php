<?php

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND attivo = 1 LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Ricerca per email (recupero password) ───────────────────────────────
    //     users.email è UNIQUE, quindi il risultato è sempre al più uno.
    public function findByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND attivo = 1 LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // ── Imposta una nuova password (già in chiaro, viene hashata qui) ────────
    public function updatePassword(int $id, string $newPassword): void {
        $this->db->prepare("UPDATE users SET password = ? WHERE id = ?")
                 ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }
}
