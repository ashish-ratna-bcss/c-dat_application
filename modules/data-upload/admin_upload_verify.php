<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/activity_logger.php';
require_once CDAT_UPLOAD . '/admin_upload_page.php';
require_once CDAT_UPLOAD . '/upload_verification_service.php';
audit_require_uploader();

$service = new UploadVerificationService();
$logId = (int)($_GET['log_id'] ?? $_POST['log_id'] ?? 0);

if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    try {
        $batch = $service->getBatchByLogId($logId);
        if (!$batch) {
            echo json_encode(['ok' => false, 'error' => 'Upload record or staging batch not found.']);
            exit;
        }
        $tables = $batch['staging_tables'];
        $module = strtolower($batch['module'] ?? 'cdr');
        $action = $_POST['ajax_action'];

        if ($action === 'fetch_rows') {
            $tableKey = $module === 'cdr' ? 'cdr' : ($_POST['table_key'] ?? array_key_first($tables));
            $table = $tables[$tableKey] ?? '';
            $limit = min(5000, max(10, (int)($_POST['limit'] ?? 100)));
            $offset = max(0, (int)($_POST['offset'] ?? 0));
            $forceRefresh = !empty($_POST['refresh_enrich']);
            // Approve and reject both drop the staging table. Say so, rather
            // than letting the query fail and reporting the PDO error verbatim.
            if (!$service->stagingTableExists($table)) {
                $status = strtolower($batch['verification_status'] ?? '');
                echo json_encode(['ok' => false, 'gone' => true, 'status' => $status, 'error' =>
                    $status === 'approved'
                        ? 'This upload has already been approved. Its staging rows were removed once they were loaded into production, so there is nothing left to review.'
                        : ($status === 'rejected'
                            ? 'This upload was rejected and its staging rows were removed.'
                            : 'The staging table for this upload no longer exists.')]);
                exit;
            }
            if ($module === 'cdr') {
                if ($forceRefresh) {
                    $service->enrichCdrStaging($table);
                }
                
                $service->refreshCdrDuplicates($table);
                $data = $service->fetchCdrRows($table, $limit, $offset);
            } else {
                if ($forceRefresh || ($offset === 0 && in_array($tableKey, ['cdataddress', 'address_other_state'], true))) {
                    $service->refreshSdrDuplicates($table, $tableKey, true);
                }
                $data = $service->fetchSdrRows($table, $limit, $offset);
            }
            $counts = $service->duplicateCounts($table);
            echo json_encode(['ok' => true, 'module' => $module, 'table' => $table, 'table_key' => $tableKey, 'counts' => $counts] + $data);
            exit;
        }

        if ($action === 'update_row') {
            $table = $_POST['staging_table'] ?? '';
            $rowId = (int)($_POST['staging_row_id'] ?? 0);
            $fields = json_decode($_POST['fields'] ?? '{}', true) ?: [];
            if ($module === 'cdr') {
                $service->updateCdrRow($table, $rowId, $fields);
                $service->enrichCdrStaging($table, $rowId);
                $updated = $service->refreshCdrDuplicatesAfterEdit($table, $rowId);
                $counts = $service->duplicateCounts($table);
                echo json_encode([
                    'ok' => true,
                    'row' => $updated,
                    'counts' => $counts,
                ]);
            } else {
                $service->updateSdrRow($table, $rowId, $fields);
                $tableKey = $_POST['table_key'] ?? array_key_first($tables);
                if (in_array($tableKey, ['cdataddress', 'address_other_state'], true)) {
                    $service->refreshSdrDuplicates($table, $tableKey, true);
                }
                echo json_encode(['ok' => true]);
            }
            exit;
        }

        if ($action === 'approve') {
            $result = $service->approveBatch((int)$batch['batch_id'], $_SESSION['audit_username'] ?? 'admin');
            if (!empty($result['queued'])) {
                echo json_encode([
                    'ok' => true,
                    'queued' => true,
                    'queue_id' => (int)$result['queue_id'],
                    'position' => (int)($result['position'] ?? 1),
                    'message' => $result['message'] ?? 'Queued for promotion.',
                ]);
            } else {
                echo json_encode([
                    'ok' => true,
                    'queued' => false,
                    'message' => $result['message'] ?? ('Approved. ' . $result['inserted'] . ' rows promoted to production.'),
                    'inserted' => (int)$result['inserted'],
                ]);
            }
            exit;
        }

        if ($action === 'poll_approve') {
            $queueId = (int)($_POST['queue_id'] ?? 0);
            if ($queueId <= 0) {
                echo json_encode(['ok' => false, 'error' => 'Missing queue_id.']);
                exit;
            }
            $result = $service->tickApprovalQueue($queueId);
            echo json_encode([
                'ok' => true,
                'queued' => !empty($result['queued']),
                'queue_id' => $queueId,
                'position' => (int)($result['position'] ?? 0),
                'message' => $result['message'] ?? '',
                'inserted' => (int)($result['inserted'] ?? 0),
            ]);
            exit;
        }

        if ($action === 'reject') {
            $service->rejectBatch((int)$batch['batch_id'], $_SESSION['audit_username'] ?? 'admin');
            echo json_encode(['ok' => true, 'message' => 'Upload rejected and staging tables removed.']);
            exit;
        }

        echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$batch = $logId > 0 ? $service->getBatchByLogId($logId) : null;
$canVerify = $batch && ($batch['verification_status'] ?? 'pending') === 'pending';
$embed = isset($_GET['embed']) && (string)$_GET['embed'] === '1';
?>
<?php
// The stylesheet below belongs in <head>; capture it and pass it to
// layout_begin() so it can stay written as plain CSS in this file.
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
$uploadBackUrl = cdat_upload_url_for_module($batch['module'] ?? 'cdr');
$historyBackUrl = (function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history') . '?type=standard';
ob_start();
?>
<style type="text/css">
body.staging-embed { margin: 0; background: #fff; height: 100%; }
html:has(body.staging-embed), body.staging-embed { height: 100%; }
body.staging-embed .verify-head { display: none; }
body.staging-embed .content {
    padding: 0;
    height: 100%;
    min-height: 0;
    display: flex;
    flex-direction: column;
}
</style>
<link rel="stylesheet" href="<?= htmlspecialchars(CDAT_ASSETS) ?>/css/upload.css">
<?php
$headExtra = ob_get_clean();
if ($embed) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Staging Preview &amp; Edit</title>';
    echo '<link rel="stylesheet" href="' . htmlspecialchars(CDAT_ASSETS, ENT_QUOTES) . '/vendor/bootstrap/css/bootstrap.min.css">';
    echo '<link rel="stylesheet" href="' . htmlspecialchars(CDAT_ASSETS, ENT_QUOTES) . '/css/cdat-bootstrap.css">';
    echo '<link rel="stylesheet" href="' . htmlspecialchars(CDAT_ASSETS, ENT_QUOTES) . '/css/app.css">';
    echo '<link rel="stylesheet" href="' . htmlspecialchars(CDAT_ASSETS, ENT_QUOTES) . '/css/upload.css">';
    echo $headExtra;
    echo '</head><body class="staging-embed"><div class="content">';
} else {
    layout_begin('Staging Preview & Edit', 'Review rows before they load into production', $headExtra);
    cdat_sum_page_open();
}
?>
              <div class="verify-wrapper">
    <?php if (!$embed): ?>
    <div class="verify-head d-flex flex-wrap align-items-center gap-2 mb-3 pb-2 border-bottom">
      <h2 class="h5 mb-0">Staging Preview &amp; Edit</h2>
      <button type="button" class="staging-close-btn btn btn-outline-secondary btn-sm ms-auto" id="staging-close-btn">Close</button>
    </div>
    <?php endif; ?>
  <?php if (!$batch): ?>
    <div class="alert alert-warn">Upload record not found or has no staging data.</div>
    <?php if (!$embed): ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($historyBackUrl, ENT_QUOTES) ?>">Back to History</a>
    <?php endif; ?>
  <?php else: ?>
    <?php
      $vStatus = (string) ($batch['verification_status'] ?? 'pending');
      $vStatusLabel = ucfirst(str_replace('_', ' ', $vStatus));
    ?>
    <div class="verify-meta row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-2 mb-3">
      <div class="col d-flex">
        <div class="verify-kv">
        <span class="form-label d-block mb-0">File</span>
        <strong><?= htmlspecialchars($batch['file_name'] ?? '') ?></strong>
        </div>
      </div>
      <div class="col d-flex">
        <div class="verify-kv">
        <span class="form-label d-block mb-0">Module</span>
        <strong><?= htmlspecialchars($batch['module_name'] ?? $batch['module'] ?? '') ?></strong>
        </div>
      </div>
      <div class="col d-flex">
        <div class="verify-kv">
        <span class="form-label d-block mb-0">Uploaded by</span>
        <strong><?= htmlspecialchars($batch['username'] ?? '') ?></strong>
        </div>
      </div>
      <div class="col d-flex">
        <div class="verify-kv">
        <span class="form-label d-block mb-0">Status</span>
        <strong class="verify-status verify-status--<?= htmlspecialchars($vStatus, ENT_QUOTES) ?>"><?= htmlspecialchars($vStatusLabel) ?></strong>
        </div>
      </div>
    </div>
    <div class="verify-summary">
      <div class="verify-stats" id="stats"></div>
      <p class="verify-hint">Red rows are already in production and will not be loaded. Click a cell to edit — it saves when you leave it.</p>
    </div>
    <div id="sdr-tabs" class="tabs" style="display:none;"></div>
    <div class="table-scroll table-responsive">
      <table id="verify-table" class="table table-sm table-hover mb-0">
        <thead id="verify-head"></thead>
        <tbody id="verify-body"></tbody>
      </table>
    </div>
    <div class="verify-footer mt-3">
      <div class="pager d-flex flex-column flex-lg-row gap-2 align-items-stretch align-items-lg-center justify-content-between mb-2">
        <div class="pager-left d-flex flex-wrap gap-2 align-items-center">
          <label class="form-label mb-0" for="page-size">Rows per page</label>
          <select class="form-select form-select-sm page-size-select" id="page-size">
            <option value="100">100</option>
            <option value="250">250</option>
            <option value="500" selected="selected">500</option>
            <option value="1000">1000</option>
            <option value="all">All</option>
          </select>
          <span id="page-label"></span>
        </div>
        <div class="pager-right d-flex flex-wrap gap-2 justify-content-lg-end">
          <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" id="prev-page" type="button">Prev</button>
          <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" id="next-page" type="button">Next</button>
        </div>
      </div>
      <div class="toolbar d-flex flex-wrap gap-2">
        <button class="btn btn-info btn-sm d-inline-flex align-items-center justify-content-center" id="refresh-btn" type="button">Refresh duplicates</button>
        <?php if ($canVerify): ?>
          <button class="btn btn-success btn-sm d-inline-flex align-items-center justify-content-center" id="approve-btn" type="button">Load to DB</button>
          <button class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center" id="reject-btn" type="button">Reject</button>
        <?php endif; ?>
        <?php if (!$embed): ?>
        <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" href="<?= htmlspecialchars($historyBackUrl, ENT_QUOTES) ?>">Back to History</a>
        <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" href="<?= htmlspecialchars($uploadBackUrl, ENT_QUOTES) ?>">Back to Upload</a>
        <?php endif; ?>
      </div>
      <div id="message"></div>
    </div>
  <?php endif; ?>
              </div>
<?php if (!$embed) { cdat_sum_page_close(); } ?>

<script>
const logId = <?= (int)$logId ?>;
const isEmbed = <?= $embed ? 'true' : 'false' ?>;
const uploadBackUrl = <?= json_encode($uploadBackUrl) ?>;
const historyBackUrl = <?= json_encode($historyBackUrl) ?>;
const moduleName = <?= json_encode(strtolower($batch['module'] ?? 'cdr')) ?>;
const stagingTables = <?= json_encode($batch['staging_tables'] ?? []) ?>;
let currentTableKey = moduleName === 'cdr' ? 'cdr' : Object.keys(stagingTables)[0];
let currentTable = stagingTables[currentTableKey] || '';
let offset = 0;
let limit = 500;
let totalRows = 0;

function renderStats(counts) {
  const el = document.getElementById('stats');
  if (!el) return;
  const valid = counts?.valid_count ?? 0;
  const dups = counts?.duplicate_count ?? 0;
  el.innerHTML =
    '<span class="verify-pill verify-pill--ok"><em>Valid</em> <strong>' + valid + '</strong></span>' +
    '<span class="verify-pill verify-pill--dup"><em>Duplicates</em> <strong>' + dups + '</strong></span>';
}

function leaveStaging(toHistory) {
  if (isEmbed && window.parent && window.parent !== window) {
    window.parent.postMessage({ type: 'cdat-staging-close', reload: !!toHistory }, '*');
    return;
  }
  window.location.href = toHistory ? historyBackUrl : uploadBackUrl;
}

document.getElementById('staging-close-btn')?.addEventListener('click', function () {
  leaveStaging(false);
});
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') leaveStaging(false);
});

