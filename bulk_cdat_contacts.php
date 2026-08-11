<?php
// One page for both halves of this screen: the form, and the results.
// Was bulk_cdat_contacts.htm (form) + bulk_cdat_contacts.php (handler). The de-duplication kept only the
// handler, so opening this URL ran the query against an undefined $_POST
// key and drew a headings-only table with no box to type in.
// GET shows the form; a submit renders the form and the results below it.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="SpryAssets/sprymenubar.js" type="text/javascript"></script>
<link href="SpryAssets/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="center" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1305" height="134" align="center" valign="bottom" background="IMAGES/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
           <li><a href="home.php">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="sum_home.php">Summary Total</a></li>
                <li><a href="sum_between_dates.php">Summary Between Dates</a></li>
                <li><a href="sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                <li><a href="sum_new_nos.php">Summary of New Contacts</a></li>
                <li><a href="sum_in_state.html">Summary Within a State</a></li>
                <li><a href="sum_out_state.php">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="movements.html"> MOVEMENTS </a></li>
		<li><a href="movements_between_two_numbers.html">Movements Btwn Two Nos</a></li>
		<li><a href="movements_between_two_numbers_comparision.php">Movements Btwn Two Nos Comparision</a></li>
		<!-----<li><a href="calls_tot.php">Call Details Total</a></li>---->
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                <!------<li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>---->
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="cdatcnts.php">Cdat Cnts</a></li>
		<li><a href="bulk_cdat_contacts.php">Bulk Cdat Contacts</a></li>
                <li><a href="otherscdat.php">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="imeisearch.php">Phones used in Imei</a></li>
                <li><a href="imeisinphone.php">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="address.php">Single Address</a></li>
                <li><a href="bulkaddress.php">Bulk Addresses</a></li>
              </ul>
            </li>
             <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
               <ul>
                <li><a href="day%26nightloc.html">Top 10 Day Night Loc</a></li>
                <li><a href="day%26nightloc_btwn_dates.html">Top 10 Day Night Loc Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Wanted</a>
                  <ul>
                    <li><a href="wanted1.php">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
              <ul>
                <li><a href="cellid_search.php">Cellid Search</a></li>
                <li><a href="vehicle_search.php">Vehicle Search</a></li>
                <li><a href="common_cnts.php">Common Cnts</a></li>
                <li><a href="admin_activity_log.php">User Activity</a></li>
                <li><a href="admin_sql_console.php">SQL Query Console</a></li>
                </ul>
                </li>
                </ul>
                </td>
        </tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p>
      <p class="MenuBarItemHover">&nbsp;</p>
      <table width="907" height="168">
        <tr>
          <th height="27" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" >BULK CDAT CONTACTS</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="bulk_cdat_contacts.php">
                 <th width="764" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" > BULK CDAT CONTACTS:            <style>
label textarea{
font: normal 15px courier;
vertical-align: middle;
}
</style>
<label> <textarea rows=3 cols=50 name='PHONE_NO'  id="BULK_ADDRESS" placeholder="Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx" required="required"></textarea></label>
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      
<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number= $_POST['PHONE_NO'];

$number2 = str_replace(",","','","$number");
$number3 = str_replace(",","' INSERT INTO #T1 SELECT '","$number");

echo "<font size=4 face=verdana  color='#F9FBFC'><td><center><b>ADDRESSES OF MOBILE NOS<center></td></font></br>";


$sqlB1= "CREATE TABLE #T1 (PHONE NVARCHAR (20) NULL)";

$sqlB2= "INSERT INTO #T1 SELECT '$number3'";

$sql1="SELECT  DISTINCT PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,'' AS CATEGORY,''LAST_UPDATED,''INC_OFFICER INTO #T FROM #T1";

$sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME+'_'+B.ROLE NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,INC_OFFICER INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE 
WHERE A.PHONE IN ('$number2') GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";

$sqlA="select distinct * INTO #CDATADDRESS from cdatdupl..cdataddress where phone in ('$number2')";

$sqlB="select distinct * INTO #ADDRESS_OTHER_STATE from cdatdupl..ADDRESS_OTHER_STATE where phone in ('$number2')";




$sql3="SELECT DISTINCT PHONE, IMEINUMBER, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,
CONVERT(VARCHAR,MAX(ASONDATE),20) AS LAST_UPDATED FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN ('$number2') GROUP BY PHONE,IMEINUMBER ORDER BY LAST_UPDATED";

