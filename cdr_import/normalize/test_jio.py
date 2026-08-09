"""Unit tests for Jio field normalization (no DB required)."""
from __future__ import annotations

from datetime import datetime
import unittest

from cdr_import.models import CdrRecord
from cdr_import.normalize import apply_operator_normalization
from cdr_import.normalize.jio import (
    normalize_jio_fields,
    _normalize_jio_msisdn,
    _normalize_celltowerid,
)


def _rec(**kwargs) -> CdrRecord:
    base = dict(
        phone='9876543210',
        other='9111111111',
        starttime=datetime(2024, 1, 15, 10, 30, 0),
        duration=12,
        incoming=0,
        imeinumber=123456789012345,
        provider_key=15,
        call_type='VOICE_OUT',
        celltowerid='405-12-34567-8',
        first_cellid='405-12-34567-8',
        last_cellid='405-12-34567-9',
        roaming_nw='MAHARASHTRA',
        calling_no='919876543210',
        called_no='919111111111',
        raw={},
    )
    base.update(kwargs)
    return CdrRecord(**base)


class JioNormalizeTests(unittest.TestCase):
    def test_non_jio_untouched(self):
        rec = _rec()
        out = apply_operator_normalization(rec, 'vi', conn=None)
        self.assertIs(out, rec)

    def test_strip_prefixes(self):
        self.assertEqual(_normalize_jio_msisdn('919876543210'), '9876543210')
        self.assertEqual(_normalize_jio_msisdn('09876543210'), '9876543210')
        # Sequential: strip 00 then 91 (unlike single-pass operators).
        self.assertEqual(_normalize_jio_msisdn('00919876543210'), '9876543210')
        self.assertEqual(_normalize_jio_msisdn('959876543210'), '9876543210')

    def test_34500_strip(self):
        self.assertEqual(_normalize_jio_msisdn('34500987654321099'), '987654321099')

    def test_keep_alpha_sender(self):
        self.assertEqual(_normalize_jio_msisdn('JA-JIONEW'), 'JA-JIONEW')

    def test_out_assigns_calling_as_phone(self):
        out = normalize_jio_fields(
            _rec(
                call_type='SMSOUT',
                calling_no='919876543210',
                called_no='919111111111',
            )
        )
        self.assertEqual(out.incoming, 0)
        self.assertEqual(out.phone, '9876543210')
        self.assertEqual(out.other, '9111111111')

    def test_in_assigns_called_as_phone(self):
        out = normalize_jio_fields(
            _rec(
                call_type='VOICE_IN',
                calling_no='919111111111',
                called_no='919876543210',
            )
        )
        self.assertEqual(out.incoming, 1)
        self.assertEqual(out.phone, '9876543210')
        self.assertEqual(out.other, '9111111111')

    def test_drop_both_non_numeric(self):
        self.assertIsNone(
            normalize_jio_fields(_rec(phone='ABC', other='XYZ', calling_no='ABC', called_no='XYZ'))
        )

    def test_keep_when_one_numeric(self):
        out = normalize_jio_fields(
            _rec(
                call_type='SMSIN',
                calling_no='JA-JIONEW',
                called_no='9876543210',
            )
        )
        self.assertIsNotNone(out)
        self.assertEqual(out.phone, '9876543210')
        self.assertEqual(out.other, 'JA-JIONEW')

    def test_imei_short_becomes_zero(self):
        out = normalize_jio_fields(_rec(imeinumber=12345))
        self.assertEqual(out.imeinumber, 0)

    def test_celltower_hygiene(self):
        self.assertEqual(_normalize_celltowerid('12--34--56'), '12-34-56')
        self.assertEqual(_normalize_celltowerid(None), '0')
        self.assertEqual(_normalize_celltowerid('12345678901234567890'), '123456789012345')

    def test_provider_key_forced(self):
        out = normalize_jio_fields(_rec(provider_key=99))
        self.assertEqual(out.provider_key, 15)

    def test_chattisgarh_typo_fix(self):
        out = apply_operator_normalization(
            _rec(roaming_nw='CHATTISGARH', otherinfo=None),
            'jio',
            conn=None,
        )
        self.assertEqual(out.otherinfo, 'CHHATTISGARH')

    def test_null_other_becomes_zero(self):
        out = apply_operator_normalization(
            _rec(
                call_type='VOICE_OUT',
                calling_no='9876543210',
                called_no=None,
                other=None,
            ),
            'jio',
            conn=None,
        )
        # other becomes empty after assign, then '0' at end of no-DB path
        self.assertEqual(out.other, '0')

    def test_apply_operator_hook(self):
        out = apply_operator_normalization(_rec(), 'jio', conn=None)
        self.assertIsNotNone(out)
        self.assertEqual(out.provider_key, 15)


if __name__ == '__main__':
    unittest.main()
