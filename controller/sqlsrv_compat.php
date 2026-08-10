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
        'cdatdupl' => 'postgress_db',
        'cdat' => 'postgress_db',
        'postgres' => 'postgress_db',
        'twrmdb' => 'postgress_db',
        'irforms' => 'postgress_db',
        'forms' => 'postgress_db',
        'jrms' => 'postgress_db',
        'pdact' => 'postgress_db',
        'lostreport_hawkeye' => 'postgress_db',
        'migrant_labours_form' => 'postgress_db',
        'training_db' => 'postgress_db',
        'cpms' => 'postgress_db',
        'cafs' => 'postgress_db',
        'cis_data_base' => 'postgress_db',
        'cdat_import' => 'postgress_db',
        'testing_db' => 'postgress_db',
        'rough' => 'postgress_db',
        'distributed_db' => 'postgress_db',
    ];
    return $map[$name] ?? 'postgress_db';
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

    // Date-range filters: convert(char(10), starttime, 121) BETWEEN ... → index-friendly ::date compare
    $q = preg_replace_callback(
        '/convert\s*\(\s*char\s*\(\s*10\s*\)\s*,\s*([^,]+)\s*,\s*121\s*\)\s+between\s*\'([^\']+)\'\s+and\s*\'([^\']+)\'/i',
        static function ($m) {
            $col = trim($m[1]);
            $from = str_replace("'", "''", trim($m[2]));
            $to = str_replace("'", "''", trim($m[3]));
            return "({$col})::date BETWEEN '{$from}'::date AND '{$to}'::date";
        },
        $q
    );

    // FDW views over Citus must filter by phone inside a LATERAL subquery; otherwise
    // postgres_fdw pulls the full remote table. Handles joins on phone OR other columns,
    // with or without an explicit eff_to_date IS NULL predicate (we always apply it).
    $q = preg_replace_callback(
        '/LEFT\s+JOIN\s+(cdataddress|address_other_state)\s+([A-Za-z_][A-Za-z0-9_]*)\s+ON\s+([A-Za-z_][A-Za-z0-9_]*)\.(\w+)\s*=\s*\2\.PHONE(?:\s+AND\s+\2\.EFF_TO_DATE\s+IS\s+NULL)?/i',
        static function ($m) {
            $table = strtolower($m[1]);
            $alias = $m[2];
            $outer = $m[3];
            $outerCol = $m[4];
            return "LEFT JOIN LATERAL (SELECT * FROM $table WHERE phone = ($outer).$outerCol AND eff_to_date IS NULL LIMIT 1) $alias ON true";
        },
        $q
    );

    // Legacy form: ON outer.PHONE = alias.PHONE AND alias.EFF_TO_DATE IS NULL
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

    $towerLateral = static function (string $alias, string $outer, bool $useKeys): string {
        $keyFilter = $useKeys
            ? " AND (COALESCE(($outer).state_key::text, '') = '' OR (state_key IS NOT DISTINCT FROM ($outer).state_key AND provider_key IS NOT DISTINCT FROM ($outer).provider_key))"
            : '';
        $shortId = "regexp_replace(($outer).celltowerid::text, '^[^-]+-[^-]+-', '')";
        return "LEFT JOIN LATERAL (SELECT * FROM cdatcelltowerareanew WHERE (celltowerid = {$shortId} OR celltowerid = ($outer).celltowerid OR bts_id = ($outer).celltowerid){$keyFilter} ORDER BY lastupdate DESC NULLS LAST LIMIT 1) $alias ON true";
    };

    $q = preg_replace_callback(
        '/INNER\s+JOIN\s+cdatcelltowerareanew\s+([A-Za-z_][A-Za-z0-9_]*)\s+ON\s+([A-Za-z_][A-Za-z0-9_]*)\.CELLTOWERID\s*=\s*\1\.CELLTOWERID(?:\s+AND\s+\2\.STATE_KEY\s*=\s*\1\.STATE_KEY\s+AND\s+\2\.PROVIDER_KEY\s*=\s*\1\.PROVIDER_KEY)?/i',
        static function ($m) use ($towerLateral) {
            return $towerLateral($m[1], $m[2], stripos($m[0], 'state_key') !== false);
        },
        $q
    );

    $q = preg_replace_callback(
        '/LEFT\s+JOIN\s+cdatcelltowerareanew\s+([A-Za-z_][A-Za-z0-9_]*)\s+ON\s+([A-Za-z_][A-Za-z0-9_]*)\.CELLTOWERID\s*=\s*\1\.CELLTOWERID(?:\s+AND\s+\2\.STATE_KEY\s*=\s*\1\.STATE_KEY\s+AND\s+\2\.PROVIDER_KEY\s*=\s*\1\.PROVIDER_KEY)?/i',
        static function ($m) use ($towerLateral) {
            return $towerLateral($m[1], $m[2], stripos($m[0], 'state_key') !== false);
        },
        $q
    );

    $q = preg_replace('/\bWHERE\s+OPERATOR\s*=\s*\'\'\s+AND\s+B\.STATE\s*=\s*\'\'/i', '', $q);
    $q = preg_replace('/\bWHERE\s+OPERATOR\s*=\s*\'\'\s+AND\s+B\.STATE\s*=\s*\'\'\s+and\b/i', 'WHERE ', $q);
    $q = preg_replace('/\bAND\s+OPERATOR\s*=\s*\'\'\s*/i', '', $q);
    $q = preg_replace('/\bWHERE\s+OPERATOR\s*=\s*\'\'\s*/i', '', $q);
    $q = preg_replace('/\bAND\s+B\.STATE\s*=\s*\'\'\s*/i', '', $q);
    $q = preg_replace('/\bWHERE\s+B\.STATE\s*=\s*\'\'\s*/i', '', $q);
    $q = preg_replace(
        '/\band\s+b\.lastupdate\s*=\s*\(\s*select\s+distinct\s+max\s*\(\s*lastupdate\s*\)\s+from\s+cdatcelltowerareanew\s+x(?:\s+with\s*\(\s*nolock\s*\))?\s+where\s+x\.celltowerid\s*=\s*b\.celltowerid[^)]+\)/i',
        '',
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
        static fn($m) => "to_char((" . trim($m[1]) . ")::timestamp, 'YYYY-MM-DD HH24:MI:SS')",
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
        static fn($m) => "to_char((" . trim($m[1]) . ")::timestamp, 'YYYY-MM-DD HH24:MI:SS')",
        $q
    );

    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*CHAR\s*\(\s*8\s*\)\s*,\s*([^,]+)\s*,\s*108\s*\)/i',
        static fn($m) => '(' . trim($m[1]) . ')::time',
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*(?:CHAR|VARCHAR)\s*\(\s*10\s*\)\s*,\s*([^,]+)\s*,\s*121\s*\)/i',
        static fn($m) => "to_char((" . trim($m[1]) . ")::timestamp, 'YYYY-MM-DD')",
        $q
    );

    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*(?:CHAR|VARCHAR)\s*\(\s*10\s*\)\s*,\s*([^,]+)\s*,\s*105\s*\)/i',
        static fn($m) => "to_char((" . trim($m[1]) . ")::timestamp, 'DD-MM-YYYY')",
        $q
    );

    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*DATETIME\s*,\s*([^)]+)\)/i',
        static fn($m) => '(' . trim($m[1]) . ')',
        $q
    );

    $q = preg_replace('/\bAS\s+DATETIME\b/i', 'AS timestamp', $q);

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
    $q = preg_replace('/\bisnumeric\s*\(/i', 'ISNUMERIC(', $q);
    $q = preg_replace('/\bISNUMERIC\s*\(\s*([^)]+)\)\s*=\s*0/i', "NOT ($1 ~ '^[0-9]+$')", $q);
    $q = preg_replace('/\bISNUMERIC\s*\(\s*([^)]+)\)\s*=\s*1/i', "($1 ~ '^[0-9]+$')", $q);
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
        static fn($m) => "to_char((" . trim($m[1]) . ")::timestamp, 'YYYY-MM-DD')",
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*\(\s*\d+\s*\)\s*,\s*([^,]+)\s*,\s*108\s*\)/i',
        static fn($m) => "to_char(" . trim($m[1]) . ", 'HH24:MI:SS')",
        $q
    );
    $q = preg_replace_callback(
        '/\bCONVERT\s*\(\s*VARCHAR\s*,\s*([^,]+)\s*,\s*120\s*\)/i',
        static fn($m) => "to_char((" . trim($m[1]) . ")::timestamp, 'YYYY-MM-DD HH24:MI:SS')",
        $q
    );

    // calculatedistance/getbearing are declared double precision, but lat/long on
    // cdatcelltowerareanew are varchar and MSSQL passed bare ints. Postgres will not
    // resolve those implicitly, so cast every argument. NULLIF keeps blank cells from
    // raising an invalid-input error mid-scan.
    $geoArgs = static function (array $m): string {
        $cast = static fn(string $a): string => "NULLIF((" . trim($a) . ")::text, '')::double precision";
        return $cast($m[1]) . ', ' . $cast($m[2]) . ', ' . $cast($m[3]) . ', ' . $cast($m[4]);
    };
    $q = preg_replace_callback(
        '/\bDBO\.CALCULATEDISTANCE\s*\(\s*([^,]+)\s*,\s*([^,]+)\s*,\s*([^,]+)\s*,\s*([^)]+)\)/i',
        static fn($m) => 'calculatedistance(' . $geoArgs($m) . ')',
        $q
    );
    $q = preg_replace_callback(
        '/\bDBO\.GETBEARING\s*\(\s*([^,]+)\s*,\s*([^,]+)\s*,\s*([^,]+)\s*,\s*([^)]+)\)/i',
        static fn($m) => 'getbearing(' . $geoArgs($m) . ')',
        $q
    );
    $q = preg_replace('/\bISNUMERIC\s*\(\s*([^)]+)\s*\)/i', "($1 ~ '^-?[0-9]+(\\.[0-9]+)?$')", $q);
    $q = preg_replace('/\bdbo\.celltowerfiltered\b/i', 'celltowerfiltered', $q);
    $q = preg_replace('/\bdbo\.CELLTOWERfiltered\b/i', 'celltowerfiltered', $q);

    if (preg_match_all('/\bset\s+@([a-z_][a-z0-9_]*)\s*=\s*\'([^\']*)\'/i', $q, $vars, PREG_SET_ORDER)) {
        $replacements = [];
        foreach ($vars as $var) {
            $replacements['@' . strtolower($var[1])] = $var[2];
        }
        // These blocks are usually NOT semicolon-terminated in this codebase, e.g.
        //   declare @lat decimal(14,10),@long decimal (14,10)\n set @lat = '...'
        // Requiring ';' left the DECLARE in place and Postgres received
        // "declare 0 decimal(14,10)". Stop at ';' or end of line, whichever is first.
        $q = preg_replace('/\bdeclare\s+@[^;\r\n]*;?/i', '', $q);
        $q = preg_replace('/\bset\s+@[^;\r\n]*;?/i', '', $q);
        foreach ($replacements as $name => $value) {
            $safe = is_numeric($value) ? $value : "'" . str_replace("'", "''", $value) . "'";
            $q = preg_replace('/' . preg_quote($name, '/') . '\b/i', $safe, $q);
        }
    }

    $q = preg_replace_callback(
        '/\bOUTER\s+APPLY\s*\(\s*SELECT\s+TOP\s+1\s+(.*?)\s+FROM\s+(.*?)\s+WHERE\s+(.*?)\s*\)\s*([A-Za-z0-9_]+)/is',
        function ($m) {
            $cols = trim($m[1]);
            $from = trim($m[2]);
            $where = trim($m[3]);
            $alias = trim($m[4]);
            if (stripos($from, 'cdatcelltowerareanew') !== false) {
                if (stripos($where, 'bts_id') !== false) {
                    $where = "T.celltowerid = regexp_replace(A.CELLTOWERID::text, '^[^-]+-[^-]+-', '') OR T.celltowerid = A.CELLTOWERID";
                } elseif (stripos($where, 'celltowerid') !== false) {
                    $where = preg_replace(
                        '/([A-Za-z0-9_]+)\.CELLTOWERID\s*=\s*A\.CELLTOWERID/i',
                        '(\\1.celltowerid = regexp_replace(A.CELLTOWERID::text, \'^[^-]+-[^-]+-\', \'\') OR \\1.celltowerid = A.CELLTOWERID)',
                        $where
                    );
                }
                return "LEFT JOIN LATERAL (SELECT $cols FROM $from WHERE $where ORDER BY lastupdate DESC NULLS LAST LIMIT 1) $alias ON TRUE";
            }
            return "LEFT JOIN LATERAL (SELECT $cols FROM $from WHERE $where LIMIT 1) $alias ON TRUE";
        },
        $q
    );

    $q = preg_replace('/\bOFFSET\s+(\S+)\s+ROWS\s+FETCH\s+NEXT\s+(\S+)\s+ROWS\s+ONLY/i', 'OFFSET $1 LIMIT $2', $q);

    $q = preg_replace_callback(
        '/\bSELECT\s+TOP\s+(\d+)\s+(.*?)\s+ORDER\s+BY\s+(.*)$/is',
        static fn($m) => 'SELECT ' . trim($m[2]) . ' ORDER BY ' . trim($m[3]) . ' LIMIT ' . trim($m[1]),
        $q
    );
    $q = preg_replace_callback(
        '/\bSELECT\s+TOP\s+(\d+)\s+(.*)$/is',
        static fn($m) => 'SELECT ' . trim($m[2]) . ' LIMIT ' . trim($m[1]),
        $q
    );

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

