<?php
/**
 * sqlsrv_* shim for legacy CDAT PHP app on PostgreSQL.
 * Routes CDATDUPL -> postgres, distributed tables via FDW views.
 */
if (defined('SQLSRV_COMPAT_LOADED')) {
    return;
}
define('SQLSRV_COMPAT_LOADED', true);
define('SQLSRV_FETCH_ASSOC', 2);
define('SQLSRV_CURSOR_KEYSET', 'keyset');
define('SQLSRV_CURSOR_FORWARD', 'forward');
define('SQLSRV_CURSOR_DYNAMIC', 'dynamic');
define('SQLSRV_CURSOR_STATIC', 'static');
define('SQLSRV_CURSOR_BUFFERED', 'buffered');

$GLOBALS['__sqlsrv_connections'] = [];
$GLOBALS['__sqlsrv_last_error'] = null;
$GLOBALS['__sqlsrv_conn_seq'] = 0;

function __sqlsrv_root(): string
{
    return __DIR__;
}

function __sqlsrv_cfg(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/db_config.php';
    }
    return $cfg;
}

function __sqlsrv_dbname(array $connectionInfo): string
{
    $name = strtolower((string)($connectionInfo['Database'] ?? 'cdatdupl'));
    $map = [
        'cdatdupl' => 'postgres',
        'cdat' => 'postgres',
        'postgres' => 'postgres',
        'twrmdb' => 'postgres',
        'irforms' => 'postgres',
        'forms' => 'postgres',
        'jrms' => 'postgres',
        'pdact' => 'postgres',
        'lostreport_hawkeye' => 'postgres',
        'migrant_labours_form' => 'postgres',
        'training_db' => 'postgres',
        'cpms' => 'postgres',
        'cafs' => 'postgres',
        'cis_data_base' => 'postgres',
        'cdat_import' => 'postgres',
        'testing_db' => 'postgres',
        'rough' => 'postgres',
        'distributed_db' => 'postgres',
    ];
    return $map[$name] ?? 'postgres';
}

function sqlsrv_connect($serverName, array $connectionInfo = [])
{
    $cfg = __sqlsrv_cfg();
    $db = __sqlsrv_dbname($connectionInfo);
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $cfg['host'], $cfg['port'], $db);
    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10,
        ]);
        // Prevent runaway scans from wedging PHP-FPM workers.
        $pdo->exec("SET statement_timeout = '120s'");
        $pdo->exec("SET idle_in_transaction_session_timeout = '60s'");
        $pdo->exec("SET lock_timeout = '30s'");
        $id = 'sqlsrv_' . (++$GLOBALS['__sqlsrv_conn_seq']);
        $GLOBALS['__sqlsrv_connections'][$id] = $pdo;
        return $id;
    } catch (Throwable $e) {
        $GLOBALS['__sqlsrv_last_error'] = [['message' => $e->getMessage(), 'code' => $e->getCode()]];
        return false;
    }
}

function __sqlsrv_pdo($conn)
{
    return $GLOBALS['__sqlsrv_connections'][$conn] ?? null;
}

