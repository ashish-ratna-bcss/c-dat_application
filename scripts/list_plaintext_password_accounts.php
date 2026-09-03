#!/usr/bin/env php
<?php
/**
 * List LOGINS accounts whose PASSWORD is not bcrypt/argon2.
 * Does not print password values. Run on the application server:
 *   php scripts/list_plaintext_password_accounts.php
 *
 * Remediation: admin resets those passwords in User Management, OR
 * temporarily set CDAT_ALLOW_PLAINTEXT_MIGRATION=1 so the next successful
 * login auto-upgrades the hash, then remove that flag.
 */
require_once dirname(__DIR__) . '/modules/common/bootstrap.php';

$pdo = get_cdat_pdo();
$st = $pdo->query('SELECT USERNAME, ROLE, STATUS, PASSWORD FROM LOGINS ORDER BY USERNAME');
$rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
$plain = [];
foreach ($rows as $row) {
    $stored = (string) ($row['PASSWORD'] ?? $row['password'] ?? '');
    $user = (string) ($row['USERNAME'] ?? $row['username'] ?? '');
    $isHashed = str_starts_with($stored, '$2y$')
        || str_starts_with($stored, '$2a$')
        || str_starts_with($stored, '$argon2');
    if ($stored !== '' && !$isHashed) {
        $plain[] = [
            'username' => $user,
            'role' => (string) ($row['ROLE'] ?? $row['role'] ?? ''),
            'status' => (string) ($row['STATUS'] ?? $row['status'] ?? ''),
        ];
    }
}

echo "Accounts needing password remediation (non-hashed storage): " . count($plain) . PHP_EOL;
foreach ($plain as $row) {
    echo sprintf(
        "- %s  role=%s  status=%s\n",
        $row['username'],
        $row['role'],
        $row['status']
    );
}
exit(count($plain) > 0 ? 2 : 0);
