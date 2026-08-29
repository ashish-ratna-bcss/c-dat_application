/* ============================================================================
   AUTO-EXTRACTED SQL QUERIES FROM THE APPLICATION
   This file contains all SQL query strings found in the codebase,
   along with the file path and line number where they are defined.
   ============================================================================ */

/* Used in: modules/data-upload/admin_upload_sync_jobs.php at line 46 */
$batchStmt = $dbprepare('SELECT batch_id FROM upload_staging_batches WHERE document_job_id = :jid');

/* Used in: modules/data-upload/admin_upload_sync_jobs.php at line 50 */
' UPDATE upload_activity_logs SET upload_status = \'Pending Verification\', verification_status = \'pending\', staging_batch_id = :bid, total_records = :total, inserted_records = 0, failed_records = 0, error_reason = NULL WHERE document_job_id = :jid ')execute([ ':bid' $batchId, ':total' ($job['total_records'] ), ':jid' $jobId, ]);

/* Used in: modules/data-upload/admin_upload_sync_jobs.php at line 67 */
' UPDATE upload_activity_logs SET upload_status = \'Success\', total_records = :total, inserted_records = :ins, failed_records = GREATEST(:total - :ins, 0), error_reason = NULL WHERE document_job_id = :jid AND upload_status = \'Processing\' ')execute([ ':total' ($job['total_records'] ), ':ins' ($job['rows_committed'] ), ':jid' $jobId, ]);

/* Used in: modules/data-upload/admin_upload_sync_jobs.php at line 81 */
' UPDATE upload_activity_logs SET upload_status = \'Failed\', error_reason = :err, total_records = COALESCE(NULLIF(:total, 0), total_records), inserted_records = :ins, failed_records = GREATEST(COALESCE(NULLIF(:total, 0), total_records) - :ins, 0) WHERE document_job_id = :jid AND upload_status IN (\'Processing\', \'Failed\') ')execute([ ':err' $job['error_message'] 'Processing failed.', ':ins' ($job['rows_committed'] ), ':total' ($job['total_records'] ), ':jid' $jobId, ]);

/* Used in: modules/data-upload/admin_upload_sync_jobs.php at line 97 */
$logStmt = $dbprepare('SELECT id, upload_status, verification_status, staging_batch_id FROM upload_activity_logs WHERE document_job_id = :jid ORDER BY id DESC LIMIT 1');

/* Used in: modules/data-upload/admin_upload_verify.php at line 35 */
'This upload has already been approved. Its staging rows were removed once they were loaded into production, so there is nothing left to review.' : ($status 'rejected' ? 'This upload was rejected and its staging rows were removed.' : 'The staging table for this upload no longer exists.')]);

/* Used in: modules/data-upload/admin_upload_verify.php at line 175 */
'Review rows before they load into production', $headExtra);

/* Used in: modules/data-upload/cdr_upload_parser.php at line 184 */
$stmt = $thisdbprepare(" INSERT INTO upload_activity_logs ( user_id, username, module_name, file_name, file_size, total_records, inserted_records, failed_records, upload_status, error_reason, ip_address, document_job_id ) VALUES ( :uid, :uname, :mod, :fname, :fsize, :total, :inserted, :failed, :status, :reason, :ip, :job_id ) RETURNING id ");

/* Used in: modules/data-upload/cdr_upload_parser.php at line 234 */
$stmt = $thisdbprepare('SELECT batch_id FROM upload_staging_batches WHERE document_job_id = :jid');

/* Used in: modules/data-upload/cdr_upload_parser.php at line 241 */
' UPDATE upload_staging_batches SET upload_log_id = :lid WHERE batch_id = :bid ');

/* Used in: modules/data-upload/cdr_upload_parser.php at line 245 */
' UPDATE upload_activity_logs SET staging_batch_id = :bid, verification_status = \'pending\' WHERE id = :lid ');

/* Used in: modules/data-upload/cdr_upload_parser.php at line 263 */
$stmt = $thisdbprepare('SELECT staging_tables FROM upload_staging_batches WHERE document_job_id = :jid');

/* Used in: modules/data-upload/cdr_upload_parser.php at line 274 */
SELECT staging_row_id, phone, other, starttime, duration, incoming, operator, is_duplicate, duplicate_reason FROM $cdrTable} ORDER BY staging_row_id LIMIT 10 ");

/* Used in: modules/data-upload/cdr_upload_parser.php at line 297 */
$stmt = $thisdbprepare(" SELECT ucid AS staging_id, phone, other, starttime, duration, incoming, NULL::varchar AS operator, asondate AS imported_at FROM cdatpcsuspect WHERE phone = :phone ORDER BY asondate DESC NULLS LAST, ucid DESC LIMIT 10 ");

/* Used in: modules/data-upload/admin_upload.php at line 88 */
'Please select a Network (Airtel, Jio, Vi, or BSNL) before preview.');

/* Used in: modules/data-upload/admin_upload.php at line 134 */
'Data inserted into the live table.', ]);

/* Used in: modules/data-upload/admin_upload.php at line 137 */
'Insert failed.']);

/* Used in: modules/data-upload/admin_upload.php at line 155 */
$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";

/* Used in: modules/data-upload/admin_upload.php at line 213 */
$dupStmt = $auditDbprepare(" SELECT id FROM upload_activity_logs WHERE table_name = :tbl AND content_fingerprint = :fp AND upload_status = 'Success' LIMIT 1 ");

/* Used in: modules/data-upload/admin_upload.php at line 221 */
$dupStmtfetchColumn()) { json_encode([ 'ok' false, 'error' 'This sheet was already imported into this table. Duplicate import blocked.', ]);

/* Used in: modules/data-upload/admin_upload.php at line 242 */
$dupSql = "SELECT 1 FROM $tableName WHERE phone = ? AND COALESCE(imei, '') = ? AND COALESCE(call_time, '') = ? LIMIT 1";

/* Used in: modules/data-upload/admin_upload.php at line 249 */
$sql = "INSERT INTO $tableName (phone, imei, call_time, duration, location, network) VALUES (?, ?, ?, ?, ?, ?)";

/* Used in: modules/data-upload/admin_upload.php at line 267 */
$stmtLog = $dbprepare(" INSERT INTO upload_activity_logs (user_id, username, module_name, file_name, file_size, upload_status, total_records, inserted_records, failed_records, ip_address, db_name, table_name, is_new_table, content_fingerprint, uploaded_at) VALUES (:uid, :uname, :module, :file, :size, :status, :total, :inserted, :failed, :ip, :db_name, :table_name, :is_new_table, :fp, NOW()) ");

/* Used in: modules/data-upload/admin_upload.php at line 311 */
$stmt = $dbprepare(' UPDATE upload_activity_logs SET document_job_id = :job_id, upload_status = \'Processing\', file_size = :fsize WHERE id = :id ');

/* Used in: modules/data-upload/admin_upload.php at line 321 */
$stmtLog = $dbprepare(" INSERT INTO upload_activity_logs ( user_id, username, module_name, file_name, file_size, total_records, inserted_records, failed_records, upload_status, ip_address, document_job_id, uploaded_at ) VALUES ( :uid, :uname, 'SDR', :fname, :fsize, 0, 0, 0, 'Processing', :ip, :job_id, NOW() ) RETURNING id ");

/* Used in: modules/data-upload/admin_upload.php at line 399 */
$vstmt = audit_db()prepare('SELECT * FROM upload_activity_logs WHERE id = :id');

/* Used in: modules/data-upload/admin_upload.php at line 440 */
'Please select a valid module (CDR or SDR).';

/* Used in: modules/data-upload/admin_upload.php at line 442 */
'Please select a Network (Airtel, Jio, Vi, or BSNL) before uploading.';

/* Used in: modules/data-upload/admin_upload.php at line 444 */
'Please select a valid file to upload.';

/* Used in: modules/data-upload/admin_upload.php at line 459 */
'Please select a valid file to upload.';

/* Used in: modules/data-upload/admin_upload.php at line 530 */
'Please select a valid file to upload.';

/* Used in: modules/data-upload/admin_upload.php at line 542 */
'Data loaded into staging. Insert it into the live table when you are ready.';

/* Used in: modules/data-upload/admin_upload.php at line 567 */
'insert') { $jobId = ($first['job_id'] );

/* Used in: modules/data-upload/admin_upload.php at line 572 */
'Insert failed.');

/* Used in: modules/data-upload/admin_upload.php at line 623 */
'Import CSV or Excel into a custom database table.', 'Common Data Upload Framework', };

/* Used in: modules/data-upload/admin_upload.php at line 667 */
'insert'], true) ? $_POST['next_action'] : '';

/* Used in: modules/data-upload/admin_upload_history.php at line 20 */
'Data inserted into the live table.', ]);

/* Used in: modules/data-upload/admin_upload_history.php at line 23 */
'Insert failed.']);

/* Used in: modules/data-upload/admin_upload_history.php at line 35 */
$userStmt = $dbquery("SELECT DISTINCT username FROM upload_activity_logs ORDER BY username");

/* Used in: modules/data-upload/admin_upload_history.php at line 102 */
$countQuery = "SELECT COUNT(*) FROM upload_activity_logs WHERE " . $whereClause;

/* Used in: modules/data-upload/admin_upload_history.php at line 119 */
$selectQuery = " SELECT l.*, b.verified_by, b.verification_status AS batch_verification_status FROM upload_activity_logs l LEFT JOIN upload_staging_batches b ON b.batch_id = l.staging_batch_id OR (l.staging_batch_id IS NULL AND b.document_job_id = l.document_job_id) WHERE $whereClause} ORDER BY l.uploaded_at DESC LIMIT :limit OFFSET :offset ";

/* Used in: modules/data-upload/upload_verification_service.php at line 20 */
$stmt = $thisdbprepare(' SELECT b.*, l.module_name, l.file_name, l.document_job_id, l.username FROM upload_activity_logs l LEFT JOIN upload_staging_batches b ON b.batch_id = l.staging_batch_id WHERE l.id = :id ');

/* Used in: modules/data-upload/upload_verification_service.php at line 32 */
$stmt2 = $thisdbprepare('SELECT * FROM upload_staging_batches WHERE document_job_id = :jid');

/* Used in: modules/data-upload/upload_verification_service.php at line 41 */
$stmt3 = $thisdbprepare(' SELECT b.* FROM document_jobs old_job JOIN document_jobs new_job ON new_job.file_sha256 = old_job.file_sha256 AND new_job.job_id > old_job.job_id AND new_job.status = \'pending_verification\' JOIN upload_staging_batches b ON b.document_job_id = new_job.job_id WHERE old_job.job_id = :jid ORDER BY new_job.job_id DESC LIMIT 1 ');

/* Used in: modules/data-upload/upload_verification_service.php at line 57 */
' UPDATE upload_activity_logs SET document_job_id = :jid, staging_batch_id = :bid, upload_status = \'Pending Verification\', verification_status = \'pending\', inserted_records = 0, failed_records = 0 WHERE id = :lid ')execute([ ':jid' $batch['document_job_id'], ':bid' $batch['batch_id'], ':lid' $logId, ]);

/* Used in: modules/data-upload/upload_verification_service.php at line 71 */
' UPDATE upload_staging_batches SET upload_log_id = :lid WHERE batch_id = :bid ')execute([':lid' $logId, ':bid' $batch['batch_id']]);

/* Used in: modules/data-upload/upload_verification_service.php at line 86 */
$countStmt = $thisdbquery("SELECT COUNT(*) FROM $qualifiedTable}");

/* Used in: modules/data-upload/upload_verification_service.php at line 89 */
$stmt = $thisdbprepare(" SELECT * FROM $qualifiedTable} ORDER BY staging_row_id LIMIT :lim OFFSET :off ");

/* Used in: modules/data-upload/upload_verification_service.php at line 130 */
$sql = 'UPDATE ' . $qualifiedTable . ' SET ' . implode(', ', $sets) . ' WHERE staging_row_id = :id';

/* Used in: modules/data-upload/upload_verification_service.php at line 144 */
$stmt = $thisdbprepare("SELECT * FROM $qualifiedTable} WHERE staging_row_id = :id");

/* Used in: modules/data-upload/upload_verification_service.php at line 179 */
$stmt = $thisdbprepare("SELECT column_name FROM information_schema.columns WHERE table_schema = 'upload_staging' AND table_name = :t");

/* Used in: modules/data-upload/upload_verification_service.php at line 199 */
$sql = 'UPDATE ' . $qualifiedTable . ' SET ' . implode(', ', $sets) . ' WHERE staging_row_id = :id';

/* Used in: modules/data-upload/upload_verification_service.php at line 210 */
UPDATE $qualifiedTable} s SET duration = 0 WHERE duration <> 0 AND (COALESCE(s.call_type, '') ~* '(sms|mms|message|text|ussd)' OR COALESCE(s.otherinfo, '') ~* '(sms|mms|message|text|ussd)') $rowFilter} ");

/* Used in: modules/data-upload/upload_verification_service.php at line 220 */
UPDATE $qualifiedTable} s SET celltowerid = sub.network_id, first_cellid = COALESCE(NULLIF(s.first_cellid, ''), sub.network_id) FROM LATERAL ( SELECT COALESCE(NULLIF(p.areadescription, ''), p.phoneprefix) AS network_id FROM cdatphonearea p WHERE regexp_replace(COALESCE(s.phone, ''), '\\D', '', 'g') LIKE p.phoneprefix || '%' ORDER BY LENGTH(p.phoneprefix) DESC LIMIT 1 ) sub WHERE COALESCE(s.celltowerid, '') = '' AND COALESCE(s.phone, '') <> '' $rowFilter} ");

/* Used in: modules/data-upload/upload_verification_service.php at line 241 */
UPDATE $qualifiedTable} s SET tower_key = COALESCE(t.bts_key, s.tower_key), state_key = COALESCE(t.state_key, s.state_key), otherinfo = CASE WHEN COALESCE(s.otherinfo, '') = '' THEN t.operator ELSE s.otherinfo END FROM LATERAL ( SELECT state_key, operator, NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')::numeric AS bts_key FROM cdatcelltowerareanew WHERE celltowerid = s.celltowerid ORDER BY lastupdate DESC NULLS LAST LIMIT 1 ) t WHERE COALESCE(s.celltowerid, '') <> '' $rowFilter} ");

/* Used in: modules/data-upload/upload_verification_service.php at line 267 */
UPDATE $qualifiedTable} SET is_duplicate = FALSE, duplicate_reason = NULL");

/* Used in: modules/data-upload/upload_verification_service.php at line 269 */
UPDATE $qualifiedTable} s SET is_duplicate = TRUE, duplicate_reason = 'exists_in_main' WHERE EXISTS ( SELECT 1 FROM cdatpcsuspect t WHERE t.phone = s.phone AND t.other IS NOT DISTINCT FROM s.other AND t.starttime = s.starttime AND t.duration IS NOT DISTINCT FROM s.duration AND t.incoming IS NOT DISTINCT FROM s.incoming ) ");

/* Used in: modules/data-upload/upload_verification_service.php at line 300 */
SELECT COUNT(*) FROM $qualifiedTable} WHERE is_duplicate = TRUE")fetchColumn();

/* Used in: modules/data-upload/upload_verification_service.php at line 301 */
SELECT COUNT(*) FROM $qualifiedTable} WHERE COALESCE(is_duplicate, FALSE) = FALSE")fetchColumn();

/* Used in: modules/data-upload/upload_verification_service.php at line 315 */
UPDATE $qualifiedTable} SET is_duplicate = FALSE, duplicate_reason = NULL");

/* Used in: modules/data-upload/upload_verification_service.php at line 318 */
UPDATE $qualifiedTable} s SET is_duplicate = TRUE, duplicate_reason = COALESCE(s.duplicate_reason, 'duplicate_in_batch') WHERE staging_row_id NOT IN ( SELECT MIN(staging_row_id) FROM $qualifiedTable} GROUP BY $groupExpr} ) ");

/* Used in: modules/data-upload/upload_verification_service.php at line 332 */
UPDATE $qualifiedTable} s SET is_duplicate = TRUE, duplicate_reason = 'exists_in_main' WHERE EXISTS ( SELECT 1 FROM public.$logicalTarget} t WHERE $matchExpr} ) ");

/* Used in: modules/data-upload/upload_verification_service.php at line 507 */
' UPDATE upload_staging_batches SET verification_status = \'approved\', verified_at = NOW(), verified_by = :user WHERE batch_id = :id ')execute([':user' $username, ':id' $batchId]);

