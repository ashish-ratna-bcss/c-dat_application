#!/bin/bash
export PGPASSWORD='BcSs@!nd!@76'

echo "=== Copying rta_data -> CDATDUPL_DB.cdat_rta ==="
psql -U postgres -h localhost -d CDATDUPL_DB <<'SQL'
INSERT INTO cdat_rta (
    regn_no, fullname, dob, fathername, phone, fulladdress, city, pin_code,
    eng_no, chas_no, mkr_name, mkr_clas, colour, seat_capacity, tr_number,
    veh_class, bdy_type, rvd_cc, fuel, hp, fc_validity, permit_validity,
    insurance_validity, tax_validity, applicant_name, email_id, permit_no,
    fc_no, updated_dt, created_dt, iss_dt, valid_upto, veh_type, off_cd
)
SELECT
    vehicle_no, owner_name, dob, father_name, contact_no, address, city, pin_code,
    engine_no, chassis_no, maker_name, maker_class, colour, seat_capacity, tr_number,
    vehicle_class, body_type, engine_cc, fuel, hp, fc_validity, permit_validity,
    insurance_validity, tax_validity, applicant_name, email, permit_no,
    fc_no, record_updated_date, record_created_date, issue_date, validity_date, vehicle_type, rta_office_code
FROM dblink(
    'dbname=distributed_db host=localhost user=postgres password=BcSs@!nd!@76',
    'SELECT vehicle_no, owner_name, dob, father_name, contact_no, address, city, pin_code,
     engine_no, chassis_no, maker_name, maker_class, colour, seat_capacity, tr_number,
     vehicle_class, body_type, engine_cc, fuel, hp, fc_validity, permit_validity,
     insurance_validity, tax_validity, applicant_name, email, permit_no,
     fc_no, record_updated_date, record_created_date, issue_date, validity_date, vehicle_type, rta_office_code
     FROM rta_data'
) AS t(
    vehicle_no text, owner_name text, dob text, father_name text, contact_no text,
    address text, city text, pin_code text, engine_no text, chassis_no text,
    maker_name text, maker_class text, colour text, seat_capacity text, tr_number text,
    vehicle_class text, body_type text, engine_cc text, fuel text, hp text,
    fc_validity text, permit_validity text, insurance_validity text, tax_validity text,
    applicant_name text, email text, permit_no text, fc_no text,
    record_updated_date text, record_created_date text, issue_date text,
    validity_date text, vehicle_type text, rta_office_code text
);
SQL

echo "=== Copying dl_data -> CDATDUPL_DB.cdat_licence ==="
psql -U postgres -h localhost -d CDATDUPL_DB <<'SQL'
INSERT INTO cdat_licence (
    licence_no, fullname, father_name, dob, gender, fulladdress, phone, issue_date
)
SELECT
    dl_no, first_name, parent_name, dob, gender, address, contact_no, issue_date
FROM dblink(
    'dbname=distributed_db host=localhost user=postgres password=BcSs@!nd!@76',
    'SELECT dl_no, first_name, parent_name, dob, gender, address, contact_no, issue_date FROM dl_data'
) AS t(
    dl_no text, first_name text, parent_name text, dob text,
    gender text, address text, contact_no text, issue_date text
);
SQL

echo "=== Final row counts ==="
psql -U postgres -h localhost -d CDATDUPL_DB -c "
SELECT 'cdat_rta' AS table_name, COUNT(*) AS rows FROM cdat_rta
UNION ALL
SELECT 'cdat_licence', COUNT(*) FROM cdat_licence;
"
echo "ALL DONE"
