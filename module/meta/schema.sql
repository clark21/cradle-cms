DROP TABLE IF EXISTS `meta`;

CREATE TABLE `meta` (`meta_id` int(10) UNSIGNED NOT NULL auto_increment, `meta_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `meta_created` datetime NOT NULL, `meta_updated` datetime NOT NULL, `meta_type` varchar(255) DEFAULT 'post', `meta_singular` varchar(255) NOT NULL, `meta_plural` varchar(255) NOT NULL, `meta_key` varchar(255) NOT NULL, `meta_detail` text DEFAULT NULL, `meta_fields` json DEFAULT NULL, `meta_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`meta_id`), UNIQUE KEY `meta_key` (`meta_key`), KEY `meta_active` (`meta_active`), 
KEY `meta_created` (`meta_created`), 
KEY `meta_updated` (`meta_updated`), 
KEY `meta_singular` (`meta_singular`), 
KEY `meta_plural` (`meta_plural`));