function __sqlsrv_translate(string $sql): string
{
    $q = $sql;

    // Bracket quoting from MSSQL
    $q = preg_replace('/\[dbo\]\./i', '', $q);
    $q = preg_replace('/\[IN\]/i', '"IN"', $q);
    $q = preg_replace('/\[OUT\]/i', '"OUT"', $q);
    $q = preg_replace('/\[([A-Za-z0-9_]+)\]/', '$1', $q);
    $q = preg_replace('/\bAS\s+\'IN\'/i', 'AS "IN"', $q);
    $q = preg_replace('/\bAS\s+\'OUT\'/i', 'AS "OUT"', $q);

    if (preg_match('/^\s*CREATE\s+TABLE\s+#([A-Za-z0-9_]+)\s*\((.*)\)/is', $q, $m)) {
        $table = strtolower(trim($m[1]));
        $definition = trim($m[2]);
        $definition = preg_replace('/\bNVARCHAR\b/i', 'VARCHAR', $definition);
        $q = "DROP TABLE IF EXISTS $table; CREATE TEMP TABLE $table ($definition)";
    }

    if (preg_match('/^\s*SELECT\b/i', $q) && preg_match('/\bINTO\s+#([A-Za-z0-9_]+)/i', $q, $m)) {
        $q = preg_replace('/\bINTO\s+#([A-Za-z0-9_]+)/i', '', $q, 1);
        $q = 'CREATE TEMP TABLE ' . strtolower($m[1]) . ' AS ' . $q;
    }

    $q = preg_replace('/#([A-Za-z0-9_]+)/', '$1', $q);
    $q = preg_replace('/\b[A-Za-z0-9_]+\.\.([A-Za-z0-9_]+)/i', '$1', $q);
    $q = preg_replace('/\b[A-Za-z0-9_]+\.DBO\.([A-Za-z0-9_]+)/i', '$1', $q);
    $q = preg_replace('/\b[A-Za-z0-9_]+\.dbo\.([A-Za-z0-9_]+)/i', '$1', $q);
    // CDATDUPL.CDAT_RTA (single-dot db prefix after bracket strip)
    $q = preg_replace(
        '/\b(?:CDATDUPL|JRMS|FORMS|IRFORMS|PDACT|TWRMDB|CDAT|CIS_DATA_BASE|CDAT_IMPORT)\.([A-Za-z0-9_]+)/i',
        '$1',
        $q
    );
    $q = preg_replace('/^\s*SET\s+DATEFORMAT\s+\w+\s*/i', '', $q);
    $q = preg_replace('/\bWITH\s*\(\s*NOLOCK\s*\)/i', '', $q);

    // FDW views over Citus must filter by phone inside a LATERAL subquery; otherwise
    // postgres_fdw pulls every eff_to_date IS NULL row (~hundreds of millions).
    $q = preg_replace_callback(
        '/LEFT\s+JOIN\s+(cdataddress|address_other_state)\s+([A-Za-z_][A-Za-z0-9_]*)\s+ON\s+([A-Za-z_][A-Za-z0-9_]*)\.PHONE\s*=\s*\2\.PHONE\s+AND\s+\2\.EFF_TO_DATE\s+IS\s+NULL/i',
        static function ($m) {
            $table = strtolower($m[1]);
            $alias = $m[2];
            $outer = $m[3];
            return "LEFT JOIN LATERAL (SELECT * FROM $table WHERE phone = ($outer).phone AND eff_to_date IS NULL LIMIT 1) $alias ON true";
        },
        $q
    );

    $q = preg_replace_callback(
        '/LEFT\s+JOIN\s+cdatcelltowerareanew\s+([A-Za-z_][A-Za-z0-9_]*)\s+ON\s+([A-Za-z_][A-Za-z0-9_]*)\.CELLTOWERID\s*=\s*\1\.CELLTOWERID/i',
        static function ($m) {
            $alias = $m[1];
            $outer = $m[2];
            return "LEFT JOIN LATERAL (SELECT * FROM cdatcelltowerareanew WHERE celltowerid = ($outer).celltowerid ORDER BY lastupdate DESC NULLS LAST LIMIT 1) $alias ON true";
        },
        $q
    );

    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*\(\s*\d+\s*\)\s*,\s*([^,\)]+)\s*,\s*106\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . "::timestamp, 'DD Mon YYYY')",
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*,\s*([^,]+)\s*,\s*106\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . "::timestamp, 'DD Mon YYYY')",
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*\(\s*\d+\s*\)\s*,\s*([^,\)]+)\s*\)/i',
        static fn($m) => '(' . trim($m[1]) . ')::text',
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*,\s*([^,]+)\s*,\s*20\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . ", 'YYYY-MM-DD HH24:MI:SS')",
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*DATE\s*,\s*([^)]+)\)/i',
        static fn($m) => '(' . trim($m[1]) . ')::date',
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*TIME\s*,\s*([^)]+)\)/i',
        static fn($m) => '(' . trim($m[1]) . ')::time',
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*,\s*([^,]+)\s*,\s*(\d+)\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . ", 'YYYY-MM-DD HH24:MI:SS')",
        $q
    );

    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*CHAR\s*\(\s*10\s*\)\s*,\s*([^,]+)\s*,\s*121\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . ", 'YYYY-MM-DD')",
        $q
    );

    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*DATETIME\s*,\s*([^)]+)\)/i',
        static fn($m) => '(' . trim($m[1]) . ')',
        $q
    );

    $q = preg_replace_callback(
        '/\bdatepart\s*\(\s*(\w+)\s*,\s*([^)]+)\)/i',
        function($m) {
            $unit = strtolower(trim($m[1]));
            $expr = trim($m[2]);
            $unit_map = [
                'yy' => 'year', 'yyyy' => 'year', 'year' => 'year',
                'qq' => 'quarter', 'q' => 'quarter', 'quarter' => 'quarter',
                'mm' => 'month', 'm' => 'month', 'month' => 'month',
                'dy' => 'doy', 'y' => 'doy', 'dayofyear' => 'doy',
                'dd' => 'day', 'd' => 'day', 'day' => 'day',
                'wk' => 'week', 'ww' => 'week', 'week' => 'week',
                'dw' => 'dow', 'w' => 'dow', 'weekday' => 'dow',
                'hh' => 'hour', 'hour' => 'hour',
                'mi' => 'minute', 'n' => 'minute', 'minute' => 'minute',
                'ss' => 'second', 's' => 'second', 'second' => 'second',
                'ms' => 'millisecond', 'millisecond' => 'millisecond'
            ];
            $pg_unit = isset($unit_map[$unit]) ? $unit_map[$unit] : $unit;
            return "date_part('$pg_unit', ($expr)::timestamp)";
        },
        $q
    );

    $q = preg_replace_callback(
        '/\bdatediff\s*\(\s*(\w+)\s*,\s*([^,]+)\s*,\s*([^)]+)\)/i',
        function($m) {
            $unit = strtolower(trim($m[1]));
            $start = trim($m[2]);
            $end = trim($m[3]);
            if ($unit === 'ss' || $unit === 'second' || $unit === 's') {
                return "extract(epoch from (($end)::timestamp - ($start)::timestamp))";
            }
            if ($unit === 'mi' || $unit === 'minute' || $unit === 'n') {
                return "(extract(epoch from (($end)::timestamp - ($start)::timestamp)) / 60)";
            }
            if ($unit === 'hh' || $unit === 'hour') {
                return "(extract(epoch from (($end)::timestamp - ($start)::timestamp)) / 3600)";
            }
            if ($unit === 'dd' || $unit === 'day' || $unit === 'd') {
                return "(extract(epoch from (($end)::timestamp - ($start)::timestamp)) / 86400)";
            }
            return "(($end)::timestamp - ($start)::timestamp)";
        },
        $q
    );

    $q = preg_replace('/\bISNULL\s*\(/i', 'COALESCE(', $q);
    $q = preg_replace('/\bisnumeric\s*\(/i', 'isnumeric(', $q);
    $q = preg_replace('/\bISNUMERIC\s*\(\s*([^)]+)\)/i', "($1 ~ '^[0-9]+$')", $q);
    $q = preg_replace('/\bCHARINDEX\s*\(\s*\'([^\']*)\'\s*,\s*([^)]+)\)/i', "strpos($2, '$1')", $q);
    $q = preg_replace('/\bREVERSE\s*\(/i', 'reverse(', $q);
    $q = preg_replace('/\bCONVERT\s*\(\s*IMAGE\s*,\s*([^)]+)\)/i', '($1)::text', $q);
    $q = preg_replace('/\bLEN\s*\(/i', 'length(', $q);
    $q = preg_replace('/LTRIM\s*\(\s*RTRIM\s*\((.*?)\)\s*\)/i', 'trim($1)', $q);
    $q = preg_replace('/\bLTRIM\s*\(/i', 'ltrim(', $q);
    $q = preg_replace('/\bRTRIM\s*\(/i', 'rtrim(', $q);

    // Common CDAT_RTA column expressions (precomputed in cdat_rta view)
    $q = preg_replace('/FULLADDRESS\s*\+\s*\'\,\s*\'\s*\+\s*CITY/i', 'FULLADDRESS', $q);
    $q = preg_replace(
        '/MKR_CLAS\s*\+\s*\'\,\s*COLOR:\s*\'\s*\+\s*COLOUR\s*\+\s*\'\,\s*\'\s*\+\s*VEH_CLASS/i',
        'VEHICLE_TYPE',
        $q
    );

    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*\(\s*\d+\s*\)\s*,\s*([^,]+)\s*,\s*120\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . ", 'YYYY-MM-DD')",
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*\(\s*\d+\s*\)\s*,\s*([^,]+)\s*,\s*108\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . ", 'HH24:MI:SS')",
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*,\s*([^,]+)\s*,\s*120\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . ", 'YYYY-MM-DD HH24:MI:SS')",
        $q
    );

    $q = preg_replace_callback(
        '/\bOUTER\s+APPLY\s*\(\s*SELECT\s+TOP\s+1\s+(.*?)\s+FROM\s+(.*?)\s+WHERE\s+(.*?)\s*\)\s*([A-Za-z0-9_]+)/is',
        function($m) {
            $cols = trim($m[1]);
            $from = trim($m[2]);
            $where = trim($m[3]);
            $alias = trim($m[4]);
            return "LEFT JOIN LATERAL (SELECT $cols FROM $from WHERE $where LIMIT 1) $alias ON TRUE";
        },
        $q
    );

    $q = preg_replace('/\bOFFSET\s+(\S+)\s+ROWS\s+FETCH\s+NEXT\s+(\S+)\s+ROWS\s+ONLY/i', 'OFFSET $1 LIMIT $2', $q);

    // Replace MS SQL + string concatenation with PostgreSQL || operator
    // safely replacing only outside of string literals
    $parts = explode("'", $q);
    for ($i = 0; $i < count($parts); $i += 2) {
        $parts[$i] = str_replace('+', '||', $parts[$i]);
    }
    $q = implode("'", $parts);


    $q = preg_replace("/LIKE\s+'%'\s*\|\|\s*'([^']*)'/i", "LIKE '%' || '$1'", $q);
    $q = preg_replace("/LIKE\s*'\[7-9\]%'/i", "SIMILAR TO '[7-9]%'", $q);

    return $q;
}

