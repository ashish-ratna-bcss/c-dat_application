<?php
/**
 * admin_activity_log.php
 * Admin-only page — User Activity Audit Log
 * UI matches the existing Hyderabad City Police project structure.
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_admin();

$db = audit_db();

// ── Fetch all unique users for dropdown ──
$users = $db->query("SELECT DISTINCT username FROM user_sessions ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

// ── Filters ──
$filter_user = trim($_POST['filter_user'] ?? $_GET['filter_user'] ?? '');
$filter_from = trim($_POST['filter_from'] ?? $_GET['filter_from'] ?? date('Y-m-d'));
$filter_to   = trim($_POST['filter_to']   ?? $_GET['filter_to']   ?? date('Y-m-d'));
$today = date('Y-m-d');
if ($filter_from > $today) $filter_from = $today;
if ($filter_to > $today) $filter_to = $today;
if ($filter_from && $filter_to && $filter_to < $filter_from) {
    $filter_to = $filter_from;
}

// ── Query ──
$logs     = [];
$sessions = [];

if ($filter_user !== '') {
    // Sessions
    $sessWhere  = "WHERE username = :u";
    $sessParams = [':u' => $filter_user];
    if ($filter_from) { $sessWhere .= " AND login_time >= :f"; $sessParams[':f'] = $filter_from . ' 00:00:00'; }
    if ($filter_to)   { $sessWhere .= " AND login_time <= :t"; $sessParams[':t'] = $filter_to   . ' 23:59:59'; }
    $st = $db->prepare("SELECT * FROM user_sessions $sessWhere ORDER BY login_time DESC");
    $st->execute($sessParams);
    $sessions = $st->fetchAll(PDO::FETCH_ASSOC);

    // Activity logs
    $logWhere  = "WHERE username = :u";
    $logParams = [':u' => $filter_user];
    if ($filter_from) { $logWhere .= " AND created_at >= :f"; $logParams[':f'] = $filter_from . ' 00:00:00'; }
    if ($filter_to)   { $logWhere .= " AND created_at <= :t"; $logParams[':t'] = $filter_to   . ' 23:59:59'; }
    $st = $db->prepare("SELECT * FROM user_activity_logs $logWhere ORDER BY created_at DESC");
    $st->execute($logParams);
    $logs = $st->fetchAll(PDO::FETCH_ASSOC);
}

// Helpers
function fmt_dt(?string $dt): string
{
    if (!$dt) return '—';
    try { return (new DateTime($dt))->format('Y-m-d H:i:s'); } catch(Exception $e) { return $dt; }
}
function fmt_dur(?int $sec): string
{
    if (!$sec || $sec <= 0) return '—';
    $h = intdiv($sec, 3600); $m = intdiv($sec % 3600, 60); $s = $sec % 60;
    $p = [];
    if ($h) $p[] = "{$h}h"; if ($m) $p[] = "{$m}m"; if ($s) $p[] = "{$s}s";
    return implode(' ', $p) ?: '< 1s';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User Activity Log</title>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body,td,th {
    font-family: Arial, Helvetica, sans-serif;
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
            <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/TOPBORDER.jpg">
              <ul id="MenuBar1" class="MenuBarHorizontal">
                <li><a href="home.php">Home</a></li>
                <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
                  <ul>
                    <li><a href="sum_home.php">Summary Total</a></li>
                    <li><a href="sum_between_dates.php">Summary Between Dates</a></li>
                    <li><a href="sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                    <li><a href="sum_new_nos.php">Summary of New Contacts</a></li>
                    <li><a href="sum_in_state.html">Summary Within a State</a></li>
                    <li><a href="sum_out_state.htm">Summary other than a state</a></li>
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
                    <li><a href="bulk_cdat_contacts.htm">Bulk Cdat Contacts</a></li>
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
                    <li><a href="address.htm">Single Address</a></li>
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
                    <li><a href="cellid_search.htm">Cellid Search</a></li>
                    <li><a href="vehicle_search.html">Vehicle Search</a></li>
                    <li><a href="common_cnts.php">Common Cnts</a></li>
                    <li><a href="admin_activity_log.php"><b>User Activity</b></a></li>
                    <li><a href="admin_sql_console.php">SQL Query Console</a></li>
                  </ul>
                </li>
                <!-- <li><a href="logout.php" style="color: #FF6347; font-weight: bold;">Logout</a></li> -->
              </ul>
            </td>
          </tr>
        </table>

        <p>&nbsp;</p>

        <!-- ═══ SEARCH FORM ═══ -->
        <table width="700" height="90" align="center">
          <tr>
            <th height="25" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">
              USER ACTIVITY LOG
            </th>
          </tr>
          <tr>
            <th align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">
              <form id="form1" name="form1" method="post" action="admin_activity_log.php">
                <p>
                  <label for="filter_user" style="font-family:verdana;font-size:13px;">Select User:</label>
                  <select name="filter_user" id="filter_user" style="height:24px;font-size:12px;margin:0 6px;" required="required">
                    <option value="">-- Select User --</option>
                    <?php foreach ($users as $u): ?>
                      <option value="<?= htmlspecialchars($u['username']) ?>"
                        <?= $filter_user === $u['username'] ? 'selected="selected"' : '' ?>>
                        <?= htmlspecialchars($u['username']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  &nbsp;
                  Date From:
                  <input type="date" name="filter_from" id="filter_from" value="<?= htmlspecialchars($filter_from) ?>" max="<?= date('Y-m-d') ?>" style="height:24px;font-size:12px;" />
                  &nbsp;To:
                  <input type="date" name="filter_to" id="filter_to" value="<?= htmlspecialchars($filter_to) ?>" max="<?= date('Y-m-d') ?>" style="height:24px;font-size:12px;" />
                  &nbsp;
                  <input type="submit" name="BTN_SEARCH" id="BTN_SEARCH" value="Submit" />
                </p>
              </form>
            </th>
          </tr>
        </table>

        <p>&nbsp;</p>

        <?php if ($filter_user !== ''): ?>

        <!-- ═══ SESSIONS TABLE ═══ -->
        <table width="1280" align="center" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td align="center">

              <?php if (!empty($sessions)): ?>
              <font size="4" face="verdana" color="#F9FBFC">
                <center><b>SESSION HISTORY OF USER: <?= htmlspecialchars(strtoupper($filter_user)) ?></b></center>
              </font><br/>
              <table border="1" cellspacing="0" cellpadding="5" align="center">
                <thead>
                  <tr>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">SESSION ID</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">USERNAME</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">LOGIN TIME</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">LOGOUT TIME</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">DURATION</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">IP ADDRESS</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">DEVICE</font></th>
                  </tr>
                </thead>
                <tbody id="sessionsBody">
                  <?php foreach ($sessions as $i => $sess): $bg = ($i % 2 === 0) ? '#AED1F1' : '#C2E0FB'; ?>
                  <tr>
                    <td width="80px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= $sess['id'] ?><center></font></td>
                    <td width="100px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= htmlspecialchars($sess['username']) ?><center></font></td>
                    <td width="160px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= fmt_dt($sess['login_time']) ?><center></font></td>
                    <td width="160px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= fmt_dt($sess['logout_time']) ?><center></font></td>
                    <td width="100px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= fmt_dur($sess['session_duration'] ? (int)$sess['session_duration'] : null) ?><center></font></td>
                    <td width="120px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= htmlspecialchars($sess['ip_address'] ?? '—') ?><center></font></td>
                    <td width="100px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= htmlspecialchars($sess['device_info'] ?? '—') ?><center></font></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="7" align="right" bgcolor="#C2E0FB" style="padding: 10px; border: 1px solid #75a7d3;">
                      <div id="sessionsPaginationControls" style="font-family: verdana; font-size: 12px; font-weight: bold; color: black;"></div>
                    </td>
                  </tr>
                </tfoot>
              </table>
              <?php endif; ?>

              <br/>

              <!-- ═══ ACTIVITY LOGS TABLE ═══ -->
              <?php if (!empty($logs)): ?>
              <font size="4" face="verdana" color="#F9FBFC">
                <center><b>ACTIVITY LOG OF USER: <?= htmlspecialchars(strtoupper($filter_user)) ?></b></center>
              </font><br/>
              <table border="1" cellspacing="0" cellpadding="5" align="center">
                <thead>
                  <tr>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">S.NO</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">SESSION ID</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">MODULE</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">ACTION</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">SEARCH DATA</font></th>
                    <th bgcolor="#921215"><font size="2" face="verdana" color="#F9FBFC">TIMESTAMP</font></th>
                  </tr>
                </thead>
                <tbody id="logsBody">
                  <?php foreach ($logs as $i => $log):
                    $bg = ($i % 2 === 0) ? '#AED1F1' : '#C2E0FB';
                    // Format search_data JSON nicely
                    $paramStr = '';
                    if ($log['search_data']) {
                        $data = json_decode($log['search_data'], true) ?? [];
                        $parts = [];
                        foreach ($data as $k => $v) {
                            if ($k === 'ip') continue;
                            $parts[] = strtoupper(str_replace('_', ' ', $k)) . ': ' . $v;
                        }
                        $paramStr = implode(' | ', $parts);
                    }
                  ?>
                  <tr>
                    <td width="50px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= $i + 1 ?><center></font></td>
                    <td width="80px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= $log['session_id'] ?? '—' ?><center></font></td>
                    <td width="180px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= htmlspecialchars($log['module_name']) ?><center></font></td>
                    <td width="120px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= htmlspecialchars($log['action_type']) ?><center></font></td>
                    <td width="400px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><?= htmlspecialchars($paramStr ?: '—') ?></font></td>
                    <td width="160px" bgcolor="<?= $bg ?>"><font size="1" face="verdana"><center><?= fmt_dt($log['created_at']) ?><center></font></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="6" align="right" bgcolor="#C2E0FB" style="padding: 10px; border: 1px solid #75a7d3;">
                      <div id="logsPaginationControls" style="font-family: verdana; font-size: 12px; font-weight: bold; color: black;"></div>
                    </td>
                  </tr>
                </tfoot>
              </table>
              
              <script type="text/javascript">
              function setupPagination(tbodyId, controlsId, rowsPerPage) {
                  var tbody = document.getElementById(tbodyId);
                  if (!tbody) return;
                  var rows = tbody.getElementsByTagName('tr');
                  var totalRows = rows.length;
                  var totalPages = Math.ceil(totalRows / rowsPerPage);
                  if (totalPages <= 1) return;

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
                      var controlsDiv = document.getElementById(controlsId);
                      if (!controlsDiv) return;
                      
                      var html = '';
                      
                      // Prev button
                      if (currentPage > 1) {
                          html += '<a href="javascript:void(0)" class="page-btn" data-page="' + (currentPage - 1) + '" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; font-weight: bold; border-radius: 3px;">Prev</a> ';
                      } else {
                          html += '<span style="padding: 4px 8px; margin: 2px; border: 1px solid #ccc; background-color: #f0f0f0; color: #888; border-radius: 3px; cursor: not-allowed;">Prev</span> ';
                      }

                      // Page numbers
                      var startPage = Math.max(1, currentPage - 2);
                      var endPage = Math.min(totalPages, currentPage + 2);

                      if (startPage > 1) {
                          html += '<a href="javascript:void(0)" class="page-btn" data-page="1" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; border-radius: 3px;">1</a> ';
                          if (startPage > 2) {
                              html += '<span style="padding: 4px; margin: 2px; color: #fff;">...</span> ';
                          }
                      }

                      for (var p = startPage; p <= endPage; p++) {
                          if (p === currentPage) {
                              html += '<span style="padding: 4px 8px; margin: 2px; border: 1px solid #921215; background-color: #921215; color: white; font-weight: bold; border-radius: 3px;">' + p + '</span> ';
                          } else {
                              html += '<a href="javascript:void(0)" class="page-btn" data-page="' + p + '" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; border-radius: 3px;">' + p + '</a> ';
                          }
                      }

                      if (endPage < totalPages) {
                          if (endPage < totalPages - 1) {
                              html += '<span style="padding: 4px; margin: 2px; color: #fff;">...</span> ';
                          }
                          html += '<a href="javascript:void(0)" class="page-btn" data-page="' + totalPages + '" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; border-radius: 3px;">' + totalPages + '</a> ';
                      }

                      // Next button
                      if (currentPage < totalPages) {
                          html += '<a href="javascript:void(0)" class="page-btn" data-page="' + (currentPage + 1) + '" style="padding: 4px 8px; margin: 2px; border: 1px solid #75a7d3; background-color: #AED1F1; text-decoration: none; color: black; font-weight: bold; border-radius: 3px;">Next</a>';
                      } else {
                          html += '<span style="padding: 4px 8px; margin: 2px; border: 1px solid #ccc; background-color: #f0f0f0; color: #888; border-radius: 3px; cursor: not-allowed;">Next</span>';
                      }

                      // Record range text
                      var startRec = (currentPage - 1) * rowsPerPage + 1;
                      var endRec = Math.min(currentPage * rowsPerPage, totalRows);
                      html = '<span style="font-family: verdana; font-size: 11px; margin-right: 15px; color: #333;">Showing ' + startRec + ' to ' + endRec + ' of ' + totalRows + ' records</span>' + html;

                      controlsDiv.innerHTML = html;

                      // Bind clicks
                      var btns = controlsDiv.getElementsByClassName('page-btn');
                      for (var j = 0; j < btns.length; j++) {
                          btns[j].addEventListener('click', function(e) {
                              var targetPage = parseInt(this.getAttribute('data-page'));
                              showPage(targetPage);
                          });
                      }
                  }

                  showPage(1);
              }

              // Run pagination logic
              setupPagination('sessionsBody', 'sessionsPaginationControls', 10);
              setupPagination('logsBody', 'logsPaginationControls', 25);
              </script>
              
              <?php elseif ($filter_user !== ''): ?>
                <font size="3" face="verdana" color="#F9FBFC">
                  <center><b>*** NO ACTIVITY FOUND FOR USER: <?= htmlspecialchars(strtoupper($filter_user)) ?> ***</b></center>
                </font>
              <?php endif; ?>

            </td>
          </tr>
        </table>

        <?php endif; ?>

        <p>&nbsp;</p>
        <p>&nbsp;</p>

      </td>
    </tr>
  </table>
</div>

<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});

document.addEventListener('DOMContentLoaded', function() {
    var fromInput = document.getElementById('filter_from');
    var toInput = document.getElementById('filter_to');
    var form = document.getElementById('form1');
    var todayStr = new Date().toISOString().split('T')[0];

    if (fromInput && toInput) {
        // Set max limit to today
        fromInput.max = todayStr;
        toInput.max = todayStr;

        // Set initial min limit for toDate
        toInput.min = fromInput.value;

        // Dynamic update on fromDate change
        fromInput.addEventListener('change', function() {
            if (fromInput.value > todayStr) {
                alert('Future dates are not allowed.');
                fromInput.value = todayStr;
            }
            toInput.min = fromInput.value;
            if (toInput.value && toInput.value < fromInput.value) {
                toInput.value = fromInput.value;
            }
        });

        // Validation on toDate manual entry/change
        toInput.addEventListener('change', function() {
            if (toInput.value > todayStr) {
                alert('Future dates are not allowed.');
                toInput.value = todayStr;
            }
            if (fromInput.value && toInput.value < fromInput.value) {
                alert('To Date cannot be earlier than From Date.');
                toInput.value = fromInput.value;
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            if (fromInput && toInput && fromInput.value && toInput.value) {
                if (fromInput.value > todayStr || toInput.value > todayStr) {
                    alert('Future dates are not allowed.');
                    e.preventDefault();
                    return false;
                }
                if (toInput.value < fromInput.value) {
                    alert('To Date cannot be earlier than From Date.');
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});
</script>
</body>
</html>
