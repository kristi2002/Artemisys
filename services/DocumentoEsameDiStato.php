<?php

require_once BASE_PATH . 'models/EsameDiStato.php';

class DocumentoEsameDiStato {

    private EsameDiStato $model;

    public function __construct() {
        $this->model = new EsameDiStato();
        $this->model->createTables();
    }

    public function getTemplatePath(string $tipo = 'calesa'): string {
        $map = [
            'calesa' => BASE_PATH . 'documenti/templates/calesa.docx',
        ];
        $path = $map[$tipo] ?? $map['calesa'];
        if (!file_exists($path)) {
            throw new RuntimeException(
                'Template Word non trovato. Copia il file in documenti/templates/calesa.docx'
            );
        }
        return $path;
    }

    public function buildMergeData(int $esameId): array {
        $esame = $this->model->findById($esameId);
        if (!$esame) {
            throw new RuntimeException('Esame di stato non trovato.');
        }

        $ordinali = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V',
            6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X',
        ];
        $annoRomano = $esame['anno_numero']
            ? ($ordinali[$esame['anno_numero']] ?? (string)$esame['anno_numero'])
            : '';

        $commissione = $this->model->getCommissione($esameId);
        $rappresentanti = [];
        foreach ($commissione as $c) {
            $rappresentanti[] = $c['cognome'] . ' ' . $c['nome'];
        }
        if (empty($rappresentanti) && !empty($esame['rapp_cognome'])) {
            $rappresentanti[] = $esame['rapp_cognome'] . ' ' . $esame['rapp_nome'];
        }

        $dataFmt = $esame['data'] ? date('d/m/Y', strtotime($esame['data'])) : '';
        $oraFmt  = $esame['ora_inizio'] ? substr($esame['ora_inizio'], 0, 5) : '';

        $prove = $this->model->getProve($esameId);
        $data = [
            'DENOMINAZIONE'      => $esame['denominazione'],
            'TIPO'               => $esame['tipo'] ?? '',
            'ANNO_SCOLASTICO'    => $esame['anno_label'] ?? '',
            'ANNO_FORMATIVO'     => $annoRomano,
            'PERCORSO'           => $esame['percorso_nome'],
            'COD_CORSO'          => $esame['cod_corso'] ?? '',
            'COD_DID_REG'        => $esame['cod_did_reg'] ?? '',
            'ORE_CORSO'          => $esame['ore_corso'] ?? '',
            'ENTE_GESTORE'       => $esame['ente_gestore'] ?? '',
            'SEDE_PRESSO'        => $esame['sede_presso']   ?: 'ELLECI SAS',
            'SEDE_VIA'           => $esame['sede_via']      ?: 'MATTEO RICCI, 34',
            'SEDE_COMUNE'        => $esame['sede_comune']   ?: 'ANCONA',
            'SEDE_TELEFONO'      => $esame['sede_telefono'] ?: '0712181303',
            'DATA_ESAME'         => $dataFmt,
            'ORARIO_ESAME'       => $oraFmt,
            'RAPPRESENTANTE_1'   => $rappresentanti[0] ?? '',
            'RAPPRESENTANTE_2'   => $rappresentanti[1] ?? '',
            'RAPPRESENTANTI'     => implode("\n", $rappresentanti),
            'NUM_ISCRITTI'       => (string)count($this->model->getIscrizioni($esameId)),
            'NOTE'               => $esame['note'] ?? '',
        ];

        for ($n = 1; $n <= 3; $n++) {
            $prova = $prove[$n - 1] ?? null;
            $dataDefault = ($n === 1) ? '8/06/2026' : '9/06/2026';
            $data["DATA_{$n}"]  = ($prova && $prova['data'])
                ? date('d/m/Y', strtotime($prova['data']))
                : $dataDefault;
            $data["PROVA_{$n}"] = 'P/S/O';

            $oraInizio = $prova && $prova['ora_inizio'] ? substr($prova['ora_inizio'], 0, 5) : '';
            $oraFine   = $prova && $prova['ora_fine']   ? substr($prova['ora_fine'],   0, 5) : '';

            // Fallback ai valori originali del template se l'utente non ha ancora salvato gli orari
            $data["ORA_INIZIO_{$n}"] = $oraInizio ?: '8:30';
            $data["ORA_FINE_{$n}"]   = $oraFine   ?: '17:30';

            // Mantiene ORARIO_N (formato combinato) per retrocompatibilità con altri template
            $data["ORARIO_{$n}"] = ($oraInizio ?: '8:30') . ' - ' . ($oraFine ?: '17:30');
        }

