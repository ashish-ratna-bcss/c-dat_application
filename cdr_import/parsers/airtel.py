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
        phone = to_phone(d.get('Target No') or d.get('TargetNo')) or self.target_phone
        other = to_phone(d.get('B Party No') or d.get('BPartyNo')) or clean(d.get('B Party No') or d.get('BPartyNo')) or None
        starttime = parse_datetime(d.get('Date', ''), d.get('Time', ''))
        duration = to_int(d.get('Dur(s)') or d.get('Dur') or d.get('Duration'))
        call_type = clean(d.get('Call Type') or d.get('CallType'))
        # Direction is finalized by Airtel normalizer (legacy OUT call-type list).
        incoming = 1 if call_type.upper() == 'IN' else 0
        first_cell = clean(d.get('First CGI') or d.get('FirstCGI') or d.get('First BTS') or d.get('FirstBTS'))
        last_cell = clean(d.get('Last CGI') or d.get('LastCGI') or d.get('Last BTS') or d.get('LastBTS'))
        if is_empty(first_cell):
            first_cell = None
        if is_empty(last_cell):
            last_cell = None
        imei = to_int(d.get('IMEI'))
        imsi = to_int(d.get('IMSI'), default=0) or None
        roam = clean(d.get('Roam Nw') or d.get('RoamNw'))
        lrn = clean(
            d.get('LRNTSPLSA')
            or d.get('LRN TS PLSA')
            or d.get('LRN/TSP/LSA')
            or d.get('LRNTS PLSA')
        )
        # Legacy: case when RoamNw like '%AIR%' then RoamNw else LRNTSPLSA end
        if roam and 'AIR' in roam.upper():
            roaming_nw = roam or None
        else:
            roaming_nw = lrn or roam or None
        return self.base_record(
            phone=phone,
            other=other,
            starttime=starttime,
            duration=duration,
            incoming=incoming,
            imeinumber=imei,
            imsinumber=imsi,
            celltowerid=first_cell,
            first_cellid=first_cell,
            last_cellid=last_cell,
            roaming_nw=roaming_nw,
            call_type=call_type,
            calling_no=phone,
            called_no=other,
            otherinfo=clean(d.get('Service Type') or d.get('ServiceType')) or None,
            source_row_number=source_row_number,
            raw=d,
        )
