<?php

class Insegnante {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Lista tutti gli insegnanti ──────────────────────────────────────────
    public function getAll($search = '') {
        $sql = "
            SELECT i.*, u.username, u.attivo
            FROM insegnanti i
            JOIN users u ON i.user_id = u.id
        ";
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE i.nome LIKE ? OR i.cognome LIKE ? OR i.email LIKE ? OR i.materie LIKE ?";
            $like = "%{$search}%";
            $params = [$like, $like, $like, $like];
        }
        $sql .= " ORDER BY i.cognome ASC, i.nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Singolo insegnante ──────────────────────────────────────────────────
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT i.*, u.username, u.attivo
            FROM insegnanti i
            JOIN users u ON i.user_id = u.id
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Cerca per cognome e nome (corrispondenza esatta, case-insensitive) ──
    public function findByCognomeNome(string $cognome, string $nome): array|false {
        $stmt = $this->db->prepare("
            SELECT i.* FROM insegnanti i
            WHERE LOWER(i.cognome) = LOWER(?) AND LOWER(i.nome) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$cognome, $nome]);
        return $stmt->fetch();
    }

    // ── Crea insegnante + utente ────────────────────────────────────────────
    public function create(array $data, string $plainPassword): int {
        $this->db->beginTransaction();
        try {
            // 1. Crea utente
            $stmt = $this->db->prepare("
                INSERT INTO users (nome, cognome, username, email, password, ruolo, attivo)
                VALUES (?, ?, ?, ?, ?, 'docente', 1)
            ");
            $stmt->execute([
                $data['nome'],
                $data['cognome'],
                $data['username'],
                $data['email'],
                password_hash($plainPassword, PASSWORD_DEFAULT),
            ]);
            $userId = (int)$this->db->lastInsertId();

            // 2. Crea profilo insegnante
            $stmt = $this->db->prepare("
                INSERT INTO insegnanti
                    (user_id, nome, cognome, email, telefono, codice_fiscale, data_nascita, indirizzo, materie, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $data['nome'],
                $data['cognome'],
                $data['email'],
                $data['telefono']       ?? null,
                $data['codice_fiscale'] ?? null,
                $data['data_nascita']   ?: null,
                $data['indirizzo']      ?? null,
                $data['materie']        ?? null,
                $data['note']           ?? null,
            ]);
            $insegnanteId = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $insegnanteId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Aggiorna insegnante ─────────────────────────────────────────────────
    public function update(int $id, array $data): void {
        $ins = $this->findById($id);
        if (!$ins) return;

        $this->db->beginTransaction();
        try {
            // Aggiorna users
            $this->db->prepare("
                UPDATE users SET nome=?, cognome=?, email=?, username=? WHERE id=?
            ")->execute([
                $data['nome'],
                $data['cognome'],
                $data['email'],
                $data['username'],
                $ins['user_id'],
            ]);

            // Aggiorna insegnanti
            $this->db->prepare("
                UPDATE insegnanti
                SET nome=?, cognome=?, email=?, telefono=?, codice_fiscale=?,
                    data_nascita=?, indirizzo=?, materie=?, note=?
                WHERE id=?
            ")->execute([
                $data['nome'],
                $data['cognome'],
                $data['email'],
                $data['telefono']       ?? null,
                $data['codice_fiscale'] ?? null,
                $data['data_nascita']   ?: null,
                $data['indirizzo']      ?? null,
                $data['materie']        ?? null,
                $data['note']           ?? null,
                $id,
            ]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Cambia password ─────────────────────────────────────────────────────
    public function changePassword(int $id, string $newPassword): void {
        $ins = $this->findById($id);
        if (!$ins) return;
        $this->db->prepare("UPDATE users SET password=? WHERE id=?")
                 ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $ins['user_id']]);
    }

    // ── Attiva / Disattiva ──────────────────────────────────────────────────
    public function toggleStatus(int $id): bool {
        $ins = $this->findById($id);
        if (!$ins) return false;
        $nuovo = $ins['attivo'] ? 0 : 1;
        $this->db->prepare("UPDATE users SET attivo=? WHERE id=?")
                 ->execute([$nuovo, $ins['user_id']]);
        return (bool)$nuovo;
    }

    // ── Aggiorna foto ───────────────────────────────────────────────────────
    public function updateFoto(int $id, string $path): void {
        $this->db->prepare("UPDATE insegnanti SET foto=? WHERE id=?")
                 ->execute([$path, $id]);
    }

    // ── Conta totale ────────────────────────────────────────────────────────
    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM insegnanti")->fetchColumn();
    }

    // ── Genera username univoco ─────────────────────────────────────────────
    public function generateUsername(string $nome, string $cognome): string {
        $base = strtolower(
            $this->removeAccents(substr($nome, 0, 1)) . '.' .
            $this->removeAccents($cognome)
        );
        $base = preg_replace('/[^a-z0-9.]/', '', $base);

        $username = $base;
        $i = 2;
        while ($this->usernameExists($username)) {
            $username = $base . $i;
            $i++;
        }
        return $username;
    }

    // ── Genera password sicura ──────────────────────────────────────────────
    public function generatePassword(int $length = 10): string {
        $lower   = 'abcdefghjkmnpqrstuvwxyz';
        $upper   = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $digits  = '23456789';
        $special = '@#!';
        $all     = $lower . $upper . $digits . $special;

        $password  = $lower[random_int(0, strlen($lower) - 1)];
        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }
        return str_shuffle($password);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────
    private function usernameExists(string $username): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function removeAccents(string $str): string {
        $map = ['à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a',
                'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
                'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i',
                'ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o',
                'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u',
                'ñ'=>'n','ç'=>'c'];
        return strtr(mb_strtolower($str), $map);
    }
}
