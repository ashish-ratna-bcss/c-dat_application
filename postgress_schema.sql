--
-- PostgreSQL database dump
--

\restrict cJ3exZHYXJhYsDbiORl0B4Svddnij7wx8y28MHyQPnBjPmkoFhJy6nQF2WVYnuS

-- Dumped from database version 16.14 (Ubuntu 16.14-1.pgdg24.04+1)
-- Dumped by pg_dump version 16.14 (Ubuntu 16.14-0ubuntu0.24.04.1)

-- Started on 2026-08-04 17:05:02 IST

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
-- TOC entry 13 (class 2615 OID 1893815)
-- Name: dist; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA dist;


ALTER SCHEMA dist OWNER TO postgres;

--
-- TOC entry 38 (class 2615 OID 2153699)
-- Name: upload_staging; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA upload_staging;


ALTER SCHEMA upload_staging OWNER TO postgres;

--
-- TOC entry 2 (class 3079 OID 1893806)
-- Name: postgres_fdw; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgres_fdw WITH SCHEMA public;


--
-- TOC entry 4739 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION postgres_fdw; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgres_fdw IS 'foreign-data wrapper for remote PostgreSQL servers';


--
-- TOC entry 508 (class 1255 OID 2153727)
-- Name: calculatedistance(double precision, double precision, double precision, double precision); Type: FUNCTION; Schema: public; Owner: postgres
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


ALTER FUNCTION public.calculatedistance(lon1 double precision, lat1 double precision, lon2 double precision, lat2 double precision) OWNER TO postgres;

--
-- TOC entry 509 (class 1255 OID 2153728)
-- Name: getbearing(double precision, double precision, double precision, double precision); Type: FUNCTION; Schema: public; Owner: postgres
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


ALTER FUNCTION public.getbearing(lat1 double precision, lon1 double precision, lat2 double precision, lon2 double precision) OWNER TO postgres;

--
-- TOC entry 2755 (class 1417 OID 1893813)
-- Name: distributed_db_srv; Type: SERVER; Schema: -; Owner: postgres
--

CREATE SERVER distributed_db_srv FOREIGN DATA WRAPPER postgres_fdw OPTIONS (
    dbname 'distributed_db',
    host '127.0.0.1',
    port '5432'
);


ALTER SERVER distributed_db_srv OWNER TO postgres;

--
-- TOC entry 4740 (class 0 OID 0)
-- Name: USER MAPPING postgres SERVER distributed_db_srv; Type: USER MAPPING; Schema: -; Owner: postgres
--

CREATE USER MAPPING FOR postgres SERVER distributed_db_srv OPTIONS (
    password 'BcSs@!nd!@76',
    "user" 'postgres'
);


--
-- TOC entry 264 (class 1259 OID 1893816)
-- Name: address_other_state; Type: FOREIGN TABLE; Schema: dist; Owner: postgres
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


ALTER FOREIGN TABLE dist.address_other_state OWNER TO postgres;

--
-- TOC entry 297 (class 1259 OID 1897980)
-- Name: cdataddress; Type: FOREIGN TABLE; Schema: dist; Owner: postgres
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


ALTER FOREIGN TABLE dist.cdataddress OWNER TO postgres;

--
-- TOC entry 298 (class 1259 OID 1897983)
-- Name: cellids; Type: FOREIGN TABLE; Schema: dist; Owner: postgres
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


ALTER FOREIGN TABLE dist.cellids OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 1893819)
-- Name: dl_data; Type: FOREIGN TABLE; Schema: dist; Owner: postgres
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


ALTER FOREIGN TABLE dist.dl_data OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 1893822)
-- Name: echallan_data; Type: FOREIGN TABLE; Schema: dist; Owner: postgres
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


ALTER FOREIGN TABLE dist.echallan_data OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 1893825)
-- Name: rta_data; Type: FOREIGN TABLE; Schema: dist; Owner: postgres
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


ALTER FOREIGN TABLE dist.rta_data OWNER TO postgres;

--
-- TOC entry 268 (class 1259 OID 1893828)
-- Name: tc_name; Type: FOREIGN TABLE; Schema: dist; Owner: postgres
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


ALTER FOREIGN TABLE dist.tc_name OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 263 (class 1259 OID 1893772)
-- Name: abstract_jan_to_july_till_date_to_check; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.abstract_jan_to_july_till_date_to_check (
    police_station character varying(100)
);


ALTER TABLE public.abstract_jan_to_july_till_date_to_check OWNER TO postgres;

--
-- TOC entry 299 (class 1259 OID 1897986)
-- Name: address_other_state; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.address_other_state OWNER TO postgres;

--
-- TOC entry 272 (class 1259 OID 1893874)
-- Name: cdat_civilsupply; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cdat_civilsupply (
    phone character varying(25)
);


ALTER TABLE public.cdat_civilsupply OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 574203)
-- Name: cdatpcsuspect; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.cdatpcsuspect OWNER TO postgres;

--
-- TOC entry 275 (class 1259 OID 1894120)
-- Name: cdat_details; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.cdat_details OWNER TO postgres;

--
-- TOC entry 276 (class 1259 OID 1894125)
-- Name: cdat_details1; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.cdat_details1 OWNER TO postgres;

--
-- TOC entry 269 (class 1259 OID 1893860)
-- Name: cdat_echallan; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.cdat_echallan OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 1893871)
-- Name: cdat_gas_details; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cdat_gas_details (
    phone character varying(25)
);


ALTER TABLE public.cdat_gas_details OWNER TO postgres;

--
-- TOC entry 302 (class 1259 OID 1898005)
-- Name: cdat_licence; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.cdat_licence OWNER TO postgres;

--
-- TOC entry 502 (class 1259 OID 2187607)
-- Name: cdat_provider_master; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cdat_provider_master (
    provider_key integer NOT NULL,
    provider text NOT NULL,
    provider_name text NOT NULL
);


ALTER TABLE public.cdat_provider_master OWNER TO postgres;

--
-- TOC entry 301 (class 1259 OID 1898000)
-- Name: cdat_rta; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.cdat_rta OWNER TO postgres;

--
-- TOC entry 501 (class 1259 OID 2187600)
-- Name: cdat_state_master; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cdat_state_master (
    state_key integer NOT NULL,
    state text,
    capital text,
    description text
);


ALTER TABLE public.cdat_state_master OWNER TO postgres;

--
-- TOC entry 270 (class 1259 OID 1893864)
-- Name: cdat_tc_name; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.cdat_tc_name AS
 SELECT id,
    phone,
    name,
    tags,
    email,
    asondate
   FROM dist.tc_name;


ALTER VIEW public.cdat_tc_name OWNER TO postgres;

--
-- TOC entry 300 (class 1259 OID 1897991)
-- Name: cdataddress; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.cdataddress OWNER TO postgres;

--
-- TOC entry 309 (class 1259 OID 2153733)
-- Name: cdatcelltowerareanew; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.cdatcelltowerareanew OWNER TO postgres;

--
-- TOC entry 279 (class 1259 OID 1894448)
-- Name: cdatpcsuspect_staging; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.cdatpcsuspect_staging OWNER TO postgres;

--
-- TOC entry 278 (class 1259 OID 1894447)
-- Name: cdatpcsuspect_staging_staging_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cdatpcsuspect_staging_staging_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cdatpcsuspect_staging_staging_id_seq OWNER TO postgres;

--
-- TOC entry 4764 (class 0 OID 0)
-- Dependencies: 278
-- Name: cdatpcsuspect_staging_staging_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cdatpcsuspect_staging_staging_id_seq OWNED BY public.cdatpcsuspect_staging.staging_id;


--
-- TOC entry 256 (class 1259 OID 1893742)
-- Name: cdatphonearea; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cdatphonearea (
    phoneprefix character varying(20),
    areadescription text,
    state text,
    numberlength integer,
    pplen integer,
    ph_type text,
    asondate timestamp without time zone,
    state_key integer,
    state_code character varying(8),
    provider_name text,
    provider_key integer,
    mobile_network text,
    state1 text
);


ALTER TABLE public.cdatphonearea OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 1893720)
-- Name: cdatsuspect; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cdatsuspect (
    phone character varying(25) NOT NULL,
    nickname character varying(100),
    mo text,
    inc_officer character varying(100),
    role character varying(50),
    category character varying
);


ALTER TABLE public.cdatsuspect OWNER TO postgres;

--
-- TOC entry 282 (class 1259 OID 1894467)
-- Name: document_jobs; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.document_jobs OWNER TO postgres;

--
-- TOC entry 283 (class 1259 OID 1894488)
-- Name: cdr_import_jobs; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.cdr_import_jobs OWNER TO postgres;

--
-- TOC entry 280 (class 1259 OID 1894463)
-- Name: cdr_import_ucid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cdr_import_ucid_seq
    START WITH -1
    INCREMENT BY -1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cdr_import_ucid_seq OWNER TO postgres;

--
-- TOC entry 310 (class 1259 OID 2153737)
-- Name: celltowerfiltered; Type: VIEW; Schema: public; Owner: postgres
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


ALTER VIEW public.celltowerfiltered OWNER TO postgres;

--
-- TOC entry 281 (class 1259 OID 1894466)
-- Name: document_jobs_job_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.document_jobs_job_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.document_jobs_job_id_seq OWNER TO postgres;

--
-- TOC entry 4772 (class 0 OID 0)
-- Dependencies: 281
-- Name: document_jobs_job_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.document_jobs_job_id_seq OWNED BY public.document_jobs.job_id;


--
-- TOC entry 262 (class 1259 OID 1893767)
-- Name: image_table; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.image_table (
    irkey character varying(50),
    image bytea
);


ALTER TABLE public.image_table OWNER TO postgres;

--
-- TOC entry 260 (class 1259 OID 1893761)
-- Name: ir_particulars; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.ir_particulars OWNER TO postgres;

--
-- TOC entry 274 (class 1259 OID 1893896)
-- Name: jrms_total_2012_to_2017; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.jrms_total_2012_to_2017 OWNER TO postgres;

--
-- TOC entry 273 (class 1259 OID 1893877)
-- Name: logins; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.logins (
    username character varying(50),
    password character varying(100),
    id integer NOT NULL,
    role character varying(50),
    fullname character varying(100)
);


ALTER TABLE public.logins OWNER TO postgres;

--
-- TOC entry 290 (class 1259 OID 1894542)
-- Name: logins_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.logins_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.logins_id_seq OWNER TO postgres;

--
-- TOC entry 4778 (class 0 OID 0)
-- Dependencies: 290
-- Name: logins_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.logins_id_seq OWNED BY public.logins.id;


--
-- TOC entry 258 (class 1259 OID 1893755)
-- Name: offence_details; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.offence_details (
    police_station character varying(100),
    crime_no character varying(50),
    year character varying(10),
    place_description character varying(100),
    crkey integer
);


ALTER TABLE public.offence_details OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 1893764)
-- Name: pdact_main_table; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pdact_main_table (
    irkey character varying(50),
    pdact_key character varying(50)
);


ALTER TABLE public.pdact_main_table OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 1893752)
-- Name: rowdy_sheeter_data1; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rowdy_sheeter_data1 (
    police_station character varying(100)
);


ALTER TABLE public.rowdy_sheeter_data1 OWNER TO postgres;

--
-- TOC entry 303 (class 1259 OID 1898044)
-- Name: s; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.s OWNER TO postgres;

--
-- TOC entry 277 (class 1259 OID 1894291)
-- Name: suspect_image_table; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.suspect_image_table (
    irkey character varying(50),
    mobile character varying(50),
    image text
);


ALTER TABLE public.suspect_image_table OWNER TO postgres;

--
-- TOC entry 304 (class 1259 OID 1898049)
-- Name: t; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.t OWNER TO postgres;

--
-- TOC entry 285 (class 1259 OID 1894513)
-- Name: tbladmin; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.tbladmin OWNER TO postgres;

--
-- TOC entry 284 (class 1259 OID 1894512)
-- Name: tbladmin_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tbladmin_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tbladmin_id_seq OWNER TO postgres;

--
-- TOC entry 4787 (class 0 OID 0)
-- Dependencies: 284
-- Name: tbladmin_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tbladmin_id_seq OWNED BY public.tbladmin.id;


