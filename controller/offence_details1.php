<?php
// One page for both halves of this screen: the form, and the results.
// Was view/offence_details1.htm (form) + controller/offence_details1.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<DOCHTML>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Offence Details1");
?>

<table>
   <tr>
<th width="1126" align="center" scope="col">OFFENCE DETAILS</th>
   </tr>
</table>
<form action="offence_details1.php" Method="post">
<div>IRKEY:</div><textarea  type="text" name="IRKEY" required="required" placeholder="IRKEY" style="float:center;"></textarea><br/><br/>
<div>PERIOD_OF_OFFENCE:</div><textarea type="text" required="required" name="PERIOD_OF_OFFENCE" placeholder="PERIOD OF OFF"></textarea><br/><br/>
<div>REGULAR_RESIDENCE:</div><textarea type="text" required="required" name="REGULAR_RESIDENCE" placeholder="REGULAR RESIDENCE"></textarea><br/><br/>
<div>PREPARATION_OF_OFFENCE:</div><textarea type="text" name="PREPARATION_OF_OFFENCE" placeholder="PREPARATION OF OFFENCE"></textarea><br/><br/>
<div>AFTER_OFFENCE:</div><textarea type="text" name="AFTER_OFFENCE" placeholder="AFTER OFFENCE"></textarea><br/><br/>
<div>INDULGANCE_BEFORE_OFFENCE:</div><textarea type="text" name="INDULGANCE_BEFORE_OFFENCE" placeholder="INDULGANCE"></textarea><br/><br/>

<div>CRIME_HEAD:</div><SELECT name="CRIME_HEAD">
<option value=""></option>
<option value="ORDINARY THEFT">ORDINARY THEFT</option>
<option value="SNATCHING">SNATCHING</option>
<option value="HB DAY">HB DAY</option>
<option value="HB NIGHT">HB NIGHT</option>
<option value="MURDER">MURDER</option>
<option value="ATTEMPT TO MURDER">ATTEMPT TO MURDER</option>
<option value="EXTORTION">EXTORTION</option>
<option value="CHEATING">CHEATING</option>
<option value="DIVERTING ATTENTION">DIVERTING ATTENTION</option>
<option value="DACOITY">DACOITY</option>
<option value="HOUSE THEFT">HOUSE THEFT</option>
<option value="RAPE">RAPE</option>
<option value="ROBBERY">ROBBERY</option>
<option value="SERVANT THEFT">SNATCHING</option>
</SELECT>
<br/><br/><div>SUB_TYPE:</div><textarea type="text" name="SUB_TYPE" placeholder="SUB_TYPE"></textarea><br/><br/>
<div>MO:</div><textarea type="text" name="MO" placeholder="MO"></textarea><br/><br/>
<div>DATE_OF_ARREST:</div><input type="TEXT" name="DATE" required="required" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/><br/><br/>
<div>PLACE_OF_ARREST:</div><textarea type="text" name="PLACE_OF_ARREST" placeholder="PLACE OF ARREST"></textarea><br/><br/>
<div>SUB_DIVISION:</div><textarea type="text" name="SUB_DIVISION" placeholder="SUB DIVISION"></textarea><br/><br/>
<div>DISTRICT_OR_UNIT:</div><textarea type="text" name="DISTRICT_OR_UNIT" placeholder="DISTRICT/UNIT"></textarea><br/><br/>
<div>ARRESTED_BY:</div><textarea type="text" name="ARRESTED_BY" placeholder="ARRESTED BY"></textarea><br/><br/>
<div>INTERROGATED_BY:</div><textarea type="text" name="INTERROGATED_BY" placeholder="INTERROGATED BY"></textarea><br/><br/>
<div>OTHERS_WHO_CAN_IDENTIFY:</div><textarea type="text" name="OTHERS_WHO_CAN_IDENTIFY" placeholder="IDENTIFIED BY"></textarea><br/><br/>
<div>CRIME_NO:</div><textarea type="text" name="CRIME_NO" required="required" placeholder="CRIME NO"></textarea><br/><br/>
<div>YEAR:</div><textarea type="text" name="YEAR" required="required" placeholder="YEAR"></textarea><br/><br/>
<div>SEC_OF_LAW:</div><textarea type="text" required="required" name="SEC_OF_LAW" placeholder="U/S"></textarea><br/><br/>
<div>POLICE_STATION:</div><textarea type="text" name="POLICE_STATION" required="required" placeholder="PS"></textarea><br/><br/>
<div>CRIME_HEAD1:</div><textarea type="text" name="CRIME_HEAD1" required="required" placeholder="PS"></textarea><br/><br/><input type="submit" value="INSERT" style="padding:15px;">
<br/><br/>

