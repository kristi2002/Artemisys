<?php
/**
 * Wrapper su PHPMailer.
 *
 * Regola di base: un'email che non parte NON deve mai rompere la richiesta in
 * corso. Tutti i metodi di invio restituiscono bool/int e registrano l'errore
 * nel log; sta al chiamante decidere se avvisare l'utente.
 */

require_once BASE_PATH . 'config/mail.php';

// vendor/ non e' versionato (viene creato dal build o da "composer install").
// Se manca, l'app deve continuare a funzionare: si disattiva solo l'invio.
if (is_file(BASE_PATH . 'vendor/autoload.php')) {
    require_once BASE_PATH . 'vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mailer {

    private static ?Mailer $instance = null;
    private array  $cfg;
    private string $lastError = '';

    private function __construct() {
        $this->cfg = MailConfig::get();
    }

    public static function getInstance(): Mailer {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function lastError(): string {
        return $this->lastError;
    }

    /** PHPMailer e' stato installato? */
    public static function libreriaDisponibile(): bool {
        return class_exists(PHPMailer::class);
    }

    /** L'invio è configurato e attivo? */
    public function isEnabled(): bool {
        if (!$this->cfg['enabled']) return false;
        if ($this->cfg['log_only']) return true;   // la modalita' log non usa PHPMailer
        if (!self::libreriaDisponibile()) return false;
        return $this->cfg['username'] !== '' && $this->cfg['password'] !== '';
    }

    /**
     * Invia un messaggio a un destinatario.
     *
     * @param string $to       indirizzo destinatario
     * @param string $subject  oggetto
     * @param string $htmlBody corpo HTML completo (vedi self::layout())
     * @param string $toName   nome del destinatario (facoltativo)
     */
    public function send(string $to, string $subject, string $htmlBody, string $toName = ''): bool {
        return $this->dispatch([[$to, $toName]], $subject, $htmlBody) === 1;
    }

    /**
     * Invia lo STESSO messaggio a più destinatari riusando una sola connessione
     * SMTP. Ogni destinatario riceve una copia separata: nessuno vede gli
     * indirizzi degli altri.
     *
     * @param array $recipients elenco di ['email' => ..., 'nome' => ...]
     * @return int numero di invii riusciti
     */
    public function sendMany(array $recipients, string $subject, string $htmlBody): int {
        $list = [];
        foreach ($recipients as $r) {
            $email = trim($r['email'] ?? '');
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $list[] = [$email, trim($r['nome'] ?? '')];
            }
        }
        return $this->dispatch($list, $subject, $htmlBody);
    }

    // ── Motore di invio ──────────────────────────────────────────────────────
    private function dispatch(array $recipients, string $subject, string $htmlBody): int {
        $this->lastError = '';

        if (empty($recipients)) {
            $this->lastError = 'Nessun destinatario valido.';
            return 0;
        }

        if (!$this->cfg['enabled']) {
            $this->lastError = 'Invio email disattivato (MAIL_ENABLED non vale 1).';
            error_log('[Mailer] ' . $this->lastError);
            return 0;
        }

        // Modalità sviluppo: scrive nel log invece di spedire davvero.
        if ($this->cfg['log_only']) {
            foreach ($recipients as [$email, $nome]) {
                error_log(sprintf(
                    "[Mailer:LOG_ONLY] a=%s oggetto=%s\n%s",
                    $email, $subject, $this->toPlainText($htmlBody)
                ));
            }
            return count($recipients);
        }

        if ($this->cfg['from'] === '') {
            $this->lastError = 'MAIL_FROM non configurato.';
            error_log('[Mailer] ' . $this->lastError);
            return 0;
        }

        if (!self::libreriaDisponibile()) {
            $this->lastError = 'PHPMailer non installato: eseguire "composer install".';
            error_log('[Mailer] ' . $this->lastError);
            return 0;
        }

        $mail = new PHPMailer(true);
        $sent = 0;

        try {
            $this->configura($mail, $subject, $htmlBody);

            foreach ($recipients as [$email, $nome]) {
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($email, $nome);
                    $mail->send();
                    $sent++;
                } catch (PHPMailerException $e) {
                    // Un destinatario non valido non deve fermare gli altri, ma
                    // l'errore va conservato: e' quello che serve per capire
                    // cosa non va (credenziali sbagliate, indirizzo rifiutato...).
                    $this->lastError = $mail->ErrorInfo ?: $e->getMessage();
                    error_log("[Mailer] invio fallito a {$email}: " . $this->lastError);
                }
            }

            $mail->smtpClose();

        } catch (PHPMailerException $e) {
            $this->lastError = $mail->ErrorInfo ?: $e->getMessage();
            error_log('[Mailer] errore SMTP: ' . $this->lastError);
            return $sent;
        }

        if ($sent === 0 && $this->lastError === '') {
            $this->lastError = 'Nessun messaggio inviato.';
        }
        return $sent;
    }

    /**
     * Applica a PHPMailer la configurazione corrente e il contenuto.
     * Condiviso fra invio reale e anteprima: così l'anteprima mostra davvero
     * le intestazioni che partirebbero, non una loro imitazione.
     */
    private function configura(PHPMailer $mail, string $subject, string $htmlBody): void {
        $mail->isSMTP();
        $mail->Host     = $this->cfg['host'];
        $mail->Port     = $this->cfg['port'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->cfg['username'];
        $mail->Password = $this->cfg['password'];
        $mail->Timeout  = $this->cfg['timeout'];
        $mail->CharSet  = PHPMailer::CHARSET_UTF8;

        $mail->SMTPSecure = $this->cfg['encryption'] === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        // Una sola connessione per tutti i destinatari.
        $mail->SMTPKeepAlive = true;

        // 1° argomento = indirizzo reale (quello che autentica), 2° = nome
        // mostrato al destinatario (MAIL_FROM_NAME). Il nome è testo libero;
        // l'indirizzo no: con Gmail deve coincidere con MAIL_USERNAME o con un
        // alias verificato, altrimenti Google lo riscrive.
        $mail->setFrom($this->cfg['from'], $this->cfg['from_name']);
        if ($this->cfg['reply_to'] !== '') {
            $mail->addReplyTo($this->cfg['reply_to'], $this->cfg['from_name']);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $this->toPlainText($htmlBody);
    }

    /**
     * Costruisce il messaggio SENZA spedirlo e restituisce le intestazioni
     * principali. Serve a controllare mittente e nome visualizzato prima di
     * un invio vero, senza bisogno di credenziali SMTP funzionanti.
     */
    public function anteprimaIntestazioni(string $to, string $subject, string $htmlBody, string $toName = ''): string {
        if (!self::libreriaDisponibile()) {
            return 'PHPMailer non installato: eseguire "composer install".';
        }

        $mail = new PHPMailer(true);

        try {
            $this->configura($mail, $subject, $htmlBody);
            $mail->addAddress($to, $toName);
            $mail->preSend();   // assembla il messaggio, non apre connessioni

            $righe = [];
            foreach (explode("
", $mail->getSentMIMEMessage()) as $r) {
                if ($r === '') break;   // riga vuota = fine intestazioni
                if (preg_match('/^(From|To|Reply-To|Subject):/i', $r)) {
                    $righe[] = $r;
                }
            }
            return implode("
", $righe);

        } catch (PHPMailerException $e) {
            return 'Anteprima non riuscita: ' . ($mail->ErrorInfo ?: $e->getMessage());
        }
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /**
     * URL assoluto dell'app, per i link dentro le email.
     * APP_URL è obbligatorio per gli invii da CLI/cron, dove $_SERVER è vuoto.
     */
    public static function appUrl(string $path = ''): string {
        $cfg  = MailConfig::get();
        $base = $cfg['app_url'];

        // Ripiego sull'host della richiesta SOLO se APP_URL non e' configurato.
        //
        // ATTENZIONE: $_SERVER['HTTP_HOST'] arriva dall'header Host, che e'
        // scelto da chi fa la richiesta. Senza APP_URL, un attaccante puo'
        // chiedere il recupero password per l'indirizzo di un'altra persona
        // falsificando l'header: la vittima riceve la mail, ma il link punta
        // al server dell'attaccante e il token finisce nelle sue mani.
        // APP_URL impostato rende il link immune. Il ripiego resta solo per non
        // spedire link rotti in sviluppo, e viene segnalato nel log.
        if ($base === '' && !empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base   = $scheme . '://' . $_SERVER['HTTP_HOST'];
            error_log('[Mailer] APP_URL non configurato: i link nelle email seguono '
                    . "l'header Host ({$_SERVER['HTTP_HOST']}), che non e' affidabile. "
                    . 'Impostare APP_URL.');
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Impagina un contenuto nel layout standard delle email Artemisys.
     * Stili inline e struttura a tabelle: i client di posta ignorano quasi
     * sempre i fogli di stile esterni e reggono male i layout moderni.
     */
    public static function layout(string $titolo, string $contenutoHtml): string {
        $anno = date('Y');
        $t    = htmlspecialchars($titolo, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px 12px;">
    <tr><td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
             style="max-width:560px;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e3e8ee;">
        <tr>
          <td style="background:#1a3a5c;padding:20px 28px;color:#ffffff;font-size:18px;font-weight:600;">Artemisys</td>
        </tr>
        <tr>
          <td style="padding:28px;color:#2c3e50;font-size:15px;line-height:1.6;">
            <h2 style="margin:0 0 16px;font-size:19px;color:#1a3a5c;">' . $t . '</h2>
            ' . $contenutoHtml . '
          </td>
        </tr>
        <tr>
          <td style="padding:16px 28px;background:#f8fafc;border-top:1px solid #e3e8ee;color:#7b8794;font-size:12px;line-height:1.5;">
            Messaggio automatico inviato da Artemisys.<br>
            &copy; ' . $anno . ' Artemisys
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';
    }

    /** Bottone per email: tabella e non <a> stilizzato, cosi regge su Outlook. */
    public static function bottone(string $testo, string $url): string {
        $t = htmlspecialchars($testo, ENT_QUOTES, 'UTF-8');
        $u = htmlspecialchars($url,   ENT_QUOTES, 'UTF-8');
        return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;">'
             . '<tr><td style="background:#1a3a5c;border-radius:6px;">'
             . '<a href="' . $u . '" style="display:inline-block;padding:12px 26px;color:#ffffff;'
             . 'text-decoration:none;font-weight:600;font-size:15px;">' . $t . '</a>'
             . '</td></tr></table>';
    }

    /** Versione testuale del messaggio, per i client che non mostrano HTML. */
    private function toPlainText(string $html): string {
        $txt = preg_replace('#<(head|style|script)\b.*?</\1>#is', '', $html);
        $txt = preg_replace('#<br\s*/?>#i', "\n", $txt);
        $txt = preg_replace('#</(p|div|tr|h[1-6])>#i', "\n\n", $txt);
        $txt = strip_tags($txt);
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        $txt = preg_replace('/[ \t]+/', ' ', $txt);
        $txt = preg_replace("/\n\s*\n\s*\n+/", "\n\n", $txt);
        return trim($txt);
    }
}
