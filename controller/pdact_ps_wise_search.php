<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("PDACT Ps Wise Search");
?>
<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT DISTINCT UPPER(LTRIM(RTRIM(PD_ACT_PS))) PD_ACT_PS FROM PDACT..PDACT_MAIN_TABLE";
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
          <th height="31" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">PDACT SEARCH BY POLICE STATION</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="pdact_ps_wise_search_php.php">
                     
             PDACT_POLICE_STATION: <select name="PDACT_PS">
<option value="">Select Police Station</option>
<?php
foreach($results as $PDACT_PS) {
?>
<option value="<?php echo $PDACT_PS["PD_ACT_PS"]; ?>"> <?php echo $PDACT_PS["PD_ACT_PS"]; ?> </option>
<?php
}
?>
</select>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />     
          </form></th>
        </tr>
     
 
<?php layout_end(); ?>
