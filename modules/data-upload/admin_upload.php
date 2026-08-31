<?php

require_once CDAT_CONFIG . '/db_connect.php';
require_once dirname(__DIR__) . '/common/bootstrap.php';
/**
 * admin_upload.php
 * Administrative panel for CDR/SDR document uploads.
 * Contains both Section 1 (Legacy Standard Upload) and Section 2 (Custom Table Upload).
 */
require_once CDAT_COMMON . '/activity_logger.php';
require_once CDAT_UPLOAD . '/admin_upload_page.php';
audit_require_uploader();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' || isset($_POST['ajax_action'])) {
    require_once CDAT_COMMON . '/csrf.php';
    csrf_verify();
}

if (!defined('CDAT_UPLOAD_PAGE')) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_POST['ajax_action'])) {
        define('CDAT_UPLOAD_PAGE', 'cdr');
    } elseif (isset($_GET['view']) && $_GET['view'] === 'results') {
        define('CDAT_UPLOAD_PAGE', 'cdr');
    } else {
        header('Location: ' . cdat_upload_self_url('cdr'));
        exit;
    }
}

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
    require_once CDAT_UPLOAD . '/excel_converter.php';

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

    $script = CDAT_ROOT . '/cdr_import/cdr_preview.py';
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

if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
        
    $conn = get_cdat_pdo();
    if (!$conn) {
        echo json_encode(['ok' => false, 'error' => 'Database connection failed.']);
        exit;
    }
    
    $ajaxAction = $_POST['ajax_action'];
    
    if ($ajaxAction === 'get_tables') {
        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";
        $stmt = $conn->query($sql);
        $tables = [];
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
        
        $stmt = $conn->query($sql);
        if ($stmt) {
            audit_log('Data Upload', 'Create Table', ['table_name' => $tableName]);
            
            echo json_encode(['ok' => true, 'table_name' => $tableName]);
        } else {
            $errors = error_get_last();
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
            $dupStmt = $conn->prepare($dupSql);
    $dupStmt->execute([$phone, $imei, $callTime]);
            if ($dupStmt && $dupStmt->fetch(PDO::FETCH_ASSOC)) {
                $skippedCount++;
                continue;
            }
            
            $sql = "INSERT INTO $tableName (phone, imei, call_time, duration, location, network) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    $stmt->execute([$phone, $imei, $callTime, $duration, $location, $network]);
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

require_once CDAT_UPLOAD . '/cdr_upload_parser.php';
$config = require CDAT_UPLOAD . '/cdr_upload_config.php';
$modules = $config;
unset($modules['api']);

$step = 1;
$error = '';
$success = '';
// Links that belong with the success message. Kept apart from the text because
// the banner escapes $success -- markup embedded in it printed as literal
// "<a href=...>" on screen.
$successLinks = [];

$selectedModule = '';
$fileName = '';
$fileSize = 0;
$results = null;
$bulkResults = [];
$openAfterUpload = '';
$flashLogId = 0;
$flashJobId = 0;
$flashStatus = '';

$uploadFlash = $_SESSION['cdat_upload_flash'] ?? null;
if (is_array($uploadFlash)) {
    unset($_SESSION['cdat_upload_flash']);
    if (!empty($uploadFlash['error'])) {
        $error = (string) $uploadFlash['error'];
    }
    $openAfterUpload = (string) ($uploadFlash['open'] ?? '');
    $flashLogId = (int) ($uploadFlash['log_id'] ?? 0);
    $flashJobId = (int) ($uploadFlash['job_id'] ?? 0);
    $flashStatus = (string) ($uploadFlash['status'] ?? '');
}

// History "Action" used to land on leftover results UI. Send them back to the
// clean upload form; if the job is still pending, open Staging once.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['view'] ?? '') === 'results') {
    $viewLogId = (int) ($_GET['log_id'] ?? 0);
    if ($viewLogId > 0) {
        try {
            $vstmt = audit_db()->prepare('SELECT * FROM upload_activity_logs WHERE id = :id');
            $vstmt->execute([':id' => $viewLogId]);
            $vlog = $vstmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $vlog = null;
        }
        if ($vlog && stripos((string) ($vlog['module_name'] ?? ''), 'custom') === false) {
            $st = (string) ($vlog['upload_status'] ?? '');
            $_SESSION['cdat_upload_flash'] = [
                'open' => ($st === 'Pending Verification') ? 'staging' : '',
                'log_id' => $viewLogId,
                'job_id' => (int) ($vlog['document_job_id'] ?? 0),
                'status' => $st,
            ];
        }
    }
    header('Location: ' . cdat_upload_self_url(CDAT_UPLOAD_PAGE));
    exit;
}

if ($selectedModule === '' && in_array(CDAT_UPLOAD_PAGE, ['cdr', 'sdr'], true)) {
    $selectedModule = CDAT_UPLOAD_PAGE;
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
            // uploads/ is at the project root, not under controller/
            $uploadDir = CDAT_ROOT . '/uploads';
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
                            $success = 'Data loaded into staging. Insert it into the live table when you are ready.';
                        } elseif ($r['status'] === 'Processing') {
                            $success = 'Upload accepted and processing in the background. Check Upload History for status.';
                            $successLinks[] = ['url' => (function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history'), 'text' => 'Upload History'];
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

                if ($error === '' && $successCount > 0 && empty($_POST['ajax_upload'])) {
                    $first = $bulkResults[0];
                    $nextAction = (string) ($_POST['next_action'] ?? '');
                    $flash = [
                        'open' => '',
                        'log_id' => (int) ($first['log_id'] ?? 0),
                        'job_id' => (int) ($first['job_id'] ?? 0),
                        'status' => (string) ($first['status'] ?? ''),
                    ];
                    if ($nextAction === 'insert') {
                        $jobId = (int) ($first['job_id'] ?? 0);
                        if ($jobId > 0 && ($first['status'] ?? '') === 'Pending Verification') {
                            $ins = cdat_insert_staging_job($jobId);
                            if (empty($ins['ok'])) {
                                $flash['error'] = (string) ($ins['error'] ?? 'Insert failed.');
                                $flash['open'] = 'staging';
                                $_SESSION['cdat_upload_flash'] = $flash;
                            }
                        } elseif ($jobId <= 0) {
                            $flash['error'] = 'Upload finished but no staging job was created.';
                            $_SESSION['cdat_upload_flash'] = $flash;
                        }
                    } else {
                        if ($nextAction === 'staging') {
                            $flash['open'] = 'staging';
                        }
                        $_SESSION['cdat_upload_flash'] = $flash;
                    }
                    header('Location: ' . cdat_upload_self_url(CDAT_UPLOAD_PAGE));
                    exit;
                }
            }
        }

        if (!empty($_POST['ajax_upload'])) {
            header('Content-Type: application/json');
            $first = $bulkResults[0] ?? [];
            if ($error !== '') {
                echo json_encode(['ok' => false, 'error' => $error]);
                exit;
            }
            echo json_encode([
                'ok' => true,
                'log_id' => (int) ($first['log_id'] ?? 0),
                'job_id' => (int) ($first['job_id'] ?? 0),
                'status' => (string) ($first['status'] ?? ''),
                'staging_url' => cdat_upload_verify_url((int) ($first['log_id'] ?? 0)),
            ]);
            exit;
        }
    }
}
?>
<?php

