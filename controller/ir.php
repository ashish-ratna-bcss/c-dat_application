<html>
<head>
</head>
<body bgcolor="#0C5D90">
<script>
function bigImg(x) { 
x.style.height="400px";
x.style.width="400px";
}
function normalImg(x) { 
x.style.height="200px";
x.style.width="220px";
}
</script>;
<script type="text/javascript" src="ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript">
$(function() {
    $(this).bind("contextmenu", function(e) {
        e.preventDefault();
    });
}); 
</script>
<script type="text/JavaScript"> 
    function killCopy(e){ return false } 
    function reEnable(){ return true } 
    document.onselectstart=new Function ("return false"); 
    if (window.sidebar)
    { 
        document.onmousedown=killCopy; 
        document.onclick=reEnable; 
    } 
</script>
<script language="javascript">
document.onmousedown=disableclick;
status="Right Click Disabled";
function disableclick(e)
{
if(event.button="2")
{
alter(status);
return false;
}
}
</script>
<li><a href="../view/ir_search.htm">Back</a></li>
<?php
$serverName = "10.10.46.14\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"FORMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_GET['IRKEY'];


$sql0="SELECT NAME,FATHER_NAME,IMAGE,B.CCNO FROM IR_PARTICULARS A LEFT JOIN IMAGE_TABLE B ON A.IRKEY=B.IRKEY WHERE A.IRKEY='$number'";

$sql1="SELECT DISTINCT 
IRKEY, NAME, ALIAS_NAME, FATHER_NAME, AGE, CONVERT(VARCHAR,DATE_OF_BIRTH,20) DATE_OF_BIRTH, NATIONALITY, 
RELIGION, CASTE, COMMUNITY, PRESENT_ADDRESS, PERMANENT_ADDRESS, MOBILE, 
EMAIL_ID, SOCIAL_MEDIA_ACCOUNTS, AADHAR_NO, RATION_CARD_NO, VOTERID, PASSPORT, 
PANCARD, ELECTRICITY_CONNECTION, GAS_CONNECTION, VEHICLES, DRIVING_LICENSE, 
OTHER_ID_PROOFS, SEX, BUILT, HEIGHT, EYES, HAIR, FACE, COLOUR, TEETH, NOSE, 
BEARD, MUSTACHES, EAR, IDENTIFICATION_MARKS, DEFORMITIES_PECULIARITIES, LANGUAGE_DIALECT, 
BURN_MARKS, LEUCODEMA, MOLE, SCAR, TATTOO, LIVING_STATUS, MARITAL_STATUS, EDUCATION_DETAILS, 
OCCUPATION, INCOME_GROUP, REGULAR_HABITS, CATEGORY FROM FORMS..IR_PARTICULARS
WHERE IRKEY='$number'";


$sql2="SELECT DISTINCT RELATIONSHIP RELATION,NAME+' FATHER_OR_SPOUSE: '+FATHER_OR_SPOUSE+' OCCUPATION: '+OCCUPATION
+' PHONE_NO: '+PHONE+' AGE: '+AGE NAME,PRESENT_ADDRESS ADDRESS,CRIMINAL_BACKGROUND,STATUS FROM FAMILY_HISTORY WHERE IRKEY='$number' ORDER BY RELATION";

$sql3="SELECT DISTINCT PERIOD_OF_OFFENCE FROM OFFENCE_DETAILS WHERE IRKEY='$number'";

$sql4="SELECT DISTINCT TOWN_CITY_OR_VILLAGE,POLICE_STATION_LIMITS,NAME+' S/O '+FATHER_NAME+' AGE: '+AGE+' OCCUPATION: '+OCCUPATION NAME 
,PHONE,ADDRESS_OF_CONTACT_PERSON ADDRESS FROM LOCAL_CONTACTS_FACILITATORS
WHERE IRKEY='$number'";

$sql5="SELECT DISTINCT REGULAR_HABITS FROM IR_PARTICULARS WHERE IRKEY='$number'";

$sql6="SELECT DISTINCT INDULGANCE_BEFORE_OFFENCE FROM OFFENCE_DETAILS
WHERE IRKEY='$number'";

$sql7="SELECT DISTINCT CRIME_HEAD,SUB_TYPE SUB_HEAD,MO FROM OFFENCE_DETAILS
WHERE IRKEY='$number'";

$sql8="SELECT DISTINCT REGULAR_RESIDENCE,PREPARATION_OF_OFFENCE,AFTER_OFFENCE FROM OFFENCE_DETAILS
WHERE IRKEY='$number'";

