<?php
/**
 * Token per il recupero password.
 *
 * Nel database finisce SOLO l'hash SHA-256 del token: se qualcuno leggesse la
 * tabella non potrebbe comunque reimpostare le password. Il token in chiaro
 * esiste solo dentro il link inviato per email.
 */

class PasswordReset {

    /** Validità del link, in minuti. */
    private const DURATA_MINUTI = 60;

    /** Massimo di richieste per utente nella finestra sotto. */
    private const MAX_RICHIESTE   = 3;
    private const FINESTRA_MINUTI = 15;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();

        // Auto-migrazione, come per le altre tabelle del progetto.
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS password_resets (
                id           INT AUTO_INCREMENT PRIMARY KEY,
                user_id      INT      NOT NULL,
                token_hash   CHAR(64) NOT NULL,
                expires_at   DATETIME NOT NULL,
                used_at      DATETIME NULL,
                requested_ip VARCHAR(45) NULL,
                created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_token_hash (token_hash),
                KEY idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /**
     * Ha già chiesto troppi reset di recente?
     * Freno contro chi usa il form per bersagliare di email una casella.
     */
    public function troppiTentativi(int $userId): bool {
        // La finestra e' una costante di classe, non input utente: la si
        // interpola invece di usare un segnaposto dentro INTERVAL, che con le
        // prepared statement native (EMULATE_PREPARES = false) non e' garantito.
        $finestra = (int)self::FINESTRA_MINUTI;

        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM password_resets
            WHERE user_id = ? AND created_at > (NOW() - INTERVAL {$finestra} MINUTE)
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn() >= self::MAX_RICHIESTE;
    }

    /**
     * Crea un token per l'utente e restituisce la versione in chiaro
     * (da mettere nel link dell'email; non viene mai salvata).
     */
    public function crea(int $userId, ?string $ip = null): string {
        // Un solo link valido alla volta: i precedenti vengono bruciati.
        $this->invalidaPerUtente($userId);

        $token = bin2hex(random_bytes(32));

        $durata = (int)self::DURATA_MINUTI;

        $stmt = $this->db->prepare("
            INSERT INTO password_resets (user_id, token_hash, expires_at, requested_ip)
            VALUES (?, ?, (NOW() + INTERVAL {$durata} MINUTE), ?)
        ");
        $stmt->execute([$userId, hash('sha256', $token), $ip]);

        return $token;
    }

    /**
     * Restituisce la riga del token se è valido (esiste, non usato, non scaduto),
     * altrimenti null.
     */
    public function trovaValido(string $token): ?array {
        // Scarta subito input malformati: risparmia una query.
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT pr.*, u.email, u.nome, u.cognome, u.username
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token_hash = ?
              AND pr.used_at IS NULL
              AND pr.expires_at > NOW()
              AND u.attivo = 1
            LIMIT 1
        ");
        $stmt->execute([hash('sha256', $token)]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Segna il token come consumato: vale una volta sola. */
    public function segnaUsato(int $id): void {
        $this->db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?")
                 ->execute([$id]);
    }

    /** Brucia tutti i token ancora aperti di un utente. */
    public function invalidaPerUtente(int $userId): void {
        $this->db->prepare("
            UPDATE password_resets SET used_at = NOW()
            WHERE user_id = ? AND used_at IS NULL
        ")->execute([$userId]);
    }

    /** Pulizia dei token vecchi (richiamabile da cron; non indispensabile). */
    public function pulisci(int $giorni = 7): int {
        $giorni = max(1, $giorni);

        $stmt = $this->db->prepare("
            DELETE FROM password_resets WHERE created_at < (NOW() - INTERVAL {$giorni} DAY)
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
