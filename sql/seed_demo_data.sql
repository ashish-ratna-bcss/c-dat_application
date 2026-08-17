-- Demo seed for existing empty tables. Linked to CDR phone 6202867013.
-- Safe to re-run: deletes previous seed keys, then inserts.

BEGIN;

-- Extra columns pages need on stub tables
ALTER TABLE public.ir_particulars ADD COLUMN IF NOT EXISTS sex varchar(20);
ALTER TABLE public.jrms_total_2012_to_2017 ADD COLUMN IF NOT EXISTS unique_key varchar(50);
ALTER TABLE public.jrms_total_2012_to_2017 ADD COLUMN IF NOT EXISTS fathersname varchar(200);
ALTER TABLE public.offence_details ADD COLUMN IF NOT EXISTS irkey varchar(50);
ALTER TABLE public.offence_details ADD COLUMN IF NOT EXISTS crime_head varchar(200);
ALTER TABLE public.offence_details ADD COLUMN IF NOT EXISTS mo varchar(200);
ALTER TABLE public.offence_details ADD COLUMN IF NOT EXISTS date_of_arrest timestamp;

ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS name varchar(200);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS father_name varchar(200);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS age varchar(20);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS district varchar(100);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS state varchar(100);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS pd_act_ps varchar(100);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS date_of_arrest date;
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS crime_head varchar(200);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS minor_head varchar(200);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS modusoperendi varchar(200);
ALTER TABLE public.pdact_main_table ADD COLUMN IF NOT EXISTS crime_head_search varchar(200);

ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS irkey varchar(50);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS pdact_key varchar(50);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS name varchar(200);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS age varchar(20);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS father_name varchar(200);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS phone varchar(25);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS present_address text;
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS lat_p varchar(30);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS long_p varchar(30);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS permanent_address text;
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS lat varchar(30);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS long varchar(30);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS id_proof_type varchar(50);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS id_no varchar(50);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS communal_noncommunal varchar(50);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS latest_bind_over_date date;
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS present_activity varchar(200);
ALTER TABLE public.rowdy_sheeter_data1 ADD COLUMN IF NOT EXISTS date_of_opening_rwd date;

-- Clear previous seed
DELETE FROM public.cdatsuspect WHERE phone IN ('6202867013','7070913066','9065391401');
DELETE FROM public.cdatphonearea WHERE phoneprefix IN ('620','707','906','629','875');
DELETE FROM public.ir_particulars WHERE irkey IN ('900001','113769');
DELETE FROM public.offence_details WHERE irkey = '900001' OR crkey = 900001;
DELETE FROM public.pdact_main_table WHERE pdact_key = '800001';
DELETE FROM public.jrms_total_2012_to_2017 WHERE prisonerno = 'P-90001';
DELETE FROM public.rowdy_sheeter_data1 WHERE irkey = '900001' OR police_station = 'Nampally';
DELETE FROM public.image_table WHERE irkey IN ('900001','113769');
DELETE FROM public.suspect_image_table WHERE irkey = '900001';
DELETE FROM public.cdat_provider_master WHERE provider_key IN (1,2);
DELETE FROM public.cdat_state_master WHERE state_key IN (1,2);
DELETE FROM public.cdat_civilsupply WHERE phone = '6202867013';
DELETE FROM public.cdat_gas_details WHERE phone = '6202867013';
DELETE FROM dist.cdataddress WHERE phone IN ('6202867013','7070913066');
DELETE FROM dist.address_other_state WHERE phone = '9065391401';
DELETE FROM dist.cellids WHERE celltowerid IN (
  '1647-22268','1698-30350','1698-30355','301-11636','301-7233'
);
DELETE FROM dist.rta_data WHERE vehicle_no = 'TS09AB1234';

INSERT INTO public.cdat_provider_master (provider_key, provider, provider_name) VALUES
  (1, 'AIRTEL', 'BHARTI AIRTEL'),
  (2, 'JIO', 'RELIANCE JIO');

INSERT INTO public.cdat_state_master (state_key, state, capital, description) VALUES
  (1, 'TELANGANA', 'HYDERABAD', 'SEED'),
  (2, 'KARNATAKA', 'BENGALURU', 'SEED');

INSERT INTO public.cdatsuspect (phone, nickname, mo, inc_officer, role, category)
VALUES
  ('6202867013', 'RAJESH', 'HOUSE BREAKING', 'SI RAMESH', 'ACCUSED', 'THEFT'),
  ('7070913066', 'SUNIL', 'HOUSE BREAKING', 'SI RAMESH', 'ASSOCIATE', 'THEFT'),
  ('9065391401', 'VIJAY', 'HOUSE BREAKING', 'SI RAMESH', 'ASSOCIATE', 'THEFT');

INSERT INTO public.cdatphonearea
  (phoneprefix, areadescription, state, numberlength, pplen, ph_type, asondate, state_key, state_code, provider_name, provider_key, mobile_network, state1)
