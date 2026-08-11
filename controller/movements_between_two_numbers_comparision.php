<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Movements Between Two Numbers Comparision");
require_once __DIR__ . '/cdr_enrichment_sql.php';
?>

<div align="center">
 
        
        <!-- Search Form -->
        <table width="1000" height="120" align="center">
          <tr>
            <th height="21" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">COMPARISION OF TWO NUMBERS LOCATION</th>
          </tr>
          <tr>
            <th align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">
              <form id="form1" name="form1" method="POST" action="">
                <label for="SUM" font="" face="verdana">Movements of Mobile No:</label>
                <input type="text" name="PHONE_NO" id="calls" placeholder="Enter Mobile No" required="required" 
                       value="<?php echo isset($_POST['PHONE_NO']) ? htmlspecialchars($_POST['PHONE_NO']) : ''; ?>">
                <label for="SUM" font="" face="verdana">Other No:</label>
                <input type="text" name="OTHER_NO" id="calls" placeholder="Enter Other No" required="required"
                       value="<?php echo isset($_POST['OTHER_NO']) ? htmlspecialchars($_POST['OTHER_NO']) : ''; ?>">
                <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit">
              </form>
            </th>
          </tr>
        </table>
        <p>&nbsp;</p>
        
        <?php
        // Check if form was submitted
        if (isset($_POST['PHONE_NO']) && !empty($_POST['PHONE_NO']) && isset($_POST['OTHER_NO']) && !empty($_POST['OTHER_NO'])) {
            
            $serverName = "CPHYDERABAD1\DAU_HYD_2023";
            $connectionInfo = array( "Database"=>"CDATDUPL");
            $conn = sqlsrv_connect( $serverName, $connectionInfo );
            
            if( $conn === false ) {
                die( print_r( sqlsrv_errors(), true));
            }
            
            // Sanitize input
            $number = $_POST['PHONE_NO'];
            $number1 = $_POST['OTHER_NO'];
            
            // Use parameterized queries to prevent SQL injection
            $sql10 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
            INC_OFFICER 
            INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN (?,?)  GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";
            $params10 = array($number, $number1);
            $st10 = sqlsrv_prepare($conn, $sql10, $params10);
            sqlsrv_execute($st10);
            
            $sql1 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
            CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
            IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN (?,?)";
            $params1 = array($number, $number1);
            $st1 = sqlsrv_prepare($conn, $sql1, $params1);
            sqlsrv_execute($st1);
            
            // Use the cdr_sql_enrich_tt function
            $sql2 = cdr_sql_enrich_tt('', '', [
                'with_last_update' => true,
                'with_lat_long' => true,
                'output_table' => '#ttppp',
            ]);
            $st2 = sqlsrv_query($conn, $sql2);
            
            $sql5 = "select distinct A.PHONE,A.STARTTIME STARTTIME,A.DURATION ,''''+A.CELLTOWERID PHONE_CELLTOWERID,
            A.AREADESCRIPTION PHONE_AREADESCRIPTION,A.LAT PHONE_LAT,A.LONG PHONE_LONG,A.AZM PHONE_AZM,
            A.OTHER,''''+B.CELLTOWERID OTHER_CELLTOWERID,
            B.AREADESCRIPTION OTHER_AREADESCRIPTION,B.LAT OTHER_LAT,B.LONG OTHER_LONG,B.AZM OTHER_AZM
            into #ttpppp from #ttppp A INNER JOIN
            #TTPPP B ON A.OTHER=B.PHONE AND A.PHONE =B.OTHER AND CONVERT(DATE,A.STARTTIME)=CONVERT(DATE,B.STARTTIME) 
            and datepart(hh,convert(datetime,A.STARTTIME))=datepart(hh,convert(datetime,b.STARTTIME)) and 
            datepart(mm,convert(datetime,A.STARTTIME))=datepart(mm,convert(datetime,b.STARTTIME)) 
            AND datediff(ss,convert(datetime,A.STARTTIME),convert(datetime,b.STARTTIME))<'4'
            WHERE A.PHONE=?";
            $params5 = array($number);
            $st5 = sqlsrv_prepare($conn, $sql5, $params5);
            sqlsrv_execute($st5);
            
            $sql7 = "select distinct *,case when 
            phone_lat like '%.%' and other_lat like '%.%' and phone_long like '%.%' and other_long like '%.%'
            then CAST(import.DBO.CALCULATEDISTANCE(left(phone_long,8),left(phone_lat,8),left(other_LONG,8),left(other_LAT,8)) AS INT) else '' end 
            DIST FROM #ttpppp
            ORDER BY STARTTIME";
            $st7 = sqlsrv_query($conn, $sql7);
            
            $sql6 = "select 'MOVEMENTS COMPARISION  OF MOBILE NO. ' + ? + ' AND OTHER NO. ' + ? as PHONE";
            $params6 = array($number, $number1);
            $st6 = sqlsrv_prepare($conn, $sql6, $params6);
            sqlsrv_execute($st6);
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #4b495a; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE']) . "</div>";
            }
            
            // Display results table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>PHONE</th>
                <th style='color: #4b495a; padding: 10px;'>OTHER</th>
                <th style='color: #4b495a; padding: 10px;'>STARTTIME</th>
                <th style='color: #4b495a; padding: 10px;'>DURATION</th>
                <th style='color: #4b495a; padding: 10px;'>PHONE AREADESCRIPTION</th>
                <th style='color: #4b495a; padding: 10px;'>OTHER AREADESCRIPTION</th>
                <th style='color: #4b495a; padding: 10px;'>PHONE CELLTOWERID</th>
                <th style='color: #4b495a; padding: 10px;'>PHONE LAT</th>
                <th style='color: #4b495a; padding: 10px;'>PHONE LONG</th>
                <th style='color: #4b495a; padding: 10px;'>PHONE AZM</th>
                <th style='color: #4b495a; padding: 10px;'>OTHER CELLTOWERID</th>
                <th style='color: #4b495a; padding: 10px;'>OTHER LAT</th>
                <th style='color: #4b495a; padding: 10px;'>OTHER LONG</th>
                <th style='color: #4b495a; padding: 10px;'>OTHER AZM</th>
                <th style='color: #4b495a; padding: 10px;'>DIST BETWEEN NUMBERS IN KM</th>
            </tr>";
            
            while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
                echo "<tr>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['STARTTIME']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['DURATION']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE_AREADESCRIPTION']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER_AREADESCRIPTION']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE_CELLTOWERID']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE_LAT']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE_LONG']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE_AZM']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER_CELLTOWERID']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER_LAT']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER_LONG']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['OTHER_AZM']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['DIST']) . "</font></td>";
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