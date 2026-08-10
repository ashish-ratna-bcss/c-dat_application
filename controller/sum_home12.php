
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Summary Home12");
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="442" height="121" align="center">
        <tr>
          <th height="29" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">SUMMARY OF MOBILE NUMBER</th>
        </tr>
        <tr>
          <th align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="SUM1.php">
            <label for="SUM" font face="verdana">Mobile No:</label>
              <input type="text" name="PHONE_NO" id="SUM" placeholder="Enter Mobile No" required="required"/>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
          </form></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
  </table>
</div>
<?php layout_end(); ?>
