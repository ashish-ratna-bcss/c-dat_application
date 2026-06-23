<!DOCTYPE html>
<html>
<head>
<title>MOVEMENTS REPORT</title>

<style>

body{
    background:#0C5D90;
    font-family:Verdana;
    margin:0;
    padding:10px;
}

a{
    text-decoration:none;
}

table{
    border-collapse:collapse;
    width:100%;
    table-layout:auto;
    background:white;
}

th{
    background:#921215;
    color:#F9FBFC;
    font-size:12px;
    position:sticky;
    top:0;
    z-index:2;
    padding:6px;
}

td{
    font-size:11px;
    padding:5px;
    text-align:center;
    border:1px solid #000;
    white-space:nowrap;
}

th,td{
    border:1px solid #000;
}

.container{
    overflow:auto;
    height:650px;
    background:white;
}

.heading{
    color:#F9FBFC;
    text-align:center;
    margin-bottom:10px;
}

.back{
    margin-bottom:10px;
}

.filter-box{
    width:95%;
    font-size:11px;
    padding:3px;
}

.area-col{
    min-width:450px;
    max-width:700px;
    white-space:normal;
    word-wrap:break-word;
}

.small-col{
    min-width:100px;
}

.medium-col{
    min-width:150px;
}

.large-col{
    min-width:220px;
}

</style>

<script>

function filterTable(col)
{
    var input, filter, table, tr, td, i, txtValue;

    input = document.getElementById("filter"+col);

    filter = input.value.toUpperCase();

    table = document.getElementById("cdrTable");

    tr = table.getElementsByTagName("tr");

    for(i=2;i<tr.length;i++)
    {
        td = tr[i].getElementsByTagName("td")[col];

        if(td)
        {
            txtValue = td.textContent || td.innerText;

            if(txtValue.toUpperCase().indexOf(filter) > -1)
            {
                tr[i].style.display = "";
            }
            else
            {
                tr[i].style.display = "none";
            }
        }
    }
}

</script>

</head>

<body>

<div class="back">
<a href="MOVEMENTS.HTML">
<font color="white"><b>Back</b></font>
</a>
</div>

<?php

set_time_limit(0);
require_once __DIR__ . '/activity_logger.php';

/*
==================================================
SQL SERVER CONNECTION
==================================================
*/

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";

$connectionInfo = array(
    "Database"=>"CDATDUPL"
);

$conn = sqlsrv_connect($serverName,$connectionInfo);

if($conn === false)
{
    die(print_r(sqlsrv_errors(),true));
}

/*
==================================================
GET MOBILE NUMBER
==================================================
*/

$number='';

if(isset($_POST['PHONE_NO']))
{
    $number = trim($_POST['PHONE_NO']);
    audit_log('Movements / Call Details', 'Search', ['phone_number' => $number]);
}

if($number=='')
{
    die("<center><font color='white'><h3>Phone Number Missing</h3></font></center>");
}

/*
==================================================
PAGINATION
==================================================
*/

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page <= 0)
{
    $page = 1;
}

$limit = 100000;

$offset = ($page - 1) * $limit;

/*
==================================================
MAIN FAST QUERY
==================================================
*/

$sql = "

SELECT DISTINCT

    A.PHONE,

    A.OTHER,

    ISNULL(C.NICKNAME,'') AS NICKNAME,

    CONVERT(VARCHAR(10),A.STARTTIME,120) AS DATE1,

    CONVERT(VARCHAR(8),A.STARTTIME,108) AS TIME1,

    CONVERT(VARCHAR,A.STARTTIME,120) AS STARTTIME,

    A.DURATION,

    CASE
        WHEN A.INCOMING='1' THEN 'IN'
        ELSE 'OUT'
    END AS TYPE,

    A.IMEINUMBER,

    A.CELLTOWERID,

    ISNULL(COALESCE(B1.OPERATOR,B2.OPERATOR),'') AS OPERATOR,

    ISNULL(COALESCE(B1.STATE,B2.STATE),'') AS STATE,

    ISNULL(COALESCE(B1.SITEADDRESS,B2.SITEADDRESS),'') AS AREADESCRIPTION,

    ISNULL(COALESCE(B1.LAT,B2.LAT),'') AS LAT,

    ISNULL(COALESCE(B1.LONG,B2.LONG),'') AS LONG,

    ISNULL(COALESCE(B1.AZIMUTH,B2.AZIMUTH),'') AS AZM

FROM CDATDUPL.dbo.CDATPCSUSPECT A WITH (NOLOCK)

/*
==================================================
FIRST MATCH CELLTOWERID
==================================================
*/

OUTER APPLY
(
    SELECT TOP 1 *
    FROM CDATDUPL.dbo.CDATCELLTOWERAREANEW X WITH (NOLOCK)
    WHERE
        X.CELLTOWERID = A.CELLTOWERID
        AND X.STATE_KEY = A.STATE_KEY
        AND X.PROVIDER_KEY = A.PROVIDER_KEY
) B1

/*
==================================================
IF NOT FOUND MATCH BTS_ID
==================================================
*/

