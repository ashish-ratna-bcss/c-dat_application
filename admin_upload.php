<?php
/**
 * admin_upload.php
 * Administrative panel for CDR/SDR document uploads.
 * Contains both Section 1 (Legacy Standard Upload) and Section 2 (Custom Table Upload).
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_uploader();


// ═════════════════════════════════════════════════════════════════════════════
// BACKEND AJAX ENDPOINTS (For Custom Table Upload)
// ═════════════════════════════════════════════════════════════════════════════
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    require_once __DIR__ . '/sqlsrv_compat.php';
    
    $conn = sqlsrv_connect("CPHYDERABAD1\\DAU_HYD_2023", ["Database" => "FORMS"]);
    if (!$conn) {
        echo json_encode(['ok' => false, 'error' => 'Database connection failed.']);
        exit;
    }
    
    $ajaxAction = $_POST['ajax_action'];
    
    if ($ajaxAction === 'get_tables') {
        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";
        $stmt = sqlsrv_query($conn, $sql);
        $tables = [];
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $tables[] = $row['TABLE_NAME'] ?? $row['table_name'];
            }
        }
        echo json_encode(['ok' => true, 'tables' => $tables]);
        exit;
    }
    
    if ($ajaxAction === 'create_table') {
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name'] ?? '');
        if (empty($tableName)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid table name.']);
            exit;
        }
        
        // Automatic columns creation in the backend
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id SERIAL PRIMARY KEY,
            phone VARCHAR(100),
            imei VARCHAR(100),
            call_time VARCHAR(100),
            duration VARCHAR(100),
            location VARCHAR(255),
            network VARCHAR(100)
        )";
        
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt) {
            // Log to general user activity log
            audit_log('Data Upload', 'Create Table', ['table_name' => $tableName]);
            
            echo json_encode(['ok' => true, 'table_name' => $tableName]);
        } else {
            $errors = sqlsrv_errors();
            echo json_encode(['ok' => false, 'error' => $errors[0]['message'] ?? 'Failed to create table.']);
        }
        exit;
    }
    
    if ($ajaxAction === 'insert_data') {
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name'] ?? '');
        $rows = json_decode($_POST['rows'] ?? '[]', true);
        if (empty($tableName) || empty($rows)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid parameters.']);
            exit;
        }
        
        $network = trim($_POST['network'] ?? 'ALL');
        $dbName = trim($_POST['db'] ?? '');
        $isNewTable = trim($_POST['is_new_table'] ?? 'No');
        $insertedCount = 0;
        foreach ($rows as $row) {
            $phone = trim($row['phone'] ?? '');
            $imei = trim($row['imei'] ?? '');
            $callTime = trim($row['call_time'] ?? '');
            $duration = trim($row['duration'] ?? '');
            $location = trim($row['location'] ?? '');
            
            $sql = "INSERT INTO $tableName (phone, imei, call_time, duration, location, network) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = sqlsrv_query($conn, $sql, [$phone, $imei, $callTime, $duration, $location, $network]);
            if ($stmt) {
                $insertedCount++;
            }
        }
        
        // Log custom upload activity
        try {
            $db = audit_db();
            $stmtLog = $db->prepare("
                INSERT INTO upload_activity_logs (user_id, username, module_name, file_name, file_size, upload_status, total_records, inserted_records, failed_records, ip_address, db_name, table_name, is_new_table, uploaded_at)
                VALUES (:uid, :uname, :module, :file, :size, :status, :total, :inserted, :failed, :ip, :db_name, :table_name, :is_new_table, NOW())
            ");
            $stmtLog->execute([
                ':uid' => $_SESSION['audit_user_id'] ?? 0,
                ':uname' => $_SESSION['audit_username'] ?? 'Admin',
                ':module' => 'Custom: ' . $tableName,
                ':file' => trim($_POST['file_name'] ?? 'custom_upload.csv'),
                ':size' => (int)($_POST['file_size'] ?? 0),
                ':status' => 'Success',
                ':total' => $insertedCount,
                ':inserted' => $insertedCount,
                ':failed' => 0,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':db_name' => $dbName,
                ':table_name' => $tableName,
                ':is_new_table' => $isNewTable
            ]);
        } catch (Exception $e) {
            error_log("Failed to log custom upload activity: " . $e->getMessage());
        }
        
        // Also log to the general user activity log
        audit_log(
            'Data Upload',
            'Custom Upload',
            [
                'database' => $dbName,
                'table' => $tableName,
                'is_new_table' => $isNewTable,
                'inserted_records' => $insertedCount,
                'file_name' => trim($_POST['file_name'] ?? 'custom_upload.csv')
            ]
        );
        
        echo json_encode(['ok' => true, 'inserted' => $insertedCount]);
        exit;
    }
    
    echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════
// SECTION 1: LEGACY STANDARD UPLOAD FORM HANDLING
// ═════════════════════════════════════════════════════════════════════════════
require_once __DIR__ . '/cdr_upload_parser.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_action'])) {
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

                        $selectedNetwork = $_POST['network'] ?? 'ALL';
                        $operatorMap = [
                            '2' => 'airtel',
                            '15' => 'jio',
                            '12' => 'vi',
                            '4' => 'bsnl'
                        ];
                        $operator = $operatorMap[$selectedNetwork] ?? null;

                        $results = $parser->processUpload(
                            $selectedModule,
                            $destFile,
                            $fileName,
                            $fileSize,
                            $fileExt,
                            [],
                            $ipAddress,
                            $operator
                        );

                        if ($results['status'] === 'Failed') {
                            $error = 'Processing failed: ' . ($results['reason'] ?? 'Unknown error');
                            @unlink($destFile);
                            
                            // Log failure to general user activity log
                            audit_log(
                                'Data Upload',
                                'Standard Upload Failed',
                                [
                                    'module' => $selectedModule,
                                    'file_name' => $fileName,
                                    'reason' => $results['reason'] ?? 'Unknown error'
                                ]
                            );
                        } else {
                            if ($results['status'] === 'Success') {
                                $success = 'Document processing completed successfully.';
                            } elseif ($results['status'] === 'Processing') {
                                $success = 'Document submitted. Job is still running in the background.';
                            } else {
                                $success = 'Upload logged with status: ' . $results['status'];
                            }
                            
                            // Log success to general user activity log
                            audit_log(
                                'Data Upload',
                                'Standard Upload',
                                [
                                    'module' => $selectedModule,
                                    'file_name' => $fileName,
                                    'status' => $results['status'],
                                    'total_records' => $results['total_records'] ?? 0
                                ]
                            );
                            
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

<!-- Load FontAwesome, SheetJS, and PapaParse -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>

<style type="text/css">
.FONT {
	color: #CFF;
	font-size: 24px;
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif;
}
/* Unified Theme Styles */
.upload-wrapper {
    width: 900px;
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
.form-group select, .form-group input[type="file"], .form-group input[type="text"] {
    width: 100%;
    padding: 8px 10px;
    font-size: 13px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    color: #333;
    background: #fff;
}
.form-label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 13px;
    color: #FFA500;
}
.form-select, .form-input {
    width: 100%;
    padding: 10px 12px;
    font-size: 13px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    box-sizing: border-box;
    color: #fff;
    background: rgba(0, 0, 0, 0.4);
    transition: all 0.3s ease;
    outline: none;
}
.form-select:focus, .form-input:focus {
    border-color: #FFA500;
    background: rgba(0, 0, 0, 0.6);
    box-shadow: 0 0 8px rgba(255, 165, 0, 0.3);
}
.form-select option {
    background: #2b5e7c;
    color: #fff;
}
.form-select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
    display: inline-flex;
    align-items: center;
    gap: 8px;
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
    text-decoration: none;
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

