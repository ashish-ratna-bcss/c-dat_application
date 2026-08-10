<?php
// One page for both halves of this screen: the form, and the results.
// Was view/suspect_search.htm (form) + controller/suspect_search.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Suspect Search");
?>
<?php
require_once("../controller/dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT * FROM OFFENCE_DETAILS";
$results = $db_handle->runQuery($query);
?>
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
          <th height="25" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">SUSPECT NUMBER SEARCH IN TOWER DUMP</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="suspect_search.php">
            <p>
              <label for="SUM" font face="verdana"> Mobile No:</label>
              <input type="text" name="PHONE_NO" id="SUM" placeholder="Enter Mobile No" required="required"/>
		<label for="SUM" font face="verdana"> Police Station:</label>
              <select name="Police_station" id="police_station" class"demoInputbox" onChange="getvalues(this.value);">
<option value="">Select PS</option>
<?php 
foreach($results as $Police_station) {
?>
<option value="<?php echo $Police_station["id"]; ?>"><?php echo $Police_station["Police_station"]; ?></option>
<?php
}
?>
</select>
              Date: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/>
                </br>
              Between Time HH:MM:SS
              <input name="hh1" style="width:40px;" type="number" id="number1"  min="00" max="23" value="00" required="required"/>
             :
              <input name="mm1" style="width:40px;" type="number" id="number2"  min="00" max="59" value="00" required="required"/>
             :
	     <input name="ss1" style="width:40px;" type="number" id="number3"  min="00" max="59" value="00" required="required"/>
              and  HH:MM:SS
              <input name="hh2" style="width:40px;" type="number" id="number4"  min="00" max="23" value="00" required="required"/>
             :
              <input name="mm2" style="width:40px;" type="number" id="number4"  min="00" max="59" value="00" required="required"/>
             :
	     <input  name="ss2"  style="width:40px;" type="number" id="number6"  min="00" max="59" value="00" required="required"/>
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


<?php if ($__submitted): ?>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>


<style type="text/css">

body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
</style>


<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="select distinct POLICE_STATION from FORMS240719..ABSTRACT_JAN_TO_JULY_TILL_DATE_TO_CHECK";
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
          <th height="25" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">SUSPECT NUMBER SEARCH IN TOWER DUMP</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">
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

<?php endif; ?>
<?php layout_end(); ?>
