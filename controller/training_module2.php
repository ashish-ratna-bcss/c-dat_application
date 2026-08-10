<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Training Module2");
?>

      <table width="625" height="124">
<br>
&nbsp;
        <tr>
          <th height="26" bgcolor="#00008B" class="CDAT" scope="col"><font color="white">EMPLOYEE SEARCH</font></th>
        </tr>
        <tr>
              </table>
    </tr>
  </table>
<form id="form1" name="form1" method="post" action="training_module1.php">
                 <th width="555" height="90" bgcolor="#F5DEB3" class="CDAT" scope="col">

Select search criteria : <select type="text" name="EMPLOYEE_SEARCH">
<option value=""></option>
<option value="EMPLOYEE_ID">EMPLOYEE ID</option>
<option value="GENERAL_NO">GENERAL NO</option>
<option value="NAME">NAME</option>
</select>
            <input type="text" name="EMPLOYEE_SEARCH_NO" id="CAF" placeholder="Emp Search" required="required"/>
<br><br>
Select Rank : <select type="text" name="EMPLOYEE_SEARCH_RANK">
<option value=""></option>
<option value="INSPECTOR">INSPECTOR</option>
<option value="SI">SI</option>
<option value="ASI">ASI</option>
<option value="HC">HC</option>
<option value="PC">PC</option>
<option value="HG">HG</option>
</select>

            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
 <table width="625" height="347">
          <tr>
            <td height="310" align="centre" valign="top"><div align="center"><img src="../assets/images/training_db1.gif" width="600" height="350" /></div></td>
          </tr>

       </table>

</div>

<?php layout_end(); ?>
