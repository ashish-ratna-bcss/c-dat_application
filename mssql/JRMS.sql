USE [master]
GO
/****** Object:  Database [JRMS]    Script Date: 13-Aug-26 6:04:44 PM ******/
CREATE DATABASE [JRMS]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'jrms', FILENAME = N'D:\SQL SOFTWARE 2016 INSTALLATION\MSSQL13.DAU_HYD_2023\MSSQL\DATA\jrms.mdf' , SIZE = 32438976KB , MAXSIZE = UNLIMITED, FILEGROWTH = 1024KB )
 LOG ON 
( NAME = N'jrms_log', FILENAME = N'D:\SQL SOFTWARE 2016 INSTALLATION\MSSQL13.DAU_HYD_2023\MSSQL\DATA\jrms_log.ldf' , SIZE = 6272KB , MAXSIZE = UNLIMITED, FILEGROWTH = 10%)
GO
ALTER DATABASE [JRMS] SET COMPATIBILITY_LEVEL = 120
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [JRMS].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [JRMS] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [JRMS] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [JRMS] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [JRMS] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [JRMS] SET ARITHABORT OFF 
GO
ALTER DATABASE [JRMS] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [JRMS] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [JRMS] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [JRMS] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [JRMS] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [JRMS] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [JRMS] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [JRMS] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [JRMS] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [JRMS] SET  DISABLE_BROKER 
GO
ALTER DATABASE [JRMS] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [JRMS] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [JRMS] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [JRMS] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [JRMS] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [JRMS] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [JRMS] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [JRMS] SET RECOVERY FULL 
GO
ALTER DATABASE [JRMS] SET  MULTI_USER 
GO
ALTER DATABASE [JRMS] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [JRMS] SET DB_CHAINING OFF 
GO
ALTER DATABASE [JRMS] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [JRMS] SET TARGET_RECOVERY_TIME = 0 SECONDS 
GO
ALTER DATABASE [JRMS] SET DELAYED_DURABILITY = DISABLED 
GO
EXEC sys.sp_db_vardecimal_storage_format N'JRMS', N'ON'
GO
USE [JRMS]
GO
/****** Object:  User [HAFEEZ]    Script Date: 13-Aug-26 6:04:44 PM ******/
CREATE USER [HAFEEZ] WITHOUT LOGIN WITH DEFAULT_SCHEMA=[dbo]
GO
ALTER ROLE [db_owner] ADD MEMBER [HAFEEZ]
GO
ALTER ROLE [db_accessadmin] ADD MEMBER [HAFEEZ]
GO
ALTER ROLE [db_securityadmin] ADD MEMBER [HAFEEZ]
GO
ALTER ROLE [db_ddladmin] ADD MEMBER [HAFEEZ]
GO
ALTER ROLE [db_backupoperator] ADD MEMBER [HAFEEZ]
GO
ALTER ROLE [db_datareader] ADD MEMBER [HAFEEZ]
GO
ALTER ROLE [db_datawriter] ADD MEMBER [HAFEEZ]
GO
ALTER ROLE [db_denydatareader] ADD MEMBER [HAFEEZ]
GO
ALTER ROLE [db_denydatawriter] ADD MEMBER [HAFEEZ]
GO
/****** Object:  Schema [nikesh]    Script Date: 13-Aug-26 6:04:44 PM ******/
CREATE SCHEMA [nikesh]
GO
/****** Object:  Table [dbo].[1]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[1](
	[CIN] [varchar](max) NOT NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
SET ANSI_PADDING OFF
ALTER TABLE [dbo].[1] ADD [OTHER] [varchar](15) NOT NULL
ALTER TABLE [dbo].[1] ADD [CNT_OTHER] [int] NULL
ALTER TABLE [dbo].[1] ADD [RANK1] [bigint] NULL

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[11]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[11](
	[CIN] [varchar](max) NOT NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[OTHER] [varchar](15) NOT NULL,
	[CNT_OTHER] [int] NULL,
	[RANK1] [bigint] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[2017_FP_LIST]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[2017_FP_LIST](
	[slno] [varchar](5000) NULL,
	[ps] [varchar](5000) NULL,
	[crime_no] [varchar](5000) NULL,
	[Section] [varchar](5000) NULL,
	[Tin No ] [varchar](5000) NULL,
	[Date of Identity] [varchar](5000) NULL,
	[Loss Property] [varchar](5000) NULL,
	[Name & Particulars] [varchar](5000) NULL,
	[Arrested] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[2018_FP_LIST]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[2018_FP_LIST](
	[slno] [varchar](5000) NULL,
	[ps] [varchar](5000) NULL,
	[crime_no] [varchar](5000) NULL,
	[Section] [varchar](5000) NULL,
	[Id PIN] [varchar](5000) NULL,
	[Date of Identity] [varchar](5000) NULL,
	[Loss of Property] [varchar](5000) NULL,
	[Name & Particulars] [varchar](5000) NULL,
	[Dt  of Arrest] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[a]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[a](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](50) NULL,
	[REMARKS] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[aa]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[aa](
	[name] [varchar](max) NOT NULL,
	[cin] [varchar](max) NOT NULL,
	[psarrested] [varchar](max) NULL,
	[fathersname] [varchar](max) NOT NULL,
	[name1] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[AB]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING OFF
GO
CREATE TABLE [dbo].[AB](
	[PHONE] [varchar](15) NOT NULL,
	[FIRST_CALL] [datetime] NULL,
	[LAST_CALL] [datetime] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ABC]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ABC](
	[PSArrested] [varchar](max) NULL,
	[CRIMENOS] [varchar](max) NULL,
	[NAME] [varchar](max) NOT NULL,
	[FATHERSNAME] [varchar](max) NOT NULL,
	[JAILNAME] [varchar](max) NULL,
	[ADMISSION_TO_JAIL] [varchar](max) NULL,
	[RELEASEDT] [varchar](max) NULL,
	[MOBILENO] [varchar](max) NULL,
	[IMSI] [varchar](20) NULL,
	[DOA] [date] NULL,
	[FIRST_CALL] [datetime] NULL,
	[LAST_CALL] [datetime] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[abcd]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[abcd](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[abcde]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[abcde](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[AC]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[AC](
	[CIN] [varchar](max) NOT NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[IMSI] [varchar](20) NULL,
	[DOA] [date] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ALL_PS_TS_49]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ALL_PS_TS_49](
	[PSARRESTED] [varchar](500) NULL,
	[DISTRICT] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[all_ts_ps]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[all_ts_ps](
	[CommissionerateName ] [varchar](5000) NULL,
	[MobileNo ] [varchar](5000) NULL,
	[AddlDCPAddl Sp] [varchar](5000) NULL,
	[PHONE] [varchar](5000) NULL,
	[ACPDSPSDPO] [varchar](5000) NULL,
	[CELLNO] [varchar](5000) NULL,
	[CICircleOffice] [varchar](5000) NULL,
	[Mobile ] [varchar](5000) NULL,
	[SHO] [varchar](5000) NULL,
	[Mobile1] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[AR]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[AR](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[PERIOD_OF_OFFENCE] [varchar](100) NULL,
	[REGULAR_RESIDENCE] [varchar](500) NULL,
	[PREPARATION_OF_OFFENCE] [varchar](500) NULL,
	[AFTER_OFFENCE] [varchar](500) NULL,
	[INDULGANCE_BEFORE_OFFENCE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[SUB_TYPE] [varchar](500) NULL,
	[MO] [varchar](2000) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[PLACE_OF_ARREST] [varchar](500) NULL,
	[SUB_DIVISION] [varchar](100) NULL,
	[DISTRICT_OR_UNIT] [varchar](100) NULL,
	[ARRESTED_BY] [varchar](500) NULL,
	[INTERROGATED_BY] [varchar](500) NULL,
	[OTHERS_WHO_CAN_IDENTIFY] [varchar](500) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ARRESTED_FEB]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ARRESTED_FEB](
	[phone] [varchar](8000) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](8000) NULL,
	[CRIME_HEAD] [varchar](8000) NULL,
	[CRIME_NO] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[DOO] [varchar](8000) NULL,
	[PLACE_OF_OFF] [varchar](8000) NULL,
	[DOR] [int] NULL,
	[MO] [varchar](8000) NULL,
	[SEC_OF_LAW] [varchar](8000) NULL,
	[UNIT] [varchar](8000) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[DOB_YEAR] [int] NULL,
	[FNAME] [int] NULL,
	[ADDRESS] [varchar](8000) NULL,
	[CITY] [varchar](8000) NULL,
	[STATE] [varchar](8000) NULL,
	[COUNTRY] [varchar](2) NOT NULL,
	[PIN] [varchar](2) NOT NULL,
	[REMARK] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[IMEINUMBER] [int] NULL,
	[ASONDATE] [datetime] NOT NULL,
	[INC_OFFICER] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ARRESTED_MARCH]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ARRESTED_MARCH](
	[phone] [varchar](8000) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](8000) NULL,
	[CRIME_HEAD] [varchar](8000) NULL,
	[CRIME_NO] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[DOO] [varchar](8000) NULL,
	[PLACE_OF_OFF] [varchar](8000) NULL,
	[DOR] [int] NULL,
	[MO] [varchar](8000) NULL,
	[SEC_OF_LAW] [varchar](8000) NULL,
	[UNIT] [varchar](8000) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[DOB_YEAR] [int] NULL,
	[FNAME] [int] NULL,
	[ADDRESS] [varchar](8000) NULL,
	[CITY] [varchar](8000) NULL,
	[STATE] [varchar](8000) NULL,
	[COUNTRY] [varchar](2) NOT NULL,
	[PIN] [varchar](2) NOT NULL,
	[REMARK] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[IMEINUMBER] [int] NULL,
	[ASONDATE] [datetime] NOT NULL,
	[INC_OFFICER] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[b]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[b](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](50) NULL,
	[REMARKS] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[b_jrms]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[b_jrms](
	[NAME] [varchar](max) NOT NULL,
	[FATHERSNAME] [varchar](max) NOT NULL,
	[PSARRESTED] [varchar](max) NULL,
	[PRISONERNO] [varchar](max) NULL,
	[JAILNAME] [varchar](max) NULL,
	[ADMISSION_TO_JAIL] [varchar](max) NULL,
	[RELEASEDT] [varchar](max) NULL,
	[ADDR_DURINGRELEASE] [varchar](max) NULL,
	[HEADOFCRIME] [varchar](max) NOT NULL,
	[CRIMENOS] [varchar](max) NULL,
	[MOBILENO] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CDAT_JRMS_FEB17]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CDAT_JRMS_FEB17](
	[PHONE] [numeric](10, 0) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](334) NOT NULL,
	[CRIME_HEAD] [varchar](50) NOT NULL,
	[CRIME_NO] [varchar](max) NULL,
	[YEAR] [int] NULL,
	[DOO] [int] NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [date] NULL,
	[MO] [varchar](50) NOT NULL,
	[SEC_OF_LAW] [int] NULL,
	[UNIT] [varchar](100) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[DOB_YEAR] [int] NULL,
	[FNAME] [varchar](99) NOT NULL,
	[ADDRESS] [varchar](500) NULL,
	[CITY] [int] NULL,
	[STATE] [int] NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[REMARK] [varchar](104) NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[IMEINUMBER] [int] NULL,
	[ASONDATE] [datetime] NOT NULL,
	[INC_OFFICER] [varchar](104) NULL,
	[MODULE_NAME] [varchar](50) NOT NULL,
	[MONIT_STATUS] [int] NULL,
	[CATEGORY] [int] NULL,
	[ORGANISATION] [varchar](4) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CDAT_JRMS_TOTAL]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CDAT_JRMS_TOTAL](
	[PHONE] [numeric](10, 0) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](334) NOT NULL,
	[CRIME_HEAD] [varchar](50) NOT NULL,
	[CRIME_NO] [varchar](max) NULL,
	[YEAR] [int] NULL,
	[DOO] [int] NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [date] NULL,
	[MO] [varchar](50) NOT NULL,
	[SEC_OF_LAW] [int] NULL,
	[UNIT] [varchar](100) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[DOB_YEAR] [int] NULL,
	[FNAME] [varchar](99) NOT NULL,
	[ADDRESS] [varchar](500) NULL,
	[CITY] [int] NULL,
	[STATE] [int] NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[REMARK] [varchar](104) NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[IMEINUMBER] [int] NULL,
	[ASONDATE] [datetime] NOT NULL,
	[INC_OFFICER] [varchar](104) NULL,
	[MODULE_NAME] [varchar](50) NOT NULL,
	[MONIT_STATUS] [int] NULL,
	[CATEGORY] [int] NULL,
	[ORGANISATION] [varchar](4) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CDATPCSUSPECT]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CDATPCSUSPECT](
	[UCID] [int] NOT NULL,
	[PHONE] [varchar](15) NOT NULL,
	[OTHER] [varchar](15) NOT NULL,
	[STARTTIME] [datetime] NOT NULL,
	[DURATION] [numeric](5, 0) NOT NULL,
	[INCOMING] [tinyint] NOT NULL,
	[IMEINUMBER] [numeric](15, 0) NOT NULL,
	[IMSINUMBER] [numeric](18, 0) NULL,
	[CELLTOWERID] [varchar](50) NULL,
	[OTHERINFO] [varchar](50) NULL,
	[TOWER_KEY] [numeric](18, 0) NULL,
	[PROVIDER_KEY] [tinyint] NOT NULL,
	[STATE_KEY] [tinyint] NULL,
	[FIRST_CELLID] [varchar](50) NULL,
	[LAST_CELLID] [varchar](50) NULL,
	[ROAMING_NW] [varchar](50) NULL,
	[CALL_TYPE] [varchar](25) NULL,
	[CALLING_NO] [varchar](50) NULL,
	[CALLED_NO] [varchar](50) NULL,
	[ASONDATE] [datetime] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CHANCHALGUDA_2010_TO_2019]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CHANCHALGUDA_2010_TO_2019](
	[CIN_1] [varchar](max) NOT NULL,
	[PSArrested_1] [varchar](max) NULL,
	[Name_1] [varchar](max) NOT NULL,
	[PrisonerNo_1] [varchar](max) NULL,
	[Gender_1] [varchar](max) NULL,
	[TypeofRelease_1] [varchar](max) NULL,
	[JailName_1] [varchar](max) NULL,
	[Admission_to_Jail_1] [varchar](max) NULL,
	[ReleaseDt_1] [varchar](max) NULL,
	[Addr_DuringRelease_1] [varchar](max) NULL,
	[HeadofCrime_1] [varchar](max) NOT NULL,
	[IdentificationMark_1] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark_1] [varchar](max) NOT NULL,
	[RlDtOrder_1] [varchar](max) NULL,
	[CrimeNos_1] [varchar](max) NULL,
	[FathersName_1] [varchar](max) NOT NULL,
	[MobileNo_1] [varchar](max) NULL,
	[JailRefId_1] [varchar](max) NULL,
	[DISTRICT_1] [varchar](500) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CHERLAPALLI]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CHERLAPALLI](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[ID_PROOF] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CHERLAPALLI_2010_TO_2019]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CHERLAPALLI_2010_TO_2019](
	[CIN] [varchar](max) NOT NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CPCommissionarate]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CPCommissionarate](
	[CommissionerateNameSP] [varchar](500) NULL,
	[MobileNo ] [varchar](500) NULL,
	[MailID] [varchar](500) NULL,
	[DCPSP] [varchar](500) NULL,
	[MobileNo1] [varchar](500) NULL,
	[MailID1] [varchar](500) NULL,
	[AddlDCPAddlSP] [varchar](500) NULL,
	[MobileNo2] [varchar](500) NULL,
	[MailID2] [varchar](500) NULL,
	[ACPDSP(SDPO)] [varchar](500) NULL,
	[MobileNo3] [varchar](500) NULL,
	[MailID3] [varchar](500) NULL,
	[CICircleOffice] [varchar](500) NULL,
	[MobileNo4] [varchar](500) NULL,
	[MailID4] [varchar](500) NULL,
	[SHO] [varchar](500) NULL,
	[MobileNo5] [varchar](500) NULL,
	[MailID5] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CYB_RACHAKONDA_PS]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CYB_RACHAKONDA_PS](
	[COMMISSIONERATENAMESP] [varchar](8000) NULL,
	[SHO] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[dup_sus]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[dup_sus](
	[A] [float] NULL,
	[PHONE] [nvarchar](255) NULL,
	[ROLE] [nvarchar](255) NULL,
	[NICKNAME] [nvarchar](255) NULL,
	[CRIME_HEAD] [nvarchar](255) NULL,
	[CRIME_NO] [nvarchar](255) NULL,
	[YEAR] [nvarchar](255) NULL,
	[DOO] [nvarchar](255) NULL,
	[PLACE_OF_OFF] [nvarchar](255) NULL,
	[DOR] [nvarchar](255) NULL,
	[MO] [nvarchar](255) NULL,
	[SEC_OF_LAW] [nvarchar](255) NULL,
	[UNIT] [nvarchar](255) NULL,
	[ISACTIVE] [nvarchar](255) NULL,
	[LNAME] [nvarchar](255) NULL,
	[DOB_YEAR] [nvarchar](255) NULL,
	[FNAME] [nvarchar](255) NULL,
	[ADDRESS] [nvarchar](255) NULL,
	[CITY] [nvarchar](255) NULL,
	[STATE] [nvarchar](255) NULL,
	[COUNTRY] [nvarchar](255) NULL,
	[PIN] [nvarchar](255) NULL,
	[REMARK] [nvarchar](255) NULL,
	[CHECKFLAG] [nvarchar](255) NULL,
	[IMEINUMBER] [nvarchar](255) NULL,
	[ASONDATE] [datetime] NULL,
	[INC_OFFICER] [nvarchar](255) NULL,
	[MODULE_NAME] [nvarchar](255) NULL,
	[MONIT_STATUS] [nvarchar](255) NULL,
	[CATEGORY] [nvarchar](255) NULL,
	[ORGANISATION] [nvarchar](255) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[FAMILY_NUMBERS_TO_UPDATE]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[FAMILY_NUMBERS_TO_UPDATE](
	[IRKEY] [decimal](18, 0) NOT NULL,
	[RELATIONSHIP] [varchar](100) NULL,
	[NAME] [varchar](50) NULL,
	[FATHER_OR_SPOUSE] [varchar](100) NULL,
	[OCCUPATION] [varchar](100) NULL,
	[PHONE] [varchar](50) NULL,
	[AGE] [varchar](50) NULL,
	[CRIMINAL_BACKGROUND] [varchar](100) NULL,
	[STATUS] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[Final_arrested_feb]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[Final_arrested_feb](
	[phone] [varchar](8000) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](8000) NULL,
	[FNAME] [varchar](8000) NULL,
	[address] [varchar](8000) NULL,
	[CITY] [int] NULL,
	[STATE] [varchar](8000) NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[CRIME_NO] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[DOO] [varchar](8000) NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [varchar](1) NOT NULL,
	[CRIME_HEAD] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[SEC_OF_LAW] [varchar](8000) NULL,
	[UNIT] [varchar](8000) NULL,
	[MODULE_NAME] [varchar](8000) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [int] NULL,
	[IMEINUMBER] [int] NULL,
	[INC_OFFICER] [varchar](8000) NULL,
	[CATEGORY] [varchar](10) NULL,
	[ORGANISATION] [varchar](30) NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[final_arrested_march]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[final_arrested_march](
	[phone] [varchar](8000) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](8000) NULL,
	[FNAME] [varchar](8000) NULL,
	[address] [varchar](8000) NULL,
	[CITY] [int] NULL,
	[STATE] [varchar](8000) NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[CRIME_NO] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[DOO] [varchar](8000) NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [varchar](1) NOT NULL,
	[CRIME_HEAD] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[SEC_OF_LAW] [varchar](8000) NULL,
	[UNIT] [varchar](8000) NULL,
	[MODULE_NAME] [varchar](8000) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [int] NULL,
	[IMEINUMBER] [int] NULL,
	[INC_OFFICER] [varchar](8000) NULL,
	[CATEGORY] [varchar](10) NULL,
	[ORGANISATION] [varchar](30) NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[G24_11_20]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[G24_11_20](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](100) NULL,
	[IRKEY] [bigint] NULL,
	[B] [varchar](20) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[hafeez]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[hafeez](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JAIL]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JAIL](
	[CIN] [varchar](5000) NULL,
	[TYPE] [varchar](5000) NULL,
	[NAME] [varchar](5000) NULL,
	[FATHERNAME] [varchar](5000) NULL,
	[AGE] [varchar](5000) NULL,
	[PHONE] [varchar](5000) NULL,
	[ID_PROOF] [varchar](5000) NULL,
	[ADDRESS] [varchar](5000) NULL,
	[STATE] [varchar](5000) NULL,
	[ADMISSION DATE] [varchar](5000) NULL,
	[RELEASEDATE] [varchar](5000) NULL,
	[MO] [varchar](5000) NULL,
	[CRIMENO] [varchar](5000) NULL,
	[SEC] [varchar](5000) NULL,
	[POLICE STATION] [varchar](5000) NULL,
	[JAIL] [varchar](5000) NULL,
	[STATUS] [varchar](5000) NULL,
	[HEIGHT] [varchar](5000) NULL,
	[PHYSICAL STATUS] [varchar](5000) NULL,
	[IDENTIFICATION MARKS] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JAIL_LATEST]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JAIL_LATEST](
	[CIN] [varchar](5000) NULL,
	[PSArrested] [varchar](5000) NULL,
	[Name] [varchar](5000) NULL,
	[PrisonerNo] [int] NULL,
	[Gender] [varchar](5000) NULL,
	[TypeofRelease] [varchar](5000) NULL,
	[Photo] [int] NULL,
	[JailName] [varchar](5000) NULL,
	[Admission_to_Jail] [varchar](5000) NULL,
	[ReleaseDt] [varchar](5000) NULL,
	[Addr_DuringRelease] [varchar](5000) NULL,
	[HeadofCrime] [varchar](5000) NULL,
	[IdentificationMark] [varchar](5000) NULL,
	[PlaceofIdentificationMark] [int] NULL,
	[RlDtOrder] [int] NULL,
	[CrimeNos] [varchar](5000) NULL,
	[FathersName] [varchar](5000) NULL,
	[MobileNo] [varchar](5000) NULL,
	[JailRefId] [int] NULL,
	[DISTRICT] [int] NULL,
	[UNIQUE_KEY] [int] NULL,
	[IRKEY] [int] NULL,
	[ASONDATE] [int] NULL,
	[APP_OR_MANUAL] [int] NULL,
	[DOB_AGE] [int] NULL,
	[IDPROOF_TYPE] [int] NULL,
	[IDPROOF_NO] [int] NULL,
	[SEC_OF_LAW] [varchar](5000) NULL,
	[REMARKS] [int] NULL,
	[ID_PROOF] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JMS_MULAKATH_FEB17]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JMS_MULAKATH_FEB17](
	[Sno] [bigint] NOT NULL,
	[JailRefId] [bigint] NOT NULL,
	[Surname_Mulakath] [varchar](99) NOT NULL,
	[Name_Mulakath] [varchar](99) NOT NULL,
	[Dt_of_Mulakath] [datetime] NOT NULL,
	[RelationwithAccused] [varchar](50) NOT NULL,
	[Address_Mulakath] [varchar](500) NOT NULL,
	[IdCardType] [varchar](20) NOT NULL,
	[IdCardNo_Mulakath] [varchar](99) NOT NULL,
	[MIs_AadharValidate] [char](1) NULL,
	[MobileNo_Mulakath] [numeric](10, 0) NULL,
	[MPhoto] [varbinary](max) NULL,
	[NoofVisits] [int] NOT NULL,
	[UpdatedDate] [datetime] NOT NULL,
	[CIN_Mulakath] [varchar](10) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JMS_MULAKATH_NOV]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JMS_MULAKATH_NOV](
	[Sno] [bigint] NOT NULL,
	[JailRefId] [bigint] NOT NULL,
	[Surname_Mulakath] [varchar](99) NOT NULL,
	[Name_Mulakath] [varchar](99) NOT NULL,
	[Dt_of_Mulakath] [datetime] NOT NULL,
	[RelationwithAccused] [varchar](50) NOT NULL,
	[Address_Mulakath] [varchar](500) NOT NULL,
	[IdCardType] [varchar](20) NOT NULL,
	[IdCardNo_Mulakath] [varchar](99) NOT NULL,
	[MIs_AadharValidate] [char](1) NULL,
	[MobileNo_Mulakath] [numeric](10, 0) NULL,
	[MPhoto] [varbinary](max) NULL,
	[NoofVisits] [int] NOT NULL,
	[UpdatedDate] [datetime] NOT NULL,
	[CIN_Mulakath] [varchar](10) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JMS_MulakathDtls]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JMS_MulakathDtls](
	[Sno] [bigint] NOT NULL,
	[JailRefId] [bigint] NOT NULL,
	[Surname_Mulakath] [varchar](99) NOT NULL,
	[Name_Mulakath] [varchar](99) NOT NULL,
	[Dt_of_Mulakath] [datetime] NOT NULL,
	[RelationwithAccused] [varchar](50) NOT NULL,
	[Address_Mulakath] [varchar](500) NOT NULL,
	[IdCardType] [varchar](20) NOT NULL,
	[IdCardNo_Mulakath] [varchar](99) NOT NULL,
	[MIs_AadharValidate] [char](1) NULL,
	[MobileNo_Mulakath] [numeric](10, 0) NULL,
	[MPhoto] [varbinary](max) NULL,
	[NoofVisits] [int] NOT NULL,
	[UpdatedDate] [datetime] NOT NULL,
	[CIN_Mulakath] [varchar](10) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_070717]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_070717](
	[Addr_DuringRelease] [varchar](500) NULL,
	[Admission_to_Jail] [varchar](500) NULL,
	[CIN] [varchar](500) NULL,
	[CrimeNos] [varchar](500) NULL,
	[Gender] [varchar](500) NULL,
	[HeadofCrime] [varchar](500) NULL,
	[IdentificationMark] [varchar](500) NULL,
	[JailName] [varchar](500) NULL,
	[Name] [varchar](500) NULL,
	[PSArrested] [varchar](500) NULL,
	[Photo] [varchar](500) NULL,
	[PhotoPath] [varchar](500) NULL,
	[PlaceofIdentificationMark] [varchar](500) NULL,
	[PrisonerNo] [varchar](500) NULL,
	[ReleaseDt] [varchar](500) NULL,
	[RlDtOrder] [varchar](500) NULL,
	[TypeofRelease] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_13SEP17]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_13SEP17](
	[Addr_DuringRelease] [varchar](500) NULL,
	[Admission_to_Jail] [varchar](500) NULL,
	[CIN] [varchar](500) NULL,
	[CrimeNos] [varchar](500) NULL,
	[FatherName] [varchar](500) NULL,
	[Gender] [varchar](500) NULL,
	[HeadofCrime] [varchar](500) NULL,
	[IdentificationMark] [varchar](500) NULL,
	[JailName] [varchar](500) NULL,
	[JailRefId] [varchar](500) NULL,
	[MobileNo] [varchar](500) NULL,
	[Name] [varchar](500) NULL,
	[PSArrested] [varchar](500) NULL,
	[Photo] [varchar](500) NULL,
	[PhotoPath] [varchar](500) NULL,
	[PlaceofIdentificationMark] [varchar](500) NULL,
	[PrisonerNo] [varchar](500) NULL,
	[ReleaseDt] [varchar](500) NULL,
	[RlDtOrder] [varchar](500) NULL,
	[TypeofRelease] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_2012TO2017_BACKUP_030221]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_2012TO2017_BACKUP_030221](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_ADDONS]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_ADDONS](
	[Addr_DuringRelease] [varchar](5000) NULL,
	[Admission_to_Jail] [varchar](5000) NULL,
	[CIN] [varchar](5000) NULL,
	[CrimeNos] [varchar](5000) NULL,
	[FatherName] [varchar](5000) NULL,
	[Gender] [varchar](5000) NULL,
	[HeadofCrime] [varchar](5000) NULL,
	[IdentificationMark] [varchar](5000) NULL,
	[JailName] [varchar](5000) NULL,
	[JailRefId] [varchar](5000) NULL,
	[MobileNo] [varchar](5000) NULL,
	[Name] [varchar](5000) NULL,
	[PSArrested] [varchar](5000) NULL,
	[Photo] [varchar](5000) NULL,
	[PhotoPath] [varchar](5000) NULL,
	[PlaceofIdentificationMark] [varchar](5000) NULL,
	[PrisonerNo] [varchar](5000) NULL,
	[ReleaseDt] [varchar](5000) NULL,
	[RlDtOrder] [varchar](5000) NULL,
	[TypeofRelease] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_BACKUP_09062021]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_BACKUP_09062021](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_BACKUP_151220]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_BACKUP_151220](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_BACKUP_24_04_2021]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_BACKUP_24_04_2021](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jrms_july2017]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jrms_july2017](
	[Addr_DuringRelease] [varchar](500) NULL,
	[Admission_to_Jail] [varchar](500) NULL,
	[CIN] [varchar](500) NULL,
	[CrimeNos] [varchar](500) NULL,
	[Gender] [varchar](500) NULL,
	[HeadofCrime] [varchar](500) NULL,
	[IdentificationMark] [varchar](500) NULL,
	[JailName] [varchar](500) NULL,
	[Name] [varchar](500) NULL,
	[PSArrested] [varchar](500) NULL,
	[Photo] [varchar](500) NULL,
	[PhotoPath] [varchar](500) NULL,
	[PlaceofIdentificationMark] [varchar](500) NULL,
	[PrisonerNo] [varchar](500) NULL,
	[ReleaseDt] [varchar](500) NULL,
	[RlDtOrder] [varchar](500) NULL,
	[TypeofRelease] [varchar](500) NULL,
	[phone] [varchar](500) NULL,
	[id_proof] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_NDPS_RELEASE_JAN_TILLDATE]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_NDPS_RELEASE_JAN_TILLDATE](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](50) NULL,
	[REMARKS] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jrms_photos]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jrms_photos](
	[MobileNo] [varchar](max) NULL,
	[photo] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jrms_total]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jrms_total](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS_TOTAL_2012_TO_2017]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS_TOTAL_2012_TO_2017](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[ID_PROOF] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jrms_total_old]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jrms_total_old](
	[CIN] [varchar](max) NOT NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jrms060919]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jrms060919](
	[CIN] [varchar](8000) NULL,
	[PSArrested] [varchar](8000) NULL,
	[Name] [varchar](8000) NULL,
	[PrisonerNo] [varchar](8000) NULL,
	[Gender] [varchar](8000) NULL,
	[TypeofRelease] [varchar](8000) NULL,
	[Photo] [varchar](8000) NULL,
	[JailName] [varchar](8000) NULL,
	[Admission_to_Jail] [varchar](8000) NULL,
	[ReleaseDt] [varchar](8000) NULL,
	[Addr_DuringRelease] [varchar](8000) NULL,
	[HeadofCrime] [varchar](8000) NULL,
	[IdentificationMark] [varchar](8000) NULL,
	[PlaceofIdentificationMark] [varchar](8000) NULL,
	[RlDtOrder] [varchar](8000) NULL,
	[CrimeNos] [varchar](8000) NULL,
	[FatherName] [varchar](8000) NULL,
	[MobileNo] [varchar](8000) NULL,
	[JailRefId] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jrms1]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jrms1](
	[CIN] [varchar](8000) NULL,
	[PSArrested] [varchar](8000) NULL,
	[Name] [varchar](8000) NULL,
	[PrisonerNo] [varchar](8000) NULL,
	[Gender] [varchar](8000) NULL,
	[TypeofRelease] [varchar](8000) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](8000) NULL,
	[Admission_to_Jail] [varchar](8000) NULL,
	[ReleaseDt] [varchar](8000) NULL,
	[Addr_DuringRelease] [varchar](8000) NULL,
	[HeadofCrime] [varchar](8000) NULL,
	[IdentificationMark] [varchar](8000) NULL,
	[PlaceofIdentificationMark] [varchar](8000) NULL,
	[RlDtOrder] [varchar](8000) NULL,
	[CrimeNos] [varchar](8000) NULL,
	[FatherName] [varchar](8000) NULL,
	[MobileNo] [varchar](8000) NULL,
	[JailRefId] [varchar](8000) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS1119]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS1119](
	[CIN] [varchar](8000) NULL,
	[PSArrested] [varchar](8000) NULL,
	[Name] [varchar](8000) NULL,
	[PrisonerNo] [varchar](8000) NULL,
	[Gender] [varchar](8000) NULL,
	[TypeofRelease] [varchar](8000) NULL,
	[Photo] [varchar](8000) NULL,
	[JailName] [varchar](8000) NULL,
	[Admission_to_Jail] [varchar](8000) NULL,
	[ReleaseDt] [varchar](8000) NULL,
	[Addr_DuringRelease] [varchar](8000) NULL,
	[HeadofCrime] [varchar](8000) NULL,
	[IdentificationMark] [varchar](8000) NULL,
	[PlaceofIdentificationMark] [varchar](8000) NULL,
	[RlDtOrder] [varchar](8000) NULL,
	[CrimeNos] [varchar](8000) NULL,
	[FatherName] [varchar](8000) NULL,
	[MobileNo] [varchar](8000) NULL,
	[JailRefId] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jrmsDataForHydPol]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jrmsDataForHydPol](
	[CIN] [varchar](10) NOT NULL,
	[JailRefId] [int] NULL,
	[PSArrested] [varchar](100) NULL,
	[Name] [varchar](334) NOT NULL,
	[PrisonerNo] [varchar](13) NULL,
	[Gender] [varchar](7) NULL,
	[TypeofRelease] [varchar](18) NULL,
	[Photo] [varbinary](max) NULL,
	[JailName] [varchar](99) NULL,
	[Admission_to_Jail] [varchar](30) NULL,
	[ReleaseDt] [varchar](30) NULL,
	[Addr_DuringRelease] [varchar](500) NULL,
	[HeadofCrime] [varchar](50) NOT NULL,
	[IdentificationMark] [varchar](50) NOT NULL,
	[PlaceofIdentificationMark] [varchar](99) NOT NULL,
	[MobileNo] [numeric](10, 0) NULL,
	[FathersName] [varchar](99) NOT NULL,
	[RlDtOrder] [date] NULL,
	[CrimeNos] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMSPS]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMSPS](
	[PSARRESTED] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MAHESH]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MAHESH](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MAHESH_ABC]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MAHESH_ABC](
	[NAME] [varchar](max) NOT NULL,
	[FATHERSNAME] [varchar](max) NOT NULL,
	[ADDR_DURINGRELEASE] [varchar](max) NULL,
	[MOBILENO] [varchar](max) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[PLACEOFIDENTIFICATIONMARK] [varchar](max) NOT NULL,
	[CRIMENOS] [varchar](max) NULL,
	[PSARRESTED] [varchar](max) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[HEADOFCRIME] [varchar](max) NOT NULL,
	[JAILNAME] [varchar](max) NULL,
	[ADMISSION_TO_JAIL] [varchar](max) NULL,
	[RELEASEDT] [varchar](max) NULL,
	[JAILREFID] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mahesh_dupli]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mahesh_dupli](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mahesh_duplicate1]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mahesh_duplicate1](
	[cin] [bigint] NULL,
	[count] [int] NULL,
	[crimenos] [varchar](max) NULL,
	[name] [varchar](max) NOT NULL,
	[admission_to_jail] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[Mahesh_jrms]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[Mahesh_jrms](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mahesh_pita]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mahesh_pita](
	[name] [varchar](max) NOT NULL,
	[fathersname] [varchar](max) NOT NULL,
	[psarrested] [varchar](max) NULL,
	[jailname] [varchar](max) NULL,
	[admission_to_jail] [varchar](max) NULL,
	[releasedt] [varchar](max) NULL,
	[addr_duringrelease] [varchar](max) NULL,
	[crimenos] [varchar](max) NULL,
	[mobileno] [varchar](max) NULL,
	[idproof_type] [varchar](100) NULL,
	[idproof_no] [varchar](20) NULL,
	[sec_of_law] [varchar](250) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MAIN_TABLE]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MAIN_TABLE](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mdkcdr]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mdkcdr](
	[phone] [varchar](15) NOT NULL,
	[OTHER] [varchar](15) NOT NULL,
	[starttime] [datetime] NOT NULL,
	[duration] [numeric](5, 0) NOT NULL,
	[incoming] [tinyint] NOT NULL,
	[IMEInumber] [numeric](15, 0) NOT NULL,
	[IMSInumber] [numeric](18, 0) NULL,
	[CELLtowerid] [varchar](50) NULL,
	[OTHERINFO] [varchar](50) NULL,
	[atw] [numeric](18, 0) NULL,
	[btw] [numeric](18, 0) NULL,
	[PROVIDER_KEY] [tinyint] NOT NULL,
	[STATE_KEY] [tinyint] NULL,
	[FIRST_CELLID] [varchar](50) NULL,
	[last_cellid] [varchar](50) NULL,
	[ROAMing_NW] [varchar](50) NULL,
	[CALL_TYPE] [varchar](25) NULL,
	[calling_no] [varchar](50) NULL,
	[called_no] [varchar](50) NULL,
	[ASONDATE] [datetime] NOT NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MO_NOT_HAVING_IR]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MO_NOT_HAVING_IR](
	[offender_id] [varchar](5000) NULL,
	[OFFENDERNAME] [varchar](5000) NULL,
	[ALIASES] [varchar](5000) NULL,
	[Father name] [varchar](5000) NULL,
	[DOB] [varchar](5000) NULL,
	[AGE] [varchar](5000) NULL,
	[PS] [varchar](5000) NULL,
	[MOBILENO] [varchar](5000) NULL,
	[PRESENT_ADDRESS] [varchar](5000) NULL,
	[Permanent_Address] [varchar](5000) NULL,
	[DATEOFLASTARREST] [varchar](5000) NULL,
	[PSARRESTED] [varchar](5000) NULL,
	[DATEOFRELEASE] [varchar](5000) NULL,
	[CURRENTACTIVITY] [varchar](5000) NULL,
	[PHOTOGRAPH_PATH] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MULAKATH_ENTRY]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MULAKATH_ENTRY](
	[JAIL_NAME] [varchar](1000) NULL,
	[PRISONER_NO] [varchar](1000) NULL,
	[PRISONER_NAME] [varchar](1000) NULL,
	[PRISONER_FATHER_NAME] [varchar](1000) NULL,
	[VISITOR_NAME] [varchar](1000) NULL,
	[VISITOR_PHONE_NO] [varchar](1000) NULL,
	[VISITOR_ID] [varchar](1000) NULL,
	[VISITOR_ADDRESS] [varchar](2000) NULL,
	[VISITOR_NAME_2] [varchar](1000) NULL,
	[VISITOR_PHONE_NO_2] [varchar](1000) NULL,
	[VISITOR_ID_2] [varchar](1000) NULL,
	[VISITOR_NAME_3] [varchar](1000) NULL,
	[VISITOR_PHONE_NO_3] [varchar](1000) NULL,
	[VISITOR_ID_3] [varchar](1000) NULL,
	[VISITOR_NAME_4] [varchar](1000) NULL,
	[VISITOR_PHONE_NO_4] [varchar](1000) NULL,
	[VISITOR_ID_4] [varchar](1000) NULL,
	[DATE_OF_MULAKATH] [varchar](1000) NULL,
	[CRIME_NO] [varchar](1000) NULL,
	[YEAR] [varchar](1000) NULL,
	[POLICE_STATION] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NDPS_CDRS]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NDPS_CDRS](
	[PHONE] [varchar](15) NOT NULL,
	[OTHER] [varchar](25) NOT NULL,
	[STARTTIME] [datetime] NOT NULL,
	[DURATION] [numeric](5, 0) NOT NULL,
	[INCOMING] [tinyint] NOT NULL,
	[IMEINUMBER] [numeric](15, 0) NOT NULL,
	[IMSINUMBER] [numeric](18, 0) NULL,
	[CELLTOWERID] [varchar](50) NULL,
	[OTHERINFO] [varchar](50) NULL,
	[TOWER_KEY] [numeric](18, 0) NULL,
	[PROVIDER_KEY] [tinyint] NOT NULL,
	[STATE_KEY] [tinyint] NULL,
	[FIRST_CELLID] [varchar](50) NULL,
	[LAST_CELLID] [varchar](50) NULL,
	[ROAMING_NW] [varchar](50) NULL,
	[CALL_TYPE] [varchar](25) NULL,
	[CALLING_NO] [varchar](50) NULL,
	[CALLED_NO] [varchar](50) NULL,
	[ASONDATE] [datetime] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NO_CHERLAPALLY_JRMS]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NO_CHERLAPALLY_JRMS](
	[NAME] [varchar](8000) NULL,
	[FATHERS_NAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[MOBILE] [varchar](8000) NULL,
	[AADHAAR] [varchar](8000) NULL,
	[CRIMENO] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[SEC_OF_LAW] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[ARREST_DATE] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[JAIL] [varchar](8000) NULL,
	[PHOTO ID] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NOV_TOTAL_DATA]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NOV_TOTAL_DATA](
	[PHONE] [numeric](10, 0) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](334) NOT NULL,
	[CRIME_HEAD] [varchar](50) NOT NULL,
	[CRIME_NO] [varchar](max) NULL,
	[YEAR] [int] NULL,
	[DOO] [int] NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [date] NULL,
	[MO] [varchar](50) NOT NULL,
	[SEC_OF_LAW] [int] NULL,
	[UNIT] [varchar](100) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[DOB_YEAR] [int] NULL,
	[FNAME] [varchar](99) NOT NULL,
	[ADDRESS] [varchar](500) NULL,
	[CITY] [int] NULL,
	[STATE] [int] NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[REMARK] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[IMEINUMBER] [int] NULL,
	[ASONDATE] [datetime] NOT NULL,
	[INC_OFFICER] [varchar](104) NULL,
	[MODULE_NAME] [varchar](50) NOT NULL,
	[MONIT_STATUS] [int] NULL,
	[CATEGORY] [int] NULL,
	[ORGANISATION] [varchar](4) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NOVDATA]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NOVDATA](
	[PHONE] [numeric](10, 0) NULL,
	[ROLE] [nvarchar](max) NULL,
	[NICKNAME] [nvarchar](max) NULL,
	[FNAME] [nvarchar](max) NULL,
	[ADDRESS] [nvarchar](max) NULL,
	[CITY] [int] NULL,
	[STATE] [int] NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[CRIME_NO] [nvarchar](max) NULL,
	[YEAR] [int] NULL,
	[DOO] [int] NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [nvarchar](max) NULL,
	[CRIME_HEAD] [nvarchar](max) NULL,
	[MO] [nvarchar](max) NULL,
	[SEC_OF_LAW] [int] NULL,
	[UNIT] [nvarchar](max) NULL,
	[MODULE_NAME] [nvarchar](max) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [int] NULL,
	[IMEINUMBER] [int] NULL,
	[INC_OFFICER] [nvarchar](max) NULL,
	[CATEGORY] [int] NULL,
	[ORGANISATION] [nvarchar](max) NULL,
	[ASONDATE] [datetime] NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[OCT24]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[OCT24](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[ID_PROOF] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[other_state_feb_march]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[other_state_feb_march](
	[PHONE] [varchar](8000) NULL,
	[NICKNAME] [varchar](8000) NULL,
	[FNAME] [int] NULL,
	[AGE] [int] NULL,
	[OCCUPATION] [int] NULL,
	[PRESENT_ADDRESS] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[DISTRICT] [int] NULL,
	[STATE] [varchar](8000) NULL,
	[LATITUDE] [int] NULL,
	[LONGITUDE] [int] NULL,
	[MO] [varchar](8000) NULL,
	[POLICE_STATION] [varchar](8000) NULL,
	[ZONE] [varchar](20) NULL,
	[CRIME_NO] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[SEC_OF_LAW] [varchar](8000) NULL,
	[DATE_OF_ARREST] [int] NULL,
	[PS SERIAL NO] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDCELL]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDCELL](
	[SLNO] [varchar](5000) NULL,
	[YEAR1] [varchar](5000) NULL,
	[DetenuName] [varchar](100) NULL,
	[Father/SpouseName] [varchar](250) NULL,
	[CategoryAsPerAct] [varchar](5000) NULL,
	[ModusOperandi] [varchar](5000) NULL,
	[SpecificCategory] [varchar](5000) NULL,
	[SubCategory] [varchar](5000) NULL,
	[PoliceStation] [varchar](5000) NULL,
	[ZONE] [varchar](5000) NULL,
	[Detention_Date] [varchar](5000) NULL,
	[DetenuNo] [varchar](5000) NULL,
	[AccusedReleasedOn] [varchar](5000) NULL,
	[WhetherInvolvedInTheCasesOfOtherUnits] [varchar](5000) NULL,
	[NameOfOtherUnits] [varchar](5000) NULL,
	[NOOFCASES] [varchar](5000) NULL,
	[TotalCasesAndGroundCases] [varchar](5000) NULL,
	[WhetherDetainedFor2ndTime] [varchar](8000) NULL,
	[District] [varchar](5000) NULL,
	[Nativity] [varchar](5000) NULL,
	[REMARKS] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDCELL_TOTAL]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDCELL_TOTAL](
	[SlNo ] [varchar](5000) NULL,
	[PadNo] [varchar](5000) NULL,
	[PLACE] [varchar](5000) NULL,
	[Year1] [varchar](5000) NULL,
	[FileNo] [varchar](5000) NULL,
	[DetenuName_Parentage] [varchar](5000) NULL,
	[CategoryAsPerAct] [varchar](5000) NULL,
	[ModusOperandi] [varchar](5000) NULL,
	[SpecificCategory] [varchar](5000) NULL,
	[SubCategory] [varchar](5000) NULL,
	[PoliceStation] [varchar](5000) NULL,
	[Zone] [varchar](5000) NULL,
	[OrdersIssuedOn] [varchar](5000) NULL,
	[WhetherOrderServed] [varchar](5000) NULL,
	[Detention_Date] [varchar](5000) NULL,
	[DetenuNo] [varchar](5000) NULL,
	[WhetherDetainedFor2ndTime] [varchar](5000) NULL,
	[ProclamationOrdersIssuedOn] [varchar](5000) NULL,
	[DateByWhichTranslationDocsToBeServed] [varchar](5000) NULL,
	[TranslationDocumentsServedOn] [varchar](5000) NULL,
	[DateByWhichBooksToBeForwardedToGovt ] [varchar](5000) NULL,
	[BooksForwardedToGovtOn] [varchar](5000) NULL,
	[WhetherApprovalOrderAvb ] [varchar](5000) NULL,
	[ApprovalOrdersNo&Date] [varchar](5000) NULL,
	[AdvisoryMeetingHeldOn] [varchar](5000) NULL,
	[WhetherADVBoardRecommendedForRevocation] [varchar](5000) NULL,
	[RevocationOrdersIfAny] [varchar](5000) NULL,
	[WhetherConfirmationRevocationOrderAvb] [varchar](5000) NULL,
	[ConfirmationRevokationOrdersNo&Date] [varchar](5000) NULL,
	[WhetherRepresentationsSubmitted] [varchar](5000) NULL,
	[DateOfReceiptOfRepn ] [varchar](5000) NULL,
	[DateOnWhichRemarksSubmittedToGovt ] [varchar](5000) NULL,
	[DisposalOfPetitioByTheGovtWithDate] [varchar](5000) NULL,
	[WhetherWPFiledIHighCourt] [varchar](5000) NULL,
	[IfWpFiledItsNo ] [varchar](5000) NULL,
	[Year2] [varchar](5000) NULL,
	[WhetherCounterFiled] [varchar](5000) NULL,
	[WhetherCounterCopyReceivedAfterSubmittingInCourtFromSHO] [varchar](5000) NULL,
	[CounterCopyAvb] [varchar](5000) NULL,
	[DateOfFilingOfCounterInHC] [varchar](5000) NULL,
	[WhetherCounterSentToGovt] [varchar](5000) NULL,
	[DateOnWhichCounterCopySentToGovt] [varchar](5000) NULL,
	[WhetherRevokedByTheCourt] [varchar](5000) NULL,
	[CourtJudgmentOn] [varchar](5000) NULL,
	[WhetherPreferredAnAppeal] [varchar](5000) NULL,
	[AppealNo ] [varchar](5000) NULL,
	[CourtJudgement1] [varchar](5000) NULL,
	[WhetherSLPFiledByAccusedInSC] [varchar](5000) NULL,
	[PetitionNo ] [varchar](5000) NULL,
	[Year3] [varchar](5000) NULL,
	[WhetherSLPFiledByDA] [varchar](5000) NULL,
	[SLPCopyAvb] [varchar](5000) NULL,
	[DateOfFilingOfSLPInSC] [varchar](5000) NULL,
	[WhetherSLPSentToGovt ] [varchar](5000) NULL,
	[DateOnWhichSLPCopySentToGovt] [varchar](5000) NULL,
	[WhetherRevokedByCourt] [varchar](5000) NULL,
	[CourtJudgment2] [varchar](5000) NULL,
	[AccusedReleasedOn] [varchar](5000) NULL,
	[WhetherInvolvedInTheCasesOfOtherUnits] [varchar](5000) NULL,
	[NameOfOtherUnits] [varchar](5000) NULL,
	[NoOfCases] [varchar](5000) NULL,
	[TotalCasesAndGroundCases] [varchar](5000) NULL,
	[WhetherDetainedIn2Time] [varchar](5000) NULL,
	[District] [varchar](5000) NULL,
	[Nativity] [varchar](5000) NULL,
	[REMARKS] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[photos_data2]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[photos_data2](
	[CIN] [varchar](max) NOT NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[photos_data3]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[photos_data3](
	[CIN] [varchar](max) NOT NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[Photo] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[rl]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[rl](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[RLINJAILCHER]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[RLINJAILCHER](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[rowdy]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[rowdy](
	[Rowdy_sheeter_id] [varchar](4000) NULL,
	[Irkey] [varchar](4000) NULL,
	[PDAct_Key] [varchar](4000) NULL,
	[Latest Arreste Date] [varchar](4000) NULL,
	[Name of the Ps] [varchar](4000) NULL,
	[Date of opening of Rowdy sheet  DD MM YY] [varchar](4000) NULL,
	[Rowdy sheet open Year] [varchar](4000) NULL,
	[name] [varchar](4000) NULL,
	[Age] [varchar](4000) NULL,
	[father_name] [varchar](4000) NULL,
	[Present address ] [varchar](4000) NULL,
	[Latitude ] [varchar](4000) NULL,
	[Langitude ] [varchar](4000) NULL,
	[Permanent address] [varchar](4000) NULL,
	[Latitude 1] [varchar](4000) NULL,
	[Langitude 2] [varchar](4000) NULL,
	[Phone number ] [varchar](4000) NULL,
	[ID proof Type] [varchar](4000) NULL,
	[ID_No] [varchar](4000) NULL,
	[Communal  Non Communal ] [varchar](4000) NULL,
	[Active In Active] [varchar](4000) NULL,
	[Latest Bind over date] [varchar](4000) NULL,
	[Year] [varchar](4000) NULL,
	[Present Activity ] [varchar](4000) NULL,
	[Photo (Soft copy)_ID] [varchar](4000) NULL,
	[Remarks ] [varchar](4000) NULL,
	[PS Transfer Status] [varchar](4000) NULL,
	[Count of involved  cases] [varchar](4000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[Sheet1$]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Sheet1$](
	[slno] [float] NULL,
	[Police Station] [nvarchar](255) NULL,
	[Crime No#] [nvarchar](255) NULL,
	[Section] [nvarchar](255) NULL,
	[Tin No#] [float] NULL,
	[Date of Identity] [datetime] NULL,
	[Loss Property] [nvarchar](255) NULL,
	[Name & Particulars] [nvarchar](255) NULL,
	[Arrested] [nvarchar](255) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[SNATCHING]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SNATCHING](
	[CIN] [varchar](max) NOT NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[CIN_1] [varchar](max) NOT NULL,
	[PSArrested_1] [varchar](max) NULL,
	[Name_1] [varchar](max) NOT NULL,
	[PrisonerNo_1] [varchar](max) NULL,
	[Gender_1] [varchar](max) NULL,
	[TypeofRelease_1] [varchar](max) NULL,
	[JailName_1] [varchar](max) NULL,
	[Admission_to_Jail_1] [varchar](max) NULL,
	[ReleaseDt_1] [varchar](max) NULL,
	[Addr_DuringRelease_1] [varchar](max) NULL,
	[HeadofCrime_1] [varchar](max) NOT NULL,
	[IdentificationMark_1] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark_1] [varchar](max) NOT NULL,
	[RlDtOrder_1] [varchar](max) NULL,
	[CrimeNos_1] [varchar](max) NULL,
	[FathersName_1] [varchar](max) NOT NULL,
	[MobileNo_1] [varchar](max) NULL,
	[JailRefId_1] [varchar](max) NULL,
	[DISTRICT_1] [varchar](500) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SUS1]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SUS1](
	[phone] [varchar](5000) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[nickname] [varchar](5000) NULL,
	[CRIME_HEAD] [varchar](5000) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[DOO] [varchar](5000) NULL,
	[PLACE_OF_OFF] [varchar](5000) NULL,
	[DOR] [varchar](5000) NULL,
	[MO] [varchar](5000) NULL,
	[UNIT] [varchar](5000) NULL,
	[ISACTIVE] [int] NULL,
	[LNAME] [int] NULL,
	[DOB_YEAR] [int] NULL,
	[FNAME] [int] NULL,
	[ADDRESS] [varchar](5000) NULL,
	[CITY] [int] NULL,
	[STATE] [int] NULL,
	[COUNTRY] [varchar](5) NOT NULL,
	[PIN] [int] NULL,
	[REMARKS] [int] NULL,
	[CHECKFLAG] [int] NULL,
	[IMEINUMBER] [int] NULL,
	[ASONDATE] [datetime] NOT NULL,
	[INC_OFFICER] [int] NULL,
	[MODULE_NAME] [int] NULL,
	[MONIT_STATUS] [int] NULL,
	[CATEGORY] [int] NULL,
	[ORGANISATION] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SUSPECT_IMAGE_TABLE_FORMAT]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SUSPECT_IMAGE_TABLE_FORMAT](
	[IRKEY] [varchar](4) NOT NULL,
	[CCNO] [varchar](4) NOT NULL,
	[IMAGE] [image] NULL,
	[MOBILENO] [varchar](max) NULL,
	[RANK] [varchar](1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[TEMP_AIRTEL_NEW]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[TEMP_AIRTEL_NEW](
	[TargetNo] [varchar](500) NULL,
	[CallType] [varchar](500) NULL,
	[TOC] [varchar](500) NULL,
	[BPartyNo] [varchar](500) NULL,
	[LRNNo] [varchar](500) NULL,
	[LRNTSPLSA] [varchar](500) NULL,
	[Date] [varchar](500) NULL,
	[Time] [varchar](500) NULL,
	[Duration] [varchar](500) NULL,
	[FirstBTS] [varchar](500) NULL,
	[FirstCGI] [varchar](500) NULL,
	[LastBTS] [varchar](500) NULL,
	[LastCGI] [varchar](500) NULL,
	[SMSCNo] [varchar](500) NULL,
	[Servicetype] [varchar](500) NULL,
	[IMEI] [varchar](500) NULL,
	[IMSI] [varchar](500) NULL,
	[CallFowNo] [varchar](500) NULL,
	[RoamNw] [varchar](500) NULL,
	[SW&MSCID] [varchar](500) NULL,
	[INTG] [varchar](500) NULL,
	[OUTTG] [varchar](500) NULL,
	[VowifiFirstUEIP] [varchar](500) NULL,
	[Port1] [varchar](500) NULL,
	[VowifiLastUEIP] [varchar](500) NULL,
	[Port2] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[TEMP_JIO1]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[TEMP_JIO1](
	[CallingPartyTelephoneNumber] [varchar](500) NULL,
	[CalledPartyTelephoneNumber] [varchar](500) NULL,
	[CallForwarding] [varchar](500) NULL,
	[LRNCalledNo] [varchar](500) NULL,
	[CallDate] [varchar](500) NULL,
	[CallTime] [varchar](500) NULL,
	[CallTerminationTime] [varchar](500) NULL,
	[CallDuration] [varchar](500) NULL,
	[FirstCellID] [varchar](500) NULL,
	[LastCellID] [varchar](500) NULL,
	[CallType] [varchar](500) NULL,
	[SMSCenterNumber] [varchar](500) NULL,
	[IMEI] [varchar](500) NULL,
	[IMSI] [varchar](500) NULL,
	[RoamingCircleName] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[TEMP_JRMS_OCT_2022]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[TEMP_JRMS_OCT_2022](
	[CIN] [bigint] NULL,
	[PSArrested] [varchar](max) NULL,
	[Name] [varchar](max) NOT NULL,
	[PrisonerNo] [varchar](max) NULL,
	[Gender] [varchar](max) NULL,
	[TypeofRelease] [varchar](max) NULL,
	[Photo] [varchar](max) NULL,
	[JailName] [varchar](max) NULL,
	[Admission_to_Jail] [varchar](max) NULL,
	[ReleaseDt] [varchar](max) NULL,
	[Addr_DuringRelease] [varchar](max) NULL,
	[HeadofCrime] [varchar](max) NOT NULL,
	[IdentificationMark] [varchar](max) NOT NULL,
	[PlaceofIdentificationMark] [varchar](max) NOT NULL,
	[RlDtOrder] [varchar](max) NULL,
	[CrimeNos] [varchar](max) NULL,
	[FathersName] [varchar](max) NOT NULL,
	[MobileNo] [varchar](max) NULL,
	[JailRefId] [varchar](max) NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNIQUE_KEY] [varchar](25) NULL,
	[IRKEY] [varchar](25) NULL,
	[ASONDATE] [date] NULL,
	[APP_OR_MANUAL] [varchar](20) NULL,
	[DOB_AGE] [date] NULL,
	[IDPROOF_TYPE] [varchar](100) NULL,
	[IDPROOF_NO] [varchar](20) NULL,
	[SEC_OF_LAW] [varchar](250) NULL,
	[REMARKS] [varchar](250) NULL,
	[AUTO_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[tempcdr]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[tempcdr](
	[UCID] [int] NOT NULL,
	[PHONE] [varchar](15) NOT NULL,
	[OTHER] [varchar](15) NOT NULL,
	[STARTTIME] [datetime] NOT NULL,
	[DURATION] [numeric](5, 0) NOT NULL,
	[INCOMING] [tinyint] NOT NULL,
	[IMEINUMBER] [numeric](15, 0) NOT NULL,
	[IMSINUMBER] [numeric](18, 0) NULL,
	[CELLTOWERID] [varchar](50) NULL,
	[OTHERINFO] [varchar](50) NULL,
	[TOWER_KEY] [numeric](18, 0) NULL,
	[PROVIDER_KEY] [tinyint] NOT NULL,
	[STATE_KEY] [tinyint] NULL,
	[FIRST_CELLID] [varchar](50) NULL,
	[LAST_CELLID] [varchar](50) NULL,
	[ROAMING_NW] [varchar](50) NULL,
	[CALL_TYPE] [varchar](25) NULL,
	[CALLING_NO] [varchar](50) NULL,
	[CALLED_NO] [varchar](50) NULL,
	[ASONDATE] [datetime] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[YES_CHERLAPALLY_JRMS]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[YES_CHERLAPALLY_JRMS](
	[IRKEY] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[CRIMENO] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[SEC_OF_LAW] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[MOBILE] [varchar](8000) NULL,
	[AADHAAR] [varchar](8000) NULL,
	[ARRESTED_DATE] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[JAIL] [varchar](8000) NULL,
	[PHOTO_ID] [varchar](8000) NULL,
	[OFFENDER_ID] [varchar](8000) NULL,
	[FATHERS_NAME] [varchar](200) NULL,
	[ADDRESS] [varchar](200) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [nikesh].[JRMS_FILTERED_FINAL]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [nikesh].[JRMS_FILTERED_FINAL](
	[PHONE] [numeric](10, 0) NULL,
	[ROLE] [nvarchar](max) NULL,
	[NICKNAME] [nvarchar](max) NULL,
	[CRIME_HEAD] [nvarchar](max) NULL,
	[CRIME_NO] [nvarchar](max) NULL,
	[YEAR] [nvarchar](max) NULL,
	[DOO] [int] NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [nvarchar](max) NULL,
	[MO] [nvarchar](max) NULL,
	[SEC_OF_LAW] [nvarchar](max) NULL,
	[UNIT] [nvarchar](max) NULL,
	[ISACTIVE] [int] NULL,
	[LNAME] [int] NULL,
	[DOB_YEAR] [int] NULL,
	[FNAME] [nvarchar](max) NULL,
	[ADDRESS] [nvarchar](max) NULL,
	[CITY] [nvarchar](max) NULL,
	[STATE] [nvarchar](max) NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[REMARK] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[IMEINUMBER] [int] NULL,
	[ASONDATE] [datetime] NOT NULL,
	[INC_OFFICER] [nvarchar](max) NULL,
	[MODULE_NAME] [int] NULL,
	[MONIT_STATUS] [int] NULL,
	[CATEGORY] [varchar](1) NOT NULL,
	[ORGANISATION] [int] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  StoredProcedure [dbo].[JRMS_IR_SEARCH]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROC [dbo].[JRMS_IR_SEARCH] (
@NAME VARCHAR(20) ,
@FNAME VARCHAR(20) 
)
AS BEGIN
DECLARE @NAME1 VARCHAR(20) , @FNAME1 VARCHAR(20)
SET @NAME1=@NAME
SET @FNAME1=@FNAME
IF 
LEN(@NAME1)<2 PRINT 'PLEASE ENTER VALID NAME MORE THAN OR EQUAL TO 3 CHARACTERS'
ELSE
SELECT DISTINCT CIN,UNIQUE_KEY,IRKEY,NAME,FATHERSNAME,ADDR_DURINGRELEASE,HEADOFCRIME,CRIMENOS,
PSARRESTED
FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE NAME LIKE '%'+@NAME1+'%' AND FATHERSNAME LIKE '%'+@FNAME1+'%' 
IF 
LEN(@NAME1)<2   PRINT 'PLEASE ENTER VALID NAME MORE THAN OR EQUAL TO 3 CHARACTERS'
ELSE
SELECT DISTINCT A.IRKEY,A.NAME,A.FATHER_NAME,A.AADHAR_NO,A.PRESENT_ADDRESS,
CONVERT(VARCHAR(20),B.CRIME_NO)+'/'+CONVERT(VARCHAR(20),B.YEAR) CRNO,
B.CRIME_HEAD,B.POLICE_STATION,A.MOBILE
FROM IRFORMS..IR_PARTICULARS A
LEFT JOIN IRFORMS..OFFENCE_DETAILS B ON A.IRKEY=B.IRKEY
WHERE A.NAME LIKE '%'+@NAME1+'%' AND A.FATHER_NAME LIKE '%'+@FNAME1+'%'
END
GO
/****** Object:  StoredProcedure [dbo].[JRMS_IR_SEARCH_1]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROC [dbo].[JRMS_IR_SEARCH_1] (
@NAME VARCHAR(20) ,
@FNAME VARCHAR(20),
@ADD VARCHAR(20)
)
AS BEGIN
DECLARE @NAME1 VARCHAR(20) , @FNAME1 VARCHAR(20), @ADD1 VARCHAR(20)
SET @NAME1=@NAME
SET @FNAME1=@FNAME
SET @ADD1=@ADD
IF 
LEN(@NAME1)<2 PRINT 'PLEASE ENTER VALID NAME MORE THAN OR EQUAL TO 3 CHARACTERS'
ELSE
SELECT DISTINCT CIN,UNIQUE_KEY,IRKEY,NAME,FATHERSNAME,ADDR_DURINGRELEASE,HEADOFCRIME,CRIMENOS,
PSARRESTED
FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE NAME LIKE '%'+@NAME1+'%' AND FATHERSNAME LIKE '%'+@FNAME1+'%' 
AND ADDR_DURINGRELEASE LIKE '%'+@ADD1+'%'
IF 
LEN(@NAME1)<2   PRINT 'PLEASE ENTER VALID NAME MORE THAN OR EQUAL TO 3 CHARACTERS'
ELSE
SELECT DISTINCT A.IRKEY,A.NAME,A.FATHER_NAME,A.AADHAR_NO,A.PRESENT_ADDRESS,
CONVERT(VARCHAR(20),B.CRIME_NO)+'/'+CONVERT(VARCHAR(20),B.YEAR) CRNO,
B.CRIME_HEAD,B.POLICE_STATION,A.MOBILE
FROM IRFORMS..IR_PARTICULARS A
left JOIN IRFORMS..OFFENCE_DETAILS B ON A.IRKEY=B.IRKEY
WHERE A.NAME LIKE '%'+@NAME1+'%' AND A.FATHER_NAME LIKE '%'+@FNAME1+'%'
END
GO
/****** Object:  StoredProcedure [dbo].[JRMS_UPDATE]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROC [dbo].[JRMS_UPDATE] (
@CIN VARCHAR(100) ,
@UNIQUE_KEY VARCHAR(20),
@IRKEY VARCHAR(20)
)
AS BEGIN
DECLARE @CIN1 VARCHAR(100), @UNIQUE_KEY1 VARCHAR(20), @IRKEY1 VARCHAR(20), @FUN VARCHAR(120)
SET @CIN1=''''+REPLACE(@CIN,',',''',''')+''''
SET @UNIQUE_KEY1=@UNIQUE_KEY
SET @IRKEY1=@IRKEY
IF 
LEN(@CIN1)<2 PRINT 'PLEASE ENTER VALID CIN NUMBER'
ELSE
CREATE TABLE #T1 (CIN NVARCHAR (20) NULL)
SET @FUN='INSERT INTO #T1 SELECT '+REPLACE(@CIN1,',',' INSERT INTO #T1 SELECT ')
EXEC (@FUN)
UPDATE JRMS_TOTAL_2012_TO_2017 SET UNIQUE_KEY=@UNIQUE_KEY1, IRKEY=@IRKEY1, ASONDATE=GETDATE(),APP_OR_MANUAL=  'MANUAL_ENTRY'
WHERE CIN IN (SELECT DISTINCT CIN FROM #T1)
DROP TABLE #T1
END
GO
/****** Object:  StoredProcedure [dbo].[JRMS_UPDATES]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROCEDURE [dbo].[JRMS_UPDATES] AS


SELECT LTRIM(RTRIM(MOBILENO)) AS PHONE,'ACCUSED' ROLE,LTRIM(RTRIM(SUBSTRING(NAME,1,CHARINDEX('/',NAME)-1))) AS NICKNAME,LTRIM(RTRIM(HEADOFCRIME)) AS CRIME_HEAD,
LTRIM(RTRIM(CRIMENOS)) AS CRIME_NO,NULL YEAR,NULL DOO, 
NULL PLACE_OF_OFF,LTRIM(RTRIM(CONVERT(DATE,RELEASEDT))) AS DOR,LTRIM(RTRIM(HEADOFCRIME)) AS MO,NULL SEC_OF_LAW,
LTRIM(RTRIM(PSARRESTED)) AS  UNIT,'1' ISACTIVE,
NULL LNAME,NULL DOB_YEAR,LTRIM(RTRIM(FATHERNAME)) AS FNAME,LTRIM(RTRIM(Addr_DuringRelease)) AS ADDRESS,NULL CITY,
NULL STATE,NULL COUNTRY,NULL PIN,LTRIM(RTRIM(JailName))+'_JAIL' REMARK,'1' CHECKFLAG,NULL IMEINUMBER,GETDATE()  ASONDATE,
LTRIM(RTRIM(PSArrested))+'_SHO' INC_OFFICER,
LTRIM(RTRIM(HEADOFCRIME)) AS MODULE_NAME,NULL MONIT_STATUS,NULL CATEGORY,'JRMS' ORGANISATION FROM  #TEMP





SELECT CONVERT(VARCHAR,PHONE) AS PHONE, ROLE, NICKNAME, CRIME_HEAD, CRIME_NO, YEAR, DOO, PLACE_OF_OFF,
 DOR, MO, SEC_OF_LAW, UNIT, ISACTIVE, LNAME, DOB_YEAR, FNAME, ADDRESS,
  CITY, STATE, COUNTRY, PIN, REMARK, CHECKFLAG, IMEINUMBER, ASONDATE,
   INC_OFFICER, MODULE_NAME, MONIT_STATUS, CATEGORY, ORGANISATION FROM #TEMP1
   EXCEPT 
SELECT CONVERT(VARCHAR,PHONE) AS PHONE, ROLE, NICKNAME, CRIME_HEAD, CRIME_NO, YEAR, DOO, PLACE_OF_OFF,
 DOR, MO, SEC_OF_LAW, UNIT, ISACTIVE, LNAME, DOB_YEAR, FNAME, ADDRESS,
  CITY, STATE, COUNTRY, PIN, REMARK, CHECKFLAG, IMEINUMBER, ASONDATE,
   INC_OFFICER, MODULE_NAME, MONIT_STATUS, CATEGORY, ORGANISATION FROM CDAT_JRMS_FEB17

GO
/****** Object:  StoredProcedure [dbo].[Recover_Deleted_Data_Proc]    Script Date: 13-Aug-26 6:04:44 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Script Name: Recover_Deleted_Data_Proc
-- Script Type : Recovery Procedure 
-- Develop By: Muhammad Imran
-- Date Created: 15 Oct 2011
-- Modify Date: 22 Aug 2012
-- Version    : 3.1
-- Notes : Included BLOB data types for recovery.& Compatibile with Default , CS collation , Arabic_CI_AS.
 
--DROP PROCEDURE Recover_Deleted_Data_Proc
--GO
Create PROCEDURE [dbo].[Recover_Deleted_Data_Proc]
@Database_Name NVARCHAR(MAX),
@SchemaName_n_TableName NVARCHAR(Max),
@Date_From DATETIME='1900/01/01',
@Date_To DATETIME ='9999/12/31'
AS
 
DECLARE @RowLogContents VARBINARY(8000)
DECLARE @TransactionID NVARCHAR(Max)
DECLARE @AllocUnitID BIGINT
DECLARE @AllocUnitName NVARCHAR(Max)
DECLARE @SQL NVARCHAR(Max)
DECLARE @Compatibility_Level INT
 
 
SELECT @Compatibility_Level=dtb.compatibility_level
FROM
master.sys.databases AS dtb WHERE dtb.name=@Database_Name
 
IF ISNULL(@Compatibility_Level,0)<=80
BEGIN
    RAISERROR('The compatibility level should be equal to or greater SQL SERVER 2005 (90)',16,1)
    RETURN
END
 
IF (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE [TABLE_SCHEMA]+'.'+[TABLE_NAME]=@SchemaName_n_TableName)=0
BEGIN
    RAISERROR('Could not found the table in the defined database',16,1)
    RETURN
END
 
DECLARE @bitTable TABLE
(
  [ID] INT,
  [Bitvalue] INT
)
--Create table to set the bit position of one byte.
 
INSERT INTO @bitTable
SELECT 0,2 UNION ALL
SELECT 1,2 UNION ALL
SELECT 2,4 UNION ALL
SELECT 3,8 UNION ALL
SELECT 4,16 UNION ALL
SELECT 5,32 UNION ALL
SELECT 6,64 UNION ALL
SELECT 7,128
 
--Create table to collect the row data.
DECLARE @DeletedRecords TABLE
(
    [Row ID]            INT IDENTITY(1,1),
    [RowLogContents]    VARBINARY(8000),
    [AllocUnitID]       BIGINT,
    [Transaction ID]    NVARCHAR(Max),
    [FixedLengthData]   SMALLINT,
    [TotalNoOfCols]     SMALLINT,
    [NullBitMapLength]  SMALLINT,
    [NullBytes]         VARBINARY(8000),
    [TotalNoofVarCols]  SMALLINT,
    [ColumnOffsetArray] VARBINARY(8000),
    [VarColumnStart]    SMALLINT,
    [Slot ID]           INT,
    [NullBitMap]        VARCHAR(MAX)
     
)
--Create a common table expression to get all the row data plus how many bytes we have for each row.
;WITH RowData AS (
SELECT
 
[RowLog Contents 0] AS [RowLogContents] 
 
,[AllocUnitID] AS [AllocUnitID] 
 
,[Transaction ID] AS [Transaction ID]  
 
--[Fixed Length Data] = Substring (RowLog content 0, Status Bit A+ Status Bit B + 1,2 bytes)
,CONVERT(SMALLINT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) AS [FixedLengthData]  --@FixedLengthData
 
-- [TotalnoOfCols] =  Substring (RowLog content 0, [Fixed Length Data] + 1,2 bytes)
,CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2)
,REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 1, 2)))) as  [TotalNoOfCols]
 
--[NullBitMapLength]=ceiling([Total No of Columns] /8.0)
,CONVERT(INT, ceiling(CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2)
,REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 1, 2))))/8.0)) as [NullBitMapLength] 
 
--[Null Bytes] = Substring (RowLog content 0, Status Bit A+ Status Bit B + [Fixed Length Data] +1, [NullBitMapLength] )
,SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 3,
CONVERT(INT, ceiling(CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2)
,REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 1, 2))))/8.0))) as [NullBytes]
 
--[TotalNoofVarCols] = Substring (RowLog content 0, Status Bit A+ Status Bit B + [Fixed Length Data] +1, [Null Bitmap length] + 2 )
,(CASE WHEN SUBSTRING([RowLog Contents 0], 1, 1) In (0x10,0x30,0x70) THEN
CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0],
CONVERT(SMALLINT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 3
+ CONVERT(INT, ceiling(CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2)
,REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 1, 2))))/8.0)), 2))))  ELSE null  END) AS [TotalNoofVarCols] 
 
--[ColumnOffsetArray]= Substring (RowLog content 0, Status Bit A+ Status Bit B + [Fixed Length Data] +1, [Null Bitmap length] + 2 , [TotalNoofVarCols]*2 )
,(CASE WHEN SUBSTRING([RowLog Contents 0], 1, 1) In (0x10,0x30,0x70) THEN
SUBSTRING([RowLog Contents 0]
, CONVERT(SMALLINT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 3
+ CONVERT(INT, ceiling(CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2)
,REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 1, 2))))/8.0)) + 2
, (CASE WHEN SUBSTRING([RowLog Contents 0], 1, 1) In (0x10,0x30,0x70) THEN
CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0],
CONVERT(SMALLINT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 3
+ CONVERT(INT, ceiling(CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2)
,REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 1, 2))))/8.0)), 2))))  ELSE null  END)
* 2)  ELSE null  END) AS [ColumnOffsetArray] 
 
--  Variable column Start = Status Bit A+ Status Bit B + [Fixed Length Data] + [Null Bitmap length] + 2+([TotalNoofVarCols]*2)
,CASE WHEN SUBSTRING([RowLog Contents 0], 1, 1)In (0x10,0x30,0x70)
THEN  (
CONVERT(SMALLINT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 4 
 
+ CONVERT(INT, ceiling(CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2)
,REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 1, 2))))/8.0)) 
 
+ ((CASE WHEN SUBSTRING([RowLog Contents 0], 1, 1) In (0x10,0x30,0x70) THEN
CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0],
CONVERT(SMALLINT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 3
+ CONVERT(INT, ceiling(CONVERT(INT, CONVERT(BINARY(2), REVERSE(SUBSTRING([RowLog Contents 0], CONVERT(SMALLINT, CONVERT(BINARY(2)
,REVERSE(SUBSTRING([RowLog Contents 0], 2 + 1, 2)))) + 1, 2))))/8.0)), 2))))  ELSE null  END) * 2)) 
 
ELSE null End AS [VarColumnStart]
,[Slot ID]
FROM sys.fn_dblog(NULL, NULL)
WHERE
AllocUnitId IN
(SELECT [Allocation_unit_id] FROM sys.allocation_units allocunits
INNER JOIN sys.partitions partitions ON (allocunits.type IN (1, 3)  
AND partitions.hobt_id = allocunits.container_id) OR (allocunits.type = 2 
AND partitions.partition_id = allocunits.container_id)  
WHERE object_id=object_ID('' + @SchemaName_n_TableName + ''))
 
AND Context IN ('LCX_MARK_AS_GHOST', 'LCX_HEAP') AND Operation in ('LOP_DELETE_ROWS') 
And SUBSTRING([RowLog Contents 0], 1, 1)In (0x10,0x30,0x70)
 
/*Use this subquery to filter the date*/
AND [TRANSACTION ID] IN (SELECT DISTINCT [TRANSACTION ID] FROM    sys.fn_dblog(NULL, NULL) 
WHERE Context IN ('LCX_NULL') AND Operation in ('LOP_BEGIN_XACT')  
And [Transaction Name] In ('DELETE','user_transaction')
And  CONVERT(NVARCHAR(11),[Begin Time]) BETWEEN @Date_From AND @Date_To)),
 
