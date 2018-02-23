DROP TABLE IF EXISTS `history`;

CREATE TABLE `history` (`history_id` int(10) UNSIGNED NOT NULL auto_increment, `history_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `history_created` datetime NOT NULL, `history_updated` datetime NOT NULL, `history_remote_address` varchar(50) DEFAULT NULL, `history_activity` text(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, `history_page` varchar(255) DEFAULT NULL, `history_meta` json DEFAULT NULL, `history_type` varchar(255) DEFAULT NULL, `history_flag` tinyint(1) DEFAULT 0, PRIMARY KEY (`history_id`), KEY `history_active` (`history_active`), 
KEY `history_created` (`history_created`), 
KEY `history_updated` (`history_updated`), 
KEY `history_flag` (`history_flag`));

DROP TABLE IF EXISTS `history_user`;

CREATE TABLE `history_user` (`history_id` int(10) UNSIGNED NOT NULL, `user_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `user_id`));