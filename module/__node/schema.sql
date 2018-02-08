DROP TABLE IF EXISTS `node`;

CREATE TABLE `node` (`node_id` int(10) UNSIGNED NOT NULL auto_increment, `node_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `node_created` datetime NOT NULL, `node_updated` datetime NOT NULL, `node_image` varchar(255) DEFAULT NULL, `node_title` varchar(254) NOT NULL, `node_slug` varchar(255) NOT NULL, `node_detail` text DEFAULT NULL, `node_tags` json DEFAULT NULL, `node_meta` json DEFAULT NULL, `node_files` json DEFAULT NULL, `node_status` varchar(255) DEFAULT 'pending', `node_published` datetime DEFAULT NULL, `node_type` varchar(255) DEFAULT NULL, `node_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`node_id`), UNIQUE KEY `node_slug` (`node_slug`), KEY `node_active` (`node_active`), 
KEY `node_created` (`node_created`), 
KEY `node_updated` (`node_updated`), 
KEY `node_title` (`node_title`));

DROP TABLE IF EXISTS `node_user`;

CREATE TABLE `node_user` (`node_id` int(10) UNSIGNED NOT NULL, `user_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`node_id`, `user_id`));

DROP TABLE IF EXISTS `node_node`;

CREATE TABLE `node_node` (`node_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`node_id`, `node_id`));