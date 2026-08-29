<?php
require_once __DIR__ . '/bootstrap.php';
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_POST["POLICE_STATION"])) {
	$query ="SELECT DISTINCT CRIME_NO FROM offence_details WHERE POLICE_STATION = '".$_POST["POLICE_STATION"]."'";
	$results = $db_handle->runQuery($query);
?>
	<option value="">Select Crime No</option>
<?php
foreach($results as $CRIME_NO) {
?>
	<option value="<?php echo $CRIME_NO["CRIME_NO"]; ?>"><?php echo $CRIME_NO["CRIME_NO"]; ?></option>
<?php
}
}
?>
