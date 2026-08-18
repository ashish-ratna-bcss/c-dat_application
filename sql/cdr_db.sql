-- =============================================================================
-- MSSQL to PostgreSQL Migration: CDR_DB
-- Source: /Desktop/old/mssql/*.sql
-- Branch: mssql-to-postgres-migration
-- Only tables/views actually referenced by the application are included.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS / CREATE OR REPLACE VIEW
-- =============================================================================

-- ------------------------------------------------------------

-- Target database: CDR_DB

-- TABLE: CDATPCSUSPECT
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdatpcsuspect (
    UCID SERIAL NOT NULL,
    PHONE varchar(15) NOT NULL,
    OTHER varchar(15) NOT NULL,
    STARTTIME TIMESTAMP NOT NULL,
    DURATION numeric(5, 0) NOT NULL,
    INCOMING SMALLINT NOT NULL,
    IMEINUMBER numeric(15, 0) NOT NULL,
    IMSINUMBER numeric(18, 0) NULL,
    CELLTOWERID varchar(50) NULL,
    OTHERINFO varchar(50) NULL,
    TOWER_KEY numeric(18, 0) NULL,
    PROVIDER_KEY SMALLINT NOT NULL,
    STATE_KEY SMALLINT NULL,
    FIRST_CELLID varchar(50) NULL,
    LAST_CELLID varchar(50) NULL,
    ROAMING_NW varchar(50) NULL,
    CALL_TYPE varchar(25) NULL,
    CALLING_NO varchar(50) NULL,
    CALLED_NO varchar(50) NULL,
    ASONDATE TIMESTAMP NULL
);

-- ------------------------------------------------------------


-- TABLE: CDATSUSPECT
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdatsuspect (
    PHONE varchar(50) NULL,
    ROLE TEXT NULL,
    NICKNAME TEXT NULL,
    FNAME TEXT NULL,
    ADDRESS TEXT NULL,
    CITY TEXT NULL,
    STATE TEXT NULL,
    COUNTRY TEXT NULL,
    PIN varchar(100) NULL,
    CRIME_NO TEXT NULL,
    YEAR TEXT NULL,
    DOO TEXT NULL,
    PLACE_OF_OFF TEXT NULL,
    DOR TEXT NULL,
    CRIME_HEAD TEXT NULL,
    MO TEXT NULL,
    SEC_OF_LAW TEXT NULL,
    UNIT TEXT NULL,
    MODULE_NAME TEXT NULL,
    ISACTIVE varchar(1) NOT NULL,
    LNAME varchar(10) NULL,
    CHECKFLAG varchar(1) NOT NULL,
    DOB_YEAR TEXT NULL,
    IMEINUMBER numeric(18, 0) NULL,
    INC_OFFICER TEXT NULL,
    CATEGORY TEXT NULL,
    ORGANISATION TEXT NULL,
    ASONDATE TIMESTAMP NOT NULL,
    REMARKS varchar(1000) NULL,
    Date_of_Arrest date NULL,
    CCNO varchar(100) NULL,
    IDPROOF varchar(100) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDATCELLTOWERAREANEW
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdatcelltowerareanew (
    TOWER_KEY BIGSERIAL NOT NULL,
    CELLTOWERID varchar(30) NULL,
    BTS_ID varchar(50) NULL,
    AREADESCRIPTION varchar(255) NULL,
    SITEADDRESS varchar(500) NULL,
    LAT varchar(20) NULL,
    LONG varchar(50) NULL,
    AZIMUTH varchar(20) NULL,
    OPERATOR varchar(50) NULL,
    STATE varchar(50) NULL,
    OTYPE varchar(50) NULL,
    LASTUPDATE TIMESTAMP NULL,
    OPID numeric(2, 0) NULL,
    STATE_KEY int NULL,
    PROVIDER_NAME varchar(50) NULL,
    PROVIDER_KEY int NULL,
    STATE_CODE varchar(2) NULL,
    CELLID varchar(30) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDATPHONEAREA
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdatphonearea (
    PHONEPREFIX varchar(10) NOT NULL,
    AREADESCRIPTION varchar(255) NULL,
    STATE varchar(50) NULL,
    NUMBERLENGTH int NULL,
    PPLEN int NULL,
    PH_TYPE varchar(10) NULL,
    ASONDATE varchar(30) NULL,
    STATE_KEY int NULL,
    STATE_CODE varchar(10) NULL,
    PROVIDER_NAME varchar(20) NULL,
    PROVIDER_KEY int NULL
);

-- ------------------------------------------------------------


-- TABLE: CDATADDRESS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdataddress (
    CDAT_SDR_KEY bigint NULL,
    PHONE VARCHAR(15) NOT NULL,
    TITLE int NULL,
    SURNAME int NULL,
    FIRSTNAME int NULL,
    MIDDLENAME int NULL,
    LASTNAME int NULL,
    FULLNAME varchar(255) NULL,
    ADDRESS1 int NULL,
    ADDRESS2 int NULL,
    ADDRESS3 int NULL,
    FULLADDRESS varchar(1000) NULL,
    CITY int NULL,
    DISTRICT int NULL,
    STATE varchar(50) NULL,
    PINCODE int NULL,
    COUNTRY varchar(5) NOT NULL,
    NATIONALITY varchar(50) NULL,
    DOA date NOT NULL,
    PERMANENTADDRESS varchar(1000) NULL,
    FATHERNAME varchar(100) NULL,
    RETAILER_DETAILS varchar(200) NULL,
    DISTRIBUTOR_DETAILS varchar(200) NULL,
    CONNECTION_TYPE varchar(15) NULL,
    CATEGORY_TYPE varchar(100) NULL,
    CAF_NO VARCHAR(50) NULL,
    POI_NAME varchar(120) NULL,
    POI_NO VARCHAR(50) NULL,
    CURRENT_STATUS varchar(50) NULL,
    REF_CONTACT_NAME varchar(200) NULL,
    REF_CONTACT_ADDRESS varchar(300) NULL,
    REF_CONTACT_NO int NULL,
    SUBSCRIBER_STATUS varchar(35) NULL,
    REVER_STATUS int NULL,
    MOBILE_PORTABILITY varchar(25) NULL,
    POA_NAME varchar(155) NULL,
    POA_NO VARCHAR(50) NULL,
    POA_ADDRESS varchar(150) NULL,
    ALT_CNT_NO VARCHAR(15) NULL,
    EMAILADDRESS varchar(100) NULL,
    BARRED int NULL,
    GIVENNAME int NULL,
    GENDER varchar(11) NULL,
    DOB date NULL,
    LRN_CODE int NULL,
    POI_ADDRESS varchar(255) NULL,
    PER_ADDRESS1 int NULL,
    PER_ADDRESS2 int NULL,
    PER_ADDRESS3 int NULL,
    PER_PINCODE int NULL,
    PER_CITY int NULL,
    PER_DISTRICT int NULL,
    PER_STATE int NULL,
    REMARKS int NULL,
    OPERATOR varchar(25) NULL,
    EFF_FROM_DATE TIMESTAMP NULL,
    EFF_TO_DATE TIMESTAMP NULL,
    imsi VARCHAR(15) NULL
);

-- ------------------------------------------------------------


-- TABLE: ADDRESS_OTHER_STATE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS address_other_state (
    OTH_SDR_KEY BIGSERIAL NOT NULL,
    PHONE varchar(15) NOT NULL,
    TITLE varchar(10) NULL,
    SURNAME varchar(15) NULL,
    FIRSTNAME varchar(50) NULL,
    MIDDLENAME varchar(50) NULL,
    LASTNAME varchar(50) NULL,
    FULLNAME varchar(255) NULL,
    ADDRESS1 varchar(100) NULL,
    ADDRESS2 varchar(100) NULL,
    ADDRESS3 varchar(100) NULL,
    FULLADDRESS varchar(1000) NULL,
    CITY varchar(255) NULL,
    DISTRICT varchar(50) NULL,
    STATE varchar(50) NULL,
    PINCODE varchar(10) NULL,
    COUNTRY varchar(30) NULL,
    NATIONALITY varchar(50) NULL,
    DOA date NULL,
    PERMANENTADDRESS varchar(1000) NULL,
    FATHERNAME varchar(100) NULL,
    RETAILER_DETAILS varchar(200) NULL,
    DISTRIBUTOR_DETAILS varchar(50) NULL,
    CONNECTION_TYPE varchar(25) NULL,
    CATEGORY_TYPE varchar(100) NULL,
    CAF_NO varchar(50) NULL,
    POI_NAME varchar(150) NULL,
    POI_NO varchar(50) NULL,
    CURRENT_STATUS varchar(50) NULL,
    REF_CONTACT_NAME varchar(50) NULL,
    REF_CONTACT_ADDRESS varchar(150) NULL,
    REF_CONTACT_NO varchar(150) NULL,
    SUBSCRIBER_STATUS varchar(50) NULL,
    REVER_STATUS varchar(50) NULL,
    MOBILE_PORTABILITY varchar(60) NULL,
    POA_NAME varchar(50) NULL,
    POA_NO varchar(50) NULL,
    POA_ADDRESS varchar(150) NULL,
    ALT_CNT_NO varchar(15) NULL,
    EMAILADDRESS varchar(100) NULL,
    BARRED varchar(10) NULL,
    GIVENNAME varchar(50) NULL,
    GENDER varchar(10) NULL,
    DOB date NULL,
    LRN_CODE int NULL,
    POI_ADDRESS varchar(255) NULL,
    PER_ADDRESS1 varchar(100) NULL,
    PER_ADDRESS2 varchar(100) NULL,
    PER_ADDRESS3 varchar(100) NULL,
    PER_PINCODE int NULL,
    PER_CITY varchar(50) NULL,
    PER_DISTRICT varchar(50) NULL,
    PER_STATE varchar(25) NULL,
    REMARKS varchar(255) NULL,
    OPERATOR varchar(25) NULL,
    EFF_FROM_DATE TIMESTAMP NULL,
    EFF_TO_DATE TIMESTAMP NULL,
    imsi varchar(20) NULL,
    CUST_AADHAR_NO varchar(200) NULL
);

-- ------------------------------------------------------------


-- VIEW: CDAT_DETAILS
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW CDAT_DETAILS
AS
(SELECT PHONE, OTHER, STARTTIME, DURATION, INCOMING, PROVIDER_KEY, OTHERINFO, IMEINUMBER,IMSINUMBER, CELLTOWERID
FROM  CDATPCSUSPECT where phone not in ('5496978056','8294982942','9212267970','8888888888','9448331696','8125055023','8125599909'
,'9030029820','7842055085','8052175595','8052175342','9891001109','9891998420','7760962785','9686202397','9024666666','9848824365'
,'8953003068','9936218000','9884098840','9719097190','8191010509','9845098450','8294982944','9212364448','9546695466','7488254286'
,'8125055024','8125599929','9030029821','7842055087','8052175593','8052175343','9891001316','9911130849','90008020617','9686202450'
,'9831378000','8071636101','7788777766','9121357123','9961824365','9818181234','8191010512','9560345888','8434784645','9236926691'
,'9885870090','7488254287','8125055025','8885599298','9030029822','7842055090','8052175592','8052175344','9891002260','9911130872'
,'9686453869','9686576258','7570736870','8039901800','7788777134','7893094589','9446256789','9212316666','8191010530','9223440000'
,'8757187575','9282113021','8888823456','7488254289','9891002740','903029811','9989468888','8125055015','8052175596','8052175346',
'9891002456','9911134090','9686453752','9686692279','8090355008','9845098450','7775557771','8015555555555','8951055098','9216148450'
,'8191010531','8726024365','8757187576','9282151079','9822012345','8121055035','9891002989','9030029812','9891006526','9030355003',
'8052175341','8052175347','9891002621','9911134151','9686454672','9686692281','8285684180','988078877','7777887748','9849578000',
'7755448877','9824423456','8191010532','5266539673','8976057645','9282151082','9730124130','8125055016','9891003503','9030029813',
'9891006886','9059365328','9891070218','9792063323','9891002651','9911134211','9845260210','9845578000','8376051594','9964023456',
'7788777134','9212230707','9848701419','9222208888','8191010534','9232232665','8294982941','9708024365','9885098850','8125055017',
'9891003551','9030029814','9891007083','9490618256','9891070224','9721135598','9891070395','9911134309','9880286053','9845911123',
'8376051911','9870807070','7788777766','8527121333','9287090010','9243355223','8191010536','8294082940','9162001070','9835111099',
'7513053900','8125055018','9891004044','9030029815','9891007152','7800000124','9891070229','8052175848','9891070420','9911134337',
'80425853434','9880286052','9542017602','9911554411','4067295700','9910069650','8191923456','9848001117','9012554411','8294982940',
'9162191623','9334553686','9871803333','8125055019','9891006451','9030029816','9891007354','7930333232','9891070273','8052175844',
'9891070435','9911195681','8244244111','9088324365','9848001112','8067335200','9420456789','8914596500','9220001111','7500095526',
'9582943043','8066900900','9247044888','9693181917','9666023456','8125055020','9891006453','9030029817','9891007849','8052175602',
'9891070286','9838004883','9891208746','9891866055','9008024019','9830098300','9848001113','9987981024','8082780827','9176621246',
'9897098970','7500095529','9837399994','8451043280','9326456993','9911852268','8026599990','8125055021','8125599894','9030029818',
'9891007924','8052175603','9891070342','8601001092','9891351851','9933008000','9686112506','8039414141','9848001114','9889012345',
'9839098390','8067922620','8105204433','7500095533','8451042518','8914596400','9771444704','9004609358','8385223791','8125055022',
'8125599908','9030029819','7842055081','8052175601','9891070368','9999999999','9891865964','9686194962','8067188300',
'9848001120','7775557771','989102345','8914596100','9211722715','7500095660','9030053407','7389907433','9848701419','8912547100',
'8912578050','8333999999','8790499899','9848009884','9030053406','9086023456','9870888888','9030053412','9242492424','8061020280','9906908510',
'9885011001','9848001115','9912002974','8061020600','9818843344','9030655008','9440156789','9030053414','9848601060'));

-- ------------------------------------------------------------


-- VIEW: CDAT_DETAILS1
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW CDAT_DETAILS1
AS
SELECT PHONE, OTHER, STARTTIME, DURATION, INCOMING, PROVIDER_KEY, OTHERINFO, IMEINUMBER, IMSINUMBER, CELLTOWERID,CALL_TYPE, TOWER_KEY, ROAMING_NW
FROM     CDATPCSUSPECT
WHERE  phone NOT IN ('5496978056', '8294982942', '9212267970', '8888888888', '9448331696', '8125055023', '8125599909', '9030029820', '7842055085', '8052175595', '8052175342', '9891001109', '9891998420', '7760962785', '9686202397', '9024666666', 
                  '9848824365', '8953003068', '9936218000', '9884098840', '9719097190', '8191010509', '9845098450', '8294982944', '9212364448', '9546695466', '7488254286', '8125055024', '8125599929', '9030029821', '7842055087', '8052175593', '8052175343', 
                  '9891001316', '9911130849', '90008020617', '9686202450', '9831378000', '8071636101', '7788777766', '9121357123', '9961824365', '9818181234', '8191010512', '9560345888', '8434784645', '9236926691', '9885870090', '7488254287', '8125055025', 
                  '8885599298', '9030029822', '7842055090', '8052175592', '8052175344', '9891002260', '9911130872', '9686453869', '9686576258', '7570736870', '8039901800', '7788777134', '7893094589', '9446256789', '9212316666', '8191010530', '9223440000', 
                  '8757187575', '9282113021', '8888823456', '7488254289', '9891002740', '903029811', '9989468888', '8125055015', '8052175596', '8052175346', '9891002456', '9911134090', '9686453752', '9686692279', '8090355008', '9845098450', '7775557771', 
                  '8015555555555', '8951055098', '9216148450', '8191010531', '8726024365', '8757187576', '9282151079', '9822012345', '8121055035', '9891002989', '9030029812', '9891006526', '9030355003', '8052175341', '8052175347', '9891002621', '9911134151', 
                  '9686454672', '9686692281', '8285684180', '988078877', '7777887748', '9849578000', '7755448877', '9824423456', '8191010532', '5266539673', '8976057645', '9282151082', '9730124130', '8125055016', '9891003503', '9030029813', '9891006886', 
                  '9059365328', '9891070218', '9792063323', '9891002651', '9911134211', '9845260210', '9845578000', '8376051594', '9964023456', '7788777134', '9212230707', '9848701419', '9222208888', '8191010534', '9232232665', '8294982941', '9708024365', 
                  '9885098850', '8125055017', '9891003551', '9030029814', '9891007083', '9490618256', '9891070224', '9721135598', '9891070395', '9911134309', '9880286053', '9845911123', '8376051911', '9870807070', '7788777766', '8527121333', '9287090010', 
                  '9243355223', '8191010536', '8294082940', '9162001070', '9835111099', '7513053900', '8125055018', '9891004044', '9030029815', '9891007152', '7800000124', '9891070229', '8052175848', '9891070420', '9911134337', '80425853434', '9880286052', 
                  '9542017602', '9911554411', '4067295700', '9910069650', '8191923456', '9848001117', '9012554411', '8294982940', '9162191623', '9334553686', '9871803333', '8125055019', '9891006451', '9030029816', '9891007354', '7930333232', '9891070273', 
                  '8052175844', '9891070435', '9911195681', '8244244111', '9088324365', '9848001112', '8067335200', '9420456789', '8914596500', '9220001111', '7500095526', '9582943043', '8066900900', '9247044888', '9693181917', '9666023456', '8125055020', 
                  '9891006453', '9030029817', '9891007849', '8052175602', '9891070286', '9838004883', '9891208746', '9891866055', '9008024019', '9830098300', '9848001113', '9987981024', '8082780827', '9176621246', '9897098970', '7500095529', '9837399994', 
                  '8451043280', '9326456993', '9911852268', '8026599990', '8125055021', '8125599894', '9030029818', '9891007924', '8052175603', '9891070342', '8601001092', '9891351851', '9933008000', '9686112506', '8039414141', '9848001114', '9889012345', 
                  '9839098390', '8067922620', '8105204433', '7500095533', '8451042518', '8914596400', '9771444704', '9004609358', '8385223791', '8125055022', '8125599908', '9030029819', '7842055081', '8052175601', '9891070368', '9999999999', '9891865964', 
                  '9686194962', '8067188300', '9848001120', '7775557771', '989102345', '8914596100', '9211722715', '7500095660', '9030053407', '7389907433', '9848701419', '8912547100', '8912578050', '8333999999', '8790499899', '9848009884', '9030053406', 
                  '9086023456', '9870888888', '9030053412', '9242492424', '8061020280', '9906908510', '9885011001', '9848001115', '9912002974', '8061020600', '9818843344', '9030655008', '9440156789', '9030053414', '9848601060')
UNION
(SELECT OTHER, PHONE, STARTTIME, DURATION, 1 - INCOMING, PROVIDER_KEY, OTHERINFO, - IMEINUMBER, - IMSINUMBER, CELLTOWERID || '*', CALL_TYPE, TOWER_KEY, ROAMING_NW
 FROM      CDATPCSUSPECT
 WHERE   other NOT IN ('5496978056', '8294982942', '9212267970', '8888888888', '9448331696', '8125055023', '8125599909', '9030029820', '7842055085', '8052175595', '8052175342', '9891001109', '9891998420', '7760962785', '9686202397', '9024666666', 
                   '9848824365', '8953003068', '9936218000', '9884098840', '9719097190', '8191010509', '9845098450', '8294982944', '9212364448', '9546695466', '7488254286', '8125055024', '8125599929', '9030029821', '7842055087', '8052175593', '8052175343', 
                   '9891001316', '9911130849', '90008020617', '9686202450', '9831378000', '8071636101', '7788777766', '9121357123', '9961824365', '9818181234', '8191010512', '9560345888', '8434784645', '9236926691', '9885870090', '7488254287', '8125055025', 
                   '8885599298', '9030029822', '7842055090', '8052175592', '8052175344', '9891002260', '9911130872', '9686453869', '9686576258', '7570736870', '8039901800', '7788777134', '7893094589', '9446256789', '9212316666', '8191010530', '9223440000', 
                   '8757187575', '9282113021', '8888823456', '7488254289', '9891002740', '903029811', '9989468888', '8125055015', '8052175596', '8052175346', '9891002456', '9911134090', '9686453752', '9686692279', '8090355008', '9845098450', '7775557771', 
                   '8015555555555', '8951055098', '9216148450', '8191010531', '8726024365', '8757187576', '9282151079', '9822012345', '8121055035', '9891002989', '9030029812', '9891006526', '9030355003', '8052175341', '8052175347', '9891002621', '9911134151', 
                   '9686454672', '9686692281', '8285684180', '988078877', '7777887748', '9849578000', '7755448877', '9824423456', '8191010532', '5266539673', '8976057645', '9282151082', '9730124130', '8125055016', '9891003503', '9030029813', '9891006886', 
                   '9059365328', '9891070218', '9792063323', '9891002651', '9911134211', '9845260210', '9845578000', '8376051594', '9964023456', '7788777134', '9212230707', '9848701419', '9222208888', '8191010534', '9232232665', '8294982941', '9708024365', 
                   '9885098850', '8125055017', '9891003551', '9030029814', '9891007083', '9490618256', '9891070224', '9721135598', '9891070395', '9911134309', '9880286053', '9845911123', '8376051911', '9870807070', '7788777766', '8527121333', '9287090010', 
                   '9243355223', '8191010536', '8294082940', '9162001070', '9835111099', '7513053900', '8125055018', '9891004044', '9030029815', '9891007152', '7800000124', '9891070229', '8052175848', '9891070420', '9911134337', '80425853434', '9880286052', 
                   '9542017602', '9911554411', '4067295700', '9910069650', '8191923456', '9848001117', '9012554411', '8294982940', '9162191623', '9334553686', '9871803333', '8125055019', '9891006451', '9030029816', '9891007354', '7930333232', '9891070273', 
                   '8052175844', '9891070435', '9911195681', '8244244111', '9088324365', '9848001112', '8067335200', '9420456789', '8914596500', '9220001111', '7500095526', '9582943043', '8066900900', '9247044888', '9693181917', '9666023456', '8125055020', 
                   '9891006453', '9030029817', '9891007849', '8052175602', '9891070286', '9838004883', '9891208746', '9891866055', '9008024019', '9830098300', '9848001113', '9987981024', '8082780827', '9176621246', '9897098970', '7500095529', '9837399994', 
                   '8451043280', '9326456993', '9911852268', '8026599990', '8125055021', '8125599894', '9030029818', '9891007924', '8052175603', '9891070342', '8601001092', '9891351851', '9933008000', '9686112506', '8039414141', '9848001114', '9889012345', 
                   '9839098390', '8067922620', '8105204433', '7500095533', '8451042518', '8914596400', '9771444704', '9004609358', '8385223791', '8125055022', '8125599908', '9030029819', '7842055081', '8052175601', '9891070368', '9999999999', '9891865964', 
                   '9686194962', '8067188300', '9848001120', '7775557771', '989102345', '8914596100', '9211722715', '7500095660', '9030053407', '7389907433', '9848701419', '8912547100', '8912578050', '8333999999', '8790499899', '9848009884', '9030053406', 
                   '9086023456', '9870888888', '9030053412', '9242492424', '8061020280', '9906908510', '9885011001', '9848001115', '9912002974', '8061020600', '9818843344', '9030655008', '9440156789', '9030053414', '9848601060'));

-- ------------------------------------------------------------


-- TABLE: SUSPECT_IMAGE_TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS suspect_image_table (
    IRKEY numeric(18, 0) NOT NULL,
    CCNO varchar(50) NULL,
    IMAGE BYTEA NULL,
    MOBILE varchar(100) NULL,
    RANK bigint NULL
);

-- ------------------------------------------------------------


-- TABLE: MO_IMAGE_TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mo_image_table (
    MO_KEY numeric(18, 0) NOT NULL,
    IMAGE BYTEA NOT NULL
);

-- ------------------------------------------------------------


-- TABLE: COMPLETE_MO_CLASSIFICATION
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS complete_mo_classification (
    MO_KEY BIGSERIAL NOT NULL,
    PHONE varchar(500) NULL,
    ROLE varchar(500) NULL,
    CATEGORY varchar(500) NULL,
    ACC_NAME varchar(500) NULL,
    ARREST_DATE_PHOTO varchar(500) NULL,
    LATEST_PHOTO varchar(500) NULL,
    FATHER_NAME varchar(500) NULL,
    DATE_OF_BIRTH varchar(500) NULL,
    AGE varchar(500) NULL,
    FULLADDRESS varchar(1000) NULL,
    CITY_OR_DISTRICT varchar(500) NULL,
    STATE varchar(500) NULL,
    ID_PROOF varchar(500) NULL,
    CRIME_HEAD varchar(500) NULL,
    MO1 varchar(500) NULL,
    MO2 varchar(500) NULL,
    CRIME_NO varchar(1000) NULL,
    Year varchar(500) NULL,
    SEC_OF_LAW varchar(500) NULL,
    DATE_OF_ARREST varchar(500) NULL,
    PLACE_OF_OFF varchar(500) NULL,
    off_lat varchar(500) NULL,
    off_long varchar(500) NULL,
    POLICE_STATION varchar(500) NULL,
    PS_DIVISION varchar(500) NULL,
    PS_ZONE varchar(500) NULL,
    INC_OFFICER varchar(500) NULL,
    OFFICIAL_MAILID varchar(500) NULL
);

-- ------------------------------------------------------------


-- TABLE: ROWDY_SHEETER_DATA1
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rowdy_sheeter_data1 (
    RWD_ID varchar(8000) NULL,
    IRKEY varchar(8000) NULL,
    PDACT_KEY varchar(8000) NULL,
    LATEST_ARREST varchar(8000) NULL,
    POLICE_STATION varchar(8000) NULL,
    DATE_OF_OPENING_RWD varchar(8000) NULL,
    RWD_YEAR varchar(8000) NULL,
    NAME varchar(8000) NULL,
    AGE varchar(8000) NULL,
    FATHER_NAME varchar(8000) NULL,
    PRESENT_ADDRESS varchar(8000) NULL,
    LAT_P varchar(8000) NULL,
    LONG_P varchar(8000) NULL,
    PERMANENT_ADDRESS varchar(8000) NULL,
    LAT varchar(8000) NULL,
    LONG varchar(8000) NULL,
    PHONE varchar(8000) NULL,
    ID_PROOF_TYPE varchar(8000) NULL,
    ID_NO varchar(8000) NULL,
    COMMUNAL_NONCOMMUNAL varchar(8000) NULL,
    ACTIVE_INACTIVE varchar(8000) NULL,
    LATEST_BIND_OVER_DATE varchar(8000) NULL,
    LBO_YEAR varchar(8000) NULL,
    PRESENT_ACTIVITY varchar(8000) NULL,
    PHOTO_ID varchar(8000) NULL,
    remarks varchar(8000) NULL,
    PS_TRANSFER_STATUS varchar(8000) NULL,
    COUNT_OF_INVD_CASES varchar(8000) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDAT_CIVILSUPPLY
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdat_civilsupply (
    CDAT_CVSPLY_KEY BIGSERIAL NOT NULL,
    DISTRICT varchar(20) NULL,
    NAME_OFFICE varchar(150) NULL,
    SHOP_NO varchar(25) NULL,
    RATION_CARD_NO varchar(50) NULL,
    CARD_TYPE varchar(20) NULL,
    FULLNAME varchar(150) NULL,
    GENDER varchar(25) NULL,
    AGE varchar(20) NULL,
    HOF varchar(50) NULL,
    CARD_POOL varchar(50) NULL,
    UID_NO varchar(50) NULL,
    FULLADDRESS varchar(500) NULL,
    PHONE varchar(25) NULL,
    GAS_CONNECTION_STATUS varchar(100) NULL,
    CUSTOMER_NO varchar(50) NULL,
    GAS_COMPANY_NAME varchar(50) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDAT_GAS_DETAILS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdat_gas_details (
    CDAT_GAS_UID numeric(18, 0) NOT NULL,
    CUSTOMER_ID numeric(18, 0) NULL,
    NAME varchar(250) NOT NULL,
    ADDRESS varchar(1000) NULL,
    DOB varchar(15) NULL,
    PHONE varchar(15) NULL,
    COMPANY varchar(50) NULL,
    AGENCY_NO varchar(50) NULL,
    AGENCY_NAME_ADDRESS varchar(550) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDAT_LICENCE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdat_licence (
    CDAT_lIC_KEY BIGSERIAL NOT NULL,
    LICENCE_NO varchar(30) NULL,
    PHONE varchar(20) NULL,
    FULLNAME varchar(250) NULL,
    FATHER_NAME varchar(200) NULL,
    DOB varchar(20) NULL,
    GENDER varchar(10) NULL,
    BLOOD_GROUP varchar(500) NULL,
    IDENTIFICATION_MARKS varchar(150) NULL,
    FULLADDRESS varchar(500) NULL,
    TEMP_FULLADDRES varchar(500) NULL,
    ISSUE_DATE varchar(15) NULL,
    LICENCE_VALIDUPTO varchar(20) NULL,
    BADGE_NO varchar(15) NULL,
    ENTRY_DATE varchar(20) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDAT_PASSPORT
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdat_passport (
    CDAT_PASSPORT_KEY BIGSERIAL NOT NULL,
    PV_REFERENCE_NO varchar(100) NULL,
    SB_NUMBER varchar(100) NULL,
    PV_REQUEST_ID varchar(100) NULL,
    FILE_NUMBER varchar(100) NULL,
    PV_SEQUENCE_NO varchar(100) NULL,
    SR_NO varchar(100) NULL,
    DPHQID_NAME varchar(500) NULL,
    POLICE_STATION varchar(100) NULL,
    FULLNAME varchar(250) NULL,
    GENDER varchar(50) NULL,
    DOB date NULL,
    PLACE_OF_BIRTH varchar(100) NULL,
    SPOUSE_NAME varchar(100) NULL,
    FATHERNAME varchar(100) NULL,
    PV_INITIATION_DATE date NULL,
    PV_REQUEST_STATUS varchar(100) NULL,
    VERIFICTATION_ADDRESS varchar(1000) NULL,
    FULLADDRESS varchar(1000) NULL,
    EMAIL_ID varchar(100) NULL,
    PHONE varchar(50) NULL,
    COMMENTS varchar(1000) NULL,
    STATION_NAME varchar(500) NULL,
    DISTRICT_NAME varchar(500) NULL,
    VERIFIER varchar(100) NULL,
    STATUS varchar(100) NULL,
    VERIFIEDDATE date NULL,
    GPS varchar(1000) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDAT_PROVIDER_MASTER
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdat_provider_master (
    PROVIDER_KEY SMALLINT NOT NULL,
    PROVIDER varchar(50) NULL,
    PROVIDER_NAME varchar(50) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDAT_RTA
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdat_rta (
    Regn_No varchar(500) NULL,
    FULLNAME varchar(500) NULL,
    DOB varchar(25) NULL,
    FATHERNAME varchar(500) NULL,
    PHONE varchar(500) NULL,
    FULLADDRESS varchar(500) NULL,
    City varchar(500) NULL,
    District varchar(500) NULL,
    PIN_CODE varchar(500) NULL,
    ENG_NO varchar(500) NULL,
    CHAS_NO varchar(500) NULL,
    MKR_NAME varchar(500) NULL,
    MKR_CLAS varchar(500) NULL,
    MFG_YR varchar(25) NULL,
    COLOUR varchar(500) NULL,
    Seat_Capacity varchar(500) NULL,
    TR_Number varchar(500) NULL,
    VEH_CLASS varchar(500) NULL,
    BDY_TYPE varchar(500) NULL,
    RVD_CC varchar(500) NULL,
    Cylinder varchar(500) NULL,
    Fuel varchar(500) NULL,
    HP varchar(500) NULL,
    OWNFR_DT varchar(100) NULL,
    OWNTO_DT varchar(100) NULL,
    FC_Validity varchar(100) NULL,
    Permit_Validity varchar(100) NULL,
    Insurance_Validity varchar(100) NULL,
    Tax_Validity varchar(100) NULL,
    OldNewFlag varchar(500) NULL,
    Old_Regn_No varchar(500) NULL,
    Registration_Status varchar(500) NULL,
    Suspension_From varchar(100) NULL,
    Suspension_To varchar(100) NULL,
    STATE_CD varchar(500) NULL,
    VCLASS_ID varchar(500) NULL,
    VEH_TYPE varchar(500) NULL,
    APPLICANT_NAME varchar(500) NULL,
    O_STATUS varchar(500) NULL,
    RVC_VCL_ID varchar(500) NULL,
    EMAIL_ID varchar(500) NULL,
    DISTRICT_CD varchar(500) NULL,
    PERMIT_NO varchar(500) NULL,
    FC_NO varchar(500) NULL,
    CREATED_DT varchar(100) NULL,
    UPDATED_DT varchar(100) NULL,
    OFF_CD varchar(500) NULL,
    ISS_DT varchar(100) NULL,
    FIRST_DT varchar(100) NULL,
    Valid_Upto varchar(100) NULL,
    TheftFlag varchar(100) NULL,
    TIREFLAG varchar(100) NULL,
    Approved_Date varchar(100) NULL,
    UpdateFlag varchar(100) NULL,
    Owner_Photo varchar(260) NULL,
    Owner_Thumb varchar(260) NULL,
    TOWFlag varchar(525) NULL,
    Insurance_No varchar(500) NULL,
    INSURANCE_COMP_NAME varchar(500) NULL,
    IsFinanced varchar(500) NULL,
    FinancerDetails varchar(450) NULL,
    AADHARNO varchar(50) NULL,
    PANNUMBER varchar(50) NULL,
    Remarks varchar(100) NULL,
    asondate TIMESTAMP NOT NULL
);

-- ------------------------------------------------------------


-- TABLE: CDAT_STATE_MASTER
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdat_state_master (
    STATE_KEY SMALLINT NOT NULL,
    STATE VARCHAR(255) NULL,
    CAPITAL VARCHAR(255) NULL,
    DESCRIPTION VARCHAR(255) NULL
);

-- ------------------------------------------------------------


-- TABLE: CDATADDRESS_OLD
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdataddress_old (
    CDAT_SDR_KEY BIGSERIAL NOT NULL,
    PHONE varchar(15) NOT NULL,
    TITLE varchar(10) NULL,
    SURNAME varchar(15) NULL,
    FIRSTNAME varchar(50) NULL,
    MIDDLENAME varchar(50) NULL,
    LASTNAME varchar(50) NULL,
    FULLNAME varchar(255) NULL,
    ADDRESS1 varchar(250) NULL,
    ADDRESS2 varchar(100) NULL,
    ADDRESS3 varchar(100) NULL,
    FULLADDRESS varchar(1000) NULL,
    CITY varchar(255) NULL,
    DISTRICT varchar(50) NULL,
    STATE varchar(50) NULL,
    PINCODE varchar(10) NULL,
    COUNTRY varchar(30) NULL,
    NATIONALITY varchar(30) NULL,
    DOA date NOT NULL,
    PERMANENTADDRESS varchar(500) NULL,
    FATHERNAME varchar(80) NULL,
    RETAILER_DETAILS varchar(50) NULL,
    DISTRIBUTOR_DETAILS varchar(50) NULL,
    CONNECTION_TYPE varchar(20) NULL,
    CATEGORY_TYPE varchar(100) NULL,
    CAF_NO varchar(50) NULL,
    POI_NAME varchar(100) NULL,
    POI_NO varchar(100) NULL,
    CURRENT_STATUS varchar(50) NULL,
    REF_CONTACT_NAME varchar(50) NULL,
    REF_CONTACT_ADDRESS varchar(150) NULL,
    REF_CONTACT_NO varchar(150) NULL,
    SUBSCRIBER_STATUS varchar(50) NULL,
    REVER_STATUS varchar(50) NULL,
    MOBILE_PORTABILITY varchar(50) NULL,
    POA_NAME varchar(60) NULL,
    POA_NO varchar(50) NULL,
    POA_ADDRESS varchar(150) NULL,
    ALT_CNT_NO varchar(40) NULL,
    EMAILADDRESS varchar(50) NULL,
    BARRED varchar(10) NULL,
    GIVENNAME varchar(50) NULL,
    GENDER varchar(12) NULL,
    DOB date NULL,
    LRN_CODE int NULL,
    POI_ADDRESS varchar(255) NULL,
    PER_ADDRESS1 varchar(100) NULL,
    PER_ADDRESS2 varchar(100) NULL,
    PER_ADDRESS3 varchar(100) NULL,
    PER_PINCODE int NULL,
    PER_CITY varchar(50) NULL,
    PER_DISTRICT varchar(50) NULL,
    PER_STATE varchar(50) NULL,
    REMARKS varchar(255) NULL,
    OPERATOR varchar(25) NULL,
    EFF_FROM_DATE TIMESTAMP NULL,
    EFF_TO_DATE TIMESTAMP NULL,
    imsi varchar(20) NULL
);

-- ------------------------------------------------------------


-- TABLE: MCC_MNC
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mcc_mnc (
    MCC int NULL,
    MNC int NULL,
    PERVIOUS_OPERATOR varchar(1000) NULL,
    STATE varchar(1000) NULL,
    PRESENT_OPERATOR varchar(100) NULL
);

-- ------------------------------------------------------------


-- TABLE: MNC_CODES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mnc_codes (
    STATE varchar(150) NULL,
    operators varchar(150) NULL,
    MCC_MNC varchar(50) NULL
);

-- ------------------------------------------------------------


-- TABLE: NDPS_ABSTRACT_1
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ndps_abstract_1 (
    PHONE varchar(2000) NULL,
    ROLE varchar(2000) NULL,
    NICKNAME varchar(2000) NULL,
    FNAME varchar(2000) NULL,
    ADDRESS varchar(2000) NULL,
    CITY varchar(2000) NULL,
    STATE varchar(2000) NULL,
    COUNTRY varchar(2000) NULL,
    PIN varchar(2000) NULL,
    CRIME_NO varchar(2000) NULL,
    YEAR varchar(2000) NULL,
    DOO varchar(2000) NULL,
    PLACE_OF_OFF varchar(2000) NULL,
    DOR varchar(2000) NULL,
    CRIME_HEAD varchar(2000) NULL,
    MO varchar(2000) NULL,
    SEC_OF_LAW varchar(2000) NULL,
    UNIT varchar(2000) NULL,
    MODULE_NAME varchar(2000) NULL,
    ISACTIVE varchar(2000) NULL,
    LNAME varchar(2000) NULL,
    CHECKFLAG varchar(2000) NULL,
    DOB_YEAR varchar(2000) NULL,
    IMEINUMBER varchar(2000) NULL,
    INC_OFFICER varchar(2000) NULL,
    CATEGORY varchar(2000) NULL,
    ORGANISATION varchar(2000) NULL,
    ASONDATE varchar(2000) NULL,
    REMARKS varchar(2000) NULL,
    Date_of_Arrest varchar(2000) NULL,
    CCNO varchar(2000) NULL,
    IDPROOF varchar(2000) NULL
);

-- ------------------------------------------------------------


-- =============================================================================
-- PostgreSQL-native application tables (upload pipeline + user management)
-- =============================================================================

CREATE TABLE IF NOT EXISTS document_jobs (
    job_id                  BIGSERIAL PRIMARY KEY,
    module                  VARCHAR(20) NOT NULL,
    source_file             TEXT NOT NULL,
    source_basename         TEXT NOT NULL,
    file_path               TEXT NOT NULL,
    file_sha256             CHAR(64) NOT NULL,
    status                  VARCHAR(30) NOT NULL DEFAULT 'queued',
    phase                   VARCHAR(40),
    operator                VARCHAR(20),
    target_phone            VARCHAR(25),
    mssql_database          VARCHAR(128),
    total_rows_estimated    BIGINT,
    rows_committed          BIGINT NOT NULL DEFAULT 0,
    last_checkpoint_key     BIGINT NOT NULL DEFAULT 0,
    last_source_row_no      BIGINT NOT NULL DEFAULT 0,
    batch_size              INTEGER NOT NULL DEFAULT 500,
    phase_state             JSONB NOT NULL DEFAULT '{}'::jsonb,
    error_message           TEXT,
    dry_run                 BOOLEAN NOT NULL DEFAULT FALSE,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    completed_at            TIMESTAMPTZ,
    UNIQUE (module, source_file, file_sha256)
);

CREATE TABLE IF NOT EXISTS upload_staging_batches (
    batch_id            BIGSERIAL PRIMARY KEY,
    document_job_id     BIGINT NOT NULL UNIQUE REFERENCES document_jobs (job_id) ON DELETE CASCADE,
    upload_log_id       BIGINT,
    module              VARCHAR(20) NOT NULL,
    staging_tables      JSONB NOT NULL DEFAULT '{}'::jsonb,
    verification_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    verified_at         TIMESTAMPTZ,
    verified_by         VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS upload_activity_logs (
    id                  BIGSERIAL PRIMARY KEY,
    user_id             BIGINT,
    username            VARCHAR(100),
    module_name         VARCHAR(150) NOT NULL,
    file_name           TEXT NOT NULL,
    file_size           BIGINT NOT NULL DEFAULT 0,
    total_records       BIGINT NOT NULL DEFAULT 0,
    inserted_records    BIGINT NOT NULL DEFAULT 0,
    failed_records      BIGINT NOT NULL DEFAULT 0,
    upload_status       VARCHAR(30) NOT NULL DEFAULT 'Processing',
    error_reason        TEXT,
    ip_address          VARCHAR(45),
    db_name             VARCHAR(128),
    table_name          VARCHAR(128),
    is_new_table        VARCHAR(10),
    content_fingerprint VARCHAR(128),
    document_job_id     BIGINT,
    staging_batch_id    BIGINT,
    verification_status VARCHAR(30),
    uploaded_at         TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS upload_approval_queue (
    queue_id          BIGSERIAL PRIMARY KEY,
    batch_id          BIGINT NOT NULL REFERENCES upload_staging_batches (batch_id) ON DELETE CASCADE,
    module            VARCHAR(20) NOT NULL,
    username          VARCHAR(100) NOT NULL DEFAULT '',
    status            VARCHAR(20) NOT NULL DEFAULT 'queued',
    inserted_records  BIGINT,
    error_message     TEXT,
    queued_at         TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    started_at        TIMESTAMPTZ,
    completed_at      TIMESTAMPTZ
);

CREATE TABLE IF NOT EXISTS cdatpcsuspect_staging (
    staging_id          BIGSERIAL PRIMARY KEY,
    import_job_id       BIGINT NOT NULL REFERENCES document_jobs (job_id),
    source_row_number   BIGINT NOT NULL,
    ucid                BIGINT NOT NULL,
    phone               VARCHAR(25),
    other               VARCHAR(50),
    starttime           TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    duration            NUMERIC(5,0) NOT NULL,
    incoming            SMALLINT NOT NULL,
    imeinumber          NUMERIC(18,0) NOT NULL,
    imsinumber          NUMERIC(18,0),
    celltowerid         VARCHAR(50),
    otherinfo           VARCHAR(50),
    tower_key           NUMERIC(18,0),
    provider_key        SMALLINT NOT NULL,
    state_key           SMALLINT,
    first_cellid        VARCHAR(50),
    last_cellid         VARCHAR(50),
    roaming_nw          VARCHAR(50),
    call_type           VARCHAR(25),
    calling_no          VARCHAR(50),
    called_no           VARCHAR(50),
    asondate            TIMESTAMP WITHOUT TIME ZONE,
    operator            VARCHAR(20) NOT NULL,
    source_file         TEXT NOT NULL,
    imported_at         TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE SEQUENCE IF NOT EXISTS cdr_import_ucid_seq START WITH -1 INCREMENT BY -1;

CREATE OR REPLACE VIEW cdr_import_jobs AS
SELECT
    job_id,
    source_file,
    source_basename,
    file_sha256,
    COALESCE(operator, '') AS operator,
    target_phone,
    status,
    NULL::INTEGER AS header_line_no,
    total_rows_estimated::INTEGER AS total_rows_estimated,
    rows_committed,
    last_source_row_no,
    batch_size,
    error_message,
    dry_run,
    created_at,
    updated_at,
    completed_at
FROM document_jobs
WHERE module = 'cdr';

CREATE TABLE IF NOT EXISTS logins (
    id              BIGSERIAL    PRIMARY KEY,
    username        VARCHAR(100) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    role            VARCHAR(50)  NOT NULL DEFAULT 'user',
    fullname        VARCHAR(255) NOT NULL DEFAULT '',
    created_at      TIMESTAMP    NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP    NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS user_sessions (
    session_id      VARCHAR(128) PRIMARY KEY,
    user_id         INT,
    username        VARCHAR(100),
    role            VARCHAR(50),
    ip_address      VARCHAR(45),
    created_at      TIMESTAMP    NOT NULL DEFAULT NOW(),
    expires_at      TIMESTAMP    NOT NULL,
    last_active_at  TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_activity_logs (
    id              BIGSERIAL    PRIMARY KEY,
    username        VARCHAR(100),
    module          VARCHAR(100),
    action          VARCHAR(100),
    detail          TEXT,
    ip_address      VARCHAR(45),
    created_at      TIMESTAMP    NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS user_quick_links (
    id              BIGSERIAL    PRIMARY KEY,
    username        VARCHAR(100) NOT NULL,
    label           TEXT         NOT NULL,
    url             TEXT         NOT NULL,
    sort_order      INT          NOT NULL DEFAULT 0,
    created_at      TIMESTAMP    NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS admin_query_logs (
    id              BIGSERIAL    PRIMARY KEY,
    username        VARCHAR(100),
    query_text      TEXT,
    executed_at     TIMESTAMP    NOT NULL DEFAULT NOW(),
    duration_ms     INT,
    row_count       INT
);

-- =============================================================================
-- Indexes for query performance
-- =============================================================================
CREATE INDEX IF NOT EXISTS idx_cdatpcsuspect_phone        ON cdatpcsuspect (phone);
CREATE INDEX IF NOT EXISTS idx_cdatpcsuspect_other        ON cdatpcsuspect (other);
CREATE INDEX IF NOT EXISTS idx_cdatpcsuspect_starttime    ON cdatpcsuspect (starttime);
CREATE INDEX IF NOT EXISTS idx_cdatpcsuspect_celltowerid  ON cdatpcsuspect (celltowerid);
CREATE INDEX IF NOT EXISTS idx_cdatpcsuspect_provider_key ON cdatpcsuspect (provider_key);
CREATE INDEX IF NOT EXISTS idx_cdatsuspect_phone          ON cdatsuspect (phone);
CREATE INDEX IF NOT EXISTS idx_cdataddress_phone          ON cdataddress (phone);
CREATE INDEX IF NOT EXISTS idx_address_other_state_phone  ON address_other_state (phone);
CREATE INDEX IF NOT EXISTS idx_cdatcelltower_celltowerid  ON cdatcelltowerareanew (celltowerid);
CREATE INDEX IF NOT EXISTS idx_cdatcelltower_state_key    ON cdatcelltowerareanew (state_key);
CREATE INDEX IF NOT EXISTS idx_cdatcelltower_provider_key ON cdatcelltowerareanew (provider_key);
CREATE INDEX IF NOT EXISTS idx_cdatphonearea_prefix       ON cdatphonearea (phoneprefix);
CREATE INDEX IF NOT EXISTS idx_logins_username            ON logins (username);
CREATE INDEX IF NOT EXISTS idx_document_jobs_status       ON document_jobs (status);
CREATE INDEX IF NOT EXISTS idx_document_jobs_module       ON document_jobs (module, status);
CREATE INDEX IF NOT EXISTS idx_user_activity_logs_user    ON user_activity_logs (username);
CREATE INDEX IF NOT EXISTS idx_upload_staging_batches_status ON upload_staging_batches (verification_status);
CREATE INDEX IF NOT EXISTS idx_upload_logs_staging_batch  ON upload_activity_logs (staging_batch_id);
CREATE INDEX IF NOT EXISTS idx_upload_logs_document_job   ON upload_activity_logs (document_job_id);
CREATE INDEX IF NOT EXISTS idx_upload_approval_queue_module_status ON upload_approval_queue (module, status, queued_at);
CREATE UNIQUE INDEX IF NOT EXISTS idx_upload_approval_queue_batch_active
    ON upload_approval_queue (batch_id)
    WHERE status IN ('queued', 'running');
CREATE INDEX IF NOT EXISTS idx_cdatpcsuspect_staging_job
    ON cdatpcsuspect_staging (import_job_id, source_row_number);

-- =============================================================================
-- Additional tables from Excel inventory (missed in first pass)
-- =============================================================================

-- ------------------------------------------------------------
