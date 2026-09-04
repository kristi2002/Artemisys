-- =============================================
-- ARTEMISYS - Database Setup Completo
-- =============================================

CREATE DATABASE IF NOT EXISTS artemisys CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE artemisys;

SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Utenti ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100) NOT NULL,
    cognome    VARCHAR(100) NOT NULL,
    username   VARCHAR(100) NOT NULL UNIQUE,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    ruolo      ENUM('admin','docente','studente') NOT NULL DEFAULT 'studente',
    attivo     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admin di default (password: 'password' — cambiare subito)
INSERT IGNORE INTO users (nome, cognome, username, email, password, ruolo) VALUES
('Admin', 'Artemisys', 'admin', 'admin@artemisys.it',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ── 2. Insegnanti ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS insegnanti (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    nome           VARCHAR(100) NOT NULL,
    cognome        VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL,
    telefono       VARCHAR(20)  DEFAULT NULL,
    codice_fiscale VARCHAR(16)  DEFAULT NULL,
    data_nascita   DATE         DEFAULT NULL,
    indirizzo      VARCHAR(255) DEFAULT NULL,
    materie        VARCHAR(255) DEFAULT NULL,
    foto           VARCHAR(255) DEFAULT NULL,
    note           TEXT         DEFAULT NULL,
    attivo         TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 3. Anni scolastici ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS anni_scolastici (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    anno       VARCHAR(20) NOT NULL UNIQUE,
    attivo     TINYINT(1)  NOT NULL DEFAULT 0,
    created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── 4. Materie ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS materie (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    codice           VARCHAR(20)  NOT NULL UNIQUE,
    nome             VARCHAR(150) NOT NULL,
    codice_regionale VARCHAR(20)  NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── 5. Percorsi accademici ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS percorsi_accademici (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    nome               VARCHAR(200) NOT NULL,
    anno_scolastico_id INT NOT NULL,
    descrizione        TEXT NULL,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anno_scolastico_id) REFERENCES anni_scolastici(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── 6. Anni di un percorso ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS percorso_anni (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    percorso_id INT NOT NULL,
    numero      TINYINT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_percorso_anno (percorso_id, numero),
    FOREIGN KEY (percorso_id) REFERENCES percorsi_accademici(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 7. Materie assegnate a un anno di percorso (PAM) ─────────────────────────
CREATE TABLE IF NOT EXISTS percorso_anno_materie (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    anno_id        INT NOT NULL,
    materia_id     INT NOT NULL,
    insegnante_id  INT NULL,
    ordine         TINYINT DEFAULT 0,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_anno_materia (anno_id, materia_id),
    FOREIGN KEY (anno_id)       REFERENCES percorso_anni(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id)    REFERENCES materie(id)       ON DELETE RESTRICT,
    FOREIGN KEY (insegnante_id) REFERENCES insegnanti(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── 8. Studenti ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS studenti (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(100) NOT NULL,
    cognome        VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NULL,
    telefono       VARCHAR(30)  NULL,
    data_nascita   DATE         NULL,
    codice_fiscale VARCHAR(20)  NULL,
    indirizzo      TEXT         NULL,
    foto           VARCHAR(255) NULL,
    note           TEXT         NULL,
    attivo         TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── 9. Iscrizione studente a un percorso ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS studente_iscrizioni (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    studente_id INT NOT NULL,
    percorso_id INT NOT NULL,
    data_inizio DATE NULL,
    note        VARCHAR(255) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_studente_percorso (studente_id, percorso_id),
    FOREIGN KEY (studente_id) REFERENCES studenti(id)            ON DELETE CASCADE,
    FOREIGN KEY (percorso_id) REFERENCES percorsi_accademici(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── 10. Anno di corso dello studente per A.S. (storicità) ────────────────────
CREATE TABLE IF NOT EXISTS studente_anni (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    studente_id         INT NOT NULL,
    percorso_anno_id    INT NOT NULL,
    anno_scolastico_id  INT NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_studente_anno_sc (studente_id, anno_scolastico_id),
    FOREIGN KEY (studente_id)        REFERENCES studenti(id)        ON DELETE CASCADE,
    FOREIGN KEY (percorso_anno_id)   REFERENCES percorso_anni(id)   ON DELETE RESTRICT,
    FOREIGN KEY (anno_scolastico_id) REFERENCES anni_scolastici(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── 11. Materie bonus (extra rispetto al piano dell'anno) ────────────────────
CREATE TABLE IF NOT EXISTS studente_materie_bonus (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    studente_id        INT NOT NULL,
    materia_id         INT NOT NULL,
    anno_scolastico_id INT NOT NULL,
    note               VARCHAR(255) NULL,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bonus (studente_id, materia_id, anno_scolastico_id),
    FOREIGN KEY (studente_id)        REFERENCES studenti(id)        ON DELETE CASCADE,
    FOREIGN KEY (materia_id)         REFERENCES materie(id)         ON DELETE RESTRICT,
    FOREIGN KEY (anno_scolastico_id) REFERENCES anni_scolastici(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── 12. Voti ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS voti (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    studente_id              INT          NOT NULL,
    percorso_anno_materia_id INT          NOT NULL,
    voto                     DECIMAL(4,2) NOT NULL,
    tipo                     ENUM('scritto','orale','pratico','finale') NOT NULL DEFAULT 'finale',
    data                     DATE         NULL,
    note                     VARCHAR(255) NULL,
    created_at               TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_studente_pam (studente_id, percorso_anno_materia_id),
    FOREIGN KEY (studente_id)              REFERENCES studenti(id)              ON DELETE CASCADE,
    FOREIGN KEY (percorso_anno_materia_id) REFERENCES percorso_anno_materie(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 13. Argomenti delle materie ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS argomenti (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    percorso_anno_materia_id INT NOT NULL,
    titolo                   VARCHAR(255) NOT NULL,
    descrizione              TEXT     NULL,
    ordine                   SMALLINT DEFAULT 0,
    created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (percorso_anno_materia_id) REFERENCES percorso_anno_materie(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 14. Lezioni ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lezioni (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    percorso_anno_materia_id INT          NULL,
    titolo                   VARCHAR(255) NOT NULL,
    data                     DATE         NULL,
    durata_minuti            SMALLINT     NULL,
    argomento                TEXT         NULL,
    note                     TEXT         NULL,
    online                   TINYINT(1)   NOT NULL DEFAULT 0,
    link_online              VARCHAR(500) NULL,
    created_at               TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (percorso_anno_materia_id) REFERENCES percorso_anno_materie(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── 15. Presenze alle lezioni ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lezione_presenze (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    lezione_id  INT NOT NULL,
    studente_id INT NOT NULL,
    presente    TINYINT(1) NOT NULL DEFAULT 1,
    note        VARCHAR(255) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_presenza (lezione_id, studente_id),
    FOREIGN KEY (lezione_id)  REFERENCES lezioni(id)  ON DELETE CASCADE,
    FOREIGN KEY (studente_id) REFERENCES studenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 16. Insegnanti per PAM (multi-docenza per materia) ───────────────────────
CREATE TABLE IF NOT EXISTS pam_insegnanti (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    pam_id        INT NOT NULL,
    insegnante_id INT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pam_ins (pam_id, insegnante_id),
    FOREIGN KEY (pam_id)        REFERENCES percorso_anno_materie(id) ON DELETE CASCADE,
    FOREIGN KEY (insegnante_id) REFERENCES insegnanti(id)            ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 17. Insegnanti per singola lezione ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS lezione_insegnanti (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    lezione_id    INT NOT NULL,
    insegnante_id INT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_lez_ins (lezione_id, insegnante_id),
    FOREIGN KEY (lezione_id)    REFERENCES lezioni(id)    ON DELETE CASCADE,
    FOREIGN KEY (insegnante_id) REFERENCES insegnanti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 18. Allegati delle lezioni ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lezione_allegati (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    lezione_id    INT NOT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type     VARCHAR(100) NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lezione_id) REFERENCES lezioni(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 19. Esami ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS esami (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    percorso_anno_materia_id INT NOT NULL,
    titolo                   VARCHAR(255) NOT NULL,
    data                     DATE NULL,
    ora_inizio               TIME NULL,
    tipo                     ENUM('scritto','orale','pratico') NOT NULL DEFAULT 'scritto',
    note                     TEXT NULL,
    created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (percorso_anno_materia_id) REFERENCES percorso_anno_materie(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 20. Iscrizioni / voti d'esame ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS esame_iscrizioni (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    esame_id    INT NOT NULL,
    studente_id INT NOT NULL,
    voto        DECIMAL(4,2) NULL,
    assente     TINYINT(1)   NOT NULL DEFAULT 0,
    note        VARCHAR(255) NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_esame_studente (esame_id, studente_id),
    FOREIGN KEY (esame_id)    REFERENCES esami(id)    ON DELETE CASCADE,
    FOREIGN KEY (studente_id) REFERENCES studenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 21. Supervisori d'esame ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS esame_supervisori (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    esame_id      INT NOT NULL,
    insegnante_id INT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_esame_ins (esame_id, insegnante_id),
    FOREIGN KEY (esame_id)      REFERENCES esami(id)      ON DELETE CASCADE,
    FOREIGN KEY (insegnante_id) REFERENCES insegnanti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 21.b. Allegati / documenti d'esame ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS esame_allegati (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    esame_id      INT NOT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type     VARCHAR(100) NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (esame_id) REFERENCES esami(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 21.c. Esami di Stato ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS esami_di_stato (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    denominazione     VARCHAR(255) NOT NULL,
    data              DATE NULL,
    ora_inizio        TIME NULL,
    riferimento_tipo  ENUM('percorso','classe') NOT NULL DEFAULT 'classe',
    percorso_id       INT NOT NULL,
    percorso_anno_id  INT NULL,
    rappresentante_id INT NULL,
    stato             ENUM('programmato','in_corso','completato','annullato')
                      NOT NULL DEFAULT 'programmato',
    note              TEXT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (percorso_id)       REFERENCES percorsi_accademici(id) ON DELETE CASCADE,
    FOREIGN KEY (percorso_anno_id)  REFERENCES percorso_anni(id) ON DELETE SET NULL,
    FOREIGN KEY (rappresentante_id) REFERENCES insegnanti(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS esame_di_stato_commissione (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    esame_di_stato_id INT NOT NULL,
    insegnante_id     INT NOT NULL,
    ruolo             ENUM('presidente','commissario','segretario')
                      NOT NULL DEFAULT 'commissario',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY u_eds_ins (esame_di_stato_id, insegnante_id),
    FOREIGN KEY (esame_di_stato_id) REFERENCES esami_di_stato(id) ON DELETE CASCADE,
    FOREIGN KEY (insegnante_id)     REFERENCES insegnanti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS esame_di_stato_iscrizioni (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    esame_di_stato_id INT NOT NULL,
    studente_id       INT NOT NULL,
    voto_finale       DECIMAL(4,2) NULL,
    esito             ENUM('ammesso','non_ammesso','assente') NULL,
    note              VARCHAR(255) NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY u_eds_studente (esame_di_stato_id, studente_id),
    FOREIGN KEY (esame_di_stato_id) REFERENCES esami_di_stato(id) ON DELETE CASCADE,
    FOREIGN KEY (studente_id)       REFERENCES studenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS esame_di_stato_allegati (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    esame_di_stato_id INT NOT NULL,
    filename          VARCHAR(255) NOT NULL,
    original_name     VARCHAR(255) NOT NULL,
    mime_type         VARCHAR(100) NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (esame_di_stato_id) REFERENCES esami_di_stato(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS esame_di_stato_prove (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    esame_di_stato_id INT NOT NULL,
    data              DATE NULL,
    ora_inizio        TIME NULL,
    ora_fine          TIME NULL,
    tipo_prova        VARCHAR(20) NOT NULL DEFAULT 'P/S/O',
    ordine            TINYINT NOT NULL DEFAULT 1,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (esame_di_stato_id) REFERENCES esami_di_stato(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Colonne aggiuntive esami_di_stato (dati stampa CAL-ESA)
-- ente_gestore, sede_presso, sede_via, sede_comune, sede_telefono,
-- cod_corso, cod_did_reg, ore_corso

-- ── 22. Eventi ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS eventi (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    titolo              VARCHAR(255) NOT NULL,
    descrizione         TEXT NULL,
    tipo                ENUM('workshop','seminario','open_day','competizione','sociale','altro')
                        NOT NULL DEFAULT 'altro',
    data_evento         DATE NOT NULL,
    ora_inizio          TIME NULL,
    ora_fine            TIME NULL,
    luogo               VARCHAR(255) NULL,
    max_iscritti        INT NULL,
    scadenza_iscrizioni DATE NULL,
    relatore            VARCHAR(255) NULL,
    stato               ENUM('aperto','chiuso','annullato','svolto')
                        NOT NULL DEFAULT 'aperto',
    immagine            VARCHAR(255) NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── 23. Iscrizioni / presenze eventi ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS evento_iscrizioni (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    evento_id   INT NOT NULL,
    studente_id INT NOT NULL,
    presente    TINYINT(1) NOT NULL DEFAULT 0,
    note        VARCHAR(255) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY u_evento_studente (evento_id, studente_id),
    FOREIGN KEY (evento_id)   REFERENCES eventi(id)   ON DELETE CASCADE,
    FOREIGN KEY (studente_id) REFERENCES studenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 24. Bacheca / Comunicazioni ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS comunicazioni (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titolo      VARCHAR(255) NOT NULL,
    contenuto   TEXT NOT NULL,
    tipo        ENUM('avviso','urgente','evento','generale') NOT NULL DEFAULT 'generale',
    target      ENUM('tutti','studenti','docenti')           NOT NULL DEFAULT 'tutti',
    autore_id   INT NULL,
    autore_nome VARCHAR(150) NULL,
    pubblicata  TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── 25. Rette ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS rette (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    studente_id    INT NOT NULL,
    importo_totale DECIMAL(10,2) NOT NULL,
    num_rate       INT NOT NULL DEFAULT 1,
    cadenza        ENUM('mensile','bimestrale','trimestrale') NOT NULL DEFAULT 'mensile',
    note           TEXT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (studente_id) REFERENCES studenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 26. Rate di pagamento ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS rate_pagamento (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    retta_id       INT NOT NULL,
    numero         INT NOT NULL,
    importo        DECIMAL(10,2) NOT NULL,
    scadenza       DATE NULL,
    pagata         TINYINT(1) NOT NULL DEFAULT 0,
    data_pagamento DATE NULL,
    metodo         VARCHAR(50)  NULL,
    note           VARCHAR(255) NULL,
    file_pdf       VARCHAR(255) NULL,
    file_originale VARCHAR(255) NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (retta_id) REFERENCES rette(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 27. Stage / Tirocini ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS stage (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    studente_id      INT NOT NULL,
    azienda          VARCHAR(255) NOT NULL,
    citta            VARCHAR(100) NULL,
    settore          VARCHAR(150) NULL,
    tutor_aziendale  VARCHAR(255) NULL,
    tutor_scolastico VARCHAR(255) NULL,
    data_inizio      DATE NULL,
    data_fine        DATE NULL,
    monte_ore        INT  NULL DEFAULT 150,
    step             TINYINT NOT NULL DEFAULT 1,
    note_candidatura TEXT NULL,
    data_convenzione DATE NULL,
    note_convenzione TEXT NULL,
    ore_svolte       INT  NOT NULL DEFAULT 0,
    note_tirocinio   TEXT NULL,
    voto_finale      DECIMAL(4,2) NULL,
    giudizio         ENUM('insufficiente','sufficiente','buono','ottimo') NULL,
    note_valutazione TEXT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (studente_id) REFERENCES studenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 27.b. Allegati / documenti dello stage (convenzione, ecc.) ───────────────
CREATE TABLE IF NOT EXISTS stage_allegati (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    stage_id      INT NOT NULL,
    categoria     ENUM('convenzione','candidatura','valutazione','altro') NOT NULL DEFAULT 'convenzione',
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type     VARCHAR(100) NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stage_id) REFERENCES stage(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 28. Template Diploma ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS diplomi_template (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    titolo       VARCHAR(255) DEFAULT 'DIPLOMA',
    sottotitolo  VARCHAR(255) NULL,
    intestazione TEXT NULL,
    formula      TEXT NULL,
    chiusura     TEXT NULL,
    luogo        VARCHAR(150) NULL,
    timbro_path  VARCHAR(255) NULL,
    firma1_nome  VARCHAR(150) NULL,
    firma1_ruolo VARCHAR(150) NULL,
    firma1_path  VARCHAR(255) NULL,
    firma2_nome  VARCHAR(150) NULL,
    firma2_ruolo VARCHAR(150) NULL,
    firma2_path  VARCHAR(255) NULL,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── 29. Documenti personali dello studente ───────────────────────────────────
-- I file stanno in uploads/studenti/ (fuori da public/) e si scaricano solo
-- da index.php/studente/documento/{id}.
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
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
