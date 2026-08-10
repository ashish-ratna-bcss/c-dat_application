
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Bulk Irkey");
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
      <table width="907" height="168">
        <tr>
          <th height="27" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" >BULK ADDRESSES</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="bulk_irsearch_irkey1.php">
                 <th width="764" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" > BULK IRKEY SEARCH:            <style>
label textarea{
font: normal 15px courier;
vertical-align: middle;
}
</style>
<label> <textarea rows=3 cols=50 name='IRKEY'  id="IRKEY" placeholder="Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx" required="required"></textarea></label>
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>
<?php layout_end(); ?>
