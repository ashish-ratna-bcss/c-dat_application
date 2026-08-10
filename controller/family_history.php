<?php
// One page for both halves of this screen: the form, and the results.
// Was view/family_history.html (form) + controller/family_history.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<DOCHTML>
<html>
<head>
<title> FAMILY HISTORY FORM </title>
<link rel="stylesheet" type="text/css" href="../assets/vendor/jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
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
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
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
<meta charset="utf-8">
<link rel="stylesheet" href="mystyles.css">
<style type="test/css">
label{
float:left;
width:150px;
text-align=right;
}
</style>
</head>
<body bgcolor="#808000">
<table>
        <tr>
          <th width="1200" align="center" scope="col">FAMILY HISTORY</th>
        </tr>
</table>
<form action="family_history.php" Method="post">
<div>IRKEY:</div><textarea  type="text" name="IRKEY" placeholder="IRKEY" style="float:center;" required="required"></textarea><br/><br/>
<div>RELATIONSHIP:</div><textarea type="text"  name="RELATIONSHIP" placeholder="RELATION" required="required"></textarea><br/><br/>
<div>NAME:</div><textarea type="text" name="NAME" placeholder="NAME" required="required"></textarea><br/><br/>
<div>FATHER_OR_SPOUSE:</div><textarea type="text" name="FATHER_OR_SPOUSE" required="required" placeholder="FATHER/SPOUSE"></textarea><br/><br/>
<div>OCCUPATION:</div><textarea type="text" name="OCCUPATION" required="required" placeholder="OCCUPATION"></textarea><br/><br/>
<div>PHONE:</div><textarea type="text" name="PHONE" placeholder="PHONE"></textarea><br/><br/>
<div>AGE:</div><textarea type="text" name="AGE" placeholder="AGE"></textarea><br/><br/>
<div>CRIMINAL_BACKGROUND:</div><textarea type="text" name="CRIMINAL_BACKGROUND" placeholder="CRIMINAL BACKGROUND"></textarea><br/><br/>
<div>STATUS:</div><textarea type="text" name="STATUS" placeholder="STATUS LIKE ALIVE OR EXPIRED"></textarea><br/><br/>
<div>PRESENT_ADDRESS:</div><textarea type="text" name="PRESENT_ADDRESS" required="required" placeholder="PRESENT ADDRESS"></textarea><br/><br/>
<div>PERMANENT_ADDRESS:</div><textarea type="text" name="PERMANENT_ADDRESS" placeholder="PERMANENT ADDRESS"></textarea><br/><br/>
<input type="submit" value="insert">
     			<br/><br/>

</form>

<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"forms");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY']; 
$RELATIONSHIP=$_POST['RELATIONSHIP']; 
$NAME=$_POST['NAME'];
$FATHER_OR_SPOUSE =$_POST['FATHER_OR_SPOUSE']; 
$OCCUPATION =$_POST['OCCUPATION']; 
$PHONE=$_POST['PHONE']; 
$AGE =$_POST['AGE']; 
$CRIMINAL_BACKGROUND =$_POST['CRIMINAL_BACKGROUND']; 
$STATUS =$_POST['STATUS'];
$PRESENT_ADDRESS =$_POST['PRESENT_ADDRESS'];
$PERMANENT_ADDRESS =$_POST['PERMANENT_ADDRESS'];
$sql="insert into FORMS.dbo.FAMILY_HISTORY 
(IRKEY,RELATIONSHIP, NAME, FATHER_OR_SPOUSE, OCCUPATION, PHONE, AGE, 
CRIMINAL_BACKGROUND, STATUS, PRESENT_ADDRESS, PERMANENT_ADDRESS
)values('$IRKEY','$RELATIONSHIP','$NAME','$FATHER_OR_SPOUSE','$OCCUPATION',
'$PHONE','$AGE','$CRIMINAL_BACKGROUND','$STATUS','$PRESENT_ADDRESS','$PERMANENT_ADDRESS')
";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=../view/family_history.html");
?>
<?php endif; ?>
</body>
</html>