
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("New Contacts");
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
      <table width="862" height="160" align="center">
        <tr>
          <th height="27" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">NEW CONTACTS SUMMARY OF MOBILE NUMBER</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="sum_new_no.php">
            <p>
              <label for="SUM" font face="verdana"> Mobile No:</label>
              <input type="text" name="PHONE_NO" id="SUM" placeholder="Enter Mobile No" required="required"/>
              New Contacts From: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="From Date" required="required"/>
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
                  <th width="90" scope="col">&nbsp;</th>
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