/* Used in: modules/data-upload/upload_verification_service.php at line 516 */
" UPDATE upload_staging_batches SET verification_status = 'approved', verified_at = NOW(), verified_by = :user WHERE module = 'cdr' AND verification_status = 'pending' AND staging_tables->>'cdr' = :tbl AND batch_id <> :id RETURNING document_job_id ");

/* Used in: modules/data-upload/upload_verification_service.php at line 532 */
" UPDATE document_jobs SET status = 'completed', phase = 'completed', completed_at = NOW(), updated_at = NOW() WHERE job_id = :jid ")execute([':jid' $sjid]);

/* Used in: modules/data-upload/upload_verification_service.php at line 537 */
" UPDATE upload_activity_logs SET upload_status = 'Success', verification_status = 'approved' WHERE document_job_id = :jid ")execute([':jid' $sjid]);

/* Used in: modules/data-upload/upload_verification_service.php at line 545 */
' UPDATE upload_activity_logs SET upload_status = \'Success\', verification_status = \'approved\', inserted_records = :ins, failed_records = GREATEST(total_records - :ins, 0) WHERE staging_batch_id = :id OR document_job_id = :jid ')execute([ ':ins' $inserted, ':id' $batchId, ':jid' $batch['document_job_id'], ]);

/* Used in: modules/data-upload/upload_verification_service.php at line 556 */
' UPDATE document_jobs SET status = \'completed\', phase = \'completed\', completed_at = NOW(), updated_at = NOW() WHERE job_id = :jid ')execute([':jid' $batch['document_job_id']]);

/* Used in: modules/data-upload/upload_verification_service.php at line 634 */
" SELECT queue_id FROM upload_approval_queue WHERE batch_id = :bid AND status IN ('queued', 'running') ORDER BY queue_id DESC LIMIT 1 ");

/* Used in: modules/data-upload/upload_verification_service.php at line 645 */
$stmt = $thisdbprepare(" INSERT INTO upload_approval_queue (batch_id, module, username, status) VALUES (:bid, :mod, :user, 'queued') RETURNING queue_id ");

/* Used in: modules/data-upload/upload_verification_service.php at line 656 */
$stmt = $thisdbprepare(" SELECT * FROM upload_approval_queue WHERE module = :mod AND status = 'queued' ORDER BY queued_at ASC, queue_id ASC LIMIT 1 ");

/* Used in: modules/data-upload/upload_verification_service.php at line 669 */
$stmt = $thisdbprepare('SELECT * FROM upload_approval_queue WHERE queue_id = :id');

/* Used in: modules/data-upload/upload_verification_service.php at line 677 */
$stmt = $thisdbprepare(" SELECT COUNT(*) FROM upload_approval_queue q1 WHERE q1.module = :mod AND q1.status IN ('queued', 'running') AND q1.queued_at <= ( SELECT q2.queued_at FROM upload_approval_queue q2 WHERE q2.queue_id = :qid ) ");

/* Used in: modules/data-upload/upload_verification_service.php at line 691 */
$stmt = $thisdbprepare(" SELECT COUNT(*) FROM upload_approval_queue WHERE module = :mod AND status = 'running' ");

/* Used in: modules/data-upload/upload_verification_service.php at line 701 */
" UPDATE upload_approval_queue SET status = 'running', started_at = NOW(), error_message = NULL WHERE queue_id = :id ")execute([':id' $queueId]);

/* Used in: modules/data-upload/upload_verification_service.php at line 710 */
" UPDATE upload_approval_queue SET status = 'completed', inserted_records = :ins, completed_at = NOW(), error_message = NULL WHERE queue_id = :id ")execute([':ins' $inserted, ':id' $queueId]);

/* Used in: modules/data-upload/upload_verification_service.php at line 719 */
" UPDATE upload_approval_queue SET status = 'failed', error_message = :err, completed_at = NOW() WHERE queue_id = :id ")execute([':err' $error, ':id' $queueId]);

/* Used in: modules/data-upload/upload_verification_service.php at line 728 */
" UPDATE upload_approval_queue SET status = 'failed', error_message = 'Promotion timed out and was reset. Please retry approval.', completed_at = NOW() WHERE module = :mod AND status = 'running' AND started_at < NOW() - INTERVAL '2 hours' ")execute([':mod' $module]);

/* Used in: modules/data-upload/upload_verification_service.php at line 741 */
$stmt = $thisdbprepare('SELECT hashtext(:k)');

/* Used in: modules/data-upload/upload_verification_service.php at line 749 */
$stmt = $thisdbquery('SELECT pg_try_advisory_lock(' . $key . ')');

/* Used in: modules/data-upload/upload_verification_service.php at line 756 */
'SELECT pg_advisory_unlock(' . $key . ')');

/* Used in: modules/data-upload/upload_verification_service.php at line 766 */
" SELECT queue_id FROM upload_approval_queue WHERE batch_id = :id AND status = 'running' LIMIT 1 ");

/* Used in: modules/data-upload/upload_verification_service.php at line 775 */
" UPDATE upload_approval_queue SET status = 'cancelled', completed_at = NOW() WHERE batch_id = :id AND status = 'queued' ")execute([':id' $batchId]);

/* Used in: modules/data-upload/upload_verification_service.php at line 789 */
$stmt = $thisdbprepare(" SELECT COUNT(*) FROM upload_staging_batches WHERE module = 'cdr' AND verification_status = 'pending' AND staging_tables->>'cdr' = :tbl AND batch_id <> :id ");

/* Used in: modules/data-upload/upload_verification_service.php at line 804 */
' UPDATE upload_staging_batches SET verification_status = \'rejected\', verified_at = NOW(), verified_by = :user WHERE batch_id = :id ')execute([':user' $username, ':id' $batchId]);

/* Used in: modules/data-upload/upload_verification_service.php at line 810 */
' UPDATE upload_activity_logs SET upload_status = \'Rejected\', verification_status = \'rejected\' WHERE staging_batch_id = :id OR document_job_id = :jid ')execute([':id' $batchId, ':jid' $batch['document_job_id']]);

/* Used in: modules/data-upload/upload_verification_service.php at line 816 */
' UPDATE document_jobs SET status = \'rejected\', phase = \'rejected\', updated_at = NOW() WHERE job_id = :jid ')execute([':jid' $batch['document_job_id']]);

/* Used in: modules/data-upload/upload_verification_service.php at line 834 */
$sql = " INSERT INTO cdatpcsuspect ( ucid, phone, other, starttime, duration, incoming, imeinumber, imsinumber, celltowerid, otherinfo, tower_key, provider_key, state_key, first_cellid, last_cellid, roaming_nw, call_type, calling_no, called_no, asondate ) SELECT s.ucid, s.phone, s.other, s.starttime, s.duration, s.incoming, s.imeinumber, s.imsinumber, s.celltowerid, s.otherinfo, s.tower_key, s.provider_key, s.state_key, s.first_cellid, s.last_cellid, s.roaming_nw, s.call_type, s.calling_no, s.called_no, COALESCE(s.asondate, NOW()) FROM $qualifiedTable} s WHERE COALESCE(s.is_duplicate, FALSE) = FALSE AND NOT EXISTS ( SELECT 1 FROM cdatpcsuspect t WHERE t.phone = s.phone AND t.other IS NOT DISTINCT FROM s.other AND t.starttime = s.starttime AND t.duration IS NOT DISTINCT FROM s.duration AND t.incoming IS NOT DISTINCT FROM s.incoming ) ";

/* Used in: modules/data-upload/upload_verification_service.php at line 875 */
SELECT $colList} FROM $staging} WHERE COALESCE(is_duplicate, FALSE) = FALSE ");

/* Used in: modules/data-upload/upload_verification_service.php at line 904 */
$sql = "INSERT INTO $table} ($colList}) VALUES $allPlaceholders}";

/* Used in: modules/data-upload/upload_verification_service.php at line 920 */
$stmt = $thisdbprepare(" SELECT column_name FROM information_schema.columns WHERE table_schema = 'upload_staging' AND table_name = :t ");

/* Used in: modules/data-upload/upload_verification_service.php at line 926 */
$stmt2 = $pdoprepare(" SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name = :t ");

/* Used in: modules/data-upload/upload_verification_service.php at line 939 */
$stmt = $thisdbprepare('SELECT * FROM upload_staging_batches WHERE batch_id = :id');

/* Used in: modules/data-upload/upload_verification_service.php at line 982 */
$stmt = $thisdbprepare('SELECT to_regclass(:t) IS NOT NULL');

/* Used in: modules/data-upload/admin_upload_page.php at line 77 */
'Insert failed.') : ('Service error (HTTP ' . $code . ').');

/* Used in: modules/data-upload/admin_upload_page.php at line 89 */
' UPDATE upload_activity_logs SET upload_status = \'Success\', verification_status = \'approved\', inserted_records = :ins, failed_records = GREATEST(COALESCE(total_records, 0) - :ins, 0) WHERE document_job_id = :jid ')execute([ ':ins' $inserted, ':jid' $jobId, ]);

/* Used in: modules/data-upload/admin_upload_page.php at line 107 */
'Data inserted into the live table.', ];

/* Used in: modules/data-upload/admin_upload_job_status.php at line 30 */
$stmt = $dbprepare('SELECT batch_id FROM upload_staging_batches WHERE document_job_id = :jid');

/* Used in: modules/data-upload/admin_upload_job_status.php at line 34 */
' UPDATE upload_activity_logs SET upload_status = \'Pending Verification\', verification_status = \'pending\', staging_batch_id = :bid, total_records = :total, inserted_records = 0, failed_records = 0, error_reason = NULL WHERE document_job_id = :jid ')execute([ ':bid' $batchId, ':total' ($job['total_records'] ), ':jid' $jobId, ]);

/* Used in: modules/data-upload/admin_upload_job_status.php at line 52 */
' UPDATE upload_activity_logs SET total_records = :total, inserted_records = :ins, failed_records = 0 WHERE document_job_id = :jid AND upload_status = \'Processing\' ')execute([ ':total' ($job['total_records'] ), ':ins' ($job['rows_committed'] ), ':jid' $jobId, ]);

/* Used in: modules/data-upload/admin_upload_job_status.php at line 64 */
' UPDATE upload_activity_logs SET upload_status = \'Success\', total_records = :total, inserted_records = :ins, failed_records = GREATEST(:total - :ins, 0), error_reason = NULL WHERE document_job_id = :jid AND upload_status = \'Processing\' ')execute([ ':total' ($job['total_records'] ), ':ins' ($job['rows_committed'] ), ':jid' $jobId, ]);

/* Used in: modules/data-upload/admin_upload_job_status.php at line 78 */
' UPDATE upload_activity_logs SET upload_status = \'Failed\', error_reason = :err, inserted_records = :ins, failed_records = GREATEST(:total - :ins, 0) WHERE document_job_id = :jid ')execute([ ':err' $job['error_message'] 'Processing failed.', ':ins' ($job['rows_committed'] ), ':total' ($job['total_records'] ), ':jid' $jobId, ]);

/* Used in: modules/data-upload/admin_upload_job_status.php at line 100 */
$logStmt = $dbprepare('SELECT id FROM upload_activity_logs WHERE document_job_id = :jid ORDER BY id DESC LIMIT 1');

/* Used in: modules/interrogation-reports/ir_search_by_head.php at line 36 */
$sql8 = "SELECT 'DETAILS OF : ' || '$number1' as PHONE1";

/* Used in: modules/interrogation-reports/ir_search_by_head.php at line 39 */
$sql9 = "SELECT A.IRKEY,(CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') ELSE '' END PDACT_KEY,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) THEN [IMAGE] ELSE (SELECT [IMAGE] FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END AS [IMAGE], NAME,ALIAS_NAME,FATHER_NAME,AGE,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION FROM FORMS..IR_PARTICULARS A INNER JOIN FORMS..OFFENCE_DETAILS B ON B.CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' AND B.MO LIKE '%'+REPLACE('$number1',' ','%')+'%' AND ltrim(rtrim('$number1'))!='' and len(replace('$number1',' ',''))>'4' AND A.IRKEY=B.IRKEY LEFT JOIN FORMS..IMAGE_TABLE C ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),C.IRKEY)";

/* Used in: modules/interrogation-reports/ir_search_by_head_gender.php at line 10 */
'Select crime head and gender and try again.');

/* Used in: modules/interrogation-reports/ir_search_by_head_gender.php at line 13 */
'PLZ Select Gender', 'FEMALE' 'FEMALE', 'MALE' 'MALE', 'TRANSGENDER' 'TRANSGENDER', ];

/* Used in: modules/interrogation-reports/ir_search_by_head_gender.php at line 19 */
'PLZ Select Gender', true);

/* Used in: modules/interrogation-reports/ir_search_by_head_gender.php at line 45 */
$sql8 = "SELECT 'DETAILS OF : ' || '$number' as PHONE1";

/* Used in: modules/interrogation-reports/ir_search_by_head_gender.php at line 47 */
$sql9 = "SELECT DISTINCT A.IRKEY,(CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') ELSE '' END PDACT_KEY,NAME,ALIAS_NAME,FATHER_NAME,AGE,SEX,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION,CONVERT(VARCHAR(20),DATE_OF_ARREST) DATE_OF_ARREST FROM FORMS..IR_PARTICULARS A INNER JOIN FORMS..OFFENCE_DETAILS B ON A.SEX ='$number' AND (B.CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' OR B.MO LIKE '%'+REPLACE('$number1',' ','%')+'%') AND ltrim(rtrim('$number'))!='' AND A.IRKEY=B.IRKEY ORDER BY DATE_OF_ARREST DESC";

/* Used in: modules/interrogation-reports/ir_search_by_head_gender.php at line 119 */
'PLZ Select Gender', true), 'BTN_CDAT', 'Submit' );

/* Used in: modules/interrogation-reports/ir.php at line 51 */
$sql0="SELECT NAME,FATHER_NAME,IMAGE,B.CCNO FROM IR_PARTICULARS A LEFT JOIN IMAGE_TABLE B ON A.IRKEY=B.IRKEY WHERE A.IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 53 */
$sql1="SELECT DISTINCT IRKEY, NAME, ALIAS_NAME, FATHER_NAME, AGE, CONVERT(VARCHAR,DATE_OF_BIRTH,20) DATE_OF_BIRTH, NATIONALITY, RELIGION, CASTE, COMMUNITY, PRESENT_ADDRESS, PERMANENT_ADDRESS, MOBILE, EMAIL_ID, SOCIAL_MEDIA_ACCOUNTS, AADHAR_NO, RATION_CARD_NO, VOTERID, PASSPORT, PANCARD, ELECTRICITY_CONNECTION, GAS_CONNECTION, VEHICLES, DRIVING_LICENSE, OTHER_ID_PROOFS, SEX, BUILT, HEIGHT, EYES, HAIR, FACE, COLOUR, TEETH, NOSE, BEARD, MUSTACHES, EAR, IDENTIFICATION_MARKS, DEFORMITIES_PECULIARITIES, LANGUAGE_DIALECT, BURN_MARKS, LEUCODEMA, MOLE, SCAR, TATTOO, LIVING_STATUS, MARITAL_STATUS, EDUCATION_DETAILS, OCCUPATION, INCOME_GROUP, REGULAR_HABITS, CATEGORY FROM FORMS..IR_PARTICULARS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 65 */
$sql2="SELECT DISTINCT RELATIONSHIP RELATION,NAME+' FATHER_OR_SPOUSE: '+FATHER_OR_SPOUSE+' OCCUPATION: '+OCCUPATION +' PHONE_NO: '+PHONE+' AGE: '+AGE NAME,PRESENT_ADDRESS ADDRESS,CRIMINAL_BACKGROUND,STATUS FROM FAMILY_HISTORY WHERE IRKEY='$number' ORDER BY RELATION";

