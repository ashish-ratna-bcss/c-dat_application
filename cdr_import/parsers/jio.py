from __future__ import annotations
import re
from .base import BaseCdrParser, clean, parse_datetime, to_int, to_phone


def split_jio_datetime_parts(call_date: str, call_time: str) -> tuple[str, str]:
    """Port of proc_rel_jio Call_Date / Call_Time len>10 substring splits.

    Legacy:
      date: when len>10 → substring(date, 1, charindex(' ', date))  (through space)
      time: when len>10 → substring(time, charindex(' ', time), 20) (from space, len 20)
    Trailing/leading spaces are stripped before parse_datetime.
    """
    date_s = clean(call_date)
    time_s = clean(call_time)
    if len(date_s) > 10:
        idx = date_s.find(' ')
        if idx > 0:
            date_s = date_s[:idx].strip()
    if len(time_s) > 10:
        idx = time_s.find(' ')
        if idx >= 0:
            time_s = time_s[idx : idx + 20].strip()
    return date_s, time_s


class JioParser(BaseCdrParser):
    operator = 'jio'

    def __init__(self, file_path, target_phone, provider_key):
        super().__init__(file_path, target_phone, provider_key)
        self._subscriber = target_phone

    def header_marker(self) -> str:
        return 'Calling Party Telephone Number'

    def parse(self):
        lines = self.file_path.read_text(encoding='utf-8', errors='replace').splitlines()
        for line in lines[:30]:
            m = re.search("Input Value.*?,'(\\d+)'", line, re.IGNORECASE)
            if m:
                self._subscriber = m.group(1)
                break
            m = re.search("MSISDN/IMSI:,'(\\d+)'", line, re.IGNORECASE)
            if m and len(m.group(1)) <= 15:
                pass
        return super().parse()

    def map_row(self, row, header, source_row_number):
        d = self.row_dict(row, header)
        calling_raw = clean(
            d.get('Calling Party Telephone Number')
            or d.get('CallingNo')
            or d.get('Calling No')
        )
        called_raw = clean(
            d.get('Called Party Telephone Number')
            or d.get('CalledNo')
            or d.get('Called No')
        )
        # Keep alphanumeric sender IDs (e.g. A2P SMS senders like JA-JIONEW)
        # when the value is not a phone number.
        calling = to_phone(calling_raw) or (calling_raw or None)
        called = to_phone(called_raw) or (called_raw or None)
        call_type = clean(d.get('Call Type') or d.get('Call_Type') or d.get('CALL_TYPE'))
        # Direction finalized by Jio normalizer; seed from legacy %out% rule.
        incoming = 0 if 'out' in call_type.lower() else 1
        # Legacy proc_rel_jio: OUT → phone=CallingNo, other=CalledNo; else reverse.
        if incoming == 0:
            phone, other = calling, called
        else:
            phone, other = called, calling
        call_date, call_time = split_jio_datetime_parts(
            d.get('Call Date') or d.get('Call_Date') or '',
            d.get('Call Time') or d.get('Call_Time') or '',
        )
        starttime = parse_datetime(call_date, call_time)
        duration = to_int(d.get('Call Duration') or d.get('Call_Dur') or d.get('Call Dur'))
        first_cell = clean(d.get('First Cell ID') or d.get('FIRST_CELLID') or d.get('First_Cellid'))
        first_cell = first_cell.strip("'") or None if first_cell else None
        last_cell = clean(d.get('Last Cell ID') or d.get('LAST_CELLID') or d.get('Last_Cellid'))
        last_cell = last_cell.strip("'") or None if last_cell else None
        imei = to_int(d.get('IMEI') or d.get('Imeinumber'))
        imsi = to_int(d.get('IMSI') or d.get('Imsinumber'), default=0) or None
        roaming = clean(
            d.get('Roaming Circle Name')
            or d.get('roam_nw')
            or d.get('Roam_NW')
            or d.get('Roam Nw')
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
            calling_no=calling,
            called_no=called,
            otherinfo=None,
            source_row_number=source_row_number,
            raw=d,
        )
