<?php
// Login gate. The .htaccess extension fallback resolves every spelling of this
// page (.php / .html / .htm, any case) to this one file, so guarding it here
// protects all of them. Must run before any output, or the redirect is lost.
require_once __DIR__ . '/activity_logger.php';
audit_require_session();
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Dashboard");
?>
<div align="center">
  <table width="1323" height="603" border="2">
<tr>
  <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
        </tr>
      </table>
<marquee behavior="scroll" direction="right"> <font color="YELLOW" face=verdana size='2'><b> *** PLEASE MAIL RAW DATA TO cdranalysiswing@gmail.com TO VIEW REPORTS *** </b></font></marquee> 
        <table width="1307" height="347">
          <tr>
            <td width="214" rowspan="2" valign="top">
              <blockquote>
                
        <p>&nbsp; </p>
      </blockquote></td>
            <td width="1015" height="24" align="left" valign="top"><p align="center" class="FONT">CALL DATA ANALYSIS TOOL</p></td>
            <td width="62" rowspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td height="310" align="left" valign="top"><div align="center"><img src="../assets/images/analysis1.jpg" width="950" height="250" /></div></td>
          </tr>

       </table>
     <table align="center"> <tr align="center">
<td width="1015" height="24" align="center" valign="top"><font size="6" font color="YELLOW">*** mail to <font color="#B22222">natgrid-hyd@tspolice.gov.in</font> for Suspect Image search ***</font></td>

<marquee behavior="scroll" direction="left"> <font face=verdana size='2' class="confirm_selection"><!-----<b> *** NOW YOU CAN VIEW UNDETECTED CASES MATCHED WITH OLD OFFENDERS FINGER PRINTS LIST  (ADDED IN OFFENDERS LIST TAB) *** </b></font></marquee>
<!-----<marquee behavior="scroll" direction="left"> <font face=verdana size='2' class="confirm_selection"><b> *** NOW YOU CAN VIEW HABITUAL OFFENDERS LIST  (ADDED IN OFFENDERS LIST TAB) *** </b></font></marquee>--> 
</tr>
</table>
            

<p>&nbsp;</p>
    
  
<?php layout_end(); ?>
