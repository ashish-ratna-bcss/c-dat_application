<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("JRMS Ps Wise Search");
?>
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
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
        </tr>

  </table>
      <p>&nbsp;</p>
<table width="1021" height="163" align="center">
        <tr>
          <th height="31" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">JAIL RELEASE SEARCH BY POLICE STATION WISE</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="jrms_ps_wise_search1.php">
                     
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
     
 
<?php layout_end(); ?>