</form>

<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"forms");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY'];
$PERIOD_OF_OFFENCE=$_POST['PERIOD_OF_OFFENCE'];
$REGULAR_RESIDENCE=$_POST['REGULAR_RESIDENCE'];
$PREPARATION_OF_OFFENCE=$_POST['PREPARATION_OF_OFFENCE'];
$AFTER_OFFENCE=$_POST['AFTER_OFFENCE'];
$INDULGANCE_BEFORE_OFFENCE=$_POST['INDULGANCE_BEFORE_OFFENCE'];
$CRIME_HEAD=$_POST['CRIME_HEAD'];
$SUB_TYPE=$_POST['SUB_TYPE'];
$MO=$_POST['MO'];
$DATE_OF_ARREST=$_POST['DATE'];
$PLACE_OF_ARREST=$_POST['PLACE_OF_ARREST'];
$SUB_DIVISION=$_POST['SUB_DIVISION'];
$DISTRICT_OR_UNIT=$_POST['DISTRICT_OR_UNIT'];
$ARRESTED_BY=$_POST['ARRESTED_BY'];
$INTERROGATED_BY=$_POST['INTERROGATED_BY'];
$OTHERS_WHO_CAN_IDENTIFY=$_POST['OTHERS_WHO_CAN_IDENTIFY'];
$CRIME_NO=$_POST['CRIME_NO'];
$YEAR=$_POST['YEAR'];
$SEC_OF_LAW=$_POST['SEC_OF_LAW'];
$POLICE_STATION=$_POST['POLICE_STATION'];
$CRIME_HEAD1=$_POST['CRIME_HEAD1'];
$sql="insert into FORMS.dbo.OFFENCE_DETAILS (IRKEY, PERIOD_OF_OFFENCE, REGULAR_RESIDENCE, PREPARATION_OF_OFFENCE, AFTER_OFFENCE, 
INDULGANCE_BEFORE_OFFENCE, CRIME_HEAD, SUB_TYPE, MO, DATE_OF_ARREST, PLACE_OF_ARREST, 
SUB_DIVISION, DISTRICT_OR_UNIT, ARRESTED_BY, INTERROGATED_BY, OTHERS_WHO_CAN_IDENTIFY, 
CRIME_NO, YEAR, SEC_OF_LAW, POLICE_STATION, CRIME_HEAD1) values('$IRKEY','$PERIOD_OF_OFFENCE',
'$REGULAR_RESIDENCE','$PREPARATION_OF_OFFENCE','$AFTER_OFFENCE','$INDULGANCE_BEFORE_OFFENCE',
'$CRIME_HEAD','$SUB_TYPE','$MO','$DATE_OF_ARREST','$PLACE_OF_ARREST','$SUB_DIVISION','$DISTRICT_OR_UNIT',
'$ARRESTED_BY','$INTERROGATED_BY','$OTHERS_WHO_CAN_IDENTIFY','$CRIME_NO','$YEAR','$SEC_OF_LAW','$POLICE_STATION',$CRIME_HEAD1)";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=../view/offence_details.html");
?>
<?php endif; ?>
<?php layout_end(); ?>
