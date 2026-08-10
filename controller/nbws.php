<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Nbws");
?>

<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="select DISTINCT POLICE_STATION  from IRFORMS..VERIFY_REPORT_IR A
where stage_of_case in ('ISSUE BW/NBW','PROCLAMATION US 82-83 CRPC','NBW','N.B.W._Ready','Issue NBW','Awaiting Warrant',
'Awaiting Warrant ( DORMANT CASE )','NBW PENDING','PROCLAMATION','Re issue NBW')
ORDER BY POLICE_STATION";
$results = $db_handle->runQuery($query);
?>
<script src="../assets/js/jquerydynamic.js" type="text/javascript"></script>
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
          <th height="25" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">NBWS/WARRANT SEARCH</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="nbws.php">
            <p>
            <label for="SUM" font face="verdana"> POLICE_STATION:</label>
              <select name="POLICE_STATION" id="POLICE_STATION" class="demoInputbox" onChange="GETPS(this.value);">
<option value="">Select POLICE_STATION</option>
<?php
foreach($results as $POLICE_STATION) {
?>
<option value="<?php echo $POLICE_STATION["POLICE_STATION"]; ?>"> <?php echo $POLICE_STATION["POLICE_STATION"]; ?> </option>
<?php
}
?>
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
