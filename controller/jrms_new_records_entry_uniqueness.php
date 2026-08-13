<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
require_once __DIR__ . '/dbcontroller.php';

$db_handle = new DBController();
$query = "SELECT distinct HEADOFCRIME FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE HEADOFCRIME!='' ORDER BY HEADOFCRIME";
$results = $db_handle->runQuery($query) ?: [];
$crimeHeadDbOptions = ['' => 'Select CrimeHead'];
foreach ($results as $r) {
    $v = (string) ($r['HEADOFCRIME'] ?? '');
    if ($v !== '') {
        $crimeHeadDbOptions[$v] = $v;
    }
}

$headOptions = [
    '' => 'Select head of crime',
    'Abetment to Suicide' => 'Abetment to Suicide',
    'AP Gaming Act' => 'AP Gaming Act',
    'Arson (435,436 IPC)' => 'Arson (435,436 IPC)',
    'Attempt to Murder' => 'Attempt to Murder',
    'Bag Lifting' => 'Bag Lifting',
    'Bigomy ' => 'Bigomy ',
    'C.Homicides' => 'C.Homicides',
    'Cheatings' => 'Cheatings',
    'Communal' => 'Communal',
    'Counterfiet Currency' => 'Counterfiet Currency',
    'Crime against Women' => 'Crime against Women',
    'Cyber Crime' => 'Cyber Crime',
    'Automobile Theft' => 'Automobile Theft',
    'Cattle Theft' => 'Cattle Theft',
    'Dacoity' => 'Dacoity',
    'Dicky Theft' => 'Dicky Theft',
    'HB by Day' => 'HB by Day',
    'HB by Night' => 'HB by Night',
    'House Theft' => 'House Theft',
    'Ordinary Theft' => 'Ordinary Theft',
    'Pocket Picking' => 'Pocket Picking',
    'Robbery' => 'Robbery',
    'Servant Theft' => 'Servant Theft',
    'Snatching' => 'Snatching',
    'Diverting Attention' => 'Diverting Attention',
    'Dowry Death' => 'Dowry Death',
    'Drunken Driving' => 'Drunken Driving',
    'Fatal Road Accidents' => 'Fatal Road Accidents',
    'Gold Polishing' => 'Gold Polishing',
    'Griev. Hurts' => 'Griev. Hurts',
    'Harassment ' => 'Harassment ',
    'ISI' => 'ISI',
    'Kidnapping ' => 'Kidnapping ',
    'Murder' => 'Murder',
    'Murder for gain' => 'Murder for gain',
    'MV Act' => 'MV Act',
    'NDPS Act' => 'NDPS Act',
    'Outraging the modesty of women' => 'Outraging the modesty of women',
    'P.C.R. Act.' => 'P.C.R. Act.',
    'PD Act' => 'PD Act',
    'Preventive Arrests' => 'Preventive Arrests',
    'Pseudo Naxalite' => 'Pseudo Naxalite',
    'Pseudo Police' => 'Pseudo Police',
    'Rape ' => 'Rape ',
    'Riotings' => 'Riotings',
    'SC & ST Act' => 'SC & ST Act',
    'Special and Local Laws' => 'Special and Local Laws',
];
$markOptions = [
    '' => 'Select mark',
    'Leuco Dema' => 'Leuco Dema',
    'One eye' => 'One eye',
    'DeforMoties' => 'DeforMoties',
    'Filariasis' => 'Filariasis',
    'Burn Mark' => 'Burn Mark',
    'Ordinary Theft' => 'Ordinary Theft',
    'Pulipiri' => 'Pulipiri',
    'Scar' => 'Scar',
    'Pimple' => 'Pimple',
    'Tattoo' => 'Tattoo',
    'Mole' => 'Mole',
    'Injury' => 'Injury',
];

$submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