require_once CDAT_CONFIG . '/db_connect.php';
$uploadPage = CDAT_UPLOAD_PAGE;
$uploadSelfUrl = cdat_upload_self_url($uploadPage);
$uploadPageTitle = match ($uploadPage) {
    'cdr' => 'CDR Upload',
    'sdr' => 'SDR Upload',
    'custom' => 'Custom Table Upload',
    default => 'Data Upload',
};
$uploadPageSubtitle = match ($uploadPage) {
    'cdr' => 'Upload operator CDR files for staging and verification.',
    'sdr' => 'Upload MSSQL backup (.bak) for subscriber directory restore.',
    'custom' => 'Import CSV or Excel into a custom database table.',
    default => 'Common Data Upload Framework',
};
$showStandardUpload = in_array($uploadPage, ['cdr', 'sdr'], true);
$showCustomUpload = ($uploadPage === 'custom');
$fixedModule = $showStandardUpload ? $uploadPage : '';
// The stylesheet and CDN tags below belong in <head>. Capture them and hand
// them to layout_begin() so they can stay written as plain HTML here.
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
ob_start();
?>

<!-- Load FontAwesome, SheetJS, and PapaParse -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
<script src="<?= htmlspecialchars(CDAT_ASSETS) ?>/js/sdr_resumable_upload.js" type="text/javascript"></script>

<style type="text/css">
.upload-wrapper h3 {
    color: #5b6b7c;
    margin-bottom: 12px;
}
.upload-wrapper p {
    color: #5b6b7c;
    margin-bottom: 12px;
}
</style>
<!-- Loaded last so it overrides the dark-background colours above, which are
     unreadable now that the page sits on the light application shell. -->
<link rel="stylesheet" href="<?= htmlspecialchars(CDAT_ASSETS) ?>/css/upload.css">
<?php

