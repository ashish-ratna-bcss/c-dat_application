<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/activity_logger.php';
require_once CDAT_COMMON . '/csrf.php';
audit_require_uploader();

function cdat_data_upload_url(): string
{
    $host = '127.0.0.1';
    $port = '8090';
    $url = '';
    $envFile = CDAT_ROOT . '/.env';
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim((string) $line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");
            if ($key === 'DATA_UPLOAD_URL' && $value !== '') {
                $url = $value;
            } elseif ($key === 'DATA_UPLOAD_HOST' && $value !== '') {
                $host = $value;
            } elseif ($key === 'DATA_UPLOAD_PORT' && $value !== '') {
                $port = $value;
            }
        }
    }
    if ($url === '/' || strcasecmp($url, 'same') === 0) {
        return '';
    }
    if ($url !== '') {
        return rtrim($url, '/');
    }
    return 'http://' . $host . ':' . $port;
}

$previewApi = cdat_data_upload_url() . '/api/v1/cdr/preview';
$stageApi = cdat_data_upload_url() . '/api/v1/cdr/stage';
$cdrUsername = (string) ($_SESSION['audit_username'] ?? 'user');
$cdrClientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
$historyUrl = cdat_href('/data-upload/history');
ob_start();
?>
<link rel="stylesheet" href="<?= htmlspecialchars(CDAT_ASSETS) ?>/css/upload.css">
<?php
layout_begin('CDR (Call Data Record) Upload', 'Choose a Call Data Record CSV to preview. The operator is detected from the file.', ob_get_clean());
cdat_sum_page_open();
?>
<div class="upload-wrapper upload-wrapper--cdr">
  <form id="cdr-preview-form" class="upload-form upload-panel" action="<?= htmlspecialchars($previewApi, ENT_QUOTES) ?>" method="post" enctype="multipart/form-data">
    <div class="upload-layout row g-3 upload-layout--file-only">
      <div class="form-group upload-field-file col-12">
        <div class="cdr-file-head">
          <label class="form-label" for="cdr_file">CDR (Call Data Record) file</label>
          <div class="cdr-toolbar">
            <button type="button" id="cdr-remove-btn" class="btn btn-outline-danger btn-sm" hidden>Remove</button>
            <button type="button" id="cdr-stage-btn" class="btn btn-primary btn-sm" hidden>Staging</button>
            <a href="<?= htmlspecialchars($historyUrl, ENT_QUOTES) ?>" id="cdr-history-btn" class="btn btn-outline-secondary btn-sm">History</a>
          </div>
        </div>
        <input type="file" name="file" id="cdr_file" accept=".csv,text/csv"
          data-upload-hint="CSV — drag &amp; drop, or browse" />
      </div>
    </div>
    <p id="cdr-preview-status" class="preview-card__summary mt-3 mb-0" hidden></p>
    <p id="cdr-stage-status" class="cdr-preview__note mt-2 mb-0" hidden></p>
  </form>

  <div id="standard-preview-container" class="cdr-preview" hidden>
    <section class="cdr-subject" id="cdr-preview-subject"></section>
    <div class="cdr-stats" id="cdr-preview-stats"></div>
    <p class="cdr-preview__note" id="cdr-preview-note"></p>
    <div class="cdr-table-card">
      <div class="preview-table-wrapper">
        <table class="preview-table cdr-table" id="cdr-preview-table">
          <thead></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  var form = document.getElementById('cdr-preview-form');
  var input = document.getElementById('cdr_file');
  var statusEl = document.getElementById('cdr-preview-status');
  var container = document.getElementById('standard-preview-container');
  var subjectEl = document.getElementById('cdr-preview-subject');
  var statsEl = document.getElementById('cdr-preview-stats');
  var noteEl = document.getElementById('cdr-preview-note');
  var table = document.getElementById('cdr-preview-table');
  var removeBtn = document.getElementById('cdr-remove-btn');
  var stageBtn = document.getElementById('cdr-stage-btn');
  var stageStatusEl = document.getElementById('cdr-stage-status');
  var endpoint = form.getAttribute('action');
  var stageEndpoint = <?= json_encode($stageApi, JSON_UNESCAPED_SLASHES) ?>;
  var username = <?= json_encode($cdrUsername) ?>;
  var clientIp = <?= json_encode($cdrClientIp) ?>;
  var historyUrl = <?= json_encode($historyUrl, JSON_UNESCAPED_SLASHES) ?>;
  var lastFile = null;
  var stagingBusy = false;
  var statusLabels = {
    new: 'New',
    duplicate: 'Duplicate',
    in_staging: 'Already in staging',
    in_db: 'Already in DB'
  };

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formatCount(value) {
    var n = Number(value || 0);
    return Number.isFinite(n) ? n.toLocaleString('en-IN') : '0';
  }

  function setFileActions(hasFile, canStage) {
    removeBtn.hidden = !hasFile;
    removeBtn.disabled = !hasFile;
    stageBtn.hidden = !hasFile;
    stageBtn.disabled = !canStage;
  }

  function setStatus(message, isError) {
    statusEl.hidden = !message;
    statusEl.textContent = message || '';
    statusEl.style.color = isError ? '#b42318' : '';
  }

  function setStageStatus(message, isError) {
    stageStatusEl.hidden = !message;
    stageStatusEl.textContent = message || '';
    stageStatusEl.style.color = isError ? '#b42318' : '#166534';
  }

  function kv(label, value, extraClass) {
    var text = (value == null || String(value).trim() === '') ? '—' : String(value);
    return '<div class="cdr-kv' + (extraClass ? ' ' + extraClass : '') + '">'
      + '<span>' + escapeHtml(label) + '</span>'
      + '<strong>' + escapeHtml(text) + '</strong>'
      + '</div>';
  }

  function cellText(value) {
    var text = String(value == null ? '' : value).trim();
    if (!text || text === '-' || text === '--' || text === '---') {
      return '<span class="cdr-empty">—</span>';
    }
    return escapeHtml(text);
  }

  function stat(kind, label, value) {
    return '<div class="cdr-stat cdr-stat--' + kind + '">'
      + '<span class="cdr-stat__label">' + escapeHtml(label) + '</span>'
      + '<span class="cdr-stat__value">' + escapeHtml(value) + '</span>'
      + '</div>';
  }

  function renderPreview(data) {
    var spec = data.schema_columns || [];
    var columns = spec.length
      ? spec.map(function (col) { return col.name; })
      : (data.columns || []);
    var rows = (spec.length && data.schema_rows) ? data.schema_rows : (data.rows || []);
    var flags = data.row_status || [];
    var thead = '<tr><th class="cdr-col-status">Status</th>' + columns.map(function (col) {
      return '<th>' + escapeHtml(col) + '</th>';
    }).join('') + '</tr>';
    var tbody = rows.map(function (row, index) {
      var kind = flags[index] || 'new';
      var label = statusLabels[kind] || 'New';
      return '<tr class="cdr-row--' + kind + '"><td class="cdr-col-status"><span class="cdr-status cdr-status--' + kind + '">' + escapeHtml(label) + '</span></td>'
        + columns.map(function (col) {
          return '<td>' + cellText(row[col]) + '</td>';
        }).join('') + '</tr>';
    }).join('');
    table.querySelector('thead').innerHTML = thead;
    table.querySelector('tbody').innerHTML = tbody || '<tr><td colspan="' + (columns.length + 1) + '">No call rows found.</td></tr>';

    var period = '';
    if (data.date_from && data.date_to) period = data.date_from + ' to ' + data.date_to;
    else if (data.date_from) period = data.date_from;
    subjectEl.innerHTML = [
      kv('Network', data.provider_label),
      kv('Target', data.target_phone),
      kv('Name', data.subscriber_name),
      kv('Nickname', data.nickname),
      kv('Connection', data.connection_type),
      kv('Category', data.category),
      kv('IMEI', data.imei),
      kv('Period', period),
      kv('Address', data.subscriber_address, 'cdr-kv--wide')
    ].join('');

    statsEl.innerHTML = [
      stat('total', 'Total records', formatCount(data.total_records)),
      stat('dup', 'Duplicate records', formatCount(data.in_file_duplicates)),
      stat('db', 'Already in DB', formatCount(data.already_in_db)),
      stat('staging', 'Already in staging', formatCount(data.already_in_staging)),
      stat('new', 'New records', formatCount(data.new_records))
    ].join('');

    var note = data.truncated
      ? ('Showing the first ' + formatCount(data.preview_count) + ' of ' + formatCount(data.total_records) + ' rows in cdatpcsuspect columns.')
      : (formatCount(data.total_records) + ' rows in cdatpcsuspect columns.');
    if (data.db_checked === false && data.db_message) {
      note += ' ' + data.db_message;
    }
    noteEl.textContent = note;
    container.hidden = false;
    setFileActions(true, Number(data.new_records || 0) > 0);
    setStageStatus('', false);
  }

  function resetPreview() {
    lastFile = null;
    container.hidden = true;
    setFileActions(false, false);
    setStatus('', false);
    setStageStatus('', false);
  }

  async function previewFile(file) {
    if (!file) {
      resetPreview();
      return;
    }
    var name = (file.name || '').toLowerCase();
    if (!name.endsWith('.csv')) {
      setStatus('Please choose a .csv file.', true);
      container.hidden = true;
      setFileActions(true, false);
      return;
    }
    lastFile = file;
    setFileActions(true, false);
    setStageStatus('', false);
    setStatus('Reading CSV and comparing with staging and the database…', false);
    container.hidden = true;
    var body = new FormData();
    body.append('file', file, file.name);
    try {
      var response = await fetch(endpoint, { method: 'POST', body: body });
      var payload = await response.json().catch(function () { return {}; });
      if (!response.ok || payload.ok === false) {
        var detail = payload.detail || payload.error || ('Preview failed (' + response.status + ').');
        if (Array.isArray(detail)) {
          detail = detail.map(function (item) { return item.msg || JSON.stringify(item); }).join(' ');
        }
        throw new Error(detail);
      }
      renderPreview(payload);
      setStatus('', false);
    } catch (err) {
      setStatus(err.message || 'Could not reach the data upload API on port 8090.', true);
      container.hidden = true;
      setFileActions(true, false);
    }
  }

  async function stageFile() {
    if (!lastFile || stagingBusy) return;
    stagingBusy = true;
    stageBtn.disabled = true;
    setStageStatus('Queuing background staging…', false);
    var body = new FormData();
    body.append('file', lastFile, lastFile.name);
    body.append('username', username);
    body.append('ip_address', clientIp);
    try {
      var response = await fetch(stageEndpoint, { method: 'POST', body: body });
      var payload = await response.json().catch(function () { return {}; });
      if (!response.ok || payload.ok === false) {
        var detail = payload.detail || payload.error || ('Staging failed (' + response.status + ').');
        if (Array.isArray(detail)) {
          detail = detail.map(function (item) { return item.msg || JSON.stringify(item); }).join(' ');
        }
        throw new Error(detail);
      }
      setStageStatus('Queued. Opening history…', false);
      window.location.href = historyUrl;
    } catch (err) {
      setStageStatus(err.message || 'Could not queue the staging job.', true);
      stageBtn.disabled = false;
    } finally {
      stagingBusy = false;
    }
  }

  input.addEventListener('change', function () {
    if (input.files && input.files[0]) previewFile(input.files[0]);
    else resetPreview();
  });
  removeBtn.addEventListener('click', function () {
    var nativeClear = form.querySelector('.sum-fu__clear');
    if (nativeClear) {
      nativeClear.click();
      return;
    }
    input.value = '';
    try {
      input.files = new DataTransfer().files;
    } catch (err) {}
    resetPreview();
  });
  stageBtn.addEventListener('click', stageFile);
})();
</script>
<?php
cdat_sum_page_close();
layout_end();
