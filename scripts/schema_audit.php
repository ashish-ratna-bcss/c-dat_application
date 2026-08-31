#!/usr/bin/env php
<?php
/** Compare live PostgreSQL vs modules/ table references. Exit 1 if gaps remain. */
require_once dirname(__DIR__) . '/modules/common/bootstrap.php';

$pdo = get_cdat_pdo();

$sqlKeywords = [
    'select', 'where', 'temp', 'public', 'case', 'left', 'inner', 'outer', 'when', 'then',
    'else', 'end', 'and', 'or', 'not', 'null', 'true', 'false', 'as', 'on', 'by', 'order',
    'group', 'having', 'limit', 'offset', 'distinct', 'union', 'all', 'exists', 'in', 'is',
    'like', 'between', 'with', 'lateral', 'information_schema', 'view', 'table', 'class',
    'creation', 'creator', 'date', 'document', 'first', 'for', 'here', 'id', 'if', 'name',
    'phones', 'production', 'reference', 'result1', 'routes', 'selection', 'staging', 'that',
    'the', 'this', 'upload', 'will', 'xx', 'tt', 't', 'a', 'another', 'anyone', 'are',
    'border', 'missing', 'menu', 'temp2', 'any', 'profile', 'postgresql', 'training_db',
    'columns', 'duration', 'incoming', 'other', 'php', 'starttime', 'tables', 'phones', 'set',
    'drop', 'create', 'distinct', 'select', 'values', 'trim', 'coalesce', 'to_char', 'ltrim',
    'rtrim', 'replace', 'count', 'min', 'max', 'sum', 'avg', 'cast', 'numeric', 'varchar',
];

/** Tables not required (removed modules, optional features). */
$allowlistMissing = [
    'rowdy_sheeter_data1',
    'rowdy_sheeter_complete_data',
];

/** Strip SQL string literals so banner text like "FROM JAIL" is not parsed as a table. */
function schema_audit_strip_sql_literals(string $code): string
{
    $code = preg_replace("/'([^'\\\\]|\\\\.)*'/s", "''", $code) ?? $code;
    return preg_replace('/"([^"\\\\]|\\\\.)*"/s', '""', $code) ?? $code;
}

$moduleTables = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/modules'));
foreach ($it as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') {
        continue;
    }
    $path = $f->getPathname();
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        continue;
    }
    foreach ($lines as $line) {
        $code = preg_replace('/\/\/.*$/', '', $line);
        $code = preg_replace('/\/\*.*?\*\//', '', $code);
        $code = preg_replace('/\bTRIM\s*\([^)]*\bFROM\s+[a-z_][a-z0-9_]*\)/i', '', $code);
        $code = schema_audit_strip_sql_literals($code);
        if (preg_match_all('/\b(?:FROM|JOIN|INTO|UPDATE|TABLE)\s+([a-z_][a-z0-9_.]{2,})/i', $code, $m)) {
            foreach ($m[1] as $t) {
                $t = strtolower($t);
                if (str_contains($t, '.')) {
                    $parts = explode('.', $t);
                    $t = end($parts);
                }
                if (!in_array($t, $sqlKeywords, true) && !str_starts_with($t, 'temp_')) {
                    $moduleTables[$t] = true;
                }
            }
        }
    }
}

$liveTables = [];
$st = $pdo->query(
    "SELECT schemaname, tablename FROM pg_tables
     WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
     ORDER BY 1, 2"
);
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $row = array_change_key_case($row, CASE_LOWER);
    $liveTables[strtolower((string) $row['tablename'])] = $row['schemaname'] . '.table';
}

$liveViews = [];
$st = $pdo->query(
    "SELECT schemaname, viewname FROM pg_views
     WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
     ORDER BY 1, 2"
);
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $row = array_change_key_case($row, CASE_LOWER);
    $liveViews[strtolower((string) $row['viewname'])] = $row['schemaname'] . '.view';
}

$fdw = [];
$st = $pdo->query(
    "SELECT foreign_table_schema, foreign_table_name
     FROM information_schema.foreign_tables
     WHERE foreign_table_schema = 'public'
     ORDER BY 1, 2"
);
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $row = array_change_key_case($row, CASE_LOWER);
    $fdw[strtolower((string) $row['foreign_table_name'])] = 'fdw';
}

$present = array_merge($liveTables, $liveViews, $fdw);

$ucid = $pdo->query(
    "SELECT data_type FROM information_schema.columns
     WHERE table_schema='public' AND table_name='cdatpcsuspect' AND column_name='ucid'"
)->fetchColumn();

echo "# Schema audit report\n\n";
echo 'Database: ' . (getenv('CDR_DB_NAME') ?: 'CDATDUPL_DB') . ' @ ' . (getenv('CDR_DB_HOST') ?: '127.0.0.1') . "\n\n";

echo "## Summary\n";
echo '- Local tables (all schemas): ' . count($liveTables) . "\n";
echo '- Views: ' . count($liveViews) . "\n";
echo '- FDW foreign tables: ' . count($fdw) . "\n";
echo '- Module SQL identifiers scanned: ' . count($moduleTables) . "\n\n";

echo "## Missing objects (referenced in modules/, not found as table/view/FDW)\n";
$missing = [];
foreach (array_keys($moduleTables) as $t) {
    if ($t === '' || isset($present[$t]) || in_array($t, $allowlistMissing, true)) {
        continue;
    }
    $missing[] = $t;
}
sort($missing);
echo $missing ? implode("\n", array_map(static fn ($t) => "- $t", $missing)) : '- (none)';

echo "\n\n## FDW foreign tables\n";
foreach (array_keys($fdw) as $t) {
    echo "- $t\n";
}

echo "\n## cdatpcsuspect.ucid\n";
echo $ucid ? "- live type: $ucid\n" : "- column not found\n";

echo "\n## Key app objects\n";
$keyObjects = [
    'cdat_details' => 'view (summary/CDAT)',
    'cdat_details1' => 'view (summary/CDAT)',
    'cdatpcsuspect' => 'table',
    'jrms_total_2012_to_2017' => 'fdw',
    'ir_particulars' => 'fdw',
    'habitual_offenders' => 'fdw',
    'nbws_verify_data_important' => 'table (IR detail NBWS)',
    'training_strength_particulars' => 'fdw (TRAINING_DB)',
    'trng_att_with_empid' => 'fdw (TRAINING_DB)',
];
foreach ($keyObjects as $name => $label) {
    $status = isset($present[$name]) ? 'yes (' . ($present[$name] ?? 'present') . ')' : 'MISSING';
    echo "- $name [$label]: $status\n";
}

if ($missing !== []) {
    echo "\n## FAILED\n";
    echo count($missing) . " missing object(s). Run sql/training_db.sql, sql/nbws_table.sql, or bash sql/apply_fdw.sh as needed.\n";
    exit(1);
}

echo "\n## PASSED\n";
exit(0);