/* Tab Layout Styles */
.nav-tabs {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 25px;
}
.nav-tab-btn {
    background: rgba(0, 0, 0, 0.4);
    color: #ccc;
    padding: 12px 24px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
    transition: all 0.3s ease;
}
.nav-tab-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}
.nav-tab-btn.active {
    background: #FFA500;
    color: #000;
    border-color: #FFA500;
    box-shadow: 0 0 10px rgba(255, 165, 0, 0.4);
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}

/* Custom Table Wizard Layout */
.wizard-card {
    background: rgba(0, 0, 0, 0.25);
    border-radius: 8px;
    padding: 22px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 20px;
}
.wizard-title {
    font-size: 18px;
    margin-bottom: 18px;
    color: #FFA500;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}
.form-col {
    flex: 1;
    text-align: left;
}
.upload-dropzone {
    border: 2px dashed rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    padding: 35px;
    text-align: center;
    background: rgba(255, 255, 255, 0.02);
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 15px;
}
.upload-dropzone:hover {
    border-color: #FFA500;
    background: rgba(255, 255, 255, 0.08);
}
.upload-filename {
    font-size: 14px;
    font-weight: bold;
    color: #90EE90;
    margin-top: 10px;
    display: none;
}
.preview-table-wrapper {
    overflow: auto;
    max-height: 400px;
    margin-top: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.3);
}
.preview-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    color: #fff;
}
.preview-table th, .preview-table td {
    padding: 10px 12px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    text-align: left;
}
.preview-table th {
    background: rgba(0, 0, 0, 0.5);
    color: #FFA500;
    font-weight: bold;
    position: sticky;
    top: 0;
}
.preview-table td[contenteditable="true"] {
    background: rgba(255, 255, 255, 0.05);
    outline: none;
    cursor: text;
}
.preview-table td[contenteditable="true"]:focus {
    background: rgba(255, 165, 0, 0.15);
    border-color: #FFA500;
    box-shadow: inset 0 0 5px rgba(255, 165, 0, 0.5);
}
.preview-table tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.02);
}
.progress-card {
    background: rgba(0, 0, 0, 0.4);
    border-radius: 8px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 20px;
}
.progress-log {
    background: rgba(0, 0, 0, 0.6);
    padding: 15px;
    border-radius: 5px;
    font-family: monospace;
    font-size: 12px;
    color: #90EE90;
    height: 150px;
    overflow-y: auto;
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 15px;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        
        <!-- Header Menu -->
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

        <p align="center" class="FONT"> COMMON DATA UPLOAD FRAMEWORK </p>
        
        <!-- SECTION / TAB TOGGLES -->
        <div class="nav-tabs">
            <button class="nav-tab-btn active" id="tab-btn-legacy" onclick="switchTab('legacy')"><i class="fa-solid fa-upload"></i> Standard Upload</button>
            <button class="nav-tab-btn" id="tab-btn-custom" onclick="switchTab('custom')"><i class="fa-solid fa-table"></i> Custom Table Upload</button>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════════
             TAB 1: LEGACY STANDARD UPLOAD (SECTION 1)
             ═════════════════════════════════════════════════════════════════════ -->
        <div class="tab-content active" id="tab-content-legacy">
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

                    <!-- Network Selection Dropdown (Only shown when CDR is selected) -->
                    <div class="form-group" id="standard-network-group" style="display: none; margin-top: 15px;">
                      <label for="standard-network-select">Select Network</label>
                      <select name="network" id="standard-network-select">
                        <option value="ALL">All Networks</option>
                        <option value="2">Airtel</option>
                        <option value="15">Jio</option>
                        <option value="12">VI</option>
                        <option value="4">BSNL</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label for="cdr_file" id="file-label">Select Upload File</label>
                      <input type="file" name="cdr_file" id="cdr_file" accept=".csv,.xls,.xlsx" required="required" />
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                      <a href="admin_upload_history.php?type=standard" class="btn-secondary"><i class="fa-solid fa-clock-rotate-left"></i> Upload History</a>
                      <button type="button" class="btn-secondary" id="standard-preview-btn" onclick="generateStandardPreview()" style="display: none; margin-right: 10px;"><i class="fa-solid fa-table-list"></i> Preview Data</button>
                      <input type="submit" class="btn-action" value="Upload & Process File" />
                    </div>
                  </form>

                  <!-- Standard Sheet Preview & Data Grid Section -->
                  <div id="standard-preview-container" style="display:none; margin-top: 25px;">
                      <div class="wizard-card" style="background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.15);">
                          <div class="wizard-title" style="color: #FFA500;"><i class="fa-solid fa-eye"></i> 3. Preview Uploaded Sheet Data</div>
                          <p style="font-size:12px; color:#ccc; margin-bottom:12px; text-align: left;">
                              Below is the preview of the data in your selected file.
                          </p>
                          
                          <div class="preview-table-wrapper" style="max-height: 300px; border: 1px solid rgba(255, 255, 255, 0.15); background: rgba(0,0,0,0.3);">
                              <table class="preview-table" id="standard-preview-table">
                                  <!-- Sheet columns and rows will load here -->
                              </table>
                          </div>
                          <div id="standard-preview-note" style="margin-top: 8px; font-size: 11px; color: #FFA500; text-align: left; display: none;"></div>
                      </div>
                  </div>
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
                      <strong>Status:</strong>
                      <span id="job-status" class="badge" style="background-color: <?= ($results['status'] === 'Success') ? '#28a745' : (($results['status'] === 'Processing') ? '#ffc107' : '#dc3545') ?>; color:#fff; padding: 2px 6px; border-radius: 3px; font-weight: bold;">
                        <?= htmlspecialchars($results['status']) ?>
                      </span>
                    </p>

                    <!-- Removed records count card as requested in previous turns -->

                    <?php if (!empty($results['pending']) && !empty($results['job_id'])): ?>
                    <div class="progress-bar-wrap">
                      <div class="progress-bar-fill" id="progress-bar">0%</div>
                    </div>
                    <p id="pending-message" style="font-size: 12px; color: #FFD700;">Job still running — updating progress...</p>
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
                    <a href="admin_upload_history.php?type=standard" class="btn-secondary">View Upload History</a>
                    <a href="admin_upload.php" class="btn-action">Upload Another File</a>
                  </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════════
             TAB 2: CUSTOM TABLE UPLOAD (SECTION 2 - WIZARD)
             ═════════════════════════════════════════════════════════════════════ -->
        <div class="tab-content" id="tab-content-custom">
            <div class="upload-wrapper">
                
                <!-- Setup Wizard Section -->
                <div id="custom-wizard-setup">
                    <div class="wizard-card">
                        <div class="wizard-title"><i class="fa-solid fa-database"></i> 1. Database & Table Selection</div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label" for="custom-db-select">Tower Database</label>
                                <select id="custom-db-select" class="form-select" onchange="loadCustomTables()">
                                    <option value="">-- Tower Database --</option>
                                    <option value="FORMS">FORMS</option>
                                    <option value="CDAT">CDAT</option>
                                    <option value="JRMS">JRMS</option>
                                    <option value="PDACT">PDACT</option>
                                    <option value="TWRDB">TWRDB</option>
                                </select>
                            </div>
                            
                            <div class="form-col">
                                <label class="form-label" for="custom-table-select">Select Target Table</label>
                                <select id="custom-table-select" class="form-select" onchange="handleCustomTableChange()" disabled>
                                    <option value="">-- Choose Table --</option>
                                </select>
                            </div>

                            <div class="form-col">
                                <label class="form-label" for="custom-network-select">Select Network</label>
                                <select id="custom-network-select" class="form-select">
                                    <option value="ALL">All Networks</option>
                                    <option value="2">Airtel</option>
                                    <option value="15">Jio</option>
                                    <option value="12">VI</option>
                                    <option value="4">BSNL</option>
                                </select>
                            </div>
                        </div>

                        <!-- Predefined Auto-Columns Table Creator (Hidden by default, shown when 'new table' is selected) -->
                        <div id="custom-table-creator-section" style="margin-top: 15px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); padding: 15px; border-radius: 6px; display: none;">
                            <div style="font-weight: bold; font-size:13px; color:#FFA500; margin-bottom:10px;"><i class="fa-solid fa-plus-circle"></i> Create New Table with Predefined Columns</div>
                            
                            <div class="form-row" style="align-items: flex-end;">
                                <div class="form-col" style="flex:2;">
                                    <label class="form-label" for="custom-new-table-name">New Table Name</label>
                                    <input type="text" id="custom-new-table-name" class="form-input" placeholder="e.g. suspect_walkin_details" />
                                </div>
                                <div class="form-col" style="flex:1;">
                                    <button type="button" class="btn-action" style="width:100%; justify-content:center;" onclick="createCustomTable()"><i class="fa-solid fa-plus"></i> Create Table</button>
                                </div>
                            </div>
                            
                            <div style="margin-top: 10px; font-size: 11px; color:#bbb;">
                                <strong>System Note:</strong> The table will be automatically created with the following columns:
                                <code style="color:#FFA500;">phone</code>, 
                                <code style="color:#FFA500;">imei</code>, 
                                <code style="color:#FFA500;">call_time</code>, 
                                <code style="color:#FFA500;">duration</code>, 
                                <code style="color:#FFA500;">network</code>, and 
                                <code style="color:#FFA500;">location</code> (Mandatory system column).
                            </div>
                        </div>
                    </div>

                    <div class="wizard-card" style="margin-top:20px;">
                        <div class="wizard-title"><i class="fa-solid fa-file-excel"></i> 2. File Upload & Location</div>
                        
                        <!-- Drag & Drop Zone -->
                        <div class="upload-dropzone" onclick="document.getElementById('custom-file-input').click()" id="custom-dropzone">
                            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                            <h3 style="margin:5px 0;">Drag & Drop or Click to Select File</h3>
                            <p style="font-size:12px; color:#bbb; margin:0;">Supports CSV, XLS, XLSX formats</p>
                            <input type="file" id="custom-file-input" style="display:none;" accept=".csv, .xls, .xlsx" onchange="handleCustomFile(this.files)" />
                            
                            <div class="upload-filename" id="custom-file-details">
                               <i class="fa-solid fa-circle-check"></i> <span id="custom-filename">file.csv</span> (<span id="custom-filesize">0 KB</span>)
                            </div>
                        </div>

                        <!-- Location Input Field -->
                        <div class="form-group" style="margin-top:15px;">
                            <label class="form-label" for="custom-location-input">Location Value <span style="color:#ff6b6b;">*</span></label>
                            <input type="text" id="custom-location-input" class="form-input" placeholder="e.g. Hyderabad, Madhapur PS, Cyberabad" />
                            <span style="font-size: 11px; color:#aaa; margin-top:4px; display:block;">This value is mandatory and will be populated for all sheet records in the <strong>location</strong> column.</span>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top:20px;">
                        <a href="admin_upload_history.php?type=custom" class="btn-secondary" style="margin-right:10px;"><i class="fa-solid fa-clock-rotate-left"></i> Upload History</a>
                        <button type="button" class="btn-action" onclick="generatePreviewGrid()"><i class="fa-solid fa-table-list"></i> Preview Data</button>
                    </div>

                    <!-- Editable Sheet Preview & Data Grid Section (Displayed directly on the same page) -->
                    <div id="custom-preview-container" style="display:none; margin-top: 25px;">
                        <div class="wizard-card">
                            <div class="wizard-title"><i class="fa-solid fa-edit"></i> 3. Preview & Edit Uploaded Sheet</div>
                            <p style="font-size:12px; color:#ccc; margin-bottom:12px;">
                               Below is the complete content of your uploaded sheet. You can double-click any cell to **edit its value** before inserting.
                            </p>
                            
                            <div class="preview-table-wrapper">
                                <table class="preview-table" id="custom-preview-table">
                                    <!-- Entire sheet columns and rows will load here -->
                                </table>
                            </div>
                        </div>

                        <div style="text-align: right; margin-top:20px;">
                            <button type="button" class="btn-action" style="background:#28a745;" onclick="insertCustomData()"><i class="fa-solid fa-database"></i> Insert Mapped Data</button>
                        </div>
                    </div>
                </div>

                <!-- Simulation Progress / Insertion Progress Card -->
                <div id="custom-progress-section" style="display:none;" class="progress-card">
                    <h4 style="margin-top:0; color:#FFA500;"><i class="fa-solid fa-spinner fa-spin"></i> Writing Records to Table...</h4>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" id="custom-insert-progress-bar">0%</div>
                    </div>
                    <div class="progress-log" id="custom-progress-log"></div>
                </div>

                <!-- Success Section -->
                <div id="custom-success-section" style="display:none; text-align: left; margin-top:20px;">
                    <div class="msg-container msg-success" style="font-size: 16px;"><i class="fa-solid fa-circle-check"></i> Data processing completed successfully!</div>
                    
                    <div class="wizard-card" style="background: rgba(40, 167, 69, 0.05); border-color: rgba(40, 167, 69, 0.2); line-height: 1.8; font-size:13px;">
                       <h3 style="margin-top:0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 6px; color:#28a745;">Upload Summary Details</h3>
                       <strong>Uploaded by:</strong> <?= htmlspecialchars($_SESSION['audit_username'] ?? 'Admin') ?><br/>
                       <strong>Database:</strong> <span id="success-custom-db"></span><br/>
                       <strong>Target Table:</strong> <span id="success-custom-table"></span><br/>
                       <strong>Processed File:</strong> <span id="success-custom-file"></span><br/>
                       <strong>Location:</strong> <span id="success-custom-location" style="color:#FFA500; font-weight:bold;"></span><br/>
                       <strong>Total Records Inserted:</strong> <span id="success-custom-rows" style="color:#90EE90; font-weight:bold;"></span><br/>
                       <strong>Status:</strong> <span class="badge-success">Success</span>
                    </div>

                    <div style="text-align: right; margin-top:20px;">
                       <button type="button" class="btn-secondary" onclick="resetCustomWizard()"><i class="fa-solid fa-refresh"></i> Upload Another File</button>
                    </div>
                </div>
            </div>
        </div>

        <p>&nbsp;</p>
      </td>
    </tr>
  </table>
</div>

<script type="text/javascript">
// Initialize Navigation horizontal menu
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});

