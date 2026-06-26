<?php
/**
 * admin_upload_history.php
 * Audit log viewer for CDR data uploads.
 * Filterable, paginated audit list with date range restrictions.
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_admin();

$config = require __DIR__ . '/cdr_upload_config.php';
$configs = $config;
unset($configs['api']);
$db = audit_db();

// 1. Get unique upload usernames for filtering
try {
    $userStmt = $db->query("SELECT DISTINCT username FROM upload_activity_logs ORDER BY username");
    $uploadUsers = $userStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $uploadUsers = [];
}

// 2. Read query parameters
$filterUser = trim($_GET['filter_user'] ?? '');
$filterModule = trim($_GET['filter_module'] ?? '');
$filterStatus = trim($_GET['filter_status'] ?? '');

$todayStr = date('Y-m-d');
$fromDate = trim($_GET['from_date'] ?? $todayStr);
$toDate = trim($_GET['to_date'] ?? $todayStr);

// Validations: date range cannot be in the future, from_date <= to_date
if (strtotime($fromDate) > strtotime($todayStr)) {
    $fromDate = $todayStr;
}
if (strtotime($toDate) > strtotime($todayStr)) {
    $toDate = $todayStr;
}
if (strtotime($fromDate) > strtotime($toDate)) {
    $fromDate = $toDate;
}

// 3. Build Query
$where = [];
$params = [];

if ($filterUser !== '') {
    $where[] = 'username = :username';
    $params[':username'] = $filterUser;
}
if ($filterModule !== '') {
    $where[] = 'module_name = :module';
    $params[':module'] = $filterModule;
}
if ($filterStatus !== '') {
    $where[] = 'upload_status = :status';
    $params[':status'] = $filterStatus;
}

// Date filters
$where[] = "uploaded_at >= :from_date_start";
$params[':from_date_start'] = $fromDate . ' 00:00:00';
$where[] = "uploaded_at <= :to_date_end";
$params[':to_date_end'] = $toDate . ' 23:59:59';

$whereClause = implode(' AND ', $where);

// Pagination settings
$limit = 10; // 10 records per page
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total records
$totalRecords = 0;
try {
    $countQuery = "SELECT COUNT(*) FROM upload_activity_logs WHERE " . $whereClause;
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
} catch (Exception $e) {
    // Fail silently or handle
}

$totalPages = max(1, ceil($totalRecords / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Fetch logs
$logs = [];
try {
    $selectQuery = "SELECT * FROM upload_activity_logs WHERE {$whereClause} ORDER BY uploaded_at DESC LIMIT :limit OFFSET :offset";
    $selectStmt = $db->prepare($selectQuery);
    
    // Bind all filters
    foreach ($params as $k => $v) {
        $selectStmt->bindValue($k, $v);
    }
    $selectStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $selectStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $selectStmt->execute();
    $logs = $selectStmt->fetchAll();
} catch (Exception $e) {
    // Fail silently
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Upload Logs History - CDR Dashboard</title>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarVertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.FONT {
	color: #CFF;
	font-size: 24px;
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif;
}
.history-wrapper {
    width: 1050px;
    margin: 10px auto;
    padding: 20px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    font-family: Verdana, Geneva, sans-serif;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}
.filter-bar {
    background: rgba(0, 0, 0, 0.2);
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    text-align: left;
}
.filter-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    align-items: flex-end;
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-group label {
    font-size: 11px;
    font-weight: bold;
    margin-bottom: 5px;
    color: #FFD700;
}
.filter-group select, .filter-group input[type="date"] {
    padding: 6px 8px;
    font-size: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    color: #333;
    background: #fff;
}
.btn-filter {
    background-color: #FFA500;
    color: white;
    padding: 7px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    font-weight: bold;
    transition: background-color 0.2s;
}
.btn-filter:hover {
    background-color: #e59400;
}
.btn-reset {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 7px 15px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
    text-align: center;
    transition: background-color 0.2s;
}
.btn-reset:hover {
    background-color: rgba(255, 255, 255, 0.3);
}
.history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    color: #fff;
}
.history-table th, .history-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}
.history-table th {
    background: rgba(0, 0, 0, 0.35);
    font-size: 12px;
    color: #87CEEB;
}
.history-table td {
    font-size: 11px;
}
.badge-status {
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
    color: #fff;
    display: inline-block;
}
.status-success { background-color: #28a745; }
.status-partial { background-color: #ffc107; color: #333; }
.status-failed { background-color: #dc3545; }

.pagination {
    margin-top: 20px;
    text-align: right;
}
.pagination-link {
    padding: 5px 10px;
    background: rgba(0, 0, 0, 0.3);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 3px;
    text-decoration: none;
    font-size: 12px;
    margin-left: 4px;
    display: inline-block;
}
.pagination-link:hover {
    background: rgba(255, 255, 255, 0.1);
}
.pagination-link.active {
    background: #FFA500;
    border-color: #FFA500;
    font-weight: bold;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        <!-- Header Section -->
        <table width="1313" height="148">
          <tr>
            <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/TOPBORDER.jpg">
              <ul id="MenuBar1" class="MenuBarHorizontal">
                <li><a href="HOME.html">Home</a>              </li>
                <li><a href="HOME.html" class="MenuBarItemSubmenu">Summary</a>
                  <ul>
                    <li><a href="SUM_HOME.HTML">Summary Total</a></li>
                    <li><a href="SUM_BETWEEN_DATES.html">Summary Between Dates</a></li>
                    <li><a href="SUM_ISD_CNTS.html">Summary of ISD Contacts</a></li>
                    <li><a href="SUM_NEW_NOS.html">Summary of New Contacts</a></li>
                    <li><a href="SUM_IN_STATE.html">Summary Within a State</a></li>
                    <li><a href="SUM_OUT_STATE.html">Summary other than a state</a></li>
                  </ul>
                </li>
                <li><a href="HOME.html" class="MenuBarItemSubmenu">Call Details</a>
                  <ul>
        		    <li><a href="MOVEMENTS.html"> MOVEMENTS </a></li>
        		    <li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS.html">Movements Btwn Two Nos</a></li>
        		    <li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS_COMPARISION.html">Movements Btwn Two Nos Comparision</a></li>
                    <li><a href="CALLS_BTWN_DATES.html">Calls Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="HOME.html" class="MenuBarItemSubmenu">Cdat</a>
                  <ul>
                    <li><a href="CDATCNTS.HTML">Cdat Cnts</a></li>
        		    <li><a href="BULK_CDAT_CONTACTS.HTML">Bulk Cdat Contacts</a></li>
        		    <li><a href="OTHERSCDAT.HTML">Others Cdat</a></li>
                  </ul>
                </li>
                <li><a href="HOME.html" class="MenuBarItemSubmenu">Imei Search</a>
                  <ul>
                    <li><a href="IMEISEARCH.HTML">Phones used in Imei</a></li>
                    <li><a href="IMEISINPHONE.HTML">Imeis used in phone</a></li>
                  </ul>
                </li>
                <li><a href="HOME.html" class="MenuBarItemSubmenu">Address</a>
                  <ul>
                    <li><a href="ADDRESS.HTML">Single Address</a></li>
                    <li><a href="BULKADDRESS.HTML">Bulk Addresses</a></li>
                  </ul>
                </li>
                 <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
                   <ul>
                    <li><a href="DAY%26NIGHTLOC.HTML">Top 10 Day Night Loc</a></li>
                    <li><a href="DAY%26NIGHTLOC_BTWN_DATES.HTML">Top 10 Day Night Loc Between Dates</a></li>
                   </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Offenders List</a>
                  <ul>
                    <li><a href="HABITUAL.PHP">Habitual Offenders List - 1</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Others</a>
                  <ul>
                    <li><a href="CELLID_SEARCH.HTML">Cellid Search</a></li>
                    <li><a href="VEHICLE_SEARCH.HTML">Vehicle Search</a></li>
                    <li><a href="COMMON_CNTS.HTML">Common Cnts</a></li>
                    <li><a href="ADMIN_ACTIVITY_LOG.PHP">User Activity</a></li>
                    <li><a href="ADMIN_SQL_CONSOLE.PHP">SQL Query Console</a></li>
        		    <li><a href="TOWER_HOME.HTML">Tower Dump Reports (Under Development)</a></li>
        		    <li><a href="LOGIN.HTML">IR FORMS</a></li>
        		    <li><a href="IR_SEARCH.HTML">IR Form Search By Name</a></li>
        		    <li><a href="TRAINING_MODULE1.HTML">TRAININGS</a></li>
                  </ul>
                </li>
              </ul>
            </td>
          </tr>
        </table>
        
        <marquee behavior="scroll" direction="right"> 
          <font color="YELLOW" face="verdana" size="2"><b> *** PLEASE MAIL RAW DATA TO cdranalysiswing@gmail.com TO VIEW REPORTS *** </b></font>
        </marquee> 
        
        <table width="1307" height="347" border="0" align="center">
          <tr>
            <td height="24" align="center" valign="top">
              <p align="center" class="FONT"> DATA UPLOADS </p>
            </td>
          </tr>
          <tr>
            <td align="center" valign="top">
              
              <div class="history-wrapper">
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                  <form action="admin_upload_history.php" method="get" id="filterForm">
                    <div class="filter-grid">
                      <div class="filter-group">
                        <label for="filter_user">Uploaded By</label>
                        <select name="filter_user" id="filter_user">
                          <option value="">-- All Users --</option>
                          <?php foreach ($uploadUsers as $u): ?>
                            <option value="<?= htmlspecialchars($u) ?>" <?= ($filterUser === $u) ? 'selected="selected"' : '' ?>><?= htmlspecialchars($u) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="filter-group">
                        <label for="filter_module">Module</label>
                        <select name="filter_module" id="filter_module">
                          <option value="">-- All Modules --</option>
                          <?php foreach ($configs as $key => $conf): ?>
                            <option value="<?= htmlspecialchars($conf['name']) ?>" <?= ($filterModule === $conf['name']) ? 'selected="selected"' : '' ?>><?= htmlspecialchars($conf['name']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="filter-group">
                        <label for="filter_status">Status</label>
                        <select name="filter_status" id="filter_status">
                          <option value="">-- All Status --</option>
                          <option value="Success" <?= ($filterStatus === 'Success') ? 'selected="selected"' : '' ?>>Success</option>
                          <option value="Partial" <?= ($filterStatus === 'Partial') ? 'selected="selected"' : '' ?>>Partial</option>
                          <option value="Failed" <?= ($filterStatus === 'Failed') ? 'selected="selected"' : '' ?>>Failed</option>
                        </select>
                      </div>

                      <div class="filter-group">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" value="<?= htmlspecialchars($fromDate) ?>" max="<?= $todayStr ?>" />
                      </div>

                      <div class="filter-group">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" value="<?= htmlspecialchars($toDate) ?>" max="<?= $todayStr ?>" />
                      </div>

                      <div class="filter-group" style="flex-direction: row; gap: 8px;">
                        <input type="submit" class="btn-filter" value="Filter" style="flex: 1;" />
                        <a href="admin_upload_history.php" class="btn-reset" style="flex: 1;">Reset</a>
                      </div>
                    </div>
                  </form>
                </div>

                <div style="text-align: right; margin-bottom: 10px;">
                  <a href="admin_upload.php" class="btn-action" style="text-decoration: none; display: inline-block; font-size: 13px; padding: 6px 12px;">New File Upload</a>
                </div>

                <!-- Logs Table -->
                <table class="history-table">
                  <thead>
                    <tr>
                      <th>Uploaded At</th>
                      <th>File Name</th>
                      <th>Module Name</th>
                      <th>Uploaded By</th>
                      <th>IP Address</th>
                      <th>Total</th>
                      <th>Inserted</th>
                      <th>Failed</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($logs)): ?>
                      <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #ccc;">No upload records matching filters were found.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($logs as $log): ?>
                        <tr>
                          <td><?= date('d-m-Y H:i A', strtotime($log['uploaded_at'])) ?></td>
                          <td style="font-weight: bold; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['file_name']) ?>">
                            <?= htmlspecialchars($log['file_name']) ?>
                            <div style="font-size: 10px; color: #ccc;"><?= number_format($log['file_size'] / 1024, 2) ?> KB</div>
                          </td>
                          <td><?= htmlspecialchars($log['module_name']) ?></td>
                          <td><?= htmlspecialchars($log['username']) ?></td>
                          <td><?= htmlspecialchars($log['ip_address']) ?></td>
                          <td><?= (int)$log['total_records'] ?></td>
                          <td style="color: #90EE90; font-weight: bold;"><?= (int)$log['inserted_records'] ?></td>
                          <td style="color: #FFB6C1; font-weight: bold;"><?= (int)$log['failed_records'] ?></td>
                          <td>
                            <span class="badge-status status-<?= strtolower($log['upload_status']) ?>">
                              <?= htmlspecialchars($log['upload_status']) ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                  <div class="pagination">
                    <?php if ($page > 1): ?>
                      <a href="?page=<?= $page - 1 ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="pagination-link">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <a href="?page=<?= $i ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="pagination-link <?= ($page === $i) ? 'active' : '' ?>">
                        <?= $i ?>
                      </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                      <a href="?page=<?= $page + 1 ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="pagination-link">Next &raquo;</a>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

              </div>

            </td>
          </tr>
        </table>
        <p>&nbsp;</p>
      </td>
    </tr>
  </table>
</div>

<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});

// Dynamic date range limit adjustment
function updateToDateLimit() {
    var fromInput = document.getElementById('from_date');
    var toInput = document.getElementById('to_date');
    if (fromInput.value) {
        toInput.min = fromInput.value;
        if (toInput.value && toInput.value < fromInput.value) {
            toInput.value = fromInput.value;
        }
    }
}

// Attach listener to update limit when From Date changes
document.getElementById('from_date').addEventListener('change', updateToDateLimit);

// Run on page load
updateToDateLimit();

// Timezone-safe JavaScript validation for dates using YYYY-MM-DD string comparison
document.getElementById('filterForm').addEventListener('submit', function(e) {
    var fromInput = document.getElementById('from_date');
    var toInput = document.getElementById('to_date');
    
    var fromVal = fromInput.value;
    var toVal = toInput.value;
    var todayStr = "<?= $todayStr ?>";
    
    if (fromVal && toVal) {
        if (fromVal > todayStr) {
            alert("From Date cannot be in the future!");
            fromInput.value = todayStr;
            updateToDateLimit();
            e.preventDefault();
            return false;
        }
        if (toVal > todayStr) {
            alert("To Date cannot be in the future!");
            toInput.value = todayStr;
            e.preventDefault();
            return false;
        }
        if (fromVal > toVal) {
            alert("From Date cannot be after To Date!");
            fromInput.value = toVal;
            updateToDateLimit();
            e.preventDefault();
            return false;
        }
    }
});
</script>
</body>
</html>
