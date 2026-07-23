from __future__ import annotations

def refresh_cdr_staging_duplicates(conn, qualified_table: str) -> dict:
    with conn.cursor() as cur:
        cur.execute(f'UPDATE {qualified_table} SET is_duplicate = FALSE, duplicate_reason = NULL')
        cur.execute(f"\n            UPDATE {qualified_table} s\n            SET is_duplicate = TRUE, duplicate_reason = 'exists_in_main'\n            WHERE EXISTS (\n                SELECT 1 FROM cdatpcsuspect t\n                WHERE t.phone = s.phone\n                  AND t.other = s.other\n                  AND t.starttime = s.starttime\n            )\n        ")
        marked_main = cur.rowcount
        cur.execute(f"\n            UPDATE {qualified_table} s\n            SET is_duplicate = TRUE,\n                duplicate_reason = COALESCE(s.duplicate_reason, 'duplicate_in_batch')\n            WHERE staging_row_id NOT IN (\n                SELECT MIN(staging_row_id)\n                FROM {qualified_table}\n                GROUP BY COALESCE(phone, ''), COALESCE(other, ''), starttime\n            )\n        ")
        marked_batch = cur.rowcount
        cur.execute(f'SELECT COUNT(*) FROM {qualified_table} WHERE is_duplicate = TRUE')
        dup = int(cur.fetchone()[0])
        cur.execute(f'SELECT COUNT(*) FROM {qualified_table} WHERE COALESCE(is_duplicate, FALSE) = FALSE')
        valid = int(cur.fetchone()[0])
    return {'marked_in_main': marked_main, 'marked_in_batch': marked_batch, 'duplicate_count': dup, 'valid_count': valid}
