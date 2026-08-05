--
-- PostgreSQL database dump
--

\restrict U0tkvHSPkfefApfVdJ2WueME0fQGdub6wfaWzRJWDjdQitgyDi2BHcu3UzHO1EU

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
-- Name: citus; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS citus WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION citus; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION citus IS 'Citus distributed database';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: address_checkpoint; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.address_checkpoint (
    id integer NOT NULL,
    last_key bigint
);


--
-- Name: address_checkpoint_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.address_checkpoint_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: address_checkpoint_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.address_checkpoint_id_seq OWNED BY public.address_checkpoint.id;


--
-- Name: address_other_state; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.address_other_state (
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
);


--
-- Name: cdataddress; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cdataddress (
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
);


--
-- Name: cellids; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cellids (
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
);


--
-- Name: distributed_migration_checkpoint; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.distributed_migration_checkpoint (
    job_name character varying(64) NOT NULL,
    last_key bigint DEFAULT 0 NOT NULL,
    rows_committed bigint DEFAULT 0 NOT NULL,
    status character varying(20) DEFAULT 'running'::character varying NOT NULL,
    error_message text,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: dl_data; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dl_data (
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
);


--
-- Name: dl_migration_checkpoint; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dl_migration_checkpoint (
    id integer NOT NULL,
    last_line bigint,
    updated_at timestamp without time zone DEFAULT now()
);


--
-- Name: dl_migration_checkpoint_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.dl_migration_checkpoint_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: dl_migration_checkpoint_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.dl_migration_checkpoint_id_seq OWNED BY public.dl_migration_checkpoint.id;


--
-- Name: echallan_data; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.echallan_data (
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
);


--
-- Name: migration_checkpoint; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migration_checkpoint (
    id integer NOT NULL,
    last_row bigint,
    updated_at timestamp without time zone DEFAULT now()
);


--
-- Name: migration_checkpoint_address; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migration_checkpoint_address (
    id integer NOT NULL,
    last_key bigint,
    seq bigint DEFAULT 0
);


--
-- Name: migration_checkpoint_address_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migration_checkpoint_address_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migration_checkpoint_address_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migration_checkpoint_address_id_seq OWNED BY public.migration_checkpoint_address.id;


--
-- Name: migration_checkpoint_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migration_checkpoint_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migration_checkpoint_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migration_checkpoint_id_seq OWNED BY public.migration_checkpoint.id;


--
-- Name: rta_data; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rta_data (
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
);


--
-- Name: rta_migration_checkpoint; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rta_migration_checkpoint (
    id integer NOT NULL,
    last_line bigint
);


--
-- Name: rta_migration_checkpoint_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.rta_migration_checkpoint_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: rta_migration_checkpoint_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.rta_migration_checkpoint_id_seq OWNED BY public.rta_migration_checkpoint.id;


--
-- Name: tc_checkpoint; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tc_checkpoint (
    id integer NOT NULL,
    last_asondate timestamp without time zone,
    last_phone text,
    last_name text
);


--
-- Name: tc_checkpoint_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tc_checkpoint_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tc_checkpoint_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tc_checkpoint_id_seq OWNED BY public.tc_checkpoint.id;


--
-- Name: tc_name; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tc_name (
    id bigint NOT NULL,
    phone character varying(15) NOT NULL,
    name character varying(75),
    tags character varying(150),
    email character varying(500),
    asondate timestamp without time zone
);


--
-- Name: tc_name_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tc_name_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tc_name_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tc_name_id_seq OWNED BY public.tc_name.id;


--
-- Name: address_checkpoint id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.address_checkpoint ALTER COLUMN id SET DEFAULT nextval('public.address_checkpoint_id_seq'::regclass);


--
-- Name: dl_migration_checkpoint id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dl_migration_checkpoint ALTER COLUMN id SET DEFAULT nextval('public.dl_migration_checkpoint_id_seq'::regclass);


--
-- Name: migration_checkpoint id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migration_checkpoint ALTER COLUMN id SET DEFAULT nextval('public.migration_checkpoint_id_seq'::regclass);


--
-- Name: migration_checkpoint_address id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migration_checkpoint_address ALTER COLUMN id SET DEFAULT nextval('public.migration_checkpoint_address_id_seq'::regclass);


--
-- Name: rta_migration_checkpoint id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rta_migration_checkpoint ALTER COLUMN id SET DEFAULT nextval('public.rta_migration_checkpoint_id_seq'::regclass);


--
-- Name: tc_checkpoint id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tc_checkpoint ALTER COLUMN id SET DEFAULT nextval('public.tc_checkpoint_id_seq'::regclass);


--
-- Name: tc_name id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tc_name ALTER COLUMN id SET DEFAULT nextval('public.tc_name_id_seq'::regclass);


--
-- Name: address_checkpoint address_checkpoint_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.address_checkpoint
    ADD CONSTRAINT address_checkpoint_pkey PRIMARY KEY (id);


--
-- Name: cdataddress cdataddress_cdat_sdr_key_uniq; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cdataddress
    ADD CONSTRAINT cdataddress_cdat_sdr_key_uniq UNIQUE (cdat_sdr_key, phone);


--
-- Name: cellids cellids_tower_key_uniq; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cellids
    ADD CONSTRAINT cellids_tower_key_uniq UNIQUE (tower_key, celltowerid);


--
-- Name: distributed_migration_checkpoint distributed_migration_checkpoint_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.distributed_migration_checkpoint
    ADD CONSTRAINT distributed_migration_checkpoint_pkey PRIMARY KEY (job_name);


--
-- Name: dl_migration_checkpoint dl_migration_checkpoint_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dl_migration_checkpoint
    ADD CONSTRAINT dl_migration_checkpoint_pkey PRIMARY KEY (id);


--
-- Name: migration_checkpoint_address migration_checkpoint_address_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migration_checkpoint_address
    ADD CONSTRAINT migration_checkpoint_address_pkey PRIMARY KEY (id);


--
-- Name: migration_checkpoint migration_checkpoint_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migration_checkpoint
    ADD CONSTRAINT migration_checkpoint_pkey PRIMARY KEY (id);


--
-- Name: rta_migration_checkpoint rta_migration_checkpoint_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rta_migration_checkpoint
    ADD CONSTRAINT rta_migration_checkpoint_pkey PRIMARY KEY (id);


--
-- Name: tc_checkpoint tc_checkpoint_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tc_checkpoint
    ADD CONSTRAINT tc_checkpoint_pkey PRIMARY KEY (id);


--
-- Name: tc_name tc_name_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tc_name
    ADD CONSTRAINT tc_name_pkey PRIMARY KEY (phone, id);


--
-- Name: idx_address_other_state_phone_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_address_other_state_phone_active ON public.address_other_state USING btree (phone) WHERE (eff_to_date IS NULL);


--
-- Name: idx_cdataddress_phone; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdataddress_phone ON public.cdataddress USING btree (phone);


--
-- Name: idx_cdataddress_phone_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cdataddress_phone_active ON public.cdataddress USING btree (phone) WHERE (eff_to_date IS NULL);


--
-- Name: idx_cellids_celltowerid; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cellids_celltowerid ON public.cellids USING btree (celltowerid);


--
-- Name: idx_cellids_celltowerid_provider; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cellids_celltowerid_provider ON public.cellids USING btree (celltowerid, provider_key);


--
-- Name: idx_cellids_lat_long; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cellids_lat_long ON public.cellids USING btree (lat, long) WHERE ((lat IS NOT NULL) AND (long IS NOT NULL));


--
-- Name: idx_echallan_challan_no; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_echallan_challan_no ON public.echallan_data USING btree (challan_no);


--
-- Name: idx_echallan_driver_contact; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_echallan_driver_contact ON public.echallan_data USING btree (driver_contact_no);


--
-- Name: idx_echallan_owner_contact; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_echallan_owner_contact ON public.echallan_data USING btree (owner_contact_no);


--
-- Name: idx_echallan_vehicle_no; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_echallan_vehicle_no ON public.echallan_data USING btree (vehicle_no);


--
-- Name: idx_rta_data_chassis_no; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_rta_data_chassis_no ON public.rta_data USING btree (chassis_no);


--
-- Name: idx_rta_data_contact_no; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_rta_data_contact_no ON public.rta_data USING btree (contact_no);


--
-- Name: idx_rta_data_engine_no; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_rta_data_engine_no ON public.rta_data USING btree (engine_no);


--
-- Name: idx_rta_data_vehicle_no; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_rta_data_vehicle_no ON public.rta_data USING btree (vehicle_no);


--
-- PostgreSQL database dump complete
--

\unrestrict U0tkvHSPkfefApfVdJ2WueME0fQGdub6wfaWzRJWDjdQitgyDi2BHcu3UzHO1EU

