<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("JRMS Search By Dates");
?>
<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT distinct HEADOFCRIME FROM JRMS..JRMS_TOTAL_2012_TO_2017 
WHERE HEADOFCRIME!='' ORDER BY HEADOFCRIME";
$results = $db_handle->runQuery($query);
?>
<div align="center">
  <table width="1323" height="100" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
        </tr>

<table width="1021" height="163" align="center">
        <tr>
          <th height="31" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">JAIL RELEASE BETWEEN DATES</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="jrms_search.php">
                      Date From: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="yyyy/mm/dd" required="required"/>
              To:
              <input type="text" name="TO_DT" id="datepickerID1" size="10" placeholder="yyyy/mm/dd" required="required"/>
              CrimeHead: <select name="CRIMEHEAD">
<option value="">Select CrimeHead</option>
<?php
foreach($results as $HEADOFCRIME) {
?>
<option value="<?php echo $HEADOFCRIME["HEADOFCRIME"]; ?>"> <?php echo $HEADOFCRIME["HEADOFCRIME"]; ?> </option>
<?php
}
?>
</select>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />     
          </form></th>
        </tr>
     

 </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
<?php layout_end(); ?>
