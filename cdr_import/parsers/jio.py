from __future__ import annotations
import re
from typing import Optional
from .base import BaseCdrParser, clean, parse_datetime, to_int, to_phone

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
        calling_raw = clean(d.get('Calling Party Telephone Number'))
        called_raw = clean(d.get('Called Party Telephone Number'))
        # Keep alphanumeric sender IDs (e.g. A2P SMS senders like JA-JIONEW)
        # when the value is not a phone number.
        calling = to_phone(calling_raw) or (calling_raw or None)
        called = to_phone(called_raw) or (called_raw or None)
        subscriber = to_phone(self._subscriber) or self.target_phone
        call_type = clean(d.get('Call Type')).lower()
        # Direction: handle *_IN/*_OUT and SMSIN/SMSOUT suffixes.
        if call_type.endswith('out'):
            incoming = 0
        elif call_type.endswith('in'):
            incoming = 1
        else:
            incoming = 0
        if subscriber and calling and (calling.endswith(subscriber) or calling == subscriber):
            phone, other = (calling, called)
        elif subscriber and called and (called.endswith(subscriber) or called == subscriber):
            phone, other = (called, calling)
        elif incoming:
            # Subscriber received the call/SMS -> they are the called party.
            phone, other = (subscriber or called), calling
        else:
            # Outgoing -> subscriber is the calling party.
            phone, other = (subscriber or calling), called
        starttime = parse_datetime(d.get('Call Date', ''), d.get('Call Time', ''))
        duration = to_int(d.get('Call Duration'))
        first_cell = clean(d.get('First Cell ID')).strip("'") or None
        last_cell = clean(d.get('Last Cell ID')).strip("'") or None
        imei = to_int(d.get('IMEI'))
        imsi = to_int(d.get('IMSI'), default=0) or None
        return self.base_record(phone=phone, other=other, starttime=starttime, duration=duration, incoming=incoming, imeinumber=imei, imsinumber=imsi, celltowerid=first_cell, first_cellid=first_cell, last_cellid=last_cell, roaming_nw=clean(d.get('Roaming Circle Name')) or None, call_type=call_type, calling_no=calling, called_no=called, source_row_number=source_row_number, raw=d)
