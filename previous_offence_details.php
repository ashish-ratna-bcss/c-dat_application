<?php
// One page for both halves of this screen: the form, and the insert.
// Was PREVIOUS_OFFENCE_DETAILS.html (form) + previous_offence_details.PHP
// (handler). The de-duplication kept only the handler, so opening this URL ran
// the INSERT against an empty $_POST and wrote a blank row.
// POST only: an insert must never run just because someone opened the page.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<html>
<head>
<title> PREVIOUS OFFENCE DETAILS FORM </title>
<meta charset="utf-8">
<link href="SpryAssets/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<style>
input[type=text], select {
    width: 25%;
    padding: 3px 10px;
    margin: 4px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 2px;
    box-sizing: border-box;
}

input[type=submit] {
    width: 15%;
    background-color: ORANGE;
    color: white;
    padding: 4px 15px;
    margin: 4px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 25px;
}

div {
    border-radius: 5px;
    background-color: #f2f2f2;
    padding: 5px;
    width: 40%;
    border-radius: 4px;
    float:left;
    width:200px;
    text-align: left;
        }
       textarea{
       width: 25%;
       height: 25px;
       }
.result{
clear:both;
width:auto;
float:none;
display:inline-block;
background-color:#921215;
color:#fff;
font:bold 16px verdana;
padding:8px 14px;
}
</style>
</head>
<body bgcolor="#808000">
<table>
   <tr>
<th width="1126" align="center" scope="col">PREVIOUS OFFENCE DETAILS</th>
   </tr>
</table>
<form action="previous_offence_details.php" Method="post">
<div>IRKEY:</div><textarea  type="text" name="IRKEY" required="required" placeholder="IRKEY" style="float:center;"></textarea><br/><br/>
<div>DISTRICT:</div><textarea type="text"  required="required" name="DISTRICT" placeholder="DISTRICT"></textarea><br/><br/>
<div>CONFESSED_POLICE_STATION:</div><textarea type="text" required="required" name="CONFESSED_POLICE_STATION" placeholder="CONFESSED_PS"></textarea><br/><br/>
<div>CONFESSED_CRIME_NO:</div><textarea type="text" required="required" name="CONFESSED_CRIME_NO" placeholder="CONFESSED_CRIME_NO"></textarea><br/><br/>
<div>CONFESSED_YEAR:</div><textarea type="text" required="required" name="CONFESSED_YEAR" placeholder="CONFESSED_YEAR"></textarea><br/><br/>
<div>CONFESSED_SEC_OF_LAW:</div><textarea type="text" required="required" name="CONFESSED_SEC_OF_LAW" placeholder="SEC OF LAW"></textarea><br/><br/>

<div>CONFESSED_DATE_OF_ARREST:</div><input type="TEXT" name="DATE" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/><br/><br/>
<div>ASSOCIATES:</div><textarea type="text" name="ASSOCIATES" placeholder="ASSOCIATES"></textarea><br/><br/>
<div>PROPERTY_STOLEN:</div><textarea type="text" name="PROPERTY_STOLEN" placeholder="PROPERTY STOLEN"></textarea><br/><br/>
<div>PROPERTY_RECOVERED:</div><textarea type="text" name="PROPERTY_RECOVERED" placeholder="PROPERTY_RECOVERED"></textarea><br/><br/>
<div>REMARKS:</div><textarea type="text" name="REMARKS" placeholder="REMARKS"></textarea><br/><br/>
<div>CRIME_NO:</div><textarea type="text" name="CRIME_NO" placeholder="CRIME_NO"></textarea><br/><br/>
<div>YEAR:</div><textarea type="text" name="YEAR" placeholder="YEAR"></textarea><br/><br/>
<div>POLICE_STATION:</div><textarea type="text" name="POLICE_STATION" placeholder="POLICE_STATION"></textarea><br/><br/>


<input type="submit" value="INSERT" style="padding:15px;"><br/><br/></form>

<?php if ($__submitted): ?>
<p class="result">
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"FORMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY'];
$DISTRICT=$_POST['DISTRICT'];
$CONFESSED_POLICE_STATION=$_POST['CONFESSED_POLICE_STATION'];
$CONFESSED_CRIME_NO =$_POST['CONFESSED_CRIME_NO'];
$CONFESSED_YEAR =$_POST['CONFESSED_YEAR'];
$CONFESSED_SEC_OF_LAW=$_POST['CONFESSED_SEC_OF_LAW'];
$CONFESSED_DATE_OF_ARREST=$_POST['DATE'];
$ASSOCIATES =$_POST['ASSOCIATES'];
$PROPERTY_STOLEN =$_POST['PROPERTY_STOLEN'];
$PROPERTY_RECOVERED =$_POST['PROPERTY_RECOVERED'];
$REMARKS =$_POST['REMARKS'];
$CRIME_NO =$_POST['CRIME_NO'];
$YEAR =$_POST['YEAR'];
$POLICE_STATION =$_POST['POLICE_STATION'];
$sql="insert into FORMS.dbo.PREVIOUS_OFFENCE_DETAILS
(IRKEY, DISTRICT, CONFESSED_POLICE_STATION, CONFESSED_CRIME_NO, CONFESSED_YEAR,
CONFESSED_SEC_OF_LAW,CONFESSED_DOA, ASSOCIATES, PROPERTY_STOLEN, PROPERTY_RECOVERED, REMARKS,
CRIME_NO, YEAR, POLICE_STATION)
values('$IRKEY','$DISTRICT','$CONFESSED_POLICE_STATION','$CONFESSED_CRIME_NO','$CONFESSED_YEAR',
'$CONFESSED_SEC_OF_LAW','$CONFESSED_DATE_OF_ARREST','$ASSOCIATES','$PROPERTY_STOLEN','$PROPERTY_RECOVERED','$REMARKS',
'$CRIME_NO','$YEAR','$POLICE_STATION')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
// The old "refresh:30" bounce back to the form is gone: the form is on this
// page now, and header() after output only raises a warning.
?>
</p>
<?php endif; ?>
</body>
</html>
