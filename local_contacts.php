<?php
// One page for both halves of this screen: the form, and the insert.
// Was LOCAL_CONTACTS.HTML (form) + LOCAL_CONTACTS.PHP (handler). The
// de-duplication kept only the handler, so opening this URL ran the INSERT
// against an empty $_POST and wrote a blank row.
// POST only: an insert must never run just because someone opened the page.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<html>
<head>
<title> LOCAL CONTACTS AND FACILITATORS FORM </title>
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
<body bgcolor="#80800">
<table>
        <tr>
          <th width="1126" align="center" scope="col">LOCAL CONTACTS AND FACILITATORS</th>
        </tr>
</table>
<form action="local_contacts.php" Method="post">
<div>IRKEY:</div><textarea  type="text" name="IRKEY" placeholder="IRKEY" style="float:center;"></textarea><br/><br/>
<div>TOWN_CITY_OR_VILLAGE:</div><textarea type="text"  name="TOWN_CITY_OR_VILLAGE" placeholder="TOWN/CITY/VILLAGE"></textarea><br/><br/>
<div>POLICE_STATION_LIMITS:</div><textarea type="text" name="POLICE_STATION_LIMITS" placeholder="POLICE STATION"></textarea><br/><br/>
<div>NAME:</div><textarea type="text" name="NAME" placeholder="NAME"></textarea><br/><br/>
<div>FATHER_NAME:</div><textarea type="text" name="FATHER_NAME" placeholder="FATHER NAME"></textarea><br/><br/>
<div>AGE:</div><textarea type="text" name="AGE" placeholder="AGE"></textarea><br/><br/>
<div>OCCUPATION:</div><textarea type="text" name="OCCUPATION" placeholder="OCCUPATION"></textarea><br/><br/>
<div>ADDRESS_OF_CONTACT_PERSON:</div><textarea type="text" name="ADDRESS_OF_CONTACT_PERSON" placeholder="ADDRESS"></textarea><br/><br/>
<div>CRIME_NO:</div><textarea type="text" name="CRIME_NO" placeholder="CRIME NO"></textarea><br/><br/>
<div>YEAR:</div><textarea type="text" name="YEAR" placeholder="YEAR"></textarea><br/><br/>
<div>SEC_OF_LAW:</div><textarea type="text" name="SEC_OF_LAW" placeholder="SEC OF LAW"></textarea><br/><br/>
<div>POLICE_STATION:</div><textarea type="text" name="POLICE_STATION" placeholder="POLICE STATION"></textarea><br/><br/>
<div>PHONE:</div><textarea type="text" name="PHONE" placeholder="PHONE"></textarea><br/><br/>
<input type="submit" value="insert">
<br/><br/>

</form>

<?php if ($__submitted): ?>
<p class="result">
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"forms");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY'];
$TOWN_CITY_OR_VILLAGE=$_POST['TOWN_CITY_OR_VILLAGE'];
$POLICE_STATION_LIMITS=$_POST['POLICE_STATION_LIMITS'];
$NAME=$_POST['NAME'];
$FATHER_NAME=$_POST['FATHER_NAME'];
$AGE=$_POST['AGE'];
$OCCUPATION=$_POST['OCCUPATION'];
$ADDRESS_OF_CONTACT_PERSON=$_POST['ADDRESS_OF_CONTACT_PERSON'];
$CRIME_NO=$_POST['CRIME_NO'];
$YEAR=$_POST['YEAR'];
$SEC_OF_LAW=$_POST['SEC_OF_LAW'];
$POLICE_STATION=$_POST['POLICE_STATION'];
$PHONE=$_POST['PHONE'];
$sql="insert into FORMS.dbo.LOCAL_CONTACTS_FACILITATORS
(IRKEY, TOWN_CITY_OR_VILLAGE, POLICE_STATION_LIMITS, NAME, FATHER_NAME,
AGE, OCCUPATION, ADDRESS_OF_CONTACT_PERSON, CRIME_NO, YEAR, SEC_OF_LAW, POLICE_STATION, PHONE)
values('$IRKEY','$TOWN_CITY_OR_VILLAGE','$POLICE_STATION_LIMITS','$NAME','$FATHER_NAME',
'$AGE','$OCCUPATION','$ADDRESS_OF_CONTACT_PERSON','$CRIME_NO','$YEAR',
'$SEC_OF_LAW','$POLICE_STATION','$PHONE')";
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
