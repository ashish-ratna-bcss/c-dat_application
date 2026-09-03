<?php
require_once __DIR__ . '/bootstrap.php';
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_session();

if (!empty($_POST['DISTRICT'])) {
    $district = trim((string) $_POST['DISTRICT']);
    $pdo = get_cdat_pdo();
    $st = $pdo->prepare(
        'SELECT DISTINCT POLICE_STATION
         FROM offence_details
         WHERE DISTRICT_OR_UNIT = :district
           AND POLICE_STATION IS NOT NULL
           AND BTRIM(POLICE_STATION) <> \'\'
         ORDER BY POLICE_STATION'
    );
    $st->execute([':district' => $district]);
    $results = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    ?>
    <option value="">Select PS</option>
    <?php foreach ($results as $row): ?>
        <option value="<?= htmlspecialchars((string) ($row['POLICE_STATION'] ?? ''), ENT_QUOTES) ?>">
            <?= htmlspecialchars((string) ($row['POLICE_STATION'] ?? '')) ?>
        </option>
    <?php endforeach;
}
