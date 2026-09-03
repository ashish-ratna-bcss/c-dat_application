<?php
require_once __DIR__ . '/../common/bootstrap.php';
/**
 * Upload History for CDR files staged from the CDR page.
 */
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_uploader();

function cdat_upload_schema(): string
{
    static $name = null;
    if ($name !== null) {
        return $name;
    }
    $name = 'cdatupload';
    $envFile = CDAT_ROOT . '/.env';
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim((string) $line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");
            if ($key === 'CDAT_UPLOAD_SCHEMA' && $value !== '') {
                $name = $value;
                break;
            }
        }
    }
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
        $name = 'cdatupload';
    }
    return $name;
}

function cdat_upload_logs_table(): string
{
    return cdat_upload_schema() . '.upload_activity_logs';
}

function cdat_data_upload_url(): string
{
    cdat_load_dotenv();
    $url = (string) (getenv('DATA_UPLOAD_URL') ?: '');
    $host = (string) (getenv('DATA_UPLOAD_HOST') ?: '127.0.0.1');
    $port = (string) (getenv('DATA_UPLOAD_PORT') ?: '8090');
    if ($url === '/' || strcasecmp($url, 'same') === 0) {
        return '';
    }
    if ($url !== '') {
        return rtrim($url, '/');
    }
    if ($host === '0.0.0.0' || $host === '::' || $host === '[::]') {
        $host = '127.0.0.1';
    }
    return 'http://' . $host . ':' . $port;
}

function cdat_data_upload_api_key(): string
{
    cdat_load_dotenv();
    $key = (string) (getenv('DATA_UPLOAD_API_KEY') ?: '');
    if ($key === '') {
        $key = (string) (getenv('CDR_API_KEY') ?: '');
    }
    return $key;
}

function cdat_upload_self_url(string $page = 'cdr'): string
{
    $path = $page === 'history' ? '/data-upload/history' : '/data-upload/cdr';
    return function_exists('cdat_href') ? cdat_href($path) : $path;
}

function cdat_upload_row(array $row): array
{
    $out = [];
    foreach ($row as $key => $value) {
        $out[strtolower((string) $key)] = $value;
    }
    return $out;
}

function cdat_insert_staging_job(int $jobId): array
{
    if ($jobId <= 0) {
        return ['ok' => false, 'error' => 'Missing job id.', 'inserted' => null, 'status' => null, 'message' => null];
    }
    $url = cdat_data_upload_url() . '/api/v1/cdr/jobs/' . $jobId . '/insert';
    $headers = "Accept: application/json\r\n";
    $apiKey = cdat_data_upload_api_key();
    if ($apiKey !== '') {
        $headers .= 'X-API-Key: ' . $apiKey . "\r\n";
    }
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => 600,
            'ignore_errors' => true,
            'header' => $headers,
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    $payload = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($payload) || empty($payload['ok'])) {
        $detail = is_array($payload) ? ($payload['detail'] ?? $payload['error'] ?? null) : null;
        return [
            'ok' => false,
            'error' => is_string($detail) ? $detail : 'Could not queue Insert DB.',
            'inserted' => null,
            'status' => null,
            'message' => null,
        ];
    }
    return [
        'ok' => true,
        'inserted' => $payload['inserted_records'] ?? 0,
        'status' => $payload['phase'] ?? 'inserting',
        'message' => $payload['message'] ?? 'Insert queued.',
    ];
}

