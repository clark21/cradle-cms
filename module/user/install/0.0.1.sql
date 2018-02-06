DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (`user_id` int(10) UNSIGNED NOT NULL auto_increment, `user_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `user_created` datetime NOT NULL, `user_updated` datetime NOT NULL, `user_name` varchar(255) NOT NULL, `user_slug` varchar(255) DEFAULT NULL, `user_meta` json DEFAULT NULL, `user_files` json DEFAULT NULL, `user_type` varchar(255) DEFAULT NULL, `user_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`user_id`), KEY `user_active` (`user_active`), 
KEY `user_created` (`user_created`), 
KEY `user_updated` (`user_updated`), 
KEY `user_name` (`user_name`), 
KEY `user_slug` (`user_slug`), 
KEY `user_type` (`user_type`), 
KEY `user_flag` (`user_flag`));

DROP TABLE IF EXISTS `user_comment`;

CREATE TABLE `user_comment` (`user_id` int(10) UNSIGNED NOT NULL, `comment_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`user_id`, `comment_id`));

DROP TABLE IF EXISTS `user_address`;

CREATE TABLE `user_address` (`user_id` int(10) UNSIGNED NOT NULL, `address_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`user_id`, `address_id`));

DROP TABLE IF EXISTS `user_history`;

CREATE TABLE `user_history` (`user_id` int(10) UNSIGNED NOT NULL, `history_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`user_id`, `history_id`));

DROP TABLE IF EXISTS `user_user`;

CREATE TABLE `user_user` (`user_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`user_id`, `user_id`));