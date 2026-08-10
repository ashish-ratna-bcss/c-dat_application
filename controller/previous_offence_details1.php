<?php
// One page for both halves of this screen: the form, and the results.
// Was view/previous_offence_details1.html (form) + controller/previous_offence_details1.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<DOCHTML>
<html>
<head>
<title> PREVIOUS OFFENCE DETAILS1 </title>
<link rel="stylesheet" type="text/css" href="../assets/vendor/jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript">
$("document").ready(function() {
	$("#datepickerID").datepicker({dateFormat: "yyyy-mm-dd",
		changeYear: true,
		changeMonth: true,
	}) 
	$("#datepickerID1").datepicker({dateFormat: "yyyy-mm-dd",
		changeYear: true,
		changeMonth: true,
	})   
	
});
</script>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
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
</style>
</head>
<body bgcolor="#808000">
<table>
   <tr>
<th width="1126" align="center" scope="col">PREVIOUS OFFENCE DETAILS1</th>
   </tr>
</table>
<form action="previous_offence_details1.php" Method="post">
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
<div>CONFESSED_DATE_OF_RELEASE:</div><input type="TEXT" name="DATE" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/><br/><br/>
<input type="submit" value="INSERT" style="padding:15px;"><br/><br/>
</form>

<?php if ($__submitted): ?>
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
$CONFESSED_DATE_OF_RELEASE=$_POST['DATE']; 
$sql="insert into FORMS.dbo.PREVIOUS_OFFENCE_DETAILS1 
(IRKEY, DISTRICT, CONFESSED_POLICE_STATION, CONFESSED_CRIME_NO, CONFESSED_YEAR, 
CONFESSED_SEC_OF_LAW,CONFESSED_DATE_OF_ARREST,ASSOCIATES, PROPERTY_STOLEN, PROPERTY_RECOVERED, REMARKS,CRIME_NO, YEAR,POLICE_STATION,CONFESSED_DATE_OF_RELEASE)
values('$IRKEY','$DISTRICT','$CONFESSED_POLICE_STATION','$CONFESSED_CRIME_NO','$CONFESSED_YEAR','$CONFESSED_SEC_OF_LAW','$CONFESSED_DATE_OF_ARREST','$ASSOCIATES','$PROPERTY_STOLEN','$PROPERTY_RECOVERED','$REMARKS',
'$CRIME_NO','$YEAR','$POLICE_STATION','$CONFESSED_DATE_OF_RELEASE')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=../view/previous_offence_details1.html");
?>
<?php endif; ?>
</body>
</html>