--Use this technique to repeate the row till the no of bytes of the row.
N1 (n) AS (SELECT 1 UNION ALL SELECT 1),
N2 (n) AS (SELECT 1 FROM N1 AS X, N1 AS Y),
N3 (n) AS (SELECT 1 FROM N2 AS X, N2 AS Y),
N4 (n) AS (SELECT ROW_NUMBER() OVER(ORDER BY X.n)
           FROM N3 AS X, N3 AS Y)
 
 
 
INSERT INTO @DeletedRecords
SELECT  RowLogContents
        ,[AllocUnitID]
        ,[Transaction ID]
        ,[FixedLengthData]
        ,[TotalNoOfCols]
        ,[NullBitMapLength]
        ,[NullBytes]
        ,[TotalNoofVarCols]
        ,[ColumnOffsetArray]
        ,[VarColumnStart]
        ,[Slot ID]
         ---Get the Null value against each column (1 means null zero means not null)
        ,[NullBitMap]=(REPLACE(STUFF((SELECT ',' +
        (CASE WHEN [ID]=0 THEN CONVERT(NVARCHAR(1),(SUBSTRING(NullBytes, n, 1) % 2))  ELSE CONVERT(NVARCHAR(1),((SUBSTRING(NullBytes, n, 1) / [Bitvalue]) % 2)) END) --as [nullBitMap]
         
FROM
N4 AS Nums
Join RowData AS C ON n<=NullBitMapLength
Cross Join @bitTable WHERE C.[RowLogContents]=D.[RowLogContents] ORDER BY [RowLogContents],n ASC FOR XML PATH('')),1,1,''),',',''))
FROM RowData D
 
