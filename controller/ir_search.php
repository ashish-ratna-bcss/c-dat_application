<?php
// One page for both halves of this screen: the form, and the results.
require_once __DIR__ . '/includes/layout.php';
layout_begin("IR Search By Name");
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
       
        <!-- Search Form -->
        <table width="800" height="100" align="center">
          <tr>
            <th height="27" bgcolor="#A9D1F5" class="CDAT" scope="col">OFFENDER IR SEARCH BY NAME</th>
          </tr>
          <tr>
            <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col">
              <form id="form1" name="form1" method="post" action="">
                NAME OF THE OFFENDER:
                <label for="textfield"></label>
                <input type="text" name="NAME" id="NAME" placeholder="Enter NAME" required="required"
                       value="<?php echo isset($_POST['NAME']) ? htmlspecialchars($_POST['NAME']) : ''; ?>"/>
                <br><br>
                CRIME HEAD:
                <label for="textfield"></label>
                <input type="text" name="CRIME_HEAD" id="CRIME_HEAD" placeholder="Enter CRIME HEAD" required="required"
                       value="<?php echo isset($_POST['CRIME_HEAD']) ? htmlspecialchars($_POST['CRIME_HEAD']) : ''; ?>"/>
                <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" />
              </form>
            </th>
          </tr>
        </table>
        <p>&nbsp;</p>
        
        <?php
        // Check if form was submitted
        if (isset($_POST['NAME']) && !empty($_POST['NAME']) && isset($_POST['CRIME_HEAD']) && !empty($_POST['CRIME_HEAD'])) {
            
            $serverName = "10.10.46.14\DAU_HYD_2023";
            $connectionInfo = array( "Database"=>"CDATDUPL");
            $conn = sqlsrv_connect( $serverName, $connectionInfo );
            
            if( $conn === false ) {
               // die( print_r( sqlsrv_errors(), true));
            }
            
            // Sanitize input
            $name = trim($_POST['NAME']);
            $crime_head = trim($_POST['CRIME_HEAD']);
            
            // Use parameterized queries to prevent SQL injection
            $sql8 = "SELECT 'DETAILS OF : ' + ? as PHONE1";
            $params8 = array($name);
            $st8 = sqlsrv_prepare($conn, $sql8, $params8);
            sqlsrv_execute($st8);
            
            $sql9 = "SELECT DISTINCT A.IRKEY,
                    (CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
                    WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,
                    CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
                    WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE 
                    WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') 
                    ELSE '' END PDACT_KEY,
                    NAME,ALIAS_NAME,FATHER_NAME,AGE,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION,
                    CONVERT(VARCHAR(20),DATE_OF_ARREST) DATE_OF_ARREST 
                    FROM FORMS..IR_PARTICULARS A
                    INNER JOIN FORMS..OFFENCE_DETAILS B ON A.NAME LIKE '%' + REPLACE(?, ' ', '%') + '%' 
                    AND (B.CRIME_HEAD LIKE '%' + REPLACE(?, ' ', '%') + '%' OR 
                    B.MO LIKE '%' + REPLACE(?, ' ', '%') + '%') 
                    AND LTRIM(RTRIM(?)) != '' 
                    AND LEN(REPLACE(?, ' ', '')) > '4' 
                    AND A.IRKEY = B.IRKEY 
                    ORDER BY DATE_OF_ARREST DESC";
            
            $params9 = array($name, $crime_head, $crime_head, $name, $name);
            $st9 = sqlsrv_prepare($conn, $sql9, $params9);
            sqlsrv_execute($st9);
            
            if ($st9 === false) {
              //  die(print_r(sqlsrv_errors(), true));
            }
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #F9FBFC; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE1']) . "</div>";
            }
            
            // Display results table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #F9FBFC; padding: 10px;'>IRKEY</th>
                <th style='color: #F9FBFC; padding: 10px;'>PDACT</th>
                <th style='color: #F9FBFC; padding: 10px;'>ACCUSED NAME</th>
                <th style='color: #F9FBFC; padding: 10px;'>ALIAS NAME</th>
                <th style='color: #F9FBFC; padding: 10px;'>FATHER NAME</th>
                <th style='color: #F9FBFC; padding: 10px;'>AGE</th>
                <th style='color: #F9FBFC; padding: 10px;'>PRESENT ADDRESS</th>
                <th style='color: #F9FBFC; padding: 10px;'>CRIME NO</th>
                <th style='color: #F9FBFC; padding: 10px;'>YEAR</th>
                <th style='color: #F9FBFC; padding: 10px;'>SEC_OF_LAW</th>
                <th style='color: #F9FBFC; padding: 10px;'>POLICE STATION</th>
                <th style='color: #F9FBFC; padding: 10px;'>CRIME HEAD</th>
                <th style='color: #F9FBFC; padding: 10px;'>MO</th>
                <th style='color: #F9FBFC; padding: 10px;'>DOA</th>
            </tr>";
            
            $rowCount = 0;
            while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
                $rowCount++;
                $bgColor1 = ($rowCount % 2 == 0) ? '#AED1F1' : '#C2E0FB';
                $bgColor2 = ($rowCount % 2 == 0) ? '#C2E0FB' : '#AED1F1';
                
                echo "<tr>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'><a href='ir.php?IRKEY=" . urlencode($row['IRKEY']) . "'>" . htmlspecialchars($row['IRKEY']) . "</a></font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'><a href='pdact_main.php?PDACT_KEY=" . urlencode($row['PDACT_KEY']) . "'>" . htmlspecialchars($row['PDACT']) . "</a></font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['NAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['ALIAS_NAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FATHER_NAME']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['AGE']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PRESENT_ADDRESS']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CRIME_NO']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['YEAR']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['SEC_OF_LAW']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['POLICE_STATION']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CRIME_HEAD']) . "</font></td>";
                echo "<td style='background-color: $bgColor1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['MO']) . "</font></td>";
                echo "<td style='background-color: $bgColor2; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['DATE_OF_ARREST']) . "</font></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            if ($rowCount == 0) {
                echo "<div style='text-align: center; margin: 20px 0;'>";
                echo "<font size='4' face='verdana' color='#F9FBFC'><b>No records found for the search criteria</b></font>";
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