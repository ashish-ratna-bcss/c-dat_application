<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Rowdy Sheeter By PS");
?>
<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT DISTINCT UPPER(LTRIM(RTRIM(POLICE_STATION))) POLICE_STATION FROM CDATDUPL..ROWDY_SHEETER_DATA1";
$results = $db_handle->runQuery($query);
?>
<div align="center">
  <table width="1323" height="100" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
        </tr>

  </table>
      <p>&nbsp;</p>
<table width="1021" height="163" align="center">
        <tr>
          <th height="31" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">ROWDYSHEET SEARCH BY POLICE STATION</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="rowdysheeter_ps_wise_search_php.php">
                     
             POLICE_STATION: <select name="POLICE_STATION">
<option value="">Select Police Station</option>
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
        </tr>
     
 
<?php layout_end(); ?>