IF (SELECT COUNT(*) FROM @DeletedRecords)=0
BEGIN
    RAISERROR('There is no data in the log as per the search criteria',16,1)
    RETURN
END
 
DECLARE @ColumnNameAndData TABLE
(
 [Row ID]           int,
 [Rowlogcontents]   varbinary(Max),
 [NAME]             sysname,
 [nullbit]          smallint,
 [leaf_offset]      smallint,
 [length]           smallint,
 [system_type_id]   tinyint,
 [bitpos]           tinyint,
 [xprec]            tinyint,
 [xscale]           tinyint,
 [is_null]          int,
 [Column value Size]int,
 [Column Length]    int,
 [hex_Value]        varbinary(max),
 [Slot ID]          int,
 [Update]           int
)
 
--Create common table expression and join it with the rowdata table
-- to get each column details
/*This part is for variable data columns*/
--@RowLogContents, 
--(col.columnOffValue - col.columnLength) + 1,
--col.columnLength
--)
INSERT INTO @ColumnNameAndData
SELECT
[Row ID],
Rowlogcontents,
NAME ,
cols.leaf_null_bit AS nullbit,
leaf_offset,
ISNULL(syscolumns.length, cols.max_length) AS [length],
cols.system_type_id,
cols.leaf_bit_position AS bitpos,
ISNULL(syscolumns.xprec, cols.precision) AS xprec,
ISNULL(syscolumns.xscale, cols.scale) AS xscale,
SUBSTRING([nullBitMap], cols.leaf_null_bit, 1) AS is_null,
(CASE WHEN leaf_offset<1 and SUBSTRING([nullBitMap], cols.leaf_null_bit, 1)=0 
THEN
(Case When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) >30000
THEN
CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) - POWER(2, 15)
ELSE
CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2))))
END)
END)  AS [Column value Size],
 
