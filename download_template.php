<?php
/**
 * download_template.php
 * Generates and downloads a CSV template file with friendly headers and a sample row for the chosen CDR module.
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_admin();

$configs = require __DIR__ . '/cdr_upload_config.php';
$moduleKey = $_GET['module'] ?? '';

if (!isset($configs[$moduleKey])) {
    die("Invalid module specified.");
}

$config = $configs[$moduleKey];
$columns = $config['columns'];

// Prepare CSV columns (headers) and mock values for one sample row
$headers = [];
$sampleRow = [];

foreach ($columns as $dbCol => $spec) {
    // We will use the database column name as the header for easy auto-mapping
    $headers[] = $dbCol;
    
    // Generate realistic sample value based on the field name and validation type
    $type = $spec['type'];
    $val = '';
    
    if ($type === 'phone') {
        $val = '9876543210';
    } elseif ($type === 'integer') {
        $val = '120';
    } elseif ($type === 'datetime') {
        $val = '2026-06-24 10:15:00';
    } elseif ($type === 'date') {
        $val = '2026-06-24';
    } else {
        // String cases based on standard field names
        switch ($dbCol) {
            case 'imei':
            case 'imeinumber':
                $val = '123456789012345';
                break;
            case 'cell_id':
            case 'celltowerid':
                $val = '404-45-12345';
                break;
            case 'location':
            case 'siteaddress':
            case 'fulladdress':
                $val = 'Road No 12, Banjara Hills, Hyderabad';
                break;
            case 'operator':
                $val = 'Airtel';
                break;
            case 'call_type':
                $val = 'Incoming';
                break;
            case 'incoming':
                $val = '1';
                break;
            case 'message':
                $val = 'Hello, this is a sample test message.';
                break;
            case 'name':
            case 'fullname':
                $val = 'John Doe';
                break;
            case 'nickname':
                $val = 'Suspect Alias';
                break;
            case 'status':
                $val = 'Active';
                break;
            default:
                $val = 'Sample Value';
                break;
        }
    }
    
    $sampleRow[] = $val;
}

// Clean buffer
ob_clean();

// Set download response headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $moduleKey . '_template.csv"');

// Output CSV stream
$output = fopen('php://output', 'w');
fputcsv($output, $headers);
fputcsv($output, $sampleRow);
fclose($output);
exit();