/* Used in: modules/interrogation-reports/ir.php at line 68 */
$sql3="SELECT DISTINCT PERIOD_OF_OFFENCE FROM OFFENCE_DETAILS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 70 */
$sql4="SELECT DISTINCT TOWN_CITY_OR_VILLAGE,POLICE_STATION_LIMITS,NAME+' S/O '+FATHER_NAME+' AGE: '+AGE+' OCCUPATION: '+OCCUPATION NAME ,PHONE,ADDRESS_OF_CONTACT_PERSON ADDRESS FROM LOCAL_CONTACTS_FACILITATORS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 74 */
$sql5="SELECT DISTINCT REGULAR_HABITS FROM IR_PARTICULARS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 76 */
$sql6="SELECT DISTINCT INDULGANCE_BEFORE_OFFENCE FROM OFFENCE_DETAILS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 79 */
$sql7="SELECT DISTINCT CRIME_HEAD,SUB_TYPE SUB_HEAD,MO FROM OFFENCE_DETAILS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 82 */
$sql8="SELECT DISTINCT REGULAR_RESIDENCE,PREPARATION_OF_OFFENCE,AFTER_OFFENCE FROM OFFENCE_DETAILS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 85 */
$sql9="SELECT DISTINCT PROPERTY_STOLEN,PROPERTY_RECOVERED,RECEIVER_NAME,RECEIVER_ADDRESS,REMARKS FROM DISPOSAL_OF_PROPERTY WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 88 */
$sql10="SELECT DISTINCT HOW_SHARE_IS_SPENT FROM DISPOSAL_OF_PROPERTY WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 91 */
$sql11="SELECT DISTINCT DISTRICT,CONFESSED_POLICE_STATION,CONFESSED_CRIME_NO,CONFESSED_YEAR,CONFESSED_SEC_OF_LAW,ASSOCIATES,PROPERTY_STOLEN,PROPERTY_RECOVERED, REMARKS FROM PREVIOUS_OFFENCE_DETAILS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 94 */
$sql12="SELECT DISTINCT CONVERT(VARCHAR,DATE_OF_ARREST) DATE_OF_ARREST,PLACE_OF_ARREST,'CRIME_NO: '+CONVERT(VARCHAR,CRIME_NO)+'/'+CONVERT(VARCHAR,YEAR)+' SEC_OF_LAW:'+SEC_OF_LAW [CRIME_NO_SEC_OF_LAW],POLICE_STATION,SUB_DIVISION,DISTRICT_OR_UNIT, ARRESTED_BY,INTERROGATED_BY,OTHERS_WHO_CAN_IDENTIFY FROM OFFENCE_DETAILS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 99 */
$sql13="SELECT DISTINCT BRIEF_FACTS1+' '+BRIEF_FACTS2+' '+BRIEF_FACTS3 BRIEF_FACTS FROM BRIEF_FACTS WHERE IRKEY='$number'";

/* Used in: modules/interrogation-reports/ir.php at line 104 */
$sql20="select DISTINCT IRKEY,COUNT(*) TOTAL_NBWS_PENDING,FIRST_HEARING_DATE,DECISION_DATE,CASE_STATUS,NEXT_HEARING_DATE,NATURE_OF_DISPOSAL,COURT_NUMBER_AND_JUDGE,STAGE_OF_CASE, PETITIONER_RESPONDENT,ACT_AND_SEC from nbws_verify_data_important WHERE CASE_STATUS LIKE '%PENDING%' AND IRKEY='$number' GROUP BY IRKEY,FIRST_HEARING_DATE,DECISION_DATE,CASE_STATUS,NEXT_HEARING_DATE,NATURE_OF_DISPOSAL,COURT_NUMBER_AND_JUDGE,STAGE_OF_CASE, PETITIONER_RESPONDENT,ACT_AND_SEC";

/* Used in: modules/interrogation-reports/ir_search.php at line 38 */
$sql8 = "SELECT 'DETAILS OF : ' + ? as PHONE1";

/* Used in: modules/interrogation-reports/ir_search.php at line 43 */
$sql9 = "SELECT DISTINCT A.IRKEY, (CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT, CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') ELSE '' END PDACT_KEY, A.NAME,A.ALIAS_NAME,A.FATHER_NAME,A.AGE,A.PRESENT_ADDRESS,A.CRIME_HEAD,A.MO,A.CRIME_NO,A.YEAR,A.SEC_OF_LAW,A.POLICE_STATION, CONVERT(VARCHAR(20),A.DATE_OF_ARREST) DATE_OF_ARREST FROM FORMS..IR_PARTICULARS A INNER JOIN FORMS..OFFENCE_DETAILS B ON A.NAME LIKE '%' + REPLACE(?, ' ', '%') + '%' AND (B.CRIME_HEAD LIKE '%' + REPLACE(?, ' ', '%') + '%' OR B.MO LIKE '%' + REPLACE(?, ' ', '%') + '%') AND LTRIM(RTRIM(?)) != '' AND LEN(REPLACE(?, ' ', '')) > '4' AND A.IRKEY = B.IRKEY ORDER BY DATE_OF_ARREST DESC";

/* Used in: modules/others/offender_fd.php at line 37 */
$sql0 = "SELECT ACC_NAME,IMAGE FROM COMPLETE_MO_CLASSIFICATION A LEFT JOIN MO_IMAGE_TABLE B ON A.MO_KEY=B.MO_KEY WHERE A.MO_KEY='$number'";

/* Used in: modules/others/offender_fd.php at line 39 */
$sql1 = "SELECT DISTINCT MO_KEY, PHONE, ROLE, CATEGORY, ACC_NAME, FATHER_NAME, DATE_OF_BIRTH, AGE, FULLADDRESS, CITY_OR_DISTRICT, STATE, ID_PROOF, CRIME_HEAD, MO1, MO2, CRIME_NO, Year, SEC_OF_LAW, DATE_OF_ARREST, PLACE_OF_OFF, off_lat, off_long, POLICE_STATION, PS_DIVISION, PS_ZONE, INC_OFFICER, OFFICIAL_MAILID FROM CDATDUPL..COMPLETE_MO_CLASSIFICATION WHERE MO_KEY='$number'";

/* Used in: modules/others/vehicle_search.php at line 38 */
$sql8 = "SELECT 'VEHICLE ADDRESS SEARCH' as PHONE1";

/* Used in: modules/others/vehicle_search.php at line 41 */
$sql9 = "SELECT REGN_NO, FULLNAME AS NAME, FATHERNAME AS FATHER_NAME, FULLADDRESS + ', ' + CITY AS ADDRESS, PHONE AS PHONE_NO, MKR_CLAS + ', COLOR: ' + COLOUR + ', ' + VEH_CLASS AS VEHICLE_TYPE, ENG_NO, CHAS_NO, CONVERT(VARCHAR, ISS_DT, 106) AS ISSUED_DATE FROM CDATDUPL.[dbo].[CDAT_RTA] WHERE REGN_NO LIKE ?";

/* Used in: modules/others/offender_search_by_mo.php at line 38 */
$sql8 = "SELECT 'DETAILS OF : ' + ? as PHONE1";

/* Used in: modules/others/offender_search_by_mo.php at line 43 */
$sql9 = "SELECT DISTINCT MO_KEY, ACC_NAME AS ACCUSED_NAME, FATHER_NAME, AGE, MO1, MO2, POLICE_STATION FROM CDATDUPL..COMPLETE_MO_CLASSIFICATION WHERE (MO1 LIKE ? OR MO2 LIKE ? OR CRIME_HEAD LIKE ?)";

/* Used in: modules/others/training_module1.php at line 14 */
'Select search criteria', 'EMPLOYEE_ID' 'EMPLOYEE ID', 'GENERAL_NO' 'GENERAL NO', 'NAME' 'NAME', ];

/* Used in: modules/others/training_module1.php at line 20 */
'Select rank', 'INSPECTOR' 'INSPECTOR', 'SI' 'SI', 'ASI' 'ASI', 'HC' 'HC', 'PC' 'PC', 'HG' 'HG', ];

/* Used in: modules/others/training_module1.php at line 29 */
'Select search criteria', true) . cdat_sum_field_text('EMPLOYEE_SEARCH_NO', 'Emp Search', $searchNo, 'CAF', 'Emp Search') . cdat_sum_searchable_select('EMPLOYEE_SEARCH_RANK', 'Rank', $rankOptions, $rank, 'Select rank', false);

/* Used in: modules/others/training_module1.php at line 58 */
$sql8 = "SELECT 'EMPLOYEE SEARCH IN PWDMS' as PHONE1";

/* Used in: modules/others/training_module1.php at line 59 */
$sql9 = "SELECT DISTINCT EMPLOYEE_ID,NAME,[RANK],[ROLE],GENERAL_NO,WING_NAME,ZONE_NAME,DIVISION_NAME, POLICE_STATION FROM TRAINING_DB.TRAINING_STRENGTH_PARTICULARS WHERE $number like '%' || '$number1' || '%' AND RANK LIKE '%' || '$number2' || '%'";

/* Used in: modules/others/training_module1.php at line 62 */
$sql10 = "SELECT 'EMPLOYEE SEARCH IN TRAINING DATA' as PHONE1";

/* Used in: modules/others/training_module1.php at line 63 */
$sql11 = "SELECT DISTINCT EMPLOYEE_ID,GENERAL_NO,NAMES NAME,PS_NAME POLICE_STATION,PH_NO PHONE_NO,ZONE, RANK,COURSE_NAME,START_DATE,END_DATE FROM TRNG_ATT_WITH_EMPID WHERE $number like '%' || '$number1' || '%' AND RANK LIKE '%' || '$number2' || '%'";

/* Used in: modules/others/training_module1.php at line 172 */
'Select search criteria', true) . cdat_sum_field_text('EMPLOYEE_SEARCH_NO', 'Emp Search', '', 'CAF', 'Emp Search') . cdat_sum_searchable_select('EMPLOYEE_SEARCH_RANK', 'Rank', $rankOptions, '', 'Select rank', false), 'BTN_CDAT', 'Submit' );

/* Used in: modules/others/rowdysheeter_ps_wise_search.php at line 10 */
'Select a police station and try again.');

/* Used in: modules/others/rowdysheeter_ps_wise_search.php at line 13 */
$query = "SELECT DISTINCT UPPER(LTRIM(RTRIM(POLICE_STATION))) POLICE_STATION FROM CDATDUPL..ROWDY_SHEETER_DATA1";

/* Used in: modules/others/rowdysheeter_ps_wise_search.php at line 15 */
'Select Police Station'];

/* Used in: modules/others/rowdysheeter_ps_wise_search.php at line 28 */
'Select Police Station', true );

/* Used in: modules/others/rowdysheeter_ps_wise_search.php at line 54 */
$sql0 = "CREATE TEMP TABLE TEMP AS SELECT DISTINCT IRKEY,PDACT_KEY,NAME,AGE,FATHER_NAME,PHONE,PRESENT_ADDRESS,LAT_P PRESENT_ADDRESS_LAT, LONG_P PRESENT_ADDRESS_LONG,PERMANENT_ADDRESS,LAT PERMANENT_ADD_LAT,LONG PERMANENT_ADD_LONG,ID_PROOF_TYPE+' '+ID_NO IDPROOF, COMMUNAL_NONCOMMUNAL COMMUNAL_STATUS,LATEST_BIND_OVER_DATE BIND_OVER_DATE,POLICE_STATION,PRESENT_ACTIVITY,DATE_OF_OPENING_RWD FROM ROWDY_SHEETER_DATA1 WHERE POLICE_STATION LIKE '%$number%'";

/* Used in: modules/others/rowdysheeter_ps_wise_search.php at line 59 */
$sql1 = "select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,PHONE,PRESENT_ADDRESS,PERMANENT_ADDRESS,PRESENT_ACTIVITY,IDPROOF,COMMUNAL_STATUS, CONVERT(VARCHAR(20),DATE_OF_OPENING_RWD) AS DATE_OF_OPENING_RWD,POLICE_STATION,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END AS IMAGE FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";

/* Used in: modules/others/cellid_search.php at line 51 */
$sql1 ="select DISTINCT CELLTOWERID,BTS_ID,AREADESCRIPTION,SITEADDRESS,LAT,LONG,AZIMUTH,OPERATOR,STATE, OTYPE, LASTUPDATE from CDATCELLTOWERAREANEW WHERE CELLTOWERID LIKE '$likePattern}' $opFilter} $stateFilter} ORDER BY LASTUPDATE DESC";

/* Used in: modules/others/common_cnts.php at line 18 */
'Select compare', false );

/* Used in: modules/others/common_cnts.php at line 22 */
'Select number'];

/* Used in: modules/others/common_cnts.php at line 31 */
'Select number', false );

/* Used in: modules/others/common_cnts.php at line 70 */
CREATE TEMP TABLE A2 AS SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL, MAX(STARTTIME) AS LAST_CALL, MAX(A.ASONDATE) AS LAST_UPDATED, NICKNAME || '_' || ROLE + ' MO:' + MO NICKNAME FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE = B.PHONE WHERE A.PHONE IN ('$number2') GROUP BY A.PHONE, NICKNAME, MO, ROLE";

/* Used in: modules/others/common_cnts.php at line 76 */
"CREATE TEMP TABLE A3 AS SELECT DISTINCT A.PHONE, FIRST_CALL, LAST_CALL, LAST_UPDATED, NICKNAME FROM #A1 A LEFT JOIN #A2 B ON A.PHONE = B.PHONE";

/* Used in: modules/others/common_cnts.php at line 78 */
CREATE TEMP TABLE A4 AS SELECT PHONE, FULLNAME, FULLADDRESS, CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM CDATADDRESS WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

/* Used in: modules/others/common_cnts.php at line 80 */
INSERT INTO #A4 SELECT PHONE, FULLNAME, FULLADDRESS, CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM ADDRESS_OTHER_STATE WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

/* Used in: modules/others/common_cnts.php at line 83 */
"CREATE TEMP TABLE A5 AS SELECT DISTINCT A.PHONE, COALESCE(CONVERT(VARCHAR, FIRST_CALL, 20), 'NIL') AS FIRST_CALL, COALESCE(CONVERT(VARCHAR, A.LAST_CALL, 20), 'NIL') AS LAST_CALL, COALESCE(CONVERT(VARCHAR, A.LAST_UPDATED, 20), 'NIL') AS LAST_UPDATED, COALESCE(NICKNAME, 'NIL') AS NICKNAME, CASE WHEN A.PHONE IN (SELECT PHONE FROM #A4) THEN FULLNAME + ', ' + B.FULLADDRESS + ', DOA: ' + CONVERT(VARCHAR, DOA, 106) + ', LAST UPDATE: ' + CONVERT(VARCHAR, EFF_FROM_DATE, 106) ELSE AREADESCRIPTION END AS ADDRESS FROM #A3 A LEFT JOIN #A4 B ON A.PHONE = B.PHONE LEFT JOIN CDATPHONEAREA E ON CASE WHEN LEN(A.PHONE) = 10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'CODE NOT AVAILABLE' END END LIKE PHONEPREFIX + '%' ORDER BY A.PHONE";

/* Used in: modules/others/common_cnts.php at line 92 */
"SELECT PHONE, FIRST_CALL, LAST_CALL, LAST_UPDATED, NICKNAME, CASE WHEN ADDRESS IS NULL AND LEN(PHONE) <> 10 THEN 'JUNK OR VOIP CALL' WHEN ADDRESS IS NULL AND SUBSTRING(PHONE, 1, 1) IN ('6','7','8','9') AND LEN(ADDRESS) >= 10 THEN 'CODE NOT AVAILABLE' ELSE ADDRESS END AS ADDRESS FROM #A5";

/* Used in: modules/others/common_cnts.php at line 98 */
$sql1 = "CREATE TEMP TABLE T AS SELECT * FROM CDATPCSUSPECT WHERE PHONE IN ('$number2')";

/* Used in: modules/others/common_cnts.php at line 99 */
$sql2 = "CREATE TEMP TABLE common_numbertable1 AS SELECT PHONE, OTHER, COUNT(OTHER) AS COUNT1 FROM #T GROUP BY OTHER, PHONE HAVING (COUNT(OTHER)) > 1 ORDER BY OTHER, PHONE";

/* Used in: modules/others/common_cnts.php at line 101 */
$sql3 = "CREATE TEMP TABLE common_numbertable2 AS SELECT OTHER, PHONE, COUNT(OTHER) COUNT1 FROM #common_numbertable1 GROUP BY OTHER, PHONE ORDER BY OTHER";

/* Used in: modules/others/common_cnts.php at line 103 */
$sql4 = "CREATE TEMP TABLE common_numbertable3 AS SELECT DISTINCT OTHER, (SELECT PHONE + ', ' FROM #common_numbertable2 US WHERE US.OTHER = SS.OTHER FOR XML PATH('')) [PHONES], (SELECT SUM(COUNT1) FROM #common_numbertable2 XX WHERE XX.OTHER = SS.OTHER) TOTALNUMBEROFPHONES FROM #common_numbertable2 SS GROUP BY SS.OTHER ORDER BY 1";