// Original Standard Upload Config
var moduleConfig = <?= json_encode($modules, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function updateModuleHint(moduleVal) {
    var hint = document.getElementById('module-hint');
    var fileInput = document.getElementById('cdr_file');
    var fileLabel = document.getElementById('file-label');
    
    // Toggle network group
    var netGroup = document.getElementById('standard-network-group');
    if (netGroup) {
        if (moduleVal === 'cdr') {
            netGroup.style.display = 'block';
        } else {
            netGroup.style.display = 'none';
        }
    }
    
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

// Monitor file input selection to show/hide standard preview button
document.addEventListener('DOMContentLoaded', function() {
    var cdrFile = document.getElementById('cdr_file');
    if (cdrFile) {
        cdrFile.addEventListener('change', function(e) {
            var btn = document.getElementById('standard-preview-btn');
            if (btn) {
                if (e.target.files.length > 0) {
                    btn.style.display = 'inline-block';
                } else {
                    btn.style.display = 'none';
                }
            }
        });
    }

    // Intercept form submission to include the edited preview data
    var form = document.querySelector('#tab-content-legacy form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var tableEl = document.getElementById("standard-preview-table");
            var containerEl = document.getElementById("standard-preview-container");
            
            // Only intercept and build if the preview table is visible and has rows
            if (tableEl && containerEl && containerEl.style.display !== 'none' && tableEl.querySelectorAll('tbody tr').length > 0) {
                var ths = Array.from(tableEl.querySelectorAll("thead th"));
                var trs = Array.from(tableEl.querySelectorAll("tbody tr"));
                
                var headers = ths.map(function(th) { return th.innerText.trim(); });
                var dataRows = [];
                
                trs.forEach(function(tr) {
                    var tds = Array.from(tr.querySelectorAll("td"));
                    var rowData = {};
                    headers.forEach(function(h, idx) {
                        rowData[h] = idx < tds.length ? tds[idx].innerText.trim() : "";
                    });
                    dataRows.push(rowData);
                });
                
                // Convert to CSV
                var csv = Papa.unparse(dataRows);
                
                // Get original file name and change extension to .csv if it was excel
                var fileInput = document.getElementById("cdr_file");
                var originalName = fileInput.files[0] ? fileInput.files[0].name : "edited_upload.csv";
                var baseName = originalName.substring(0, originalName.lastIndexOf('.')) || originalName;
                var newName = baseName + "_edited.csv";
                
                // Create a new File object
                var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                var file = new File([blob], newName, { type: 'text/csv' });
                
                // Set the file input files using DataTransfer
                var dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
            }
        });
    }
});

