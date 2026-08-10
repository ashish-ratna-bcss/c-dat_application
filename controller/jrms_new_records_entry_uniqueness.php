<?php
// One page for both halves of this screen: the form, and the results.
// Was view/jrms_new_records_entry_uniqueness.htm (form) + controller/jrms_new_records_entry_uniqueness.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("JRMS New Records Entry Uniqueness");
?>

<?php
require_once("../controller/dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT distinct HEADOFCRIME FROM JRMS..JRMS_TOTAL_2012_TO_2017 
WHERE HEADOFCRIME!='' ORDER BY HEADOFCRIME";
$results = $db_handle->runQuery($query);
?>
<table>
        <tr>
          <th width="1126" align="center" scope="col">JRMS</th>
        </tr>
</table>
<form action="jrms_new_records_entry_uniqueness.php" Method="post">
<div>PSARRESTED:</div>
<select name="CRIMEHEAD">
<option value="">Select CrimeHead</option>
<?php
foreach($results as $HEADOFCRIME) {
?>
<option value="<?php echo $HEADOFCRIME["HEADOFCRIME"]; ?>"> <?php echo $HEADOFCRIME["HEADOFCRIME"]; ?> </option>
<?php
}
?>
</select>
<br/><br/>

<div>NAME:</div><textarea type="text" name="NAME"></textarea><br/><br/>

<div>FATHER_NAME:</div><textarea type="text" name="FATHER_NAME"></textarea><br/><br/>

<div>GENDER:</div><SELECT name="GENDER">
<option value=""></option>
<option value="FEMALE">FEMALE</option>
<option value="MALE">MALE</option>
</SELECT> <br/><br/>

<div>PRISONERNO:</div><textarea type="text" name="PRISONERNO"></textarea><br/><br/>

<div>TYPEOFRELEASE:</div><SELECT name="TYPEOFRELEASE">
<option value=""></option>
<option value="Out of Jail">Out of Jail</option>
</SELECT>
<br/><br/>

<div>JAIL NAME:</div><SELECT name="JAILNAME">
<option value=""></option>
<option value="CHANCHALGUDA">CHANCHALGUDA</option>
<option value="CHERLAPALLI">CHERLAPALLI</option>
</SELECT>
<br/><br/>

<div>ADMISSION DATE:</div>
<input type="text" name="ADD_DT" id="datepickerID" size="10" placeholder="yyyy/mm/dd" required="required"/>
<br/><br/>

<div>RELEASE DATE:</div>
<input type="text" name="REL_DT" id="datepickerID1" size="10" placeholder="yyyy/mm/dd" required="required"/>
<br/><br/>

<div>ADD_DURING_RELEASE:</div><textarea type="text" name="ADD_DUR_REL"></textarea><br/><br/>

<div>HEADOFCRIME:</div><SELECT name="HEADOFCRIME">
<option value=""></option>
<option value="Abetment to Suicide">Abetment to Suicide</option>
<option value="AP Gaming Act">AP Gaming Act</option>
<option value="Arson (435,436 IPC)">Arson (435,436 IPC)</option>
<option value="Attempt to Murder">Attempt to Murder</option>
<option value="Bag Lifting">Bag Lifting</option>
<option value="Bigomy ">Bigomy </option>
<option value="C.Homicides">C.Homicides</option>
<option value="Cheatings">Cheatings</option>
<option value="Communal">Communal</option>
<option value="Counterfiet Currency">Counterfiet Currency</option>
<option value="Crime against Women">Crime against Women</option>
<option value="Cyber Crime">Cyber Crime</option>
<option value="Automobile Theft">Automobile Theft</option>
<option value="Cattle Theft">Cattle Theft</option>
<option value="Dacoity">Dacoity</option>
<option value="Dicky Theft">Dicky Theft</option>
<option value="HB by Day">HB by Day</option>
<option value="HB by Night">HB by Night</option>
<option value="House Theft">House Theft</option>
<option value="Ordinary Theft">Ordinary Theft</option>
<option value="Pocket Picking">Pocket Picking</option>
<option value="Robbery">Robbery</option>
<option value="Servant Theft">Servant Theft</option>
<option value="Snatching">Snatching</option>
<option value="Diverting Attention">Diverting Attention</option>
<option value="Dowry Death">Dowry Death</option>
<option value="Drunken Driving">Drunken Driving</option>
<option value="Fatal Road Accidents">Fatal Road Accidents</option>
<option value="Gold Polishing">Gold Polishing</option>
<option value="Griev. Hurts">Griev. Hurts</option>
<option value="Harassment ">Harassment </option>
<option value="ISI">ISI</option>
<option value="Kidnapping ">Kidnapping </option>
<option value="Murder">Murder</option>
<option value="Murder for gain">Murder for gain</option>
<option value="MV Act">MV Act</option>
<option value="NDPS Act">NDPS Act</option>
<option value="Outraging the modesty of women">Outraging the modesty of women</option>
<option value="P.C.R. Act.">P.C.R. Act.</option>
<option value="PD Act">PD Act</option>
<option value="Preventive Arrests">Preventive Arrests</option>
<option value="Pseudo Naxalite">Pseudo Naxalite</option>
<option value="Pseudo Police">Pseudo Police</option>
<option value="Rape ">Rape </option>
<option value="Riotings">Riotings</option>
<option value="SC & ST Act">SC & ST Act</option>
<option value="Special and Local Laws">Special and Local Laws</option>
</SELECT>
<br/><br/>

<div>IDENTIFICATIONMARK:</div><select name="IDENTIFICATIONMARK"> 
<option value=""></option>
<option value="Leuco Dema">Leuco Dema</option>
<option value="One eye">One eye</option>
<option value="DeforMoties">DeforMoties</option>
<option value="Filariasis">Filariasis</option>
<option value="Burn Mark">Burn Mark</option>
<option value="Ordinary Theft">Ordinary Theft</option>
<option value="Pulipiri">Pulipiri</option>
<option value="Scar">Scar</option>
<option value="Pimple">Pimple</option>
<option value="Tattoo">Tattoo</option>
<option value="Mole">Mole</option>
<option value="Injury">Injury</option>
</select>
<br/><br/>
<div>PLACE OF MARK:</div><textarea type="text" name="PLACE_OF_MARK"></textarea><br/><br/>

<div>CRIME NOS:</div><textarea type="text" name="CRIME_NOS"></textarea><br/><br/>
<div>MOBILE NO:</div><textarea type="NUMBER" name="PHONE"></textarea><br/><br/>
<div>DISTRICT:</div><textarea type="TEXT" name="DISTRICT"></textarea>
<br/><br/>

<input type="submit" value="insert">
<br/><br/>

 </form>


<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"JRMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$PSARRESTED=$_POST['PSARRESTED']; 
$NAME=$_POST['NAME'];
$FATHER_NAME=$_POST['FATHER_NAME'];
$GENDER=$_POST['GENDER'];
$PRISONERNO=$_POST['PRISONERNO'];
$TYPEOFRELEASE=$_POST['TYPEOFRELEASE'];  
$JAILNAME=$_POST['JAILNAME'];
$ADD_DT=$_POST['ADD_DT'];
$REL_DT=$_POST['REL_DT'];
$ADD_DUR_REL=$_POST['ADD_DUR_REL'];
$HEADOFCRIME=$_POST['HEADOFCRIME'];
$IDENTIFICATIONMARK=$_POST['IDENTIFICATIONMARK'];
$PLACE_OF_MARK=$_POST['PLACE_OF_MARK'];
$CRIME_NOS=$_POST['CRIME_NOS'];
$PHONE=$_POST['PHONE'];
$DISTRICT=$_POST['DISTRICT'];
$DOB_DT=$_POST['DOB_DT'];
$IDPROOF_TYPE=$_POST['IDPROOF_TYPE'];
$IDPROOF_NO=$_POST['IDPROOF_NO'];
$SEC_OF_LAW=$_POST['SEC_OF_LAW'];

$sql="set dateformat mdy insert into JRMS..JRMS_TOTAL_2012_TO_2017 (CIN,PSArrested, Name, FathersName, Gender, PrisonerNo, TypeofRelease, JailName, 
Admission_to_Jail, ReleaseDt, Addr_DuringRelease, HeadofCrime, 
IdentificationMark, PlaceofIdentificationMark, CrimeNos, 
MobileNo, DISTRICT, ASONDATE,DOB_AGE,IDPROOF_TYPE,IDPROOF_NO,SEC_OF_LAW,REMARKS)
VALUES ((SELECT DISTINCT MAX(CIN)+1 FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE REMARKS LIKE '%JRMS_ENTRY_FORM%'),'$PSARRESTED','$NAME','$FATHER_NAME','$GENDER','$PRISONERNO','$TYPEOFRELEASE',
'$JAILNAME',convert(varchar(10),cast('$ADD_DT' as date),103),convert(varchar(10),cast('$REL_DT' as date),103),'$ADD_DUR_REL','$HEADOFCRIME','$IDENTIFICATIONMARK',
'$PLACE_OF_MARK','$CRIME_NOS','$PHONE','$DISTRICT',GETDATE(),'$DOB_DT','$IDPROOF_TYPE','$IDPROOF_NO','$SEC_OF_LAW','JRMS_ENTRY_FORM')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=../view/irreport.html");
?>
<?php endif; ?>
<?php layout_end(); ?>
