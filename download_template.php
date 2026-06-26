<?php
/**
 * download_template.php
 * Template downloads are not available for operator CDR / SDR document uploads.
 */
require_once __DIR__ . '/activity_logger.php';
audit_require_admin();

http_response_code(410);
die('CSV templates are not available for CDR/SDR document uploads. Use operator CDR files or MSSQL .bak backups.');