function generateStandardPreview() {
    var fileInput = document.getElementById("cdr_file");
    if (!fileInput || fileInput.files.length === 0) {
        alert("Please select a file first!");
        return;
    }
    
    var file = fileInput.files[0];
    var ext = file.name.split('.').pop().toLowerCase();
    
    if (ext !== 'csv' && ext !== 'xls' && ext !== 'xlsx') {
        alert("Unsupported file format! Please select a CSV or Excel file.");
        return;
    }
    
    var reader = new FileReader();
    
    reader.onload = function(e) {
        var headers = [];
        var rows = [];
        
        if (ext === 'csv') {
            var text = e.target.result;
            var parsed = Papa.parse(text, { header: true, skipEmptyLines: true });
            if (parsed.data && parsed.data.length > 0) {
                headers = Object.keys(parsed.data[0]);
                rows = parsed.data;
            }
        } else {
            var data = new Uint8Array(e.target.result);
            var workbook = XLSX.read(data, { type: 'array' });
            var firstSheet = workbook.SheetNames[0];
            var json = XLSX.utils.sheet_to_json(workbook.Sheets[firstSheet], { defval: "" });
            if (json.length > 0) {
                headers = Object.keys(json[0]);
                rows = json;
            }
        }
        
        if (rows.length === 0) {
            alert("The selected file is empty!");
            return;
        }
        
        var tableEl = document.getElementById("standard-preview-table");
        tableEl.innerHTML = "";
        
        // Set headers
        var headerHTML = "<thead><tr>";
        headers.forEach(function(h) {
            headerHTML += "<th>" + h + "</th>";
        });
        headerHTML += "</tr></thead>";
        
        // Render rows with contenteditable="true" (limit to 150 rows for performance)
        var bodyHTML = "<tbody>";
        var previewRows = rows.slice(0, 150);
        previewRows.forEach(function(row) {
            bodyHTML += "<tr>";
            headers.forEach(function(h) {
                var val = row[h];
                bodyHTML += '<td contenteditable="true">' + (val !== undefined ? val : "") + '</td>';
            });
            bodyHTML += "</tr>";
        });
        bodyHTML += "</tbody>";
        
        tableEl.innerHTML = headerHTML + bodyHTML;
        
        // Update note if there are more rows
        var noteEl = document.getElementById("standard-preview-note");
        if (noteEl) {
            if (rows.length > 150) {
                noteEl.innerHTML = "Showing first 150 of " + rows.length + " rows in the file. You can edit any cell before uploading.";
                noteEl.style.display = "block";
            } else {
                noteEl.innerHTML = "You can double-click or click any cell to edit its value before uploading.";
                noteEl.style.display = "block";
            }
        }
        
        // Show the preview container
        document.getElementById("standard-preview-container").style.display = "block";
        document.getElementById("standard-preview-container").scrollIntoView({ behavior: 'smooth' });
    };
    
    if (ext === 'csv') {
        reader.readAsText(file);
    } else {
        reader.readAsArrayBuffer(file);
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// TAB SWITCHING LOGIC
// ═════════════════════════════════════════════════════════════════════════════
function switchTab(tab) {
    document.getElementById("tab-btn-legacy").classList.remove("active");
    document.getElementById("tab-btn-custom").classList.remove("active");
    document.getElementById("tab-content-legacy").classList.remove("active");
    document.getElementById("tab-content-custom").classList.remove("active");

    if (tab === 'legacy') {
        document.getElementById("tab-btn-legacy").classList.add("active");
        document.getElementById("tab-content-legacy").classList.add("active");
    } else {
        document.getElementById("tab-btn-custom").classList.add("active");
        document.getElementById("tab-content-custom").classList.add("active");
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// CUSTOM TABLE UPLOAD SCRIPTS
// ═════════════════════════════════════════════════════════════════════════════
let customParsedData = null;
let newlyCreatedTableName = '';
setupCustomDragAndDrop();

function setupCustomDragAndDrop() {
    const dropzone = document.getElementById("custom-dropzone");
    if (!dropzone) return;
    
    dropzone.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropzone.style.borderColor = "#FFA500";
        dropzone.style.background = "rgba(255, 165, 0, 0.05)";
    });
    
    dropzone.addEventListener("dragleave", (e) => {
        e.preventDefault();
        dropzone.style.borderColor = "rgba(255, 255, 255, 0.3)";
        dropzone.style.background = "rgba(255, 255, 255, 0.02)";
    });
    
    dropzone.addEventListener("drop", (e) => {
        e.preventDefault();
        dropzone.style.borderColor = "rgba(255, 255, 255, 0.3)";
        dropzone.style.background = "rgba(255, 255, 255, 0.02)";
        if (e.dataTransfer.files.length > 0) {
            handleCustomFile(e.dataTransfer.files);
        }
    });
}

// Load Tables List via AJAX based on Database selection
function loadCustomTables(selectValue = '') {
    const db = document.getElementById("custom-db-select").value;
    const tableSelect = document.getElementById("custom-table-select");
    
    if (!db) {
        tableSelect.innerHTML = '<option value="">-- Choose Table --</option>';
        tableSelect.disabled = true;
        handleCustomTableChange();
        return;
    }
    
    const formData = new FormData();
    formData.append("ajax_action", "get_tables");
    formData.append("db", db);
    
    fetch("admin_upload.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            tableSelect.disabled = false;
            let options = '<option value="">-- Choose Table --</option>';
            data.tables.forEach(t => {
                const selected = t === selectValue ? 'selected' : '';
                options += `<option value="${t}" ${selected}>${t}</option>`;
            });
            // Add 'new table' option
            options += '<option value="__CREATE__" style="color: #FFA500; font-weight:bold;">new table</option>';
            tableSelect.innerHTML = options;
            handleCustomTableChange();
        } else {
            alert("Error loading tables: " + data.error);
        }
    })
    .catch(err => {
        console.error(err);
    });
}

