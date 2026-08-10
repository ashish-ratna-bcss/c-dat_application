<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TRAINING MODULE</title>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarVertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.aa{
width: 300px;
height: 300px;
background-color: rgba(0,0,0,0.4);
margin:0 auto;
margin-top:40px;
padding-top:10px;
padding-left: 50px;
border-radius: 15px;
-webkit-border-radius: 15px;
-moz-border-radius:15px;
color:white;
font-weight:bolder;
box-shadow: inset -4px -4px rgba(0,0,0,0.4);
font-size:18px;
}
.aa input[type="text"]{
width:200px;
height:35px;
border:0;
border-radius:5px;
-webkit-border-radius:5px;
-o- border-radius:5px;
-moz-border-radius:5px;
padding-left: 10px;
}
.aa input[type="password"]{
width:200px;
height:35px;
border:0;
border-radius:5px;
-webkit-border-radius:5px;
-o- border-radius:5px;
-moz-border-radius:5px;
}
.aa input[type="submit"]{
width:200px;
height:35px;
border:0;
border-radius:5px;
-webkit-border-radius:5px;
-o- border-radius:5px;
-moz-border-radius:5px;
background-color: orange;
font-weight: bolder;
}
</style>
</head>
<body background="IMAGES/emp.PNG">
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
            <td height="310" align="centre" valign="top"><div align="center"><img src="IMAGES/TRAINING_DB1.GIF" width="600" height="350" /></div></td>
          </tr>

       </table>

</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
</html>
