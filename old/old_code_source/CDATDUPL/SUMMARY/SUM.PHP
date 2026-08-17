<?php

/* =========================================================
   DATABASE CONNECTION
========================================================= */

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";

$connectionInfo = array(
    "Database" => "ADDRESS_OTHER_LOCAL"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

/* =========================================================
   INPUT VALIDATION
========================================================= */

$number = trim($_POST['PHONE_NO']);

if (!preg_match('/^[0-9]{10,15}$/', $number)) {
    die("INVALID MOBILE NUMBER");
}

/* =========================================================
   QUERY
========================================================= */

$sql = "

SET NOCOUNT ON;

;WITH TEMP_TT AS
(
    SELECT
        PHONE,
        LTRIM(RTRIM(OTHER)) AS OTHER,
        INCOMING,
        TRY_CAST(DURATION AS BIGINT) AS DURATION,
        STARTTIME

    FROM CDATDUPL.dbo.CDATPCSUSPECT WITH (NOLOCK)

    WHERE PHONE = ?
      AND OTHER IS NOT NULL
      AND OTHER <> ''
      AND OTHER NOT LIKE '%[A-Z]%'
      AND OTHER NOT LIKE '%[a-z]%'
),

TEMP_RESULT AS
(
    SELECT
        PHONE,
        OTHER,

        SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS [IN],
        SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS [OUT],

        COUNT(*) AS CALLS,

        SUM(ISNULL(DURATION,0)) AS DUR,

        MIN(STARTTIME) AS FIRSTCALL,
        MAX(STARTTIME) AS LASTCALL

    FROM TEMP_TT

    GROUP BY PHONE, OTHER
)

SELECT

    A.PHONE,
    A.OTHER,
    A.[IN],
    A.[OUT],
    A.CALLS,
    A.DUR,

    CONVERT(VARCHAR,A.FIRSTCALL,20) AS FIRSTCALL,
    CONVERT(VARCHAR,A.LASTCALL,20) AS LASTCALL,

    CASE
        WHEN A.OTHER LIKE '140%' THEN 'TELEMARKETING NUMBER'
        WHEN A.OTHER LIKE '1800%' THEN 'TOLL FREE NUMBER'
        WHEN A.OTHER IN ('100','101','108','112','121','198','199')
             THEN 'CUSTOMER CARE NUMBER'
        WHEN LEN(A.OTHER) < 10
             THEN 'POSSIBLE VOIP / WIFI CALL'

        ELSE
        LTRIM(RTRIM(
            ISNULL(ISNULL(B.FULLNAME,C.FULLNAME),'')
            +
            CASE
                WHEN ISNULL(ISNULL(B.FULLADDRESS,C.FULLADDRESS),'')=''
                THEN ''
                ELSE ' , ' + ISNULL(ISNULL(B.FULLADDRESS,C.FULLADDRESS),'')
            END
        ))
    END AS ADDRESS

FROM TEMP_RESULT A

LEFT JOIN ADDRESS_OTHER_STATE B WITH (NOLOCK)
       ON A.OTHER = B.PHONE
      AND B.EFF_TO_DATE IS NULL

LEFT JOIN CDATADDRESS C WITH (NOLOCK)
       ON A.OTHER = C.PHONE
      AND C.EFF_TO_DATE IS NULL

ORDER BY A.FIRSTCALL ASC

";

$params = array($number);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

?>

<!DOCTYPE html>
<html>
<head>

<title>CDR ANALYSIS SUMMARY</title>

<style>

body{
    background:#0C5D90;
    font-family:Verdana, Arial, sans-serif;
    margin:0;
    padding:0;
    width:100%;
    overflow-x:auto;
}

a{
    text-decoration:none;
}

.container{
    width:100%;
}

.backbtn{
    margin-bottom:10px;
}

.backbtn a{
    display:inline-block;
    background:#921215;
    color:#FFFFFF;
    padding:8px 15px;
    border-radius:5px;
    font-size:14px;
    font-weight:bold;
    text-decoration:none;
}

.backbtn a:hover{
    background:#6f0f11;

}

.topbar{
    background:#F9FBFC;
    color:#921215;
    text-align:center;
    font-size:22px;
    font-weight:bold;
    padding:10px;
    margin-bottom:10px;
    border-radius:5px;
    border:2px solid #921215;
}

.searchbox{
    background:#F9FBFC;
    color:#0C5D90;
    text-align:center;
    font-size:16px;
    font-weight:bold;
    padding:8px;
    margin-bottom:10px;
    border-radius:5px;
    border:2px solid #921215;
}

.table-wrapper{
    overflow:auto;
    height:650px;
    background:#FFFFFF;
}

table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    background:#FFFFFF;
}

th{
    background:#921215;
    color:#F9FBFC;
    border:1px solid #000;
    padding:6px;
    font-size:12px;
    text-align:center;
    vertical-align:middle;
    position:sticky;
    top:0;
    z-index:10;
}

td{
    border:1px solid #000;
    padding:5px;
    font-size:11px;
    text-align:center;
    vertical-align:middle;
    word-wrap:break-word;
    word-break:break-word;
}

tr:nth-child(even){
    background:#C2E0FB;
}

tr:nth-child(odd){
    background:#AED1F1;
}

.calls{
    font-weight:bold;
}

.address{
    text-align:center;
    vertical-align:middle;
    white-space:normal;
    word-break:break-word;
}

.nodata{
    color:red;
    font-size:18px;
    font-weight:bold;
    text-align:center;
    padding:20px;
}

</style>

</head>

<body>

<div class="container">

<div class="backbtn">
    <a href="HOME.html">&#8962; HOME</a>
</div>

<div class="topbar">
CDR ANALYSIS SUMMARY
</div>

<div class="searchbox">
SUMMARY OF MOBILE NO : <?php echo htmlspecialchars($number); ?>
</div>

<div class="table-wrapper">

<table>

<colgroup>
    <col style="width:8%;">
    <col style="width:10%;">
    <col style="width:4%;">
    <col style="width:4%;">
    <col style="width:5%;">
    <col style="width:7%;">
    <col style="width:10%;">
    <col style="width:10%;">
    <col style="width:42%;">
</colgroup>

<tr>
    <th>PHONE</th>
    <th>OTHER PARTY</th>
    <th>IN</th>
    <th>OUT</th>
    <th>CALLS</th>
    <th>DURATION</th>
    <th>FIRST CALL</th>
    <th>LAST CALL</th>
    <th>ADDRESS</th>
</tr>

<?php

if(sqlsrv_has_rows($stmt)==false)
{
    echo "
    <tr>
        <td colspan='9' class='nodata'>
            *** CDRs NOT AVAILABLE ***
        </td>
    </tr>";
}
else
{
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
    {
        echo "<tr>";

        echo "<td class='center'>".htmlspecialchars($row['PHONE'])."</td>";

        echo "<td class='center'>".htmlspecialchars($row['OTHER'])."</td>";

        echo "<td class='center'>".$row['IN']."</td>";

        echo "<td class='center'>".$row['OUT']."</td>";

        echo "<td class='center calls'>".$row['CALLS']."</td>";

        echo "<td class='center'>".$row['DUR']."</td>";

        echo "<td class='center'>".$row['FIRSTCALL']."</td>";

        echo "<td class='center'>".$row['LASTCALL']."</td>";

        echo "<td class='address'>".htmlspecialchars($row['ADDRESS'])."</td>";

        echo "</tr>";
    }
}

?>

</table>

</div>

</div>

</body>
</html>

<?php

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

?>