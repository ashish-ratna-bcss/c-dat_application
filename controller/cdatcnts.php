<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("CDAT Contacts");
set_time_limit(0);
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';
?>

<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        <!-- Search Form -->
        <table width="625" height="94" align="center">
          <tr>
            <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col">
              <form id="form1" name="form1" method="post" action="">
                <label for="textfield">CDAT CONTACTS OF MOBILE NO:</label>
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
            
            audit_log('CDAT Contacts', 'Search', ['phone_number' => $_POST['PHONE_NO'] ?? '']);
            
            $serverName = "CPHYDERABAD1\DAU_HYD_2023";
            $connectionInfo = array( "Database"=>"CDATDUPL");
            $conn = sqlsrv_connect( $serverName, $connectionInfo );
            
            if( $conn === false ) {
                die( print_r( sqlsrv_errors(), true));
            }
            
            $number = trim((string) ($_POST['PHONE_NO'] ?? ''));
            if ($number === '') {
                die('<center><font color="white">Phone number required</font></center>');
            }
            
            // Use parameterized queries to prevent SQL injection
            $sql10 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME+'_'+B.ROLE NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
            INC_OFFICER 
            FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE=? GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";
            $params10 = array($number);
            $st10 = sqlsrv_prepare($conn, $sql10, $params10);
            sqlsrv_execute($st10);
            
            $sql4 = "SELECT * INTO #XX FROM CDAT_DETAILS1 WHERE PHONE=? and other!=''";
            $params4 = array($number);
            $st4 = sqlsrv_prepare($conn, $sql4, $params4);
            sqlsrv_execute($st4);
            
            $sql5 = "select distinct a.PHONE,OTHER, NICKNAME+'_'+ROLE NICKNAME,
            SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
            SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', count(*) as CALLS,sum(cast(duration as numeric)) as dur,CONVERT(VARCHAR,MIN(STARTTIME),20) as FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) as LAST_CALL,
            MO, CATEGORY, INC_OFFICER INTO #TT from #XX a
            left join cdatdupl.dbo.cdatsuspect b on a.other=b.phone
            WHERE OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT)
            group by a.phone, A.other, nickname,ROLE, MO, CATEGORY, INC_OFFICER order by  calls desc, other";
            $st5 = sqlsrv_query($conn, $sql5);
            
            if ($st5 === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            
            $sql8 = "SELECT 'CDAT CONTACTS OF MOBILE NO: ' + ? as PHONE";
            $params8 = array($number);
            $st8 = sqlsrv_prepare($conn, $sql8, $params8);
            sqlsrv_execute($st8);
            
            $phoneAreaPrefixes = cdat_load_phonearea_prefixes($conn);
            $cdatAddressMap = cdat_fetch_cdataddress_map($conn, [$number]);
            $otherStateMap = cdat_fetch_other_state_address_map($conn, [$number]);
            $defaultImage = cdat_default_suspect_image($conn);
            $suspectProfile = cdat_fetch_suspect_profile_map($conn, [$number]);
            $searchedSuspect = $suspectProfile[$number] ?? null;
            
            $headerRow = [
                'PHONE' => $number,
                'FIRST_CALL' => '',
                'LAST_CALL' => '',
                'NICKNAME' => $searchedSuspect['nickname_label'] ?? '',
                'MO' => $searchedSuspect['mo'] ?? '',
                'CAT' => $searchedSuspect['category'] ?? '',
                'ADDRESS' => cdat_format_sum_header_address($number, $cdatAddressMap, $otherStateMap, cdat_phonearea_lookup($phoneAreaPrefixes, $number)),
                'INC_OFFICER' => $searchedSuspect['inc_officer'] ?? '',
                'IMAGE' => $defaultImage,
            ];
            
            if ($st10 && ($stats = sqlsrv_fetch_array($st10, SQLSRV_FETCH_ASSOC))) {
                $headerRow['FIRST_CALL'] = $stats['FIRST_CALL'] ?? '';
                $headerRow['LAST_CALL'] = $stats['LAST_CALL'] ?? '';
                if ($searchedSuspect === null) {
                    $headerRow['NICKNAME'] = $stats['NICKNAME'] ?? '';
                    $headerRow['MO'] = $stats['MO'] ?? '';
                    $headerRow['CAT'] = $stats['CATEGORY'] ?? '';
                    $headerRow['INC_OFFICER'] = $stats['INC_OFFICER'] ?? '';
                }
            }
            
            $headerImages = cdat_fetch_suspect_image_map($conn, [$number]);
            if (isset($headerImages[$number])) {
                $headerRow['IMAGE'] = $headerImages[$number];
            }
            
            $contactRows = [];
            $lookupPhones = [$number];
            $stContacts = sqlsrv_query($conn, 'SELECT * FROM #TT ORDER BY CALLS DESC, OTHER');
            if ($stContacts) {
                while ($row = sqlsrv_fetch_array($stContacts, SQLSRV_FETCH_ASSOC)) {
                    $contactRows[] = $row;
                    $lookupPhones[] = $row['OTHER'] ?? '';
                }
            }
            
            $contactAddressMap = cdat_fetch_cdataddress_map($conn, $lookupPhones);
            $contactOtherStateMap = cdat_fetch_other_state_address_map($conn, $lookupPhones);
            $contactSuspectMap = cdat_fetch_suspect_profile_map($conn, array_column($contactRows, 'OTHER'));
            $irFormsMap = cdat_fetch_ir_forms_map($conn, array_column($contactRows, 'OTHER'));
            $contactImageMap = cdat_fetch_suspect_image_map($conn, array_column($contactRows, 'OTHER'));
            
            $displayContacts = [];
            foreach ($contactRows as $row) {
                $other = trim((string) ($row['OTHER'] ?? ''));
                $address = cdat_format_cdatcnts_tt_address(
                    $other,
                    $row['CALLS'] ?? 0,
                    $row['DUR'] ?? 0,
                    $contactAddressMap,
                    $phoneAreaPrefixes
                );
                if (isset($contactOtherStateMap[$other])) {
                    $address = cdat_format_cdatcnts_other_state_address($contactOtherStateMap[$other]);
                }
                
                $suspect = $contactSuspectMap[$other] ?? null;
                
                $displayContacts[] = [
                    'PHONE' => $row['PHONE'] ?? '',
                    'OTHER' => $other,
                    'NICKNAME' => $suspect['nickname_label'] ?? ($row['NICKNAME'] ?? ''),
                    'MO' => $suspect['mo'] ?? ($row['MO'] ?? ''),
                    'CAT' => $suspect['category'] ?? ($row['CATEGORY'] ?? ''),
                    'IN' => $row['IN'] ?? '',
                    'OUT' => $row['OUT'] ?? '',
                    'CALLS' => $row['CALLS'] ?? '',
                    'DUR' => $row['DUR'] ?? '',
                    'FIRST_CALL' => $row['FIRST_CALL'] ?? '',
                    'LAST_CALL' => $row['LAST_CALL'] ?? '',
                    'ADDRESS' => $address,
                    'INC_OFFICER' => $suspect['inc_officer'] ?? ($row['INC_OFFICER'] ?? ''),
                    'IRFORMS' => $irFormsMap[$other] ?? '',
                    'IMAGE' => $contactImageMap[$other] ?? $defaultImage,
                ];
            }
            
            $noContactsMsg = count($displayContacts) >= 1 ? '' : "*** NO CDAT CONTACTS TO $number ***";
            
            // Display header
            while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
                echo "<div style='font-size: 18px; font-weight: bold; color: #4b495a; text-align: center; margin: 20px 0;'>" . htmlspecialchars($row['PHONE']) . "</div>";
            }
            
            // Display Phone Information Table
            echo "<div style='overflow-x: auto;'>";
            echo "<table border='1' cellspacing='0' cellpadding='5' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>PHONE</th>
                <th style='color: #4b495a; padding: 10px;'>IMAGE</th>
                <th style='color: #4b495a; padding: 10px;'>FIRST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>LAST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>NICKNAME</th>
                <th style='color: #4b495a; padding: 10px;'>MO</th>
                <th style='color: #4b495a; padding: 10px;'>CAT</th>
                <th style='color: #4b495a; padding: 10px;'>ADDRESS</th>
                <th style='color: #4b495a; padding: 10px;'>IO NAME</th>
            </tr>";
            
            $row = $headerRow;
            echo "<tr>";
            echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
            echo "<td style='text-align: center;'>";
            echo '<img height="100" width="100" src="' . cdat_base64_image_src($row['IMAGE']) . '">';
            echo "</td>";
            echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FIRST_CALL']) . "</font></td>";
            echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_CALL']) . "</font></td>";
            echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['NICKNAME']) . "</font></td>";
            echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['MO']) . "</font></td>";
            echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CAT']) . "</font></td>";
            echo "<td style='background-color: #AED1F1; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['ADDRESS']) . "</font></td>";
            echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['INC_OFFICER']) . "</font></td>";
            echo "</tr>";
            echo "</table><br />";
            
            // Display Contacts Table
            echo "<h3 style='color: #4b495a; margin-top: 30px;'>CDAT Contacts</h3>";
            echo "<table border='1' cellspacing='0' cellpadding='3' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr bgcolor='#921215'>
                <th style='color: #4b495a; padding: 10px;'>PHONE</th>
                <th style='color: #4b495a; padding: 10px;'>OTHER</th>
                <th style='color: #4b495a; padding: 10px;'>IMAGE</th>
                <th style='color: #4b495a; padding: 10px;'>NICK NAME</th>
                <th style='color: #4b495a; padding: 10px;'>MO</th>
                <th style='color: #4b495a; padding: 10px;'>CAT</th>
                <th style='color: #4b495a; padding: 10px;'>IN</th>
                <th style='color: #4b495a; padding: 10px;'>OUT</th>
                <th style='color: #4b495a; padding: 10px;'>CALLS</th>
                <th style='color: #4b495a; padding: 10px;'>DUR</th>
                <th style='color: #4b495a; padding: 10px;'>FIRST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>LAST_CALL</th>
                <th style='color: #4b495a; padding: 10px;'>ADDRESS</th>
                <th style='color: #4b495a; padding: 10px;'>IO NAME</th>
                <th style='color: #4b495a; padding: 10px;'>IR</th>
            </tr>";
            
            foreach ($displayContacts as $row) {
                echo "<tr>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['PHONE']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'><a href='CDATCNTS2.PHP?PHONE_NO=" . urlencode($row['OTHER']) . "'>" . htmlspecialchars($row['OTHER']) . "</a></font></td>";
                echo "<td style='text-align: center;'>";
                echo '<img height="100" width="100" src="' . cdat_base64_image_src($row['IMAGE']) . '">';
                echo "</td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['NICKNAME']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['MO']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['CAT']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars((string) $row['IN']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars((string) $row['OUT']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars((string) $row['CALLS']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars((string) $row['DUR']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['FIRST_CALL']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'>" . htmlspecialchars($row['LAST_CALL']) . "</font></td>";
                echo "<td style='background-color: #AED1F1; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['ADDRESS']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px;'><font size='1' face='verdana'>" . htmlspecialchars($row['INC_OFFICER']) . "</font></td>";
                echo "<td style='background-color: #C2E0FB; padding: 5px; text-align: center;'><font size='1' face='verdana'><a href='CDAT_IRFORM.PHP?OTHER_NO=" . urlencode($row['OTHER']) . "'>" . htmlspecialchars($row['IRFORMS']) . "</a></font></td>";
                echo "</tr>";
            }
            echo "</table><br />";
            echo "</div>";
            
            if ($noContactsMsg !== '') {
                echo "<div style='text-align: center; margin: 20px 0;'>";
                echo "<font size='4' face='verdana' color='#4b495a'><b>" . htmlspecialchars($noContactsMsg) . "</b></font>";
                echo "</div>";
            }
            
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