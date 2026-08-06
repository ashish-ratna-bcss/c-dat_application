from __future__ import annotations


# Production match key (preview + promote NOT EXISTS).
# IS NOT DISTINCT FROM so NULL phone/other compare equal.
CDR_DEDUP_MATCH_SQL = """
    t.starttime IS NOT DISTINCT FROM s.starttime
    AND t.phone IS NOT DISTINCT FROM s.phone
    AND t.other IS NOT DISTINCT FROM s.other
    AND t.duration IS NOT DISTINCT FROM s.duration
    AND t.incoming IS NOT DISTINCT FROM s.incoming
"""


def refresh_cdr_staging_duplicates(
    conn,
    qualified_table: str,
    *,
    import_job_id: int | None = None,
) -> dict:
    """Flag staging rows that already exist in production.

    Does NOT flag within-upload duplicates (those are collapsed only at promote).
    When import_job_id is set, only that job's rows are cleared/flagged/counted.
    """
    job_filter = ''
    params: tuple = ()
    if import_job_id is not None:
        job_filter = ' AND s.import_job_id = %s'
        params = (import_job_id,)

    with conn.cursor() as cur:
        # Cap dedup so a slow scan cannot wedge the DB / block new uploads for hours.
        # Disable role-level timeout first, then apply an explicit long budget.
        cur.execute("SET LOCAL statement_timeout TO '0'")
        cur.execute("SET LOCAL statement_timeout TO '30min'")
        cur.execute("SET LOCAL lock_timeout TO '0'")
        if import_job_id is not None:
            cur.execute(
                f'''
                UPDATE {qualified_table} s
                SET is_duplicate = FALSE, duplicate_reason = NULL
                WHERE s.import_job_id = %s
                ''',
                (import_job_id,),
            )
        else:
            cur.execute(
                f'UPDATE {qualified_table} SET is_duplicate = FALSE, duplicate_reason = NULL'
            )

        cur.execute(
            f'''
            UPDATE {qualified_table} s
            SET is_duplicate = TRUE, duplicate_reason = 'exists_in_main'
            WHERE EXISTS (
                SELECT 1 FROM cdatpcsuspect t
                WHERE {CDR_DEDUP_MATCH_SQL}
            )
            {job_filter}
            ''',
            params,
        )
        marked_main = cur.rowcount

        if import_job_id is not None:
            cur.execute(
                f'SELECT COUNT(*) FROM {qualified_table} WHERE is_duplicate = TRUE AND import_job_id = %s',
                (import_job_id,),
            )
            dup = int(cur.fetchone()[0])
            cur.execute(
                f'''
                SELECT COUNT(*) FROM {qualified_table}
                WHERE COALESCE(is_duplicate, FALSE) = FALSE AND import_job_id = %s
                ''',
                (import_job_id,),
            )
            valid = int(cur.fetchone()[0])
        else:
            cur.execute(f'SELECT COUNT(*) FROM {qualified_table} WHERE is_duplicate = TRUE')
            dup = int(cur.fetchone()[0])
            cur.execute(
                f'SELECT COUNT(*) FROM {qualified_table} WHERE COALESCE(is_duplicate, FALSE) = FALSE'
            )
            valid = int(cur.fetchone()[0])

    return {
        'marked_in_main': marked_main,
        'marked_in_batch': 0,
        'duplicate_count': dup,
        'valid_count': valid,
    }