(CASE WHEN leaf_offset<1 and SUBSTRING([nullBitMap], cols.leaf_null_bit, 1)=0  THEN
(Case
 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) >30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])<30000
THEN  (Case When [System_type_id]In (35,34,99) Then 16 else 24  end)
 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) >30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])>30000
THEN  (Case When [System_type_id]In (35,34,99) Then 16 else 24  end) --24 
 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) <30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])<30000
THEN (CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2))))
- ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart]))
 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) <30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])>30000
 
THEN POWER(2, 15) +CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2))))
- ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])
 
END)
 
END) AS [Column Length]
 
,(CASE WHEN SUBSTRING([nullBitMap], cols.leaf_null_bit, 1)=1 THEN  NULL ELSE
 SUBSTRING
 (
 Rowlogcontents, 
 (
 
(Case When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) >30000
THEN
CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) - POWER(2, 15)
ELSE
CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2))))
END)
 
 - 
(Case When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) >30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])<30000
 
THEN  (Case When [System_type_id]In (35,34,99) Then 16 else 24  end) --24 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) >30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])>30000
 
THEN  (Case When [System_type_id]In (35,34,99) Then 16 else 24  end) --24 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) <30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])<30000
 
THEN CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2))))
- ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])
 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) <30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])>30000
 
THEN POWER(2, 15) +CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2))))
- ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])
 
