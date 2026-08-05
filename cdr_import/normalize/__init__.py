"""Per-operator CDR normalization (ported from legacy MSSQL import procs).

Only the selected operator's normalizer runs. Other operators are unchanged.
"""
from __future__ import annotations

from typing import Optional

from ..models import CdrRecord


def apply_operator_normalization(
    rec: CdrRecord,
    operator: str,
    *,
    conn=None,
    enricher=None,
) -> Optional[CdrRecord]:
    """Apply operator-specific normalization.

    Returns the record, or None when the legacy proc would have deleted the row.
    Operators without a dedicated normalizer return the record untouched.
    """
    key = (operator or '').strip().lower()
    if key == 'airtel':
        from .airtel import normalize_airtel_record

        return normalize_airtel_record(rec, conn=conn, enricher=enricher)
    if key == 'bsnl':
        from .bsnl import normalize_bsnl_record

        return normalize_bsnl_record(rec, conn=conn, enricher=enricher)
    if key == 'jio':
        from .jio import normalize_jio_record

        return normalize_jio_record(rec, conn=conn, enricher=enricher)
    if key in ('vi', 'vodafone', 'idea'):
        from .vi import normalize_vi_record

        return normalize_vi_record(rec, conn=conn, enricher=enricher)
    return rec
