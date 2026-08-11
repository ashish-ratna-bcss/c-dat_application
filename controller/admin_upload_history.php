<?php
/**
 * admin_upload_history.php
 * Audit log viewer for CDR data uploads.
 * Filterable, paginated audit list with date range restrictions.
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_uploader();

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
$type = trim($_GET['type'] ?? 'standard'); // default to standard if not specified
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

if ($type === 'custom') {
    $where[] = "module_name LIKE 'Custom:%'";
} else {
    $where[] = "module_name NOT LIKE 'Custom:%'";
}

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
    $selectQuery = "
        SELECT l.*, b.verified_by, b.verification_status AS batch_verification_status
        FROM upload_activity_logs l
        LEFT JOIN upload_staging_batches b
            ON b.batch_id = l.staging_batch_id
            OR (l.staging_batch_id IS NULL AND b.document_job_id = l.document_job_id)
        WHERE {$whereClause}
        ORDER BY l.uploaded_at DESC
        LIMIT :limit OFFSET :offset
    ";
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

function renderApprovalStatus(array $log): array
{
    $uploadStatus = (string)($log['upload_status'] ?? '');
    $verificationStatus = strtolower((string)($log['verification_status'] ?? $log['batch_verification_status'] ?? ''));
    $verifiedBy = trim((string)($log['verified_by'] ?? ''));

    if ($verificationStatus === 'approved' || ($uploadStatus === 'Success' && $verifiedBy !== '')) {
        return [
            'text' => 'Approved by ' . htmlspecialchars($verifiedBy),
            'class' => 'approval-approved',
        ];
    }
    if ($verificationStatus === 'pending' || $uploadStatus === 'Pending Verification') {
        return [
            'text' => 'Awaiting Approval',
            'class' => 'approval-awaiting',
        ];
    }
    if ($verificationStatus === 'rejected' || $uploadStatus === 'Rejected') {
        $by = $verifiedBy !== '' ? htmlspecialchars($verifiedBy) : '—';
        return [
            'text' => 'Rejected by ' . $by,
            'class' => 'approval-rejected',
        ];
    }

    return [
        'text' => '—',
        'class' => 'approval-na',
    ];
}
?>
<?php
// The stylesheet below belongs in <head>; capture it and pass it to
// layout_begin() so it can stay written as plain CSS in this file.
require_once __DIR__ . '/includes/layout.php';
ob_start();
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
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
.status-pending-verification { background-color: #dc3545; }
.status-success { background-color: #28a745; }
.status-partial { background-color: #ffc107; color: #333; }
.status-failed { background-color: #dc3545; }
.status-pending { background-color: #17a2b8; }
.status-processing { background-color: #007bff; }
.status-rejected { background-color: #6c757d; }
.approval-awaiting { color: #ff6b6b; font-weight: bold; }
.approval-approved { color: #90EE90; font-weight: bold; }
.approval-rejected { color: #ccc; font-weight: bold; }
.approval-na { color: #aaa; }
.history-row-link { color: #fff; text-decoration: underline; cursor: pointer; }
.history-row-link:hover { color: #FFD700; }
.action-btns { display: inline-flex; gap: 6px; align-items: center; }
.action-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    text-decoration: none !important;
    color: #10222b !important;
    font-size: 13px;
    border: 1px solid rgba(255,255,255,0.25);
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.action-icon-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
.action-icon-preview { background: #1f8a70; color: #fff !important; }
.action-icon-insert { background: #FFA500; }
.action-icon-view { background: #5dade2; color: #10222b !important; }

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
<link rel="stylesheet" href="../assets/css/upload.css">
<?php
layout_begin('Upload History', 'Previous uploads and their status', ob_get_clean());
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        <!-- Header Section -->

        
        <marquee behavior="scroll" direction="right"> 
          <font color="YELLOW" face="verdana" size="2"><b> *** PLEASE MAIL RAW DATA TO cdranalysiswing@gmail.com TO VIEW REPORTS *** </b></font>
        </marquee> 
        
        <table width="1307" height="347" border="0" align="center">
          <tr>
            <td height="24" align="center" valign="top">
              <p align="center" class="FONT"> <?= $type === 'custom' ? 'CUSTOM TABLE UPLOADS HISTORY' : 'STANDARD UPLOADS HISTORY' ?> </p>
            </td>
          </tr>
          <tr>
            <td align="center" valign="top">
              
              <div class="history-wrapper">
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                  <form action="admin_upload_history.php" method="get" id="filterForm">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>" />
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

                      <?php if ($type !== 'custom'): ?>
                      <div class="filter-group">
                        <label for="filter_module">Module</label>
                        <select name="filter_module" id="filter_module">
                          <option value="">-- All Modules --</option>
                          <?php foreach ($configs as $key => $conf): ?>
                            <option value="<?= htmlspecialchars($conf['name']) ?>" <?= ($filterModule === $conf['name']) ? 'selected="selected"' : '' ?>><?= htmlspecialchars($conf['name']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <?php endif; ?>

                      <div class="filter-group">
                        <label for="filter_status">Status</label>
                        <select name="filter_status" id="filter_status">
                          <option value="">-- All Status --</option>
                          <option value="Processing" <?= ($filterStatus === 'Processing') ? 'selected="selected"' : '' ?>>Processing</option>
                          <option value="Success" <?= ($filterStatus === 'Success') ? 'selected="selected"' : '' ?>>Success</option>
                          <option value="Pending Verification" <?= ($filterStatus === 'Pending Verification') ? 'selected="selected"' : '' ?>>Pending Verification</option>
                          <option value="Rejected" <?= ($filterStatus === 'Rejected') ? 'selected="selected"' : '' ?>>Rejected</option>
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
                        <a href="admin_upload_history.php?type=<?= urlencode($type) ?>" class="btn-reset" style="flex: 1;">Reset</a>
                      </div>
                    </div>
                  </form>
                </div>

                <div style="text-align: right; margin-bottom: 10px;">
                  <a href="admin_upload.php?tab=<?= $type === 'custom' ? 'custom' : 'legacy' ?>" class="btn-action" style="text-decoration: none; display: inline-block; font-size: 13px; padding: 6px 12px;">Back to Upload Panel</a>
                </div>

                <!-- Logs Table -->
                <table class="history-table">
                  <thead>
                    <tr>
                      <th>Uploaded At</th>
                      <th>File Name</th>
                      <?php if ($type === 'custom'): ?>
                        <th>Database</th>
                        <th>Target Table</th>
                        <th>Newly Created?</th>
                      <?php else: ?>
                        <th>Module Name</th>
                      <?php endif; ?>
                      <th>Uploaded By</th>
                      <th>IP Address</th>
                      <th>Total Rows</th>
                      <th>Inserted</th>
                      <?php if ($type !== 'custom'): ?>
                        <th>Failed</th>
                      <?php endif; ?>
                      <th>Status</th>
                      <?php if ($type !== 'custom'): ?>
                      <?php if (false): // Approval column hidden - admin-approval step removed ?>
                      <th>Approval</th>
                      <?php endif; ?>
                      <th>Action</th>
                      <?php endif; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($logs)): ?>
                      <tr>
                        <td colspan="10" style="text-align: center; padding: 20px; color: #ccc;">No upload records matching filters were found.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($logs as $log): ?>
                        <?php
                          $isProcessing = ($log['upload_status'] ?? '') === 'Processing' && !empty($log['document_job_id']);
                          $rowJobId = $isProcessing ? (int)$log['document_job_id'] : 0;
                        ?>
                        <tr<?= $rowJobId > 0 ? ' data-processing-job="' . $rowJobId . '" data-log-id="' . (int)$log['id'] . '"' : '' ?>>
                          <td><?= date('d-m-Y H:i A', strtotime($log['uploaded_at'])) ?></td>
                          <td style="font-weight: bold; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['file_name']) ?>">
                            <?= htmlspecialchars($log['file_name']) ?>
                            <div style="font-size: 10px; color: #ccc;"><?= number_format($log['file_size'] / 1024, 2) ?> KB</div>
                          </td>
                          <?php if ($type === 'custom'): ?>
                            <td style="color: #FFA500; font-weight: bold;"><?= htmlspecialchars($log['db_name'] ?? '') ?></td>
                            <td style="color: #FFD700; font-weight: bold;"><?= htmlspecialchars($log['table_name'] ?? '') ?></td>
                            <td>
                              <span class="badge-status" style="background: <?= strtolower($log['is_new_table'] ?? '') === 'yes' ? '#28a745' : 'rgba(255,255,255,0.1)' ?>; padding: 2px 6px; border-radius: 3px; font-size: 10px;">
                                <?= htmlspecialchars($log['is_new_table'] ?? 'No') ?>
                              </span>
                            </td>
                          <?php else: ?>
                            <td><?= htmlspecialchars($log['module_name']) ?></td>
                          <?php endif; ?>
                          <td><?= htmlspecialchars($log['username']) ?></td>
                          <td><?= htmlspecialchars($log['ip_address']) ?></td>
                          <td><?= (int)$log['total_records'] ?></td>
                          <td style="color: #90EE90; font-weight: bold;"><?= (int)$log['inserted_records'] ?></td>
                          <?php if ($type !== 'custom'): ?>
                            <td style="color: #FFB6C1; font-weight: bold;"><?= (int)$log['failed_records'] ?></td>
                          <?php endif; ?>
                          <td>
                            <?php
                              $statusClass = strtolower(str_replace(' ', '-', $log['upload_status']));
                              if (!in_array($statusClass, ['success','partial','failed','pending-verification','rejected','processing'], true)) {
                                  $statusClass = 'partial';
                              }
                            ?>
                            <span class="badge-status status-<?= htmlspecialchars($statusClass) ?>" data-upload-status="<?= $rowJobId > 0 ? (int)$log['id'] : '' ?>">
                              <?= htmlspecialchars($log['upload_status']) ?>
                            </span>
                          </td>
                          <?php if ($type !== 'custom'): ?>
                          <?php if (false): // Approval column hidden - admin-approval step removed ?>
                          <?php $approval = renderApprovalStatus($log); ?>
                          <td>
                            <span class="<?= htmlspecialchars($approval['class']) ?>">
                              <?= $approval['text'] ?>
                            </span>
                          </td>
                          <?php endif; ?>
                          <td>
                            <?php
                              $pendingVerify = ($log['upload_status'] === 'Pending Verification');
                              $hasJob = !empty($log['document_job_id']);
                              $verifyHref = 'admin_upload_verify.php?log_id=' . (int)$log['id'];
                              $resultsHref = 'admin_upload.php?view=results&log_id=' . (int)$log['id'];
                            ?>
                            <?php if ($hasJob && $pendingVerify): ?>
                              <span class="action-btns">
                                <a class="action-icon-btn action-icon-preview" href="<?= htmlspecialchars($verifyHref) ?>" title="Preview &amp; Edit staging">
                                  <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a class="action-icon-btn action-icon-insert" href="<?= htmlspecialchars($resultsHref) ?>" title="Insert Data to live table">
                                  <i class="fa-solid fa-database"></i>
                                </a>
                              </span>
                            <?php elseif ($hasJob): ?>
                              <span class="action-btns">
                                <a class="action-icon-btn action-icon-view" href="<?= htmlspecialchars($resultsHref) ?>" title="View upload">
                                  <i class="fa-solid fa-eye"></i>
                                </a>
                              </span>
                            <?php else: ?>
                              —
                            <?php endif; ?>
                          </td>
                          <?php endif; ?>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                  <div class="pagination">
                    <?php if ($page > 1): ?>
                      <a href="?page=<?= $page - 1 ?>&type=<?= urlencode($type) ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="pagination-link">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <a href="?page=<?= $i ?>&type=<?= urlencode($type) ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="pagination-link <?= ($page === $i) ? 'active' : '' ?>">
                        <?= $i ?>
                      </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                      <a href="?page=<?= $page + 1 ?>&type=<?= urlencode($type) ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="pagination-link">Next &raquo;</a>
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
<script type="text/javascript">
(function pollProcessingUploads() {
    var rows = document.querySelectorAll('tr[data-processing-job]');
    if (!rows.length) return;
    var jobIds = [];
    rows.forEach(function(row) {
        var id = parseInt(row.getAttribute('data-processing-job'), 10);
        if (id > 0) jobIds.push(id);
    });
    if (!jobIds.length) return;

    var delay = 8000;

    function statusClassFor(label) {
        var cls = (label || '').toLowerCase().replace(/\s+/g, '-');
        if (['success','partial','failed','pending-verification','rejected','processing'].indexOf(cls) === -1) {
            cls = 'partial';
        }
        return cls;
    }

    function updateRow(row, job) {
        var logId = row.getAttribute('data-log-id');
        var badge = logId ? document.querySelector('[data-upload-status="' + logId + '"]') : null;
        var uploadStatus = job.upload_status || '';
        if (badge && uploadStatus) {
            badge.textContent = uploadStatus;
            badge.className = 'badge-status status-' + statusClassFor(uploadStatus);
        }
        if (uploadStatus && uploadStatus !== 'Processing') {
            row.removeAttribute('data-processing-job');
        }
    }

    function sync() {
        fetch('admin_upload_sync_jobs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ job_ids: jobIds })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (!data.ok || !data.jobs) return;
            var stillProcessing = false;
            data.jobs.forEach(function(job) {
                if (!job.job_id) return;
                var row = document.querySelector('tr[data-processing-job="' + job.job_id + '"]');
                if (!row) return;
                updateRow(row, job);
                if ((job.upload_status || '') === 'Processing') {
                    stillProcessing = true;
                }
            });
            if (stillProcessing) {
                delay = Math.min(Math.round(delay * 1.25), 20000);
                setTimeout(sync, delay);
            }
        }).catch(function() {
            setTimeout(sync, delay);
        });
    }

    sync();
})();
</script>
<?php layout_end(); ?>
