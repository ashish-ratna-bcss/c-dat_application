<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT DISTINCT POLICE_STATION FROM OFFENCE_DETAILS";
$results = $db_handle->runQuery($query);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Dump Analysis");
?>

<div class="frmDronpDown">
<div class="row">
<label>Police Station:</label><br/>
<select name="POLICE_STATION" id="POLICE_STATION" class="demoInputBox" onChange="getState(this.value);">
<option value="">Select PS</option>
<?php
foreach($results as $POLICE_STATION) {
?>
<option value="<?php echo $POLICE_STATION["POLICE_STATION"]; ?>"> <?php echo $POLICE_STATION["POLICE_STATION"]; ?> </option>
<?php
}
?>
</select>
</div>
<div class="row">
<label>Crime No:</label><br/>
<select name="CRIME_NO" id="Crime-list" class="demoInputBox" >
<option value="">Select crime</option>
<option value="Crime-list">Select year</option>
</select>
</div>
<div class="row">
<label>year:</label><br/>
<select name="year" id="year-list" class="demoInputBox" >
<option value="">Select year</option>
<option value="year-list">Select year</option>
</select>
</div>
</div>
<?php layout_end(); ?>