// Handle Custom Table Dropdown Change
function handleCustomTableChange() {
    const tableSelect = document.getElementById("custom-table-select");
    const creatorSection = document.getElementById("custom-table-creator-section");
    if (tableSelect && tableSelect.value === "__CREATE__") {
        creatorSection.style.display = "block";
    } else {
        if (creatorSection) creatorSection.style.display = "none";
    }
}

// Create New Table with Predefined Columns
function createCustomTable() {
    const db = document.getElementById("custom-db-select").value;
    const tableName = document.getElementById("custom-new-table-name").value.trim().toLowerCase().replace(/[^a-z0-9_]/g, '_');
    
    if (!db) {
        alert("Please select a Database first!");
        return;
    }
    if (!tableName) {
        alert("Please enter a valid Table Name!");
        return;
    }
    
    const formData = new FormData();
    formData.append("ajax_action", "create_table");
    formData.append("db", db);
    formData.append("table_name", tableName);
    
    fetch("admin_upload.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert(`Table "${data.table_name}" created successfully with automatic columns: phone, imei, call_time, duration, location, network!`);
            newlyCreatedTableName = data.table_name;
            document.getElementById("custom-new-table-name").value = "";
            loadCustomTables(data.table_name);
        } else {
            alert("Failed to create table: " + data.error);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Server error occurred during table creation.");
    });
}

