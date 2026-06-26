<?php
/**
 * admin_upload.php
 * Administrative panel for CDR/SDR document uploads via the Document Processing Service.
 */
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/cdr_upload_parser.php';
audit_require_admin();

$config = require __DIR__ . '/cdr_upload_config.php';
$modules = $config;
unset($modules['api']);

$step = 1;
$error = '';
$success = '';

$selectedModule = '';
$fileName = '';
$fileSize = 0;
$results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_file') {
        $selectedModule = $_POST['module'] ?? '';
        if (!isset($modules[$selectedModule])) {
            $error = 'Please select a valid module (CDR or SDR).';
        } elseif (!isset($_FILES['cdr_file']) || $_FILES['cdr_file']['error'] !== UPLOAD_ERR_OK) {
            $uploadErr = $_FILES['cdr_file']['error'] ?? UPLOAD_ERR_NO_FILE;
            $errMap = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was selected.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
            ];
            $error = $errMap[$uploadErr] ?? 'Please select a valid file to upload.';
        } else {
            $moduleConfig = $modules[$selectedModule];
            $file = $_FILES['cdr_file'];
            $fileName = basename($file['name']);
            $fileSize = $file['size'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExt = $moduleConfig['allowed_extensions'];
            $maxSize = (int)$moduleConfig['max_file_size'];

            if (!in_array($fileExt, $allowedExt, true)) {
                $error = 'Unsupported file format for ' . $moduleConfig['name']
                    . '. Allowed: ' . implode(', ', $allowedExt) . '.';
            } elseif ($fileSize > $maxSize) {
                $error = 'File size exceeds the ' . round($maxSize / (1024 * 1024)) . 'MB limit.';
            } else {
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true)) {
                    $error = 'Upload directory is missing and could not be created. Contact administrator.';
                } elseif (!is_writable($uploadDir)) {
                    $error = 'Upload directory is not writable by the web server.';
                } else {
                $destFile = $uploadDir . '/' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                if (move_uploaded_file($file['tmp_name'], $destFile)) {
                    set_time_limit(0);

                    $parser = new CdrUploadParser();
                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $results = $parser->processUpload(
                        $selectedModule,
                        $destFile,
                        $fileName,
                        $fileSize,
                        $fileExt,
                        [],
                        $ipAddress
                    );

                    if ($results['status'] === 'Failed') {
                        $error = 'Processing failed: ' . ($results['reason'] ?? 'Unknown error');
                        @unlink($destFile);
                    } else {
                        if ($results['status'] === 'Success') {
                            $success = 'Document processing completed successfully.';
                        } elseif ($results['status'] === 'Processing') {
                            $success = 'Document submitted. Job is still running in the background.';
                        } else {
                            $success = 'Upload logged with status: ' . $results['status'];
                        }
                        $step = 2;
                    }
                } else {
                    $uploadErr = $file['error'] ?? UPLOAD_ERR_OK;
                    $errMap = [
                        UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit (check PHP upload_max_filesize).',
                        UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder.',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                        UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
                    ];
                    $error = 'Failed to save the uploaded file on the server.'
                        . (isset($errMap[$uploadErr]) ? ' ' . $errMap[$uploadErr] : '');
                }
                }
            }
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Common Data Upload Framework - CDR Dashboard</title>
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
.upload-wrapper {
    width: 850px;
    margin: 10px auto;
    padding: 25px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    font-family: Verdana, Geneva, sans-serif;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}