function sqlsrv_query($conn, $query, array $params = [], array $options = [])
{
    $pdo = __sqlsrv_pdo($conn);
    if (!$pdo) {
        $GLOBALS['__sqlsrv_last_error'] = [['message' => 'Invalid connection', 'code' => 0]];
        return false;
    }
    try {
        $sql = __sqlsrv_translate($query);
        if (!empty($params)) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $pdo->query($sql);
        }
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return (object)['rows' => $rows, 'pos' => 0, 'sql' => $sql];
    } catch (Throwable $e) {
        $GLOBALS['__sqlsrv_last_error'] = [['message' => $e->getMessage(), 'code' => $e->getCode(), 'sql' => $query]];
        error_log('sqlsrv_compat: ' . $e->getMessage());
        return false;
    }
}

function sqlsrv_fetch_array($result, $fetchType = SQLSRV_FETCH_ASSOC)
{
    if (!is_object($result) || !isset($result->rows) || $result->pos >= count($result->rows)) {
        return false;
    }
    $row = $result->rows[$result->pos++];
    $row = array_change_key_case($row, CASE_UPPER);
    return $fetchType === SQLSRV_FETCH_ASSOC ? $row : array_values($row);
}

function sqlsrv_num_rows($result)
{
    return (is_object($result) && isset($result->rows)) ? count($result->rows) : 0;
}

function sqlsrv_has_rows($result)
{
    return (is_object($result) && isset($result->rows) && count($result->rows) > 0);
}

function sqlsrv_errors($errorsOrWarnings = null)
{
    return $GLOBALS['__sqlsrv_last_error'];
}

function sqlsrv_close($conn)
{
    unset($GLOBALS['__sqlsrv_connections'][$conn]);
    return true;
}

function sqlsrv_free_stmt($stmt)
{
    return true;
}

function sqlsrv_prepare($conn, $query, array $params = [], array $options = [])
{
    return sqlsrv_query($conn, $query, $params, $options);
}

function sqlsrv_execute($stmt)
{
    return true;
}