function currentLimit() {
  const sel = document.getElementById('page-size');
  if (!sel) return limit;
  if (sel.value === 'all') {
    return Math.max(totalRows || 5000, 5000);
  }
  return parseInt(sel.value, 10) || 500;
}

function updatePagerLabel() {
  const pageSize = currentLimit();
  const effective = Math.min(pageSize, Math.max(totalRows, 1));
  const page = Math.floor(offset / Math.max(pageSize, 1)) + 1;
  const pages = Math.max(1, Math.ceil(totalRows / Math.max(pageSize, 1)));
  const from = totalRows === 0 ? 0 : offset + 1;
  const to = Math.min(offset + pageSize, totalRows);
  document.getElementById('page-label').textContent =
    `Showing ${from}-${to} of ${totalRows}  |  Page ${page} of ${pages}`;
  const prev = document.getElementById('prev-page');
  const next = document.getElementById('next-page');
  if (prev) prev.disabled = offset <= 0;
  if (next) next.disabled = offset + pageSize >= totalRows;
}

const editableCdr = [
  'phone','other','starttime','duration','incoming','imeinumber','imsinumber',
  'celltowerid','otherinfo','first_cellid','last_cellid','roaming_nw','call_type',
  'calling_no','called_no'
];

function post(action, extra = {}) {
  const fd = new FormData();
  fd.append('ajax_action', action);
  fd.append('log_id', logId);
  Object.entries(extra).forEach(([k,v]) => fd.append(k, v));
  return fetch(<?= json_encode(function_exists('cdat_href') ? cdat_href('/data-upload/verify') : '/data-upload/verify') ?>, { method:'POST', body: fd }).then(r => r.json());
}

