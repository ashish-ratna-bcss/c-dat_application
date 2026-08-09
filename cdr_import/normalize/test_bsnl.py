"""Unit tests for BSNL field normalization (no DB required)."""
from __future__ import annotations

from datetime import datetime
import unittest

from cdr_import.models import CdrRecord
from cdr_import.normalize import apply_operator_normalization
from cdr_import.normalize.bsnl import (
    normalize_bsnl_fields,
    _format_bsnl_cell_id,
    _normalize_bsnl_msisdn,
    _apply_roaming_state_overrides,
)


def _rec(**kwargs) -> CdrRecord:
    base = dict(
        phone='919876543210',
        other='919111111111',
        starttime=datetime(2024, 1, 15, 10, 30, 0),
        duration=12,
        incoming=0,
        imeinumber=123456789012345,
        provider_key=4,
        call_type='IN',
        celltowerid='40410123456789',
        first_cellid='40410123456789',
        last_cellid='40410123456780',
        roaming_nw='BSNL_AP',
        otherinfo='VOICE',
        raw={'Service_Type': 'VOICE', 'Call_Type': 'IN'},
    )
    base.update(kwargs)
    return CdrRecord(**base)


class BsnlNormalizeTests(unittest.TestCase):
    def test_non_bsnl_untouched(self):
        rec = _rec(phone='91XXXXXXXXXX')
        out = apply_operator_normalization(rec, 'jio', conn=None)
        self.assertIs(out, rec)

    def test_strip_91_and_0_prefixes(self):
        self.assertEqual(_normalize_bsnl_msisdn('919876543210'), '9876543210')
        self.assertEqual(_normalize_bsnl_msisdn('09876543210'), '9876543210')
        # Legacy strips only one of 00/95/91 (not nested): 00 → leaves 91…
        self.assertEqual(_normalize_bsnl_msisdn('00919876543210'), '919876543210')
        self.assertEqual(_normalize_bsnl_msisdn('959876543210'), '9876543210')

    def test_format_cell_id_len14(self):
        # 14 chars: substr(6,4)+'-'+substr(10,…)
        self.assertEqual(_format_bsnl_cell_id('40410123456789'), '1234-56789')

    def test_format_cell_id_other_len(self):
        # e.g. 13 chars: substr(6,3)+'-'+substr(9,…)
        self.assertEqual(_format_bsnl_cell_id('4041012345678'), '123-45678')

    def test_incoming_from_combined_type(self):
        out = normalize_bsnl_fields(
            _rec(call_type='IN', raw={'Service_Type': 'SMS', 'Call_Type': 'IN'})
        )
        self.assertEqual(out.incoming, 1)
        out = normalize_bsnl_fields(
            _rec(call_type='OUT', raw={'Service_Type': 'VOICE', 'Call_Type': 'OUT'})
        )
        self.assertEqual(out.incoming, 0)
        out = normalize_bsnl_fields(
            _rec(call_type='Callforward', raw={'Service_Type': '', 'Call_Type': 'Callforward'})
        )
        self.assertEqual(out.incoming, 1)

    def test_drop_null_other(self):
        self.assertIsNone(normalize_bsnl_fields(_rec(other=None)))

    def test_drop_tel_equals_other(self):
        self.assertIsNone(
            normalize_bsnl_fields(_rec(phone='9876543210', other='9876543210'))
        )

    def test_drop_non_numeric_tel(self):
        self.assertIsNone(normalize_bsnl_fields(_rec(phone='ABCDEF')))

    def test_provider_key_forced(self):
        out = normalize_bsnl_fields(_rec(provider_key=99))
        self.assertEqual(out.provider_key, 4)

    def test_first_cellid_stays_raw_celltowerid_formatted(self):
        raw = '40410123456789'
        out = normalize_bsnl_fields(_rec(first_cellid=raw, celltowerid=raw))
        self.assertEqual(out.first_cellid, raw)
        self.assertEqual(out.celltowerid, '1234-56789')

    def test_imei_truncated_and_non_numeric(self):
        out = normalize_bsnl_fields(_rec(imeinumber=1234567890123456789))
        self.assertEqual(out.imeinumber, 123456789012345)
        out = normalize_bsnl_fields(_rec(imeinumber=0))
        # 0 is numeric
        self.assertEqual(out.imeinumber, 0)

    def test_roaming_state_overrides(self):
        rec = _rec(roaming_nw='BSNL_AP', state_key=99)
        _apply_roaming_state_overrides(rec)
        self.assertEqual(rec.state_key, 1)
        rec = _rec(roaming_nw='something_karna_x', state_key=99)
        _apply_roaming_state_overrides(rec)
        self.assertEqual(rec.state_key, 12)
        rec = _rec(roaming_nw='INDMH', state_key=99)
        _apply_roaming_state_overrides(rec)
        self.assertEqual(rec.state_key, 15)

    def test_roaming_truncated(self):
        long_roam = 'ABCDEFGHIJKLMNOPQRST'
        out = normalize_bsnl_fields(_rec(roaming_nw=long_roam))
        self.assertEqual(len(out.roaming_nw), 15)

    def test_apply_operator_hook(self):
        out = apply_operator_normalization(_rec(), 'bsnl', conn=None)
        self.assertIsNotNone(out)
        self.assertEqual(out.provider_key, 4)
        self.assertEqual(out.phone, '9876543210')


if __name__ == '__main__':
    unittest.main()
