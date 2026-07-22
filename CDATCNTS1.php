<html>
<head>
</head>
<body bgcolor="#0C5D90">
<p><a href="CDATCNTS.html"><font color=#FDEFEF>BACK</a></p>
<style type="text/css">
a:link , a:visited{
text-decoration: none;
}
</style>
<body bgcolor="#BDBDBD">
<?php
require_once __DIR__ . '/activity_logger.php';
audit_log('CDAT Contacts', 'Search', ['phone_number' => $_POST['PHONE_NO'] ?? '']);
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$number 	= $_POST['PHONE_NO'];

$sql1="SELECT  DISTINCT '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,'' AS CATEGORY,''LAST_UPDATED,''INC_OFFICER INTO #T";

$sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME+'_'+B.ROLE NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";

$sql2= "SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.MO ELSE A.MO END AS MO,
CASE WHEN A.PHONE=B.PHONE THEN B.CATEGORY ELSE A.CATEGORY END AS CAT,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+
(CASE WHEN C.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+
(CASE WHEN D.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS,
CASE WHEN A.PHONE=B.PHONE THEN B.INC_OFFICER ELSE A.INC_OFFICER END AS INC_OFFICER into #ss FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE 
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";

$sql56="select distinct a.phone PHONE,a.first_call FIRST_CALL,a.last_call LAST_CALL,case when a.nickname=''
then b.nickname else a.nickname end as NICKNAME,case when a.mo=''
then b.mo else a.mo end as MO,
case when a.cat=''
then b.CATEGORY else a.cat end as  CAT,
LAST_UPDATED,a.address ADDRESS,
case when a.INC_OFFICER=''
then b.INC_OFFICER else a.INC_OFFICER end as  INC_OFFICER
into #ss1 from #ss a
left join cdatdupl.dbo.cdatsuspect b on a.phone=b.phone";

$sql57="Select A.PHONE,CASE WHEN B.MOBILE=A.PHONE THEN B.IMAGE ELSE (SELECT IMAGE FROM SUSPECT_IMAGE_TABLE WHERE IRKEY='113769') END AS IMAGE,A.FIRST_CALL,A.LAST_CALL,A.NICKNAME,A.MO,A.CAT,A.LAST_UPDATED,A.ADDRESS,A.INC_OFFICER FROM #ss1 A LEFT JOIN
SUSPECT_IMAGE_TABLE B ON B.MOBILE=A.PHONE";

$sql3="SELECT DISTINCT PHONE, IMEINUMBER, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,
CONVERT(VARCHAR,MAX(ASONDATE),20) AS LAST_UPDATED FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' GROUP BY PHONE,IMEINUMBER ORDER BY LAST_UPDATED";

$sql4="SELECT * INTO #XX FROM CDAT_DETAILS1 WHERE PHONE='$number' and other!=''";

$sql5 = "select distinct a.PHONE,OTHER, NICKNAME+'_'+ROLE NICKNAME,
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', count(*) as CALLS,sum(cast(duration as numeric)) as dur,CONVERT(VARCHAR,MIN(STARTTIME),20) as FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) as LAST_CALL INTO #TT from #XX a
left join cdatdupl.dbo.cdatsuspect b on a.other=b.phone
WHERE OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT)
 group by a.phone, A.other, nickname,ROLE order by  calls desc, other";
 
$sql6 = "SELECT distinct A.PHONE,A.OTHER,A.NICKNAME,MO,CATEGORY,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL,
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

$sql7 = "SELECT DISTINCT  A.PHONE,A.OTHER,A.NICKNAME,A.MO,A.CATEGORY AS CAT,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL,
CASE WHEN A.OTHER=B.PHONE THEN ISNULL(B.FULLNAME,'')+','+ISNULL(B.FULLADDRESS,'')+','+
ISNULL(CATEGORY_TYPE,'')+','+CONVERT(CHAR(10),CAST(DOA AS DATETIME),105)  ELSE A.ADDRESS END AS ADDRESS, 
A.INC_OFFICER,CASE WHEN C.MOBILE LIKE '%'+A.OTHER+'%' THEN 'IR AVAILABLE CLICK HERE TO VIEW IR' ELSE '' END AS IRFORMS INTO #WITHADDRESS1 FROM #WITHADDRESS A
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE B ON A.OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL 
LEFT JOIN FORMS.DBO.IR_PARTICULARS C ON C.MOBILE LIKE '%'+A.OTHER+'%'";

$sql71="Select A.*,CASE WHEN B.MOBILE=A.OTHER THEN B.IMAGE ELSE (SELECT IMAGE FROM SUSPECT_IMAGE_TABLE WHERE IRKEY='113769') END AS IMAGE FROM #WITHADDRESS1 A LEFT JOIN 
SUSPECT_IMAGE_TABLE B ON B.MOBILE=A.OTHER ORDER BY CALLS DESC,OTHER";

$sql8 ="SELECT 'CDAT CONTACTS OF MOBILE NO: '+'$number' as PHONE";

$sql9="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** NO CDAT CONTACTS TO $number ***' end as CNTS FROM #WITHADDRESS";


$st1 = sqlsrv_query($conn, $sql1);
$st10=sqlsrv_query($conn, $sql10);
$st2 = sqlsrv_query( $conn, $sql2);
$st20 =sqlsrv_query( $conn, $sql56);
$st21 =sqlsrv_query( $conn, $sql57);
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
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CAT</font></th>
<!-- <th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font></th> -->
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IO NAME</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st21, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="data:image; base64,'.$row["IMAGE"].'"></img>' ?> <?php "</td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CAT'] ."<center></font></td>";
//echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_UPDATED'] ."<center></font></td>";
echo "<td width=500px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['INC_OFFICER'] ."<center></font></td>";
echo "</tr>";
}

echo"</table><br />";

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
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IR</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st71, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'CDATCNTS2.PHP?PHONE_NO='.($row['OTHER']).">".$row['OTHER']."<center></font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="data:image; base64,'.$row["IMAGE"].'"></img>' ?> <?php "</td>";
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
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'CDAT_IRFORM.PHP?OTHER_NO='.($row['OTHER'])."><center>". $row['IRFORMS'] ."<center></font></td>";
echo "</tr>";


}
echo"</table><br />";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['CNTS'] ."<center></td></font></br>";
}

?>
</body>
</html>