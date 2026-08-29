<?php
require_once __DIR__ . '/../common/bootstrap.php';
/**
 * admin_create_user.php
 * Admin-only module to create new application logins.
 */
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_uname = trim($_POST['username'] ?? '');
    $new_pass  = trim($_POST['password'] ?? '');
    $new_role  = strtolower(trim($_POST['role'] ?? 'user'));
    $new_fname = trim($_POST['fullname'] ?? '');

    if ($new_uname === '' || $new_pass === '' || $new_fname === '') {
        $error = 'All fields are required!';
    } else {
        $db = audit_db();
        try {
            $st = $db->prepare('SELECT COUNT(*) FROM logins WHERE LOWER(username) = LOWER(:u)');
            $st->execute([':u' => $new_uname]);
            if ($st->fetchColumn() > 0) {
                $error = 'Username already exists!';
            } else {
                $st = $db->prepare('INSERT INTO logins (username, password, role, fullname) VALUES (:u, :p, :r, :f)');
                $ok = $st->execute([
                    ':u' => $new_uname,
                    ':p' => password_hash($new_pass, PASSWORD_DEFAULT),
                    ':r' => $new_role,
                    ':f' => $new_fname,
                ]);
                if ($ok) {
                    $success = 'User created successfully!';
                    audit_log('User Management', 'CREATE USER', [
                        'created_username' => $new_uname,
                        'role' => $new_role,
                        'fullname' => $new_fname,
                    ]);
                } else {
                    $error = 'Database error. Failed to create user.';
                }
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Create User');
cdat_sum_page_open();
cdat_sum_entry_card_open(
    'Create System User',
    'Enter credentials for a new application login.',
    'admin_create_user.php',
    'post',
    '',
    'form1',
    'no-ajax'
);

if ($error !== '') {
    cdat_sum_status_message($error, false);
}
if ($success !== '') {
    cdat_sum_status_message($success, true);
}
?>
<div class="col-12 col-md-6">
  <label class="form-label" for="username">Username (lowercase, alphanumeric)</label>
  <input type="text" class="form-control" name="username" id="username" required="required" autocomplete="off" placeholder="e.g. admin2" />
</div>
<div class="col-12 col-md-6">
  <label class="form-label" for="fullname">Full name</label>
  <input type="text" class="form-control" name="fullname" id="fullname" required="required" placeholder="e.g. Hyderabad Police Officer" />
</div>
<div class="col-12 col-md-6">
  <label class="form-label" for="password">Password</label>
  <input type="password" class="form-control" name="password" id="password" required="required" placeholder="Enter secure password" />
</div>
<div class="col-12 col-md-6">
  <label class="form-label" for="role">Role</label>
  <select class="form-select" name="role" id="role" data-placeholder="Select role">
    <option value="" data-placeholder="1">Select role</option>
    <option value="user">User (Standard Access)</option>
    <option value="poweruser">Power User (User || Bulk Upload Access)</option>
    <option value="admin">Admin (All Access)</option>
  </select>
</div>
<?php
cdat_sum_entry_card_close('CREATE USER');
cdat_sum_page_close();
layout_end();
