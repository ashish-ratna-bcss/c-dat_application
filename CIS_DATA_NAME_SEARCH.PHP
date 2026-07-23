<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
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
$query ="SELECT distinct DISTRICT FROM CIS_DATA_BASE.DBO.CIS_COMPLETE_DATA";
$results = $db_handle->runQuery($query);
?>
<script src="jquerydynamic.js" type="text/javascript"></script>
<script>
function GETPS(val) {
	$.ajax({
	type: "POST",
	url: "GET_PS.php",
	data:'DISTRICT='+val,
	success: function(data){
		$("#POLICE_STATION").html(data);
			}
	});
}


</script>
<link rel="stylesheet" type="text/css" href="jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
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
          <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/TOPBORDER.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            </td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="862" height="158" align="center">
        <tr>
          <th height="25" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">ACCUSED SEARCH IN CIS DATA</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col"><form id="form1" name="form1" method="post" action="CIS_DATA_NAME_SEARCH_PHP.PHP">
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
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
</html>
