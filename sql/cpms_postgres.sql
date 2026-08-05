CREATE TABLE IF NOT EXISTS tbladmin (
    id SERIAL PRIMARY KEY,
    adminname VARCHAR(120),
    username VARCHAR(120),
    mobilenumber BIGINT,
    email VARCHAR(120),
    password VARCHAR(200),
    adminregdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tblcategory (
    id SERIAL PRIMARY KEY,
    categoryname VARCHAR(200),
    creationdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tblpass (
    id SERIAL PRIMARY KEY,
    passnumber VARCHAR(200),
    fullname VARCHAR(200),
    contactnumber BIGINT,
    email VARCHAR(200),
    identitytype VARCHAR(200),
    identitycardno VARCHAR(200),
    category VARCHAR(100),
    fromdate VARCHAR(200),
    todate VARCHAR(200),
    passcreationdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO tbladmin (id, adminname, username, mobilenumber, email, password, adminregdate)
VALUES (1, 'CampCodes', 'admin', 1234567890, 'adminuser@gmail.com', 'f925916e2754e5e03f75dd58a5733251', '2020-04-14 06:44:27')
ON CONFLICT (id) DO NOTHING;

INSERT INTO tblcategory (id, categoryname, creationdate) VALUES
(1, 'Logistic Deliveries', '2020-04-14 07:27:32'),
(2, 'Cleaning', '2020-04-14 07:49:09'),
(3, 'Essential Services', '2020-04-14 07:49:22'),
(4, 'eccomerce delivery boys', '2020-04-14 07:49:47'),
(5, 'Medical Supply', '2020-04-14 07:50:36'),
(8, 'Buy Grocery', '2020-06-30 15:05:55')
ON CONFLICT (id) DO NOTHING;

INSERT INTO tblpass (id, passnumber, fullname, contactnumber, email, identitytype, identitycardno, category, fromdate, todate, passcreationdate) VALUES
(1, '286529906', 'Yogesh Kumar', 4654464646, 'yogi@gmail.com', 'Adhar Card', 'AD-122346', 'Cleaning', '2020-04-14', '2020-05-14', '2020-04-14 11:47:03'),
(2, '915773340', 'Suresh Khanna', 9879878978, 'suresh@gmail.com', 'Any Other Govt Issued Doc', 'KTI-896567', 'Essential Services', '2020-04-14', '2020-07-31', '2020-04-13 11:50:15'),
(3, '884595667', 'Lyndon Bermoy', 1234567890, 'serbermz2020@gmail.com', 'Voter Card', '5235252', 'Essential Services', '2020-04-16', '2020-04-19', '2020-04-16 02:38:27'),
(4, '189062898', 'Jonah Juarez', 123456789, 'jonah@gmail.com', 'Passport', '123456789', 'Buy Grocery', '2020-07-14', '2020-07-21', '2020-06-30 15:07:05')
ON CONFLICT (id) DO NOTHING;

SELECT setval(pg_get_serial_sequence('tbladmin', 'id'), GREATEST((SELECT COALESCE(MAX(id), 1) FROM tbladmin), 1));
SELECT setval(pg_get_serial_sequence('tblcategory', 'id'), GREATEST((SELECT COALESCE(MAX(id), 1) FROM tblcategory), 1));
SELECT setval(pg_get_serial_sequence('tblpass', 'id'), GREATEST((SELECT COALESCE(MAX(id), 1) FROM tblpass), 1));
