<?php
// Must run before any output: audit_require_* redirects with
// header(), which is lost once the layout has started printing.
require_once __DIR__ . '/activity_logger.php';
audit_require_session();
?>
<?php
// One page for both halves of this screen: the form, and the results.
// Was view/near_by_celltowerids.htm (form) + controller/near_by_celltowerids.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Near By Celltowerids");
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="1126" height="144" align="center">
        <tr>
          <th height="25" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">NEAR BY CELLID SEARCH</th>
        </tr>
        <tr>
          <th align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="near_by_celltowerids.php">
            <label  font face="verdana">LAT:</label>
              <input type="text" name="LAT" id="LAT" placeholder="Enter LAT" required="required"/>
             <label  font face="verdana">LONG:</label>
              <input type="text" name="LONG" id="LONG" placeholder="Enter LONG" required="required"/>
              RANGE IN MTS : 
<select name="RANGE">
<option value=""></option>
<option value="100">100</option>
<option value="200">200</option>
<option value="300">300</option>
<option value="400">400</option>
<option value="500">500</option>
<option value="600">600</option>
<option value="700">700</option>
<option value="800">800</option>
<option value="900">900</option>
<option value="1000">1000</option>
<option value="1100">1100</option>
<option value="1200">1200</option>
<option value="1300">1300</option>
<option value="1400">1400</option>
<option value="1500">1500</option>
<option value="1600">1600</option>
<option value="1700">1700</option>
<option value="1800">1800</option>
<option value="1900">1900</option>
<option value="2000">2000</option>
<option value="2100">2100</option>
<option value="2200">2200</option>
<option value="2300">2300</option>
<option value="2400">2400</option>
<option value="2500">2500</option>
<option value="2600">2600</option>
<option value="2700">2700</option>
<option value="2800">2800</option>
<option value="2900">2900</option>
<option value="3000">3000</option>
<option value="3100">3100</option>
<option value="3200">3200</option>
<option value="3300">3300</option>
<option value="3400">3400</option>
<option value="3500">3500</option>
<option value="3600">3600</option>
<option value="3700">3700</option>
<option value="3800">3800</option>
<option value="3900">3900</option>
<option value="4000">4000</option>
<option value="4100">4100</option>
<option value="4200">4200</option>
<option value="4300">4300</option>
<option value="4400">4400</option>
<option value="4500">4500</option>
<option value="4600">4600</option>
<option value="4700">4700</option>
<option value="4800">4800</option>
<option value="4900">4900</option>
<option value="5000">5000</option>



</select>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
          </form></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
  </table>
</div>


<?php if ($__submitted): ?>
<?php
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/sql_safe.php';
audit_require_session();
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$LAT = sql_safe_float($_POST['LAT'] ?? '0');
$LONG = sql_safe_float($_POST['LONG'] ?? '0');
$RANGE = sql_safe_float($_POST['RANGE'] ?? '10000');



$sql1="SELECT 'NEAR BY CELLID SEARCH: '+'$LAT' + ' AND ' + '$LONG' as PHONE1";


$sql2 ="declare @lat decimal(14,10),@long decimal (14,10),@radius decimal(15,10)
set @lat='$LAT'
set @long='$LONG'
set @radius='$RANGE'
SELECT CELLTOWERID, CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)  DIST,
DBO.GETBEARING(LAT,LONG,@lat,@long) BR,
AREADESCRIPTION,SITEADDRESS,OPERATOR,LAT,LONG,AZIMUTH,OTYPE,STATE,CONVERT(VARCHAR,LASTUPDATE,20) LASTUPDATE
FROM dbo.CELLTOWERfiltered WHERE 
LAT BETWEEN @lat-1 AND @lat+1  AND LONG BETWEEN @long-1 AND @long+1  AND
ISNUMERIC(LAT)=1 AND LAT IS NOT NULL AND ISNUMERIC(LONG)=1 AND LONG IS NOT NULL AND
DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000<@radius
ORDER BY OPERATOR,DIST,OTYPE";


$st1 = sqlsrv_query( $conn, $sql1 );
sqlsrv_render_query_error($st1, 'Title query');
$st2 = sqlsrv_query( $conn, $sql2 );
sqlsrv_render_query_error($st2, 'Nearby towers');

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". h($row['PHONE1'] ?? '') ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DIST</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SITEADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZIMUTH</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTYPE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LASTUPDATE</font></th>
</tr>";


while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['CELLTOWERID'] ?? '') ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['DIST'] ?? '') ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['BR'] ?? '') ."<center></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['AREADESCRIPTION'] ?? '') ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['SITEADDRESS'] ?? '') ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['OPERATOR'] ?? '') ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['LAT'] ?? '') ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['LONG'] ?? '') ."<center></td>";
echo "<td width=15px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['AZIMUTH'] ?? '') ."<center></font></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['OTYPE'] ?? '') ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['STATE'] ?? '') ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['LASTUPDATE'] ?? '') ."</td>";
echo "</tr>";

} 
echo"</table></br>";

sqlsrv_free_stmt( $st2);
?>
<?php endif; ?>
<?php layout_end(); ?>