require_once CDAT_CONFIG . '/db_connect.php';
layout_begin($uploadPageTitle, $uploadPageSubtitle, ob_get_clean());
cdat_sum_page_open();
$historyUrl = (function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history') . '?type=' . ($uploadPage === 'custom' ? 'custom' : 'standard');
$uploadLogId = $flashLogId > 0 ? $flashLogId : (int) (is_array($results) ? ($results['log_id'] ?? 0) : 0);
$stagingUrl = cdat_upload_verify_url($uploadLogId > 0 ? $uploadLogId : null);
$formJobId = $flashJobId > 0 ? $flashJobId : (int) (is_array($results) ? ($results['job_id'] ?? 0) : 0);
$formCanInsert = ($flashStatus === 'Pending Verification' && $formJobId > 0)
    || (is_array($results)
        && (($results['status'] ?? '') === 'Pending Verification')
        && $formJobId > 0);
if ($openAfterUpload === '') {
    $openAfterUpload = in_array((string)($_POST['next_action'] ?? ''), ['staging', 'insert'], true)
        ? (string)$_POST['next_action']
        : '';
}
?>
        <?php 
require_once CDAT_CONFIG . '/db_connect.php';
if ($showStandardUpload): ?>
        <div class="tab-content active" id="tab-content-legacy">
            <div class="upload-wrapper">
                <?php 
require_once CDAT_CONFIG . '/db_connect.php';
if ($error !== ''): ?>
                  <div class="msg-container msg-error"><?= htmlspecialchars($error) ?></div>
                <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endif; ?>

                <?php
                  
require_once CDAT_CONFIG . '/db_connect.php';
$hideSuccessBanner = $success !== '' && is_array($results)
                      && (($results['status'] ?? '') === 'Pending Verification');
                ?>
                <?php 
require_once CDAT_CONFIG . '/db_connect.php';
if ($success !== '' && !$hideSuccessBanner): ?>
                  <div class="msg-container msg-success" id="upload-success-msg">
                    <?= htmlspecialchars($success) ?>
                    <?php 
require_once CDAT_CONFIG . '/db_connect.php';
foreach ($successLinks as $lnk): ?>
                      <a href="<?= htmlspecialchars($lnk['url'], ENT_QUOTES) ?>" class="msg-link"><?= htmlspecialchars($lnk['text']) ?></a>
                    <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endforeach; ?>
                  </div>
                <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endif; ?>

                <?php 
require_once CDAT_CONFIG . '/db_connect.php';
if ($step === 1): ?>
                  <?php 
require_once CDAT_CONFIG . '/db_connect.php';
if ($uploadPage === 'sdr'): ?>
                  <div class="staging-banner">
                    <strong>SDR backups</strong> (.bak, up to 700 GB) use <em>resumable chunked upload</em> — if interrupted, re-select the same file to continue.
                  </div>
                  <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endif; ?>
                  <div id="sdr-pending-banner" class="staging-banner" style="display:none; border-color:#FFA500; margin-bottom:12px;"></div>
                  <form action="<?= htmlspecialchars($uploadSelfUrl, ENT_QUOTES) ?>" method="post" enctype="multipart/form-data" id="standard-upload-form" class="upload-form upload-panel" onsubmit="return handleStandardUploadSubmit(event)">
                    <input type="hidden" name="action" value="upload_file" />
                    <input type="hidden" name="next_action" id="next-action" value="" />

                    <div class="upload-layout row g-3<?= $uploadPage === 'sdr' ? ' upload-layout--file-only' : '' ?>">
                      <?php 
require_once CDAT_CONFIG . '/db_connect.php';
if ($fixedModule === ''): ?>
                      <div class="form-group col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="module">Select Module</label>
                        <select class="form-select" name="module" id="module" required="required" onchange="updateModuleHint(this.value)">
                          <option value="">-- Choose Module --</option>
                          <?php 
require_once CDAT_CONFIG . '/db_connect.php';
foreach ($modules as $key => $conf): ?>
                            <option value="<?= htmlspecialchars($key) ?>"<?= ($selectedModule === $key ? ' selected="selected"' : '') ?>><?= htmlspecialchars($conf['name']) ?></option>
                          <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endforeach; ?>
                        </select>
                        <div id="module-hint" class="module-hint" style="display:none;"></div>
                      </div>
                      <?php 
require_once CDAT_CONFIG . '/db_connect.php';
else: ?>
                      <input type="hidden" name="module" id="module" value="<?= htmlspecialchars($fixedModule, ENT_QUOTES) ?>" />
                      <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endif; ?>

                      <div class="form-group col-12 col-md-6 col-lg-4<?= $uploadPage === 'cdr' ? '' : ' d-none' ?>" id="standard-network-group">
                        <label class="form-label" for="standard-network-select">Network <span class="req">*</span></label>
                        <select class="form-select" name="network" id="standard-network-select"<?= $uploadPage === 'cdr' ? ' required="required"' : '' ?>>
                          <option value="">Select network</option>
                          <option value="2">Airtel</option>
                          <option value="15">Jio</option>
                          <option value="12">VI</option>
                          <option value="4">BSNL</option>
                        </select>
                      </div>

                      <div class="form-group upload-field-file col-12">
                        <label class="form-label" for="cdr_file" id="file-label"><?= $uploadPage === 'sdr' ? 'Backup file' : 'CDR file' ?></label>
                        <input type="file" name="cdr_file[]" id="cdr_file"
                          accept="<?= $uploadPage === 'sdr' ? '.bak' : '.csv,.xls,.xlsx' ?>"
                          <?= $uploadPage === 'sdr' ? '' : 'multiple="multiple"' ?>
                          data-upload-hint="<?= $uploadPage === 'sdr' ? 'Drag & drop a .bak file, or browse' : 'CSV, XLS or XLSX — drag & drop, or browse' ?>" />
                      </div>
                    </div>

                    <div class="upload-actions d-flex flex-wrap gap-2 justify-content-end mt-4 pt-3 border-top" id="upload-form-actions">
                      <a href="<?= htmlspecialchars($historyUrl, ENT_QUOTES) ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
                      <button type="button" class="btn btn-outline-primary btn-sm" id="form-staging-btn" data-staging-url="<?= htmlspecialchars($stagingUrl, ENT_QUOTES) ?>"><i class="fa-solid fa-table-list"></i> Staging</button>
                      <button type="button" class="btn btn-primary btn-sm" id="form-insert-btn" data-job-id="<?= (int)$formJobId ?>"<?= $formCanInsert ? '' : ' data-needs-upload="1"' ?>><i class="fa-solid fa-database"></i> Insert to Live</button>
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
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="sdr-upload-pause-btn" onclick="pauseSdrUpload()">Pause</button>
                        <button type="button" class="btn btn-outline-danger btn-sm ms-2" id="sdr-upload-cancel-btn" onclick="cancelSdrUpload()">Cancel</button>
                      </div>
                    </div>
                  </div>

                  <!-- File preview table (shown after a file is chosen) -->
                  <div id="standard-preview-container" class="preview-card" style="display:none;">
                      <div class="preview-card__head">
                          <h3>Preview</h3>
                          <p>First rows after operator parsing. Edit in Staging after upload.</p>
                      </div>
                      <div id="standard-preview-summary" class="preview-card__summary" style="display:none;"></div>
                      <div id="standard-preview-pager" class="preview-card__pager d-none gap-1 flex-wrap" style=""></div>
                      <div id="standard-preview-files"></div>
                  </div>
                <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endif; ?>

            </div>
        </div>
        <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endif; ?>

        <?php 
require_once CDAT_CONFIG . '/db_connect.php';
if ($showCustomUpload): ?>
        <div class="tab-content active" id="tab-content-custom">
            <div class="upload-wrapper">
                
                <!-- Setup Wizard Section -->
                <div id="custom-wizard-setup">
                    <div class="wizard-card">
                        <div class="wizard-title"><i class="fa-solid fa-database"></i> 1. Database & Table Selection</div>
                        
                        <div class="form-row row g-3">
                            <div class="form-col col-12 col-md-6 col-lg-4">
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
                            
                            <div class="form-col col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="custom-table-select">Select Target Table</label>
                                <select id="custom-table-select" class="form-select" onchange="handleCustomTableChange()" disabled>
                                    <option value="">-- Choose Table --</option>
                                </select>
                            </div>

                            <div class="form-col col-12 col-md-6 col-lg-4">
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
                        <div id="custom-table-creator-section" class="mt-3 p-3 rounded-3 border bg-light-subtle" style="display: none;">
                            <div class="fw-semibold mb-2"><i class="fa-solid fa-plus-circle"></i> Create New Table with Predefined Columns</div>
                            
                            <div class="form-row row g-3 align-items-end">
                                <div class="form-col col-12 col-md-8">
                                    <label class="form-label" for="custom-new-table-name">New Table Name</label>
                                    <input type="text" id="custom-new-table-name" class="form-input" placeholder="e.g. suspect_walkin_details" />
                                </div>
                                <div class="form-col col-12 col-md-4">
                                    <button type="button" class="btn btn-primary w-100 justify-content-center" onclick="createCustomTable()"><i class="fa-solid fa-plus"></i> Create Table</button>
                                </div>
                            </div>
                            
                            <div class="mt-2 small text-secondary">
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

                    <div class="wizard-card mt-4">
                        <div class="wizard-title"><i class="fa-solid fa-file-excel"></i> 2. File Upload & Location</div>
                        
                        <!-- Drag & Drop Zone -->
                        <div class="upload-dropzone" onclick="document.getElementById('custom-file-input').click()" id="custom-dropzone">
                            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                            <h3 class="my-1">Drag & Drop or Click to Select File</h3>
                            <p class="small text-secondary mb-0">Supports CSV, XLS, XLSX formats</p>
                            <input type="file" id="custom-file-input" style="display:none;" accept=".csv, .xls, .xlsx" onchange="handleCustomFile(this.files)" />
                            
                            <div class="upload-filename" id="custom-file-details">
                               <i class="fa-solid fa-circle-check"></i> <span id="custom-filename">file.csv</span> (<span id="custom-filesize">0 KB</span>)
                            </div>
                        </div>

                        <!-- Location Input Field -->
                        <div class="form-group mt-3">
                            <label class="form-label" for="custom-location-input">Location Value <span class="text-danger">*</span></label>
                            <input type="text" id="custom-location-input" class="form-input" placeholder="e.g. Hyderabad, Madhapur PS, Cyberabad" />
                            <span class="d-block mt-1 small text-secondary">This value is mandatory and will be populated for all sheet records in the <strong>location</strong> column.</span>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                        <a href="<?= htmlspecialchars(function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history') ?>?type=custom" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-clock-rotate-left"></i> Upload History</a>
                        <button type="button" class="btn btn-primary btn-sm" onclick="generatePreviewGrid()"><i class="fa-solid fa-table-list"></i> Preview Data</button>
                    </div>

                    <!-- Editable Sheet Preview & Data Grid Section (Displayed directly on the same page) -->
                    <div id="custom-preview-container" class="mt-4" style="display:none;">
                        <div class="wizard-card">
                            <div class="wizard-title"><i class="fa-solid fa-edit"></i> 3. Preview & Edit Uploaded Sheet</div>
                            <p class="small text-secondary mb-3">
                               Below is the complete content of your uploaded sheet. You can double-click any cell to **edit its value** before inserting.
                            </p>
                            
                            <div class="preview-table-wrapper">
                                <table class="preview-table" id="custom-preview-table">
                                    <!-- Entire sheet columns and rows will load here -->
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-success" onclick="insertCustomData()"><i class="fa-solid fa-database"></i> Insert Mapped Data</button>
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
                <div id="custom-success-section" class="mt-4 text-start" style="display:none;">
                    <div class="msg-container msg-success fs-6"><i class="fa-solid fa-circle-check"></i> Data processing completed successfully!</div>
                    
                    <div class="wizard-card border-success-subtle bg-success-subtle" style="line-height: 1.8; font-size:13px;">
                       <h3 class="mt-0 pb-2 border-bottom text-success">Upload Summary Details</h3>
                       <strong>Uploaded by:</strong> <?= htmlspecialchars($_SESSION['audit_username'] ?? 'Admin') ?><br/>
                       <strong>Database:</strong> <span id="success-custom-db"></span><br/>
                       <strong>Target Table:</strong> <span id="success-custom-table"></span><br/>
                       <strong>Processed File:</strong> <span id="success-custom-file"></span><br/>
                       <strong>Location:</strong> <span id="success-custom-location" class="fw-bold text-warning-emphasis"></span><br/>
                       <strong>Total Records Inserted:</strong> <span id="success-custom-rows" class="fw-bold text-success"></span><br/>
                       <strong>Status:</strong> <span class="badge-success">Success</span>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                       <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetCustomWizard()"><i class="fa-solid fa-refresh"></i> Upload Another File</button>
                    </div>
                </div>
            </div>
        </div>
        <?php 
require_once CDAT_CONFIG . '/db_connect.php';
endif; ?>

<style>
    .upload-wrapper h3 {
        color: #5b6b7c;
        margin-bottom: 12px;
    }
    .upload-wrapper p {
        color: #5b6b7c;
        margin-bottom: 12px;
    }
    </style>
<script type="text/javascript">
// Initialize Navigation horizontal menu

var moduleConfig = <?= json_encode($modules, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
var apiConfig = <?= json_encode($config['api'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
var uploadEndpoint = <?= json_encode($uploadSelfUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
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
    ['form-staging-btn', 'form-insert-btn'].forEach(function (id) {
        var btn = document.getElementById(id);
        if (btn) btn.disabled = !!show;
    });
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
        var resp = await fetch(uploadEndpoint, { method: 'POST', body: fd });
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
    var fileInput = document.getElementById('cdr_file');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        event.preventDefault();
        alert('Please select a file first.');
        return false;
    }
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
                alert(file.name || ' is not a .bak file.');
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
                        sdrUploadHelpers.formatBytes(p.offset) + ' / ' + sdrUploadHelpers.formatBytes(p.total) + '  |  ' + speed + '  |  ETA ' + eta;
                }
            });

            await logSdrResumableUpload(result, file, sdrLogId);
            sdrLog('Completed: job #' + result.job_id);
        }
        refreshSdrPendingBanner();
        alert('SDR backup uploaded. Processing runs in the background — check Upload History for status.');
        window.location.href = <?= json_encode(function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history') ?> + '?type=standard';
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
    var networkSelect = document.getElementById('standard-network-select');
    if (netGroup) {
        netGroup.classList.toggle('d-none', moduleVal !== 'cdr');
    }
    if (networkSelect) {
        networkSelect.required = (moduleVal === 'cdr');
    }
    
    if (!moduleVal || !moduleConfig[moduleVal]) {
        if (hint) hint.style.display = 'none';
        return;
    }
    var conf = moduleConfig[moduleVal];
    if (hint) {
        hint.textContent = conf.description || '';
        hint.style.display = 'block';
    }
    fileInput.accept = conf.accept || '';
    if (moduleVal === 'sdr') {
        fileLabel.textContent = 'Backup file';
        fileInput.removeAttribute('multiple');
        fileInput.setAttribute('data-upload-hint', 'Drag & drop a .bak file, or browse');
    } else {
        fileLabel.textContent = 'CDR file';
        fileInput.setAttribute('multiple', 'multiple');
        fileInput.setAttribute('data-upload-hint', 'CSV, XLS or XLSX — drag & drop, or browse');
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
    var networkSelect = document.getElementById('standard-network-select');
    function maybePreview() {
        var isCdr = moduleSelect && moduleSelect.value === 'cdr';
        var hasFile = cdrFile && cdrFile.files && cdrFile.files.length > 0;
        if (isCdr && hasFile) {
            generateStandardPreview({ scroll: false });
        } else {
            var c = document.getElementById('standard-preview-container');
            if (c) c.style.display = 'none';
        }
    }
    if (cdrFile) cdrFile.addEventListener('change', maybePreview);
    if (networkSelect) networkSelect.addEventListener('change', maybePreview);
    if (moduleSelect) {
        if (moduleSelect.value) updateModuleHint(moduleSelect.value);
    }
    maybePreview();
    initStagingModal();
    initDirectUploadActions();
    var openNext = <?= json_encode($openAfterUpload) ?>;
    if (openNext === 'staging') {
        var stagingBtn = document.getElementById('form-staging-btn');
        var url = stagingBtn ? stagingBtn.getAttribute('data-staging-url') : '';
        if (url) openStagingModal(url);
    } else if (openNext === 'insert') {
        var insertBtn = document.getElementById('form-insert-btn');
        var jobId = insertBtn ? parseInt(insertBtn.getAttribute('data-job-id') || '0', 10) : 0;
        if (jobId > 0) insertToLive(jobId, insertBtn, true);
    }
});