/* Used in: modules/others/common_cnts.php at line 109 */
$sql5 = "DELETE FROM #common_numbertable3 WHERE TOTALNUMBEROFPHONES = 1";

/* Used in: modules/others/common_cnts.php at line 112 */
$sql8 = "UPDATE #common_numbertable3 SET PHONES = LEFT(PHONES, LEN(PHONES) - 1) + ''";

/* Used in: modules/others/common_cnts.php at line 113 */
$sql9 = "SELECT DISTINCT A.OTHER, A.PHONES, A.TOTALNUMBEROFPHONES PHONE_COUNT, E.NICKNAME || '_' || ROLE OTHERS_NICKNAME, E.MO OTHERS_MO, CASE WHEN A.OTHER = C.PHONE THEN COALESCE(C.FULLNAME, '') + ', ' + COALESCE(C.FULLADDRESS, '') + ', DOA: ' + COALESCE(CONVERT(VARCHAR, C.DOA, 20), '') + ', LAST_UPDATED: ' + COALESCE(CONVERT(VARCHAR, C.EFF_FROM_DATE, 20), '') + ', ' + (CASE WHEN C.OPERATOR IS NULL THEN COALESCE(AREADESCRIPTION, '') ELSE C.OPERATOR END) WHEN A.OTHER = D.PHONE THEN COALESCE(D.FULLNAME, '') + ', ' + COALESCE(D.FULLADDRESS, '') + ', ' + COALESCE(CONVERT(VARCHAR, D.DOA, 20), '') + ', ' + (CASE WHEN D.OPERATOR IS NULL THEN COALESCE(AREADESCRIPTION, '') ELSE D.OPERATOR END) ELSE COALESCE(AREADESCRIPTION, '') END AS OTHER_ADDRESS FROM #common_numbertable3 A LEFT JOIN CDATADDRESS C ON A.OTHER = C.PHONE AND C.EFF_TO_DATE IS NULL AND LEN(A.OTHER) >= '10' LEFT JOIN ADDRESS_OTHER_STATE D ON A.OTHER = D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA ON CASE WHEN LEN(A.OTHER) = 10 THEN A.OTHER ELSE CASE WHEN LEN(A.OTHER) > 10 THEN '00' + A.OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX + '%' LEFT JOIN CDATSUSPECT E ON A.OTHER = E.PHONE WHERE LEN(A.OTHER) = '10' AND ISNUMERIC(A.OTHER) = '1' AND A.OTHER LIKE '[6-9]%' ORDER BY PHONE_COUNT DESC, OTHER DESC";

/* Used in: modules/others/common_cnts.php at line 150 */
DELETE FROM #common_numbertable3 WHERE NOT (TOTALNUMBEROFPHONES $op $n)");

/* Used in: modules/offenders-list/habitual.php at line 16 */
$sql8 = "select 'HABITUAL OFFENDERS' PHONE1";

/* Used in: modules/offenders-list/habitual.php at line 17 */
$sql9 = "SELECT IRKEY, NAME, ALIAS_NAME, FATHER_NAME, AGE, PRESENT_ADDRESS, ARRESTED_IN_CRIMEHEAD, MO, CRIME_NO, YEAR, SEC_OF_LAW, POLICE_STATION, count1, image FROM IRFORMS..HABITUAL_OFFENDERS ORDER BY COUNT1 desc";

/* Used in: modules/offenders-list/fp_list.php at line 16 */
$sql8 = "select 'UNDETECTED CASES MATCHED WITH OLD OFFENDERS FINGER PRINT LIST' PHONE1";

/* Used in: modules/offenders-list/fp_list.php at line 17 */
$sql9 = "select SNO, POLICE_STATION, ZONE, CRIME_NO, SECTION, TIN_NO, DATE_OF_IDENTITY, LOSS_OF_PROPERTY, NAME_AND_PARTICULARS, IRKEY, CCNO, DOA, REMARKS,IMAGE from IRFORMS..FINGERPRINT_MATCHED_UNDETECTED_CASES_WITHIMAGE ORDER BY ZONE,IRKEY";

/* Used in: modules/pd-act/pdact_mo_search.php at line 34 */
$sql0 = "CREATE TEMP TABLE temp AS SELECT distinct PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS, CONVERT(VARCHAR(20),Date_Of_Arrest) AS DATE_OF_PDACT,CRIME_HEAD,MINOR_HEAD,MODUSOPERENDI from PDACT_MAIN_TABLE WHERE (CRIME_HEAD LIKE '%$number%' OR MINOR_HEAD LIKE '%$number%' OR MODUSOPERENDI LIKE '%$number%' OR CRIME_HEAD_SEARCH LIKE '%$number%')";

/* Used in: modules/pd-act/pdact_mo_search.php at line 38 */
$sql1 = "select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS, CONVERT(VARCHAR(20),DATE_OF_PDACT) AS DATE_OF_PDACT,CRIME_HEAD,MINOR_HEAD,MODUSOPERENDI,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END AS IMAGE FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";

/* Used in: modules/pd-act/pdact_ps_wise_search.php at line 10 */
'Select a police station and try again.');

/* Used in: modules/pd-act/pdact_ps_wise_search.php at line 13 */
$query = "SELECT DISTINCT UPPER(LTRIM(RTRIM(PD_ACT_PS))) PD_ACT_PS FROM PDACT..PDACT_MAIN_TABLE";

/* Used in: modules/pd-act/pdact_ps_wise_search.php at line 15 */
'Select Police Station'];

/* Used in: modules/pd-act/pdact_ps_wise_search.php at line 27 */
'Select Police Station', true );

/* Used in: modules/pd-act/pdact_ps_wise_search.php at line 53 */
$sql0 = "CREATE TEMP TABLE temp AS SELECT distinct PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS, CONVERT(VARCHAR(20),Date_Of_Arrest) AS DATE_OF_PDACT from PDACT_MAIN_TABLE WHERE PD_ACT_PS LIKE '%$number%'";

/* Used in: modules/pd-act/pdact_ps_wise_search.php at line 56 */
$sql1 = "select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS, CONVERT(VARCHAR(20),DATE_OF_PDACT) AS DATE_OF_PDACT,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END AS IMAGE FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";

/* Used in: modules/pd-act/pdact_search.php at line 34 */
$sql0 = "CREATE TEMP TABLE temp AS SELECT distinct PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS, CONVERT(VARCHAR(20),Date_Of_Arrest) AS DATE_OF_PDACT from PDACT_MAIN_TABLE WHERE NAME LIKE '%$number%'";

/* Used in: modules/pd-act/pdact_search.php at line 37 */
$sql1 = "select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS, CONVERT(VARCHAR(20),DATE_OF_PDACT) AS DATE_OF_PDACT,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END AS IMAGE FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";

/* Used in: modules/pd-act/pdact_main.php at line 37 */
$sql0 = "CREATE TEMP TABLE TEMP AS SELECT distinct PDACT_KEY,IRKEY,NAME,FATHER_NAME,AGE,DISTRICT NATIVE_DISTRICT,STATE NATIVE_STATE from PDACT_MAIN_TABLE WHERE PDACT_KEY='$number'";

/* Used in: modules/pd-act/pdact_main.php at line 40 */
$sql2 = "select A.PDACT_KEY,A.IRKEY,A.NAME,A.FATHER_NAME,A.AGE,NATIVE_DISTRICT,NATIVE_STATE,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END AS IMAGE from #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)";

/* Used in: modules/pd-act/pdact_main.php at line 44 */
$sql1 = "SELECT distinct PD_ACT_PS,ZONE,FILE_NO,DETENU_NO,CONVERT(VARCHAR(20),ORDER_ISSUED_ON) ORDER_ISSUED_ON,APPROVAL_ORDERS_NO,CONFIRMATION_REVOCATION_ORDERS,CRIME_HEAD,MINOR_HEAD MODUSOPERENDI,POLICE_STATION,WHETHER_INVOLVED_IN_OTHER_UNIT_CASES,NAME_OF_UNITS,NO_OF_CASES, CONVERT(VARCHAR(20),DATE_OF_ARREST) PDACT_DATE,CONVERT(VARCHAR(20),DATE_OF_RELEASE) DATE_OF_RELEASE,BRIEF_FACTS FROM PDACT_MAIN_TABLE WHERE PDACT_KEY='$number'";

/* Used in: modules/common/login.php at line 63 */
"SELECT * FROM LOGINS WHERE USERNAME = ?", [$USERNAME] );

/* Used in: modules/common/login.php at line 77 */
"UPDATE LOGINS SET PASSWORD = ? WHERE USERNAME = ?", [$hashed, $USERNAME]);

/* Used in: modules/common/sqlsrv_compat.php at line 188 */
'/^\s*SELECT\b/i', $q) preg_match('/\bINTO\s+#([A-Za-z0-9_]+)/i', $q, $m)) { $q = preg_replace('/\bINTO\s+#([A-Za-z0-9_]+)/i', '', $q, );

/* Used in: modules/common/sqlsrv_compat.php at line 227 */
LEFT JOIN LATERAL (SELECT * FROM $table WHERE phone = ($outer).$outerCol AND eff_to_date IS NULL LIMIT 1) $alias ON true";

/* Used in: modules/common/sqlsrv_compat.php at line 239 */
LEFT JOIN LATERAL (SELECT * FROM $table WHERE phone = ($outer).phone AND eff_to_date IS NULL LIMIT 1) $alias ON true";

/* Used in: modules/common/sqlsrv_compat.php at line 249 */
LEFT JOIN LATERAL (SELECT * FROM cdatcelltowerareanew WHERE (celltowerid = $shortId} OR celltowerid = ($outer).celltowerid OR bts_id = ($outer).celltowerid)$keyFilter} ORDER BY lastupdate DESC NULLS LAST LIMIT 1) $alias ON true";

/* Used in: modules/common/sqlsrv_compat.php at line 275 */
'/\band\s+b\.lastupdate\s*=\s*\(\s*select\s+distinct\s+max\s*\(\s*lastupdate\s*\)\s+from\s+cdatcelltowerareanew\s+x(?:\s+with\s*\(\s*nolock\s*\))?\s+where\s+x\.celltowerid\s*=\s*b\.celltowerid[^)]+\)/i', '', $q );

/* Used in: modules/common/sqlsrv_compat.php at line 480 */
'/\bOUTER\s+APPLY\s*\(\s*SELECT\s+TOP\s+1\s+(.*?)\s+FROM\s+(.*?)\s+WHERE\s+(.*?)\s*\)\s*([A-Za-z0-9_]+)/is', ($m) { $cols = trim($m[]);

/* Used in: modules/common/sqlsrv_compat.php at line 496 */
LEFT JOIN LATERAL (SELECT $cols FROM $from WHERE $where ORDER BY lastupdate DESC NULLS LAST LIMIT 1) $alias ON TRUE";

/* Used in: modules/common/sqlsrv_compat.php at line 498 */
LEFT JOIN LATERAL (SELECT $cols FROM $from WHERE $where LIMIT 1) $alias ON TRUE";

/* Used in: modules/common/sqlsrv_compat.php at line 507 */
'SELECT ' . trim($m[]) . ' ORDER BY ' . trim($m[]) . ' LIMIT ' . trim($m[]), $q );

/* Used in: modules/common/sqlsrv_compat.php at line 512 */
'SELECT ' . trim($m[]) . ' LIMIT ' . trim($m[]), $q );

/* Used in: modules/common/sqlsrv_compat.php at line 525 */
'/\(\s*SELECT\s+(.*?)\s+FOR\s+XML\s+PATH\s*\(\s*\'\'\s*\)\s*\)/is', ($m) { $inner = trim($m[]);

/* Used in: modules/common/sqlsrv_compat.php at line 529 */
'(SELECT string_agg((' . trim($p[]) . ')::text, \'\') FROM ' . trim($p[]) . ')';

/* Used in: modules/common/includes/quick_links.php at line 148 */
$stmt = $dbprepare('SELECT url FROM user_quick_links WHERE username = :u ORDER BY sort_order, id');

/* Used in: modules/common/includes/quick_links.php at line 205 */
'DELETE FROM user_quick_links WHERE username = :u');

/* Used in: modules/common/includes/quick_links.php at line 208 */
'INSERT INTO user_quick_links (username, url, label, sort_order) VALUES (:u, :url, :label, :pos)');

/* Used in: modules/common/includes/sum_ui.php at line 56 */
INSERT INTO #$table} SELECT '$esc'");

/* Used in: modules/common/includes/sum_ui.php at line 87 */
'Select state', 'ANDAMAN AND NICOBAR ISLANDS' 'ANDAMAN AND NICOBAR ISLANDS', 'ANDHRA PRADESH' 'ANDHRA PRADESH', 'ASSAM' 'ASSAM', 'BIHAR' 'BIHAR', 'CHENNAI' 'CHENNAI', 'DELHI' 'DELHI', 'GUJARAT' 'GUJARAT', 'HARYANA' 'HARYANA', 'HIMACHAL PRADESH' 'HIMACHAL PRADESH', 'JAMMU_KASHMIR' 'JAMMU_KASHMIR', 'KARNATAKA' 'KARNATAKA', 'KERALA' 'KERALA', 'KOLKATA' 'KOLKATA', 'MADHYA PRADESH' 'MADHYA PRADESH', 'MAHARASHTRA' 'MAHARASHTRA', 'MUMBAI' 'MUMBAI', 'NORTH_EAST' 'NORTH_EAST', 'ORISSA' 'ORISSA', 'PUNJAB' 'PUNJAB', 'RAJASTHAN' 'RAJASTHAN', 'TAMILNADU' 'TAMILNADU', 'TELANGANA' 'TELANGANA', 'UP_EAST' 'UP_EAS', 'UP_WEST' 'UP_WEST', 'WEST BENGAL' 'WEST BENGAL', ];

/* Used in: modules/common/includes/sum_ui.php at line 119 */
'Select state', $required, 'sum-search-form__field--state' );

/* Used in: modules/common/includes/sum_ui.php at line 131 */
'Select…', bool $required = false, string $fieldClass = '', string $id = ''): string { $idAttr = $id '' ? $id : $name;

/* Used in: modules/common/includes/sum_ui.php at line 140 */
'<select name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($idAttr) . '"' . $req . ' class="form-select sum-select" data-searchable-select="1"' . ' data-placeholder="' . cdat_sum_h($placeholder) . '">';

/* Used in: modules/common/includes/sum_ui.php at line 155 */
'</select></div>';

/* Used in: modules/common/includes/sum_ui.php at line 200 */
' title="Select a date from the calendar"' . $req . ' value="' . cdat_sum_h($value) . '"/>' . '</div>';

/* Used in: modules/common/includes/sum_ui.php at line 348 */
'Select operator', 'AIRCEL_TOWER' 'AIRCEL_TOWER', 'AIRTEL_TOWER' 'AIRTEL_TOWER', 'BPL_TOWER' 'BPL_TOWER', 'CELLONE_TOWER' 'CELLONE_TOWER', 'ETISALAT_TOWER' 'ETISALAT_TOWER', 'IDEA_TOWER' 'IDEA_TOWER', 'JIO_TOWER' 'JIO_TOWER', 'MTS_TOWER' 'MTS_TOWER', 'RELIANCE_TOWER' 'RELIANCE_TOWER', 'TATA_TOWER' 'TATA_TOWER', 'UNINOR_TOWER' 'UNINOR_TOWER', 'VIDEOCON_TOWER' 'VIDEOCON_TOWER', 'VODAFONE_TOWER' 'VODAFONE_TOWER', ];

/* Used in: modules/common/includes/sum_ui.php at line 372 */
'Select operator', false, 'sum-search-form__field--operator' );