--
-- TOC entry 287 (class 1259 OID 1894523)
-- Name: tblcategory; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcategory (
    id integer NOT NULL,
    categoryname character varying(200),
    creationdate timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.tblcategory OWNER TO postgres;

--
-- TOC entry 286 (class 1259 OID 1894522)
-- Name: tblcategory_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcategory_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcategory_id_seq OWNER TO postgres;

--
-- TOC entry 4790 (class 0 OID 0)
-- Dependencies: 286
-- Name: tblcategory_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcategory_id_seq OWNED BY public.tblcategory.id;


--
-- TOC entry 289 (class 1259 OID 1894531)
-- Name: tblpass; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.tblpass OWNER TO postgres;

--
-- TOC entry 288 (class 1259 OID 1894530)
-- Name: tblpass_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblpass_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblpass_id_seq OWNER TO postgres;

--
-- TOC entry 4793 (class 0 OID 0)
-- Dependencies: 288
-- Name: tblpass_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblpass_id_seq OWNED BY public.tblpass.id;


--
-- TOC entry 259 (class 1259 OID 1893758)
-- Name: twrmdb_master_cdat; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.twrmdb_master_cdat OWNER TO postgres;

--
-- TOC entry 296 (class 1259 OID 1894570)
-- Name: upload_activity_logs; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.upload_activity_logs OWNER TO postgres;

--
-- TOC entry 295 (class 1259 OID 1894569)
-- Name: upload_activity_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.upload_activity_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.upload_activity_logs_id_seq OWNER TO postgres;

--
-- TOC entry 4797 (class 0 OID 0)
-- Dependencies: 295
-- Name: upload_activity_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.upload_activity_logs_id_seq OWNED BY public.upload_activity_logs.id;


--
-- TOC entry 312 (class 1259 OID 2153748)
-- Name: upload_approval_queue; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.upload_approval_queue OWNER TO postgres;

--
-- TOC entry 311 (class 1259 OID 2153747)
-- Name: upload_approval_queue_queue_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.upload_approval_queue_queue_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.upload_approval_queue_queue_id_seq OWNER TO postgres;

--
-- TOC entry 4800 (class 0 OID 0)
-- Dependencies: 311
-- Name: upload_approval_queue_queue_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.upload_approval_queue_queue_id_seq OWNED BY public.upload_approval_queue.queue_id;


--
-- TOC entry 306 (class 1259 OID 2153673)
-- Name: upload_staging_batches; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.upload_staging_batches OWNER TO postgres;

--
-- TOC entry 305 (class 1259 OID 2153672)
-- Name: upload_staging_batches_batch_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.upload_staging_batches_batch_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.upload_staging_batches_batch_id_seq OWNER TO postgres;

--
-- TOC entry 4803 (class 0 OID 0)
-- Dependencies: 305
-- Name: upload_staging_batches_batch_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.upload_staging_batches_batch_id_seq OWNED BY public.upload_staging_batches.batch_id;


--
-- TOC entry 294 (class 1259 OID 1894558)
-- Name: user_activity_logs; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.user_activity_logs OWNER TO postgres;

--
-- TOC entry 293 (class 1259 OID 1894557)
-- Name: user_activity_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_activity_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_activity_logs_id_seq OWNER TO postgres;

--
-- TOC entry 4806 (class 0 OID 0)
-- Dependencies: 293
-- Name: user_activity_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_activity_logs_id_seq OWNED BY public.user_activity_logs.id;


--
-- TOC entry 292 (class 1259 OID 1894548)
-- Name: user_sessions; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.user_sessions OWNER TO postgres;

--
-- TOC entry 291 (class 1259 OID 1894547)
-- Name: user_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_sessions_id_seq OWNER TO postgres;

--
-- TOC entry 4809 (class 0 OID 0)
-- Dependencies: 291
-- Name: user_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_sessions_id_seq OWNED BY public.user_sessions.id;


--
-- TOC entry 332 (class 1259 OID 2183997)
-- Name: stg_cdr_102; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_102 OWNER TO postgres;

--
-- TOC entry 331 (class 1259 OID 2183996)
-- Name: stg_cdr_102_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_102_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_102_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4811 (class 0 OID 0)
-- Dependencies: 331
-- Name: stg_cdr_102_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_102_staging_row_id_seq OWNED BY upload_staging.stg_cdr_102.staging_row_id;


--
-- TOC entry 334 (class 1259 OID 2184011)
-- Name: stg_cdr_104; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_104 OWNER TO postgres;

--
-- TOC entry 333 (class 1259 OID 2184010)
-- Name: stg_cdr_104_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_104_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_104_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4812 (class 0 OID 0)
-- Dependencies: 333
-- Name: stg_cdr_104_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_104_staging_row_id_seq OWNED BY upload_staging.stg_cdr_104.staging_row_id;


--
-- TOC entry 336 (class 1259 OID 2184039)
-- Name: stg_cdr_106; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_106 OWNER TO postgres;

--
-- TOC entry 335 (class 1259 OID 2184038)
-- Name: stg_cdr_106_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_106_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_106_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4813 (class 0 OID 0)
-- Dependencies: 335
-- Name: stg_cdr_106_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_106_staging_row_id_seq OWNED BY upload_staging.stg_cdr_106.staging_row_id;


--
-- TOC entry 338 (class 1259 OID 2184054)
-- Name: stg_cdr_109; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_109 OWNER TO postgres;

--
-- TOC entry 337 (class 1259 OID 2184053)
-- Name: stg_cdr_109_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_109_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_109_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4814 (class 0 OID 0)
-- Dependencies: 337
-- Name: stg_cdr_109_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_109_staging_row_id_seq OWNED BY upload_staging.stg_cdr_109.staging_row_id;


--
-- TOC entry 340 (class 1259 OID 2184070)
-- Name: stg_cdr_112; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_112 OWNER TO postgres;

--
-- TOC entry 339 (class 1259 OID 2184069)
-- Name: stg_cdr_112_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_112_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_112_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4815 (class 0 OID 0)
-- Dependencies: 339
-- Name: stg_cdr_112_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_112_staging_row_id_seq OWNED BY upload_staging.stg_cdr_112.staging_row_id;


--
-- TOC entry 342 (class 1259 OID 2184084)
-- Name: stg_cdr_114; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_114 OWNER TO postgres;

--
-- TOC entry 341 (class 1259 OID 2184083)
-- Name: stg_cdr_114_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_114_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_114_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4816 (class 0 OID 0)
-- Dependencies: 341
-- Name: stg_cdr_114_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_114_staging_row_id_seq OWNED BY upload_staging.stg_cdr_114.staging_row_id;


--
-- TOC entry 344 (class 1259 OID 2184096)
-- Name: stg_cdr_115; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_115 OWNER TO postgres;

--
-- TOC entry 343 (class 1259 OID 2184095)
-- Name: stg_cdr_115_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_115_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_115_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4817 (class 0 OID 0)
-- Dependencies: 343
-- Name: stg_cdr_115_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_115_staging_row_id_seq OWNED BY upload_staging.stg_cdr_115.staging_row_id;


--
-- TOC entry 346 (class 1259 OID 2184129)
-- Name: stg_cdr_118; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_118 OWNER TO postgres;

--
-- TOC entry 345 (class 1259 OID 2184128)
-- Name: stg_cdr_118_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_118_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_118_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4818 (class 0 OID 0)
-- Dependencies: 345
-- Name: stg_cdr_118_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_118_staging_row_id_seq OWNED BY upload_staging.stg_cdr_118.staging_row_id;


--
-- TOC entry 348 (class 1259 OID 2184143)
-- Name: stg_cdr_120; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_120 OWNER TO postgres;

--
-- TOC entry 347 (class 1259 OID 2184142)
-- Name: stg_cdr_120_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_120_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_120_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4819 (class 0 OID 0)
-- Dependencies: 347
-- Name: stg_cdr_120_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_120_staging_row_id_seq OWNED BY upload_staging.stg_cdr_120.staging_row_id;


--
-- TOC entry 350 (class 1259 OID 2184157)
-- Name: stg_cdr_122; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_122 OWNER TO postgres;

--
-- TOC entry 349 (class 1259 OID 2184156)
-- Name: stg_cdr_122_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_122_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_122_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4820 (class 0 OID 0)
-- Dependencies: 349
-- Name: stg_cdr_122_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_122_staging_row_id_seq OWNED BY upload_staging.stg_cdr_122.staging_row_id;


--
-- TOC entry 352 (class 1259 OID 2184172)
-- Name: stg_cdr_125; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_125 OWNER TO postgres;

--
-- TOC entry 351 (class 1259 OID 2184171)
-- Name: stg_cdr_125_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_125_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_125_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4821 (class 0 OID 0)
-- Dependencies: 351
-- Name: stg_cdr_125_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_125_staging_row_id_seq OWNED BY upload_staging.stg_cdr_125.staging_row_id;


--
-- TOC entry 354 (class 1259 OID 2184184)
-- Name: stg_cdr_126; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_126 OWNER TO postgres;

--
-- TOC entry 353 (class 1259 OID 2184183)
-- Name: stg_cdr_126_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_126_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_126_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4822 (class 0 OID 0)
-- Dependencies: 353
-- Name: stg_cdr_126_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_126_staging_row_id_seq OWNED BY upload_staging.stg_cdr_126.staging_row_id;


--
-- TOC entry 356 (class 1259 OID 2184197)
-- Name: stg_cdr_129; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_129 OWNER TO postgres;

--
-- TOC entry 355 (class 1259 OID 2184196)
-- Name: stg_cdr_129_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_129_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_129_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4823 (class 0 OID 0)
-- Dependencies: 355
-- Name: stg_cdr_129_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_129_staging_row_id_seq OWNED BY upload_staging.stg_cdr_129.staging_row_id;


--
-- TOC entry 358 (class 1259 OID 2184210)
-- Name: stg_cdr_131; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_131 OWNER TO postgres;

--
-- TOC entry 357 (class 1259 OID 2184209)
-- Name: stg_cdr_131_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_131_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_131_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4824 (class 0 OID 0)
-- Dependencies: 357
-- Name: stg_cdr_131_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_131_staging_row_id_seq OWNED BY upload_staging.stg_cdr_131.staging_row_id;


--
-- TOC entry 360 (class 1259 OID 2184234)
-- Name: stg_cdr_133; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_133 OWNER TO postgres;

--
-- TOC entry 359 (class 1259 OID 2184233)
-- Name: stg_cdr_133_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_133_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_133_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4825 (class 0 OID 0)
-- Dependencies: 359
-- Name: stg_cdr_133_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_133_staging_row_id_seq OWNED BY upload_staging.stg_cdr_133.staging_row_id;


--
-- TOC entry 362 (class 1259 OID 2184253)
-- Name: stg_cdr_135; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_135 OWNER TO postgres;

--
-- TOC entry 361 (class 1259 OID 2184252)
-- Name: stg_cdr_135_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_135_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_135_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4826 (class 0 OID 0)
-- Dependencies: 361
-- Name: stg_cdr_135_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_135_staging_row_id_seq OWNED BY upload_staging.stg_cdr_135.staging_row_id;


--
-- TOC entry 364 (class 1259 OID 2184265)
-- Name: stg_cdr_136; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_136 OWNER TO postgres;

--
-- TOC entry 363 (class 1259 OID 2184264)
-- Name: stg_cdr_136_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_136_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_136_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4827 (class 0 OID 0)
-- Dependencies: 363
-- Name: stg_cdr_136_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_136_staging_row_id_seq OWNED BY upload_staging.stg_cdr_136.staging_row_id;


--
-- TOC entry 366 (class 1259 OID 2184290)
-- Name: stg_cdr_139; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_139 OWNER TO postgres;

--
-- TOC entry 365 (class 1259 OID 2184289)
-- Name: stg_cdr_139_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_139_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_139_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4828 (class 0 OID 0)
-- Dependencies: 365
-- Name: stg_cdr_139_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_139_staging_row_id_seq OWNED BY upload_staging.stg_cdr_139.staging_row_id;


--
-- TOC entry 368 (class 1259 OID 2184302)
-- Name: stg_cdr_140; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_140 OWNER TO postgres;

--
-- TOC entry 367 (class 1259 OID 2184301)
-- Name: stg_cdr_140_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_140_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_140_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4829 (class 0 OID 0)
-- Dependencies: 367
-- Name: stg_cdr_140_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_140_staging_row_id_seq OWNED BY upload_staging.stg_cdr_140.staging_row_id;


--
-- TOC entry 370 (class 1259 OID 2184315)
-- Name: stg_cdr_143; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_143 OWNER TO postgres;

--
-- TOC entry 369 (class 1259 OID 2184314)
-- Name: stg_cdr_143_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_143_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_143_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4830 (class 0 OID 0)
-- Dependencies: 369
-- Name: stg_cdr_143_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_143_staging_row_id_seq OWNED BY upload_staging.stg_cdr_143.staging_row_id;


--
-- TOC entry 372 (class 1259 OID 2184329)
-- Name: stg_cdr_145; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_145 OWNER TO postgres;

--
-- TOC entry 371 (class 1259 OID 2184328)
-- Name: stg_cdr_145_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_145_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_145_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4831 (class 0 OID 0)
-- Dependencies: 371
-- Name: stg_cdr_145_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_145_staging_row_id_seq OWNED BY upload_staging.stg_cdr_145.staging_row_id;


--
-- TOC entry 374 (class 1259 OID 2184341)
-- Name: stg_cdr_146; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_146 OWNER TO postgres;

--
-- TOC entry 373 (class 1259 OID 2184340)
-- Name: stg_cdr_146_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_146_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_146_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4832 (class 0 OID 0)
-- Dependencies: 373
-- Name: stg_cdr_146_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_146_staging_row_id_seq OWNED BY upload_staging.stg_cdr_146.staging_row_id;


--
-- TOC entry 376 (class 1259 OID 2184354)
-- Name: stg_cdr_149; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_149 OWNER TO postgres;

--
-- TOC entry 375 (class 1259 OID 2184353)
-- Name: stg_cdr_149_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_149_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_149_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4833 (class 0 OID 0)
-- Dependencies: 375
-- Name: stg_cdr_149_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_149_staging_row_id_seq OWNED BY upload_staging.stg_cdr_149.staging_row_id;


--
-- TOC entry 378 (class 1259 OID 2184389)
-- Name: stg_cdr_151; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_151 OWNER TO postgres;

--
-- TOC entry 377 (class 1259 OID 2184388)
-- Name: stg_cdr_151_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_151_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_151_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4834 (class 0 OID 0)
-- Dependencies: 377
-- Name: stg_cdr_151_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_151_staging_row_id_seq OWNED BY upload_staging.stg_cdr_151.staging_row_id;


--
-- TOC entry 380 (class 1259 OID 2184401)
-- Name: stg_cdr_152; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_152 OWNER TO postgres;

--
-- TOC entry 379 (class 1259 OID 2184400)
-- Name: stg_cdr_152_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_152_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_152_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4835 (class 0 OID 0)
-- Dependencies: 379
-- Name: stg_cdr_152_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_152_staging_row_id_seq OWNED BY upload_staging.stg_cdr_152.staging_row_id;


--
-- TOC entry 382 (class 1259 OID 2184415)
-- Name: stg_cdr_155; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_155 OWNER TO postgres;

--
-- TOC entry 381 (class 1259 OID 2184414)
-- Name: stg_cdr_155_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_155_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_155_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4836 (class 0 OID 0)
-- Dependencies: 381
-- Name: stg_cdr_155_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_155_staging_row_id_seq OWNED BY upload_staging.stg_cdr_155.staging_row_id;


--
-- TOC entry 384 (class 1259 OID 2184428)
-- Name: stg_cdr_156; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_156 OWNER TO postgres;

--
-- TOC entry 383 (class 1259 OID 2184427)
-- Name: stg_cdr_156_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_156_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_156_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4837 (class 0 OID 0)
-- Dependencies: 383
-- Name: stg_cdr_156_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_156_staging_row_id_seq OWNED BY upload_staging.stg_cdr_156.staging_row_id;


--
-- TOC entry 386 (class 1259 OID 2184441)
-- Name: stg_cdr_158; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_158 OWNER TO postgres;

--
-- TOC entry 385 (class 1259 OID 2184440)
-- Name: stg_cdr_158_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_158_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_158_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4838 (class 0 OID 0)
-- Dependencies: 385
-- Name: stg_cdr_158_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_158_staging_row_id_seq OWNED BY upload_staging.stg_cdr_158.staging_row_id;


--
-- TOC entry 388 (class 1259 OID 2184455)
-- Name: stg_cdr_161; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_161 OWNER TO postgres;

--
-- TOC entry 387 (class 1259 OID 2184454)
-- Name: stg_cdr_161_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_161_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_161_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4839 (class 0 OID 0)
-- Dependencies: 387
-- Name: stg_cdr_161_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_161_staging_row_id_seq OWNED BY upload_staging.stg_cdr_161.staging_row_id;


--
-- TOC entry 390 (class 1259 OID 2184467)
-- Name: stg_cdr_162; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_162 OWNER TO postgres;

--
-- TOC entry 389 (class 1259 OID 2184466)
-- Name: stg_cdr_162_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_162_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_162_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4840 (class 0 OID 0)
-- Dependencies: 389
-- Name: stg_cdr_162_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_162_staging_row_id_seq OWNED BY upload_staging.stg_cdr_162.staging_row_id;


--
-- TOC entry 392 (class 1259 OID 2184481)
-- Name: stg_cdr_165; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_165 OWNER TO postgres;

--
-- TOC entry 391 (class 1259 OID 2184480)
-- Name: stg_cdr_165_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_165_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_165_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4841 (class 0 OID 0)
-- Dependencies: 391
-- Name: stg_cdr_165_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_165_staging_row_id_seq OWNED BY upload_staging.stg_cdr_165.staging_row_id;


--
-- TOC entry 394 (class 1259 OID 2184493)
-- Name: stg_cdr_166; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_166 OWNER TO postgres;

--
-- TOC entry 393 (class 1259 OID 2184492)
-- Name: stg_cdr_166_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_166_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_166_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4842 (class 0 OID 0)
-- Dependencies: 393
-- Name: stg_cdr_166_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_166_staging_row_id_seq OWNED BY upload_staging.stg_cdr_166.staging_row_id;


--
-- TOC entry 396 (class 1259 OID 2184506)
-- Name: stg_cdr_169; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_169 OWNER TO postgres;

--
-- TOC entry 395 (class 1259 OID 2184505)
-- Name: stg_cdr_169_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_169_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_169_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4843 (class 0 OID 0)
-- Dependencies: 395
-- Name: stg_cdr_169_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_169_staging_row_id_seq OWNED BY upload_staging.stg_cdr_169.staging_row_id;


--
-- TOC entry 398 (class 1259 OID 2184520)
-- Name: stg_cdr_171; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_171 OWNER TO postgres;

--
-- TOC entry 397 (class 1259 OID 2184519)
-- Name: stg_cdr_171_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_171_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_171_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4844 (class 0 OID 0)
-- Dependencies: 397
-- Name: stg_cdr_171_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_171_staging_row_id_seq OWNED BY upload_staging.stg_cdr_171.staging_row_id;


--
-- TOC entry 400 (class 1259 OID 2184532)
-- Name: stg_cdr_172; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_172 OWNER TO postgres;

--
-- TOC entry 399 (class 1259 OID 2184531)
-- Name: stg_cdr_172_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_172_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_172_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4845 (class 0 OID 0)
-- Dependencies: 399
-- Name: stg_cdr_172_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_172_staging_row_id_seq OWNED BY upload_staging.stg_cdr_172.staging_row_id;


--
-- TOC entry 402 (class 1259 OID 2184545)
-- Name: stg_cdr_175; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_175 OWNER TO postgres;

--
-- TOC entry 401 (class 1259 OID 2184544)
-- Name: stg_cdr_175_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_175_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_175_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4846 (class 0 OID 0)
-- Dependencies: 401
-- Name: stg_cdr_175_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_175_staging_row_id_seq OWNED BY upload_staging.stg_cdr_175.staging_row_id;


--
-- TOC entry 404 (class 1259 OID 2184559)
-- Name: stg_cdr_177; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_177 OWNER TO postgres;

--
-- TOC entry 403 (class 1259 OID 2184558)
-- Name: stg_cdr_177_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_177_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_177_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4847 (class 0 OID 0)
-- Dependencies: 403
-- Name: stg_cdr_177_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_177_staging_row_id_seq OWNED BY upload_staging.stg_cdr_177.staging_row_id;


--
-- TOC entry 406 (class 1259 OID 2184571)
-- Name: stg_cdr_178; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_178 OWNER TO postgres;

--
-- TOC entry 405 (class 1259 OID 2184570)
-- Name: stg_cdr_178_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_178_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_178_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4848 (class 0 OID 0)
-- Dependencies: 405
-- Name: stg_cdr_178_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_178_staging_row_id_seq OWNED BY upload_staging.stg_cdr_178.staging_row_id;


--
-- TOC entry 408 (class 1259 OID 2184586)
-- Name: stg_cdr_182; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_182 OWNER TO postgres;

--
-- TOC entry 407 (class 1259 OID 2184585)
-- Name: stg_cdr_182_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_182_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_182_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4849 (class 0 OID 0)
-- Dependencies: 407
-- Name: stg_cdr_182_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_182_staging_row_id_seq OWNED BY upload_staging.stg_cdr_182.staging_row_id;


--
-- TOC entry 410 (class 1259 OID 2184598)
-- Name: stg_cdr_183; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_183 OWNER TO postgres;

--
-- TOC entry 409 (class 1259 OID 2184597)
-- Name: stg_cdr_183_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_183_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_183_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4850 (class 0 OID 0)
-- Dependencies: 409
-- Name: stg_cdr_183_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_183_staging_row_id_seq OWNED BY upload_staging.stg_cdr_183.staging_row_id;


--
-- TOC entry 412 (class 1259 OID 2184611)
-- Name: stg_cdr_186; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_186 OWNER TO postgres;

--
-- TOC entry 411 (class 1259 OID 2184610)
-- Name: stg_cdr_186_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_186_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_186_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4851 (class 0 OID 0)
-- Dependencies: 411
-- Name: stg_cdr_186_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_186_staging_row_id_seq OWNED BY upload_staging.stg_cdr_186.staging_row_id;


--
-- TOC entry 414 (class 1259 OID 2184675)
-- Name: stg_cdr_188; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_188 OWNER TO postgres;

--
-- TOC entry 413 (class 1259 OID 2184674)
-- Name: stg_cdr_188_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_188_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_188_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4852 (class 0 OID 0)
-- Dependencies: 413
-- Name: stg_cdr_188_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_188_staging_row_id_seq OWNED BY upload_staging.stg_cdr_188.staging_row_id;


--
-- TOC entry 416 (class 1259 OID 2184687)
-- Name: stg_cdr_189; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_189 OWNER TO postgres;

--
-- TOC entry 415 (class 1259 OID 2184686)
-- Name: stg_cdr_189_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_189_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_189_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4853 (class 0 OID 0)
-- Dependencies: 415
-- Name: stg_cdr_189_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_189_staging_row_id_seq OWNED BY upload_staging.stg_cdr_189.staging_row_id;


--
-- TOC entry 418 (class 1259 OID 2184701)
-- Name: stg_cdr_192; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_192 OWNER TO postgres;

--
-- TOC entry 417 (class 1259 OID 2184700)
-- Name: stg_cdr_192_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_192_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_192_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4854 (class 0 OID 0)
-- Dependencies: 417
-- Name: stg_cdr_192_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_192_staging_row_id_seq OWNED BY upload_staging.stg_cdr_192.staging_row_id;


--
-- TOC entry 420 (class 1259 OID 2184713)
-- Name: stg_cdr_193; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_193 OWNER TO postgres;

--
-- TOC entry 419 (class 1259 OID 2184712)
-- Name: stg_cdr_193_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_193_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_193_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4855 (class 0 OID 0)
-- Dependencies: 419
-- Name: stg_cdr_193_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_193_staging_row_id_seq OWNED BY upload_staging.stg_cdr_193.staging_row_id;


--
-- TOC entry 422 (class 1259 OID 2184727)
-- Name: stg_cdr_196; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_196 OWNER TO postgres;

--
-- TOC entry 421 (class 1259 OID 2184726)
-- Name: stg_cdr_196_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_196_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_196_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4856 (class 0 OID 0)
-- Dependencies: 421
-- Name: stg_cdr_196_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_196_staging_row_id_seq OWNED BY upload_staging.stg_cdr_196.staging_row_id;


--
-- TOC entry 424 (class 1259 OID 2184739)
-- Name: stg_cdr_197; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_197 OWNER TO postgres;

--
-- TOC entry 423 (class 1259 OID 2184738)
-- Name: stg_cdr_197_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_197_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_197_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4857 (class 0 OID 0)
-- Dependencies: 423
-- Name: stg_cdr_197_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_197_staging_row_id_seq OWNED BY upload_staging.stg_cdr_197.staging_row_id;


--
-- TOC entry 426 (class 1259 OID 2184753)
-- Name: stg_cdr_200; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_200 OWNER TO postgres;

--
-- TOC entry 425 (class 1259 OID 2184752)
-- Name: stg_cdr_200_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_200_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_200_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4858 (class 0 OID 0)
-- Dependencies: 425
-- Name: stg_cdr_200_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_200_staging_row_id_seq OWNED BY upload_staging.stg_cdr_200.staging_row_id;


--
-- TOC entry 428 (class 1259 OID 2184767)
-- Name: stg_cdr_203; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_203 OWNER TO postgres;

--
-- TOC entry 427 (class 1259 OID 2184766)
-- Name: stg_cdr_203_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_203_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_203_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4859 (class 0 OID 0)
-- Dependencies: 427
-- Name: stg_cdr_203_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_203_staging_row_id_seq OWNED BY upload_staging.stg_cdr_203.staging_row_id;


--
-- TOC entry 430 (class 1259 OID 2184779)
-- Name: stg_cdr_204; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_204 OWNER TO postgres;

--
-- TOC entry 429 (class 1259 OID 2184778)
-- Name: stg_cdr_204_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_204_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_204_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4860 (class 0 OID 0)
-- Dependencies: 429
-- Name: stg_cdr_204_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_204_staging_row_id_seq OWNED BY upload_staging.stg_cdr_204.staging_row_id;


--
-- TOC entry 432 (class 1259 OID 2184793)
-- Name: stg_cdr_207; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_207 OWNER TO postgres;

--
-- TOC entry 431 (class 1259 OID 2184792)
-- Name: stg_cdr_207_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_207_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_207_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4861 (class 0 OID 0)
-- Dependencies: 431
-- Name: stg_cdr_207_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_207_staging_row_id_seq OWNED BY upload_staging.stg_cdr_207.staging_row_id;


--
-- TOC entry 434 (class 1259 OID 2184805)
-- Name: stg_cdr_208; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_208 OWNER TO postgres;

--
-- TOC entry 433 (class 1259 OID 2184804)
-- Name: stg_cdr_208_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_208_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_208_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4862 (class 0 OID 0)
-- Dependencies: 433
-- Name: stg_cdr_208_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_208_staging_row_id_seq OWNED BY upload_staging.stg_cdr_208.staging_row_id;


--
-- TOC entry 436 (class 1259 OID 2184819)
-- Name: stg_cdr_211; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_211 OWNER TO postgres;

--
-- TOC entry 435 (class 1259 OID 2184818)
-- Name: stg_cdr_211_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_211_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_211_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4863 (class 0 OID 0)
-- Dependencies: 435
-- Name: stg_cdr_211_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_211_staging_row_id_seq OWNED BY upload_staging.stg_cdr_211.staging_row_id;


--
-- TOC entry 438 (class 1259 OID 2184845)
-- Name: stg_cdr_216; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_216 OWNER TO postgres;

--
-- TOC entry 437 (class 1259 OID 2184844)
-- Name: stg_cdr_216_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_216_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_216_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4864 (class 0 OID 0)
-- Dependencies: 437
-- Name: stg_cdr_216_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_216_staging_row_id_seq OWNED BY upload_staging.stg_cdr_216.staging_row_id;


--
-- TOC entry 440 (class 1259 OID 2184859)
-- Name: stg_cdr_218; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_218 OWNER TO postgres;

--
-- TOC entry 439 (class 1259 OID 2184858)
-- Name: stg_cdr_218_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_218_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_218_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4865 (class 0 OID 0)
-- Dependencies: 439
-- Name: stg_cdr_218_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_218_staging_row_id_seq OWNED BY upload_staging.stg_cdr_218.staging_row_id;


--
-- TOC entry 442 (class 1259 OID 2184871)
-- Name: stg_cdr_219; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_219 OWNER TO postgres;

--
-- TOC entry 441 (class 1259 OID 2184870)
-- Name: stg_cdr_219_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_219_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_219_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4866 (class 0 OID 0)
-- Dependencies: 441
-- Name: stg_cdr_219_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_219_staging_row_id_seq OWNED BY upload_staging.stg_cdr_219.staging_row_id;


--
-- TOC entry 444 (class 1259 OID 2184894)
-- Name: stg_cdr_222; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_222 OWNER TO postgres;

--
-- TOC entry 443 (class 1259 OID 2184893)
-- Name: stg_cdr_222_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_222_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_222_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4867 (class 0 OID 0)
-- Dependencies: 443
-- Name: stg_cdr_222_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_222_staging_row_id_seq OWNED BY upload_staging.stg_cdr_222.staging_row_id;


--
-- TOC entry 446 (class 1259 OID 2184953)
-- Name: stg_cdr_226; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_226 OWNER TO postgres;

--
-- TOC entry 445 (class 1259 OID 2184952)
-- Name: stg_cdr_226_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_226_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_226_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4868 (class 0 OID 0)
-- Dependencies: 445
-- Name: stg_cdr_226_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_226_staging_row_id_seq OWNED BY upload_staging.stg_cdr_226.staging_row_id;


--
-- TOC entry 448 (class 1259 OID 2184972)
-- Name: stg_cdr_228; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_228 OWNER TO postgres;

--
-- TOC entry 447 (class 1259 OID 2184971)
-- Name: stg_cdr_228_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_228_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_228_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4869 (class 0 OID 0)
-- Dependencies: 447
-- Name: stg_cdr_228_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_228_staging_row_id_seq OWNED BY upload_staging.stg_cdr_228.staging_row_id;


--
-- TOC entry 450 (class 1259 OID 2185110)
-- Name: stg_cdr_232; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_232 OWNER TO postgres;

--
-- TOC entry 449 (class 1259 OID 2185109)
-- Name: stg_cdr_232_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_232_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_232_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4870 (class 0 OID 0)
-- Dependencies: 449
-- Name: stg_cdr_232_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_232_staging_row_id_seq OWNED BY upload_staging.stg_cdr_232.staging_row_id;


--
-- TOC entry 452 (class 1259 OID 2185209)
-- Name: stg_cdr_234; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_234 OWNER TO postgres;

--
-- TOC entry 451 (class 1259 OID 2185208)
-- Name: stg_cdr_234_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_234_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_234_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4871 (class 0 OID 0)
-- Dependencies: 451
-- Name: stg_cdr_234_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_234_staging_row_id_seq OWNED BY upload_staging.stg_cdr_234.staging_row_id;


--
-- TOC entry 454 (class 1259 OID 2185221)
-- Name: stg_cdr_235; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_235 OWNER TO postgres;

--
-- TOC entry 453 (class 1259 OID 2185220)
-- Name: stg_cdr_235_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_235_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_235_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4872 (class 0 OID 0)
-- Dependencies: 453
-- Name: stg_cdr_235_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_235_staging_row_id_seq OWNED BY upload_staging.stg_cdr_235.staging_row_id;


--
-- TOC entry 456 (class 1259 OID 2185235)
-- Name: stg_cdr_238; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_238 OWNER TO postgres;

--
-- TOC entry 455 (class 1259 OID 2185234)
-- Name: stg_cdr_238_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_238_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_238_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4873 (class 0 OID 0)
-- Dependencies: 455
-- Name: stg_cdr_238_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_238_staging_row_id_seq OWNED BY upload_staging.stg_cdr_238.staging_row_id;


--
-- TOC entry 458 (class 1259 OID 2185247)
-- Name: stg_cdr_239; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_239 OWNER TO postgres;

--
-- TOC entry 457 (class 1259 OID 2185246)
-- Name: stg_cdr_239_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_239_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_239_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4874 (class 0 OID 0)
-- Dependencies: 457
-- Name: stg_cdr_239_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_239_staging_row_id_seq OWNED BY upload_staging.stg_cdr_239.staging_row_id;


--
-- TOC entry 460 (class 1259 OID 2185261)
-- Name: stg_cdr_242; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_242 OWNER TO postgres;

--
-- TOC entry 459 (class 1259 OID 2185260)
-- Name: stg_cdr_242_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_242_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_242_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4875 (class 0 OID 0)
-- Dependencies: 459
-- Name: stg_cdr_242_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_242_staging_row_id_seq OWNED BY upload_staging.stg_cdr_242.staging_row_id;


--
-- TOC entry 462 (class 1259 OID 2185273)
-- Name: stg_cdr_243; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_243 OWNER TO postgres;

--
-- TOC entry 461 (class 1259 OID 2185272)
-- Name: stg_cdr_243_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_243_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_243_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4876 (class 0 OID 0)
-- Dependencies: 461
-- Name: stg_cdr_243_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_243_staging_row_id_seq OWNED BY upload_staging.stg_cdr_243.staging_row_id;


--
-- TOC entry 464 (class 1259 OID 2185287)
-- Name: stg_cdr_246; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_246 OWNER TO postgres;

--
-- TOC entry 463 (class 1259 OID 2185286)
-- Name: stg_cdr_246_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_246_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_246_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4877 (class 0 OID 0)
-- Dependencies: 463
-- Name: stg_cdr_246_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_246_staging_row_id_seq OWNED BY upload_staging.stg_cdr_246.staging_row_id;


--
-- TOC entry 466 (class 1259 OID 2185301)
-- Name: stg_cdr_248; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_248 OWNER TO postgres;

--
-- TOC entry 465 (class 1259 OID 2185300)
-- Name: stg_cdr_248_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_248_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_248_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4878 (class 0 OID 0)
-- Dependencies: 465
-- Name: stg_cdr_248_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_248_staging_row_id_seq OWNED BY upload_staging.stg_cdr_248.staging_row_id;


--
-- TOC entry 468 (class 1259 OID 2185313)
-- Name: stg_cdr_249; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_249 OWNER TO postgres;

--
-- TOC entry 467 (class 1259 OID 2185312)
-- Name: stg_cdr_249_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_249_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_249_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4879 (class 0 OID 0)
-- Dependencies: 467
-- Name: stg_cdr_249_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_249_staging_row_id_seq OWNED BY upload_staging.stg_cdr_249.staging_row_id;


--
-- TOC entry 470 (class 1259 OID 2185326)
-- Name: stg_cdr_252; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_252 OWNER TO postgres;

--
-- TOC entry 469 (class 1259 OID 2185325)
-- Name: stg_cdr_252_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_252_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_252_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4880 (class 0 OID 0)
-- Dependencies: 469
-- Name: stg_cdr_252_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_252_staging_row_id_seq OWNED BY upload_staging.stg_cdr_252.staging_row_id;


--
-- TOC entry 472 (class 1259 OID 2185340)
-- Name: stg_cdr_254; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_254 OWNER TO postgres;

--
-- TOC entry 471 (class 1259 OID 2185339)
-- Name: stg_cdr_254_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_254_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_254_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4881 (class 0 OID 0)
-- Dependencies: 471
-- Name: stg_cdr_254_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_254_staging_row_id_seq OWNED BY upload_staging.stg_cdr_254.staging_row_id;


--
-- TOC entry 474 (class 1259 OID 2185352)
-- Name: stg_cdr_255; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_255 OWNER TO postgres;

--
-- TOC entry 473 (class 1259 OID 2185351)
-- Name: stg_cdr_255_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_255_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_255_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4882 (class 0 OID 0)
-- Dependencies: 473
-- Name: stg_cdr_255_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_255_staging_row_id_seq OWNED BY upload_staging.stg_cdr_255.staging_row_id;


--
-- TOC entry 476 (class 1259 OID 2185366)
-- Name: stg_cdr_258; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_258 OWNER TO postgres;

--
-- TOC entry 475 (class 1259 OID 2185365)
-- Name: stg_cdr_258_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_258_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_258_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4883 (class 0 OID 0)
-- Dependencies: 475
-- Name: stg_cdr_258_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_258_staging_row_id_seq OWNED BY upload_staging.stg_cdr_258.staging_row_id;


--
-- TOC entry 478 (class 1259 OID 2185380)
-- Name: stg_cdr_261; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_261 OWNER TO postgres;

--
-- TOC entry 477 (class 1259 OID 2185379)
-- Name: stg_cdr_261_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_261_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_261_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4884 (class 0 OID 0)
-- Dependencies: 477
-- Name: stg_cdr_261_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_261_staging_row_id_seq OWNED BY upload_staging.stg_cdr_261.staging_row_id;


--
-- TOC entry 480 (class 1259 OID 2185467)
-- Name: stg_cdr_268; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_268 OWNER TO postgres;

--
-- TOC entry 479 (class 1259 OID 2185466)
-- Name: stg_cdr_268_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_268_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_268_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4885 (class 0 OID 0)
-- Dependencies: 479
-- Name: stg_cdr_268_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_268_staging_row_id_seq OWNED BY upload_staging.stg_cdr_268.staging_row_id;


--
-- TOC entry 482 (class 1259 OID 2185486)
-- Name: stg_cdr_270; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_270 OWNER TO postgres;

--
-- TOC entry 481 (class 1259 OID 2185485)
-- Name: stg_cdr_270_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_270_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_270_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4886 (class 0 OID 0)
-- Dependencies: 481
-- Name: stg_cdr_270_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_270_staging_row_id_seq OWNED BY upload_staging.stg_cdr_270.staging_row_id;


--
-- TOC entry 484 (class 1259 OID 2185503)
-- Name: stg_cdr_273; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_273 OWNER TO postgres;

--
-- TOC entry 483 (class 1259 OID 2185502)
-- Name: stg_cdr_273_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_273_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_273_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4887 (class 0 OID 0)
-- Dependencies: 483
-- Name: stg_cdr_273_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_273_staging_row_id_seq OWNED BY upload_staging.stg_cdr_273.staging_row_id;


--
-- TOC entry 486 (class 1259 OID 2185531)
-- Name: stg_cdr_275; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_275 OWNER TO postgres;

--
-- TOC entry 485 (class 1259 OID 2185530)
-- Name: stg_cdr_275_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_275_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_275_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4888 (class 0 OID 0)
-- Dependencies: 485
-- Name: stg_cdr_275_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_275_staging_row_id_seq OWNED BY upload_staging.stg_cdr_275.staging_row_id;


--
-- TOC entry 488 (class 1259 OID 2185597)
-- Name: stg_cdr_279; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_279 OWNER TO postgres;

--
-- TOC entry 487 (class 1259 OID 2185596)
-- Name: stg_cdr_279_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_279_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_279_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4889 (class 0 OID 0)
-- Dependencies: 487
-- Name: stg_cdr_279_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_279_staging_row_id_seq OWNED BY upload_staging.stg_cdr_279.staging_row_id;


--
-- TOC entry 490 (class 1259 OID 2185611)
-- Name: stg_cdr_281; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_281 OWNER TO postgres;

--
-- TOC entry 489 (class 1259 OID 2185610)
-- Name: stg_cdr_281_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_281_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_281_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4890 (class 0 OID 0)
-- Dependencies: 489
-- Name: stg_cdr_281_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_281_staging_row_id_seq OWNED BY upload_staging.stg_cdr_281.staging_row_id;


--
-- TOC entry 492 (class 1259 OID 2185739)
-- Name: stg_cdr_293; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_293 OWNER TO postgres;

--
-- TOC entry 491 (class 1259 OID 2185738)
-- Name: stg_cdr_293_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_293_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_293_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4891 (class 0 OID 0)
-- Dependencies: 491
-- Name: stg_cdr_293_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_293_staging_row_id_seq OWNED BY upload_staging.stg_cdr_293.staging_row_id;


--
-- TOC entry 494 (class 1259 OID 2185893)
-- Name: stg_cdr_299; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_299 OWNER TO postgres;

--
-- TOC entry 493 (class 1259 OID 2185892)
-- Name: stg_cdr_299_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_299_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_299_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4892 (class 0 OID 0)
-- Dependencies: 493
-- Name: stg_cdr_299_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_299_staging_row_id_seq OWNED BY upload_staging.stg_cdr_299.staging_row_id;


--
-- TOC entry 496 (class 1259 OID 2187110)
-- Name: stg_cdr_319; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_319 OWNER TO postgres;

--
-- TOC entry 495 (class 1259 OID 2187109)
-- Name: stg_cdr_319_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_319_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_319_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4893 (class 0 OID 0)
-- Dependencies: 495
-- Name: stg_cdr_319_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_319_staging_row_id_seq OWNED BY upload_staging.stg_cdr_319.staging_row_id;


--
-- TOC entry 498 (class 1259 OID 2187482)
-- Name: stg_cdr_341; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_341 OWNER TO postgres;

--
-- TOC entry 497 (class 1259 OID 2187481)
-- Name: stg_cdr_341_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_341_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_341_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4894 (class 0 OID 0)
-- Dependencies: 497
-- Name: stg_cdr_341_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_341_staging_row_id_seq OWNED BY upload_staging.stg_cdr_341.staging_row_id;


--
-- TOC entry 500 (class 1259 OID 2187496)
-- Name: stg_cdr_343; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_343 OWNER TO postgres;

--
-- TOC entry 499 (class 1259 OID 2187495)
-- Name: stg_cdr_343_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_343_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_343_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4895 (class 0 OID 0)
-- Dependencies: 499
-- Name: stg_cdr_343_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_343_staging_row_id_seq OWNED BY upload_staging.stg_cdr_343.staging_row_id;


--
-- TOC entry 308 (class 1259 OID 2153701)
-- Name: stg_cdr_36; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_36 OWNER TO postgres;

--
-- TOC entry 307 (class 1259 OID 2153700)
-- Name: stg_cdr_36_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE SEQUENCE upload_staging.stg_cdr_36_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_36_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4896 (class 0 OID 0)
-- Dependencies: 307
-- Name: stg_cdr_36_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_36_staging_row_id_seq OWNED BY upload_staging.stg_cdr_36.staging_row_id;


--
-- TOC entry 314 (class 1259 OID 2173880)
-- Name: stg_cdr_38; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_38 OWNER TO postgres;

--
-- TOC entry 313 (class 1259 OID 2173879)
-- Name: stg_cdr_38_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_38_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_38_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4897 (class 0 OID 0)
-- Dependencies: 313
-- Name: stg_cdr_38_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_38_staging_row_id_seq OWNED BY upload_staging.stg_cdr_38.staging_row_id;


--
-- TOC entry 316 (class 1259 OID 2173896)
-- Name: stg_cdr_41; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_41 OWNER TO postgres;

--
-- TOC entry 315 (class 1259 OID 2173895)
-- Name: stg_cdr_41_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_41_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_41_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4898 (class 0 OID 0)
-- Dependencies: 315
-- Name: stg_cdr_41_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_41_staging_row_id_seq OWNED BY upload_staging.stg_cdr_41.staging_row_id;


--
-- TOC entry 318 (class 1259 OID 2173950)
-- Name: stg_cdr_43; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_43 OWNER TO postgres;

--
-- TOC entry 317 (class 1259 OID 2173949)
-- Name: stg_cdr_43_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_43_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_43_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4899 (class 0 OID 0)
-- Dependencies: 317
-- Name: stg_cdr_43_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_43_staging_row_id_seq OWNED BY upload_staging.stg_cdr_43.staging_row_id;


--
-- TOC entry 320 (class 1259 OID 2174629)
-- Name: stg_cdr_65; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_65 OWNER TO postgres;

--
-- TOC entry 319 (class 1259 OID 2174628)
-- Name: stg_cdr_65_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_65_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_65_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4900 (class 0 OID 0)
-- Dependencies: 319
-- Name: stg_cdr_65_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_65_staging_row_id_seq OWNED BY upload_staging.stg_cdr_65.staging_row_id;


--
-- TOC entry 322 (class 1259 OID 2174705)
-- Name: stg_cdr_71; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_71 OWNER TO postgres;

--
-- TOC entry 321 (class 1259 OID 2174704)
-- Name: stg_cdr_71_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_71_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_71_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4901 (class 0 OID 0)
-- Dependencies: 321
-- Name: stg_cdr_71_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_71_staging_row_id_seq OWNED BY upload_staging.stg_cdr_71.staging_row_id;


--
-- TOC entry 324 (class 1259 OID 2174722)
-- Name: stg_cdr_73; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_73 OWNER TO postgres;

--
-- TOC entry 323 (class 1259 OID 2174721)
-- Name: stg_cdr_73_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_73_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_73_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4902 (class 0 OID 0)
-- Dependencies: 323
-- Name: stg_cdr_73_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_73_staging_row_id_seq OWNED BY upload_staging.stg_cdr_73.staging_row_id;


--
-- TOC entry 326 (class 1259 OID 2174952)
-- Name: stg_cdr_87; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_87 OWNER TO postgres;

--
-- TOC entry 325 (class 1259 OID 2174951)
-- Name: stg_cdr_87_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_87_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_87_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4903 (class 0 OID 0)
-- Dependencies: 325
-- Name: stg_cdr_87_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_87_staging_row_id_seq OWNED BY upload_staging.stg_cdr_87.staging_row_id;


--
-- TOC entry 328 (class 1259 OID 2174965)
-- Name: stg_cdr_89; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_89 OWNER TO postgres;

--
-- TOC entry 327 (class 1259 OID 2174964)
-- Name: stg_cdr_89_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_89_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_89_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4904 (class 0 OID 0)
-- Dependencies: 327
-- Name: stg_cdr_89_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_89_staging_row_id_seq OWNED BY upload_staging.stg_cdr_89.staging_row_id;


--
-- TOC entry 330 (class 1259 OID 2183984)
-- Name: stg_cdr_99; Type: TABLE; Schema: upload_staging; Owner: postgres
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


ALTER TABLE upload_staging.stg_cdr_99 OWNER TO postgres;

--
-- TOC entry 329 (class 1259 OID 2183983)
-- Name: stg_cdr_99_staging_row_id_seq; Type: SEQUENCE; Schema: upload_staging; Owner: postgres
--

CREATE UNLOGGED SEQUENCE upload_staging.stg_cdr_99_staging_row_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE upload_staging.stg_cdr_99_staging_row_id_seq OWNER TO postgres;

--
-- TOC entry 4905 (class 0 OID 0)
-- Dependencies: 329
-- Name: stg_cdr_99_staging_row_id_seq; Type: SEQUENCE OWNED BY; Schema: upload_staging; Owner: postgres
--

ALTER SEQUENCE upload_staging.stg_cdr_99_staging_row_id_seq OWNED BY upload_staging.stg_cdr_99.staging_row_id;


--
-- TOC entry 4005 (class 2604 OID 1894451)
-- Name: cdatpcsuspect_staging staging_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cdatpcsuspect_staging ALTER COLUMN staging_id SET DEFAULT nextval('public.cdatpcsuspect_staging_staging_id_seq'::regclass);


--
-- TOC entry 4007 (class 2604 OID 1894470)
-- Name: document_jobs job_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_jobs ALTER COLUMN job_id SET DEFAULT nextval('public.document_jobs_job_id_seq'::regclass);


--
-- TOC entry 4004 (class 2604 OID 1894543)
-- Name: logins id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logins ALTER COLUMN id SET DEFAULT nextval('public.logins_id_seq'::regclass);


--
-- TOC entry 4017 (class 2604 OID 1894516)
-- Name: tbladmin id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tbladmin ALTER COLUMN id SET DEFAULT nextval('public.tbladmin_id_seq'::regclass);


--
-- TOC entry 4019 (class 2604 OID 1894526)
-- Name: tblcategory id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcategory ALTER COLUMN id SET DEFAULT nextval('public.tblcategory_id_seq'::regclass);


--
-- TOC entry 4021 (class 2604 OID 1894534)
-- Name: tblpass id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblpass ALTER COLUMN id SET DEFAULT nextval('public.tblpass_id_seq'::regclass);


--
-- TOC entry 4027 (class 2604 OID 1894573)
-- Name: upload_activity_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_activity_logs ALTER COLUMN id SET DEFAULT nextval('public.upload_activity_logs_id_seq'::regclass);


--
-- TOC entry 4040 (class 2604 OID 2153751)
-- Name: upload_approval_queue queue_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_approval_queue ALTER COLUMN queue_id SET DEFAULT nextval('public.upload_approval_queue_queue_id_seq'::regclass);


--
-- TOC entry 4033 (class 2604 OID 2153676)
-- Name: upload_staging_batches batch_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_staging_batches ALTER COLUMN batch_id SET DEFAULT nextval('public.upload_staging_batches_batch_id_seq'::regclass);


--
-- TOC entry 4025 (class 2604 OID 1894561)
-- Name: user_activity_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_activity_logs ALTER COLUMN id SET DEFAULT nextval('public.user_activity_logs_id_seq'::regclass);


--
-- TOC entry 4023 (class 2604 OID 1894551)
-- Name: user_sessions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_sessions ALTER COLUMN id SET DEFAULT nextval('public.user_sessions_id_seq'::regclass);


--
-- TOC entry 4071 (class 2604 OID 2184000)
-- Name: stg_cdr_102 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_102 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_102_staging_row_id_seq'::regclass);


--
-- TOC entry 4074 (class 2604 OID 2184014)
-- Name: stg_cdr_104 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_104 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_104_staging_row_id_seq'::regclass);


--
-- TOC entry 4077 (class 2604 OID 2184042)
-- Name: stg_cdr_106 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_106 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_106_staging_row_id_seq'::regclass);


--
-- TOC entry 4080 (class 2604 OID 2184057)
-- Name: stg_cdr_109 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_109 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_109_staging_row_id_seq'::regclass);


--
-- TOC entry 4083 (class 2604 OID 2184073)
-- Name: stg_cdr_112 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_112 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_112_staging_row_id_seq'::regclass);


--
-- TOC entry 4086 (class 2604 OID 2184087)
-- Name: stg_cdr_114 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_114 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_114_staging_row_id_seq'::regclass);


--
-- TOC entry 4089 (class 2604 OID 2184099)
-- Name: stg_cdr_115 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_115 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_115_staging_row_id_seq'::regclass);


--
-- TOC entry 4092 (class 2604 OID 2184132)
-- Name: stg_cdr_118 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_118 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_118_staging_row_id_seq'::regclass);


--
-- TOC entry 4095 (class 2604 OID 2184146)
-- Name: stg_cdr_120 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_120 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_120_staging_row_id_seq'::regclass);


--
-- TOC entry 4098 (class 2604 OID 2184160)
-- Name: stg_cdr_122 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_122 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_122_staging_row_id_seq'::regclass);


--
-- TOC entry 4101 (class 2604 OID 2184175)
-- Name: stg_cdr_125 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_125 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_125_staging_row_id_seq'::regclass);


--
-- TOC entry 4104 (class 2604 OID 2184187)
-- Name: stg_cdr_126 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_126 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_126_staging_row_id_seq'::regclass);


--
-- TOC entry 4107 (class 2604 OID 2184200)
-- Name: stg_cdr_129 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_129 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_129_staging_row_id_seq'::regclass);


--
-- TOC entry 4110 (class 2604 OID 2184213)
-- Name: stg_cdr_131 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_131 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_131_staging_row_id_seq'::regclass);


--
-- TOC entry 4113 (class 2604 OID 2184237)
-- Name: stg_cdr_133 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_133 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_133_staging_row_id_seq'::regclass);


--
-- TOC entry 4116 (class 2604 OID 2184256)
-- Name: stg_cdr_135 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_135 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_135_staging_row_id_seq'::regclass);


--
-- TOC entry 4119 (class 2604 OID 2184268)
-- Name: stg_cdr_136 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_136 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_136_staging_row_id_seq'::regclass);


--
-- TOC entry 4122 (class 2604 OID 2184293)
-- Name: stg_cdr_139 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_139 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_139_staging_row_id_seq'::regclass);


--
-- TOC entry 4125 (class 2604 OID 2184305)
-- Name: stg_cdr_140 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_140 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_140_staging_row_id_seq'::regclass);


--
-- TOC entry 4128 (class 2604 OID 2184318)
-- Name: stg_cdr_143 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_143 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_143_staging_row_id_seq'::regclass);


--
-- TOC entry 4131 (class 2604 OID 2184332)
-- Name: stg_cdr_145 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_145 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_145_staging_row_id_seq'::regclass);


--
-- TOC entry 4134 (class 2604 OID 2184344)
-- Name: stg_cdr_146 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_146 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_146_staging_row_id_seq'::regclass);


--
-- TOC entry 4137 (class 2604 OID 2184357)
-- Name: stg_cdr_149 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_149 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_149_staging_row_id_seq'::regclass);


--
-- TOC entry 4140 (class 2604 OID 2184392)
-- Name: stg_cdr_151 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_151 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_151_staging_row_id_seq'::regclass);


--
-- TOC entry 4143 (class 2604 OID 2184404)
-- Name: stg_cdr_152 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_152 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_152_staging_row_id_seq'::regclass);


--
-- TOC entry 4146 (class 2604 OID 2184418)
-- Name: stg_cdr_155 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_155 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_155_staging_row_id_seq'::regclass);


--
-- TOC entry 4149 (class 2604 OID 2184431)
-- Name: stg_cdr_156 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_156 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_156_staging_row_id_seq'::regclass);


--
-- TOC entry 4152 (class 2604 OID 2184444)
-- Name: stg_cdr_158 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_158 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_158_staging_row_id_seq'::regclass);


--
-- TOC entry 4155 (class 2604 OID 2184458)
-- Name: stg_cdr_161 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_161 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_161_staging_row_id_seq'::regclass);


--
-- TOC entry 4158 (class 2604 OID 2184470)
-- Name: stg_cdr_162 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_162 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_162_staging_row_id_seq'::regclass);


--
-- TOC entry 4161 (class 2604 OID 2184484)
-- Name: stg_cdr_165 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_165 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_165_staging_row_id_seq'::regclass);


--
-- TOC entry 4164 (class 2604 OID 2184496)
-- Name: stg_cdr_166 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_166 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_166_staging_row_id_seq'::regclass);


--
-- TOC entry 4167 (class 2604 OID 2184509)
-- Name: stg_cdr_169 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_169 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_169_staging_row_id_seq'::regclass);


--
-- TOC entry 4170 (class 2604 OID 2184523)
-- Name: stg_cdr_171 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_171 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_171_staging_row_id_seq'::regclass);


--
-- TOC entry 4173 (class 2604 OID 2184535)
-- Name: stg_cdr_172 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_172 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_172_staging_row_id_seq'::regclass);


--
-- TOC entry 4176 (class 2604 OID 2184548)
-- Name: stg_cdr_175 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_175 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_175_staging_row_id_seq'::regclass);


--
-- TOC entry 4179 (class 2604 OID 2184562)
-- Name: stg_cdr_177 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_177 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_177_staging_row_id_seq'::regclass);


--
-- TOC entry 4182 (class 2604 OID 2184574)
-- Name: stg_cdr_178 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_178 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_178_staging_row_id_seq'::regclass);


--
-- TOC entry 4185 (class 2604 OID 2184589)
-- Name: stg_cdr_182 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_182 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_182_staging_row_id_seq'::regclass);


--
-- TOC entry 4188 (class 2604 OID 2184601)
-- Name: stg_cdr_183 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_183 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_183_staging_row_id_seq'::regclass);


--
-- TOC entry 4191 (class 2604 OID 2184614)
-- Name: stg_cdr_186 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_186 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_186_staging_row_id_seq'::regclass);


--
-- TOC entry 4194 (class 2604 OID 2184678)
-- Name: stg_cdr_188 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_188 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_188_staging_row_id_seq'::regclass);


--
-- TOC entry 4197 (class 2604 OID 2184690)
-- Name: stg_cdr_189 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_189 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_189_staging_row_id_seq'::regclass);


--
-- TOC entry 4200 (class 2604 OID 2184704)
-- Name: stg_cdr_192 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_192 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_192_staging_row_id_seq'::regclass);


--
-- TOC entry 4203 (class 2604 OID 2184716)
-- Name: stg_cdr_193 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_193 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_193_staging_row_id_seq'::regclass);


--
-- TOC entry 4206 (class 2604 OID 2184730)
-- Name: stg_cdr_196 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_196 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_196_staging_row_id_seq'::regclass);


--
-- TOC entry 4209 (class 2604 OID 2184742)
-- Name: stg_cdr_197 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_197 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_197_staging_row_id_seq'::regclass);


--
-- TOC entry 4212 (class 2604 OID 2184756)
-- Name: stg_cdr_200 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_200 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_200_staging_row_id_seq'::regclass);


--
-- TOC entry 4215 (class 2604 OID 2184770)
-- Name: stg_cdr_203 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_203 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_203_staging_row_id_seq'::regclass);


--
-- TOC entry 4218 (class 2604 OID 2184782)
-- Name: stg_cdr_204 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_204 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_204_staging_row_id_seq'::regclass);


--
-- TOC entry 4221 (class 2604 OID 2184796)
-- Name: stg_cdr_207 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_207 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_207_staging_row_id_seq'::regclass);


--
-- TOC entry 4224 (class 2604 OID 2184808)
-- Name: stg_cdr_208 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_208 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_208_staging_row_id_seq'::regclass);


--
-- TOC entry 4227 (class 2604 OID 2184822)
-- Name: stg_cdr_211 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_211 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_211_staging_row_id_seq'::regclass);


--
-- TOC entry 4230 (class 2604 OID 2184848)
-- Name: stg_cdr_216 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_216 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_216_staging_row_id_seq'::regclass);


--
-- TOC entry 4233 (class 2604 OID 2184862)
-- Name: stg_cdr_218 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_218 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_218_staging_row_id_seq'::regclass);


--
-- TOC entry 4236 (class 2604 OID 2184874)
-- Name: stg_cdr_219 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_219 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_219_staging_row_id_seq'::regclass);


--
-- TOC entry 4239 (class 2604 OID 2184897)
-- Name: stg_cdr_222 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_222 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_222_staging_row_id_seq'::regclass);


--
-- TOC entry 4242 (class 2604 OID 2184956)
-- Name: stg_cdr_226 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_226 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_226_staging_row_id_seq'::regclass);


--
-- TOC entry 4245 (class 2604 OID 2184975)
-- Name: stg_cdr_228 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_228 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_228_staging_row_id_seq'::regclass);


--
-- TOC entry 4248 (class 2604 OID 2185113)
-- Name: stg_cdr_232 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_232 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_232_staging_row_id_seq'::regclass);


--
-- TOC entry 4251 (class 2604 OID 2185212)
-- Name: stg_cdr_234 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_234 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_234_staging_row_id_seq'::regclass);


--
-- TOC entry 4254 (class 2604 OID 2185224)
-- Name: stg_cdr_235 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_235 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_235_staging_row_id_seq'::regclass);


--
-- TOC entry 4257 (class 2604 OID 2185238)
-- Name: stg_cdr_238 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_238 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_238_staging_row_id_seq'::regclass);


--
-- TOC entry 4260 (class 2604 OID 2185250)
-- Name: stg_cdr_239 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_239 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_239_staging_row_id_seq'::regclass);


--
-- TOC entry 4263 (class 2604 OID 2185264)
-- Name: stg_cdr_242 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_242 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_242_staging_row_id_seq'::regclass);


--
-- TOC entry 4266 (class 2604 OID 2185276)
-- Name: stg_cdr_243 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_243 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_243_staging_row_id_seq'::regclass);


--
-- TOC entry 4269 (class 2604 OID 2185290)
-- Name: stg_cdr_246 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_246 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_246_staging_row_id_seq'::regclass);


--
-- TOC entry 4272 (class 2604 OID 2185304)
-- Name: stg_cdr_248 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_248 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_248_staging_row_id_seq'::regclass);


--
-- TOC entry 4275 (class 2604 OID 2185316)
-- Name: stg_cdr_249 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_249 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_249_staging_row_id_seq'::regclass);


--
-- TOC entry 4278 (class 2604 OID 2185329)
-- Name: stg_cdr_252 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_252 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_252_staging_row_id_seq'::regclass);


--
-- TOC entry 4281 (class 2604 OID 2185343)
-- Name: stg_cdr_254 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_254 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_254_staging_row_id_seq'::regclass);


--
-- TOC entry 4284 (class 2604 OID 2185355)
-- Name: stg_cdr_255 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_255 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_255_staging_row_id_seq'::regclass);


--
-- TOC entry 4287 (class 2604 OID 2185369)
-- Name: stg_cdr_258 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_258 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_258_staging_row_id_seq'::regclass);


--
-- TOC entry 4290 (class 2604 OID 2185383)
-- Name: stg_cdr_261 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_261 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_261_staging_row_id_seq'::regclass);


--
-- TOC entry 4293 (class 2604 OID 2185470)
-- Name: stg_cdr_268 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_268 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_268_staging_row_id_seq'::regclass);


--
-- TOC entry 4296 (class 2604 OID 2185489)
-- Name: stg_cdr_270 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_270 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_270_staging_row_id_seq'::regclass);


--
-- TOC entry 4299 (class 2604 OID 2185506)
-- Name: stg_cdr_273 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_273 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_273_staging_row_id_seq'::regclass);


--
-- TOC entry 4302 (class 2604 OID 2185534)
-- Name: stg_cdr_275 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_275 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_275_staging_row_id_seq'::regclass);


--
-- TOC entry 4305 (class 2604 OID 2185600)
-- Name: stg_cdr_279 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_279 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_279_staging_row_id_seq'::regclass);


--
-- TOC entry 4308 (class 2604 OID 2185614)
-- Name: stg_cdr_281 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_281 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_281_staging_row_id_seq'::regclass);


--
-- TOC entry 4311 (class 2604 OID 2185742)
-- Name: stg_cdr_293 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_293 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_293_staging_row_id_seq'::regclass);


--
-- TOC entry 4314 (class 2604 OID 2185896)
-- Name: stg_cdr_299 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_299 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_299_staging_row_id_seq'::regclass);


--
-- TOC entry 4317 (class 2604 OID 2187113)
-- Name: stg_cdr_319 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_319 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_319_staging_row_id_seq'::regclass);


--
-- TOC entry 4320 (class 2604 OID 2187485)
-- Name: stg_cdr_341 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_341 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_341_staging_row_id_seq'::regclass);


--
-- TOC entry 4323 (class 2604 OID 2187499)
-- Name: stg_cdr_343 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_343 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_343_staging_row_id_seq'::regclass);


--
-- TOC entry 4037 (class 2604 OID 2153704)
-- Name: stg_cdr_36 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_36 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_36_staging_row_id_seq'::regclass);


--
-- TOC entry 4044 (class 2604 OID 2173883)
-- Name: stg_cdr_38 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_38 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_38_staging_row_id_seq'::regclass);


--
-- TOC entry 4047 (class 2604 OID 2173899)
-- Name: stg_cdr_41 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_41 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_41_staging_row_id_seq'::regclass);


--
-- TOC entry 4050 (class 2604 OID 2173953)
-- Name: stg_cdr_43 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_43 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_43_staging_row_id_seq'::regclass);


--
-- TOC entry 4053 (class 2604 OID 2174632)
-- Name: stg_cdr_65 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_65 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_65_staging_row_id_seq'::regclass);


--
-- TOC entry 4056 (class 2604 OID 2174708)
-- Name: stg_cdr_71 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_71 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_71_staging_row_id_seq'::regclass);


--
-- TOC entry 4059 (class 2604 OID 2174725)
-- Name: stg_cdr_73 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_73 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_73_staging_row_id_seq'::regclass);


--
-- TOC entry 4062 (class 2604 OID 2174955)
-- Name: stg_cdr_87 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_87 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_87_staging_row_id_seq'::regclass);


--
-- TOC entry 4065 (class 2604 OID 2174968)
-- Name: stg_cdr_89 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_89 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_89_staging_row_id_seq'::regclass);


--
-- TOC entry 4068 (class 2604 OID 2183987)
-- Name: stg_cdr_99 staging_row_id; Type: DEFAULT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_99 ALTER COLUMN staging_row_id SET DEFAULT nextval('upload_staging.stg_cdr_99_staging_row_id_seq'::regclass);


--
-- TOC entry 4572 (class 2606 OID 2187613)
-- Name: cdat_provider_master cdat_provider_master_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cdat_provider_master
    ADD CONSTRAINT cdat_provider_master_pkey PRIMARY KEY (provider_key);


--
-- TOC entry 4570 (class 2606 OID 2187606)
-- Name: cdat_state_master cdat_state_master_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cdat_state_master
    ADD CONSTRAINT cdat_state_master_pkey PRIMARY KEY (state_key);


--
-- TOC entry 4342 (class 2606 OID 1894456)
-- Name: cdatpcsuspect_staging cdatpcsuspect_staging_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cdatpcsuspect_staging
    ADD CONSTRAINT cdatpcsuspect_staging_pkey PRIMARY KEY (staging_id);


--
-- TOC entry 4334 (class 2606 OID 1893726)
-- Name: cdatsuspect cdatsuspect_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cdatsuspect
    ADD CONSTRAINT cdatsuspect_pkey PRIMARY KEY (phone);


--
-- TOC entry 4345 (class 2606 OID 1894485)
-- Name: document_jobs document_jobs_module_source_file_file_sha256_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_jobs
    ADD CONSTRAINT document_jobs_module_source_file_file_sha256_key UNIQUE (module, source_file, file_sha256);


--
-- TOC entry 4347 (class 2606 OID 1894483)
-- Name: document_jobs document_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_jobs
    ADD CONSTRAINT document_jobs_pkey PRIMARY KEY (job_id);


--
-- TOC entry 4351 (class 2606 OID 1894521)
-- Name: tbladmin tbladmin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tbladmin
    ADD CONSTRAINT tbladmin_pkey PRIMARY KEY (id);


--
-- TOC entry 4353 (class 2606 OID 1894529)
-- Name: tblcategory tblcategory_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcategory
    ADD CONSTRAINT tblcategory_pkey PRIMARY KEY (id);


--
-- TOC entry 4355 (class 2606 OID 1894539)
-- Name: tblpass tblpass_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblpass
    ADD CONSTRAINT tblpass_pkey PRIMARY KEY (id);


--
-- TOC entry 4369 (class 2606 OID 1894581)
-- Name: upload_activity_logs upload_activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_activity_logs
    ADD CONSTRAINT upload_activity_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4380 (class 2606 OID 2153758)
-- Name: upload_approval_queue upload_approval_queue_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_approval_queue
    ADD CONSTRAINT upload_approval_queue_pkey PRIMARY KEY (queue_id);


--
-- TOC entry 4372 (class 2606 OID 2153685)
-- Name: upload_staging_batches upload_staging_batches_document_job_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_staging_batches
    ADD CONSTRAINT upload_staging_batches_document_job_id_key UNIQUE (document_job_id);


--
-- TOC entry 4374 (class 2606 OID 2153683)
-- Name: upload_staging_batches upload_staging_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_staging_batches
    ADD CONSTRAINT upload_staging_batches_pkey PRIMARY KEY (batch_id);


--
-- TOC entry 4361 (class 2606 OID 1894566)
-- Name: user_activity_logs user_activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_activity_logs
    ADD CONSTRAINT user_activity_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4358 (class 2606 OID 1894556)
-- Name: user_sessions user_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_sessions
    ADD CONSTRAINT user_sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 4400 (class 2606 OID 2184006)
-- Name: stg_cdr_102 stg_cdr_102_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_102
    ADD CONSTRAINT stg_cdr_102_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4402 (class 2606 OID 2184020)
-- Name: stg_cdr_104 stg_cdr_104_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_104
    ADD CONSTRAINT stg_cdr_104_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4404 (class 2606 OID 2184048)
-- Name: stg_cdr_106 stg_cdr_106_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_106
    ADD CONSTRAINT stg_cdr_106_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4406 (class 2606 OID 2184063)
-- Name: stg_cdr_109 stg_cdr_109_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_109
    ADD CONSTRAINT stg_cdr_109_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4408 (class 2606 OID 2184079)
-- Name: stg_cdr_112 stg_cdr_112_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_112
    ADD CONSTRAINT stg_cdr_112_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4410 (class 2606 OID 2184093)
-- Name: stg_cdr_114 stg_cdr_114_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_114
    ADD CONSTRAINT stg_cdr_114_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4412 (class 2606 OID 2184105)
-- Name: stg_cdr_115 stg_cdr_115_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_115
    ADD CONSTRAINT stg_cdr_115_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4414 (class 2606 OID 2184138)
-- Name: stg_cdr_118 stg_cdr_118_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_118
    ADD CONSTRAINT stg_cdr_118_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4416 (class 2606 OID 2184152)
-- Name: stg_cdr_120 stg_cdr_120_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_120
    ADD CONSTRAINT stg_cdr_120_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4418 (class 2606 OID 2184166)
-- Name: stg_cdr_122 stg_cdr_122_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_122
    ADD CONSTRAINT stg_cdr_122_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4420 (class 2606 OID 2184181)
-- Name: stg_cdr_125 stg_cdr_125_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_125
    ADD CONSTRAINT stg_cdr_125_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4422 (class 2606 OID 2184193)
-- Name: stg_cdr_126 stg_cdr_126_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_126
    ADD CONSTRAINT stg_cdr_126_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4424 (class 2606 OID 2184206)
-- Name: stg_cdr_129 stg_cdr_129_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_129
    ADD CONSTRAINT stg_cdr_129_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4426 (class 2606 OID 2184219)
-- Name: stg_cdr_131 stg_cdr_131_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_131
    ADD CONSTRAINT stg_cdr_131_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4428 (class 2606 OID 2184243)
-- Name: stg_cdr_133 stg_cdr_133_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_133
    ADD CONSTRAINT stg_cdr_133_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4430 (class 2606 OID 2184262)
-- Name: stg_cdr_135 stg_cdr_135_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_135
    ADD CONSTRAINT stg_cdr_135_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4432 (class 2606 OID 2184274)
-- Name: stg_cdr_136 stg_cdr_136_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_136
    ADD CONSTRAINT stg_cdr_136_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4434 (class 2606 OID 2184299)
-- Name: stg_cdr_139 stg_cdr_139_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_139
    ADD CONSTRAINT stg_cdr_139_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4436 (class 2606 OID 2184311)
-- Name: stg_cdr_140 stg_cdr_140_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_140
    ADD CONSTRAINT stg_cdr_140_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4438 (class 2606 OID 2184324)
-- Name: stg_cdr_143 stg_cdr_143_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_143
    ADD CONSTRAINT stg_cdr_143_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4440 (class 2606 OID 2184338)
-- Name: stg_cdr_145 stg_cdr_145_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_145
    ADD CONSTRAINT stg_cdr_145_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4442 (class 2606 OID 2184350)
-- Name: stg_cdr_146 stg_cdr_146_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_146
    ADD CONSTRAINT stg_cdr_146_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4444 (class 2606 OID 2184363)
-- Name: stg_cdr_149 stg_cdr_149_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_149
    ADD CONSTRAINT stg_cdr_149_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4446 (class 2606 OID 2184398)
-- Name: stg_cdr_151 stg_cdr_151_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_151
    ADD CONSTRAINT stg_cdr_151_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4448 (class 2606 OID 2184410)
-- Name: stg_cdr_152 stg_cdr_152_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_152
    ADD CONSTRAINT stg_cdr_152_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4450 (class 2606 OID 2184424)
-- Name: stg_cdr_155 stg_cdr_155_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_155
    ADD CONSTRAINT stg_cdr_155_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4452 (class 2606 OID 2184437)
-- Name: stg_cdr_156 stg_cdr_156_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_156
    ADD CONSTRAINT stg_cdr_156_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4454 (class 2606 OID 2184450)
-- Name: stg_cdr_158 stg_cdr_158_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_158
    ADD CONSTRAINT stg_cdr_158_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4456 (class 2606 OID 2184464)
-- Name: stg_cdr_161 stg_cdr_161_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_161
    ADD CONSTRAINT stg_cdr_161_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4458 (class 2606 OID 2184476)
-- Name: stg_cdr_162 stg_cdr_162_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_162
    ADD CONSTRAINT stg_cdr_162_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4460 (class 2606 OID 2184490)
-- Name: stg_cdr_165 stg_cdr_165_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_165
    ADD CONSTRAINT stg_cdr_165_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4462 (class 2606 OID 2184502)
-- Name: stg_cdr_166 stg_cdr_166_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_166
    ADD CONSTRAINT stg_cdr_166_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4464 (class 2606 OID 2184515)
-- Name: stg_cdr_169 stg_cdr_169_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_169
    ADD CONSTRAINT stg_cdr_169_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4466 (class 2606 OID 2184529)
-- Name: stg_cdr_171 stg_cdr_171_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_171
    ADD CONSTRAINT stg_cdr_171_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4468 (class 2606 OID 2184541)
-- Name: stg_cdr_172 stg_cdr_172_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_172
    ADD CONSTRAINT stg_cdr_172_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4470 (class 2606 OID 2184554)
-- Name: stg_cdr_175 stg_cdr_175_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_175
    ADD CONSTRAINT stg_cdr_175_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4472 (class 2606 OID 2184568)
-- Name: stg_cdr_177 stg_cdr_177_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_177
    ADD CONSTRAINT stg_cdr_177_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4474 (class 2606 OID 2184580)
-- Name: stg_cdr_178 stg_cdr_178_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_178
    ADD CONSTRAINT stg_cdr_178_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4476 (class 2606 OID 2184595)
-- Name: stg_cdr_182 stg_cdr_182_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_182
    ADD CONSTRAINT stg_cdr_182_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4478 (class 2606 OID 2184607)
-- Name: stg_cdr_183 stg_cdr_183_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_183
    ADD CONSTRAINT stg_cdr_183_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4480 (class 2606 OID 2184620)
-- Name: stg_cdr_186 stg_cdr_186_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_186
    ADD CONSTRAINT stg_cdr_186_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4482 (class 2606 OID 2184684)
-- Name: stg_cdr_188 stg_cdr_188_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_188
    ADD CONSTRAINT stg_cdr_188_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4484 (class 2606 OID 2184696)
-- Name: stg_cdr_189 stg_cdr_189_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_189
    ADD CONSTRAINT stg_cdr_189_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4486 (class 2606 OID 2184710)
-- Name: stg_cdr_192 stg_cdr_192_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_192
    ADD CONSTRAINT stg_cdr_192_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4488 (class 2606 OID 2184722)
-- Name: stg_cdr_193 stg_cdr_193_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_193
    ADD CONSTRAINT stg_cdr_193_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4490 (class 2606 OID 2184736)
-- Name: stg_cdr_196 stg_cdr_196_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_196
    ADD CONSTRAINT stg_cdr_196_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4492 (class 2606 OID 2184748)
-- Name: stg_cdr_197 stg_cdr_197_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_197
    ADD CONSTRAINT stg_cdr_197_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4494 (class 2606 OID 2184762)
-- Name: stg_cdr_200 stg_cdr_200_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_200
    ADD CONSTRAINT stg_cdr_200_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4496 (class 2606 OID 2184776)
-- Name: stg_cdr_203 stg_cdr_203_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_203
    ADD CONSTRAINT stg_cdr_203_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4498 (class 2606 OID 2184788)
-- Name: stg_cdr_204 stg_cdr_204_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_204
    ADD CONSTRAINT stg_cdr_204_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4500 (class 2606 OID 2184802)
-- Name: stg_cdr_207 stg_cdr_207_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_207
    ADD CONSTRAINT stg_cdr_207_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4502 (class 2606 OID 2184814)
-- Name: stg_cdr_208 stg_cdr_208_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_208
    ADD CONSTRAINT stg_cdr_208_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4504 (class 2606 OID 2184828)
-- Name: stg_cdr_211 stg_cdr_211_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_211
    ADD CONSTRAINT stg_cdr_211_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4506 (class 2606 OID 2184854)
-- Name: stg_cdr_216 stg_cdr_216_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_216
    ADD CONSTRAINT stg_cdr_216_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4508 (class 2606 OID 2184868)
-- Name: stg_cdr_218 stg_cdr_218_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_218
    ADD CONSTRAINT stg_cdr_218_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4510 (class 2606 OID 2184880)
-- Name: stg_cdr_219 stg_cdr_219_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_219
    ADD CONSTRAINT stg_cdr_219_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4512 (class 2606 OID 2184903)
-- Name: stg_cdr_222 stg_cdr_222_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_222
    ADD CONSTRAINT stg_cdr_222_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4514 (class 2606 OID 2184962)
-- Name: stg_cdr_226 stg_cdr_226_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_226
    ADD CONSTRAINT stg_cdr_226_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4516 (class 2606 OID 2184981)
-- Name: stg_cdr_228 stg_cdr_228_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_228
    ADD CONSTRAINT stg_cdr_228_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4518 (class 2606 OID 2185119)
-- Name: stg_cdr_232 stg_cdr_232_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_232
    ADD CONSTRAINT stg_cdr_232_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4520 (class 2606 OID 2185218)
-- Name: stg_cdr_234 stg_cdr_234_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_234
    ADD CONSTRAINT stg_cdr_234_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4522 (class 2606 OID 2185230)
-- Name: stg_cdr_235 stg_cdr_235_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_235
    ADD CONSTRAINT stg_cdr_235_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4524 (class 2606 OID 2185244)
-- Name: stg_cdr_238 stg_cdr_238_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_238
    ADD CONSTRAINT stg_cdr_238_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4526 (class 2606 OID 2185256)
-- Name: stg_cdr_239 stg_cdr_239_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_239
    ADD CONSTRAINT stg_cdr_239_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4528 (class 2606 OID 2185270)
-- Name: stg_cdr_242 stg_cdr_242_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_242
    ADD CONSTRAINT stg_cdr_242_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4530 (class 2606 OID 2185282)
-- Name: stg_cdr_243 stg_cdr_243_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_243
    ADD CONSTRAINT stg_cdr_243_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4532 (class 2606 OID 2185296)
-- Name: stg_cdr_246 stg_cdr_246_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_246
    ADD CONSTRAINT stg_cdr_246_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4534 (class 2606 OID 2185310)
-- Name: stg_cdr_248 stg_cdr_248_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_248
    ADD CONSTRAINT stg_cdr_248_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4536 (class 2606 OID 2185322)
-- Name: stg_cdr_249 stg_cdr_249_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_249
    ADD CONSTRAINT stg_cdr_249_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4538 (class 2606 OID 2185335)
-- Name: stg_cdr_252 stg_cdr_252_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_252
    ADD CONSTRAINT stg_cdr_252_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4540 (class 2606 OID 2185349)
-- Name: stg_cdr_254 stg_cdr_254_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_254
    ADD CONSTRAINT stg_cdr_254_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4542 (class 2606 OID 2185361)
-- Name: stg_cdr_255 stg_cdr_255_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_255
    ADD CONSTRAINT stg_cdr_255_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4544 (class 2606 OID 2185375)
-- Name: stg_cdr_258 stg_cdr_258_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_258
    ADD CONSTRAINT stg_cdr_258_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4546 (class 2606 OID 2185389)
-- Name: stg_cdr_261 stg_cdr_261_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_261
    ADD CONSTRAINT stg_cdr_261_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4548 (class 2606 OID 2185476)
-- Name: stg_cdr_268 stg_cdr_268_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_268
    ADD CONSTRAINT stg_cdr_268_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4550 (class 2606 OID 2185495)
-- Name: stg_cdr_270 stg_cdr_270_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_270
    ADD CONSTRAINT stg_cdr_270_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4552 (class 2606 OID 2185512)
-- Name: stg_cdr_273 stg_cdr_273_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_273
    ADD CONSTRAINT stg_cdr_273_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4554 (class 2606 OID 2185540)
-- Name: stg_cdr_275 stg_cdr_275_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_275
    ADD CONSTRAINT stg_cdr_275_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4556 (class 2606 OID 2185606)
-- Name: stg_cdr_279 stg_cdr_279_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_279
    ADD CONSTRAINT stg_cdr_279_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4558 (class 2606 OID 2185620)
-- Name: stg_cdr_281 stg_cdr_281_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_281
    ADD CONSTRAINT stg_cdr_281_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4560 (class 2606 OID 2185748)
-- Name: stg_cdr_293 stg_cdr_293_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_293
    ADD CONSTRAINT stg_cdr_293_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4562 (class 2606 OID 2185902)
-- Name: stg_cdr_299 stg_cdr_299_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_299
    ADD CONSTRAINT stg_cdr_299_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4564 (class 2606 OID 2187119)
-- Name: stg_cdr_319 stg_cdr_319_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_319
    ADD CONSTRAINT stg_cdr_319_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4566 (class 2606 OID 2187491)
-- Name: stg_cdr_341 stg_cdr_341_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_341
    ADD CONSTRAINT stg_cdr_341_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4568 (class 2606 OID 2187505)
-- Name: stg_cdr_343 stg_cdr_343_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_343
    ADD CONSTRAINT stg_cdr_343_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4376 (class 2606 OID 2153710)
-- Name: stg_cdr_36 stg_cdr_36_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_36
    ADD CONSTRAINT stg_cdr_36_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4382 (class 2606 OID 2173889)
-- Name: stg_cdr_38 stg_cdr_38_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_38
    ADD CONSTRAINT stg_cdr_38_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4384 (class 2606 OID 2173905)
-- Name: stg_cdr_41 stg_cdr_41_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_41
    ADD CONSTRAINT stg_cdr_41_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4386 (class 2606 OID 2173959)
-- Name: stg_cdr_43 stg_cdr_43_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_43
    ADD CONSTRAINT stg_cdr_43_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4388 (class 2606 OID 2174638)
-- Name: stg_cdr_65 stg_cdr_65_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_65
    ADD CONSTRAINT stg_cdr_65_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4390 (class 2606 OID 2174714)
-- Name: stg_cdr_71 stg_cdr_71_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_71
    ADD CONSTRAINT stg_cdr_71_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4392 (class 2606 OID 2174731)
-- Name: stg_cdr_73 stg_cdr_73_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_73
    ADD CONSTRAINT stg_cdr_73_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4394 (class 2606 OID 2174961)
-- Name: stg_cdr_87 stg_cdr_87_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_87
    ADD CONSTRAINT stg_cdr_87_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4396 (class 2606 OID 2174974)
-- Name: stg_cdr_89 stg_cdr_89_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_89
    ADD CONSTRAINT stg_cdr_89_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4398 (class 2606 OID 2183993)
-- Name: stg_cdr_99 stg_cdr_99_pkey; Type: CONSTRAINT; Schema: upload_staging; Owner: postgres
--

ALTER TABLE ONLY upload_staging.stg_cdr_99
    ADD CONSTRAINT stg_cdr_99_pkey PRIMARY KEY (staging_row_id);


--
-- TOC entry 4326 (class 1259 OID 574457)
-- Name: idx_cdatpcsuspect_asondate; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatpcsuspect_asondate ON public.cdatpcsuspect USING btree (asondate);


--
-- TOC entry 4327 (class 1259 OID 2173867)
-- Name: idx_cdatpcsuspect_celltowerid; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatpcsuspect_celltowerid ON public.cdatpcsuspect USING btree (celltowerid) WHERE ((celltowerid IS NOT NULL) AND ((celltowerid)::text <> ''::text));


--
-- TOC entry 4328 (class 1259 OID 1898097)
-- Name: idx_cdatpcsuspect_imeinumber; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatpcsuspect_imeinumber ON public.cdatpcsuspect USING btree (imeinumber) WHERE ((imeinumber IS NOT NULL) AND (imeinumber <> (0)::numeric));


--
-- TOC entry 4329 (class 1259 OID 2173866)
-- Name: idx_cdatpcsuspect_other; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatpcsuspect_other ON public.cdatpcsuspect USING btree (other) WHERE ((other IS NOT NULL) AND ((other)::text <> ''::text));


--
-- TOC entry 4330 (class 1259 OID 1893789)
-- Name: idx_cdatpcsuspect_phone; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatpcsuspect_phone ON public.cdatpcsuspect USING btree (phone);


--
-- TOC entry 4331 (class 1259 OID 2153744)
-- Name: idx_cdatpcsuspect_phone_other_starttime; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatpcsuspect_phone_other_starttime ON public.cdatpcsuspect USING btree (phone, other, starttime);


--
-- TOC entry 4332 (class 1259 OID 2173865)
-- Name: idx_cdatpcsuspect_phone_starttime; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatpcsuspect_phone_starttime ON public.cdatpcsuspect USING btree (phone, starttime);


--
-- TOC entry 4343 (class 1259 OID 1894462)
-- Name: idx_cdatpcsuspect_staging_job; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatpcsuspect_staging_job ON public.cdatpcsuspect_staging USING btree (import_job_id, source_row_number);


--
-- TOC entry 4335 (class 1259 OID 2153722)
-- Name: idx_cdatphonearea_phoneprefix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatphonearea_phoneprefix ON public.cdatphonearea USING btree (phoneprefix);


--
-- TOC entry 4336 (class 1259 OID 2153723)
-- Name: idx_cdatphonearea_phoneprefix_len; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatphonearea_phoneprefix_len ON public.cdatphonearea USING btree (length((phoneprefix)::text) DESC);


--
-- TOC entry 4337 (class 1259 OID 2187627)
-- Name: idx_cdatphonearea_provider_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatphonearea_provider_key ON public.cdatphonearea USING btree (provider_key);


--
-- TOC entry 4338 (class 1259 OID 2187626)
-- Name: idx_cdatphonearea_state_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_cdatphonearea_state_key ON public.cdatphonearea USING btree (state_key);


--
-- TOC entry 4348 (class 1259 OID 1894487)
-- Name: idx_document_jobs_module; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_document_jobs_module ON public.document_jobs USING btree (module, status);


--
-- TOC entry 4349 (class 1259 OID 1894486)
-- Name: idx_document_jobs_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_document_jobs_status ON public.document_jobs USING btree (status);


--
-- TOC entry 4362 (class 1259 OID 2153766)
-- Name: idx_upload_activity_logs_document_job_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_upload_activity_logs_document_job_id ON public.upload_activity_logs USING btree (document_job_id) WHERE (document_job_id IS NOT NULL);


--
-- TOC entry 4377 (class 1259 OID 2153765)
-- Name: idx_upload_approval_queue_batch_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX idx_upload_approval_queue_batch_active ON public.upload_approval_queue USING btree (batch_id) WHERE ((status)::text = ANY ((ARRAY['queued'::character varying, 'running'::character varying])::text[]));


--
-- TOC entry 4378 (class 1259 OID 2153764)
-- Name: idx_upload_approval_queue_module_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_upload_approval_queue_module_status ON public.upload_approval_queue USING btree (module, status, queued_at);


--
-- TOC entry 4363 (class 1259 OID 2153718)
-- Name: idx_upload_logs_content_fingerprint; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_upload_logs_content_fingerprint ON public.upload_activity_logs USING btree (table_name, content_fingerprint) WHERE ((content_fingerprint IS NOT NULL) AND ((upload_status)::text = 'Success'::text));


--
-- TOC entry 4364 (class 1259 OID 1894584)
-- Name: idx_upload_logs_document_job_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_upload_logs_document_job_id ON public.upload_activity_logs USING btree (document_job_id);


--
-- TOC entry 4365 (class 1259 OID 2153692)
-- Name: idx_upload_logs_staging_batch; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_upload_logs_staging_batch ON public.upload_activity_logs USING btree (staging_batch_id);


--
-- TOC entry 4366 (class 1259 OID 1894582)
-- Name: idx_upload_logs_uploaded_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_upload_logs_uploaded_at ON public.upload_activity_logs USING btree (uploaded_at DESC);


--
-- TOC entry 4367 (class 1259 OID 1894583)
-- Name: idx_upload_logs_username; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_upload_logs_username ON public.upload_activity_logs USING btree (username);


--
-- TOC entry 4370 (class 1259 OID 2153691)
-- Name: idx_upload_staging_batches_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_upload_staging_batches_status ON public.upload_staging_batches USING btree (verification_status);


--
-- TOC entry 4359 (class 1259 OID 1894568)
-- Name: idx_user_activity_logs_username; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_activity_logs_username ON public.user_activity_logs USING btree (username);


--
-- TOC entry 4356 (class 1259 OID 1894567)
-- Name: idx_user_sessions_username; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_user_sessions_username ON public.user_sessions USING btree (username);


--
-- TOC entry 4339 (class 1259 OID 2187624)
-- Name: uq_cdatphonearea_prefix_provider; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX uq_cdatphonearea_prefix_provider ON public.cdatphonearea USING btree (phoneprefix, provider_key) WHERE (provider_key IS NOT NULL);


--
-- TOC entry 4340 (class 1259 OID 2187625)
-- Name: uq_cdatphonearea_seed_prefix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX uq_cdatphonearea_seed_prefix ON public.cdatphonearea USING btree (phoneprefix) WHERE (provider_key IS NULL);


--
-- TOC entry 4575 (class 2606 OID 1894503)
-- Name: cdatpcsuspect_staging cdatpcsuspect_staging_import_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cdatpcsuspect_staging
    ADD CONSTRAINT cdatpcsuspect_staging_import_job_id_fkey FOREIGN KEY (import_job_id) REFERENCES public.document_jobs(job_id);


--
-- TOC entry 4573 (class 2606 OID 2187633)
-- Name: cdatphonearea fk_cdatphonearea_provider; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cdatphonearea
    ADD CONSTRAINT fk_cdatphonearea_provider FOREIGN KEY (provider_key) REFERENCES public.cdat_provider_master(provider_key);


--
-- TOC entry 4574 (class 2606 OID 2187628)
-- Name: cdatphonearea fk_cdatphonearea_state; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cdatphonearea
    ADD CONSTRAINT fk_cdatphonearea_state FOREIGN KEY (state_key) REFERENCES public.cdat_state_master(state_key);


--
-- TOC entry 4577 (class 2606 OID 2153759)
-- Name: upload_approval_queue upload_approval_queue_batch_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_approval_queue
    ADD CONSTRAINT upload_approval_queue_batch_id_fkey FOREIGN KEY (batch_id) REFERENCES public.upload_staging_batches(batch_id) ON DELETE CASCADE;


--
-- TOC entry 4576 (class 2606 OID 2153686)
-- Name: upload_staging_batches upload_staging_batches_document_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.upload_staging_batches
    ADD CONSTRAINT upload_staging_batches_document_job_id_fkey FOREIGN KEY (document_job_id) REFERENCES public.document_jobs(job_id) ON DELETE CASCADE;


--
-- TOC entry 4737 (class 0 OID 0)
-- Dependencies: 13
-- Name: SCHEMA dist; Type: ACL; Schema: -; Owner: postgres
--

GRANT USAGE ON SCHEMA dist TO mahesh;
GRANT USAGE ON SCHEMA dist TO saikant;
GRANT USAGE ON SCHEMA dist TO jahangir;
GRANT USAGE ON SCHEMA dist TO varaprasad;


--
-- TOC entry 4738 (class 0 OID 0)
-- Dependencies: 12
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT USAGE ON SCHEMA public TO mahesh;
GRANT USAGE ON SCHEMA public TO saikant;
GRANT USAGE ON SCHEMA public TO jahangir;
GRANT USAGE ON SCHEMA public TO varaprasad;


--
-- TOC entry 4741 (class 0 OID 0)
-- Dependencies: 264
-- Name: TABLE address_other_state; Type: ACL; Schema: dist; Owner: postgres
--

GRANT SELECT ON TABLE dist.address_other_state TO mahesh;
GRANT SELECT ON TABLE dist.address_other_state TO saikant;
GRANT SELECT ON TABLE dist.address_other_state TO jahangir;
GRANT SELECT ON TABLE dist.address_other_state TO varaprasad;


--
-- TOC entry 4742 (class 0 OID 0)
-- Dependencies: 297
-- Name: TABLE cdataddress; Type: ACL; Schema: dist; Owner: postgres
--

GRANT SELECT ON TABLE dist.cdataddress TO mahesh;
GRANT SELECT ON TABLE dist.cdataddress TO saikant;
GRANT SELECT ON TABLE dist.cdataddress TO jahangir;
GRANT SELECT ON TABLE dist.cdataddress TO varaprasad;


--
-- TOC entry 4743 (class 0 OID 0)
-- Dependencies: 298
-- Name: TABLE cellids; Type: ACL; Schema: dist; Owner: postgres
--

GRANT SELECT ON TABLE dist.cellids TO mahesh;
GRANT SELECT ON TABLE dist.cellids TO saikant;
GRANT SELECT ON TABLE dist.cellids TO jahangir;
GRANT SELECT ON TABLE dist.cellids TO varaprasad;


--
-- TOC entry 4744 (class 0 OID 0)
-- Dependencies: 265
-- Name: TABLE dl_data; Type: ACL; Schema: dist; Owner: postgres
--

GRANT SELECT ON TABLE dist.dl_data TO mahesh;
GRANT SELECT ON TABLE dist.dl_data TO saikant;
GRANT SELECT ON TABLE dist.dl_data TO jahangir;
GRANT SELECT ON TABLE dist.dl_data TO varaprasad;


--
-- TOC entry 4745 (class 0 OID 0)
-- Dependencies: 266
-- Name: TABLE echallan_data; Type: ACL; Schema: dist; Owner: postgres
--

GRANT SELECT ON TABLE dist.echallan_data TO mahesh;
GRANT SELECT ON TABLE dist.echallan_data TO saikant;
GRANT SELECT ON TABLE dist.echallan_data TO jahangir;
GRANT SELECT ON TABLE dist.echallan_data TO varaprasad;


--
-- TOC entry 4746 (class 0 OID 0)
-- Dependencies: 267
-- Name: TABLE rta_data; Type: ACL; Schema: dist; Owner: postgres
--

GRANT SELECT ON TABLE dist.rta_data TO mahesh;
GRANT SELECT ON TABLE dist.rta_data TO saikant;
GRANT SELECT ON TABLE dist.rta_data TO jahangir;
GRANT SELECT ON TABLE dist.rta_data TO varaprasad;


--
-- TOC entry 4747 (class 0 OID 0)
-- Dependencies: 268
-- Name: TABLE tc_name; Type: ACL; Schema: dist; Owner: postgres
--

GRANT SELECT ON TABLE dist.tc_name TO mahesh;
GRANT SELECT ON TABLE dist.tc_name TO saikant;
GRANT SELECT ON TABLE dist.tc_name TO jahangir;
GRANT SELECT ON TABLE dist.tc_name TO varaprasad;


--
-- TOC entry 4748 (class 0 OID 0)
-- Dependencies: 263
-- Name: TABLE abstract_jan_to_july_till_date_to_check; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.abstract_jan_to_july_till_date_to_check TO mahesh;
GRANT SELECT ON TABLE public.abstract_jan_to_july_till_date_to_check TO saikant;
GRANT SELECT ON TABLE public.abstract_jan_to_july_till_date_to_check TO jahangir;
GRANT SELECT ON TABLE public.abstract_jan_to_july_till_date_to_check TO varaprasad;


--
-- TOC entry 4749 (class 0 OID 0)
-- Dependencies: 299
-- Name: TABLE address_other_state; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.address_other_state TO mahesh;
GRANT SELECT ON TABLE public.address_other_state TO saikant;
GRANT SELECT ON TABLE public.address_other_state TO jahangir;
GRANT SELECT ON TABLE public.address_other_state TO varaprasad;


--
-- TOC entry 4750 (class 0 OID 0)
-- Dependencies: 272
-- Name: TABLE cdat_civilsupply; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_civilsupply TO mahesh;
GRANT SELECT ON TABLE public.cdat_civilsupply TO saikant;
GRANT SELECT ON TABLE public.cdat_civilsupply TO jahangir;
GRANT SELECT ON TABLE public.cdat_civilsupply TO varaprasad;


--
-- TOC entry 4751 (class 0 OID 0)
-- Dependencies: 254
-- Name: TABLE cdatpcsuspect; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdatpcsuspect TO mahesh;
GRANT SELECT ON TABLE public.cdatpcsuspect TO saikant;
GRANT SELECT ON TABLE public.cdatpcsuspect TO jahangir;
GRANT SELECT ON TABLE public.cdatpcsuspect TO varaprasad;


--
-- TOC entry 4752 (class 0 OID 0)
-- Dependencies: 275
-- Name: TABLE cdat_details; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_details TO mahesh;
GRANT SELECT ON TABLE public.cdat_details TO saikant;
GRANT SELECT ON TABLE public.cdat_details TO jahangir;
GRANT SELECT ON TABLE public.cdat_details TO varaprasad;


--
-- TOC entry 4753 (class 0 OID 0)
-- Dependencies: 276
-- Name: TABLE cdat_details1; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_details1 TO mahesh;
GRANT SELECT ON TABLE public.cdat_details1 TO saikant;
GRANT SELECT ON TABLE public.cdat_details1 TO jahangir;
GRANT SELECT ON TABLE public.cdat_details1 TO varaprasad;


--
-- TOC entry 4754 (class 0 OID 0)
-- Dependencies: 269
-- Name: TABLE cdat_echallan; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_echallan TO mahesh;
GRANT SELECT ON TABLE public.cdat_echallan TO saikant;
GRANT SELECT ON TABLE public.cdat_echallan TO jahangir;
GRANT SELECT ON TABLE public.cdat_echallan TO varaprasad;


--
-- TOC entry 4755 (class 0 OID 0)
-- Dependencies: 271
-- Name: TABLE cdat_gas_details; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_gas_details TO mahesh;
GRANT SELECT ON TABLE public.cdat_gas_details TO saikant;
GRANT SELECT ON TABLE public.cdat_gas_details TO jahangir;
GRANT SELECT ON TABLE public.cdat_gas_details TO varaprasad;


--
-- TOC entry 4756 (class 0 OID 0)
-- Dependencies: 302
-- Name: TABLE cdat_licence; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_licence TO mahesh;
GRANT SELECT ON TABLE public.cdat_licence TO saikant;
GRANT SELECT ON TABLE public.cdat_licence TO jahangir;
GRANT SELECT ON TABLE public.cdat_licence TO varaprasad;


--
-- TOC entry 4757 (class 0 OID 0)
-- Dependencies: 502
-- Name: TABLE cdat_provider_master; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_provider_master TO mahesh;
GRANT SELECT ON TABLE public.cdat_provider_master TO saikant;
GRANT SELECT ON TABLE public.cdat_provider_master TO jahangir;
GRANT SELECT ON TABLE public.cdat_provider_master TO varaprasad;


--
-- TOC entry 4758 (class 0 OID 0)
-- Dependencies: 301
-- Name: TABLE cdat_rta; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_rta TO mahesh;
GRANT SELECT ON TABLE public.cdat_rta TO saikant;
GRANT SELECT ON TABLE public.cdat_rta TO jahangir;
GRANT SELECT ON TABLE public.cdat_rta TO varaprasad;


--
-- TOC entry 4759 (class 0 OID 0)
-- Dependencies: 501
-- Name: TABLE cdat_state_master; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_state_master TO mahesh;
GRANT SELECT ON TABLE public.cdat_state_master TO saikant;
GRANT SELECT ON TABLE public.cdat_state_master TO jahangir;
GRANT SELECT ON TABLE public.cdat_state_master TO varaprasad;


--
-- TOC entry 4760 (class 0 OID 0)
-- Dependencies: 270
-- Name: TABLE cdat_tc_name; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdat_tc_name TO mahesh;
GRANT SELECT ON TABLE public.cdat_tc_name TO saikant;
GRANT SELECT ON TABLE public.cdat_tc_name TO jahangir;
GRANT SELECT ON TABLE public.cdat_tc_name TO varaprasad;


--
-- TOC entry 4761 (class 0 OID 0)
-- Dependencies: 300
-- Name: TABLE cdataddress; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdataddress TO mahesh;
GRANT SELECT ON TABLE public.cdataddress TO saikant;
GRANT SELECT ON TABLE public.cdataddress TO jahangir;
GRANT SELECT ON TABLE public.cdataddress TO varaprasad;


--
-- TOC entry 4762 (class 0 OID 0)
-- Dependencies: 309
-- Name: TABLE cdatcelltowerareanew; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdatcelltowerareanew TO mahesh;
GRANT SELECT ON TABLE public.cdatcelltowerareanew TO saikant;
GRANT SELECT ON TABLE public.cdatcelltowerareanew TO jahangir;
GRANT SELECT ON TABLE public.cdatcelltowerareanew TO varaprasad;


--
-- TOC entry 4763 (class 0 OID 0)
-- Dependencies: 279
-- Name: TABLE cdatpcsuspect_staging; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdatpcsuspect_staging TO mahesh;
GRANT SELECT ON TABLE public.cdatpcsuspect_staging TO saikant;
GRANT SELECT ON TABLE public.cdatpcsuspect_staging TO jahangir;
GRANT SELECT ON TABLE public.cdatpcsuspect_staging TO varaprasad;


--
-- TOC entry 4765 (class 0 OID 0)
-- Dependencies: 278
-- Name: SEQUENCE cdatpcsuspect_staging_staging_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.cdatpcsuspect_staging_staging_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.cdatpcsuspect_staging_staging_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.cdatpcsuspect_staging_staging_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.cdatpcsuspect_staging_staging_id_seq TO varaprasad;


--
-- TOC entry 4766 (class 0 OID 0)
-- Dependencies: 256
-- Name: TABLE cdatphonearea; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdatphonearea TO mahesh;
GRANT SELECT ON TABLE public.cdatphonearea TO saikant;
GRANT SELECT ON TABLE public.cdatphonearea TO jahangir;
GRANT SELECT ON TABLE public.cdatphonearea TO varaprasad;


--
-- TOC entry 4767 (class 0 OID 0)
-- Dependencies: 255
-- Name: TABLE cdatsuspect; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdatsuspect TO mahesh;
GRANT SELECT ON TABLE public.cdatsuspect TO saikant;
GRANT SELECT ON TABLE public.cdatsuspect TO jahangir;
GRANT SELECT ON TABLE public.cdatsuspect TO varaprasad;


--
-- TOC entry 4768 (class 0 OID 0)
-- Dependencies: 282
-- Name: TABLE document_jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.document_jobs TO mahesh;
GRANT SELECT ON TABLE public.document_jobs TO saikant;
GRANT SELECT ON TABLE public.document_jobs TO jahangir;
GRANT SELECT ON TABLE public.document_jobs TO varaprasad;


--
-- TOC entry 4769 (class 0 OID 0)
-- Dependencies: 283
-- Name: TABLE cdr_import_jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.cdr_import_jobs TO mahesh;
GRANT SELECT ON TABLE public.cdr_import_jobs TO saikant;
GRANT SELECT ON TABLE public.cdr_import_jobs TO jahangir;
GRANT SELECT ON TABLE public.cdr_import_jobs TO varaprasad;


--
-- TOC entry 4770 (class 0 OID 0)
-- Dependencies: 280
-- Name: SEQUENCE cdr_import_ucid_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.cdr_import_ucid_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.cdr_import_ucid_seq TO saikant;
GRANT SELECT ON SEQUENCE public.cdr_import_ucid_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.cdr_import_ucid_seq TO varaprasad;


--
-- TOC entry 4771 (class 0 OID 0)
-- Dependencies: 310
-- Name: TABLE celltowerfiltered; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.celltowerfiltered TO mahesh;
GRANT SELECT ON TABLE public.celltowerfiltered TO saikant;
GRANT SELECT ON TABLE public.celltowerfiltered TO jahangir;
GRANT SELECT ON TABLE public.celltowerfiltered TO varaprasad;


--
-- TOC entry 4773 (class 0 OID 0)
-- Dependencies: 281
-- Name: SEQUENCE document_jobs_job_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.document_jobs_job_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.document_jobs_job_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.document_jobs_job_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.document_jobs_job_id_seq TO varaprasad;


--
-- TOC entry 4774 (class 0 OID 0)
-- Dependencies: 262
-- Name: TABLE image_table; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.image_table TO mahesh;
GRANT SELECT ON TABLE public.image_table TO saikant;
GRANT SELECT ON TABLE public.image_table TO jahangir;
GRANT SELECT ON TABLE public.image_table TO varaprasad;


--
-- TOC entry 4775 (class 0 OID 0)
-- Dependencies: 260
-- Name: TABLE ir_particulars; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.ir_particulars TO mahesh;
GRANT SELECT ON TABLE public.ir_particulars TO saikant;
GRANT SELECT ON TABLE public.ir_particulars TO jahangir;
GRANT SELECT ON TABLE public.ir_particulars TO varaprasad;


--
-- TOC entry 4776 (class 0 OID 0)
-- Dependencies: 274
-- Name: TABLE jrms_total_2012_to_2017; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.jrms_total_2012_to_2017 TO mahesh;
GRANT SELECT ON TABLE public.jrms_total_2012_to_2017 TO saikant;
GRANT SELECT ON TABLE public.jrms_total_2012_to_2017 TO jahangir;
GRANT SELECT ON TABLE public.jrms_total_2012_to_2017 TO varaprasad;


--
-- TOC entry 4777 (class 0 OID 0)
-- Dependencies: 273
-- Name: TABLE logins; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.logins TO mahesh;
GRANT SELECT ON TABLE public.logins TO saikant;
GRANT SELECT ON TABLE public.logins TO jahangir;
GRANT SELECT ON TABLE public.logins TO varaprasad;


--
-- TOC entry 4779 (class 0 OID 0)
-- Dependencies: 290
-- Name: SEQUENCE logins_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.logins_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.logins_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.logins_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.logins_id_seq TO varaprasad;


--
-- TOC entry 4780 (class 0 OID 0)
-- Dependencies: 258
-- Name: TABLE offence_details; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.offence_details TO mahesh;
GRANT SELECT ON TABLE public.offence_details TO saikant;
GRANT SELECT ON TABLE public.offence_details TO jahangir;
GRANT SELECT ON TABLE public.offence_details TO varaprasad;


--
-- TOC entry 4781 (class 0 OID 0)
-- Dependencies: 261
-- Name: TABLE pdact_main_table; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.pdact_main_table TO mahesh;
GRANT SELECT ON TABLE public.pdact_main_table TO saikant;
GRANT SELECT ON TABLE public.pdact_main_table TO jahangir;
GRANT SELECT ON TABLE public.pdact_main_table TO varaprasad;


--
-- TOC entry 4782 (class 0 OID 0)
-- Dependencies: 257
-- Name: TABLE rowdy_sheeter_data1; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.rowdy_sheeter_data1 TO mahesh;
GRANT SELECT ON TABLE public.rowdy_sheeter_data1 TO saikant;
GRANT SELECT ON TABLE public.rowdy_sheeter_data1 TO jahangir;
GRANT SELECT ON TABLE public.rowdy_sheeter_data1 TO varaprasad;


--
-- TOC entry 4783 (class 0 OID 0)
-- Dependencies: 303
-- Name: TABLE s; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.s TO mahesh;
GRANT SELECT ON TABLE public.s TO saikant;
GRANT SELECT ON TABLE public.s TO jahangir;
GRANT SELECT ON TABLE public.s TO varaprasad;


--
-- TOC entry 4784 (class 0 OID 0)
-- Dependencies: 277
-- Name: TABLE suspect_image_table; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.suspect_image_table TO mahesh;
GRANT SELECT ON TABLE public.suspect_image_table TO saikant;
GRANT SELECT ON TABLE public.suspect_image_table TO jahangir;
GRANT SELECT ON TABLE public.suspect_image_table TO varaprasad;


--
-- TOC entry 4785 (class 0 OID 0)
-- Dependencies: 304
-- Name: TABLE t; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.t TO mahesh;
GRANT SELECT ON TABLE public.t TO saikant;
GRANT SELECT ON TABLE public.t TO jahangir;
GRANT SELECT ON TABLE public.t TO varaprasad;


--
-- TOC entry 4786 (class 0 OID 0)
-- Dependencies: 285
-- Name: TABLE tbladmin; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.tbladmin TO mahesh;
GRANT SELECT ON TABLE public.tbladmin TO saikant;
GRANT SELECT ON TABLE public.tbladmin TO jahangir;
GRANT SELECT ON TABLE public.tbladmin TO varaprasad;


--
-- TOC entry 4788 (class 0 OID 0)
-- Dependencies: 284
-- Name: SEQUENCE tbladmin_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.tbladmin_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.tbladmin_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.tbladmin_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.tbladmin_id_seq TO varaprasad;


--
-- TOC entry 4789 (class 0 OID 0)
-- Dependencies: 287
-- Name: TABLE tblcategory; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.tblcategory TO mahesh;
GRANT SELECT ON TABLE public.tblcategory TO saikant;
GRANT SELECT ON TABLE public.tblcategory TO jahangir;
GRANT SELECT ON TABLE public.tblcategory TO varaprasad;


--
-- TOC entry 4791 (class 0 OID 0)
-- Dependencies: 286
-- Name: SEQUENCE tblcategory_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.tblcategory_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.tblcategory_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.tblcategory_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.tblcategory_id_seq TO varaprasad;


--
-- TOC entry 4792 (class 0 OID 0)
-- Dependencies: 289
-- Name: TABLE tblpass; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.tblpass TO mahesh;
GRANT SELECT ON TABLE public.tblpass TO saikant;
GRANT SELECT ON TABLE public.tblpass TO jahangir;
GRANT SELECT ON TABLE public.tblpass TO varaprasad;


--
-- TOC entry 4794 (class 0 OID 0)
-- Dependencies: 288
-- Name: SEQUENCE tblpass_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.tblpass_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.tblpass_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.tblpass_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.tblpass_id_seq TO varaprasad;


--
-- TOC entry 4795 (class 0 OID 0)
-- Dependencies: 259
-- Name: TABLE twrmdb_master_cdat; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.twrmdb_master_cdat TO mahesh;
GRANT SELECT ON TABLE public.twrmdb_master_cdat TO saikant;
GRANT SELECT ON TABLE public.twrmdb_master_cdat TO jahangir;
GRANT SELECT ON TABLE public.twrmdb_master_cdat TO varaprasad;


--
-- TOC entry 4796 (class 0 OID 0)
-- Dependencies: 296
-- Name: TABLE upload_activity_logs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.upload_activity_logs TO mahesh;
GRANT SELECT ON TABLE public.upload_activity_logs TO saikant;
GRANT SELECT ON TABLE public.upload_activity_logs TO jahangir;
GRANT SELECT ON TABLE public.upload_activity_logs TO varaprasad;


--
-- TOC entry 4798 (class 0 OID 0)
-- Dependencies: 295
-- Name: SEQUENCE upload_activity_logs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.upload_activity_logs_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.upload_activity_logs_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.upload_activity_logs_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.upload_activity_logs_id_seq TO varaprasad;


--
-- TOC entry 4799 (class 0 OID 0)
-- Dependencies: 312
-- Name: TABLE upload_approval_queue; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.upload_approval_queue TO mahesh;
GRANT SELECT ON TABLE public.upload_approval_queue TO saikant;
GRANT SELECT ON TABLE public.upload_approval_queue TO jahangir;
GRANT SELECT ON TABLE public.upload_approval_queue TO varaprasad;


--
-- TOC entry 4801 (class 0 OID 0)
-- Dependencies: 311
-- Name: SEQUENCE upload_approval_queue_queue_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.upload_approval_queue_queue_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.upload_approval_queue_queue_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.upload_approval_queue_queue_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.upload_approval_queue_queue_id_seq TO varaprasad;


--
-- TOC entry 4802 (class 0 OID 0)
-- Dependencies: 306
-- Name: TABLE upload_staging_batches; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.upload_staging_batches TO mahesh;
GRANT SELECT ON TABLE public.upload_staging_batches TO saikant;
GRANT SELECT ON TABLE public.upload_staging_batches TO jahangir;
GRANT SELECT ON TABLE public.upload_staging_batches TO varaprasad;


--
-- TOC entry 4804 (class 0 OID 0)
-- Dependencies: 305
-- Name: SEQUENCE upload_staging_batches_batch_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.upload_staging_batches_batch_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.upload_staging_batches_batch_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.upload_staging_batches_batch_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.upload_staging_batches_batch_id_seq TO varaprasad;


--
-- TOC entry 4805 (class 0 OID 0)
-- Dependencies: 294
-- Name: TABLE user_activity_logs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.user_activity_logs TO mahesh;
GRANT SELECT ON TABLE public.user_activity_logs TO saikant;
GRANT SELECT ON TABLE public.user_activity_logs TO jahangir;
GRANT SELECT ON TABLE public.user_activity_logs TO varaprasad;


--
-- TOC entry 4807 (class 0 OID 0)
-- Dependencies: 293
-- Name: SEQUENCE user_activity_logs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.user_activity_logs_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.user_activity_logs_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.user_activity_logs_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.user_activity_logs_id_seq TO varaprasad;


--
-- TOC entry 4808 (class 0 OID 0)
-- Dependencies: 292
-- Name: TABLE user_sessions; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.user_sessions TO mahesh;
GRANT SELECT ON TABLE public.user_sessions TO saikant;
GRANT SELECT ON TABLE public.user_sessions TO jahangir;
GRANT SELECT ON TABLE public.user_sessions TO varaprasad;


--
-- TOC entry 4810 (class 0 OID 0)
-- Dependencies: 291
-- Name: SEQUENCE user_sessions_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON SEQUENCE public.user_sessions_id_seq TO mahesh;
GRANT SELECT ON SEQUENCE public.user_sessions_id_seq TO saikant;
GRANT SELECT ON SEQUENCE public.user_sessions_id_seq TO jahangir;
GRANT SELECT ON SEQUENCE public.user_sessions_id_seq TO varaprasad;


--
-- TOC entry 2758 (class 826 OID 1893972)
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: dist; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA dist GRANT SELECT ON TABLES TO mahesh;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA dist GRANT SELECT ON TABLES TO saikant;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA dist GRANT SELECT ON TABLES TO jahangir;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA dist GRANT SELECT ON TABLES TO varaprasad;


--
-- TOC entry 2757 (class 826 OID 1893971)
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT ON SEQUENCES TO mahesh;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT ON SEQUENCES TO saikant;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT ON SEQUENCES TO jahangir;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT ON SEQUENCES TO varaprasad;


--
-- TOC entry 2756 (class 826 OID 1893970)
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT ON TABLES TO mahesh;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT ON TABLES TO saikant;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT ON TABLES TO jahangir;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT ON TABLES TO varaprasad;


-- Completed on 2026-08-04 17:05:04 IST

--
-- PostgreSQL database dump complete
--

\unrestrict cJ3exZHYXJhYsDbiORl0B4Svddnij7wx8y28MHyQPnBjPmkoFhJy6nQF2WVYnuS

