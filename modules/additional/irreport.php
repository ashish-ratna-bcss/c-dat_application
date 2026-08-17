<?php
require_once __DIR__ . '/../common/bootstrap.php';
// One page for both halves of this screen: the form, and the results.
// Was view/irreport.html (form) + controller/irreport.php (handler).
// GET shows the form; POST submit renders the form and status below it.
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin("Irreport");
cdat_sum_page_open();

cdat_sum_entry_card_open('IR Particular', 'Enter interrogation report particulars.', 'irreport.php');
?>
<div>NAME:</div><textarea type="text" name="NAME"  required="required"></textarea><br/><br/>
<div>ALIAS_NAME:</div><textarea type="text" name="ALIAS_NAME"></textarea><br/><br/>
<div>FATHER_NAME:</div><textarea type="text" name="FATHER_NAME"  required="required"></textarea><br/><br/>
<div>AGE:</div><textarea type="text" name="AGE"  required="required"></textarea><br/><br/>
<div>DATE_OF_BIRTH:</div><textarea type="text" name="DATE_OF_BIRTH"></textarea><br/><br/>
<div>NATIONALITY:</div><textarea type="text" name="NATIONALITY"  required="required"></textarea><br/><br/>
<div>RELIGION:</div><textarea type="text" name="RELIGION"  required="required"></textarea><br/><br/>
<div>CASTE:</div><textarea type="text" name="CASTE"></textarea><br/><br/>
<div>COMMUNITY:</div><textarea type="text" name="COMMUNITY"></textarea><br/><br/>
<div>PRESENT_ADDRESS:</div><textarea type="text" name="PRESENT_ADDRESS"  required="required"></textarea><br/><br/>
<div>PERMANENT_ADDRESS:</div><textarea type="text" name="PERMANENT_ADDRESS"></textarea><br/><br/>
<div>MOBILE:</div><textarea type="text" name="MOBILE"></textarea><br/><br/>
<div>EMAIL_ID:</div><textarea type="text" name="EMAIL_ID"></textarea><br/><br/>
<div>SOCIAL_MEDIA_ACCOUNTS:</div><textarea type="text" name="SOCIAL_MEDIA_ACCOUNTS"></textarea><br/><br/>
<div>AADHAR_NO:</div><textarea type="text" name="AADHAR_NO"></textarea><br/><br/>
<div>RATION_CARD_NO:</div><textarea type="text" name="RATION_CARD_NO"></textarea><br/><br/>
<div>VOTERID:</div><textarea type="text" name="VOTERID"></textarea><br/><br/>
<div>PASSPORT:</div><textarea type="text" name="PASSPORT"></textarea><br/><br/>
<div>PANCARD:</div><textarea type="text" name="PANCARD"></textarea><br/><br/>
<div>ELECTRICITY_CONNECTION:</div><textarea type="text" name="ELECTRICITY_CONNECTIONE"></textarea><br/><br/>
<div>GAS_CONNECTION:</div><textarea type="text" name="GAS_CONNECTION"></textarea><br/><br/>
<div>VEHICLES:</div><textarea type="text" name="VEHICLES"></textarea><br/><br/>
<div>DRIVING_LICENSE:</div><textarea type="text" name="DRIVING_LICENSE"></textarea><br/><br/>
<div>OTHER_ID_PROOFS:</div><textarea type="text" name="OTHER_ID_PROOFS"></textarea><br/><br/>
<div>SEX:</div><SELECT name="SEX" required="required">
<option value=""></option>
<option value="MALE">MALE</option>
<option value="FEMALE">FEMALE</option>
<option value="MALE">TRANSGENDER</option>
</SELECT> <br/><br/>
<div>BUILT:</div><textarea type="text" name="BUILT"></textarea><br/><br/>
<div>HEIGHT:</div><textarea type="text" name="HEIGHT"></textarea><br/><br/>
<div>EYES:</div><textarea type="text" name="EYES"></textarea><br/><br/>
<div>HAIR:</div><textarea type="text" name="HAIR"></textarea><br/><br/>
<div>FACE:</div><textarea type="text" name="FACE"></textarea><br/><br/>
<div>COLOUR:</div><textarea type="text" name="COLOUR"></textarea><br/><br/>
<div>TEETH:</div><textarea type="text" name="TEETH"></textarea><br/><br/>
<div>NOSE:</div><textarea type="text" name="NOSE"></textarea><br/><br/>
<div>BEARD:</div><textarea type="text" name="BEARD"></textarea><br/><br/>
<div>MUSTACHES:</div><textarea type="text" name="MUSTACHES"></textarea><br/><br/>
<div>EAR:</div><textarea type="text" name="EAR"></textarea><br/><br/>
<div>IDENTIFICATION_MARKS:</div><textarea type="text" name="IDENTIFICATION_MARKS"></textarea><br/><br/>
<div>DEFORMITIES_PECULIARITIES:</div><textarea type="text" name="DEFORMITIES_PECULIARITIES"></textarea><br/><br/>
<div>LANGUAGE_DIALECT:</div><textarea type="text" name="LANGUAGE_DIALECT"></textarea><br/><br/>
<div>BURN_MARKS:</div><textarea type="text" name="BURN_MARKS"></textarea><br/><br/>
<div>LEUCODEMA:</div><textarea type="text" name="LEUCODEMA"></textarea><br/><br/>
<div>MOLE:</div><textarea type="text" name="MOLE"></textarea><br/><br/>
<div>SCAR:</div><textarea type="text" name="SCAR"></textarea><br/><br/>
<div>TATTOO:</div><textarea type="text" name="TATTOO"></textarea><br/><br/>
<div>LIVING_STATUS:</div><textarea type="text" name="LIVING_STATUS"></textarea><br/><br/>
<div>MARITAL_STATUS:</div><textarea type="text" name="MARITAL_STATUS" required="required"></textarea><br/><br/>
<div>EDUCATION_DETAILS:</div><textarea type="text" name="EDUCATION_DETAILS"></textarea><br/><br/>
<div>OCCUPATION:</div><textarea type="text" name="OCCUPATION" required="required"></textarea><br/><br/>
<div>INCOME_GROUP:</div><textarea type="text" name="INCOME_GROUP"></textarea><br/><br/>
<div>REGULAR_HABITS:</div><textarea type="text" name="REGULAR_HABITS"></textarea><br/><br/>
<div>CATEGORY:</div><textarea type="text" name="CATEGORY" required="required" PLACEHOLDER="A1 OR A2..."></textarea><br/><br/>
<div>CC_OR_EXCC:</div>
<SELECT name="CC_OR_EXCC" required="required">
<option value=""></option>
<option value="CC">CC</option>
<option value="EXCC">EXCC</option>
<option value="NOT GIVEN">NOT GIVEN</option>
</SELECT> <br/><br/>
<div>CCNO:</div><textarea type="tex30203t" name="CCNO"></textarea><br/><br/>
<div>IR_ENTRY_DONE_BY:</div>
<SELECT name="IR_ENTRY_DONE_BY" required="required">
<option value=""></option><option value="A SUJANA WPC 31145 ">A SUJANA WPC 31145</option><option value="RAJESHWARI WPC 30203">RAJESHWARI WPC 30203</option>
<option value="MOHD MAZHAR SAYEED PC 30568"> MOHD MAZHAR SAYEED PC 30568</option></SELECT> <br/><br/>
<?php
cdat_sum_entry_card_close('insert');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"FORMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$NAME=$_POST['NAME'];     
$ALIAS_NAME=$_POST['ALIAS_NAME'];     
$FATHER_NAME=$_POST['FATHER_NAME'];     
$AGE=$_POST['AGE'];     
$DATE_OF_BIRTH=$_POST['DATE_OF_BIRTH'];     
$NATIONALITY=$_POST['NATIONALITY'];     
$RELIGION=$_POST['RELIGION'];     
$CASTE=$_POST['CASTE'];     
$COMMUNITY=$_POST['COMMUNITY'];     
$PRESENT_ADDRESS=$_POST['PRESENT_ADDRESS'];     
$PERMANENT_ADDRESS=$_POST['PERMANENT_ADDRESS'];     
$MOBILE=$_POST['MOBILE'];     
$EMAIL_ID=$_POST['EMAIL_ID'];     
$SOCIAL_MEDIA_ACCOUNTS=$_POST['SOCIAL_MEDIA_ACCOUNTS'];     
$AADHAR_NO=$_POST['AADHAR_NO'];
$RATION_CARD_NO=$_POST['RATION_CARD_NO'];     
$VOTERID=$_POST['VOTERID'];     
$PASSPORT=$_POST['PASSPORT'];     
$PANCARD=$_POST['PANCARD'];     
$ELECTRICITY_CONNECTION=$_POST['ELECTRICITY_CONNECTIONE'];     
$GAS_CONNECTION=$_POST['GAS_CONNECTION'];     
$VEHICLES=$_POST['VEHICLES'];
$DRIVING_LICENSE=$_POST['DRIVING_LICENSE'];     
$OTHER_ID_PROOFS=$_POST['OTHER_ID_PROOFS'];     
$SEX=$_POST['SEX'];
$BUILT=$_POST['BUILT'];
$HEIGHT=$_POST['HEIGHT'];
$EYES=$_POST['EYES'];
$HAIR=$_POST['HAIR'];
$FACE=$_POST['FACE'];
$COLOUR=$_POST['COLOUR'];
$TEETH=$_POST['TEETH'];
$NOSE=$_POST['NOSE'];
$BEARD=$_POST['BEARD'];
$MUSTACHES=$_POST['MUSTACHES'];
$EAR=$_POST['EAR'];
$IDENTIFICATION_MARKS=$_POST['IDENTIFICATION_MARKS'];
$DEFORMITIES_PECULIARITIES=$_POST['DEFORMITIES_PECULIARITIES'];
$LANGUAGE_DIALECT=$_POST['LANGUAGE_DIALECT'];
$BURN_MARKS=$_POST['BURN_MARKS'];
$LEUCODEMA=$_POST['LEUCODEMA'];
$MOLE=$_POST['MOLE'];
$SCAR=$_POST['SCAR'];
$TATTOO=$_POST['TATTOO'];
$LIVING_STATUS=$_POST['LIVING_STATUS'];
$MARITAL_STATUS=$_POST['MARITAL_STATUS'];
$EDUCATION_DETAILS=$_POST['EDUCATION_DETAILS'];
$OCCUPATION=$_POST['OCCUPATION'];
$INCOME_GROUP=$_POST['INCOME_GROUP'];
$REGULAR_HABITS=$_POST['REGULAR_HABITS'];
$CATEGORY=$_POST['CATEGORY'];
$CC_OR_EXCC=$_POST['CC_OR_EXCC'];
$CCNO=$_POST['CCNO'];
$IR_ENTRY_DONE_BY=$_POST['IR_ENTRY_DONE_BY'];
$sql="insert into FORMS..IR_PARTICULARS (NAME, ALIAS_NAME, FATHER_NAME, AGE, DATE_OF_BIRTH, NATIONALITY, 
RELIGION, CASTE, COMMUNITY, PRESENT_ADDRESS, PERMANENT_ADDRESS, MOBILE, 
EMAIL_ID, SOCIAL_MEDIA_ACCOUNTS, AADHAR_NO, RATION_CARD_NO, VOTERID, 
PASSPORT, PANCARD, ELECTRICITY_CONNECTION, GAS_CONNECTION, VEHICLES, 
DRIVING_LICENSE, OTHER_ID_PROOFS, SEX, BUILT, HEIGHT, EYES, HAIR, FACE, 
COLOUR, TEETH, NOSE, BEARD, MUSTACHES, EAR, IDENTIFICATION_MARKS, DEFORMITIES_PECULIARITIES, 
LANGUAGE_DIALECT, BURN_MARKS, LEUCODEMA, MOLE, SCAR, TATTOO, LIVING_STATUS, MARITAL_STATUS, 
EDUCATION_DETAILS, OCCUPATION, INCOME_GROUP, REGULAR_HABITS, CATEGORY,CC_OR_EXCC,CC_OR_EXCCNO,ASONDATE,IR_ENTRY_DONE_BY) 
values('$NAME','$ALIAS_NAME','$FATHER_NAME','$AGE','$DATE_OF_BIRTH','$NATIONALITY','$RELIGION','$CASTE',
'$COMMUNITY','$PRESENT_ADDRESS','$PERMANENT_ADDRESS','$MOBILE',
'$EMAIL_ID','$SOCIAL_MEDIA_ACCOUNTS','$AADHAR_NO','$RATION_CARD_NO','$VOTERID',
'$PASSPORT','$PANCARD','$ELECTRICITY_CONNECTION','$GAS_CONNECTION','$VEHICLES',
'$DRIVING_LICENSE','$OTHER_ID_PROOFS','$SEX','$BUILT','$HEIGHT','$EYES','$HAIR','$FACE',
'$COLOUR','$TEETH','$NOSE','$BEARD','$MUSTACHES','$EAR','$IDENTIFICATION_MARKS','$DEFORMITIES_PECULIARITIES',
'$LANGUAGE_DIALECT','$BURN_MARKS','$LEUCODEMA','$MOLE','$SCAR','$TATTOO','$LIVING_STATUS','$MARITAL_STATUS',
'$EDUCATION_DETAILS','$OCCUPATION','$INCOME_GROUP','$REGULAR_HABITS','$CATEGORY','$CC_OR_EXCC','$CCNO',GETDATE(),'$IR_ENTRY_DONE_BY')";
if(!sqlsrv_query($conn,$sql))
{
cdat_sum_status_message('not inserted', false);
}
else
{
cdat_sum_status_message('inserted', true);
}
header("refresh:30; url=irreport.php");
}

cdat_sum_page_close();
layout_end();
