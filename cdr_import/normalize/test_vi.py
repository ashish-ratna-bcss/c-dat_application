"""Unit tests for Vi/Vodafone field normalization (no DB required)."""
from __future__ import annotations

from datetime import datetime
import unittest

from cdr_import.models import CdrRecord
from cdr_import.normalize import apply_operator_normalization
from cdr_import.normalize.vi import (
    normalize_vi_fields,
    _normalize_vi_msisdn,
    _normalize_celltowerid,
    _map_roaming_to_state,
    _apply_operator_cell_transform,
    _apply_vi_special_phone_prefixes,
)


def _rec(**kwargs) -> CdrRecord:
    base = dict(
        phone='919876543210',
        other='919111111111',
        starttime=datetime(2024, 1, 15, 10, 30, 0),
        duration=12,
        incoming=0,
        imeinumber=123456789012345,
        provider_key=12,
        call_type='OUTGOING_VOICE',
        celltowerid='404-12-34567-8',
        first_cellid='404-12-34567-8',
        last_cellid='404-12-34567-9',
        roaming_nw='AP-Vodafone-INDIA',
        calling_no='919876543210',
        called_no='919111111111',
        raw={'Service Type': 'VOICE', 'CallType': 'OUTGOING'},
    )
    base.update(kwargs)
    return CdrRecord(**base)


class ViNormalizeTests(unittest.TestCase):
    def test_non_vi_untouched(self):
        rec = _rec()
        out = apply_operator_normalization(rec, 'unknown', conn=None)
        self.assertIs(out, rec)

    def test_strip_phone_and_other(self):
        self.assertEqual(_normalize_vi_msisdn('919876543210', is_phone=True), '9876543210')
        self.assertEqual(_normalize_vi_msisdn('00919876543210', is_phone=True), '9876543210')
        self.assertEqual(_normalize_vi_msisdn('09876543210', is_phone=False), '9876543210')
        # Special prefixes are deferred until after PHONE=OTHER delete.
        self.assertEqual(_normalize_vi_msisdn('30649876543210', is_phone=True), '30649876543210')
        self.assertEqual(_apply_vi_special_phone_prefixes('30649876543210'), '9876543210')
        self.assertEqual(_apply_vi_special_phone_prefixes('613064987654321099'), '987654321099')

    def test_special_prefix_after_phone_eq_other_delete(self):
        # Before specials: phone != other → keep; after specials phone may equal other.
        out = normalize_vi_fields(
            _rec(phone='30649876543210', other='9876543210', calling_no='30649876543210')
        )
        self.assertIsNotNone(out)
        self.assertEqual(out.phone, '9876543210')
        self.assertEqual(out.other, '9876543210')

    def test_drop_phone_equals_other_before_specials(self):
        self.assertIsNone(
            normalize_vi_fields(_rec(phone='9876543210', other='9876543210'))
        )

    def test_outgoing_incoming(self):
        out = normalize_vi_fields(_rec(call_type='OUTGOING_VOICE'))
        self.assertEqual(out.incoming, 0)
        out = normalize_vi_fields(_rec(call_type='INCOMING_VOICE'))
        self.assertEqual(out.incoming, 1)

    def test_drop_both_non_numeric(self):
        self.assertIsNone(normalize_vi_fields(_rec(phone='ABC', other='XYZ')))

    def test_sms_truncates_other(self):
        out = normalize_vi_fields(
            _rec(call_type='OUTGOING_SMS', other='12345678901234567890', phone='9876543210')
        )
        self.assertEqual(out.other, '123456789012345')

    def test_celltower_hygiene(self):
        # After -00→- collapse, len<10 strips remaining dashes.
        self.assertEqual(_normalize_celltowerid('12-00-34'), '1234')
        self.assertEqual(_normalize_celltowerid('---'), '0')
        self.assertEqual(_normalize_celltowerid('12-3'), '123')

    def test_idea_airtel_cell_transform(self):
        self.assertEqual(
            _apply_operator_cell_transform('40-4123456789', 'IDEA'),
            '404123456789',
        )
        # MSSQL SUBSTRING(cell,8,30) on '405-12-34567890' → '34567890'
        self.assertEqual(
            _apply_operator_cell_transform('405-12-34567890', 'AIRTEL'),
            '34567890',
        )

    def test_roaming_map(self):
        self.assertEqual(_map_roaming_to_state('XX-ANDHRA-YY'), 'ANDHRA PRADESH')
        self.assertEqual(_map_roaming_to_state('FOO-HONDURAS-BAR'), 'ROAMING_FOO-HONDURAS-BAR')

    def test_provider_key_forced(self):
        out = normalize_vi_fields(_rec(provider_key=99))
        self.assertEqual(out.provider_key, 12)

    def test_imei_dash_becomes_zero(self):
        out = normalize_vi_fields(_rec(imeinumber='12-345'))
        self.assertEqual(out.imeinumber, 0)

    def test_vodafone_alias_hook(self):
        out = apply_operator_normalization(_rec(), 'vodafone', conn=None)
        self.assertIsNotNone(out)
        self.assertEqual(out.provider_key, 12)
        self.assertEqual(out.phone, '9876543210')

    def test_apply_operator_hook(self):
        out = apply_operator_normalization(_rec(), 'vi', conn=None)
        self.assertIsNotNone(out)
        self.assertEqual(out.provider_key, 12)


if __name__ == '__main__':
    unittest.main()
