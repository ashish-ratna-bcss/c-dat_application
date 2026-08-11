<?php
// One page for both halves of this screen: the form, and the insert.
// Was BRIEF_FACTS.html (form) + brief_facts.php (handler). The de-duplication
// kept only the handler, so opening this URL ran the INSERT against an empty
// $_POST and wrote a blank row.
// POST only: an insert must never run just because someone opened the page.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<html>
<head>
<title> BRIEF FACTS FORM </title>
<meta charset="utf-8">
<link href="SpryAssets/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
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
.result{
clear:both;
width:auto;
float:none;
display:inline-block;
background-color:#921215;
color:#fff;
font:bold 16px verdana;
padding:8px 14px;
}
</style>
</head>
<body bgcolor="#80800">
<table>
        <tr>
          <th width="1126" align="center" scope="col">BRIEF FACTS</th>
        </tr>
</table>
<form action="brief_facts.php" Method="post">
<div>IRKEY:</div><input  type="text" name="IRKEY" placeholder="IRKEY" style="float:center;">
     <br/><br/>
<div>BRIEF_FACTS1:</div><textarea type="text"  name="BRIEF_FACTS1" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<div>BRIEF_FACTS2:</div><textarea type="text" name="BRIEF_FACTS2" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<div>BRIEF_FACTS3:</div><textarea type="TEXT" name="BRIEF_FACTS3" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<input type="submit" value="INSERT" style="padding:15px;">
    <br/><br/>

</form>

<?php if ($__submitted): ?>
<p class="result">
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"forms");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY'];
$BRIEF_FACTS1=$_POST['BRIEF_FACTS1'];
$BRIEF_FACTS2=$_POST['BRIEF_FACTS2'];
$BRIEF_FACTS3=$_POST['BRIEF_FACTS3'];
$sql="insert into FORMS.dbo.BRIEF_FACTS
(IRKEY, BRIEF_FACTS1, BRIEF_FACTS2, BRIEF_FACTS3)
values('$IRKEY', '$BRIEF_FACTS1', '$BRIEF_FACTS2', '$BRIEF_FACTS3')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
// The old "refresh:30" bounce back to the form is gone: the form is on this
// page now, and header() after output only raises a warning.
?>
</p>
<?php endif; ?>
</body>
</html>
