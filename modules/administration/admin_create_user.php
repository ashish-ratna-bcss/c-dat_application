<?php
require_once __DIR__ . '/../common/bootstrap.php';
/**
 * admin_create_user.php
 * Admin-only module to create and manage application logins.
 */
require_once CDAT_COMMON . '/activity_logger.php';
require_once CDAT_COMMON . '/csrf.php';
audit_require_admin();

function cdat_ensure_logins_status_column(PDO $db): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $db->exec("ALTER TABLE logins ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'active'");
        $db->exec("UPDATE logins SET status = 'active' WHERE status IS NULL OR BTRIM(status) = ''");
    } catch (Throwable $e) {
        // Column may already exist with a different definition on older DBs.
    }
}

function cdat_login_row_val(array $row, string $key): string
{
    $upper = strtoupper($key);
    return (string) ($row[$key] ?? $row[$upper] ?? '');
}

function cdat_user_valid_roles(): array
{
    return ['user', 'poweruser', 'admin'];
}

function cdat_user_role_label(string $role): string
{
    $role = strtolower(trim($role));
    return match ($role) {
        'admin' => 'Admin',
        'poweruser' => 'Power User',
        'user' => 'User',
        default => ucfirst($role),
    };
}

function cdat_user_is_self(int $userId, string $username, int $currentUserId, string $currentUsername): bool
{
    if ($userId > 0 && $userId === $currentUserId) {
        return true;
    }
    $username = strtolower(trim($username));
    return $currentUsername !== '' && $username !== '' && $username === $currentUsername;
}

function cdat_user_active_admin_count(PDO $db): int
{
    $st = $db->query(
        "SELECT COUNT(*) FROM logins WHERE LOWER(BTRIM(role)) = 'admin' AND LOWER(BTRIM(status)) = 'active'"
    );
    return $st !== false ? (int) $st->fetchColumn() : 0;
}

function cdat_user_is_last_active_admin(PDO $db, int $userId, string $role, string $status): bool
{
    return strtolower(trim($role)) === 'admin'
        && strtolower(trim($status)) === 'active'
        && cdat_user_active_admin_count($db) <= 1;
}

function cdat_users_flash_redirect(string $type, string $message, array $extra = []): never
{
    $_SESSION['cdat_users_flash'] = array_merge(['type' => $type, 'message' => $message], $extra);
    $url = function_exists('cdat_form_action') ? cdat_form_action('/administration/create-user') : '/administration/create-user';
    header('Location: ' . $url);
    exit;
}

$db = audit_db();
cdat_ensure_logins_status_column($db);

$currentUserId = (int) ($_SESSION['audit_user_id'] ?? 0);
$currentUsername = strtolower(trim((string) ($_SESSION['audit_username'] ?? '')));
$error = '';
$success = '';
$openModal = false;
$openEditModal = false;
$formValues = [
    'username' => '',
    'fullname' => '',
    'role'     => '',
];
$editValues = [
    'user_id'  => 0,
    'username' => '',
    'fullname' => '',
    'role'     => '',
];

