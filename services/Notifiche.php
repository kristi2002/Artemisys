<?php
/**
 * Notifiche via email verso studenti e docenti.
 *
 * Scelta di fondo: le email NON contengono dati scolastici (voti, pagelle,
 * documenti). Avvisano che c'è qualcosa di nuovo e rimandano al login, dove
 * l'accesso è autenticato. Meno dati personali in giro per le caselle di posta.
 */

require_once BASE_PATH . 'services/Mailer.php';

class Notifiche {

    /**
     * Oltre questa soglia l'invio sincrono non è più una buona idea:
     * meglio una coda con cron. Vedi nota in fondo al file.
     */
    private const SOGLIA_AVVISO = 150;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Destinatari ──────────────────────────────────────────────────────────

    /** Studenti attivi con un indirizzo email valorizzato. */
    public function destinatariStudenti(): array {
        $rows = $this->db->query("
            SELECT email, CONCAT(nome, ' ', cognome) AS nome
            FROM studenti
            WHERE attivo = 1 AND email IS NOT NULL AND email <> ''
        ")->fetchAll();

        return $this->soloValidi($rows);
    }

    /** Docenti con account attivo. */
    public function destinatariDocenti(): array {
        $rows = $this->db->query("
            SELECT email, CONCAT(nome, ' ', cognome) AS nome
            FROM users
            WHERE ruolo = 'docente' AND attivo = 1 AND email IS NOT NULL AND email <> ''
        ")->fetchAll();

        return $this->soloValidi($rows);
    }

    /** Destinatari per il target di una comunicazione di bacheca. */
    public function perTarget(string $target): array {
        return match ($target) {
            'studenti' => $this->destinatariStudenti(),
            'docenti'  => $this->destinatariDocenti(),
            default    => $this->deduplica(array_merge(
                              $this->destinatariStudenti(),
                              $this->destinatariDocenti()
                          )),
        };
    }

    // ── Invii ────────────────────────────────────────────────────────────────

    /**
     * Avvisa i destinatari di una nuova comunicazione in bacheca.
     * Il testo integrale sta in bacheca: qui va solo un estratto.
     *
     * @return int quante email sono partite
     */
    public function comunicazione(array $com): int {
        $destinatari = $this->perTarget($com['target'] ?? 'tutti');
        if (empty($destinatari)) return 0;

        $titolo  = htmlspecialchars($com['titolo'], ENT_QUOTES, 'UTF-8');
        $estratto = mb_substr(trim(strip_tags($com['contenuto'] ?? '')), 0, 240);
        if (mb_strlen(trim(strip_tags($com['contenuto'] ?? ''))) > 240) {
            $estratto .= '…';
        }
        $estratto = nl2br(htmlspecialchars($estratto, ENT_QUOTES, 'UTF-8'));

        $urgente = ($com['tipo'] ?? '') === 'urgente';
        $link    = Mailer::appUrl(ltrim(BASE_URL, '/') . 'bacheca');

        $corpo = ($urgente
                    ? '<p style="margin:0 0 12px;padding:8px 12px;background:#fdeaea;border-left:3px solid #c0392b;'
                      . 'color:#c0392b;font-weight:600;">Comunicazione urgente</p>'
                    : '')
               . '<p style="font-size:16px;font-weight:600;margin:0 0 8px;">' . $titolo . '</p>'
               . '<p style="color:#5a6b7b;">' . $estratto . '</p>'
               . Mailer::bottone('Leggi in bacheca', $link);

        return $this->spedisci(
            $destinatari,
            'Artemisys — ' . $com['titolo'],
            Mailer::layout('Nuova comunicazione', $corpo)
        );
    }

    /**
     * Avvisa un singolo studente che la pagella è consultabile.
     * Nessun allegato e nessun voto nel messaggio: solo il rimando all'area
     * riservata.
     */
    public function pagellaPronta(int $studenteId, string $periodo = ''): bool {
        $stmt = $this->db->prepare("
            SELECT email, CONCAT(nome, ' ', cognome) AS nome
            FROM studenti
            WHERE id = ? AND attivo = 1 AND email IS NOT NULL AND email <> ''
            LIMIT 1
        ");
        $stmt->execute([$studenteId]);
        $studente = $stmt->fetch();

        if (!$studente || !filter_var($studente['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $link = Mailer::appUrl(ltrim(BASE_URL, '/') . 'studente/voti');
        $per  = $periodo !== ''
              ? ' relativa a <strong>' . htmlspecialchars($periodo, ENT_QUOTES, 'UTF-8') . '</strong>'
              : '';

        $corpo = '<p>Ciao ' . htmlspecialchars($studente['nome'], ENT_QUOTES, 'UTF-8') . ',</p>'
               . '<p>La tua pagella' . $per . ' è ora consultabile nella tua area personale.</p>'
               . Mailer::bottone('Vai alla mia area', $link)
               . '<p style="font-size:13px;color:#7b8794;">Per motivi di riservatezza i voti non vengono '
               . 'inviati per email: sono visibili solo dopo l\'accesso con le tue credenziali.</p>';

        return Mailer::getInstance()->send(
            $studente['email'],
            'Artemisys — la tua pagella è disponibile',
            Mailer::layout('Pagella disponibile', $corpo),
            $studente['nome']
        );
    }

    // ── Interni ──────────────────────────────────────────────────────────────

    private function spedisci(array $destinatari, string $oggetto, string $html): int {
        $n = count($destinatari);

        if ($n > self::SOGLIA_AVVISO) {
            error_log("[Notifiche] invio massivo a {$n} destinatari in una sola richiesta: "
                    . 'valutare una coda con cron.');
        }

        // L'invio è sincrono: un blocco grande può superare i 120s di
        // max_execution_time impostati nel Dockerfile. Si allarga la finestra
        // e si prosegue anche se l'utente chiude il browser, così non restano
        // invii a metà.
        if ($n > 20) {
            @set_time_limit(0);
            ignore_user_abort(true);
        }

        return Mailer::getInstance()->sendMany($destinatari, $oggetto, $html);
    }

    private function soloValidi(array $rows): array {
        $out = [];
        foreach ($rows as $r) {
            $email = trim((string)($r['email'] ?? ''));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out[] = ['email' => $email, 'nome' => trim((string)($r['nome'] ?? ''))];
            }
        }
        return $this->deduplica($out);
    }

    /** Stesso indirizzo su più record (es. fratelli) = una sola email. */
    private function deduplica(array $rows): array {
        $visti = [];
        $out   = [];
        foreach ($rows as $r) {
            $k = mb_strtolower($r['email']);
            if (!isset($visti[$k])) {
                $visti[$k] = true;
                $out[] = $r;
            }
        }
        return $out;
    }
}
