<script src="jquerydynamic.js"></script>
<script type="text/javascript">
$(document).ready(function()
{
$('#country').on('change',function()
{
var countryID =$(this).val();
if(countryID)

{
$.ajax
({
type:'POST',
url:'ajax.php',
data:'country_id='+countryID,
success:function(data)
{

$('#state').html(data);
$('#city').html('<option value="">Select State First</option>');
}
});
}else
{
$('#state').html('<option value="">Select Country First</option>');
$('#city').html('<option value="">Select State First</option>');

}

})

$('#state').on('change',function()
{
var stateID =$(this).val();
if(stateID)

{
$.ajax
({
type:'POST',
url:'ajax.php',
data:'state_id='+stateID,
success:function(data)
{

$('#city').html(data);
}
});
}else
{
$('#city').html('<option value="">Select State First</option>');
}

})

});
</script>
<?php
$query=$db->query("Select * From countries Where status='1' ORDER BY country_name ASC");
$rowcount=$query->num_rows;

?>

<div>
<!– Select Box for county where data is fetch through select query!–>
<label for="country">Country</label>
<select name="country" id="country" >
<option value="">Select Country</option>
<?php
if($rowcount>0){

while($row=$query->fetch_assoc()){
echo '<option value="'.$row['country_id'].'">'.$row['country_name'].'</option>';
}

}
else{
echo '<option value="">Country Not Available</option>';

}
?>

</select>
</div>
<!– Select Box for state –>
<div>
<label for="state">State</label>
<select name="state" id="state" >
<option value="">Select State</option>
</select>
</div>
<!– Select Box for City–>
<div>
<label for="city">City</label>
<select name="city" id="city" >
<option value="">Select City</option>
</select>
</div>