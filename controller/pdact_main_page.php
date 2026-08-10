<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">    <head>
    <title>jQuery Dynamic Rows</title>
    <meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="../assets/vendor/jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
    <script src="jquery-ui-1.10.4.custom/js/jquery-latest.min.js" type="text/javascript"></script>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
    <script>
    $(document).ready(function(){

        $("#add").on("click",function(){

          var rowcount = $("#rowcount").val();   

          var row = '<tr class="dynamic" id="'+rowcount+'"><td>'+$("#Name").val()+'</td><td>'+$("#Father_Name").val()+'</td><td>'+$("#Age").val()+'</td><td>'+ $("#datepickerID").val()+'</td><td>'+$("#Occupation").val()+'</td><td>'+ $("#Caste").val()+'</td><td>'+ $("#Id_Proof").val()+'</td><td>'+ $("#Id_Proof_No").val()+'</td><td>'+$("#Phone_No").val()+'</td><td>'+ $("#Irkey").val()+'</td><td>'+ $("#PRE_PDACT_KEY").val()+'</td><td>'+ $("#Present_Address").val()+'</td><td>'+ $("#Permanent_Address").val()+'</td><td>'+$("#District").val()+'</td><td>'+$("#State").val()+'</td><td>'+ $("#PD_ACT_PS").val()+'</td><td>'+$("#Zone").val()+'</td><td>'+$("#File_No").val()+'</td><td>'+$("#File_No_Year").val()+'</td><td>'+$("#Detenu_No").val()+'</td><td>'+$("#datepickerID3").val()+'</td><td>'+$("#Approval_Order_No").val()+'</td><td>'+$("#Confirmation_Revocation_Orders").val()+'</td><td>'+$("#Crime_Head").val()+'</td><td>'+$("#Minor_Head").val()+'</td><td>'+$("#ModusOperendi").val()+'</td><td>'+$("#Police_Station").val()+'</td><td>'+$("#Crime_No").val()+'</td><td>'+$("#Year").val()+'</td><td>'+$("#Sec_Of_Law").val()+'</td><td>'+$("#Other_Unit_Cases").val()+'</td><td>'+$("#Name_Of_Units").val()+'</td><td>'+$("#No_Of_Cases").val()+'</td><td>'+$("#datepickerID1").val()+'</td><td>'+$("#datepickerID2").val()+'</td><td>'+$("#Brief_Facts").val()+'</td><tr>';

 		  rowcount = parseInt(rowcount)+1;

           $("#rowcount").val(rowcount);
           $("#dataTab").append(row);
           $("#dataTab").show();
           $("#submit").show();
        });
        $("#submit").on("click",function(){
                alert("submit");
                var jsonObj = [];
                i=0;
          
            $("#dataTab tr.dynamic").each(function(){
			    var td = $(this).find('td');

				Name = td.eq(0).text();
				Father_Name = td.eq(1).text();
				Age = td.eq(2).text();
				Dob = td.eq(3).text();
				Occupation = td.eq(4).text();
				Caste = td.eq(5).text();
				Id_Proof = td.eq(6).text();
				Id_Proof_No = td.eq(7).text();
				Phone_No = td.eq(8).text();
				Irkey = td.eq(9).text();
				PRE_PDACT_KEY = td.eq(10).text();
				Present_Address = td.eq(11).text();
				Permanent_Address = td.eq(12).text();
				District = td.eq(13).text();
				State = td.eq(14).text();
				PD_ACT_PS = td.eq(15).text();
				Zone = td.eq(16).text();
				File_No= td.eq(17).text();
				File_No_Year= td.eq(18).text();
				Detenu_No = td.eq(19).text();
				Order_Issued_On = td.eq(20).text();
				Approval_Orders_No = td.eq(21).text();
				Confirmation_Revocation_Orders= td.eq(22).text();
                		Crime_Head = td.eq(23).text();
				Minor_Head = td.eq(24).text();
				ModusOperendi = td.eq(25).text();
				Police_Station = td.eq(26).text();
				Crime_No = td.eq(27).text();
				Year = td.eq(28).text();
				Sec_Of_Law = td.eq(29).text();
				Whether_Involved_In_Other_Unit_Cases = td.eq(30).text();
				Name_Of_Units = td.eq(31).text();
				No_Of_Cases = td.eq(32).text();
				Date_Of_Arrest = td.eq(33).text();
				Date_Of_Release = td.eq(34).text();
				Brief_Facts = td.eq(35).text();
				
			
				jsonObj.push({

                  		Name : Name, 
				Father_Name : Father_Name, 
				Age : Age,
				Dob : Dob,
				Occupation : Occupation,
				Caste : Caste, 
				Id_Proof : Id_Proof,
				Id_Proof_No : Id_Proof_No,
				Phone_No : Phone_No,
				Irkey : Irkey,
				PRE_PDACT_KEY : PRE_PDACT_KEY,
				Present_Address : Present_Address,
				Permanent_Address : Permanent_Address,
				District : District,
				State : State,
				PD_ACT_PS : PD_ACT_PS,
				Zone : Zone,
				File_No : File_No,
				File_No_Year : File_No_Year,
				Detenu_No : Detenu_No,
				Order_Issued_On : Order_Issued_On,
				Approval_Orders_No : Approval_Orders_No,
				Confirmation_Revocation_Orders : Confirmation_Revocation_Orders,
               			Crime_Head : Crime_Head,
				Minor_Head : Minor_Head,
				ModusOperendi : ModusOperendi,
				Police_Station : Police_Station,
				Crime_No : Crime_No,
				Year : Year,
				Sec_Of_Law : Sec_Of_Law,
				Whether_Involved_In_Other_Unit_Cases : Whether_Involved_In_Other_Unit_Cases,
				Name_Of_Units : Name_Of_Units,
				No_Of_Cases : No_Of_Cases,
				Irkey : Irkey,
				Date_Of_Arrest : Date_Of_Arrest,
				Date_Of_Release : Date_Of_Release,
                		Brief_Facts : Brief_Facts,
			



             });
            });
			
            var dataString = JSON.stringify(jsonObj);
     
                $.ajax({
                    url: "pdact_submit.php",
                    type: "POST",
                    data: {json:dataString},
                    success: function(response){
                               alert(response);
                             }
                });                             

        });
      
    });    

    
</script>

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
	$("#datepickerID2").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	})     
	$("#datepickerID3").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	})     
});
</script>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />

    </head>

<body bgcolor="#5195BA">

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
    
        <script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>
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
    
    </body>

    </html>