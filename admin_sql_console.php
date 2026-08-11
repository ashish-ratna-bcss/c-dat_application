<?php
/**
 * ADMIN_SQL_CONSOLE.PHP
 * Admin-only page — PostgreSQL SQL Query Console
 * UI matches the existing Hyderabad City Police project structure.
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_admin(); // Restrict to admin-only

$db = audit_db();

// ── Handling CSV / Excel Export ──
if (isset($_POST['export']) && isset($_POST['sql_query'])) {
    $raw_query = $_POST['sql_query'];
    $export_type = $_POST['export_type'] ?? 'csv'; // 'csv' or 'excel'
    
    try {
        $clean_query = trim($raw_query);
        
        // 1. Basic validation
        if (!preg_match('/^select\b/i', $clean_query)) {
            throw new Exception("Only SELECT queries are allowed.");
        }
        
        // 2. Block multiple statements
        $semicolon_pos = strpos($clean_query, ';');
        if ($semicolon_pos !== false && $semicolon_pos < (strlen($clean_query) - 1)) {
            throw new Exception("Multiple SQL statements are blocked.");
        }
        
        // 3. Block DDL/DML keywords
        if (preg_match('/\b(insert|update|delete|drop|truncate|alter|create|grant|revoke)\b/i', $clean_query)) {
            throw new Exception("Only SELECT queries are allowed. DML/DDL commands are blocked.");
        }
        
        // Strip trailing semicolon and wrap query to enforce maximum 1000 records
        $exec_query = rtrim($clean_query, ';');
        $wrapped_query = "SELECT * FROM ($exec_query) AS query_run LIMIT 1000";
        
        $stmt = $db->query($wrapped_query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log to User Activity Trail
        audit_log('SQL Query Console', 'Export ' . strtoupper($export_type), [
            'query' => $clean_query,
            'rows_count' => count($rows)
        ]);
        
        if ($export_type === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="query_export_' . time() . '.xls"');
            
            echo '<table border="1">';
            if (!empty($rows)) {
                echo '<tr>';
                foreach (array_keys($rows[0]) as $col) {
                    echo '<th bgcolor="#921215"><font color="#FFFFFF">' . htmlspecialchars($col) . '</font></th>';
                }
                echo '</tr>';
                foreach ($rows as $row) {
                    echo '<tr>';
                    foreach ($row as $val) {
                        echo '<td>' . htmlspecialchars($val ?? '') . '</td>';
                    }
                    echo '</tr>';
                }
            } else {
                echo '<tr><td>No records found.</td></tr>';
            }
            echo '</table>';
        } else {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="query_export_' . time() . '.csv"');
            
            $output = fopen('php://output', 'w');
            if (!empty($rows)) {
                fputcsv($output, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($output, $row);
                }
            } else {
                fputcsv($output, ['No records found']);
            }
            fclose($output);
        }
        exit;
    } catch (Throwable $e) {
        die("Export Error: " . $e->getMessage());
    }
}

// ── Query Execution for Page View ──
$query = isset($_POST['sql_query']) ? trim($_POST['sql_query']) : '';
$error = '';
$columns = [];
$results = [];
$total_rows = 0;
$execution_time = 0.0;
$limit_warning = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['BTN_EXECUTE']) && $query !== '') {
    $start_time = microtime(true);
    try {
        // 1. Basic validation
        if (!preg_match('/^select\b/i', $query)) {
            throw new Exception("Only SELECT queries are allowed.");
        }
        
        // 2. Block multiple statements
        $semicolon_pos = strpos($query, ';');
        if ($semicolon_pos !== false && $semicolon_pos < (strlen($query) - 1)) {
            throw new Exception("Multiple SQL statements are blocked.");
        }
        
        // 3. Block DDL/DML keywords
        if (preg_match('/\b(insert|update|delete|drop|truncate|alter|create|grant|revoke)\b/i', $query)) {
            throw new Exception("Only SELECT queries are allowed. DML/DDL commands are blocked.");
        }
        
        // Strip trailing semicolon and wrap query to enforce maximum 1000 records
        $exec_query = rtrim($query, ';');
        $wrapped_query = "SELECT * FROM ($exec_query) AS query_run LIMIT 1001";
        
        $stmt = $db->query($wrapped_query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_rows = count($results);
        
        if ($total_rows > 1000) {
            $limit_warning = true;
            array_pop($results); // Remove the 1001st record
            $total_rows = 1000;
        }
        
        if ($total_rows > 0) {
            $columns = array_keys($results[0]);
        }
        
        $execution_time = microtime(true) - $start_time;
        
        // Log to User Activity Trail
        audit_log('SQL Query Console', 'Execute Query', [
            'query' => $query,
            'duration' => round($execution_time, 4) . 's',
            'rows_count' => $total_rows
        ]);
        
        // Log query execution history
        $logStmt = $db->prepare("
            INSERT INTO admin_query_logs (user_id, username, query_text, execution_time, ip_address)
            VALUES (:uid, :uname, :q, :time, :ip)
        ");
        $logStmt->execute([
            ':uid'   => $_SESSION['audit_user_id'] ?? 0,
            ':uname' => $_SESSION['audit_username'] ?? 'unknown',
            ':q'     => $query,
            ':time'  => round($execution_time, 4),
            ':ip'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Fetch query history (last 15 records)
$history = [];
try {
    $history = $db->query("SELECT * FROM admin_query_logs ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Ignore history load errors
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SQL Query Console</title>
<script src="SpryAssets/sprymenubar.js" type="text/javascript"></script>
<link href="SpryAssets/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body,td,th {
    font-family: Arial, Helvetica, sans-serif;
}
.console-editor {
    background-color: #1e1e1e;
    color: #d4d4d4;
    font-family: 'Courier New', Courier, monospace;
    font-size: 14px;
    padding: 10px;
    border: 1px solid #3c3c3c;
    border-radius: 4px;
    width: 95%;
    height: 120px;
    resize: vertical;
}
.btn-console {
    background-color: #0A4D73;
    color: white;
    border: 1px solid #002244;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 3px;
}
.btn-console:hover {
    background-color: #0d6292;
}
.btn-clear {
    background-color: #7D0F12;
}
.btn-clear:hover {
    background-color: #921215;
}
.btn-secondary {
    background-color: #555;
}
.btn-secondary:hover {
    background-color: #666;
}
.history-item {
    background-color: #AED1F1;
    border: 1px solid #75a7d3;
    margin: 5px 0;
    padding: 5px;
    border-radius: 3px;
    font-size: 11px;
    font-family: monospace;
    text-align: left;
    cursor: pointer;
    max-height: 50px;
    overflow: hidden;
}
.history-item:hover {
    background-color: #C2E0FB;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">

        <!-- ═══ TOP NAVIGATION ═══ -->
        <table width="1313" height="148">
          <tr>
            <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/topborder.jpg">
              <ul id="MenuBar1" class="MenuBarHorizontal">
                <li><a href="home.php">Home</a></li>
                <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
                  <ul>
                    <li><a href="sum_home.php">Summary Total</a></li>
                    <li><a href="sum_between_dates.php">Summary Between Dates</a></li>
                    <li><a href="sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                    <li><a href="sum_new_nos.php">Summary of New Contacts</a></li>
                    <li><a href="sum_in_state.html">Summary Within a State</a></li>
                    <li><a href="sum_out_state.php">Summary other than a state</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
                  <ul>
                    <li><a href="movements.html">MOVEMENTS</a></li>
                    <li><a href="movements_between_two_numbers.html">Movements Btwn Two Nos</a></li>
                    <li><a href="movements_between_two_numbers_comparision.php">Movements Btwn Two Nos Comparision</a></li>
                    <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
                  <ul>
                    <li><a href="cdatcnts.php">Cdat Cnts</a></li>
                    <li><a href="bulk_cdat_contacts.php">Bulk Cdat Contacts</a></li>
                    <li><a href="otherscdat.php">Others Cdat</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
                  <ul>
                    <li><a href="imeisearch.php">Phones used in Imei</a></li>
                    <li><a href="imeisinphone.php">Imeis used in phone</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Address</a>
                  <ul>
                    <li><a href="address.php">Single Address</a></li>
                    <li><a href="bulkaddress.php">Bulk Addresses</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
                  <ul>
                    <li><a href="day%26nightloc.html">Top 10 Day Night Loc</a></li>
                    <li><a href="day%26nightloc_btwn_dates.html">Top 10 Day Night Loc Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Wanted</a>
                  <ul>
                    <li><a href="wanted1.php">List - 1</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Others</a>
                  <ul>
                    <li><a href="cellid_search.php">Cellid Search</a></li>
                    <li><a href="vehicle_search.php">Vehicle Search</a></li>
                    <li><a href="common_cnts.php">Common Cnts</a></li>
                    <li><a href="admin_activity_log.php">User Activity</a></li>
                    <li><a href="admin_sql_console.php"><b>SQL Query Console</b></a></li>
                  </ul>
                </li>
                <!-- <li><a href="logout.php" style="color: #FF6347; font-weight: bold;">Logout</a></li> -->
              </ul>
            </td>
          </tr>
        </table>

        <p>&nbsp;</p>

        <!-- ═══ CONSOLE GRID ═══ -->
        <table width="1280" align="center" border="0" cellpadding="5" cellspacing="0">
          <tr>
            <!-- Left Console Panel (Query Editor + Results) -->
            <td width="980" align="left" valign="top">
              
              <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <th height="25" align="center" valign="middle" background="IMAGES/border.jpg" scope="col">
                    <font color="#FFFFFF">SQL QUERY CONSOLE (POSTGRESQL)</font>
                  </th>
                </tr>
                <tr>
                  <td bgcolor="#C2E0FB" style="padding: 15px; border: 1px solid #75a7d3;">
                    <form id="sqlForm" name="sqlForm" method="post" action="admin_sql_console.php">
                      <textarea id="sql_query" name="sql_query" class="console-editor" placeholder="Type your SELECT query here... e.g. SELECT * FROM user_sessions LIMIT 100;"><?= htmlspecialchars($query) ?></textarea>
                      <br/><br/>
                      <input type="submit" name="BTN_EXECUTE" id="BTN_EXECUTE" value="Execute Query" class="btn-console" />
                      <input type="button" value="Clear" class="btn-console btn-clear" onclick="document.getElementById('sql_query').value='';" />
                      <input type="button" value="Copy Query" class="btn-console btn-secondary" onclick="navigator.clipboard.writeText(document.getElementById('sql_query').value); alert('Query copied to clipboard!');" />
                    </form>
                  </td>
                </tr>
              </table>

              <br/>

              <!-- Display Errors -->
              <?php if ($error !== ''): ?>
                <div style="background-color: #F8D7DA; color: #721C24; border: 1px solid #F5C6CB; padding: 10px; border-radius: 4px; font-weight: bold; font-family: verdana; font-size: 13px;">
                  Error: <?= htmlspecialchars($error) ?>
                </div>
                <br/>
              <?php endif; ?>

              <!-- Display Limit Warning -->
              <?php if ($limit_warning): ?>
                <div style="background-color: #FFF3CD; color: #856404; border: 1px solid #FFEBAA; padding: 10px; border-radius: 4px; font-weight: bold; font-family: verdana; font-size: 13px; margin-bottom: 10px;">
                  ⚠️ Showing first 1000 records.
                </div>
              <?php endif; ?>

              <!-- ═══ RESULTS SECTION ═══ -->
              <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '' && isset($_POST['BTN_EXECUTE'])): ?>
                
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td bgcolor="#921215" height="30" style="padding: 5px 10px;">
                      <table width="100%" border="0">
                        <tr>
                          <td>
                            <font color="#FFFFFF" size="2" face="verdana">
                              <b>Results: <?= $total_rows ?> Rows | Execution Time: <?= round($execution_time, 4) ?> Seconds</b>
                            </font>
                          </td>
                          <td align="right">
                            <form method="post" action="admin_sql_console.php" style="display:inline; margin:0;">
                              <input type="hidden" name="sql_query" value="<?= htmlspecialchars($query) ?>" />
                              <input type="hidden" name="export" value="1" />
                              <input type="hidden" name="export_type" value="csv" />
                              <input type="submit" value="Export CSV" class="btn-console" style="padding:2px 8px; font-size:11px;" />
                            </form>
                            &nbsp;
                            <form method="post" action="admin_sql_console.php" style="display:inline; margin:0;">
                              <input type="hidden" name="sql_query" value="<?= htmlspecialchars($query) ?>" />
                              <input type="hidden" name="export" value="1" />
                              <input type="hidden" name="export_type" value="excel" />
                              <input type="submit" value="Export Excel" class="btn-console" style="padding:2px 8px; font-size:11px;" />
                            </form>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td bgcolor="#C2E0FB" style="padding: 10px; border: 1px solid #75a7d3;">
                      <?php if ($total_rows > 0): ?>
                        <div style="overflow-x: auto; max-width: 950px;">
                          <table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; background-color: white; font-size: 12px; width: 100%;">
                            <thead>
                              <tr bgcolor="#921215">
                                <?php foreach ($columns as $col): ?>
                                  <th><font color="#FFFFFF"><?= htmlspecialchars($col) ?></font></th>
                                <?php endforeach; ?>
                              </tr>
                            </thead>
                            <tbody id="resultsBody">
                              <?php foreach ($results as $i => $row): $bg = ($i % 2 === 0) ? '#AED1F1' : '#C2E0FB'; ?>
                                <tr bgcolor="<?= $bg ?>">
                                  <?php foreach ($columns as $col): ?>
                                    <td><?= htmlspecialchars($row[$col] ?? '—') ?></td>
                                  <?php endforeach; ?>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                        <div id="paginationControls" align="right" style="margin-top: 10px; font-family: verdana; font-size: 12px;"></div>
                        
                        <script type="text/javascript">
                        (function() {
                            var rowsPerPage = 50;
                            var tbody = document.getElementById('resultsBody');
                            if (!tbody) return;
                            var rows = tbody.getElementsByTagName('tr');
                            var totalRows = rows.length;
                            var totalPages = Math.ceil(totalRows / rowsPerPage);
                            if (totalPages <= 1) return; // No pagination needed if <= 50 rows

                            var currentPage = 1;

                            function showPage(page) {
                                currentPage = page;
                                var start = (page - 1) * rowsPerPage;
                                var end = start + rowsPerPage;

                                for (var i = 0; i < totalRows; i++) {
                                    if (i >= start && i < end) {
                                        rows[i].style.display = '';
                                    } else {
                                        rows[i].style.display = 'none';
                                    }
                                }
                                renderControls();
                            }

                            function renderControls() {
                                var controlsDiv = document.getElementById('paginationControls');
                                if (!controlsDiv) return;
                                
                                var html = '';
                                
                                // Prev button
                                if (currentPage > 1) {
                                    html += '<a href="javascript:void(0)" onclick="window.sqlConsoleShowPage(' + (currentPage - 1) + ')" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; font-weight: bold; border-radius: 3px;">Prev</a> ';
                                } else {
                                    html += '<span style="padding: 4px 8px; margin: 2px; border: 1px solid #ccc; background-color: #f0f0f0; color: #888; border-radius: 3px; cursor: not-allowed;">Prev</span> ';
                                }

                                // Page numbers
                                var startPage = Math.max(1, currentPage - 2);
                                var endPage = Math.min(totalPages, currentPage + 2);

                                if (startPage > 1) {
                                    html += '<a href="javascript:void(0)" onclick="window.sqlConsoleShowPage(1)" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; border-radius: 3px;">1</a> ';
                                    if (startPage > 2) {
                                        html += '<span style="padding: 4px; margin: 2px; color: #555;">...</span> ';
                                    }
                                }

                                for (var p = startPage; p <= endPage; p++) {
                                    if (p === currentPage) {
                                        html += '<span style="padding: 4px 8px; margin: 2px; border: 1px solid #921215; background-color: #921215; color: white; font-weight: bold; border-radius: 3px;">' + p + '</span> ';
                                    } else {
                                        html += '<a href="javascript:void(0)" onclick="window.sqlConsoleShowPage(' + p + ')" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; border-radius: 3px;">' + p + '</a> ';
                                    }
                                }

                                if (endPage < totalPages) {
                                    if (endPage < totalPages - 1) {
                                        html += '<span style="padding: 4px; margin: 2px; color: #555;">...</span> ';
                                    }
                                    html += '<a href="javascript:void(0)" onclick="window.sqlConsoleShowPage(' + totalPages + ')" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; border-radius: 3px;">' + totalPages + '</a> ';
                                }

                                // Next button
                                if (currentPage < totalPages) {
                                    html += '<a href="javascript:void(0)" onclick="window.sqlConsoleShowPage(' + (currentPage + 1) + ')" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; font-weight: bold; border-radius: 3px;">Next</a>';
                                } else {
                                    html += '<span style="padding: 4px 8px; margin: 2px; border: 1px solid #ccc; background-color: #f0f0f0; color: #888; border-radius: 3px; cursor: not-allowed;">Next</span>';
                                }

                                // Record range text
                                var startRec = (currentPage - 1) * rowsPerPage + 1;
                                var endRec = Math.min(currentPage * rowsPerPage, totalRows);
                                html = '<span style="font-family: verdana; font-size: 11px; margin-right: 15px; color: #333;">Showing ' + startRec + ' to ' + endRec + ' of ' + totalRows + ' records</span>' + html;

                                controlsDiv.innerHTML = html;
                            }

                            window.sqlConsoleShowPage = showPage;
                            showPage(1);
                        })();
                        </script>
                      <?php else: ?>
                        <font size="2" face="verdana">No records returned.</font>
                      <?php endif; ?>
                    </td>
                  </tr>
                </table>

              <?php endif; ?>

            </td>

            <!-- Right Sidebar Panel (Query History) -->
            <td width="300" align="left" valign="top" style="padding-left: 15px;">
              <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <th height="25" align="center" valign="middle" background="IMAGES/border.jpg" scope="col">
                    <font color="#FFFFFF">RECENT QUERIES</font>
                  </th>
                </tr>
                <tr>
                  <td bgcolor="#C2E0FB" style="padding: 10px; border: 1px solid #75a7d3; min-height: 400px;" align="center" valign="top">
                    <?php if (!empty($history)): ?>
                      <?php foreach ($history as $h): ?>
                        <div class="history-item" onclick="document.getElementById('sql_query').value = this.getAttribute('data-query');" data-query="<?= htmlspecialchars($h['query_text']) ?>" title="Click to copy query to editor">
                          <b><?= htmlspecialchars($h['username']) ?></b> (<?= round((float)$h['execution_time'], 3) ?>s)<br/>
                          <font color="#555"><?= date('d-m-Y h:i A', strtotime($h['created_at'])) ?></font><br/>
                          <code><?= htmlspecialchars(substr($h['query_text'], 0, 80)) ?><?= strlen($h['query_text']) > 80 ? '...' : '' ?></code>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <font size="2" face="verdana" color="#555">No queries run yet.</font>
                    <?php endif; ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <p>&nbsp;</p>
        <p>&nbsp;</p>

      </td>
    </tr>
  </table>
</div>

<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
</html>
