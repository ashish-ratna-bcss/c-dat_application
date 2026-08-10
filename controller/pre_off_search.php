<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<style type="text/css">

body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
</style>
</head>
<body bgcolor="#5195BA">
<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT distinct POLICE_STATION FROM OFFENCE_DETAILS";
$results = $db_handle->runQuery($query);
?>
<script src="../assets/js/jquerydynamic.js" type="text/javascript"></script>
<script>
function getps(val) {
	$.ajax({
	type: "POST",
	url: "get_crno.php",
	data:'POLICE_STATION='+val,
	success: function(data){
		$("#Crime-list").html(data);
		$("#YEAR").html(html);
	}
	});
}
function getyear(val1) {
	$.ajax({
	type: "POST",
	url: "get_year.php",
	data: 'CRIME_NO='+val1,
	success: function(data){
		 $("#YEAR").html(data);
	}
	});
}

</script>
<link rel="stylesheet" type="text/css" href="../assets/vendor/jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript">
$("document").ready(function() {
	$("#datepickerID").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	}) 

});
</script>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            </td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="862" height="158" align="center">
        <tr>
          <th height="25" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">PREVIOUS OFFENDERS IN TOWER DUMP</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="pre_off_search_twr.php">
            <p>              
		<label for="SUM" font face="verdana"> Police Station:</label>
              <select name="Police_station" id="Police_station" class"demoInputbox" onChange="getps(this.value);">
<option value="">Select PS</option>
<?php
foreach($results as $POLICE_STATION) {
?>
<option value="<?php echo $POLICE_STATION["POLICE_STATION"]; ?>"> <?php echo $POLICE_STATION["POLICE_STATION"]; ?> </option>
<?php
}
?>
</select>
<label for="SUM" font face="verdana"> Crime No:</label>
<select name="CRIME_NO" id="Crime-list" onChange="getyear(this.value);">
<option value="">Select Crime No</option>
</select>
<label for="SUM" font face="verdana"> Year:</label>
<select name="YEAR" id="YEAR">
<option value="">Select Year</option>
</select>
              Date: 
              <input type="text" name="OFF_DATE" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/>
                </br>
              Between Time HH:MM:SS
              <input name="hh1" style="width:40px;" type="number" id="number1"  min="00" max="23" value="00" required="required"/>
             :
              <input name="mm1" style="width:40px;" type="number" id="number2"  min="00" max="59" value="00" required="required"/>
             :
	     <input name="ss1" style="width:40px;" type="number" id="number3"   min="00" max="59" value="00" required="required"/>
              and  HH:MM:SS
              <input name="hh2" style="width:40px;" type="number" id="number4"  min="00" max="23" value="00" required="required"/>
             :
              <input name="mm2" style="width:40px;" type="number" id="number4"  min="00" max="59" value="00" required="required"/>
             :
	     <input  name="ss2"  style="width:40px;" type="number" id="number6" min="00" max="59" value="00" required="required"/>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
              </p>
          </form>
            <div align="justify">
              <table width="734" height="25">
                <tr>
                  <th width="40" scope="col">&nbsp;</th>
                  <th width="8" scope="col">&nbsp;</th>
                  <th width="79" scope="col">&nbsp;</th>
                  <th width="368" scope="col">&nbsp;</th>
                  </tr>
              </table>
            </div></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>
</body>
</html>
