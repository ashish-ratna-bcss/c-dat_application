<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Vehicle Search");
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        
        <!-- Search Form -->
        <table width="625" height="124" align="center">
          <tr>
            <th height="26" bgcolor="#A9D1F5" class="CDAT" scope="col">VEHICLE NUMBER SEARCH</th>
          </tr>
          <tr>
            <th width="555" height="90" bgcolor="#A9D1F5" class="CDAT" scope="col">
              <form id="form1" name="form1" method="post" action="">
                VEHICLE NO:
                <label for="textfield"></label>
                <input type="text" name="VEHICLE_NO" id="CAF" placeholder="Enter Vehicle No" required="required"
                       value="<?php echo isset($_POST['VEHICLE_NO']) ? htmlspecialchars($_POST['VEHICLE_NO']) : ''; ?>"/>
                <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" />
              </form>
            </th>
          </tr>
        </table>
        <p>&nbsp;</p>
        
        <?php
        // Check if form was submitted
        if (isset($_POST['VEHICLE_NO']) && !empty($_POST['VEHICLE_NO'])) {
            
            $serverName = "CPHYDERABAD1\DAU_HYD_2023";
            $connectionInfo = array( "Database"=>"CDATDUPL");
            $conn = sqlsrv_connect( $serverName, $connectionInfo );
            
            if( $conn === false ) {
                die( print_r( sqlsrv_errors(), true));
            }
            
            $number = trim($_POST['VEHICLE_NO']);
            
            // Use parameterized queries to prevent SQL injection
            $sql8 = "SELECT 'VEHICLE ADDRESS SEARCH' as PHONE1";
            $st8 = sqlsrv_query($conn, $sql8);
            
            $sql9 = "SELECT REGN_NO, FULLNAME AS NAME, FATHERNAME AS FATHER_NAME, 
                    FULLADDRESS + ', ' + CITY AS ADDRESS, PHONE AS PHONE_NO,
                    MKR_CLAS + ', COLOR: ' + COLOUR + ', ' + VEH_CLASS AS VEHICLE_TYPE, 
                    ENG_NO, CHAS_NO, CONVERT(VARCHAR, ISS_DT, 106) AS ISSUED_DATE 
                    FROM CDATDUPL.[dbo].[CDAT_RTA] 
                    WHERE REGN_NO LIKE ?";
            $params9 = array('%' . $number . '%');
            $st9 = sqlsrv_prepare($conn, $sql9, $params9);
            sqlsrv_execute($st9);
            
            if ($st9 === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #F9FBFC; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE1']) . "</div>";
            }
            
            // Display results table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #F9FBFC; padding: 10px;'>REGN_NO</th>
                <th style='color: #F9FBFC; padding: 10px;'>NAME</th>
                <th style='color: #F9FBFC; padding: 10px;'>FATHER_NAME</th>
                <th style='color: #F9FBFC; padding: 10px;'>ADDRESS</th>
                <th style='color: #F9FBFC; padding: 10px;'>PHONE_NO</th>
                <th style='color: #F9FBFC; padding: 10px;'>VEHICLE_TYPE</th>
                <th style='color: #F9FBFC; padding: 10px;'>ENG_NO</th>
                <th style='color: #F9FBFC; padding: 10px;'>CHAS_NO</th>
                <th style='color: #F9FBFC; padding: 10px;'>ISSUED_DATE</th>
                <th style='color: #F9FBFC; padding: 10px;'>QRCODE</th>
            </tr>";
            
            $rowCount = 0;
            while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
                $rowCount++;
                $bgColor1 = ($rowCount % 2 == 0) ? '#AED1F1' : '#C2E0FB';
                $bgColor2 = ($rowCount % 2 == 0) ? '#C2E0FB' : '#AED1F1';
                
                echo "<tr>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['REGN_NO']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['NAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FATHER_NAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['ADDRESS']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE_NO']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['VEHICLE_TYPE']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['ENG_NO']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CHAS_NO']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['ISSUED_DATE']) . "</font></td>";
                
                // QR Code
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'>";
                $qrData = 'REGNNO: ' . $row['REGN_NO'] . 
                         ' NAME: ' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', $row['NAME']) . 
                         ' FATHERNAME: ' . $row['FATHER_NAME'] . 
                         ' PHONE: ' . $row['PHONE_NO'] . 
                         ' ADDRESS: ' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', $row['ADDRESS']) . 
                         ' VEH_TYPE: ' . $row['VEHICLE_TYPE'] . 
                         ' ENG_NO: ' . $row['ENG_NO'] . 
                         ' CHAS_NO: ' . $row['CHAS_NO'];
                echo '<img height="100" width="100" src="../qrcode/php/qr_img.php?d=' . urlencode($qrData) . '">';
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            if ($rowCount == 0) {
                echo "<div style='text-align: center; margin: 20px 0;'>";
                echo "<font size='4' face='verdana' color='#F9FBFC'><b>No vehicle records found for: " . htmlspecialchars($number) . "</b></font>";
                echo "</div>";
            }
            
            sqlsrv_free_stmt($st9);
            sqlsrv_close($conn);
        }
        ?>
        
        <p>&nbsp;</p>
        <p>&nbsp;</p>
      </td>
    </tr>
  </table>
</div>

<?php layout_end(); ?>