<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<style type="text/css">


select {
width: 300px;
}


</style>

<script src="jquerydynamic.js" type="text/javascript"></script>

<script type="text/javascript">

$(document).ready(function(){

$('#country').on('change',function(){

var countryID = $(this).val();

if(countryID){
$.ajax({

type:'POST',
url:'ajaxFile.php',
data:'countryid='+countryid,
success:function(data){
$('#state').html(data);
$('#city').html('<option value="">Select state first</option>');
}
});
}
else
{
$('#state').html('<option value="">Select country first</option>');
$('#city').html('<option value="">Select state first</option>'); 

}

});


$('#state').on('change',function(){

var stateID = $(this).val();

if(stateID)
{
$.ajax({
type:'POST',
url:'ajaxFile.php',
data:'state_id='+stateID,
success:function(data){
$('#city').html(data);
}
}); 

}
else
{
$('#city').html('<option value="">Select state first</option>'); 

}

});

});

</script>

</head>

<body>

<center>


<div style='margin-top:50px;'>
	
<br>
				
<h2>Country, State and City dropdown box using jquery in Php.</h2>
		
<br>
<?php

//Include database configuration file

require_once("dbcontroller.php");


//Get all country data


$db_handle = new DBController();
$query ="SELECT * FROM countries ORDER BY country_name";
$results = $db_handle->runQuery($query);
?>
<select name="country" id="country" >
<option value="">Select Country</option>
<?php
foreach($results as $country) {
?>
<option value="<?php echo $country["country_id"]; ?>"><?php echo $country["country_name"]; ?></option>
<?php
}
?>
</select>
<br><br>

  
<select name="state" id="state">

<option value="">Select country first</option>

</select>
	
<br><br>


<select name="city" id="city">

<option value="">Select state first</option>

</select>

</div>
	
</center>

</body>

</html>