/**
 * Render a visible error banner when sqlsrv_query() returns false.
 * Call after each query on legacy report pages for diagnostics.
 */
function sqlsrv_render_query_error($stmt, string $context = ''): void
{
    if ($stmt !== false) {
        return;
    }
    $errs = sqlsrv_errors();
    $msg = $errs[0]['message'] ?? 'Unknown database error';
    $label = $context !== '' ? htmlspecialchars($context) : 'Query';
    echo '<div style="background:#5c1a1a;color:#ffb3b3;padding:12px;margin:10px 0;border:1px solid #dc3545;border-radius:4px;font-family:Verdana,sans-serif;font-size:12px;">';
    echo '<strong>' . $label . ' failed:</strong> ' . htmlspecialchars($msg);
    echo '</div>';
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

/**
 * Build an <img src> value for base64 image columns from SQL Server/PostgreSQL.
 * Returns a data URI when image data exists, otherwise a static placeholder.
 */
function cdat_base64_image_src($data, string $fallback = '../assets/images/emp.png'): string
{
    if ($data === null) {
        return $fallback;
    }

    $data = trim((string) $data);
    if ($data === '') {
        return $fallback;
    }

    if (stripos($data, 'data:image') === 0) {
        $fixed = preg_replace('/^data:image;\s*base64,/i', 'data:image/jpeg;base64,', $data, 1);
        return $fixed !== '' ? $fixed : $fallback;
    }

    if (preg_match('/^data:image\/[^;]+;base64,(.+)$/is', $data, $matches)) {
        $data = $matches[1];
    }

    $data = preg_replace('/\s+/', '', $data);
    if ($data === '') {
        return $fallback;
    }

    $mime = 'image/jpeg';
    if (str_starts_with($data, 'iVBORw0KGgo')) {
        $mime = 'image/png';
    } elseif (str_starts_with($data, 'R0lGOD')) {
        $mime = 'image/gif';
    }

    return 'data:' . $mime . ';base64,' . $data;
}
