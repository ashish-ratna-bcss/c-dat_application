
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Phones used in IMEI");
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="center" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1305" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg">
                </td>
        </tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p>
      <p class="MenuBarItemHover">&nbsp;</p>
      <table width="625" height="99">
        <tr>
          <th height="26" bgcolor="#A9D1F5" class="CDAT" scope="col">IMEI SEARCH</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="imei_search.php">
                 <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col">IMEI NUMBER:            <label for="textfield"></label>
            <input type="text" name="IMEI_NO" id="IMEI" placeholder="Enter IMEI No" required="required"/>
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>
<?php layout_end(); ?>
