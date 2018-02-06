ALTER TABLE `user` CHANGE `user_flag`  `user_flag` int(1) unsigned DEFAULT NULL;

DROP TABLE IF EXISTS `user_address`;

DROP TABLE IF EXISTS `user_comment`;

DROP TABLE IF EXISTS `user_history`;

CREATE TABLE `user_user` (`user_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`user_id`, `user_id`));

CREATE TABLE `user_node` (`user_id` int(10) UNSIGNED NOT NULL, `node_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`user_id`, `node_id`));