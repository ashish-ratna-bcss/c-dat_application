<?php
require_once __DIR__ . '/bootstrap.php';
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_session();

if (!empty($_POST['CRIME_NO'])) {
    $crimeNo = (int) $_POST['CRIME_NO'];
    $pdo = get_cdat_pdo();
    $st = $pdo->prepare(
        'SELECT DISTINCT YEAR
         FROM offence_details
         WHERE CRIME_NO = :crime_no
           AND YEAR IS NOT NULL
         ORDER BY YEAR'
    );
    $st->execute([':crime_no' => $crimeNo]);
    $results = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    ?>
    <option value="">Select Year</option>
    <?php foreach ($results as $row): ?>
        <option value="<?= htmlspecialchars((string) ($row['YEAR'] ?? ''), ENT_QUOTES) ?>">
            <?= htmlspecialchars((string) ($row['YEAR'] ?? '')) ?>
        </option>
    <?php endforeach;
}
