<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT DISTINCT POLICE_STATION FROM OFFENCE_DETAILS";
$results = $db_handle->runQuery($query);
?>
<html>
<head>
<TITLE>jQuery Dependent DropDown List - Countries and States</TITLE>
<head>
<style>
body{width:610px;font-family:calibri;}
.frmDronpDown {border: 1px solid #7ddaff;background-color:#C8EEFD;margin: 2px 0px;padding:40px;border-radius:4px;}
.demoInputBox {padding: 10px;border: #bdbdbd 1px solid;border-radius: 4px;background-color: #FFF;width: 50%;}
.row{padding-bottom:15px;}
</style>
<script src="jquerydynamic.js" type="text/javascript"></script>
<script>
function getState(val) {
	$.ajax({
	type: "POST",
	url: "get_state.php",
	data: 'POLICE_STATION='datastring,
	success: function(data){
		$("#State-list").html(data);
	}
	});
}

function selectCountry(val) {
$("#search-box").val(val);
$("#suggesstion-box").hide();
}
</script>
</head>
<body>
<div class="frmDronpDown">
<div class="row">
<label>Police Station:</label><br/>
<select name="POLICE_STATION" id="POLICE_STATION" class="demoInputBox" onChange="getState(this.value);">
<option value="">Select PS</option>
<?php
foreach($results as $POLICE_STATION) {
?>
<option value="<?php echo $POLICE_STATION["id"]; ?>"> <?php echo $POLICE_STATION["POLICE_STATION"]; ?> </option>
<?php
}
?>
</select>
</div>
<div class="row">
<label>Crime No:</label><br/>
<select name="CRIME_NO" id="State-list" class="demoInputBox">
<option value="State-list">Select Crime No</option>
</select>
</div>
</div>
</body>
</html>