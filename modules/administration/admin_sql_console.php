<?php
require_once __DIR__ . '/../common/bootstrap.php';
/**
 * admin_sql_console.php
 * Admin-only page — PostgreSQL SQL Query Console
 * UI matches the existing Hyderabad City Police project structure.
 */
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_admin(); // Restrict to admin-only

$db = audit_db();

function cdat_sql_console_begin_query(PDO $db): void
{
    set_time_limit(0);
    $db->exec('SET statement_timeout = 0');
}

/** Read-only guard: SELECT / WITH only, no writes or multiple statements. */
function cdat_sql_console_validate(string $sql): string
{
    $sql = trim($sql);
    if ($sql === '') {
        throw new Exception('Query is empty.');
    }
    if (!preg_match('/^(select|with)\b/is', $sql)) {
        throw new Exception('Only read-only SELECT queries are allowed.');
    }
    $semicolon_pos = strpos($sql, ';');
    if ($semicolon_pos !== false && $semicolon_pos < strlen($sql) - 1) {
        throw new Exception('Multiple SQL statements are blocked.');
    }
    if (preg_match('/\b(insert|update|delete|drop|truncate|alter|create|grant|revoke)\b/i', $sql)) {
        throw new Exception('Only read-only SELECT queries are allowed.');
    }

    return rtrim($sql, ';');
}

function cdat_sql_console_log_query(PDO $db, string $query, float $execution_time, int $row_count): void
{
    $stmt = $db->prepare(
        'INSERT INTO admin_query_logs (username, query_text, duration_ms, row_count)
         VALUES (:uname, :q, :duration_ms, :row_count)'
    );
    $stmt->execute([
        ':uname'       => $_SESSION['audit_username'] ?? 'unknown',
        ':q'           => $query,
        ':duration_ms' => (int) round($execution_time * 1000),
        ':row_count'   => $row_count,
    ]);
}

