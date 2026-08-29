#!/usr/bin/env php
<?php
/** Compare live PostgreSQL vs modules/ table references. */
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
    'border', 'missing', 'menu', 'temp2',
];

$moduleTables = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/modules'));
foreach ($it as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') {
        continue;
    }
    $text = file_get_contents($f->getPathname());
    if (preg_match_all('/\b(?:FROM|JOIN|INTO|UPDATE|TABLE)\s+([a-z_][a-z0-9_]{2,})/i', $text, $m)) {
        foreach ($m[1] as $t) {
            $t = strtolower($t);
            if (!in_array($t, $sqlKeywords, true) && !str_starts_with($t, 'temp_')) {
                $moduleTables[$t] = true;
            }
        }
    }
}

$liveTables = [];
$st = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY 1");
foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $t) {
    $liveTables[strtolower($t)] = 'table';
}

$liveViews = [];
$st = $pdo->query("SELECT viewname FROM pg_views WHERE schemaname = 'public' ORDER BY 1");
foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $t) {
    $liveViews[strtolower($t)] = 'view';
}

$fdw = [];
$st = $pdo->query("SELECT foreign_table_name FROM information_schema.foreign_tables WHERE foreign_table_schema = 'public' ORDER BY 1");
foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $t) {
    $fdw[strtolower($t)] = 'fdw';
}

$present = array_merge($liveTables, $liveViews, $fdw);

$ucid = $pdo->query("SELECT data_type FROM information_schema.columns WHERE table_schema='public' AND table_name='cdatpcsuspect' AND column_name='ucid'")->fetchColumn();

echo "# Schema audit report\n\n";
echo "Database: " . (getenv('CDR_DB_NAME') ?: 'CDATDUPL_DB') . " @ " . (getenv('CDR_DB_HOST') ?: '127.0.0.1') . "\n\n";

echo "## Summary\n";
echo "- Local tables: " . count($liveTables) . "\n";
echo "- Views: " . count($liveViews) . "\n";
echo "- FDW foreign tables: " . count($fdw) . "\n";
echo "- Module SQL identifiers scanned: " . count($moduleTables) . "\n\n";

echo "## Missing objects (referenced in modules/, not found as table/view/FDW)\n";
$missing = [];
foreach (array_keys($moduleTables) as $t) {
    if (!isset($present[$t])) {
        $missing[] = $t;
    }
}
sort($missing);
echo $missing ? implode("\n", array_map(static fn ($t) => "- $t", $missing)) : "- (none)";

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
    'rowdy_sheeter_complete_data' => 'fdw (rowdy module should use this)',
];
foreach ($keyObjects as $name => $label) {
    $status = isset($present[$name]) ? 'yes (' . ($present[$name] ?? 'present') . ')' : 'MISSING';
    echo "- $name [$label]: $status\n";
}

if (isset($moduleTables['rowdy_sheeter_data1']) && !isset($present['rowdy_sheeter_data1'])) {
    echo "\n## Action needed\n";
    echo "- modules use `rowdy_sheeter_data1` but live DB has `rowdy_sheeter_complete_data` (FDW). Update PHP or add a view alias.\n";
}
if (isset($moduleTables['nbws_verify_data_important']) && !isset($present['nbws_verify_data_important'])) {
    echo "- `nbws_verify_data_important` used in ir.php is not on CDATDUPL_DB — needs FDW import or table create.\n";
}
