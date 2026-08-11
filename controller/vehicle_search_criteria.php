<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Search Criteria");
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        <!-- Search Form -->
        <table width="625" height="124" align="center">
          <tr>
            <th height="26" bgcolor="#A9D1F5" class="CDAT" scope="col">VEHICLE SEARCH</th>
          </tr>
          <tr>
            <th width="555" height="90" bgcolor="#A9D1F5" class="CDAT" scope="col">
              <form id="form1" name="form1" method="post" action="">
                Select search criteria:
                <label for="textfield"></label>
                <select name="VEHICLE_SOURCE">
                  <option value="REGN_NO" <?php echo (isset($_POST['VEHICLE_SOURCE']) && $_POST['VEHICLE_SOURCE'] == 'REGN_NO') ? 'selected' : ''; ?>>VEHICLE_NO</option>
                  <option value="CHAS_NO" <?php echo (isset($_POST['VEHICLE_SOURCE']) && $_POST['VEHICLE_SOURCE'] == 'CHAS_NO') ? 'selected' : ''; ?>>CHASSIS_NO</option>
                  <option value="ENG_NO" <?php echo (isset($_POST['VEHICLE_SOURCE']) && $_POST['VEHICLE_SOURCE'] == 'ENG_NO') ? 'selected' : ''; ?>>ENGINE_NO</option>
                  <option value="PHONE" <?php echo (isset($_POST['VEHICLE_SOURCE']) && $_POST['VEHICLE_SOURCE'] == 'PHONE') ? 'selected' : ''; ?>>PHONE</option>
                </select>
                <input type="text" name="VEHICLE_NO" id="CAF" placeholder="Enter No" required="required"
                       value="<?php echo isset($_POST['VEHICLE_NO']) ? htmlspecialchars($_POST['VEHICLE_NO']) : ''; ?>"/>
                <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" />
              </form>
            </th>
          </tr>
        </table>
        <p>&nbsp;</p>
        
        <?php
        // Check if form was submitted
        if (isset($_POST['VEHICLE_SOURCE']) && isset($_POST['VEHICLE_NO']) && !empty($_POST['VEHICLE_NO'])) {
            
            $serverName = "CPHYDERABAD1\DAU_HYD_2023";
            $connectionInfo = array( "Database"=>"CDATDUPL");
            $conn = sqlsrv_connect( $serverName, $connectionInfo );
            
            if( $conn === false ) {
                die( print_r( sqlsrv_errors(), true));
            }
            
            $number = trim($_POST['VEHICLE_NO']);
            $number1 = $_POST['VEHICLE_SOURCE'];
            
            // Validate search criteria to prevent SQL injection
            $validColumns = array('REGN_NO', 'CHAS_NO', 'ENG_NO', 'PHONE');
            if (!in_array($number1, $validColumns)) {
                die('Invalid search criteria');
            }
            
            // Use parameterized queries with column name validation
            $sql8 = "SELECT 'VEHICLE ADDRESS SEARCH' as PHONE1";
            $st8 = sqlsrv_query($conn, $sql8);
            
            // Build query with validated column name
            $sql9 = "SELECT DISTINCT REGN_NO, FULLNAME AS NAME, FATHERNAME AS FATHER_NAME, 
                    FULLADDRESS + ', ' + CITY AS ADDRESS, PHONE AS PHONE_NO,
                    MKR_CLAS + ', COLOR: ' + COLOUR + ', ' + VEH_CLASS AS VEHICLE_TYPE, 
                    ENG_NO, CHAS_NO, CONVERT(VARCHAR, ISS_DT, 106) AS ISSUED_DATE 
                    FROM CDATDUPL.[dbo].[CDAT_RTA] 
                    WHERE " . $number1 . " LIKE ?";
            $params9 = array('%' . $number . '%');
            $st9 = sqlsrv_prepare($conn, $sql9, $params9);
            sqlsrv_execute($st9);
            
            if ($st9 === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #4b495a; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE1']) . "</div>";
            }
            
            // Display results table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' id='mytable' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>REGN_NO</th>
                <th style='color: #4b495a; padding: 10px;'>NAME</th>
                <th style='color: #4b495a; padding: 10px;'>FATHER_NAME</th>
                <th style='color: #4b495a; padding: 10px;'>ADDRESS</th>
                <th style='color: #4b495a; padding: 10px;'>PHONE_NO</th>
                <th style='color: #4b495a; padding: 10px;'>VEHICLE_TYPE</th>
                <th style='color: #4b495a; padding: 10px;'>ENG_NO</th>
                <th style='color: #4b495a; padding: 10px;'>CHAS_NO</th>
                <th style='color: #4b495a; padding: 10px;'>ISSUED_DATE</th>
                <th style='color: #4b495a; padding: 10px;'>QRCODE</th>
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
                echo "<font size='4' face='verdana' color='#4b495a'><b>No vehicle records found for: " . htmlspecialchars($number) . "</b></font>";
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

<!-- Include jQuery and dropdown filter scripts -->
<script src="../assets/vendor/drop-down-filter/jquery.min.js"></script>
<script src="../assets/vendor/drop-down-filter/ddtf.js"></script>
<script>
    $(document).ready(function() {
        $('#mytable').ddTableFilter();
    });
</script>

<?php layout_end(); ?>