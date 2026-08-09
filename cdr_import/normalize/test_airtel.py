"""Unit tests for Airtel field normalization (no DB required)."""
from __future__ import annotations

from datetime import datetime
import unittest

from cdr_import.models import CdrRecord
from cdr_import.normalize import apply_operator_normalization
from cdr_import.normalize.airtel import (
    normalize_airtel_fields,
    _normalize_cell_id,
    _normalize_msisdn,
    _map_roaming_to_state,
)


def _rec(**kwargs) -> CdrRecord:
    base = dict(
        phone='919876543210',
        other='919111111111',
        starttime=datetime(2024, 1, 15, 10, 30, 0),
        duration=12,
        incoming=1,
        imeinumber=123456789012345,
        provider_key=2,
        call_type='IN',
        celltowerid='405-12-34567-890',
        first_cellid='405-12-34567-890',
        last_cellid='405-12-34567-891',
        roaming_nw='AIRTELAP',
        otherinfo='VOICE',
        raw={},
    )
    base.update(kwargs)
    return CdrRecord(**base)


class AirtelNormalizeTests(unittest.TestCase):
    def test_non_airtel_untouched(self):
        rec = _rec(phone='91XXXXXXXXXX')
        out = apply_operator_normalization(rec, 'jio', conn=None)
        self.assertIs(out, rec)

    def test_strip_91_and_0_prefixes(self):
        self.assertEqual(_normalize_msisdn('919876543210'), '9876543210')
        self.assertEqual(_normalize_msisdn('09876543210'), '9876543210')
        self.assertEqual(_normalize_msisdn('00919876543210'), '9876543210')

    def test_special_prefix_3006(self):
        self.assertEqual(_normalize_msisdn('30069876543210'), '9876543210')

    def test_outgoing_call_types(self):
        for ct in ('OUT', 'SMS', 'OG', 'DAT', 'SMS_MOC'):
            out = normalize_airtel_fields(_rec(call_type=ct))
            self.assertEqual(out.incoming, 0, ct)
        out = normalize_airtel_fields(_rec(call_type='IN'))
        self.assertEqual(out.incoming, 1)

    def test_drop_dsm_null_phone(self):
        out = normalize_airtel_fields(_rec(phone=None, call_type='DSM'))
        self.assertIsNone(out)

    def test_dsm_short_phone_swap(self):
        out = normalize_airtel_fields(_rec(phone='12345', other='9876543210', call_type='DSM'))
        self.assertEqual(out.phone, '9876543210')
        self.assertEqual(out.other, '12345')

    def test_cell_id_40_wildcard_strip(self):
        # MSSQL LIKE '40_-%' on first 4 chars → strip from position 8
        self.assertEqual(_normalize_cell_id('405-abcdefghijk'), 'defghijk')

    def test_cell_id_hygiene(self):
        self.assertEqual(_normalize_cell_id('-'), '0')
        self.assertEqual(_normalize_cell_id('NULL-NULL'), '0')
        self.assertEqual(_normalize_cell_id('12_34'), '12-34')

    def test_roaming_state_map(self):
        self.assertEqual(_map_roaming_to_state('AIRTELAP'), 'ANDHRA PRADESH')
        self.assertEqual(_map_roaming_to_state('SOMETHINGPUNXYZ'), 'PUNJAB')

    def test_roaming_air_prefers_roamnw(self):
        rec = _rec(
            roaming_nw='AIRTELKER',
            raw={'Roam Nw': 'AIRTELKER', 'LRNTSPLSA': 'OTHERLSA'},
        )
        out = normalize_airtel_fields(rec)
        self.assertEqual(out.roaming_nw, 'AIRTELKER')
        self.assertEqual(out.otherinfo, 'KERALA')

    def test_roaming_non_air_uses_lrn(self):
        rec = _rec(
            roaming_nw='SOMETHINGELSE',
            raw={'Roam Nw': 'SOMETHINGELSE', 'LRNTSPLSA': 'AIRTELTN'},
        )
        out = normalize_airtel_fields(rec)
        self.assertEqual(out.roaming_nw, 'AIRTELTN')
        self.assertEqual(out.otherinfo, 'TAMILNADU')

    def test_provider_key_forced(self):
        out = normalize_airtel_fields(_rec(provider_key=99))
        self.assertEqual(out.provider_key, 2)

    def test_other_null_becomes_empty(self):
        out = normalize_airtel_fields(_rec(other=None, call_type='IN'))
        self.assertEqual(out.other, '')


if __name__ == '__main__':
    unittest.main()
