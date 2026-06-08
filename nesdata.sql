-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.13-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for cms2
CREATE DATABASE IF NOT EXISTS `cms2` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `cms2`;


-- Dumping structure for table cms2.adm_institution
CREATE TABLE IF NOT EXISTS `adm_institution` (
  `id` bigint(5) unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` bigint(15) DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `contact_person` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `phone` bigint(8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inst_id` (`institution_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table cms2.adm_institution: ~6 rows (approximately)
/*!40000 ALTER TABLE `adm_institution` DISABLE KEYS */;
REPLACE INTO `adm_institution` (`id`, `institution_id`, `name`, `contact_person`, `address`, `phone`, `created_at`) VALUES
	(3, 161230121720, 'BANK OF BHUTAN', 'TASHI YEZER', 'C/O BANK OF BHUTAN, THIMPHU', 97517626573, '2016-12-30 12:17:20'),
	(4, 161230121913, 'BHUTAN DEVELOPMENT BANK LIMITED', 'DAWA DAKPA', 'C/O BHUTAN DEVELOPMENT BANK, THIMPHU', 97517626573, '2016-12-30 12:19:13'),
	(5, 161230121956, 'ROYAL INSURANCE CORPORATION LTD', 'KUENZANG CHODEN', 'C/O RICB, THIMPHU', 97517626573, '2016-12-30 12:19:56'),
	(6, 161230122041, 'BHUTAN NATIONAL BANK', 'NAWANGLHENDUP', 'C/O BNB, THIMPHU.', 97517626573, '2016-12-30 12:20:41'),
	(7, 161230122209, 'DRUKYUL SECURITIES PVT. LTD', 'KHANDU WANGMO', 'C/O DSB, THIMPHU', 97517626573, '2016-12-30 12:22:09'),
	(8, 170103110240, 'T BANK LIMITED', 'TENZIN RABGAY', 'C/O T BANK LIMITED, THIMPHU', 17626573, '2017-01-03 11:02:40');
/*!40000 ALTER TABLE `adm_institution` ENABLE KEYS */;


-- Dumping structure for table cms2.adm_participants
CREATE TABLE IF NOT EXISTS `adm_participants` (
  `participant_id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `participant_type` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `participant_code` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_person` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `institution_id` bigint(15) NOT NULL,
  `phone` bigint(11) NOT NULL,
  `email` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`participant_id`),
  UNIQUE KEY `participant_code` (`participant_code`),
  KEY `pcode` (`participant_code`),
  KEY `inst_id` (`institution_id`),
  CONSTRAINT `inst_id` FOREIGN KEY (`institution_id`) REFERENCES `adm_institution` (`institution_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table cms2.adm_participants: ~6 rows (approximately)
/*!40000 ALTER TABLE `adm_participants` DISABLE KEYS */;
REPLACE INTO `adm_participants` (`participant_id`, `participant_type`, `participant_code`, `contact_person`, `address`, `institution_id`, `phone`, `email`, `created_at`) VALUES
	(6, 'MEMBER', 'MEMBHUT', 'DAWA DAKPA', 'C/O BDB, THIMPHU', 161230121913, 97517626573, 'DAWA@YAHOO.COM', '2016-12-30 12:25:23'),
	(7, 'MEMBER', 'MEMROYA', 'KUENZANG CHODEN', 'C/O RICB, THIMPHU', 161230121956, 97517626573, 'kuenzang@rsebl.org.b', '2016-12-30 12:28:25'),
	(9, 'MEMBER', 'MEMDSB', 'KHANDU WANGMO', 'C/O DSB, THIMPHU', 161230122209, 97517626573, 'khandu@yahoo.com', '2016-12-30 12:30:20'),
	(10, 'MEMBER', 'MEMBNB', 'NAWANG LHENDUP', 'C/O BNBL, THIMPHU', 161230122041, 97517626573, 'NAWANG@YAHOO.COM', '2016-12-30 12:31:20'),
	(11, 'MEMBER', 'MEMT BA', 'TENZIN RABGAY', 'C/O T BANK LIMITED, THIMPHU', 170103110240, 17626573, 'tenzin@yahoo.com', '2017-01-03 11:03:38'),
	(12, 'MEMBER', 'MEMBOB', 'Tashi Yoezer', 'Thimphu, BHutan', 161230121720, 17339660, 'tashi@gmail.com', '2017-11-27 12:20:56');
/*!40000 ALTER TABLE `adm_participants` ENABLE KEYS */;


-- Dumping structure for table cms2.banks
CREATE TABLE IF NOT EXISTS `banks` (
  `bank_id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`bank_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.banks: ~5 rows (approximately)
/*!40000 ALTER TABLE `banks` DISABLE KEYS */;
REPLACE INTO `banks` (`bank_id`, `bank_name`) VALUES
	(1, 'Bhutan National Bank'),
	(2, 'Bank of Bhutan'),
	(5, 'Bhutan Development Bank'),
	(6, 'TBANK'),
	(7, 'PNB');
/*!40000 ALTER TABLE `banks` ENABLE KEYS */;


-- Dumping structure for table cms2.bank_branch
CREATE TABLE IF NOT EXISTS `bank_branch` (
  `BRANCH_ID` int(5) NOT NULL AUTO_INCREMENT,
  `BRANCH_NAME` varchar(200) NOT NULL,
  `BRANCH_ADDRESS` varchar(200) NOT NULL,
  `BANK_ID` tinyint(3) NOT NULL,
  PRIMARY KEY (`BRANCH_ID`),
  KEY `FK_bank_branch_banks` (`BANK_ID`),
  CONSTRAINT `FK_bank_branch_banks` FOREIGN KEY (`BANK_ID`) REFERENCES `banks` (`bank_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.bank_branch: ~2 rows (approximately)
/*!40000 ALTER TABLE `bank_branch` DISABLE KEYS */;
REPLACE INTO `bank_branch` (`BRANCH_ID`, `BRANCH_NAME`, `BRANCH_ADDRESS`, `BANK_ID`) VALUES
	(1, 'Thimphu112', 'TEST', 1),
	(2, 'T11', 'tphu', 2);
/*!40000 ALTER TABLE `bank_branch` ENABLE KEYS */;


-- Dumping structure for table cms2.bbo_account
CREATE TABLE IF NOT EXISTS `bbo_account` (
  `bbo_client_id` int(10) NOT NULL AUTO_INCREMENT,
  `acc_type` varchar(50) DEFAULT NULL,
  `acc_code` varchar(15) DEFAULT NULL,
  `f_name` char(20) DEFAULT NULL,
  `l_name` char(20) DEFAULT NULL,
  `nationality` char(15) DEFAULT NULL,
  `ID` varchar(15) DEFAULT NULL,
  `DzongkhagID` tinyint(3) DEFAULT NULL,
  `tpn` varchar(10) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `bank_id` tinyint(3) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `brokerage_commission` decimal(10,2) DEFAULT NULL,
  `address` varchar(50) DEFAULT NULL,
  `institution_id` bigint(15) DEFAULT NULL,
  `ca_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`bbo_client_id`),
  UNIQUE KEY `acc_code` (`acc_code`),
  KEY `Bank_id` (`bank_id`),
  KEY `dzongkhag_id` (`DzongkhagID`),
  CONSTRAINT `Bank_idq` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`bank_id`) ON UPDATE NO ACTION,
  CONSTRAINT `dzongkhag_idq` FOREIGN KEY (`DzongkhagID`) REFERENCES `tbldzongkhag` (`DzongkhagID`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.bbo_account: ~11 rows (approximately)
/*!40000 ALTER TABLE `bbo_account` DISABLE KEYS */;
REPLACE INTO `bbo_account` (`bbo_client_id`, `acc_type`, `acc_code`, `f_name`, `l_name`, `nationality`, `ID`, `DzongkhagID`, `tpn`, `phone`, `user_name`, `email`, `bank_id`, `bank_account`, `brokerage_commission`, `address`, `institution_id`, `ca_date`) VALUES
	(3, 'I', 'DD9999', 'tAAHSI', 'yANGDON', 'Bhut', '444', 2, 'tpn-30', '0244444444', 'MEMBANK0157', 'tyezer@rsebl.org.bt', 1, '1233333', 20.00, 'Tashigang, Nanong, Wongchilo, P/gatshel', 161230121720, '2017-01-05 11:42:52'),
	(4, 'J', 'CD23333333', 'Wangdi Dratshang', '', '', 'Do990099', 17, 'tpn-90000', '', 'MEMBANK0157', 'dorji@gmail.com', 2, '45474756956', 50.00, 'Thinleygang, Wangdiphodrang', 161230121720, '2017-01-05 11:47:11'),
	(5, 'I', 'U000000001', 'KARMA', 'PEMA', 'BHUTANESE', '11007000189', 4, 'KAP5555', '17626573', 'MEMDSB0188', 'KARMA', 1, '555555555', 5.00, 'C/O DSB, THIMPHU', 161230122209, '2017-01-05 11:49:38'),
	(6, 'I', 'U000000002', 'YANGSEL', 'DEMA', 'BHUATANESE', '1100700090', 1, 'YAP4444', '17626573', 'MEMDSB0188', 'YANGSEL@YAHOO.COM', 2, '', 5.00, 'C/O DSB, THIMPHU', 161230122209, '2017-01-05 11:51:22'),
	(7, 'I', '0000000000', 'ty', 'yezer', 'Bhu', '11111111111', 17, '000', '1111111111', 'MEMBANK0157', 'tyezer.TY@gmail.com', 2, '99999', 50.00, 'Nanong, Wamrong, Trashigang', 161230121720, '2017-01-05 11:52:17'),
	(8, 'J', 'cd9899999', 'Tashiding  Welfare A', '', '', 'D4444', 1, 'tpn 222', '', 'MEMBANK0157', 'tyezer.TY@gmail.com', 1, '10', 80.00, 'Khomang, drupkang, Tashigang', 161230121720, '2017-01-05 12:15:15'),
	(9, 'I', 'hhhhhhhhhh', 'yy', 'oo', 'Bhu', '5555555555', 1, 'tpn 222', '6666666666', 'MEMBANK0157', 'tyezer.TY@gmail.com', 2, '88888888888', 5.00, 'Thinleygang, Wangdiphodrang', 161230121720, '2017-01-05 12:30:22'),
	(10, 'I', 'dddddd', 'D', 'd', 'Dd', '33333333', 1, 'tpn-30', '66666', 'MEMBANK0157', 'tyezer.ty@gmail.com', 1, '7777777777777', 30.00, 'Thinleygang, Wangdiphodrang', 161230121720, '2017-01-05 12:31:16'),
	(12, 'I', 'bbb1234567', 'Cheku', 'Dhendup', 'Bhutanese', '11504003828', 1, 'CAP1235', '17339889', 'MEMDSB0188', 'seventhcheku100nos@gmail.com', 2, '123456789', 20.00, 'Thimphu, BHutan', 161230122209, '2017-09-28 13:55:35'),
	(13, 'J', 'NMK0212222', 'Dhendup', '', '', '125ddd22', 6, 'VB123456', '77279458', 'MEMDSB0188', 'chekuantondendup@hotmail.com', 2, '552364459', 30.00, 'Thimphu, BHutan', 161230122209, '2017-09-28 13:57:01'),
	(15, 'J', 'KIM01235', 'Thinkey', '', '', '12546', 1, 'TN125LLL', '17638064', 'MEMDSB0188', 'cheku@rsebl.org.bt', 2, '1254666', 10.00, 'Thimphu, BHutan', 161230122209, '2017-09-28 14:08:29');
/*!40000 ALTER TABLE `bbo_account` ENABLE KEYS */;


-- Dumping structure for table cms2.bbo_commission
CREATE TABLE IF NOT EXISTS `bbo_commission` (
  `bro_comm_id` bigint(10) NOT NULL AUTO_INCREMENT,
  `commission_name` varchar(50) NOT NULL,
  `rate` int(4) NOT NULL,
  `institution_id` bigint(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`bro_comm_id`),
  KEY `bbo_inst_id` (`institution_id`),
  CONSTRAINT `bbo_inst_id` FOREIGN KEY (`institution_id`) REFERENCES `adm_institution` (`institution_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.bbo_commission: ~8 rows (approximately)
/*!40000 ALTER TABLE `bbo_commission` DISABLE KEYS */;
REPLACE INTO `bbo_commission` (`bro_comm_id`, `commission_name`, `rate`, `institution_id`, `created_at`) VALUES
	(1, 'tsh', 22, 161230122041, '2017-11-07 12:44:55'),
	(2, 'ddd', 20, 161230122041, '2017-11-07 12:46:06'),
	(3, 'kkk', 90, 161230122041, '2017-11-07 12:46:59'),
	(28, 'ds', 3, 161230122041, '2017-11-27 13:04:53'),
	(29, 'te', 1, 161230122041, '2017-11-27 15:26:16'),
	(30, 'te', 1, 161230122041, '2017-11-27 15:28:03'),
	(31, 'te', 1, 161230122041, '2017-11-27 15:30:16'),
	(32, 'te', 1, 161230122041, '2017-11-27 15:35:42'),
	(33, 'af', 5, 161230122041, '2017-11-27 15:35:49');
/*!40000 ALTER TABLE `bbo_commission` ENABLE KEYS */;


-- Dumping structure for table cms2.bbo_finance
CREATE TABLE IF NOT EXISTS `bbo_finance` (
  `finance_id` bigint(10) NOT NULL AUTO_INCREMENT,
  `remarks` varchar(200) NOT NULL,
  `cd_code` varchar(15) NOT NULL,
  `amount` decimal(13,2) NOT NULL,
  `flag` tinyint(1) NOT NULL,
  `flag_id` bigint(10) NOT NULL,
  `finance_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_name` varchar(50) NOT NULL,
  `institution_id` bigint(15) NOT NULL,
  PRIMARY KEY (`finance_id`),
  KEY `Index 2` (`cd_code`),
  CONSTRAINT `FK_bbo_finance_client_account` FOREIGN KEY (`cd_code`) REFERENCES `client_account` (`cd_code`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.bbo_finance: ~5 rows (approximately)
/*!40000 ALTER TABLE `bbo_finance` DISABLE KEYS */;
REPLACE INTO `bbo_finance` (`finance_id`, `remarks`, `cd_code`, `amount`, `flag`, `flag_id`, `finance_date`, `user_name`, `institution_id`) VALUES
	(27, 'test', 'CD12566301', -168000000.00, 1, 0, '2017-11-16 15:05:40', 'MEMBNB3828', 161230122041),
	(28, 'Buy ,Order entry by user MEMBNB3828 of member,MEMB', 'CD12566301', 59994.00, 3, 171120032656, '2017-11-20 15:26:56', 'MEMBNB3828', 161230122041),
	(29, 'undefined', 'CD12566301', 123.00, 1, 171126053829, '2017-11-26 17:34:03', 'MEMBNB3828', 161230122041),
	(33, 'Sell ,Order entry by user MEMBNB3828 of member,MEMBNBof volume,100 @ Nu. 18 /share', 'CD12566301', 163800.00, 2, 171126053825, '2017-11-26 17:38:25', 'MEMBNB3828', 161230122041),
	(42, 'undefined', 'Cheku00122', 777.00, 1, 0, '2017-11-27 15:40:32', 'MEMBNB3828', 161230122041);
/*!40000 ALTER TABLE `bbo_finance` ENABLE KEYS */;


-- Dumping structure for table cms2.bbo_vault
CREATE TABLE IF NOT EXISTS `bbo_vault` (
  `vault_id` bigint(10) NOT NULL AUTO_INCREMENT,
  `remarks` varchar(50) NOT NULL DEFAULT '0',
  `acc_code` varchar(15) DEFAULT '0',
  `symbol_id` bigint(15) DEFAULT '0',
  `bbo_holding` bigint(15) DEFAULT '0',
  `dep_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_name` varchar(50) DEFAULT NULL,
  `institution_id` bigint(15) DEFAULT NULL,
  PRIMARY KEY (`vault_id`),
  KEY `FK_bbo_vault_symbol` (`symbol_id`),
  KEY `FK_bbo_vault_bbo_account` (`acc_code`),
  CONSTRAINT `FK_bbo_vault_bbo_account` FOREIGN KEY (`acc_code`) REFERENCES `bbo_account` (`acc_code`) ON UPDATE NO ACTION,
  CONSTRAINT `FK_bbo_vault_symbol` FOREIGN KEY (`symbol_id`) REFERENCES `symbol` (`symbol_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.bbo_vault: ~0 rows (approximately)
/*!40000 ALTER TABLE `bbo_vault` DISABLE KEYS */;
/*!40000 ALTER TABLE `bbo_vault` ENABLE KEYS */;


-- Dumping structure for table cms2.cds_dep_wit
CREATE TABLE IF NOT EXISTS `cds_dep_wit` (
  `cds_dep_wit_id` bigint(15) NOT NULL AUTO_INCREMENT,
  `cd_code` varchar(50) NOT NULL DEFAULT '0',
  `symbol_id` bigint(15) NOT NULL DEFAULT '0',
  `volume` bigint(15) NOT NULL DEFAULT '0',
  `user_name` varchar(50) NOT NULL DEFAULT '0',
  `institution_id` bigint(15) NOT NULL DEFAULT '0',
  `remarks` varchar(255) NOT NULL DEFAULT '0',
  `type` varchar(50) NOT NULL DEFAULT '0',
  `entry_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cds_dep_wit_id`),
  KEY `FK_cds_dep_wit` (`symbol_id`),
  CONSTRAINT `FK_cds_dep_wit` FOREIGN KEY (`symbol_id`) REFERENCES `symbol` (`symbol_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.cds_dep_wit: ~74 rows (approximately)
/*!40000 ALTER TABLE `cds_dep_wit` DISABLE KEYS */;
REPLACE INTO `cds_dep_wit` (`cds_dep_wit_id`, `cd_code`, `symbol_id`, `volume`, `user_name`, `institution_id`, `remarks`, `type`, `entry_date`) VALUES
	(1, 'BNB0000003', 6, 10000, 'MEMDSB0154', 161230122209, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(2, 'D000000001', 5, 5000, 'MEMDSB0154', 161230122209, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(3, 'BNB0000002', 8, 45000, 'MEMDSB0154', 161230122209, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(5, 'BNB0000003', 5, -5000, 'MEMDSB0154', 161230122209, 'WITHDRAW', '0', '2017-01-25 13:36:07'),
	(6, 'BNB0000002', 6, 5555, 'MEMDSB0154', 161230122209, 'DEP', '0', '2017-01-25 13:36:07'),
	(7, 'D000000001', 6, 9999, 'MEMDSB0154', 161230122209, 'DEP', '0', '2017-01-25 13:36:07'),
	(8, 'BNB0000001', 6, 1000, 'ADMIN-CDS', 161230121720, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(9, 'BNB0000001', 5, 10000, 'ADMIN-CDS', 161230121720, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(10, 'YD00000001', 3, 25000, 'MEMDSB0154', 161230122209, 'DEP', '0', '2017-01-25 13:36:07'),
	(11, 'UW00000001', 9, 25000, 'MEMDSB0154', 161230122209, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(12, 'YD00000001', 9, 25000, 'MEMDSB0154', 161230122209, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(13, 'YD00000001', 6, 35000, 'MEMDSB0154', 161230122209, 'DEPSOT', '0', '2017-01-25 13:36:07'),
	(14, 'YD00000001', 2, 90000, 'MEMDSB0154', 161230122209, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(16, 'DD00000001', 5, 100000, 'ADMIN-CDS', 161230121720, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(17, 'DD00000001', 6, 40000, 'ADMIN-CDS', 161230121720, 'DEPOSIT', '0', '2017-01-25 13:36:07'),
	(18, 'BNB0000001', 6, -7, 'ADMIN-CDS', 161230121720, 'wit', '0', '2017-01-25 13:36:07'),
	(19, 'BNB0000001', 6, -7, 'ADMIN-CDS', 161230121720, 'wit', '0', '2017-01-25 13:36:07'),
	(25, 'BNB0000001', 6, -5, 'ADMIN-CDS', 161230121720, 'wer', '0', '2017-01-25 13:48:53'),
	(26, 'BNB0000001', 6, -5, 'ADMIN-CDS', 161230121720, 'wer', '0', '2017-01-25 13:49:24'),
	(27, 'BNB0000001', 6, -5, 'ADMIN-CDS', 161230121720, 'wer', '0', '2017-01-25 13:49:41'),
	(28, 'BNB0000001', 6, -5, 'ADMIN-CDS', 161230121720, 'wer', '0', '2017-01-25 13:50:05'),
	(29, 'BNB0000001', 6, 5, 'ADMIN-CDS', 161230121720, 'wer', '0', '2017-01-25 13:52:36'),
	(30, 'BNB0000001', 6, -5, 'ADMIN-CDS', 161230121720, 'wer', '0', '2017-01-25 14:01:43'),
	(31, 'BNB0000001', 6, -5, 'ADMIN-CDS', 161230121720, 'wer', '0', '2017-01-25 14:03:53'),
	(32, 'BNB0000001', 6, 1, 'ADMIN-CDS', 161230121720, 'asfd', '0', '2017-01-25 14:56:49'),
	(33, 'BNB0000001', 6, -1, 'ADMIN-CDS', 161230121720, 'ZXCZXC', '0', '2017-01-25 14:57:15'),
	(34, 'BNB0000001', 6, -900, 'ADMIN-CDS', 161230121720, 'kjflsdaj', 'WITHDRAW', '2017-01-30 15:26:45'),
	(35, 'BNB0000001', 6, 1000, 'ADMIN-CDS', 161230121720, 'kjksafj', 'DEPOSIT', '2017-01-30 15:30:37'),
	(36, 'BNB0000001', 6, 1000, 'ADMIN-CDS', 161230121720, 'pledged', 'PLEDGE', '2017-01-30 15:43:00'),
	(37, 'BNB0000001', 6, 500, 'ADMIN-CDS', 161230121720, 'saflkjsdklf', 'PLEDGE RELEASE', '2017-01-30 15:44:05'),
	(38, 'BNB0000001', 6, 1000, 'ADMIN-CDS', 161230121720, '', 'DEPOSIT', '2017-01-30 16:00:06'),
	(39, 'BNB0000001', 6, 1000, 'ADMIN-CDS', 161230121720, 'fghj', 'PLEDGE', '2017-01-30 16:00:30'),
	(40, 'BNB0000003', 6, -2000, 'ADMIN-CDS', 161230121720, 't', 'WITHDRAW', '2017-08-04 11:45:17'),
	(41, 'BNB0000001', 6, 55555555, 'ADMIN-CDS', 161230121720, '', 'DEPOSIT', '2017-08-04 12:21:40'),
	(42, 'BNB0000001', 6, 100, 'ADMIN-CDS', 161230121720, '', 'DEPOSIT', '2017-08-07 11:42:38'),
	(43, 'BNB0000001', 6, 100, 'ADMIN-CDS', 161230121720, 'y', 'DEPOSIT', '2017-08-07 11:48:10'),
	(44, 'BNB0000001', 5, 100, 'ADMIN-CDS', 161230121720, 'yrd', 'DEPOSIT', '2017-08-07 11:51:59'),
	(45, 'BNB0000001', 8, 100, 'ADMIN-CDS', 161230121720, '', '0', '2017-08-07 12:29:18'),
	(46, 'BNB0000001', 8, 10, 'ADMIN-CDS', 161230121720, '9', 'DEPOSIT', '2017-08-07 12:30:06'),
	(47, 'BNB0000001', 8, 10, 'ADMIN-CDS', 161230121720, '9', 'DEPOSIT', '2017-08-07 12:30:41'),
	(48, 'BNB0000001', 5, 100, 'ADMIN-CDS', 161230121720, '', 'DEPOSIT', '2017-08-07 14:39:48'),
	(49, 'BNB0000001', 5, -100, 'ADMIN-CDS', 161230121720, '', 'WITHDRAW', '2017-08-07 14:40:24'),
	(50, 'BNB0000001', 8, -20, 'ADMIN-CDS', 161230121720, '', 'WITHDRAW', '2017-08-07 14:41:06'),
	(51, 'BNB0000001', 5, -100, 'ADMIN-CDS', 161230121720, '', 'WITHDRAW', '2017-08-07 14:43:15'),
	(52, 'BNB0000001', 8, -20, 'ADMIN-CDS', 161230121720, '', 'WITHDRAW', '2017-08-07 14:49:18'),
	(53, 'D000000001', 10, 1000, 'ADMIN-CDS', 161230121720, '', '0', '2017-08-07 14:54:04'),
	(54, 'D000000001', 8, 1000, 'ADMIN-CDS', 161230121720, '', 'DEPOSIT', '2017-08-07 15:00:54'),
	(55, 'D000000001', 8, -100, 'ADMIN-CDS', 161230121720, '', 'WITHDRAW', '2017-08-07 15:01:23'),
	(56, 'D000000001', 5, 200, 'ADMIN-CDS', 161230121720, '', 'DEPOSIT', '2017-08-07 15:02:49'),
	(57, 'D000000001', 5, -300, 'ADMIN-CDS', 161230121720, '', 'WITHDRAW', '2017-08-07 15:03:22'),
	(58, 'BNB0000001', 2, 300, 'ADMIN-CDS', 161230121720, '', 'DEPOSIT', '2017-08-08 15:58:38'),
	(59, 'BNB0000001', 2, -30, 'ADMIN-CDS', 161230121720, '', 'WITHDRAW', '2017-08-08 15:59:14'),
	(60, 'BNB0000001', 6, 55, 'ADMIN-CDS', 161230121720, 'both bnbn', 'PLEDGE', '2017-08-11 11:18:46'),
	(61, 'YD00000001', 6, 500, 'ADMIN-CDS', 161230121720, '125', 'PLEDGE', '2017-08-15 11:32:19'),
	(62, 'YD00000001', 9, 200, 'ADMIN-CDS', 161230121720, 'd', 'PLEDGE', '2017-08-15 11:32:48'),
	(63, '', 9, 150, 'ADMIN-CDS', 161230121720, '12', 'PLEDGE RELEASE', '2017-08-15 11:34:43'),
	(64, '', 9, 100, 'ADMIN-CDS', 161230121720, '2', 'PLEDGE RELEASE', '2017-08-15 11:37:26'),
	(65, '', 9, 100, 'ADMIN-CDS', 161230121720, '3', 'PLEDGE RELEASE', '2017-08-15 11:38:57'),
	(66, '', 9, 100, 'ADMIN-CDS', 161230121720, 'd', 'PLEDGE RELEASE', '2017-08-15 11:40:17'),
	(67, 'YD00000001', 9, 100, 'ADMIN-CDS', 161230121720, 'd', 'PLEDGE RELEASE', '2017-08-15 11:42:41'),
	(68, 'YD00000001', 6, 10, 'ADMIN-CDS', 161230121720, 'd', 'PLEDGE RELEASE', '2017-08-15 11:45:06'),
	(70, 'YD00000001', 9, 100, 'ADMIN-CDS', 231321321, 'dfdsa', 'pl', '2017-08-15 12:02:16'),
	(71, 'YD00000001', 9, 100, 'ADMIN-CDS', 231321321, 'dfdsa', 'pl', '2017-08-15 12:02:57'),
	(72, 'BNB0000001', 6, 100, 'ADMIN-CDS', 161230121720, 'd', 'PLEDGE RELEASE', '2017-08-15 12:03:59'),
	(73, 'BNB0000001', 6, 50, 'ADMIN-CDS', 161230121720, '3', 'PLEDGE RELEASE', '2017-08-15 12:08:48'),
	(74, 'YD00000001', 9, 100, 'ADMIN-CDS', 161230121720, 'Pledge Edited for pledge Contract 170815113229', 'PLEDGE EDIT', '2017-08-16 15:06:43'),
	(75, 'YD00000001', 9, 100, 'ADMIN-CDS', 161230121720, 'Pledge Edited for pledge Contract 170815113229', 'PLEDGE EDIT', '2017-08-16 15:11:42'),
	(76, 'YD00000001', 9, 100, 'ADMIN-CDS', 161230121720, 'Pledge Edited for pledge Contract 170815113229', 'PLEDGE EDIT', '2017-08-16 15:14:54'),
	(77, 'YD00000001', 9, 100, 'ADMIN-CDS', 161230121720, 'Pledge Edited for pledge Contract 170815113229', 'PLEDGE EDIT', '2017-08-16 15:16:41'),
	(78, 'YD00000001', 9, 300, 'ADMIN-CDS', 161230121720, 'Pledge Edited for pledge Contract 170815113229', 'PLEDGE EDIT', '2017-08-16 15:28:25'),
	(79, 'YD00000001', 9, -100, 'ADMIN-CDS', 161230121720, 'Pledge release edited for contract code:  170815113229 , and pledge id : 26', 'PLEDGE RELEASE EDIT', '2017-08-17 16:06:51'),
	(80, 'BNB0000001', 6, 12, 'ADMIN-CDS', 161230121720, '', 'DEPOSIT', '2017-09-29 15:40:37'),
	(81, 'BNB0000001', 6, -100, 'ADMIN-CDS', 161230121720, '100', 'WITHDRAW', '2017-09-29 16:57:21'),
	(82, 'BNB0000001', 6, 122, 'ADMIN-CDS', 161230121720, 'o', 'DEPOSIT', '2017-10-19 23:14:04');
/*!40000 ALTER TABLE `cds_dep_wit` ENABLE KEYS */;


-- Dumping structure for table cms2.cds_holding
CREATE TABLE IF NOT EXISTS `cds_holding` (
  `cds_holding_id` bigint(15) NOT NULL AUTO_INCREMENT,
  `cd_code` varchar(15) NOT NULL DEFAULT '0',
  `symbol_id` bigint(15) NOT NULL DEFAULT '0',
  `volume` bigint(15) NOT NULL DEFAULT '0',
  `pledge_volume` bigint(15) NOT NULL DEFAULT '0',
  `block_volume` bigint(15) NOT NULL DEFAULT '0',
  `pending_in_vol` bigint(15) NOT NULL DEFAULT '0',
  `pending_out_vol` bigint(15) NOT NULL DEFAULT '0',
  `user_name` varchar(50) NOT NULL DEFAULT '0',
  `institution_id` bigint(15) NOT NULL DEFAULT '0',
  `remarks` varchar(50) NOT NULL DEFAULT '0',
  `flag` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cds_holding_id`),
  KEY `FK_cds_holding_symbol` (`symbol_id`),
  KEY `FK_cds_holding_client_account` (`cd_code`),
  CONSTRAINT `FK_cds_holding_client_account` FOREIGN KEY (`cd_code`) REFERENCES `client_account` (`cd_code`) ON UPDATE NO ACTION,
  CONSTRAINT `FK_cds_holding_symbol` FOREIGN KEY (`symbol_id`) REFERENCES `symbol` (`symbol_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.cds_holding: ~23 rows (approximately)
/*!40000 ALTER TABLE `cds_holding` DISABLE KEYS */;
REPLACE INTO `cds_holding` (`cds_holding_id`, `cd_code`, `symbol_id`, `volume`, `pledge_volume`, `block_volume`, `pending_in_vol`, `pending_out_vol`, `user_name`, `institution_id`, `remarks`, `flag`) VALUES
	(1, 'CD12566301', 6, 7880, 0, 0, 100, 120, 'MEMBNB3828', 161230122209, 'DEPOSIT', 0),
	(2, 'D000000001', 5, 4900, 0, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEPOSIT', 0),
	(3, 'BNB0000002', 8, 45000, 0, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEPOSIT', 0),
	(5, 'BNB0000003', 5, 5000, 0, 0, 0, 0, 'MEMDSB0154', 161230122209, 'WITHDRAW', 0),
	(6, 'BNB0000002', 6, 5555, 0, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEP', 0),
	(7, 'D000000001', 6, 9999, 0, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEP', 0),
	(8, 'BNB0000001', 6, 55557884, -95, 0, 0, 0, 'ADMIN-CDS', 161230121720, 'DEPOSIT', 0),
	(9, 'BNB0000001', 5, 10000, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, 'DEPOSIT', 0),
	(10, 'YD00000001', 3, 24123, 0, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEP', 0),
	(11, 'UW00000001', 9, 24790, 0, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEPOSIT', 0),
	(12, 'YD00000001', 9, 21700, 300, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEPOSIT', 0),
	(13, 'YD00000001', 6, 35496, 504, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEPSOT', 0),
	(14, 'YD00000001', 2, 89978, 0, 0, 0, 0, 'MEMDSB0154', 161230122209, 'DEPOSIT', 0),
	(16, 'DD00000001', 5, 100000, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, 'DEPOSIT', 0),
	(17, 'DD00000001', 6, 39000, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, 'DEPOSIT', 0),
	(29, 'uw00000001', 2, 0, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, '0', 0),
	(30, 'BNB0000001', 8, 80, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, '0', 0),
	(31, 'D000000001', 10, 1000, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, '0', 0),
	(32, 'D000000001', 8, 900, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, 'DEPOSIT', 0),
	(33, 'BNB0000001', 2, 200, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, 'DEPOSIT', 0),
	(35, 'DD00000001', 2, 3, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, '0', 0),
	(37, 'NT00000001', 2, 70, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, '0', 0),
	(38, 'BNB0000001', 9, 1900, 0, 0, 0, 0, 'ADMIN-CDS', 161230121720, '0', 0);
/*!40000 ALTER TABLE `cds_holding` ENABLE KEYS */;


-- Dumping structure for table cms2.cds_pledge
CREATE TABLE IF NOT EXISTS `cds_pledge` (
  `pledge_id` bigint(5) NOT NULL AUTO_INCREMENT,
  `pledge_name` varchar(50) DEFAULT NULL,
  `pledge_contract` bigint(15) DEFAULT NULL,
  `symbol_id` bigint(15) DEFAULT NULL,
  `pledge_volume` bigint(15) DEFAULT NULL,
  `cd_code` varchar(15) DEFAULT NULL,
  `pledgee` varchar(50) DEFAULT NULL,
  `pledge_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(2) DEFAULT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pledge_id`),
  KEY `FK_cds_pledge_client_account` (`cd_code`),
  CONSTRAINT `FK_cds_pledge_client_account` FOREIGN KEY (`cd_code`) REFERENCES `client_account` (`cd_code`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.cds_pledge: ~10 rows (approximately)
/*!40000 ALTER TABLE `cds_pledge` DISABLE KEYS */;
REPLACE INTO `cds_pledge` (`pledge_id`, `pledge_name`, `pledge_contract`, `symbol_id`, `pledge_volume`, `cd_code`, `pledgee`, `pledge_date`, `status`, `remarks`, `user_name`) VALUES
	(2, NULL, 1, 6, 10, 'YD00000001', 'BHUTAN NATIONAL BANK', '2017-01-18 15:20:11', NULL, 'PLEGEE', 'ADMIN-CDS'),
	(12, NULL, 1, 6, -4, 'YD00000001', 'BHUTAN NATIONAL BANK', '2017-01-26 14:25:29', NULL, 'test', 'ADMIN-CDS'),
	(18, NULL, 20170130040017, 6, 1000, 'BNB0000001', 'DRUK PNB BANK LIMITED', '2017-01-30 16:00:31', NULL, 'fghj', 'ADMIN-CDS'),
	(19, NULL, 170811111658, 6, 55, 'BNB0000001', 'BANK OF BHUTAN', '2017-08-11 11:18:46', NULL, 'both bnbn', 'ADMIN-CDS'),
	(20, NULL, 170815113140, 6, 500, 'YD00000001', 'BANK OF BHUTAN', '2017-08-15 11:32:19', NULL, '125', 'ADMIN-CDS'),
	(21, NULL, 170815113229, 9, 300, 'YD00000001', 'BANK OF BHUTAN', '2017-08-15 11:32:48', NULL, 'd', 'ADMIN-CDS'),
	(26, NULL, 170815113229, 9, -100, 'YD00000001', 'BANK OF BHUTAN', '2017-08-15 11:42:42', NULL, 'd', 'ADMIN-CDS'),
	(27, NULL, 170815113140, 6, -10, 'YD00000001', 'BANK OF BHUTAN', '2017-08-15 11:45:06', NULL, 'd', 'ADMIN-CDS'),
	(28, NULL, 20170130040017, 6, -100, 'BNB0000001', 'DRUK PNB BANK LIMITED', '2017-08-15 12:03:59', NULL, 'd', 'ADMIN-CDS'),
	(29, NULL, 20170130040017, 6, -50, 'BNB0000001', 'DRUK PNB BANK LIMITED', '2017-08-15 12:08:48', NULL, '3', 'ADMIN-CDS');
/*!40000 ALTER TABLE `cds_pledge` ENABLE KEYS */;


-- Dumping structure for table cms2.cds_pledgee
CREATE TABLE IF NOT EXISTS `cds_pledgee` (
  `pledgee_id` int(11) NOT NULL AUTO_INCREMENT,
  `pledgee` varchar(50) NOT NULL DEFAULT '0',
  `address` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pledgee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.cds_pledgee: ~5 rows (approximately)
/*!40000 ALTER TABLE `cds_pledgee` DISABLE KEYS */;
REPLACE INTO `cds_pledgee` (`pledgee_id`, `pledgee`, `address`) VALUES
	(1, 'DRUK PNB BANK LIMITED', 'THIMPHU, BHUTAN'),
	(2, 'BHUTAN NATIONAL BANK', 'THIMPHU, BHUTAN1'),
	(3, 'BANK OF BHUTAN', 'THIMPHU'),
	(4, 'BANK OF BHUTAN', 'thimphu, bhutan'),
	(5, 'bnbddddd', 'sdfsa');
/*!40000 ALTER TABLE `cds_pledgee` ENABLE KEYS */;


-- Dumping structure for table cms2.cds_transfer
CREATE TABLE IF NOT EXISTS `cds_transfer` (
  `transfer_id` bigint(15) NOT NULL AUTO_INCREMENT,
  `from_acc` varchar(15) DEFAULT NULL,
  `to_acc` varchar(15) DEFAULT NULL,
  `symbol_id` bigint(15) DEFAULT NULL,
  `trs_vol` bigint(15) DEFAULT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `trs_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transfer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.cds_transfer: ~25 rows (approximately)
/*!40000 ALTER TABLE `cds_transfer` DISABLE KEYS */;
REPLACE INTO `cds_transfer` (`transfer_id`, `from_acc`, `to_acc`, `symbol_id`, `trs_vol`, `remarks`, `user_name`, `trs_date`) VALUES
	(1, 'yd00000001', 'uw00000001', 9, 20000, 'transfer', 'ADMIN-CDS', '2017-01-17 14:50:51'),
	(2, 'uw00000001', 'yd00000001', 9, 25000, 'transfer', 'ADMIN-CDS', '2017-01-17 14:57:21'),
	(3, 'uw00000001', 'yd00000001', 9, 25000, 'transfer', 'ADMIN-CDS', '2017-01-17 14:58:06'),
	(4, 'uw00000001', 'yd00000001', 9, 25000, 'transfer', 'ADMIN-CDS', '2017-01-17 14:59:25'),
	(5, 'uw00000001', 'yd00000001', 9, 25000, 'transfre', 'ADMIN-CDS', '2017-01-17 15:00:11'),
	(6, 'DD00000001', 'YD00000001', 5, 45000, 'transfer', 'ADMIN-CDS', '2017-01-18 15:07:16'),
	(7, 'yd00000001', 'uw00000001', 2, 45000, 'asdf', 'ADMIN-CDS', '2017-01-25 14:17:45'),
	(8, 'yd00000001', 'uw00000001', 2, 45000, 'asdf', 'ADMIN-CDS', '2017-01-25 14:21:37'),
	(18, 'yd00000001', 'uw00000001', 2, 1, 'asdf', 'ADMIN-CDS', '2017-01-25 15:12:57'),
	(19, 'yd00000001', 'uw00000001', 2, 1, 'asdf', 'ADMIN-CDS', '2017-01-25 15:13:28'),
	(20, 'yd00000001', 'yd00000001', 2, 200, '2', 'ADMIN-CDS', '2017-08-09 10:42:58'),
	(21, 'yd00000001', 'yd00000001', 3, 1000, '0', 'ADMIN-CDS', '2017-08-09 10:43:43'),
	(22, 'yd00000001', '1234', 3, 100, '2', 'ADMIN-CDS', '2017-08-09 10:48:32'),
	(23, 'yd00000001', 'DD00000001', 2, 1, '4', 'ADMIN-CDS', '2017-08-09 11:00:12'),
	(24, 'uw00000001', 'DD00000001', 2, 2, 'test', 'ADMIN-CDS', '2017-08-09 11:04:01'),
	(25, 'yd00000001', 'NT00000001', 3, 111, 'tesssss', 'ADMIN-CDS', '2017-08-09 11:05:17'),
	(26, 'yd00000001', '666', 3, 666, 'check', 'ADMIN-CDS', '2017-08-09 11:06:35'),
	(27, 'uw00000001', 'abcd', 9, 100, 'sh', 'ADMIN-CDS', '2017-08-09 11:15:41'),
	(28, 'uw00000001', 'abc', 9, 100, 'jd', 'ADMIN-CDS', '2017-08-09 11:25:34'),
	(29, 'uw00000001', 'acbd', 9, 10, 'oi', 'ADMIN-CDS', '2017-08-09 11:26:14'),
	(30, 'DD00000001', 'yd00000001', 6, 1000, 'bnb', 'ADMIN-CDS', '2017-08-10 11:02:37'),
	(31, 'BNB0000001', 'NT00000001', 2, 70, 'nt', 'ADMIN-CDS', '2017-08-10 11:11:49'),
	(32, 'yd00000001', 'BNB0000001', 9, 900, 'rcb', 'ADMIN-CDS', '2017-08-10 11:13:31'),
	(33, 'yd00000001', 'BNB0000001', 9, 1000, 'rcb', 'ADMIN-CDS', '2017-08-10 11:15:13'),
	(34, 'YD00000001', '1254', 9, 1000, 'n', 'ADMIN-CDS', '2017-08-10 11:16:31');
/*!40000 ALTER TABLE `cds_transfer` ENABLE KEYS */;


-- Dumping structure for table cms2.circuit_breaker
CREATE TABLE IF NOT EXISTS `circuit_breaker` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '0',
  `margin` decimal(13,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.circuit_breaker: ~0 rows (approximately)
/*!40000 ALTER TABLE `circuit_breaker` DISABLE KEYS */;
REPLACE INTO `circuit_breaker` (`id`, `name`, `margin`) VALUES
	(1, 'CAP', 15.00);
/*!40000 ALTER TABLE `circuit_breaker` ENABLE KEYS */;


-- Dumping structure for table cms2.client_account
CREATE TABLE IF NOT EXISTS `client_account` (
  `client_id` int(10) NOT NULL AUTO_INCREMENT,
  `acc_type` varchar(50) DEFAULT NULL,
  `cd_code` varchar(15) DEFAULT NULL,
  `f_name` char(20) DEFAULT NULL,
  `l_name` char(20) DEFAULT NULL,
  `nationality` char(15) DEFAULT NULL,
  `ID` varchar(15) DEFAULT NULL,
  `DzongkhagID` tinyint(3) DEFAULT NULL,
  `tpn` varchar(10) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `email` varchar(20) DEFAULT NULL,
  `bank_id` tinyint(3) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bro_comm_id` bigint(10) DEFAULT NULL,
  `address` varchar(50) DEFAULT NULL,
  `institution_id` bigint(15) DEFAULT NULL,
  `ca_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(15) DEFAULT NULL,
  `occupation` varchar(15) DEFAULT NULL,
  `bank_account_type` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`client_id`),
  UNIQUE KEY `Index 2` (`cd_code`),
  KEY `Bank_id` (`bank_id`),
  KEY `dzongkhag_id` (`DzongkhagID`),
  KEY `Index 5` (`bro_comm_id`),
  CONSTRAINT `Bank_id` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`bank_id`) ON UPDATE NO ACTION,
  CONSTRAINT `FK_client_account_bbo_commission` FOREIGN KEY (`bro_comm_id`) REFERENCES `bbo_commission` (`bro_comm_id`) ON UPDATE NO ACTION,
  CONSTRAINT `dzongkhag_id` FOREIGN KEY (`DzongkhagID`) REFERENCES `tbldzongkhag` (`DzongkhagID`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.client_account: ~19 rows (approximately)
/*!40000 ALTER TABLE `client_account` DISABLE KEYS */;
REPLACE INTO `client_account` (`client_id`, `acc_type`, `cd_code`, `f_name`, `l_name`, `nationality`, `ID`, `DzongkhagID`, `tpn`, `phone`, `user_name`, `email`, `bank_id`, `bank_account`, `bro_comm_id`, `address`, `institution_id`, `ca_date`, `title`, `occupation`, `bank_account_type`) VALUES
	(6, '', 'BNB0000001', 'SAM', 'DORJI', '', '11508000908', 13, 'NAP0001', '12345', 'MEMDSB0188', 'lhendup@rseb.org.bt', 1, '5000003744002', 1, 'ROYAL SECURITIES EXCHANGE OF BHUTAN LTD., THIMPHU', 161230122041, '2017-01-05 12:29:53', '', '', ''),
	(7, 'I', 'BNB0000002', 'PEMA', 'WANGMO', 'BHUTANESE', '11508000913', 13, 'PAP001', '174523', 'MEMBNB0153', 'PEMA@GMAIL.COM', 1, '500002000024', NULL, 'C/O. DORJI, BNBL, THIMPHU', 161230122041, '2017-01-05 12:38:26', NULL, NULL, NULL),
	(8, '', 'BNB0000003', 'SEDEN', 'METO', 'BHUTANESE', '11508000910', 13, 'SAP0001', '45566', 'MEMBNB0153', '', 1, '520000200012', NULL, 'CO. DORJI DUKPA, RSPN, THIMPHU', 161230122209, '2017-01-05 12:41:37', NULL, NULL, NULL),
	(9, 'J', 'D000000001', 'BHUTAN TRUST FUND', '', '', 'II0000000001', 4, 'GATA3333', '', 'MEMDSB0154', 'KARMA@YAHOO.COM', 2, '1100000000', 1, 'C/O DSB, THIMPHU', 161230122209, '2017-01-05 14:35:52', '', '', ''),
	(10, 'I', 'YD00000001', 'YANGSEL', 'DEMA', 'BHUTANESE', '1100700099', 1, 'YAP001', '17626573', 'MEMDSB0154', 'YANGSEL@YAHOO.COM', 1, '500000125112', NULL, 'THIMPHU', 161230122209, '2017-01-17 12:57:29', NULL, NULL, NULL),
	(11, 'I', 'UW00000001', 'UGYEN', 'WANGCHUK', 'BHUTANESE', '11007000998', 1, 'UAP0001', '17626573', 'MEMDSB0154', 'UGYEN@YAHOO.COM', 1, '500000051255', NULL, 'THIMPHU, BHUTAN', 161230122209, '2017-01-17 13:59:36', NULL, NULL, NULL),
	(14, 'I', 'NT00000001', 'nedup', 'tshering', 'bhutanese', '10807000364', 1, 'nap00001', '8923783792', 'ADMIN-CDS', 'nedu@yahoo.com', 1, '50000001251', NULL, 'THIMPHU, BHUTAN', 161230121720, '2017-01-17 15:46:26', NULL, NULL, NULL),
	(16, 'I', 'DD00000001', 'DAWA', 'DAKPA', 'BHUTANESE', '11410003852', 5, 'DAP00001', '17626573', 'ADMIN-CDS', 'DAWA@YAHOO.COM', 1, '5000000012', NULL, 'RSEB, THIMPHU', 161230121720, '2017-01-18 14:59:03', NULL, NULL, NULL),
	(17, 'I', 'CD12566332', 'Karma', 'Wangchuk', 'Bhutanese', '11504003830', 17, 'CAP1235', '17339889', 'ADMIN-CDS', 'cheku@rsebl.org.bt', 1, '561', NULL, '', 161230121720, '2017-11-02 11:53:29', 'Mr', '2', 'Saving Account'),
	(18, 'I', 'BNB0000011', 'VeelaSH', 'Mongar', 'Bhutanese', '11234000000', 4, 'VLJD102', '17000000', 'ADMIN-CDS', 'vlash@gmail.com', 1, '1522222222', NULL, 'Thimphu, BHutan', 161230121720, '2017-11-07 10:00:33', 'Mr', '2', 'Saving Account'),
	(19, 'I', 'BNB0000030', 'Khandu', 'Wangchuk', 'Bhutanese', '12545001266', 17, 'LMBI201', '10333333', 'ADMIN-CDS', 'gmal@gmail.com', 1, '6215478984', NULL, 'Thinleygang, Wangdiphodrang', 161230121720, '2017-11-07 10:36:10', 'Mrs', '2', 'Saving Account'),
	(20, 'J', '', 'Kuenphen', 'Wangchuk', '', '22222222222', 17, 'Khas566', '17339878', 'ADMIN-CDS', 'cheku@rsebl.org.bt', 1, '213213', NULL, 'Thimphu, BHutan', 161230121720, '2017-11-07 10:53:44', '', '', ''),
	(21, 'J', '2566666666', 'Khenphen', 'Lham', '', '66632122222', 5, 'LMKK012', '12455555', 'ADMIN-CDS', 'cheku@rsebl.org.bt', 1, '1255555555', NULL, 'Thimphu, BHutan', 161230121720, '2017-11-07 10:58:03', '', '', 'Saving Account'),
	(22, 'J', '1200122222', 'Pem', 'Sangagggg', '', '20133211556', 17, 'dsfsadd', '77377974', 'ADMIN-CDS', 'cheku@rsebl.org.bt', 1, '3211262222', NULL, 'th', 161230121720, '2017-11-07 11:00:49', '', '', 'Saving Account'),
	(23, 'I', 'Cheku00122', 'Sonam', 'L Wangda', 'Bhutanese', '11504201232', 17, 'MBB3012', '32155455', 'MEMBNB3828', 'nj@hotmail.com', 1, '12547888996', 3, 'Thimphu, BHutan', 161230122041, '2017-11-08 09:14:02', '', '', ''),
	(24, 'I', 'CD12566301', '789', '654', 'Bhutanese', '15487885555', 17, 'NKJH566', '17336568', 'MEMBNB3828', 'cheku@rsebl.org.bt', 1, '13231313544', 3, '2313213321321', 161230122041, '2017-11-08 09:25:18', 'Mr', '2', 'Saving Account'),
	(25, 'J', 'adads', '3', '2', '', '2', 17, '1', '6', 'MEMBNB3828', '32132@yahoo.com', 1, '62', 1, 'C/O T BANK LIMITED, THIMPHU', 161230122041, '2017-11-08 09:50:53', '', '', 'Current Account'),
	(26, 'I', '6666666666', 'Th', 'Lo', 'Bhutanese', '15203214455', 7, 'LKJLK45', '17339000', 'ADMIN-CDS', 'cheku@rsebl.org.bt', 2, '1321311321', NULL, '23132132', 161230121720, '2017-11-08 09:53:04', 'Dasho', '2', 'Current Account'),
	(27, 'I', 'lm001', 'Karma', 'Drupchu', 'Bhutanese', '11504003221', 7, 'KPM1254', '17339665', 'MEMDSB0188', 'h@gmail.com', 1, '12545687422', 1, 'Thimphu, BHutan', 161230122209, '2017-11-27 10:16:32', 'Mr', '2', '');
/*!40000 ALTER TABLE `client_account` ENABLE KEYS */;


-- Dumping structure for table cms2.client_nominee
CREATE TABLE IF NOT EXISTS `client_nominee` (
  `nominee_id` bigint(15) NOT NULL AUTO_INCREMENT,
  `Nominee_name` varchar(50) DEFAULT NULL,
  `Nominee_cid` varchar(15) DEFAULT NULL,
  `Nominee_relation` varchar(15) DEFAULT NULL,
  `ID` varchar(15) DEFAULT NULL,
  `updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nominee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.client_nominee: ~24 rows (approximately)
/*!40000 ALTER TABLE `client_nominee` DISABLE KEYS */;
REPLACE INTO `client_nominee` (`nominee_id`, `Nominee_name`, `Nominee_cid`, `Nominee_relation`, `ID`, `updated`) VALUES
	(1, 'u', '', '', '', '2017-08-29 11:55:59'),
	(2, 'u', '', '', '', '2017-08-29 11:56:04'),
	(3, 'u', '', '', '', '2017-08-29 11:56:04'),
	(4, 'u', '', '', '', '2017-08-29 11:56:04'),
	(5, 'u', '', '', '', '2017-08-29 11:56:05'),
	(6, 'u', '', '', '', '2017-08-29 11:56:29'),
	(7, 'u', '', '', '', '2017-08-29 11:56:34'),
	(8, 'u', '', '', '', '2017-08-29 11:56:35'),
	(9, 'u', '', '', '', '2017-08-29 11:56:36'),
	(10, 'u', '', '', '', '2017-08-29 11:56:37'),
	(11, 'u', '', '', '', '2017-08-29 11:58:19'),
	(12, '', '', '', '11504003892', '2017-08-30 12:02:09'),
	(13, '', '', '', '11504003892', '2017-08-30 12:03:37'),
	(14, 'h', '', '', '11504003830', '2017-11-02 11:53:29'),
	(15, 'c', '11504003821', 'Son', '15121212121', '2017-11-06 15:58:27'),
	(16, 'Cheku DHendup', '11504003828', 'Brother', '11234000000', '2017-11-07 10:00:33'),
	(17, 'BIjoy CHhetri', '11504003820', 'Brother', '11234000000', '2017-11-07 10:00:33'),
	(18, 'Dhendup', '11504003820', 'Son', '12545001266', '2017-11-07 10:36:09'),
	(19, 'Chhh', '23654112222', 'Mother', '12545001266', '2017-11-07 10:36:10'),
	(20, 'kkkkk', '12455788', 'Father', '15487885555', '2017-11-08 09:25:18'),
	(21, 'kjhkj525', '123136', 'Mother', '15487885555', '2017-11-08 09:25:18'),
	(22, '5555555', '1111111111', 'Brother', '15203214455', '2017-11-08 09:53:04'),
	(24, 'Pema Drupchu', '11201333012', 'Mother', '11504003221', '2017-11-27 10:16:32');
/*!40000 ALTER TABLE `client_nominee` ENABLE KEYS */;


-- Dumping structure for table cms2.corporate_announcement
CREATE TABLE IF NOT EXISTS `corporate_announcement` (
  `corp_announcement_id` int(10) NOT NULL AUTO_INCREMENT,
  `symbol_id` bigint(15) DEFAULT NULL,
  `announcement_type` tinyint(3) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `announcement_date` date DEFAULT NULL,
  `rate` decimal(13,2) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL,
  `modifier_username` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`corp_announcement_id`),
  KEY `FK_corporate_announcement_symbol` (`symbol_id`),
  CONSTRAINT `FK_corporate_announcement_symbol` FOREIGN KEY (`symbol_id`) REFERENCES `symbol` (`symbol_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.corporate_announcement: ~18 rows (approximately)
/*!40000 ALTER TABLE `corporate_announcement` DISABLE KEYS */;
REPLACE INTO `corporate_announcement` (`corp_announcement_id`, `symbol_id`, `announcement_type`, `record_date`, `announcement_date`, `rate`, `type`, `status`, `modifier_username`) VALUES
	(1, 3, 1, '2017-12-01', '0000-00-22', 15.00, 'Interim', 0, 'MEMBHUT0151'),
	(2, 2, 1, '2323-00-00', '2017-10-20', 50.00, 'Interim', 0, 'MEMBHUT0151'),
	(3, 6, 1, '2323-00-00', '2017-10-10', 50.00, 'Final', NULL, 'MEMBHUT0151'),
	(4, 6, 1, '2323-00-00', '2017-10-10', 50.00, 'Interim', NULL, 'MEMBHUT0151'),
	(5, 6, 1, '2323-00-00', '2017-10-10', 0.50, 'Interim', NULL, 'MEMBHUT0151'),
	(6, 6, 2, '1212-00-00', '2017-10-11', 19.00, 'Final', 0, 'MEMBHUT0151'),
	(7, 6, 2, '2017-01-30', '2017-10-10', 50.00, 'Interim', NULL, 'MEMBHUT0151'),
	(8, 6, 2, '2017-01-30', '2017-10-10', 100.00, 'Final', NULL, 'MEMBHUT0151'),
	(9, 6, 3, '1990-01-01', '2017-10-10', 15.00, 'Final', 1, 'MEMBHUT0151'),
	(11, 6, 3, '2018-01-30', '2017-10-10', 100.00, 'Final', 0, 'MEMBHUT0151'),
	(12, 6, 3, '2017-01-30', '2017-10-10', 100.00, 'Interim', NULL, 'MEMBHUT0151'),
	(13, 6, 3, '2017-01-30', '2017-10-10', 100.00, 'Final', NULL, 'MEMBHUT0151'),
	(14, 6, 3, '2017-01-30', '2017-10-10', 100.00, 'Final', NULL, 'MEMBHUT0151'),
	(15, 10, 1, '2017-09-02', '2017-08-11', 56.00, 'Interim', 1, 'ADMIN-CDS'),
	(16, 6, 2, '2017-08-31', '2017-08-04', 23.00, 'Final', 1, 'ADMIN-CDS'),
	(17, 9, 2, '2017-09-02', '2017-08-31', 50.00, 'Interim', 1, 'ADMIN-CDS'),
	(18, 8, 2, '2017-08-30', '2017-08-05', 10.00, 'Interim', 1, 'ADMIN-CDS'),
	(19, 2, 1, '2017-10-26', '2017-10-03', 9.00, 'Interim', 1, 'ADMIN-CDS');
/*!40000 ALTER TABLE `corporate_announcement` ENABLE KEYS */;


-- Dumping structure for table cms2.corporate_announcement_status
CREATE TABLE IF NOT EXISTS `corporate_announcement_status` (
  `cas_id` int(10) NOT NULL AUTO_INCREMENT,
  `corp_announcement_id` int(10) DEFAULT '0',
  `status` int(10) DEFAULT '0',
  PRIMARY KEY (`cas_id`),
  KEY `FK_corporate_announcement_status_corporate_announcement` (`corp_announcement_id`),
  CONSTRAINT `FK_corporate_announcement_status_corporate_announcement` FOREIGN KEY (`corp_announcement_id`) REFERENCES `corporate_announcement` (`corp_announcement_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.corporate_announcement_status: ~0 rows (approximately)
/*!40000 ALTER TABLE `corporate_announcement_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `corporate_announcement_status` ENABLE KEYS */;


-- Dumping structure for table cms2.css_clearing_calendar
CREATE TABLE IF NOT EXISTS `css_clearing_calendar` (
  `id` bigint(15) NOT NULL AUTO_INCREMENT,
  `trade_date` date NOT NULL,
  `settlement_date` date NOT NULL,
  `sett_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_css_clearing_calendar_css_settlement_cycle` (`sett_id`),
  CONSTRAINT `FK_css_clearing_calendar_css_settlement_cycle` FOREIGN KEY (`sett_id`) REFERENCES `css_settlement_cycle` (`sett_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.css_clearing_calendar: ~0 rows (approximately)
/*!40000 ALTER TABLE `css_clearing_calendar` DISABLE KEYS */;
/*!40000 ALTER TABLE `css_clearing_calendar` ENABLE KEYS */;


-- Dumping structure for table cms2.css_settlement_cycle
CREATE TABLE IF NOT EXISTS `css_settlement_cycle` (
  `sett_id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `days` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`sett_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.css_settlement_cycle: ~2 rows (approximately)
/*!40000 ALTER TABLE `css_settlement_cycle` DISABLE KEYS */;
REPLACE INTO `css_settlement_cycle` (`sett_id`, `name`, `days`) VALUES
	(1, 'Testing', 10),
	(2, 'tss', 31);
/*!40000 ALTER TABLE `css_settlement_cycle` ENABLE KEYS */;


-- Dumping structure for table cms2.executed_orders
CREATE TABLE IF NOT EXISTS `executed_orders` (
  `exe_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cd_code` varchar(10) DEFAULT NULL,
  `participant_code` varchar(16) DEFAULT NULL,
  `order_exe_price` decimal(13,2) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lot_size_execute` bigint(11) DEFAULT NULL,
  `status` bigint(10) DEFAULT NULL,
  `symbol_id` bigint(15) DEFAULT NULL,
  `side` char(1) DEFAULT NULL,
  `lot_check` bigint(10) DEFAULT NULL,
  `order_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`exe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.executed_orders: ~0 rows (approximately)
/*!40000 ALTER TABLE `executed_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `executed_orders` ENABLE KEYS */;


-- Dumping structure for table cms2.holiday
CREATE TABLE IF NOT EXISTS `holiday` (
  `id` bigint(15) NOT NULL AUTO_INCREMENT,
  `holiday_date` date DEFAULT NULL,
  `hol_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.holiday: ~3 rows (approximately)
/*!40000 ALTER TABLE `holiday` DISABLE KEYS */;
REPLACE INTO `holiday` (`id`, `holiday_date`, `hol_name`) VALUES
	(1, '2000-07-23', 'sdff'),
	(2, '2017-07-12', 'asdf'),
	(3, '2019-08-31', 'dave');
/*!40000 ALTER TABLE `holiday` ENABLE KEYS */;


-- Dumping structure for table cms2.linkuser
CREATE TABLE IF NOT EXISTS `linkuser` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `participant_code` varchar(32) DEFAULT NULL,
  `client_code` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.linkuser: ~2 rows (approximately)
/*!40000 ALTER TABLE `linkuser` DISABLE KEYS */;
REPLACE INTO `linkuser` (`id`, `participant_code`, `client_code`) VALUES
	(5, 'COM', 'MEM77771111'),
	(6, 'MEMBANK', 'MEMBANK1111');
/*!40000 ALTER TABLE `linkuser` ENABLE KEYS */;


-- Dumping structure for table cms2.market_price
CREATE TABLE IF NOT EXISTS `market_price` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `symbol_id` bigint(15) DEFAULT '0',
  `market_price` decimal(13,2) DEFAULT '0.00',
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_market_price_symbol` (`symbol_id`),
  CONSTRAINT `FK_market_price_symbol` FOREIGN KEY (`symbol_id`) REFERENCES `symbol` (`symbol_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.market_price: ~0 rows (approximately)
/*!40000 ALTER TABLE `market_price` DISABLE KEYS */;
REPLACE INTO `market_price` (`id`, `symbol_id`, `market_price`, `date`) VALUES
	(1, 6, 20.00, '2017-11-14 12:15:14');
/*!40000 ALTER TABLE `market_price` ENABLE KEYS */;


-- Dumping structure for table cms2.occupation
CREATE TABLE IF NOT EXISTS `occupation` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.occupation: ~2 rows (approximately)
/*!40000 ALTER TABLE `occupation` DISABLE KEYS */;
REPLACE INTO `occupation` (`id`, `name`) VALUES
	(1, 'FARMER1'),
	(2, 'civil servant g');
/*!40000 ALTER TABLE `occupation` ENABLE KEYS */;


-- Dumping structure for table cms2.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` bigint(15) NOT NULL AUTO_INCREMENT,
  `symbol_id` bigint(15) DEFAULT '0',
  `cd_code` varchar(15) DEFAULT '0',
  `participant_code` varchar(16) DEFAULT '0',
  `order_size` int(10) DEFAULT '0',
  `order_entry` varchar(16) DEFAULT '0',
  `buy_vol` int(10) DEFAULT NULL,
  `flag_id` bigint(10) DEFAULT NULL,
  `sell_vol` int(10) DEFAULT NULL,
  `price` decimal(13,2) DEFAULT NULL,
  `side` char(1) DEFAULT NULL,
  `commis_amt` decimal(13,2) DEFAULT NULL,
  `exe_vol` bigint(13) DEFAULT NULL,
  `exe_price` decimal(13,2) DEFAULT NULL,
  `lot_check` bigint(13) DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `FK_order_client_account` (`cd_code`),
  KEY `Index 3` (`symbol_id`),
  CONSTRAINT `FK_order_symbol` FOREIGN KEY (`symbol_id`) REFERENCES `symbol` (`symbol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.orders: ~13 rows (approximately)
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
REPLACE INTO `orders` (`order_id`, `symbol_id`, `cd_code`, `participant_code`, `order_size`, `order_entry`, `buy_vol`, `flag_id`, `sell_vol`, `price`, `side`, `commis_amt`, `exe_vol`, `exe_price`, `lot_check`, `order_date`) VALUES
	(1, 6, '1232323', 'MEMBNB', 12, '0', 12, NULL, NULL, 10.00, 'B', NULL, NULL, NULL, NULL, '2017-11-20 13:01:55'),
	(2, 6, '1232323', 'MEMBNB', 12000000, '0', 12000000, 171126053829, NULL, 10.00, 'B', 12000000.00, NULL, NULL, NULL, '2017-11-20 13:01:56'),
	(4, 6, '1232323', 'MEMBNB', 12, '0', 0, NULL, 12, 14.50, 'S', NULL, NULL, NULL, NULL, '2017-11-20 13:01:58'),
	(13, 5, 'CD12566301', 'MEMBNB', 100, 'MEMBNB3828', NULL, 171126053825, 100, 18.00, 'S', 100.00, NULL, NULL, NULL, '2017-11-26 17:38:25'),
	(15, 5, 'CD12566301', 'MEMBNB', 100, 'MEMBNB3828', NULL, 171126053825, 100, 16.00, 'S', 100.00, NULL, NULL, NULL, '2017-11-26 17:38:25'),
	(17, 6, '1232323', 'MEMBNB', 12, '0', 12, NULL, NULL, 10.00, 'B', NULL, NULL, NULL, NULL, '2017-11-20 13:01:55'),
	(18, 6, '1232323', 'MEMBNB', 12, '0', 12, NULL, NULL, 10.00, 'B', NULL, NULL, NULL, NULL, '2017-11-20 13:01:55'),
	(19, 5, '1232323', 'MEMBNB', 12, '0', 12, NULL, NULL, 18.00, 'B', NULL, NULL, NULL, NULL, '2017-11-20 13:01:55'),
	(20, 5, '1232323', 'MEMBNB', 12, '0', 12, NULL, NULL, 10.00, 'B', NULL, NULL, NULL, NULL, '2017-11-20 13:01:55');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;


-- Dumping structure for table cms2.price_table
CREATE TABLE IF NOT EXISTS `price_table` (
  `pid` bigint(20) NOT NULL AUTO_INCREMENT,
  `prices` decimal(13,2) DEFAULT NULL,
  `volume_buy` bigint(20) DEFAULT NULL,
  `volume_sell` bigint(20) DEFAULT NULL,
  `difference` bigint(20) DEFAULT NULL,
  `symbol_id` bigint(15) DEFAULT NULL,
  `diff_chk` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.price_table: ~0 rows (approximately)
/*!40000 ALTER TABLE `price_table` DISABLE KEYS */;
/*!40000 ALTER TABLE `price_table` ENABLE KEYS */;


-- Dumping structure for table cms2.spot_date_holding
CREATE TABLE IF NOT EXISTS `spot_date_holding` (
  `sdh_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `symbol_id` bigint(15) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `corp_announcement_id` int(10) DEFAULT NULL,
  `client_id` int(10) DEFAULT NULL,
  `volume` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`sdh_id`),
  KEY `client_id` (`client_id`),
  KEY `FK_spot_date_holding_corporate_announcement` (`corp_announcement_id`),
  KEY `FK_spot_date_holding_symbol` (`symbol_id`),
  CONSTRAINT `FK_spot_date_holding_corporate_announcement` FOREIGN KEY (`corp_announcement_id`) REFERENCES `corporate_announcement` (`corp_announcement_id`) ON UPDATE NO ACTION,
  CONSTRAINT `FK_spot_date_holding_symbol` FOREIGN KEY (`symbol_id`) REFERENCES `symbol` (`symbol_id`) ON UPDATE NO ACTION,
  CONSTRAINT `dividend_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client_account` (`client_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.spot_date_holding: ~0 rows (approximately)
/*!40000 ALTER TABLE `spot_date_holding` DISABLE KEYS */;
REPLACE INTO `spot_date_holding` (`sdh_id`, `symbol_id`, `record_date`, `corp_announcement_id`, `client_id`, `volume`) VALUES
	(1, 6, '2017-01-30', 7, 8, 10000);
/*!40000 ALTER TABLE `spot_date_holding` ENABLE KEYS */;


-- Dumping structure for table cms2.symbol
CREATE TABLE IF NOT EXISTS `symbol` (
  `symbol_id` bigint(15) NOT NULL AUTO_INCREMENT,
  `board_lot` bigint(11) NOT NULL DEFAULT '0',
  `paid_up_shares` decimal(15,2) NOT NULL DEFAULT '0.00',
  `date_of_listing` date DEFAULT NULL,
  `date_of_est` date DEFAULT NULL,
  `isin` bigint(15) DEFAULT NULL,
  `symbol` varchar(5) DEFAULT NULL,
  `name` varchar(62) DEFAULT NULL,
  `sector` varchar(32) DEFAULT NULL,
  `face_value` decimal(13,2) DEFAULT NULL,
  `premium_value` decimal(13,2) DEFAULT NULL,
  `security_type` varchar(32) DEFAULT NULL,
  `status` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`symbol_id`),
  UNIQUE KEY `symbol` (`symbol`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.symbol: ~7 rows (approximately)
/*!40000 ALTER TABLE `symbol` DISABLE KEYS */;
REPLACE INTO `symbol` (`symbol_id`, `board_lot`, `paid_up_shares`, `date_of_listing`, `date_of_est`, `isin`, `symbol`, `name`, `sector`, `face_value`, `premium_value`, `security_type`, `status`) VALUES
	(2, 33, 3333.00, '0000-00-00', '0000-00-00', 2222, 'asdf', 'asdf', 'Manufacturing', 323.00, 33.00, 'OS', 2),
	(3, 10, 500000.00, '0000-00-00', '0000-00-00', 9223372036854775807, 't', 'TASHI', 'Banking', 10.00, 460.00, 'OS', 1),
	(5, 10, 888888888888.00, '0000-00-00', '0000-00-00', 122222222, 'PCAL', 'Penden Cement Authority', 'Manufacturing', 10.00, 50.00, 'OS', 1),
	(6, 1, 2000000000.00, '0000-00-00', '0000-00-00', 9223372036854775807, 'BNBL', 'Bhutan National Bank', 'Banking', 10.00, 100.00, 'OS', 1),
	(8, 31, 9999999999999.99, '0000-00-00', '0000-00-00', 0, 'PTL', 'Phuntsho Tashi Ltd', 'Mining', 10000.00, 0.00, 'OS', 1),
	(9, 10, 500000.00, '0000-00-00', '0000-00-00', 1111111110, 'RICB', 'royal insurance corporation of bhutan', 'Insurance', 10.00, 65.00, 'OS', 1),
	(10, 22222222222, 0.00, '0000-00-00', '0000-00-00', 0, '22222', '22222222222222222222222222222222', 'Manufacturing', 99999999999.99, 2.00, 'OS', 1);
/*!40000 ALTER TABLE `symbol` ENABLE KEYS */;


-- Dumping structure for table cms2.tbldzongkhag
CREATE TABLE IF NOT EXISTS `tbldzongkhag` (
  `DzongkhagID` tinyint(3) NOT NULL AUTO_INCREMENT,
  `DzongkhagName` varchar(50) DEFAULT NULL,
  `CountryCode` int(11) DEFAULT NULL,
  PRIMARY KEY (`DzongkhagID`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

-- Dumping data for table cms2.tbldzongkhag: ~20 rows (approximately)
/*!40000 ALTER TABLE `tbldzongkhag` DISABLE KEYS */;
REPLACE INTO `tbldzongkhag` (`DzongkhagID`, `DzongkhagName`, `CountryCode`) VALUES
	(1, 'Chhukha', 1),
	(2, 'Haa', 1),
	(3, 'Paro', 1),
	(4, 'Samtse', 1),
	(5, 'Thimphu', 1),
	(6, 'Dagana', 1),
	(7, 'Gasa', 1),
	(8, 'Punakha', 1),
	(9, 'Tsirang', 1),
	(10, 'Wanduephodrang', 1),
	(11, 'Lhuentse', 1),
	(12, 'Mongar', 1),
	(13, 'Pemagatshel', 1),
	(14, 'Zhemgang', 1),
	(15, 'Trashigang', 1),
	(16, 'Trashi Yangtse', 1),
	(17, 'Bumthang', 1),
	(18, 'Sarpang', 1),
	(19, 'Trongsa', 1),
	(20, 'Samdrup Jongkhar', 1);
/*!40000 ALTER TABLE `tbldzongkhag` ENABLE KEYS */;


-- Dumping structure for table cms2.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(320) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `participant_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cid` bigint(12) DEFAULT NULL,
  `address` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `phone` bigint(11) NOT NULL,
  `status` bigint(11) NOT NULL,
  `role_id` bigint(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_check` bigint(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username_2` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table cms2.users: ~28 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
REPLACE INTO `users` (`user_id`, `name`, `username`, `email`, `password`, `participant_code`, `cid`, `address`, `phone`, `status`, `role_id`, `created_at`, `log_check`) VALUES
	(1, 'BIJOY', 'ADMIN', 'test@test.com', '21232f297a57a5a743894a0e4a801fc3', 'MEM777', NULL, 'RSEBL', 17871852, 1, 1, '2016-09-12 12:31:51', 0),
	(2, 'BIJOY', 'ADMIN-BBO', 'test@test.com', '21232f297a57a5a743894a0e4a801fc3', 'MEM7777', NULL, 'RSEBL', 1231456, 1, 2, '2016-09-12 14:47:19', 0),
	(3, 'BIJOY', 'ADMIN-CDS', 'test@test.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', NULL, 'RSEBL', 1211111, 1, 3, '2016-09-12 14:48:18', 0),
	(6, 'MEM009', 'MEMRICB1', '', '21232f297a57a5a743894a0e4a801fc3', 'MEMRICB', 11111111, 'asdfsdf', 0, 1, 2, '2016-12-29 12:04:04', 0),
	(12, 'TENZIN RABGAY', 'MEMT BA0155', 'tenzin@yahoo.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMT BA', 11007000155, 'C/O T BANK LIMITED, THIMPHU', 17626573, 1, 1, '2017-01-03 11:05:13', 0),
	(13, 'TASHI YEZER', 'MEMBANK0150', 'tyezer@rsebl.org.bt', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 11007000150, 'C/O BANK OF BHUTAN, THIMPHU', 17626573, 1, 3, '2017-01-03 14:21:23', 0),
	(14, 'NAWANG LHENDUP', 'MEMBNB0153', 'NAWANG@YAHOO.COM', '21232f297a57a5a743894a0e4a801fc3', 'MEMBNB', 11007000153, 'C/O BNB, THIMPHU', 17626573, 1, 3, '2017-01-04 11:37:00', 0),
	(15, 'DAWA DAKPA', 'MEMBHUT0151', 'DAWA@YAHOO.COM', '21232f297a57a5a743894a0e4a801fc3', 'MEMBHUT', 11007000151, 'C/O BHUTAN DEVELOPMENT BANK LTD', 17626573, 1, 3, '2017-01-04 11:39:45', 0),
	(16, 'KUENZANG CHODEN', 'MEMROYA0152', 'kuenzang@rsebl.org.bt', '21232f297a57a5a743894a0e4a801fc3', 'MEMROYA', 11007000152, 'C/O RICB, THIMPHU', 17626573, 1, 3, '2017-01-04 11:40:29', 0),
	(17, 'KHANDU WANGMO', 'MEMDSB0154', 'khandu@yahoo.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMDSB', 11007000154, 'C/O DSB, THIMPHU', 17626573, 1, 3, '2017-01-04 11:45:16', 0),
	(18, 'PEMA WANGDI', 'MEMBANK0157', 'pema@yahoo.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 11007000157, 'C/O BANK OF BHUTAN, THIMPHU', 17626573, 1, 2, '2017-01-05 11:31:51', 0),
	(19, 'tAAHSIyANGDON', 'DD9999', 'tyezer@rsebl.org.bt', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 444, 'Tashigang, Nanong, Wongchilo, P/gatshel', 9223372036854775807, 2, 4, '2017-01-05 11:42:52', NULL),
	(20, 'UGYEN WANGCHUK', 'MEMDSB0188', 'UGYEN@YAHOO.COM', '21232f297a57a5a743894a0e4a801fc3', 'MEMDSB', 11007000188, 'C/O DSB, THIMPHU', 17626573, 1, 2, '2017-01-05 11:46:35', 0),
	(21, 'Wangdi Dratshang', 'CD23333333', 'dorji@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 0, 'Thinleygang, Wangdiphodrang', 0, 2, 4, '2017-01-05 11:47:11', NULL),
	(22, 'KARMAPEMA', 'U000000001', 'KARMA', '21232f297a57a5a743894a0e4a801fc3', 'MEMDSB', 11007000189, 'C/O DSB, THIMPHU', 17626573, 2, 4, '2017-01-05 11:49:38', NULL),
	(23, 'YANGSELDEMA', 'U000000002', 'YANGSEL@YAHOO.COM', '21232f297a57a5a743894a0e4a801fc3', 'MEMDSB', 1100700090, 'C/O DSB, THIMPHU', 17626573, 2, 4, '2017-01-05 11:51:22', NULL),
	(24, 'tyyezer', '0000000000', 'tyezer.TY@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 11111111111, 'Nanong, Wamrong, Trashigang', 111111111111111111, 2, 4, '2017-01-05 11:52:17', NULL),
	(25, 'Kuenzang Cho3en', 'MEMROYA5454', 'kuenzang@rsebl.org.bt', '21232f297a57a5a743894a0e4a801fc3', 'MEMROYA', 11410005454, 'thimphu', 17117111, 1, 2, '2017-01-05 11:57:34', 1),
	(26, 'Tashiding  Welfare Association', 'cd9899999', 'tyezer.TY@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 0, 'Khomang, drupkang, Tashigang', 0, 2, 4, '2017-01-05 12:15:15', NULL),
	(27, 'yyoo', 'hhhhhhhhhh', 'tyezer.TY@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 5555555555, 'Thinleygang, Wangdiphodrang', 66666666666, 2, 4, '2017-01-05 12:30:23', NULL),
	(28, 'ddddddddddddddd', 'dddddd', 'tyezer.ty@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 33333333, '', 66666, 2, 4, '2017-01-05 12:31:16', NULL),
	(29, 'lkjajslkj', 'MEMBHUT21', 'a@a.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBHUT', 121212121, 'asdfwd', 12, 1, 1, '2017-01-16 10:55:34', 1),
	(30, 'TEST', 'MEMBANK4545', '', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 54545454545, 'test', 45454, 1, 4, '2017-01-30 12:50:19', 0),
	(31, 'test', 'MEMBANK1111', 'bijoytechnocrat@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBANK', 11111111111, 'test', 121212, 1, 4, '2017-08-01 11:04:29', 0),
	(33, 'ChekuDhendup', 'bbb1234567', 'seventhcheku100nos@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMDSB', 11504003828, 'Thimphu, BHutan', 17339889, 2, 4, '2017-09-28 13:55:35', NULL),
	(34, 'Karma', 'BNB0000078', 'cheku@rsebl.org.bt', '21232f297a57a5a743894a0e4a801fc3', 'MEMDSB', 111, 'Thimphu, BHutan', 17339878, 2, 4, '2017-09-28 13:58:36', NULL),
	(35, 'Thinkey', 'KIM01235', 'cheku@rsebl.org.bt', '21232f297a57a5a743894a0e4a801fc3', 'MEMDSB', 12546, 'Thimphu, BHutan', 17638064, 2, 4, '2017-09-28 14:08:29', NULL),
	(36, 'Cheku', 'MEMBNB3828', 'cheku@rsebl.org.bt', '21232f297a57a5a743894a0e4a801fc3', 'MEMBNB', 11504003828, 'Thimphu, BHutan', 17339889, 1, 2, '2017-11-07 12:43:51', 0),
	(37, 'Tandin', 'MEMBNB1838', 'tandin@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBNB', 11501001838, 'Thimphu', 17339002, 1, 2, '2017-11-27 12:14:16', 0),
	(38, 'Tshering', 'MEMBOB3810', 'tshering@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMBOB', 11504003810, 'Thimphu, BHutan', 17339860, 1, 2, '2017-11-27 12:21:40', 1),
	(39, 'Thinley', 'MEMROYA3220', 'thinley@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'MEMROYA', 11504003220, 'Thimphu, BHutan', 17332012, 1, 2, '2017-11-27 12:22:58', 0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