END)
 
) + 1,
(Case When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) >30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])<30000
 
THEN  (Case When [System_type_id] In (35,34,99) Then 16 else 24  end) --24 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) >30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])>30000
 
THEN  (Case When [System_type_id] In (35,34,99) Then 16 else 24  end) --24 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) <30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])<30000
 
THEN ABS(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2))))
- ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart]))
 
When CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2)))) <30000 And
ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])>30000
 
THEN POWER(2, 15) +CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * leaf_offset*-1) - 1, 2))))
- ISNULL(NULLIF(CONVERT(INT, CONVERT(BINARY(2), REVERSE (SUBSTRING ([ColumnOffsetArray], (2 * ((leaf_offset*-1) - 1)) - 1, 2)))), 0), [varColumnStart])
 
END)
)
 
END) AS hex_Value
,[Slot ID]
,0
FROM @DeletedRecords A
Inner Join sys.allocation_units allocunits On A.[AllocUnitId]=allocunits.[Allocation_Unit_Id]
INNER JOIN sys.partitions partitions ON (allocunits.type IN (1, 3)
AND partitions.hobt_id = allocunits.container_id) OR (allocunits.type = 2 AND partitions.partition_id = allocunits.container_id)
INNER JOIN sys.system_internals_partition_columns cols ON cols.partition_id = partitions.partition_id
LEFT OUTER JOIN syscolumns ON syscolumns.id = partitions.object_id AND syscolumns.colid = cols.partition_column_id
WHERE leaf_offset<0
UNION
/*This part is for fixed data columns*/
SELECT 
[Row ID],
Rowlogcontents,
NAME ,
cols.leaf_null_bit AS nullbit,
leaf_offset,
ISNULL(syscolumns.length, cols.max_length) AS [length],
cols.system_type_id,
cols.leaf_bit_position AS bitpos,
ISNULL(syscolumns.xprec, cols.precision) AS xprec,
ISNULL(syscolumns.xscale, cols.scale) AS xscale,
SUBSTRING([nullBitMap], cols.leaf_null_bit, 1) AS is_null,
(SELECT TOP 1 ISNULL(SUM(CASE WHEN C.leaf_offset >1 THEN max_length ELSE 0 END),0) FROM
sys.system_internals_partition_columns C WHERE cols.partition_id =C.partition_id And C.leaf_null_bit<cols.leaf_null_bit)+5 AS [Column value Size],
syscolumns.length AS [Column Length]
 
