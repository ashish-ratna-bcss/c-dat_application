USE [master]
GO
/****** Object:  Database [FORMS]    Script Date: 13-Aug-26 6:04:17 PM ******/
CREATE DATABASE [FORMS]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'FORMS', FILENAME = N'D:\SQL SOFTWARE 2016 INSTALLATION\MSSQL13.DAU_HYD_2023\MSSQL\DATA\FORMS.mdf' , SIZE = 4648832KB , MAXSIZE = UNLIMITED, FILEGROWTH = 1024KB )
 LOG ON 
( NAME = N'FORMS_log', FILENAME = N'D:\SQL SOFTWARE 2016 INSTALLATION\MSSQL13.DAU_HYD_2023\MSSQL\DATA\FORMS_log.ldf' , SIZE = 11200KB , MAXSIZE = UNLIMITED, FILEGROWTH = 10%)
GO
ALTER DATABASE [FORMS] SET COMPATIBILITY_LEVEL = 120
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [FORMS].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [FORMS] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [FORMS] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [FORMS] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [FORMS] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [FORMS] SET ARITHABORT OFF 
GO
ALTER DATABASE [FORMS] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [FORMS] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [FORMS] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [FORMS] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [FORMS] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [FORMS] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [FORMS] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [FORMS] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [FORMS] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [FORMS] SET  DISABLE_BROKER 
GO
ALTER DATABASE [FORMS] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [FORMS] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [FORMS] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [FORMS] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [FORMS] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [FORMS] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [FORMS] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [FORMS] SET RECOVERY FULL 
GO
ALTER DATABASE [FORMS] SET  MULTI_USER 
GO
ALTER DATABASE [FORMS] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [FORMS] SET DB_CHAINING OFF 
GO
ALTER DATABASE [FORMS] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [FORMS] SET TARGET_RECOVERY_TIME = 0 SECONDS 
GO
ALTER DATABASE [FORMS] SET DELAYED_DURABILITY = DISABLED 
GO
EXEC sys.sp_db_vardecimal_storage_format N'FORMS', N'ON'
GO
USE [FORMS]
GO
/****** Object:  User [NIKESH]    Script Date: 13-Aug-26 6:04:17 PM ******/
CREATE USER [NIKESH] WITHOUT LOGIN WITH DEFAULT_SCHEMA=[dbo]
GO
/****** Object:  User [IR_ENTRY]    Script Date: 13-Aug-26 6:04:17 PM ******/
CREATE USER [IR_ENTRY] FOR LOGIN [IR_ENTRY] WITH DEFAULT_SCHEMA=[dbo]
GO
/****** Object:  User [FORMS]    Script Date: 13-Aug-26 6:04:17 PM ******/
CREATE USER [FORMS] WITHOUT LOGIN WITH DEFAULT_SCHEMA=[dbo]
GO
/****** Object:  User [form]    Script Date: 13-Aug-26 6:04:17 PM ******/
CREATE USER [form] WITHOUT LOGIN WITH DEFAULT_SCHEMA=[dbo]
GO
ALTER ROLE [db_accessadmin] ADD MEMBER [NIKESH]
GO
ALTER ROLE [db_securityadmin] ADD MEMBER [NIKESH]
GO
ALTER ROLE [db_ddladmin] ADD MEMBER [NIKESH]
GO
ALTER ROLE [db_backupoperator] ADD MEMBER [NIKESH]
GO
ALTER ROLE [db_datareader] ADD MEMBER [NIKESH]
GO
ALTER ROLE [db_datawriter] ADD MEMBER [NIKESH]
GO
ALTER ROLE [db_denydatareader] ADD MEMBER [NIKESH]
GO
ALTER ROLE [db_denydatawriter] ADD MEMBER [NIKESH]
GO
ALTER ROLE [db_owner] ADD MEMBER [IR_ENTRY]
GO
ALTER ROLE [db_accessadmin] ADD MEMBER [FORMS]
GO
ALTER ROLE [db_securityadmin] ADD MEMBER [FORMS]
GO
ALTER ROLE [db_ddladmin] ADD MEMBER [FORMS]
GO
ALTER ROLE [db_backupoperator] ADD MEMBER [FORMS]
GO
ALTER ROLE [db_datareader] ADD MEMBER [FORMS]
GO
ALTER ROLE [db_datawriter] ADD MEMBER [FORMS]
GO
ALTER ROLE [db_denydatareader] ADD MEMBER [FORMS]
GO
ALTER ROLE [db_denydatawriter] ADD MEMBER [FORMS]
GO
ALTER ROLE [db_datareader] ADD MEMBER [form]
GO
ALTER ROLE [db_datawriter] ADD MEMBER [form]
GO
ALTER ROLE [db_denydatareader] ADD MEMBER [form]
GO
ALTER ROLE [db_denydatawriter] ADD MEMBER [form]
GO
/****** Object:  UserDefinedFunction [dbo].[GetPercentageOfTwoStringMatching]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE FUNCTION [dbo].[GetPercentageOfTwoStringMatching]
(
    @string1 NVARCHAR(100)
    ,@string2 NVARCHAR(100)
)
RETURNS INT
AS
BEGIN

    DECLARE @levenShteinNumber INT

    DECLARE @string1Length INT = LEN(@string1)
    , @string2Length INT = LEN(@string2)
    DECLARE @maxLengthNumber INT = CASE WHEN @string1Length > @string2Length THEN @string1Length ELSE @string2Length END

    SELECT @levenShteinNumber = [dbo].[LEVENSHTEIN] (   @string1  ,@string2)

    DECLARE @percentageOfBadCharacters INT = @levenShteinNumber * 100 / @maxLengthNumber

    DECLARE @percentageOfGoodCharacters INT = 100 - @percentageOfBadCharacters

    -- Return the result of the function
    RETURN @percentageOfGoodCharacters

END




GO
/****** Object:  UserDefinedFunction [dbo].[LEVENSHTEIN]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE FUNCTION [dbo].[LEVENSHTEIN](@left  VARCHAR(100),
                                    @right VARCHAR(100))
returns INT
AS
  BEGIN
      DECLARE @difference    INT,
              @lenRight      INT,
              @lenLeft       INT,
              @leftIndex     INT,
              @rightIndex    INT,
              @left_char     CHAR(1),
              @right_char    CHAR(1),
              @compareLength INT

      SET @lenLeft = LEN(@left)
      SET @lenRight = LEN(@right)
      SET @difference = 0

      IF @lenLeft = 0
        BEGIN
            SET @difference = @lenRight

            GOTO done
        END

      IF @lenRight = 0
        BEGIN
            SET @difference = @lenLeft

            GOTO done
        END

      GOTO comparison

      COMPARISON:

      IF ( @lenLeft >= @lenRight )
        SET @compareLength = @lenLeft
      ELSE
        SET @compareLength = @lenRight

      SET @rightIndex = 1
      SET @leftIndex = 1

      WHILE @leftIndex <= @compareLength
        BEGIN
            SET @left_char = substring(@left, @leftIndex, 1)
            SET @right_char = substring(@right, @rightIndex, 1)

            IF @left_char <> @right_char
              BEGIN -- Would an insertion make them re-align?
                  IF( @left_char = substring(@right, @rightIndex + 1, 1) )
                    SET @rightIndex = @rightIndex + 1
                  -- Would an deletion make them re-align?
                  ELSE IF( substring(@left, @leftIndex + 1, 1) = @right_char )
                    SET @leftIndex = @leftIndex + 1

                  SET @difference = @difference + 1
              END

            SET @leftIndex = @leftIndex + 1
            SET @rightIndex = @rightIndex + 1
        END

      GOTO done

      DONE:

      RETURN @difference
  END

GO
/****** Object:  Table [dbo].[07032017 FAMILY_HISTORY]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[07032017 FAMILY_HISTORY](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[RELATIONSHIP] [varchar](100) NULL,
	[NAME] [varchar](50) NULL,
	[FATHER_OR_SPOUSE] [varchar](100) NULL,
	[OCCUPATION] [varchar](100) NULL,
	[PHONE] [varchar](50) NULL,
	[AGE] [varchar](50) NULL,
	[CRIMINAL_BACKGROUND] [varchar](50) NULL,
	[STATUS] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ab]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ab](
	[irkey] [numeric](18, 0) NOT NULL,
	[name] [varchar](250) NULL,
	[father_name] [varchar](100) NULL,
	[Present_address] [varchar](1000) NULL,
	[mobile] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ab1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ab1](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[PERIOD_OF_OFFENCE] [varchar](100) NULL,
	[REGULAR_RESIDENCE] [varchar](500) NULL,
	[PREPARATION_OF_OFFENCE] [varchar](500) NULL,
	[AFTER_OFFENCE] [varchar](500) NULL,
	[INDULGANCE_BEFORE_OFFENCE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[SUB_TYPE] [varchar](100) NULL,
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
/****** Object:  Table [dbo].[ab2]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ab2](
	[irkey] [numeric](18, 0) NOT NULL,
	[name] [varchar](250) NULL,
	[father_name] [varchar](100) NULL,
	[Present_address] [varchar](1000) NULL,
	[mobile] [varchar](100) NULL,
	[crime_no] [int] NULL,
	[year] [int] NULL,
	[sec_of_law] [varchar](500) NULL,
	[police_station] [varchar](100) NULL,
	[date_of_arrest] [date] NULL,
	[mo] [varchar](2000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ab3]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ab3](
	[irkey] [numeric](18, 0) NOT NULL,
	[name] [varchar](250) NULL,
	[father_name] [varchar](100) NULL,
	[Present_address] [varchar](1000) NULL,
	[mobile] [varchar](100) NULL,
	[crime_no] [int] NULL,
	[year] [int] NULL,
	[sec_of_law] [varchar](500) NULL,
	[police_station] [varchar](100) NULL,
	[date_of_arrest] [date] NULL,
	[mo] [varchar](2000) NULL,
	[offences_count] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ABSTRACT_JAN_TO_JULY_TILL_DATE]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ABSTRACT_JAN_TO_JULY_TILL_DATE](
	[POLICE_STATION] [varchar](100) NULL,
	[SUB_DIVISION] [varchar](100) NULL,
	[ZONE] [varchar](50) NULL,
	[COUNT1] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ABSTRACT_JAN_TO_JULY_TILL_DATE_TO_CHECK]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ABSTRACT_JAN_TO_JULY_TILL_DATE_TO_CHECK](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[SUB_DIVISION] [varchar](100) NULL,
	[ZONE] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ABSTRACT_OFFENCE]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ABSTRACT_OFFENCE](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[PERIOD_OF_OFFENCE] [varchar](100) NULL,
	[REGULAR_RESIDENCE] [varchar](500) NULL,
	[PREPARATION_OF_OFFENCE] [varchar](500) NULL,
	[AFTER_OFFENCE] [varchar](500) NULL,
	[INDULGANCE_BEFORE_OFFENCE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[SUB_TYPE] [varchar](100) NULL,
	[MO] [varchar](500) NULL,
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
/****** Object:  Table [dbo].[am]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[am](
	[irkey] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[name] [varchar](250) NULL,
	[father_name] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ARREST_DATA_2023]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ARREST_DATA_2023](
	[YEAR] [varchar](5000) NULL,
	[SECTION] [varchar](5000) NULL,
	[POLICE_STATION] [varchar](5000) NULL,
	[SURNAME] [varchar](5000) NULL,
	[FULLNAME] [varchar](5000) NULL,
	[FATHERNAME] [varchar](5000) NULL,
	[ADDRESS] [varchar](5000) NULL,
	[Age] [varchar](5000) NULL,
	[CRIME_NO] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ARREST_DETAILS_03_08]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ARREST_DETAILS_03_08](
	[PHONE] [varchar](100) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](100) NULL,
	[FNAME] [varchar](100) NULL,
	[ADDRESS] [varchar](1000) NULL,
	[CITY] [int] NULL,
	[STATE] [varchar](9) NOT NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[DOO] [varchar](100) NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [int] NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](500) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[UNIT] [varchar](100) NULL,
	[MODULE_NAME] [int] NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [date] NULL,
	[IMEINUMBER] [int] NULL,
	[INC_OFFICER] [varchar](500) NULL,
	[CATEGORY] [varchar](1) NOT NULL,
	[ORGANISATION] [varchar](21) NOT NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [int] NULL,
	[Date_of_Arrest] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ARREST_LIST_04_08]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ARREST_LIST_04_08](
	[PHONE] [varchar](100) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](100) NULL,
	[FNAME] [varchar](100) NULL,
	[ADDRESS] [varchar](1000) NULL,
	[CITY] [int] NULL,
	[STATE] [varchar](9) NOT NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[DOO] [varchar](100) NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [int] NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](500) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[UNIT] [varchar](100) NULL,
	[MODULE_NAME] [int] NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [date] NULL,
	[IMEINUMBER] [int] NULL,
	[INC_OFFICER] [varchar](500) NULL,
	[CATEGORY] [varchar](1) NOT NULL,
	[ORGANISATION] [varchar](21) NOT NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [int] NULL,
	[Date_of_Arrest] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ARREST_LIST_FAM_04_08]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ARREST_LIST_FAM_04_08](
	[PHONE] [varchar](100) NULL,
	[ROLE] [varchar](108) NULL,
	[NICKNAME] [varchar](151) NULL,
	[FNAME] [int] NULL,
	[ADDRESS] [varchar](1000) NULL,
	[CITY] [int] NULL,
	[STATE] [varchar](9) NOT NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[DOO] [varchar](100) NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [int] NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](500) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[UNIT] [varchar](100) NULL,
	[MODULE_NAME] [int] NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [varchar](50) NULL,
	[IMEINUMBER] [int] NULL,
	[INC_OFFICER] [varchar](500) NULL,
	[CATEGORY] [varchar](1) NOT NULL,
	[ORGANISATION] [varchar](25) NOT NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [varchar](21) NULL,
	[Date_of_Arrest] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ARREST_LIST_FAM_042017_032018]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ARREST_LIST_FAM_042017_032018](
	[PHONE] [varchar](100) NULL,
	[ROLE] [varchar](108) NULL,
	[NICKNAME] [varchar](151) NULL,
	[FNAME] [int] NULL,
	[ADDRESS] [varchar](1000) NULL,
	[CITY] [int] NULL,
	[STATE] [varchar](9) NOT NULL,
	[COUNTRY] [int] NULL,
	[PIN] [int] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[DOO] [varchar](100) NULL,
	[PLACE_OF_OFF] [int] NULL,
	[DOR] [int] NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](500) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[UNIT] [varchar](100) NULL,
	[MODULE_NAME] [int] NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [int] NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [varchar](50) NULL,
	[IMEINUMBER] [int] NULL,
	[INC_OFFICER] [varchar](500) NULL,
	[CATEGORY] [varchar](1) NOT NULL,
	[ORGANISATION] [varchar](25) NOT NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [varchar](21) NULL,
	[Date_of_Arrest] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[AUTO_FINAL]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[AUTO_FINAL](
	[ZONE] [varchar](100) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](2000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[AUTO_FINAL1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[AUTO_FINAL1](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[ZONE] [varchar](100) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](2000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[AUTO_IR]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[AUTO_IR](
	[IRKEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[NAME] [varchar](250) NULL,
	[ALIAS_NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[DATE_OF_BIRTH] [date] NULL,
	[NATIONALITY] [varchar](50) NULL,
	[RELIGION] [varchar](50) NULL,
	[CASTE] [varchar](50) NULL,
	[COMMUNITY] [varchar](50) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[EMAIL_ID] [varchar](100) NULL,
	[SOCIAL_MEDIA_ACCOUNTS] [varchar](1000) NULL,
	[AADHAR_NO] [bigint] NULL,
	[RATION_CARD_NO] [varchar](100) NULL,
	[VOTERID] [varchar](500) NULL,
	[PASSPORT] [varchar](500) NULL,
	[PANCARD] [varchar](500) NULL,
	[ELECTRICITY_CONNECTION] [varchar](500) NULL,
	[GAS_CONNECTION] [varchar](500) NULL,
	[VEHICLES] [varchar](500) NULL,
	[DRIVING_LICENSE] [varchar](500) NULL,
	[OTHER_ID_PROOFS] [varchar](500) NULL,
	[SEX] [varchar](100) NULL,
	[BUILT] [varchar](100) NULL,
	[HEIGHT] [varchar](100) NULL,
	[EYES] [varchar](100) NULL,
	[HAIR] [varchar](100) NULL,
	[FACE] [varchar](100) NULL,
	[COLOUR] [varchar](100) NULL,
	[TEETH] [varchar](100) NULL,
	[NOSE] [varchar](100) NULL,
	[BEARD] [varchar](100) NULL,
	[MUSTACHES] [varchar](100) NULL,
	[EAR] [varchar](100) NULL,
	[IDENTIFICATION_MARKS] [varchar](500) NULL,
	[DEFORMITIES_PECULIARITIES] [varchar](500) NULL,
	[LANGUAGE_DIALECT] [varchar](500) NULL,
	[BURN_MARKS] [varchar](100) NULL,
	[LEUCODEMA] [varchar](100) NULL,
	[MOLE] [varchar](100) NULL,
	[SCAR] [varchar](100) NULL,
	[TATTOO] [varchar](500) NULL,
	[LIVING_STATUS] [varchar](100) NULL,
	[MARITAL_STATUS] [varchar](100) NULL,
	[EDUCATION_DETAILS] [varchar](500) NULL,
	[OCCUPATION] [varchar](250) NULL,
	[INCOME_GROUP] [varchar](100) NULL,
	[REGULAR_HABITS] [varchar](100) NULL,
	[CATEGORY] [varchar](50) NULL,
	[CC_OR_EXCC] [varchar](20) NULL,
	[CC_OR_EXCCNO] [varchar](20) NULL,
	[ASONDATE] [datetime] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[AUTO_OFF]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[AUTO_OFF](
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
/****** Object:  Table [dbo].[BA]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[BA](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[BRIEF_FACTS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[BRIEF_FACTS](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[BRIEF_FACTS1] [varchar](8000) NULL,
	[BRIEF_FACTS2] [nvarchar](max) NULL,
	[BRIEF_FACTS3] [nvarchar](max) NULL,
	[BRIEF_FACTS4] [varchar](500) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CATTLE_THEFT]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CATTLE_THEFT](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[OCCUPATION] [varchar](250) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](12) NOT NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CHILLI_POWDER_MO]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CHILLI_POWDER_MO](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](2000) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[YEAR] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[CHILLI_POWDER_MO1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[CHILLI_POWDER_MO1](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[AADHAR_NO] [bigint] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[MO] [varchar](2000) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[CRIME_NO] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[DISPOSAL_OF_PROPERTY]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[DISPOSAL_OF_PROPERTY](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[PROPERTY_STOLEN] [varchar](1000) NULL,
	[PROPERTY_RECOVERED] [varchar](2000) NULL,
	[RECEIVER_NAME] [varchar](500) NULL,
	[RECEIVER_ADDRESS] [varchar](500) NULL,
	[HOW_SHARE_IS_SPENT] [varchar](1000) NULL,
	[REMARKS] [varchar](500) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[POLICE_STATION] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[DUPLICATE_OFFENCE_DETAILS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[DUPLICATE_OFFENCE_DETAILS](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[PERIOD_OF_OFFENCE] [varchar](100) NULL,
	[REGULAR_RESIDENCE] [varchar](500) NULL,
	[PREPARATION_OF_OFFENCE] [varchar](500) NULL,
	[AFTER_OFFENCE] [varchar](500) NULL,
	[INDULGANCE_BEFORE_OFFENCE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[SUB_TYPE] [varchar](100) NULL,
	[MO] [varchar](500) NULL,
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
/****** Object:  Table [dbo].[FAMILY]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[FAMILY](
	[IRKEY] [numeric](18, 0) NOT NULL,
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
/****** Object:  Table [dbo].[FAMILY_HISTORY]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[FAMILY_HISTORY](
	[IRKEY] [numeric](18, 0) NOT NULL,
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
/****** Object:  Table [dbo].[fingerprint_matched_undetected_cases]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[fingerprint_matched_undetected_cases](
	[SNO] [varchar](8000) NULL,
	[Police_Station] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[Crime_No] [varchar](8000) NULL,
	[Section] [varchar](8000) NULL,
	[Tin_No] [varchar](8000) NULL,
	[Date_of_Identity] [varchar](8000) NULL,
	[Loss_of_Property] [varchar](8000) NULL,
	[Name_And_Particulars] [varchar](8000) NULL,
	[Irkey] [varchar](8000) NULL,
	[CCNO] [varchar](8000) NULL,
	[DOA] [varchar](8000) NULL,
	[Remarks] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[FINGERPRINT_MATCHED_UNDETECTED_CASES_WITHIMAGE]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[FINGERPRINT_MATCHED_UNDETECTED_CASES_WITHIMAGE](
	[SNO] [varchar](8000) NULL,
	[POLICE_STATION] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[CRIME_NO] [varchar](8000) NULL,
	[SECTION] [varchar](8000) NULL,
	[TIN_NO] [varchar](8000) NULL,
	[DATE_OF_IDENTITY] [varchar](8000) NULL,
	[LOSS_OF_PROPERTY] [varchar](8000) NULL,
	[NAME_AND_PARTICULARS] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[CCNO] [varchar](8000) NULL,
	[DOA] [varchar](8000) NULL,
	[REMARKS] [varchar](8000) NULL,
	[IMAGE] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[FOUND_MISSING_CCNO]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[FOUND_MISSING_CCNO](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[CATEGORY] [varchar](50) NULL,
	[CC_OR_EXCCNO] [varchar](50) NULL,
	[NAME] [varchar](250) NULL,
	[DATE_OF_ARREST] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[GANG_FILES_RPS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[GANG_FILES_RPS](
	[Name] [varchar](5000) NULL,
	[Alias] [varchar](5000) NULL,
	[Father_name] [varchar](5000) NULL,
	[Age] [varchar](5000) NULL,
	[Caste] [varchar](5000) NULL,
	[Occupation] [varchar](5000) NULL,
	[Resident of ] [varchar](5000) NULL,
	[Status] [varchar](5000) NULL,
	[Built] [varchar](5000) NULL,
	[Height] [varchar](5000) NULL,
	[Complexion] [varchar](5000) NULL,
	[Habits ] [varchar](5000) NULL,
	[Identification marks] [varchar](5000) NULL,
	[Area of Operation] [varchar](5000) NULL,
	[MO] [varchar](5000) NULL,
	[CRIME_NO_SEC_OF_LAW_PS_DIST] [varchar](5000) NULL,
	[DATE_OF_ARREST] [varchar](5000) NULL,
	[CATEGORY] [varchar](5000) NULL,
	[GANG_NAME] [varchar](5000) NULL,
	[PHOTO_ID] [varchar](5000) NULL,
	[GANG_ID] [varchar](5000) NULL,
	[STATE] [varchar](5000) NULL,
	[CRIME_NO] [varchar](5000) NULL,
	[YEAR] [varchar](5000) NULL,
	[CRIME_NO_TOTAL] [varchar](5000) NULL,
	[CRIME_HEAD] [varchar](5000) NULL,
	[POLICE_STATION] [varchar](5000) NULL,
	[IRKEY] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[GANGS_ARREST_DATE]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[GANGS_ARREST_DATE](
	[IRKEY] [varchar](4000) NULL,
	[OFFENDER_ID] [varchar](4000) NULL,
	[OFFENDERNAME] [varchar](4000) NULL,
	[OFENDER_F_NAME] [varchar](4000) NULL,
	[NAME] [varchar](4000) NULL,
	[FATHER_NAME] [varchar](4000) NULL,
	[PRESENT_ADDRESS_OFFENDER] [varchar](4000) NULL,
	[PRESENT_ADDRESS_IR] [varchar](4000) NULL,
	[PERMANENT_ADDRESS_OFFENDER] [varchar](4000) NULL,
	[PERMANENT_ADDRESS] [varchar](4000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[HABITUAL_OFFENDERS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[HABITUAL_OFFENDERS](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[NAME] [varchar](100) NULL,
	[ALIAS_NAME] [varchar](100) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[ARRESTED_IN_CRIMEHEAD] [varchar](500) NULL,
	[MO] [varchar](500) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[count1] [int] NULL,
	[image] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[HABITUAL_OFFENDERS_TOTAL1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[HABITUAL_OFFENDERS_TOTAL1](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[ZONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[HANUMAN]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[HANUMAN](
	[NAME] [varchar](4000) NULL,
	[EMP_ID] [varchar](4000) NULL,
	[GENDER] [varchar](4000) NULL,
	[MACHINE_PASS_OR_FAIL] [varchar](4000) NULL,
	[DISTRICT] [varchar](4000) NULL,
	[DATE] [varchar](4000) NULL,
	[FACE] [varchar](4000) NULL,
	[RANKING] [varchar](4000) NULL,
	[STATUS] [varchar](4000) NULL,
	[MARTIAL] [varchar](4000) NULL,
	[STATUS1] [varchar](4000) NULL,
	[RANKING1] [varchar](4000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[HYD_OFFENDER_DATA]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[HYD_OFFENDER_DATA](
	[Zone] [varchar](5000) NULL,
	[Divisions] [varchar](5000) NULL,
	[PS] [varchar](5000) NULL,
	[OFFENDER_ID] [varchar](5000) NULL,
	[OFFENDERNAME] [varchar](5000) NULL,
	[ALIASES] [varchar](5000) NULL,
	[Father_Name] [varchar](5000) NULL,
	[MOBILENO] [varchar](5000) NULL,
	[AGE] [varchar](5000) NULL,
	[DOB] [varchar](5000) NULL,
	[PRESENT_ADDRESS] [varchar](5000) NULL,
	[PERMANENT_ADDRESS] [varchar](5000) NULL,
	[CURRENTACTIVITY] [varchar](5000) NULL,
	[PSARRESTED] [varchar](5000) NULL,
	[PERMANENT_ADDRESS1] [varchar](5000) NULL,
	[DATEOFLASTARREST] [varchar](5000) NULL,
	[DATEOFRELEASE] [varchar](5000) NULL,
	[MoType] [varchar](5000) NULL,
	[PERMANENT_ADDRESS2] [varchar](5000) NULL,
	[DATEOFRELEASE1] [varchar](5000) NULL,
	[MODUSOPERENDI] [varchar](5000) NULL,
	[Latitude] [varchar](5000) NULL,
	[LONGITUDE] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[IMAGE_TABLE]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[IMAGE_TABLE](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[CATEGORY] [varchar](50) NULL,
	[CCNO] [varchar](100) NULL,
	[IMAGE] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[IR]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[IR](
	[NAME] [varchar](1000) NULL,
	[FNAME] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[IR_FORMS_MUTIPLE]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[IR_FORMS_MUTIPLE](
	[IRKEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[NAME] [varchar](250) NULL,
	[ALIAS_NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[DATE_OF_BIRTH] [date] NULL,
	[NATIONALITY] [varchar](50) NULL,
	[RELIGION] [varchar](50) NULL,
	[CASTE] [varchar](50) NULL,
	[COMMUNITY] [varchar](50) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[EMAIL_ID] [varchar](100) NULL,
	[SOCIAL_MEDIA_ACCOUNTS] [varchar](1000) NULL,
	[AADHAR_NO] [bigint] NULL,
	[RATION_CARD_NO] [varchar](100) NULL,
	[VOTERID] [varchar](500) NULL,
	[PASSPORT] [varchar](500) NULL,
	[PANCARD] [varchar](500) NULL,
	[ELECTRICITY_CONNECTION] [varchar](500) NULL,
	[GAS_CONNECTION] [varchar](500) NULL,
	[VEHICLES] [varchar](500) NULL,
	[DRIVING_LICENSE] [varchar](500) NULL,
	[OTHER_ID_PROOFS] [varchar](500) NULL,
	[SEX] [varchar](100) NULL,
	[BUILT] [varchar](100) NULL,
	[HEIGHT] [varchar](100) NULL,
	[EYES] [varchar](100) NULL,
	[HAIR] [varchar](100) NULL,
	[FACE] [varchar](100) NULL,
	[COLOUR] [varchar](100) NULL,
	[TEETH] [varchar](100) NULL,
	[NOSE] [varchar](100) NULL,
	[BEARD] [varchar](100) NULL,
	[MUSTACHES] [varchar](100) NULL,
	[EAR] [varchar](100) NULL,
	[IDENTIFICATION_MARKS] [varchar](500) NULL,
	[DEFORMITIES_PECULIARITIES] [varchar](500) NULL,
	[LANGUAGE_DIALECT] [varchar](500) NULL,
	[BURN_MARKS] [varchar](100) NULL,
	[LEUCODEMA] [varchar](100) NULL,
	[MOLE] [varchar](100) NULL,
	[SCAR] [varchar](100) NULL,
	[TATTOO] [varchar](500) NULL,
	[LIVING_STATUS] [varchar](100) NULL,
	[MARITAL_STATUS] [varchar](100) NULL,
	[EDUCATION_DETAILS] [varchar](500) NULL,
	[OCCUPATION] [varchar](250) NULL,
	[INCOME_GROUP] [varchar](100) NULL,
	[REGULAR_HABITS] [varchar](100) NULL,
	[CATEGORY] [varchar](50) NULL,
	[CC_OR_EXCC] [varchar](20) NULL,
	[CC_OR_EXCCNO] [varchar](20) NULL,
	[ASONDATE] [datetime] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[IR_MULTIPLE_CRIMES]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[IR_MULTIPLE_CRIMES](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[DISTRICT] [varchar](500) NULL,
	[CONFESSED_POLICE_STATION] [varchar](100) NULL,
	[CONFESSED_CRIME_NO] [varchar](100) NULL,
	[CONFESSED_YEAR] [varchar](100) NULL,
	[CONFESSED_SEC_OF_LAW] [varchar](500) NULL,
	[ASSOCIATES] [varchar](500) NULL,
	[PROPERTY_STOLEN] [varchar](500) NULL,
	[PROPERTY_RECOVERED] [varchar](1000) NULL,
	[REMARKS] [varchar](500) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[POLICE_STATION] [varchar](50) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[CONFESSED_DOA] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[IR_PARTICULARS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[IR_PARTICULARS](
	[IRKEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[NAME] [varchar](250) NULL,
	[ALIAS_NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[DATE_OF_BIRTH] [date] NULL,
	[NATIONALITY] [varchar](50) NULL,
	[RELIGION] [varchar](50) NULL,
	[CASTE] [varchar](50) NULL,
	[COMMUNITY] [varchar](50) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[EMAIL_ID] [varchar](100) NULL,
	[SOCIAL_MEDIA_ACCOUNTS] [varchar](1000) NULL,
	[AADHAR_NO] [bigint] NULL,
	[RATION_CARD_NO] [varchar](100) NULL,
	[VOTERID] [varchar](500) NULL,
	[PASSPORT] [varchar](500) NULL,
	[PANCARD] [varchar](500) NULL,
	[ELECTRICITY_CONNECTION] [varchar](500) NULL,
	[GAS_CONNECTION] [varchar](500) NULL,
	[VEHICLES] [varchar](500) NULL,
	[DRIVING_LICENSE] [varchar](500) NULL,
	[OTHER_ID_PROOFS] [varchar](500) NULL,
	[SEX] [varchar](100) NULL,
	[BUILT] [varchar](100) NULL,
	[HEIGHT] [varchar](100) NULL,
	[EYES] [varchar](100) NULL,
	[HAIR] [varchar](100) NULL,
	[FACE] [varchar](100) NULL,
	[COLOUR] [varchar](100) NULL,
	[TEETH] [varchar](100) NULL,
	[NOSE] [varchar](100) NULL,
	[BEARD] [varchar](100) NULL,
	[MUSTACHES] [varchar](100) NULL,
	[EAR] [varchar](100) NULL,
	[IDENTIFICATION_MARKS] [varchar](500) NULL,
	[DEFORMITIES_PECULIARITIES] [varchar](500) NULL,
	[LANGUAGE_DIALECT] [varchar](500) NULL,
	[BURN_MARKS] [varchar](100) NULL,
	[LEUCODEMA] [varchar](100) NULL,
	[MOLE] [varchar](100) NULL,
	[SCAR] [varchar](100) NULL,
	[TATTOO] [varchar](500) NULL,
	[LIVING_STATUS] [varchar](100) NULL,
	[MARITAL_STATUS] [varchar](100) NULL,
	[EDUCATION_DETAILS] [varchar](500) NULL,
	[OCCUPATION] [varchar](250) NULL,
	[INCOME_GROUP] [varchar](100) NULL,
	[REGULAR_HABITS] [varchar](100) NULL,
	[CATEGORY] [varchar](50) NULL,
	[CC_OR_EXCC] [varchar](20) NULL,
	[CC_OR_EXCCNO] [varchar](20) NULL,
	[ASONDATE] [datetime] NULL,
	[IR_ENTRY_DONE_BY] [varchar](50) NULL,
PRIMARY KEY CLUSTERED 
(
	[IRKEY] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[IR_PARTICULARS_CRIME]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[IR_PARTICULARS_CRIME](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[DISTRICT] [varchar](500) NULL,
	[CONFESSED_POLICE_STATION] [varchar](100) NULL,
	[CONFESSED_CRIME_NO] [varchar](100) NULL,
	[CONFESSED_YEAR] [varchar](100) NULL,
	[CONFESSED_SEC_OF_LAW] [varchar](500) NULL,
	[ASSOCIATES] [varchar](500) NULL,
	[PROPERTY_STOLEN] [varchar](500) NULL,
	[PROPERTY_RECOVERED] [varchar](1000) NULL,
	[REMARKS] [varchar](500) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[POLICE_STATION] [varchar](50) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[CONFESSED_DOA] [date] NULL,
	[NAME] [varchar](250) NULL,
	[ALIAS_NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[IR_PARTICULARS1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[IR_PARTICULARS1](
	[IRKEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[NAME] [varchar](250) NULL,
	[ALIAS_NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[DATE_OF_BIRTH] [date] NULL,
	[NATIONALITY] [varchar](50) NULL,
	[RELIGION] [varchar](50) NULL,
	[CASTE] [varchar](50) NULL,
	[COMMUNITY] [varchar](50) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[EMAIL_ID] [varchar](100) NULL,
	[SOCIAL_MEDIA_ACCOUNTS] [varchar](1000) NULL,
	[AADHAR_NO] [varchar](100) NULL,
	[RATION_CARD_NO] [varchar](100) NULL,
	[VOTERID] [varchar](500) NULL,
	[PASSPORT] [varchar](500) NULL,
	[PANCARD] [varchar](500) NULL,
	[ELECTRICITY_CONNECTION] [varchar](500) NULL,
	[GAS_CONNECTION] [varchar](500) NULL,
	[VEHICLES] [varchar](500) NULL,
	[DRIVING_LICENSE] [varchar](500) NULL,
	[OTHER_ID_PROOFS] [varchar](500) NULL,
	[SEX] [varchar](100) NULL,
	[BUILT] [varchar](100) NULL,
	[HEIGHT] [varchar](100) NULL,
	[EYES] [varchar](100) NULL,
	[HAIR] [varchar](100) NULL,
	[FACE] [varchar](100) NULL,
	[COLOUR] [varchar](100) NULL,
	[TEETH] [varchar](100) NULL,
	[NOSE] [varchar](100) NULL,
	[BEARD] [varchar](100) NULL,
	[MUSTACHES] [varchar](100) NULL,
	[EAR] [varchar](100) NULL,
	[IDENTIFICATION_MARKS] [varchar](500) NULL,
	[DEFORMITIES_PECULIARITIES] [varchar](500) NULL,
	[LANGUAGE_DIALECT] [varchar](500) NULL,
	[BURN_MARKS] [varchar](100) NULL,
	[LEUCODEMA] [varchar](100) NULL,
	[MOLE] [varchar](100) NULL,
	[SCAR] [varchar](100) NULL,
	[TATTOO] [varchar](500) NULL,
	[LIVING_STATUS] [varchar](100) NULL,
	[MARITAL_STATUS] [varchar](100) NULL,
	[EDUCATION_DETAILS] [varchar](500) NULL,
	[OCCUPATION] [varchar](250) NULL,
	[INCOME_GROUP] [varchar](100) NULL,
	[REGULAR_HABITS] [varchar](100) NULL,
	[CATEGORY] [varchar](50) NULL,
	[CC_OR_EXCC] [varchar](20) NULL,
	[CC_OR_EXCCNO] [varchar](20) NULL,
	[ASONDATE] [datetime] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ir_previous_2017]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ir_previous_2017](
	[irkey] [numeric](18, 0) NOT NULL,
	[name] [varchar](100) NULL,
	[alias_name] [varchar](100) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[mobile] [varchar](100) NULL,
	[category] [varchar](50) NULL,
	[relationship] [varchar](100) NULL,
	[relative_name] [varchar](50) NULL,
	[phone] [varchar](50) NULL,
	[crime_head] [varchar](500) NULL,
	[mo] [varchar](500) NULL,
	[date_of_arrest] [date] NULL,
	[crime_no] [int] NULL,
	[year] [int] NULL,
	[sec_of_law] [varchar](500) NULL,
	[police_station] [varchar](100) NULL,
	[confessed_police_station] [varchar](100) NULL,
	[CONFESSED_CRIME_NO] [varchar](100) NULL,
	[confessed_year] [varchar](100) NULL,
	[CONFESSED_SEC_OF_LAW] [varchar](500) NULL,
	[associates] [varchar](500) NULL,
	[remarks] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[IRPHOTOS_1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[IRPHOTOS_1](
	[irkey] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[MOBILE] [varchar](100) NULL,
	[name] [varchar](100) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JAN_TO_JUNE_IR_DATA]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JAN_TO_JUNE_IR_DATA](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[CC_OR_EXCC] [varchar](20) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](2000) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CONFESSED_CRIME_NO] [varchar](100) NULL,
	[CONFESSED_YEAR] [varchar](100) NULL,
	[CONFESSED_SEC_OF_LAW] [varchar](500) NULL,
	[CNT] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JEWELRY_SHOP]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JEWELRY_SHOP](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[MO] [varchar](2000) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[YEAR] [int] NULL,
	[CRIME_NO] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JRMS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JRMS](
	[S_NO] [varchar](4000) NULL,
	[JAIL_REF_ID] [varchar](4000) NULL,
	[HABITUAL_OR_NON_HABITUAL] [varchar](4000) NULL,
	[NAME] [varchar](4000) NULL,
	[FATHER_NAME] [varchar](4000) NULL,
	[AGE] [varchar](4000) NULL,
	[MOBILE] [varchar](4000) NULL,
	[ID_PROOF] [varchar](4000) NULL,
	[FULL_ADDRESS] [varchar](4000) NULL,
	[STATE] [varchar](4000) NULL,
	[ADMISSION_TO_JAIL] [varchar](4000) NULL,
	[RELEASE_DATE] [varchar](4000) NULL,
	[HEAD_OF_CRIME] [varchar](4000) NULL,
	[CRIME_NUMBER] [varchar](4000) NULL,
	[UNDER_SECTION] [varchar](4000) NULL,
	[ARRESTED_PS] [varchar](4000) NULL,
	[JAIL_NAME] [varchar](4000) NULL,
	[JAIL_REF_ID1] [varchar](4000) NULL,
	[GENDER] [varchar](4000) NULL,
	[TYPE_OF_RELEASE] [varchar](4000) NULL,
	[HEIGHT] [varchar](4000) NULL,
	[DEFORMATIES] [varchar](4000) NULL,
	[IDENTIFICATION_MARK_TYPE] [varchar](4000) NULL,
	[PLACE_OF_IDENTIFICATION_MARK] [varchar](4000) NULL,
	[RELEASE_DATE1] [varchar](4000) NULL,
	[IRKEY] [varchar](4000) NULL,
	[MO_OFFENDER_ID] [varchar](4000) NULL,
	[MO_POLICE_STATION] [varchar](4000) NULL,
	[RWD_ID] [varchar](4000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jrms_chanchalguda]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jrms_chanchalguda](
	[NAME] [varchar](3000) NULL,
	[FATHERS NAME] [varchar](3000) NULL,
	[RESIDENTIAL ADDRESS] [varchar](3000) NULL,
	[CRIME NO] [varchar](3000) NULL,
	[YEAR] [varchar](3000) NULL,
	[SECTION OF LAW] [varchar](3000) NULL,
	[POLICE STATION] [varchar](3000) NULL,
	[CONTACT NO] [varchar](3000) NULL,
	[AADHAR NO] [varchar](3000) NULL,
	[ARRESTED DATE] [varchar](3000) NULL,
	[RELEASED DATE] [varchar](3000) NULL,
	[JAIL] [varchar](3000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[LOCAL_CONTACTS_FACILITATORS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[LOCAL_CONTACTS_FACILITATORS](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[TOWN_CITY_OR_VILLAGE] [varchar](500) NULL,
	[POLICE_STATION_LIMITS] [varchar](500) NULL,
	[NAME] [varchar](500) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [varchar](100) NULL,
	[OCCUPATION] [varchar](100) NULL,
	[ADDRESS_OF_CONTACT_PERSON] [varchar](1000) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](50) NULL,
	[PHONE] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[LOGINS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[LOGINS](
	[USERNAME] [varchar](50) NULL,
	[PASSWORD] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ma]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ma](
	[irkey1] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[name1] [varchar](250) NULL,
	[fathername] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[mahesh]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mahesh](
	[IRKEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[NAME] [varchar](250) NULL,
	[ALIAS_NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[DATE_OF_BIRTH] [date] NULL,
	[NATIONALITY] [varchar](50) NULL,
	[RELIGION] [varchar](50) NULL,
	[CASTE] [varchar](50) NULL,
	[COMMUNITY] [varchar](50) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[EMAIL_ID] [varchar](100) NULL,
	[SOCIAL_MEDIA_ACCOUNTS] [varchar](1000) NULL,
	[AADHAR_NO] [bigint] NULL,
	[RATION_CARD_NO] [varchar](100) NULL,
	[VOTERID] [varchar](500) NULL,
	[PASSPORT] [varchar](500) NULL,
	[PANCARD] [varchar](500) NULL,
	[ELECTRICITY_CONNECTION] [varchar](500) NULL,
	[GAS_CONNECTION] [varchar](500) NULL,
	[VEHICLES] [varchar](500) NULL,
	[DRIVING_LICENSE] [varchar](500) NULL,
	[OTHER_ID_PROOFS] [varchar](500) NULL,
	[SEX] [varchar](100) NULL,
	[BUILT] [varchar](100) NULL,
	[HEIGHT] [varchar](100) NULL,
	[EYES] [varchar](100) NULL,
	[HAIR] [varchar](100) NULL,
	[FACE] [varchar](100) NULL,
	[COLOUR] [varchar](100) NULL,
	[TEETH] [varchar](100) NULL,
	[NOSE] [varchar](100) NULL,
	[BEARD] [varchar](100) NULL,
	[MUSTACHES] [varchar](100) NULL,
	[EAR] [varchar](100) NULL,
	[IDENTIFICATION_MARKS] [varchar](500) NULL,
	[DEFORMITIES_PECULIARITIES] [varchar](500) NULL,
	[LANGUAGE_DIALECT] [varchar](500) NULL,
	[BURN_MARKS] [varchar](100) NULL,
	[LEUCODEMA] [varchar](100) NULL,
	[MOLE] [varchar](100) NULL,
	[SCAR] [varchar](100) NULL,
	[TATTOO] [varchar](500) NULL,
	[LIVING_STATUS] [varchar](100) NULL,
	[MARITAL_STATUS] [varchar](100) NULL,
	[EDUCATION_DETAILS] [varchar](500) NULL,
	[OCCUPATION] [varchar](250) NULL,
	[INCOME_GROUP] [varchar](100) NULL,
	[REGULAR_HABITS] [varchar](100) NULL,
	[CATEGORY] [varchar](50) NULL,
	[CC_OR_EXCC] [varchar](20) NULL,
	[CC_OR_EXCCNO] [varchar](20) NULL,
	[ASONDATE] [datetime] NULL,
	[IR_ENTRY_DONE_BY] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MAHESH_123]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MAHESH_123](
	[IRKEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[NAME] [varchar](250) NULL,
	[ALIAS_NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[DATE_OF_BIRTH] [date] NULL,
	[NATIONALITY] [varchar](50) NULL,
	[RELIGION] [varchar](50) NULL,
	[CASTE] [varchar](50) NULL,
	[COMMUNITY] [varchar](50) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[MOBILE] [varchar](100) NULL,
	[EMAIL_ID] [varchar](100) NULL,
	[SOCIAL_MEDIA_ACCOUNTS] [varchar](1000) NULL,
	[AADHAR_NO] [bigint] NULL,
	[RATION_CARD_NO] [varchar](100) NULL,
	[VOTERID] [varchar](500) NULL,
	[PASSPORT] [varchar](500) NULL,
	[PANCARD] [varchar](500) NULL,
	[ELECTRICITY_CONNECTION] [varchar](500) NULL,
	[GAS_CONNECTION] [varchar](500) NULL,
	[VEHICLES] [varchar](500) NULL,
	[DRIVING_LICENSE] [varchar](500) NULL,
	[OTHER_ID_PROOFS] [varchar](500) NULL,
	[SEX] [varchar](100) NULL,
	[BUILT] [varchar](100) NULL,
	[HEIGHT] [varchar](100) NULL,
	[EYES] [varchar](100) NULL,
	[HAIR] [varchar](100) NULL,
	[FACE] [varchar](100) NULL,
	[COLOUR] [varchar](100) NULL,
	[TEETH] [varchar](100) NULL,
	[NOSE] [varchar](100) NULL,
	[BEARD] [varchar](100) NULL,
	[MUSTACHES] [varchar](100) NULL,
	[EAR] [varchar](100) NULL,
	[IDENTIFICATION_MARKS] [varchar](500) NULL,
	[DEFORMITIES_PECULIARITIES] [varchar](500) NULL,
	[LANGUAGE_DIALECT] [varchar](500) NULL,
	[BURN_MARKS] [varchar](100) NULL,
	[LEUCODEMA] [varchar](100) NULL,
	[MOLE] [varchar](100) NULL,
	[SCAR] [varchar](100) NULL,
	[TATTOO] [varchar](500) NULL,
	[LIVING_STATUS] [varchar](100) NULL,
	[MARITAL_STATUS] [varchar](100) NULL,
	[EDUCATION_DETAILS] [varchar](500) NULL,
	[OCCUPATION] [varchar](250) NULL,
	[INCOME_GROUP] [varchar](100) NULL,
	[REGULAR_HABITS] [varchar](100) NULL,
	[CATEGORY] [varchar](50) NULL,
	[CC_OR_EXCC] [varchar](20) NULL,
	[CC_OR_EXCCNO] [varchar](20) NULL,
	[ASONDATE] [datetime] NULL,
	[IR_ENTRY_DONE_BY] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MO_IRDATA_NAME]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MO_IRDATA_NAME](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[NAME] [varchar](100) NULL,
	[ALIAS_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[MOBILE] [varchar](100) NULL,
	[OCCUPATION] [varchar](250) NULL,
	[MO] [varchar](500) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[SUB_DIVISION] [varchar](100) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MO_LIST]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MO_LIST](
	[OFFENDER_ID] [varchar](3000) NULL,
	[IRKEY] [varchar](3000) NULL,
	[OFFENDERNAME] [varchar](3000) NULL,
	[ALIASES] [varchar](3000) NULL,
	[Father name] [varchar](3000) NULL,
	[DOB] [varchar](3000) NULL,
	[AGE] [varchar](3000) NULL,
	[PS] [varchar](3000) NULL,
	[MOBILENO] [varchar](3000) NULL,
	[PRESENT_ADDRESS] [varchar](3000) NULL,
	[Permanent_Address] [varchar](3000) NULL,
	[DATEOFLASTARREST] [varchar](3000) NULL,
	[PSARRESTED] [varchar](5000) NULL,
	[DATEOFRELEASE] [varchar](5000) NULL,
	[CURRENTACTIVITY] [varchar](3000) NULL,
	[PHOTOGRAPH_PATH] [varchar](3000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MO_LIST_IR_KEY]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MO_LIST_IR_KEY](
	[OFFNEDER _ID] [varchar](5000) NULL,
	[IRKEY] [varchar](5000) NULL,
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
/****** Object:  Table [dbo].[mo_mahesh]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[mo_mahesh](
	[OFFENDER_ID] [varchar](3000) NULL,
	[IRKEY] [varchar](3000) NULL,
	[OFFENDERNAME] [varchar](3000) NULL,
	[ALIASES] [varchar](3000) NULL,
	[Father name] [varchar](3000) NULL,
	[DOB] [varchar](3000) NULL,
	[AGE] [varchar](3000) NULL,
	[PS] [varchar](3000) NULL,
	[MOBILENO] [varchar](3000) NULL,
	[PRESENT_ADDRESS] [varchar](3000) NULL,
	[Permanent_Address] [varchar](3000) NULL,
	[DATEOFLASTARREST] [varchar](3000) NULL,
	[PSARRESTED] [varchar](3000) NULL,
	[DATEOFRELEASE] [varchar](3000) NULL,
	[CURRENTACTIVITY] [varchar](3000) NULL,
	[PHOTOGRAPH_PATH] [varchar](3000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MO_PS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MO_PS](
	[PS] [varchar](500) NULL,
	[OFFENDERNAME] [varchar](500) NULL,
	[ALIASES] [varchar](500) NULL,
	[Age] [varchar](500) NULL,
	[MOBILENO] [varchar](500) NULL,
	[CURRENTACTIVITY] [varchar](500) NULL,
	[MODUSOPERENDI] [varchar](500) NULL,
	[presentaddress] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MOBILE_SNAT_JAIL_DATA]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MOBILE_SNAT_JAIL_DATA](
	[IRKEY] [bigint] NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[CRIME_OR_CCNO] [varchar](1000) NULL,
	[YEAR] [varchar](1000) NULL,
	[SEC_OF_LAW] [varchar](1000) NULL,
	[POLICE_STATION] [varchar](1000) NULL,
	[PHONE] [varchar](1000) NULL,
	[AADHAR_NO] [varchar](1000) NULL,
	[ARRESTED_DATE] [varchar](1000) NULL,
	[RELEASED_DATE] [varchar](1000) NULL,
	[JAIL_NAME] [varchar](1000) NULL,
	[PDACT_KEY] [varchar](3500) NULL,
	[PDACT_ORDER_DATE] [varchar](3500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MOBILE_SNAT_JAIL_DATA1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MOBILE_SNAT_JAIL_DATA1](
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[CRIME_OR_CCNO] [varchar](1000) NULL,
	[YEAR] [varchar](1000) NULL,
	[SEC_OF_LAW] [varchar](1000) NULL,
	[POLICE_STATION] [varchar](1000) NULL,
	[PDACT_KEY] [varchar](3500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MOBILE_SNATCHERS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MOBILE_SNATCHERS](
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[CRIME_OR_CCNO] [varchar](1000) NULL,
	[YEAR] [varchar](1000) NULL,
	[SEC_OF_LAW] [varchar](1000) NULL,
	[POLICE_STATION] [varchar](1000) NULL,
	[PDACT_KEY] [varchar](3500) NULL,
	[ZONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[MULAKATH_DETAILS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[MULAKATH_DETAILS](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[DISTRICT] [varchar](500) NULL,
	[UNDERTRIAL_PRISONER_NO] [varchar](100) NULL,
	[VISITOR_NAME] [varchar](100) NULL,
	[VISITOR_PHONE_NO] [varchar](100) NULL,
	[VISITOR_ID] [varchar](500) NULL,
	[DATE_OF_MULAKATH] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[POLICE_STATION] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NBWS_COMP_TOTAL]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NBWS_COMP_TOTAL](
	[SLNO] [int] NULL,
	[POLICE_STATION] [varchar](5000) NULL,
	[NAME_AND_ADDRESS] [varchar](5000) NULL,
	[WHETHER_WARRANTEE_ADD_TORF] [varchar](5000) NULL,
	[DATE_OF_ISSUE] [varchar](5000) NULL,
	[CRIME_NO_AND_SEC] [varchar](5000) NULL,
	[MO] [varchar](5000) NULL,
	[NAME_OF_THE_COURT] [varchar](5000) NULL,
	[CCSC_NO] [varchar](5000) NULL,
	[ORIGINALLY_ARRESTED_OR_NOT] [varchar](5000) NULL,
	[RELEASED_STATUS] [varchar](5000) NULL,
	[NAME_AND_ADD_OF_SURIETY] [varchar](5000) NULL,
	[ACTION_AGAINST_SURIETY] [varchar](5000) NULL,
	[REASON_FOR_PENDING] [varchar](5000) NULL,
	[UNIQUE_KEY] [int] NULL,
	[IRKEY] [varchar](20) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NBWS_JANUARY_2018]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NBWS_JANUARY_2018](
	[SLNO] [int] NULL,
	[POLICE_STATION] [varchar](5000) NULL,
	[NAME_AND_ADDRESS] [varchar](5000) NULL,
	[WHETHER_WARRANTEE_ADD_TORF] [varchar](5000) NULL,
	[DATE_OF_ISSUE] [varchar](5000) NULL,
	[CRIME_NO_AND_SEC] [varchar](5000) NULL,
	[MO] [varchar](5000) NULL,
	[NAME_OF_THE_COURT] [varchar](5000) NULL,
	[CCSC_NO] [varchar](5000) NULL,
	[ORIGINALLY_ARRESTED_OR_NOT] [varchar](5000) NULL,
	[RELEASED_STATUS] [varchar](5000) NULL,
	[NAME_AND_ADD_OF_SURIETY] [varchar](5000) NULL,
	[ACTION_AGAINST_SURIETY] [varchar](5000) NULL,
	[REASON_FOR_PENDING] [varchar](5000) NULL,
	[UNIQUE_KEY] [int] NULL,
	[IRKEY] [varchar](20) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NBWS_VERIFY_DATA]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NBWS_VERIFY_DATA](
	[SLNO] [varchar](8000) NULL,
	[PRESENT_ABSENT] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[First_Hearing_Date] [varchar](8000) NULL,
	[Decision_Date] [varchar](8000) NULL,
	[Case_Status] [varchar](8000) NULL,
	[Next_Hearing_Date] [varchar](8000) NULL,
	[Nature_Of_Disposal ] [varchar](8000) NULL,
	[Court_Number_and_Judge] [varchar](8000) NULL,
	[Stage_Of_Case] [varchar](8000) NULL,
	[petitioner_Respondent] [varchar](8000) NULL,
	[criminal Civil] [varchar](8000) NULL,
	[Act_AND_Sec] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NICK]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NICK](
	[PHONE] [varchar](100) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](201) NULL,
	[FNAME] [varchar](100) NULL,
	[ADDRESS] [varchar](1000) NULL,
	[CITY] [varchar](1) NOT NULL,
	[STATE] [varchar](1) NOT NULL,
	[COUNTRY] [varchar](1) NOT NULL,
	[PIN] [varchar](1) NOT NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[DOO] [varchar](100) NULL,
	[PLACE_OF_OFF] [varchar](1) NOT NULL,
	[DOR] [varchar](1) NOT NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](500) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[UNIT] [varchar](100) NULL,
	[MODULE_NAME] [varchar](500) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [varchar](1) NOT NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [varchar](1) NOT NULL,
	[IMEINUMBER] [varchar](1) NOT NULL,
	[INC_OFFICER] [varchar](500) NULL,
	[CATEGORY] [varchar](1) NOT NULL,
	[ORGANISATION] [varchar](17) NOT NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [varchar](1) NOT NULL,
	[Date_of_Arrest] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NICK_17_18]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NICK_17_18](
	[PHONE] [varchar](100) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](201) NULL,
	[FNAME] [varchar](100) NULL,
	[ADDRESS] [varchar](1000) NULL,
	[CITY] [varchar](1) NOT NULL,
	[STATE] [varchar](1) NOT NULL,
	[COUNTRY] [varchar](1) NOT NULL,
	[PIN] [varchar](1) NOT NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[DOO] [varchar](100) NULL,
	[PLACE_OF_OFF] [varchar](1) NOT NULL,
	[DOR] [varchar](1) NOT NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](500) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[UNIT] [varchar](100) NULL,
	[MODULE_NAME] [varchar](500) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [varchar](1) NOT NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [varchar](1) NOT NULL,
	[IMEINUMBER] [varchar](1) NOT NULL,
	[INC_OFFICER] [varchar](500) NULL,
	[CATEGORY] [varchar](1) NOT NULL,
	[ORGANISATION] [varchar](17) NOT NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [varchar](1) NOT NULL,
	[Date_of_Arrest] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NIK_1718]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NIK_1718](
	[IRKEY] [numeric](18, 0) NULL,
	[PHONE] [varchar](100) NULL,
	[ROLE] [varchar](7) NOT NULL,
	[NICKNAME] [varchar](201) NULL,
	[FNAME] [varchar](100) NULL,
	[ADDRESS] [varchar](1000) NULL,
	[CITY] [varchar](1) NOT NULL,
	[STATE] [varchar](1) NOT NULL,
	[COUNTRY] [varchar](1) NOT NULL,
	[PIN] [varchar](1) NOT NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[DOO] [varchar](100) NULL,
	[PLACE_OF_OFF] [varchar](1) NOT NULL,
	[DOR] [varchar](1) NOT NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](500) NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[UNIT] [varchar](100) NULL,
	[MODULE_NAME] [varchar](500) NULL,
	[ISACTIVE] [varchar](1) NOT NULL,
	[LNAME] [varchar](1) NOT NULL,
	[CHECKFLAG] [varchar](1) NOT NULL,
	[DOB_YEAR] [varchar](1) NOT NULL,
	[IMEINUMBER] [varchar](1) NOT NULL,
	[INC_OFFICER] [varchar](500) NULL,
	[CATEGORY] [varchar](1) NOT NULL,
	[ORGANISATION] [varchar](17) NOT NULL,
	[ASONDATE] [datetime] NOT NULL,
	[REMARKS] [varchar](1) NOT NULL,
	[Date_of_Arrest] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NIKI]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NIKI](
	[present_Absent] [varchar](2000) NULL,
	[TRACK_ID] [varchar](2000) NULL,
	[NAME_AS_PER_ID] [varchar](2000) NULL,
	[FATHER_NAME_AS_PER_ID] [varchar](2000) NULL,
	[ADDRESS_AS_PER_ID] [varchar](2000) NULL,
	[First_Hearing_Date] [varchar](2000) NULL,
	[Decision_Date] [varchar](2000) NULL,
	[Case_Status] [varchar](2000) NULL,
	[Next_Hearing_Date] [varchar](2000) NULL,
	[Nature_Of_Disposal ] [varchar](2000) NULL,
	[Court_Number_and_Judge] [varchar](2000) NULL,
	[Stage_Of_Case] [varchar](2000) NULL,
	[petitioner_Respondent] [varchar](2000) NULL,
	[criminal_Civil] [varchar](2000) NULL,
	[SEC_OF_LAW] [varchar](2000) NULL,
	[UPDATE_LINK] [varchar](2000) NULL,
	[OLD_LINK] [varchar](2000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[nk]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[nk](
	[IRKEY] [float] NULL,
	[NAME] [nvarchar](255) NULL,
	[FATHER_NAME] [nvarchar](255) NULL,
	[CRIME_HEAD] [nvarchar](255) NULL,
	[DATE_OF_ARREST] [datetime] NULL,
	[CRIME_NO] [float] NULL,
	[YEAR] [float] NULL,
	[POLICE_STATION] [nvarchar](255) NULL,
	[PHONE] [float] NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[NUMBER_PROFORMA]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NUMBER_PROFORMA](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[RELATIONSHIP] [varchar](100) NULL,
	[NAME] [varchar](50) NULL,
	[FATHER_OR_SPOUSE] [varchar](100) NULL,
	[OCCUPATION] [varchar](100) NULL,
	[PHONE] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[OFFENCE_DETAILS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[OFFENCE_DETAILS](
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
	[POLICE_STATION] [varchar](100) NULL,
	[ARREST_TYPE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[OFFENCE_DETAILS_06_12_2025]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[OFFENCE_DETAILS_06_12_2025](
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
	[POLICE_STATION] [varchar](100) NULL,
	[ARREST_TYPE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[OFFENCE_DETAILS_3]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[OFFENCE_DETAILS_3](
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
	[POLICE_STATION] [varchar](100) NULL,
	[a] [varchar](1000) NULL,
UNIQUE NONCLUSTERED 
(
	[IRKEY] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[OFFENCE_DETAILS_4]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[OFFENCE_DETAILS_4](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[PERIOD_OF_OFFENCE] [varchar](100) NULL,
	[REGULAR_RESIDENCE] [varchar](100) NULL,
	[PREPARATION_OF_OFFENCE] [varchar](500) NULL,
	[AFTER_OFFENCE] [varchar](500) NULL,
	[INDULGANCE_BEFORE_OFFENCE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](100) NULL,
	[SUB_TYPE] [varchar](100) NULL,
	[MO] [varchar](100) NULL,
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
/****** Object:  Table [dbo].[OFFENCE_DETAILS_OLD]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[OFFENCE_DETAILS_OLD](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[PERIOD_OF_OFFENCE] [varchar](100) NULL,
	[REGULAR_RESIDENCE] [varchar](500) NULL,
	[PREPARATION_OF_OFFENCE] [varchar](500) NULL,
	[AFTER_OFFENCE] [varchar](500) NULL,
	[INDULGANCE_BEFORE_OFFENCE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[SUB_TYPE] [varchar](100) NULL,
	[MO] [varchar](500) NULL,
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
/****** Object:  Table [dbo].[PDACT]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT](
	[PDACT_KEY] [varchar](3500) NULL,
	[PDACT_CALL_KEY] [varchar](3500) NULL,
	[Name] [varchar](3500) NULL,
	[Father_Name] [varchar](3500) NULL,
	[Age] [varchar](3500) NULL,
	[Dob] [varchar](3500) NULL,
	[Occupation] [varchar](3500) NULL,
	[Caste] [varchar](3500) NULL,
	[Id_Proof] [varchar](3500) NULL,
	[Id_Proof_No] [varchar](3500) NULL,
	[Phone_No] [varchar](3500) NULL,
	[Irkey] [varchar](3500) NULL,
	[Present_Address] [varchar](3500) NULL,
	[Permanent_Address] [varchar](3500) NULL,
	[District] [varchar](3500) NULL,
	[State] [varchar](3500) NULL,
	[PD_ACT_PS] [varchar](3500) NULL,
	[Zone] [varchar](3500) NULL,
	[File_no] [varchar](3500) NULL,
	[File_No_Year] [varchar](3500) NULL,
	[Detenu_No] [varchar](3500) NULL,
	[Order_Issued_On] [varchar](3500) NULL,
	[Approval_Orders_No] [varchar](3500) NULL,
	[Confirmation_Revocation_Orders] [varchar](3500) NULL,
	[Crime_Head] [varchar](3500) NULL,
	[Minor_Head] [varchar](3500) NULL,
	[ModusOperendi] [varchar](3500) NULL,
	[Police_Station] [varchar](3500) NULL,
	[Crime_No] [varchar](3500) NULL,
	[Year] [varchar](3500) NULL,
	[Sec_Of_Law] [varchar](3500) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](3500) NULL,
	[Name_Of_Units] [varchar](3500) NULL,
	[No_Of_Cases] [varchar](3500) NULL,
	[Date_Of_Arrest] [varchar](3500) NULL,
	[CRIME_HEAD_SEARCH] [varchar](3500) NULL,
	[DIVISION] [varchar](3500) NULL,
	[Column 37] [varchar](3500) NULL,
	[Column 38] [varchar](3500) NULL,
	[Column 39] [varchar](3500) NULL,
	[Column 40] [varchar](3500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_MATCHING_IR4]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_MATCHING_IR4](
	[PDACT_KEY] [varchar](3500) NULL,
	[PDACT_CALL_KEY] [varchar](3500) NULL,
	[Name] [varchar](3500) NULL,
	[Father_Name] [varchar](3500) NULL,
	[Age] [varchar](3500) NULL,
	[Dob] [varchar](3500) NULL,
	[Occupation] [varchar](3500) NULL,
	[Caste] [varchar](3500) NULL,
	[Id_Proof] [varchar](3500) NULL,
	[Id_Proof_No] [varchar](3500) NULL,
	[Phone_No] [varchar](3500) NULL,
	[Irkey] [varchar](3500) NULL,
	[Present_Address] [varchar](3500) NULL,
	[Permanent_Address] [varchar](8000) NULL,
	[District] [varchar](3500) NULL,
	[State] [varchar](3500) NULL,
	[PD_ACT_PS] [varchar](3500) NULL,
	[Zone] [varchar](3500) NULL,
	[File_no] [varchar](3500) NULL,
	[File_No_Year] [varchar](3500) NULL,
	[Detenu_No] [varchar](3500) NULL,
	[Order_Issued_On] [varchar](3500) NULL,
	[Approval_Orders_No] [varchar](3500) NULL,
	[Confirmation_Revocation_Orders] [varchar](3500) NULL,
	[Crime_Head] [varchar](3500) NULL,
	[Minor_Head] [varchar](3500) NULL,
	[ModusOperendi] [varchar](3500) NULL,
	[Police_Station] [varchar](3500) NULL,
	[Crime_No] [varchar](3500) NULL,
	[Year] [varchar](3500) NULL,
	[Sec_Of_Law] [varchar](3500) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](3500) NULL,
	[Name_Of_Units] [varchar](3500) NULL,
	[No_Of_Cases] [varchar](3500) NULL,
	[Date_Of_Arrest] [varchar](3500) NULL,
	[CRIME_HEAD_SEARCH] [varchar](3500) NULL,
	[DIVISION] [varchar](3500) NULL,
	[Column 37] [varchar](3500) NULL,
	[Column 38] [varchar](3500) NULL,
	[Column 39] [varchar](3500) NULL,
	[Column 40] [varchar](3500) NULL,
	[IR_PRESENT_ADDRESS] [varchar](1000) NULL,
	[IR_PERMANENT_ADDRESS] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_MATCHING_IR5]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_MATCHING_IR5](
	[PDACT_KEY] [varchar](3500) NULL,
	[PDACT_CALL_KEY] [varchar](3500) NULL,
	[Name] [varchar](3500) NULL,
	[Father_Name] [varchar](3500) NULL,
	[Age] [varchar](3500) NULL,
	[Dob] [varchar](3500) NULL,
	[Occupation] [varchar](3500) NULL,
	[Caste] [varchar](3500) NULL,
	[Id_Proof] [varchar](3500) NULL,
	[Id_Proof_No] [varchar](3500) NULL,
	[Phone_No] [varchar](3500) NULL,
	[Irkey] [varchar](3500) NULL,
	[Present_Address] [varchar](3500) NULL,
	[Permanent_Address] [varchar](3500) NULL,
	[District] [varchar](3500) NULL,
	[State] [varchar](3500) NULL,
	[PD_ACT_PS] [varchar](3500) NULL,
	[Zone] [varchar](3500) NULL,
	[File_no] [varchar](3500) NULL,
	[File_No_Year] [varchar](3500) NULL,
	[Detenu_No] [varchar](3500) NULL,
	[Order_Issued_On] [varchar](3500) NULL,
	[Approval_Orders_No] [varchar](3500) NULL,
	[Confirmation_Revocation_Orders] [varchar](3500) NULL,
	[Crime_Head] [varchar](3500) NULL,
	[Minor_Head] [varchar](3500) NULL,
	[ModusOperendi] [varchar](3500) NULL,
	[Police_Station] [varchar](3500) NULL,
	[Crime_No] [varchar](3500) NULL,
	[Year] [varchar](3500) NULL,
	[Sec_Of_Law] [varchar](3500) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](3500) NULL,
	[Name_Of_Units] [varchar](3500) NULL,
	[No_Of_Cases] [varchar](3500) NULL,
	[Date_Of_Arrest] [varchar](3500) NULL,
	[CRIME_HEAD_SEARCH] [varchar](3500) NULL,
	[DIVISION] [varchar](3500) NULL,
	[Column 37] [varchar](3500) NULL,
	[Column 38] [varchar](3500) NULL,
	[Column 39] [varchar](3500) NULL,
	[Column 40] [varchar](3500) NULL,
	[IR_PRESENT_ADDRESS] [varchar](1000) NULL,
	[IR_PERMANENT_ADDRESS] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdcell_data]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdcell_data](
	[slno] [varchar](4000) NULL,
	[Year] [varchar](4000) NULL,
	[File Number] [varchar](4000) NULL,
	[remarks] [varchar](4000) NULL,
	[pdact_key] [varchar](4000) NULL,
	[NAME] [varchar](4000) NULL,
	[FATHERNAME] [varchar](4000) NULL,
	[Address] [varchar](4000) NULL,
	[Category as per Act] [varchar](4000) NULL,
	[Specific Category] [varchar](4000) NULL,
	[Sub category] [varchar](4000) NULL,
	[Police Station] [varchar](4000) NULL,
	[Zone] [varchar](4000) NULL,
	[Year of Orders issued] [varchar](4000) NULL,
	[Orders issued on] [varchar](4000) NULL,
	[Whether order served] [varchar](4000) NULL,
	[Year of Detention] [varchar](4000) NULL,
	[Date of detention] [varchar](4000) NULL,
	[Detenu No] [varchar](4000) NULL,
	[If not served Proclamation orders issued on] [varchar](4000) NULL,
	[Approval orders No date] [varchar](4000) NULL,
	[Revocation Orders GO Rt No Date] [varchar](4000) NULL,
	[Confirmation orders vide GO Rt No] [varchar](4000) NULL,
	[Date of Confirmation Orders] [varchar](4000) NULL,
	[Date of Accused released from Detention Jail] [varchar](4000) NULL,
	[No of Times detained] [varchar](4000) NULL,
	[District] [varchar](4000) NULL,
	[Nativity] [varchar](4000) NULL,
	[Religion] [varchar](4000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PREVIOUS_OFFENCE_DETAILS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[DISTRICT] [varchar](500) NULL,
	[CONFESSED_POLICE_STATION] [varchar](100) NULL,
	[CONFESSED_CRIME_NO] [varchar](100) NULL,
	[CONFESSED_YEAR] [varchar](100) NULL,
	[CONFESSED_SEC_OF_LAW] [varchar](500) NULL,
	[ASSOCIATES] [varchar](500) NULL,
	[PROPERTY_STOLEN] [varchar](500) NULL,
	[PROPERTY_RECOVERED] [varchar](1000) NULL,
	[REMARKS] [varchar](500) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[POLICE_STATION] [varchar](50) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[CONFESSED_DOA] [date] NULL,
	[CONFSSED_DATE_OF_RELEASE] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PREVIOUS_OFFENCE_DETAILS1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS1](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[DISTRICT] [varchar](500) NULL,
	[CONFESSED_POLICE_STATION] [varchar](100) NULL,
	[CONFESSED_CRIME_NO] [varchar](100) NULL,
	[CONFESSED_YEAR] [varchar](100) NULL,
	[CONFESSED_SEC_OF_LAW] [varchar](500) NULL,
	[CONFESSED_DATE_OF_ARREST] [varchar](100) NULL,
	[ASSOCIATES] [varchar](500) NULL,
	[PROPERTY_STOLEN] [varchar](500) NULL,
	[PROPERTY_RECOVERED] [varchar](1000) NULL,
	[REMARKS] [varchar](500) NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[POLICE_STATION] [varchar](50) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[CONFESSED_DATE_OF_RELEASE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PROPERTY_OFFENDERS_LIST]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PROPERTY_OFFENDERS_LIST](
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[IRKEY] [numeric](18, 0) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[ZONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PROPERTY_OFFENDERS_TOTAL]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PROPERTY_OFFENDERS_TOTAL](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PROPERTY_OFFENDERS_TOTAL1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PROPERTY_OFFENDERS_TOTAL1](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[ZONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PROPERTY_OFFENDERS_TOTAL2]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PROPERTY_OFFENDERS_TOTAL2](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[ZONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[RECEIVERS_LIST]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[RECEIVERS_LIST](
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[IRKEY] [numeric](18, 0) NOT NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](8) NOT NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[ZONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[RECEIVERS_TOTAL]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[RECEIVERS_TOTAL](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](8) NOT NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[RECEIVERS_TOTAL1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[RECEIVERS_TOTAL1](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[MO] [varchar](8) NOT NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[ZONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[RELATIONSHIP_WITH_OTHER_ASSOCIATES]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[RELATIONSHIP_WITH_OTHER_ASSOCIATES](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[GANG] [varchar](100) NULL,
	[CATEGORY] [varchar](100) NULL,
	[MEMBER] [varchar](100) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [varchar](100) NULL,
	[OCCUPATION] [varchar](200) NULL,
	[ADDRESS] [varchar](500) NULL,
	[PHONE] [varchar](100) NULL,
	[RELATIONSHIP] [varchar](100) NULL,
	[REMARKS] [varchar](250) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[REPEATED_OFFENDERS_LIST]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[REPEATED_OFFENDERS_LIST](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[REPEATED_OFFENDERS_LIST1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[REPEATED_OFFENDERS_LIST1](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[MOBILE] [varchar](100) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL,
	[ZONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ROWDY SHEETERS TO CHECK]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ROWDY SHEETERS TO CHECK](
	[Rowdy_sheeter_id] [varchar](8000) NULL,
	[Irkey] [varchar](8000) NULL,
	[PDAct_Key] [varchar](8000) NULL,
	[Latest Arreste Date] [varchar](8000) NULL,
	[Name of the Ps] [varchar](8000) NULL,
	[Date of opening of Rowdy sheet  DD MM YY] [varchar](8000) NULL,
	[Rowdy sheet open Year] [varchar](8000) NULL,
	["Name  @ Alias name "] [varchar](8000) NULL,
	[Age] [varchar](8000) NULL,
	[Father Name ] [varchar](8000) NULL,
	[Present address ] [varchar](8000) NULL,
	[Lat ] [varchar](8000) NULL,
	[Lang] [varchar](8000) NULL,
	[Permanent address] [varchar](8000) NULL,
	[Latitude ] [varchar](8000) NULL,
	[Langitude ] [varchar](8000) NULL,
	[Phone number ] [varchar](8000) NULL,
	[ID proof Type] [varchar](8000) NULL,
	[ID_No] [varchar](8000) NULL,
	[Communal  Non Communal ] [varchar](8000) NULL,
	[Active In Active] [varchar](8000) NULL,
	[Latest Bind over date] [varchar](8000) NULL,
	[Year] [varchar](8000) NULL,
	[Present Activity ] [varchar](8000) NULL,
	[Photo (Soft copy)_ID] [varchar](8000) NULL,
	[Remarks ] [varchar](8000) NULL,
	[PS Transfer Status] [varchar](8000) NULL,
	[Count of involved  cases] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ROWDY_SHEETERS_TOTAL]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ROWDY_SHEETERS_TOTAL](
	[RWD_ID] [int] NULL,
	[IRKEY] [int] NULL,
	[PDACT_KEY] [varchar](8000) NULL,
	[LATEST_ARREST] [varchar](8000) NULL,
	[POLICE_STATION] [varchar](8000) NULL,
	[DATE_OF_OPENING_RWD] [varchar](8000) NULL,
	[RWD_YEAR] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[AGE] [varchar](8000) NULL,
	[FATHER_NAME] [varchar](8000) NULL,
	[PRESENT_ADDRESS] [varchar](8000) NULL,
	[LAT_P] [varchar](8000) NULL,
	[LONG_P] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[LAT] [varchar](8000) NULL,
	[LONG] [varchar](8000) NULL,
	[PHONE] [varchar](8000) NULL,
	[ID_PROOF_TYPE] [varchar](8000) NULL,
	[ID_NO] [varchar](8000) NULL,
	[COMMUNAL_NONCOMMUNAL] [varchar](8000) NULL,
	[ACTIVE_INACTIVE] [varchar](8000) NULL,
	[LATEST_BIND_OVER_DATE] [varchar](8000) NULL,
	[LBO_YEAR] [varchar](8000) NULL,
	[PRESENT_ACTIVITY] [varchar](8000) NULL,
	[PHOTO_ID] [varchar](8000) NULL,
	[Remarks ] [varchar](8000) NULL,
	[PS_TRANSFER_STATUS] [varchar](8000) NULL,
	[COUNT_OF_INVD_CASES] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[ROWDY_SHEETERS_TOTAL1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[ROWDY_SHEETERS_TOTAL1](
	[RWD_ID] [int] NULL,
	[IRKEY] [int] NULL,
	[PDACT_KEY] [varchar](3000) NULL,
	[LATEST_ARREST] [varchar](3000) NULL,
	[POLICE_STATION] [varchar](3000) NULL,
	[DATE_OF_OPENING_RWD] [varchar](3000) NULL,
	[RWD_YEAR] [varchar](3000) NULL,
	[NAME] [varchar](3000) NULL,
	[AGE] [varchar](3000) NULL,
	[FATHER_NAME] [varchar](3000) NULL,
	[PRESENT_ADDRESS] [varchar](3000) NULL,
	[LAT_P] [varchar](3000) NULL,
	[LONG_P] [varchar](3000) NULL,
	[PERMANENT_ADDRESS] [varchar](3000) NULL,
	[LAT] [varchar](3000) NULL,
	[LONG] [varchar](3000) NULL,
	[PHONE] [varchar](3000) NULL,
	[ID_PROOF_TYPE] [varchar](3000) NULL,
	[ID_NO] [varchar](3000) NULL,
	[COMMUNAL_NONCOMMUNAL] [varchar](3000) NULL,
	[ACTIVE_INACTIVE] [varchar](3000) NULL,
	[LATEST_BIND_OVER_DATE] [varchar](3000) NULL,
	[LBO_YEAR] [varchar](3000) NULL,
	[PRESENT_ACTIVITY] [varchar](3000) NULL,
	[PHOTO_ID] [varchar](3000) NULL,
	[REMARKS] [varchar](3000) NULL,
	[PS_TRANSFER_STATUS] [varchar](3000) NULL,
	[COUNT_OF_INVD_CASES] [varchar](3000) NULL,
	[ZONE] [varchar](3000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[RWD_CHECK]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[RWD_CHECK](
	[RWD_ID] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[PDACT_KEY] [varchar](8000) NULL,
	[LATEST_ARREST_DATE] [varchar](8000) NULL,
	[POLICE_STATION] [varchar](8000) NULL,
	[Date of opening of Rowdy sheet  DD MM YY] [varchar](8000) NULL,
	[Rowdy sheet open Year] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[Age] [varchar](8000) NULL,
	[FATHER_NAME] [varchar](8000) NULL,
	[Present address ] [varchar](8000) NULL,
	[Lat] [varchar](8000) NULL,
	[Lang] [varchar](8000) NULL,
	[Permanent address] [varchar](8000) NULL,
	[Latitude ] [varchar](8000) NULL,
	[Langitude ] [varchar](8000) NULL,
	[Phone number ] [varchar](8000) NULL,
	[ID proof Type] [varchar](8000) NULL,
	[ID_No] [varchar](8000) NULL,
	[Communal  Non Communal ] [varchar](8000) NULL,
	[Active In Active] [varchar](8000) NULL,
	[Latest Bind over date] [varchar](8000) NULL,
	[Year] [varchar](8000) NULL,
	[Present Activity ] [varchar](8000) NULL,
	[Photo (Soft copy)_ID] [varchar](8000) NULL,
	[Remarks ] [varchar](8000) NULL,
	[PS Transfer Status] [varchar](8000) NULL,
	[Count of involved  cases] [varchar](8000) NULL,
	[Column 28] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[RWDY_CHECK_ASON_080820]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[RWDY_CHECK_ASON_080820](
	[RWD_ID] [varchar](8000) NULL,
	[Irkey] [varchar](8000) NULL,
	[PDAct_Key] [varchar](8000) NULL,
	[latest_arrest] [varchar](8000) NULL,
	[police_station] [varchar](8000) NULL,
	[date_of_rwd] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[name] [varchar](8000) NULL,
	[Age] [varchar](8000) NULL,
	[father_name] [varchar](8000) NULL,
	[present_address] [varchar](8000) NULL,
	[Latitude1 ] [varchar](8000) NULL,
	[Langitude1 ] [varchar](8000) NULL,
	[permanent_address] [varchar](8000) NULL,
	[Latitude2] [varchar](8000) NULL,
	[Langitude2 ] [varchar](8000) NULL,
	[phone] [varchar](8000) NULL,
	[idproof] [varchar](8000) NULL,
	[ID_No] [varchar](8000) NULL,
	[communal_noncommunal] [varchar](8000) NULL,
	[ACTIVE_STATUS] [varchar](8000) NULL,
	[latest_bind_over] [varchar](8000) NULL,
	[year2] [varchar](8000) NULL,
	[present_activity] [varchar](8000) NULL,
	[photo_id] [varchar](8000) NULL,
	[Remarks ] [varchar](8000) NULL,
	[TRANSFER_PS] [varchar](8000) NULL,
	[COUNT_OF_INVOLVED_CASES] [varchar](8000) NULL,
	[ZONE] [varchar](10) NULL,
	[DIVISION] [varchar](30) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SAMPLE]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SAMPLE](
	[NAME] [varchar](4000) NULL,
	[EMP_ID] [varchar](4000) NULL,
	[GENDER] [varchar](4000) NULL,
	[MACHINE_PASS_OR_FAIL] [varchar](4000) NULL,
	[DISTRICT] [varchar](4000) NULL,
	[DATE] [varchar](4000) NULL,
	[FACE] [varchar](4000) NULL,
	[GENDER1] [varchar](4000) NULL,
	[RANKING] [varchar](4000) NULL,
	[STATUS] [varchar](4000) NULL,
	[MARTIAL] [varchar](4000) NULL,
	[STATUS1] [varchar](4000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SAMPLE2]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SAMPLE2](
	[EMP_IDD] [varchar](4000) NULL,
	[GENDER M/F] [varchar](4000) NULL,
	[MACHINE_PASS_OR_FAIL2] [varchar](4000) NULL,
	[DISTRICT_NATIVE] [varchar](4000) NULL,
	[DATE OF PERIOD] [varchar](4000) NULL,
	[FACE 7] [varchar](4000) NULL,
	[RANKING_IN_ORDER] [varchar](4000) NULL,
	[STATUS L/A] [varchar](4000) NULL,
	[MARTIAL_M/U] [varchar](4000) NULL,
	[ADRESS11] [varchar](4000) NULL,
	[NAME_OF_NAME2] [int] NOT NULL,
	[NAME9] [varchar](4000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SERIES]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SERIES](
	[IRKEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[NAME] [varchar](100) NULL,
	[ALIAS_NAME] [varchar](100) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[DATE_OF_BIRTH] [date] NULL,
PRIMARY KEY CLUSTERED 
(
	[IRKEY] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[Sheet1$]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Sheet1$](
	[PDACT_KEY] [float] NULL,
	[PDACT_CALL_KEY] [float] NULL,
	[Name] [nvarchar](255) NULL,
	[Father_Name] [nvarchar](255) NULL,
	[Age] [float] NULL,
	[Occupation] [nvarchar](255) NULL,
	[Phone_No] [nvarchar](255) NULL,
	[Present_Address] [nvarchar](255) NULL,
	[Permanent_Address] [nvarchar](255) NULL,
	[District] [nvarchar](255) NULL,
	[State] [nvarchar](255) NULL,
	[PD_ACT_PS] [nvarchar](255) NULL,
	[Zone] [nvarchar](255) NULL,
	[File_no] [float] NULL,
	[File_No_Year] [float] NULL,
	[Detenu_No] [float] NULL,
	[Order_Issued_On] [datetime] NULL,
	[Approval_Orders_No] [nvarchar](255) NULL,
	[Confirmation_Revocation_Orders] [nvarchar](255) NULL,
	[Crime_Head] [nvarchar](255) NULL,
	[Minor_Head] [nvarchar](255) NULL,
	[ModusOperendi] [nvarchar](255) NULL,
	[Police_Station] [nvarchar](255) NULL,
	[Crime_No] [nvarchar](255) NULL,
	[Year] [nvarchar](255) NULL,
	[Sec_Of_Law] [nvarchar](255) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [nvarchar](255) NULL,
	[Name_Of_Units] [nvarchar](255) NULL,
	[No_Of_Cases] [float] NULL,
	[Date_Of_Arrest] [datetime] NULL,
	[Date_Of_Release] [datetime] NULL,
	[CRIME_HEAD_SEARCH] [nvarchar](255) NULL,
	[DIVISION] [nvarchar](255) NULL,
	[F34] [nvarchar](255) NULL,
	[F35] [nvarchar](255) NULL,
	[F36] [nvarchar](255) NULL,
	[F37] [nvarchar](255) NULL,
	[F38] [nvarchar](255) NULL,
	[F39] [nvarchar](255) NULL,
	[F40] [nvarchar](255) NULL,
	[F41] [nvarchar](255) NULL,
	[F42] [nvarchar](255) NULL,
	[F43] [nvarchar](255) NULL,
	[F44] [nvarchar](255) NULL,
	[F45] [nvarchar](255) NULL,
	[F46] [nvarchar](255) NULL,
	[F47] [nvarchar](255) NULL,
	[F48] [nvarchar](255) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[SIX_MNTHS_DATA]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SIX_MNTHS_DATA](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](250) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[AGE] [int] NULL,
	[OCCUPATION] [varchar](250) NULL,
	[MOBILE] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[CRIME_HEAD] [varchar](500) NULL,
	[DATE_OF_ARREST] [date] NULL,
	[CRIME_NO] [int] NULL,
	[YEAR] [int] NULL,
	[SEC_OF_LAW] [varchar](500) NULL,
	[POLICE_STATION] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SNATCHING_2019_2020]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SNATCHING_2019_2020](
	[SL_NO] [varchar](1000) NULL,
	[IRKEY] [varchar](1000) NULL,
	[NAME] [varchar](1000) NULL,
	[FATHERS NAME] [varchar](1000) NULL,
	[RESIDENTIAL ADDRESS] [varchar](1000) NULL,
	[CRIME NO] [varchar](1000) NULL,
	[YEAR] [varchar](1000) NULL,
	[SECTION OF LAW] [varchar](1000) NULL,
	[POLICE STATION] [varchar](1000) NULL,
	[CONTACT NO] [varchar](1000) NULL,
	[AADHAR NO] [varchar](1000) NULL,
	[ARRESTED DATE] [varchar](1000) NULL,
	[RELEASED DATE] [varchar](1000) NULL,
	[JAIL] [varchar](1000) NULL,
	[OFFENDER ID] [varchar](1000) NULL,
	[ROWDY SHEETER ID] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SNATCHING_2019_2020_1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SNATCHING_2019_2020_1](
	[SL_NO] [varchar](1000) NULL,
	[IRKEY] [bigint] NULL,
	[NAME] [varchar](1000) NULL,
	[FATHERS NAME] [varchar](1000) NULL,
	[RESIDENTIAL ADDRESS] [varchar](1000) NULL,
	[CRIME NO] [varchar](1000) NULL,
	[YEAR] [varchar](1000) NULL,
	[SECTION OF LAW] [varchar](1000) NULL,
	[POLICE STATION] [varchar](1000) NULL,
	[CONTACT NO] [varchar](1000) NULL,
	[AADHAR NO] [varchar](1000) NULL,
	[ARRESTED DATE] [varchar](1000) NULL,
	[RELEASED DATE] [varchar](1000) NULL,
	[JAIL] [varchar](1000) NULL,
	[OFFENDER ID] [varchar](1000) NULL,
	[ROWDY SHEETER ID] [varchar](1000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SUS1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SUS1](
	[Name] [varchar](3000) NULL,
	[Father_name] [varchar](3000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[SUS2]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[SUS2](
	[NAME] [varchar](3000) NULL,
	[FATHER_NAME] [varchar](3000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[Suspect_sheet matched with IR]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[Suspect_sheet matched with IR](
	[NAME] [varchar](500) NULL,
	[FATHER_NAME] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[Suspect_Sheets]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[Suspect_Sheets](
	[DGP SIR PRESS MEET INFORMATION - 2019 (MOST URGENT)] [varchar](4000) NULL,
	[Column 1] [varchar](4000) NULL,
	[Column 2] [varchar](4000) NULL,
	[Column 3] [varchar](4000) NULL,
	[Column 4] [varchar](4000) NULL,
	[Column 5] [varchar](4000) NULL,
	[Column 6] [varchar](4000) NULL,
	[Column 7] [varchar](4000) NULL,
	[Column 8] [varchar](4000) NULL,
	[Column 9] [varchar](4000) NULL,
	[Column 10] [varchar](4000) NULL,
	[Column 11] [varchar](4000) NULL,
	[Column 12] [varchar](4000) NULL,
	[Column 13] [varchar](4000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[TEMP_THEFT]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[TEMP_THEFT](
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
/****** Object:  Table [dbo].[TSCOP_MO_LIST1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[TSCOP_MO_LIST1](
	[zone] [varchar](8000) NULL,
	[Divisions] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[OFFENDER_ID] [varchar](8000) NULL,
	[LATITUDE] [varchar](8000) NULL,
	[LONGITUDE] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[FATHER_NAME] [varchar](8000) NULL,
	[age] [varchar](8000) NULL,
	[dob] [varchar](8000) NULL,
	[MOBILENO] [varchar](8000) NULL,
	[PRESENT_ADDRESS] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[CURRENTACTIVITY] [varchar](8000) NULL,
	[dateoflastarrest] [varchar](8000) NULL,
	[psarrested] [varchar](8000) NULL,
	[dateofrelease] [varchar](8000) NULL,
	[MODUSOPERENDI] [varchar](8000) NULL,
	[MoType] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[twoandabove]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[twoandabove](
	[irkey] [numeric](18, 0) NOT NULL,
	[name] [varchar](100) NULL,
	[alias_name] [varchar](100) NULL,
	[father_name] [varchar](100) NULL,
	[age] [int] NULL,
	[present_address] [varchar](1000) NULL,
	[permanent_address] [varchar](1000) NULL,
	[mobile] [varchar](100) NULL,
	[crime_head] [varchar](500) NULL,
	[crime_no] [int] NULL,
	[year] [int] NULL,
	[sec_of_law] [varchar](500) NULL,
	[police_station] [varchar](100) NULL,
	[CONFESSED_CRIME_NO] [varchar](100) NULL,
	[CONFESSED_YEAR] [varchar](100) NULL,
	[CONFESSED_SEC_OF_LAW] [varchar](500) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[UI]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[UI](
	[IRKEY] [numeric](18, 0) NOT NULL,
	[ACCUSED_NAME] [varchar](250) NULL,
	[PHONE] [varchar](100) NULL,
	[police_station] [varchar](100) NULL,
	[crime_no] [int] NULL,
	[year] [int] NULL,
	[sec_of_law] [varchar](500) NULL,
	[crime_head] [varchar](500) NULL,
	[date_of_arrest] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[WARRANT_PURPOSE]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[WARRANT_PURPOSE](
	[IRKEY] [numeric](18, 0) NULL,
	[NAME] [varchar](100) NULL,
	[ALIAS_NAME] [varchar](100) NULL,
	[FATHER_NAME] [varchar](100) NULL,
	[PRESENT_ADDRESS] [varchar](1000) NULL,
	[PERMANENT_ADDRESS] [varchar](1000) NULL,
	[CONFESSED_CRIME_NO] [varchar](100) NULL,
	[CONFESSED_YEAR] [varchar](100) NULL,
	[CONFESSED_POLICE_STATION] [varchar](100) NULL,
	[CONFESSED_SEC_OF_LAW] [varchar](500) NULL,
	[ARRESTED_CRIME_NO] [int] NULL,
	[ARRESTED_YEAR] [int] NULL,
	[ARRESTED_POLICESTATION] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
ALTER TABLE [dbo].[BRIEF_FACTS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[BRIEF_FACTS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[BRIEF_FACTS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[DISPOSAL_OF_PROPERTY]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[DISPOSAL_OF_PROPERTY]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[DISPOSAL_OF_PROPERTY]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[FAMILY_HISTORY]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[FAMILY_HISTORY]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[FAMILY_HISTORY]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[IMAGE_TABLE]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[IMAGE_TABLE]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[IMAGE_TABLE]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[LOCAL_CONTACTS_FACILITATORS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[NUMBER_PROFORMA]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[SERIES] ([IRKEY])
GO
ALTER TABLE [dbo].[OFFENCE_DETAILS_3]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[OFFENCE_DETAILS_OLD]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[OFFENCE_DETAILS_OLD]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[PREVIOUS_OFFENCE_DETAILS]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
ALTER TABLE [dbo].[RELATIONSHIP_WITH_OTHER_ASSOCIATES]  WITH CHECK ADD FOREIGN KEY([IRKEY])
REFERENCES [dbo].[IR_PARTICULARS] ([IRKEY])
GO
/****** Object:  StoredProcedure [dbo].[IR_DETAILS]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

    CREATE PROCEDURE [dbo].[IR_DETAILS]
AS
BEGIN
    SET NOCOUNT ON;

    SELECT TOP (10000)
        T.*,
        E.IMAGE
    FROM
    (
        SELECT
            A.IRKEY,
            A.NAME,
            A.FATHER_NAME,
            A.PRESENT_ADDRESS,
            A.PERMANENT_ADDRESS,
            A.MOBILE,
            A.AADHAR_NO,
            B.CRIME_NO,
            B.YEAR,
            B.SEC_OF_LAW,
            B.POLICE_STATION,
            B.DATE_OF_ARREST,
            COUNT(C.IRKEY) AS PREVIOUS_OFFENCE_COUNT ,COUNT(D.IRKEY) AS ASSOCIATES_COUNT
        FROM IR_PARTICULARS A
        LEFT JOIN OFFENCE_DETAILS B
            ON A.IRKEY = B.IRKEY
        LEFT JOIN PREVIOUS_OFFENCE_DETAILS C
            ON A.IRKEY = C.IRKEY
        LEFT JOIN RELATIONSHIP_WITH_OTHER_ASSOCIATES D
            ON A.IRKEY = D.IRKEY
        GROUP BY
            A.IRKEY,
            A.NAME,
            A.FATHER_NAME,
            A.PRESENT_ADDRESS,
            A.PERMANENT_ADDRESS,
            A.MOBILE,
            A.AADHAR_NO,
            B.CRIME_NO,
            B.YEAR,
            B.SEC_OF_LAW,
            B.POLICE_STATION,
            B.DATE_OF_ARREST
    ) AS T
    LEFT JOIN IMAGE_TABLE E
        ON T.IRKEY = E.IRKEY
    ORDER BY T.IRKEY;
END;

GO
/****** Object:  StoredProcedure [dbo].[IR_DETAILS1]    Script Date: 13-Aug-26 6:04:18 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROCEDURE [dbo].[IR_DETAILS1]
AS
BEGIN
    SET NOCOUNT ON;

    SELECT TOP (10000)
        T.*,
        E.IMAGE
    FROM
    (
        SELECT DISTINCT 
            A.IRKEY,
            A.NAME,
            A.FATHER_NAME,
            A.PRESENT_ADDRESS,
            A.PERMANENT_ADDRESS,
            A.MOBILE,
            A.AADHAR_NO,
            B.CRIME_NO,
            B.YEAR,
            B.SEC_OF_LAW,
            B.POLICE_STATION,
            B.DATE_OF_ARREST,
            COUNT(C.IRKEY) AS PREVIOUS_OFFENCE_COUNT ,COUNT(D.IRKEY) AS ASSOCIATES_COUNT
        FROM IR_PARTICULARS A
        LEFT JOIN OFFENCE_DETAILS B
            ON A.IRKEY = B.IRKEY
        LEFT JOIN PREVIOUS_OFFENCE_DETAILS C
            ON A.IRKEY = C.IRKEY
        LEFT JOIN RELATIONSHIP_WITH_OTHER_ASSOCIATES D
            ON A.IRKEY = D.IRKEY
        GROUP BY
            A.IRKEY,
            A.NAME,
            A.FATHER_NAME,
            A.PRESENT_ADDRESS,
            A.PERMANENT_ADDRESS,
            A.MOBILE,
            A.AADHAR_NO,
            B.CRIME_NO,
            B.YEAR,
            B.SEC_OF_LAW,
            B.POLICE_STATION,
            B.DATE_OF_ARREST
    ) AS T
    LEFT JOIN IMAGE_TABLE E
        ON T.IRKEY = E.IRKEY
    ORDER BY T.IRKEY;
END;

GO
USE [master]
GO
ALTER DATABASE [FORMS] SET  READ_WRITE 
GO
