<?php

class Diploma {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTables(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS diplomi_template (
                id              INT AUTO_INCREMENT PRIMARY KEY,
                titolo          VARCHAR(255) DEFAULT 'DIPLOMA',
                sottotitolo     VARCHAR(255) NULL,
                intestazione    TEXT NULL,
                formula         TEXT NULL,
                chiusura        TEXT NULL,
                luogo           VARCHAR(150) NULL,
                timbro_path     VARCHAR(255) NULL,
                firma1_nome     VARCHAR(150) NULL,
                firma1_ruolo    VARCHAR(150) NULL,
                firma1_path     VARCHAR(255) NULL,
                firma2_nome     VARCHAR(150) NULL,
                firma2_ruolo    VARCHAR(150) NULL,
                firma2_path     VARCHAR(255) NULL,
                updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");

        // Garantisci 1 record default
        $count = (int)$this->db->query("SELECT COUNT(*) FROM diplomi_template")->fetchColumn();
        if ($count === 0) {
            $this->db->exec("
                INSERT INTO diplomi_template
                    (titolo, sottotitolo, intestazione, formula, chiusura, luogo,
                     firma1_nome, firma1_ruolo, firma2_nome, firma2_ruolo)
                VALUES
                    ('DIPLOMA',
                     'di Specializzazione',
                     'Artemisys Academy',
                     'Si certifica che lo studente {{NOME}} {{COGNOME}} ha completato con successo il percorso di {{PERCORSO}} conseguendo la votazione finale.',
                     'Il presente diploma è rilasciato a tutti gli effetti di legge.',
                     'Milano',
                     '', 'Direttore',
                     '', 'Coordinatore Didattico')
            ");
        }
    }

    public function get(): array {
        $row = $this->db->query("SELECT * FROM diplomi_template ORDER BY id ASC LIMIT 1")->fetch();
        return $row ?: [];
    }

    public function update(array $data): void {
        $this->db->prepare("
            UPDATE diplomi_template SET
                titolo       = ?,
                sottotitolo  = ?,
                intestazione = ?,
                formula      = ?,
                chiusura     = ?,
                luogo        = ?,
                firma1_nome  = ?,
                firma1_ruolo = ?,
                firma2_nome  = ?,
                firma2_ruolo = ?
            WHERE id = (SELECT id FROM (SELECT id FROM diplomi_template ORDER BY id ASC LIMIT 1) AS t)
        ")->execute([
            $data['titolo']       ?: 'DIPLOMA',
            $data['sottotitolo']  ?: null,
            $data['intestazione'] ?: null,
            $data['formula']      ?: null,
            $data['chiusura']     ?: null,
            $data['luogo']        ?: null,
            $data['firma1_nome']  ?: null,
            $data['firma1_ruolo'] ?: null,
            $data['firma2_nome']  ?: null,
            $data['firma2_ruolo'] ?: null,
        ]);
    }

    public function setImage(string $field, ?string $path): void {
        $allowed = ['timbro_path', 'firma1_path', 'firma2_path'];
        if (!in_array($field, $allowed, true)) return;

        $this->db->prepare("
            UPDATE diplomi_template SET {$field} = ?
            WHERE id = (SELECT id FROM (SELECT id FROM diplomi_template ORDER BY id ASC LIMIT 1) AS t)
        ")->execute([$path]);
    }
}
