<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("PDACT Main Page");
?>


        <form name="jqtest"  action="#">
		<h2>Accused Particulars</h2>
       Name : <input type="text" name="Name" id="Name"/>
       Father_Name : <input type="text" name="Father_Name" id="Father_Name"/>
       Age : <input type="text" name="Age" id="Age"/>
       Dob : <input type="text" name="Dob" id="datepickerID" size="10" placeholder="yyyy-mm-dd" /><br/><br/>
       Occupation : <input type="text" name="Occupation" id="Occupation"/>
       Caste : <input type="text" name="Caste" id="Caste"/>
       Id_Proof : <select name="Id_Proof" id="Id_Proof" style="float:center;">
<option value=""></option>
<option value="Aadhaar">Aadhaar</option>
<option value="Driving_Licence">Driving Licence</option>
<option value="PASSPORT">PASSPORT</option>
<option value="Ration_Card">Ration Card</option>
<option value="Voter_Id">Voter_Id</option>
<option value="OTHER">OTHER</option>
</select>
       Id_Proof_No  : <input type="text" name="Id_Proof_No" id="Id_Proof_No"/><br/><br/>
       PRE_PDACT_KEY  : <input type="text" name="PRE_PDACT_KEY" id="PRE_PDACT_KEY"/><br/><br/>
       Phone_No : <input type="text" name="Phone_No" id="Phone_No"/>
       Irkey : <input type="text" name="Irkey" id="Irkey"/><br/><br/>
       Present_Address : <input type="text" name="Present_Address" id="Present_Address"/>
       Permanent_Address : <input type="text" name="Permanent_Address" id="Permanent_Address"/><br/><br/>
       District : <input type="text" name="District" id="District"/>
       State : <input type="text" name="State" id="State"/><br/><br/>
       PD_ACT_PS : <input type="text" name="PD_Act" id="PD_ACT_PS"/>
       Zone : <input type="text" name="Zone" id="Zone"/><br/><br/>
       File_No: <input type="text" name="File_No" id="File_No"/>
       File_No_Year: <input type="text" name="File_No_Year" id="File_No_Year"/>
       Detenu_No : <input type="text" name="Detenu_No" id="Detenu_No"/><br/><br/>
       Order_Issued_On : <input type="text" name="Order_Issued_On" id="datepickerID3" size="10" placeholder="yyyy-mm-dd" />
       Approval_Order_No : <input type="text" name="Approval_Order_No" id="Approval_Order_No"/>
       Confirmation/Revocation orders : <input type="text" name="Confirmation_Revocation_Orders" id="Confirmation_Revocation_Orders"/>
	        <h3>Crime History</h3>
       Crime_Head : <input type="text" name="Crime_Head" id="Crime_Head"/>
       Minor_Head : <input type="text" name="Minor_Head" id="Minor_Head"/>
       ModusOperendi : <input type="text" name="ModusOperendi" id="ModusOperendi"/><br/><br/>
       Police_Station : <input type="text" name="Police_Station" id="Police_Station"/>
       Crime_No : <input type="text" name="Crime_No" id="Crime_No"/>
       Year : <input type="text" name="Year" id="Year"/>
       Sec_Of_Law : <input type="text" name="Sec_Of_Law" id="Sec_Of_Law"/><br/><br/>
       Whether Involved In Other unit cases : <select name="Other_unit_cases" id="Other_Unit_Cases" style="float:center;">
<option value=""></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select>
      Name of Units: <input type="text" name="Name_of_Units" id="Name_Of_Units"/>
      No Of Cases: <input type="text" name="No_Of_Cases" id="No_Of_Cases"/><br/><br/>
       Date_Of_Arrest (Date_Of_Detention) : <input type="text" name="Date_Of_Arrest" id="datepickerID1" size="10" placeholder="yyyy/mm/dd" />
       Date_Of_Release : <input type="text" name="TO_DT" id="datepickerID2" size="10" placeholder="yyyy/mm/dd" />
       Brief_Facts : <input type="text" name="Brief_Facts" id="Brief_Facts"/>
        
       <p> <input type="reset" name="reset" id="reset" value="RESET"/>&nbsp;&nbsp;<input type="button" name="add" id="add" value="ADD"/> </p>
         <input type="hidden" name="rowcount" id="rowcount" value="1"/>
         </form>
    
        
     <table id="dataTab" style="display:none;" border="1">

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

    <p> <input style="display:none;" type="Submit" name="submit" id="submit" value="submit"/> </p>
    
    
<?php layout_end(); ?>
