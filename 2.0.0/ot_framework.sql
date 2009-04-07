-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: mysql02.unity.ncsu.edu
-- Generation Time: Jan 28, 2009 at 01:14 PM
-- Server version: 4.1.19
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ot_sandbox`
--

-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_account_attributes`
--

CREATE TABLE `ot_tbl_account_attributes` (
  `accountId` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`accountId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ot_tbl_account_attributes`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_api_log`
--

CREATE TABLE `ot_tbl_api_log` (
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
-- Dumping data for table `ot_tbl_api_log`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_image`
--

CREATE TABLE `ot_tbl_image` (
  `imageId` int(10) unsigned NOT NULL auto_increment,
  `source` longblob NOT NULL,
  `alt` varchar(64) NOT NULL default '',
  `name` varchar(64) NOT NULL default '',
  `contentType` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`imageId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=39 ;

--
-- Dumping data for table `ot_tbl_image`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_account`
--

CREATE TABLE `ot_tbl_ot_account` (
  `accountId` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(64) NOT NULL default '',
  `realm` varchar(64) NOT NULL default '',
  `password` varchar(128) NOT NULL default '',
  `apiCode` varchar(255) NOT NULL default '',
  `role` int(10) unsigned NOT NULL default '0',
  `emailAddress` varchar(255) NOT NULL default '',
  `firstName` varchar(64) NOT NULL default '',
  `lastName` varchar(64) NOT NULL default '',
  `timezone` varchar(32) NOT NULL default '',
  `lastLogin` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`accountId`),
  UNIQUE KEY `username` (`username`,`realm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `ot_tbl_ot_account`
--

INSERT INTO `ot_tbl_ot_account` VALUES(1, 'admin', 'local', '21232f297a57a5a743894a0e4a801fc3', '', 2, 'admin@admin.com', 'Admin', 'User', 'America/New_York', 0);

-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_bug`
--

CREATE TABLE `ot_tbl_ot_bug` (
  `bugId` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `submitDt` int(10) unsigned NOT NULL default '0',
  `reproducibility` enum('always','sometimes','never') NOT NULL default 'always',
  `severity` enum('minor','major','crash') NOT NULL default 'minor',
  `priority` enum('low','medium','high','critical') NOT NULL default 'low',
  `status` enum('new','ignore','escalated','fixed') NOT NULL default 'new',
  PRIMARY KEY  (`bugId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `ot_tbl_ot_bug`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_bug_text`
--

CREATE TABLE `ot_tbl_ot_bug_text` (
  `bugTextId` int(10) unsigned NOT NULL auto_increment,
  `bugId` int(10) unsigned NOT NULL default '0',
  `accountId` int(10) unsigned NOT NULL default '0',
  `postDt` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  PRIMARY KEY  (`bugTextId`),
  KEY `bugId` (`bugId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `ot_tbl_ot_bug_text`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_cron_status`
--

CREATE TABLE `ot_tbl_ot_cron_status` (
  `name` varchar(255) NOT NULL default '',
  `status` enum('enabled','disabled') NOT NULL default 'enabled',
  `lastRunDt` int(11) NOT NULL default '0',
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ot_tbl_ot_cron_status`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_custom_attribute`
--

CREATE TABLE `ot_tbl_ot_custom_attribute` (
  `attributeId` int(10) unsigned NOT NULL auto_increment,
  `objectId` varchar(64) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `type` enum('text','textarea','radio','checkbox','select','ranking') character set utf8 NOT NULL default 'text',
  `options` text NOT NULL,
  `required` binary(1) NOT NULL default '',
  `direction` enum('vertical','horizontal') NOT NULL default 'vertical',
  `order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`attributeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `ot_tbl_ot_custom_attribute`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_custom_attribute_value`
--

CREATE TABLE `ot_tbl_ot_custom_attribute_value` (
  `objectId` varchar(64) NOT NULL default '',
  `parentId` varchar(255) NOT NULL default '',
  `attributeId` int(11) NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`objectId`,`parentId`,`attributeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ot_tbl_ot_custom_attribute_value`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_email_queue`
--

CREATE TABLE `ot_tbl_ot_email_queue` (
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
-- Dumping data for table `ot_tbl_ot_email_queue`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_log`
--

CREATE TABLE `ot_tbl_ot_log` (
  `logId` int(10) unsigned NOT NULL auto_increment,
  `accountId` int(10) unsigned NOT NULL default '0',
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
  KEY `userId` (`accountId`),
  KEY `attributeName` (`attributeName`),
  KEY `attributeId` (`attributeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=452 ;

--
-- Dumping data for table `ot_tbl_ot_log`
--


-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_nav`
--

CREATE TABLE `ot_tbl_ot_nav` (
  `id` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `display` varchar(255) NOT NULL default '',
  `module` varchar(255) NOT NULL default '',
  `controller` varchar(255) NOT NULL default '',
  `action` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `target` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ot_tbl_ot_nav`
--

INSERT INTO `ot_tbl_ot_nav` VALUES(1, 0, 'Home', 'default', 'index', '', 'index/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(2, 0, 'Admin', 'admin', 'index', 'index', '', '');
INSERT INTO `ot_tbl_ot_nav` VALUES(3, 2, 'Configuration', 'admin', 'index', 'index', '', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(4, 3, 'App Config', 'admin', 'config', 'index', 'admin/config/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(5, 3, 'Debug Mode', 'admin', 'debug', 'index', 'admin/debug/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(6, 3, 'Maintenance Mode', 'admin', 'maintenance', 'index', 'admin/maintenance', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(7, 3, 'Navigation Editor', 'admin', 'nav', 'index', 'admin/nav/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(8, 2, 'App Triggers', 'admin', 'trigger', 'index', 'admin/trigger/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(9, 2, 'Bug Reports', 'admin', 'bug', '', 'admin/bug', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(10, 2, 'Caching', 'admin', 'cache', '', 'admin/cache', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(11, 2, 'Cron Jobs', 'admin', 'cron', 'index', 'admin/cron/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(12, 2, 'Custom Fields', 'admin', 'custom', 'index', 'admin/custom/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(13, 2, 'Database Backup', 'admin', 'backup', '', 'admin/backup', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(14, 2, 'Email Queue', 'admin', 'emailqueue', 'index', 'admin/emailqueue/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(15, 2, 'Logs', 'admin', 'log', 'index', 'admin/log/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(16, 2, 'Users', 'account', 'index', 'all', 'account/index/all', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(17, 2, 'User Access Roles', 'admin', 'acl', 'index', 'admin/acl/index', '_self');
INSERT INTO `ot_tbl_ot_nav` VALUES(18, 2, 'Version Information', 'admin', 'index', 'index', 'admin/index/index', '_self');

-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_role`
--

CREATE TABLE `ot_tbl_ot_role` (
  `roleId` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `inheritRoleId` int(10) unsigned NOT NULL default '0',
  `editable` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `ot_tbl_ot_role`
--

INSERT INTO `ot_tbl_ot_role` VALUES(1, 'guest', 0, 1);
INSERT INTO `ot_tbl_ot_role` VALUES(2, 'administrator', 0, 0);
INSERT INTO `ot_tbl_ot_role` VALUES(3, 'oit_ot_staff', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_role_rule`
--

CREATE TABLE `ot_tbl_ot_role_rule` (
  `ruleId` int(10) unsigned NOT NULL auto_increment,
  `roleId` int(11) NOT NULL default '0',
  `type` enum('allow','deny') NOT NULL default 'allow',
  `resource` varchar(64) NOT NULL default '',
  `privilege` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`ruleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;

--
-- Dumping data for table `ot_tbl_ot_role_rule`
--

INSERT INTO `ot_tbl_ot_role_rule` VALUES(8, 2, 'allow', '*', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(9, 3, 'allow', '*', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(33, 1, 'allow', 'default_index', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(34, 1, 'allow', 'error_error', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(35, 1, 'allow', 'login_index', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(36, 1, 'allow', 'remote_index', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(46, 14, 'allow', 'admin_acl', 'index');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(47, 14, 'allow', 'admin_bug', 'index');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(48, 14, 'allow', 'admin_nav', 'index');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(49, 14, 'allow', 'admin_translate', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(50, 14, 'allow', 'default_index', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(51, 14, 'allow', 'error_error', '*');
INSERT INTO `ot_tbl_ot_role_rule` VALUES(52, 14, 'allow', 'login_index', '*');

-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_trigger_action`
--

CREATE TABLE `ot_tbl_ot_trigger_action` (
  `triggerActionId` int(10) unsigned NOT NULL auto_increment,
  `triggerId` varchar(64) NOT NULL default '',
  `name` varchar(64) NOT NULL default '',
  `helper` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`triggerActionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `ot_tbl_ot_trigger_action`
--

INSERT INTO `ot_tbl_ot_trigger_action` VALUES(14, 'Login_Index_Signup', 'Signup for an account', 'Ot_Trigger_Plugin_Email');
INSERT INTO `ot_tbl_ot_trigger_action` VALUES(15, 'Login_Index_Forgot', 'User forgot password', 'Ot_Trigger_Plugin_Email');
INSERT INTO `ot_tbl_ot_trigger_action` VALUES(16, 'Admin_Account_Create_Password', 'Admin created account', 'Ot_Trigger_Plugin_Email');
INSERT INTO `ot_tbl_ot_trigger_action` VALUES(17, 'Admin_Account_Create_NoPassword', 'When a WRAP account gets created', 'Ot_Trigger_Plugin_Email');

-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_trigger_helper_email`
--

CREATE TABLE `ot_tbl_ot_trigger_helper_email` (
  `triggerActionId` int(11) NOT NULL default '0',
  `to` varchar(255) NOT NULL default '',
  `from` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  PRIMARY KEY  (`triggerActionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ot_tbl_ot_trigger_helper_email`
--

INSERT INTO `ot_tbl_ot_trigger_helper_email` VALUES(14, '[[emailAddress]]', 'webapps_admin@ncsu.edu', 'Thanks for signing up!', 'Hey [[firstName]]!  Welcome to The System.\r\n\r\nYour user id:  [[username]]\r\nYou password: [[password]]');
INSERT INTO `ot_tbl_ot_trigger_helper_email` VALUES(15, '[[emailAddress]]', 'admin@webapps.ncsu.edu', 'Your password has been reset', 'Thanks [[firstName]] [[lastName]]\r\n\r\nYou password for [[username]] has been reset.  Go here [[resetUrl]] to change your password.');
INSERT INTO `ot_tbl_ot_trigger_helper_email` VALUES(16, '[[emailAddress]]', 'admin@webapps.ncsu.edu', 'You''ve been given an account', 'Hey [[firstName]], You''ve been given a(n) [[role]] account!\r\n\r\n[[username]]\r\n[[password]]');
INSERT INTO `ot_tbl_ot_trigger_helper_email` VALUES(17, '[[emailAddress]]', 'admin@webapps.ncsu.edu', 'You''ve got a new account!', 'Hey [[firstName]] [[lastName]]\r\n\r\nYou''ve been given a new [[role]] [[loginMethod]] account.\r\n\r\nYour username is [[username]]');

-- --------------------------------------------------------

--
-- Table structure for table `ot_tbl_ot_trigger_helper_emailqueue`
--

CREATE TABLE `ot_tbl_ot_trigger_helper_emailqueue` (
  `triggerActionId` int(11) NOT NULL default '0',
  `to` varchar(255) NOT NULL default '',
  `from` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  PRIMARY KEY  (`triggerActionId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ot_tbl_ot_trigger_helper_emailqueue`
--

