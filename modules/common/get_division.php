<?php
require_once __DIR__ . '/bootstrap.php';

if (!empty($_POST['POLICE_STATION'])) {
    $policeStation = trim((string) $_POST['POLICE_STATION']);
    $pdo = get_cdat_pdo();
    $st = $pdo->prepare(
        'SELECT DISTINCT SUB_DIVISION AS DIVISION
         FROM offence_details
         WHERE POLICE_STATION = :ps
           AND SUB_DIVISION IS NOT NULL
           AND BTRIM(SUB_DIVISION) <> \'\'
         ORDER BY SUB_DIVISION'
    );
    $st->execute([':ps' => $policeStation]);
    $results = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    ?>
    <option value="">Select DIVISION</option>
    <?php foreach ($results as $row): ?>
        <option value="<?= htmlspecialchars((string) ($row['DIVISION'] ?? ''), ENT_QUOTES) ?>">
            <?= htmlspecialchars((string) ($row['DIVISION'] ?? '')) ?>
        </option>
    <?php endforeach;
}
