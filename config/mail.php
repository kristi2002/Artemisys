<?php
/**
 * Configurazione email.
 *
 * TUTTI i valori arrivano da variabili d'ambiente (tab "Environment Variables"
 * di Coolify). Questo file è versionato: NON scrivere qui credenziali reali.
 *
 * Cambiare provider (Gmail -> Brevo/Mailgun/SES) significa cambiare solo le
 * variabili d'ambiente, non il codice.
 */

class MailConfig {

    public static function get(): array {
        return [
            // --- Trasporto SMTP ---
            'host'       => getenv('MAIL_HOST')       ?: 'smtp.gmail.com',
            'port'       => (int)(getenv('MAIL_PORT') ?: 587),
            // 'tls' (STARTTLS, porta 587) oppure 'ssl' (SMTPS, porta 465)
            'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
            'username'   => getenv('MAIL_USERNAME')   ?: '',
            'password'   => getenv('MAIL_PASSWORD')   ?: '',

            // --- Identità del mittente ---
            // Con Gmail gratuito il From DEVE coincidere con l'account SMTP
            // (o con un alias verificato), altrimenti Google lo riscrive.
            'from'       => getenv('MAIL_FROM')       ?: (getenv('MAIL_USERNAME') ?: ''),
            'from_name'  => getenv('MAIL_FROM_NAME')  ?: 'Moascuola',
            // Le risposte degli utenti arrivano qui (es. la casella della scuola).
            'reply_to'   => getenv('MAIL_REPLY_TO')   ?: '',

            // --- Comportamento ---
            // MAIL_ENABLED=0 -> non invia nulla davvero.
            'enabled'    => getenv('MAIL_ENABLED') === '1',
            // MAIL_LOG_ONLY=1 -> scrive il messaggio nel log invece di spedirlo.
            // Utile in locale per provare i flussi senza credenziali SMTP.
            'log_only'   => getenv('MAIL_LOG_ONLY') === '1',

            // Timeout basso di proposito: Apache qui ha solo 8 worker
            // (vedi Dockerfile). Un SMTP che non risponde non deve tenere
            // occupato un worker fino al timeout di default (300s).
            'timeout'    => (int)(getenv('MAIL_TIMEOUT') ?: 10),

            // Indirizzo pubblico dell'app, per costruire i link nelle email.
            // Necessario perché gli invii da CLI/cron non hanno $_SERVER['HTTP_HOST'].
            'app_url'    => rtrim(getenv('APP_URL') ?: '', '/'),
        ];
    }
}
