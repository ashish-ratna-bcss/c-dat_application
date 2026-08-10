
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Common Cnts1");
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
      <table width="972" height="165">
        <tr>
          <th height="29" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" >COMMON CONTACTS OF MOBILE NUMBERS</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="common_cnts.php">
                 <th width="764" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" > MOBILE NUMBERS:            <style>
label textarea{
font: normal 15px courier;
vertical-align: middle;
}
</style>
<label> <textarea rows=3 cols=50 name='PHONE_NO'  id="COMMON_NOS" placeholder="Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx" required="required"></textarea></label>
<select name="STRING">
<option value=">" SELECTED>></option>
<option value="=">=</option></b>
</select> 
<b>  MORE THAN NO : <select name="NO">
<option value="1" SELECTED>1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
<option value="7">7</option>
<option value="8">8</option>
<option value="9">9</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">14</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option></b>
</select> 
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>
<?php layout_end(); ?>
