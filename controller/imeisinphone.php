<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("IMEIs used in Phone");
require_once __DIR__ . '/sql_safe.php';
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        <!-- Search Form -->
        <table width="625" height="101" align="center">
          <tr>
            <th height="28" bgcolor="#A9D1F5" class="CDAT" scope="col">IMEIS USED IN PHONE NUMBER</th>
          </tr>
          <tr>
            <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col">
              <form id="form1" name="form1" method="post" action="">
                PHONE NO:
                <label for="textfield"></label>
                <input type="text" name="PHONE_NO" id="IMEI_IN_PHONE" placeholder="Enter Mobile No" required="required"
                       value="<?php echo isset($_POST['PHONE_NO']) ? htmlspecialchars($_POST['PHONE_NO']) : ''; ?>"/>
                <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" />
              </form>
            </th>
          </tr>
        </table>
        <p>&nbsp;</p>
        
        <?php
        // Check if form was submitted
        if (isset($_POST['PHONE_NO']) && !empty($_POST['PHONE_NO'])) {
            
            $serverName = "CPHYDERABAD1\DAU_HYD_2023";
            $connectionInfo = array( "Database"=>"CDATDUPL");
            $conn = sqlsrv_connect( $serverName, $connectionInfo );
            
            if( $conn === false ) {
                die( print_r( sqlsrv_errors(), true));
            }
            
            $number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
            
            // Use parameterized queries to prevent SQL injection
            $sql1 = "SELECT * INTO #T FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE = ?";
            $params1 = array($number);
            $st1 = sqlsrv_prepare($conn, $sql1, $params1);
            sqlsrv_execute($st1);
            sqlsrv_render_query_error($st1, 'Phone CDR lookup');
            
            $sql2 = "SELECT DISTINCT PHONE, IMEINUMBER,
                    SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
                    SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT',
                    COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR,
                    CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL,
                    CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL 
                    INTO #TT FROM #T
                    GROUP BY PHONE, IMEINUMBER ORDER BY LAST_CALL";
            $st2 = sqlsrv_query($conn, $sql2);
            sqlsrv_render_query_error($st2, 'IMEI aggregation');
            
            $sql3 = "SELECT A.PHONE, IMEINUMBER, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, 
                    CASE WHEN C.PHONE IS NOT NULL
                    THEN COALESCE(C.FULLNAME + ', ' + C.FULLADDRESS, '') + ' ' + COALESCE(C.CATEGORY_TYPE, '')
                    WHEN D.PHONE IS NOT NULL
                    THEN COALESCE(D.FULLNAME + ', ' + D.FULLADDRESS, '') + ' ' + COALESCE(D.CATEGORY_TYPE, '')
                    ELSE AREADESCRIPTION END AS ADDRESS FROM #TT A
                    LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
                    LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
                    LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON A.PHONE LIKE PHONEPREFIX + '%'
                    ORDER BY LAST_CALL";
            $st3 = sqlsrv_query($conn, $sql3);
            sqlsrv_render_query_error($st3, 'Address join');
            
            $sql4 = "SELECT 'LIST OF IMEIS USED IN PHONE NO: ' + ? as PHONE1";
            $params4 = array($number);
            $st4 = sqlsrv_prepare($conn, $sql4, $params4);
            sqlsrv_execute($st4);
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st4, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #F9FBFC; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE1']) . "</div>";
            }
            
            // Display results table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #F9FBFC; padding: 10px;'>PHONE</th>
                <th style='color: #F9FBFC; padding: 10px;'>IMEINUMBER</th>
                <th style='color: #F9FBFC; padding: 10px;'>IN</th>
                <th style='color: #F9FBFC; padding: 10px;'>OUT</th>
                <th style='color: #F9FBFC; padding: 10px;'>CALLS</th>
                <th style='color: #F9FBFC; padding: 10px;'>DUR</th>
                <th style='color: #F9FBFC; padding: 10px;'>FIRST_CALL</th>
                <th style='color: #F9FBFC; padding: 10px;'>LAST_CALL</th>
                <th style='color: #F9FBFC; padding: 10px;'>ADDRESS</th>
            </tr>";
            
            while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
                echo "<tr>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['IMEINUMBER']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['IN']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OUT']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CALLS']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['DUR']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FIRST_CALL']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_CALL']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['ADDRESS']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            sqlsrv_free_stmt($st3);
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