/** @return list<array<string,mixed>> */
function cdat_sql_console_recent_queries(PDO $db, int $limit = 10): array
{
    $stmt = $db->query(
        'SELECT username, query_text, duration_ms, row_count, executed_at
         FROM admin_query_logs
         ORDER BY executed_at DESC
         LIMIT ' . max(1, min($limit, 50))
    );
    if ($stmt === false) {
        return [];
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return is_array($rows) ? $rows : [];
}

// ── Handling CSV / Excel Export ──
if (isset($_POST['export']) && isset($_POST['sql_query'])) {
    $raw_query = $_POST['sql_query'];
    $export_type = $_POST['export_type'] ?? 'csv'; // 'csv' or 'excel'
    
    try {
        $exec_query = cdat_sql_console_validate($raw_query);
        
        cdat_sql_console_begin_query($db);
        $stmt = $db->query($exec_query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        audit_log('SQL Query Console', 'Export ' . strtoupper($export_type), [
            'query' => $exec_query,
            'rows_count' => count($rows),
        ]);
        
        if ($export_type === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="query_export_' . time() . '.xls"');
            
            echo '<table border="1">';
            if (!empty($rows)) {
                echo '<tr>';
                foreach (array_keys($rows[0]) as $col) {
                    echo '<th bgcolor="#921215"><font color="#FFFFFF">' . htmlspecialchars($col) . '</font></th>';
                }
                echo '</tr>';
                foreach ($rows as $row) {
                    echo '<tr>';
                    foreach ($row as $val) {
                        echo '<td>' . htmlspecialchars($val ?? '') . '</td>';
                    }
                    echo '</tr>';
                }
            } else {
                echo '<tr><td>No records found.</td></tr>';
            }
            echo '</table>';
        } else {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="query_export_' . time() . '.csv"');
            
            $output = fopen('php://output', 'w');
            if (!empty($rows)) {
                fputcsv($output, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($output, $row);
                }
            } else {
                fputcsv($output, ['No records found']);
            }
            fclose($output);
        }
        exit;
    } catch (Throwable $e) {
        die("Export Error: " . $e->getMessage());
    }
}

// ── Query Execution for Page View ──
$query = isset($_POST['sql_query']) ? trim($_POST['sql_query']) : '';
$error = '';
$columns = [];
$results = [];
$total_rows = 0;
$execution_time = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['BTN_EXECUTE']) && $query !== '') {
    $start_time = microtime(true);
    try {
        $exec_query = cdat_sql_console_validate($query);
        
        cdat_sql_console_begin_query($db);
        $stmt = $db->query($exec_query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_rows = count($results);
        
        if ($total_rows > 0) {
            $columns = array_keys($results[0]);
        }
        
        $execution_time = microtime(true) - $start_time;
        
        audit_log('SQL Query Console', 'Execute Query', [
            'query' => $exec_query,
            'duration' => round($execution_time, 4) . 's',
            'rows_count' => $total_rows,
        ]);
        
        cdat_sql_console_log_query($db, $exec_query, $execution_time, $total_rows);
        
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Fetch query history (last 10 records)
$history = cdat_sql_console_recent_queries($db);
?>
<?php
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('SQL Query Console');
cdat_sum_page_open('sum-admin-layout');
?>
<div class="row g-4">
  <div class="col-12 col-lg-9">
    <section class="sum-console-panel" aria-label="SQL Query Console">
      <h2 class="sum-console-panel__title">SQL Query Console (PostgreSQL — read-only)</h2>
      <p class="sum-console-panel__desc mb-2">Any SELECT query is allowed. Writes (INSERT/UPDATE/DELETE/DDL) are blocked for safety.</p>
      <form id="sqlForm" name="sqlForm" method="post" action="" class="no-ajax" data-no-ajax>
        <textarea id="sql_query" name="sql_query" class="sum-console-editor form-control font-monospace" placeholder="Read-only: SELECT * FROM cdatpcsuspect WHERE phone = '7569422355'"><?= htmlspecialchars($query) ?></textarea>
        <div class="sum-console-actions">
          <input type="submit" name="BTN_EXECUTE" id="BTN_EXECUTE" value="Execute Query" class="sum-console-btn btn btn-primary btn-sm" />
          <input type="button" value="Clear" class="sum-console-btn sum-console-btn--secondary btn btn-secondary btn-sm" onclick="document.getElementById('sql_query').value='';" />
          <input type="button" value="Copy Query" class="sum-console-btn sum-console-btn--secondary btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('sql_query').value); alert('Query copied to clipboard!');" />
        </div>
      </form>
    </section>

    <?php if ($error !== ''): ?>
      <?php cdat_sum_status_message('Error: ' . $error, false); ?>
    <?php endif; ?>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '' && isset($_POST['BTN_EXECUTE'])): ?>
      <div class="sum-console-actions d-flex flex-wrap gap-2 justify-content-end mb-3">
        <form method="post" action="" class="no-ajax" data-no-ajax>
          <input type="hidden" name="sql_query" value="<?= htmlspecialchars($query) ?>" />
          <input type="hidden" name="export" value="1" />
          <input type="hidden" name="export_type" value="csv" />
          <input type="submit" value="Export CSV" class="btn btn-primary btn-sm" />
        </form>
        <form method="post" action="" class="no-ajax" data-no-ajax>
          <input type="hidden" name="sql_query" value="<?= htmlspecialchars($query) ?>" />
          <input type="hidden" name="export" value="1" />
          <input type="hidden" name="export_type" value="excel" />
          <input type="submit" value="Export Excel" class="btn btn-outline-secondary btn-sm" />
        </form>
      </div>

      <?php if ($total_rows > 0): ?>
        <?php
        $panelTitle = 'Results: ' . $total_rows . ' Rows | Execution Time: ' . round($execution_time, 4) . ' Seconds';
        cdat_sum_generic_table_open($panelTitle, $columns, 'sql_console_results', 'query_export.csv', $total_rows);
        foreach ($results as $row) {
            $cells = [];
            foreach ($columns as $col) {
                $cells[] = $row[$col] ?? '—';
            }
            cdat_sum_table_row($cells);
        }
        cdat_sum_generic_table_close();
        ?>
      <?php else: ?>
        <?php cdat_sum_empty_state('No records returned.'); ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <aside class="col-12 col-lg-3">
    <section class="sum-console-panel" aria-label="Recent Queries">
      <h2 class="sum-console-panel__title">Recent Queries</h2>
      <?php if (!empty($history)): ?>
        <?php foreach ($history as $h): ?>
          <div class="sum-history-item" onclick="document.getElementById('sql_query').value = this.getAttribute('data-query');" data-query="<?= htmlspecialchars($h['query_text'] ?? $h['QUERY_TEXT'] ?? '') ?>" title="Click to copy query to editor">
            <b><?= htmlspecialchars($h['username'] ?? $h['USERNAME'] ?? 'unknown') ?></b> (<?= round(((float) ($h['duration_ms'] ?? $h['DURATION_MS'] ?? 0)) / 1000, 3) ?>s)<br/>
            <span><?= date('d-m-Y h:i A', strtotime((string) ($h['executed_at'] ?? $h['EXECUTED_AT'] ?? 'now'))) ?></span>
            <?php $qtext = (string) ($h['query_text'] ?? $h['QUERY_TEXT'] ?? ''); ?>
            <code><?= htmlspecialchars(substr($qtext, 0, 80)) ?><?= strlen($qtext) > 80 ? '...' : '' ?></code>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="sum-console-panel__empty">No queries run yet.</p>
      <?php endif; ?>
    </section>
  </aside>
</div>
<?php
cdat_sum_page_close();
layout_end();
?>
