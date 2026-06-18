<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<table>
        <tr>
          <th width="1126" align="center" scope="col"><font size='5' color='#AED1F1'>INFORMATION ON CATEGORY OF MIGRANT LABOURS</font></th>
        </tr>
</table>
<title> CATEGORY OF MIGRANT LABOURS FORM</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript">
$("document").ready(function() {
	$("#datepickerID").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	}) 
	$("#datepickerID1").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	})    
	
});
</script>
<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="select distinct PS POLICE_STATION from MIGRANT_LABOURS_FORM..PS_NAMES";
$results = $db_handle->runQuery($query);
?>
<script src="dynamicdependentbox/jquerydynamic.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<style>
input[type=text], select {
    width: 25%;
    padding: 3px 10px;
    margin: 4px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 2px;
    box-sizing: border-box;
}

input[type=submit] {
    width: 15%;
    background-color: ORANGE;
    color: white;
    padding: 4px 15px;
    margin: 4px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 25px;
}

div {
    border-radius: 5px;
    background-color: #f2f2f2;
    padding: 5px;
    width: 40%;
    border-radius: 4px;
    float:left;
    width:200px;
    text-align: left;
        }
       textarea{
       width: 25%;
       height: 25px;
       }
</style>
</head>

<body bgcolor="#808000">
<table>
        <tr>
          <th width="1126" align="center" scope="col">MIGRANT LABOUR FORM</th>
        </tr>
</table>

<form action="MIGRANT_LABOURS_2.php" Method="post">
<div>POLICE_STATION:</div>
<select name="POLICE_STATION" id="Police_station" class="demoInputbox" onChange="getdiv(this.value);" required>
<option value="" required>Select PS</option>
<?php
foreach($results as $POLICE_STATION) {
?>
<option value="<?php echo $POLICE_STATION["POLICE_STATION"]; ?>"> <?php echo $POLICE_STATION["POLICE_STATION"]; ?> </option>
<?php
}
?>
</select>
<br/><br/>

<div>NAME:</div>
<textarea type="text" name="NAME" minlength="4" max="100" required></textarea>
<br/><br/>

<div>FATHER_NAME:</div>
<textarea type="text" name="FATHER_NAME" ></textarea>
<br/><br/>

<div>AGE:</div>
<textarea type="text" name="AGE" ></textarea>
<br/><br/>


<table>
        <tr>
          <th width="1126" align="LEFT" scope="col">MIGRANT LABOUR ADDRESS STATUS</th>
        </tr>
</table>
<div>NATIVE_STATE:</div>
<SELECT name="NATIVE_STATE" required>
<option value="" >Select State</option>
<OPTION VALUE="Andaman and Nicobar Islands">Andaman and Nicobar Islands</OPTION>
<OPTION VALUE="Andhra Pradesh">Andhra Pradesh</OPTION>
<OPTION VALUE="Arunachal Pradesh">Arunachal Pradesh</OPTION>
<OPTION VALUE="Assam">Assam</OPTION>
<OPTION VALUE="Bihar">Bihar</OPTION>
<OPTION VALUE="Chandigarh">Chandigarh</OPTION>
<OPTION VALUE="Chhattisgarh">Chhattisgarh</OPTION>
<OPTION VALUE="Dadra and Nagar Haveli">Dadra and Nagar Haveli</OPTION>
<OPTION VALUE=" Daman and Diu"> Daman and Diu</OPTION>
<OPTION VALUE="Delhi">Delhi</OPTION>
<OPTION VALUE="Goa">Goa</OPTION>
<OPTION VALUE="Gujarat">Gujarat</OPTION>
<OPTION VALUE="Haryana">Haryana</OPTION>
<OPTION VALUE="Himachal Pradesh">Himachal Pradesh</OPTION>
<OPTION VALUE="Jammu and Kashmir">Jammu and Kashmir</OPTION>
<OPTION VALUE="Jharkhand">Jharkhand</OPTION>
<OPTION VALUE="Karnataka">Karnataka</OPTION>
<OPTION VALUE="Kerala">Kerala</OPTION>
<OPTION VALUE="Ladakh">Ladakh</OPTION>
<OPTION VALUE="Madhya Pradesh">Madhya Pradesh</OPTION>
<OPTION VALUE="Maharashtra">Maharashtra</OPTION>
<OPTION VALUE="Manipur">Manipur</OPTION>
<OPTION VALUE="Meghalaya">Meghalaya</OPTION>
<OPTION VALUE="Mizoram">Mizoram</OPTION>
<OPTION VALUE="Odisha">Odisha</OPTION>
<OPTION VALUE="Puducherry">Puducherry</OPTION>
<OPTION VALUE="Punjab">Punjab</OPTION>
<OPTION VALUE="Rajasthan">Rajasthan</OPTION>
<OPTION VALUE="Tamil Nadu">Tamil Nadu</OPTION>
<OPTION VALUE="Telangana">Telangana</OPTION>
<OPTION VALUE="Tripura">Tripura</OPTION>
<OPTION VALUE="Uttar Pradesh">Uttar Pradesh</OPTION>
<OPTION VALUE="Uttarakhand">Uttarakhand</OPTION>
<OPTION VALUE="West Bengal">West Bengal</OPTION>
</SELECT>

<br/><br/>



<div>NATIVE_DISTRICT:</div>
<textarea type="text" name="NATIVE_DISTRICT" ></textarea>
<br/><br/>



<div>MOBILE_NO:</div>
<input type="number" name="PHONE" required> 
<br/><br/>


<table>
        <tr>
          <th width="1126" align="LEFT" scope="col">CATEGORY OF MIGRANT WORKERS STATUS</th>
        </tr>
</table>
<div>WORK_STATUS:</div>
<select name="WORK_STATUS" required>
              <option value="" >Select work status</option>
<option value="YES">YES</option>
<option value="NO">NO</option>
</select>
<br/><br/>



<div>PART_OF_LABOUR_CAMP:</div>
<select name="PART_OF_LABOUR_CAMP" required>
              <option value="" >Select Camp status</option>
<option value="YES">YES</option>
<option value="NO">NO</option>
</select>
<br/><br/>



<div>URGENT:</div>
<select name="URGENT" required>
              <option value="" >Select Problem case</option>
<option value="YES">YES</option>
<option value="NO">NO</option>
</select>
<br/><br/>



<div>PROBLEM_CASES:</div>
<select name="PROBLEM_CASES" required>
              <option value="" >Select Problem case</option>
<option value="YES">YES</option>
<option value="NO">NO</option>
</select>
<br/><br/>



<div>REMARKS:</div>
<textarea type="text" name="REMARKS"></textarea>
<br/><br/>



<div>DATE:</div>
<input type="text" name="DATE1" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/>
<br/><br/>


<input type="submit" value="insert">
<br/><br/>

 </form>
</body>
</html>