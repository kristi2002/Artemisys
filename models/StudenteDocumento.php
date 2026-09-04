<?php

class StudenteDocumento {
    private $db;

    /** Etichette selezionabili dallo studente in fase di caricamento */
    public const ETICHETTE = [
        'identita'           => "Documento d'identità",
        'codice_fiscale'     => 'Codice fiscale',
        'certificato_medico' => 'Certificato medico',
        'titolo_studio'      => 'Titolo di studio',
        'cv'                 => 'Curriculum vitae',
        'altro'              => 'Altro',
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS studente_documenti (
                id              INT AUTO_INCREMENT PRIMARY KEY,
                studente_id     INT NOT NULL,
                etichetta       ENUM('identita','codice_fiscale','certificato_medico','titolo_studio','cv','altro')
                                NOT NULL DEFAULT 'altro',
                etichetta_altro VARCHAR(100) NULL,
                descrizione     VARCHAR(255) NULL,
                filename        VARCHAR(255) NOT NULL,
                original_name   VARCHAR(255) NOT NULL,
                mime_type       VARCHAR(100) NULL,
                caricato_da     ENUM('studente','segreteria') NOT NULL DEFAULT 'studente',
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_stud_doc_studente (studente_id)
            ) ENGINE=InnoDB
        ");
    }

    /**
     * Cartella dei documenti personali: sta fuori da public/ ed è protetta da un
     * .htaccess, così l'unica via d'accesso resta un'azione PHP che verifica chi
     * sta scaricando. Il guard va scritto a runtime: in produzione uploads/ è un
     * volume Docker già esistente, che non riceve i file nuovi dell'immagine.
     */
    public static function uploadDir(): string {
        $dir = BASE_PATH . 'uploads/studenti/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $guard = $dir . '.htaccess';
        if (!is_file($guard)) {
            file_put_contents($guard, implode("\n", [
                '# Documenti personali degli studenti: Apache non deve servirli.',
                '# Si scaricano solo da studente/documento/{id} o studenti/documento/{id}.',
                '<IfModule mod_authz_core.c>',
                '    Require all denied',
                '</IfModule>',
                '<IfModule !mod_authz_core.c>',
                '    Order allow,deny',
                '    Deny from all',
                '</IfModule>',
                '',
            ]));
        }
        return $dir;
    }

    /** Etichetta leggibile: usa il testo libero quando l'etichetta è 'altro' */
    public static function labelOf(array $doc): string {
        if ($doc['etichetta'] === 'altro' && !empty($doc['etichetta_altro'])) {
            return $doc['etichetta_altro'];
        }
        return self::ETICHETTE[$doc['etichetta']] ?? 'Altro';
    }

    public function getByStudente(int $studenteId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM studente_documenti
            WHERE studente_id = ?
            ORDER BY created_at DESC, id DESC
        ");
        $stmt->execute([$studenteId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM studente_documenti WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(
        int $studenteId,
        string $etichetta,
        ?string $etichettaAltro,
        ?string $descrizione,
        string $filename,
        string $originalName,
        ?string $mime,
        string $caricatoDa = 'studente'
    ): int {
        $this->db->prepare("
            INSERT INTO studente_documenti
                (studente_id, etichetta, etichetta_altro, descrizione,
                 filename, original_name, mime_type, caricato_da)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $studenteId, $etichetta, $etichettaAltro, $descrizione,
            $filename, $originalName, $mime, $caricatoDa,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM studente_documenti WHERE id = ?")->execute([$id]);
    }
}