,CASE WHEN SUBSTRING([nullBitMap], cols.leaf_null_bit, 1)=1 THEN NULL ELSE
SUBSTRING
(
Rowlogcontents,(SELECT TOP 1 ISNULL(SUM(CASE WHEN C.leaf_offset >1 And C.leaf_bit_position=0 THEN max_length ELSE 0 END),0) FROM
sys.system_internals_partition_columns C where cols.partition_id =C.partition_id And C.leaf_null_bit<cols.leaf_null_bit)+5
,syscolumns.length) END AS hex_Value
,[Slot ID]
,0
FROM @DeletedRecords A
Inner Join sys.allocation_units allocunits ON A.[AllocUnitId]=allocunits.[Allocation_Unit_Id]
INNER JOIN sys.partitions partitions ON (allocunits.type IN (1, 3)
AND partitions.hobt_id = allocunits.container_id) OR (allocunits.type = 2 AND partitions.partition_id = allocunits.container_id)
INNER JOIN sys.system_internals_partition_columns cols ON cols.partition_id = partitions.partition_id
LEFT OUTER JOIN syscolumns ON syscolumns.id = partitions.object_id AND syscolumns.colid = cols.partition_column_id
WHERE leaf_offset>0
Order By nullbit
 
Declare @BitColumnByte as int
Select @BitColumnByte=CONVERT(INT, ceiling( Count(*)/8.0)) from @ColumnNameAndData Where [System_Type_id]=104
 
