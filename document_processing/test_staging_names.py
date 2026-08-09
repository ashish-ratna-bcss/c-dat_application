"""Staging table names must ignore per-upload UUID prefixes."""
from document_processing.staging import cdr_staging_table_name, original_cdr_basename


def test_original_basename_strips_uuid_prefixes():
    assert original_cdr_basename('LEA25614588_VVM_8247628384_6862.csv') == (
        'LEA25614588_VVM_8247628384_6862.csv'
    )
    assert original_cdr_basename(
        '38a856df57744681aeffefde1f7e73b5_LEA25614588_VVM_8247628384_6862.csv'
    ) == 'LEA25614588_VVM_8247628384_6862.csv'
    assert original_cdr_basename(
        '809244f1c7c54123abf8fa66c09e7d10_38a856df57744681aeffefde1f7e73b5_'
        'LEA25614588_VVM_8247628384_6862.csv'
    ) == 'LEA25614588_VVM_8247628384_6862.csv'


def test_same_client_name_same_staging_table():
    original = 'LEA25614588_VVM_8247628384_6862.csv'
    prefixed = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa_' + original
    double = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb_' + prefixed
    assert cdr_staging_table_name(original) == cdr_staging_table_name(prefixed)
    assert cdr_staging_table_name(original) == cdr_staging_table_name(double)
    assert cdr_staging_table_name(original) == 'stg_cdr_lea25614588_vvm_8247628384_6862_c89589bc'
