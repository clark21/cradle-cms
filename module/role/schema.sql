--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;

CREATE TABLE `role` (`role_id` int(10) UNSIGNED NOT NULL auto_increment, `role_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `role_created` datetime NOT NULL, `role_updated` datetime NOT NULL, `role_name` varchar(255) NOT NULL, `role_permissions` json NOT NULL, `role_type` varchar(255) DEFAULT NULL, `role_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`role_id`), KEY `role_active` (`role_active`),
KEY `role_created` (`role_created`),
KEY `role_updated` (`role_updated`),
KEY `role_name` (`role_name`),
KEY `role_flag` (`role_flag`));


--
-- Table structure for table `auth_role`
--

DROP TABLE IF EXISTS `role_auth`;

CREATE TABLE `role_auth` (`role_id` int(10) UNSIGNED NOT NULL, `auth_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`role_id`, `auth_id`));
