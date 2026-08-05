<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_POST["POLICE_STATION"])) {
	$query ="SELECT DISTINCT CRIME_NO FROM TWRMDB..OFFENCE_DETAILS WHERE POLICE_STATION = '" . $_POST["POLICE_STATION"] . "'";
	$results = $db_handle->runQuery($query);
?>
	<option value="">Select State</option>
<?php
	foreach($results as $state) {
?>
	<option value="<?php echo $state["CRIME_NO"]; ?>"><?php echo $state["CRIME_NO"]; ?></option>
<?php
	}
}
?>