DROP TABLE IF EXISTS `article`;

CREATE TABLE `article` (`article_id` int(10) UNSIGNED NOT NULL auto_increment, `article_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `article_created` datetime NOT NULL, `article_updated` datetime NOT NULL, `article_slug` varchar(255) NOT NULL, `article_password` varchar(255) DEFAULT NULL, `article_type` varchar(255) DEFAULT NULL, `article_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`article_id`), UNIQUE KEY `article_slug` (`article_slug`), KEY `article_active` (`article_active`),
KEY `article_created` (`article_created`),
KEY `article_updated` (`article_updated`),
KEY `article_password` (`article_password`),
KEY `article_type` (`article_type`));