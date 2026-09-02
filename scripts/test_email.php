<?php
/**
 * Verifica della configurazione email.
 *
 * Uso (in locale):
 *     php scripts/test_email.php destinatario@esempio.it
 *
 * Uso (sul server, dentro il container dell'app):
 *     docker exec -it <container> php scripts/test_email.php destinatario@esempio.it
 *
 * Stampa la configurazione letta dalle variabili d'ambiente (password mascherata)
 * e prova a spedire un messaggio di prova.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Questo script si esegue solo da riga di comando.\n");
}

define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

if (!is_file(BASE_PATH . 'vendor/autoload.php')) {
    exit("ERRORE: manca vendor/. Esegui prima: composer install\n");
}

require_once BASE_PATH . 'services/Mailer.php';

$destinatario = $argv[1] ?? '';

if ($destinatario === '' || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
    exit("Uso: php scripts/test_email.php destinatario@esempio.it\n");
}

$cfg = MailConfig::get();

// ── Configurazione letta ─────────────────────────────────────────────────────
$maschera = static function (string $v): string {
    if ($v === '') return '(vuoto)';
    return strlen($v) <= 4 ? '****' : substr($v, 0, 2) . str_repeat('*', strlen($v) - 4) . substr($v, -2);
};

echo "\n=== Configurazione email ===\n";
printf("  MAIL_ENABLED     %s\n", $cfg['enabled']  ? 'si' : 'NO  <-- non spedira nulla');
printf("  MAIL_LOG_ONLY    %s\n", $cfg['log_only'] ? 'si  <-- scrive nel log, non spedisce' : 'no');
printf("  MAIL_HOST        %s\n", $cfg['host']);
printf("  MAIL_PORT        %d\n", $cfg['port']);
printf("  MAIL_ENCRYPTION  %s\n", $cfg['encryption']);
printf("  MAIL_USERNAME    %s\n", $cfg['username'] !== '' ? $cfg['username'] : '(vuoto)');
printf("  MAIL_PASSWORD    %s\n", $maschera($cfg['password']));
printf("  MAIL_FROM        %s\n", $cfg['from'] !== '' ? $cfg['from'] : '(vuoto)');
printf("  MAIL_FROM_NAME   %s\n", $cfg['from_name']);
printf("  MAIL_REPLY_TO    %s\n", $cfg['reply_to'] !== '' ? $cfg['reply_to'] : '(non impostato)');
printf("  APP_URL          %s\n", $cfg['app_url'] !== '' ? $cfg['app_url'] : '(non impostato)');
echo "\n";

// ── Controlli preventivi ─────────────────────────────────────────────────────
$problemi = [];

if (!$cfg['enabled']) {
    $problemi[] = 'MAIL_ENABLED non vale 1: nessun invio verra effettuato.';
}
if (!$cfg['log_only']) {
    if ($cfg['username'] === '') $problemi[] = 'MAIL_USERNAME non impostato.';
    if ($cfg['password'] === '') $problemi[] = 'MAIL_PASSWORD non impostato.';
    if ($cfg['from']     === '') $problemi[] = 'MAIL_FROM non impostato.';

    // Errore tipico: password dell'account Google invece della password per le app.
    $isGmail = str_contains($cfg['host'], 'gmail') || str_contains($cfg['host'], 'google');
    if ($isGmail && $cfg['password'] !== '' && strlen(str_replace(' ', '', $cfg['password'])) !== 16) {
        $problemi[] = 'Host Gmail ma la password non ha 16 caratteri: '
                    . 'probabilmente non e una "Password per le app".';
    }
    if ($isGmail && $cfg['from'] !== '' && $cfg['username'] !== ''
        && strcasecmp($cfg['from'], $cfg['username']) !== 0) {
        $problemi[] = 'MAIL_FROM diverso da MAIL_USERNAME: con Gmail funziona solo '
                    . 'se e un alias verificato, altrimenti Google riscrive il mittente.';
    }
}
if ($cfg['app_url'] === '') {
    $problemi[] = 'APP_URL non impostato: i link dentro le email saranno incompleti.';
}

if ($problemi) {
    echo "=== Possibili problemi ===\n";
    foreach ($problemi as $p) echo "  - {$p}\n";
    echo "\n";
}

$corpo = '<p>Se stai leggendo questo messaggio, la configurazione email di Artemisys funziona.</p>'
       . '<p style="color:#7b8794;font-size:13px;">Inviato il ' . date('d/m/Y \a\l\l\e H:i') . '.</p>';

$html = Mailer::layout('Prova di invio', $corpo);

// ── Anteprima delle intestazioni ─────────────────────────────────────────────
// Costruisce il messaggio senza spedirlo: si vede subito che mittente e nome
// visualizzato partirebbero, anche senza credenziali SMTP valide.
echo "=== Intestazioni del messaggio ===\n";
foreach (explode("\n", Mailer::getInstance()->anteprimaIntestazioni(
             $destinatario, 'Artemisys — messaggio di prova', $html)) as $riga) {
    echo '  ' . $riga . "\n";
}
echo "\n";
echo "  Il testo prima di <...> e' MAIL_FROM_NAME: e' cio' che vede il destinatario.\n";
echo "  L'indirizzo fra <...> resta leggibile aprendo il messaggio: non e' nascosto.\n\n";

// ── Invio di prova ───────────────────────────────────────────────────────────
echo "Invio del messaggio di prova a {$destinatario} ...\n";

$inizio = microtime(true);
$mailer = Mailer::getInstance();
$ok     = $mailer->send($destinatario, 'Artemisys — messaggio di prova', $html);
$durata = round(microtime(true) - $inizio, 2);

if ($ok) {
    echo "\nOK — messaggio accettato dal server SMTP in {$durata}s.\n";
    if ($cfg['log_only']) {
        echo "     (MAIL_LOG_ONLY attivo: il messaggio e finito nel log, non e stato spedito)\n";
    } else {
        echo "     Controlla la posta in arrivo, e anche lo spam.\n";
    }
    exit(0);
}

echo "\nFALLITO dopo {$durata}s.\n";
echo "Errore: " . ($mailer->lastError() ?: 'non specificato') . "\n\n";
echo "Cause piu frequenti:\n";
echo "  - Password normale al posto della Password per le app (Gmail).\n";
echo "  - Verifica in due passaggi non attiva sull'account Google.\n";
echo "  - Porta 587 in uscita bloccata dal firewall dell'host.\n";
echo "  - MAIL_ENABLED diverso da 1.\n";
exit(1);