/* Used in: modules/common/includes/sum_ui.php at line 382 */
'Select state', 'ANDAMAN AND NICOBAR ISLANDS' 'ANDAMAN AND NICOBAR ISLANDS', 'ANDHRA PRADESH' 'ANDHRA PRADESH', 'ARUNACHAL PRADESH' 'ARUNACHAL PRADESH', 'ASSAM' 'ASSAM', 'BIHAR' 'BIHAR', 'CHENNAI' 'CHENNAI', 'CHHATTISGARH' 'CHHATTISGARH', 'DELHI' 'DELHI', 'GUJARAT' 'GUJARAT', 'HARYANA' 'HARYANA', 'HIMACHAL PRADESH' 'HIMACHAL PRADESH', 'JAMMU_KASHMIR' 'JAMMU_KASHMIR', 'JHARKHAND' 'JHARKHAND', 'KARNATAKA' 'KARNATAKA', 'KERALA' 'KERALA', 'KOLKATA' 'KOLKATA', 'MADHYA PRADESH' 'MADHYA PRADESH', 'MAHARASHTRA' 'MAHARASHTRA', 'MANIPUR' 'MANIPUR', 'MEGHALAYA' 'MEGHALAYA', 'MIZORAM' 'MIZORAM', 'MUMBAI' 'MUMBAI', 'NAGALAND' 'NAGALAND', 'NORTH_EAST' 'NORTH_EAST', 'ORISSA' 'ORISSA', 'PUNJAB' 'PUNJAB', 'RAJASTHAN' 'RAJASTHAN', 'TAMILNADU' 'TAMILNADU', 'TELANGANA' 'TELANGANA', 'TRIPURA' 'TRIPURA', 'UP_EAST' 'UP_EAST', 'UP_WEST' 'UP_WEST', 'WEST BENGAL' 'WEST BENGAL', ];

/* Used in: modules/common/includes/sum_ui.php at line 426 */
'Select state', $required, 'sum-search-form__field--state' );

/* Used in: modules/common/get_year.php at line 6 */
$query ="SELECT DISTINCT YEAR FROM TWRMDB..OFFENCE_DETAILS WHERE CRIME_NO = '".$_POST["CRIME_NO"]."'";

/* Used in: modules/common/get_crno.php at line 6 */
$query ="SELECT DISTINCT CRIME_NO FROM TWRMDB..OFFENCE_DETAILS WHERE POLICE_STATION = '".$_POST["POLICE_STATION"]."'";

/* Used in: modules/common/get_division.php at line 6 */
$query ="SELECT DISTINCT DIVISION FROM MIGRANT_LABOURS_FORM..PS_NAMES WHERE ZONE='".$_POST["POLICE_STATION"]."'";

/* Used in: modules/common/cdr_enrichment_sql.php at line 36 */
$sql = "SELECT DISTINCT ON (celltowerid) celltowerid, COALESCE(operator, '') AS operator, COALESCE(state, '') AS state, COALESCE(siteaddress, areadescription, '') AS areadescription, COALESCE(lat, '') AS lat, COALESCE(long, '') AS long, COALESCE(azimuth, '') AS azimuth FROM cdatcelltowerareanew WHERE celltowerid IN ($placeholders) ORDER BY celltowerid, lastupdate DESC NULLS LAST";

/* Used in: modules/common/cdr_enrichment_sql.php at line 100 */
'SELECT phoneprefix, areadescription FROM cdatphonearea');

/* Used in: modules/common/cdr_enrichment_sql.php at line 153 */
SELECT phone, COALESCE(nickname, '') AS nickname, COALESCE(mo, '') AS mo, COALESCE(inc_officer, '') AS inc_officer, COALESCE(category, '') AS category, COALESCE(role, '') AS role FROM cdatsuspect WHERE phone IN ($placeholders)", $phones );

/* Used in: modules/common/cdr_enrichment_sql.php at line 210 */
SELECT phone, COALESCE(fullname, '') AS fullname, COALESCE(fulladdress, '') AS fulladdress, COALESCE(category_type, '') AS category_type, doa FROM cdataddress WHERE phone IN ($placeholders) AND eff_to_date IS NULL", $phones );

/* Used in: modules/common/cdr_enrichment_sql.php at line 246 */
SELECT phone, COALESCE(fullname, '') AS fullname, COALESCE(fulladdress, '') AS fulladdress, COALESCE(category_type, '') AS category_type, doa FROM address_other_state WHERE phone IN ($placeholders) AND eff_to_date IS NULL", $phones );

/* Used in: modules/common/cdr_enrichment_sql.php at line 407 */
"SELECT 1 FROM ir_particulars WHERE mobile LIKE ? LIMIT 1", ['%' . $phone . '%'] );

/* Used in: modules/common/cdr_enrichment_sql.php at line 428 */
SELECT mobile, image FROM suspect_image_table WHERE mobile IN ($placeholders)", $phones );

/* Used in: modules/common/cdr_enrichment_sql.php at line 450 */
"SELECT image FROM suspect_image_table WHERE irkey = '113769' LIMIT 1");

/* Used in: modules/common/cdr_enrichment_sql.php at line 513 */
SELECT DISTINCT A.PHONE,OTHER,CASE WHEN other in (select phone from cdatsuspect) THEN nickname ELSE ' ' END as NICKNAME, $dateTimeSelect}STARTTIME,DURATION,TYPE,A.IMEINUMBER,A.CELLTOWERID,COALESCE(B.OPERATOR, '') AS OPERATOR$stateSelect}, $areaExpr} AS AREADESCRIPTION$latSelect} INTO $outputTable} FROM #TT A LEFT JOIN CDATCELLTOWERAREANEW B ON $joinOn} left join cdatsuspect c on a.other=c.phone $where} GROUP BY $groupBy}";

/* Used in: modules/common/cdr_enrichment_sql.php at line 524 */
SELECT DISTINCT A.PHONE,A.OTHER,A.STARTTIME,A.DURATION, CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE, A.IMEINUMBER,A.CELLTOWERID, COALESCE(B.SITEADDRESS, B.AREADESCRIPTION, '') AS SITEADDRESS, COALESCE(B.LAT, '') AS LAT, COALESCE(B.LONG, '') AS LONG, COALESCE(B.AZIMUTH, '') AS AZM INTO $outputTable} FROM $sourceTable} A LEFT JOIN CDATCELLTOWERAREANEW B ON A.CELLTOWERID=B.CELLTOWERID";

/* Used in: modules/common/cdr_enrichment_sql.php at line 543 */
SELECT A.PHONE, A.OTHER, CONVERT(VARCHAR, A.STARTTIME, 20) AS STARTTIME, A.DURATION, CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE, A.IMEINUMBER, A.CELLTOWERID, COALESCE(B.SITEADDRESS, B.AREADESCRIPTION, 'NO LOCATION') AS AREADESCRIPTION, COALESCE(B.OPERATOR, '') AS OPERATOR FROM CDATPCSUSPECT A LEFT JOIN CDATCELLTOWERAREANEW B ON A.CELLTOWERID=B.CELLTOWERID WHERE A.PHONE='$phone}' AND TO_CHAR(A.STARTTIME, 'YYYY-MM-DD') BETWEEN '$fromDate}' AND '$toDate}' $stateFilter} ORDER BY A.STARTTIME";

/* Used in: modules/common/get_ps.php at line 6 */
$query ="SELECT DISTINCT POLICE_STATION FROM CIS_DATA_BASE..CIS_COMPLETE_DATA WHERE DISTRICT= '".$_POST["DISTRICT"]."'";

/* Used in: modules/common/activity_logger.php at line 49 */
$stmt = audit_db()prepare(" INSERT INTO user_sessions (session_id, user_id, username, role, ip_address, expires_at, last_active_at) VALUES (:sid, :uid, :uname, :role, :ip, NOW() + INTERVAL '12 hours', NOW()) ");

/* Used in: modules/common/activity_logger.php at line 94 */
" UPDATE user_sessions SET last_active_at = NOW(), expires_at = NOW() WHERE session_id = :id ")execute([':id' $sid]);

/* Used in: modules/common/activity_logger.php at line 130 */
" INSERT INTO user_activity_logs (username, module, action, detail, ip_address) VALUES (:uname, :module, :action, :data, :ip) ")execute([ ':uname' $username, ':module' $module, ':action' $action_type, ':data' $json, ':ip' $ip, ]);

/* Used in: modules/common/activity_logger.php at line 144 */
" UPDATE user_sessions SET last_active_at = NOW() WHERE session_id = :sid ")execute([':sid' $sid]);

/* Used in: modules/cdat/otherscdat.php at line 36 */
$sql1 = "CREATE TEMP TABLE T AS SELECT ? AS PHONE, '' AS FIRST_CALL, '' AS LAST_CALL, '' AS NICKNAME, '' AS LAST_UPDATED";

/* Used in: modules/cdat/otherscdat.php at line 41 */
$sql2 = "CREATE TEMP TABLE S AS SELECT A.PHONE, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL, B.NICKNAME, CONVERT(VARCHAR, MAX(A.ASONDATE), 20) AS LAST_UPDATED FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE = B.PHONE WHERE A.PHONE = ? GROUP BY A.PHONE, B.NICKNAME";

/* Used in: modules/cdat/otherscdat.php at line 47 */
$sql3 = "SELECT DISTINCT A.PHONE, CASE WHEN A.PHONE = B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL, CASE WHEN A.PHONE = B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL, CASE WHEN A.PHONE = B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME, CASE WHEN A.PHONE = B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED, CASE WHEN A.PHONE = C.PHONE THEN COALESCE(C.FULLNAME, '') + ', ' + COALESCE(C.FULLADDRESS, '') + ', ' + COALESCE(C.CATEGORY_TYPE, '') WHEN A.PHONE = D.PHONE THEN COALESCE(D.FULLNAME, '') + ', ' + COALESCE(D.FULLADDRESS, '') + ', ' + COALESCE(D.CATEGORY_TYPE, '') ELSE COALESCE(AREADESCRIPTION, '') END AS ADDRESS FROM #T A LEFT JOIN CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA ON CASE WHEN LEN(A.PHONE) = 10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX + '%' LEFT JOIN #S B ON A.PHONE = B.PHONE";

/* Used in: modules/cdat/otherscdat.php at line 64 */
$sql4 = "CREATE TEMP TABLE TEMP AS SELECT DISTINCT OTHER FROM CDATPCSUSPECT WHERE PHONE = ? AND LEN(OTHER) >= 10 AND ISNUMERIC(OTHER) = 1 AND SUBSTRING(OTHER,1,1) IN ('7','8','9') AND OTHER NOT IN (SELECT DISTINCT OTHER FROM CDAT_IMPORT.CALLCENTER_NOS)";

/* Used in: modules/cdat/otherscdat.php at line 71 */
$sql5 = "CREATE TEMP TABLE TEMP1 AS SELECT DISTINCT PHONE, OTHER, SUM(CASE WHEN INCOMING = '1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING = '0' THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FC, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LC FROM CDATPCSUSPECT WHERE OTHER IN (SELECT DISTINCT OTHER FROM #TEMP) AND PHONE != ? GROUP BY PHONE, OTHER ORDER BY OTHER";

/* Used in: modules/cdat/otherscdat.php at line 83 */
$sql6 = "CREATE TEMP TABLE TEMP2 AS SELECT OTHER AS PHONE, A.PHONE AS OTHER, C.NICKNAME, CATEGORY, [IN], [OUT], CALLS, DUR, FC AS FIRST_CALL, LC AS LAST_CALL, INC_OFFICER FROM #TEMP1 A LEFT JOIN CDATSUSPECT C ON A.PHONE = C.PHONE";

/* Used in: modules/cdat/otherscdat.php at line 88 */
$sql7 = "SELECT DISTINCT A.PHONE, OTHER, NICKNAME, CATEGORY, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, INC_OFFICER FROM #TEMP2 A ORDER BY PHONE, CALLS DESC";

/* Used in: modules/cdat/otherscdat.php at line 92 */
$sql8 = "SELECT 'OTHERS CDAT CONTACTS OF MOBILE NO: ' + ? as PHONE";

/* Used in: modules/cdat/otherscdat.php at line 97 */
$sql9 = "SELECT CASE WHEN COUNT(PHONE) >= 1 THEN '' ELSE '*** NO CDAT CONTACTS TO OTHERS OF $number ***' END as CNTS FROM #TEMP2";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 48 */
$sql1="CREATE TEMP TABLE T AS SELECT DISTINCT PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,'' AS CATEGORY,''LAST_UPDATED,''INC_OFFICER FROM #T1";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 50 */
$sql10="CREATE TEMP TABLE S AS SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME || '_' || B.ROLE NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,INC_OFFICER FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN ('$number2') GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 53 */
$sqlA="CREATE TEMP TABLE CDATADDRESS AS SELECT distinct * from cdatdupl..cdataddress where phone in ('$number2')";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 55 */
$sqlB="CREATE TEMP TABLE ADDRESS_OTHER_STATE AS SELECT distinct * from cdatdupl..ADDRESS_OTHER_STATE where phone in ('$number2')";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 60 */
$sql3="SELECT DISTINCT PHONE, IMEINUMBER, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL, CONVERT(VARCHAR,MAX(ASONDATE),20) AS LAST_UPDATED FROM CDATPCSUSPECT WHERE PHONE IN ('$number2') GROUP BY PHONE,IMEINUMBER ORDER BY LAST_UPDATED";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 63 */
$sql4="CREATE TEMP TABLE XX AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE IN ('$number2') and other!=''";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 65 */
$sql5 = "CREATE TEMP TABLE TT AS SELECT distinct a.PHONE,OTHER, NICKNAME || '_' || ROLE NICKNAME, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', count(*) as CALLS,sum(cast(duration as numeric)) as dur,CONVERT(VARCHAR,MIN(STARTTIME),20) as FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) as LAST_CALL from #XX a left join cdatsuspect b on a.other=b.phone WHERE OTHER IN (SELECT PHONE FROM CDATSUSPECT) group by a.phone, A.other, nickname,ROLE order by calls desc, other";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 72 */
$sql6 = "CREATE TEMP TABLE WITHADDRESS AS SELECT A.PHONE,A.OTHER,A.NICKNAME,MO,CATEGORY,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL, CASE WHEN FULLNAME IS NULL THEN '' ELSE FULLNAME END+' '+ CASE WHEN b.FULLADDRESS IS NULL THEN CASE WHEN (CALLS=DUR AND LEN(OTHER)<>10) OR (LEFT(OTHER,1)NOT IN ('9','8') AND LEN(OTHER)>14) OR LEN(OTHER)<10 OR SUBSTRING(OTHER,5,10) LIKE '%0000%' or isnumeric(other)=0 --or (len(other)>11 and '00'+other not in (select phoneprefix+'%' from cdatphonearea)) THEN 'JUNK-COULD BE bulk SMS or VOIP calls' else case when min(areadescription) is null then 'code n/a' else min(areadescription) end END ELSE b.FULLADDRESS+','+COALESCE(CATEGORY_type,'') END AS ADDRESS,INC_OFFICER FROM #TT A LEFT JOIN CDATADDRESS B ON OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL LEFT JOIN CDATSUSPECT C ON A.OTHER=C.PHONE left join cdatphonearea d on case when len(other)=10 then other else case when len(other)>10 then '00'+other else null end end like phoneprefix+'%' group by a.PHONE, other,[IN],[OUT],calls,dur, FIRST_CALL, LAST_CALL,FULLNAME,b.FULLADDRESS, A.nickname,CATEGORY_type,MO,CATEGORY, INC_OFFICER";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 90 */
$sql7 = "CREATE TEMP TABLE WITHADDRESS1 AS SELECT A.PHONE,OTHER,NICKNAME,MO,CATEGORY AS CAT,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL, CASE WHEN A.OTHER=B.PHONE THEN COALESCE(B.FULLNAME,'')+','+COALESCE(B.FULLADDRESS,'')+','+ COALESCE(CATEGORY_TYPE,'')+','+CONVERT(CHAR(10),CAST(DOA AS DATETIME),105) ELSE A.ADDRESS END AS ADDRESS, INC_OFFICER FROM #WITHADDRESS A LEFT JOIN ADDRESS_OTHER_STATE B ON A.OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 96 */
$sql71="Select A.*,CASE WHEN B.MOBILE=A.OTHER THEN B.IMAGE ELSE (SELECT IMAGE FROM SUSPECT_IMAGE_TABLE WHERE IRKEY='113769') END AS IMAGE FROM #WITHADDRESS1 A LEFT JOIN SUSPECT_IMAGE_TABLE B ON B.MOBILE=A.OTHER ORDER BY PHONE,CALLS DESC,OTHER";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 99 */
$sql8 ="SELECT 'CDAT CONTACTS OF MOBILE NO: ' || '$number' as PHONE";

/* Used in: modules/cdat/bulk_cdat_contacts.php at line 101 */
$sql9="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** NO CDAT CONTACTS TO $number ***' end as CNTS FROM #WITHADDRESS";

/* Used in: modules/cdat/cdatcnts.php at line 44 */
$sql10 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME || '_' || B.ROLE NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED, INC_OFFICER FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE=? GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";

