from __future__ import annotations
from decimal import Decimal
from .base import BaseCdrParser, clean, is_empty, is_scientific_notation, parse_datetime, to_int, to_phone

class ViParser(BaseCdrParser):
    operator = 'vi'

    def header_marker(self) -> str:
        return 'Target /A PARTY NUMBER'

    def is_skippable_data_row(self, row, header):
        d = self.row_dict(row, header)
        return is_empty(d.get('Call date', '') or d.get('Date', '')) and is_empty(
            d.get('Call Initiation Time', '') or d.get('Time', '')
        )

    def map_row(self, row, header, source_row_number):
        d = self.row_dict(row, header)
        phone = to_phone(
            d.get('Target /A PARTY NUMBER')
            or d.get('TargetNo')
            or d.get('Target No')
            or d.get('Target/A PARTY NUMBER')
        ) or self.target_phone
        other_raw = clean(
            d.get('B PARTY NUMBER')
            or d.get('BPartyNo')
            or d.get('B Party No')
            or d.get('BPARTY NUMBER')
        )
        other = to_phone(other_raw) or (other_raw if other_raw and (not is_empty(other_raw)) else None)
        starttime = parse_datetime(
            d.get('Call date') or d.get('Date') or d.get('Call_Date') or '',
            d.get('Call Initiation Time') or d.get('Time') or d.get('Call_Time') or '',
        )
        duration = to_int(d.get('Call Duration') or d.get('Dur') or d.get('Duration'))
        call_type_raw = clean(d.get('CALL_TYPE') or d.get('CallType') or d.get('Call Type'))
        service_type = clean(d.get('Service Type') or d.get('ServiceType') or d.get('Service_Type'))
        # Legacy: Call_Type = CallType + '_' + ServiceType; direction from OUTGOING%.
        call_type = f'{call_type_raw}_{service_type}' if service_type else call_type_raw
        call_type = call_type.replace(' ', '')
        incoming = 0 if call_type.upper().startswith('OUTGOING') else 1
        first_cell = clean(
            d.get('First Cell Global Id')
            or d.get('FirstCGI')
            or d.get('First CGI')
            or d.get('First_Cellid')
        ) or None
        last_cell = clean(
            d.get('Last Cell Global Id')
            or d.get('LastCGI')
            or d.get('Last CGI')
            or d.get('Last_Cellid')
        ) or None
        if first_cell and is_scientific_notation(first_cell):
            first_cell = str(int(Decimal(first_cell)))
        if last_cell and is_scientific_notation(last_cell):
            last_cell = str(int(Decimal(last_cell)))
        imei = to_int(d.get('IMEI'))
        imsi = to_int(d.get('IMSI'), default=0) or None
        roaming = clean(
            d.get('Roaming Network/Circle')
            or d.get('RoamNw')
            or d.get('Roam Nw')
            or d.get('Roaming_NW')
        ) or None
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
            calling_no=phone,
            called_no=other,
            otherinfo=service_type or None,
            source_row_number=source_row_number,
            raw=d,
        )
