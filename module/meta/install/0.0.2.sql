ALTER TABLE `meta` DROP `meta_slug`, 
ADD `meta_key` varchar(255) NOT NULL, 
CHANGE `meta_flag`  `meta_flag` int(1) unsigned DEFAULT NULL, 
ADD UNIQUE (`meta_key`);