$sql4="SELECT * INTO #XX FROM CDAT_DETAILS1 WHERE PHONE IN ('$number2') and other!=''";

$sql5 = "select distinct a.PHONE,OTHER, NICKNAME+'_'+ROLE NICKNAME,
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', count(*) as CALLS,sum(cast(duration as numeric)) as dur,CONVERT(VARCHAR,MIN(STARTTIME),20) as FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) as LAST_CALL INTO #TT from #XX a
left join cdatdupl.dbo.cdatsuspect b on a.other=b.phone
WHERE OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT)
 group by a.phone, A.other, nickname,ROLE order by  calls desc, other";
 
$sql6 = "SELECT A.PHONE,A.OTHER,A.NICKNAME,MO,CATEGORY,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL,
CASE WHEN FULLNAME IS NULL THEN '' ELSE FULLNAME END+' '+
CASE WHEN b.FULLADDRESS IS NULL THEN  
CASE WHEN (CALLS=DUR AND LEN(OTHER)<>10) 
OR (LEFT(OTHER,1)NOT IN ('9','8') AND LEN(OTHER)>14) 
OR LEN(OTHER)<10  OR SUBSTRING(OTHER,5,10) LIKE '%0000%' or isnumeric(other)=0
--or (len(other)>11 and '00'+other not in (select phoneprefix+'%' from cdatphonearea))
THEN 'JUNK-COULD BE bulk SMS or VOIP calls' else
case when min(areadescription) is null then 'code n/a' else min(areadescription) end
END  ELSE b.FULLADDRESS+','+ISNULL(CATEGORY_type,'') 
END AS ADDRESS,INC_OFFICER INTO #WITHADDRESS FROM #TT  A 
LEFT JOIN CDATDUPL.DBO.CDATADDRESS B ON OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT C ON A.OTHER=C.PHONE
left join cdatdupl.dbo.cdatphonearea d on case when len(other)=10 then other else case when len(other)>10 then '00'+other else null end end
like phoneprefix+'%'
group by a.PHONE, other,[IN],[OUT],calls,dur, FIRST_CALL,
LAST_CALL,FULLNAME,b.FULLADDRESS, A.nickname,CATEGORY_type,MO,CATEGORY, INC_OFFICER";

$sql7 = "SELECT A.PHONE,OTHER,NICKNAME,MO,CATEGORY AS CAT,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL,
CASE WHEN A.OTHER=B.PHONE THEN ISNULL(B.FULLNAME,'')+','+ISNULL(B.FULLADDRESS,'')+','+
ISNULL(CATEGORY_TYPE,'')+','+CONVERT(CHAR(10),CAST(DOA AS DATETIME),105)  ELSE A.ADDRESS END AS ADDRESS, 
INC_OFFICER INTO #WITHADDRESS1 FROM #WITHADDRESS A
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE B ON A.OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL";

$sql71="Select A.*,CASE WHEN B.MOBILE=A.OTHER THEN B.IMAGE ELSE (SELECT IMAGE FROM SUSPECT_IMAGE_TABLE WHERE IRKEY='113769') END AS IMAGE FROM #WITHADDRESS1 A LEFT JOIN 
SUSPECT_IMAGE_TABLE B ON B.MOBILE=A.OTHER ORDER BY PHONE,CALLS DESC,OTHER";

$sql8 ="SELECT 'CDAT CONTACTS OF MOBILE NO: '+'$number' as PHONE";

$sql9="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** NO CDAT CONTACTS TO $number ***' end as CNTS FROM #WITHADDRESS";

$stA = sqlsrv_query($conn, $sqlA);
$stB = sqlsrv_query($conn, $sqlB);
$stB1 = sqlsrv_query($conn, $sqlB1);
$stB2= sqlsrv_query($conn, $sqlB2);
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st71= sqlsrv_query( $conn, $sql71);
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );


while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=3>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICK NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IN</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OUT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IO NAME</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st71, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'BULK_CDAT_CONTACTS1.PHP?PHONE_NO='.($row['OTHER']).">".$row['OTHER']."<center></font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CAT'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IN'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OUT'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DUR'] ."<center></font></td>";
echo "<td width=125px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=500px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['INC_OFFICER'] ."</font></td>";
echo "</tr>";


}
echo"</table><br />";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['CNTS'] ."<center></td></font></br>";
}

?>
<?php endif; ?>
<p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
</html>