        return $data;
    }

    public function genera(int $esameId, string $tipo = 'calesa'): DocxTemplate {
        require_once BASE_PATH . 'services/DocxTemplate.php';

        // Crea copia temporanea del template per iniettare i placeholder degli orari
        $templatePath = $this->getTemplatePath($tipo);
        $tmpPath = sys_get_temp_dir() . '/calesa_pre_' . uniqid('', true) . '.docx';
        copy($templatePath, $tmpPath);
        $this->preprocessTemplate($tmpPath);
        register_shutdown_function(fn() => @unlink($tmpPath));

        $docx = new DocxTemplate($tmpPath);
        $docx->setMany($this->buildMergeData($esameId));
        return $docx;
    }

    /**
     * Apre il .docx e inietta {{ORA_INIZIO_N}} / {{ORA_FINE_N}} nelle celle
     * che contengono orari numerici (es. "8:30", "17:30") tra {{DATA_N}} e {{PROVA_N}}.
     * Necessario perché Word spezza ogni valore in un <w:tc> separato,
     * rendendo impossibile la sostituzione diretta con str_replace.
     */
    private function preprocessTemplate(string $docxPath): void {
        $zip = new ZipArchive();
        if ($zip->open($docxPath) !== true) return;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!preg_match('#^word/[^/]+\.xml$#', $name)) continue;

            $content = $zip->getFromIndex($i);
            if ($content === false) continue;

            $modified = $this->injectOrarioPlaceholders($content);
            if ($modified !== $content) {
                $zip->addFromString($name, $modified);
            }
        }

        $zip->close();
    }

    private function injectOrarioPlaceholders(string $xml): string {
        for ($n = 1; $n <= 2; $n++) {
            $startTag = "{{DATA_{$n}}}";
            $endTag   = "{{PROVA_{$n}}}";

            $startPos = strpos($xml, $startTag);
            $endPos   = strpos($xml, $endTag);
            if ($startPos === false || $endPos === false || $startPos >= $endPos) continue;

            $before  = substr($xml, 0, $startPos + strlen($startTag));
            $segment = substr($xml, $startPos + strlen($startTag), $endPos - $startPos - strlen($startTag));
            $after   = substr($xml, $endPos);

            // Trova tutti i valori orari numerici (\d+:\d+) nelle celle del segmento
            preg_match_all('/<w:t(?:\s[^>]*)?>(\d+:\d+)<\/w:t>/', $segment, $matches, PREG_OFFSET_CAPTURE);

            if (!empty($matches[0])) {
                $all   = $matches[0];
                $last  = $all[count($all) - 1];

                // Sostituisce prima l'ultimo (così gli offset precedenti restano validi)
                $segment = substr_replace(
                    $segment,
                    '<w:t xml:space="preserve">{{ORA_FINE_' . $n . '}}</w:t>',
                    $last[1],
                    strlen($last[0])
                );

                // Ri-cerca il primo dopo la modifica
                if (preg_match('/<w:t(?:\s[^>]*)?>(\d+:\d+)<\/w:t>/', $segment, $first, PREG_OFFSET_CAPTURE)) {
                    $segment = substr_replace(
                        $segment,
                        '<w:t xml:space="preserve">{{ORA_INIZIO_' . $n . '}}</w:t>',
                        $first[0][1],
                        strlen($first[0][0])
                    );
                }
            }

            $xml = $before . $segment . $after;
        }

        return $xml;
    }
}
