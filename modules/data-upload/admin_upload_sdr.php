<?php
require_once __DIR__ . '/../common/bootstrap.php';
define('CDAT_UPLOAD_PAGE', 'sdr');
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_uploader();
require CDAT_UPLOAD . '/admin_upload.php';