;With N1 (n) AS (SELECT 1 UNION ALL SELECT 1),
N2 (n) AS (SELECT 1 FROM N1 AS X, N1 AS Y),
N3 (n) AS (SELECT 1 FROM N2 AS X, N2 AS Y),
N4 (n) AS (SELECT ROW_NUMBER() OVER(ORDER BY X.n)
           FROM N3 AS X, N3 AS Y),
CTE As(
Select RowLogContents,[nullbit]
        ,[BitMap]=Convert(varbinary(1),Convert(int,Substring((REPLACE(STUFF((SELECT ',' +
        (CASE WHEN [ID]=0 THEN CONVERT(NVARCHAR(1),(SUBSTRING(hex_Value, n, 1) % 2))  ELSE CONVERT(NVARCHAR(1),((SUBSTRING(hex_Value, n, 1) / [Bitvalue]) % 2)) END) --as [nullBitMap]
 
from N4 AS Nums
Join @ColumnNameAndData AS C ON n<=@BitColumnByte And [System_Type_id]=104 And bitpos=0
Cross Join @bitTable WHERE C.[RowLogContents]=D.[RowLogContents] ORDER BY [RowLogContents],n ASC FOR XML PATH('')),1,1,''),',','')),bitpos+1,1)))
FROM @ColumnNameAndData D Where  [System_Type_id]=104)
 
Update A Set [hex_Value]=[BitMap]
from @ColumnNameAndData  A
Inner Join CTE B On A.[RowLogContents]=B.[RowLogContents]
And A.[nullbit]=B.[nullbit]
 
 
/**************Check for BLOB DATA TYPES******************************/
DECLARE @Fileid INT
DECLARE @Pageid INT
DECLARE @Slotid INT
DECLARE @CurrentLSN INT
DECLARE @LinkID INT
DECLARE @Context VARCHAR(50)
DECLARE @ConsolidatedPageID VARCHAR(MAX)
DECLARE @LCX_TEXT_MIX VARBINARY(MAX)
 
declare @temppagedata table
(
[ParentObject] sysname,
[Object] sysname,
[Field] sysname,
[Value] sysname)
 
declare @pagedata table
(
[Page ID] sysname,
[File IDS] int,
[Page IDS] int,
[AllocUnitId] bigint,
[ParentObject] sysname,
[Object] sysname,
[Field] sysname,
[Value] sysname)
 
DECLARE @ModifiedRawData TABLE
(
  [ID] INT IDENTITY(1,1),
  [PAGE ID] VARCHAR(MAX),
  [FILE IDS] INT,
  [PAGE IDS] INT,
  [Slot ID]  INT,
  [AllocUnitId] BIGINT,
  [RowLog Contents 0_var] VARCHAR(Max),
  [RowLog Length] VARCHAR(50),
  [RowLog Len] INT,
  [RowLog Contents 0] VARBINARY(Max),
  [Link ID] INT default (0),
  [Update] INT
)
 
            DECLARE Page_Data_Cursor CURSOR FOR
            /*We need to filter LOP_MODIFY_ROW,LOP_MODIFY_COLUMNS from log for deleted records of BLOB data type& Get its Slot No, Page ID & AllocUnit ID*/
            SELECT LTRIM(RTRIM(Replace([Description],'Deallocated',''))) AS [PAGE ID]
            ,[Slot ID],[AllocUnitId],NULL AS [RowLog Contents 0],NULL AS [RowLog Contents 0],Context
            FROM    sys.fn_dblog(NULL, NULL)  
            WHERE   
            AllocUnitId IN
            (SELECT [Allocation_unit_id] FROM sys.allocation_units allocunits
            INNER JOIN sys.partitions partitions ON (allocunits.type IN (1, 3)  
            AND partitions.hobt_id = allocunits.container_id) OR (allocunits.type = 2 
            AND partitions.partition_id = allocunits.container_id)  
            WHERE object_id=object_ID('' + @SchemaName_n_TableName + ''))
            AND Operation IN ('LOP_MODIFY_ROW') AND [Context] IN ('LCX_PFS') 
            AND Description Like '%Deallocated%'
            /*Use this subquery to filter the date*/
            AND [TRANSACTION ID] IN (SELECT DISTINCT [TRANSACTION ID] FROM    sys.fn_dblog(NULL, NULL) 
            WHERE Context IN ('LCX_NULL') AND Operation in ('LOP_BEGIN_XACT')  
            AND [Transaction Name]='DELETE'
            AND  CONVERT(NVARCHAR(11),[Begin Time]) BETWEEN @Date_From AND @Date_To)
            GROUP BY [Description],[Slot ID],[AllocUnitId],Context
 
            UNION
 
            SELECT [PAGE ID],[Slot ID],[AllocUnitId]
            ,Substring([RowLog Contents 0],15,LEN([RowLog Contents 0])) AS [RowLog Contents 0]
            ,CONVERT(INT,Substring([RowLog Contents 0],7,2)),Context --,CAST(RIGHT([Current LSN],4) AS INT) AS [Current LSN]
            FROM    sys.fn_dblog(NULL, NULL)  
            WHERE  
             AllocUnitId IN
            (SELECT [Allocation_unit_id] FROM sys.allocation_units allocunits
            INNER JOIN sys.partitions partitions ON (allocunits.type IN (1, 3)  
            AND partitions.hobt_id = allocunits.container_id) OR (allocunits.type = 2 
            AND partitions.partition_id = allocunits.container_id)  
            WHERE object_id=object_ID('' + @SchemaName_n_TableName + ''))
            AND Context IN ('LCX_TEXT_MIX') AND Operation in ('LOP_DELETE_ROWS') 
            /*Use this subquery to filter the date*/
            AND [TRANSACTION ID] IN (SELECT DISTINCT [TRANSACTION ID] FROM    sys.fn_dblog(NULL, NULL) 
            WHERE Context IN ('LCX_NULL') AND Operation in ('LOP_BEGIN_XACT')  
            And [Transaction Name]='DELETE'
            And  CONVERT(NVARCHAR(11),[Begin Time]) BETWEEN @Date_From AND @Date_To)
                         
            /****************************************/
 
        OPEN Page_Data_Cursor
 
        FETCH NEXT FROM Page_Data_Cursor INTO @ConsolidatedPageID, @Slotid,@AllocUnitID,@LCX_TEXT_MIX,@LinkID,@Context
 
        WHILE @@FETCH_STATUS = 0
        BEGIN
            DECLARE @hex_pageid AS VARCHAR(Max)
            /*Page ID contains File Number and page number It looks like 0001:00000130.
              In this example 0001 is file Number &  00000130 is Page Number & These numbers are in Hex format*/
            SET @Fileid=SUBSTRING(@ConsolidatedPageID,0,CHARINDEX(':',@ConsolidatedPageID)) -- Seperate File ID from Page ID
         
            SET @hex_pageid ='0x'+ SUBSTRING(@ConsolidatedPageID,CHARINDEX(':',@ConsolidatedPageID)+1,Len(@ConsolidatedPageID))  ---Seperate the page ID
            SELECT @Pageid=Convert(INT,cast('' AS XML).value('xs:hexBinary(substring(sql:variable("@hex_pageid"),sql:column("t.pos")) )', 'varbinary(max)')) -- Convert Page ID from hex to integer
            FROM (SELECT CASE substring(@hex_pageid, 1, 2) WHEN '0x' THEN 3 ELSE 0 END) AS t(pos) 
             
            IF @Context='LCX_PFS'    
              BEGIN
                        DELETE @temppagedata
                        INSERT INTO @temppagedata EXEC( 'DBCC PAGE(' + @DataBase_Name + ', ' + @fileid + ', ' + @pageid + ', 1) with tableresults,no_infomsgs;'); 
                        INSERT INTO @pagedata SELECT @ConsolidatedPageID,@fileid,@pageid,@AllocUnitID,[ParentObject],[Object],[Field] ,[Value] FROM @temppagedata
              END
            ELSE IF @Context='LCX_TEXT_MIX'
              BEGIN
                        INSERT INTO  @ModifiedRawData SELECT @ConsolidatedPageID,@fileid,@pageid,@Slotid,@AllocUnitID,NULL,0,CONVERT(INT,CONVERT(VARBINARY,REVERSE(SUBSTRING(@LCX_TEXT_MIX,11,2)))),@LCX_TEXT_MIX,@LinkID,0
              END    
            FETCH NEXT FROM Page_Data_Cursor INTO  @ConsolidatedPageID, @Slotid,@AllocUnitID,@LCX_TEXT_MIX,@LinkID,@Context
        END
     
    CLOSE Page_Data_Cursor
    DEALLOCATE Page_Data_Cursor
 
    DECLARE @Newhexstring VARCHAR(MAX);
 
    --The data is in multiple rows in the page, so we need to convert it into one row as a single hex value.
    --This hex value is in string format
    INSERT INTO @ModifiedRawData ([PAGE ID],[FILE IDS],[PAGE IDS],[Slot ID],[AllocUnitId]
    ,[RowLog Contents 0_var]
    , [RowLog Length])
    SELECT [Page ID],[FILE IDS],[PAGE IDS],Substring([ParentObject],CHARINDEX('Slot', [ParentObject])+4, (CHARINDEX('Offset', [ParentObject])-(CHARINDEX('Slot', [ParentObject])+4))-2 ) as [Slot ID]
    ,[AllocUnitId]
    ,Substring((
    SELECT
    REPLACE(STUFF((SELECT REPLACE(SUBSTRING([Value],CHARINDEX(':',[Value])+1,CHARINDEX('†',[Value])-CHARINDEX(':',[Value])),'†','')
    FROM @pagedata C  WHERE B.[Page ID]= C.[Page ID] And Substring(B.[ParentObject],CHARINDEX('Slot', B.[ParentObject])+4, (CHARINDEX('Offset', B.[ParentObject])-(CHARINDEX('Slot', B.[ParentObject])+4)) )=Substring(C.[ParentObject],CHARINDEX('Slot', C.[ParentObject])+4, (CHARINDEX('Offset', C.[ParentObject])-(CHARINDEX('Slot', C.[ParentObject])+4)) ) And
    [Object] Like '%Memory Dump%'  Order By '0x'+ LEFT([Value],CHARINDEX(':',[Value])-1)
    FOR XML PATH('') ),1,1,'') ,' ','')
    ),1,20000) AS [Value]
     
    ,
     Substring((
    SELECT '0x' +REPLACE(STUFF((SELECT REPLACE(SUBSTRING([Value],CHARINDEX(':',[Value])+1,CHARINDEX('†',[Value])-CHARINDEX(':',[Value])),'†','')
    FROM @pagedata C  WHERE B.[Page ID]= C.[Page ID] And Substring(B.[ParentObject],CHARINDEX('Slot', B.[ParentObject])+4, (CHARINDEX('Offset', B.[ParentObject])-(CHARINDEX('Slot', B.[ParentObject])+4)) )=Substring(C.[ParentObject],CHARINDEX('Slot', C.[ParentObject])+4, (CHARINDEX('Offset', C.[ParentObject])-(CHARINDEX('Slot', C.[ParentObject])+4)) ) And
    [Object] Like '%Memory Dump%'  Order By '0x'+ LEFT([Value],CHARINDEX(':',[Value])-1)
    FOR XML PATH('') ),1,1,'') ,' ','')
    ),7,4) AS [Length]
     
    From @pagedata B
    Where [Object] Like '%Memory Dump%'
    Group By [Page ID],[FILE IDS],[PAGE IDS],[ParentObject],[AllocUnitId]--,[Current LSN]
    Order By [Slot ID]
 
    UPDATE @ModifiedRawData  SET [RowLog Len] = CONVERT(VARBINARY(8000),REVERSE(cast('' AS XML).value('xs:hexBinary(substring(sql:column("[RowLog Length]"),0))', 'varbinary(Max)')))
    FROM @ModifiedRawData Where [LINK ID]=0
 
    UPDATE @ModifiedRawData  SET [RowLog Contents 0] =cast('' AS XML).value('xs:hexBinary(substring(sql:column("[RowLog Contents 0_var]"),0))', 'varbinary(Max)')  
    FROM @ModifiedRawData Where [LINK ID]=0
 
    Update B Set B.[RowLog Contents 0] =
    (CASE WHEN A.[RowLog Contents 0] IS NOT NULL AND C.[RowLog Contents 0] IS NOT NULL THEN  A.[RowLog Contents 0]+C.[RowLog Contents 0] 
        WHEN A.[RowLog Contents 0] IS NULL AND C.[RowLog Contents 0] IS NOT NULL THEN  C.[RowLog Contents 0]
        WHEN A.[RowLog Contents 0] IS NOT NULL AND C.[RowLog Contents 0] IS NULL THEN  A.[RowLog Contents 0]  
        END)
    ,B.[Update]=ISNULL(B.[Update],0)+1
    from @ModifiedRawData B
    LEFT Join @ModifiedRawData A On A.[Page IDS]=Convert(int,Convert(Varbinary(Max),Reverse(Substring(B.[RowLog Contents 0],15+14,2))))
    And A.[File IDS]=Convert(int,Convert(Varbinary(Max),Reverse(Substring(B.[RowLog Contents 0],19+14,2)))) 
    And A.[Link ID]=B.[Link ID]
    LEFT Join @ModifiedRawData C On C.[Page IDS]=Convert(int,Convert(Varbinary(Max),Reverse(Substring(B.[RowLog Contents 0],27+14,2))))
    And C.[File IDS]=Convert(int,Convert(Varbinary(Max),Reverse(Substring(B.[RowLog Contents 0],31+14,2))))
    And C.[Link ID]=B.[Link ID]
    Where  (A.[RowLog Contents 0] IS NOT NULL OR C.[RowLog Contents 0] IS NOT NULL)
 
 
    Update B Set B.[RowLog Contents 0] =
    (CASE WHEN A.[RowLog Contents 0] IS NOT NULL AND C.[RowLog Contents 0] IS NOT NULL THEN  A.[RowLog Contents 0]+C.[RowLog Contents 0] 
        WHEN A.[RowLog Contents 0] IS NULL AND C.[RowLog Contents 0] IS NOT NULL THEN  C.[RowLog Contents 0]
        WHEN A.[RowLog Contents 0] IS NOT NULL AND C.[RowLog Contents 0] IS NULL THEN  A.[RowLog Contents 0]  
        END)
    --,B.[Update]=ISNULL(B.[Update],0)+1
    from @ModifiedRawData B
    LEFT Join @ModifiedRawData A On A.[Page IDS]=Convert(int,Convert(Varbinary(Max),Reverse(Substring(B.[RowLog Contents 0],15+14,2))))
    And A.[File IDS]=Convert(int,Convert(Varbinary(Max),Reverse(Substring(B.[RowLog Contents 0],19+14,2)))) 
    And A.[Link ID]<>B.[Link ID] And B.[Update]=0
    LEFT Join @ModifiedRawData C On C.[Page IDS]=Convert(int,Convert(Varbinary(Max),Reverse(Substring(B.[RowLog Contents 0],27+14,2))))
    And C.[File IDS]=Convert(int,Convert(Varbinary(Max),Reverse(Substring(B.[RowLog Contents 0],31+14,2))))
    And C.[Link ID]<>B.[Link ID] And B.[Update]=0
    Where  (A.[RowLog Contents 0] IS NOT NULL OR C.[RowLog Contents 0] IS NOT NULL)
 
    UPDATE @ModifiedRawData  SET [RowLog Contents 0] =  
    (Case When [RowLog Len]>=8000 Then
    Substring([RowLog Contents 0] ,15,[RowLog Len]) 
    When [RowLog Len]<8000 Then
    SUBSTRING([RowLog Contents 0],15+6,Convert(int,Convert(varbinary(max),REVERSE(Substring([RowLog Contents 0],15,6)))))
    End)
    FROM @ModifiedRawData Where [LINK ID]=0
 
    UPDATE @ColumnNameAndData SET [hex_Value]=[RowLog Contents 0] 
    --,A.[Update]=A.[Update]+1
    FROM @ColumnNameAndData A
    INNER JOIN @ModifiedRawData B ON
    Convert(int,Convert(Varbinary(Max),Reverse(Substring([hex_value],17,4))))=[PAGE IDS]
    AND  Convert(int,Substring([hex_value],9,2)) =B.[Link ID] 
    Where [System_Type_Id] In (99,167,175,231,239,241,165,98) And [Link ID] <>0 
 
    UPDATE @ColumnNameAndData SET [hex_Value]=
    (CASE WHEN B.[RowLog Contents 0] IS NOT NULL AND C.[RowLog Contents 0] IS NOT NULL THEN  B.[RowLog Contents 0]+C.[RowLog Contents 0] 
    WHEN B.[RowLog Contents 0] IS NULL AND C.[RowLog Contents 0] IS NOT NULL THEN  C.[RowLog Contents 0]
    WHEN B.[RowLog Contents 0] IS NOT NULL AND C.[RowLog Contents 0] IS NULL THEN  B.[RowLog Contents 0]  
    END)
    --,A.[Update]=A.[Update]+1
    FROM @ColumnNameAndData A
    LEFT JOIN @ModifiedRawData B ON
    Convert(int,Convert(Varbinary(Max),Reverse(Substring([hex_value],5,4))))=B.[PAGE IDS]  And B.[Link ID] =0 
    LEFT JOIN @ModifiedRawData C ON
    Convert(int,Convert(Varbinary(Max),Reverse(Substring([hex_value],17,4))))=C.[PAGE IDS]  And C.[Link ID] =0 
    Where [System_Type_Id] In (99,167,175,231,239,241,165,98)  And (B.[RowLog Contents 0] IS NOT NULL OR C.[RowLog Contents 0] IS NOT NULL)
 
    UPDATE @ColumnNameAndData SET [hex_Value]=[RowLog Contents 0] 
    --,A.[Update]=A.[Update]+1
    FROM @ColumnNameAndData A
    INNER JOIN @ModifiedRawData B ON
    Convert(int,Convert(Varbinary(Max),Reverse(Substring([hex_value],9,4))))=[PAGE IDS]
    And Convert(int,Substring([hex_value],3,2))=[Link ID]
    Where [System_Type_Id] In (35,34,99) And [Link ID] <>0 
     
    UPDATE @ColumnNameAndData SET [hex_Value]=[RowLog Contents 0]
    --,A.[Update]=A.[Update]+10
    FROM @ColumnNameAndData A
    INNER JOIN @ModifiedRawData B ON
    Convert(int,Convert(Varbinary(Max),Reverse(Substring([hex_value],9,4))))=[PAGE IDS]
    Where [System_Type_Id] In (35,34,99) And [Link ID] =0
 
    UPDATE @ColumnNameAndData SET [hex_Value]=[RowLog Contents 0] 
    --,A.[Update]=A.[Update]+1
    FROM @ColumnNameAndData A
    INNER JOIN @ModifiedRawData B ON
    Convert(int,Convert(Varbinary(Max),Reverse(Substring([hex_value],15,4))))=[PAGE IDS]
    Where [System_Type_Id] In (35,34,99) And [Link ID] =0
 
    Update @ColumnNameAndData set [hex_value]= 0xFFFE + Substring([hex_value],9,LEN([hex_value]))
    --,[Update]=[Update]+1
    Where [system_type_id]=241
 
