<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Cis Data Name Search");
?>

<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT distinct DISTRICT FROM CIS_DATA_BASE.DBO.CIS_COMPLETE_DATA";
$results = $db_handle->runQuery($query);
?>
<script src="../assets/js/jquerydynamic.js" type="text/javascript"></script>
<script>
function GETPS(val) {
	$.ajax({
	type: "POST",
	url: "get_ps.php",
	data:'DISTRICT='+val,
	success: function(data){
		$("#POLICE_STATION").html(data);
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
          <th height="25" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">ACCUSED SEARCH IN CIS DATA</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="cis_data_name_search_php.php">
            <p>
              <label for="SUM" font face="verdana"> NAME:</label>
              <input type="text" name="NAME" id="NAME" placeholder="Enter Name" required="required"/>
		<label for="SUM" font face="verdana"> DISTRICT:</label>
              <select name="DISTRICT" id="DISTRICT" class="demoInputbox" onChange="GETPS(this.value);">
<option value="">Select DISTRICT</option>
<?php
foreach($results as $DISTRICT) {
?>
<option value="<?php echo $DISTRICT["DISTRICT"]; ?>"> <?php echo $DISTRICT["DISTRICT"]; ?> </option>
<?php
}
?>
</select>
<label for="SUM" font face="verdana"> POLICE_STATION:</label>
<select name="POLICE_STATION" id="POLICE_STATION">
<option value="">Select POLICE_STATION</option>
</select>

              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />     
             </form></th>
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

<?php layout_end(); ?>
