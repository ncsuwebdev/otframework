-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: mysql02.unity.ncsu.edu
-- Generation Time: May 15, 2008 at 11:36 AM
-- Server version: 4.1.19
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `oitpeople`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_account_attributes`
--

CREATE TABLE `tbl_account_attributes` (
  `userId` varchar(64) NOT NULL default '',
  `age` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_account_attributes`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_api_log`
--

CREATE TABLE `tbl_api_log` (
  `apiLogId` int(10) unsigned NOT NULL auto_increment,
  `userId` varchar(16) NOT NULL default '',
  `function` varchar(64) NOT NULL default '',
  `args` text NOT NULL,
  `message` varchar(255) NOT NULL default '',
  `priority` varchar(16) NOT NULL default '',
  `priorityName` varchar(64) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`apiLogId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `tbl_api_log`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_image`
--

CREATE TABLE `tbl_image` (
  `imageId` int(10) unsigned NOT NULL auto_increment,
  `source` longblob NOT NULL,
  `alt` varchar(64) NOT NULL default '',
  `name` varchar(64) NOT NULL default '',
  `contentType` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`imageId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=39 ;

--
-- Dumping data for table `tbl_image`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_account`
--

CREATE TABLE `tbl_ot_account` (
  `userId` varchar(64) NOT NULL default '',
  `emailAddress` varchar(255) NOT NULL default '',
  `firstName` varchar(64) NOT NULL default '',
  `lastName` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_ot_account`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_api_code`
--

CREATE TABLE `tbl_ot_api_code` (
  `userId` varchar(16) NOT NULL default '',
  `code` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_ot_api_code`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_authz_local`
--

CREATE TABLE `tbl_ot_authz_local` (
  `userId` varchar(64) NOT NULL default '',
  `role` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_ot_authz_local`
--

INSERT INTO `tbl_ot_authz_local` VALUES('itappdev@wrap', 'administrator');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_auth_local`
--

CREATE TABLE `tbl_ot_auth_local` (
  `userId` varchar(64) NOT NULL default '',
  `password` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_ot_auth_local`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_bug`
--

CREATE TABLE `tbl_ot_bug` (
  `bugId` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `submitDt` int(10) unsigned NOT NULL default '0',
  `reproducibility` enum('always','sometimes','never') NOT NULL default 'always',
  `severity` enum('minor','major','crash') NOT NULL default 'minor',
  `priority` enum('low','medium','high','critical') NOT NULL default 'low',
  `status` enum('new','ignore','escalated','fixed') NOT NULL default 'new',
  PRIMARY KEY  (`bugId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `tbl_ot_bug`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_bug_text`
--

CREATE TABLE `tbl_ot_bug_text` (
  `bugTextId` int(10) unsigned NOT NULL auto_increment,
  `bugId` int(10) unsigned NOT NULL default '0',
  `userId` varchar(32) NOT NULL default '',
  `postDt` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  PRIMARY KEY  (`bugTextId`),
  KEY `bugId` (`bugId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `tbl_ot_bug_text`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_cron_status`
--

CREATE TABLE `tbl_ot_cron_status` (
  `path` varchar(255) NOT NULL default '',
  `schedule` varchar(255) NOT NULL default '',
  `status` enum('enabled','disabled') NOT NULL default 'enabled',
  `lastRunDt` int(11) NOT NULL default '0',
  PRIMARY KEY  (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_ot_cron_status`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_custom_attribute`
--

CREATE TABLE `tbl_ot_custom_attribute` (
  `attributeId` int(10) unsigned NOT NULL auto_increment,
  `objectId` varchar(64) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `type` enum('text','textarea','radio','checkbox','select','ranking') character set utf8 NOT NULL default 'text',
  `options` text NOT NULL,
  `required` binary(1) NOT NULL default '',
  `direction` enum('vertical','horizontal') NOT NULL default 'vertical',
  `order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`attributeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `tbl_ot_custom_attribute`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_custom_attribute_value`
--

CREATE TABLE `tbl_ot_custom_attribute_value` (
  `objectId` varchar(64) NOT NULL default '',
  `parentId` varchar(255) NOT NULL default '',
  `attributeId` int(11) NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`objectId`,`parentId`,`attributeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_ot_custom_attribute_value`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_email_queue`
--

CREATE TABLE `tbl_ot_email_queue` (
  `queueId` int(10) unsigned NOT NULL auto_increment,
  `attributeName` varchar(128) NOT NULL default '',
  `attributeId` int(10) unsigned NOT NULL default '0',
  `zendMailObject` blob NOT NULL,
  `queueDt` int(10) unsigned NOT NULL default '0',
  `sentDt` int(10) unsigned NOT NULL default '0',
  `status` enum('waiting','sent','error') NOT NULL default 'waiting',
  PRIMARY KEY  (`queueId`),
  KEY `attributeName` (`attributeName`),
  KEY `attributeId` (`attributeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `tbl_ot_email_queue`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_log`
--

CREATE TABLE `tbl_ot_log` (
  `logId` int(10) unsigned NOT NULL auto_increment,
  `userId` varchar(64) character set utf8 NOT NULL default '',
  `role` varchar(128) character set utf8 NOT NULL default '',
  `request` varchar(255) character set utf8 NOT NULL default '',
  `sid` varchar(128) character set utf8 NOT NULL default '',
  `timestamp` int(10) unsigned NOT NULL default '0',
  `message` varchar(255) character set utf8 NOT NULL default '',
  `priority` int(10) unsigned NOT NULL default '0',
  `priorityName` varchar(64) character set utf8 NOT NULL default '',
  `attributeName` varchar(128) character set utf8 NOT NULL default '',
  `attributeId` varchar(64) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`logId`),
  KEY `userId` (`userId`),
  KEY `attributeName` (`attributeName`),
  KEY `attributeId` (`attributeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=284 ;

--
-- Dumping data for table `tbl_ot_log`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_trigger_action`
--

CREATE TABLE `tbl_ot_trigger_action` (
  `triggerActionId` int(10) unsigned NOT NULL auto_increment,
  `triggerId` varchar(64) NOT NULL default '',
  `name` varchar(64) NOT NULL default '',
  `helper` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`triggerActionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `tbl_ot_trigger_action`
--


-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_trigger_helper_emailqueue`
--

CREATE TABLE `tbl_ot_trigger_helper_emailqueue` (
  `triggerActionId` int(11) NOT NULL default '0',
  `to` varchar(255) NOT NULL default '',
  `from` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  PRIMARY KEY  (`triggerActionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_ot_trigger_helper_emailqueue`
--

