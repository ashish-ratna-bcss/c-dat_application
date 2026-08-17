<?php
require_once __DIR__ . '/bootstrap.php';
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_POST["POLICE_STATION"])) {
$query ="SELECT DISTINCT DIVISION FROM MIGRANT_LABOURS_FORM..PS_NAMES WHERE ZONE='".$_POST["POLICE_STATION"]."'";
	$results = $db_handle->runQuery($query);
?>
	<option value="">Select DIVISION</option>
<?php
foreach($results as $DIVISION) {
?>
	<option value="<?php echo $DIVISION["DIVISION"]; ?>"><?php echo $DIVISION["DIVISION"]; ?></option>
<?php
}
}
?>
