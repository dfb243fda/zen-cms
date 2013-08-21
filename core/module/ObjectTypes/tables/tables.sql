CREATE TABLE `#__fields_controller` (
	`sorting` int(11) NOT NULL DEFAULT '0',
	`field_id` int(11) NOT NULL,
	`group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `#__objects` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`parent_id` int(11) NOT NULL DEFAULT '0',
	`guid` varchar(128) DEFAULT NULL,
	`name` varchar(255) NOT NULL,
	`type_id` int(11) NOT NULL,
	`created_user` int(11) unsigned NOT NULL,
	`sorting` int(11) DEFAULT NULL,
	`is_active` tinyint(1) NOT NULL DEFAULT '0',
	`is_deleted` tinyint(1) NOT NULL DEFAULT '0',
	`created_time` int(11) NOT NULL,
	`modified_time` int(11) NOT NULL,
	PRIMARY KEY  (`id`),
	KEY `type_id` (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE `#__object_content` (
	`object_id` int(11) NOT NULL,
	`field_id` int(11) DEFAULT NULL,
	`int_val` int(11) DEFAULT NULL,
	`varchar_val` varchar(255) DEFAULT NULL,
	`text_val` mediumtext,
	`float_val` double DEFAULT NULL,
	`object_rel_val` int(11) DEFAULT NULL,
	`page_rel_val` int(11) DEFAULT NULL,
	KEY `object_id` (`object_id`),
	KEY `field_id` (`field_id`),
	KEY `object_rel_val` (`object_rel_val`),
	KEY `page_rel_val` (`page_rel_val`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `#__object_fields` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(32) NOT NULL,
	`title` varchar(128) NOT NULL,
	`field_type_id` int(11) NOT NULL,
	`tip` varchar(255) NOT NULL DEFAULT '',
	`is_locked` tinyint(1) NOT NULL DEFAULT '0',
	`is_visible` tinyint(1) NOT NULL DEFAULT '1',
	`is_required` tinyint(1) NOT NULL DEFAULT '0',
	`in_filter` tinyint(1) NOT NULL DEFAULT '0',
	`in_search` tinyint(1) NOT NULL DEFAULT '0',
	`sortable` tinyint(1) NOT NULL DEFAULT '0',
	`guide_id` int(11) DEFAULT NULL,
	`is_system` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY  (`id`),
	KEY `field_type_id` (`field_type_id`),
	KEY `guide_id` (`guide_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE `#__object_field_groups` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(32) NOT NULL,
	`title` varchar(64) NOT NULL,
	`object_type_id` int(11) NOT NULL,
	`is_locked` tinyint(1) NOT NULL,
	`sorting` int(11) NOT NULL,
	PRIMARY KEY  (`id`),
	KEY `object_type_id` (`object_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE `#__object_field_types` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(64) NOT NULL,
	`is_multiple` tinyint(1) NOT NULL,
	`name` varchar(32) NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE `#__object_types` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`guid` varchar(128) DEFAULT NULL,
	`name` varchar(255) NOT NULL,
	`parent_id` int(11) NOT NULL DEFAULT '0',
	`is_guidable` tinyint(1) NOT NULL DEFAULT '0',
	`page_content_type_id` int(11) NOT NULL DEFAULT '0',
	`page_type_id` int(11) NOT NULL DEFAULT '0',
	`is_locked` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


ALTER TABLE `#__objects`
	ADD CONSTRAINT `#__objects_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `#__object_types` (`id`);

        
ALTER TABLE `#__object_content`
	ADD CONSTRAINT `#__object_content_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `#__object_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `#__object_content_ibfk_2` FOREIGN KEY (`object_rel_val`) REFERENCES `#__objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `#__object_content_ibfk_3` FOREIGN KEY (`page_rel_val`) REFERENCES `#__pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `#__object_content_ibfk_4` FOREIGN KEY (`object_id`) REFERENCES `#__objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
     
        
ALTER TABLE `#__object_fields`
	ADD CONSTRAINT `#__object_fields_ibfk_1` FOREIGN KEY (`field_type_id`) REFERENCES `#__object_field_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `#__object_fields_ibfk_2` FOREIGN KEY (`guide_id`) REFERENCES `#__object_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

        

ALTER TABLE `#__object_field_groups`
	ADD CONSTRAINT `#__object_field_groups_ibfk_1` FOREIGN KEY (`object_type_id`) REFERENCES `#__object_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