VALUES
  ('620', 'Patna, Bihar', 'BIHAR', 10, 3, 'MOBILE', now(), 1, 'BR', 'AIRTEL', 1, 'AIRTEL', 'BIHAR'),
  ('707', 'Patna, Bihar', 'BIHAR', 10, 3, 'MOBILE', now(), 1, 'BR', 'AIRTEL', 1, 'AIRTEL', 'BIHAR'),
  ('629', 'Patna, Bihar', 'BIHAR', 10, 3, 'MOBILE', now(), 1, 'BR', 'JIO', 2, 'JIO', 'BIHAR'),
  ('875', 'Patna, Bihar', 'BIHAR', 10, 3, 'MOBILE', now(), 1, 'BR', 'JIO', 2, 'JIO', 'BIHAR'),
  ('906', 'Bengaluru, Karnataka', 'KARNATAKA', 10, 3, 'MOBILE', now(), 2, 'KA', 'AIRTEL', 1, 'AIRTEL', 'KARNATAKA');

INSERT INTO dist.cdataddress (
  cdat_sdr_key, phone, fullname, fathername, fulladdress, permanentaddress, state,
  gender, nationality, operator, circle, doa, category_type, eff_from_date, eff_to_date, aadhar_no
) VALUES
  (1, '6202867013', 'RAJESH KUMAR', 'RAMESH KUMAR',
   '12, Nampally, Hyderabad', '12, Nampally, Hyderabad', 'TELANGANA',
   'MALE', 'INDIAN', 'AIRTEL', 'AP', DATE '2020-01-15', 'SUBSCRIBER', now(), NULL, '123412341234'),
  (2, '7070913066', 'SUNIL KUMAR', 'MAHESH KUMAR',
   '45, Begumpet, Hyderabad', '45, Begumpet, Hyderabad', 'TELANGANA',
   'MALE', 'INDIAN', 'AIRTEL', 'AP', DATE '2021-03-10', 'SUBSCRIBER', now(), NULL, '234523452345');

INSERT INTO dist.address_other_state (
  oth_sdr_key, phone, fullname, fathername, fulladdress, permanentaddress, state,
  gender, nationality, doa, category_type, eff_from_date, eff_to_date
) VALUES (
  1, '9065391401', 'VIJAY SINGH', 'ANIL SINGH',
  '88 MG Road, Bengaluru', '88 MG Road, Bengaluru', 'KARNATAKA',
  'MALE', 'INDIAN', now(), 'SUBSCRIBER', now(), NULL
);

INSERT INTO dist.cellids (
  tower_key, celltowerid, bts_id, areadescription, siteaddress, lat, long, azimuth,
  operator, state, otype, provider_key, state_key, lastupdate
) VALUES
  (101, '1647-22268', 'BTS-1647', 'Nampally Circle', 'Nampally, Hyderabad', '17.3850', '78.4867', '90', 'AIRTEL_TOWER', 'TELANGANA', '4G', 1, 1, now()),
  (102, '1698-30350', 'BTS-1698', 'Abids Market', 'Abids, Hyderabad', '17.3920', '78.4770', '120', 'AIRTEL_TOWER', 'TELANGANA', '4G', 1, 1, now()),
  (103, '1698-30355', 'BTS-1698B', 'Koti', 'Koti, Hyderabad', '17.3885', '78.4810', '180', 'AIRTEL_TOWER', 'TELANGANA', '4G', 1, 1, now()),
  (104, '301-11636', 'BTS-301', 'Begumpet', 'Begumpet, Hyderabad', '17.4440', '78.4700', '45', 'JIO_TOWER', 'TELANGANA', '4G', 2, 1, now()),
  (105, '301-7233', 'BTS-301B', 'Punjagutta', 'Punjagutta, Hyderabad', '17.4250', '78.4480', '270', 'JIO_TOWER', 'TELANGANA', '4G', 2, 1, now());

INSERT INTO dist.rta_data (
  vehicle_no, owner_name, father_name, address, city, contact_no,
  maker_class, colour, vehicle_class, engine_no, chassis_no, issue_date, seq
) VALUES (
  'TS09AB1234', 'RAJESH KUMAR', 'RAMESH KUMAR',
  '12 Nampally', 'Hyderabad', '6202867013',
  'HONDA ACTIVA', 'BLACK', 'MCWOG', 'ENG90001', 'CHS90001', '15-Jan-2022', 1
);

INSERT INTO public.ir_particulars (
  irkey, name, alias_name, father_name, age, date_of_birth, nationality, occupation,
  present_address, crime_head, mo, crime_no, year, sec_of_law, police_station,
  date_of_arrest, aadhar_no, mobile, sex, category
) VALUES (
  '900001', 'RAJESH KUMAR', 'RAJU', 'RAMESH KUMAR', '32', '1994-05-12', 'INDIAN', 'LABOUR',
  '12, Nampally, Hyderabad', 'THEFT', 'HOUSE BREAKING', '123/2026', '2026', '379 IPC', 'Nampally',
  TIMESTAMP '2026-02-10 10:00:00', '123412341234', '6202867013', 'MALE', 'HABITUAL'
);

