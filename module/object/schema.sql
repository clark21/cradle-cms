DROP TABLE IF EXISTS `object`;

CREATE TABLE `object` (`object_id` int(10) UNSIGNED NOT NULL auto_increment, `object_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `object_created` datetime NOT NULL, `object_updated` datetime NOT NULL, `object_singular` varchar(255) NOT NULL, `object_plural` varchar(255) NOT NULL, `object_key` varchar(255) NOT NULL, `object_detail` text DEFAULT NULL, `object_relations` json DEFAULT NULL, `object_fields` json DEFAULT NULL, PRIMARY KEY (`object_id`), UNIQUE KEY `object_key` (`object_key`), KEY `object_active` (`object_active`), 
KEY `object_created` (`object_created`), 
KEY `object_updated` (`object_updated`), 
KEY `object_singular` (`object_singular`), 
KEY `object_plural` (`object_plural`));