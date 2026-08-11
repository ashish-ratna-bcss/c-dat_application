<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Call Details Between Dates");
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/sql_safe.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
     
        
        <!-- Search Form -->
        <table width="1000" height="160" align="center">
          <tr>
            <th height="27" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">CALL DETAILS BETWEEN DATES</th>
          </tr>
          <tr>
            <th align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">
              <form id="form1" name="form1" method="POST" action="">
                <p>
                  <label for="SUM" font="" face="verdana">Mobile No:</label>
                  <input type="text" name="PHONE_NO" id="calls" placeholder="Enter Mobile No" required="required" 
                         value="<?php echo isset($_POST['PHONE_NO']) ? htmlspecialchars($_POST['PHONE_NO']) : ''; ?>">
                  
                  <label for="FROM_DT" font="" face="verdana">From Date:</label>
                  <input type="date" name="FROM_DT" id="FROM_DT" required="required"
                         value="<?php echo isset($_POST['FROM_DT']) ? htmlspecialchars($_POST['FROM_DT']) : ''; ?>">
                  
                  <label for="TO_DT" font="" face="verdana">To Date:</label>
                  <input type="date" name="TO_DT" id="TO_DT" required="required"
                         value="<?php echo isset($_POST['TO_DT']) ? htmlspecialchars($_POST['TO_DT']) : ''; ?>">
                  
                  <br><br>
                  
                  <label for="OPERATOR" font="" face="verdana">Operator:</label>
                  <input type="text" name="OPERATOR" id="OPERATOR" placeholder="Operator" 
                         value="<?php echo isset($_POST['OPERATOR']) ? htmlspecialchars($_POST['OPERATOR']) : ''; ?>">
                  
                  <label for="STATE" font="" face="verdana">State:</label>
                  <input type="text" name="STATE" id="STATE" placeholder="State" 
                         value="<?php echo isset($_POST['STATE']) ? htmlspecialchars($_POST['STATE']) : ''; ?>">
                  
                  <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit">
                </p>
              </form>
            </th>
          </tr>
        </table>
        <p>&nbsp;</p>
        
        <?php
        // Check if form was submitted
        if (isset($_POST['PHONE_NO']) && !empty($_POST['PHONE_NO']) && 
            isset($_POST['FROM_DT']) && !empty($_POST['FROM_DT']) && 
            isset($_POST['TO_DT']) && !empty($_POST['TO_DT'])) {
            
            audit_require_session();
            
            $serverName = "CPHYDERABAD1\DAU_HYD_2023";
            $connectionInfo = array( "Database"=>"CDATDUPL");
            $conn = sqlsrv_connect( $serverName, $connectionInfo );
            
            if( $conn === false ) {
                die( print_r( sqlsrv_errors(), true));
            }
            
            // Sanitize input
            $number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
            $operator = sql_safe_alnum($_POST['OPERATOR'] ?? '', 50);
            $state = sql_safe_alnum($_POST['STATE'] ?? '', 50);
            $f_date = sql_safe_alnum($_POST['FROM_DT'] ?? '', 10);
            $t_date = sql_safe_alnum($_POST['TO_DT'] ?? '', 10);
            
            // Audit log
            audit_log('Call Details Between Dates', 'Search', [
                'phone_number' => $number, 
                'from_date' => $f_date, 
                'to_date' => $t_date,
                'state' => $state, 
                'operator' => $operator
            ]);
            
            // Use parameterized queries to prevent SQL injection
            $sql1 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
                    CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
                    IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  
                    INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT 
                    WHERE PHONE = ? AND convert(char(10),STARTTIME,121) BETWEEN ? AND ?";
            $params1 = array($number, $f_date, $t_date);
            $st1 = sqlsrv_prepare($conn, $sql1, $params1);
            sqlsrv_execute($st1);
            sqlsrv_render_query_error($st1, 'Calls between dates base');
            
            // Use the cdr_sql_enrich_tt function
            $sql2 = cdr_sql_enrich_tt($operator, $state);
            $st2 = sqlsrv_query($conn, $sql2);
            sqlsrv_render_query_error($st2, 'Tower enrichment');
            
            $sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION 
                     FROM #temp_cdrs ORDER BY STARTTIME";
            $st5 = sqlsrv_query($conn, $sql5);
            sqlsrv_render_query_error($st5, 'Result ordering');
            
            $sql6 = "SELECT 'CALL DETAILS OF MOBILE NO: ' + ? + ' FROM: ' + ? + ' TO: ' + ? AS PHONE";
            $params6 = array($number, $f_date, $t_date);
            $st6 = sqlsrv_prepare($conn, $sql6, $params6);
            sqlsrv_execute($st6);
            sqlsrv_render_query_error($st6, 'Title');
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #F9FBFC; text-align: center; margin: 20px 0;'>" . h($row['PHONE'] ?? '') . "</div>";
            }
            
            // Display results table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #F9FBFC; padding: 10px;'>PHONE</th>
                <th style='color: #F9FBFC; padding: 10px;'>OTHER</th>
                <th style='color: #F9FBFC; padding: 10px;'>NICK NAME</th>
                <th style='color: #F9FBFC; padding: 10px;'>STARTTIME</th>
                <th style='color: #F9FBFC; padding: 10px;'>DUR</th>
                <th style='color: #F9FBFC; padding: 10px;'>TYPE</th>
                <th style='color: #F9FBFC; padding: 10px;'>IMEI</th>
                <th style='color: #F9FBFC; padding: 10px;'>CELLID</th>
                <th style='color: #F9FBFC; padding: 10px;'>OPERATOR</th>
                <th style='color: #F9FBFC; padding: 10px;'>AREA DESCRIPTION</th>
            </tr>";
            
            while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
                echo "<tr>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['PHONE'] ?? '') . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['OTHER'] ?? '') . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['NICKNAME'] ?? '') . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['STARTTIME'] ?? '') . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['DURATION'] ?? '') . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['TYPE'] ?? '') . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['IMEINUMBER'] ?? '') . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['CELLTOWERID'] ?? '') . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['OPERATOR'] ?? '') . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . h($row['AREADESCRIPTION'] ?? '') . "</font></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            sqlsrv_free_stmt($st5);
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