function renderTabs() {
  if (moduleName !== 'sdr') return;
  const tabs = document.getElementById('sdr-tabs');
  tabs.style.display = 'block';
  tabs.innerHTML = '';
  Object.keys(stagingTables).forEach(key => {
    const btn = document.createElement('button');
    btn.textContent = key;
    btn.className = key === currentTableKey ? 'active' : 'inactive';
    btn.onclick = () => { currentTableKey = key; currentTable = stagingTables[key]; offset = 0; loadRows(); renderTabs(); };
    tabs.appendChild(btn);
  });
}

function renderTable(rows) {
  const head = document.getElementById('verify-head');
  const body = document.getElementById('verify-body');
  head.innerHTML = '';
  body.innerHTML = '';
  if (!rows.length) {
    body.innerHTML = '<tr><td colspan="20">No staged rows found.</td></tr>';
    return;
  }
  const skip = new Set(['staging_row_id','is_duplicate','duplicate_reason','user_edited','import_job_id','operator','source_file','source_row_number']);
  const cols = Object.keys(rows[0]).filter(c => !skip.has(c));
  const trh = document.createElement('tr');
  ['#', ...cols, 'Duplicate'].forEach(c => {
    const th = document.createElement('th');
    th.textContent = String(c).replace(/_/g, ' ');
    trh.appendChild(th);
  });
  head.appendChild(trh);

  rows.forEach(row => {
    const tr = document.createElement('tr');
    if (row.is_duplicate === true || row.is_duplicate === 't' || row.is_duplicate === 1 || row.is_duplicate === '1') {
      tr.classList.add('duplicate');
    }
    const tdId = document.createElement('td');
    tdId.textContent = row.staging_row_id;
    tr.appendChild(tdId);
    cols.forEach(col => {
      const td = document.createElement('td');
      td.textContent = row[col] ?? '';
      if (moduleName === 'cdr' && editableCdr.includes(col)) {
        td.contentEditable = 'true';
        td.dataset.col = col;
        td.dataset.rowId = row.staging_row_id;
        td.addEventListener('blur', onEditCell);
      } else if (moduleName === 'sdr' && !['ucid','tower_key','provider_key','state_key','asondate'].includes(col)) {
        td.contentEditable = 'true';
        td.dataset.col = col;
        td.dataset.rowId = row.staging_row_id;
        td.addEventListener('blur', onEditCell);
      }
      tr.appendChild(td);
    });
    const tdDup = document.createElement('td');
    tdDup.textContent = row.is_duplicate ? (row.duplicate_reason || 'duplicate') : '';
    tr.appendChild(tdDup);
    body.appendChild(tr);
  });
}

