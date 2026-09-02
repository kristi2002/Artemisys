# Artemisys — PHP 8.3 + Apache

# --- Dipendenze Composer (PHPMailer) ---
# Stage a parte: l'immagine finale non si porta dietro Composer.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# --- Immagine applicativa ---
FROM php:8.3-apache

# --- Estensioni PHP richieste dall'app ---
#  pdo_mysql -> database
#  zip       -> generazione .docx/.odt (ZipArchive)
#  mbstring  -> mb_strtolower / mb_substr
#  dom/xml/simplexml sono già inclusi nell'immagine ufficiale
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-install pdo_mysql zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- Apache: abilita mod_rewrite e consenti .htaccess ---
RUN a2enmod rewrite headers
RUN sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# --- Apache: limita i processi figli ---
# mpm_prefork di default arriva a 150 figli; con memory_limit=256M
# basta poca concorrenza per sfondare il mem_limit del container.
# 8 worker sono ampiamente sufficienti per questa app.
RUN { \
      echo '<IfModule mpm_prefork_module>'; \
      echo '  StartServers 2'; \
      echo '  MinSpareServers 2'; \
      echo '  MaxSpareServers 5'; \
      echo '  MaxRequestWorkers 8'; \
      echo '  MaxConnectionsPerChild 500'; \
      echo '</IfModule>'; \
    } > /etc/apache2/conf-available/artemisys-mpm.conf \
    && a2enconf artemisys-mpm

# --- Impostazioni PHP produzione (upload documenti pesanti) ---
RUN { \
      echo 'upload_max_filesize=32M'; \
      echo 'post_max_size=40M'; \
      echo 'memory_limit=256M'; \
      echo 'max_execution_time=120'; \
      echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/artemisys.ini

# --- Codice applicazione ---
WORKDIR /var/www/html
COPY . /var/www/html

# Le dipendenze arrivano dallo stage "vendor": non vengono mai committate
# (vedi .gitignore) e si ricostruiscono a ogni build.
COPY --from=vendor /app/vendor /var/www/html/vendor

# Cartelle scrivibili per upload utente (il resto è read-only)
RUN mkdir -p public/uploads uploads documenti \
    && chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

EXPOSE 80
