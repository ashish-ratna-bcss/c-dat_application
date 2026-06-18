<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"PDACT");
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

  $arr = json_decode($_POST['json']);


  for($i=0; $i<count($arr); $i++)
{


$sql = "INSERT INTO PDACT_MAIN_TABLE (Name, Father_Name,Age, Dob, Occupation, Caste, Id_Proof, Id_Proof_No,PDACT_CALL_KEY, Phone_No, Irkey, Present_Address, Permanent_Address,District, State,PD_ACT_PS, Zone, File_No, File_No_Year,Detenu_No,Order_Issued_On, Approval_Orders_No, Confirmation_Revocation_Orders, 
Crime_Head, Minor_Head, ModusOperendi, Police_Station, Crime_No, Year, 
Sec_Of_Law, Whether_Involved_In_Other_Unit_Cases, Name_Of_Units, 
No_Of_Cases, Date_Of_Arrest, Date_Of_Release, Brief_Facts,ASONDATE) 
VALUES ('".$arr[$i]->Name."','".$arr[$i]->Father_Name."','".$arr[$i]->Age."','".$arr[$i]->Dob."','".$arr[$i]->Occupation."','".$arr[$i]->Caste."','".$arr[$i]->Id_Proof."','".$arr[$i]->Id_Proof_No."','".$arr[$i]->PRE_PDACT_KEY."','".$arr[$i]->Phone_No."','".$arr[$i]->Irkey."','".$arr[$i]->Present_Address."','".$arr[$i]->Permanent_Address."','".$arr[$i]->District."','".$arr[$i]->State."','".$arr[$i]->PD_ACT_PS."','".$arr[$i]->Zone."','".$arr[$i]->File_No."','".$arr[$i]->File_No_Year."','".$arr[$i]->Detenu_No."','".$arr[$i]->Order_Issued_On."','".$arr[$i]->Approval_Orders_No."','".$arr[$i]->Confirmation_Revocation_Orders."','".$arr[$i]->Crime_Head."','".$arr[$i]->Minor_Head."','".$arr[$i]->ModusOperendi."','".$arr[$i]->Police_Station."','".$arr[$i]->Crime_No."','".$arr[$i]->Year."','".$arr[$i]->Sec_Of_Law."','".$arr[$i]->Whether_Involved_In_Other_Unit_Cases."','".$arr[$i]->Name_Of_Units."','".$arr[$i]->No_Of_Cases."','".$arr[$i]->Date_Of_Arrest."','".$arr[$i]->Date_Of_Release."','".$arr[$i]->Brief_Facts."',getdate())";
			
$st8 = sqlsrv_query( $conn, $sql);

		}

		echo json_encode(['success'=>'Names Inserted successfully.']);


?>