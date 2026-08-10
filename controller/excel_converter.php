<?php
/**
 * Server-side Excel → CSV conversion before handing off to the document API.
 */

if (!function_exists('cdr_python_bin')) {
    /**
     * Resolve the Python 3 executable for the current OS.
     * Windows usually has `python` (not `python3`); Linux/live has `python3`.
     * Override with the CDR_PYTHON env var (e.g. a full path to python.exe).
     */
    function cdr_python_bin(): string
    {
        $env = getenv('CDR_PYTHON');
        if ($env !== false && trim($env) !== '') {
            return trim($env);
        }
        return stripos(PHP_OS, 'WIN') === 0 ? 'python' : 'python3';
    }
}

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
    $script = __DIR__ . '/../scripts/excel_to_csv.py';
    $cmd = sprintf(
        '%s %s %s %s 2>&1',
        cdr_python_bin(),
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
