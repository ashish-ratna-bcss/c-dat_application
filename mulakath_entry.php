<?php
// One page for both halves of this screen: the form, and the insert.
// Was MULAKATH_ENTRY.html (form) + mulakath_entry.php (handler). The
// de-duplication kept only the handler, so opening this URL ran the INSERT
// against an empty $_POST and wrote a blank row.
// POST only: an insert must never run just because someone opened the page.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<html>
<head>
<title> MULAKATH DETAILS FORM </title>
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
<th width="1126" align="center" scope="col">MULAKATH_DETAILS</th>
   </tr>
</table>
<form action="mulakath_entry.php" Method="post">
<div>JAIL_NAME:</div><textarea type="text" required="required" name="JAIL_NAME" placeholder="JAIL_NAME"></textarea><br/><br/>

<div>PRISONER_NO:</div><textarea type="text" required="required" name="PRISONER_NO" placeholder="PRISONER_NO"></textarea><br/><br/>
<div>PRISONER_NAME:</div><textarea type="text" required="required" name="PRISONER_NAME" placeholder="PRISONER_NAME"></textarea><br/><br/>

<div>PRISONER_FATHER_NAME:</div><textarea type="text" required="required" name="PRISONER_FATHER_NAME" placeholder="PRISONER_FATHER_NAME"></textarea><br/><br/>

<div>VISITOR_NAME:</div><textarea type="text" required="required" name="VISITOR_NAME" placeholder="VISITOR_NAME"></textarea><br/><br/>
<div>VISITOR_PHONE_NO:</div><textarea type="text" required="required" name="VISITOR_PHONE_NO" placeholder="VISITOR_PHONE_NO"></textarea><br/><br/>
<div>VISITOR_ID:</div><textarea type="text" required="required" name="VISITOR_ID" placeholder="VISITOR_ID"></textarea><br/><br/>

<div>VISITOR_NAME_2:</div><textarea type="text" required="required" name="VISITOR_NAME_2" placeholder="VISITOR_NAME_2"></textarea><br/><br/>
<div>VISITOR_PHONE_NO_2:</div><textarea type="text" required="required" name="VISITOR_PHONE_NO_2" placeholder="VISITOR_PHONE_NO_2"></textarea><br/><br/>
<div>VISITOR_ID_2:</div><textarea type="text" required="required" name="VISITOR_ID_2" placeholder="VISITOR_ID_2"></textarea><br/><br/>

<div>VISITOR_NAME_3:</div><textarea type="text" required="required" name="VISITOR_NAME_3" placeholder="VISITOR_NAME_3"></textarea><br/><br/>
<div>VISITOR_PHONE_NO_3:</div><textarea type="text" required="required" name="VISITOR_PHONE_NO_3" placeholder="VISITOR_PHONE_NO_3"></textarea><br/><br/>
<div>VISITOR_ID_3:</div><textarea type="text" required="required" name="VISITOR_ID_3" placeholder="VISITOR_ID_3"></textarea><br/><br/>
<div>VISITOR_NAME_4:</div><textarea type="text" required="required" name="VISITOR_NAME_4" placeholder="VISITOR_NAME_4"></textarea><br/><br/>
<div>VISITOR_PHONE_NO_4:</div><textarea type="text" required="required" name="VISITOR_PHONE_NO_4" placeholder="VISITOR_PHONE_NO_4"></textarea><br/><br/>
<div>VISITOR_ID_4:</div><textarea type="text" required="required" name="VISITOR_ID_4" placeholder="VISITOR_ID_4"></textarea><br/><br/>
<div>DATE_OF_MULAKATH:</div><input type="TEXT" name="DATE" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/><br/><br/>

<div>CRIME_NO:</div><textarea type="text" name="CRIME_NO" placeholder="CRIME_NO"></textarea><br/><br/>

<div>YEAR:</div><textarea type="text" name="YEAR" placeholder="YEAR"></textarea><br/><br/>
<div>POLICE_STATION:</div><textarea type="text" name="POLICE_STATION" placeholder="POLICE_STATION"></textarea><br/><br/>
<input type="submit" value="INSERT" style="padding:15px;"><br/><br/>
</form>

<?php if ($__submitted): ?>
<p class="result">
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"JRMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$JAIL_NAME=$_POST['JAIL_NAME'];
$PRISONER_NO=$_POST['PRISONER_NO'];
$PRISONER_NAME=$_POST['PRISONER_NAME'];
$PRISONER_FATHER_NAME=$_POST['PRISONER_FATHER_NAME'];
$VISITOR_NAME =$_POST['VISITOR_NAME'];
$VISITOR_PHONE_NO =$_POST['VISITOR_PHONE_NO'];
$VISITOR_ID=$_POST['VISITOR_ID'];
$VISITOR_NAME_2 =$_POST['VISITOR_NAME_2'];
$VISITOR_PHONE_NO_2 =$_POST['VISITOR_PHONE_NO_2'];
$VISITOR_ID_2=$_POST['VISITOR_ID_2'];
$VISITOR_NAME_3 =$_POST['VISITOR_NAME_3'];
$VISITOR_PHONE_NO_3 =$_POST['VISITOR_PHONE_NO_3'];
$VISITOR_ID_3=$_POST['VISITOR_ID_3'];
$VISITOR_NAME_4 =$_POST['VISITOR_NAME_4'];
$VISITOR_PHONE_NO_4 =$_POST['VISITOR_PHONE_NO_4'];
$VISITOR_ID_4=$_POST['VISITOR_ID_4'];
$DATE_OF_MULAKATH=$_POST['DATE'];
$CRIME_NO =$_POST['CRIME_NO'];
$YEAR =$_POST['YEAR'];
$POLICE_STATION =$_POST['POLICE_STATION'];
$sql="insert into JRMS.dbo.MULAKATH_ENTRY
(JAIL_NAME, PRISONER_NO, PRISONER_NAME, PRISONER_FATHER_NAME, VISITOR_NAME, VISITOR_PHONE_NO,
VISITOR_ID,VISITOR_NAME_2,VISITOR_PHONE_NO_2,
VISITOR_ID_2,VISITOR_NAME_3,VISITOR_PHONE_NO_3,
VISITOR_ID_3,VISITOR_NAME_4,VISITOR_PHONE_NO_4,
VISITOR_ID_4,DATE_OF_MULAKATH,CRIME_NO, YEAR, POLICE_STATION)
values('$JAIL_NAME','$PRISONER_NO','$PRISONER_NAME','$PRISONER_FATHER_NAME',
'$VISITOR_NAME','$VISITOR_PHONE_NO','$VISITOR_ID','$VISITOR_NAME_2','$VISITOR_PHONE_NO_2','$VISITOR_ID_2','$VISITOR_NAME_3','$VISITOR_PHONE_NO_3','$VISITOR_ID_3','$VISITOR_NAME_4','$VISITOR_PHONE_NO_4','$VISITOR_ID_4','$DATE_OF_MULAKATH','$CRIME_NO','$YEAR','$POLICE_STATION')";
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