CREATE TABLE [#temp_Data]
(
    [FieldName]  VARCHAR(MAX),
    [FieldValue] NVARCHAR(MAX),
    [Rowlogcontents] VARBINARY(8000),
    [Row ID] int
)
 
INSERT INTO #temp_Data
SELECT NAME,
CASE
 WHEN system_type_id IN (231, 239) THEN  LTRIM(RTRIM(CONVERT(NVARCHAR(max),hex_Value)))  --NVARCHAR ,NCHAR
 WHEN system_type_id IN (167,175) THEN  LTRIM(RTRIM(CONVERT(VARCHAR(max),hex_Value)))  --VARCHAR,CHAR
 WHEN system_type_id IN (35) THEN  LTRIM(RTRIM(CONVERT(VARCHAR(max),hex_Value))) --Text
 WHEN system_type_id IN (99) THEN  LTRIM(RTRIM(CONVERT(NVARCHAR(max),hex_Value))) --nText 
 WHEN system_type_id = 48 THEN CONVERT(VARCHAR(MAX), CONVERT(TINYINT, CONVERT(BINARY(1), REVERSE (hex_Value)))) --TINY INTEGER
 WHEN system_type_id = 52 THEN CONVERT(VARCHAR(MAX), CONVERT(SMALLINT, CONVERT(BINARY(2), REVERSE (hex_Value)))) --SMALL INTEGER
 WHEN system_type_id = 56 THEN CONVERT(VARCHAR(MAX), CONVERT(INT, CONVERT(BINARY(4), REVERSE(hex_Value)))) -- INTEGER
 WHEN system_type_id = 127 THEN CONVERT(VARCHAR(MAX), CONVERT(BIGINT, CONVERT(BINARY(8), REVERSE(hex_Value))))-- BIG INTEGER
 WHEN system_type_id = 61 Then CONVERT(VARCHAR(MAX),CONVERT(DATETIME,CONVERT(VARBINARY(8000),REVERSE (hex_Value))),100) --DATETIME
 WHEN system_type_id =58 Then CONVERT(VARCHAR(MAX),CONVERT(SMALLDATETIME,CONVERT(VARBINARY(8000),REVERSE(hex_Value))),100) --SMALL DATETIME
 WHEN system_type_id = 108 THEN CONVERT(VARCHAR(MAX),CONVERT(NUMERIC(38,20), CONVERT(VARBINARY,CONVERT(VARBINARY(1),xprec)+CONVERT(VARBINARY(1),xscale))+CONVERT(VARBINARY(1),0) + hex_Value)) --- NUMERIC
 WHEN system_type_id =106 THEN CONVERT(VARCHAR(MAX), CONVERT(DECIMAL(38,20), CONVERT(VARBINARY,Convert(VARBINARY(1),xprec)+CONVERT(VARBINARY(1),xscale))+CONVERT(VARBINARY(1),0) + hex_Value)) --- DECIMAL
 WHEN system_type_id In(60,122) THEN CONVERT(VARCHAR(MAX),Convert(MONEY,Convert(VARBINARY(8000),Reverse(hex_Value))),2) --MONEY,SMALLMONEY
 WHEN system_type_id = 104 THEN CONVERT(VARCHAR(MAX),CONVERT (BIT,CONVERT(BINARY(1), hex_Value)%2))  -- BIT
 WHEN system_type_id =62 THEN  RTRIM(LTRIM(STR(CONVERT(FLOAT,SIGN(CAST(CONVERT(VARBINARY(8000),Reverse(hex_Value)) AS BIGINT)) * (1.0 + (CAST(CONVERT(VARBINARY(8000),Reverse(hex_Value)) AS BIGINT) & 0x000FFFFFFFFFFFFF) * POWER(CAST(2 AS FLOAT), -52)) * POWER(CAST(2 AS FLOAT),((CAST(CONVERT(VARBINARY(8000),Reverse(hex_Value)) AS BIGINT) & 0x7ff0000000000000) / EXP(52 * LOG(2))-1023))),53,LEN(hex_Value)))) --- FLOAT
 When system_type_id =59 THEN  Left(LTRIM(STR(CAST(SIGN(CAST(Convert(VARBINARY(8000),REVERSE(hex_Value)) AS BIGINT))* (1.0 + (CAST(CONVERT(VARBINARY(8000),Reverse(hex_Value)) AS BIGINT) & 0x007FFFFF) * POWER(CAST(2 AS Real), -23)) * POWER(CAST(2 AS Real),(((CAST(CONVERT(VARBINARY(8000),Reverse(hex_Value)) AS INT) )& 0x7f800000)/ EXP(23 * LOG(2))-127))AS REAL),23,23)),8) --Real
 WHEN system_type_id In (165,173) THEN (CASE WHEN CHARINDEX(0x,cast('' AS XML).value('xs:hexBinary(sql:column("hex_Value"))', 'VARBINARY(8000)')) = 0 THEN '0x' ELSE '' END) +cast('' AS XML).value('xs:hexBinary(sql:column("hex_Value"))', 'varchar(max)') -- BINARY,VARBINARY
 WHEN system_type_id =34 THEN (CASE WHEN CHARINDEX(0x,cast('' AS XML).value('xs:hexBinary(sql:column("hex_Value"))', 'VARBINARY(8000)')) = 0 THEN '0x' ELSE '' END) +cast('' AS XML).value('xs:hexBinary(sql:column("hex_Value"))', 'varchar(max)')  --IMAGE
 WHEN system_type_id =36 THEN CONVERT(VARCHAR(MAX),CONVERT(UNIQUEIDENTIFIER,hex_Value)) --UNIQUEIDENTIFIER
 WHEN system_type_id =231 THEN CONVERT(VARCHAR(MAX),CONVERT(sysname,hex_Value)) --SYSNAME
 WHEN system_type_id =241 THEN CONVERT(VARCHAR(MAX),CONVERT(xml,hex_Value)) --XML
 
 WHEN system_type_id =189 THEN (CASE WHEN CHARINDEX(0x,cast('' AS XML).value('xs:hexBinary(sql:column("hex_Value"))', 'VARBINARY(8000)')) = 0 THEN '0x' ELSE '' END) +cast('' AS XML).value('xs:hexBinary(sql:column("hex_Value"))', 'varchar(max)') --TIMESTAMP
 WHEN system_type_id=98 THEN (CASE
 WHEN CONVERT(INT,SUBSTRING(hex_Value,1,1))=56 THEN CONVERT(VARCHAR(MAX), CONVERT(INT, CONVERT(BINARY(4), REVERSE(Substring(hex_Value,3,Len(hex_Value))))))  -- INTEGER
 WHEN CONVERT(INT,SUBSTRING(hex_Value,1,1))=108 THEN CONVERT(VARCHAR(MAX),CONVERT(numeric(38,20),CONVERT(VARBINARY(1),Substring(hex_Value,3,1)) +CONVERT(VARBINARY(1),Substring(hex_Value,4,1))+CONVERT(VARBINARY(1),0) + Substring(hex_Value,5,Len(hex_Value)))) --- NUMERIC
 WHEN CONVERT(INT,SUBSTRING(hex_Value,1,1))=167 THEN LTRIM(RTRIM(CONVERT(VARCHAR(max),Substring(hex_Value,9,Len(hex_Value))))) --VARCHAR,CHAR
 WHEN CONVERT(INT,SUBSTRING(hex_Value,1,1))=36 THEN CONVERT(VARCHAR(MAX),CONVERT(UNIQUEIDENTIFIER,Substring((hex_Value),3,20))) --UNIQUEIDENTIFIER
 WHEN CONVERT(INT,SUBSTRING(hex_Value,1,1))=61 THEN CONVERT(VARCHAR(MAX),CONVERT(DATETIME,CONVERT(VARBINARY(8000),REVERSE (Substring(hex_Value,3,LEN(hex_Value)) ))),100) --DATETIME
 WHEN CONVERT(INT,SUBSTRING(hex_Value,1,1))=165 THEN '0x'+ SUBSTRING((CASE WHEN CHARINDEX(0x,cast('' AS XML).value('xs:hexBinary(sql:column("hex_Value"))', 'VARBINARY(8000)')) = 0 THEN '0x' ELSE '' END) +cast('' AS XML).value('xs:hexBinary(sql:column("hex_Value"))', 'varchar(max)'),11,LEN(hex_Value)) -- BINARY,VARBINARY
 END)
  
END AS FieldValue
,[Rowlogcontents]
,[Row ID]
FROM @ColumnNameAndData ORDER BY nullbit
 
--Create the column name in the same order to do pivot table.
 
DECLARE @FieldName VARCHAR(max)
SET @FieldName = STUFF(
(
    SELECT ',' + CAST(QUOTENAME([Name]) AS VARCHAR(MAX)) FROM syscolumns WHERE id=object_id('' + @SchemaName_n_TableName + '')
    FOR XML PATH('')), 1, 1, '')
 
--Finally did pivot table and get the data back in the same format.
 
SET @sql = 'SELECT ' + @FieldName  + ' FROM #temp_Data PIVOT (Min([FieldValue]) FOR FieldName IN (' + @FieldName  + ')) AS pvt'
EXEC sp_executesql @sql
 

GO
USE [master]
GO
ALTER DATABASE [JRMS] SET  READ_WRITE 
GO
