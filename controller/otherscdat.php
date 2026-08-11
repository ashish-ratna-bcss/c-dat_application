<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Others Cdat");
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
       
        
        <!-- Search Form -->
        <table width="625" height="106" align="center">
          <tr>
            <th height="28" bgcolor="#A9D1F5" class="CDAT" scope="col">OTHERS CDAT CONTACTS</th>
          </tr>
          <tr>
            <th width="555" height="70" bgcolor="#A9D1F5" class="CDAT" scope="col">
              <form id="form1" name="form1" method="post" action="">
                MOBILE NO:
                <label for="textfield"></label>
                <input type="text" name="PHONE_NO" id="SUM" placeholder="Enter Mobile No" required="required"
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
            
            $number = $_POST['PHONE_NO'];
            
            // Use parameterized queries to prevent SQL injection
            $sql1 = "SELECT ? AS PHONE, '' AS FIRST_CALL, '' AS LAST_CALL, '' AS NICKNAME, '' AS LAST_UPDATED INTO #T";
            $params1 = array($number);
            $st1 = sqlsrv_prepare($conn, $sql1, $params1);
            sqlsrv_execute($st1);
            
            $sql2 = "SELECT A.PHONE, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL, B.NICKNAME, CONVERT(VARCHAR, MAX(A.ASONDATE), 20) AS LAST_UPDATED 
            INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE = B.PHONE WHERE A.PHONE = ? GROUP BY A.PHONE, B.NICKNAME";
            $params2 = array($number);
            $st2 = sqlsrv_prepare($conn, $sql2, $params2);
            sqlsrv_execute($st2);
            
            $sql3 = "SELECT DISTINCT A.PHONE,
                    CASE WHEN A.PHONE = B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
                    CASE WHEN A.PHONE = B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
                    CASE WHEN A.PHONE = B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
                    CASE WHEN A.PHONE = B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
                    CASE WHEN A.PHONE = C.PHONE THEN ISNULL(C.FULLNAME, '') + ', ' + ISNULL(C.FULLADDRESS, '') + ', ' + ISNULL(C.CATEGORY_TYPE, '') 
                    WHEN A.PHONE = D.PHONE THEN ISNULL(D.FULLNAME, '') + ', ' + ISNULL(D.FULLADDRESS, '') + ', ' + ISNULL(D.CATEGORY_TYPE, '') 
                    ELSE ISNULL(AREADESCRIPTION, '') END AS ADDRESS 
                    FROM #T A
                    LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
                    LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
                    LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE) = 10 THEN A.PHONE 
                    ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
                    LIKE PHONEPREFIX + '%'
                    LEFT JOIN #S B ON A.PHONE = B.PHONE";
            $st3 = sqlsrv_query($conn, $sql3);
            
            $sql4 = "SELECT DISTINCT OTHER INTO #TEMP FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE = ?
                    AND LEN(OTHER) >= 10 AND ISNUMERIC(OTHER) = 1 AND SUBSTRING(OTHER,1,1) IN ('7','8','9')
                    AND OTHER NOT IN (SELECT DISTINCT OTHER FROM CDAT_IMPORT.dbo.CALLCENTER_NOS)";
            $params4 = array($number);
            $st4 = sqlsrv_prepare($conn, $sql4, $params4);
            sqlsrv_execute($st4);
            
            $sql5 = "SELECT DISTINCT PHONE, OTHER,
                    SUM(CASE WHEN INCOMING = '1' THEN 1 ELSE 0 END) AS 'IN',
                    SUM(CASE WHEN INCOMING = '0' THEN 1 ELSE 0 END) AS 'OUT',
                    COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR,
                    CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FC, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LC 
                    INTO #TEMP1 FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE OTHER IN
                    (SELECT DISTINCT OTHER FROM #TEMP) AND PHONE != ?
                    GROUP BY PHONE, OTHER ORDER BY OTHER";
            $params5 = array($number);
            $st5 = sqlsrv_prepare($conn, $sql5, $params5);
            sqlsrv_execute($st5);
            
            $sql6 = "SELECT OTHER AS PHONE, A.PHONE AS OTHER, C.NICKNAME, CATEGORY, [IN], [OUT], CALLS, DUR, FC AS FIRST_CALL, LC AS LAST_CALL, INC_OFFICER 
                    INTO #TEMP2 FROM #TEMP1 A
                    LEFT JOIN CDATDUPL.DBO.CDATSUSPECT C ON A.PHONE = C.PHONE";
            $st6 = sqlsrv_query($conn, $sql6);
            
            $sql7 = "SELECT DISTINCT A.PHONE, OTHER, NICKNAME, CATEGORY, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, INC_OFFICER 
                    FROM #TEMP2 A ORDER BY PHONE, CALLS DESC";
            $st7 = sqlsrv_query($conn, $sql7);
            
            $sql8 = "SELECT 'OTHERS CDAT CONTACTS OF MOBILE NO: ' + ? as PHONE";
            $params8 = array($number);
            $st8 = sqlsrv_prepare($conn, $sql8, $params8);
            sqlsrv_execute($st8);
            
            $sql9 = "SELECT CASE WHEN COUNT(PHONE) >= 1 THEN '' ELSE '*** NO CDAT CONTACTS TO OTHERS OF $number ***' END as CNTS FROM #TEMP2";
            $st9 = sqlsrv_query($conn, $sql9);
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #4b495a; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE']) . "</div>";
            }
            
            // Display Phone Information Table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>PHONE</th>
                <th style='color: #4b495a; padding: 10px;'>FIRST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>LAST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>NICKNAME</th>
                <th style='color: #4b495a; padding: 10px;'>LAST_UPDATED</th>
                <th style='color: #4b495a; padding: 10px;'>ADDRESS</th>
            </tr>";
            
            while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
                echo "<tr>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FIRST_CALL']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_CALL']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['NICKNAME']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_UPDATED']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['ADDRESS']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table><br />";
            
            // Display CDAT Contacts Table
            echo "<h3 style='color: #4b495a; margin-top: 30px;'>CDAT Contacts</h3>";
            echo "<table border='1' cellspacing='0' cellpadding='3' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>OTHER</th>
                <th style='color: #4b495a; padding: 10px;'>CDAT PHONE</th>
                <th style='color: #4b495a; padding: 10px;'>NICK NAME</th>
                <th style='color: #4b495a; padding: 10px;'>CAT</th>
                <th style='color: #4b495a; padding: 10px;'>IN</th>
                <th style='color: #4b495a; padding: 10px;'>OUT</th>
                <th style='color: #4b495a; padding: 10px;'>CALLS</th>
                <th style='color: #4b495a; padding: 10px;'>DUR</th>
                <th style='color: #4b495a; padding: 10px;'>FIRST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>LAST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>IO NAME</th>
            </tr>";
            
            while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
                echo "<tr>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'><a href='cdatcnts2.php?PHONE_NO=" . urlencode($row['OTHER']) . "'>" . htmlspecialchars($row['OTHER']) . "</a></font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['NICKNAME']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CATEGORY']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['IN']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OUT']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CALLS']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['DUR']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FIRST_CALL']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_CALL']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['INC_OFFICER']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table><br />";
            echo "</div>";
            
            // Display no contacts message if applicable
            while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
                if (!empty($row['CNTS'])) {
                    echo "<div style='text-align: center; margin: 20px 0;'>";
                    echo "<font size='4' face='verdana' color='#4b495a'><b>" . htmlspecialchars($row['CNTS']) . "</b></font>";
                    echo "</div>";
                }
            }
            
            sqlsrv_free_stmt($st2);
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