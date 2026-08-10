<?php
/**
 * admin_upload.php
 * Administrative panel for CDR/SDR document uploads.
 * Contains both Section 1 (Legacy Standard Upload) and Section 2 (Custom Table Upload).
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_uploader();

function mapNetworkToOperator(?string $network): ?string
{
    $map = [
        '2' => 'airtel',
        '15' => 'jio',
        '12' => 'vi',
        '4' => 'bsnl',
        'Airtel' => 'airtel',
        'Jio' => 'jio',
        'BSNL' => 'bsnl',
        'Vodafone' => 'vi',
        'Idea' => 'vi',
        'Vi' => 'vi',
        'VI' => 'vi',
    ];
    $network = trim((string)$network);
    if ($network === '' || strcasecmp($network, 'ALL') === 0) {
        return null;
    }
    return $map[$network] ?? strtolower($network);
}

function normalizeUploadedFiles(array $files): array
{
    if (!is_array($files['name'] ?? null)) {
        return [$files];
    }
    $normalized = [];
    foreach ($files['name'] as $index => $name) {
        $normalized[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
    }
    return $normalized;
}


if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'preview_cdr') {
    header('Content-Type: application/json');
    require_once __DIR__ . '/excel_converter.php';

    try {
        if (empty($_FILES['preview_file']['tmp_name']) || ($_FILES['preview_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No file received for preview.');
        }

        $originalName = (string)($_FILES['preview_file']['name'] ?? 'upload.csv');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xls', 'xlsx'], true)) {
            throw new RuntimeException('Preview supports CSV, XLS, and XLSX only.');
        }

        $tmpDir = sys_get_temp_dir();
        $workPath = $tmpDir . '/cdr_preview_' . bin2hex(random_bytes(8)) . '_' . preg_replace('/[^A-Za-z0-9._ -]+/', '_', $originalName);
        if (!move_uploaded_file($_FILES['preview_file']['tmp_name'], $workPath)) {
            throw new RuntimeException('Could not read the uploaded file.');
        }

        $csvPath = convert_excel_upload_to_csv($workPath, $ext);
        $operator = mapNetworkToOperator($_POST['network'] ?? null);
        if ($operator === null) {
            throw new RuntimeException('Please select a Network (Airtel, Jio, Vi, or BSNL) before preview.');
        }
        $operatorArg = $operator;

        $script = __DIR__ . '/scripts/cdr_preview.py';
        $cmd = sprintf(
            '%s %s %s %s 150 2>&1',
            cdr_python_bin(),
            escapeshellarg($script),
            escapeshellarg($csvPath),
            escapeshellarg($operatorArg)
        );
        $output = [];
        $code = 0;
        exec($cmd, $output, $code);
        $json = json_decode(implode("\n", $output), true);
        if (!is_array($json) || !array_key_exists('ok', $json)) {
            throw new RuntimeException('Preview parse failed: ' . implode("\n", $output));
        }
        if (empty($json['ok'])) {
            throw new RuntimeException($json['error'] ?? 'Preview failed.');
        }

        echo json_encode($json);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    } finally {
        if (isset($workPath) && is_file($workPath)) {
            @unlink($workPath);
        }
        if (isset($csvPath) && is_file($csvPath) && $csvPath !== ($workPath ?? '')) {
            @unlink($csvPath);
        }
    }
    exit;
}

// Promote a staged upload straight into the live table (uploader self-insert, no separate admin step).
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'approve_staging') {
    header('Content-Type: application/json');
    try {
        $jobId = (int)($_POST['job_id'] ?? 0);
        if ($jobId <= 0) {
            throw new RuntimeException('Missing job id.');
        }
        $cfg = require __DIR__ . '/cdr_upload_config.php';
        $base = rtrim($cfg['api']['base_url'] ?? 'http://127.0.0.1:8088', '/');
        $user = $_SESSION['audit_username'] ?? 'user';
        $url = $base . '/api/v1/documents/' . $jobId . '/staging/approve?username=' . rawurlencode($user);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300,
        ]);
        if (!empty($cfg['api']['api_key'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: ' . $cfg['api']['api_key']]);
        }
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        if ($resp === false) {
            throw new RuntimeException('Could not reach the upload service (is it running on port 8088?): ' . $curlErr);
        }
        $json = json_decode($resp, true);
        if ($code >= 400 || !is_array($json)) {
            $msg = is_array($json) ? ($json['detail'] ?? $json['message'] ?? 'Insert failed.') : ('Service error (HTTP ' . $code . ').');
            throw new RuntimeException(is_array($msg) ? json_encode($msg) : (string)$msg);
        }
        echo json_encode([
            'ok' => true,
            'inserted' => $json['inserted'] ?? null,
            'status' => $json['status'] ?? 'completed',
            'message' => $json['message'] ?? 'Data inserted into the live table.',
        ]);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

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
        $contentFingerprint = trim($_POST['content_fingerprint'] ?? '');
        $batchIndex = (int)($_POST['batch_index'] ?? 0);

        if ($batchIndex === 0 && $contentFingerprint !== '') {
            try {
                $auditDb = audit_db();
                $dupStmt = $auditDb->prepare("
                    SELECT id FROM upload_activity_logs
                    WHERE table_name = :tbl
                      AND content_fingerprint = :fp
                      AND upload_status = 'Success'
                    LIMIT 1
                ");
                $dupStmt->execute([':tbl' => $tableName, ':fp' => $contentFingerprint]);
                if ($dupStmt->fetchColumn()) {
                    echo json_encode([
                        'ok' => false,
                        'error' => 'This sheet was already imported into this table. Duplicate import blocked.',
                    ]);
                    exit;
                }
            } catch (Exception $e) {
                error_log('Duplicate import check failed: ' . $e->getMessage());
            }
        }

        $insertedCount = 0;
        $skippedCount = 0;
        foreach ($rows as $row) {
            $phone = trim($row['phone'] ?? '');
            $imei = trim($row['imei'] ?? '');
            $callTime = trim($row['call_time'] ?? '');
            $duration = trim($row['duration'] ?? '');
            $location = trim($row['location'] ?? '');

            $dupSql = "SELECT 1 FROM $tableName WHERE phone = ? AND COALESCE(imei, '') = ? AND COALESCE(call_time, '') = ? LIMIT 1";
            $dupStmt = sqlsrv_query($conn, $dupSql, [$phone, $imei, $callTime]);
            if ($dupStmt && sqlsrv_fetch_array($dupStmt, SQLSRV_FETCH_ASSOC)) {
                $skippedCount++;
                continue;
            }
            
            $sql = "INSERT INTO $tableName (phone, imei, call_time, duration, location, network) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = sqlsrv_query($conn, $sql, [$phone, $imei, $callTime, $duration, $location, $network]);
            if ($stmt) {
                $insertedCount++;
            }
        }
        
        echo json_encode(['ok' => true, 'inserted' => $insertedCount, 'skipped' => $skippedCount]);
        exit;
    }

    if ($ajaxAction === 'finalize_custom_import') {
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table_name'] ?? '');
        $contentFingerprint = trim($_POST['content_fingerprint'] ?? '');
        $dbName = trim($_POST['db'] ?? '');
        $isNewTable = trim($_POST['is_new_table'] ?? 'No');
        try {
            $db = audit_db();
            $stmtLog = $db->prepare("
                INSERT INTO upload_activity_logs (user_id, username, module_name, file_name, file_size, upload_status, total_records, inserted_records, failed_records, ip_address, db_name, table_name, is_new_table, content_fingerprint, uploaded_at)
                VALUES (:uid, :uname, :module, :file, :size, :status, :total, :inserted, :failed, :ip, :db_name, :table_name, :is_new_table, :fp, NOW())
            ");
            $stmtLog->execute([
                ':uid' => $_SESSION['audit_user_id'] ?? 0,
                ':uname' => $_SESSION['audit_username'] ?? 'Admin',
                ':module' => 'Custom: ' . $tableName,
                ':file' => trim($_POST['file_name'] ?? 'custom_upload.csv'),
                ':size' => (int)($_POST['file_size'] ?? 0),
                ':status' => 'Success',
                ':total' => (int)($_POST['total_rows'] ?? 0),
                ':inserted' => (int)($_POST['inserted_total'] ?? 0),
                ':failed' => (int)($_POST['skipped_total'] ?? 0),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':db_name' => $dbName,
                ':table_name' => $tableName,
                ':is_new_table' => $isNewTable,
                ':fp' => $contentFingerprint !== '' ? $contentFingerprint : null,
            ]);
            audit_log('Data Upload', 'Custom Upload', [
                'database' => $dbName,
                'table' => $tableName,
                'inserted_records' => (int)($_POST['inserted_total'] ?? 0),
                'skipped_duplicates' => (int)($_POST['skipped_total'] ?? 0),
                'file_name' => trim($_POST['file_name'] ?? 'custom_upload.csv'),
            ]);
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($ajaxAction === 'log_sdr_resumable') {
        try {
            $db = audit_db();
            $jobId = (int)($_POST['job_id'] ?? 0);
            $fileName = trim($_POST['file_name'] ?? 'sdr_upload.bak');
            $fileSize = (int)($_POST['file_size'] ?? 0);
            $uploadId = trim($_POST['upload_id'] ?? '');
            $logId = (int)($_POST['log_id'] ?? 0);

            if ($logId > 0 && $jobId > 0) {
                $stmt = $db->prepare('
                    UPDATE upload_activity_logs
                    SET document_job_id = :job_id, upload_status = \'Processing\', file_size = :fsize
                    WHERE id = :id
                ');
                $stmt->execute([':job_id' => $jobId, ':fsize' => $fileSize, ':id' => $logId]);
                echo json_encode(['ok' => true, 'log_id' => $logId]);
                exit;
            }

            $stmtLog = $db->prepare("
                INSERT INTO upload_activity_logs (
                    user_id, username, module_name, file_name, file_size,
                    total_records, inserted_records, failed_records,
                    upload_status, ip_address, document_job_id, uploaded_at
                ) VALUES (
                    :uid, :uname, 'SDR', :fname, :fsize,
                    0, 0, 0, 'Processing', :ip, :job_id, NOW()
                )
                RETURNING id
            ");
            $stmtLog->execute([
                ':uid' => $_SESSION['audit_user_id'] ?? 0,
                ':uname' => $_SESSION['audit_username'] ?? 'Admin',
                ':fname' => $fileName,
                ':fsize' => $fileSize,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':job_id' => $jobId > 0 ? $jobId : null,
            ]);
            $newLogId = (int)$stmtLog->fetchColumn();
            audit_log('Data Upload', 'SDR Resumable Upload', [
                'file_name' => $fileName,
                'upload_id' => $uploadId,
                'job_id' => $jobId,
                'log_id' => $newLogId,
            ]);
            echo json_encode(['ok' => true, 'log_id' => $newLogId]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
    exit;
}

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
$bulkResults = [];

// View a past upload's "2. Results & Log" screen (opened from Upload History -> Action).
// Rebuilds $results from the audit log so the same insert-to-live UI appears without re-uploading.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'results') {
    $viewLogId = (int)($_GET['log_id'] ?? 0);
    if ($viewLogId > 0) {
        try {
            $vstmt = audit_db()->prepare('SELECT * FROM upload_activity_logs WHERE id = :id');
            $vstmt->execute([':id' => $viewLogId]);
            $vlog = $vstmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $vlog = null;
        }
        if ($vlog && stripos((string)($vlog['module_name'] ?? ''), 'custom') === false) {
            $selectedModule = (stripos((string)($vlog['module_name'] ?? ''), 'sdr') !== false) ? 'sdr' : 'cdr';
            $fileName = (string)($vlog['file_name'] ?? '');
            $results = [
                'status'    => (string)($vlog['upload_status'] ?? ''),
                'job_id'    => (int)($vlog['document_job_id'] ?? 0),
                'file_name' => $fileName,
            ];
            $step = 2;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_action'])) {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_file') {
        $selectedModule = $_POST['module'] ?? '';
        $operator = mapNetworkToOperator($_POST['network'] ?? null);
        $errMap = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
        ];

        if (!isset($modules[$selectedModule])) {
            $error = 'Please select a valid module (CDR or SDR).';
        } elseif ($selectedModule === 'cdr' && $operator === null) {
            $error = 'Please select a Network (Airtel, Jio, Vi, or BSNL) before uploading.';
        } elseif (!isset($_FILES['cdr_file'])) {
            $error = 'Please select a valid file to upload.';
        } else {
            $moduleConfig = $modules[$selectedModule];
            $allowedExt = $moduleConfig['allowed_extensions'];
            $maxSize = (int)$moduleConfig['max_file_size'];
            $uploadDir = __DIR__ . '/uploads';
            $parser = new CdrUploadParser();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $files = normalizeUploadedFiles($_FILES['cdr_file']);
            $hadValidFile = false;

            foreach ($files as $file) {
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    if (!$hadValidFile && count($files) === 1) {
                        $error = $errMap[$file['error']] ?? 'Please select a valid file to upload.';
                    }
                    continue;
                }
                $hadValidFile = true;
                $fileName = basename($file['name']);
                $fileSize = (int)$file['size'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($fileExt, $allowedExt, true)) {
                    $bulkResults[] = ['status' => 'Failed', 'file_name' => $fileName, 'reason' => 'Unsupported file format.'];
                    continue;
                }
                if ($fileSize > $maxSize) {
                    $limitLabel = $selectedModule === 'sdr'
                        ? round($maxSize / (1024 * 1024 * 1024)) . ' GB'
                        : round($maxSize / (1024 * 1024)) . ' MB';
                    $bulkResults[] = ['status' => 'Failed', 'file_name' => $fileName, 'reason' => "File exceeds the {$limitLabel} limit."];
                    continue;
                }
                if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true)) {
                    $error = 'Upload directory is missing and could not be created. Contact administrator.';
                    break;
                }
                if (!is_writable($uploadDir)) {
                    $error = 'Upload directory is not writable by the web server.';
                    break;
                }

                $destFile = $uploadDir . '/' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                if (!move_uploaded_file($file['tmp_name'], $destFile)) {
                    $bulkResults[] = ['status' => 'Failed', 'file_name' => $fileName, 'reason' => 'Failed to save the uploaded file on the server.'];
                    continue;
                }

                if ($selectedModule === 'cdr' && in_array($fileExt, ['xls', 'xlsx'], true)) {
                    try {
                        $csvFile = convert_excel_upload_to_csv($destFile, $fileExt);
                        if ($csvFile !== $destFile) {
                            @unlink($destFile);
                            $destFile = $csvFile;
                            $fileExt = 'csv';
                            $fileName = preg_replace('/\.[^.]+$/', '.csv', $fileName);
                        }
                    } catch (Throwable $convEx) {
                        $bulkResults[] = ['status' => 'Failed', 'file_name' => $fileName, 'reason' => $convEx->getMessage()];
                        @unlink($destFile);
                        continue;
                    }
                }

                set_time_limit(0);
                $fileResult = $parser->processUpload(
                    $selectedModule,
                    $destFile,
                    $fileName,
                    $fileSize,
                    $fileExt,
                    [],
                    $ipAddress,
                    $operator
                );
                $fileResult['file_name'] = $fileName;
                $bulkResults[] = $fileResult;

                if ($fileResult['status'] === 'Failed') {
                    @unlink($destFile);
                }
            }

            if ($error === '' && !$hadValidFile) {
                $error = 'Please select a valid file to upload.';
            } elseif ($error === '' && !empty($bulkResults)) {
                $results = count($bulkResults) === 1 ? $bulkResults[0] : null;
                $failedCount = count(array_filter($bulkResults, fn($r) => ($r['status'] ?? '') === 'Failed'));
                $successCount = count($bulkResults) - $failedCount;

                if ($successCount > 0 && $failedCount === 0) {
                    if (count($bulkResults) === 1) {
                        $r = $bulkResults[0];
                        if ($r['status'] === 'Success') {
                            $success = 'Document processing completed successfully.';
                        } elseif ($r['status'] === 'Pending Verification') {
                            $verifyUrl = $r['verify_url'] ?? null;
                            $success = 'Data loaded into staging. Please review and approve before production load.';
                            if ($verifyUrl) {
                                $success .= ' <a href="' . htmlspecialchars($verifyUrl) . '" style="color:#FFD700;font-weight:bold;">Open Verification Screen</a>';
                            }
                        } elseif ($r['status'] === 'Processing') {
                            $success = 'Upload accepted and processing in the background. Refresh <a href="admin_upload_history.php" style="color:#FFD700;font-weight:bold;">Upload History</a> for status.';
                        } else {
                            $success = 'Upload logged with status: ' . $r['status'];
                        }
                    } else {
                        $success = "Processed {$successCount} file(s) successfully.";
                    }
                } elseif ($failedCount > 0 && $successCount === 0) {
                    $error = 'All uploads failed. ' . ($bulkResults[0]['reason'] ?? '');
                } else {
                    $success = "Processed {$successCount} file(s); {$failedCount} failed.";
                }
                $step = 2;
            }
        }
    }
}
?>
<?php
// The stylesheet and CDN tags below belong in <head>. Capture them and hand
// them to layout_begin() so they can stay written as plain HTML here.
require_once __DIR__ . '/includes/layout.php';
ob_start();
?>

<!-- Load FontAwesome, SheetJS, and PapaParse -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
<script src="../assets/js/sdr_resumable_upload.js" type="text/javascript"></script>

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
.preview-table td.preview-readonly {
    background: rgba(255, 255, 255, 0.05);
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
<!-- Loaded last so it overrides the dark-background colours above, which are
     unreadable now that the page sits on the light application shell. -->
<link rel="stylesheet" href="../assets/css/upload.css">
<?php
layout_begin('Data Upload', 'Common Data Upload Framework', ob_get_clean());
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        

        
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
                    <strong>Staging workflow:</strong> CDR uploads are loaded into staging tables for manual verification before promotion to
                    <code style="color:#fff;">cdatpcsuspect</code>.
                    <strong>SDR backups</strong> (.bak, up to 700 GB) use <em>resumable chunked upload</em> — if interrupted, re-select the same file to continue.
                  </div>
                  <div id="sdr-pending-banner" class="staging-banner" style="display:none; border-color:#FFA500; margin-bottom:12px;"></div>
                  <form action="admin_upload.php" method="post" enctype="multipart/form-data" id="standard-upload-form" onsubmit="return handleStandardUploadSubmit(event)">
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
                      <label for="standard-network-select">Select Network <span style="color:#FFA500;">*</span></label>
                      <select name="network" id="standard-network-select" required>
                        <option value="">-- Select Network --</option>
                        <option value="2">Airtel</option>
                        <option value="15">Jio</option>
                        <option value="12">VI</option>
                        <option value="4">BSNL</option>
                      </select>
                      <div style="font-size:11px;color:#ccc;margin-top:6px;">
                        Operator is taken from this dropdown only (not from the filename).
                      </div>
                    </div>

                    <div class="form-group">
                      <label for="cdr_file" id="file-label">Select Upload File(s)</label>
                      <input type="file" name="cdr_file[]" id="cdr_file" accept=".csv,.xls,.xlsx" multiple="multiple" required="required" />
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                      <a href="admin_upload_history.php?type=standard" class="btn-secondary"><i class="fa-solid fa-clock-rotate-left"></i> Upload History</a>
                      <button type="button" class="btn-secondary" id="standard-preview-btn" onclick="generateStandardPreview()" style="display: none; margin-right: 10px;"><i class="fa-solid fa-table-list"></i> Preview Data</button>
                      <input type="submit" class="btn-action" id="standard-submit-btn" value="Upload & Process File" />
                    </div>
                  </form>

                  <!-- SDR resumable upload progress -->
                  <div id="sdr-upload-progress" style="display:none; margin-top:20px; text-align:left;">
                    <div class="wizard-card" style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.15); padding:15px; border-radius:6px;">
                      <div class="wizard-title" style="color:#FFA500; margin-bottom:10px;"><i class="fa-solid fa-cloud-arrow-up"></i> SDR Resumable Upload</div>
                      <div id="sdr-upload-filename" style="font-weight:bold; margin-bottom:8px;"></div>
                      <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" id="sdr-upload-progress-bar">0%</div>
                      </div>
                      <div id="sdr-upload-stats" style="font-size:11px; color:#ccc; margin:8px 0;"></div>
                      <div id="sdr-upload-log" style="font-size:11px; color:#ddd; max-height:120px; overflow-y:auto; background:rgba(0,0,0,0.2); padding:8px; border-radius:4px;"></div>
                      <div style="margin-top:12px; text-align:right;">
                        <button type="button" class="btn-secondary" id="sdr-upload-pause-btn" onclick="pauseSdrUpload()">Pause</button>
                        <button type="button" class="btn-secondary" id="sdr-upload-cancel-btn" onclick="cancelSdrUpload()" style="margin-left:8px;">Cancel</button>
                      </div>
                    </div>
                  </div>

                  <!-- Standard Sheet Preview & Data Grid Section -->
                  <div id="standard-preview-container" style="display:none; margin-top: 25px;">
                      <div class="wizard-card" style="background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.15);">
                          <div class="wizard-title" style="color: #FFA500;"><i class="fa-solid fa-eye"></i> 3. Preview Normalized CDR Data</div>
                          <p style="font-size:12px; color:#ccc; margin-bottom:12px; text-align: left;">
                              Preview after operator parsing and normalization (phone cleanup, direction, cell ID, roaming/state rules).
                              This is read-only here &mdash; after upload you can edit rows on the staging verification screen.
                          </p>

                          <div id="standard-preview-summary" style="font-size:12px;color:#9fd0e6;margin-bottom:12px;text-align:left;display:none;"></div>
                          <div id="standard-preview-pager" style="display:none;flex-wrap:wrap;gap:6px;align-items:center;margin-bottom:14px;"></div>
                          <div id="standard-preview-files"><!-- current file's table (page-wise) --></div>
                      </div>
                  </div>
                <?php endif; ?>

                <?php if ($step === 2 && ($results || !empty($bulkResults))): ?>
                  <div style="text-align: left; margin-bottom: 20px;">
                    <h3>Upload Summary Details</h3>
                    <?php if (count($bulkResults) > 1): ?>
                    <?php $anyPending = count(array_filter($bulkResults, fn($r) => ($r['status'] ?? '') === 'Pending Verification' && !empty($r['job_id']))); ?>
                    <table class="preview-table" style="margin-bottom:15px;">
                      <thead><tr><th>File</th><th>Status</th><th>Job</th><th>Note</th><th>Action</th></tr></thead>
                      <tbody>
                      <?php foreach ($bulkResults as $br): ?>
                        <?php $isPending = (($br['status'] ?? '') === 'Pending Verification') && !empty($br['job_id']); ?>
                        <tr>
                          <td><?= htmlspecialchars($br['file_name'] ?? '') ?></td>
                          <td class="pv-status"><?= htmlspecialchars($br['status'] ?? '') ?></td>
                          <td><?= !empty($br['job_id']) ? (int)$br['job_id'] : '—' ?></td>
                          <td class="pv-note"><?= htmlspecialchars($br['reason'] ?? ($br['verify_url'] ? 'Pending verification' : '')) ?></td>
                          <td>
                            <?php if ($isPending): ?>
                              <?php if (!empty($br['verify_url'])): ?>
                              <a href="<?= htmlspecialchars($br['verify_url']) ?>"
                                style="display:inline-block;background:#1f8a70;color:#fff;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:bold;text-decoration:none;margin-right:6px;">
                                <i class="fa-solid fa-table-list"></i> Preview &amp; Edit</a>
                              <?php endif; ?>
                              <button type="button" onclick="insertToLive(<?= (int)$br['job_id'] ?>, this)"
                                style="background:#FFA500;color:#10222b;border:0;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:bold;cursor:pointer;">
                                <i class="fa-solid fa-database"></i> Insert Data</button>
                            <?php else: ?>&mdash;<?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                      </tbody>
                    </table>
                    <?php if ($anyPending > 1): ?>
                    <button type="button" onclick="insertAllToLive(this)"
                      style="background:#FFA500;color:#10222b;border:0;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:bold;cursor:pointer;margin-bottom:12px;">
                      <i class="fa-solid fa-database"></i> Insert ALL to Live Tables</button>
                    <?php endif; ?>
                    <?php elseif ($results): ?>
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
                    <?php if (($results['status'] ?? '') === 'Pending Verification' && !empty($results['job_id'])): ?>
                    <div id="single-insert-wrap" style="margin:6px 0 14px;">
                      <?php if (!empty($results['verify_url'])): ?>
                      <a href="<?= htmlspecialchars($results['verify_url']) ?>"
                        style="display:inline-block;background:#1f8a70;color:#fff;border:0;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:bold;text-decoration:none;margin-right:8px;">
                        <i class="fa-solid fa-table-list"></i> Preview &amp; Edit Staging</a>
                      <?php endif; ?>
                      <button type="button" onclick="insertToLive(<?= (int)$results['job_id'] ?>, this)"
                        style="background:#FFA500;color:#10222b;border:0;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:bold;cursor:pointer;">
                        <i class="fa-solid fa-database"></i> Insert Data to Live Table</button>
                      <div style="font-size:11px;color:#9fd0e6;margin-top:5px;">Normalized data is in staging. Review/edit rows first, then insert into the live table.</div>
                    </div>
                    <?php endif; ?>

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

var moduleConfig = <?= json_encode($modules, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
var apiConfig = <?= json_encode($config['api'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
var activeSdrUploader = null;
var activeSdrUploadMeta = { uploadId: null, fileKey: null };

function sdrLog(msg) {
    var log = document.getElementById('sdr-upload-log');
    if (!log) return;
    log.innerHTML += '<div>' + msg + '</div>';
    log.scrollTop = log.scrollHeight;
}

function showSdrProgress(show) {
    var el = document.getElementById('sdr-upload-progress');
    if (el) el.style.display = show ? 'block' : 'none';
    var btn = document.getElementById('standard-submit-btn');
    if (btn) btn.disabled = !!show;
}

function refreshSdrPendingBanner() {
    var banner = document.getElementById('sdr-pending-banner');
    if (!banner || !window.sdrUploadHelpers) return;
    var pending = sdrUploadHelpers.loadSavedSessions();
    var keys = Object.keys(pending);
    if (!keys.length) {
        banner.style.display = 'none';
        return;
    }
    var lines = keys.map(function(k) {
        var p = pending[k];
        return (p.filename || k) + ' — ' + sdrUploadHelpers.formatBytes(p.offset || 0) + ' uploaded';
    });
    banner.innerHTML = '<strong>Interrupted SDR upload(s) detected.</strong> Re-select the same .bak file(s) and click Upload to resume.<br/>' + lines.join('<br/>');
    banner.style.display = 'block';
}

async function logSdrResumableUpload(result, file, logId) {
    var fd = new FormData();
    fd.append('ajax_action', 'log_sdr_resumable');
    fd.append('job_id', result.job_id || '');
    fd.append('file_name', file.name);
    fd.append('file_size', file.size);
    fd.append('upload_id', result.upload_id || '');
    if (logId) fd.append('log_id', logId);
    try {
        var resp = await fetch('admin_upload.php', { method: 'POST', body: fd });
        var data = await resp.json();
        return data.log_id || logId || null;
    } catch (e) {
        return logId || null;
    }
}

async function startSdrActivityLog(file) {
    return logSdrResumableUpload({ job_id: '', upload_id: '' }, file, null);
}

async function handleStandardUploadSubmit(event) {
    var moduleVal = document.getElementById('module').value;
    if (moduleVal === 'cdr') {
        var networkSelect = document.getElementById('standard-network-select');
        var networkVal = networkSelect ? (networkSelect.value || '') : '';
        if (!networkVal || networkVal === 'ALL') {
            event.preventDefault();
            alert('Please select a Network (Airtel, Jio, Vi, or BSNL) before uploading.');
            if (networkSelect) networkSelect.focus();
            return false;
        }
        return true;
    }
    if (moduleVal !== 'sdr') {
        return true;
    }
    event.preventDefault();

    var files = document.getElementById('cdr_file').files;
    if (!files || files.length === 0) {
        alert('Please select at least one .bak file.');
        return false;
    }

    if (typeof SdrResumableUploader === 'undefined') {
        alert('Resumable upload module failed to load. Refresh the page.');
        return false;
    }

    activeSdrUploader = new SdrResumableUploader(apiConfig);
    showSdrProgress(true);
    document.getElementById('sdr-upload-log').innerHTML = '';
    document.getElementById('sdr-upload-progress-bar').style.width = '0%';
    document.getElementById('sdr-upload-progress-bar').innerText = '0%';

    try {
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (!file.name.toLowerCase().endsWith('.bak')) {
                alert(file.name + ' is not a .bak file.');
                continue;
            }
            document.getElementById('sdr-upload-filename').innerText = 'Uploading: ' + file.name + ' (' + sdrUploadHelpers.formatBytes(file.size) + ')';
            activeSdrUploadMeta.fileKey = sdrUploadHelpers.fileKey(file);

            var sdrLogId = await startSdrActivityLog(file);

            var result = await activeSdrUploader.uploadFile(file, {
                onStatus: sdrLog,
                onProgress: function(p) {
                    var bar = document.getElementById('sdr-upload-progress-bar');
                    bar.style.width = p.percent.toFixed(1) + '%';
                    bar.innerText = p.percent.toFixed(1) + '%';
                    var speed = sdrUploadHelpers.formatBytes(p.speedBps) + '/s';
                    var eta = sdrUploadHelpers.formatDuration(p.etaSeconds);
                    document.getElementById('sdr-upload-stats').innerText =
                        sdrUploadHelpers.formatBytes(p.offset) + ' / ' + sdrUploadHelpers.formatBytes(p.total) +
                        '  |  ' + speed + '  |  ETA ' + eta;
                }
            });

            await logSdrResumableUpload(result, file, sdrLogId);
            sdrLog('Completed: job #' + result.job_id);
        }
        refreshSdrPendingBanner();
        alert('SDR backup uploaded. Processing runs in the background — check Upload History for status.');
        window.location.href = 'admin_upload_history.php?type=standard';
    } catch (err) {
        sdrLog('[ERROR] ' + (err.message || err));
        alert('Upload stopped: ' + (err.message || err) + '\n\nRe-select the same file to resume from where it left off.');
        refreshSdrPendingBanner();
    } finally {
        showSdrProgress(false);
        activeSdrUploader = null;
    }
    return false;
}

function pauseSdrUpload() {
    if (!activeSdrUploader) return;
    activeSdrUploader.paused = !activeSdrUploader.paused;
    var btn = document.getElementById('sdr-upload-pause-btn');
    btn.innerText = activeSdrUploader.paused ? 'Resume' : 'Pause';
    sdrLog(activeSdrUploader.paused ? 'Paused by user.' : 'Resumed by user.');
}

function cancelSdrUpload() {
    if (!activeSdrUploader) return;
    if (!confirm('Cancel this upload? Partial data on the server will be removed.')) return;
    activeSdrUploader.abort(activeSdrUploadMeta.uploadId, activeSdrUploadMeta.fileKey);
    sdrLog('Upload cancelled.');
    showSdrProgress(false);
}

function updateModuleHint(moduleVal) {
    var hint = document.getElementById('module-hint');
    var fileInput = document.getElementById('cdr_file');
    var fileLabel = document.getElementById('file-label');
    
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
    if (moduleVal === 'sdr') {
        var maxGb = Math.round((conf.max_file_size || 0) / (1024 * 1024 * 1024));
        fileLabel.textContent = 'Select .bak file(s) (max ' + maxGb + ' GB each, resumable upload)';
        fileInput.removeAttribute('multiple');
    } else {
        var maxMb = Math.round((conf.max_file_size || 0) / (1024 * 1024));
        fileLabel.textContent = 'Select Upload File(s) (' + exts + ', max ' + maxMb + ' MB each)';
        fileInput.setAttribute('multiple', 'multiple');
    }

    var previewBtn = document.getElementById('standard-preview-btn');
    if (previewBtn) {
        var showPreview = moduleVal === 'cdr' && fileInput.files && fileInput.files.length > 0;
        previewBtn.style.display = showPreview ? 'inline-block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    refreshSdrPendingBanner();
    var cdrFile = document.getElementById('cdr_file');
    var moduleSelect = document.getElementById('module');
    function syncPreviewButton() {
        var btn = document.getElementById('standard-preview-btn');
        if (!btn) return;
        var isCdr = moduleSelect && moduleSelect.value === 'cdr';
        var hasFile = cdrFile && cdrFile.files && cdrFile.files.length > 0;
        btn.style.display = (isCdr && hasFile) ? 'inline-block' : 'none';
    }
    if (cdrFile) {
        cdrFile.addEventListener('change', syncPreviewButton);
    }
    if (moduleSelect) {
        moduleSelect.addEventListener('change', syncPreviewButton);
        if (moduleSelect.value) {
            updateModuleHint(moduleSelect.value);
        }
    }
    syncPreviewButton();
});

function previewEscapeHtml(s) {
    return String(s).replace(/[&<>"]/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
}

function buildPreviewTableHTML(data) {
    var columns = data.columns || [];
    var rows = data.rows || [];
    var html = '<div class="preview-table-wrapper" style="max-height:300px;border:1px solid rgba(255,255,255,0.15);background:rgba(0,0,0,0.3);">'
             + '<table class="preview-table"><thead><tr>';
    columns.forEach(function(col) { html += '<th>' + previewEscapeHtml(String(col).replace(/_/g, ' ')) + '</th>'; });
    html += '</tr></thead><tbody>';
    if (!rows.length) {
        html += '<tr><td colspan="' + Math.max(columns.length, 1) + '">No parseable CDR rows found.</td></tr>';
    } else {
        rows.forEach(function(row) {
            html += '<tr>';
            columns.forEach(function(col) {
                var val = row[col];
                html += '<td class="preview-readonly">' + (val !== undefined && val !== null ? previewEscapeHtml(String(val)) : '') + '</td>';
            });
            html += '</tr>';
        });
    }
    html += '</tbody></table></div>';
    return html;
}

function buildPreviewNoteHTML(data) {
    var rows = data.rows || [];
    var parts = [];
    if (data.normalized) parts.push('<strong>Normalized preview</strong>');
    if (data.operator) parts.push('Operator: <strong>' + previewEscapeHtml(data.operator) + '</strong>');
    if (data.target_phone) parts.push('Target phone: <strong>' + previewEscapeHtml(data.target_phone) + '</strong>');
    if (data.total_records != null) parts.push('Records: <strong>' + data.total_records + '</strong>');
    if (data.truncated) parts.push('showing first <strong>' + rows.length + '</strong>');
    if (data.parse_errors) parts.push('<span style="color:#ffb3b3;">' + data.parse_errors + ' row(s) unparseable</span>');
    return parts.join(' &nbsp;|&nbsp; ');
}

function showPreviewNotice(message) {
    var old = document.getElementById('preview-notice-overlay');
    if (old) old.remove();
    var overlay = document.createElement('div');
    overlay.id = 'preview-notice-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.55);display:flex;'
        + 'align-items:center;justify-content:center;z-index:99999;';
    var card = document.createElement('div');
    card.style.cssText = 'max-width:460px;width:90%;background:#123c4f;color:#eaf3f8;'
        + 'border:2px solid #FFA500;border-radius:12px;padding:26px 26px 22px;'
        + 'box-shadow:0 18px 50px rgba(0,0,0,0.55);font-family:Verdana,Arial,sans-serif;text-align:center;';
    card.innerHTML =
        '<div style="font-size:38px;line-height:1;margin-bottom:12px;">⚠️</div>'
        + '<div style="font-size:16px;font-weight:bold;margin-bottom:10px;color:#FFA500;">Cannot preview this file</div>'
        + '<div style="font-size:13.5px;line-height:1.6;color:#eaf3f8;margin-bottom:22px;"></div>'
        + '<button type="button" style="background:#FFA500;color:#10222b;border:0;border-radius:8px;'
        + 'padding:10px 34px;font-size:14px;font-weight:bold;cursor:pointer;">OK</button>';
    card.querySelector('div:nth-child(3)').textContent = message;
    var btn = card.querySelector('button');
    btn.onclick = function () { overlay.remove(); };
    overlay.onclick = function (e) { if (e.target === overlay) overlay.remove(); };
    document.addEventListener('keydown', function esc(ev) {
        if (ev.key === 'Escape') { overlay.remove(); document.removeEventListener('keydown', esc); }
    });
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    btn.focus();
}

var previewState = { files: [], cache: {}, current: 0, network: 'ALL' };

async function generateStandardPreview() {
    var fileInput = document.getElementById("cdr_file");
    var moduleSelect = document.getElementById("module");
    if (!fileInput || fileInput.files.length === 0) {
        showPreviewNotice("Please select at least one file first!");
        return;
    }
    if (moduleSelect && moduleSelect.value !== 'cdr') {
        showPreviewNotice("Preview is available for CDR uploads only.");
        return;
    }

    var networkSelect = document.getElementById('standard-network-select');
    var networkVal = networkSelect ? (networkSelect.value || '') : '';
    if (!networkVal || networkVal === 'ALL') {
        showPreviewNotice("Please select a Network (Airtel, Jio, Vi, or BSNL) before preview.");
        if (networkSelect) networkSelect.focus();
        return;
    }

    var files = Array.prototype.slice.call(fileInput.files);
    for (var i = 0; i < files.length; i++) {
        var ext = files[i].name.split('.').pop().toLowerCase();
        if (ext !== 'csv' && ext !== 'xls' && ext !== 'xlsx') {
            showPreviewNotice('Unsupported file: ' + files[i].name + '. Only CSV, XLS, XLSX are allowed.');
            return;
        }
    }

    previewState = { files: files, cache: {}, current: 0, network: networkVal };

    var container = document.getElementById('standard-preview-container');
    var summaryEl = document.getElementById('standard-preview-summary');
    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth' });
    if (summaryEl) {
        summaryEl.style.display = 'block';
        summaryEl.innerHTML = '<strong>' + files.length + '</strong> file(s) selected'
            + (files.length > 1 ? ' — use the page buttons below to review each file.' : '.');
    }

    renderPreviewPager();
    await showPreviewPage(0);
}

function renderPreviewPager() {
    var pager = document.getElementById('standard-preview-pager');
    if (!pager) return;
    var n = previewState.files.length;
    if (n <= 1) { pager.style.display = 'none'; pager.innerHTML = ''; return; }
    pager.style.display = 'flex';
    var base = 'border:1px solid #FFA500;border-radius:6px;padding:5px 11px;font-size:12px;cursor:pointer;font-family:inherit;';
    var html = '<button type="button" data-pv="prev" style="' + base + 'background:transparent;color:#FFA500;">&laquo; Prev</button>';
    for (var i = 0; i < n; i++) {
        var active = (i === previewState.current);
        html += '<button type="button" data-pv="' + i + '" style="' + base
            + (active ? 'background:#FFA500;color:#10222b;font-weight:bold;' : 'background:transparent;color:#eaf3f8;')
            + '">' + (i + 1) + '</button>';
    }
    html += '<button type="button" data-pv="next" style="' + base + 'background:transparent;color:#FFA500;">Next &raquo;</button>';
    pager.innerHTML = html;
    pager.querySelectorAll('button').forEach(function(b) {
        b.onclick = function() {
            var v = b.getAttribute('data-pv');
            var target = previewState.current;
            if (v === 'prev') target = Math.max(0, previewState.current - 1);
            else if (v === 'next') target = Math.min(n - 1, previewState.current + 1);
            else target = parseInt(v, 10);
            showPreviewPage(target);
        };
    });
}

async function showPreviewPage(idx) {
    var n = previewState.files.length;
    if (idx < 0 || idx >= n) return;
    previewState.current = idx;
    renderPreviewPager();

    var filesArea = document.getElementById('standard-preview-files');
    var file = previewState.files[idx];
    var label = 'File ' + (idx + 1) + ' of ' + n + ': ' + previewEscapeHtml(file.name);

    if (previewState.cache[idx]) {
        renderPreviewBlock(filesArea, label, previewState.cache[idx]);
        return;
    }

    filesArea.innerHTML = '<div style="font-weight:bold;color:#FFA500;font-size:13px;text-align:left;">'
        + '<i class="fa-solid fa-file-csv"></i> ' + label
        + ' <span style="color:#9fd0e6;font-weight:normal;"><i class="fa-solid fa-spinner fa-spin"></i> loading…</span></div>';

    try {
        var fd = new FormData();
        fd.append('ajax_action', 'preview_cdr');
        fd.append('preview_file', file);
        fd.append('network', previewState.network);
        var resp = await fetch('admin_upload.php', { method: 'POST', body: fd });
        var data = await resp.json();
        previewState.cache[idx] = data;
        if (previewState.current === idx) renderPreviewBlock(filesArea, label, data);
    } catch (err) {
        var errData = { ok: false, error: 'Preview failed: ' + (err.message || err) };
        previewState.cache[idx] = errData;
        if (previewState.current === idx) renderPreviewBlock(filesArea, label, errData);
    }
}

function renderPreviewBlock(filesArea, label, data) {
    var html;
    if (!data || !data.ok) {
        html = '<div style="font-weight:bold;color:#ffb3b3;font-size:13px;margin-bottom:6px;text-align:left;">'
             + '<i class="fa-solid fa-triangle-exclamation"></i> ' + label + '</div>'
             + '<div style="background:rgba(146,18,21,0.30);border:1px solid #FFA500;border-radius:8px;padding:10px 12px;'
             + 'color:#ffd9a0;font-size:12.5px;text-align:left;">⚠️ ' + previewEscapeHtml((data && data.error) || 'Preview failed.') + '</div>';
    } else {
        var note = buildPreviewNoteHTML(data);
        html = '<div style="font-weight:bold;color:#FFA500;font-size:13px;margin-bottom:6px;text-align:left;">'
             + '<i class="fa-solid fa-file-csv" style="color:#7CFC00;"></i> ' + label
             + (note ? ' <span style="color:#9fd0e6;font-weight:normal;font-size:11px;">— ' + note + '</span>' : '') + '</div>'
             + buildPreviewTableHTML(data);
    }
    filesArea.innerHTML = html;
}

async function _insertStaging(jobId, btn) {
    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Inserting…';
    try {
        var fd = new FormData();
        fd.append('ajax_action', 'approve_staging');
        fd.append('job_id', jobId);
        var resp = await fetch('admin_upload.php', { method: 'POST', body: fd });
        var data = await resp.json();
        if (!data.ok) {
            showPreviewNotice(data.error || 'Insert failed.');
            btn.disabled = false; btn.innerHTML = orig;
            return false;
        }
        var row = btn.closest('tr');
        if (row) {
            var st = row.querySelector('.pv-status'); if (st) { st.textContent = 'Inserted'; st.style.color = '#7CFC00'; }
            var nt = row.querySelector('.pv-note'); if (nt) nt.textContent = (data.inserted != null ? data.inserted + ' row(s) inserted' : 'Inserted to live');
        } else {
            var badge = document.getElementById('job-status');
            if (badge) { badge.textContent = 'Inserted'; badge.style.backgroundColor = '#28a745'; }
        }
        btn.outerHTML = '<span style="color:#7CFC00;font-weight:bold;"><i class="fa-solid fa-check"></i> Inserted'
            + (data.inserted != null ? ' (' + data.inserted + ')' : '') + '</span>';
        return true;
    } catch (err) {
        showPreviewNotice('Insert failed: ' + (err.message || err));
        btn.disabled = false; btn.innerHTML = orig;
        return false;
    }
}

function insertToLive(jobId, btn) {
    if (!confirm("Insert this file's staged data into the LIVE table?\nThis cannot be undone.")) return;
    _insertStaging(jobId, btn);
}

async function insertAllToLive(btn) {
    if (!confirm('Insert ALL staged files into the LIVE table?\nThis cannot be undone.')) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Inserting all…';
    var buttons = Array.prototype.slice.call(document.querySelectorAll('button[onclick^="insertToLive("]'));
    for (var i = 0; i < buttons.length; i++) {
        var m = /insertToLive\((\d+)/.exec(buttons[i].getAttribute('onclick') || '');
        if (m) { await _insertStaging(parseInt(m[1], 10), buttons[i]); }
    }
    btn.outerHTML = '<span style="color:#7CFC00;font-weight:bold;"><i class="fa-solid fa-check"></i> All inserted to live</span>';
}

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

let customParsedData = null;
let customContentFingerprint = '';
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

function handleCustomTableChange() {
    const tableSelect = document.getElementById("custom-table-select");
    const creatorSection = document.getElementById("custom-table-creator-section");
    if (tableSelect && tableSelect.value === "__CREATE__") {
        creatorSection.style.display = "block";
    } else {
        if (creatorSection) creatorSection.style.display = "none";
    }
}

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

async function computeCustomFingerprint(headers, rows) {
    const payload = JSON.stringify({headers: headers, rows: rows});
    const digest = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(payload));
    return Array.from(new Uint8Array(digest)).map(b => b.toString(16).padStart(2, '0')).join('');
}

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

        computeCustomFingerprint(headers, rows).then(function(fp) {
            customContentFingerprint = fp;
        });
        
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
    
    const sheetHeaders = customParsedData.headers;
    
    let headerHTML = "<thead><tr>";
    sheetHeaders.forEach(h => {
        headerHTML += `<th>${h}</th>`;
    });
    headerHTML += "<th style='background: rgba(255, 165, 0, 0.2); color:#FFA500;'>location</th>";
    headerHTML += "</tr></thead>";
    
    let bodyHTML = "<tbody>";
    customParsedData.rows.forEach(row => {
        bodyHTML += "<tr>";
        sheetHeaders.forEach(h => {
            const val = row[h];
            bodyHTML += `<td contenteditable="true">${val !== undefined ? val : ""}</td>`;
        });
        bodyHTML += `<td contenteditable="true" style='background: rgba(255, 165, 0, 0.05); font-weight:bold;'>${locVal}</td>`;
        bodyHTML += "</tr>";
    });
    bodyHTML += "</tbody>";
    
    tableEl.innerHTML = headerHTML + bodyHTML;
    
    document.getElementById("custom-preview-container").style.display = "block";
    document.getElementById("custom-preview-container").scrollIntoView({ behavior: 'smooth' });
}

function insertCustomData() {
    const tableEl = document.getElementById("custom-preview-table");
    const ths = Array.from(tableEl.querySelectorAll("thead th"));
    const tbody = tableEl.querySelector("tbody");
    if (!tbody || ths.length === 0) return;
    
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
    
    const batchSize = 100;
    let index = 0;
    let insertedTotal = 0;
    let skippedTotal = 0;
    let batchIndex = 0;
    
    function sendNextBatch() {
        if (index >= rows.length) {
            const finalize = new FormData();
            finalize.append("ajax_action", "finalize_custom_import");
            finalize.append("db", db);
            finalize.append("table_name", tbl);
            finalize.append("is_new_table", (tbl === newlyCreatedTableName) ? 'Yes' : 'No');
            finalize.append("file_name", customParsedData.name);
            finalize.append("file_size", customParsedData.size);
            finalize.append("content_fingerprint", customContentFingerprint || '');
            finalize.append("total_rows", String(rows.length));
            finalize.append("inserted_total", String(insertedTotal));
            finalize.append("skipped_total", String(skippedTotal));
            fetch("admin_upload.php", { method: "POST", body: finalize })
                .finally(showCustomSuccess);
            return;
        }

        function showCustomSuccess() {
            const skippedMsg = skippedTotal > 0 ? ` (${skippedTotal} duplicate rows skipped)` : '';
            printCustomLog(`[SUCCESS] Completed insertion of ${insertedTotal} rows successfully!${skippedMsg}`);
            setTimeout(() => {
                document.getElementById("custom-progress-section").style.display = "none";
                document.getElementById("custom-success-section").style.display = "block";
                document.getElementById("success-custom-db").innerText = db;
                document.getElementById("success-custom-table").innerText = tbl;
                document.getElementById("success-custom-file").innerText = customParsedData.name;
                document.getElementById("success-custom-location").innerText = document.getElementById("custom-location-input").value;
                document.getElementById("success-custom-rows").innerText = insertedTotal + " records";
            }, 800);
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
        formData.append("content_fingerprint", customContentFingerprint || '');
        formData.append("batch_index", String(batchIndex));
        formData.append("rows", JSON.stringify(batch));
        
        fetch("admin_upload.php", {
            method: "POST",
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                insertedTotal += data.inserted;
                skippedTotal += data.skipped || 0;
                index += batchSize;
                batchIndex++;
                
                const percent = Math.min(100, Math.round((index / rows.length) * 100));
                progressBar.style.width = percent + "%";
                progressBar.innerText = percent + "%";
                const skipNote = (data.skipped || 0) > 0 ? ` (${data.skipped} duplicates skipped)` : '';
                printCustomLog(`[BATCH] Mapped and inserted rows ${index - batch.length + 1} - ${Math.min(index, rows.length)} successfully.${skipNote}`);
                
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

<?php if ($step === 2 && !empty($results['pending']) && !empty($results['job_id'])): ?>
(function pollJob() {
    var jobId = <?= (int)$results['job_id'] ?>;
    var delay = 5000;
    var timer = null;
    var stopped = false;

    function stopPolling() {
        stopped = true;
        if (timer) clearTimeout(timer);
    }

    function scheduleNext() {
        if (stopped) return;
        timer = setTimeout(pollOnce, delay);
        delay = Math.min(Math.round(delay * 1.4), 15000);
    }

    function pollOnce() {
        fetch('admin_upload_job_status.php?job_id=' + jobId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.ok) {
                    scheduleNext();
                    return;
                }
                var status = (data.status || '').toLowerCase();
                var statusEl = document.getElementById('job-status');
                var bar = document.getElementById('progress-bar');
                if (data.progress_percent != null) {
                    if (bar) {
                        bar.style.width = data.progress_percent + '%';
                        bar.textContent = data.progress_percent + '%';
                    }
                }
                if (status === 'pending_verification') {
                    statusEl.textContent = 'Pending Verification';
                    statusEl.style.backgroundColor = '#ffc107';
                    stopPolling();
                    var msg = document.getElementById('pending-message');
                    if (msg) {
                        msg.innerHTML = 'Data loaded into staging. '
                            + (data.verify_url ? '<a href="' + data.verify_url + '" style="color:#FFD700;font-weight:bold;">Open Verification</a>' : 'Refresh Upload History.');
                    }
                } else if (status === 'completed' || status === 'validated') {
                    statusEl.textContent = 'Success';
                    statusEl.style.backgroundColor = '#28a745';
                    stopPolling();
                    var msg = document.getElementById('pending-message');
                    if (msg) msg.textContent = 'Processing completed.';
                } else if (status === 'failed') {
                    statusEl.textContent = 'Failed';
                    statusEl.style.backgroundColor = '#dc3545';
                    stopPolling();
                    var msgFail = document.getElementById('pending-message');
                    if (msgFail) msgFail.textContent = data.error_message || 'Processing failed.';
                } else {
                    scheduleNext();
                }
            })
            .catch(function() { scheduleNext(); });
    }

    pollOnce();
})();
<?php endif; ?>
</script>
<?php layout_end(); ?>
