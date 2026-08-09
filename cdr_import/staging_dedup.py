from __future__ import annotations

# Production duplicate key (matches legacy MSSQL CDR check):
#   starttime, phone, other, duration, incoming
_PROD_DUP_MATCH = """
    t.phone = s.phone
    AND t.other IS NOT DISTINCT FROM s.other
    AND t.starttime = s.starttime
    AND t.duration IS NOT DISTINCT FROM s.duration
    AND t.incoming IS NOT DISTINCT FROM s.incoming
"""


def refresh_cdr_staging_duplicates(conn, qualified_table: str) -> dict:
    """Flag staging rows that already exist in production.

    Within-file / within-staging duplicates are intentionally not flagged —
    only matches against cdatpcsuspect count as duplicates.
    """
    with conn.cursor() as cur:
        cur.execute(f'UPDATE {qualified_table} SET is_duplicate = FALSE, duplicate_reason = NULL')
        cur.execute(
            f"""
            UPDATE {qualified_table} s
            SET is_duplicate = TRUE, duplicate_reason = 'exists_in_main'
            WHERE EXISTS (
                SELECT 1 FROM cdatpcsuspect t
                WHERE {_PROD_DUP_MATCH}
            )
            """
        )
        marked_main = cur.rowcount
        cur.execute(f'SELECT COUNT(*) FROM {qualified_table} WHERE is_duplicate = TRUE')
        dup = int(cur.fetchone()[0])
        cur.execute(f'SELECT COUNT(*) FROM {qualified_table} WHERE COALESCE(is_duplicate, FALSE) = FALSE')
        valid = int(cur.fetchone()[0])
    return {
        'marked_in_main': marked_main,
        'marked_in_batch': 0,
        'duplicate_count': dup,
        'valid_count': valid,
    }


def production_not_exists_sql(staging_alias: str = 's', production_alias: str = 't') -> str:
    """SQL predicate: staging row is not already in production (for promote INSERT)."""
    return f"""
        NOT EXISTS (
            SELECT 1 FROM cdatpcsuspect {production_alias}
            WHERE {production_alias}.phone = {staging_alias}.phone
              AND {production_alias}.other IS NOT DISTINCT FROM {staging_alias}.other
              AND {production_alias}.starttime = {staging_alias}.starttime
              AND {production_alias}.duration IS NOT DISTINCT FROM {staging_alias}.duration
              AND {production_alias}.incoming IS NOT DISTINCT FROM {staging_alias}.incoming
        )
    """
