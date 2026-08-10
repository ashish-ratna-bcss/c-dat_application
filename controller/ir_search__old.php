<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<link href="../assets/spry/sprymenubarvertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.FONT {
	color: #CFF;
	font-size: 24px;
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
<tr>
  <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            <li><a href="home_ir.php">Home</a>              </li>
            <li><a href="irreport.php">IRREPORT</a></li>
            <li><a href="family_history.php">FAMILY HISTORY</a></li>
            <li><a href="home_ir.php" class="MenuBarItemSubmenu">CRIME DETAILS</a>
             <ul>
                <li><a href="offence_details.php">OFFENCE DETAILS</a></li>
                <li><a href="previous_offence_details.php">PREVIOUS OFFENCE DETAILS</a></li>
              </ul>
              </li>
            <li><a href="local_contacts.php">LOCAL CONTACTS</a></li>
            <li><a href="relation_with_other_associates_and_gangs.php">GANGS/ASSOCIATES</a></li>
            <li><a href="disposal_of_property.php">PROPERTY DETAILS</a></li>
            <li><a href="brief_facts.php">BRIEF FACTS</a></li>
            <li><a href="image_list.php">IMAGE</a></li>
          </ul></td>
        </tr>
        <table width="800" height="100" align=center>
        <tr>
          <th height="27" bgcolor="#A9D1F5" class="CDAT" scope="col">OFFENDER IR SEARCH BY NAME</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="ir_search.php">
                 <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col"> NAME OF THE OFFENDER:            <label for="textfield"></label>
            <input type="text" name="NAME" id="NAME" placeholder="Enter NAME" required="required"/>
	CRIME HEAD:            	<label for="textfield"></label>
            <input type="text" name="CRIME_HEAD" id="CRIME_HEAD" placeholder="Enter CRIME HEAD" required="required"/>
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p></td>
          </tr>
        </table> 
</div>
  <script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
var MenuBar2 = new Spry.Widget.MenuBar("MenuBar2", {imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>
</body>
</html>
