<?php
$path = __DIR__ . '/documenti/VERBALE ESAME DI STATO PULITO.odt';
$zip  = new ZipArchive();
$zip->open($path);
$xml = $zip->getFromName('content.xml');
$zip->close();

// Paragrafo P21 completo (denominazione corso + tipo)
$pos = strpos($xml, 'text:style-name="P21"');
$chunk = substr($xml, $pos - 10, 1200);
echo "=== P21 (denominazione+tipo) ===\n$chunk\n\n";

// Paragrafo P22 completo (date corso + ore)
$pos = strpos($xml, 'text:style-name="P22"');
$chunk = substr($xml, $pos - 10, 600);
echo "=== P22 (date corso + ore) ===\n$chunk\n\n";

// Paragrafo P23 completo (date esami)
$pos = strpos($xml, 'text:style-name="P23"');
$chunk = substr($xml, $pos - 10, 400);
echo "=== P23 (date esami) ===\n$chunk\n\n";

// Riga Presidente nella tabella commissione
$pos = strpos($xml, 'Presidente ');
$chunk = substr($xml, $pos - 300, 600);
echo "=== Presidente row ===\n$chunk\n\n";
