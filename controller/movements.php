<?php
// One page for both halves of this screen: the form, and the results.
// Was view/movements.html (form) + controller/movements.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            <li><a href="../controller/home.php">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="../controller/sum_home.php">Summary Total</a></li>
                <li><a href="../controller/sum_between_dates.php">Summary Between Dates</a></li>
                <li><a href="../controller/sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                <li><a href="../controller/sum_new_nos.php">Summary of New Contacts</a></li>
                <li><a href="sum_in_state.php">Summary Within a State</a></li>
                <li><a href="sum_out_state.php">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
		<li><a href="movements.php"> MOVEMENTS </a></li>
		<li><a href="movements_between_two_numbers.php">Movements Btwn Two Nos</a></li>
		<li><a href="../controller/movements_between_two_numbers_comparision.php">Movements Btwn Two Nos Comparision</a></li>
		<!------<li><a href="../controller/calls_tot.php">Call Details Total</a></li>---->
                <li><a href="../controller/calls_btwn_dates.php">Calls Between Dates</a></li>
                <!-----<li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>---->
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="../controller/cdatcnts.php">Cdat Cnts</a></li>
		<li><a href="bulk_cdat_contacts.php">Bulk Cdat Contacts</a></li>
		<li><a href="../controller/otherscdat.php">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="../controller/imeisearch.php">Phones used in Imei</a></li>
                <li><a href="../controller/imeisinphone.php">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="address.php">Single Address</a></li>
                <li><a href="../controller/bulkaddress.php">Bulk Addresses</a></li>
              </ul>
            </li>
             <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
               <ul>
                <li><a href="../view/day%26nightloc.html">Top 10 Day Night Loc</a></li>
                <li><a href="../view/day%26nightloc_btwn_dates.html">Top 10 Day Night Loc Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Wanted</a>
                  <ul>
                    <li><a href="../controller/wanted1.php">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
              <ul>
                <li><a href="cellid_search.php">Cellid Search</a></li>
                <li><a href="vehicle_search.php">Vehicle Search</a></li>
                <li><a href="../controller/common_cnts.php">Common Cnts</a></li>
                <li><a href="../controller/admin_activity_log.php">User Activity</a></li>
                <li><a href="../controller/admin_sql_console.php">SQL Query Console</a></li>
                </ul>
            </li>
          </ul></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="500" height="120" align="center">
        <tr>
          <th height="21" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">MOVEMENTS OF MOBILE NUMBER</th>
        </tr>
        <tr>
          <th align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="POST" action="movements.php">
            <label for="SUM" font face="verdana">Movements of Mobile No:</label>
              <input type="text" name="PHONE_NO" id="calls" placeholder="Enter Mobile No" required="required"/>
              
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
          </form></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>

<?php if ($__submitted): ?>
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





<div class="back">
<a href="movements.php">
<font color="white"><b>Back</b></font>
</a>
</div>

<?php

set_time_limit(0);
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';

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

    A.CELLTOWERID

FROM CDATDUPL.dbo.CDATPCSUSPECT A WITH (NOLOCK)

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

$rows = [];
while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
    $rows[] = $row;
}

$towerMap = cdat_fetch_tower_map($conn, array_column($rows, 'CELLTOWERID'));

foreach ($rows as $row)
{
    $tower = $towerMap[$row['CELLTOWERID']] ?? [
        'operator' => '',
        'state' => '',
        'areadescription' => '',
        'lat' => '',
        'long' => '',
        'azimuth' => '',
    ];

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
    echo "<td bgcolor='#AED1F1'>".htmlspecialchars($tower['operator'])."</td>";
    echo "<td bgcolor='#AED1F1'>".htmlspecialchars($tower['state'])."</td>";
    echo "<td bgcolor='#C2E0FB' class='area-col'>".htmlspecialchars($tower['areadescription'])."</td>";
    echo "<td bgcolor='#C2E0FB'>".htmlspecialchars($tower['lat'])."</td>";
    echo "<td bgcolor='#C2E0FB'>".htmlspecialchars($tower['long'])."</td>";
    echo "<td bgcolor='#C2E0FB'>".htmlspecialchars($tower['azimuth'])."</td>";

    echo "</tr>";
}

echo "</table>";

echo "</div>";

sqlsrv_free_stmt($st);

sqlsrv_close($conn);

?>
<?php endif; ?>
</body>
</html>
