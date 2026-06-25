from __future__ import annotations
from typing import Optional
from .base import BaseCdrParser, clean, is_empty, parse_datetime, to_int, to_phone

class BsnlParser(BaseCdrParser):
    operator = 'bsnl'

    def header_marker(self) -> str:
        return 'SL_NO'

    def map_row(self, row, header, source_row_number):
        d = self.row_dict(row, header)
        phone = to_phone(d.get('Mobile_No')) or self.target_phone
        other_raw = clean(d.get('Other_Party_No'))
        other = to_phone(other_raw) or (other_raw if other_raw and (not is_empty(other_raw)) else None)
        starttime = parse_datetime(d.get('Call_Date', ''), d.get('Call_Initiation_Time(CIT)', ''))
        duration = to_int(d.get('Call_Duration'))
        call_type = clean(d.get('Call_Type')).upper()
        incoming = 1 if call_type == 'IN' else 0
        first_cell = clean(d.get('First_Cell_id')) or None
        last_cell = clean(d.get('Last_Cell_ID')) or None
        imei = to_int(d.get('IMEI'))
        imsi = to_int(d.get('IMSI'), default=0) or None
        return self.base_record(phone=phone, other=other, starttime=starttime, duration=duration, incoming=incoming, imeinumber=imei, imsinumber=imsi, celltowerid=first_cell, first_cellid=first_cell, last_cellid=last_cell, roaming_nw=clean(d.get('Circle_NW')) or None, call_type=call_type, calling_no=phone if incoming == 0 else other, called_no=other if incoming == 0 else phone, otherinfo=clean(d.get('Service_Type')) or None, source_row_number=source_row_number, raw=d)