/* Used in: modules/cdat/cdatcnts.php at line 51 */
$sql4 = "CREATE TEMP TABLE XX AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE=? and other!=''";

/* Used in: modules/cdat/cdatcnts.php at line 56 */
$sql5 = "CREATE TEMP TABLE TT AS SELECT distinct a.PHONE,OTHER, NICKNAME || '_' || ROLE NICKNAME, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', count(*) as CALLS,sum(cast(duration as numeric)) as dur,CONVERT(VARCHAR,MIN(STARTTIME),20) as FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) as LAST_CALL, MO, CATEGORY, INC_OFFICER from #XX a left join cdatsuspect b on a.other=b.phone WHERE OTHER IN (SELECT PHONE FROM CDATSUSPECT) group by a.phone, A.other, nickname,ROLE, MO, CATEGORY, INC_OFFICER order by calls desc, other";

/* Used in: modules/cdat/cdatcnts.php at line 69 */
$sql8 = "SELECT 'CDAT CONTACTS OF MOBILE NO: ' + ? as PHONE";

/* Used in: modules/cdat/cdatcnts.php at line 111 */
'SELECT * FROM #TT ORDER BY CALLS DESC, OTHER');

/* Used in: modules/address/address.php at line 34 */
$sql8="SELECT 'ADDRESS OF MOBILE NO: ' || '$number' as PHONE1";

/* Used in: modules/address/address.php at line 36 */
$sql9="CREATE TEMP TABLE T AS SELECT '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,''LAST_UPDATED,''INC_OFFICER";

/* Used in: modules/address/address.php at line 38 */
$sql10="CREATE TEMP TABLE S AS SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME || '_' || B.ROLE NICKNAME,MO,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED, INC_OFFICER FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,B.ROLE, INC_OFFICER";

/* Used in: modules/address/address.php at line 42 */
$sql11="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME, CASE WHEN A.PHONE=B.PHONE THEN B.MO ELSE A.MO END AS MO, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED, CASE WHEN A.PHONE=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+', DOA: '+COALESCE(CONVERT(VARCHAR,C.DOA,20),'')+', '+COALESCE(C.CATEGORY_TYPE,'')+', '+ (CASE WHEN C.OPERATOR IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE C.OPERATOR END) WHEN A.PHONE=D.PHONE THEN COALESCE(D.FULLNAME,'')+', '+COALESCE(D.FULLADDRESS,'')+',DOA: '+COALESCE(CONVERT(VARCHAR,D.DOA,20),'')+', ' || ', '+COALESCE(D.CATEGORY_TYPE,'')+', '+ (CASE WHEN D.OPERATOR IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE COALESCE(AREADESCRIPTION,'') END AS ADDRESS, CASE WHEN A.PHONE=B.PHONE THEN B.INC_OFFICER ELSE A.INC_OFFICER END AS INC_OFFICER FROM #T A LEFT JOIN CDATADDRESS C  ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D  ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' LEFT JOIN #S B ON A.PHONE=B.PHONE";

/* Used in: modules/address/bulkaddress.php at line 28 */
$sql3= "CREATE TEMP TABLE T2 AS SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL,MAX(STARTTIME) AS LAST_CALL, MAX(A.ASONDATE) AS LAST_UPDATED,NICKNAME FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN ('$number2') GROUP BY A.PHONE,NICKNAME";

/* Used in: modules/address/bulkaddress.php at line 33 */
$sql4 = "CREATE TEMP TABLE T3 AS SELECT DISTINCT A.PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME FROM #T1 A LEFT JOIN #T2 B ON A.PHONE=B.PHONE";

/* Used in: modules/address/bulkaddress.php at line 36 */
$sql5= "CREATE TEMP TABLE T4 AS SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE,DOA, EFF_FROM_DATE FROM CDATADDRESS WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

/* Used in: modules/address/bulkaddress.php at line 39 */
$sql6 = "INSERT INTO #T4 SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM ADDRESS_OTHER_STATE WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

/* Used in: modules/address/bulkaddress.php at line 43 */
$sql7 = "CREATE TEMP TABLE T5 AS SELECT DISTINCT A.PHONE,COALESCE(CONVERT(VARCHAR,FIRST_CALL,20),'NIL') AS FIRST_CALL, COALESCE(CONVERT(VARCHAR,A.LAST_CALL,20),'NIL') AS LAST_CALL, COALESCE(CONVERT(VARCHAR,A.LAST_UPDATED,20),'NIL') AS LAST_UPDATED,COALESCE(NICKNAME,'NIL') AS NICKNAME, CASE WHEN A.PHONE IN (SELECT PHONE FROM #T4) THEN FULLNAME+', '+B.FULLADDRESS+', DOA: '+CONVERT(VARCHAR,DOA,106)+', LAST UPDATE: '+CONVERT(VARCHAR,EFF_FROM_DATE,106) ELSE AREADESCRIPTION END AS ADDRESS FROM #T3 A LEFT JOIN #T4 B ON A.PHONE=B.PHONE LEFT JOIN CDATPHONEAREA E ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'CODE NOT AVAILABLE' END END LIKE PHONEPREFIX+'%' ORDER BY A.PHONE";

/* Used in: modules/address/bulkaddress.php at line 52 */
$sql8 = "SELECT PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME, CASE WHEN ADDRESS IS NULL AND LEN(PHONE)<>10 THEN 'JUNK OR VOIP CALL' WHEN ADDRESS IS NULL AND SUBSTRING(PHONE,1,1) IN ('7','8','9') AND LEN(ADDRESS)>=10 THEN 'CODE NOT AVAILABLE' ELSE ADDRESS END AS ADDRESS FROM #T5";

/* Used in: modules/jrms/jrms_search_for_uniquekey.php at line 23 */
$sql1 ="SET DATEFORMAT DMY CREATE TEMP TABLE TEMP AS SELECT DISTINCT PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE, CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF, ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME, CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO FROM JRMS..JRMS_TOTAL_2012_TO_2017 WHERE UNIQUE_KEY='$UNIQUE_KEY' ";

/* Used in: modules/jrms/jrms_search_for_uniquekey.php at line 30 */
$sql2 ="SELECT PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,ADDR_DURING_RELEASE, JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM, CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF)) ELSE '' END IRKEY FROM #TEMP ORDER BY JAILNAME, RELEASE_DATE DESC";

/* Used in: modules/jrms/jrms_search_for_uniquekey.php at line 36 */
$sql6="SELECT 'ACCUSED RELEASED FROM JAIL' PHONE ";

/* Used in: modules/jrms/jrms_search_by_dates.php at line 15 */
$query = "SELECT distinct HEADOFCRIME FROM JRMS..JRMS_TOTAL_2012_TO_2017 WHERE HEADOFCRIME!='' ORDER BY HEADOFCRIME";

/* Used in: modules/jrms/jrms_search_by_dates.php at line 18 */
'Select CrimeHead'];

/* Used in: modules/jrms/jrms_search_by_dates.php at line 28 */
'Select CrimeHead', false);

/* Used in: modules/jrms/jrms_search_by_dates.php at line 54 */
$sql1 = "SET DATEFORMAT DMY CREATE TEMP TABLE TEMP AS SELECT DISTINCT PRISONERNO,UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE, CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF, ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME, CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO FROM JRMS..JRMS_TOTAL_2012_TO_2017 WHERE (CONVERT(DATE,RELEASEDT) BETWEEN '$f_date' AND '$t_date') AND HEADOFCRIME LIKE '%' || '$CRIMEHEAD' || '%' AND HEADOFCRIME!='' ";

/* Used in: modules/jrms/jrms_search_by_dates.php at line 61 */
$sql11 = "CREATE TEMP TABLE COUNT AS SELECT distinct UNIQUE_KEY,COUNT(UNIQUE_KEY) NO_OF_TIMES_RELEASED from JRMS..JRMS_TOTAL_2012_TO_2017 GROUP BY UNIQUE_KEY";

/* Used in: modules/jrms/jrms_search_by_dates.php at line 64 */
$sql2 = "SELECT PRISONERNO,A.UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,NO_OF_TIMES_RELEASED NO_OF_TIMES_RELEASED,PHONE,IDPROOF,ADDR_DURING_RELEASE, JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM, CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF)) ELSE '' END IRKEY FROM #TEMP A LEFT JOIN #COUNT B ON A.UNIQUE_KEY=B.UNIQUE_KEY ORDER BY JAILNAME, RELEASE_DATE DESC";

/* Used in: modules/jrms/jrms_search_by_dates.php at line 72 */
$sql6 = "SELECT 'ACCUSED RELEASED FROM: ' || '$f_date' || ' TO: ' || '$t_date' || ' UNDER CRIME HEAD ' || '$CRIMEHEAD' AS PHONE";

/* Used in: modules/jrms/jrms_search_by_dates.php at line 140 */
'Select CrimeHead', false), 'BTN_SUM', 'Submit' );

/* Used in: modules/jrms/jrms_name_search_php.php at line 14 */
$query = "SELECT distinct HEADOFCRIME FROM JRMS.JRMS_TOTAL_2012_TO_2017 WHERE HEADOFCRIME!='' ORDER BY HEADOFCRIME";

/* Used in: modules/jrms/jrms_name_search_php.php at line 17 */
'Select CrimeHead'];

/* Used in: modules/jrms/jrms_name_search_php.php at line 26 */
'Select CrimeHead', false);

/* Used in: modules/jrms/jrms_name_search_php.php at line 51 */
$sql1 = "SET DATEFORMAT DMY CREATE TEMP TABLE TEMP AS SELECT DISTINCT PRISONERNO,UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE, CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF, ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME, CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO FROM JRMS..JRMS_TOTAL_2012_TO_2017 WHERE NAME LIKE '%' || '$NAME' || '%' AND HEADOFCRIME LIKE '%' || '$CRIMEHEAD' || '%' AND HEADOFCRIME!='' ";

/* Used in: modules/jrms/jrms_name_search_php.php at line 58 */
$sql11 = "CREATE TEMP TABLE COUNT AS SELECT distinct UNIQUE_KEY,COUNT(UNIQUE_KEY) NO_OF_TIMES_RELEASED from JRMS..JRMS_TOTAL_2012_TO_2017 GROUP BY UNIQUE_KEY";

/* Used in: modules/jrms/jrms_name_search_php.php at line 61 */
$sql2 = "SELECT PRISONERNO,A.UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME, NO_OF_TIMES_RELEASED,PHONE,IDPROOF,ADDR_DURING_RELEASE,JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM, CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF)) ELSE '' END IRKEY FROM #TEMP A LEFT JOIN #COUNT B ON A.UNIQUE_KEY=B.UNIQUE_KEY ORDER BY JAILNAME, RELEASE_DATE DESC";

/* Used in: modules/jrms/jrms_name_search_php.php at line 67 */
$sql6 = "SELECT 'ACCUSED RELEASED FROM JAIL UNDER CRIME HEAD ' || '$CRIMEHEAD' || ' BY NAME ' || '$NAME' AS PHONE";

/* Used in: modules/jrms/jrms_name_search_php.php at line 134 */
'Select CrimeHead', false), 'BTN_SUM', 'Submit' );

/* Used in: modules/jrms/jrms_ps_wise_search.php at line 15 */
$query = "select distinct PSARRESTED from jrms..JRMS_TOTAL_2012_TO_2017 where jailname in ('CHERLAPALLI','CHANCHALGUDA','CHANCHALGUDA WOMEN') AND jailname in ('CHERLAPALLI','CHANCHALGUDA','CHANCHALGUDA WOMEN') and psarrested in ('Abidroad','Bahadurpura','Afzalgunj','Amberpet','Asifnagar','Banjara Hills','Begumbazar','Begumpet','Bhavaninagar', 'Bollarum','Bowenpally','CCS','CCS HYD','Chaderghat','Chandrayanagutta','Charminar','Chatrinaka', 'Chikkadpally','Chilkalguda','CYBER CRIME CCS','CYBER CRIME PS','Dabeerpura','Falaknuma','Gandhinagar', 'Golconda','Gopalapuram','Habeebnagar','Humayunnagar','Hussainialam','Jubilee Hills','KACHEGUDA','Kachiguda','Kalapathar', 'KAMATIPURA','Kanchanbagh','Karkhana','Lalaguda','Langer House','Madannapet','Mahankali','Malakpet', 'Mangalhat','Market','Marredpally','Mirchowk','Moghalpura','Musheerabad','Nallakunta', 'Nampally','Narayanaguda','Osmania University','Panjagutta','RAINBAZAR','Ramgopalpet', 'Reinbazar','Saidabad','Saifabad','Sanjeevareddynagar','Shahalibanda','Shahinayathgunj','SR NAGAR', 'Sultanbazar','Tappachabutra','THIRUMALAGIRI','THUKARAMGATE','Trimulgherry','Tukaramgate', 'WPS SouthZone','SANTOSHNAGAR','Is Sadan','Bandlaguda','Domalguda','Secretariat','Khairatabad','Warasiguda','Gudimalkapur','Masab tank','Film nagar','Madhuranagar','Borabanda')";

/* Used in: modules/jrms/jrms_ps_wise_search.php at line 28 */
'Select POLICE STATION'];

/* Used in: modules/jrms/jrms_ps_wise_search.php at line 38 */
'Select POLICE STATION', true);

/* Used in: modules/jrms/jrms_ps_wise_search.php at line 64 */
$sql1 = "SET DATEFORMAT DMY CREATE TEMP TABLE TEMP AS SELECT DISTINCT PRISONERNO,UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE, CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF, ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME, CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO FROM JRMS..JRMS_TOTAL_2012_TO_2017 WHERE (CONVERT(DATE,RELEASEDT) BETWEEN '$f_date' AND '$t_date') AND PSARRESTED LIKE '%' || '$CRIMEHEAD' || '%' AND PSARRESTED!='' ";

/* Used in: modules/jrms/jrms_ps_wise_search.php at line 71 */
$sql11 = "CREATE TEMP TABLE COUNT AS SELECT distinct UNIQUE_KEY,COUNT(UNIQUE_KEY) NO_OF_TIMES_RELEASED from JRMS..JRMS_TOTAL_2012_TO_2017 GROUP BY UNIQUE_KEY";

/* Used in: modules/jrms/jrms_ps_wise_search.php at line 74 */
$sql2 = "SELECT PRISONERNO,A.UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,NO_OF_TIMES_RELEASED NO_OF_TIMES_RELEASED,PHONE,IDPROOF,ADDR_DURING_RELEASE, JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM, CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF)) ELSE '' END IRKEY FROM #TEMP A LEFT JOIN #COUNT B ON A.UNIQUE_KEY=B.UNIQUE_KEY ORDER BY JAILNAME, RELEASE_DATE DESC";

/* Used in: modules/jrms/jrms_ps_wise_search.php at line 82 */
$sql6 = "SELECT 'ACCUSED RELEASED FROM: ' || '$f_date' || ' TO: ' || '$t_date' || ' OF POLICE STATION ' || '$CRIMEHEAD' AS PHONE";

/* Used in: modules/jrms/jrms_ps_wise_search.php at line 150 */
'Select POLICE STATION', true), 'BTN_SUM', 'Submit' );

/* Used in: modules/administration/admin_create_user.php at line 24 */
'SELECT COUNT(*) FROM logins WHERE LOWER(username) = LOWER(:u)');

/* Used in: modules/administration/admin_create_user.php at line 29 */
'INSERT INTO logins (username, password, role, fullname) VALUES (:u, :p, :r, :f)');

/* Used in: modules/administration/admin_sql_console.php at line 22 */
$clean_query)) { Exception("Only SELECT queries are allowed.");

/* Used in: modules/administration/admin_sql_console.php at line 33 */
$clean_query)) { Exception("Only SELECT queries are allowed. DML/DDL commands are blocked.");

/* Used in: modules/administration/admin_sql_console.php at line 106 */
$query)) { Exception("Only SELECT queries are allowed.");

/* Used in: modules/administration/admin_sql_console.php at line 117 */
$query)) { Exception("Only SELECT queries are allowed. DML/DDL commands are blocked.");

/* Used in: modules/administration/admin_sql_console.php at line 149 */
$logStmt = $dbprepare(" INSERT INTO admin_query_logs (user_id, username, query_text, execution_time, ip_address) VALUES (:uid, :uname, :q, :time, :ip) ");

/* Used in: modules/administration/admin_sql_console.php at line 169 */
"SELECT * FROM admin_query_logs ORDER BY created_at DESC LIMIT 10")fetchAll(PDOFETCH_ASSOC);