layout_begin('JRMS New Records Entry Uniqueness');
cdat_sum_page_open();
cdat_sum_entry_card_open(
    'JRMS',
    'Enter a new JRMS uniqueness record.',
    'jrms_new_records_entry_uniqueness.php'
);
echo cdat_sum_searchable_select('CRIMEHEAD', 'PSARRESTED', $crimeHeadDbOptions, (string) ($_POST['CRIMEHEAD'] ?? ''), 'Select CrimeHead', false);
echo cdat_sum_field_textarea('NAME', 'NAME', (string) ($_POST['NAME'] ?? ''));
echo cdat_sum_field_textarea('FATHER_NAME', 'FATHER_NAME', (string) ($_POST['FATHER_NAME'] ?? ''));
echo cdat_sum_searchable_select('GENDER', 'GENDER', ['' => '', 'FEMALE' => 'FEMALE', 'MALE' => 'MALE'], (string) ($_POST['GENDER'] ?? ''), 'Select gender', false);
echo cdat_sum_field_textarea('PRISONERNO', 'PRISONERNO', (string) ($_POST['PRISONERNO'] ?? ''));
echo cdat_sum_searchable_select('TYPEOFRELEASE', 'TYPEOFRELEASE', ['' => '', 'Out of Jail' => 'Out of Jail'], (string) ($_POST['TYPEOFRELEASE'] ?? ''), 'Select type', false);
echo cdat_sum_searchable_select('JAILNAME', 'JAIL NAME', ['' => '', 'CHANCHALGUDA' => 'CHANCHALGUDA', 'CHERLAPALLI' => 'CHERLAPALLI'], (string) ($_POST['JAILNAME'] ?? ''), 'Select jail', false);
echo cdat_sum_field_date('ADD_DT', 'ADMISSION DATE', 'datepickerID', (string) ($_POST['ADD_DT'] ?? ''));
echo cdat_sum_field_date('REL_DT', 'RELEASE DATE', 'datepickerID1', (string) ($_POST['REL_DT'] ?? ''));
echo cdat_sum_field_textarea('ADD_DUR_REL', 'ADD_DURING_RELEASE', (string) ($_POST['ADD_DUR_REL'] ?? ''));
echo cdat_sum_searchable_select('HEADOFCRIME', 'HEADOFCRIME', $headOptions, (string) ($_POST['HEADOFCRIME'] ?? ''), 'Select head of crime', false);
echo cdat_sum_searchable_select('IDENTIFICATIONMARK', 'IDENTIFICATIONMARK', $markOptions, (string) ($_POST['IDENTIFICATIONMARK'] ?? ''), 'Select mark', false);
echo cdat_sum_field_textarea('PLACE_OF_MARK', 'PLACE OF MARK', (string) ($_POST['PLACE_OF_MARK'] ?? ''));
echo cdat_sum_field_textarea('CRIME_NOS', 'CRIME NOS', (string) ($_POST['CRIME_NOS'] ?? ''));
echo cdat_sum_field_textarea('PHONE', 'MOBILE NO', (string) ($_POST['PHONE'] ?? ''));
echo cdat_sum_field_textarea('DISTRICT', 'DISTRICT', (string) ($_POST['DISTRICT'] ?? ''));
cdat_sum_entry_card_close('insert');

if ($submitted) {
    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'JRMS');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $PSARRESTED = $_POST['PSARRESTED'] ?? '';
    $NAME = $_POST['NAME'];
    $FATHER_NAME = $_POST['FATHER_NAME'];
    $GENDER = $_POST['GENDER'];
    $PRISONERNO = $_POST['PRISONERNO'];
    $TYPEOFRELEASE = $_POST['TYPEOFRELEASE'];
    $JAILNAME = $_POST['JAILNAME'];
    $ADD_DT = $_POST['ADD_DT'];
    $REL_DT = $_POST['REL_DT'];
    $ADD_DUR_REL = $_POST['ADD_DUR_REL'];
    $HEADOFCRIME = $_POST['HEADOFCRIME'];
    $IDENTIFICATIONMARK = $_POST['IDENTIFICATIONMARK'];
    $PLACE_OF_MARK = $_POST['PLACE_OF_MARK'];
    $CRIME_NOS = $_POST['CRIME_NOS'];
    $PHONE = $_POST['PHONE'];
    $DISTRICT = $_POST['DISTRICT'];
    $DOB_DT = $_POST['DOB_DT'] ?? '';
    $IDPROOF_TYPE = $_POST['IDPROOF_TYPE'] ?? '';
    $IDPROOF_NO = $_POST['IDPROOF_NO'] ?? '';
    $SEC_OF_LAW = $_POST['SEC_OF_LAW'] ?? '';

    $sql = "set dateformat mdy insert into JRMS..JRMS_TOTAL_2012_TO_2017 (CIN,PSArrested, Name, FathersName, Gender, PrisonerNo, TypeofRelease, JailName,
Admission_to_Jail, ReleaseDt, Addr_DuringRelease, HeadofCrime,
IdentificationMark, PlaceofIdentificationMark, CrimeNos,
MobileNo, DISTRICT, ASONDATE,DOB_AGE,IDPROOF_TYPE,IDPROOF_NO,SEC_OF_LAW,REMARKS)
VALUES ((SELECT DISTINCT MAX(CIN)+1 FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE REMARKS LIKE '%JRMS_ENTRY_FORM%'),'$PSARRESTED','$NAME','$FATHER_NAME','$GENDER','$PRISONERNO','$TYPEOFRELEASE',
'$JAILNAME',convert(varchar(10),cast('$ADD_DT' as date),103),convert(varchar(10),cast('$REL_DT' as date),103),'$ADD_DUR_REL','$HEADOFCRIME','$IDENTIFICATIONMARK',
'$PLACE_OF_MARK','$CRIME_NOS','$PHONE','$DISTRICT',GETDATE(),'$DOB_DT','$IDPROOF_TYPE','$IDPROOF_NO','$SEC_OF_LAW','JRMS_ENTRY_FORM')";
    if (!sqlsrv_query($conn, $sql)) {
        cdat_sum_status_message('not inserted', false);
    } else {
        cdat_sum_status_message('inserted', true);
    }
    sqlsrv_close($conn);
}

cdat_sum_page_close();
layout_end();
