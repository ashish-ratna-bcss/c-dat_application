<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="bulk_cdat_contacts.htm"><font color=#FDEFEF>Back</a></li>
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
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'bulk_cdat_contacts1.php?PHONE_NO='.($row['OTHER']).">".$row['OTHER']."<center></font></td>";
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
</body>
</html>