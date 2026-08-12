<?php
/**
 * admin_activity_log.php
 * Admin-only page — User Activity Audit Log
 * UI matches the existing Hyderabad City Police project structure.
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_admin();

$db = audit_db();

// ── Fetch all unique users for dropdown ──
$users = $db->query("SELECT DISTINCT username FROM user_sessions ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

// ── Filters ──
$filter_user = trim($_POST['filter_user'] ?? $_GET['filter_user'] ?? '');
$filter_from = trim($_POST['filter_from'] ?? $_GET['filter_from'] ?? date('Y-m-d'));
$filter_to   = trim($_POST['filter_to']   ?? $_GET['filter_to']   ?? date('Y-m-d'));
$today = date('Y-m-d');
if ($filter_from > $today) $filter_from = $today;
if ($filter_to > $today) $filter_to = $today;
if ($filter_from && $filter_to && $filter_to < $filter_from) {
    $filter_to = $filter_from;
}

// ── Query ──
$logs     = [];
$sessions = [];

if ($filter_user !== '') {
    // Sessions
    $sessWhere  = "WHERE username = :u";
    $sessParams = [':u' => $filter_user];
    if ($filter_from) { $sessWhere .= " AND login_time >= :f"; $sessParams[':f'] = $filter_from . ' 00:00:00'; }
    if ($filter_to)   { $sessWhere .= " AND login_time <= :t"; $sessParams[':t'] = $filter_to   . ' 23:59:59'; }
    $st = $db->prepare("SELECT * FROM user_sessions $sessWhere ORDER BY login_time DESC");
    $st->execute($sessParams);
    $sessions = $st->fetchAll(PDO::FETCH_ASSOC);

    // Activity logs
    $logWhere  = "WHERE username = :u";
    $logParams = [':u' => $filter_user];
    if ($filter_from) { $logWhere .= " AND created_at >= :f"; $logParams[':f'] = $filter_from . ' 00:00:00'; }
    if ($filter_to)   { $logWhere .= " AND created_at <= :t"; $logParams[':t'] = $filter_to   . ' 23:59:59'; }
    $st = $db->prepare("SELECT * FROM user_activity_logs $logWhere ORDER BY created_at DESC");
    $st->execute($logParams);
    $logs = $st->fetchAll(PDO::FETCH_ASSOC);
}

// Helpers
function fmt_dt(?string $dt): string
{
    if (!$dt) return '—';
    try { return (new DateTime($dt))->format('Y-m-d H:i:s'); } catch(Exception $e) { return $dt; }
}
function fmt_dur(?int $sec): string
{
    if (!$sec || $sec <= 0) return '—';
    $h = intdiv($sec, 3600); $m = intdiv($sec % 3600, 60); $s = $sec % 60;
    $p = [];
    if ($h) $p[] = "{$h}h"; if ($m) $p[] = "{$m}m"; if ($s) $p[] = "{$s}s";
    return implode(' ', $p) ?: '< 1s';
}
?>
<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
layout_begin("User Activity");
cdat_sum_page_open('sum-admin-layout');

$userOptions = ['' => 'Select user'];
foreach ($users as $u) {
    $uname = (string) $u['username'];
    $userOptions[$uname] = $uname;
}
$userSelect = cdat_sum_searchable_select(
    'filter_user',
    'Select User',
    $userOptions,
    $filter_user,
    'Select user',
    true
);

$fieldsHtml = $userSelect
    . cdat_sum_field_date_native('filter_from', 'Date From', $filter_from)
    . cdat_sum_field_date_native('filter_to', 'Date To', $filter_to);

cdat_sum_search_card(
    'User Activity Log',
    'Filter sessions and activity by user and date range.',
    'admin_activity_log.php',
    $fieldsHtml,
    'BTN_SEARCH',
    'Search',
    'get'
);

if ($filter_user !== '') {
    $userLabel = strtoupper($filter_user);
    $hasSessions = !empty($sessions);
    $hasLogs = !empty($logs);

    if ($hasSessions) {
        cdat_sum_report_banner('Session History of User: ' . $userLabel);
        cdat_sum_generic_table_open(
            'Sessions',
            ['SESSION ID', 'USERNAME', 'LOGIN TIME', 'LOGOUT TIME', 'DURATION', 'IP ADDRESS', 'DEVICE'],
            'sessions_table',
            'sessions.csv',
            count($sessions)
        );
        foreach ($sessions as $sess) {
            cdat_sum_table_row([
                ['text' => (string) $sess['id'], 'class' => 'sum-cell-num'],
                $sess['username'],
                ['text' => fmt_dt($sess['login_time']), 'class' => 'sum-cell-date'],
                ['text' => fmt_dt($sess['logout_time']), 'class' => 'sum-cell-date'],
                fmt_dur($sess['session_duration'] ? (int) $sess['session_duration'] : null),
                $sess['ip_address'] ?? '—',
                $sess['device_info'] ?? '—',
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if ($hasLogs) {
        cdat_sum_report_banner('Activity Log of User: ' . $userLabel);
        cdat_sum_generic_table_open(
            'Activity Log',
            ['S.NO', 'SESSION ID', 'MODULE', 'ACTION', 'SEARCH DATA', 'TIMESTAMP'],
            'logs_table',
            'activity_log.csv',
            count($logs)
        );
        foreach ($logs as $i => $log) {
            $paramStr = '';
            if ($log['search_data']) {
                $data = json_decode($log['search_data'], true) ?? [];
                $parts = [];
                foreach ($data as $k => $v) {
                    if ($k === 'ip') continue;
                    $parts[] = strtoupper(str_replace('_', ' ', $k)) . ': ' . $v;
                }
                $paramStr = implode(' | ', $parts);
            }
            cdat_sum_table_row([
                ['text' => (string) ($i + 1), 'class' => 'sum-cell-num'],
                ['text' => (string) ($log['session_id'] ?? '—'), 'class' => 'sum-cell-num'],
                $log['module_name'],
                $log['action_type'],
                $paramStr !== '' ? $paramStr : '—',
                ['text' => fmt_dt($log['created_at']), 'class' => 'sum-cell-date'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if (!$hasSessions && !$hasLogs) {
        cdat_sum_empty_state('*** NO ACTIVITY FOUND FOR USER: ' . $userLabel . ' ***');
    }
}

cdat_sum_page_close();
layout_end();
