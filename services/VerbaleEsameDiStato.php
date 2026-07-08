<?php

/**
 * Genera un ODT verbale esame di stato pre-compilato con i dati del DB.
 */
class VerbaleEsameDiStato {

    private string $templatePath;

    public function __construct() {
        $this->templatePath = BASE_PATH . 'documenti/VERBALE ESAME DI STATO PULITO.odt';
    }

    /**
     * Genera l'ODT compilato e lo restituisce come stringa binaria.
     */
    public function genera(array $esame, array $prove, array $commissione, array $iscrizioni = []): string {
        if (!file_exists($this->templatePath)) {
            throw new RuntimeException('Template verbale non trovato.');
        }

        // ── Copia il template in memoria ───────────────────────────────────
        $tmpFile = tempnam(sys_get_temp_dir(), 'verbale_') . '.odt';
        copy($this->templatePath, $tmpFile);

        $zip = new ZipArchive();
        if ($zip->open($tmpFile) !== true) {
            throw new RuntimeException('Impossibile aprire il template ODT.');
        }
        $xml = $zip->getFromName('content.xml');

        // ── Prepara i valori ────────────────────────────────────────────────
        $fmt = function(?string $d): string {
            if (!$d) return '';
            try { return (new DateTime($d))->format('d/m/Y'); } catch (Exception $e) { return $d; }
        };

        $annoFormativo = htmlspecialchars($esame['anno_label'] ?? '', ENT_XML1);
        $enteGestore   = htmlspecialchars($esame['ente_gestore'] ?? '', ENT_XML1);

        // Sede (dal percorso)
        $sedeNome = htmlspecialchars($esame['sede_nome'] ?? '', ENT_XML1);
        $sedeVia  = htmlspecialchars(trim(($esame['sede_via'] ?? '') . ' ' . ($esame['sede_comune'] ?? '')), ENT_XML1);

        $denominazione = htmlspecialchars($esame['denominazione'] ?? '', ENT_XML1);
        $tipo          = htmlspecialchars($esame['tipo'] ?? '', ENT_XML1);
        $codCorso      = htmlspecialchars($esame['cod_corso'] ?? '', ENT_XML1);
        $oreCorso      = htmlspecialchars((string)($esame['ore_corso'] ?? ''), ENT_XML1);

        // Date dal percorso
        $dataInizioCors = $fmt($esame['percorso_data_inizio'] ?? null);
        $dataFineCors   = $fmt($esame['percorso_data_fine']   ?? null);

        // Date prove
        $prova1 = null;
        $prova2 = null;
        foreach ($prove as $i => $p) {
            if ($i === 0) $prova1 = $p;
            if ($i === 1) $prova2 = $p;
        }
        $dataIniziEsami = $fmt($prova1['data'] ?? null);
        $dataFineEsami  = $fmt($prova2['data'] ?? null);

        // ── Sostituzioni nel XML ────────────────────────────────────────────

        // 1. Anno formativo
        $xml = str_replace(
            '<text:p text:style-name="P47">Anno formativo </text:p>',
            '<text:p text:style-name="P47">Anno formativo ' . $annoFormativo . '</text:p>',
            $xml
        );

        // 2. Organismo Attuatore (senza tab per tenerlo vicino)
        $xml = str_replace(
            '<text:p text:style-name="P48">Organismo Attuatore <text:tab/></text:p>',
            '<text:p text:style-name="P48">Organismo Attuatore ' . $enteGestore . '</text:p>',
            $xml
        );

        // 3. Sede del corso (nome)
        $xml = str_replace(
            '<text:p text:style-name="P49">Sede del corso </text:p>',
            '<text:p text:style-name="P49">Sede del corso ' . $sedeNome . '</text:p>',
            $xml
        );

        // 4. Indirizzo sede (riga spazi vuoti dopo la sede) - lascia vuota
        $xml = str_replace(
            '<text:p text:style-name="P54"><text:s text:c="67"/></text:p>',
            '<text:p text:style-name="P54"/>',
            $xml
        );

        // 5. Denominazione corso ("del corso")
        $xml = str_replace(
            '<text:p text:style-name="P21"><text:span text:style-name="T4">del corso </text:span>',
            '<text:p text:style-name="P21"><text:span text:style-name="T4">del corso ' . $denominazione . ' </text:span>',
            $xml
        );

        // 6. Tipo corso
        $xml = str_replace(
            '<text:p text:style-name="P21"><text:span text:style-name="T4">tipo </text:span>',
            '<text:p text:style-name="P21"><text:span text:style-name="T4">tipo ' . $tipo . ' </text:span>',
            $xml
        );

        // 7. Date corso + ore (P22)
        $xml = str_replace(
            '<text:span text:style-name="T4">data inizio </text:span><text:span text:style-name="T4">corso data termine corso </text:span><text:span text:style-name="T4">ore corso attuate </text:span>',
            '<text:span text:style-name="T4">data inizio ' . $dataInizioCors . ' </text:span><text:span text:style-name="T4">corso data termine corso ' . $dataFineCors . ' </text:span><text:span text:style-name="T4">ore corso attuate ' . $oreCorso . '</text:span>',
            $xml
        );

        // 8. Date esami (P23)
        $xml = str_replace(
            '<text:span text:style-name="T4">data inizio esami </text:span><text:span text:style-name="T4"><text:s text:c="20"/>data termine esami </text:span>',
            '<text:span text:style-name="T4">data inizio esami ' . $dataIniziEsami . ' </text:span><text:span text:style-name="T4"><text:s text:c="20"/>data termine esami ' . $dataFineEsami . ' </text:span>',
            $xml
        );

        // 9. Sede esami (P48) + codice su riga separata
        $sedeEsamiCompleta = trim($sedeNome . ' ' . $sedeVia);
        $xml = str_replace(
            '<text:p text:style-name="P48">sede esami <text:tab/>codice: </text:p>',
            '<text:p text:style-name="P48">sede esami ' . $sedeEsamiCompleta . '</text:p><text:p text:style-name="P48">codice: ' . $codCorso . '</text:p>',
            $xml
        );

        // 10. Commissione — riempi la prima cella vuota di ogni riga con il nome del membro
        // La struttura è: cella vuota (P13) | cella ruolo | cella rappresentanza
        // Ordina: presidente prima, poi commissari
        $commissari = [];
        $presidente  = null;
        foreach ($commissione as $c) {
            $nomeCompleto = htmlspecialchars(($c['cognome'] ?? '') . ' ' . ($c['nome'] ?? ''), ENT_XML1);
            if (($c['ruolo'] ?? '') === 'presidente') {
                $presidente = $nomeCompleto;
            } else {
                $commissari[] = $nomeCompleto;
            }
        }

        // Riga presidente (prima cella P13 vuota nella tabella commissione)
        if ($presidente !== null) {
            $xml = $this->sostituisciPrimaOccorrenza(
                $xml,
                '<text:p text:style-name="P13"/>',
                '<text:p text:style-name="P13">' . $presidente . '</text:p>'
            );
        }

        // Righe commissari (le successive celle P13 vuote)
        foreach ($commissari as $nome) {
            $xml = $this->sostituisciPrimaOccorrenza(
                $xml,
                '<text:p text:style-name="P13"/>',
                '<text:p text:style-name="P13">' . $nome . '</text:p>'
            );
        }

        // 11. Tabella studenti (Tabella3) — righe 1-10
        $xml = $this->riempiStudenti($xml, $iscrizioni, $fmt);

        // ── Scrivi il XML aggiornato nel ZIP ────────────────────────────────
        $zip->addFromString('content.xml', $xml);
        $zip->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);
        return $content;
    }

    /**
     * Riempie le righe della tabella studenti (Tabella3) con i dati degli iscritti.
     * Righe 1-10: usa le righe già presenti nel template.
     * Studenti 11+: aggiunge nuove righe prima della chiusura di Tabella3.
     */
    private function riempiStudenti(string $xml, array $iscrizioni, callable $fmt): string {
        // ── Riempi righe 1-10 (già presenti nel template) ─────────────────
        $xml = preg_replace_callback(
            '/<table:table-row\b[^>]*>(?:(?!<table:table-row\b).)*?<text:p text:style-name="P19">(\d+)<\/text:p>(?:(?!<table:table-row\b).)*?<\/table:table-row>/s',
            function (array $m) use ($iscrizioni, $fmt): string {
                $n = (int)$m[1];
                if ($n < 1) return $m[0];

                $studente = $iscrizioni[$n - 1] ?? null;
                if ($studente === null) return $m[0]; // riga vuota: lascia com'è

                $cognomeNome  = htmlspecialchars(trim(($studente['cognome'] ?? '') . ' ' . ($studente['nome'] ?? '')), ENT_XML1);
                $luogoNascita = htmlspecialchars($studente['luogo_nascita'] ?? '', ENT_XML1);
                $dataNascita  = $fmt($studente['data_nascita'] ?? null);
                $residenza    = htmlspecialchars($studente['indirizzo'] ?? '', ENT_XML1);

                $row     = $m[0];
                $cellIdx = 0;

                $row = preg_replace_callback(
                    '/<table:table-cell\b[^>]*>.*?<\/table:table-cell>/s',
                    function (array $cm) use (&$cellIdx, $cognomeNome, $luogoNascita, $dataNascita, $residenza): string {
                        $cellIdx++;
                        $cell = $cm[0];
                        if ($cellIdx === 2) {
                            $cell = preg_replace('/(<table:table-cell[^>]*>).*?(<\/table:table-cell>)/s',
                                '$1<text:p text:style-name="P61">' . $cognomeNome . '</text:p>$2', $cell);
                        } elseif ($cellIdx === 3) {
                            $cell = preg_replace('/(<table:table-cell[^>]*>).*?(<\/table:table-cell>)/s',
                                '$1<text:p text:style-name="P61">' . $luogoNascita . '</text:p>$2', $cell);
                        } elseif ($cellIdx === 4) {
                            $cell = preg_replace('/(<table:table-cell[^>]*>).*?(<\/table:table-cell>)/s',
                                '$1<text:p text:style-name="P65">' . $dataNascita . '</text:p>$2', $cell);
                        } elseif ($cellIdx === 5) {
                            $cell = preg_replace('/(<table:table-cell[^>]*>).*?(<\/table:table-cell>)/s',
                                '$1<text:p text:style-name="P61">' . $residenza . '</text:p>$2', $cell);
                        }
                        return $cell;
                    },
                    $row
                );

                return $row;
            },
            $xml
        );

        // ── Aggiungi righe extra per studenti oltre le 14 righe del template ─
        $templateRows = 14; // righe pre-costruite nel template ODT
        if (count($iscrizioni) > $templateRows) {
            $righeExtra = '';
            foreach (array_slice($iscrizioni, $templateRows) as $idx => $studente) {
                $n            = $idx + $templateRows + 1;
                $cognomeNome  = htmlspecialchars(trim(($studente['cognome'] ?? '') . ' ' . ($studente['nome'] ?? '')), ENT_XML1);
                $luogoNascita = htmlspecialchars($studente['luogo_nascita'] ?? '', ENT_XML1);
                $dataNascita  = $fmt($studente['data_nascita'] ?? null);
                $residenza    = htmlspecialchars($studente['indirizzo'] ?? '', ENT_XML1);

                $righeExtra .=
                    '<table:table-row table:style-name="Tabella3.2">' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P19">' . $n . '</text:p></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P61">' . $cognomeNome . '</text:p></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P61">' . $luogoNascita . '</text:p></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P65">' . $dataNascita . '</text:p></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.E4" office:value-type="string"><text:p text:style-name="P34">' . $residenza . '</text:p></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P17"/></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P17"/></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P19"/></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P19"/></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P19"/></table:table-cell>' .
                    '<table:table-cell table:style-name="Tabella3.F1" office:value-type="string"><text:p text:style-name="P19"/></table:table-cell>' .
                    '</table:table-row>';
            }

            // Inserisci prima della chiusura di Tabella3
            $posTabella3    = strpos($xml, 'table:name="Tabella3"');
            $posChiusura    = strpos($xml, '</table:table>', $posTabella3);
            $xml = substr($xml, 0, $posChiusura) . $righeExtra . substr($xml, $posChiusura);
        }

        return $xml;
    }

    private function sostituisciPrimaOccorrenza(string $str, string $search, string $replace): string {
        $pos = strpos($str, $search);
        if ($pos === false) return $str;
        return substr($str, 0, $pos) . $replace . substr($str, $pos + strlen($search));
    }
}
