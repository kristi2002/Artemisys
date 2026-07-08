<?php

/**
 * Sostituisce placeholder {{CHIAVE}} nei file XML di un documento .docx.
 * I file .docx sono archivi ZIP: non serve Composer né PHPWord.
 */
class DocxTemplate {

    private string $templatePath;
    private array $replacements = [];

    public function __construct(string $templatePath) {
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Template non trovato: {$templatePath}");
        }
        $this->templatePath = $templatePath;
    }

    public function set(string $key, ?string $value): self {
        $this->replacements[$key] = $value ?? '';
        return $this;
    }

    public function setMany(array $data): self {
        foreach ($data as $key => $value) {
            $this->set((string)$key, is_scalar($value) ? (string)$value : '');
        }
        return $this;
    }

    /**
     * Genera il documento in un file temporaneo e restituisce il percorso.
     */
    public function generate(?string $outputPath = null): string {
        $tmpDir = sys_get_temp_dir() . '/docx_' . uniqid('', true);
        $workDocx = $tmpDir . '/work.docx';

        if (!is_dir($tmpDir) && !mkdir($tmpDir, 0755, true)) {
            throw new RuntimeException('Impossibile creare cartella temporanea.');
        }

        if (!copy($this->templatePath, $workDocx)) {
            throw new RuntimeException('Impossibile copiare il template.');
        }

        $zip = new ZipArchive();
        if ($zip->open($workDocx) !== true) {
            throw new RuntimeException('Impossibile aprire il documento Word.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!preg_match('#^word/[^/]+\.xml$#', $name)) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) {
                continue;
            }

            $updated = $this->applyReplacements($content);
            if ($updated !== $content) {
                $zip->addFromString($name, $updated);
            }
        }

        $zip->close();

        if ($outputPath === null) {
            $outputPath = $tmpDir . '/output.docx';
        }

        if (!rename($workDocx, $outputPath)) {
            copy($workDocx, $outputPath);
            @unlink($workDocx);
        }

        return $outputPath;
    }

    /**
     * Invia il file al browser per download.
     */
    public function download(string $filename): void {
        $path = $this->generate();
        $safeName = preg_replace('/[^\w\-\. ]+/u', '_', $filename) ?: 'documento.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache, must-revalidate');

        readfile($path);
        @unlink($path);
        @rmdir(dirname($path));
        exit;
    }

    private function applyReplacements(string $xml): string {
        if (empty($this->replacements)) {
            return $xml;
        }

        $search  = [];
        $replace = [];
        foreach ($this->replacements as $key => $value) {
            $safe = htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $search[]  = '{{' . $key . '}}';
            $replace[] = $safe;
        }

        return str_replace($search, $replace, $xml);
    }
}
