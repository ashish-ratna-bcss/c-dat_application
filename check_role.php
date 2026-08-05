<?php
/**
 * check_role.php
 * JSON endpoint to check if the current user is logged in as an admin.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

$role = $_SESSION['audit_role'] ?? '';

echo json_encode([
    'is_admin' => ($role === 'admin'),
    'role' => $role
]);
