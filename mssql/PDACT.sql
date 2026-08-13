USE [master]
GO
/****** Object:  Database [PDACT]    Script Date: 13-Aug-26 6:03:50 PM ******/
CREATE DATABASE [PDACT]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'PDACT', FILENAME = N'D:\SQL SOFTWARE 2016 INSTALLATION\MSSQL13.DAU_HYD_2023\MSSQL\DATA\PDACT.mdf' , SIZE = 45056KB , MAXSIZE = UNLIMITED, FILEGROWTH = 1024KB )
 LOG ON 
( NAME = N'PDACT_log', FILENAME = N'D:\SQL SOFTWARE 2016 INSTALLATION\MSSQL13.DAU_HYD_2023\MSSQL\DATA\PDACT_log.ldf' , SIZE = 2048KB , MAXSIZE = UNLIMITED, FILEGROWTH = 10%)
GO
ALTER DATABASE [PDACT] SET COMPATIBILITY_LEVEL = 120
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [PDACT].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [PDACT] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [PDACT] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [PDACT] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [PDACT] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [PDACT] SET ARITHABORT OFF 
GO
ALTER DATABASE [PDACT] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [PDACT] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [PDACT] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [PDACT] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [PDACT] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [PDACT] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [PDACT] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [PDACT] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [PDACT] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [PDACT] SET  DISABLE_BROKER 
GO
ALTER DATABASE [PDACT] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [PDACT] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [PDACT] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [PDACT] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [PDACT] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [PDACT] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [PDACT] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [PDACT] SET RECOVERY FULL 
GO
ALTER DATABASE [PDACT] SET  MULTI_USER 
GO
ALTER DATABASE [PDACT] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [PDACT] SET DB_CHAINING OFF 
GO
ALTER DATABASE [PDACT] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [PDACT] SET TARGET_RECOVERY_TIME = 0 SECONDS 
GO
ALTER DATABASE [PDACT] SET DELAYED_DURABILITY = DISABLED 
GO
EXEC sys.sp_db_vardecimal_storage_format N'PDACT', N'ON'
GO
USE [PDACT]
GO
/****** Object:  UserDefinedFunction [dbo].[GetPercentageOfTwoStringMatching]    Script Date: 13-Aug-26 6:03:51 PM ******/
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
/****** Object:  UserDefinedFunction [dbo].[LEVENSHTEIN]    Script Date: 13-Aug-26 6:03:51 PM ******/
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
/****** Object:  Table [dbo].[111]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[111](
	[Year] [varchar](8000) NULL,
	[File Number] [varchar](8000) NULL,
	[Column 2] [varchar](8000) NULL,
	[Column 3] [varchar](8000) NULL,
	[Seat] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[FATHERNAME] [varchar](8000) NULL,
	[Address] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[222]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[222](
	[Year] [varchar](5000) NULL,
	[File Number] [varchar](5000) NULL,
	[Column 2] [varchar](5000) NULL,
	[Column 3] [varchar](5000) NULL,
	[Seat] [varchar](5000) NULL,
	[NAME] [varchar](5000) NULL,
	[FATHERNAME] [varchar](5000) NULL,
	[Address] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[a]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[a](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [int] NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[AB]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[AB](
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

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[B_PDACT_FROM_IR_ADDRESS]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[B_PDACT_FROM_IR_ADDRESS](
	[PDACT_KEY] [numeric](18, 0) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [int] NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL,
	[IR_PRESENT_ADDRESS] [varchar](1000) NULL,
	[IR_PERMANENT_ADDRESS] [varchar](1000) NULL,
	[IR_PHONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[D]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[D](
	[PDACT_KEY] [numeric](18, 0) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [int] NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL,
	[IR_PRESENT_ADDRESS] [varchar](1000) NULL,
	[IR_PERMANENT_ADDRESS] [varchar](1000) NULL,
	[IR_PHONE] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[JAHA]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[JAHA](
	[irkey] [varchar](20) NULL,
	[count_ir] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[jahangir_pdact]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[jahangir_pdact](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [int] NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NBWS_COUNT]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NBWS_COUNT](
	[IRKEY] [varchar](5000) NULL,
	[SLNO ] [varchar](5000) NULL,
	[COUNT] [varchar](5000) NULL,
	[NAME] [varchar](5000) NULL,
	[ALIAS_NAME] [varchar](5000) NULL,
	[FATHER_NAME] [varchar](5000) NULL,
	[AGE] [varchar](5000) NULL,
	[PRESENT_ADDRESS] [varchar](5000) NULL,
	[ARRESTED_IN_CRIMEHEAD] [varchar](5000) NULL,
	[MO] [varchar](5000) NULL,
	[CRIME_NO] [varchar](5000) NULL,
	[YEAR] [varchar](5000) NULL,
	[SEC_OF_LAW] [varchar](5000) NULL,
	[POLICE_STATION] [varchar](5000) NULL,
	[count1] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NBWS_JANUARY]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NBWS_JANUARY](
	[SLNO] [varchar](5000) NULL,
	[POLICE_STATION] [varchar](5000) NULL,
	[NAME_WARRENTEE] [varchar](5000) NULL,
	[ADD_TRUE_OR_FALSE] [varchar](5000) NULL,
	[DATE_OF_NBW] [varchar](5000) NULL,
	[CRIME_NO] [varchar](5000) NULL,
	[MO] [varchar](5000) NULL,
	[COURT_NAME] [varchar](5000) NULL,
	[CC_SC_NO] [varchar](5000) NULL,
	[ORG_ARR] [varchar](5000) NULL,
	[RELEASED_ON_BAIL_OR_NOT] [varchar](5000) NULL,
	[SURETIES_ADD] [varchar](5000) NULL,
	[ACTION_AGAINST_SURETIES] [varchar](5000) NULL,
	[REASON_FOR_PENDING] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[not_found22]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[not_found22](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[CRIME_HEAD_SEARCH] [date] NULL,
	[DIVISION] [varchar](50) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[NOT_IN_PDACT_20]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[NOT_IN_PDACT_20](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL,
	[DIVISION] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[notin_data24]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[notin_data24](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL,
	[DIVISION] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACELL]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACELL](
	[Sl No ] [varchar](8000) NULL,
	[Year] [varchar](8000) NULL,
	[File Number] [varchar](8000) NULL,
	[Seat] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[FATHERNAME ] [varchar](8000) NULL,
	[Address] [varchar](8000) NULL,
	[Category as per Act] [varchar](8000) NULL,
	[Specific Category ] [varchar](8000) NULL,
	[Sub category] [varchar](8000) NULL,
	[Police Station] [varchar](8000) NULL,
	[Zone] [varchar](8000) NULL,
	[Year of Orders issued] [varchar](8000) NULL,
	[Orders issued on] [varchar](8000) NULL,
	[Whether order served] [varchar](8000) NULL,
	[Year of Detention] [varchar](8000) NULL,
	[Date of detention] [varchar](8000) NULL,
	[Detenu No ] [varchar](8000) NULL,
	[PROCLAMATION] [varchar](8000) NULL,
	[Approval_ODER] [varchar](8000) NULL,
	[Revocation Orders ] [varchar](8000) NULL,
	[Confirmation orders ] [varchar](8000) NULL,
	[DATE Confirmation Orders] [varchar](8000) NULL,
	[released ] [varchar](8000) NULL,
	[No of Times detained ] [varchar](8000) NULL,
	[District] [varchar](8000) NULL,
	[Nativity] [varchar](8000) NULL,
	[Religion] [varchar](8000) NULL,
	[Column 28] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdact_check_with_pdcell]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdact_check_with_pdcell](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL,
	[DIVISION] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_FOR_APP]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_FOR_APP](
	[SLNO] [varchar](8000) NULL,
	[PDACT_PHOTO_ID] [varchar](8000) NULL,
	[PDACT_CALL_KEY] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[DATE_DETENTION] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[ORDER_DATE] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[FILE_NO] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[AGE] [varchar](8000) NULL,
	[FATHERNAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[LATITUDE1] [varchar](8000) NULL,
	[LONGITUDE1] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[LATITUDE2] [varchar](8000) NULL,
	[LONGITUDE2] [varchar](8000) NULL,
	[PHONE_NO] [varchar](8000) NULL,
	[ID_PROOF] [varchar](8000) NULL,
	[ID_PROOF_NO] [varchar](8000) NULL,
	[PRESENT ACTIVITY] [varchar](8000) NULL,
	[PHOTO_ID] [varchar](8000) NULL,
	[REMARKS] [varchar](8000) NULL,
	[PS TRANSFER] [varchar](8000) NULL,
	[COUNT] [varchar](8000) NULL,
	[JAIL_RELEASE_DATE] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_FROM_SERVER]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_FROM_SERVER](
	[PDACT_KEY] [varchar](5000) NULL,
	[PDACT_CALL_KEY] [varchar](5000) NULL,
	[Name] [varchar](5000) NULL,
	[Father_Name] [varchar](5000) NULL,
	[Age] [varchar](5000) NULL,
	[Occupation] [varchar](5000) NULL,
	[Caste] [varchar](5000) NULL,
	[Id_Proof] [varchar](5000) NULL,
	[Id_Proof_No] [varchar](5000) NULL,
	[Phone_No] [varchar](5000) NULL,
	[Irkey] [varchar](5000) NULL,
	[Present_Address] [varchar](5000) NULL,
	[Permanent_Address] [varchar](5000) NULL,
	[State] [varchar](5000) NULL,
	[PD_ACT_PS] [varchar](5000) NULL,
	[Zone] [varchar](5000) NULL,
	[File_no] [varchar](5000) NULL,
	[Detenu_No] [varchar](5000) NULL,
	[Order_Issued_On] [varchar](5000) NULL,
	[Crime_Head] [varchar](5000) NULL,
	[Minor_Head] [varchar](5000) NULL,
	[ModusOperendi] [varchar](5000) NULL,
	[Police_Station] [varchar](5000) NULL,
	[Date_Of_Arrest] [varchar](5000) NULL,
	[Date_Of_ReleaSE] [varchar](5000) NULL,
	[Column 25] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_JAHAGIR]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_JAHAGIR](
	[SLNO] [varchar](8000) NULL,
	[PDACT_PHOTO_ID] [varchar](8000) NULL,
	[photos found or not] [varchar](8000) NULL,
	[PDACT_CALL_KEY] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[DATE_DETENTION] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[ORDER_DATE] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[FILE_NO] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[AGE] [varchar](8000) NULL,
	[FATHERNAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[LATITUDE1] [varchar](8000) NULL,
	[LONGITUDE1] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[LATITUDE2] [varchar](8000) NULL,
	[LONGITUDE2] [varchar](8000) NULL,
	[PHONE_NO] [varchar](8000) NULL,
	[ID_PROOF] [varchar](8000) NULL,
	[ID_PROOF_NO] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[DIVISION] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[Category as per Act] [varchar](8000) NULL,
	[Specific Category] [varchar](8000) NULL,
	[Sub category] [varchar](8000) NULL,
	[ORDER_SERVED_YEAR] [varchar](8000) NULL,
	[ORDER_SERVED] [varchar](8000) NULL,
	[YEAR_OF_DETENTION] [varchar](8000) NULL,
	[DETENUE_NO] [varchar](8000) NULL,
	[PROCLAMATION_ORDERS_DATE] [varchar](8000) NULL,
	[APPROVAL_ORDER_NO_DATE] [varchar](8000) NULL,
	[REVOCATION_NO_DATE] [varchar](8000) NULL,
	[GO_RT_NO] [varchar](8000) NULL,
	[GO_RT_NO_DATE] [varchar](8000) NULL,
	[COUNT1] [varchar](8000) NULL,
	[DISTRICT] [varchar](8000) NULL,
	[NATIVITY] [varchar](8000) NULL,
	[RELIGION] [varchar](8000) NULL,
	[Column 42] [varchar](8000) NULL,
	[Column 43] [varchar](8000) NULL,
	[Column 44] [varchar](8000) NULL,
	[Column 45] [varchar](8000) NULL,
	[Column 46] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_MAIN_TABLE]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_MAIN_TABLE](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL,
	[DIVISION] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_MAIN_TABLE_WITH_CALL_KEYS]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_MAIN_TABLE_WITH_CALL_KEYS](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdact_main_table1]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdact_main_table1](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[CRIME_HEAD_SEARCH] [varchar](50) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdact_ndps_2014_2021]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdact_ndps_2014_2021](
	[SlNo ] [varchar](8000) NULL,
	[Name] [varchar](100) NULL,
	[Address] [varchar](8000) NULL,
	[Occupation] [varchar](8000) NULL,
	[Category_as_per_Act] [varchar](8000) NULL,
	[Police_Station] [varchar](8000) NULL,
	[Zone] [varchar](8000) NULL,
	[Orders_issued_on] [varchar](8000) NULL,
	[Date_of_detention] [varchar](8000) NULL,
	[Detenu_No ] [varchar](8000) NULL,
	[Cases_as_per_pdact] [varchar](8000) NULL,
	[Type_of_Drug] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_NDPS_NORMALISED_WITH_NAME]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_NDPS_NORMALISED_WITH_NAME](
	[SlNo ] [varchar](8000) NULL,
	[Name] [varchar](8000) NULL,
	[Address] [varchar](8000) NULL,
	[Occupation] [varchar](8000) NULL,
	[Category_as_per_Act] [varchar](8000) NULL,
	[Police_Station] [varchar](8000) NULL,
	[Zone] [varchar](8000) NULL,
	[Orders_issued_on] [varchar](8000) NULL,
	[Date_of_detention] [varchar](8000) NULL,
	[Detenu_No ] [varchar](8000) NULL,
	[Cases_as_per_pdact] [varchar](8000) NULL,
	[Type_of_Drug] [varchar](8000) NULL,
	[NAME1] [varchar](100) NULL,
	[FATHER_NAME] [varchar](100) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_NOT IN IRS]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_NOT IN IRS](
	[PDACT_KEY] [varchar](3000) NULL,
	[PDACT_CALL_KEY] [varchar](3000) NULL,
	[Name] [varchar](3000) NULL,
	[Father_Name] [varchar](3000) NULL,
	[Age] [varchar](3000) NULL,
	[Occupation] [varchar](3000) NULL,
	[Phone_No] [varchar](3000) NULL,
	[Present_Address] [varchar](3000) NULL,
	[Permanent_Address] [varchar](3000) NULL,
	[District] [varchar](3000) NULL,
	[State] [varchar](3000) NULL,
	[PD_ACT_PS] [varchar](3000) NULL,
	[Zone] [varchar](3000) NULL,
	[File_no] [varchar](3000) NULL,
	[File_No_Year] [varchar](3000) NULL,
	[Detenu_No] [varchar](3000) NULL,
	[Order_Issued_On] [varchar](3000) NULL,
	[Approval_Orders_No] [varchar](3000) NULL,
	[Confirmation_Revocation_Orders] [varchar](3000) NULL,
	[Crime_Head] [varchar](3000) NULL,
	[Minor_Head] [varchar](3000) NULL,
	[ModusOperendi] [varchar](3000) NULL,
	[Police_Station] [varchar](3000) NULL,
	[Crime_No] [varchar](3000) NULL,
	[Year] [varchar](3000) NULL,
	[Sec_Of_Law] [varchar](3000) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](3000) NULL,
	[Name_Of_Units] [varchar](3000) NULL,
	[No_Of_Cases] [varchar](3000) NULL,
	[Date_Of_Arrest] [varchar](3000) NULL,
	[Date_Of_Release] [varchar](3000) NULL,
	[CRIME_HEAD_SEARCH] [varchar](3000) NULL,
	[DIVISION] [varchar](3000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_PDCELL_RAW_DATA]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_PDCELL_RAW_DATA](
	[S_NO] [varchar](50) NULL,
	[Year] [varchar](50) NULL,
	[File_Number] [varchar](50) NULL,
	[Seat] [varchar](50) NULL,
	[NAME_OF_DETENUE] [varchar](8000) NULL,
	[Address] [varchar](8000) NULL,
	[Category_AS_PER_Act] [varchar](8000) NULL,
	[Specific_Category ] [varchar](8000) NULL,
	[Police_Station] [varchar](8000) NULL,
	[Zone] [varchar](8000) NULL,
	[Orders_issued_on] [varchar](8000) NULL,
	[Whether_order_served] [varchar](8000) NULL,
	[Date_of_detention] [varchar](8000) NULL,
	[Detenu_No ] [varchar](8000) NULL,
	[Proclamation_orders_issued_on] [varchar](8000) NULL,
	[Approval_orders_No] [varchar](8000) NULL,
	[Revocation_Orders] [varchar](8000) NULL,
	[Confirmation_orders] [varchar](8000) NULL,
	[Date_of_Confirmation_Orders] [varchar](8000) NULL,
	[WP_filed_in_HighCourt] [varchar](8000) NULL,
	[Court_judgment_on] [varchar](8000) NULL,
	[Date_of_Judgment] [varchar](8000) NULL,
	[released_DATE] [varchar](8000) NULL,
	[No_of_Times_detained ] [varchar](8000) NULL,
	[District] [varchar](8000) NULL,
	[Nativity] [varchar](8000) NULL,
	[Religion] [varchar](8000) NULL,
	[Column 27] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdact_practice]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdact_practice](
	[PDACT_KEY] [numeric](18, 0) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[count_ir] [int] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_PRESS_NOTES_TABLE]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_PRESS_NOTES_TABLE](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](8000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL,
	[INCHARGE_OFFICER] [varchar](5000) NULL,
	[HANDED_OVER_PS] [varchar](500) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdact_previous_match]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdact_previous_match](
	[pdact_key] [numeric](18, 0) NOT NULL,
	[confessed_crime_no] [varchar](100) NULL,
	[confessed_year] [varchar](100) NULL,
	[confessed_sec_of_law] [varchar](500) NULL,
	[police_station] [varchar](50) NULL,
	[confessed_doa] [date] NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdact_rough_table]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdact_rough_table](
	[PDACT_KEY] [numeric](18, 0) IDENTITY(1,1) NOT NULL,
	[PDACT_CALL_KEY] [varchar](20) NULL,
	[Name] [varchar](100) NULL,
	[Father_Name] [varchar](50) NOT NULL,
	[Age] [varchar](10) NULL,
	[Dob] [date] NULL,
	[Occupation] [varchar](50) NULL,
	[Caste] [varchar](50) NULL,
	[Id_Proof] [varchar](50) NULL,
	[Id_Proof_No] [varchar](50) NULL,
	[Phone_No] [varchar](50) NULL,
	[Irkey] [varchar](20) NULL,
	[Present_Address] [varchar](1000) NULL,
	[Permanent_Address] [varchar](1000) NULL,
	[District] [varchar](50) NULL,
	[State] [varchar](50) NULL,
	[PD_ACT_PS] [varchar](50) NULL,
	[Zone] [varchar](50) NULL,
	[File_no] [varchar](50) NULL,
	[File_No_Year] [varchar](50) NULL,
	[Detenu_No] [varchar](50) NULL,
	[Order_Issued_On] [date] NULL,
	[Approval_Orders_No] [varchar](500) NULL,
	[Confirmation_Revocation_Orders] [varchar](500) NULL,
	[Crime_Head] [varchar](50) NULL,
	[Minor_Head] [varchar](50) NULL,
	[ModusOperendi] [varchar](500) NULL,
	[Police_Station] [varchar](50) NULL,
	[Crime_No] [varchar](100) NULL,
	[Year] [varchar](20) NULL,
	[Sec_Of_Law] [varchar](250) NULL,
	[Whether_Involved_In_Other_Unit_Cases] [varchar](250) NULL,
	[Name_Of_Units] [varchar](500) NULL,
	[No_Of_Cases] [varchar](50) NULL,
	[Date_Of_Arrest] [date] NULL,
	[Date_Of_Release] [date] NULL,
	[Brief_Facts] [varchar](3000) NULL,
	[ASONDATE] [datetime] NULL,
	[image] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_TOTAL_2_AND_ABOVE_AS_ON_SEP]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_TOTAL_2_AND_ABOVE_AS_ON_SEP](
	[SLNO1] [varchar](8000) NULL,
	[SLNO] [varchar](8000) NULL,
	[PDACT_PHOTO_ID] [varchar](8000) NULL,
	[PDACT_CALL_KEY] [varchar](8000) NULL,
	[COUNT1] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[Category as per Act] [varchar](8000) NULL,
	[Specific Category] [varchar](8000) NULL,
	[Sub category] [varchar](8000) NULL,
	[ORDER_SERVED_YEAR] [varchar](8000) NULL,
	[ORDER_SERVED] [varchar](8000) NULL,
	[YEAR_OF_DETENTION] [varchar](8000) NULL,
	[DETENUE_NO] [varchar](8000) NULL,
	[PROCLAMATION_ORDERS_DATE] [varchar](8000) NULL,
	[APPROVAL_ORDER_NO_DATE] [varchar](8000) NULL,
	[REVOCATION_NO_DATE] [varchar](8000) NULL,
	[GO_RT_NO] [varchar](8000) NULL,
	[GO_RT_NO_DATE] [varchar](8000) NULL,
	[COUNT] [varchar](8000) NULL,
	[DISTRICT] [varchar](8000) NULL,
	[NATIVITY] [varchar](8000) NULL,
	[RELIGION] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_TOTAL_AS_ON_december2019_NEW]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_TOTAL_AS_ON_december2019_NEW](
	[SLNO] [varchar](8000) NULL,
	[PDACT_PHOTO_ID] [varchar](8000) NULL,
	[PDACT_CALL_KEY] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[DATE_DETENTION] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[ORDER_DATE] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[FILE_NO] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[AGE] [varchar](8000) NULL,
	[FATHERNAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[LATITUDE1] [varchar](8000) NULL,
	[LONGITUDE1] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[LATITUDE2] [varchar](8000) NULL,
	[LONGITUDE2] [varchar](8000) NULL,
	[PHONE_NO] [varchar](8000) NULL,
	[ID_PROOF] [varchar](8000) NULL,
	[ID_PROOF_NO] [varchar](8000) NULL,
	[PRESENT ACTIVITY] [varchar](8000) NULL,
	[PHOTO_ID] [varchar](8000) NULL,
	[REMARKS] [varchar](8000) NULL,
	[PS TRANSFER] [varchar](8000) NULL,
	[COUNT] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[DIVISION] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[Category as per Act] [varchar](8000) NULL,
	[Specific Category] [varchar](8000) NULL,
	[Sub category] [varchar](8000) NULL,
	[ORDER_SERVED_YEAR] [varchar](8000) NULL,
	[ORDER_SERVED] [varchar](8000) NULL,
	[YEAR_OF_DETENTION] [varchar](8000) NULL,
	[DETENUE_NO] [varchar](8000) NULL,
	[PROCLAMATION_ORDERS_DATE] [varchar](8000) NULL,
	[APPROVAL_ORDER_NO_DATE] [varchar](8000) NULL,
	[REVOCATION_NO_DATE] [varchar](8000) NULL,
	[GO_RT_NO] [varchar](8000) NULL,
	[GO_RT_NO_DATE] [varchar](8000) NULL,
	[COUNT1] [varchar](8000) NULL,
	[DISTRICT] [varchar](8000) NULL,
	[NATIVITY] [varchar](8000) NULL,
	[RELIGION] [varchar](8000) NULL,
	[Column 46] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_TOTAL_AS_ON_december20192]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_TOTAL_AS_ON_december20192](
	[SLNO] [varchar](8000) NULL,
	[PDACT_PHOTO_ID] [varchar](8000) NULL,
	[PDACT_CALL_KEY] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[DATE_DETENTION] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[ORDER_DATE] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[FILE_NO] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[AGE] [varchar](8000) NULL,
	[FATHERNAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[LATITUDE1] [varchar](8000) NULL,
	[LONGITUDE1] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[LATITUDE2] [varchar](8000) NULL,
	[LONGITUDE2] [varchar](8000) NULL,
	[PHONE_NO] [varchar](8000) NULL,
	[ID_PROOF] [varchar](8000) NULL,
	[ID_PROOF_NO] [varchar](8000) NULL,
	[PRESENT ACTIVITY] [varchar](8000) NULL,
	[PHOTO_ID] [varchar](8000) NULL,
	[REMARKS] [varchar](8000) NULL,
	[PS TRANSFER] [varchar](8000) NULL,
	[COUNT] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[DIVISION] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[Category as per Act] [varchar](8000) NULL,
	[Specific Category] [varchar](8000) NULL,
	[Sub category] [varchar](8000) NULL,
	[ORDER_SERVED_YEAR] [varchar](8000) NULL,
	[ORDER_SERVED] [varchar](8000) NULL,
	[YEAR_OF_DETENTION] [varchar](8000) NULL,
	[DETENUE_NO] [varchar](8000) NULL,
	[PROCLAMATION_ORDERS_DATE] [varchar](8000) NULL,
	[APPROVAL_ORDER_NO_DATE] [varchar](8000) NULL,
	[REVOCATION_NO_DATE] [varchar](8000) NULL,
	[GO_RT_NO] [varchar](8000) NULL,
	[GO_RT_NO_DATE] [varchar](8000) NULL,
	[COUNT1] [varchar](8000) NULL,
	[DISTRICT] [varchar](8000) NULL,
	[NATIVITY] [varchar](8000) NULL,
	[RELIGION] [varchar](8000) NULL,
	[Column 46] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_TOTAL_AS_ON_SEPTEMBER]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_TOTAL_AS_ON_SEPTEMBER](
	[SLNO] [varchar](8000) NULL,
	[PDACT_PHOTO_ID] [varchar](8000) NULL,
	[PDACT_CALL_KEY] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[DATE_DETENTION] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[ORDER_DATE] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[FILE_NO] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[AGE] [varchar](8000) NULL,
	[FATHERNAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[LATITUDE1] [varchar](8000) NULL,
	[LONGITUDE1] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[LATITUDE2] [varchar](8000) NULL,
	[LONGITUDE2] [varchar](8000) NULL,
	[PHONE_NO] [varchar](8000) NULL,
	[ID_PROOF] [varchar](8000) NULL,
	[ID_PROOF_NO] [varchar](8000) NULL,
	[PRESENT ACTIVITY] [varchar](8000) NULL,
	[PHOTO_ID] [varchar](8000) NULL,
	[REMARKS] [varchar](8000) NULL,
	[PS TRANSFER] [varchar](8000) NULL,
	[COUNT] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[DIVISION] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[Category as per Act] [varchar](8000) NULL,
	[Specific Category] [varchar](8000) NULL,
	[Sub category] [varchar](8000) NULL,
	[ORDER_SERVED_YEAR] [varchar](8000) NULL,
	[ORDER_SERVED] [varchar](8000) NULL,
	[YEAR_OF_DETENTION] [varchar](8000) NULL,
	[DETENUE_NO] [varchar](8000) NULL,
	[PROCLAMATION_ORDERS_DATE] [varchar](8000) NULL,
	[APPROVAL_ORDER_NO_DATE] [varchar](8000) NULL,
	[REVOCATION_NO_DATE] [varchar](8000) NULL,
	[GO_RT_NO] [varchar](8000) NULL,
	[GO_RT_NO_DATE] [varchar](8000) NULL,
	[COUNT1] [varchar](8000) NULL,
	[DISTRICT] [varchar](8000) NULL,
	[NATIVITY] [varchar](8000) NULL,
	[RELIGION] [varchar](8000) NULL,
	[Column 46] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[PDACT_TOTAL_pdacell_as_on_2019dec]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[PDACT_TOTAL_pdacell_as_on_2019dec](
	[SLNO] [varchar](8000) NULL,
	[PDACT_PHOTO_ID] [varchar](8000) NULL,
	[PDACT_CALL_KEY] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[DATE_DETENTION] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[ORDER_DATE] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[FILE_NO] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[AGE] [varchar](8000) NULL,
	[FATHERNAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[LATITUDE1] [varchar](8000) NULL,
	[LONGITUDE1] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[LATITUDE2] [varchar](8000) NULL,
	[LONGITUDE2] [varchar](8000) NULL,
	[PHONE_NO] [varchar](8000) NULL,
	[ID_PROOF] [varchar](8000) NULL,
	[ID_PROOF_NO] [varchar](8000) NULL,
	[PRESENT ACTIVITY] [varchar](8000) NULL,
	[PHOTO_ID] [varchar](8000) NULL,
	[REMARKS] [varchar](8000) NULL,
	[PS TRANSFER] [varchar](8000) NULL,
	[COUNT1] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[DIVISION] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[Category as per Act] [varchar](8000) NULL,
	[Specific Category] [varchar](8000) NULL,
	[Sub category] [varchar](8000) NULL,
	[ORDER_SERVED_YEAR] [varchar](8000) NULL,
	[ORDER_SERVED] [varchar](8000) NULL,
	[YEAR_OF_DETENTION] [varchar](8000) NULL,
	[DETENUE_NO] [varchar](8000) NULL,
	[PROCLAMATION_ORDERS_DATE] [varchar](8000) NULL,
	[APPROVAL_ORDER_NO_DATE] [varchar](8000) NULL,
	[REVOCATION_NO_DATE] [varchar](8000) NULL,
	[GO_RT_NO] [varchar](8000) NULL,
	[GO_RT_NO_DATE] [varchar](8000) NULL,
	[COUNT] [varchar](8000) NULL,
	[DISTRICT] [varchar](8000) NULL,
	[NATIVITY] [varchar](8000) NULL,
	[RELIGION] [varchar](8000) NULL,
	[Column 46] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdact_with_photos_keys]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdact_with_photos_keys](
	[pdact_key] [varchar](8000) NULL,
	[name] [varchar](8000) NULL,
	[cc no] [varchar](8000) NULL,
	[Column 3] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[pdcell_pdact_data1]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[pdcell_pdact_data1](
	[YEAR ] [varchar](8000) NULL,
	[FILE_NO] [varchar](8000) NULL,
	[SEAT] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[FATHER_NAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[CATERGORY_AS_PER ACT] [varchar](8000) NULL,
	[SPECIAL_CATEGORY] [varchar](8000) NULL,
	[SUB_CATEGORY] [varchar](8000) NULL,
	[POLICE_STATION] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[YEAR_OF__ ORDER_ ISSUED] [varchar](8000) NULL,
	[ORDERS_ISSUED_ON] [varchar](8000) NULL,
	[WHETHER_ORDER_SERVRED] [varchar](8000) NULL,
	[YEAR_OF_DETENTION] [varchar](8000) NULL,
	[DATE_OF_DETENTION] [varchar](8000) NULL,
	[DETENUE_NO] [varchar](8000) NULL,
	[PROCLAMATION_ORDER] [varchar](8000) NULL,
	[APPROVAL_ORDER] [varchar](8000) NULL,
	[REVOCATION_ORDER] [varchar](8000) NULL,
	[CONFORMATION_ORDER_VIDE_ GO R No ] [varchar](8000) NULL,
	[DATE_OF_CONFORMATIONS_ORDERS] [varchar](8000) NULL,
	[DATE_OF_RELEASE] [varchar](8000) NULL,
	[NO_OF_TIMES_DETAINED ] [varchar](8000) NULL,
	[DISTRICT] [varchar](8000) NULL,
	[NATIVITY] [varchar](8000) NULL,
	[RELIGION] [varchar](8000) NULL,
	[REMARKS] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[Query]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Query](
	[PDACT_KEY] [float] NULL,
	[PDACT_CALL_KEY] [nvarchar](255) NULL,
	[Name] [nvarchar](255) NULL,
	[Father_Name] [nvarchar](255) NULL,
	[Age] [nvarchar](255) NULL,
	[Dob] [nvarchar](255) NULL,
	[Occupation] [nvarchar](255) NULL,
	[Caste] [nvarchar](255) NULL,
	[Id_Proof] [nvarchar](255) NULL,
	[Id_Proof_No] [nvarchar](255) NULL,
	[Phone_No] [nvarchar](255) NULL,
	[Irkey] [nvarchar](255) NULL,
	[Present_Address] [nvarchar](255) NULL,
	[Permanent_Address] [nvarchar](255) NULL,
	[District] [nvarchar](255) NULL,
	[State] [nvarchar](255) NULL,
	[PD_ACT_PS] [nvarchar](255) NULL,
	[Zone] [nvarchar](255) NULL,
	[File_no] [nvarchar](255) NULL,
	[File_No_Year] [nvarchar](255) NULL,
	[Detenu_No] [nvarchar](255) NULL,
	[Order_Issued_On] [nvarchar](255) NULL,
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
	[No_Of_Cases] [nvarchar](255) NULL,
	[Date_Of_Arrest] [nvarchar](255) NULL,
	[Date_Of_Release] [nvarchar](255) NULL,
	[Brief_Facts] [nvarchar](255) NULL,
	[ASONDATE] [datetime] NULL,
	[IMAGE] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[rowdy_pdact _20]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[rowdy_pdact _20](
	[Rowdy_sheeter_id] [varchar](5000) NULL,
	[Irkey] [varchar](5000) NULL,
	[PDAct_Key] [varchar](5000) NULL,
	[Latest Arreste Date] [varchar](5000) NULL,
	[Name of the Ps] [varchar](5000) NULL,
	[Date of opening of Rowdy sheet  DD MM YY] [varchar](5000) NULL,
	[Rowdy sheet open Year] [varchar](5000) NULL,
	[Name @ Alias name ] [varchar](5000) NULL,
	[Age] [varchar](5000) NULL,
	[Father Name ] [varchar](5000) NULL,
	[Present address ] [varchar](5000) NULL,
	[Latitude ] [varchar](5000) NULL,
	[Langitude ] [varchar](5000) NULL,
	[Permanent address] [varchar](5000) NULL,
	[Lat] [varchar](5000) NULL,
	[Lan] [varchar](5000) NULL,
	[Phone number ] [varchar](5000) NULL,
	[ID proof Type] [varchar](5000) NULL,
	[ID_No] [varchar](5000) NULL,
	[Communal  Non Communal ] [varchar](5000) NULL,
	[Active In Active] [varchar](5000) NULL,
	[Latest Bind over date] [varchar](5000) NULL,
	[Year] [varchar](5000) NULL,
	[Present Activity ] [varchar](5000) NULL,
	[Photo (Soft copy)_ID] [varchar](5000) NULL,
	[Remarks ] [varchar](5000) NULL,
	[PS Transfer Status] [varchar](5000) NULL,
	[Count of involved  cases] [varchar](5000) NULL,
	[Column 28] [varchar](5000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[WITH_AGE]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[WITH_AGE](
	[SLNO] [varchar](8000) NULL,
	[PDACT_PHOTO_ID] [varchar](8000) NULL,
	[photos found or not] [varchar](8000) NULL,
	[PDACT_CALL_KEY] [varchar](8000) NULL,
	[IRKEY] [varchar](8000) NULL,
	[DATE_DETENTION] [varchar](8000) NULL,
	[PS] [varchar](8000) NULL,
	[ORDER_DATE] [varchar](8000) NULL,
	[YEAR] [varchar](8000) NULL,
	[FILE_NO] [varchar](8000) NULL,
	[NAME] [varchar](8000) NULL,
	[AGE] [varchar](8000) NULL,
	[FATHERNAME] [varchar](8000) NULL,
	[ADDRESS] [varchar](8000) NULL,
	[LATITUDE1] [varchar](8000) NULL,
	[LONGITUDE1] [varchar](8000) NULL,
	[PERMANENT_ADDRESS] [varchar](8000) NULL,
	[LATITUDE2] [varchar](8000) NULL,
	[LONGITUDE2] [varchar](8000) NULL,
	[PHONE_NO] [varchar](8000) NULL,
	[ID_PROOF] [varchar](8000) NULL,
	[ID_PROOF_NO] [varchar](8000) NULL,
	[RELEASE_DATE] [varchar](8000) NULL,
	[MO] [varchar](8000) NULL,
	[DIVISION] [varchar](8000) NULL,
	[ZONE] [varchar](8000) NULL,
	[Category as per Act] [varchar](8000) NULL,
	[Specific Category] [varchar](8000) NULL,
	[Sub category] [varchar](8000) NULL,
	[ORDER_SERVED_YEAR] [varchar](8000) NULL,
	[ORDER_SERVED] [varchar](8000) NULL,
	[YEAR_OF_DETENTION] [varchar](8000) NULL,
	[DETENUE_NO] [varchar](8000) NULL,
	[PROCLAMATION_ORDERS_DATE] [varchar](8000) NULL,
	[APPROVAL_ORDER_NO_DATE] [varchar](8000) NULL,
	[REVOCATION_NO_DATE] [varchar](8000) NULL,
	[GO_RT_NO] [varchar](8000) NULL,
	[GO_RT_NO_DATE] [varchar](8000) NULL,
	[COUNT1] [varchar](8000) NULL,
	[DISTRICT] [varchar](8000) NULL,
	[NATIVITY] [varchar](8000) NULL,
	[RELIGION] [varchar](8000) NULL
) ON [PRIMARY]

GO
SET ANSI_PADDING OFF
GO
/****** Object:  StoredProcedure [dbo].[usp_ExportImage]    Script Date: 13-Aug-26 6:03:51 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROCEDURE [dbo].[usp_ExportImage] (
   @PicName NVARCHAR (100)
   ,@ImageFolderPath NVARCHAR(1000)
   ,@Filename NVARCHAR(1000)
   )
AS
BEGIN
   DECLARE @ImageData VARBINARY (max);
   DECLARE @Path2OutFile NVARCHAR (2000);
   DECLARE @Obj INT
 
   SET NOCOUNT ON
 
   SELECT @ImageData = (
         SELECT convert (VARBINARY (max), image, 1)
         FROM irforms..IMAGE_TABLE
         WHERE irkey = @PicName
         );
 
   SET @Path2OutFile = CONCAT (
         @ImageFolderPath
         ,'\'
         , @Filename
         );
    BEGIN TRY
     EXEC sp_OACreate 'ADODB.Stream' ,@Obj OUTPUT;
     EXEC sp_OASetProperty @Obj ,'Type',1;
     EXEC sp_OAMethod @Obj,'Open';
     EXEC sp_OAMethod @Obj,'Write', NULL, @ImageData;
     EXEC sp_OAMethod @Obj,'SaveToFile', NULL, @Path2OutFile, 2;
     EXEC sp_OAMethod @Obj,'Close';
     EXEC sp_OADestroy @Obj;
    END TRY
    
 BEGIN CATCH
  EXEC sp_OADestroy @Obj;
 END CATCH
 
   SET NOCOUNT OFF
END

GO
USE [master]
GO
ALTER DATABASE [PDACT] SET  READ_WRITE 
GO
