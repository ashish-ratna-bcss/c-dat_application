from __future__ import annotations
from typing import Optional
from .base import BaseCdrParser, clean, is_empty, parse_datetime, to_int, to_phone

class BsnlParser(BaseCdrParser):
    operator = 'bsnl'

    def header_marker(self) -> str:
        return 'SL_NO'

    def map_row(self, row, header, source_row_number):
        d = self.row_dict(row, header)
        phone = to_phone(d.get('Mobile_No') or d.get('mobile_no')) or self.target_phone
        other_raw = clean(d.get('Other_Party_No') or d.get('other_party_no'))
        other = to_phone(other_raw) or (other_raw if other_raw and (not is_empty(other_raw)) else None)
        starttime = parse_datetime(
            d.get('Call_Date') or d.get('call_date') or '',
            d.get('Call_Time')
            or d.get('call_time')
            or d.get('Call_Initiation_Time(CIT)')
            or d.get('Call_Initiation_Time')
            or '',
        )
        duration = to_int(d.get('Call_Duration') or d.get('call_duration'))
        call_type = clean(d.get('Call_Type') or d.get('call_type'))
        service_type = clean(d.get('Service_Type') or d.get('service_type') or d.get('Service Type'))
        # Direction is finalized by BSNL normalizer (legacy TYPE = call_type+'_'+service_type).
        incoming = 1 if call_type.upper() == 'IN' else 0
        first_cell = clean(d.get('First_Cell_id') or d.get('first_cell_id') or d.get('First_Cell_ID')) or None
        last_cell = clean(d.get('Last_Cell_ID') or d.get('last_cell_id') or d.get('Last_Cell_id')) or None
        if first_cell and is_empty(first_cell):
            first_cell = None
        if last_cell and is_empty(last_cell):
            last_cell = None
        imei = to_int(d.get('IMEI') or d.get('imei'))
        imsi = to_int(d.get('IMSI') or d.get('imsi'), default=0) or None
        roaming = clean(d.get('Circle_NW') or d.get('circle_nw') or d.get('Circle_Nw')) or None
        # Keep service type on otherinfo temporarily for duration SMS zeroing;
        # BSNL normalizer replaces otherinfo with state from phonearea.
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
            roaming_nw=roaming,
            call_type=call_type,
            # Legacy BSNL_STORED: clg=mobile_no, cld=other_party_no (not direction-swapped).
            calling_no=phone,
            called_no=other,
            otherinfo=service_type or None,
            source_row_number=source_row_number,
            raw=d,
        )