function onEditCell(e) {
  const td = e.target;
  const rowId = td.dataset.rowId;
  const fields = {};
  fields[td.dataset.col] = td.textContent.trim();
  post('update_row', {
    staging_table: currentTable,
    table_key: currentTableKey,
    staging_row_id: rowId,
    fields: JSON.stringify(fields),
  }).then(data => {
    if (!data.ok) {
      document.getElementById('message').textContent = data.error || 'Could not save edit.';
      return;
    }
    if (data.counts) {
      renderStats(data.counts);
    }
    updatePagerLabel();
    loadRows();
  }).catch(err => {
    document.getElementById('message').textContent = 'Save failed: ' + (err.message || err);
  });
}

function loadRows(refreshEnrich) {
  limit = currentLimit();
  document.getElementById('message').textContent = refreshEnrich ? 'Refreshing tower enrichment and duplicates…' : '';
  post('fetch_rows', { table_key: currentTableKey, limit, offset, refresh_enrich: refreshEnrich ? '1' : '' }).then(data => {
    if (!data.ok) {
      const msg = document.getElementById('message');
      msg.textContent = data.error || 'Failed to load rows.';
      if (data.gone) {
        // Nothing on this screen can act on a batch whose staging rows are
        // gone, so show the explanation instead of empty controls -- including
        // the "you may edit values before approving" instructions.
        document.querySelector('.verify-hint')?.remove();
        msg.className = 'alert ' + (data.status === 'approved' ? 'alert-ok' : 'alert-warn');
        ['stats', 'sdr-tabs'].forEach(id => document.getElementById(id)?.remove());
        document.querySelector('.table-scroll')?.remove();
        document.querySelector('.verify-footer .pager')?.remove();
        document.getElementById('refresh-btn')?.remove();
        const tb = document.querySelector('.toolbar');
        if (tb) { tb.parentNode.insertBefore(msg, tb); }
      }
      return;
    }
    currentTable = data.table;
    totalRows = data.total || 0;
    // If "All" was chosen before total was known, reload once with full size.
    const sel = document.getElementById('page-size');
    if (sel && sel.value === 'all' && limit < totalRows && totalRows <= 5000) {
      limit = totalRows;
      offset = 0;
      return loadRows(refreshEnrich);
    }
    renderStats(data.counts);
    updatePagerLabel();
    renderTable(data.rows || []);
    document.getElementById('message').textContent = '';
  }).catch(err => {
    document.getElementById('message').textContent = 'Failed to load rows: ' + (err.message || err);
  });
}