OUTER APPLY
(
    SELECT TOP 1 *
    FROM CDATDUPL.dbo.CDATCELLTOWERAREANEW Y WITH (NOLOCK)
    WHERE
        B1.CELLTOWERID IS NULL
        AND Y.BTS_ID = A.CELLTOWERID
        AND Y.STATE_KEY = A.STATE_KEY
        AND Y.PROVIDER_KEY = A.PROVIDER_KEY
) B2

/*
==================================================
NICKNAME TABLE
==================================================
*/

LEFT JOIN CDATDUPL.dbo.CDATSUSPECT C WITH (NOLOCK)

ON A.OTHER = C.PHONE

WHERE A.PHONE = ?

ORDER BY STARTTIME ASC

OFFSET ? ROWS
FETCH NEXT ? ROWS ONLY

";

$params = array($number,$offset,$limit);

$options = array(
    "Scrollable" => SQLSRV_CURSOR_KEYSET
);

$st = sqlsrv_query($conn,$sql,$params,$options);

if($st === false)
{
    die(print_r(sqlsrv_errors(),true));
}

/*
==================================================
TOTAL RECORDS
==================================================
*/

$count_sql = "

SELECT COUNT(*) AS TOTAL

FROM CDATDUPL.dbo.CDATPCSUSPECT WITH (NOLOCK)

WHERE PHONE = ?

";

$count_stmt = sqlsrv_query($conn,$count_sql,array($number));

$count_row = sqlsrv_fetch_array($count_stmt,SQLSRV_FETCH_ASSOC);

$total_records = $count_row['TOTAL'];

$total_pages = ceil($total_records / $limit);

/*
==================================================
HEADING
==================================================
*/

echo "

<div class='heading'>

<h2>CALL DETAILS OF MOBILE NO : $number</h2>

<h3>Total Records : $total_records</h3>

</div>

";

/*
==================================================
TABLE
==================================================
*/

echo "

<div class='container'>

<table id='cdrTable'>

<tr>

<th>PHONE</th>
<th>OTHER</th>
<th>NICKNAME</th>
<th>DATE</th>
<th>TIME</th>
<th>STARTTIME</th>
<th>DURATION</th>
<th>TYPE</th>
<th>IMEI</th>
<th>CELLID</th>
<th>OPERATOR</th>
<th>STATE</th>
<th class='area-col'>AREA DESCRIPTION</th>
<th>LAT</th>
<th>LONG</th>
<th>AZM</th>

</tr>

<tr>

<th><input type='text' id='filter0' class='filter-box' onkeyup='filterTable(0)'></th>
<th><input type='text' id='filter1' class='filter-box' onkeyup='filterTable(1)'></th>
<th><input type='text' id='filter2' class='filter-box' onkeyup='filterTable(2)'></th>
<th><input type='text' id='filter3' class='filter-box' onkeyup='filterTable(3)'></th>
<th><input type='text' id='filter4' class='filter-box' onkeyup='filterTable(4)'></th>
<th><input type='text' id='filter5' class='filter-box' onkeyup='filterTable(5)'></th>
<th><input type='text' id='filter6' class='filter-box' onkeyup='filterTable(6)'></th>
<th><input type='text' id='filter7' class='filter-box' onkeyup='filterTable(7)'></th>
<th><input type='text' id='filter8' class='filter-box' onkeyup='filterTable(8)'></th>
<th><input type='text' id='filter9' class='filter-box' onkeyup='filterTable(9)'></th>
<th><input type='text' id='filter10' class='filter-box' onkeyup='filterTable(10)'></th>
<th><input type='text' id='filter11' class='filter-box' onkeyup='filterTable(11)'></th>
<th><input type='text' id='filter12' class='filter-box' onkeyup='filterTable(12)'></th>
<th><input type='text' id='filter13' class='filter-box' onkeyup='filterTable(13)'></th>
<th><input type='text' id='filter14' class='filter-box' onkeyup='filterTable(14)'></th>
<th><input type='text' id='filter15' class='filter-box' onkeyup='filterTable(15)'></th>

</tr>

";

/*
==================================================
FETCH DATA
==================================================
*/

while($row = sqlsrv_fetch_array($st,SQLSRV_FETCH_ASSOC))
{
    echo "<tr>";

    echo "<td bgcolor='#AED1F1'>".$row['PHONE']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['OTHER']."</td>";
    echo "<td bgcolor='#AED1F1'>".$row['NICKNAME']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['DATE1']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['TIME1']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['STARTTIME']."</td>";
    echo "<td bgcolor='#AED1F1'>".$row['DURATION']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['TYPE']."</td>";
    echo "<td bgcolor='#AED1F1'>".$row['IMEINUMBER']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['CELLTOWERID']."</td>";
    echo "<td bgcolor='#AED1F1'>".$row['OPERATOR']."</td>";
    echo "<td bgcolor='#AED1F1'>".$row['STATE']."</td>";
    echo "<td bgcolor='#C2E0FB' class='area-col'>".$row['AREADESCRIPTION']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['LAT']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['LONG']."</td>";
    echo "<td bgcolor='#C2E0FB'>".$row['AZM']."</td>";

    echo "</tr>";
}

echo "</table>";

echo "</div>";

sqlsrv_free_stmt($st);

sqlsrv_close($conn);

?>

</body>
</html>