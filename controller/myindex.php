<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT * FROM country";
$results = $db_handle->runQuery($query);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Myindex");
?>

<div class="frmDronpDown">
<div class="row">
<label>Country:</label><br/>
<select name="country" id="country-list" class="demoInputBox" onChange="getState(this.value);">
<option value="">Select Country</option>
<?php
foreach($results as $country) {
?>
<option value="<?php echo $country["id"]; ?>"><?php echo $country["country_name"]; ?></option>
<?php
}
?>
</select>
</div>
<div class="row">
<label>State:</label><br/>
<select name="state" id="state-list" class="demoInputBox">
<option value="">Select State</option>
</select>
</div>
</div>
<?php layout_end(); ?>