/* Used in: modules/administration/admin_activity_log.php at line 14 */
"SELECT DISTINCT username FROM user_sessions ORDER BY username")fetchAll(PDOFETCH_ASSOC);

/* Used in: modules/administration/admin_activity_log.php at line 37 */
SELECT * FROM user_sessions $sessWhere ORDER BY login_time DESC");

/* Used in: modules/administration/admin_activity_log.php at line 46 */
SELECT * FROM user_activity_logs $logWhere ORDER BY created_at DESC");

/* Used in: modules/administration/admin_activity_log.php at line 72 */
'Select user'];

/* Used in: modules/administration/admin_activity_log.php at line 79 */
'Select User', $userOptions, $filter_user, 'Select user', true );

/* Used in: modules/imei/imeisinphone.php at line 37 */
$sql1 = "CREATE TEMP TABLE T AS SELECT * FROM CDATPCSUSPECT WHERE PHONE = ?";

/* Used in: modules/imei/imeisinphone.php at line 43 */
$sql2 = "CREATE TEMP TABLE TT AS SELECT DISTINCT PHONE, IMEINUMBER, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL FROM #T GROUP BY PHONE, IMEINUMBER ORDER BY LAST_CALL";

/* Used in: modules/imei/imeisinphone.php at line 54 */
$sql3 = "SELECT A.PHONE, IMEINUMBER, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, CASE WHEN C.PHONE IS NOT NULL THEN COALESCE(C.FULLNAME + ', ' + C.FULLADDRESS, '') + ' ' + COALESCE(C.CATEGORY_TYPE, '') WHEN D.PHONE IS NOT NULL THEN COALESCE(D.FULLNAME + ', ' + D.FULLADDRESS, '') + ' ' + COALESCE(D.CATEGORY_TYPE, '') ELSE AREADESCRIPTION END AS ADDRESS FROM #TT A LEFT JOIN CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA E ON A.PHONE LIKE PHONEPREFIX + '%' ORDER BY LAST_CALL";

/* Used in: modules/imei/imeisinphone.php at line 67 */
$sql4 = "SELECT 'LIST OF IMEIS USED IN PHONE NO: ' + ? as PHONE1";

/* Used in: modules/imei/imeisearch.php at line 44 */
$sql1 = "CREATE TEMP TABLE T AS SELECT * FROM CDATPCSUSPECT WHERE IMEINUMBER = ?";

/* Used in: modules/imei/imeisearch.php at line 50 */
$sql2 = "CREATE TEMP TABLE TT AS SELECT DISTINCT PHONE, IMEINUMBER, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL FROM #T GROUP BY PHONE, IMEINUMBER ORDER BY LAST_CALL";

/* Used in: modules/imei/imeisearch.php at line 61 */
$sql3 = "SELECT A.PHONE, IMEINUMBER, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, CASE WHEN C.PHONE IS NOT NULL THEN COALESCE(C.FULLNAME + ', ' + C.FULLADDRESS, '') + ' ' + COALESCE(C.CATEGORY_TYPE, '') WHEN D.PHONE IS NOT NULL THEN COALESCE(D.FULLNAME + ', ' + D.FULLADDRESS, '') + ' ' + COALESCE(D.CATEGORY_TYPE, '') ELSE AREADESCRIPTION END AS ADDRESS FROM #TT A LEFT JOIN CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA E ON A.PHONE LIKE PHONEPREFIX + '%' ORDER BY LAST_CALL";

/* Used in: modules/imei/imeisearch.php at line 74 */
$sql4 = "SELECT 'LIST OF PHONE NOs USED IN IMEI: ' + ? as PHONE1";

/* Used in: modules/imei/imeisearch.php at line 79 */
$sql5 = "SELECT CASE WHEN COUNT(PHONE) >= 1 THEN '' ELSE '*** NO PHONES ARE AVAILABLE IN IMEI $number ***' END as PHONE FROM #tt";

/* Used in: modules/call-details/movements_between_two_numbers.php at line 34 */
$sql10 = "CREATE TEMP TABLE S AS SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED, INC_OFFICER FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";

/* Used in: modules/call-details/movements_between_two_numbers.php at line 38 */
$sql1 = "CREATE TEMP TABLE TT AS SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION, CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE, IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY FROM CDATPCSUSPECT WHERE PHONE='$number' AND OTHER='$number1'";

/* Used in: modules/call-details/movements_between_two_numbers.php at line 44 */
$sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION,LAT,LONG,AZM from #temp_cdrs ORDER BY STARTTIME";

/* Used in: modules/call-details/movements_between_two_numbers.php at line 46 */
$sql6 = "select 'CALL DETAILS OF MOBILE NO. ' || '$number' || 'AND OTHER NO. ' || '$number1' as PHONE";

/* Used in: modules/call-details/movements_between_two_numbers_comparision.php at line 34 */
$sql10 = "CREATE TEMP TABLE S AS SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED, INC_OFFICER FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN (?,?) GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";

/* Used in: modules/call-details/movements_between_two_numbers_comparision.php at line 41 */
$sql1 = "CREATE TEMP TABLE TT AS SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION, CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE, IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY FROM CDATPCSUSPECT WHERE PHONE IN (?,?)";

/* Used in: modules/call-details/movements_between_two_numbers_comparision.php at line 55 */
$sql5 = "CREATE TEMP TABLE ttpppp AS SELECT distinct A.PHONE,A.STARTTIME STARTTIME,A.DURATION ,''''+A.CELLTOWERID PHONE_CELLTOWERID, A.AREADESCRIPTION PHONE_AREADESCRIPTION,A.LAT PHONE_LAT,A.LONG PHONE_LONG,A.AZM PHONE_AZM, A.OTHER,''''+B.CELLTOWERID OTHER_CELLTOWERID, B.AREADESCRIPTION OTHER_AREADESCRIPTION,B.LAT OTHER_LAT,B.LONG OTHER_LONG,B.AZM OTHER_AZM from #ttppp A INNER JOIN #TTPPP B ON A.OTHER=B.PHONE AND A.PHONE =B.OTHER AND CONVERT(DATE,A.STARTTIME)=CONVERT(DATE,B.STARTTIME) and datepart(hh,convert(datetime,A.STARTTIME))=datepart(hh,convert(datetime,b.STARTTIME)) and datepart(mm,convert(datetime,A.STARTTIME))=datepart(mm,convert(datetime,b.STARTTIME)) AND datediff(ss,convert(datetime,A.STARTTIME),convert(datetime,b.STARTTIME))<'4' WHERE A.PHONE=?";

/* Used in: modules/call-details/movements_between_two_numbers_comparision.php at line 69 */
$sql7 = "select distinct *,case when phone_lat like '%.%' and other_lat like '%.%' and phone_long like '%.%' and other_long like '%.%' then CAST(import.CALCULATEDISTANCE(left(phone_long,8),left(phone_lat,8),left(other_LONG,8),left(other_LAT,8)) AS INT) else '' end DIST FROM #ttpppp ORDER BY STARTTIME";

/* Used in: modules/call-details/movements_between_two_numbers_comparision.php at line 76 */
$sql6 = "select 'MOVEMENTS COMPARISION OF MOBILE NO. ' + ? + ' AND OTHER NO. ' + ? as PHONE";

/* Used in: modules/call-details/movements.php at line 42 */
$sql = " SELECT DISTINCT A.PHONE, A.OTHER, COALESCE(C.NICKNAME,'') AS NICKNAME, TO_CHAR(A.STARTTIME, 'YYYY-MM-DD') AS DATE1, TO_CHAR(A.STARTTIME, 'HH24:MI:SS') AS TIME1, TO_CHAR(A.STARTTIME, 'YYYY-MM-DD HH24:MI:SS') AS STARTTIME, A.DURATION, CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE, A.IMEINUMBER, A.CELLTOWERID FROM CDATPCSUSPECT A  LEFT JOIN CDATSUSPECT C  ON A.OTHER = C.PHONE WHERE A.PHONE = ? ORDER BY STARTTIME ASC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

/* Used in: modules/call-details/movements.php at line 62 */
$count_stmt = sqlsrv_query($conn, 'SELECT COUNT(*) AS TOTAL FROM CDATPCSUSPECT  WHERE PHONE = ?', [$number]);

/* Used in: modules/call-details/movements_in_particular_place.php at line 28 */
'<select name="RANGE" id="RANGE" class="form-select" required>' . '<option value="">-- Select --</option>' . $rangeOptions . '</select>' . '</div>';

/* Used in: modules/call-details/movements_in_particular_place.php at line 66 */
$sql2 = "CREATE TEMP TABLE TT AS SELECT DISTINCT PHONE, OTHER, CONVERT(VARCHAR, STARTTIME, 20) AS STARTTIME, DURATION, CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE, IMEINUMBER, CELLTOWERID, STATE_KEY, PROVIDER_KEY FROM CDATPCSUSPECT WHERE PHONE = ?";

/* Used in: modules/call-details/movements_in_particular_place.php at line 75 */
$sql3 = "CREATE TEMP TABLE TTP AS SELECT DISTINCT A.PHONE, OTHER, CASE WHEN OTHER IN (SELECT PHONE FROM CDATSUSPECT) THEN NICKNAME ELSE '' END AS NICKNAME, STARTTIME, DURATION, TYPE, A.IMEINUMBER, A.CELLTOWERID, OPERATOR, (CASE WHEN A.CELLTOWERID = B.CELLTOWERID THEN MAX(SITEADDRESS) ELSE '' END + ', LAST_UPDATE:' + CONVERT(VARCHAR, LASTUPDATE, 20)) AS AREADESCRIPTION, LAT, LONG, AZIMUTH AS AZM FROM #TT A INNER JOIN CDATCELLTOWERAREANEW B ON A.CELLTOWERID = B.CELLTOWERID AND A.STATE_KEY = B.STATE_KEY AND A.PROVIDER_KEY = B.PROVIDER_KEY LEFT JOIN CDATSUSPECT C ON A.OTHER = C.PHONE WHERE B.LASTUPDATE = ( SELECT DISTINCT MAX(LASTUPDATE) FROM CDATCELLTOWERAREANEW X WHERE X.CELLTOWERID = B.CELLTOWERID AND X.PROVIDER_KEY = B.PROVIDER_KEY AND X.STATE_KEY = B.STATE_KEY ) GROUP BY A.PHONE, OTHER, NICKNAME, STARTTIME, DURATION, TYPE, A.IMEINUMBER, A.CELLTOWERID, B.CELLTOWERID, LASTUPDATE, OPERATOR, A.STATE_KEY, B.STATE_KEY, A.PROVIDER_KEY, B.PROVIDER_KEY, LAT, LONG, AZIMUTH";

/* Used in: modules/call-details/movements_in_particular_place.php at line 104 */
$sql4 = "DECLARE @lat DECIMAL(14,10) = ?, @long DECIMAL(14,10) = ?, @radius DECIMAL(15,10) = ? SELECT PHONE, OTHER, NICKNAME, STARTTIME, DURATION, TYPE, CELLTOWERID, CAST(CALCULATEDISTANCE(@long, @lat, LONG, LAT) * 1000 AS INT) AS DIST, GETBEARING(LAT, LONG, @lat, @long) AS BR, AREADESCRIPTION, OPERATOR, LAT, LONG, AZM FROM #TTP WHERE LAT BETWEEN @lat - 1 AND @lat + 1 AND LONG BETWEEN @long - 1 AND @long + 1 AND ISNUMERIC(LAT) = 1 AND LAT IS NOT NULL AND ISNUMERIC(LONG) = 1 AND LONG IS NOT NULL AND CALCULATEDISTANCE(@long, @lat, LONG, LAT) * 1000 < @radius ORDER BY STARTTIME";

/* Used in: modules/call-details/calls_btwn_dates.php at line 63 */
$sql1 = "CREATE TEMP TABLE TT AS SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION, CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE, IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY FROM CDATPCSUSPECT WHERE PHONE = ? AND TO_CHAR(STARTTIME, 'YYYY-MM-DD') BETWEEN ? AND ?";

/* Used in: modules/call-details/calls_btwn_dates.php at line 78 */
$sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION FROM #temp_cdrs ORDER BY STARTTIME";

/* Used in: modules/call-details/calls_btwn_dates.php at line 83 */
$sql6 = "SELECT 'CALL DETAILS OF MOBILE NO: ' + ? + ' FROM: ' + ? + ' TO: ' + ? AS PHONE";

/* Used in: modules/summary/sum_new_nos.php at line 24 */
$sql3 = "CREATE TEMP TABLE TT AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE='$number' AND STARTTIME>'$date' AND OTHER NOT IN (SELECT DISTINCT OTHER FROM CDATPCSUSPECT WHERE PHONE='$number' AND STARTTIME < '$date')";

/* Used in: modules/summary/sum_new_nos.php at line 28 */
$sql4 = "CREATE TEMP TABLE RESULT AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL FROM #TT GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_new_nos.php at line 36 */
$sql5 = "CREATE TEMP TABLE RESULT1 AS SELECT * FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN ( SELECT DISTINCT OTHER FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR) AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

/* Used in: modules/summary/sum_new_nos.php at line 40 */
$sql6 = "SELECT DISTINCT A.PHONE, CASE WHEN OTHER IN (SELECT PHONE FROM CDATSUSPECT) THEN OTHER+' - '+NICKNAME ELSE OTHER END AS OTHER,[IN],[OUT],CALLS,DUR, FIRSTCALL,LASTCALL, CASE WHEN OTHER=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+' '+CONVERT(VARCHAR,C.DOA,20)+' '+COALESCE(C.CATEGORY_TYPE,'') WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER' WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER' WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER' WHEN LEN(OTHER)<10 AND [OUT]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' WHEN LEN(OTHER)<10 AND [IN]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR CUSTOMER CARE / ENQUIRY NUMBER' WHEN OTHER IN(SELECT DISTINCT PHONE FROM ADDRESS_OTHER_STATE) THEN COALESCE(D.FULLNAME+', '+D.FULLADDRESS,'')+' '+COALESCE(D.CATEGORY_TYPE,'') ELSE AREADESCRIPTION END AS ADDRESS FROM #RESULT1 A LEFT JOIN CDATSUSPECT B ON OTHER=B.PHONE LEFT JOIN CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA E ON CASE WHEN LEN(OTHER)=10 THEN OTHER ELSE CASE WHEN LEN(OTHER)>10 THEN '00'+OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_new_nos.php at line 60 */
$sql9 = "CREATE TEMP TABLE T AS SELECT '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED";

/* Used in: modules/summary/sum_new_nos.php at line 62 */
$sql10 = "CREATE TEMP TABLE S AS SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

/* Used in: modules/summary/sum_new_nos.php at line 65 */
$sql11 = "SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED, CASE WHEN A.PHONE=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+', '+COALESCE(CONVERT(VARCHAR,C.DOA,20),'')+', '+ (CASE WHEN C.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END) WHEN A.PHONE=D.PHONE THEN COALESCE(D.FULLNAME,'')+', '+COALESCE(D.FULLADDRESS,'')+', '+COALESCE(CONVERT(VARCHAR,D.DOA,20),'')+', '+ (CASE WHEN D.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE COALESCE(AREADESCRIPTION,'') END AS ADDRESS FROM #T A LEFT JOIN CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' LEFT JOIN #S B ON A.PHONE=B.PHONE";

/* Used in: modules/summary/sum_out_state.php at line 24 */
$sql3 ="CREATE TEMP TABLE TT AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE='$number'";

/* Used in: modules/summary/sum_out_state.php at line 26 */
$sql4 ="CREATE TEMP TABLE RESULT AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL FROM #TT GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_out_state.php at line 34 */
$sql5 ="CREATE TEMP TABLE RESULT1 AS SELECT * FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN ( SELECT DISTINCT OTHER FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR) AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