INSERT INTO public.offence_details (
  police_station, crime_no, year, place_description, crkey, irkey, crime_head, mo, date_of_arrest
) VALUES (
  'Nampally', '123/2026', '2026', 'PLACE_OF_OFFENCE', 900001, '900001', 'THEFT', 'HOUSE BREAKING',
  TIMESTAMP '2026-02-10 10:00:00'
);

INSERT INTO public.pdact_main_table (
  irkey, pdact_key, name, father_name, age, district, state, pd_act_ps, date_of_arrest,
  crime_head, minor_head, modusoperendi, crime_head_search
) VALUES (
  '900001', '800001', 'RAJESH KUMAR', 'RAMESH KUMAR', '32', 'HYDERABAD', 'TELANGANA', 'Nampally',
  DATE '2026-02-20', 'THEFT', 'HOUSE THEFT', 'HOUSE BREAKING', 'THEFT HOUSE BREAKING'
);

-- Name includes /AADHAR so JRMS IDPROOF extraction matches IR
INSERT INTO public.jrms_total_2012_to_2017 (
  prisonerno, unique_key, psarrested, name, fathersname, crimenos, headofcrime, mobileno,
  addr_duringrelease, gender, jailname, admission_to_jail, releasedt, photo
) VALUES (
  'P-90001', 'UK-90001', 'Nampally', 'RAJESH KUMAR/123412341234', 'RAMESH KUMAR',
  '123/2026', 'THEFT', '6202867013', '12, Nampally, Hyderabad', 'MALE', 'CHERLAPALLI',
  DATE '2026-02-15', DATE '2026-03-15', NULL
);

INSERT INTO public.rowdy_sheeter_data1 (
  police_station, irkey, pdact_key, name, age, father_name, phone, present_address,
  lat_p, long_p, permanent_address, lat, long, id_proof_type, id_no,
  communal_noncommunal, latest_bind_over_date, present_activity, date_of_opening_rwd
) VALUES (
  'Nampally', '900001', '800001', 'RAJESH KUMAR', '32', 'RAMESH KUMAR', '6202867013',
  '12, Nampally, Hyderabad', '17.3850', '78.4867', '12, Nampally, Hyderabad', '17.3850', '78.4867',
  'AADHAR', '123412341234', 'NONCOMMUNAL', DATE '2026-01-10', 'ACTIVE', DATE '2025-12-01'
);

INSERT INTO public.image_table (irkey, image) VALUES
  ('900001', decode('89504e470d0a1a0a0000000d49484452000000010000000108060000001f15c4890000000a49444154789c63000100000500010d0a2db40000000049454e44ae426082','hex')),
  ('113769', decode('89504e470d0a1a0a0000000d49484452000000010000000108060000001f15c4890000000a49444154789c63000100000500010d0a2db40000000049454e44ae426082','hex'));

INSERT INTO public.suspect_image_table (irkey, mobile, image)
VALUES ('900001', '6202867013', NULL);

INSERT INTO public.cdat_civilsupply (phone) VALUES ('6202867013');
INSERT INTO public.cdat_gas_details (phone) VALUES ('6202867013');

-- Reverse CDR so 7070913066 is also a PHONE (comparison + common contacts)
DELETE FROM public.cdatpcsuspect WHERE phone = '7070913066';

INSERT INTO public.cdatpcsuspect (
  ucid, phone, other, starttime, duration, incoming, imeinumber, imsinumber,
  celltowerid, otherinfo, tower_key, provider_key, state_key, first_cellid,
  last_cellid, roaming_nw, call_type, calling_no, called_no, asondate
)
SELECT
  (-1000 - row_number() OVER (ORDER BY ucid))::bigint,
  other,
  phone,
  starttime,
  duration,
  CASE WHEN incoming = 1 THEN 0 ELSE 1 END,
  353342621898171,
  imsinumber,
  celltowerid,
  otherinfo,
  tower_key,
  provider_key,
  state_key,
  first_cellid,
  last_cellid,
  roaming_nw,
  call_type,
  called_no,
  calling_no,
  asondate
FROM public.cdatpcsuspect
WHERE phone = '6202867013' AND other = '7070913066';

INSERT INTO public.cdatpcsuspect (
  ucid, phone, other, starttime, duration, incoming, imeinumber, imsinumber,
  celltowerid, otherinfo, tower_key, provider_key, state_key, first_cellid,
  last_cellid, roaming_nw, call_type, calling_no, called_no, asondate
)
SELECT
  (-2000 - row_number() OVER (ORDER BY ucid))::bigint,
  '7070913066',
  other,
  starttime,
  duration,
  incoming,
  353342621898171,
  imsinumber,
  celltowerid,
  otherinfo,
  tower_key,
  provider_key,
  state_key,
  first_cellid,
  last_cellid,
  roaming_nw,
  call_type,
  calling_no,
  called_no,
  asondate
FROM public.cdatpcsuspect
WHERE phone = '6202867013'
  AND other IN ('9065391401', '6295488443', '8757345558');

COMMIT;
