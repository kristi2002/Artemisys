USE artemisys;

-- =============================================
-- Inserimento studenti - dati anagrafici da completare
-- email, telefono, data_nascita, codice_fiscale, indirizzo
-- sono NULL: aggiornare in seguito con i dati reali
-- =============================================

INSERT INTO studenti (cognome, nome, email, telefono, data_nascita, codice_fiscale, indirizzo, note, attivo) VALUES
('Acerra',              'Francesca',         NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Akimova',             'Ivanna',             NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Bove',                'Nicolo''',            NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Caballero Del Gadillo','Samanta Valentina',  NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Campolucci',          'Alex',               NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Coppa',               'Anastasia',          NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Gazulli',             'Elda',               NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Gencheva',            'Desislava Kancheva', NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Giagnorio',           'Nathan',             NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Giorgetti',           'Chiara',             NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Giuli',               'Gabriele',           NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Goffredi',            'Giacomo',            NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Grizi',               'Paride',             NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Ippoliti',            'Veronica Andrea',    NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Mitraoui',            'Rayen',              NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Piku',                'Ergin',              NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Pla Carbonel',        'Yosune Athalma',     NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Quattrini',           'Gianluca',           NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Samailiuk',           'Axana',              NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Stafa',               'Matteo',             NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Vitale',              'Martina',            NULL, NULL, NULL, NULL, NULL, NULL, 1),
('Catena',              'Leonardo',           NULL, NULL, NULL, NULL, NULL, NULL, 1);
