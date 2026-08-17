<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('PDACT Main Page');
cdat_sum_page_open();
cdat_sum_entry_card_open(
    'Accused Particulars',
    'Enter PDACT accused and crime history details.',
    '#',
    'post',
    '',
    'jqtest'
);
?>
<?php echo cdat_sum_field_text('Name', 'Name', '', 'Name', 'Name', false); ?>
<?php echo cdat_sum_field_text('Father_Name', 'Father Name', '', 'Father_Name', 'Father Name', false); ?>
<?php echo cdat_sum_field_text('Age', 'Age', '', 'Age', 'Age', false); ?>
<?php echo cdat_sum_field_date('Dob', 'Dob', 'datepickerID', '', false); ?>
<?php echo cdat_sum_field_text('Occupation', 'Occupation', '', 'Occupation', 'Occupation', false); ?>
<?php echo cdat_sum_field_text('Caste', 'Caste', '', 'Caste', 'Caste', false); ?>
<?php echo cdat_sum_searchable_select('Id_Proof', 'Id Proof', [
    '' => '',
    'Aadhaar' => 'Aadhaar',
    'Driving_Licence' => 'Driving Licence',
    'PASSPORT' => 'PASSPORT',
    'Ration_Card' => 'Ration Card',
    'Voter_Id' => 'Voter_Id',
    'OTHER' => 'OTHER',
], '', 'Select Id Proof', false, '', 'Id_Proof'); ?>
<?php echo cdat_sum_field_text('Id_Proof_No', 'Id Proof No', '', 'Id_Proof_No', 'Id Proof No', false); ?>
<?php echo cdat_sum_field_text('PRE_PDACT_KEY', 'PRE PDACT KEY', '', 'PRE_PDACT_KEY', 'PRE PDACT KEY', false); ?>
<?php echo cdat_sum_field_text('Phone_No', 'Phone No', '', 'Phone_No', 'Phone No', false, 'tel'); ?>
<?php echo cdat_sum_field_text('Irkey', 'Irkey', '', 'Irkey', 'Irkey', false); ?>
<?php echo cdat_sum_field_text('Present_Address', 'Present Address', '', 'Present_Address', 'Present Address', false); ?>
<?php echo cdat_sum_field_text('Permanent_Address', 'Permanent Address', '', 'Permanent_Address', 'Permanent Address', false); ?>
<?php echo cdat_sum_field_text('District', 'District', '', 'District', 'District', false); ?>
<?php echo cdat_sum_field_text('State', 'State', '', 'State', 'State', false); ?>
<?php echo cdat_sum_field_text('PD_Act', 'PD ACT PS', '', 'PD_ACT_PS', 'PD ACT PS', false); ?>
<?php echo cdat_sum_field_text('Zone', 'Zone', '', 'Zone', 'Zone', false); ?>
<?php echo cdat_sum_field_text('File_No', 'File No', '', 'File_No', 'File No', false); ?>
<?php echo cdat_sum_field_text('File_No_Year', 'File No Year', '', 'File_No_Year', 'File No Year', false); ?>
<?php echo cdat_sum_field_text('Detenu_No', 'Detenu No', '', 'Detenu_No', 'Detenu No', false); ?>
<?php echo cdat_sum_field_date('Order_Issued_On', 'Order Issued On', 'datepickerID3', '', false); ?>
<?php echo cdat_sum_field_text('Approval_Order_No', 'Approval Order No', '', 'Approval_Order_No', 'Approval Order No', false); ?>
<?php echo cdat_sum_field_text('Confirmation_Revocation_Orders', 'Confirmation / Revocation Orders', '', 'Confirmation_Revocation_Orders', 'Confirmation / Revocation Orders', false); ?>
<h3 class="sum-search-card__title">Crime History</h3>
<?php echo cdat_sum_field_text('Crime_Head', 'Crime Head', '', 'Crime_Head', 'Crime Head', false); ?>
<?php echo cdat_sum_field_text('Minor_Head', 'Minor Head', '', 'Minor_Head', 'Minor Head', false); ?>
<?php echo cdat_sum_field_text('ModusOperendi', 'Modus Operendi', '', 'ModusOperendi', 'Modus Operendi', false); ?>
<?php echo cdat_sum_field_text('Police_Station', 'Police Station', '', 'Police_Station', 'Police Station', false); ?>
<?php echo cdat_sum_field_text('Crime_No', 'Crime No', '', 'Crime_No', 'Crime No', false); ?>
<?php echo cdat_sum_field_text('Year', 'Year', '', 'Year', 'Year', false); ?>
<?php echo cdat_sum_field_text('Sec_Of_Law', 'Sec Of Law', '', 'Sec_Of_Law', 'Sec Of Law', false); ?>
<?php echo cdat_sum_searchable_select('Other_unit_cases', 'Whether Involved In Other Unit Cases', [
    '' => '',
    'Yes' => 'Yes',
    'No' => 'No',
], '', 'Select', false, '', 'Other_Unit_Cases'); ?>
<?php echo cdat_sum_field_text('Name_of_Units', 'Name of Units', '', 'Name_Of_Units', 'Name of Units', false); ?>
<?php echo cdat_sum_field_text('No_Of_Cases', 'No Of Cases', '', 'No_Of_Cases', 'No Of Cases', false); ?>
<?php echo cdat_sum_field_date('Date_Of_Arrest', 'Date Of Arrest (Date Of Detention)', 'datepickerID1', '', false); ?>
<?php echo cdat_sum_field_date('TO_DT', 'Date Of Release', 'datepickerID2', '', false); ?>
<?php echo cdat_sum_field_text('Brief_Facts', 'Brief Facts', '', 'Brief_Facts', 'Brief Facts', false); ?>
<input type="hidden" name="rowcount" id="rowcount" value="1"/>
<div class="sum-entry-form__actions">
<input type="reset" name="reset" id="reset" class="sum-search-form__submit" value="RESET"/>
<input type="button" name="add" id="add" class="sum-search-form__submit" value="ADD"/>
</div>
</form></section>
<div id="global-ajax-results" class="sum-ajax-results" aria-live="polite"></div>

<table id="dataTab" style="display:none;" border="1" class="sum-data-table">
<tr>
<th>Name</th>
<th>Father_Name</th>
<th>Age</th>
<th>Dob</th>
<th>Occupation</th>
<th>Caste</th>
<th>Id_Proof</th>
<th>Id_Proof_No</th>
<th>PRE_PDACT_KEY</th>
<th>Phone_No</th>
<th>Irkey</th>
<th>Present_Address</th>
<th>Permanent_Address</th>
<th>District</th>
<th>State</th>
<th>PD_ACT_PS</th>
<th>Zone</th>
<th>File_No</th>
<th>File_No_Year</th>
<th>Detenu_No</th>
<th>Order_Issued_On</th>
<th>Approval_Orders_No</th>
<th>Confirmation_Revocation_Orders</th>
<th>Crime_Head</th>
<th>Minor_Head</th>
<th>ModusOperendi</th>
<th>Police_Station</th>
<th>Crime_NO</th>
<th>Year</th>
<th>Sec_Of_Law</th>
<th>Whether_Involved_In_Other_Unit_Cases</th>
<th>Name_Of_Units</th>
<th>No_Of_Cases</th>
<th>Date_Of_Arrest</th>
<th>Date_Of_Release</th>
<th>Brief_Facts</th>
</tr>
</table>
<p><input style="display:none;" type="Submit" name="submit" id="submit" value="submit"/></p>
<?php
cdat_sum_page_close();
layout_end();
