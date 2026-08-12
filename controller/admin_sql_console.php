<?php
/**
 * admin_sql_console.php
 * Admin-only page — PostgreSQL SQL Query Console
 * UI matches the existing Hyderabad City Police project structure.
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_admin(); // Restrict to admin-only

$db = audit_db();

// ── Handling CSV / Excel Export ──
if (isset($_POST['export']) && isset($_POST['sql_query'])) {
    $raw_query = $_POST['sql_query'];
    $export_type = $_POST['export_type'] ?? 'csv'; // 'csv' or 'excel'
    
    try {
        $clean_query = trim($raw_query);
        
        // 1. Basic validation
        if (!preg_match('/^select\b/i', $clean_query)) {
            throw new Exception("Only SELECT queries are allowed.");
        }
        
        // 2. Block multiple statements
        $semicolon_pos = strpos($clean_query, ';');
        if ($semicolon_pos !== false && $semicolon_pos < (strlen($clean_query) - 1)) {
            throw new Exception("Multiple SQL statements are blocked.");
        }
        
        // 3. Block DDL/DML keywords
        if (preg_match('/\b(insert|update|delete|drop|truncate|alter|create|grant|revoke)\b/i', $clean_query)) {
            throw new Exception("Only SELECT queries are allowed. DML/DDL commands are blocked.");
        }
        
        // Strip trailing semicolon and wrap query to enforce maximum 1000 records
        $exec_query = rtrim($clean_query, ';');
        $wrapped_query = "SELECT * FROM ($exec_query) AS query_run LIMIT 1000";
        
        $stmt = $db->query($wrapped_query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log to User Activity Trail
        audit_log('SQL Query Console', 'Export ' . strtoupper($export_type), [
            'query' => $clean_query,
            'rows_count' => count($rows)
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
$limit_warning = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['BTN_EXECUTE']) && $query !== '') {
    $start_time = microtime(true);
    try {
        // 1. Basic validation
        if (!preg_match('/^select\b/i', $query)) {
            throw new Exception("Only SELECT queries are allowed.");
        }
        
        // 2. Block multiple statements
        $semicolon_pos = strpos($query, ';');
        if ($semicolon_pos !== false && $semicolon_pos < (strlen($query) - 1)) {
            throw new Exception("Multiple SQL statements are blocked.");
        }
        
        // 3. Block DDL/DML keywords
        if (preg_match('/\b(insert|update|delete|drop|truncate|alter|create|grant|revoke)\b/i', $query)) {
            throw new Exception("Only SELECT queries are allowed. DML/DDL commands are blocked.");
        }
        
        // Strip trailing semicolon and wrap query to enforce maximum 1000 records
        $exec_query = rtrim($query, ';');
        $wrapped_query = "SELECT * FROM ($exec_query) AS query_run LIMIT 1001";
        
        $stmt = $db->query($wrapped_query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_rows = count($results);
        
        if ($total_rows > 1000) {
            $limit_warning = true;
            array_pop($results); // Remove the 1001st record
            $total_rows = 1000;
        }
        
        if ($total_rows > 0) {
            $columns = array_keys($results[0]);
        }
        
        $execution_time = microtime(true) - $start_time;
        
        // Log to User Activity Trail
        audit_log('SQL Query Console', 'Execute Query', [
            'query' => $query,
            'duration' => round($execution_time, 4) . 's',
            'rows_count' => $total_rows
        ]);
        
        // Log query execution history
        $logStmt = $db->prepare("
            INSERT INTO admin_query_logs (user_id, username, query_text, execution_time, ip_address)
            VALUES (:uid, :uname, :q, :time, :ip)
        ");
        $logStmt->execute([
            ':uid'   => $_SESSION['audit_user_id'] ?? 0,
            ':uname' => $_SESSION['audit_username'] ?? 'unknown',
            ':q'     => $query,
            ':time'  => round($execution_time, 4),
            ':ip'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Fetch query history (last 15 records)
$history = [];
try {
    $history = $db->query("SELECT * FROM admin_query_logs ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Ignore history load errors
}
?>
<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('SQL Query Console');
cdat_sum_page_open('sum-admin-layout');
?>
<div class="sum-admin-grid">
  <div class="sum-admin-grid__main">
    <section class="sum-console-panel" aria-label="SQL Query Console">
      <h2 class="sum-console-panel__title">SQL Query Console (PostgreSQL)</h2>
      <form id="sqlForm" name="sqlForm" method="post" action="admin_sql_console.php" class="no-ajax" data-no-ajax>
        <textarea id="sql_query" name="sql_query" class="sum-console-editor" placeholder="Type your SELECT query here... e.g. SELECT * FROM user_sessions LIMIT 100;"><?= htmlspecialchars($query) ?></textarea>
        <div class="sum-console-actions">
          <input type="submit" name="BTN_EXECUTE" id="BTN_EXECUTE" value="Execute Query" class="sum-console-btn" />
          <input type="button" value="Clear" class="sum-console-btn sum-console-btn--secondary" onclick="document.getElementById('sql_query').value='';" />
          <input type="button" value="Copy Query" class="sum-console-btn sum-console-btn--secondary" onclick="navigator.clipboard.writeText(document.getElementById('sql_query').value); alert('Query copied to clipboard!');" />
        </div>
      </form>
    </section>

    <?php if ($error !== ''): ?>
      <?php cdat_sum_status_message('Error: ' . $error, false); ?>
    <?php endif; ?>

    <?php if ($limit_warning): ?>
      <div class="sum-status" role="status">Showing first 1000 records.</div>
    <?php endif; ?>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '' && isset($_POST['BTN_EXECUTE'])): ?>
      <div class="sum-console-actions" style="justify-content: flex-end; margin-bottom: .65rem;">
        <form method="post" action="admin_sql_console.php" class="no-ajax" data-no-ajax style="display:inline; margin:0;">
          <input type="hidden" name="sql_query" value="<?= htmlspecialchars($query) ?>" />
          <input type="hidden" name="export" value="1" />
          <input type="hidden" name="export_type" value="csv" />
          <input type="submit" value="Export CSV" class="sum-console-btn" />
        </form>
        <form method="post" action="admin_sql_console.php" class="no-ajax" data-no-ajax style="display:inline; margin:0;">
          <input type="hidden" name="sql_query" value="<?= htmlspecialchars($query) ?>" />
          <input type="hidden" name="export" value="1" />
          <input type="hidden" name="export_type" value="excel" />
          <input type="submit" value="Export Excel" class="sum-console-btn" />
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

  <aside class="sum-admin-grid__side">
    <section class="sum-console-panel" aria-label="Recent Queries">
      <h2 class="sum-console-panel__title">Recent Queries</h2>
      <?php if (!empty($history)): ?>
        <?php foreach ($history as $h): ?>
          <div class="sum-history-item" onclick="document.getElementById('sql_query').value = this.getAttribute('data-query');" data-query="<?= htmlspecialchars($h['query_text']) ?>" title="Click to copy query to editor">
            <b><?= htmlspecialchars($h['username']) ?></b> (<?= round((float)$h['execution_time'], 3) ?>s)<br/>
            <span><?= date('d-m-Y h:i A', strtotime($h['created_at'])) ?></span>
            <code><?= htmlspecialchars(substr($h['query_text'], 0, 80)) ?><?= strlen($h['query_text']) > 80 ? '...' : '' ?></code>
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