document.getElementById('page-size')?.addEventListener('change', () => {
  offset = 0;
  limit = currentLimit();
  loadRows();
});
document.getElementById('refresh-btn')?.addEventListener('click', () => loadRows(true));
document.getElementById('prev-page')?.addEventListener('click', () => {
  limit = currentLimit();
  if (offset >= limit) { offset -= limit; loadRows(); }
});
document.getElementById('next-page')?.addEventListener('click', () => {
  limit = currentLimit();
  if (offset + limit < totalRows) { offset += limit; loadRows(); }
});
document.getElementById('approve-btn')?.addEventListener('click', () => {
  if (!confirm('Approve and load non-duplicate rows into production?')) return;
  const approveBtn = document.getElementById('approve-btn');
  const msg = document.getElementById('message');
  if (approveBtn) approveBtn.disabled = true;
  post('approve').then(data => {
    if (!data.ok) {
      msg.innerHTML = `<div class="alert alert-warn">${data.error}</div>`;
      if (approveBtn) approveBtn.disabled = false;
      return;
    }
    if (data.queued) {
      msg.innerHTML = `<div class="alert alert-warn">${data.message} Queue position: ${data.position}.</div>`;
      pollApprovalQueue(data.queue_id);
      return;
    }
    msg.innerHTML = `<div class="alert alert-ok">${data.message}</div>`;
    setTimeout(() => leaveStaging(true), 1200);
  });
});

function pollApprovalQueue(queueId) {
  const msg = document.getElementById('message');
  const approveBtn = document.getElementById('approve-btn');
  const timer = setInterval(() => {
    post('poll_approve', { queue_id: queueId }).then(data => {
      if (!data.ok) {
        clearInterval(timer);
        msg.innerHTML = `<div class="alert alert-warn">${data.error}</div>`;
        if (approveBtn) approveBtn.disabled = false;
        return;
      }
      if (data.queued) {
        const pos = data.position ? ` Queue position: ${data.position}.` : '';
        msg.innerHTML = `<div class="alert alert-warn">${data.message || 'Waiting for another promotion to finish.'}${pos}</div>`;
        return;
      }
      clearInterval(timer);
      msg.innerHTML = `<div class="alert alert-ok">${data.message}</div>`;
      setTimeout(() => leaveStaging(true), 1200);
    });
  }, 3000);
}
document.getElementById('reject-btn')?.addEventListener('click', () => {
  if (!confirm('Reject this upload and delete staging data?')) return;
  post('reject').then(data => {
    document.getElementById('message').innerHTML = data.ok
      ? `<div class="alert alert-ok">${data.message}</div>`
      : `<div class="alert alert-warn">${data.error}</div>`;
    if (data.ok) setTimeout(() => leaveStaging(true), 1200);
  });
});

if (logId > 0 && <?= $batch ? 'true' : 'false' ?>) { renderTabs(); loadRows(); }
</script>
<?php
if ($embed) {
    echo '</div></body></html>';
} else {
    layout_end();
}
?>
