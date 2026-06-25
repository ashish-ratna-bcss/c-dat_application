<?php
/**
 * ADMIN_CREATE_USER.PHP
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Create User - Interrogation Reports</title>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarVertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.FONT {
	color: #CFF;
	font-size: 24px;
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif;
}
.form-container {
    width: 480px;
    margin: 10px auto;
    padding: 20px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    font-family: Verdana, Geneva, sans-serif;
    color: #fff;
}
.form-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.3);
    padding-bottom: 8px;
    color: #FFD700;
}
.form-group {
    margin-bottom: 15px;
    text-align: left;
}
.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 12px;
}
.form-group input[type="text"],
.form-group input[type="password"],
.form-group select {
    width: 100%;
    padding: 6px 10px;
    font-size: 13px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    color: #333;
}
.btn-submit {
    background-color: #FFA500;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
    width: 100%;
    margin-top: 10px;
}
.btn-submit:hover {
    background-color: #e59400;
}
.msg-error {
    background: #f8d7da;
    color: #721c24;
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 13px;
    font-weight: bold;
    border: 1px solid #f5c6cb;
}
.msg-success {
    background: #d4edda;
    color: #155724;
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 13px;
    font-weight: bold;
    border: 1px solid #c3e6cb;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top">
        <table width="1313" height="148">
          <tr>
            <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/TOPBORDER.jpg">
              <ul id="MenuBar1" class="MenuBarHorizontal">
                <li><a href="HOME_IR.HTML">Home</a></li>
                <li><a href="IRREPORT.html">IRREPORT</a></li>
                <li><a href="FAMILY_HISTORY.html">FAMILY HISTORY</a></li>
                <li><a href="HOME_IR.HTML" class="MenuBarItemSubmenu">CRIME DETAILS</a>
                  <ul>
                    <li><a href="OFFENCE_DETAILS.HTML">OFFENCE DETAILS</a></li>
                    <li><a href="PREVIOUS_OFFENCE_DETAILS.HTML">PREVIOUS OFFENCE DETAILS</a></li>
                  </ul>
                </li>
                <li><a href="LOCAL_CONTACTS.html">LOCAL CONTACTS</a></li>
                <li><a href="RELATION_WITH_OTHER_ASSOCIATES_AND_GANGS.html">GANGS/ASSOCIATES</a></li>
                <li><a href="DISPOSAL_OF_PROPERTY.HTML">PROPERTY DETAILS</a></li>
                <li><a href="BRIEF_FACTS.HTML">BRIEF FACTS</a></li>
                <li><a href="IMAGE_LIST.HTML">IMAGE</a></li>
                <li><a href="MULAKATH_DETAILS.HTML">MULAKATH_DETAILS</a></li>
                <li><a href="RETRIEVE.HTML">RETRIEVE</a></li>
                <li><a href="ADMIN_CREATE_USER.PHP">CREATE USER</a></li>
                <li><a href="admin_upload.php">DATA UPLOAD</a></li>
              </ul>
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
                
                <form action="ADMIN_CREATE_USER.PHP" method="post" id="createUserForm">
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

<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});

// Simple validation to ensure username has no spaces
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    var unameInput = document.getElementById('username');
    var uname = unameInput.value.trim();
    if (uname.indexOf(' ') !== -1) {
        alert('Username cannot contain spaces.');
        e.preventDefault();
        return false;
    }
});
</script>
</body>
</html>