// File Selection Parser
function handleCustomFile(files) {
    if (files.length === 0) return;
    const file = files[0];
    const ext = file.name.split('.').pop().toLowerCase();
    
    if (ext !== 'csv' && ext !== 'xls' && ext !== 'xlsx') {
        alert("Unsupported file format! Please upload a CSV or Excel file.");
        return;
    }
    
    const reader = new FileReader();
    
    reader.onload = function(e) {
        let headers = [];
        let rows = [];
        
        if (ext === 'csv') {
            const text = e.target.result;
            const parsed = Papa.parse(text, { header: true, skipEmptyLines: true });
            if (parsed.data && parsed.data.length > 0) {
                headers = Object.keys(parsed.data[0]);
                rows = parsed.data;
            }
        } else {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.SheetNames[0];
            const json = XLSX.utils.sheet_to_json(workbook.Sheets[firstSheet], { defval: "" });
            if (json.length > 0) {
                headers = Object.keys(json[0]);
                rows = json;
            }
        }
        
        customParsedData = {
            name: file.name,
            size: file.size,
            headers: headers,
            rows: rows
        };
        
        document.getElementById("custom-filename").innerText = file.name;
        document.getElementById("custom-filesize").innerText = Math.round(file.size / 1024) + " KB";
        document.getElementById("custom-file-details").style.display = "block";
    };
    
    if (ext === 'csv') {
        reader.readAsText(file);
    } else {
        reader.readAsArrayBuffer(file);
    }
}

