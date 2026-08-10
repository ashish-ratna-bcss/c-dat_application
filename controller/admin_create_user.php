<?php
/**
 * admin_create_user.php
 * Admin-only module to create new application logins.
 * Integrated seamlessly into the Interrogation Reports UI.
 */
require_once __DIR__ . '/activity_logger.php';
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
            // Check if username already exists (case-insensitive check)
            $st = $db->prepare("SELECT COUNT(*) FROM logins WHERE LOWER(username) = LOWER(:u)");
            $st->execute([':u' => $new_uname]);
            if ($st->fetchColumn() > 0) {
                $error = 'Username already exists!';
            } else {
                // Insert new user
                $st = $db->prepare("INSERT INTO logins (username, password, role, fullname) VALUES (:u, :p, :r, :f)");
                $ok = $st->execute([
                    ':u' => $new_uname,
                    ':p' => $new_pass,
                    ':r' => $new_role,
                    ':f' => $new_fname
                ]);
                if ($ok) {
                    $success = 'User created successfully!';
                    audit_log('User Management', 'CREATE USER', [
                        'created_username' => $new_uname,
                        'role' => $new_role,
                        'fullname' => $new_fname
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
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Create User");
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        <table width="1313" height="148">
          <tr>
            <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg">
              
            </td>
          </tr>
        </table>
        
        <marquee behavior="scroll" direction="left"> 
          <font color="YELLOW" face="verdana" size="2"><b> *** IF YOU HAVE ANY SUGGESTIONS OR CHANGES ON THIS SITE PLEASE SHARE WITH ANALYSIS WING *** </b></font>
        </marquee> 
        
        <table width="1307" height="347" border="0" align="center">
          <tr>
            <td height="24" align="center" valign="top">
              <p align="center" class="FONT"> CREATE SYSTEM USER </p>
            </td>
          </tr>
          <tr>
            <td height="310" align="center" valign="top">
              
              <!-- Form container -->
              <div class="form-container">
                <div class="form-title">Enter New User Credentials</div>
                
                <?php if ($error !== ''): ?>
                  <div class="msg-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success !== ''): ?>
                  <div class="msg-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <form action="admin_create_user.php" method="post" id="createUserForm">
                  <div class="form-group">
                    <label for="username">USERNAME (Lowercase, Alphanumeric)</label>
                    <input type="text" name="username" id="username" required="required" autocomplete="off" placeholder="e.g. admin2" />
                  </div>
                  
                  <div class="form-group">
                    <label for="fullname">FULL NAME</label>
                    <input type="text" name="fullname" id="fullname" required="required" placeholder="e.g. Hyderabad Police Officer" />
                  </div>
                  
                  <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <input type="password" name="password" id="password" required="required" placeholder="Enter secure password" />
                  </div>
                  
                  <div class="form-group">
                    <label for="role">ROLE</label>
                    <select name="role" id="role">
                      <option value="user">User (Standard Access)</option>
                      <option value="poweruser">Power User (User + Bulk Upload Access)</option>
                      <option value="admin">Admin (All Access)</option>
                    </select>
                  </div>
                  
                  <input type="submit" class="btn-submit" value="CREATE USER" />
                </form>
              </div>
              
            </td>
          </tr>
        </table>
        <p>&nbsp;</p>
      </td>
    </tr>
  </table>
</div>
<?php layout_end(); ?>