.step-header {
    display: flex;
    justify-content: space-around;
    margin-bottom: 25px;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.2);
    padding-bottom: 12px;
}
.step-pill {
    padding: 6px 12px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 4px;
    font-size: 13px;
    font-weight: bold;
    color: #bbb;
    flex: 1;
    text-align: center;
    max-width: 250px;
}
.step-pill.active {
    background: #FFA500;
    color: white;
}
.form-group {
    margin-bottom: 20px;
    text-align: left;
}
.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
    font-size: 13px;
}
.form-group select, .form-group input[type="file"] {
    width: 100%;
    padding: 8px 10px;
    font-size: 13px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    color: #333;
    background: #fff;
}
.module-hint {
    margin-top: 8px;
    font-size: 12px;
    color: #FFD700;
    line-height: 1.5;
}
.staging-banner {
    margin-bottom: 18px;
    padding: 10px 12px;
    background: rgba(255, 215, 0, 0.15);
    border: 1px solid rgba(255, 215, 0, 0.4);
    border-radius: 4px;
    font-size: 12px;
    text-align: left;
    color: #FFD700;
}
.staging-preview-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    margin-top: 12px;
}
.staging-preview-table th, .staging-preview-table td {
    padding: 6px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-align: left;
}
.staging-preview-table th {
    background: rgba(0, 0, 0, 0.25);
}
.btn-action {
    background-color: #FFA500;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
    transition: background-color 0.2s;
}
.btn-action:hover {
    background-color: #e59400;
}
.btn-secondary {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 10px 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    cursor: pointer;
    font-size: 15px;
    margin-right: 10px;
}
.btn-secondary:hover {
    background-color: rgba(255, 255, 255, 0.3);
}
.msg-container {
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-size: 13px;
    font-weight: bold;
    text-align: left;
}
.msg-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.msg-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.badge {
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
}
.results-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}
.result-card {
    background: rgba(0, 0, 0, 0.25);
    padding: 15px;
    border-radius: 6px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.result-card .num {
    font-size: 24px;
    font-weight: bold;
    margin-top: 5px;
}
.color-total { color: #87CEEB; }
.color-success { color: #90EE90; }
.color-skipped { color: #FFD700; }
.color-failed { color: #FFB6C1; }
.progress-bar-wrap {
    margin: 15px 0;
    background: rgba(0,0,0,0.3);
    border-radius: 4px;
    height: 22px;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    background: #FFA500;
    width: 0%;
    transition: width 0.4s ease;
    text-align: center;
    font-size: 11px;
    line-height: 22px;
    color: #fff;
    font-weight: bold;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
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
              <p align="center" class="FONT"> COMMON DATA UPLOAD FRAMEWORK </p>
            </td>
          </tr>
          <tr>
            <td align="center" valign="top">
              
              <div class="upload-wrapper">
                <div class="step-header">
                  <div class="step-pill <?= ($step === 1) ? 'active' : '' ?>">1. Select Module & File</div>
                  <div class="step-pill <?= ($step === 2) ? 'active' : '' ?>">2. Results & Log</div>
                </div>

                <?php if ($error !== ''): ?>
                  <div class="msg-container msg-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success !== ''): ?>
                  <div class="msg-container msg-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                  <div class="staging-banner">
                    <strong>Production mode:</strong> CDR uploads are written to PostgreSQL table
                    <code style="color:#fff;">cdatpcsuspect</code>.
                  </div>
                  <form action="admin_upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_file" />
                    
                    <div class="form-group">
                      <label for="module">Select Module</label>
                      <select name="module" id="module" required="required" onchange="updateModuleHint(this.value)">
                        <option value="">-- Choose Module --</option>
                        <?php foreach ($modules as $key => $conf): ?>
                          <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($conf['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div id="module-hint" class="module-hint" style="display:none;"></div>
                    </div>

                    <div class="form-group">
                      <label for="cdr_file" id="file-label">Select Upload File</label>
                      <input type="file" name="cdr_file" id="cdr_file" accept=".csv,.xls,.xlsx,.bak" required="required" />
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                      <a href="admin_upload_history.php" class="btn-secondary" style="text-decoration: none; display: inline-block;">Upload History</a>
                      <input type="submit" class="btn-action" value="Upload & Process File" />
                    </div>
                  </form>
                <?php endif; ?>

                <?php if ($step === 2 && $results): ?>
                  <div style="text-align: left; margin-bottom: 20px;">
                    <h3>Upload Summary Details</h3>
                    <p style="font-size: 13px; line-height: 1.8;">
                      <strong>Uploaded by:</strong> <?= htmlspecialchars($_SESSION['audit_username'] ?? 'Admin') ?><br/>
                      <strong>Module:</strong> <?= htmlspecialchars($modules[$selectedModule]['name']) ?><br/>
                      <strong>File:</strong> <?= htmlspecialchars($fileName) ?><br/>
                      <?php if (!empty($results['target_table'])): ?>
                      <strong>Target table:</strong> <?= htmlspecialchars($results['target_table']) ?><br/>
                      <?php endif; ?>
                      <?php if (!empty($results['job_id'])): ?>
                      <strong>Job ID:</strong> <span id="job-id"><?= (int)$results['job_id'] ?></span><br/>
                      <?php endif; ?>
                      <?php if (!empty($results['operator'])): ?>
                      <strong>Operator:</strong> <?= htmlspecialchars($results['operator']) ?><br/>
                      <?php endif; ?>
                      <?php if (!empty($results['target_phone'])): ?>
                      <strong>Target phone:</strong> <?= htmlspecialchars($results['target_phone']) ?><br/>
                      <?php endif; ?>
                      <?php if (!empty($results['mssql_database'])): ?>
                      <strong>MSSQL database:</strong> <?= htmlspecialchars($results['mssql_database']) ?><br/>
                      <?php endif; ?>
                      <?php if (!empty($results['phase'])): ?>
                      <strong>Phase:</strong> <span id="job-phase"><?= htmlspecialchars($results['phase']) ?></span><br/>
                      <?php endif; ?>
                      <strong>Status:</strong>
                      <span id="job-status" class="badge" style="background-color: <?= ($results['status'] === 'Success') ? '#28a745' : (($results['status'] === 'Processing') ? '#ffc107' : '#dc3545') ?>; color:#fff; padding: 2px 6px; border-radius: 3px; font-weight: bold;">
                        <?= htmlspecialchars($results['status']) ?>
                      </span>
                    </p>

                    <div class="results-summary">
                      <div class="result-card">
                        <div>Total Records</div>
                        <div class="num color-total" id="stat-total"><?= (int)$results['total'] ?></div>
                      </div>
                      <div class="result-card">
                        <div>Inserted</div>
                        <div class="num color-success" id="stat-inserted"><?= (int)$results['inserted'] ?></div>
                      </div>
                      <div class="result-card">
                        <div>Failed</div>
                        <div class="num color-failed" id="stat-failed"><?= (int)$results['failed'] ?></div>
                      </div>
                      <div class="result-card">
                        <div>Progress</div>
                        <div class="num color-skipped" id="stat-progress"><?= htmlspecialchars((string)($results['progress_percent'] ?? '—')) ?><?= isset($results['progress_percent']) ? '%' : '' ?></div>
                      </div>
                    </div>

                    <?php if (!empty($results['pending']) && !empty($results['job_id'])): ?>
                    <div class="progress-bar-wrap">
                      <div class="progress-bar-fill" id="progress-bar">0%</div>
                    </div>
                    <p id="pending-message" style="font-size: 12px; color: #FFD700;">Job still running — updating progress...</p>
                    <?php endif; ?>

                    <?php if (!empty($results['staging_rows'])): ?>
                    <div style="margin-top: 18px;">
                      <strong>Rows in cdatpcsuspect (latest <?= count($results['staging_rows']) ?> for this phone)</strong>
                      <table class="staging-preview-table">
                        <tr>
                          <th>ID</th>
                          <th>Phone</th>
                          <th>Other</th>
                          <th>Start Time</th>
                          <th>Duration</th>
                          <th>Incoming</th>
                          <th>Operator</th>
                          <th>Imported At</th>
                        </tr>
                        <?php foreach ($results['staging_rows'] as $row): ?>
                        <tr>
                          <td><?= (int)$row['staging_id'] ?></td>
                          <td><?= htmlspecialchars((string)$row['phone']) ?></td>
                          <td><?= htmlspecialchars((string)$row['other']) ?></td>
                          <td><?= htmlspecialchars((string)$row['starttime']) ?></td>
                          <td><?= htmlspecialchars((string)$row['duration']) ?></td>
                          <td><?= htmlspecialchars((string)$row['incoming']) ?></td>
                          <td><?= htmlspecialchars((string)$row['operator']) ?></td>
                          <td><?= htmlspecialchars((string)$row['imported_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </table>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['warnings'])): ?>
                    <div style="font-size: 12px; margin-top: 10px;">
                      <strong>Warnings:</strong>
                      <ul>
                        <?php foreach ($results['warnings'] as $warning): ?>
                          <li><?= htmlspecialchars($warning) ?></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['reason'])): ?>
                    <p style="font-size: 12px; color: #FFB6C1;"><strong>Note:</strong> <?= htmlspecialchars($results['reason']) ?></p>
                    <?php endif; ?>
                  </div>

                  <div style="text-align: right; margin-top: 25px;">
                    <a href="admin_upload_history.php" class="btn-secondary" style="text-decoration: none;">View Upload History</a>
                    <a href="admin_upload.php" class="btn-action" style="text-decoration: none; display: inline-block;">Upload Another File</a>
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

var moduleConfig = <?= json_encode($modules, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function updateModuleHint(moduleVal) {
    var hint = document.getElementById('module-hint');
    var fileInput = document.getElementById('cdr_file');
    var fileLabel = document.getElementById('file-label');
    if (!moduleVal || !moduleConfig[moduleVal]) {
        hint.style.display = 'none';
        return;
    }
    var conf = moduleConfig[moduleVal];
    hint.textContent = conf.description || '';
    hint.style.display = 'block';
    fileInput.accept = conf.accept || '';
    var exts = (conf.allowed_extensions || []).join(', ').toUpperCase();
    var maxMb = Math.round((conf.max_file_size || 0) / (1024 * 1024));
    fileLabel.textContent = 'Select Upload File (' + exts + ', max ' + maxMb + 'MB)';
}

<?php if ($step === 2 && !empty($results['pending']) && !empty($results['job_id'])): ?>
(function pollJob() {
    var jobId = <?= (int)$results['job_id'] ?>;
    var interval = setInterval(function() {
        fetch('admin_upload_job_status.php?job_id=' + jobId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.ok) return;
                var status = (data.status || '').toLowerCase();
                var statusEl = document.getElementById('job-status');
                var phaseEl = document.getElementById('job-phase');
                var bar = document.getElementById('progress-bar');
                if (data.phase && phaseEl) phaseEl.textContent = data.phase;
                if (data.progress_percent != null) {
                    document.getElementById('stat-progress').textContent = data.progress_percent + '%';
                    if (bar) {
                        bar.style.width = data.progress_percent + '%';
                        bar.textContent = data.progress_percent + '%';
                    }
                }
                if (data.total_records != null) document.getElementById('stat-total').textContent = data.total_records;
                if (data.rows_committed != null) document.getElementById('stat-inserted').textContent = data.rows_committed;
                if (status === 'completed' || status === 'validated') {
                    statusEl.textContent = 'Success';
                    statusEl.style.backgroundColor = '#28a745';
                    clearInterval(interval);
                    var msg = document.getElementById('pending-message');
                    if (msg) msg.textContent = 'Processing completed.';
                } else if (status === 'failed') {
                    statusEl.textContent = 'Failed';
                    statusEl.style.backgroundColor = '#dc3545';
                    clearInterval(interval);
                    var msgFail = document.getElementById('pending-message');
                    if (msgFail) msgFail.textContent = data.error_message || 'Processing failed.';
                }
            })
            .catch(function() {});
    }, 3000);
})();
<?php endif; ?>
</script>
</body>
</html>
