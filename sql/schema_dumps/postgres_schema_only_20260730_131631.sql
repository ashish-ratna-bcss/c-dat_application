--
-- PostgreSQL database dump
--

\restrict QX5K0pUZpW2dGWmUmkbCo7JoIiajKNBvnca8dFxUCzhdkdTbCLgd0iJKcj7ymbR

-- Dumped from database version 16.14 (Ubuntu 16.14-1.pgdg24.04+1)
-- Dumped by pg_dump version 16.14 (Ubuntu 16.14-1.pgdg24.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: dist; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA dist;


--
-- Name: upload_staging; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA upload_staging;


--
-- Name: postgres_fdw; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgres_fdw WITH SCHEMA public;


--
-- Name: EXTENSION postgres_fdw; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION postgres_fdw IS 'foreign-data wrapper for remote PostgreSQL servers';


--
-- Name: calculatedistance(double precision, double precision, double precision, double precision); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.calculatedistance(lon1 double precision, lat1 double precision, lon2 double precision, lat2 double precision) RETURNS double precision
    LANGUAGE sql IMMUTABLE
    AS $$
    SELECT 6371 * acos(
        LEAST(1.0, GREATEST(-1.0,
            cos(radians(lat1)) * cos(radians(lat2)) * cos(radians(lon2) - radians(lon1))
            + sin(radians(lat1)) * sin(radians(lat2))
        ))
    );
$$;


--
-- Name: getbearing(double precision, double precision, double precision, double precision); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.getbearing(lat1 double precision, lon1 double precision, lat2 double precision, lon2 double precision) RETURNS double precision
    LANGUAGE sql IMMUTABLE
    AS $$
    SELECT degrees(atan2(
        sin(radians(lon2 - lon1)) * cos(radians(lat2)),
        cos(radians(lat1)) * sin(radians(lat2))
          - sin(radians(lat1)) * cos(radians(lat2)) * cos(radians(lon2 - lon1))
    ));
$$;


--
-- Name: distributed_db_srv; Type: SERVER; Schema: -; Owner: -
--

CREATE SERVER distributed_db_srv FOREIGN DATA WRAPPER postgres_fdw OPTIONS (
    dbname 'distributed_db',
    host '127.0.0.1',
    port '5432'
);


--
-- Name: USER MAPPING postgres SERVER distributed_db_srv; Type: USER MAPPING; Schema: -; Owner: -
--

CREATE USER MAPPING FOR postgres SERVER distributed_db_srv OPTIONS (
    password 'BcSs@!nd!@76',
    "user" 'postgres'
);


--
-- Name: address_other_state; Type: FOREIGN TABLE; Schema: dist; Owner: -
--

CREATE FOREIGN TABLE dist.address_other_state (
    oth_sdr_key bigint,
    phone character varying(15),
    caf_no character varying(50),
    aadhar_no character varying(15),
    fullname character varying(255),
    uniquekyccode character varying(50),
    kycdatetime timestamp without time zone,
    kycrespcode character varying(50),
    kycrespdate date,
    uniqackrecno character varying(50),
    dob date,
    fathername character varying(100),
    fulladdress character varying(1000),
    permanentaddress character varying(1000),
    state character varying(35),
    alt_cnt_no character varying(15),
    email_id character varying(100),
    gender character varying(8),
    nationality character varying(50),
    profession_subscriber character varying(100),
    pan_gir_no character varying(50),
    status_subscriber character varying(35),
    conn_type character varying(25),
    form_of_payment character varying(35),
    mode_of_paid character varying(30),
    bank_acno character varying(30),
    bank_name character varying(50),
    bank_addr character varying(50),
    imsi_no character varying(15),
    operator character varying(25),
    circle character varying(25),
    doa timestamp without time zone,
    current_status character varying(50),
    previous_service_provider character varying(60),
    previous_circle character varying(35),
    point_of_sale_code character varying(50),
    point_of_sale_name character varying(200),
    point_of_sale_agent_name character varying(200),
    sale_agent_adhaar_no character varying(15),
    pos_agent_unique_kyc character varying(50),
    pos_auth_datetime timestamp without time zone,
    point_of_saleaddress character varying(300),
    details_add_on_value character varying(200),
    scanned_photo_colour character varying(50),
    name_design_of_active_officer character varying(100),
    poa_name character varying(50),
    poa_no character varying(50),
    poa_address character varying(150),
    poi_name character varying(150),
    poi_no character varying(50),
    poi_address character varying(255),
    category_type character varying(100),
    eff_from_date timestamp without time zone,
    eff_to_date timestamp without time zone,
    seq bigint
)
SERVER distributed_db_srv
OPTIONS (
    schema_name 'public',
    table_name 'address_other_state'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN oth_sdr_key OPTIONS (
    column_name 'oth_sdr_key'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN phone OPTIONS (
    column_name 'phone'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN caf_no OPTIONS (
    column_name 'caf_no'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN aadhar_no OPTIONS (
    column_name 'aadhar_no'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN fullname OPTIONS (
    column_name 'fullname'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN uniquekyccode OPTIONS (
    column_name 'uniquekyccode'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN kycdatetime OPTIONS (
    column_name 'kycdatetime'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN kycrespcode OPTIONS (
    column_name 'kycrespcode'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN kycrespdate OPTIONS (
    column_name 'kycrespdate'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN uniqackrecno OPTIONS (
    column_name 'uniqackrecno'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN dob OPTIONS (
    column_name 'dob'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN fathername OPTIONS (
    column_name 'fathername'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN fulladdress OPTIONS (
    column_name 'fulladdress'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN permanentaddress OPTIONS (
    column_name 'permanentaddress'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN state OPTIONS (
    column_name 'state'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN alt_cnt_no OPTIONS (
    column_name 'alt_cnt_no'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN email_id OPTIONS (
    column_name 'email_id'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN gender OPTIONS (
    column_name 'gender'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN nationality OPTIONS (
    column_name 'nationality'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN profession_subscriber OPTIONS (
    column_name 'profession_subscriber'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN pan_gir_no OPTIONS (
    column_name 'pan_gir_no'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN status_subscriber OPTIONS (
    column_name 'status_subscriber'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN conn_type OPTIONS (
    column_name 'conn_type'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN form_of_payment OPTIONS (
    column_name 'form_of_payment'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN mode_of_paid OPTIONS (
    column_name 'mode_of_paid'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN bank_acno OPTIONS (
    column_name 'bank_acno'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN bank_name OPTIONS (
    column_name 'bank_name'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN bank_addr OPTIONS (
    column_name 'bank_addr'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN imsi_no OPTIONS (
    column_name 'imsi_no'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN operator OPTIONS (
    column_name 'operator'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN circle OPTIONS (
    column_name 'circle'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN doa OPTIONS (
    column_name 'doa'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN current_status OPTIONS (
    column_name 'current_status'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN previous_service_provider OPTIONS (
    column_name 'previous_service_provider'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN previous_circle OPTIONS (
    column_name 'previous_circle'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN point_of_sale_code OPTIONS (
    column_name 'point_of_sale_code'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN point_of_sale_name OPTIONS (
    column_name 'point_of_sale_name'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN point_of_sale_agent_name OPTIONS (
    column_name 'point_of_sale_agent_name'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN sale_agent_adhaar_no OPTIONS (
    column_name 'sale_agent_adhaar_no'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN pos_agent_unique_kyc OPTIONS (
    column_name 'pos_agent_unique_kyc'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN pos_auth_datetime OPTIONS (
    column_name 'pos_auth_datetime'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN point_of_saleaddress OPTIONS (
    column_name 'point_of_saleaddress'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN details_add_on_value OPTIONS (
    column_name 'details_add_on_value'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN scanned_photo_colour OPTIONS (
    column_name 'scanned_photo_colour'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN name_design_of_active_officer OPTIONS (
    column_name 'name_design_of_active_officer'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN poa_name OPTIONS (
    column_name 'poa_name'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN poa_no OPTIONS (
    column_name 'poa_no'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN poa_address OPTIONS (
    column_name 'poa_address'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN poi_name OPTIONS (
    column_name 'poi_name'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN poi_no OPTIONS (
    column_name 'poi_no'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN poi_address OPTIONS (
    column_name 'poi_address'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN category_type OPTIONS (
    column_name 'category_type'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN eff_from_date OPTIONS (
    column_name 'eff_from_date'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN eff_to_date OPTIONS (
    column_name 'eff_to_date'
);
ALTER FOREIGN TABLE dist.address_other_state ALTER COLUMN seq OPTIONS (
    column_name 'seq'
);


--
-- Name: cdataddress; Type: FOREIGN TABLE; Schema: dist; Owner: -
--

CREATE FOREIGN TABLE dist.cdataddress (
    cdat_sdr_key bigint,
    phone character varying(15) NOT NULL,
    caf_no character varying(50),
    aadhar_no character varying(15),
    fullname character varying(255),
    uniquekyccode character varying(33),
    kycdatetime timestamp without time zone,
    kycrespcode character varying(50),
    kycrespdate date,
    uniqackrecno character varying(50),
    dob date,
    fathername character varying(100),
    fulladdress text,
    permanentaddress text,
    state character varying(50),
    alt_cnt_no character varying(15),
    email_id character varying(100),
    gender character varying(11),
    nationality character varying(50),
    profession_subscriber character varying(100),
    pan_gir_no character varying(50),
    status_of_subscriber character varying(35),
    conn_type character varying(15),
    form_of_payment character varying(35),
    mode_of_paid character varying(25),
    bank_acno character varying(25),
    bank_name character varying(25),
    bank_addr character varying(50),
    imsi_no character varying(15),
    operator character varying(25),
    circle character varying(25),
    doa date NOT NULL,
    current_status character varying(50),
    previous_service_provider character varying(60),
    previous_circle character varying(35),
    point_of_sale_code character varying(100),
    point_of_sale_name character varying(200),
    point_of_sale_agent_name character varying(200),
    sale_agent_adhaar_no character varying(15),
    pos_agent_unique_kyc character varying(50),
    pos_auth_datetime timestamp without time zone,
    point_of_saleaddress character varying(300),
    details_add_on_value character varying(200),
    scanned_photo_colour character varying(50),
    name_design_of_active_officer character varying(100),
    poa_name character varying(155),
    poa_no character varying(50),
    poa_address character varying(150),
    poi_name character varying(120),
    poi_no character varying(50),
    poi_address character varying(255),
    category_type character varying(100),
    eff_from_date timestamp without time zone,
    eff_to_date timestamp without time zone
)
SERVER distributed_db_srv
OPTIONS (
    schema_name 'public',
    table_name 'cdataddress'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN cdat_sdr_key OPTIONS (
    column_name 'cdat_sdr_key'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN phone OPTIONS (
    column_name 'phone'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN caf_no OPTIONS (
    column_name 'caf_no'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN aadhar_no OPTIONS (
    column_name 'aadhar_no'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN fullname OPTIONS (
    column_name 'fullname'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN uniquekyccode OPTIONS (
    column_name 'uniquekyccode'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN kycdatetime OPTIONS (
    column_name 'kycdatetime'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN kycrespcode OPTIONS (
    column_name 'kycrespcode'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN kycrespdate OPTIONS (
    column_name 'kycrespdate'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN uniqackrecno OPTIONS (
    column_name 'uniqackrecno'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN dob OPTIONS (
    column_name 'dob'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN fathername OPTIONS (
    column_name 'fathername'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN fulladdress OPTIONS (
    column_name 'fulladdress'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN permanentaddress OPTIONS (
    column_name 'permanentaddress'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN state OPTIONS (
    column_name 'state'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN alt_cnt_no OPTIONS (
    column_name 'alt_cnt_no'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN email_id OPTIONS (
    column_name 'email_id'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN gender OPTIONS (
    column_name 'gender'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN nationality OPTIONS (
    column_name 'nationality'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN profession_subscriber OPTIONS (
    column_name 'profession_subscriber'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN pan_gir_no OPTIONS (
    column_name 'pan_gir_no'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN status_of_subscriber OPTIONS (
    column_name 'status_of_subscriber'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN conn_type OPTIONS (
    column_name 'conn_type'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN form_of_payment OPTIONS (
    column_name 'form_of_payment'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN mode_of_paid OPTIONS (
    column_name 'mode_of_paid'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN bank_acno OPTIONS (
    column_name 'bank_acno'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN bank_name OPTIONS (
    column_name 'bank_name'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN bank_addr OPTIONS (
    column_name 'bank_addr'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN imsi_no OPTIONS (
    column_name 'imsi_no'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN operator OPTIONS (
    column_name 'operator'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN circle OPTIONS (
    column_name 'circle'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN doa OPTIONS (
    column_name 'doa'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN current_status OPTIONS (
    column_name 'current_status'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN previous_service_provider OPTIONS (
    column_name 'previous_service_provider'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN previous_circle OPTIONS (
    column_name 'previous_circle'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN point_of_sale_code OPTIONS (
    column_name 'point_of_sale_code'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN point_of_sale_name OPTIONS (
    column_name 'point_of_sale_name'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN point_of_sale_agent_name OPTIONS (
    column_name 'point_of_sale_agent_name'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN sale_agent_adhaar_no OPTIONS (
    column_name 'sale_agent_adhaar_no'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN pos_agent_unique_kyc OPTIONS (
    column_name 'pos_agent_unique_kyc'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN pos_auth_datetime OPTIONS (
    column_name 'pos_auth_datetime'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN point_of_saleaddress OPTIONS (
    column_name 'point_of_saleaddress'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN details_add_on_value OPTIONS (
    column_name 'details_add_on_value'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN scanned_photo_colour OPTIONS (
    column_name 'scanned_photo_colour'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN name_design_of_active_officer OPTIONS (
    column_name 'name_design_of_active_officer'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN poa_name OPTIONS (
    column_name 'poa_name'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN poa_no OPTIONS (
    column_name 'poa_no'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN poa_address OPTIONS (
    column_name 'poa_address'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN poi_name OPTIONS (
    column_name 'poi_name'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN poi_no OPTIONS (
    column_name 'poi_no'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN poi_address OPTIONS (
    column_name 'poi_address'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN category_type OPTIONS (
    column_name 'category_type'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN eff_from_date OPTIONS (
    column_name 'eff_from_date'
);
ALTER FOREIGN TABLE dist.cdataddress ALTER COLUMN eff_to_date OPTIONS (
    column_name 'eff_to_date'
);


--
-- Name: cellids; Type: FOREIGN TABLE; Schema: dist; Owner: -
--

CREATE FOREIGN TABLE dist.cellids (
    tower_key numeric(18,0) NOT NULL,
    celltowerid character varying(30),
    bts_id character varying(50),
    areadescription character varying(255),
    siteaddress character varying(500),
    lat character varying(20),
    long character varying(50),
    azimuth character varying(20),
    operator character varying(50),
    state character varying(50),
    otype character varying(50),
    opid numeric(2,0),
    state_key integer,
    provider_name character varying(50),
    provider_key integer,
    state_code character varying(2),
    modified_celltowerid character varying(30),
    circle_name character varying(50),
    circle_key integer,
    eff_from_date timestamp without time zone,
    eff_to_date timestamp without time zone,
    lastupdate timestamp without time zone
)
SERVER distributed_db_srv
OPTIONS (
    schema_name 'public',
    table_name 'cellids'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN tower_key OPTIONS (
    column_name 'tower_key'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN celltowerid OPTIONS (
    column_name 'celltowerid'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN bts_id OPTIONS (
    column_name 'bts_id'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN areadescription OPTIONS (
    column_name 'areadescription'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN siteaddress OPTIONS (
    column_name 'siteaddress'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN lat OPTIONS (
    column_name 'lat'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN long OPTIONS (
    column_name 'long'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN azimuth OPTIONS (
    column_name 'azimuth'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN operator OPTIONS (
    column_name 'operator'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN state OPTIONS (
    column_name 'state'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN otype OPTIONS (
    column_name 'otype'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN opid OPTIONS (
    column_name 'opid'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN state_key OPTIONS (
    column_name 'state_key'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN provider_name OPTIONS (
    column_name 'provider_name'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN provider_key OPTIONS (
    column_name 'provider_key'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN state_code OPTIONS (
    column_name 'state_code'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN modified_celltowerid OPTIONS (
    column_name 'modified_celltowerid'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN circle_name OPTIONS (
    column_name 'circle_name'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN circle_key OPTIONS (
    column_name 'circle_key'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN eff_from_date OPTIONS (
    column_name 'eff_from_date'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN eff_to_date OPTIONS (
    column_name 'eff_to_date'
);
ALTER FOREIGN TABLE dist.cellids ALTER COLUMN lastupdate OPTIONS (
    column_name 'lastupdate'
);


--
-- Name: dl_data; Type: FOREIGN TABLE; Schema: dist; Owner: -
--

CREATE FOREIGN TABLE dist.dl_data (
    dl_no character varying,
    first_name character varying,
    parent_name character varying,
    dob character varying,
    gender character varying,
    address character varying,
    city character varying,
    state character varying,
    pin character varying,
    contact_no character varying,
    issue_date character varying,
    issuing_office character varying,
    record_generated_date character varying,
    seq bigint
)
SERVER distributed_db_srv
OPTIONS (
    schema_name 'public',
    table_name 'dl_data'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN dl_no OPTIONS (
    column_name 'dl_no'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN first_name OPTIONS (
    column_name 'first_name'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN parent_name OPTIONS (
    column_name 'parent_name'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN dob OPTIONS (
    column_name 'dob'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN gender OPTIONS (
    column_name 'gender'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN address OPTIONS (
    column_name 'address'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN city OPTIONS (
    column_name 'city'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN state OPTIONS (
    column_name 'state'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN pin OPTIONS (
    column_name 'pin'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN contact_no OPTIONS (
    column_name 'contact_no'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN issue_date OPTIONS (
    column_name 'issue_date'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN issuing_office OPTIONS (
    column_name 'issuing_office'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN record_generated_date OPTIONS (
    column_name 'record_generated_date'
);
ALTER FOREIGN TABLE dist.dl_data ALTER COLUMN seq OPTIONS (
    column_name 'seq'
);


--
-- Name: echallan_data; Type: FOREIGN TABLE; Schema: dist; Owner: -
--

CREATE FOREIGN TABLE dist.echallan_data (
    s_no character varying(50),
    unit_name character varying(100),
    ps_name character varying(100),
    point_name character varying(255),
    vehicle_no character varying(100),
    challan_no character varying(100),
    offence_date character varying(100),
    vehicle_class character varying(100),
    driver_name character varying(100),
    parent_name character varying(100),
    age character varying(50),
    driver_address character varying(255),
    driver_contact_no character varying(100),
    owner_name character varying(100),
    owner_address character varying(255),
    owner_contact_no character varying(100),
    date_of_pay character varying(100),
    payer_contact_no character varying(100),
    image_url character varying(255),
    seq bigint
)
SERVER distributed_db_srv
OPTIONS (
    schema_name 'public',
    table_name 'echallan_data'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN s_no OPTIONS (
    column_name 's_no'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN unit_name OPTIONS (
    column_name 'unit_name'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN ps_name OPTIONS (
    column_name 'ps_name'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN point_name OPTIONS (
    column_name 'point_name'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN vehicle_no OPTIONS (
    column_name 'vehicle_no'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN challan_no OPTIONS (
    column_name 'challan_no'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN offence_date OPTIONS (
    column_name 'offence_date'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN vehicle_class OPTIONS (
    column_name 'vehicle_class'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN driver_name OPTIONS (
    column_name 'driver_name'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN parent_name OPTIONS (
    column_name 'parent_name'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN age OPTIONS (
    column_name 'age'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN driver_address OPTIONS (
    column_name 'driver_address'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN driver_contact_no OPTIONS (
    column_name 'driver_contact_no'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN owner_name OPTIONS (
    column_name 'owner_name'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN owner_address OPTIONS (
    column_name 'owner_address'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN owner_contact_no OPTIONS (
    column_name 'owner_contact_no'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN date_of_pay OPTIONS (
    column_name 'date_of_pay'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN payer_contact_no OPTIONS (
    column_name 'payer_contact_no'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN image_url OPTIONS (
    column_name 'image_url'
);
ALTER FOREIGN TABLE dist.echallan_data ALTER COLUMN seq OPTIONS (
    column_name 'seq'
);


--
-- Name: rta_data; Type: FOREIGN TABLE; Schema: dist; Owner: -
--

CREATE FOREIGN TABLE dist.rta_data (
    vehicle_no character varying(20),
    rta_office_code character varying(255),
    issue_date character varying(255),
    validity_date character varying(255),
    vehicle_class character varying(255),
    engine_no character varying(255),
    chassis_no character varying(255),
    maker_name character varying(100),
    maker_class character varying(100),
    vehicleclass_id character varying(255),
    vehicle_type character varying(255),
    colour character varying(255),
    body_type character varying(255),
    engine_cc character varying(255),
    fuel character varying(255),
    hp character varying(255),
    seat_capacity character varying(255),
    owner_name character varying(255),
    dob character varying(255),
    father_name character varying(255),
    address character varying(255),
    city character varying(255),
    pin_code character varying(255),
    contact_no character varying(255),
    tr_number character varying(255),
    fc_validity character varying(255),
    permit_validity character varying(255),
    insurance_validity character varying(255),
    tax_validity character varying(255),
    applicant_name character varying(255),
    email character varying(255),
    insurance_policy_no character varying(255),
    insurance_from character varying(255),
    permit_no character varying(255),
    fc_no character varying(255),
    record_updated_date character varying(255),
    record_created_date character varying(255),
    seq bigint
)
SERVER distributed_db_srv
OPTIONS (
    schema_name 'public',
    table_name 'rta_data'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN vehicle_no OPTIONS (
    column_name 'vehicle_no'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN rta_office_code OPTIONS (
    column_name 'rta_office_code'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN issue_date OPTIONS (
    column_name 'issue_date'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN validity_date OPTIONS (
    column_name 'validity_date'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN vehicle_class OPTIONS (
    column_name 'vehicle_class'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN engine_no OPTIONS (
    column_name 'engine_no'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN chassis_no OPTIONS (
    column_name 'chassis_no'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN maker_name OPTIONS (
    column_name 'maker_name'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN maker_class OPTIONS (
    column_name 'maker_class'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN vehicleclass_id OPTIONS (
    column_name 'vehicleclass_id'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN vehicle_type OPTIONS (
    column_name 'vehicle_type'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN colour OPTIONS (
    column_name 'colour'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN body_type OPTIONS (
    column_name 'body_type'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN engine_cc OPTIONS (
    column_name 'engine_cc'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN fuel OPTIONS (
    column_name 'fuel'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN hp OPTIONS (
    column_name 'hp'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN seat_capacity OPTIONS (
    column_name 'seat_capacity'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN owner_name OPTIONS (
    column_name 'owner_name'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN dob OPTIONS (
    column_name 'dob'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN father_name OPTIONS (
    column_name 'father_name'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN address OPTIONS (
    column_name 'address'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN city OPTIONS (
    column_name 'city'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN pin_code OPTIONS (
    column_name 'pin_code'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN contact_no OPTIONS (
    column_name 'contact_no'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN tr_number OPTIONS (
    column_name 'tr_number'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN fc_validity OPTIONS (
    column_name 'fc_validity'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN permit_validity OPTIONS (
    column_name 'permit_validity'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN insurance_validity OPTIONS (
    column_name 'insurance_validity'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN tax_validity OPTIONS (
    column_name 'tax_validity'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN applicant_name OPTIONS (
    column_name 'applicant_name'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN email OPTIONS (
    column_name 'email'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN insurance_policy_no OPTIONS (
    column_name 'insurance_policy_no'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN insurance_from OPTIONS (
    column_name 'insurance_from'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN permit_no OPTIONS (
    column_name 'permit_no'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN fc_no OPTIONS (
    column_name 'fc_no'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN record_updated_date OPTIONS (
    column_name 'record_updated_date'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN record_created_date OPTIONS (
    column_name 'record_created_date'
);
ALTER FOREIGN TABLE dist.rta_data ALTER COLUMN seq OPTIONS (
    column_name 'seq'
);


--
-- Name: tc_name; Type: FOREIGN TABLE; Schema: dist; Owner: -
--

CREATE FOREIGN TABLE dist.tc_name (
    id bigint NOT NULL,
    phone character varying(15) NOT NULL,
    name character varying(75),
    tags character varying(150),
    email character varying(500),
    asondate timestamp without time zone
)
SERVER distributed_db_srv
OPTIONS (
    schema_name 'public',
    table_name 'tc_name'
);
ALTER FOREIGN TABLE dist.tc_name ALTER COLUMN id OPTIONS (
    column_name 'id'
);
ALTER FOREIGN TABLE dist.tc_name ALTER COLUMN phone OPTIONS (
    column_name 'phone'
);
ALTER FOREIGN TABLE dist.tc_name ALTER COLUMN name OPTIONS (
    column_name 'name'
);
ALTER FOREIGN TABLE dist.tc_name ALTER COLUMN tags OPTIONS (
    column_name 'tags'
);
ALTER FOREIGN TABLE dist.tc_name ALTER COLUMN email OPTIONS (
    column_name 'email'
);
ALTER FOREIGN TABLE dist.tc_name ALTER COLUMN asondate OPTIONS (
    column_name 'asondate'
);


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: abstract_jan_to_july_till_date_to_check; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.abstract_jan_to_july_till_date_to_check (
    police_station character varying(100)
);


--
-- Name: address_other_state; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.address_other_state AS
 SELECT oth_sdr_key,
    phone,
    caf_no,
    aadhar_no,
    fullname,
    uniquekyccode,
    kycdatetime,
    kycrespcode,
    kycrespdate,
    uniqackrecno,
    dob,
    fathername,
    fulladdress,
    permanentaddress,
    state,
    alt_cnt_no,
    email_id,
    gender,
    nationality,
    profession_subscriber,
    pan_gir_no,
    status_subscriber,
    conn_type,
    form_of_payment,
    mode_of_paid,
    bank_acno,
    bank_name,
    bank_addr,
    imsi_no,
    operator,
    circle,
    doa,
    current_status,
    previous_service_provider,
    previous_circle,
    point_of_sale_code,
    point_of_sale_name,
    point_of_sale_agent_name,
    sale_agent_adhaar_no,
    pos_agent_unique_kyc,
    pos_auth_datetime,
    point_of_saleaddress,
    details_add_on_value,
    scanned_photo_colour,
    name_design_of_active_officer,
    poa_name,
    poa_no,
    poa_address,
    poi_name,
    poi_no,
    poi_address,
    category_type,
    eff_from_date,
    eff_to_date,
    seq
   FROM dist.address_other_state;


--
-- Name: cdat_civilsupply; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cdat_civilsupply (
    phone character varying(25)
);


--
-- Name: cdatpcsuspect; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cdatpcsuspect (
    ucid bigint NOT NULL,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone
);


--
-- Name: cdat_details; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdat_details AS
 SELECT ucid,
    phone,
    other,
    starttime,
    duration,
    incoming,
    imeinumber,
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
   FROM public.cdatpcsuspect;


--
-- Name: cdat_details1; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdat_details1 AS
 SELECT phone,
    other,
    starttime,
    duration,
    incoming,
    imeinumber,
    imsinumber,
    celltowerid,
    provider_key,
    state_key,
    asondate
   FROM public.cdatpcsuspect;


--
-- Name: cdat_echallan; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdat_echallan AS
 SELECT s_no,
    unit_name,
    ps_name,
    point_name,
    vehicle_no,
    challan_no,
    offence_date,
    vehicle_class,
    driver_name,
    parent_name,
    age,
    driver_address,
    driver_contact_no,
    owner_name,
    owner_address,
    owner_contact_no,
    date_of_pay,
    payer_contact_no,
    image_url,
    seq
   FROM dist.echallan_data;


--
-- Name: cdat_gas_details; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cdat_gas_details (
    phone character varying(25)
);


--
-- Name: cdat_licence; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdat_licence AS
 SELECT contact_no AS phone,
    dl_no AS licence_no,
    first_name AS fullname,
    parent_name AS father_name,
    dob,
    ((COALESCE(address, ''::character varying))::text ||
        CASE
            WHEN ((city IS NOT NULL) AND ((city)::text <> ''::text)) THEN (', '::text || (city)::text)
            ELSE ''::text
        END) AS fulladdress
   FROM dist.dl_data;


--
-- Name: cdat_rta; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdat_rta AS
 SELECT vehicle_no AS regn_no,
    owner_name AS fullname,
    father_name AS fathername,
    ((COALESCE(address, ''::character varying))::text ||
        CASE
            WHEN ((city IS NOT NULL) AND ((city)::text <> ''::text)) THEN (', '::text || (city)::text)
            ELSE ''::text
        END) AS fulladdress,
    city,
    contact_no AS phone,
    maker_class AS mkr_clas,
    colour,
    vehicle_class AS veh_class,
    concat(COALESCE(maker_class, ''::character varying), ', COLOR: ', COALESCE(colour, ''::character varying), ', ', COALESCE(vehicle_class, ''::character varying)) AS vehicle_type,
    engine_no AS eng_no,
    chassis_no AS chas_no,
    issue_date AS iss_dt,
    issue_date AS updated_dt
   FROM dist.rta_data;


--
-- Name: cdat_tc_name; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdat_tc_name AS
 SELECT id,
    phone,
    name,
    tags,
    email,
    asondate
   FROM dist.tc_name;


--
-- Name: cdataddress; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdataddress AS
 SELECT cdat_sdr_key,
    phone,
    caf_no,
    aadhar_no,
    fullname,
    uniquekyccode,
    kycdatetime,
    kycrespcode,
    kycrespdate,
    uniqackrecno,
    dob,
    fathername,
    fulladdress,
    permanentaddress,
    state,
    alt_cnt_no,
    email_id,
    gender,
    nationality,
    profession_subscriber,
    pan_gir_no,
    status_of_subscriber,
    conn_type,
    form_of_payment,
    mode_of_paid,
    bank_acno,
    bank_name,
    bank_addr,
    imsi_no,
    operator,
    circle,
    doa,
    current_status,
    previous_service_provider,
    previous_circle,
    point_of_sale_code,
    point_of_sale_name,
    point_of_sale_agent_name,
    sale_agent_adhaar_no,
    pos_agent_unique_kyc,
    pos_auth_datetime,
    point_of_saleaddress,
    details_add_on_value,
    scanned_photo_colour,
    name_design_of_active_officer,
    poa_name,
    poa_no,
    poa_address,
    poi_name,
    poi_no,
    poi_address,
    category_type,
    eff_from_date,
    eff_to_date
   FROM dist.cdataddress;


--
-- Name: cdatcelltowerareanew; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdatcelltowerareanew AS
 SELECT celltowerid,
    operator,
    state,
    siteaddress,
    areadescription,
    lastupdate,
    (provider_key)::smallint AS provider_key,
    (state_key)::smallint AS state_key,
    bts_id,
    lat,
    long,
    azimuth,
    otype
   FROM dist.cellids;


--
-- Name: cdatpcsuspect_staging; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cdatpcsuspect_staging (
    staging_id bigint NOT NULL,
    import_job_id bigint NOT NULL,
    source_row_number bigint NOT NULL,
    ucid bigint NOT NULL,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(18,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20) NOT NULL,
    source_file text NOT NULL,
    imported_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: cdatpcsuspect_staging_staging_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cdatpcsuspect_staging_staging_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cdatpcsuspect_staging_staging_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cdatpcsuspect_staging_staging_id_seq OWNED BY public.cdatpcsuspect_staging.staging_id;


--
-- Name: cdatphonearea; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cdatphonearea (
    phoneprefix character varying(20),
    areadescription text
);


--
-- Name: cdatsuspect; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cdatsuspect (
    phone character varying(25) NOT NULL,
    nickname character varying(100),
    mo text,
    inc_officer character varying(100),
    role character varying(50),
    category character varying
);


--
-- Name: document_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_jobs (
    job_id bigint NOT NULL,
    module character varying(20) NOT NULL,
    source_file text NOT NULL,
    source_basename text NOT NULL,
    file_path text NOT NULL,
    file_sha256 character(64) NOT NULL,
    status character varying(30) DEFAULT 'queued'::character varying NOT NULL,
    phase character varying(40),
    operator character varying(20),
    target_phone character varying(25),
    mssql_database character varying(128),
    total_rows_estimated bigint,
    rows_committed bigint DEFAULT 0 NOT NULL,
    last_checkpoint_key bigint DEFAULT 0 NOT NULL,
    last_source_row_no bigint DEFAULT 0 NOT NULL,
    batch_size integer DEFAULT 500 NOT NULL,
    phase_state jsonb DEFAULT '{}'::jsonb NOT NULL,
    error_message text,
    dry_run boolean DEFAULT false NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    completed_at timestamp with time zone
);


--
-- Name: cdr_import_jobs; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.cdr_import_jobs AS
 SELECT job_id,
    source_file,
    source_basename,
    file_sha256,
    COALESCE(operator, ''::character varying) AS operator,
    target_phone,
    status,
    NULL::integer AS header_line_no,
    (total_rows_estimated)::integer AS total_rows_estimated,
    rows_committed,
    last_source_row_no,
    batch_size,
    error_message,
    dry_run,
    created_at,
    updated_at,
    completed_at
   FROM public.document_jobs
  WHERE ((module)::text = 'cdr'::text);


--
-- Name: cdr_import_ucid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cdr_import_ucid_seq
    START WITH -1
    INCREMENT BY -1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: celltowerfiltered; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.celltowerfiltered AS
 SELECT celltowerid,
    operator,
    state,
    siteaddress,
    areadescription,
    lastupdate,
    provider_key,
    state_key,
    bts_id,
    lat,
    long,
    azimuth,
    otype
   FROM public.cdatcelltowerareanew
  WHERE ((lat IS NOT NULL) AND (long IS NOT NULL) AND ((lat)::text ~ '^-?[0-9]+(\.[0-9]+)?$'::text) AND ((long)::text ~ '^-?[0-9]+(\.[0-9]+)?$'::text));


--
-- Name: document_jobs_job_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.document_jobs_job_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: document_jobs_job_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.document_jobs_job_id_seq OWNED BY public.document_jobs.job_id;


--
-- Name: image_table; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.image_table (
    irkey character varying(50),
    image bytea
);


--
-- Name: ir_particulars; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ir_particulars (
    irkey character varying(50),
    name character varying(200),
    alias_name character varying,
    father_name character varying,
    age character varying,
    date_of_birth character varying,
    nationality character varying,
    occupation character varying,
    income_group character varying,
    regular_habits character varying,
    category character varying,
    present_address text,
    crime_head character varying,
    mo character varying,
    crime_no character varying,
    year character varying,
    sec_of_law character varying,
    police_station character varying,
    date_of_arrest timestamp without time zone,
    aadhar_no character varying,
    mobile character varying
);


--
-- Name: jrms_total_2012_to_2017; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jrms_total_2012_to_2017 (
    prisonerno character varying(50),
    psarrested character varying(100),
    name character varying(200),
    crimenos character varying(200),
    headofcrime character varying(100),
    mobileno character varying(25),
    addr_duringrelease text,
    gender character varying(20),
    jailname character varying(100),
    admission_to_jail date,
    releasedt date,
    photo text
);


--
-- Name: logins; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.logins (
    username character varying(50),
    password character varying(100),
    id integer NOT NULL,
    role character varying(50),
    fullname character varying(100)
);


--
-- Name: logins_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.logins_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logins_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.logins_id_seq OWNED BY public.logins.id;


--
-- Name: offence_details; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.offence_details (
    police_station character varying(100),
    crime_no character varying(50),
    year character varying(10),
    place_description character varying(100),
    crkey integer
);


--
-- Name: pdact_main_table; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pdact_main_table (
    irkey character varying(50),
    pdact_key character varying(50)
);


--
-- Name: rowdy_sheeter_data1; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rowdy_sheeter_data1 (
    police_station character varying(100)
);


--
-- Name: s; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.s (
    phone character varying(25),
    first_call text,
    last_call text,
    nickname text,
    mo text,
    last_updated text,
    inc_officer character varying(100)
);


--
-- Name: suspect_image_table; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.suspect_image_table (
    irkey character varying(50),
    mobile character varying(50),
    image text
);


--
-- Name: t; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t (
    phone text,
    first_call text,
    last_call text,
    nickname text,
    mo text,
    last_updated text,
    inc_officer text
);


--
-- Name: tbladmin; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tbladmin (
    id integer NOT NULL,
    adminname character varying(120),
    username character varying(120),
    mobilenumber bigint,
    email character varying(120),
    password character varying(200),
    adminregdate timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: tbladmin_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tbladmin_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tbladmin_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tbladmin_id_seq OWNED BY public.tbladmin.id;


--
-- Name: tblcategory; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tblcategory (
    id integer NOT NULL,
    categoryname character varying(200),
    creationdate timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: tblcategory_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tblcategory_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tblcategory_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tblcategory_id_seq OWNED BY public.tblcategory.id;


--
-- Name: tblpass; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tblpass (
    id integer NOT NULL,
    passnumber character varying(200),
    fullname character varying(200),
    contactnumber bigint,
    email character varying(200),
    identitytype character varying(200),
    identitycardno character varying(200),
    category character varying(100),
    fromdate character varying(200),
    todate character varying(200),
    passcreationdate timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: tblpass_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tblpass_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tblpass_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tblpass_id_seq OWNED BY public.tblpass.id;


--
-- Name: twrmdb_master_cdat; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.twrmdb_master_cdat (
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone,
    duration numeric(5,0),
    imeinumber numeric(15,0),
    call_type character varying(25),
    crkey integer
);


--
-- Name: upload_activity_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.upload_activity_logs (
    id integer NOT NULL,
    user_id integer NOT NULL,
    username character varying(100) NOT NULL,
    module_name character varying(100) NOT NULL,
    file_name character varying(255) NOT NULL,
    file_size bigint NOT NULL,
    total_records integer DEFAULT 0,
    inserted_records integer DEFAULT 0,
    failed_records integer DEFAULT 0,
    upload_status character varying(20) NOT NULL,
    error_reason text,
    ip_address character varying(45) NOT NULL,
    uploaded_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    document_job_id bigint,
    db_name character varying(100),
    table_name character varying(100),
    is_new_table character varying(10) DEFAULT 'No'::character varying,
    staging_batch_id bigint,
    verification_status character varying(30),
    content_fingerprint character varying(64)
);


--
-- Name: upload_activity_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.upload_activity_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: upload_activity_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.upload_activity_logs_id_seq OWNED BY public.upload_activity_logs.id;


--
-- Name: upload_approval_queue; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.upload_approval_queue (
    queue_id bigint NOT NULL,
    batch_id bigint NOT NULL,
    module character varying(20) NOT NULL,
    username character varying(100) DEFAULT ''::character varying NOT NULL,
    status character varying(20) DEFAULT 'queued'::character varying NOT NULL,
    inserted_records bigint,
    error_message text,
    queued_at timestamp with time zone DEFAULT now() NOT NULL,
    started_at timestamp with time zone,
    completed_at timestamp with time zone
);


--
-- Name: upload_approval_queue_queue_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.upload_approval_queue_queue_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: upload_approval_queue_queue_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.upload_approval_queue_queue_id_seq OWNED BY public.upload_approval_queue.queue_id;


--
-- Name: upload_staging_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.upload_staging_batches (
    batch_id bigint NOT NULL,
    document_job_id bigint NOT NULL,
    upload_log_id bigint,
    module character varying(20) NOT NULL,
    staging_tables jsonb DEFAULT '{}'::jsonb NOT NULL,
    verification_status character varying(30) DEFAULT 'pending'::character varying NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    verified_at timestamp with time zone,
    verified_by character varying(100)
);


--
-- Name: upload_staging_batches_batch_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.upload_staging_batches_batch_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: upload_staging_batches_batch_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.upload_staging_batches_batch_id_seq OWNED BY public.upload_staging_batches.batch_id;


--
-- Name: user_activity_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_activity_logs (
    id integer NOT NULL,
    session_id integer,
    user_id integer,
    username character varying(100) NOT NULL,
    module_name character varying(150) NOT NULL,
    action_type character varying(100) NOT NULL,
    search_data jsonb,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: user_activity_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.user_activity_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_activity_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.user_activity_logs_id_seq OWNED BY public.user_activity_logs.id;


--
-- Name: user_sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_sessions (
    id integer NOT NULL,
    user_id integer,
    username character varying(100) NOT NULL,
    fullname character varying(200),
    login_time timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    logout_time timestamp without time zone,
    session_duration integer,
    ip_address character varying(45),
    browser_info text,
    device_info character varying(50),
    session_token character varying(64)
);


--
-- Name: user_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.user_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.user_sessions_id_seq OWNED BY public.user_sessions.id;


--
-- Name: stg_cdr_102; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_102 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_102_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_102_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_102_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_102_staging_row_id_seq OWNED BY upload_staging.stg_cdr_102.staging_row_id;


--
-- Name: stg_cdr_104; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_104 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_104_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_104_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_104_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_104_staging_row_id_seq OWNED BY upload_staging.stg_cdr_104.staging_row_id;


--
-- Name: stg_cdr_106; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_106 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_106_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_106_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_106_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_106_staging_row_id_seq OWNED BY upload_staging.stg_cdr_106.staging_row_id;


--
-- Name: stg_cdr_109; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_109 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_109_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_109_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_109_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_109_staging_row_id_seq OWNED BY upload_staging.stg_cdr_109.staging_row_id;


--
-- Name: stg_cdr_112; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_112 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_112_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_112_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_112_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_112_staging_row_id_seq OWNED BY upload_staging.stg_cdr_112.staging_row_id;


--
-- Name: stg_cdr_114; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_114 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_114_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_114_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_114_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_114_staging_row_id_seq OWNED BY upload_staging.stg_cdr_114.staging_row_id;


--
-- Name: stg_cdr_115; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_115 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_115_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_115_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_115_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_115_staging_row_id_seq OWNED BY upload_staging.stg_cdr_115.staging_row_id;


--
-- Name: stg_cdr_118; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_118 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_118_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_118_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_118_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_118_staging_row_id_seq OWNED BY upload_staging.stg_cdr_118.staging_row_id;


--
-- Name: stg_cdr_120; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_120 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_120_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_120_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_120_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_120_staging_row_id_seq OWNED BY upload_staging.stg_cdr_120.staging_row_id;


--
-- Name: stg_cdr_122; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_122 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_122_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_122_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_122_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_122_staging_row_id_seq OWNED BY upload_staging.stg_cdr_122.staging_row_id;


--
-- Name: stg_cdr_125; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_125 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_125_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_125_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_125_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_125_staging_row_id_seq OWNED BY upload_staging.stg_cdr_125.staging_row_id;


--
-- Name: stg_cdr_126; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_126 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_126_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_126_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_126_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_126_staging_row_id_seq OWNED BY upload_staging.stg_cdr_126.staging_row_id;


--
-- Name: stg_cdr_129; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_129 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_129_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_129_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_129_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_129_staging_row_id_seq OWNED BY upload_staging.stg_cdr_129.staging_row_id;


--
-- Name: stg_cdr_131; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_131 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_131_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_131_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_131_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_131_staging_row_id_seq OWNED BY upload_staging.stg_cdr_131.staging_row_id;


--
-- Name: stg_cdr_133; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_133 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_133_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_133_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_133_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_133_staging_row_id_seq OWNED BY upload_staging.stg_cdr_133.staging_row_id;


--
-- Name: stg_cdr_135; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_135 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_135_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_135_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_135_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_135_staging_row_id_seq OWNED BY upload_staging.stg_cdr_135.staging_row_id;


--
-- Name: stg_cdr_136; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_136 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_136_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_136_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_136_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_136_staging_row_id_seq OWNED BY upload_staging.stg_cdr_136.staging_row_id;


--
-- Name: stg_cdr_139; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_139 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_139_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_139_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_139_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_139_staging_row_id_seq OWNED BY upload_staging.stg_cdr_139.staging_row_id;


--
-- Name: stg_cdr_140; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_140 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_140_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_140_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_140_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_140_staging_row_id_seq OWNED BY upload_staging.stg_cdr_140.staging_row_id;


--
-- Name: stg_cdr_143; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_143 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_143_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_143_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_143_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_143_staging_row_id_seq OWNED BY upload_staging.stg_cdr_143.staging_row_id;


--
-- Name: stg_cdr_145; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_145 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_145_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_145_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_145_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_145_staging_row_id_seq OWNED BY upload_staging.stg_cdr_145.staging_row_id;


--
-- Name: stg_cdr_146; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_146 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_146_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_146_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_146_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_146_staging_row_id_seq OWNED BY upload_staging.stg_cdr_146.staging_row_id;


--
-- Name: stg_cdr_149; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_149 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_149_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_149_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_149_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_149_staging_row_id_seq OWNED BY upload_staging.stg_cdr_149.staging_row_id;


--
-- Name: stg_cdr_151; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_151 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_151_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_151_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_151_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_151_staging_row_id_seq OWNED BY upload_staging.stg_cdr_151.staging_row_id;


--
-- Name: stg_cdr_152; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_152 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_152_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_152_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_152_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_152_staging_row_id_seq OWNED BY upload_staging.stg_cdr_152.staging_row_id;


--
-- Name: stg_cdr_155; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_155 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_155_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_155_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_155_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_155_staging_row_id_seq OWNED BY upload_staging.stg_cdr_155.staging_row_id;


--
-- Name: stg_cdr_156; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_156 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_156_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_156_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_156_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_156_staging_row_id_seq OWNED BY upload_staging.stg_cdr_156.staging_row_id;


--
-- Name: stg_cdr_158; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_158 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_158_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_158_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_158_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_158_staging_row_id_seq OWNED BY upload_staging.stg_cdr_158.staging_row_id;


--
-- Name: stg_cdr_161; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_161 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_161_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_161_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_161_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_161_staging_row_id_seq OWNED BY upload_staging.stg_cdr_161.staging_row_id;


--
-- Name: stg_cdr_162; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_162 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_162_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_162_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_162_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_162_staging_row_id_seq OWNED BY upload_staging.stg_cdr_162.staging_row_id;


--
-- Name: stg_cdr_165; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_165 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_165_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_165_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_165_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_165_staging_row_id_seq OWNED BY upload_staging.stg_cdr_165.staging_row_id;


--
-- Name: stg_cdr_166; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_166 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_166_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_166_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_166_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_166_staging_row_id_seq OWNED BY upload_staging.stg_cdr_166.staging_row_id;


--
-- Name: stg_cdr_169; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_169 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_169_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_169_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_169_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_169_staging_row_id_seq OWNED BY upload_staging.stg_cdr_169.staging_row_id;


--
-- Name: stg_cdr_171; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_171 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_171_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_171_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_171_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_171_staging_row_id_seq OWNED BY upload_staging.stg_cdr_171.staging_row_id;


--
-- Name: stg_cdr_172; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_172 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_172_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_172_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_172_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_172_staging_row_id_seq OWNED BY upload_staging.stg_cdr_172.staging_row_id;


--
-- Name: stg_cdr_175; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_175 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_175_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_175_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_175_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_175_staging_row_id_seq OWNED BY upload_staging.stg_cdr_175.staging_row_id;


--
-- Name: stg_cdr_177; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_177 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_177_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_177_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_177_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_177_staging_row_id_seq OWNED BY upload_staging.stg_cdr_177.staging_row_id;


--
-- Name: stg_cdr_178; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_178 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_178_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_178_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_178_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_178_staging_row_id_seq OWNED BY upload_staging.stg_cdr_178.staging_row_id;


--
-- Name: stg_cdr_182; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_182 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_182_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_182_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_182_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_182_staging_row_id_seq OWNED BY upload_staging.stg_cdr_182.staging_row_id;


--
-- Name: stg_cdr_183; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_183 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_183_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_183_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_183_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_183_staging_row_id_seq OWNED BY upload_staging.stg_cdr_183.staging_row_id;


--
-- Name: stg_cdr_186; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_186 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_186_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_186_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_186_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_186_staging_row_id_seq OWNED BY upload_staging.stg_cdr_186.staging_row_id;


--
-- Name: stg_cdr_188; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_188 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_188_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_188_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_188_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_188_staging_row_id_seq OWNED BY upload_staging.stg_cdr_188.staging_row_id;


--
-- Name: stg_cdr_189; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_189 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_189_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_189_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_189_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_189_staging_row_id_seq OWNED BY upload_staging.stg_cdr_189.staging_row_id;


--
-- Name: stg_cdr_192; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_192 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_192_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_192_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_192_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_192_staging_row_id_seq OWNED BY upload_staging.stg_cdr_192.staging_row_id;


--
-- Name: stg_cdr_193; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_193 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_193_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_193_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_193_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_193_staging_row_id_seq OWNED BY upload_staging.stg_cdr_193.staging_row_id;


--
-- Name: stg_cdr_196; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_196 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_196_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_196_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_196_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_196_staging_row_id_seq OWNED BY upload_staging.stg_cdr_196.staging_row_id;


--
-- Name: stg_cdr_197; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_197 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_197_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_197_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_197_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_197_staging_row_id_seq OWNED BY upload_staging.stg_cdr_197.staging_row_id;


--
-- Name: stg_cdr_200; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_200 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_200_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_200_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_200_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_200_staging_row_id_seq OWNED BY upload_staging.stg_cdr_200.staging_row_id;


--
-- Name: stg_cdr_203; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_203 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_203_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_203_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_203_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_203_staging_row_id_seq OWNED BY upload_staging.stg_cdr_203.staging_row_id;


--
-- Name: stg_cdr_204; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_204 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_204_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_204_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_204_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_204_staging_row_id_seq OWNED BY upload_staging.stg_cdr_204.staging_row_id;


--
-- Name: stg_cdr_207; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_207 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_207_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_207_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_207_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_207_staging_row_id_seq OWNED BY upload_staging.stg_cdr_207.staging_row_id;


--
-- Name: stg_cdr_208; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_208 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_208_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_208_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_208_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_208_staging_row_id_seq OWNED BY upload_staging.stg_cdr_208.staging_row_id;


--
-- Name: stg_cdr_211; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_211 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_211_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_211_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_211_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_211_staging_row_id_seq OWNED BY upload_staging.stg_cdr_211.staging_row_id;


--
-- Name: stg_cdr_216; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_216 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_216_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_216_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_216_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_216_staging_row_id_seq OWNED BY upload_staging.stg_cdr_216.staging_row_id;


--
-- Name: stg_cdr_218; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_218 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_218_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_218_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_218_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_218_staging_row_id_seq OWNED BY upload_staging.stg_cdr_218.staging_row_id;


--
-- Name: stg_cdr_219; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_219 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_219_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_219_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_219_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_219_staging_row_id_seq OWNED BY upload_staging.stg_cdr_219.staging_row_id;


--
-- Name: stg_cdr_222; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_222 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_222_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_222_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_222_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_222_staging_row_id_seq OWNED BY upload_staging.stg_cdr_222.staging_row_id;


--
-- Name: stg_cdr_226; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_226 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_226_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_226_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_226_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_226_staging_row_id_seq OWNED BY upload_staging.stg_cdr_226.staging_row_id;


--
-- Name: stg_cdr_228; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_228 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_228_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_228_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_228_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_228_staging_row_id_seq OWNED BY upload_staging.stg_cdr_228.staging_row_id;


--
-- Name: stg_cdr_232; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_232 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_232_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_232_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_232_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_232_staging_row_id_seq OWNED BY upload_staging.stg_cdr_232.staging_row_id;


--
-- Name: stg_cdr_234; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_234 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_234_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_234_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_234_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_234_staging_row_id_seq OWNED BY upload_staging.stg_cdr_234.staging_row_id;


--
-- Name: stg_cdr_235; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_235 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_235_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_235_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_235_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_235_staging_row_id_seq OWNED BY upload_staging.stg_cdr_235.staging_row_id;


--
-- Name: stg_cdr_238; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_238 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_238_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_238_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_238_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_238_staging_row_id_seq OWNED BY upload_staging.stg_cdr_238.staging_row_id;


--
-- Name: stg_cdr_239; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_239 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_239_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_239_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_239_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_239_staging_row_id_seq OWNED BY upload_staging.stg_cdr_239.staging_row_id;


--
-- Name: stg_cdr_242; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_242 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_242_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_242_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_242_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_242_staging_row_id_seq OWNED BY upload_staging.stg_cdr_242.staging_row_id;


--
-- Name: stg_cdr_243; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_243 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_243_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_243_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_243_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_243_staging_row_id_seq OWNED BY upload_staging.stg_cdr_243.staging_row_id;


--
-- Name: stg_cdr_246; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_246 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_246_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_246_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_246_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_246_staging_row_id_seq OWNED BY upload_staging.stg_cdr_246.staging_row_id;


--
-- Name: stg_cdr_248; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_248 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_248_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_248_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_248_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_248_staging_row_id_seq OWNED BY upload_staging.stg_cdr_248.staging_row_id;


--
-- Name: stg_cdr_249; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_249 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_249_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_249_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_249_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_249_staging_row_id_seq OWNED BY upload_staging.stg_cdr_249.staging_row_id;


--
-- Name: stg_cdr_252; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_252 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_252_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_252_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_252_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_252_staging_row_id_seq OWNED BY upload_staging.stg_cdr_252.staging_row_id;


--
-- Name: stg_cdr_254; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_254 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_254_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_254_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_254_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_254_staging_row_id_seq OWNED BY upload_staging.stg_cdr_254.staging_row_id;


--
-- Name: stg_cdr_255; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_255 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_255_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_255_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_255_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_255_staging_row_id_seq OWNED BY upload_staging.stg_cdr_255.staging_row_id;


--
-- Name: stg_cdr_258; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_258 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_258_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_258_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_258_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_258_staging_row_id_seq OWNED BY upload_staging.stg_cdr_258.staging_row_id;


--
-- Name: stg_cdr_261; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_261 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_261_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_261_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_261_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_261_staging_row_id_seq OWNED BY upload_staging.stg_cdr_261.staging_row_id;


--
-- Name: stg_cdr_268; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_268 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_268_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_268_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_268_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_268_staging_row_id_seq OWNED BY upload_staging.stg_cdr_268.staging_row_id;


--
-- Name: stg_cdr_270; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_270 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_270_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_270_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_270_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_270_staging_row_id_seq OWNED BY upload_staging.stg_cdr_270.staging_row_id;


--
-- Name: stg_cdr_273; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_273 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_273_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_273_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_273_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_273_staging_row_id_seq OWNED BY upload_staging.stg_cdr_273.staging_row_id;


--
-- Name: stg_cdr_275; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_275 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_275_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_275_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_275_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_275_staging_row_id_seq OWNED BY upload_staging.stg_cdr_275.staging_row_id;


--
-- Name: stg_cdr_279; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_279 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_279_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_279_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_279_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_279_staging_row_id_seq OWNED BY upload_staging.stg_cdr_279.staging_row_id;


--
-- Name: stg_cdr_281; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_281 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_281_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_281_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_281_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_281_staging_row_id_seq OWNED BY upload_staging.stg_cdr_281.staging_row_id;


--
-- Name: stg_cdr_293; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_293 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_293_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_293_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_293_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_293_staging_row_id_seq OWNED BY upload_staging.stg_cdr_293.staging_row_id;


--
-- Name: stg_cdr_299; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_299 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_299_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_299_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_299_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_299_staging_row_id_seq OWNED BY upload_staging.stg_cdr_299.staging_row_id;


--
-- Name: stg_cdr_319; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_319 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_319_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_319_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_319_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_319_staging_row_id_seq OWNED BY upload_staging.stg_cdr_319.staging_row_id;


--
-- Name: stg_cdr_341; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_341 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_341_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_341_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_341_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_341_staging_row_id_seq OWNED BY upload_staging.stg_cdr_341.staging_row_id;


--
-- Name: stg_cdr_343; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_343 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_343_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_343_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_343_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_343_staging_row_id_seq OWNED BY upload_staging.stg_cdr_343.staging_row_id;


--
-- Name: stg_cdr_36; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE TABLE upload_staging.stg_cdr_36 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_36_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE SEQUENCE upload_staging.stg_cdr_36_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_36_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_36_staging_row_id_seq OWNED BY upload_staging.stg_cdr_36.staging_row_id;


--
-- Name: stg_cdr_38; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_38 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_38_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_38_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_38_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_38_staging_row_id_seq OWNED BY upload_staging.stg_cdr_38.staging_row_id;


--
-- Name: stg_cdr_41; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_41 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_41_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_41_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_41_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_41_staging_row_id_seq OWNED BY upload_staging.stg_cdr_41.staging_row_id;


--
-- Name: stg_cdr_43; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_43 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_43_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_43_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_43_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_43_staging_row_id_seq OWNED BY upload_staging.stg_cdr_43.staging_row_id;


--
-- Name: stg_cdr_65; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_65 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_65_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_65_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_65_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_65_staging_row_id_seq OWNED BY upload_staging.stg_cdr_65.staging_row_id;


--
-- Name: stg_cdr_71; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_71 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_71_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_71_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_71_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_71_staging_row_id_seq OWNED BY upload_staging.stg_cdr_71.staging_row_id;


--
-- Name: stg_cdr_73; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_73 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_73_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_73_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_73_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_73_staging_row_id_seq OWNED BY upload_staging.stg_cdr_73.staging_row_id;


--
-- Name: stg_cdr_87; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_87 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_87_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_87_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_87_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_87_staging_row_id_seq OWNED BY upload_staging.stg_cdr_87.staging_row_id;


--
-- Name: stg_cdr_89; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_89 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_89_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_89_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_89_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_89_staging_row_id_seq OWNED BY upload_staging.stg_cdr_89.staging_row_id;


--
-- Name: stg_cdr_99; Type: TABLE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED TABLE upload_staging.stg_cdr_99 (
    staging_row_id bigint NOT NULL,
    ucid bigint,
    phone character varying(25),
    other character varying(50),
    starttime timestamp without time zone NOT NULL,
    duration numeric(5,0) NOT NULL,
    incoming smallint NOT NULL,
    imeinumber numeric(15,0) NOT NULL,
    imsinumber numeric(18,0),
    celltowerid character varying(50),
    otherinfo character varying(50),
    tower_key numeric(18,0),
    provider_key smallint NOT NULL,
    state_key smallint,
    first_cellid character varying(50),
    last_cellid character varying(50),
    roaming_nw character varying(50),
    call_type character varying(25),
    calling_no character varying(50),
    called_no character varying(50),
    asondate timestamp without time zone,
    operator character varying(20),
    source_file text,
    source_row_number bigint,
    import_job_id bigint,
    is_duplicate boolean DEFAULT false NOT NULL,
    duplicate_reason character varying(50),
    user_edited boolean DEFAULT false NOT NULL
);


--
-- Name: stg_cdr_99_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: -
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_99_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stg_cdr_99_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: -
--

ALTER SEQUENCE upload_staging.stg_cdr_99_staging_row_id_seq OWNED BY upload_staging.stg_cdr_99.staging_row_id;


--
-- Name: cdatpcsuspect_staging staging_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cdatpcsuspect_staging ALTER COLUMN staging_id SET DEFAULT nextval('public.cdatpcsuspect_staging_staging_id_seq'::regclass);


--
-- Name: document_jobs job_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_jobs ALTER COLUMN job_id SET DEFAULT nextval('public.document_jobs_job_id_seq'::regclass);


--
-- Name: logins id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logins ALTER COLUMN id SET DEFAULT nextval('public.logins_id_seq'::regclass);


--
-- Name: tbladmin id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tbladmin ALTER COLUMN id SET DEFAULT nextval('public.tbladmin_id_seq'::regclass);


--
-- Name: tblcategory id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tblcategory ALTER COLUMN id SET DEFAULT nextval('public.tblcategory_id_seq'::regclass);


--
-- Name: tblpass id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tblpass ALTER COLUMN id SET DEFAULT nextval('public.tblpass_id_seq'::regclass);


--
-- Name: upload_activity_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_activity_logs ALTER COLUMN id SET DEFAULT nextval('public.upload_activity_logs_id_seq'::regclass);


--
-- Name: upload_approval_queue queue_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_approval_queue ALTER COLUMN queue_id SET DEFAULT nextval('public.upload_approval_queue_queue_id_seq'::regclass);


--
-- Name: upload_staging_batches batch_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_staging_batches ALTER COLUMN batch_id SET DEFAULT nextval('public.upload_staging_batches_batch_id_seq'::regclass);


--
-- Name: user_activity_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_activity_logs ALTER COLUMN id SET DEFAULT nextval('public.user_activity_logs_id_seq'::regclass);


--
-- Name: user_sessions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_sessions ALTER COLUMN id SET DEFAULT nextval('public.user_sessions_id_seq'::regclass);


--
-- Name: stg_cdr_102 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_102 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_102_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_104 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_104 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_104_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_106 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_106 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_106_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_109 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_109 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_109_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_112 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_112 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_112_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_114 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_114 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_114_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_115 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_115 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_115_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_118 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_118 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_118_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_120 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_120 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_120_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_122 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_122 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_122_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_125 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_125 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_125_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_126 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_126 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_126_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_129 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_129 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_129_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_131 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_131 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_131_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_133 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_133 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_133_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_135 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_135 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_135_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_136 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_136 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_136_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_139 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_139 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_139_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_140 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_140 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_140_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_143 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_143 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_143_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_145 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_145 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_145_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_146 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_146 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_146_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_149 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_149 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_149_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_151 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_151 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_151_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_152 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_152 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_152_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_155 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_155 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_155_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_156 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_156 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_156_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_158 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_158 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_158_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_161 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_161 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_161_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_162 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_162 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_162_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_165 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_165 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_165_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_166 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_166 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_166_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_169 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_169 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_169_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_171 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_171 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_171_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_172 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_172 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_172_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_175 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_175 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_175_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_177 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_177 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_177_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_178 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_178 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_178_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_182 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_182 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_182_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_183 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_183 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_183_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_186 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_186 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_186_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_188 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_188 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_188_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_189 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_189 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_189_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_192 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_192 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_192_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_193 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_193 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_193_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_196 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_196 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_196_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_197 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_197 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_197_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_200 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_200 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_200_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_203 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_203 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_203_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_204 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_204 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_204_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_207 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_207 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_207_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_208 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_208 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_208_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_211 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_211 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_211_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_216 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_216 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_216_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_218 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_218 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_218_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_219 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_219 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_219_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_222 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_222 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_222_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_226 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_226 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_226_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_228 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_228 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_228_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_232 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_232 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_232_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_234 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_234 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_234_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_235 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_235 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_235_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_238 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_238 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_238_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_239 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_239 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_239_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_242 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_242 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_242_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_243 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_243 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_243_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_246 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_246 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_246_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_248 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_248 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_248_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_249 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_249 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_249_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_252 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_252 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_252_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_254 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_254 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_254_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_255 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_255 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_255_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_258 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_258 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_258_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_261 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_261 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_261_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_268 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_268 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_268_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_270 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_270 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_270_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_273 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_273 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_273_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_275 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_275 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_275_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_279 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_279 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_279_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_281 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_281 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_281_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_293 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_293 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_293_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_299 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_299 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_299_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_319 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_319 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_319_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_341 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_341 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_341_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_343 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_343 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_343_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_36 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_36 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_36_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_38 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_38 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_38_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_41 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_41 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_41_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_43 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_43 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_43_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_65 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_65 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_65_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_71 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_71 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_71_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_73 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_73 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_73_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_87 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_87 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_87_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_89 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_89 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_89_staging_row_id_seq'::regclass);


--
-- Name: stg_cdr_99 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_99 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_99_staging_row_id_seq'::regclass);


--
-- Name: cdatpcsuspect_staging cdatpcsuspect_staging_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cdatpcsuspect_staging
    ADD CONSTRAINT cdatpcsuspect_staging_pkey PRIMARY KEY (staging_id);


--
-- Name: cdatsuspect cdatsuspect_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cdatsuspect
    ADD CONSTRAINT cdatsuspect_pkey PRIMARY KEY (phone);


--
-- Name: document_jobs document_jobs_module_source_file_file_sha256_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_jobs
    ADD CONSTRAINT document_jobs_module_source_file_file_sha256_key UNIQUE (module, source_file, file_sha256);


--
-- Name: document_jobs document_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_jobs
    ADD CONSTRAINT document_jobs_pkey PRIMARY KEY (job_id);


--
-- Name: tbladmin tbladmin_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tbladmin
    ADD CONSTRAINT tbladmin_pkey PRIMARY KEY (id);


--
-- Name: tblcategory tblcategory_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tblcategory
    ADD CONSTRAINT tblcategory_pkey PRIMARY KEY (id);


--
-- Name: tblpass tblpass_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tblpass
    ADD CONSTRAINT tblpass_pkey PRIMARY KEY (id);


--
-- Name: upload_activity_logs upload_activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_activity_logs
    ADD CONSTRAINT upload_activity_logs_pkey PRIMARY KEY (id);


--
-- Name: upload_approval_queue upload_approval_queue_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_approval_queue
    ADD CONSTRAINT upload_approval_queue_pkey PRIMARY KEY (queue_id);


--
-- Name: upload_staging_batches upload_staging_batches_document_job_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_staging_batches
    ADD CONSTRAINT upload_staging_batches_document_job_id_key UNIQUE (document_job_id);


--
-- Name: upload_staging_batches upload_staging_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_staging_batches
    ADD CONSTRAINT upload_staging_batches_pkey PRIMARY KEY (batch_id);


--
-- Name: user_activity_logs user_activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_activity_logs
    ADD CONSTRAINT user_activity_logs_pkey PRIMARY KEY (id);


--
-- Name: user_sessions user_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_sessions
    ADD CONSTRAINT user_sessions_pkey PRIMARY KEY (id);


--
-- Name: stg_cdr_102 stg_cdr_102_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_102
    ADD CONSTRAINT stg_cdr_102_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_104 stg_cdr_104_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_104
    ADD CONSTRAINT stg_cdr_104_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_106 stg_cdr_106_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_106
    ADD CONSTRAINT stg_cdr_106_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_109 stg_cdr_109_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_109
    ADD CONSTRAINT stg_cdr_109_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_112 stg_cdr_112_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_112
    ADD CONSTRAINT stg_cdr_112_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_114 stg_cdr_114_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_114
    ADD CONSTRAINT stg_cdr_114_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_115 stg_cdr_115_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_115
    ADD CONSTRAINT stg_cdr_115_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_118 stg_cdr_118_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_118
    ADD CONSTRAINT stg_cdr_118_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_120 stg_cdr_120_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_120
    ADD CONSTRAINT stg_cdr_120_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_122 stg_cdr_122_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_122
    ADD CONSTRAINT stg_cdr_122_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_125 stg_cdr_125_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_125
    ADD CONSTRAINT stg_cdr_125_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_126 stg_cdr_126_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_126
    ADD CONSTRAINT stg_cdr_126_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_129 stg_cdr_129_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_129
    ADD CONSTRAINT stg_cdr_129_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_131 stg_cdr_131_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_131
    ADD CONSTRAINT stg_cdr_131_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_133 stg_cdr_133_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_133
    ADD CONSTRAINT stg_cdr_133_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_135 stg_cdr_135_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_135
    ADD CONSTRAINT stg_cdr_135_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_136 stg_cdr_136_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_136
    ADD CONSTRAINT stg_cdr_136_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_139 stg_cdr_139_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_139
    ADD CONSTRAINT stg_cdr_139_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_140 stg_cdr_140_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_140
    ADD CONSTRAINT stg_cdr_140_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_143 stg_cdr_143_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_143
    ADD CONSTRAINT stg_cdr_143_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_145 stg_cdr_145_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_145
    ADD CONSTRAINT stg_cdr_145_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_146 stg_cdr_146_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_146
    ADD CONSTRAINT stg_cdr_146_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_149 stg_cdr_149_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_149
    ADD CONSTRAINT stg_cdr_149_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_151 stg_cdr_151_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_151
    ADD CONSTRAINT stg_cdr_151_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_152 stg_cdr_152_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_152
    ADD CONSTRAINT stg_cdr_152_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_155 stg_cdr_155_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_155
    ADD CONSTRAINT stg_cdr_155_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_156 stg_cdr_156_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_156
    ADD CONSTRAINT stg_cdr_156_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_158 stg_cdr_158_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_158
    ADD CONSTRAINT stg_cdr_158_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_161 stg_cdr_161_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_161
    ADD CONSTRAINT stg_cdr_161_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_162 stg_cdr_162_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_162
    ADD CONSTRAINT stg_cdr_162_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_165 stg_cdr_165_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_165
    ADD CONSTRAINT stg_cdr_165_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_166 stg_cdr_166_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_166
    ADD CONSTRAINT stg_cdr_166_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_169 stg_cdr_169_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_169
    ADD CONSTRAINT stg_cdr_169_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_171 stg_cdr_171_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_171
    ADD CONSTRAINT stg_cdr_171_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_172 stg_cdr_172_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_172
    ADD CONSTRAINT stg_cdr_172_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_175 stg_cdr_175_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_175
    ADD CONSTRAINT stg_cdr_175_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_177 stg_cdr_177_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_177
    ADD CONSTRAINT stg_cdr_177_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_178 stg_cdr_178_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_178
    ADD CONSTRAINT stg_cdr_178_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_182 stg_cdr_182_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_182
    ADD CONSTRAINT stg_cdr_182_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_183 stg_cdr_183_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_183
    ADD CONSTRAINT stg_cdr_183_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_186 stg_cdr_186_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_186
    ADD CONSTRAINT stg_cdr_186_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_188 stg_cdr_188_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_188
    ADD CONSTRAINT stg_cdr_188_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_189 stg_cdr_189_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_189
    ADD CONSTRAINT stg_cdr_189_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_192 stg_cdr_192_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_192
    ADD CONSTRAINT stg_cdr_192_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_193 stg_cdr_193_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_193
    ADD CONSTRAINT stg_cdr_193_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_196 stg_cdr_196_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_196
    ADD CONSTRAINT stg_cdr_196_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_197 stg_cdr_197_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_197
    ADD CONSTRAINT stg_cdr_197_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_200 stg_cdr_200_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_200
    ADD CONSTRAINT stg_cdr_200_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_203 stg_cdr_203_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_203
    ADD CONSTRAINT stg_cdr_203_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_204 stg_cdr_204_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_204
    ADD CONSTRAINT stg_cdr_204_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_207 stg_cdr_207_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_207
    ADD CONSTRAINT stg_cdr_207_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_208 stg_cdr_208_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_208
    ADD CONSTRAINT stg_cdr_208_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_211 stg_cdr_211_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_211
    ADD CONSTRAINT stg_cdr_211_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_216 stg_cdr_216_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_216
    ADD CONSTRAINT stg_cdr_216_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_218 stg_cdr_218_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_218
    ADD CONSTRAINT stg_cdr_218_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_219 stg_cdr_219_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_219
    ADD CONSTRAINT stg_cdr_219_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_222 stg_cdr_222_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_222
    ADD CONSTRAINT stg_cdr_222_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_226 stg_cdr_226_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_226
    ADD CONSTRAINT stg_cdr_226_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_228 stg_cdr_228_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_228
    ADD CONSTRAINT stg_cdr_228_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_232 stg_cdr_232_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_232
    ADD CONSTRAINT stg_cdr_232_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_234 stg_cdr_234_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_234
    ADD CONSTRAINT stg_cdr_234_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_235 stg_cdr_235_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_235
    ADD CONSTRAINT stg_cdr_235_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_238 stg_cdr_238_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_238
    ADD CONSTRAINT stg_cdr_238_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_239 stg_cdr_239_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_239
    ADD CONSTRAINT stg_cdr_239_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_242 stg_cdr_242_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_242
    ADD CONSTRAINT stg_cdr_242_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_243 stg_cdr_243_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_243
    ADD CONSTRAINT stg_cdr_243_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_246 stg_cdr_246_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_246
    ADD CONSTRAINT stg_cdr_246_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_248 stg_cdr_248_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_248
    ADD CONSTRAINT stg_cdr_248_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_249 stg_cdr_249_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_249
    ADD CONSTRAINT stg_cdr_249_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_252 stg_cdr_252_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_252
    ADD CONSTRAINT stg_cdr_252_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_254 stg_cdr_254_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_254
    ADD CONSTRAINT stg_cdr_254_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_255 stg_cdr_255_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_255
    ADD CONSTRAINT stg_cdr_255_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_258 stg_cdr_258_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_258
    ADD CONSTRAINT stg_cdr_258_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_261 stg_cdr_261_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_261
    ADD CONSTRAINT stg_cdr_261_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_268 stg_cdr_268_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_268
    ADD CONSTRAINT stg_cdr_268_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_270 stg_cdr_270_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_270
    ADD CONSTRAINT stg_cdr_270_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_273 stg_cdr_273_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_273
    ADD CONSTRAINT stg_cdr_273_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_275 stg_cdr_275_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_275
    ADD CONSTRAINT stg_cdr_275_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_279 stg_cdr_279_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_279
    ADD CONSTRAINT stg_cdr_279_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_281 stg_cdr_281_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_281
    ADD CONSTRAINT stg_cdr_281_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_293 stg_cdr_293_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_293
    ADD CONSTRAINT stg_cdr_293_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_299 stg_cdr_299_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_299
    ADD CONSTRAINT stg_cdr_299_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_319 stg_cdr_319_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_319
    ADD CONSTRAINT stg_cdr_319_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_341 stg_cdr_341_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_341
    ADD CONSTRAINT stg_cdr_341_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_343 stg_cdr_343_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_343
    ADD CONSTRAINT stg_cdr_343_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_36 stg_cdr_36_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_36
    ADD CONSTRAINT stg_cdr_36_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_38 stg_cdr_38_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_38
    ADD CONSTRAINT stg_cdr_38_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_41 stg_cdr_41_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_41
    ADD CONSTRAINT stg_cdr_41_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_43 stg_cdr_43_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_43
    ADD CONSTRAINT stg_cdr_43_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_65 stg_cdr_65_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_65
    ADD CONSTRAINT stg_cdr_65_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_71 stg_cdr_71_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_71
    ADD CONSTRAINT stg_cdr_71_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_73 stg_cdr_73_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_73
    ADD CONSTRAINT stg_cdr_73_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_87 stg_cdr_87_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_87
    ADD CONSTRAINT stg_cdr_87_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_89 stg_cdr_89_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_89
    ADD CONSTRAINT stg_cdr_89_pkey PRIMARY KEY (staging_row_id);


--
-- Name: stg_cdr_99 stg_cdr_99_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: -
--

ALTER TABLE ONLY upload_staging.stg_cdr_99
    ADD CONSTRAINT stg_cdr_99_pkey PRIMARY KEY (staging_row_id);


--
-- Name: idx_cdatpcsuspect_asondate; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatpcsuspect_asondate ON public.cdatpcsuspect USING btree (asondate);


--
-- Name: idx_cdatpcsuspect_celltowerid; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatpcsuspect_celltowerid ON public.cdatpcsuspect USING btree (celltowerid) WHERE ((celltowerid IS NOT NULL) AND ((celltowerid)::text <> ''::text));


--
-- Name: idx_cdatpcsuspect_imeinumber; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatpcsuspect_imeinumber ON public.cdatpcsuspect USING btree (imeinumber) WHERE ((imeinumber IS NOT NULL) AND (imeinumber <> (0)::numeric));


--
-- Name: idx_cdatpcsuspect_other; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatpcsuspect_other ON public.cdatpcsuspect USING btree (other) WHERE ((other IS NOT NULL) AND ((other)::text <> ''::text));


--
-- Name: idx_cdatpcsuspect_phone; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatpcsuspect_phone ON public.cdatpcsuspect USING btree (phone);


--
-- Name: idx_cdatpcsuspect_phone_other_starttime; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatpcsuspect_phone_other_starttime ON public.cdatpcsuspect USING btree (phone, other, starttime);


--
-- Name: idx_cdatpcsuspect_phone_starttime; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatpcsuspect_phone_starttime ON public.cdatpcsuspect USING btree (phone, starttime);


--
-- Name: idx_cdatpcsuspect_staging_job; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatpcsuspect_staging_job ON public.cdatpcsuspect_staging USING btree (import_job_id, source_row_number);


--
-- Name: idx_cdatphonearea_phoneprefix; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatphonearea_phoneprefix ON public.cdatphonearea USING btree (phoneprefix);


--
-- Name: idx_cdatphonearea_phoneprefix_len; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdatphonearea_phoneprefix_len ON public.cdatphonearea USING btree (length((phoneprefix)::text) DESC);


--
-- Name: idx_document_jobs_module; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_document_jobs_module ON public.document_jobs USING btree (module, status);


--
-- Name: idx_document_jobs_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_document_jobs_status ON public.document_jobs USING btree (status);


--
-- Name: idx_upload_activity_logs_document_job_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_upload_activity_logs_document_job_id ON public.upload_activity_logs USING btree (document_job_id) WHERE (document_job_id IS NOT NULL);


--
-- Name: idx_upload_approval_queue_batch_active; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_upload_approval_queue_batch_active ON public.upload_approval_queue USING btree (batch_id) WHERE ((status)::text = ANY ((ARRAY['queued'::character varying, 'running'::character varying])::text[]));


--
-- Name: idx_upload_approval_queue_module_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_upload_approval_queue_module_status ON public.upload_approval_queue USING btree (module, status, queued_at);


--
-- Name: idx_upload_logs_content_fingerprint; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_upload_logs_content_fingerprint ON public.upload_activity_logs USING btree (table_name, content_fingerprint) WHERE ((content_fingerprint IS NOT NULL) AND ((upload_status)::text = 'Success'::text));


--
-- Name: idx_upload_logs_document_job_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_upload_logs_document_job_id ON public.upload_activity_logs USING btree (document_job_id);


--
-- Name: idx_upload_logs_staging_batch; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_upload_logs_staging_batch ON public.upload_activity_logs USING btree (staging_batch_id);


--
-- Name: idx_upload_logs_uploaded_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_upload_logs_uploaded_at ON public.upload_activity_logs USING btree (uploaded_at DESC);


--
-- Name: idx_upload_logs_username; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_upload_logs_username ON public.upload_activity_logs USING btree (username);


--
-- Name: idx_upload_staging_batches_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_upload_staging_batches_status ON public.upload_staging_batches USING btree (verification_status);


--
-- Name: idx_user_activity_logs_username; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_user_activity_logs_username ON public.user_activity_logs USING btree (username);


--
-- Name: idx_user_sessions_username; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_user_sessions_username ON public.user_sessions USING btree (username);


--
-- Name: cdatpcsuspect_staging cdatpcsuspect_staging_import_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cdatpcsuspect_staging
    ADD CONSTRAINT cdatpcsuspect_staging_import_job_id_fkey FOREIGN KEY (import_job_id) REFERENCES public.document_jobs(job_id);


--
-- Name: upload_approval_queue upload_approval_queue_batch_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_approval_queue
    ADD CONSTRAINT upload_approval_queue_batch_id_fkey FOREIGN KEY (batch_id) REFERENCES public.upload_staging_batches(batch_id) ON DELETE CASCADE;


--
-- Name: upload_staging_batches upload_staging_batches_document_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.upload_staging_batches
    ADD CONSTRAINT upload_staging_batches_document_job_id_fkey FOREIGN KEY (document_job_id) REFERENCES public.document_jobs(job_id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict QX5K0pUZpW2dGWmUmkbCo7JoIiajKNBvnca8dFxUCzhdkdTbCLgd0iJKcj7ymbR

