from __future__ import annotations
from decimal import Decimal
from .base import BaseCdrParser, clean, is_empty, is_scientific_notation, parse_datetime, to_int, to_phone

class ViParser(BaseCdrParser):
    operator = 'vi'

    def header_marker(self) -> str:
        return 'Target /A PARTY NUMBER'

    def is_skippable_data_row(self, row, header):
        d = self.row_dict(row, header)
        return is_empty(d.get('Call date', '')) and is_empty(d.get('Call Initiation Time', ''))

    def map_row(self, row, header, source_row_number):
        d = self.row_dict(row, header)
        phone = to_phone(d.get('Target /A PARTY NUMBER')) or self.target_phone
        other_raw = clean(d.get('B PARTY NUMBER'))
        other = to_phone(other_raw) or (other_raw if other_raw and (not is_empty(other_raw)) else None)
        starttime = parse_datetime(d.get('Call date', ''), d.get('Call Initiation Time', ''))
        duration = to_int(d.get('Call Duration'))
        call_type_raw = clean(d.get('CALL_TYPE'))
        incoming = 1 if call_type_raw.lower().startswith('in') else 0
        first_cell = clean(d.get('First Cell Global Id')) or None
        last_cell = clean(d.get('Last Cell Global Id')) or None
        if first_cell and is_scientific_notation(first_cell):
            first_cell = str(int(Decimal(first_cell)))
        if last_cell and is_scientific_notation(last_cell):
            last_cell = str(int(Decimal(last_cell)))
        imei = to_int(d.get('IMEI'))
        imsi = to_int(d.get('IMSI'), default=0) or None
        return self.base_record(phone=phone, other=other, starttime=starttime, duration=duration, incoming=incoming, imeinumber=imei, imsinumber=imsi, celltowerid=first_cell, first_cellid=first_cell, last_cellid=last_cell, roaming_nw=clean(d.get('Roaming Network/Circle')) or None, call_type=call_type_raw, calling_no=phone if incoming == 0 else other, called_no=other if incoming == 0 else phone, otherinfo=clean(d.get('Service Type')) or None, source_row_number=source_row_number, raw=d)
