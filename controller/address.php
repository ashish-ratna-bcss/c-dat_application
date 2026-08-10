<html>
<head>

<link href="../assets/css/w3.css" rel="stylesheet"/>
<style>
        .container{width:960px;margin:30px auto;}
        thead select{border: 1px solid #ffffff;width:100%;}
    </style>
</head>
<body bgcolor="#0C5D90">
<li><a href="../view/address.htm"><font color=#FDEFEF>Back</a></li>
<form action ='address.php'method='post'>
<b><font size=3 face=verdana color='#F9FBFC'>ADDRESS OF MOBILE NO : </b>
<input type='text' name='PHONE_NO' value=''>
<input type ='submit' value='Submit'/>



<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['PHONE_NO'])){

$number=$_POST['PHONE_NO'];

$sql8="SELECT 'ADDRESS OF MOBILE NO: '+'$number' as PHONE1";

$sql9="SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,''LAST_UPDATED,''INC_OFFICER INTO #T";

$sql10="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME+'_'+B.ROLE NICKNAME,MO,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,B.ROLE, INC_OFFICER";

$sql11="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.MO ELSE A.MO END AS MO,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS,
CASE WHEN A.PHONE=B.PHONE THEN B.INC_OFFICER ELSE A.INC_OFFICER END AS INC_OFFICER FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C WITH (NOLOCK) ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D WITH (NOLOCK) ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";

$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st11 = sqlsrv_query( $conn, $sql11 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5 id=mytable class=w3-table-all>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IO NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>QRCODE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_UPDATED'] ."<center></font></td>";
echo "<td width=500px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['INC_OFFICER'] ."<center></font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="../qrcode/php/qr_img.php?d='.urlencode('PHONE NO:'.$number.'  '.'ADDRESS: '. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["ADDRESS"])).'"></img>'; ?> <?php "</td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st11);
}

?>
</tbody>
        </table>
</div>
<script src="../assets/vendor/drop-down-filter/jquery.min.js"></script>
    <script src="../assets/vendor/drop-down-filter/ddtf.js"></script>
    <script>
        $('#mytable').ddTableFilter();
    </script> 
</body>
</html>
</html>