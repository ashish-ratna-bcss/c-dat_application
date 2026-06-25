from __future__ import annotations
from datetime import datetime
from typing import Optional
from .base import BaseCdrParser, clean, is_empty, parse_datetime, to_int, to_phone

class AirtelParser(BaseCdrParser):
    operator = 'airtel'

    def header_marker(self) -> str:
        return 'Target No'

    def is_skippable_data_row(self, row, header):
        d = self.row_dict(row, header)
        return is_empty(d.get('Date', '')) and is_empty(d.get('Time', ''))

    def map_row(self, row, header, source_row_number):
        d = self.row_dict(row, header)
        phone = to_phone(d.get('Target No')) or self.target_phone
        other = to_phone(d.get('B Party No')) or clean(d.get('B Party No')) or None
        starttime = parse_datetime(d.get('Date', ''), d.get('Time', ''))
        duration = to_int(d.get('Dur(s)'))
        call_type = clean(d.get('Call Type'))
        incoming = 1 if call_type.upper() == 'IN' else 0
        first_cell = clean(d.get('First CGI'))
        last_cell = clean(d.get('Last CGI'))
        if is_empty(first_cell):
            first_cell = None
        if is_empty(last_cell):
            last_cell = None
        imei = to_int(d.get('IMEI'))
        imsi = to_int(d.get('IMSI'), default=0) or None
        return self.base_record(phone=phone, other=other, starttime=starttime, duration=duration, incoming=incoming, imeinumber=imei, imsinumber=imsi, celltowerid=first_cell, first_cellid=first_cell, last_cellid=last_cell, roaming_nw=clean(d.get('Roam Nw')) or None, call_type=call_type, calling_no=phone, called_no=other, otherinfo=clean(d.get('Service Type')) or None, source_row_number=source_row_number, raw=d)
