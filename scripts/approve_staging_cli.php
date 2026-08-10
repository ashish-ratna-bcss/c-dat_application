<?php

if ($argc < 3) {
    fwrite(STDERR, "Usage: php approve_staging_cli.php <batch_id> <username>\n");
    exit(2);
}
require_once __DIR__ . '/../controller/upload_verification_service.php';
try {
    $service = new UploadVerificationService();
    $result = $service->approveBatch((int)$argv[1], $argv[2]);
    if (!empty($result['queued'])) {
        $queueId = (int)$result['queue_id'];
        $deadline = time() + 86400;
        while (time() < $deadline) {
            sleep(3);
            $result = $service->tickApprovalQueue($queueId);
            if (empty($result['queued'])) {
                echo json_encode(['ok' => true] + $result);
                exit(0);
            }
        }
        throw new RuntimeException('Approval queue timed out after 24 hours.');
    }
    echo json_encode(['ok' => true] + $result);
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage());
    exit(1);
}