/* Used in: modules/summary/sum_out_state.php at line 38 */
$sql6="SELECT DISTINCT A.PHONE, CASE WHEN OTHER IN (SELECT PHONE FROM CDATSUSPECT) THEN OTHER+' - '+NICKNAME ELSE OTHER END AS OTHER,[IN],[OUT],CALLS,DUR, FIRSTCALL,LASTCALL, CASE WHEN OTHER=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+' '+CONVERT(VARCHAR,C.DOA,20)+' '+COALESCE(C.CATEGORY_TYPE,'') WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER' WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER' WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER' WHEN LEN(OTHER)<10 AND [OUT]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' WHEN LEN(OTHER)<10 AND [IN]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR CUSTOMER CARE / ENQUIRY NUMBER' WHEN OTHER IN(SELECT DISTINCT PHONE FROM ADDRESS_OTHER_STATE) THEN COALESCE(D.FULLNAME+', '+D.FULLADDRESS,'')+' '+COALESCE(D.CATEGORY_TYPE,'') ELSE AREADESCRIPTION END AS ADDRESS,AREADESCRIPTION,E.STATE FROM #RESULT1 A LEFT JOIN CDATSUSPECT B ON OTHER=B.PHONE LEFT JOIN CDATADDRESS C ON A.OTHER=C.PHONE LEFT JOIN ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE LEFT JOIN CDATPHONEAREA E ON CASE WHEN LEN(OTHER)=10 THEN OTHER ELSE CASE WHEN LEN(OTHER)>10 THEN '00'+OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' WHERE E.STATE !='$state' ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_out_state.php at line 58 */
$sql8="SELECT 'SUMMARY OF MOBILE NO: ' || '$number ' || ' OTHER THAN ' || '$state ' || ' STATE' as PHONE1";

/* Used in: modules/summary/sum_out_state.php at line 60 */
$sql9="CREATE TEMP TABLE T AS SELECT '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED";

/* Used in: modules/summary/sum_out_state.php at line 62 */
$sql10="CREATE TEMP TABLE S AS SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

/* Used in: modules/summary/sum_out_state.php at line 65 */
$sql11="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED, CASE WHEN A.PHONE=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+', '+COALESCE(CONVERT(VARCHAR,C.DOA,20),'')+', '+ (CASE WHEN C.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END) WHEN A.PHONE=D.PHONE THEN COALESCE(D.FULLNAME,'')+', '+COALESCE(D.FULLADDRESS,'')+', '+COALESCE(CONVERT(VARCHAR,D.DOA,20),'')+', '+ (CASE WHEN D.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE COALESCE(AREADESCRIPTION,'') END AS ADDRESS FROM #T A LEFT JOIN CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' LEFT JOIN #S B ON A.PHONE=B.PHONE";

/* Used in: modules/summary/sum_in_state.php at line 27 */
$sql3 ="CREATE TEMP TABLE TT AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE='$number'";

/* Used in: modules/summary/sum_in_state.php at line 29 */
$sql4 ="CREATE TEMP TABLE RESULT AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL FROM #TT GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_in_state.php at line 37 */
$sql5 ="CREATE TEMP TABLE RESULT1 AS SELECT * FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN ( SELECT DISTINCT OTHER FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR) AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

/* Used in: modules/summary/sum_in_state.php at line 41 */
$sql6="SELECT DISTINCT A.PHONE, CASE WHEN OTHER IN (SELECT PHONE FROM CDATSUSPECT) THEN OTHER+' - '+NICKNAME ELSE OTHER END AS OTHER,[IN],[OUT],CALLS,DUR, FIRSTCALL,LASTCALL, CASE WHEN OTHER=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+' '+CONVERT(VARCHAR,C.DOA,20)+' '+COALESCE(C.CATEGORY_TYPE,'') WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER' WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER' WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER' WHEN LEN(OTHER)<10 AND [OUT]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' WHEN LEN(OTHER)<10 AND [IN]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR CUSTOMER CARE / ENQUIRY NUMBER' WHEN OTHER IN(SELECT DISTINCT PHONE FROM ADDRESS_OTHER_STATE) THEN COALESCE(D.FULLNAME+', '+D.FULLADDRESS,'')+' '+COALESCE(D.CATEGORY_TYPE,'') ELSE AREADESCRIPTION END AS ADDRESS,AREADESCRIPTION,E.STATE FROM #RESULT1 A LEFT JOIN CDATSUSPECT B ON OTHER=B.PHONE LEFT JOIN CDATADDRESS C ON A.OTHER=C.PHONE LEFT JOIN ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE LEFT JOIN CDATPHONEAREA E ON CASE WHEN LEN(OTHER)=10 THEN OTHER ELSE CASE WHEN LEN(OTHER)>10 THEN '00'+OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' WHERE E.STATE='$state' ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_in_state.php at line 61 */
$sql8="SELECT 'SUMMARY OF MOBILE NO: ' || '$number ' || ' IN ' || '$state ' || ' STATE' as PHONE1";

/* Used in: modules/summary/sum_in_state.php at line 63 */
$sql9="CREATE TEMP TABLE T AS SELECT '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED";

/* Used in: modules/summary/sum_in_state.php at line 65 */
$sql10="CREATE TEMP TABLE S AS SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

/* Used in: modules/summary/sum_in_state.php at line 68 */
$sql11="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED, CASE WHEN A.PHONE=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+', '+COALESCE(CONVERT(VARCHAR,C.DOA,20),'')+', '+ (CASE WHEN C.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END) WHEN A.PHONE=D.PHONE THEN COALESCE(D.FULLNAME,'')+', '+COALESCE(D.FULLADDRESS,'')+', '+COALESCE(CONVERT(VARCHAR,D.DOA,20),'')+', '+ (CASE WHEN D.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE COALESCE(AREADESCRIPTION,'') END AS ADDRESS FROM #T A LEFT JOIN CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' LEFT JOIN #S B ON A.PHONE=B.PHONE";

/* Used in: modules/summary/sum_home.php at line 28 */
$sql3 = "CREATE TEMP TABLE TT AS SELECT * FROM CDAT_DETAILS  WHERE PHONE='$number' and isnumeric(other)=1";

/* Used in: modules/summary/sum_home.php at line 30 */
$sql4 = "CREATE TEMP TABLE RESULT AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL FROM #TT GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_home.php at line 38 */
$sql5 = "CREATE TEMP TABLE RESULT1 AS SELECT * FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN ( SELECT DISTINCT OTHER FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR) AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

/* Used in: modules/summary/sum_home.php at line 42 */
$sql6 = "SELECT PHONE, OTHER, [IN], [OUT], CALLS, DUR, FIRSTCALL, LASTCALL FROM #RESULT1 ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_home.php at line 45 */
$sql8 = "SELECT 'SUMMARY OF MOBILE NO: ' || '$number' as PHONE1";

/* Used in: modules/summary/sum_home.php at line 47 */
$sql10 = "SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED FROM CDATPCSUSPECT A  LEFT JOIN CDATSUSPECT B  ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

/* Used in: modules/summary/sum_home.php at line 50 */
$sql12 = "SELECT case when count(PHONE)>=1 THEN '' ELSE 'Records not found' end as PHONE FROM #RESULT";

/* Used in: modules/summary/sum_between_dates.php at line 29 */
$sql1 = "CREATE TEMP TABLE TT AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE='$number' AND TO_CHAR(STARTTIME, 'YYYY-MM-DD') BETWEEN'$f_date' and '$t_date'";

/* Used in: modules/summary/sum_between_dates.php at line 31 */
$sql2 = "CREATE TEMP TABLE RESULT AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL FROM #TT GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_between_dates.php at line 39 */
$sql3 = "CREATE TEMP TABLE RESULT1 AS SELECT * FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN ( SELECT DISTINCT OTHER FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR) AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

/* Used in: modules/summary/sum_between_dates.php at line 43 */
$sql4 = "SELECT DISTINCT A.PHONE, CASE WHEN OTHER IN (SELECT PHONE FROM CDATSUSPECT) THEN OTHER+' - '+NICKNAME ELSE OTHER END AS OTHER,[IN],[OUT],CALLS,DUR, FIRSTCALL,LASTCALL, CASE WHEN OTHER=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+' '+CONVERT(VARCHAR,C.DOA,20)+' '+COALESCE(C.CATEGORY_TYPE,'') WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER' WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER' WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER' WHEN LEN(OTHER)<10 AND [OUT]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' WHEN LEN(OTHER)<10 AND [IN]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR CUSTOMER CARE / ENQUIRY NUMBER' WHEN OTHER IN(SELECT DISTINCT PHONE FROM ADDRESS_OTHER_STATE) THEN COALESCE(D.FULLNAME+', '+D.FULLADDRESS,'')+' '+COALESCE(D.CATEGORY_TYPE,'') ELSE AREADESCRIPTION END AS ADDRESS FROM #RESULT1 A LEFT JOIN CDATSUSPECT B ON OTHER=B.PHONE LEFT JOIN CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA E ON CASE WHEN LEN(OTHER)=10 THEN OTHER ELSE CASE WHEN LEN(OTHER)>10 THEN '00'+OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_between_dates.php at line 63 */
$sql6 = "CREATE TEMP TABLE T AS SELECT '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED";

/* Used in: modules/summary/sum_between_dates.php at line 65 */
$sql7 = "CREATE TEMP TABLE S AS SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

/* Used in: modules/summary/sum_between_dates.php at line 68 */
$sql8 = "SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL, CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME, CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED, CASE WHEN A.PHONE=C.PHONE THEN COALESCE(C.FULLNAME,'')+', '+COALESCE(C.FULLADDRESS,'')+', '+COALESCE(CONVERT(VARCHAR,C.DOA,20),'')+', '+ (CASE WHEN C.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END) WHEN A.PHONE=D.PHONE THEN COALESCE(D.FULLNAME,'')+', '+COALESCE(D.FULLADDRESS,'')+', '+COALESCE(CONVERT(VARCHAR,D.DOA,20),'')+', '+ (CASE WHEN D.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE COALESCE(AREADESCRIPTION,'') END AS ADDRESS FROM #T A LEFT JOIN CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX+'%' LEFT JOIN #S B ON A.PHONE=B.PHONE";

/* Used in: modules/summary/sum_isd_cnts.php at line 26 */
$sql1 = "CREATE TEMP TABLE XX AS SELECT DISTINCT * FROM CDATPCSUSPECT WHERE phone = ?";

/* Used in: modules/summary/sum_isd_cnts.php at line 31 */
$sql3 = "CREATE TEMP TABLE TEMP AS SELECT * FROM CDAT_DETAILS1 WHERE LEN(OTHER) > 10 AND DURATION > '0' AND PHONE = ?";

/* Used in: modules/summary/sum_isd_cnts.php at line 36 */
$sql4 = "CREATE TEMP TABLE TT AS SELECT DISTINCT * FROM #TEMP";

/* Used in: modules/summary/sum_isd_cnts.php at line 39 */
$sql5 = "CREATE TEMP TABLE RESULT AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN', SUM(CASE WHEN INCOMING ='0' THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS, SUM(CAST(DURATION AS NUMERIC)) AS DUR, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRSTCALL, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LASTCALL FROM #TT GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

/* Used in: modules/summary/sum_isd_cnts.php at line 49 */
$sql6 = "CREATE TEMP TABLE WITHADDRESS AS SELECT A.PHONE, CASE WHEN A.OTHER = B.PHONE THEN OTHER + ', - ' + NICKNAME ELSE OTHER END AS OTHER, [IN],[OUT], CALLS, DUR, FIRSTCALL, LASTCALL, COALESCE(AREADESCRIPTION, 'CODE N/A') AS ADDRESS FROM #RESULT A LEFT JOIN cdatsuspect B ON a.other = B.phone LEFT JOIN cdatphonearea C ON '00' + other LIKE phoneprefix + '%' WHERE A.OTHER NOT LIKE '1800%' GROUP BY a.PHONE, B.PHONE, other, [IN],[OUT], calls, dur, FIRSTCALL, LASTCALL, nickname, AREADESCRIPTION";

/* Used in: modules/summary/sum_isd_cnts.php at line 60 */
$sql7 = "SELECT * FROM #WITHADDRESS WHERE ADDRESS != ' JUNK-COULD BE bulk SMS or VOIP calls' ORDER BY calls DESC";

/* Used in: modules/summary/sum_isd_cnts.php at line 63 */
$sql8 = "SELECT 'ISD CONTACTS OF MOBILE NO: ' + ? AS PHONE1";

/* Used in: modules/summary/sum_isd_cnts.php at line 68 */
$sql9 = "CREATE TEMP TABLE T AS SELECT ? AS PHONE, '' AS FIRST_CALL, '' AS LAST_CALL, '' AS NICKNAME, '' AS LAST_UPDATED";

/* Used in: modules/summary/sum_isd_cnts.php at line 73 */
$sql10 = "CREATE TEMP TABLE S AS SELECT A.PHONE, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL, B.NICKNAME, CONVERT(VARCHAR, MAX(A.ASONDATE), 20) AS LAST_UPDATED FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE = B.PHONE WHERE A.PHONE = ? GROUP BY A.PHONE, B.NICKNAME";

/* Used in: modules/summary/sum_isd_cnts.php at line 83 */
$sql11 = "SELECT DISTINCT A.PHONE, CASE WHEN A.PHONE = B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL, CASE WHEN A.PHONE = B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL, CASE WHEN A.PHONE = B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME, CASE WHEN A.PHONE = B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED, CASE WHEN A.PHONE = C.PHONE THEN COALESCE(C.FULLNAME, '') + ', ' + COALESCE(C.FULLADDRESS, '') + ', ' + COALESCE(CONVERT(VARCHAR, C.DOA, 20), '') + ', ' + (CASE WHEN C.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION, '') ELSE C.CATEGORY_TYPE END) WHEN A.PHONE = D.PHONE THEN COALESCE(D.FULLNAME, '') + ', ' + COALESCE(D.FULLADDRESS, '') + ', ' + COALESCE(CONVERT(VARCHAR, D.DOA, 20), '') + ', ' + (CASE WHEN D.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION, '') ELSE D.CATEGORY_TYPE END) ELSE COALESCE(AREADESCRIPTION, '') END AS ADDRESS FROM #T A LEFT JOIN CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL LEFT JOIN CDATPHONEAREA ON CASE WHEN LEN(A.PHONE) = 10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END LIKE PHONEPREFIX + '%' LEFT JOIN #S B ON A.PHONE = B.PHONE";

/* Used in: modules/day-night-location/day&nightloc_btwn_dates.php at line 27 */
$sql1 = "CREATE TEMP TABLE TEMP AS SELECT * FROM CDATPCSUSPECT WHERE (TO_CHAR(STARTTIME, 'HH24:MI:SS')<'22:00:00' AND TO_CHAR(STARTTIME, 'HH24:MI:SS')>'05:00:00') AND PHONE='$number' AND TO_CHAR(STARTTIME, 'YYYY-MM-DD') BETWEEN '$f_date' AND '$t_date'";

/* Used in: modules/day-night-location/day&nightloc_btwn_dates.php at line 33 */
$sql4 = "CREATE TEMP TABLE T AS SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS, SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM FROM #TT1 GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

/* Used in: modules/day-night-location/day&nightloc_btwn_dates.php at line 37 */
$sql5 = 'SELECT  * FROM #T order by calls desc LIMIT 10';

/* Used in: modules/day-night-location/day&nightloc_btwn_dates.php at line 39 */
$sql6 = "SELECT 'DAY LOCATION OF MOBILE NO: ' || '$number' || ' BETWEEN ' || '$f_date' || ' AND ' || '$t_date' as PHONE1";

/* Used in: modules/day-night-location/day&nightloc_btwn_dates.php at line 41 */
$sql7 = "SELECT 'NIGHT LOCATION OF MOBILE NO: ' || '$number' || ' BETWEEN ' || '$f_date' || ' AND ' || '$t_date' as PHONE1";

/* Used in: modules/day-night-location/day&nightloc_btwn_dates.php at line 43 */
$sql8 = "CREATE TEMP TABLE T1 AS SELECT * FROM CDATPCSUSPECT WHERE (TO_CHAR(STARTTIME, 'HH24:MI:SS')>'22:00:00' OR TO_CHAR(STARTTIME, 'HH24:MI:SS')<'07:00:00') AND PHONE='$number' AND TO_CHAR(STARTTIME, 'YYYY-MM-DD') BETWEEN '$f_date' AND '$t_date'";

/* Used in: modules/day-night-location/day&nightloc_btwn_dates.php at line 49 */
$sql11 = "CREATE TEMP TABLE T4 AS SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS, SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM FROM #T3 GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

/* Used in: modules/day-night-location/day&nightloc_btwn_dates.php at line 53 */
$sql12 = 'SELECT  * FROM #T4 order by calls desc LIMIT 10';