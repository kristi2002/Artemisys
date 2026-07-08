# MySQL con lo schema Artemisys incluso.
# Lo script in /docker-entrypoint-initdb.d viene eseguito UNA SOLA VOLTA,
# al primo avvio con datadir vuoto (volume db_data nuovo).
FROM mysql:8.0
COPY sql/artemisys.sql /docker-entrypoint-initdb.d/01-artemisys.sql
