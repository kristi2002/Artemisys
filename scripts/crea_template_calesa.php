<?php
/**
 * Crea documenti/templates/calesa.docx dal file originale,
 * sostituendo i dati di esempio con placeholder {{CHIAVE}}.
 */
$source = __DIR__ . '/../documenti/calesa II ANNO 25 26.docx';
$destDir = __DIR__ . '/../documenti/templates';
$dest    = $destDir . '/calesa.docx';

if (!file_exists($source)) {
    fwrite(STDERR, "File sorgente non trovato: {$source}\n");
    exit(1);
}

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

copy($source, $dest);

$map = [
    'OPERATORE DELL\'ACCONCIATURA' => '{{PERCORSO}}',
    'OPERATORE DELL&#39;ACCONCIATURA' => '{{PERCORSO}}',
    '10 98686' => '{{COD_CORSO}}',
    '1098686' => '{{COD_CORSO}}',
    '1820' => '{{ORE_CORSO}}',
    'ELLECI SAS DI CARELLA DONATELLA' => '{{ENTE_GESTORE}}',
    'ELLECI SAS' => '{{SEDE_PRESSO}}',
    'MATTEO RICCI, 34' => '{{SEDE_VIA}}',
    'ANCONA' => '{{SEDE_COMUNE}}',
    '0712181303' => '{{SEDE_TELEFONO}}',
    'VITTORIA FRATERNALI' => '{{RAPPRESENTANTE_1}}',
    'Giusepponi Barbara' => '{{RAPPRESENTANTE_2}}',
    '8 / 06 /202 6' => '{{DATA_1}}',
    '9 /06/202 6' => '{{DATA_2}}',
    'dalle 8:30 alle 12:30 e dalle 13:30 alle 17:30' => '{{ORARIO_1}}',
    'P/S/O' => '{{PROVA_1}}',
    '202 5 /202 6' => '{{ANNO_SCOLASTICO}}',
    'Annualit&#224; I&#176;' => 'Annualit&#224; {{ANNO_FORMATIVO}}&#176;',
    'Annualità I°' => 'Annualità {{ANNO_FORMATIVO}}°',
];

$zip = new ZipArchive();
$zip->open($dest);
for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (!preg_match('#^word/[^/]+\.xml$#', $name)) {
        continue;
    }
    $content = $zip->getFromIndex($i);
    foreach ($map as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    $zip->addFromString($name, $content);
}
$zip->close();

echo "Template creato: {$dest}\n";
