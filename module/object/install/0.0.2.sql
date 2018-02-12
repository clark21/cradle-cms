ALTER TABLE `object` DROP `object_flag`, 
ADD `object_relations` json DEFAULT NULL;