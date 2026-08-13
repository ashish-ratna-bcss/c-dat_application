<?php

require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/upload_verification_service.php';
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
?>
<?php
// The stylesheet below belongs in <head>; capture it and pass it to
// layout_begin() so it can stay written as plain CSS in this file.
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
ob_start();
?>
<style type="text/css">
.FONT {
	color: #CFF;
	font-size: 24px;
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif;
}
.verify-wrapper {
    width: 1200px;
    margin: 10px auto;
    padding: 20px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    font-family: Verdana, Geneva, sans-serif;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    text-align: left;
}
.verify-wrapper h2 {
    margin-top: 0;
    color: #FFA500;
    font-size: 20px;
}
.meta {
    background: rgba(0,0,0,.25);
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 13px;
}
.toolbar {
    display: flex;
    gap: 10px;
    margin: 15px 0;
    flex-wrap: wrap;
    align-items: center;
}
.btn {
    border: none;
    border-radius: 4px;
    padding: 8px 14px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 12px;
    font-family: Verdana, Geneva, sans-serif;
}
.btn-approve { background: #28a745; color: #fff; }
.btn-reject { background: #dc3545; color: #fff; }
.btn-back { background: rgba(255,255,255,.2); color: #fff; border: 1px solid rgba(255,255,255,.3); }
.btn-refresh { background: #17a2b8; color: #fff; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.stats { font-size: 12px; color: #9fd0e6; margin-bottom: 10px; }
.table-scroll {
    max-height: 520px;
    overflow: auto;
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 4px;
    background: rgba(0,0,0,.25);
}
#verify-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    color: #fff;
}
#verify-table th, #verify-table td {
    border-bottom: 1px solid rgba(255,255,255,.15);
    padding: 6px;
    text-align: left;
    white-space: nowrap;
}
#verify-table th {
    background: rgba(0,0,0,.45);
    color: #87CEEB;
    position: sticky;
    top: 0;
    z-index: 1;
}
#verify-table tr.duplicate td {
    background: rgba(220,53,69,.35);
    color: #ffd6d6;
}
#verify-table td[contenteditable="true"] {
    background: rgba(255,255,255,.08);
    outline: none;
}
#verify-table td[contenteditable="true"]:focus {
    background: rgba(255,165,0,.25);
}
.pager { margin-top: 12px; display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:space-between; }
.pager-left, .pager-right { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.pager select {
    padding: 6px 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 12px;
}
#page-label { color: #9fd0e6; font-size: 12px; font-weight: bold; }
.alert { padding: 10px; border-radius: 4px; margin-bottom: 10px; font-size: 12px; }
.alert-warn { background: rgba(255,193,7,.2); border: 1px solid #ffc107; color: #ffe8a3; }
.alert-ok { background: rgba(40,167,69,.2); border: 1px solid #28a745; color: #b7f0c4; }
.tabs button {
    margin-right: 8px;
    margin-bottom: 8px;
    border: none;
    border-radius: 4px;
    padding: 6px 12px;
    cursor: pointer;
    font-weight: bold;
}
.tabs button.active { background: #FFA500; color: #10222b; }
.tabs button.inactive { background: rgba(255,255,255,.15); color: #fff; }
#message { margin-top: 10px; min-height: 20px; }
</style>
<link rel="stylesheet" href="../assets/css/upload.css">
<?php
layout_begin('Staging Preview & Edit', 'Review rows before they load into production', ob_get_clean());
cdat_sum_page_open();
cdat_sum_hub_card(
    'Staging Preview & Edit',
    'Review rows before they load into production.',
    '',
    'Please mail raw data to <strong>cdranalysiswing@gmail.com</strong> to view reports.'
);
?>
              <div class="verify-wrapper">
  <?php if (!$batch): ?>
    <div class="alert alert-warn">Upload record not found or has no staging data.</div>
    <a class="btn btn-back" href="admin_upload_history.php?type=standard">Back to History</a>
  <?php else: ?>
    <div class="meta">
      <strong>File:</strong> <?= htmlspecialchars($batch['file_name'] ?? '') ?> |
      <strong>Module:</strong> <?= htmlspecialchars($batch['module_name'] ?? $batch['module'] ?? '') ?> |
      <strong>Uploaded By:</strong> <?= htmlspecialchars($batch['username'] ?? '') ?> |
      <strong>Status:</strong> <?= htmlspecialchars($batch['verification_status'] ?? 'pending') ?>
    </div>
    <div class="alert alert-warn">
      Rows highlighted in <span style="color:#ff6b6b;font-weight:bold;">red</span> are duplicates already present in production and will <strong>not</strong> be loaded on approval.
      Duplicates against production are checked automatically when this page opens and again before approve.
      You may edit values before approving; edits are saved automatically when you leave a cell.
    </div>
    <div id="sdr-tabs" class="tabs" style="display:none;"></div>
    <div class="stats" id="stats"></div>
    <div class="table-scroll">
      <table id="verify-table">
        <thead id="verify-head"></thead>
        <tbody id="verify-body"></tbody>
      </table>
    </div>
    <div class="pager">
      <div class="pager-left">
        <label for="page-size" style="font-size:12px;color:#FFD700;font-weight:bold;">Rows per page</label>
        <select id="page-size">
          <option value="100">100</option>
          <option value="250">250</option>
          <option value="500" selected="selected">500</option>
          <option value="1000">1000</option>
          <option value="all">All</option>
        </select>
        <span id="page-label"></span>
      </div>
      <div class="pager-right">
        <button class="btn btn-refresh" id="prev-page" type="button">Prev</button>
        <button class="btn btn-refresh" id="next-page" type="button">Next</button>
      </div>
    </div>
    <div class="toolbar">
      <button class="btn btn-refresh" id="refresh-btn" type="button">Refresh Enrichment &amp; Duplicates</button>
      <?php if ($canVerify): ?>
        <button class="btn btn-approve" id="approve-btn" type="button">Approve &amp; Load to Production</button>
        <button class="btn btn-reject" id="reject-btn" type="button">Reject Upload</button>
      <?php endif; ?>
      <a class="btn btn-back" href="admin_upload_history.php?type=standard">Back to History</a>
      <a class="btn btn-back" href="admin_upload.php">Back to Upload</a>
    </div>
    <div id="message"></div>
  <?php endif; ?>
              </div>
<?php cdat_sum_page_close(); ?>

<script>
const logId = <?= (int)$logId ?>;
const moduleName = <?= json_encode(strtolower($batch['module'] ?? 'cdr')) ?>;
const stagingTables = <?= json_encode($batch['staging_tables'] ?? []) ?>;
let currentTableKey = moduleName === 'cdr' ? 'cdr' : Object.keys(stagingTables)[0];
let currentTable = stagingTables[currentTableKey] || '';
let offset = 0;
let limit = 500;
let totalRows = 0;

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
  return fetch('admin_upload_verify.php', { method:'POST', body: fd }).then(r => r.json());
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
  ['#', ...cols, 'Duplicate'].forEach(c => { const th = document.createElement('th'); th.textContent = c; trh.appendChild(th); });
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
      document.getElementById('stats').textContent =
        `Valid: ${data.counts.valid_count ?? 0}, Duplicates: ${data.counts.duplicate_count ?? 0}`;
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
        document.querySelector('.verify-wrapper > .alert-warn')?.remove();
        msg.className = 'alert ' + (data.status === 'approved' ? 'alert-ok' : 'alert-warn');
        ['stats', 'sdr-tabs'].forEach(id => document.getElementById(id)?.remove());
        document.querySelector('.table-scroll')?.remove();
        document.querySelector('.pager')?.remove();
        document.getElementById('refresh-btn')?.remove();
        // put the explanation above the Back links rather than below them
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
    document.getElementById('stats').textContent =
      `Valid: ${data.counts?.valid_count ?? 0}, Duplicates: ${data.counts?.duplicate_count ?? 0}`;
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
    setTimeout(() => window.location.href = 'admin_upload_history.php?type=standard', 1500);
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
      setTimeout(() => window.location.href = 'admin_upload_history.php?type=standard', 1500);
    });
  }, 3000);
}
document.getElementById('reject-btn')?.addEventListener('click', () => {
  if (!confirm('Reject this upload and delete staging data?')) return;
  post('reject').then(data => {
    document.getElementById('message').innerHTML = data.ok
      ? `<div class="alert alert-ok">${data.message}</div>`
      : `<div class="alert alert-warn">${data.error}</div>`;
    if (data.ok) setTimeout(() => window.location.href = 'admin_upload_history.php?type=standard', 1500);
  });
});

if (logId > 0 && <?= $batch ? 'true' : 'false' ?>) { renderTabs(); loadRows(); }
</script>
<?php layout_end(); ?>
