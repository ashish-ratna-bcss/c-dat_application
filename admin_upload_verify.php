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
            $limit = min(200, max(10, (int)($_POST['limit'] ?? 100)));
            $offset = max(0, (int)($_POST['offset'] ?? 0));
            $forceRefresh = !empty($_POST['refresh_enrich']);
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
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Upload Verification - CDR Dashboard</title>
<style>
body { background:
.wrap { width:1200px; margin:20px auto; background:rgba(0,0,0,.25); border-radius:8px; padding:20px; }
h2 { margin-top:0; color:
.meta { background:rgba(0,0,0,.2); padding:12px; border-radius:6px; margin-bottom:15px; font-size:13px; }
.toolbar { display:flex; gap:10px; margin:15px 0; flex-wrap:wrap; }
.btn { border:none; border-radius:4px; padding:8px 14px; font-weight:bold; cursor:pointer; }
.btn-approve { background:
.btn-reject { background:
.btn-back { background:rgba(255,255,255,.2); color:
.btn-refresh { background:
.stats { font-size:12px; color:
table { width:100%; border-collapse:collapse; font-size:11px; }
th, td { border-bottom:1px solid rgba(255,255,255,.15); padding:6px; text-align:left; }
th { background:rgba(0,0,0,.35); color:
tr.duplicate td { background:rgba(220,53,69,.35); color:
td[contenteditable="true"] { background:rgba(255,255,255,.08); outline:none; }
td[contenteditable="true"]:focus { background:rgba(255,165,0,.25); }
.pager { margin-top:12px; text-align:right; }
.pager button { margin-left:6px; }
.alert { padding:10px; border-radius:4px; margin-bottom:10px; }
.alert-warn { background:rgba(255,193,7,.2); border:1px solid 
.alert-ok { background:rgba(40,167,69,.2); border:1px solid 
.tabs button { margin-right:8px; margin-bottom:8px; }
.tabs button.active { background:
.tabs button.inactive { background:rgba(255,255,255,.15); color:
</style>
</head>
<body>
<div class="wrap">
  <h2>Upload Verification</h2>
  <?php if (!$batch): ?>
    <div class="alert alert-warn">Upload record not found or has no staging data.</div>
    <a class="btn btn-back" href="admin_upload_history.php">Back to History</a>
  <?php else: ?>
    <div class="meta">
      <strong>File:</strong> <?= htmlspecialchars($batch['file_name'] ?? '') ?> |
      <strong>Module:</strong> <?= htmlspecialchars($batch['module_name'] ?? $batch['module'] ?? '') ?> |
      <strong>Uploaded By:</strong> <?= htmlspecialchars($batch['username'] ?? '') ?> |
      <strong>Status:</strong> <?= htmlspecialchars($batch['verification_status'] ?? 'pending') ?>
    </div>
    <div class="alert alert-warn">
      Rows highlighted in <span style="color:#ff6b6b;font-weight:bold;">red</span> are duplicates (already in production or repeated in this file) and will <strong>not</strong> be loaded on approval.
      Duplicates against production are checked automatically when this page opens and again before approve.
      You may edit values before approving; edits are saved automatically when you leave a cell.
    </div>
    <div id="sdr-tabs" class="tabs" style="display:none;"></div>
    <div class="stats" id="stats"></div>
    <div style="max-height:520px; overflow:auto;">
      <table id="verify-table">
        <thead id="verify-head"></thead>
        <tbody id="verify-body"></tbody>
      </table>
    </div>
    <div class="pager">
      <button class="btn btn-refresh" id="prev-page" type="button">Prev</button>
      <span id="page-label"></span>
      <button class="btn btn-refresh" id="next-page" type="button">Next</button>
    </div>
    <div class="toolbar">
      <button class="btn btn-refresh" id="refresh-btn" type="button">Refresh Enrichment &amp; Duplicates</button>
      <?php if ($canVerify): ?>
        <button class="btn btn-approve" id="approve-btn" type="button">Approve &amp; Load to Production</button>
        <button class="btn btn-reject" id="reject-btn" type="button">Reject Upload</button>
      <?php endif; ?>
      <a class="btn btn-back" href="admin_upload_history.php">Back to History</a>
    </div>
    <div id="message"></div>
  <?php endif; ?>
</div>
<script>
const logId = <?= (int)$logId ?>;
const moduleName = <?= json_encode(strtolower($batch['module'] ?? 'cdr')) ?>;
const stagingTables = <?= json_encode($batch['staging_tables'] ?? []) ?>;
let currentTableKey = moduleName === 'cdr' ? 'cdr' : Object.keys(stagingTables)[0];
let currentTable = stagingTables[currentTableKey] || '';
let offset = 0;
const limit = 100;
let totalRows = 0;

const editableCdr = ['phone','other','starttime','duration','incoming','imeinumber','celltowerid','first_cellid','last_cellid'];

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
        `Showing ${offset + 1}-${Math.min(offset + limit, totalRows)} of ${totalRows}. ` +
        `Valid: ${data.counts.valid_count ?? 0}, Duplicates: ${data.counts.duplicate_count ?? 0}`;
    }
    loadRows();
  }).catch(err => {
    document.getElementById('message').textContent = 'Save failed: ' + (err.message || err);
  });
}

function loadRows(refreshEnrich) {
  document.getElementById('message').textContent = refreshEnrich ? 'Refreshing tower enrichment and duplicates…' : '';
  post('fetch_rows', { table_key: currentTableKey, limit, offset, refresh_enrich: refreshEnrich ? '1' : '' }).then(data => {
    if (!data.ok) { document.getElementById('message').textContent = data.error || 'Failed to load rows.'; return; }
    currentTable = data.table;
    totalRows = data.total || 0;
    document.getElementById('stats').textContent =
      `Showing ${offset + 1}-${Math.min(offset + limit, totalRows)} of ${totalRows}. ` +
      `Valid: ${data.counts?.valid_count ?? 0}, Duplicates: ${data.counts?.duplicate_count ?? 0}`;
    document.getElementById('page-label').textContent = `Page ${Math.floor(offset/limit)+1}`;
    renderTable(data.rows || []);
    document.getElementById('message').textContent = '';
  }).catch(err => {
    document.getElementById('message').textContent = 'Failed to load rows: ' + (err.message || err);
  });
}

document.getElementById('refresh-btn')?.addEventListener('click', () => loadRows(true));
document.getElementById('prev-page')?.addEventListener('click', () => { if (offset >= limit) { offset -= limit; loadRows(); }});
document.getElementById('next-page')?.addEventListener('click', () => { if (offset + limit < totalRows) { offset += limit; loadRows(); }});
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
    setTimeout(() => window.location.href = 'admin_upload_history.php', 1500);
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
      setTimeout(() => window.location.href = 'admin_upload_history.php', 1500);
    });
  }, 3000);
}
document.getElementById('reject-btn')?.addEventListener('click', () => {
  if (!confirm('Reject this upload and delete staging data?')) return;
  post('reject').then(data => {
    document.getElementById('message').innerHTML = data.ok
      ? `<div class="alert alert-ok">${data.message}</div>`
      : `<div class="alert alert-warn">${data.error}</div>`;
    if (data.ok) setTimeout(() => window.location.href = 'admin_upload_history.php', 1500);
  });
});

if (logId > 0 && <?= $batch ? 'true' : 'false' ?>) { renderTabs(); loadRows(); }
</script>
</body>
</html>
