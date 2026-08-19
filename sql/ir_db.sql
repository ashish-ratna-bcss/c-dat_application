-- =============================================================================
-- MSSQL to PostgreSQL Migration: IR_DB
-- Source: /Desktop/old/mssql/*.sql
-- Branch: mssql-to-postgres-migration
-- Only tables/views actually referenced by the application are included.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS / CREATE OR REPLACE VIEW
-- =============================================================================

-- ------------------------------------------------------------

-- Target database: IR_DB

-- TABLE: IMAGE_TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS image_table (
    IRKEY numeric(18, 0) NOT NULL,
    CATEGORY varchar(50) NULL,
    CCNO varchar(100) NULL,
    IMAGE BYTEA NULL
);

-- ------------------------------------------------------------


-- TABLE: LOCAL_CONTACTS_FACILITATORS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS local_contacts_facilitators (
    IRKEY numeric(18, 0) NOT NULL,
    TOWN_CITY_OR_VILLAGE varchar(500) NULL,
    POLICE_STATION_LIMITS varchar(500) NULL,
    NAME varchar(500) NULL,
    FATHER_NAME varchar(100) NULL,
    AGE varchar(100) NULL,
    OCCUPATION varchar(100) NULL,
    ADDRESS_OF_CONTACT_PERSON varchar(1000) NULL,
    CRIME_NO int NULL,
    YEAR int NULL,
    SEC_OF_LAW varchar(500) NULL,
    POLICE_STATION varchar(50) NULL,
    PHONE varchar(50) NULL
);

-- ------------------------------------------------------------


-- TABLE: HABITUAL_OFFENDERS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS habitual_offenders (
    IRKEY numeric(18, 0) NOT NULL,
    NAME varchar(100) NULL,
    ALIAS_NAME varchar(100) NULL,
    FATHER_NAME varchar(100) NULL,
    AGE int NULL,
    PRESENT_ADDRESS varchar(1000) NULL,
    ARRESTED_IN_CRIMEHEAD varchar(500) NULL,
    MO varchar(500) NULL,
    CRIME_NO int NULL,
    YEAR int NULL,
    SEC_OF_LAW varchar(500) NULL,
    POLICE_STATION varchar(100) NULL,
    count1 int NULL,
    IMAGE BYTEA NULL
);

-- ------------------------------------------------------------


-- TABLE: IR_PARTICULARS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ir_particulars (
    IRKEY BIGSERIAL NOT NULL,
    NAME varchar(250) NULL,
    ALIAS_NAME varchar(250) NULL,
    FATHER_NAME varchar(100) NULL,
    AGE int NULL,
    DATE_OF_BIRTH date NULL,
    NATIONALITY varchar(50) NULL,
    RELIGION varchar(50) NULL,
    CASTE varchar(50) NULL,
    COMMUNITY varchar(50) NULL,
    PRESENT_ADDRESS varchar(1000) NULL,
    PERMANENT_ADDRESS varchar(1000) NULL,
    MOBILE varchar(100) NULL,
    EMAIL_ID varchar(100) NULL,
    SOCIAL_MEDIA_ACCOUNTS varchar(1000) NULL,
    AADHAR_NO bigint NULL,
    RATION_CARD_NO varchar(100) NULL,
    VOTERID varchar(500) NULL,
    PASSPORT varchar(500) NULL,
    PANCARD varchar(500) NULL,
    ELECTRICITY_CONNECTION varchar(500) NULL,
    GAS_CONNECTION varchar(500) NULL,
    VEHICLES varchar(500) NULL,
    DRIVING_LICENSE varchar(500) NULL,
    OTHER_ID_PROOFS varchar(500) NULL,
    SEX varchar(100) NULL,
    BUILT varchar(100) NULL,
    HEIGHT varchar(100) NULL,
    EYES varchar(100) NULL,
    HAIR varchar(100) NULL,
    FACE varchar(100) NULL,
    COLOUR varchar(100) NULL,
    TEETH varchar(100) NULL,
    NOSE varchar(100) NULL,
    BEARD varchar(100) NULL,
    MUSTACHES varchar(100) NULL,
    EAR varchar(100) NULL,
    IDENTIFICATION_MARKS varchar(500) NULL,
    DEFORMITIES_PECULIARITIES varchar(500) NULL,
    LANGUAGE_DIALECT varchar(500) NULL,
    BURN_MARKS varchar(100) NULL,
    LEUCODEMA varchar(100) NULL,
    MOLE varchar(100) NULL,
    SCAR varchar(100) NULL,
    TATTOO varchar(500) NULL,
    LIVING_STATUS varchar(100) NULL,
    MARITAL_STATUS varchar(100) NULL,
    EDUCATION_DETAILS varchar(500) NULL,
    OCCUPATION varchar(250) NULL,
    INCOME_GROUP varchar(100) NULL,
    REGULAR_HABITS varchar(100) NULL,
    CATEGORY varchar(50) NULL,
    CC_OR_EXCC varchar(20) NULL,
    CC_OR_EXCCNO varchar(20) NULL,
    ASONDATE TIMESTAMP NULL,
    IR_ENTRY_DONE_BY varchar(50) NULL
);

-- ------------------------------------------------------------


-- TABLE: OFFENCE_DETAILS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS offence_details (
    IRKEY numeric(18, 0) NOT NULL,
    PERIOD_OF_OFFENCE varchar(100) NULL,
    REGULAR_RESIDENCE varchar(500) NULL,
    PREPARATION_OF_OFFENCE varchar(500) NULL,
    AFTER_OFFENCE varchar(500) NULL,
    INDULGANCE_BEFORE_OFFENCE varchar(100) NULL,
    CRIME_HEAD varchar(500) NULL,
    SUB_TYPE varchar(500) NULL,
    MO varchar(2000) NULL,
    DATE_OF_ARREST date NULL,
    PLACE_OF_ARREST varchar(500) NULL,
    SUB_DIVISION varchar(100) NULL,
    DISTRICT_OR_UNIT varchar(100) NULL,
    ARRESTED_BY varchar(500) NULL,
    INTERROGATED_BY varchar(500) NULL,
    OTHERS_WHO_CAN_IDENTIFY varchar(500) NULL,
    CRIME_NO int NULL,
    YEAR int NULL,
    SEC_OF_LAW varchar(500) NULL,
    POLICE_STATION varchar(100) NULL,
    ARREST_TYPE varchar(100) NULL
);

-- ------------------------------------------------------------


-- TABLE: DISPOSAL_OF_PROPERTY
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS disposal_of_property (
    IRKEY numeric(18, 0) NOT NULL,
    PROPERTY_STOLEN varchar(1000) NULL,
    PROPERTY_RECOVERED varchar(2000) NULL,
    RECEIVER_NAME varchar(500) NULL,
    RECEIVER_ADDRESS varchar(500) NULL,
    HOW_SHARE_IS_SPENT varchar(1000) NULL,
    REMARKS varchar(500) NULL,
    CRIME_NO int NULL,
    YEAR int NULL,
    POLICE_STATION varchar(50) NULL
);

-- ------------------------------------------------------------


-- TABLE: FAMILY_HISTORY
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS family_history (
    IRKEY numeric(18, 0) NOT NULL,
    RELATIONSHIP varchar(100) NULL,
    NAME varchar(50) NULL,
    FATHER_OR_SPOUSE varchar(100) NULL,
    OCCUPATION varchar(100) NULL,
    PHONE varchar(50) NULL,
    AGE varchar(50) NULL,
    CRIMINAL_BACKGROUND varchar(100) NULL,
    STATUS varchar(100) NULL,
    PRESENT_ADDRESS varchar(1000) NULL,
    PERMANENT_ADDRESS varchar(1000) NULL
);

-- ------------------------------------------------------------


-- TABLE: BRIEF_FACTS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS brief_facts (
    IRKEY numeric(18, 0) NOT NULL,
    BRIEF_FACTS1 varchar(8000) NULL,
    BRIEF_FACTS2 TEXT NULL,
    BRIEF_FACTS3 TEXT NULL,
    BRIEF_FACTS4 varchar(500) NULL
);

-- ------------------------------------------------------------


-- ------------------------------------------------------------


-- TABLE: PREVIOUS_OFFENCE_DETAILS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS previous_offence_details (
    IRKEY numeric(18, 0) NOT NULL,
    DISTRICT varchar(500) NULL,
    CONFESSED_POLICE_STATION varchar(100) NULL,
    CONFESSED_CRIME_NO varchar(100) NULL,
    CONFESSED_YEAR varchar(100) NULL,
    CONFESSED_SEC_OF_LAW varchar(500) NULL,
    ASSOCIATES varchar(500) NULL,
    PROPERTY_STOLEN varchar(500) NULL,
    PROPERTY_RECOVERED varchar(1000) NULL,
    REMARKS varchar(500) NULL,
    CRIME_NO int NULL,
    YEAR int NULL,
    POLICE_STATION varchar(50) NULL,
    CRIME_HEAD varchar(500) NULL,
    CONFESSED_DOA date NULL,
    CONFSSED_DATE_OF_RELEASE date NULL
);

-- ------------------------------------------------------------


-- TABLE: FINGERPRINT_MATCHED_UNDETECTED_CASES_WITHIMAGE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fingerprint_matched_undetected_cases_withimage (
    SNO varchar(8000) NULL,
    POLICE_STATION varchar(8000) NULL,
    ZONE varchar(8000) NULL,
    CRIME_NO varchar(8000) NULL,
    SECTION varchar(8000) NULL,
    TIN_NO varchar(8000) NULL,
    DATE_OF_IDENTITY varchar(8000) NULL,
    LOSS_OF_PROPERTY varchar(8000) NULL,
    NAME_AND_PARTICULARS varchar(8000) NULL,
    IRKEY varchar(8000) NULL,
    CCNO varchar(8000) NULL,
    DOA varchar(8000) NULL,
    REMARKS varchar(8000) NULL,
    IMAGE BYTEA NULL
);

-- ------------------------------------------------------------


-- TABLE: RELATIONSHIP_WITH_OTHER_ASSOCIATES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS relationship_with_other_associates (
    IRKEY numeric(18, 0) NOT NULL,
    GANG varchar(100) NULL,
    CATEGORY varchar(100) NULL,
    MEMBER varchar(100) NULL,
    FATHER_NAME varchar(100) NULL,
    AGE varchar(100) NULL,
    OCCUPATION varchar(200) NULL,
    ADDRESS varchar(500) NULL,
    PHONE varchar(100) NULL,
    RELATIONSHIP varchar(100) NULL,
    REMARKS varchar(250) NULL
);

