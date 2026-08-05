<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_POST["state_id"]))  {
	$query ="SELECT * FROM irforms..cities WHERE state_id = '" . $_POST["state_id"] . "'";
	$results = $db_handle->runQuery($query);
?>
	<option value="">Select city</option>
<?php
	foreach($results as $city) {
?>
	<option value="<?php echo $city["city_id"]; ?>"><?php echo $city["city_name"]; ?></option>
<?php
	}
}
?>