// Generate the Preview Grid showing the ENTIRE sheet
function generatePreviewGrid() {
    const db = document.getElementById("custom-db-select").value;
    const tbl = document.getElementById("custom-table-select").value;
    const locVal = document.getElementById("custom-location-input").value.trim();
    
    if (!db || !tbl) {
        alert("Please select a Database and a Target Table first!");
        return;
    }
    if (!customParsedData) {
        alert("Please select or upload a CSV/Excel file!");
        return;
    }
    if (!locVal) {
        alert("Please specify a Location Value!");
        return;
    }
    
    const tableEl = document.getElementById("custom-preview-table");
    tableEl.innerHTML = "";
    
    // Get all headers from the uploaded sheet
    const sheetHeaders = customParsedData.headers;
    
    // Set headers: Render all sheet headers + location
    let headerHTML = "<thead><tr>";
    sheetHeaders.forEach(h => {
        headerHTML += `<th>${h}</th>`;
    });
    headerHTML += "<th style='background: rgba(255, 165, 0, 0.2); color:#FFA500;'>location</th>";
    headerHTML += "</tr></thead>";
    
    // Render all rows with all sheet columns + location value
    let bodyHTML = "<tbody>";
    customParsedData.rows.forEach(row => {
        bodyHTML += "<tr>";
        sheetHeaders.forEach(h => {
            const val = row[h];
            bodyHTML += `<td contenteditable="true">${val !== undefined ? val : ""}</td>`;
        });
        // Location column
        bodyHTML += `<td contenteditable="true" style='background: rgba(255, 165, 0, 0.05); font-weight:bold;'>${locVal}</td>`;
        bodyHTML += "</tr>";
    });
    bodyHTML += "</tbody>";
    
    tableEl.innerHTML = headerHTML + bodyHTML;
    
    // Show the preview container
    document.getElementById("custom-preview-container").style.display = "block";
    document.getElementById("custom-preview-container").scrollIntoView({ behavior: 'smooth' });
}