$sql9="SELECT DISTINCT PROPERTY_STOLEN,PROPERTY_RECOVERED,RECEIVER_NAME,RECEIVER_ADDRESS,REMARKS FROM DISPOSAL_OF_PROPERTY
WHERE IRKEY='$number'";

$sql10="SELECT DISTINCT HOW_SHARE_IS_SPENT FROM DISPOSAL_OF_PROPERTY
WHERE IRKEY='$number'";

$sql11="SELECT DISTINCT DISTRICT,CONFESSED_POLICE_STATION,CONFESSED_CRIME_NO,CONFESSED_YEAR,CONFESSED_SEC_OF_LAW,ASSOCIATES,PROPERTY_STOLEN,PROPERTY_RECOVERED,
REMARKS FROM PREVIOUS_OFFENCE_DETAILS WHERE IRKEY='$number'";

$sql12="SELECT DISTINCT CONVERT(VARCHAR,DATE_OF_ARREST) DATE_OF_ARREST,PLACE_OF_ARREST,'CRIME_NO: '+CONVERT(VARCHAR,CRIME_NO)+'/'+CONVERT(VARCHAR,YEAR)+' SEC_OF_LAW:'+SEC_OF_LAW
[CRIME_NO_SEC_OF_LAW],POLICE_STATION,SUB_DIVISION,DISTRICT_OR_UNIT,
ARRESTED_BY,INTERROGATED_BY,OTHERS_WHO_CAN_IDENTIFY FROM OFFENCE_DETAILS
WHERE IRKEY='$number'";

$sql13="SELECT DISTINCT BRIEF_FACTS1+'
'+BRIEF_FACTS2+'
'+BRIEF_FACTS3 BRIEF_FACTS FROM BRIEF_FACTS
WHERE IRKEY='$number'";

$sql20="select DISTINCT IRKEY,COUNT(*) TOTAL_NBWS_PENDING,FIRST_HEARING_DATE,DECISION_DATE,CASE_STATUS,NEXT_HEARING_DATE,NATURE_OF_DISPOSAL,COURT_NUMBER_AND_JUDGE,STAGE_OF_CASE,
PETITIONER_RESPONDENT,ACT_AND_SEC from nbws_verify_data_important
WHERE CASE_STATUS LIKE '%PENDING%' AND IRKEY='$number'
GROUP BY IRKEY,FIRST_HEARING_DATE,DECISION_DATE,CASE_STATUS,NEXT_HEARING_DATE,NATURE_OF_DISPOSAL,COURT_NUMBER_AND_JUDGE,STAGE_OF_CASE,
PETITIONER_RESPONDENT,ACT_AND_SEC";

$st0 = sqlsrv_query( $conn, $sql0 );
$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql1 );
$st3 = sqlsrv_query( $conn, $sql1 );
$st4 = sqlsrv_query( $conn, $sql1 );
$st5 = sqlsrv_query( $conn, $sql2 );
$st6 = sqlsrv_query( $conn, $sql3 );
$st7 = sqlsrv_query( $conn, $sql4 );
$st8 = sqlsrv_query( $conn, $sql5 );
$st9 = sqlsrv_query( $conn, $sql6 );
$st10 = sqlsrv_query( $conn, $sql7 );
$st11 = sqlsrv_query( $conn, $sql8 );
$st12 = sqlsrv_query( $conn, $sql9 );
$st13 = sqlsrv_query( $conn, $sql10 );
$st14 = sqlsrv_query( $conn, $sql11 );
$st15 = sqlsrv_query( $conn, $sql12 );
$st20 = sqlsrv_query( $conn, $sql20 );
$st16 = sqlsrv_query( $conn, $sql13 );


echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th width=800px ><font size=3 face=verdana color='#F9FBFC'>INTERROGATION REPORT</font></th>
</tr>";
echo "</table>";
echo "</br>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>EXCC/CCNO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st0, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana ><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CCNO'] ."<center></font></td>";
echo "<td height=200px width=200px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="200" width="220" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";


echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'> INDIVIDUAL PARTICULARS </font></th>";
echo "</tr>";
echo "</table>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<center>";
echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>"; 
echo "<td width=638px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['IRKEY']."<center></font></td>";
echo "</tr>";
echo "</center>";

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['NAME']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ALIAS_NAME</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['ALIAS_NAME']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['FATHER_NAME']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['AGE']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE_OF_BIRTH</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['DATE_OF_BIRTH']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIONALITY</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['NATIONALITY']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>RELIGION</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['RELIGION']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CASTE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['CASTE']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>COMMUNITY</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['COMMUNITY']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRESENT ADDRESS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PRESENT_ADDRESS']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PERMANENT ADDRESS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PERMANENT_ADDRESS']."<center></font></td>"; 
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'> UNIQUE IDENTIFICATIONS (DOCUMENTS) </font></th>";
echo "</tr>";
echo "</table>";