if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'approve_staging') {
    require_once CDAT_COMMON . '/csrf.php';
    csrf_verify();
    header('Content-Type: application/json');
    $out = cdat_insert_staging_job((int) ($_POST['job_id'] ?? 0));
    if (!empty($out['ok'])) {
        audit_log('Data Upload', 'Approve staging / Insert DB', [
            'job_id' => (int) ($_POST['job_id'] ?? 0),
        ]);
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

$db = audit_db();
$logsTable = cdat_upload_logs_table();
$jobsApi = (defined('CDAT_BASE') ? rtrim((string) CDAT_BASE, '/') : '') . '/api/data-upload/cdr/jobs';

// 1. Get unique upload usernames for filtering
try {
    $userStmt = $db->query("SELECT DISTINCT username FROM {$logsTable} ORDER BY username");
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

$where[] = "module_name NOT LIKE 'Custom:%'";
$where[] = "module_name <> 'SDR'";

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
    $countQuery = "SELECT COUNT(*) FROM {$logsTable} WHERE " . $whereClause;
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
        SELECT l.*
        FROM {$logsTable} l
        WHERE {$whereClause}
        ORDER BY l.uploaded_at DESC, l.id DESC
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
    $logs = array_map('cdat_upload_row', $selectStmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
} catch (Exception $e) {
    // Fail silently
}

function renderApprovalStatus(array $log): array
{
    $uploadStatus = (string)($log['upload_status'] ?? '');
    $verificationStatus = strtolower((string)($log['verification_status'] ?? ''));
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

function cdat_history_page_url(
    int $pageNum,
    string $filterUser,
    string $filterModule,
    string $filterStatus,
    string $fromDate,
    string $toDate
): string {
    $query = array_filter([
        'page' => $pageNum > 1 ? $pageNum : null,
        'filter_user' => $filterUser !== '' ? $filterUser : null,
        'filter_module' => $filterModule !== '' ? $filterModule : null,
        'filter_status' => $filterStatus !== '' ? $filterStatus : null,
        'from_date' => $fromDate !== '' ? $fromDate : null,
        'to_date' => $toDate !== '' ? $toDate : null,
    ], static fn($v) => $v !== null && $v !== '');
    $base = function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history';
    return $query === [] ? $base : $base . '?' . http_build_query($query);
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

                      <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <label class="form-label mb-1" for="filter_module">Module</label>
                        <select class="form-select form-select-sm" name="filter_module" id="filter_module" data-placeholder="-- All Modules --">
                          <option value="">-- All Modules --</option>
                          <option value="CDR" <?= ($filterModule === 'CDR') ? 'selected' : '' ?>>CDR</option>
                        </select>
                      </div>

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
                          <a href="<?= htmlspecialchars(function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history') ?>" class="btn btn-outline-secondary btn-sm flex-fill flex-lg-grow-0">Reset</a>
                          <a href="<?= htmlspecialchars(cdat_upload_self_url('cdr')) ?>" class="btn btn-outline-primary btn-sm flex-fill flex-lg-grow-0">Back to Upload Panel</a>
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
                      <th>Module Name</th>
                      <th>Uploaded By</th>
                      <th>IP Address</th>
                      <th>Total Rows</th>
                      <th>New</th>
                      <th>Skipped</th>
                      <th>Inserted</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($logs)): ?>
                      <tr>
                        <td colspan="11" style="text-align: center; padding: 20px; color: #ccc;">No upload records matching filters were found.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($logs as $log): ?>
                        <?php
                          $isProcessing = ($log['upload_status'] ?? '') === 'Processing';
                          $rowJobId = (int) ($log['document_job_id'] ?? 0);
                        ?>
                        <tr<?= $rowJobId > 0 ? ' data-pipeline-job="' . $rowJobId . '" data-log-id="' . (int)$log['id'] . '"' : '' ?>>
                          <td><?= date('d-m-Y H:i A', strtotime($log['uploaded_at'])) ?></td>
                          <td style="font-weight: bold; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['file_name']) ?>">
                            <?= htmlspecialchars($log['file_name']) ?>
                            <div style="font-size: 10px; color: #ccc;"><?= number_format($log['file_size'] / 1024, 2) ?> KB</div>
                          </td>
                          <td><?= htmlspecialchars($log['module_name']) ?></td>
                          <td><?= htmlspecialchars($log['username']) ?></td>
                          <td><?= htmlspecialchars($log['ip_address']) ?></td>
                          <td><?= (int)$log['total_records'] ?></td>
                          <?php
                            $totalShown = (int) ($log['total_records'] ?? 0);
                            $skippedShown = (int) ($log['failed_records'] ?? 0);
                            $newShown = (int) ($log['new_records'] ?? max(0, $totalShown - $skippedShown));
                            $insertedShown = (int) ($log['inserted_records'] ?? 0);
                          ?>
                          <td style="color: #90EE90; font-weight: bold;"><?= $newShown ?></td>
                          <td style="color: #E6C35C; font-weight: bold;"><?= $skippedShown ?></td>
                          <td style="color: #90EE90; font-weight: bold;"><?= $insertedShown ?></td>
                          <td>
                            <?php
                              $statusClass = strtolower(str_replace(' ', '-', (string)($log['upload_status'] ?? '')));
                              if (!in_array($statusClass, ['success','partial','failed','pending-verification','rejected','processing'], true)) {
                                  $statusClass = 'partial';
                              }
                            ?>
                            <span class="badge-status status-<?= htmlspecialchars($statusClass) ?>" data-upload-status="<?= $rowJobId > 0 ? (int)$log['id'] : '' ?>">
                              <?= htmlspecialchars($isProcessing ? 'Processing' : (string)($log['upload_status'] ?? '')) ?>
                            </span>
                          </td>
                          <td>
                            <?php
                              $pendingVerify = (($log['upload_status'] ?? '') === 'Pending Verification');
                            ?>
                            <span class="action-btns" data-pipeline-actions="<?= $rowJobId ?>">
                              <?php if ($rowJobId > 0 && $pendingVerify): ?>
                                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center justify-content-center js-insert-db" data-job-id="<?= $rowJobId ?>">Insert DB</button>
                                <button type="button" class="btn btn-info btn-sm d-inline-flex align-items-center justify-content-center js-view-staging" data-job-id="<?= $rowJobId ?>">View</button>
                              <?php else: ?>
                                —
                              <?php endif; ?>
                            </span>
                          </td>
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
                      <a href="<?= htmlspecialchars(cdat_history_page_url($page - 1, $filterUser, $filterModule, $filterStatus, $fromDate, $toDate), ENT_QUOTES) ?>" class="btn btn-outline-secondary btn-sm">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <a href="<?= htmlspecialchars(cdat_history_page_url($i, $filterUser, $filterModule, $filterStatus, $fromDate, $toDate), ENT_QUOTES) ?>" class="btn btn-sm <?= ($page === $i) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        <?= $i ?>
                      </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                      <a href="<?= htmlspecialchars(cdat_history_page_url($page + 1, $filterUser, $filterModule, $filterStatus, $fromDate, $toDate), ENT_QUOTES) ?>" class="btn btn-outline-secondary btn-sm">Next &raquo;</a>
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
(function () {
    var jobsApi = <?= json_encode($jobsApi, JSON_UNESCAPED_SLASHES) ?>;
    var activeJobId = 0;
    var pageSize = 100;
    var pageOffset = 0;
    var pageTotal = 0;
    var pageFile = '';

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function cellText(value) {
        var text = String(value == null ? '' : value).trim();
        if (!text || text === '-' || text === '--' || text === '---') {
            return '<span class="cdr-empty">—</span>';
        }
        return escapeHtml(text);
    }

    function closeStagingModal() {
        var modal = document.getElementById('staging-modal');
        if (modal) modal.hidden = true;
        activeJobId = 0;
        pageOffset = 0;
        pageTotal = 0;
        document.body.style.overflow = '';
    }

    function formatCount(n) {
        return Number(n || 0).toLocaleString('en-IN');
    }

    function loadStagingPage() {
        var meta = document.getElementById('staging-modal-meta');
        var table = document.getElementById('staging-modal-table');
        var pageEl = document.getElementById('staging-modal-page');
        var prevBtn = document.getElementById('staging-modal-prev');
        var nextBtn = document.getElementById('staging-modal-next');
        var insertBtn = document.getElementById('staging-modal-load');
        if (!table) return;
        meta.textContent = 'Loading…';
        fetch(jobsApi + '/' + activeJobId + '/rows?limit=' + pageSize + '&offset=' + pageOffset)
            .then(function (r) { return r.json().then(function (body) { return { ok: r.ok, body: body }; }); })
            .then(function (res) {
                if (!res.ok || res.body.ok === false) {
                    throw new Error(res.body.detail || 'Could not load staging rows.');
                }
                var cols = (res.body.columns || []).map(function (c) { return c.name || c; });
                var rows = res.body.rows || [];
                pageTotal = Number(res.body.total || rows.length);
                var from = pageTotal ? pageOffset + 1 : 0;
                var to = Math.min(pageOffset + rows.length, pageTotal);
                meta.textContent = (pageFile ? pageFile + ' · ' : '') + formatCount(pageTotal) + ' rows';
                pageEl.textContent = pageTotal
                    ? ('Showing ' + formatCount(from) + '–' + formatCount(to) + ' of ' + formatCount(pageTotal))
                    : 'No rows';
                prevBtn.disabled = pageOffset <= 0;
                nextBtn.disabled = pageOffset + pageSize >= pageTotal;
                table.querySelector('thead').innerHTML = '<tr>' + cols.map(function (name) {
                    return '<th>' + escapeHtml(name) + '</th>';
                }).join('') + '</tr>';
                table.querySelector('tbody').innerHTML = rows.length
                    ? rows.map(function (row) {
                        return '<tr>' + cols.map(function (name) {
                            return '<td>' + cellText(row[name]) + '</td>';
                        }).join('') + '</tr>';
                    }).join('')
                    : '<tr><td colspan="' + Math.max(cols.length, 1) + '">No staging rows.</td></tr>';
                var wrap = table.closest('.staging-modal__table-wrap');
                if (wrap) wrap.scrollTop = 0;
                return fetch(jobsApi + '?ids=' + activeJobId).then(function (r) { return r.json(); });
            })
            .then(function (data) {
                var job = (data && data.jobs && data.jobs[0]) || {};
                if (job.filename) {
                    pageFile = job.filename;
                    meta.textContent = pageFile + ' · ' + formatCount(pageTotal) + ' rows';
                }
                if (insertBtn) insertBtn.hidden = !job.can_insert;
            })
            .catch(function (err) {
                meta.textContent = err.message || 'Could not load staging rows.';
            });
    }

    function openStagingModal(jobId) {
        var modal = document.getElementById('staging-modal');
        var table = document.getElementById('staging-modal-table');
        var insertBtn = document.getElementById('staging-modal-load');
        if (!modal || !table) return;
        activeJobId = parseInt(jobId, 10) || 0;
        pageOffset = 0;
        pageFile = '';
        table.querySelector('thead').innerHTML = '';
        table.querySelector('tbody').innerHTML = '';
        if (insertBtn) {
            insertBtn.hidden = true;
            insertBtn.setAttribute('data-job-id', String(activeJobId));
        }
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        loadStagingPage();
    }

    function renderActions(job) {
        var html = '';
        if (job.can_insert) {
            html += '<button type="button" class="btn btn-success btn-sm js-insert-db" data-job-id="' + job.job_id + '">Insert DB</button> ';
        }
        if (job.can_view) {
            html += '<button type="button" class="btn btn-info btn-sm js-view-staging" data-job-id="' + job.job_id + '">View</button>';
        }
        return html || '—';
    }

    function applyJob(job) {
        var rows = document.querySelectorAll('[data-pipeline-job="' + job.job_id + '"]');
        Array.prototype.forEach.call(rows, function (tr) {
            var badge = tr.querySelector('.badge-status');
            if (badge && job.status_text) {
                badge.textContent = job.status_text;
                badge.className = 'badge-status status-' + (
                    job.phase === 'failed' ? 'failed'
                    : job.phase === 'inserted' ? 'success'
                    : job.phase === 'staged' ? 'pending-verification'
                    : 'processing'
                );
            }
            var totalCell = tr.children[5];
            var newCell = tr.children[6];
            var skippedCell = tr.children[7];
            var insertedCell = tr.children[8];
            if (totalCell && job.total_records != null) totalCell.textContent = job.total_records;
            if (newCell && job.new_records != null) newCell.textContent = job.new_records;
            if (skippedCell && job.failed_records != null) skippedCell.textContent = job.failed_records;
            if (insertedCell && job.inserted_records != null) insertedCell.textContent = job.inserted_records;
            var actions = tr.querySelector('[data-pipeline-actions]');
            if (actions) actions.innerHTML = renderActions(job);
        });
    }

    function pollJobs() {
        var ids = [];
        document.querySelectorAll('[data-pipeline-job]').forEach(function (el) {
            var id = el.getAttribute('data-pipeline-job');
            if (id && ids.indexOf(id) === -1) ids.push(id);
        });
        if (!ids.length) return;
        fetch(jobsApi + '?ids=' + ids.join(','))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                (data.jobs || []).forEach(applyJob);
            })
            .catch(function () {});
    }

    function insertJob(jobId, btn) {
        jobId = parseInt(jobId, 10) || 0;
        if (jobId <= 0) {
            alert('No staging job found for this upload.');
            return;
        }
        var orig = btn ? btn.textContent : '';
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Queuing…';
        }
        fetch(jobsApi + '/' + jobId + '/insert', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                'Accept': 'application/json'
            }
        })
            .then(function (r) { return r.json().then(function (body) { return { ok: r.ok, body: body }; }); })
            .then(function (res) {
                if (!res.ok || res.body.ok === false) {
                    throw new Error(res.body.detail || res.body.error || 'Insert failed.');
                }
                closeStagingModal();
                pollJobs();
            })
            .catch(function (err) {
                alert(err.message || 'Insert failed.');
                if (btn) { btn.disabled = false; btn.textContent = orig || 'Insert DB'; }
            });
    }

    document.addEventListener('click', function (e) {
        var insertBtn = e.target.closest('.js-insert-db, #staging-modal-load');
        if (insertBtn && insertBtn.id === 'staging-modal-load') {
            insertJob(insertBtn.getAttribute('data-job-id') || activeJobId, insertBtn);
            return;
        }
        if (insertBtn && insertBtn.classList.contains('js-insert-db')) {
            insertJob(insertBtn.getAttribute('data-job-id'), insertBtn);
            return;
        }
        var viewBtn = e.target.closest('.js-view-staging');
        if (viewBtn) {
            openStagingModal(viewBtn.getAttribute('data-job-id'));
            return;
        }
        if (e.target.closest('[data-staging-close], #staging-modal-close')) {
            closeStagingModal();
            return;
        }
        if (e.target.closest('#staging-modal-prev') && pageOffset > 0) {
            pageOffset = Math.max(0, pageOffset - pageSize);
            loadStagingPage();
            return;
        }
        if (e.target.closest('#staging-modal-next') && pageOffset + pageSize < pageTotal) {
            pageOffset += pageSize;
            loadStagingPage();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeStagingModal();
    });
    pollJobs();
    setInterval(pollJobs, 2000);
})();
</script>
<div id="staging-modal" class="staging-modal" hidden>
  <div class="staging-modal__backdrop" data-staging-close="1"></div>
  <div class="staging-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="staging-modal-title">
    <div class="staging-modal__head">
      <div class="staging-modal__head-text">
        <h2 id="staging-modal-title">Staging data</h2>
        <p id="staging-modal-meta" class="staging-modal__meta"></p>
      </div>
      <div class="staging-modal__actions">
        <button type="button" class="staging-modal__load btn btn-success btn-sm" id="staging-modal-load" hidden>Insert DB</button>
        <button type="button" class="staging-modal__close btn btn-outline-secondary btn-sm" id="staging-modal-close">Close</button>
      </div>
    </div>
    <div class="staging-modal__body">
      <div class="staging-modal__table-wrap">
        <table class="preview-table cdr-table" id="staging-modal-table">
          <thead></thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="staging-modal__pager">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="staging-modal-prev">Previous</button>
        <span id="staging-modal-page">—</span>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="staging-modal-next">Next</button>
      </div>
    </div>
  </div>
</div>
<?php
cdat_sum_page_close();
layout_end();
?>
