/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : bod_core

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2015-04-27 15:50:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for files_of_member
-- ----------------------------
DROP TABLE IF EXISTS `files_of_member`;
CREATE TABLE `files_of_member` (
  `file` int(10) unsigned NOT NULL COMMENT 'File of member.',
  `member` int(10) unsigned NOT NULL COMMENT 'Member who owns the file',
  `count_view` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'View count of file within member profile',
  `date_added` datetime NOT NULL COMMENT 'Date when the file is attached to member.',
  PRIMARY KEY (`file`,`member`),
  UNIQUE KEY `idx_u_files_of_member` (`file`,`member`) USING BTREE,
  KEY `idx_n_files_of_member_date_added` (`date_added`) USING BTREE,
  KEY `idx_f_files_of_member_member` (`member`) USING BTREE,
  KEY `idx_f_files_of_member_file` (`file`) USING BTREE,
  CONSTRAINT `idx_f_files_of_member_file` FOREIGN KEY (`file`) REFERENCES `file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idx_f_files_of_member_member` FOREIGN KEY (`member`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for member
-- ----------------------------
DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System given id.',
  `name_first` varchar(155) COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'First name of member.',
  `name_last` varchar(155) COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'Last Name of member',
  `email` varchar(255) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Valid e-mail of user.',
  `username` varchar(155) COLLATE utf8_turkish_ci NOT NULL,
  `password` text COLLATE utf8_turkish_ci NOT NULL COMMENT 'Password of user.',
  `date_birth` datetime DEFAULT NULL COMMENT 'Date of birth of the user.',
  `file_avatar` text COLLATE utf8_turkish_ci COMMENT 'Avatar image of user.',
  `date_registration` datetime NOT NULL COMMENT 'Date of registration.',
  `date_activation` datetime DEFAULT NULL COMMENT 'Date of activation.',
  `date_status_changed` datetime DEFAULT NULL COMMENT 'Date when the user status last changed.',
  `status` varchar(1) COLLATE utf8_turkish_ci NOT NULL DEFAULT 'i' COMMENT 'a:active,i:inactive,b:banned,n:never activated',
  `key_activation` text COLLATE utf8_turkish_ci COMMENT 'Activation key.',
  `language` int(10) unsigned DEFAULT NULL COMMENT 'Member selected language.',
  `site` int(10) unsigned DEFAULT NULL COMMENT 'Primary site that user has registered with.',
  `extra_info` text COLLATE utf8_turkish_ci COMMENT 'Holds extra information ',
  `date_last_login` datetime DEFAULT NULL,
  `gender` char(1) COLLATE utf8_turkish_ci DEFAULT 'f' COMMENT 'f:female, m:male',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_u_member_id` (`id`) USING BTREE,
  UNIQUE KEY `idx_u_member_username` (`username`,`site`),
  UNIQUE KEY `idx_u_member_email` (`email`,`site`),
  KEY `idx_n_member_full_name` (`name_last`,`name_first`) USING BTREE,
  KEY `idx_n_member_date_birth` (`date_birth`) USING BTREE,
  KEY `idx_n_member_date_registration` (`date_registration`) USING BTREE,
  KEY `idx_n_member_date_activation` (`date_status_changed`) USING BTREE,
  KEY `idx_n_member_date_status_changed` (`date_status_changed`) USING BTREE,
  KEY `idx_f_member_language_idx` (`language`) USING BTREE,
  KEY `idx_f_member_site` (`site`) USING BTREE,
  CONSTRAINT `idx_f_member_language` FOREIGN KEY (`language`) REFERENCES `language` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `idx_f_member_site` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for member_group
-- ----------------------------
DROP TABLE IF EXISTS `member_group`;
CREATE TABLE `member_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System given id.',
  `code` varchar(45) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Member group code. This is not editable.',
  `date_added` datetime NOT NULL COMMENT 'Date when the group is created.',
  `date_created` datetime NOT NULL COMMENT 'Date when the group is created.',
  `date_updated` datetime DEFAULT NULL COMMENT 'Date when the group definition is last updated.',
  `type` varchar(1) COLLATE utf8_turkish_ci NOT NULL DEFAULT 'r' COMMENT 'r:regular,a:admin,s:support',
  `count_members` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of members associated with this group.',
  `site` int(10) unsigned DEFAULT NULL COMMENT 'Site that member group is defined for.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_u_member_group_id` (`id`) USING BTREE,
  UNIQUE KEY `idx_u_member_group_code` (`code`,`site`) USING BTREE,
  KEY `idx_n_member_group_date_crated` (`date_created`) USING BTREE,
  KEY `idx_n_member_group_date_updated` (`date_updated`) USING BTREE,
  KEY `idx_f_member_group_site` (`site`) USING BTREE,
  CONSTRAINT `idx_f_member_group_site` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for member_group_localization
-- ----------------------------
DROP TABLE IF EXISTS `member_group_localization`;
CREATE TABLE `member_group_localization` (
  `language` int(5) unsigned NOT NULL COMMENT 'Language of localization.',
  `member_group` int(10) unsigned NOT NULL COMMENT 'Localized member group.',
  `name` varchar(45) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Localized name of member group.',
  `url_key` varchar(55) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Localized url key of member group.',
  `description` text COLLATE utf8_turkish_ci COMMENT 'Localized description of member group.',
  PRIMARY KEY (`language`,`member_group`),
  UNIQUE KEY `idx_u_member_group_localization` (`member_group`,`language`) USING BTREE,
  UNIQUE KEY `idx_u_member_group_localized_url_key` (`language`,`url_key`) USING BTREE,
  KEY `idx_f_member_group_localization_language` (`language`) USING BTREE,
  CONSTRAINT `idx_f_member_group_localization_language` FOREIGN KEY (`language`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idx_f_member_group_localization_member_group` FOREIGN KEY (`member_group`) REFERENCES `member_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for member_localization
-- ----------------------------
DROP TABLE IF EXISTS `member_localization`;
CREATE TABLE `member_localization` (
  `member` int(10) unsigned NOT NULL COMMENT 'Member of localization',
  `language` int(5) unsigned NOT NULL COMMENT 'Language of localization.',
  `title` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'Localized title of member.',
  `biography` text COLLATE utf8_turkish_ci COMMENT 'Localized biography of member.',
  `extra_data` text COLLATE utf8_turkish_ci COMMENT 'Localized extra data - serializaed array - of member.',
  PRIMARY KEY (`member`,`language`),
  UNIQUE KEY `idx_u_member_localization` (`member`,`language`) USING BTREE,
  KEY `idx_f_member_localization_member` (`member`) USING BTREE,
  KEY `idx_f_member_localization_language` (`language`) USING BTREE,
  KEY `idx_n_member_localized_titles` (`language`,`title`,`member`) USING BTREE,
  CONSTRAINT `idx_f_member_localization_language` FOREIGN KEY (`language`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idx_f_member_localization_member` FOREIGN KEY (`member`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for members_of_group
-- ----------------------------
DROP TABLE IF EXISTS `members_of_group`;
CREATE TABLE `members_of_group` (
  `member` int(10) unsigned NOT NULL COMMENT 'Member of group.',
  `member_group` int(10) unsigned NOT NULL COMMENT 'Group of member.',
  `date_added` datetime NOT NULL,
  `referrer` int(10) unsigned DEFAULT NULL COMMENT 'Member who added the other member into the group.',
  UNIQUE KEY `idx_u_members_of_group` (`member`,`member_group`) USING BTREE,
  KEY `idx_f_members_of_group_member_idx` (`member`) USING BTREE,
  KEY `idx_f_members_of_group_group_idx` (`member_group`) USING BTREE,
  KEY `idx_n_members_of_group_dateadded` (`date_added`) USING BTREE,
  KEY `idx_f_members_of_group_referrer` (`referrer`) USING BTREE,
  CONSTRAINT `idx_f_members_of_group_group` FOREIGN KEY (`member_group`) REFERENCES `member_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idx_f_members_of_group_member` FOREIGN KEY (`member`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idx_f_members_of_group_referrer` FOREIGN KEY (`referrer`) REFERENCES `member` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for members_of_site
-- ----------------------------
DROP TABLE IF EXISTS `members_of_site`;
CREATE TABLE `members_of_site` (
  `member` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Associated member.',
  `site` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Associated site.',
  `date_added` datetime DEFAULT NULL COMMENT 'Date when the member is added to site.',
  PRIMARY KEY (`member`,`site`),
  UNIQUE KEY `idx_u_members_of_site` (`member`,`site`) USING BTREE,
  KEY `idx_f_members_of_site_site` (`site`) USING BTREE,
  KEY `idx_n_members_of_site_date_added` (`date_added`) USING BTREE,
  CONSTRAINT `idx_f_members_of_site_member` FOREIGN KEY (`member`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idx_f_members_of_site_site` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;