function previewEscapeHtml(s) {
    return String(s).replace(/[&<>"]/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
}

function buildPreviewTableHTML(data) {
    var columns = data.columns || [];
    var rows = data.rows || [];
    var html = '<div class="preview-table-wrapper"><table class="preview-table"><thead><tr>';
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
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.55);display:flex;' + 'align-items:center;justify-content:center;z-index:99999;';
    var card = document.createElement('div');
    card.style.cssText = 'max-width:460px;width:90%;background:#fff;border-radius:12px;padding:26px 26px 22px;' + 'box-shadow:0 18px 50px rgba(0,0,0,0.55);text-align:center;';
    card.innerHTML =
        '<div style="font-size:38px;line-height:1;margin-bottom:12px;">⚠️</div>' + '<div style="font-size:16px;font-weight:bold;margin-bottom:10px;">Cannot preview this file</div>' + '<div style="font-size:13.5px;line-height:1.6;margin-bottom:22px;"></div>' + '<button type="button" class="btn btn-primary px-4">OK</button>';
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

function stagingUrlHasLog(url) {
    return /[?&]log_id=\d+/.test(url || '');
}

function embedStagingUrl(url) {
    if (!url) return url;
    return url + (url.indexOf('?') >= 0 ? '&' : '?') + 'embed=1';
}

function goToFreshUpload() {
    window.location.href = uploadEndpoint;
}

function closeStagingModal(reload) {
    var modal = document.getElementById('staging-modal');
    var frame = document.getElementById('staging-modal-frame');
    if (modal) modal.hidden = true;
    if (frame) frame.src = 'about:blank';
    document.body.style.overflow = '';
    if (reload) goToFreshUpload();
}

function openStagingModal(url) {
    if (!stagingUrlHasLog(url)) {
        alert('Upload a file first, then open Staging to review rows.');
        return;
    }
    var modal = document.getElementById('staging-modal');
    var frame = document.getElementById('staging-modal-frame');
    if (!modal || !frame) {
        window.location.href = url;
        return;
    }
    frame.src = embedStagingUrl(url);
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function initStagingModal() {
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-staging-url], .js-open-staging');
        if (btn && btn.id !== 'form-staging-btn') {
            e.preventDefault();
            openStagingModal(btn.getAttribute('data-staging-url') || btn.getAttribute('href'));
            return;
        }
        if (e.target.closest('[data-staging-close], #staging-modal-close')) {
            closeStagingModal(false);
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        var modal = document.getElementById('staging-modal');
        if (modal && !modal.hidden) closeStagingModal(false);
    });
    window.addEventListener('message', function(e) {
        if (e.data && e.data.type === 'cdat-staging-close') {
            closeStagingModal(!!e.data.reload);
        }
    });
}

function formHasSelectedFile() {
    var input = document.getElementById('cdr_file');
    return !!(input && input.files && input.files.length > 0);
}

function applyUploadResult(data) {
    var stagingBtn = document.getElementById('form-staging-btn');
    var insertBtn = document.getElementById('form-insert-btn');
    if (stagingBtn && data.staging_url) {
        stagingBtn.setAttribute('data-staging-url', data.staging_url);
    }
    if (insertBtn && data.job_id) {
        insertBtn.setAttribute('data-job-id', String(data.job_id));
        insertBtn.removeAttribute('data-needs-upload');
    }
}

async function uploadSelectedFile() {
    var form = document.getElementById('standard-upload-form');
    if (!form) throw new Error('Upload form not found.');
    var fd = new FormData(form);
    fd.set('action', 'upload_file');
    fd.set('ajax_upload', '1');
    fd.set('next_action', '');
    var resp = await fetch(uploadEndpoint, { method: 'POST', body: fd });
    var data = await resp.json();
    if (!data.ok) throw new Error(data.error || 'Upload failed.');
    applyUploadResult(data);
    return data;
}

function initDirectUploadActions() {
    var stagingBtn = document.getElementById('form-staging-btn');
    var insertBtn = document.getElementById('form-insert-btn');
    if (stagingBtn) {
        stagingBtn.addEventListener('click', function () {
            if (formHasSelectedFile()) {
                var orig = stagingBtn.innerHTML;
                stagingBtn.disabled = true;
                stagingBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Staging…';
                uploadSelectedFile().then(function (data) {
                    if (data.staging_url) openStagingModal(data.staging_url);
                }).catch(function (err) {
                    showPreviewNotice(err.message || String(err));
                }).finally(function () {
                    stagingBtn.disabled = false;
                    stagingBtn.innerHTML = orig;
                });
                return;
            }
            var url = stagingBtn.getAttribute('data-staging-url') || '';
            if (stagingUrlHasLog(url)) {
                openStagingModal(url);
                return;
            }
            alert('Select a network and file first.');
        });
    }
    if (insertBtn) {
        insertBtn.addEventListener('click', function () {
            var jobId = parseInt(insertBtn.getAttribute('data-job-id') || '0', 10);
            if (formHasSelectedFile()) {
                var orig = insertBtn.innerHTML;
                insertBtn.disabled = true;
                insertBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading…';
                uploadSelectedFile().then(function (data) {
                    var uploadedJob = parseInt(data.job_id || '0', 10);
                    if (uploadedJob > 0) insertToLive(uploadedJob, insertBtn, true);
                    else alert('Upload finished but no staging job was created.');
                }).catch(function (err) {
                    showPreviewNotice(err.message || String(err));
                    insertBtn.disabled = false;
                    insertBtn.innerHTML = orig;
                });
                return;
            }
            if (jobId > 0) {
                insertToLive(jobId, insertBtn);
                return;
            }
            alert('Select a network and file first.');
        });
    }
}

async function generateStandardPreview(opts) {
    var fileInput = document.getElementById("cdr_file");
    var moduleSelect = document.getElementById("module");
    if (!fileInput || fileInput.files.length === 0) {
        return;
    }
    if (moduleSelect && moduleSelect.value !== 'cdr') {
        return;
    }

    var networkSelect = document.getElementById('standard-network-select');
    var networkVal = networkSelect ? (networkSelect.value || '') : '';
    var container = document.getElementById('standard-preview-container');
    var summaryEl = document.getElementById('standard-preview-summary');
    var filesArea = document.getElementById('standard-preview-files');
    if (!container) return;

    if (!networkVal || networkVal === 'ALL') {
        container.style.display = 'block';
        if (summaryEl) {
            summaryEl.style.display = 'block';
            summaryEl.textContent = 'Select a network to preview this file.';
        }
        if (filesArea) filesArea.innerHTML = '';
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

    container.style.display = 'block';
    if (opts && opts.scroll) container.scrollIntoView({ behavior: 'smooth' });
    if (summaryEl) {
        summaryEl.style.display = 'block';
        summaryEl.innerHTML = '<strong>' + files.length + '</strong> file(s) selected' + (files.length > 1 ? ' — use the page buttons below to review each file.' : '.');
    }

    renderPreviewPager();
    await showPreviewPage(0);
}

function renderPreviewPager() {
    var pager = document.getElementById('standard-preview-pager');
    if (!pager) return;
    var n = previewState.files.length;
    if (n <= 1) { pager.classList.add('d-none'); pager.classList.remove('d-flex'); pager.innerHTML = ''; return; }
    pager.classList.remove('d-none'); pager.classList.add('d-flex');
    var html = '<button type="button" class="btn btn-outline-secondary btn-sm" data-pv="prev">&laquo; Prev</button>';
    for (var i = 0; i < n; i++) {
        var active = (i === previewState.current);
        html += '<button type="button" class="btn btn-sm ' + (active ? 'btn-primary' : 'btn-outline-secondary') + '" data-pv="' + i + '">' + (i + 1) + '</button>';
    }
    html += '<button type="button" class="btn btn-outline-secondary btn-sm" data-pv="next">Next &raquo;</button>';
    pager.innerHTML = html;
    pager.querySelectorAll('button').forEach(function(b) {
        b.onclick = function() {
            var v = b.getAttribute('data-pv');
            var target = previewState.current;
            if (v === 'prev') target = Math.max(0, previewState.current - 1);
            else if (v === 'next') target = Math.min(n - 1, previewState.current || 1);
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

    filesArea.innerHTML = '<div style="font-weight:bold;color:#FFA500;font-size:13px;text-align:left;">' + '<i class="fa-solid fa-file-csv"></i> ' + label + ' <span style="color:#9fd0e6;font-weight:normal;"><i class="fa-solid fa-spinner fa-spin"></i> loading…</span></div>';

    try {
        var fd = new FormData();
        fd.append('ajax_action', 'preview_cdr');
        fd.append('preview_file', file);
        fd.append('network', previewState.network);
        var resp = await fetch(uploadEndpoint, { method: 'POST', body: fd });
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
        html = '<div style="font-weight:bold;color:#ffb3b3;font-size:13px;margin-bottom:6px;text-align:left;">' + '<i class="fa-solid fa-triangle-exclamation"></i> ' + label + '</div>' + '<div style="background:rgba(146,18,21,0.30);border:1px solid #FFA500;border-radius:8px;padding:10px 12px;' + 'color:#ffd9a0;font-size:12.5px;text-align:left;">⚠️ ' + previewEscapeHtml((data && data.error) || 'Preview failed.') + '</div>';
    } else {
        var note = buildPreviewNoteHTML(data);
        html = '<div style="font-weight:bold;color:#FFA500;font-size:13px;margin-bottom:6px;text-align:left;">' + '<i class="fa-solid fa-file-csv" style="color:#7CFC00;"></i> ' + label + (note ? ' <span style="color:#9fd0e6;font-weight:normal;font-size:11px;">— ' + note + '</span>' : '') + '</div>' + buildPreviewTableHTML(data);
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
        var resp = await fetch(uploadEndpoint, { method: 'POST', body: fd });
        var data = await resp.json();
        if (!data.ok) {
            showPreviewNotice(data.error || 'Insert failed.');
            btn.disabled = false; btn.innerHTML = orig;
            return false;
        }
        var row = btn.closest('tr');
        if (row) {
            var st = row.querySelector('.pv-status'); if (st) { st.textContent = 'Inserted'; st.style.color = '#7CFC00'; }
            var nt = row.querySelector('.pv-note'); if (nt) nt.textContent = (data.inserted != null ? data.inserted || ' row(s) inserted' : 'Inserted to live');
        } else {
            var badge = document.getElementById('job-status');
            if (badge) { badge.textContent = 'Inserted'; badge.style.backgroundColor = '#28a745'; }
        }
        btn.outerHTML = '<span style="color:#7CFC00;font-weight:bold;"><i class="fa-solid fa-check"></i> Inserted' + (data.inserted != null ? ' (' + data.inserted + ')' : '') + '</span>';
        goToFreshUpload();
        return true;
    } catch (err) {
        showPreviewNotice('Insert failed: ' + (err.message || err));
        btn.disabled = false; btn.innerHTML = orig;
        return false;
    }
}

function insertToLive(jobId, btn, skipConfirm) {
    if (!skipConfirm && !confirm("Insert this file's staged data into the LIVE table?\nThis cannot be undone.")) return;
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
    goToFreshUpload();
}

function resetCustomWizard() {
    window.location.href = uploadEndpoint;
}

let customParsedData = null;
let customContentFingerprint = '';
let newlyCreatedTableName = '';

<?php 
require_once CDAT_CONFIG . '/db_connect.php';
if ($showCustomUpload): ?>
setupCustomDragAndDrop();
<?php 
require_once CDAT_CONFIG . '/db_connect.php';
endif; ?>

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
    
    fetch(uploadEndpoint, {
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
    
    fetch(uploadEndpoint, {
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
    
    tableEl.innerHTML = headerHTML || bodyHTML;
    
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
            const phone = phoneIdx < tds.length ? tdsphoneIdx.innerText.trim() : "";
            const imei = imeiIdx < tds.length ? tdsimeiIdx.innerText.trim() : "";
            const callTime = callTimeIdx < tds.length ? tdscallTimeIdx.innerText.trim() : "";
            const duration = durationIdx < tds.length ? tdsdurationIdx.innerText.trim() : "";
            const location = locationIdx < tds.length ? tdslocationIdx.innerText.trim() : "";
            
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
    
    printCustomLog(`INFO Initializing transaction pipeline for table [${tbl}]...`);
    printCustomLog(`INFO Preparing to insert ${rows.length} rows...`);
    
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
            fetch(uploadEndpoint, { method: "POST", body: finalize })
                .finally(showCustomSuccess);
            return;
        }

        function showCustomSuccess() {
            const skippedMsg = skippedTotal > 0 ? ` (${skippedTotal} duplicate rows skipped)` : '';
            printCustomLog(`SUCCESS Completed insertion of ${insertedTotal} rows successfully!${skippedMsg}`);
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
        
        const batch = rows.slice(index, index || batchSize);
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
        
        fetch(uploadEndpoint, {
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
                printCustomLog(`BATCH Mapped and inserted rows ${index - batch.length || 1} - ${Math.min(index, rows.length)} successfully.${skipNote}`);
                
                setTimeout(sendNextBatch, 300); // 300ms pause for visual updates
            } else {
                printCustomLog(`ERROR Batch insertion failed: ${data.error}`);
                alert("Insertion failed. Check console for error details.");
            }
        })
        .catch(err => {
            printCustomLog(`ERROR Fetch error: ${err.message}`);
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
</script>
<div id="staging-modal" class="staging-modal" hidden>
  <div class="staging-modal__backdrop" data-staging-close="1"></div>
  <div class="staging-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="staging-modal-title">
    <div class="staging-modal__head">
      <h2 id="staging-modal-title">Staging Preview &amp; Edit</h2>
      <div class="staging-modal__actions">
        <button type="button" class="staging-modal__close btn btn-outline-secondary btn-sm" id="staging-modal-close">Close</button>
      </div>
    </div>
    <iframe id="staging-modal-frame" class="staging-modal__frame" title="Staging preview"></iframe>
  </div>
</div>
<?php

require_once CDAT_CONFIG . '/db_connect.php';
cdat_sum_page_close();
layout_end();
?>
