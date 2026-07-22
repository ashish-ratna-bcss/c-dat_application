<?php
/**
 * Server-side Excel → CSV conversion before handing off to the document API.
 */
function convert_excel_upload_to_csv(string $sourcePath, string $ext): string
{
    $ext = strtolower($ext);
    if ($ext === 'csv') {
        return $sourcePath;
    }
    if (!in_array($ext, ['xls', 'xlsx'], true)) {
        throw new RuntimeException('Unsupported spreadsheet format.');
    }
    $csvPath = preg_replace('/\.[^.]+$/', '.csv', $sourcePath);
    if ($csvPath === $sourcePath) {
        $csvPath .= '.csv';
    }
    $script = __DIR__ . '/scripts/excel_to_csv.py';
    $cmd = sprintf(
        'python3 %s %s %s 2>&1',
        escapeshellarg($script),
        escapeshellarg($sourcePath),
        escapeshellarg($csvPath)
    );
    $output = [];
    $code = 0;
    exec($cmd, $output, $code);
    if ($code !== 0 || !is_file($csvPath)) {
        throw new RuntimeException('Excel conversion failed: ' . implode("\n", $output));
    }
    return $csvPath;
}