// Bulk insert rows from the editable preview table via AJAX
function insertCustomData() {
    const tableEl = document.getElementById("custom-preview-table");
    const ths = Array.from(tableEl.querySelectorAll("thead th"));
    const tbody = tableEl.querySelector("tbody");
    if (!tbody || ths.length === 0) return;
    
    // Find column indices by matching header text (case-insensitive)
    const findHeaderIndex = (keywords) => {
        return ths.findIndex(th => {
            const txt = th.innerText.toLowerCase().replace(/[^a-z0-9]/g, '');
            return keywords.some(k => txt.includes(k));
        });
    };
    
    let phoneIdx = findHeaderIndex(["phone", "mobile", "msisdn", "number", "calling"]);
    let imeiIdx = findHeaderIndex(["imei", "esn", "device"]);
    let callTimeIdx = findHeaderIndex(["time", "date", "datetime", "start"]);
    let durationIdx = findHeaderIndex(["dur", "sec", "length"]);
    let locationIdx = ths.length - 1; // The last column is always location
    
    // If not found, use default sequential fallbacks
    if (phoneIdx === -1) phoneIdx = 0;
    if (imeiIdx === -1 && ths.length > 1) imeiIdx = 1;
    if (callTimeIdx === -1 && ths.length > 2) callTimeIdx = 2;
    if (durationIdx === -1 && ths.length > 3) durationIdx = 3;
    
    const trs = tbody.querySelectorAll("tr");
    const rows = [];
    
    trs.forEach(tr => {
        const tds = tr.querySelectorAll("td");
        if (tds.length >= ths.length) {
            const phone = phoneIdx < tds.length ? tds[phoneIdx].innerText.trim() : "";
            const imei = imeiIdx < tds.length ? tds[imeiIdx].innerText.trim() : "";
            const callTime = callTimeIdx < tds.length ? tds[callTimeIdx].innerText.trim() : "";
            const duration = durationIdx < tds.length ? tds[durationIdx].innerText.trim() : "";
            const location = locationIdx < tds.length ? tds[locationIdx].innerText.trim() : "";
            
            rows.push({
                phone: phone,
                imei: imei,
                call_time: callTime,
                duration: duration,
                location: location
            });
        }
    });
    
    if (rows.length === 0) {
        alert("No records to insert!");
        return;
    }
    
    const db = document.getElementById("custom-db-select").value;
    const tbl = document.getElementById("custom-table-select").value;
    
    document.getElementById("custom-wizard-setup").style.display = "none";
    document.getElementById("custom-progress-section").style.display = "block";
    
    const progressLog = document.getElementById("custom-progress-log");
    const progressBar = document.getElementById("custom-insert-progress-bar");
    
    progressLog.innerHTML = "";
    progressBar.style.width = "0%";
    progressBar.innerText = "0%";
    
    printCustomLog(`[INFO] Initializing transaction pipeline for table [${tbl}]...`);
    printCustomLog(`[INFO] Preparing to insert ${rows.length} rows...`);
    
    // Send in batches of 100 rows to simulate insertion progress bar
    const batchSize = 100;
    let index = 0;
    let insertedTotal = 0;
    
    function sendNextBatch() {
        if (index >= rows.length) {
            // Completed
            printCustomLog(`[SUCCESS] Completed insertion of ${insertedTotal} rows successfully!`);
            setTimeout(() => {
                document.getElementById("custom-progress-section").style.display = "none";
                document.getElementById("custom-success-section").style.display = "block";
                
                document.getElementById("success-custom-db").innerText = db;
                document.getElementById("success-custom-table").innerText = tbl;
                document.getElementById("success-custom-file").innerText = customParsedData.name;
                document.getElementById("success-custom-location").innerText = document.getElementById("custom-location-input").value;
                document.getElementById("success-custom-rows").innerText = insertedTotal + " records";
            }, 800);
            return;
        }
        
        const batch = rows.slice(index, index + batchSize);
        const isNewTable = (tbl === newlyCreatedTableName) ? 'Yes' : 'No';
        const formData = new FormData();
        formData.append("ajax_action", "insert_data");
        formData.append("db", db);
        formData.append("table_name", tbl);
        formData.append("is_new_table", isNewTable);
        formData.append("network", document.getElementById("custom-network-select").value);
        formData.append("file_name", customParsedData.name);
        formData.append("file_size", customParsedData.size);
        formData.append("rows", JSON.stringify(batch));
        
        fetch("admin_upload.php", {
            method: "POST",
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                insertedTotal += data.inserted;
                index += batchSize;
                
                const percent = Math.min(100, Math.round((index / rows.length) * 100));
                progressBar.style.width = percent + "%";
                progressBar.innerText = percent + "%";
                printCustomLog(`[BATCH] Mapped and inserted rows ${index - batch.length + 1} - ${Math.min(index, rows.length)} successfully.`);
                
                setTimeout(sendNextBatch, 300); // 300ms pause for visual updates
            } else {
                printCustomLog(`[ERROR] Batch insertion failed: ${data.error}`);
                alert("Insertion failed. Check console for error details.");
            }
        })
        .catch(err => {
            printCustomLog(`[ERROR] Fetch error: ${err.message}`);
            alert("A server error occurred during batch insertion.");
        });
    }
    
    sendNextBatch();
}

function printCustomLog(text) {
    const log = document.getElementById("custom-progress-log");
    log.innerHTML += `<div>${text}</div>`;
    log.scrollTop = log.scrollHeight;
}

function resetCustomWizard() {
    window.location.href = 'admin_upload.php?tab=custom';
}

window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab === 'custom') {
        switchTab('custom');
    } else {
        switchTab('legacy');
    }
};

// ═════════════════════════════════════════════════════════════════════════════
// ORIGINAL STANDARD UPLOAD STATUS POLLING
// ═════════════════════════════════════════════════════════════════════════════
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
                var bar = document.getElementById('progress-bar');
                if (data.progress_percent != null) {
                    if (bar) {
                        bar.style.width = data.progress_percent + '%';
                        bar.textContent = data.progress_percent + '%';
                    }
                }
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
