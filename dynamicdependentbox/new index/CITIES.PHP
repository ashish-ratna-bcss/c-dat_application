<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_POST["country_id"])) {
	$query ="SELECT * FROM states WHERE country_ID = '" . $_POST["country_id"] . "'";
	$results = $db_handle->runQuery($query);
?>
	<option value="">Select State</option>
<?php
	foreach($results as $state) {
?>
	<option value="<?php echo $state["state_id"]; ?>"><?php echo $state["state_name"]; ?></option>
<?php
	}
}
?>