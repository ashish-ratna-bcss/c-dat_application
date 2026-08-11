<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Common Contacts");
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        
        <!-- Search Form -->
        <table width="972" height="165" align="center">
          <tr>
            <th height="29" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col">COMMON CONTACTS OF MOBILE NUMBERS</th>
          </tr>
          <tr>
            <th width="764" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col">
              <form id="form1" name="form1" method="post" action="">
                <style>
                  label textarea {
                    font: normal 15px courier;
                    vertical-align: middle;
                  }
                </style>
                <label>
                  <textarea rows="3" cols="50" name="PHONE_NO" id="COMMON_NOS" placeholder="Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx" required="required"><?php echo isset($_POST['PHONE_NO']) ? htmlspecialchars($_POST['PHONE_NO']) : ''; ?></textarea>
                </label>
                <select name="STRING">
                  <option value="&gt;" <?php echo (isset($_POST['STRING']) && $_POST['STRING'] == '>') ? 'selected' : ''; ?>></option>
                  <option value="=" <?php echo (isset($_POST['STRING']) && $_POST['STRING'] == '=') ? 'selected' : ''; ?>>=</option>
                </select>
                <b>MORE THAN NO : 
                  <select name="NO">
                    <?php for($i=1; $i<=20; $i++): ?>
                      <option value="<?php echo $i; ?>" <?php echo (isset($_POST['NO']) && $_POST['NO'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                  </select>
                  <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit">
                </b>
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
            $number2 = str_replace(",", "','", $number);
            $number3 = str_replace(",", "' INSERT INTO #A1 SELECT '", $number);
            
            // Display header for addresses
            echo "<div style='font-size: 18px; font-weight: bold; color: #4b495a; text-align: center; margin: 20px 0;'>ADDRESSES OF MOBILE NOS</div>";
            
            // Address queries - using parameterized where possible, but some dynamic SQL is needed
            $address1 = "CREATE TABLE #A1 (PHONE NVARCHAR (20) NULL)";
            $address2 = "INSERT INTO #A1 SELECT '$number3'";
            $address3 = "SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL, MAX(STARTTIME) AS LAST_CALL, 
                        MAX(A.ASONDATE) AS LAST_UPDATED, NICKNAME + '_' + ROLE + ' MO:' + MO NICKNAME INTO #A2
                        FROM CDATDUPL.DBO.CDATPCSUSPECT A 
                        LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE = B.PHONE 
                        WHERE A.PHONE IN ('$number2')
                        GROUP BY A.PHONE, NICKNAME, MO, ROLE";
            $address4 = "SELECT DISTINCT A.PHONE, FIRST_CALL, LAST_CALL, LAST_UPDATED, NICKNAME INTO #A3 FROM #A1 A
                        LEFT JOIN #A2 B ON A.PHONE = B.PHONE";
            $address5 = "SELECT PHONE, FULLNAME, FULLADDRESS, CATEGORY_TYPE, DOA, EFF_FROM_DATE INTO #A4 FROM CDATDUPL.DBO.CDATADDRESS 
                        WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";
            $address6 = "INSERT INTO #A4
                        SELECT PHONE, FULLNAME, FULLADDRESS, CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE
                        WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";
            $address7 = "SELECT DISTINCT A.PHONE, ISNULL(CONVERT(VARCHAR, FIRST_CALL, 20), 'NIL') AS FIRST_CALL,
                        ISNULL(CONVERT(VARCHAR, A.LAST_CALL, 20), 'NIL') AS LAST_CALL,
                        ISNULL(CONVERT(VARCHAR, A.LAST_UPDATED, 20), 'NIL') AS LAST_UPDATED, ISNULL(NICKNAME, 'NIL') AS NICKNAME,
                        CASE WHEN A.PHONE IN (SELECT PHONE FROM #A4) THEN FULLNAME + ', ' + B.FULLADDRESS + ', DOA: ' + CONVERT(VARCHAR, DOA, 106) + ', LAST UPDATE: ' + CONVERT(VARCHAR, EFF_FROM_DATE, 106)
                        ELSE AREADESCRIPTION END AS ADDRESS INTO #A5 FROM #A3 A
                        LEFT JOIN #A4 B ON A.PHONE = B.PHONE
                        LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON CASE WHEN LEN(A.PHONE) = 10 
                        THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'CODE NOT AVAILABLE' END END
                        LIKE PHONEPREFIX + '%' ORDER BY A.PHONE";
            $address8 = "SELECT PHONE, FIRST_CALL, LAST_CALL, LAST_UPDATED, NICKNAME,
                        CASE WHEN ADDRESS IS NULL AND LEN(PHONE) <> 10 THEN 'JUNK OR VOIP CALL' 
                        WHEN ADDRESS IS NULL AND SUBSTRING(PHONE, 1, 1) IN ('6','7','8','9') AND LEN(ADDRESS) >= 10 THEN 'CODE NOT AVAILABLE' 
                        ELSE ADDRESS END AS ADDRESS FROM #A5";
            
            // Common contacts queries
            $sql1 = "SELECT * INTO #T FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN ('$number2')";
            $sql2 = "SELECT PHONE, OTHER, COUNT(OTHER) AS COUNT1 INTO #common_numbertable1 FROM #T
                    GROUP BY OTHER, PHONE HAVING (COUNT(OTHER)) > 1 ORDER BY OTHER, PHONE";
            $sql3 = "SELECT OTHER, PHONE, COUNT(OTHER) COUNT1 INTO #common_numbertable2 FROM #common_numbertable1
                    GROUP BY OTHER, PHONE ORDER BY OTHER";
            $sql4 = "SELECT DISTINCT OTHER, 
                    (SELECT PHONE + ', ' FROM #common_numbertable2 US
                    WHERE US.OTHER = SS.OTHER FOR XML PATH('')) [PHONES],
                    (SELECT SUM(COUNT1) FROM #common_numbertable2 XX WHERE XX.OTHER = SS.OTHER) TOTALNUMBEROFPHONES
                    INTO #common_numbertable3 FROM #common_numbertable2 SS
                    GROUP BY SS.OTHER ORDER BY 1";
            $sql5 = "DELETE FROM #common_numbertable3 WHERE TOTALNUMBEROFPHONES = 1";
            $sql6 = "DROP TABLE #common_numbertable1";
            $sql7 = "DROP TABLE #common_numbertable2";
            $sql8 = "UPDATE #common_numbertable3 SET PHONES = LEFT(PHONES, LEN(PHONES) - 1) + '.'";
            $sql9 = "SELECT DISTINCT A.OTHER, A.PHONES, A.TOTALNUMBEROFPHONES PHONE_COUNT, E.NICKNAME + '_' + ROLE OTHERS_NICKNAME, E.MO OTHERS_MO,
                    CASE WHEN A.OTHER = C.PHONE THEN ISNULL(C.FULLNAME, '') + ', ' + ISNULL(C.FULLADDRESS, '') +
                    ', DOA: ' + ISNULL(CONVERT(VARCHAR, C.DOA, 20), '') + ', LAST_UPDATED: ' +
                    ISNULL(CONVERT(VARCHAR, C.EFF_FROM_DATE, 20), '') + ', ' +
                    (CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION, '') ELSE C.OPERATOR END)
                    WHEN A.OTHER = D.PHONE THEN ISNULL(D.FULLNAME, '') + ', ' + ISNULL(D.FULLADDRESS, '') +
                    ', ' + ISNULL(CONVERT(VARCHAR, D.DOA, 20), '') + ', ' +
                    (CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION, '') ELSE D.OPERATOR END) 
                    ELSE ISNULL(AREADESCRIPTION, '') END AS OTHER_ADDRESS 
                    FROM #common_numbertable3 A
                    LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER = C.PHONE AND C.EFF_TO_DATE IS NULL AND LEN(A.OTHER) >= '10'
                    LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER = D.PHONE AND D.EFF_TO_DATE IS NULL
                    LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.OTHER) = 10 THEN A.OTHER 
                    ELSE CASE WHEN LEN(A.OTHER) > 10 THEN '00' + A.OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
                    LIKE PHONEPREFIX + '%'
                    LEFT JOIN CDATDUPL.DBO.CDATSUSPECT E ON A.OTHER = E.PHONE
                    WHERE LEN(A.OTHER) = '10' AND ISNUMERIC(A.OTHER) = '1' AND A.OTHER LIKE '[6-9]%'
                    ORDER BY PHONE_COUNT DESC, OTHER DESC";
            
            // Execute address queries
            $at1 = sqlsrv_query($conn, $address1);
            $at2 = sqlsrv_query($conn, $address2);
            $at3 = sqlsrv_query($conn, $address3);
            $at4 = sqlsrv_query($conn, $address4);
            $at5 = sqlsrv_query($conn, $address5);
            $at6 = sqlsrv_query($conn, $address6);
            $at7 = sqlsrv_query($conn, $address7);
            $at8 = sqlsrv_query($conn, $address8);
            
            // Execute common contacts queries
            $st1 = sqlsrv_query($conn, $sql1);
            $st2 = sqlsrv_query($conn, $sql2);
            $st3 = sqlsrv_query($conn, $sql3);
            $st4 = sqlsrv_query($conn, $sql4);
            $st5 = sqlsrv_query($conn, $sql5);
            $st6 = sqlsrv_query($conn, $sql6);
            $st7 = sqlsrv_query($conn, $sql7);
            $st8 = sqlsrv_query($conn, $sql8);
            $st9 = sqlsrv_query($conn, $sql9);
            
            // Display Addresses Table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>PHONE</th>
                <th style='color: #4b495a; padding: 10px;'>FIRST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>LAST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>LAST_UPDATED</th>
                <th style='color: #4b495a; padding: 10px;'>NICKNAME</th>
                <th style='color: #4b495a; padding: 10px;'>ADDRESS</th>
            </tr>";
            
            $rowCount = 0;
            while( $row = sqlsrv_fetch_array( $at8, SQLSRV_FETCH_ASSOC) ) {
                $rowCount++;
                $bgColor1 = ($rowCount % 2 == 0) ? '#AED1F1' : '#C2E0FB';
                $bgColor2 = ($rowCount % 2 == 0) ? '#C2E0FB' : '#AED1F1';
                
                echo "<tr>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FIRST_CALL']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_CALL']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_UPDATED']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['NICKNAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['ADDRESS']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div><br />";
            
            // Display Common Contacts Table
            echo "<div style='font-size: 18px; font-weight: bold; color: #4b495a; text-align: center; margin: 20px 0;'>COMMON CONTACTS</div>";
            
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>COMMON CONTACT</th>
                <th style='color: #4b495a; padding: 10px;'>PHONES</th>
                <th style='color: #4b495a; padding: 10px;'>PHONE_COUNT</th>
                <th style='color: #4b495a; padding: 10px;'>OTHERS_NICKNAME</th>
                <th style='color: #4b495a; padding: 10px;'>OTHERS_MO</th>
                <th style='color: #4b495a; padding: 10px;'>OTHER_ADDRESS</th>
            </tr>";
            
            $rowCount = 0;
            while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
                $rowCount++;
                $bgColor1 = ($rowCount % 2 == 0) ? '#AED1F1' : '#C2E0FB';
                $bgColor2 = ($rowCount % 2 == 0) ? '#C2E0FB' : '#AED1F1';
                
                echo "<tr>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONES']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE_COUNT']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHERS_NICKNAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHERS_MO']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER_ADDRESS']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            sqlsrv_free_stmt($at8);
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