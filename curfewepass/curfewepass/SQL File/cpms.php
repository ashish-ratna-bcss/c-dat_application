-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2020 at 05:12 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cpms`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `ID` int(10) NOT NULL,
  `AdminName` varchar(120) DEFAULT NULL,
  `UserName` varchar(120) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(120) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `AdminRegdate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`ID`, `AdminName`, `UserName`, `MobileNumber`, `Email`, `Password`, `AdminRegdate`) VALUES
(1, 'CampCodes', 'admin', 1234567890, 'adminuser@gmail.com', 'f925916e2754e5e03f75dd58a5733251', '2020-04-14 06:44:27');

-- --------------------------------------------------------

--
-- Table structure for table `tblcategory`
--

CREATE TABLE `tblcategory` (
  `ID` int(10) NOT NULL,
  `CategoryName` varchar(200) DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tblcategory`
--

INSERT INTO `tblcategory` (`ID`, `CategoryName`, `CreationDate`) VALUES
(1, 'Logistic Deliveries', '2020-04-14 07:27:32'),
(2, 'Cleaning', '2020-04-14 07:49:09'),
(3, 'Essential Services', '2020-04-14 07:49:22'),
(4, 'eccomerce delivery boys', '2020-04-14 07:49:47'),
(5, 'Medical Supply', '2020-04-14 07:50:36'),
(8, 'Buy Grocery', '2020-06-30 15:05:55');

-- --------------------------------------------------------

--
-- Table structure for table `tblpass`
--

CREATE TABLE `tblpass` (
  `ID` int(10) NOT NULL,
  `PassNumber` varchar(200) DEFAULT NULL,
  `FullName` varchar(200) DEFAULT NULL,
  `ContactNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `IdentityType` varchar(200) DEFAULT NULL,
  `IdentityCardno` varchar(200) DEFAULT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `FromDate` varchar(200) DEFAULT NULL,
  `ToDate` varchar(200) DEFAULT NULL,
  `PasscreationDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tblpass`
--

INSERT INTO `tblpass` (`ID`, `PassNumber`, `FullName`, `ContactNumber`, `Email`, `IdentityType`, `IdentityCardno`, `Category`, `FromDate`, `ToDate`, `PasscreationDate`) VALUES
(1, '286529906', 'Yogesh Kumar', 4654464646, 'yogi@gmail.com', 'Adhar Card', 'AD-122346', 'Cleaning', '2020-04-14', '2020-05-14', '2020-04-14 11:47:03'),
(2, '915773340', 'Suresh Khanna', 9879878978, 'suresh@gmail.com', 'Any Other Govt Issued Doc', 'KTI-896567', 'Essential Services', '2020-04-14', '2020-07-31', '2020-04-13 11:50:15'),
(3, '884595667', 'Lyndon Bermoy', 1234567890, 'serbermz2020@gmail.com', 'Voter Card', '5235252', 'Essential Services', '2020-04-16', '2020-04-19', '2020-04-16 02:38:27'),
(4, '189062898', 'Jonah Juarez', 123456789, 'jonah@gmail.com', 'Passport', '123456789', 'Buy Grocery', '2020-07-14', '2020-07-21', '2020-06-30 15:07:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblcategory`
--
ALTER TABLE `tblcategory`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblpass`
--
ALTER TABLE `tblpass`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblcategory`
--
ALTER TABLE `tblcategory`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tblpass`
--
ALTER TABLE `tblpass`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



ALTER TABLE cdatcelltowerareanew ADD COLUMN IF NOT EXISTS bts_id character varying;
ALTER TABLE cdatcelltowerareanew ADD COLUMN IF NOT EXISTS lat character varying;
ALTER TABLE cdatcelltowerareanew ADD COLUMN IF NOT EXISTS long character varying;
ALTER TABLE cdatcelltowerareanew ADD COLUMN IF NOT EXISTS azimuth character varying;
 
 
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS alias_name character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS father_name character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS age character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS date_of_birth character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS nationality character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS occupation character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS income_group character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS regular_habits character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS category character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS present_address text;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS crime_head character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS mo character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS crime_no character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS year character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS sec_of_law character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS police_station character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS date_of_arrest timestamp without time zone;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS aadhar_no character varying;
ALTER TABLE ir_particulars ADD COLUMN IF NOT EXISTS mobile character varying;



ALTER TABLE LOGINS ADD ROLE VARCHAR(50);
ALTER TABLE LOGINS ADD FULLNAME VARCHAR(100);

 
CREATE TABLE IF NOT EXISTS user_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER,
    username VARCHAR(100) NOT NULL,
    fullname VARCHAR(200),
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP,
    session_duration INTEGER,
    ip_address VARCHAR(45),
    browser_info TEXT,
    device_info VARCHAR(50),
    session_token VARCHAR(64)
);
 
CREATE TABLE IF NOT EXISTS user_activity_logs (
    id SERIAL PRIMARY KEY,
    session_id INTEGER,
    user_id INTEGER,
    username VARCHAR(100) NOT NULL,
    module_name VARCHAR(150) NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    search_data JSONB, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
 
CREATE INDEX IF NOT EXISTS idx_user_sessions_username ON user_sessions(username);
CREATE INDEX IF NOT EXISTS idx_user_activity_logs_username ON user_activity_logs(username);


CREATE TABLE upload_activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    total_records INT DEFAULT 0,
    inserted_records INT DEFAULT 0,
    failed_records INT DEFAULT 0,
    upload_status VARCHAR(20) NOT NULL, -- 'Success', 'Partial', 'Failed'
    error_reason TEXT,
    ip_address VARCHAR(45) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
 
-- Index for fast audit listing page loads (admin_upload_history.php)
CREATE INDEX idx_upload_logs_uploaded_at ON upload_activity_logs(uploaded_at DESC);
CREATE INDEX idx_upload_logs_username ON upload_activity_logs(username);

-- Additional columns for tracking database and table names
ALTER TABLE upload_activity_logs ADD COLUMN IF NOT EXISTS db_name VARCHAR(100);
ALTER TABLE upload_activity_logs ADD COLUMN IF NOT EXISTS table_name VARCHAR(100);
ALTER TABLE upload_activity_logs ADD COLUMN IF NOT EXISTS is_new_table VARCHAR(10) DEFAULT 'No';