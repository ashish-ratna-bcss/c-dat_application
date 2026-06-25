<?php
/**
 * admin_upload.php
 * Administrative panel for the Common Data Upload Framework.
 * Simplified 2-step workflow: upload with automatic column header detection and view results directly.
 */
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/cdr_upload_parser.php';
audit_require_admin();

$configs = require __DIR__ . '/cdr_upload_config.php';
$step = 1;
$error = '';
$success = '';

// Step 1: Upload variables
$selectedModule = '';
$fileName = '';
$fileSize = 0;

// Results variables for Step 2
$results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_file') {
        $selectedModule = $_POST['module'] ?? '';
        if (!isset($configs[$selectedModule])) {
            $error = 'Please select a valid CDR module.';
        } elseif (!isset($_FILES['cdr_file']) || $_FILES['cdr_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please select a valid file to upload.';
        } else {
            $file = $_FILES['cdr_file'];
            $fileName = basename($file['name']);
            $fileSize = $file['size'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExt, ['csv', 'xlsx'])) {
                $error = 'Unsupported file format! Please upload a CSV or XLSX file.';
            } elseif ($fileSize > 25 * 1024 * 1024) { // 25MB limit
                $error = 'File size exceeds the 25MB limit.';
            } else {
                // Ensure temp upload dir exists
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $tempFile = $uploadDir . '/' . uniqid('upload_', true) . '.' . $fileExt;
                if (move_uploaded_file($file['tmp_name'], $tempFile)) {
                    $parser = new CdrUploadParser();
                    
                    // Rename to a permanent file path to keep it on the server
                    $destFile = $uploadDir . '/' . time() . '_' . $fileName;
                    if (@rename($tempFile, $destFile)) {
                        $usePath = $destFile;
                    } else {
                        $usePath = $tempFile;
                    }

                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $results = $parser->processUpload(
                        $selectedModule,
                        $usePath,
                        $fileName,
                        $fileSize,
                        $fileExt,
                        [], // No mapping required since we do not insert rows
                        $ipAddress
                    );

                    if ($results['status'] === 'Failed') {
                        $error = 'Processing failed: ' . ($results['reason'] ?? 'Unknown error');
                        @unlink($usePath);
                    } else {
                        $success = 'Data processing completed successfully!';
                        $step = 2; // Show results directly
                    }
                } else {
                    $error = 'Failed to save the uploaded file on the server.';
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

.error-preview-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin-top: 15px;
}
.error-preview-table th, .error-preview-table td {
    padding: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-align: left;
}
.error-preview-table th {
    background: rgba(255, 0, 0, 0.1);
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

                <!-- Step 1: Upload form -->
                <?php if ($step === 1): ?>
                  <form action="admin_upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_file" />
                    
                    <div class="form-group">
                      <label for="module">Select CDR Module</label>
                      <select name="module" id="module" required="required" onchange="toggleTemplateDownload(this.value)">
                        <option value="">-- Choose Module --</option>
                        <?php foreach ($configs as $key => $conf): ?>
                          <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($conf['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div id="template-download-container" style="display: none; margin-top: 10px; font-size: 13px;">
                        <span style="color: #FFD700; font-weight: bold;">Download Template: </span>
                        <a id="template-download-btn" href="#" class="btn-action" style="font-size: 12px; padding: 5px 12px; text-decoration: none; display: inline-block;">Download CSV Template</a>
                      </div>
                    </div>

                    <div class="form-group">
                      <label for="cdr_file">Select Upload File (CSV, XLSX)</label>
                      <input type="file" name="cdr_file" id="cdr_file" accept=".csv,.xlsx" required="required" />
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                      <a href="admin_upload_history.php" class="btn-secondary" style="text-decoration: none; display: inline-block;">Upload History</a>
                      <input type="submit" class="btn-action" value="Upload & Process File" />
                    </div>
                  </form>
                <?php endif; ?>

                <!-- Step 2: Results display -->
                <?php if ($step === 2): ?>
                  <div style="text-align: left; margin-bottom: 20px;">
                    <h3>Upload Summary Details</h3>
                    <p style="font-size: 13px; line-height: 1.8;">
                      <strong>Uploaded by:</strong> <?= htmlspecialchars($_SESSION['audit_username'] ?? 'Admin') ?><br/>
                      <strong>Module processed:</strong> <?= htmlspecialchars($configs[$selectedModule]['name']) ?><br/>
                      <strong>Processed File:</strong> <?= htmlspecialchars($fileName) ?><br/>
                      <strong>Status:</strong> 
                      <span class="badge" style="background-color: <?= ($results['status'] === 'Success') ? '#28a745' : (($results['status'] === 'Partial') ? '#ffc107' : '#dc3545') ?>; color:#fff; padding: 2px 6px; border-radius: 3px; font-weight: bold;">
                        <?= htmlspecialchars($results['status']) ?>
                      </span>
                    </p>
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

function toggleTemplateDownload(moduleVal) {
    var container = document.getElementById('template-download-container');
    var downloadBtn = document.getElementById('template-download-btn');
    if (moduleVal !== '') {
        downloadBtn.href = 'download_template.php?module=' + encodeURIComponent(moduleVal);
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        downloadBtn.href = '#';
    }
}
</script>
</body>
</html>
