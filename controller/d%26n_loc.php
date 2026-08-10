<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("D & N Loc");
?>
<li><a href="../view/day%26nightloc.html"><font color=#FDEFEF>Back</a></li>
<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';
require_once __DIR__ . '/sql_safe.php';

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
if ($number === '') {
    die('<font color=#F9FBFC>Invalid mobile number.</font>');
}
$numberSql = cdr_escape_sql_literal($number);

function dn_top_towers($conn, string $numberSql, string $timePredicate): array
{
    $sql = "SELECT TOP 10
                PHONE,
                CELLTOWERID,
                COUNT(CELLTOWERID) AS CALLS
            FROM CDATDUPL.DBO.CDATPCSUSPECT
            WHERE PHONE = '{$numberSql}'
              AND ({$timePredicate})
            GROUP BY PHONE, CELLTOWERID
            ORDER BY CALLS DESC";

    $st = sqlsrv_query($conn, $sql);
    if ($st === false) {
        return [];
    }

    $rows = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

function dn_render_table(array $rows, array $towerMap): void
{
    echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZM</font></th>
</tr>";

    foreach ($rows as $row) {
        $cid = $row['CELLTOWERID'] ?? '';
        $tower = $towerMap[$cid] ?? [
            'areadescription' => '',
            'lat' => '',
            'long' => '',
            'azimuth' => '',
        ];
        echo "<tr>";
        echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>" . htmlspecialchars((string)($row['PHONE'] ?? ''), ENT_QUOTES) . "<center></td>";
        echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>" . htmlspecialchars((string)$cid, ENT_QUOTES) . "</td>";
        echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>" . htmlspecialchars((string)($row['CALLS'] ?? ''), ENT_QUOTES) . "<center></td>";
        echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>" . htmlspecialchars((string)$tower['areadescription'], ENT_QUOTES) . "</td>";
        echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>" . htmlspecialchars((string)$tower['lat'], ENT_QUOTES) . "<center></td>";
        echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>" . htmlspecialchars((string)$tower['long'], ENT_QUOTES) . "<center></td>";
        echo "<td width=15px bgcolor=#AED1F1><font size=1 face=verdana><center>" . htmlspecialchars((string)$tower['azimuth'], ENT_QUOTES) . "<center></font></td>";
        echo "</tr>";
    }
    echo "</table></br>";
}

$dayPred = "CONVERT(CHAR(8),STARTTIME,108)<'22:00:00' AND CONVERT(CHAR(8),STARTTIME,108)>'05:00:00'";
$nightPred = "CONVERT(CHAR(8),STARTTIME,108)>'22:00:00' OR CONVERT(CHAR(8),STARTTIME,108)<'07:00:00'";

$dayRows = dn_top_towers($conn, $numberSql, $dayPred);
$nightRows = dn_top_towers($conn, $numberSql, $nightPred);

$towerIds = array_merge(
    array_column($dayRows, 'CELLTOWERID'),
    array_column($nightRows, 'CELLTOWERID')
);
$towerMap = cdat_fetch_tower_map($conn, $towerIds);

echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>DAY LOCATION OF MOBILE NO: "
    . htmlspecialchars($number, ENT_QUOTES)
    . "<center></td></font></br>";
dn_render_table($dayRows, $towerMap);

echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>NIGHT LOCATION OF MOBILE NO: "
    . htmlspecialchars($number, ENT_QUOTES)
    . "<center></td></font></br>";
dn_render_table($nightRows, $towerMap);
?>
<?php layout_end(); ?>
