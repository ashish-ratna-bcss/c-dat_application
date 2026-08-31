<?php
require_once __DIR__ . '/../common/bootstrap.php';
/**
 * admin_activity_log.php
 * Admin-only page — User Activity Audit Log
 * UI matches the existing Hyderabad City Police project structure.
 */
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_admin();

function cdat_audit_row_val(array $row, string $key): string
{
    $upper = strtoupper($key);
    return (string) ($row[$key] ?? $row[$upper] ?? '');
}

$db = audit_db();

// Users from sessions, activity logs, and login accounts (PDO returns uppercase keys).
$userStmt = $db->query("
    SELECT DISTINCT username FROM (
        SELECT username FROM user_sessions WHERE username IS NOT NULL AND BTRIM(username) <> ''
        UNION
        SELECT username FROM user_activity_logs WHERE username IS NOT NULL AND BTRIM(username) <> ''
        UNION
        SELECT username FROM logins WHERE username IS NOT NULL AND BTRIM(username) <> ''
    ) AS u
    ORDER BY username
");
$users = $userStmt ? ($userStmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

// ── Filters ──
$filter_user = trim($_POST['filter_user'] ?? $_GET['filter_user'] ?? '');
$filter_from = trim($_POST['filter_from'] ?? $_GET['filter_from'] ?? date('Y-m-d'));
$filter_to   = trim($_POST['filter_to']   ?? $_GET['filter_to']   ?? date('Y-m-d'));
$today = date('Y-m-d');
if ($filter_from > $today) {
    $filter_from = $today;
}
if ($filter_to > $today) {
    $filter_to = $today;
}
if ($filter_from && $filter_to && $filter_to < $filter_from) {
    $filter_to = $filter_from;
}

// ── Query ──
$logs     = [];
$sessions = [];

if ($filter_user !== '') {
    $sessWhere  = 'WHERE username = :u';
    $sessParams = [':u' => $filter_user];
    if ($filter_from) {
        $sessWhere .= ' AND created_at >= :f';
        $sessParams[':f'] = $filter_from . ' 00:00:00';
    }
    if ($filter_to) {
        $sessWhere .= ' AND created_at <= :t';
        $sessParams[':t'] = $filter_to . ' 23:59:59';
    }
    $st = $db->prepare("
        SELECT
            session_id,
            username,
            role,
            ip_address,
            created_at,
            last_active_at,
            expires_at
        FROM user_sessions
        $sessWhere
        ORDER BY created_at DESC
    ");
    $st->execute($sessParams);
    $sessions = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $logWhere  = 'WHERE username = :u';
    $logParams = [':u' => $filter_user];
    if ($filter_from) {
        $logWhere .= ' AND created_at >= :f';
        $logParams[':f'] = $filter_from . ' 00:00:00';
    }
    if ($filter_to) {
        $logWhere .= ' AND created_at <= :t';
        $logParams[':t'] = $filter_to . ' 23:59:59';
    }
    $st = $db->prepare("
        SELECT id, username, module, action, detail, ip_address, created_at
        FROM user_activity_logs
        $logWhere
        ORDER BY created_at DESC
    ");
    $st->execute($logParams);
    $logs = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// Helpers
function fmt_dt(?string $dt): string
{
    if (!$dt) {
        return '—';
    }
    try {
        return (new DateTime($dt))->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        return $dt;
    }
}

function fmt_dur(?int $sec): string
{
    if (!$sec || $sec <= 0) {
        return '—';
    }
    $h = intdiv($sec, 3600);
    $m = intdiv($sec % 3600, 60);
    $s = $sec % 60;
    $p = [];
    if ($h) {
        $p[] = "{$h}h";
    }
    if ($m) {
        $p[] = "{$m}m";
    }
    if ($s) {
        $p[] = "{$s}s";
    }
    return implode(' ', $p) ?: '< 1s';
}

function fmt_session_id(string $sessionId): string
{
    if ($sessionId === '') {
        return '—';
    }
    if (strlen($sessionId) <= 12) {
        return $sessionId;
    }
    return substr($sessionId, 0, 8) . '…';
}

function cdat_session_logout_time(array $sess): ?string
{
    $expires = cdat_audit_row_val($sess, 'expires_at');
    $lastActive = cdat_audit_row_val($sess, 'last_active_at');
    if ($expires === '' || $lastActive === '') {
        return null;
    }
    try {
        $expiresTs = (new DateTime($expires))->getTimestamp();
        $lastActiveTs = (new DateTime($lastActive))->getTimestamp();
    } catch (Exception $e) {
        return null;
    }
    if ($expiresTs <= $lastActiveTs + 120) {
        return $expires;
    }
    return null;
}

function cdat_session_duration(array $sess): ?int
{
    $created = cdat_audit_row_val($sess, 'created_at');
    if ($created === '') {
        return null;
    }
    $logout = cdat_session_logout_time($sess);
    $end = $logout ?: cdat_audit_row_val($sess, 'last_active_at');
    if ($end === '') {
        $end = date('Y-m-d H:i:s');
    }
    try {
        $startTs = (new DateTime($created))->getTimestamp();
        $endTs = (new DateTime($end))->getTimestamp();
    } catch (Exception $e) {
        return null;
    }
    return max(0, $endTs - $startTs);
}
?>
<?php
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
layout_begin('User Activity');
cdat_sum_page_open('sum-admin-layout');

$userOptions = ['' => 'Select user'];
foreach ($users as $u) {
    $uname = cdat_audit_row_val($u, 'username');
    if ($uname !== '') {
        $userOptions[$uname] = $uname;
    }
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
    '/administration/user-activity',
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
            ['SESSION ID', 'USERNAME', 'LOGIN TIME', 'LOGOUT TIME', 'DURATION', 'IP ADDRESS', 'ROLE'],
            'sessions_table',
            'sessions.csv',
            count($sessions)
        );
        foreach ($sessions as $sess) {
            cdat_sum_table_row([
                ['text' => fmt_session_id(cdat_audit_row_val($sess, 'session_id')), 'class' => 'sum-cell-num'],
                cdat_audit_row_val($sess, 'username'),
                ['text' => fmt_dt(cdat_audit_row_val($sess, 'created_at')), 'class' => 'sum-cell-date'],
                ['text' => fmt_dt(cdat_session_logout_time($sess)), 'class' => 'sum-cell-date'],
                fmt_dur(cdat_session_duration($sess)),
                cdat_audit_row_val($sess, 'ip_address') ?: '—',
                cdat_audit_row_val($sess, 'role') ?: '—',
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if ($hasLogs) {
        cdat_sum_report_banner('Activity Log of User: ' . $userLabel);
        cdat_sum_generic_table_open(
            'Activity Log',
            ['S.NO', 'MODULE', 'ACTION', 'DETAIL', 'IP ADDRESS', 'TIMESTAMP'],
            'logs_table',
            'activity_log.csv',
            count($logs)
        );
        foreach ($logs as $i => $log) {
            $paramStr = '';
            $detail = cdat_audit_row_val($log, 'detail');
            if ($detail !== '') {
                $data = json_decode($detail, true);
                if (is_array($data)) {
                    $parts = [];
                    foreach ($data as $k => $v) {
                        if ($k === 'ip') {
                            continue;
                        }
                        $parts[] = strtoupper(str_replace('_', ' ', (string) $k)) . ': ' . $v;
                    }
                    $paramStr = implode(' | ', $parts);
                } else {
                    $paramStr = $detail;
                }
            }
            cdat_sum_table_row([
                ['text' => (string) ($i + 1), 'class' => 'sum-cell-num'],
                cdat_audit_row_val($log, 'module'),
                cdat_audit_row_val($log, 'action'),
                $paramStr !== '' ? $paramStr : '—',
                cdat_audit_row_val($log, 'ip_address') ?: '—',
                ['text' => fmt_dt(cdat_audit_row_val($log, 'created_at')), 'class' => 'sum-cell-date'],
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
