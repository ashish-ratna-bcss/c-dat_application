<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
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
	
});
</script>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarVertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
	
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
</style>

</head>

<body bgcolor="#5195BA">
<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="select distinct PSARRESTED from jrms..JRMS_TOTAL_2012_TO_2017
where jailname in ('CHERLAPALLI','CHANCHALGUDA','CHANCHALGUDA WOMEN') AND  jailname in ('CHERLAPALLI','CHANCHALGUDA','CHANCHALGUDA WOMEN')
and psarrested in ('Abidroad','Bahadurpura','Afzalgunj','Amberpet','Asifnagar','Banjara Hills','Begumbazar','Begumpet','Bhavaninagar',
'Bollarum','Bowenpally','CCS','CCS HYD','Chaderghat','Chandrayanagutta','Charminar','Chatrinaka',
'Chikkadpally','Chilkalguda','CYBER CRIME CCS','CYBER CRIME PS','Dabeerpura','Falaknuma','Gandhinagar',
'Golconda','Gopalapuram','Habeebnagar','Humayunnagar','Hussainialam','Jubilee Hills','KACHEGUDA','Kachiguda','Kalapathar',
'KAMATIPURA','Kanchanbagh','Karkhana','Lalaguda','Langer House','Madannapet','Mahankali','Malakpet',
'Mangalhat','Market','Marredpally','Mirchowk','Moghalpura','Musheerabad','Nallakunta',
'Nampally','Narayanaguda','Osmania University','Panjagutta','RAINBAZAR','Ramgopalpet',
'Reinbazar','Saidabad','Saifabad','Sanjeevareddynagar','Shahalibanda','Shahinayathgunj','SR NAGAR',
'Sultanbazar','Tappachabutra','THIRUMALAGIRI','THUKARAMGATE','Trimulgherry','Tukaramgate',
'WPS SouthZone','SANTOSHNAGAR','Is Sadan','Bandlaguda','Domalguda','Secretariat','Khairatabad','Warasiguda','Gudimalkapur','Masab tank','Film nagar','Madhuranagar','Borabanda')";
$results = $db_handle->runQuery($query);
?>
<div align="center">
  <table width="1323" height="100" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/TOPBORDER.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            <li><a href="HOME.html">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="SUM_HOME.HTML">Summary Total</a></li>
                <li><a href="SUM_BETWEEN_DATES.html">Summary Between Dates</a></li>
                <li><a href="SUM_ISD_CNTS.html">Summary of ISD Contacts</a></li>
                <li><a href="SUM_NEW_NOS.html">Summary of New Contacts</a></li>
                <li><a href="SUM_IN_STATE.html">Summary Within a State</a></li>
                <li><a href="SUM_OUT_STATE.html">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="MOVEMENTS.html"> MOVEMENTS </a></li>
		<li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS.html">Movements Btwn Two Nos</a></li>
		<li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS_COMPARISION.html">Movements Btwn Two Nos Comparision</a></li> 
		<!----<li><a href="CALLS_TOT.html">Call Details Total</a></li>--->
                <li><a href="CALLS_BTWN_DATES.html">Calls Between Dates</a></li>
                <!----<li><a href="CALLS_BT_NOS.html">Calls Between Two Numbers</a></li>---->
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="CDATCNTS.HTML">Cdat Cnts</a></li>
		<li><a href="BULK_CDAT_CONTACTS.HTML">Bulk Cdat Contacts</a></li>
		<li><a href="OTHERSCDAT.HTML">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="IMEISEARCH.HTML">Phones used in Imei</a></li>
                <li><a href="IMEISINPHONE.HTML">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="ADDRESS.HTML">Single Address</a></li>
                <li><a href="BULKADDRESS.HTML">Bulk Addresses</a></li>
              </ul>
            </li>
             <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
               <ul>
                <li><a href="DAY%26NIGHTLOC.HTML">Top 10 Day Night Loc</a></li>
                <li><a href="DAY%26NIGHTLOC_BTWN_DATES.HTML">Top 10 Day Night Loc Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Wanted</a>
                  <ul>
                    <li><a href="wanted1.html">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
              <ul>
                <li><a href="CELLID_SEARCH.HTML">Cellid Search</a></li>
                <li><a href="VEHICLE_SEARCH.HTML">Vehicle Search</a></li>
                <li><a href="COMMON_CNTS.HTML">Common Cnts</a></li>
                </ul>
            </li>
          </ul></td>
        </tr>

  </table>
      <p>&nbsp;</p>
<table width="1021" height="163" align="center">
        <tr>
          <th height="31" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">JAIL RELEASE SEARCH BY POLICE STATION WISE</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col"><form id="form1" name="form1" method="post" action="JRMS_PS_WISE_SEARCH1.php">
                     
         Date From: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="yyyy/mm/dd" required="required"/>
              To:
              <input type="text" name="TO_DT" id="datepickerID1" size="10" placeholder="yyyy/mm/dd" required="required"/>
             POLICE_STATION: <select name="PSARRESTED">
<option value="">Select POLICE STATION</option>
<?php
foreach($results as $PSARRESTED) {
?>
<option value="<?php echo $PSARRESTED["PSARRESTED"]; ?>"> <?php echo $PSARRESTED["PSARRESTED"]; ?> </option>
<?php
}
?>
</select>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />     
          </form></th>
        </tr>
     
 

<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
</html>dy>
</html>
