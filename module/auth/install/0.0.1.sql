DROP TABLE IF EXISTS `auth`;

CREATE TABLE `auth` (`auth_id` int(10) UNSIGNED NOT NULL auto_increment, `auth_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `auth_created` datetime NOT NULL, `auth_updated` datetime NOT NULL, `auth_slug` varchar(255) NOT NULL, `auth_password` varchar(255) DEFAULT NULL, `auth_type` varchar(255) DEFAULT NULL, `auth_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`auth_id`), UNIQUE KEY `auth_slug` (`auth_slug`), KEY `auth_active` (`auth_active`),
KEY `auth_created` (`auth_created`),
KEY `auth_updated` (`auth_updated`),
KEY `auth_password` (`auth_password`),
KEY `auth_type` (`auth_type`));

DROP TABLE IF EXISTS `auth_profile`;

CREATE TABLE `auth_profile` (`auth_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`auth_id`, `profile_id`));
