<?php
require_once __DIR__ . '/bootstrap.php';
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_POST["CRIME_NO"])) {
	$query ="SELECT DISTINCT YEAR FROM offence_details WHERE CRIME_NO = '".$_POST["CRIME_NO"]."'";
	$results = $db_handle->runQuery($query);
?>
	<option value="">Select Year</option>
<?php
foreach($results as $YEAR) {
?>
	<option value="<?php echo $YEAR["YEAR"]; ?>"><?php echo $YEAR["YEAR"]; ?></option>
<?php
}
}
?>
