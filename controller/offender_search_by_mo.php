<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Offender Search By MO");
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        <!-- Search Form -->
        <table width="800" height="100" align="center">
          <tr>
            <th height="27" bgcolor="#A9D1F5" class="CDAT" scope="col">OFFENDER SEARCH BY SUB CLASSIFICATION</th>
          </tr>
          <tr>
            <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col">
              <form id="form1" name="form1" method="post" action="">
                MO SUB CLASSIFICATION:
                <label for="textfield"></label>
                <input type="text" name="MO" id="NAME" placeholder="SUB CLASSIFICATION" required="required"
                       value="<?php echo isset($_POST['MO']) ? htmlspecialchars($_POST['MO']) : ''; ?>"/>
                <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" />
              </form>
            </th>
          </tr>
        </table>
        <p>&nbsp;</p>
        
        <?php
        // Check if form was submitted
        if (isset($_POST['MO']) && !empty($_POST['MO'])) {
            
            $serverName = "CPHYDERABAD1\DAU_HYD_2023";
            $connectionInfo = array( "Database"=>"CDATDUPL");
            $conn = sqlsrv_connect( $serverName, $connectionInfo );
            
            if( $conn === false ) {
               // die( print_r( sqlsrv_errors(), true));
            }
            
            $number = trim($_POST['MO']);
            
            // Use parameterized queries to prevent SQL injection
            $sql8 = "SELECT 'DETAILS OF : ' + ? as PHONE1";
            $params8 = array($number);
            $st8 = sqlsrv_prepare($conn, $sql8, $params8);
            sqlsrv_execute($st8);
            
            $sql9 = "SELECT DISTINCT MO_KEY, ACC_NAME AS ACCUSED_NAME, FATHER_NAME, AGE, MO1, MO2, POLICE_STATION 
                    FROM CDATDUPL..COMPLETE_MO_CLASSIFICATION
                    WHERE (MO1 LIKE ? OR MO2 LIKE ? OR CRIME_HEAD LIKE ?)";
            $searchPattern = '%' . str_replace(' ', '%', $number) . '%';
            $params9 = array($searchPattern, $searchPattern, $searchPattern);
            $st9 = sqlsrv_prepare($conn, $sql9, $params9);
            sqlsrv_execute($st9);
            
            if ($st9 === false) {
              //  die(print_r(sqlsrv_errors(), true));
            }
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #4b495a; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE1']) . "</div>";
            }
            
            // Display results table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>MO_KEY</th>
                <th style='color: #4b495a; padding: 10px;'>ACCUSED NAME</th>
                <th style='color: #4b495a; padding: 10px;'>FATHER_NAME</th>
                <th style='color: #4b495a; padding: 10px;'>AGE</th>
                <th style='color: #4b495a; padding: 10px;'>MO_SUB_CLASSIFICATION1</th>
                <th style='color: #4b495a; padding: 10px;'>MO_SUB_CLASSIFICATION2</th>
                <th style='color: #4b495a; padding: 10px;'>POLICE_STATION</th>
            </tr>";
            
            $rowCount = 0;
            while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
                $rowCount++;
                $bgColor1 = ($rowCount % 2 == 0) ? '#AED1F1' : '#C2E0FB';
                $bgColor2 = ($rowCount % 2 == 0) ? '#C2E0FB' : '#AED1F1';
                
                echo "<tr>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'><a href='offender_fd.php?MO_KEY=" . urlencode($row['MO_KEY']) . "'>" . htmlspecialchars($row['MO_KEY']) . "</a></font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['ACCUSED_NAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FATHER_NAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['AGE']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['MO1']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['MO2']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['POLICE_STATION']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            if ($rowCount == 0) {
                echo "<div style='text-align: center; margin: 20px 0;'>";
                echo "<font size='4' face='verdana' color='#4b495a'><b>No records found for: " . htmlspecialchars($number) . "</b></font>";
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