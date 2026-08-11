<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("ISD Contacts");
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        <table width="1313" height="148">
          <tr>
            <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
          </tr>
        </table>
        <p>&nbsp;</p>
        
        <!-- Search Form -->
        <table width="862" height="160" align="center">
          <tr>
            <th height="27" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">ISD CONTACTS SUMMARY OF MOBILE NUMBER</th>
          </tr>
          <tr>
            <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">
              <form id="form1" name="form1" method="post" action="">
                <p>
                  <label for="SUM" font face="verdana"> Mobile No:</label>
                  <input type="text" name="PHONE_NO" id="SUM" placeholder="Enter Mobile No" required="required" 
                         value="<?php echo isset($_POST['PHONE_NO']) ? htmlspecialchars($_POST['PHONE_NO']) : ''; ?>"/>
                  <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
                </p>
              </form>
              <div align="justify">
                <table width="734" height="25">
                  <tr>
                    <th width="40" scope="col">&nbsp;</th>
                    <th width="8" scope="col">&nbsp;</th>
                    <th width="79" scope="col">&nbsp;</th>
                    <th width="368" scope="col">&nbsp;</th>
                    <th width="90" scope="col">&nbsp;</th>
                  </tr>
                </table>
              </div>
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
            
            // Sanitize input
            $number = $_POST['PHONE_NO'];
            
            // Use parameterized queries to prevent SQL injection
            $sql1 = "SELECT DISTINCT * INTO #XX FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE phone = ?";
            $params1 = array($number);
            $st1 = sqlsrv_prepare($conn, $sql1, $params1);
            sqlsrv_execute($st1);
            
            $sql3 = "SELECT * INTO #TEMP FROM CDAT_DETAILS1 WHERE LEN(OTHER) > 10 AND DURATION > '0' AND PHONE = ?";
            $params3 = array($number);
            $st3 = sqlsrv_prepare($conn, $sql3, $params3);
            sqlsrv_execute($st3);
            
            $sql4 = "SELECT DISTINCT * INTO #TT FROM #TEMP";
            $st4 = sqlsrv_query($conn, $sql4);
            
            $sql5 = "SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
                    SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
                    SUM(CASE WHEN INCOMING ='0' THEN 1 ELSE 0 END) AS 'OUT',
                    COUNT(PHONE) AS CALLS, SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
                    CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRSTCALL,
                    CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LASTCALL 
                    INTO #RESULT FROM #TT 
                    GROUP BY PHONE, OTHER ORDER BY CALLS DESC";
            $st5 = sqlsrv_query($conn, $sql5);
            
            $sql6 = "SELECT A.PHONE, 
                    CASE WHEN A.OTHER = B.PHONE THEN OTHER + ', - ' + NICKNAME ELSE OTHER END AS OTHER,
                    [IN],[OUT], CALLS, DUR, FIRSTCALL, LASTCALL,
                    ISNULL(AREADESCRIPTION, 'CODE N/A') AS ADDRESS 
                    INTO #WITHADDRESS FROM #RESULT A 
                    LEFT JOIN CDATDUPL.DBO.cdatsuspect B ON a.other = B.phone 
                    LEFT JOIN CDATDUPL.DBO.cdatphonearea C ON '00' + other LIKE phoneprefix + '%' 
                    WHERE A.OTHER NOT LIKE '1800%'
                    GROUP BY a.PHONE, B.PHONE, other, [IN],[OUT], calls, dur, FIRSTCALL, LASTCALL, nickname, AREADESCRIPTION";
            $st6 = sqlsrv_query($conn, $sql6);
            
            $sql7 = "SELECT * FROM #WITHADDRESS WHERE ADDRESS != ' JUNK-COULD BE bulk SMS or VOIP calls' ORDER BY calls DESC";
            $st7 = sqlsrv_query($conn, $sql7);
            
            $sql8 = "SELECT 'ISD CONTACTS OF MOBILE NO: ' + ? AS PHONE1";
            $params8 = array($number);
            $st8 = sqlsrv_prepare($conn, $sql8, $params8);
            sqlsrv_execute($st8);
            
            $sql9 = "SELECT ? AS PHONE, '' AS FIRST_CALL, '' AS LAST_CALL, '' AS NICKNAME, '' AS LAST_UPDATED INTO #T";
            $params9 = array($number);
            $st9 = sqlsrv_prepare($conn, $sql9, $params9);
            sqlsrv_execute($st9);
            
            $sql10 = "SELECT A.PHONE, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL, 
                      CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL, B.NICKNAME, 
                      CONVERT(VARCHAR, MAX(A.ASONDATE), 20) AS LAST_UPDATED 
                      INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A 
                      LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE = B.PHONE 
                      WHERE A.PHONE = ? GROUP BY A.PHONE, B.NICKNAME";
            $params10 = array($number);
            $st10 = sqlsrv_prepare($conn, $sql10, $params10);
            sqlsrv_execute($st10);
            
            $sql11 = "SELECT DISTINCT A.PHONE,
                      CASE WHEN A.PHONE = B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
                      CASE WHEN A.PHONE = B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
                      CASE WHEN A.PHONE = B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
                      CASE WHEN A.PHONE = B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
                      CASE WHEN A.PHONE = C.PHONE THEN ISNULL(C.FULLNAME, '') + ', ' + ISNULL(C.FULLADDRESS, '') + ', ' + ISNULL(CONVERT(VARCHAR, C.DOA, 20), '') + ', ' +
                      (CASE WHEN C.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION, '') ELSE C.CATEGORY_TYPE END)
                      WHEN A.PHONE = D.PHONE THEN ISNULL(D.FULLNAME, '') + ', ' + ISNULL(D.FULLADDRESS, '') + ', ' + ISNULL(CONVERT(VARCHAR, D.DOA, 20), '') + ', ' +
                      (CASE WHEN D.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION, '') ELSE D.CATEGORY_TYPE END) 
                      ELSE ISNULL(AREADESCRIPTION, '') END AS ADDRESS 
                      FROM #T A
                      LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
                      LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
                      LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE) = 10 THEN A.PHONE 
                      ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
                      LIKE PHONEPREFIX + '%'
                      LEFT JOIN #S B ON A.PHONE = B.PHONE";
            $st11 = sqlsrv_query($conn, $sql11);
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #333; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE1']) . "</div>";
            }
            
            // Display Phone Information Table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr bgcolor='#921215'>
                        <th style='color: #F9FBFC; padding: 10px;'>PHONE</th>
                        <th style='color: #F9FBFC; padding: 10px;'>FIRST_CALL</th>
                        <th style='color: #F9FBFC; padding: 10px;'>LAST_CALL</th>
                        <th style='color: #F9FBFC; padding: 10px;'>NICKNAME</th>
                        <th style='color: #F9FBFC; padding: 10px;'>ADDRESS</th>
                    </tr>";
            
            while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
                echo "<tr>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FIRST_CALL']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_CALL']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['NICKNAME']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['ADDRESS']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table><br />";
            
            // Display Call Details Table
            echo "<h3 style='color: #333; margin-top: 30px;'>Call Details</h3>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr bgcolor='#921215'>
                        <th style='color: #F9FBFC; padding: 10px;'>PHONE</th>
                        <th style='color: #F9FBFC; padding: 10px;'>OTHER</th>
                        <th style='color: #F9FBFC; padding: 10px;'>IN</th>
                        <th style='color: #F9FBFC; padding: 10px;'>OUT</th>
                        <th style='color: #F9FBFC; padding: 10px;'>CALLS</th>
                        <th style='color: #F9FBFC; padding: 10px;'>DUR</th>
                        <th style='color: #F9FBFC; padding: 10px;'>FIRST_CALL</th>
                        <th style='color: #F9FBFC; padding: 10px;'>LAST_CALL</th>
                        <th style='color: #F9FBFC; padding: 10px;'>ADDRESS</th>
                    </tr>";
            
            while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
                echo "<tr>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['IN']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OUT']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CALLS']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['DUR']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FIRSTCALL']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LASTCALL']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['ADDRESS']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            sqlsrv_free_stmt($st7);
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