echo "<table border=1 cellspacing=0 cellpadding=5>";

while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) )
{

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MOBILE</font></th>"; 
echo "<td width=620px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['MOBILE']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>EMAIL_ID</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['EMAIL_ID']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SOCIAL MEDIA ACCOUNTS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['SOCIAL_MEDIA_ACCOUNTS']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AADHAR_NO</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['AADHAR_NO']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>RATION CARD NO</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['RATION_CARD_NO']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>VOTERID</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['VOTERID']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PASSPORT</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PASSPORT']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PANCARD</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PANCARD']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ELECTRICITY CONNECTION</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['ELECTRICITY_CONNECTION']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>GAS_CONNECTION</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['GAS_CONNECTION']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>VEHICLES</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['VEHICLES']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DRIVING LICENSE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['DRIVING_LICENSE']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTHER ID PROOFS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['OTHER_ID_PROOFS']."<center></font></td>";
echo "</tr>"; 
}
echo "</table>";
echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'>PHYSICAL FEATURES</font></th>";
echo "</tr>";
echo "</table>";

echo "<table border=1 cellspacing=0 cellpadding=5>";

while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) )
{
echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SEX</font></th>"; 
echo "<td width=510px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['SEX']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BUILT</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['BUILT']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>HEIGHT</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['HEIGHT']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>EYES</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['EYES']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>HAIR</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['HAIR']."<center></font></td>";
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FACE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['FACE']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>COLOUR</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['COLOUR']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>TEETH</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['TEETH']."<center></font></td>";
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NOSE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['NOSE']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BEARD</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['BEARD']."<center></font></td>";
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MUSTACHES</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['MUSTACHES']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>EAR</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['EAR']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IDENTIFICATION_MARKS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['IDENTIFICATION_MARKS']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DEFORMITIES_PECULIARITIES</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['DEFORMITIES_PECULIARITIES']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LANGUAGE_DIALECT</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['LANGUAGE_DIALECT']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BURN_MARKS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['BURN_MARKS']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LEUCODEMA</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['LEUCODEMA']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MOLE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['MOLE']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SCAR</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['SCAR']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>TATTOO</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['TATTOO'] ."<center></font></td>"; 
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'> SOCIO/ECONOMIC PROFILE </font></th>";
echo "</tr>";
echo "</table>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
while( $row = sqlsrv_fetch_array( $st4, SQLSRV_FETCH_ASSOC) )
{

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LIVING_STATUS</font></th>"; 
echo "<td width=595px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['LIVING_STATUS']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MARITAL_STATUS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['MARITAL_STATUS']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>EDUCATION_DETAILS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['EDUCATION_DETAILS']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OCCUPATION</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['OCCUPATION']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>INCOME_GROUP</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['INCOME_GROUP']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>REGULAR_HABITS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['REGULAR_HABITS']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CATEGORY</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['CATEGORY']."<center></font></td>";
echo "</tr>";

}

echo "</table>";

echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=870px ><font size=3 face=verdana color='#F9FBFC'> FAMILY HISTORY </font></th>";
echo "</tr>";
echo "</table>";
echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>RELATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIMINAL_BACKGROUND</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATUS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'><center>". $row['RELATION'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRIMINAL_BACKGROUND'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['STATUS'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'> PERIOD OF OFFENCE </font></th>";
echo "</tr>";
echo "</table>";
echo "<table width=815px border=1 cellspacing=0 cellpadding=5>";

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PERIOD OF OFFENCE</font></th>";
echo "<td width=125px bgcolor=#C2E0FB><font size=3 face=verdana><center>". $row['PERIOD_OF_OFFENCE'] ."<center></font></td>";
echo"<tr>";
}
echo "</table>";
echo "</br>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LOCAL CONTACTS/FACILITATORS</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>TOWN_CITY_OR_VILLAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE_STATION_LIMITS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana ><center>". $row['TOWN_CITY_OR_VILLAGE'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION_LIMITS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'>REGULAR HABITS</font></th>";
echo "</tr>";
echo "</table>";
echo "<table width=815px border=1 cellspacing=0 cellpadding=5>";

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>REGULAR HABITS</font></th>";
echo "<td width=125px bgcolor=#C2E0FB><font size=3 face=verdana><center>". $row['REGULAR_HABITS'] ."<center></font></td>";
echo"<tr>";
}
echo "</table>";
echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'>INDULGANCE BEFORE OFFENCE</font></th>";
echo "</tr>";
echo "</table>";
echo "<table width=815px border=1 cellspacing=0 cellpadding=5>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>INDULGANCE BEFORE OFFENCE</font></th>";
echo "<td width=125px bgcolor=#C2E0FB><font size=3 face=verdana><center>". $row['INDULGANCE_BEFORE_OFFENCE'] ."<center></font></td>";
echo"<tr>";
}
echo "</table>";
echo "</br>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MODUS OPERANDI</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SUB_HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana ><center>". $row['CRIME_HEAD'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['SUB_HEAD'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SHELTER</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>REGULAR RESIDENCE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PREPARATION OF OFFENCE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AFTER OFFENCE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana ><center>". $row['REGULAR_RESIDENCE'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PREPARATION_OF_OFFENCE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AFTER_OFFENCE'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DISPOSAL OF PROPERTY</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PROPERTY STOLEN</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PROPERTY RECOVERED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>RECEIVER NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>RECEIVER ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>REMARKS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st12, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=100px bgcolor=#AED1F1><font size=2 face=verdana ><center>". $row['PROPERTY_STOLEN'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PROPERTY_RECOVERED'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['RECEIVER_NAME'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['RECEIVER_ADDRESS'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['REMARKS'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'>HOW SHARE OF AMOUNT SPENT</font></th>";
echo "</tr>";
echo "</table>";
echo "<table width=815px border=1 cellspacing=0 cellpadding=5>";

while( $row = sqlsrv_fetch_array( $st13, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>HOW SHARE OF AMOUNT SPENT</font></th>";
echo "<td width=125px bgcolor=#C2E0FB><font size=3 face=verdana><center>". $row['HOW_SHARE_IS_SPENT'] ."<center></font></td>";
echo"<tr>";
}
echo "</table>";
echo "</br>";

echo "<table width=994px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CASES CONFESSED / PREVIOUS OFFENCE DETAILS</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DISTRICT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CONFESSED POLICE STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CONFESSED CRIME NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana 
color='#F9FBFC'>CONFESSED YEAR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CONFESSED SEC OF LAW</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ASSOCIATES</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PROPERTY STOLEN</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PROPERTY RECOVERED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>REMARKS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st14, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=100px bgcolor=#AED1F1><font size=2 face=verdana ><center>". $row['DISTRICT'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CONFESSED_POLICE_STATION'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CONFESSED_CRIME_NO'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CONFESSED_YEAR'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CONFESSED_SEC_OF_LAW'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ASSOCIATES'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PROPERTY_STOLEN'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PROPERTY_RECOVERED'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['REMARKS'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table width=994px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ARREST PARTICULARS</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE OF ARREST</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PLACE OF ARREST</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME NO AND SEC OF LAW</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SUB DIVISION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DIST/UNIT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ARRESTED BY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>INTERROGATED BY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTHERS WHO CAN IDENTIFY</font></th>
</tr>";


while( $row = sqlsrv_fetch_array( $st15, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=100px bgcolor=#AED1F1><font size=2 face=verdana ><center>".$row['DATE_OF_ARREST'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PLACE_OF_ARREST'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIME_NO_SEC_OF_LAW'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['SUB_DIVISION'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DISTRICT_OR_UNIT'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ARRESTED_BY'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['INTERROGATED_BY'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHERS_WHO_CAN_IDENTIFY'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table width=1050px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NBWS PENDING</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>TOTAL_NBWS_PENDING</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_HEARING_DATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DECISION_DATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CASE_STATUS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NEXT_HEARING_DATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATURE_OF_DISPOSAL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>COURT_NUMBER_AND_JUDGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STAGE_OF_CASE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PETITIONER_RESPONDENT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACT_AND_SEC</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st20, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=100px bgcolor=#AED1F1><font size=2 face=verdana ><center>". $row['IRKEY'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['TOTAL_NBWS_PENDING'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRST_HEARING_DATE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DECISION_DATE'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CASE_STATUS'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NEXT_HEARING_DATE'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NATURE_OF_DISPOSAL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['COURT_NUMBER_AND_JUDGE'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['STAGE_OF_CASE'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PETITIONER_RESPONDENT'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ACT_AND_SEC'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=800px ><font size=3 face=verdana color='#F9FBFC'>BRIEF FACTS</font></th>";
echo "</tr>";
echo "</table>";
echo "<table width=815px border=1 cellspacing=0 cellpadding=5>";

while( $row = sqlsrv_fetch_array( $st16, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BRIEF FACTS</font></th>";
echo "<td width=125px bgcolor=#C2E0FB><font size=3 face=verdana><center>". $row['BRIEF_FACTS'] ."<center></font></td>";
echo"<tr>";
}
echo "</table>";
echo "</br>";
?>
</body>
</html>