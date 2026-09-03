<?php
require_once __DIR__ . '/bootstrap.php';
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_session();

if (!empty($_POST['POLICE_STATION'])) {
    $policeStation = trim((string) $_POST['POLICE_STATION']);
    $pdo = get_cdat_pdo();
    $st = $pdo->prepare(
        'SELECT DISTINCT CRIME_NO
         FROM offence_details
         WHERE POLICE_STATION = :ps
           AND CRIME_NO IS NOT NULL
         ORDER BY CRIME_NO'
    );
    $st->execute([':ps' => $policeStation]);
    $results = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    ?>
    <option value="">Select Crime No</option>
    <?php foreach ($results as $row): ?>
        <option value="<?= htmlspecialchars((string) ($row['CRIME_NO'] ?? ''), ENT_QUOTES) ?>">
            <?= htmlspecialchars((string) ($row['CRIME_NO'] ?? '')) ?>
        </option>
    <?php endforeach;
}
