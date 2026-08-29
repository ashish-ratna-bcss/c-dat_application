<?php
require_once __DIR__ . '/../common/bootstrap.php';
/**
 * admin_upload_history.php
 * Audit log viewer for CDR data uploads.
 * Filterable, paginated audit list with date range restrictions.
 */
require_once CDAT_COMMON . '/activity_logger.php';
require_once CDAT_UPLOAD . '/admin_upload_page.php';
audit_require_uploader();

if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'approve_staging') {
    header('Content-Type: application/json');
    $out = cdat_insert_staging_job((int) ($_POST['job_id'] ?? 0));
    if (!empty($out['ok'])) {
        echo json_encode([
            'ok' => true,
            'inserted' => $out['inserted'] ?? null,
            'status' => $out['status'] ?? 'completed',
            'message' => $out['message'] ?? 'Data inserted into the live table.',
        ]);
    } else {
        echo json_encode(['ok' => false, 'error' => $out['error'] ?? 'Insert failed.']);
    }
    exit;
}

$config = require CDAT_UPLOAD . '/cdr_upload_config.php';
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
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
ob_start();
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<style type="text/css">
.history-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
</style>
<link rel="stylesheet" href="<?= htmlspecialchars(CDAT_ASSETS) ?>/css/upload.css">
<?php
layout_begin('Upload History', 'Previous uploads and their status', ob_get_clean());
cdat_sum_page_open();
?>
              <div class="history-wrapper">

                <!-- Filter Card -->
                <div class="history-filter-card">
                  <form action="<?= htmlspecialchars(function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history') ?>" method="get" id="filterForm">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>" />
                    <div class="row g-2 align-items-end">
                      <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <label class="form-label mb-1" for="filter_user">Uploaded By</label>
                        <select class="form-select form-select-sm" name="filter_user" id="filter_user" data-placeholder="-- All Users --">
                          <option value="">-- All Users --</option>
                          <?php foreach ($uploadUsers as $u): ?>
                            <option value="<?= htmlspecialchars($u) ?>" <?= ($filterUser === $u) ? 'selected' : '' ?>><?= htmlspecialchars($u) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <?php if ($type !== 'custom'): ?>
                      <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <label class="form-label mb-1" for="filter_module">Module</label>
                        <select class="form-select form-select-sm" name="filter_module" id="filter_module" data-placeholder="-- All Modules --">
                          <option value="">-- All Modules --</option>
                          <?php foreach ($configs as $key => $conf): ?>
                            <option value="<?= htmlspecialchars($conf['name']) ?>" <?= ($filterModule === $conf['name']) ? 'selected' : '' ?>><?= htmlspecialchars($conf['name']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <?php endif; ?>

                      <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <label class="form-label mb-1" for="filter_status">Status</label>
                        <select class="form-select form-select-sm" name="filter_status" id="filter_status" data-placeholder="-- All Status --">
                          <option value="">-- All Status --</option>
                          <option value="Processing" <?= ($filterStatus === 'Processing') ? 'selected' : '' ?>>Processing</option>
                          <option value="Success" <?= ($filterStatus === 'Success') ? 'selected' : '' ?>>Success</option>
                          <option value="Pending Verification" <?= ($filterStatus === 'Pending Verification') ? 'selected' : '' ?>>Pending Verification</option>
                          <option value="Rejected" <?= ($filterStatus === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                          <option value="Partial" <?= ($filterStatus === 'Partial') ? 'selected' : '' ?>>Partial</option>
                          <option value="Failed" <?= ($filterStatus === 'Failed') ? 'selected' : '' ?>>Failed</option>
                        </select>
                      </div>

                      <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <label class="form-label mb-1" for="from_date">From Date</label>
                        <input class="form-control form-control-sm" type="date" name="from_date" id="from_date" value="<?= htmlspecialchars($fromDate) ?>" max="<?= $todayStr ?>" />
                      </div>

                      <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <label class="form-label mb-1" for="to_date">To Date</label>
                        <input class="form-control form-control-sm" type="date" name="to_date" id="to_date" value="<?= htmlspecialchars($toDate) ?>" max="<?= $todayStr ?>" />
                      </div>

                      <div class="col-12 col-lg-auto">
                        <div class="d-flex flex-wrap gap-2">
                          <input type="submit" class="btn btn-primary btn-sm flex-fill flex-lg-grow-0" value="Filter" />
                          <a href="<?= htmlspecialchars(function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history') ?>?type=<?= urlencode($type) ?>" class="btn btn-outline-secondary btn-sm flex-fill flex-lg-grow-0">Reset</a>
                          <a href="<?= htmlspecialchars($type === 'custom' ? cdat_upload_self_url('custom') : cdat_upload_self_url('cdr')) ?>" class="btn btn-outline-primary btn-sm flex-fill flex-lg-grow-0">Back to Upload Panel</a>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Logs Table -->
                <div class="history-table-card">
                <div class="history-table-wrap table-responsive mb-0">
                <table class="history-table table table-striped table-hover table-sm mb-0">
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
                          <?php
                            $pendingVerify = ($log['upload_status'] === 'Pending Verification');
                            $insertedShown = $pendingVerify ? 0 : (int)$log['inserted_records'];
                            $failedShown = $pendingVerify ? 0 : (int)$log['failed_records'];
                          ?>
                          <td style="color: #90EE90; font-weight: bold;"><?= $insertedShown ?></td>
                          <?php if ($type !== 'custom'): ?>
                            <td style="color: #FFB6C1; font-weight: bold;"><?= $failedShown ?></td>
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
                              $isSuccess = ($log['upload_status'] === 'Success');
                              $hasJob = !empty($log['document_job_id']);
                              $rowJobId = (int) ($log['document_job_id'] ?? 0);
                              $stagingUrl = cdat_upload_verify_url((int) $log['id']);
                            ?>
                            <?php if ($hasJob && !$isSuccess): ?>
                              <span class="action-btns">
                                <?php if ($pendingVerify): ?>
                                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center justify-content-center js-load-to-db" data-job-id="<?= $rowJobId ?>">Load to DB</button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-info btn-sm d-inline-flex align-items-center justify-content-center js-view-staging" data-staging-url="<?= htmlspecialchars($stagingUrl, ENT_QUOTES) ?>" data-job-id="<?= $rowJobId ?>" data-can-insert="<?= $pendingVerify ? '1' : '0' ?>">View</button>
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
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                  <div class="history-pagination d-flex flex-wrap gap-1">
                    <?php if ($page > 1): ?>
                      <a href="?page=<?= $page - 1 ?>&type=<?= urlencode($type) ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="btn btn-outline-secondary btn-sm">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <a href="?page=<?= $i ?>&type=<?= urlencode($type) ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="btn btn-sm <?= ($page === $i) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        <?= $i ?>
                      </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                      <a href="?page=<?= $page || 1 ?>&type=<?= urlencode($type) ?>&filter_user=<?= urlencode($filterUser) ?>&filter_module=<?= urlencode($filterModule) ?>&filter_status=<?= urlencode($filterStatus) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>" class="btn btn-outline-secondary btn-sm">Next &raquo;</a>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                </div><!-- /.history-table-card -->

              </div><!-- /.history-wrapper -->

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
        fetch(<?= json_encode(function_exists('cdat_href') ? cdat_href('/data-upload/sync-jobs') : '/data-upload/sync-jobs') ?>, {
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
<script type="text/javascript">
(function () {
    var historySelf = <?= json_encode(function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history') ?>;
    var activeJobId = 0;

    function embedStagingUrl(url) {
        if (!url) return url;
        return url + (url.indexOf('?') >= 0 ? '&' : '?') + 'embed=1';
    }

    function closeStagingModal(reload) {
        var modal = document.getElementById('staging-modal');
        var frame = document.getElementById('staging-modal-frame');
        var loadBtn = document.getElementById('staging-modal-load');
        if (modal) modal.hidden = true;
        if (frame) frame.src = 'about:blank';
        if (loadBtn) loadBtn.hidden = true;
        activeJobId = 0;
        document.body.style.overflow = '';
        if (reload) window.location.reload();
    }

    function openStagingModal(url, jobId, canInsert) {
        var modal = document.getElementById('staging-modal');
        var frame = document.getElementById('staging-modal-frame');
        var loadBtn = document.getElementById('staging-modal-load');
        if (!modal || !frame) return;
        activeJobId = parseInt(jobId, 10) || 0;
        if (loadBtn) {
            loadBtn.hidden = !canInsert;
            loadBtn.disabled = false;
            loadBtn.textContent = 'Load to DB';
        }
        frame.src = embedStagingUrl(url);
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function loadToDb(jobId, btn) {
        jobId = parseInt(jobId, 10) || 0;
        if (jobId <= 0) {
            alert('No staging job found for this upload.');
            return;
        }
        var orig = btn ? btn.textContent : '';
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Loading…';
        }
        var fd = new FormData();
        fd.append('ajax_action', 'approve_staging');
        fd.append('job_id', jobId);
        fetch(historySelf, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    alert(data.error || 'Insert failed.');
                    if (btn) { btn.disabled = false; btn.textContent = orig || 'Load to DB'; }
                    return;
                }
                window.location.reload();
            })
            .catch(function (err) {
                alert('Insert failed: ' + (err.message || err));
                if (btn) { btn.disabled = false; btn.textContent = orig || 'Load to DB'; }
            });
    }

    document.addEventListener('click', function (e) {
        var loadBtn = e.target.closest('.js-load-to-db');
        if (loadBtn) {
            loadToDb(loadBtn.getAttribute('data-job-id'), loadBtn);
            return;
        }
        var viewBtn = e.target.closest('.js-view-staging');
        if (viewBtn) {
            openStagingModal(
                viewBtn.getAttribute('data-staging-url'),
                viewBtn.getAttribute('data-job-id'),
                viewBtn.getAttribute('data-can-insert') === '1'
            );
            return;
        }
        if (e.target.closest('[data-staging-close], #staging-modal-close')) {
            closeStagingModal(false);
        }
        if (e.target.closest('#staging-modal-load')) {
            var headerLoad = document.getElementById('staging-modal-load');
            loadToDb(activeJobId, headerLoad);
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        var modal = document.getElementById('staging-modal');
        if (modal && !modal.hidden) closeStagingModal(false);
    });
    window.addEventListener('message', function (e) {
        if (e.data && e.data.type === 'cdat-staging-close') {
            closeStagingModal(!!e.data.reload);
        }
    });
})();
</script>
<div id="staging-modal" class="staging-modal" hidden>
  <div class="staging-modal__backdrop" data-staging-close="1"></div>
  <div class="staging-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="staging-modal-title">
    <div class="staging-modal__head">
      <h2 id="staging-modal-title">Staging data</h2>
      <div class="staging-modal__actions">
        <button type="button" class="staging-modal__load btn btn-success btn-sm" id="staging-modal-load" hidden>Load to DB</button>
        <button type="button" class="staging-modal__close btn btn-outline-secondary btn-sm" id="staging-modal-close">Close</button>
      </div>
    </div>
    <iframe id="staging-modal-frame" class="staging-modal__frame" title="Staging data"></iframe>
  </div>
</div>
<?php
cdat_sum_page_close();
layout_end();
?>
