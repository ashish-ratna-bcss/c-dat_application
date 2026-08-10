<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_POST["DISTRICT"])) {
	$query ="SELECT DISTINCT POLICE_STATION FROM CIS_DATA_BASE..CIS_COMPLETE_DATA WHERE DISTRICT= '".$_POST["DISTRICT"]."'";
	$results = $db_handle->runQuery($query);
?>
	<option value="">Select PS</option>
<?php
foreach($results as $POLICE_STATION) {
?>
	<option value="<?php echo $POLICE_STATION["POLICE_STATION"]; ?>"><?php echo $POLICE_STATION["POLICE_STATION"]; ?></option>
<?php
}
}
?>