$flash = $_SESSION['cdat_users_flash'] ?? null;
if (is_array($flash)) {
    unset($_SESSION['cdat_users_flash']);
    if (($flash['type'] ?? '') === 'success') {
        $success = (string) ($flash['message'] ?? '');
    } else {
        $error = (string) ($flash['message'] ?? '');
    }
    $openModal = ($flash['open'] ?? '') === 'create';
    $openEditModal = ($flash['open'] ?? '') === 'edit';
    if (is_array($flash['form'] ?? null)) {
        $formValues = array_merge($formValues, $flash['form']);
    }
    if (is_array($flash['edit'] ?? null)) {
        $editValues = array_merge($editValues, $flash['edit']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $userAction = strtolower(trim((string) ($_POST['user_action'] ?? 'create')));

    if ($userAction === 'edit') {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        $edit_fullname = trim($_POST['fullname'] ?? '');
        $edit_role = strtolower(trim($_POST['role'] ?? ''));
        $edit_pass = trim($_POST['password'] ?? '');
        $editValues = [
            'user_id'  => $targetId,
            'username' => trim($_POST['username'] ?? ''),
            'fullname' => $edit_fullname,
            'role'     => $edit_role,
        ];

        if ($targetId <= 0 || $edit_fullname === '' || $edit_role === '') {
            cdat_users_flash_redirect('error', 'Full name and role are required.', [
                'open' => 'edit',
                'edit' => $editValues,
            ]);
        }
        if (!in_array($edit_role, cdat_user_valid_roles(), true)) {
            cdat_users_flash_redirect('error', 'Please select a valid role.', [
                'open' => 'edit',
                'edit' => $editValues,
            ]);
        }

        try {
            $st = $db->prepare('SELECT id, username, role, status FROM logins WHERE id = :id');
            $st->execute([':id' => $targetId]);
            $target = $st->fetch(PDO::FETCH_ASSOC);
            if (!$target) {
                cdat_users_flash_redirect('error', 'User not found.');
            }

            $username = cdat_login_row_val($target, 'username');
            $currentRole = strtolower(cdat_login_row_val($target, 'role'));
            $currentStatus = strtolower(cdat_login_row_val($target, 'status') ?: 'active');
            $isSelf = cdat_user_is_self($targetId, $username, $currentUserId, $currentUsername);

            if ($currentRole === 'admin'
                && $edit_role !== 'admin'
                && cdat_user_is_last_active_admin($db, $targetId, $currentRole, $currentStatus)) {
                cdat_users_flash_redirect('error', 'Cannot change role of the last active admin.', [
                    'open' => 'edit',
                    'edit' => $editValues,
                ]);
            }

            $params = [':f' => $edit_fullname, ':r' => $edit_role, ':id' => $targetId];
            if ($edit_pass !== '') {
                $sql = 'UPDATE logins SET fullname = :f, role = :r, password = :p, updated_at = NOW() WHERE id = :id';
                $params[':p'] = password_hash($edit_pass, PASSWORD_DEFAULT);
            } else {
                $sql = 'UPDATE logins SET fullname = :f, role = :r, updated_at = NOW() WHERE id = :id';
            }

            $upd = $db->prepare($sql);
            $upd->execute($params);

            if ($isSelf) {
                $_SESSION['audit_fullname'] = $edit_fullname;
                $_SESSION['audit_role'] = $edit_role;
            }

            audit_log('User Management', 'EDIT USER', [
                'user_id' => $targetId,
                'username' => $username,
                'role' => $edit_role,
                'password_changed' => $edit_pass !== '',
            ]);
            cdat_users_flash_redirect('success', 'User ' . $username . ' updated successfully.');
        } catch (Exception $e) {
            cdat_users_flash_redirect('error', 'Database error: ' . $e->getMessage(), [
                'open' => 'edit',
                'edit' => $editValues,
            ]);
        }
    } elseif ($userAction === 'deactivate' || $userAction === 'activate' || $userAction === 'delete') {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        if ($targetId <= 0) {
            cdat_users_flash_redirect('error', 'Invalid user selected.');
        }

        try {
            $st = $db->prepare('SELECT id, username, role, status FROM logins WHERE id = :id');
            $st->execute([':id' => $targetId]);
            $target = $st->fetch(PDO::FETCH_ASSOC);
            if (!$target) {
                cdat_users_flash_redirect('error', 'User not found.');
            }

            $username = cdat_login_row_val($target, 'username');
            $targetRole = strtolower(cdat_login_row_val($target, 'role'));
            $targetStatus = strtolower(cdat_login_row_val($target, 'status') ?: 'active');

            if (cdat_user_is_self($targetId, $username, $currentUserId, $currentUsername)) {
                cdat_users_flash_redirect('error', 'You cannot modify your own account from this page.');
            }

            if ($userAction === 'delete') {
                if (cdat_user_is_last_active_admin($db, $targetId, $targetRole, $targetStatus)) {
                    cdat_users_flash_redirect('error', 'Cannot delete the last active admin.');
                }
                $del = $db->prepare('DELETE FROM logins WHERE id = :id');
                $del->execute([':id' => $targetId]);
                audit_log('User Management', 'DELETE USER', ['user_id' => $targetId, 'username' => $username]);
                cdat_users_flash_redirect('success', 'User ' . $username . ' deleted.');
            }

            if ($userAction === 'deactivate' && cdat_user_is_last_active_admin($db, $targetId, $targetRole, $targetStatus)) {
                cdat_users_flash_redirect('error', 'Cannot deactivate the last active admin.');
            }

            $newStatus = $userAction === 'activate' ? 'active' : 'inactive';
            $upd = $db->prepare('UPDATE logins SET status = :status, updated_at = NOW() WHERE id = :id');
            $upd->execute([':status' => $newStatus, ':id' => $targetId]);
            audit_log('User Management', strtoupper($userAction) . ' USER', [
                'user_id' => $targetId,
                'username' => $username,
                'status' => $newStatus,
            ]);
            cdat_users_flash_redirect(
                'success',
                $newStatus === 'active'
                    ? 'User ' . $username . ' activated.'
                    : 'User ' . $username . ' deactivated.'
            );
        } catch (Exception $e) {
            cdat_users_flash_redirect('error', 'Database error: ' . $e->getMessage());
        }
    } elseif ($userAction === 'create') {
        $new_uname = trim($_POST['username'] ?? '');
        $new_pass  = trim($_POST['password'] ?? '');
        $new_role  = strtolower(trim($_POST['role'] ?? ''));
        $new_fname = trim($_POST['fullname'] ?? '');
        $formValues = [
            'username' => $new_uname,
            'fullname' => $new_fname,
            'role'     => $new_role,
        ];

        if ($new_uname === '' || $new_pass === '' || $new_fname === '' || $new_role === '') {
            cdat_users_flash_redirect('error', 'All fields are required.', [
                'open' => 'create',
                'form' => $formValues,
            ]);
        }
        if (!preg_match('/^[a-zA-Z0-9._-]{3,32}$/', $new_uname)) {
            cdat_users_flash_redirect('error', 'Username must be 3–32 characters (letters, numbers, . _ - only).', [
                'open' => 'create',
                'form' => $formValues,
            ]);
        }
        if (!in_array($new_role, cdat_user_valid_roles(), true)) {
            cdat_users_flash_redirect('error', 'Please select a valid role.', [
                'open' => 'create',
                'form' => $formValues,
            ]);
        }

        try {
            $st = $db->prepare('SELECT COUNT(*) FROM logins WHERE LOWER(username) = LOWER(:u)');
            $st->execute([':u' => $new_uname]);
            if ($st->fetchColumn() > 0) {
                cdat_users_flash_redirect('error', 'Username already exists.', [
                    'open' => 'create',
                    'form' => $formValues,
                ]);
            }

            $st = $db->prepare(
                'INSERT INTO logins (username, password, role, fullname, status)
                 VALUES (:u, :p, :r, :f, :status)'
            );
            $ok = $st->execute([
                ':u' => $new_uname,
                ':p' => password_hash($new_pass, PASSWORD_DEFAULT),
                ':r' => $new_role,
                ':f' => $new_fname,
                ':status' => 'active',
            ]);
            if (!$ok) {
                cdat_users_flash_redirect('error', 'Database error. Failed to create user.', [
                    'open' => 'create',
                    'form' => $formValues,
                ]);
            }

            audit_log('User Management', 'CREATE USER', [
                'created_username' => $new_uname,
                'role' => $new_role,
                'fullname' => $new_fname,
            ]);
            cdat_users_flash_redirect('success', 'User ' . $new_uname . ' created successfully.');
        } catch (Exception $e) {
            cdat_users_flash_redirect('error', 'Database error: ' . $e->getMessage(), [
                'open' => 'create',
                'form' => $formValues,
            ]);
        }
    }
}

$users = [];
try {
    $userStmt = $db->query(
        'SELECT id, username, fullname, role, status, created_at
         FROM logins
         ORDER BY username ASC'
    );
    if ($userStmt !== false) {
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Throwable $e) {
    $users = [];
}

$formAction = function_exists('cdat_form_action') ? cdat_form_action('/administration/create-user') : '/administration/create-user';

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('User Management');
cdat_sum_page_open('sum-create-user-page');
?>
<section class="sum-users-panel" aria-label="User management">
  <div class="sum-users-panel__head">
    <div class="sum-users-panel__head-left">
      <div class="sum-users-panel__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
          <path d="M19 8v6M22 11h-6"/>
        </svg>
      </div>
      <div>
        <h2 class="sum-users-panel__title">User Management</h2>
        <p class="sum-users-panel__desc">Manage application logins, status, and access roles.</p>
      </div>
      <span class="sum-badge"><?= (int) count($users) ?> <?= count($users) === 1 ? 'user' : 'users' ?></span>
    </div>
    <div class="sum-users-panel__actions">
      <input type="search" id="users-table-search" class="form-control form-control-sm sum-users-search" placeholder="Search users…" aria-label="Search users" />
      <button type="button" class="cdat-dt-btn cdat-dt-btn--export" id="users-export-excel">Export Excel</button>
      <button type="button" class="cdat-dt-btn cdat-dt-btn--print" id="users-print">Print</button>
      <button type="button" class="btn btn-primary btn-sm sum-users-create-btn" data-bs-toggle="modal" data-bs-target="#createUserModal">
        + Create User
      </button>
    </div>
  </div>

  <?php if ($error !== '' && !$openModal && !$openEditModal): ?>
    <div class="sum-users-panel__alert">
      <?php cdat_sum_status_message($error, false); ?>
    </div>
  <?php endif; ?>

  <?php if ($users !== []): ?>
    <div class="table-responsive">
      <table class="table table-sm table-striped table-hover mb-0 sum-users-table" id="admin_users_table" data-no-datatable="1" data-export-name="users.csv">
        <thead>
          <tr>
            <th scope="col">Username</th>
            <th scope="col">Full Name</th>
            <th scope="col" class="sum-users-table__role">Role</th>
            <th scope="col" class="sum-users-table__status">Status</th>
            <th scope="col" class="sum-users-table__created">Created</th>
            <th scope="col" class="sum-users-table__actions no-export">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <?php
            $userId = (int) ($user['id'] ?? $user['ID'] ?? 0);
            $username = cdat_login_row_val($user, 'username');
            $fullname = cdat_login_row_val($user, 'fullname');
            $role = cdat_login_row_val($user, 'role');
            $status = strtolower(cdat_login_row_val($user, 'status') ?: 'active');
            $isSelf = cdat_user_is_self($userId, $username, $currentUserId, $currentUsername);
            $roleKey = strtolower($role);
            $created = cdat_login_row_val($user, 'created_at');
            if ($created !== '') {
                try {
                    $created = (new DateTime($created))->format('d-m-Y h:i A');
                } catch (Exception $e) {
                    // keep raw value
                }
            }
            ?>
            <tr class="<?= $status === 'inactive' ? 'sum-users-row--inactive' : '' ?>">
              <td class="sum-users-table__username"><?= cdat_sum_h($username) ?><?= $isSelf ? ' <span class="sum-users-you">(you)</span>' : '' ?></td>
              <td><?= cdat_sum_h($fullname) ?></td>
              <td class="sum-users-table__role"><span class="sum-users-role sum-users-role--<?= cdat_sum_h($roleKey) ?>"><?= cdat_sum_h(cdat_user_role_label($role)) ?></span></td>
              <td class="sum-users-table__status">
                <span class="sum-users-status sum-users-status--<?= cdat_sum_h($status) ?>"><?= cdat_sum_h(ucfirst($status)) ?></span>
              </td>
              <td class="sum-users-table__created"><?= cdat_sum_h($created !== '' ? $created : '—') ?></td>
              <td class="sum-users-table__actions no-export">
                <div class="sum-users-action-group">
                  <button type="button"
                          class="btn btn-primary btn-sm sum-users-edit-btn"
                          data-bs-toggle="modal"
                          data-bs-target="#editUserModal"
                          data-user-id="<?= $userId ?>"
                          data-username="<?= cdat_sum_h($username) ?>"
                          data-fullname="<?= cdat_sum_h($fullname) ?>"
                          data-role="<?= cdat_sum_h($roleKey) ?>">Edit</button>
                  <?php if (!$isSelf): ?>
                    <?php if ($status === 'active'): ?>
                      <form method="post" action="<?= cdat_sum_h($formAction) ?>" class="sum-users-action-form no-ajax" data-no-ajax onsubmit="return confirm('Deactivate user <?= cdat_sum_h($username) ?>?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="user_action" value="deactivate" />
                        <input type="hidden" name="user_id" value="<?= $userId ?>" />
                        <button type="submit" class="btn btn-warning btn-sm">Deactivate</button>
                      </form>
                    <?php else: ?>
                      <form method="post" action="<?= cdat_sum_h($formAction) ?>" class="sum-users-action-form no-ajax" data-no-ajax onsubmit="return confirm('Activate user <?= cdat_sum_h($username) ?>?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="user_action" value="activate" />
                        <input type="hidden" name="user_id" value="<?= $userId ?>" />
                        <button type="submit" class="btn btn-success btn-sm">Activate</button>
                      </form>
                    <?php endif; ?>
                    <form method="post" action="<?= cdat_sum_h($formAction) ?>" class="sum-users-action-form no-ajax" data-no-ajax onsubmit="return confirm('Permanently delete user <?= cdat_sum_h($username) ?>? This cannot be undone.');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="user_action" value="delete" />
                      <input type="hidden" name="user_id" value="<?= $userId ?>" />
                      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="sum-users-panel__empty">
      <p>No users found.</p>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">Create first user</button>
    </div>
  <?php endif; ?>
</section>

<?php if ($success !== ''): ?>
<div class="toast-container position-fixed top-0 end-0 p-3 sum-users-toast-container" style="z-index: 1090;">
  <div id="users-success-toast" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body"><?= cdat_sum_h($success) ?></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content sum-create-user-modal">
      <div class="modal-header">
        <h5 class="modal-title" id="createUserModalLabel">Create System User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="<?= cdat_sum_h($formAction) ?>" class="no-ajax" data-no-ajax id="createUserForm">
        <?= csrf_field() ?>
        <input type="hidden" name="user_action" value="create" />
        <div class="modal-body">
          <p class="sum-create-user-modal__desc">Enter credentials for a new application login.</p>
          <?php if ($error !== '' && $openModal): ?>
            <?php cdat_sum_status_message($error, false); ?>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input type="text" class="form-control" name="username" id="username" required="required" autocomplete="off" placeholder="e.g. admin2" value="<?= cdat_sum_h($formValues['username']) ?>" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="fullname">Full name</label>
            <input type="text" class="form-control" name="fullname" id="fullname" required="required" placeholder="e.g. Hyderabad Police Officer" value="<?= cdat_sum_h($formValues['fullname']) ?>" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input type="password" class="form-control" name="password" id="password" required="required" placeholder="Enter secure password" autocomplete="new-password" />
          </div>
          <div class="mb-0">
            <label class="form-label" for="role">Role</label>
            <select class="form-select" name="role" id="role" required="required">
              <option value="" disabled="disabled"<?= $formValues['role'] === '' ? ' selected="selected"' : '' ?>>Select role</option>
              <option value="user"<?= $formValues['role'] === 'user' ? ' selected="selected"' : '' ?>>User (Standard Access)</option>
              <option value="poweruser"<?= $formValues['role'] === 'poweruser' ? ' selected="selected"' : '' ?>>Power User (Bulk Upload Access)</option>
              <option value="admin"<?= $formValues['role'] === 'admin' ? ' selected="selected"' : '' ?>>Admin (All Access)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content sum-create-user-modal">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="<?= cdat_sum_h($formAction) ?>" class="no-ajax" data-no-ajax id="editUserForm">
        <?= csrf_field() ?>
        <input type="hidden" name="user_action" value="edit" />
        <input type="hidden" name="user_id" id="edit_user_id" value="<?= (int) $editValues['user_id'] ?>" />
        <input type="hidden" name="username" id="edit_username_hidden" value="<?= cdat_sum_h($editValues['username']) ?>" />
        <div class="modal-body">
          <p class="sum-create-user-modal__desc">Update profile details. Leave password blank to keep the current password.</p>
          <?php if ($error !== '' && $openEditModal): ?>
            <?php cdat_sum_status_message($error, false); ?>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label" for="edit_username_display">Username</label>
            <input type="text" class="form-control" id="edit_username_display" value="<?= cdat_sum_h($editValues['username']) ?>" readonly="readonly" disabled="disabled" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="edit_fullname">Full name</label>
            <input type="text" class="form-control" name="fullname" id="edit_fullname" required="required" value="<?= cdat_sum_h($editValues['fullname']) ?>" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="edit_password">New password</label>
            <input type="password" class="form-control" name="password" id="edit_password" placeholder="Leave blank to keep current password" autocomplete="new-password" />
          </div>
          <div class="mb-0">
            <label class="form-label" for="edit_role">Role</label>
            <select class="form-select" name="role" id="edit_role" required="required">
              <option value="user"<?= $editValues['role'] === 'user' ? ' selected="selected"' : '' ?>>User (Standard Access)</option>
              <option value="poweruser"<?= $editValues['role'] === 'poweruser' ? ' selected="selected"' : '' ?>>Power User (Bulk Upload Access)</option>
              <option value="admin"<?= $editValues['role'] === 'admin' ? ' selected="selected"' : '' ?>>Admin (All Access)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  var table = document.getElementById('admin_users_table');
  var search = document.getElementById('users-table-search');
  var exportBtn = document.getElementById('users-export-excel');
  var printBtn = document.getElementById('users-print');

  if (search && table) {
    search.addEventListener('input', function () {
      var q = this.value.toLowerCase().trim();
      Array.prototype.forEach.call(table.tBodies[0].rows, function (row) {
        row.style.display = row.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
      });
    });
  }

  function cellExportText(td) {
    if (td.classList.contains('no-export') || td.querySelector('button')) {
      return '';
    }
    return td.textContent.replace(/\s+/g, ' ').trim();
  }

  function exportUsersExcel() {
    if (!table) return;
    var html = '<table border="1"><thead><tr>';
    Array.prototype.forEach.call(table.tHead.rows[0].cells, function (th) {
      if (th.classList.contains('no-export')) return;
      html += '<th>' + th.textContent + '</th>';
    });
    html += '</tr></thead><tbody>';
    Array.prototype.forEach.call(table.tBodies[0].rows, function (row) {
      if (row.style.display === 'none') return;
      html += '<tr>';
      Array.prototype.forEach.call(row.cells, function (td) {
        if (td.classList.contains('no-export')) return;
        html += '<td>' + cellExportText(td) + '</td>';
      });
      html += '</tr>';
    });
    html += '</tbody></table>';
    var blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'users_' + Date.now() + '.xls';
    link.click();
    URL.revokeObjectURL(link.href);
  }

  function printUsers() {
    window.print();
  }

  if (exportBtn) exportBtn.addEventListener('click', exportUsersExcel);
  if (printBtn) printBtn.addEventListener('click', printUsers);

  var successToast = document.getElementById('users-success-toast');
  if (successToast && window.bootstrap) {
    var toast = bootstrap.Toast.getOrCreateInstance(successToast, { delay: 3500, autohide: true });
    toast.show();
  }

  document.querySelectorAll('.sum-users-edit-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = document.getElementById('edit_user_id');
      var hiddenUser = document.getElementById('edit_username_hidden');
      var displayUser = document.getElementById('edit_username_display');
      var fullname = document.getElementById('edit_fullname');
      var role = document.getElementById('edit_role');
      var password = document.getElementById('edit_password');
      if (id) id.value = btn.getAttribute('data-user-id') || '';
      if (hiddenUser) hiddenUser.value = btn.getAttribute('data-username') || '';
      if (displayUser) displayUser.value = btn.getAttribute('data-username') || '';
      if (fullname) fullname.value = btn.getAttribute('data-fullname') || '';
      if (role) role.value = btn.getAttribute('data-role') || 'user';
      if (password) password.value = '';
    });
  });

  <?php if ($openModal): ?>
  document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('createUserModal');
    if (el && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(el).show();
    }
  });
  <?php endif; ?>
  <?php if ($openEditModal): ?>
  document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('editUserModal');
    if (el && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(el).show();
    }
  });
  <?php endif; ?>
})();
</script>
<?php
cdat_sum_page_close();
layout_end();
