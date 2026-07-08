USE artemisys;

-- Tabella profili insegnanti (estende users)
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
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
