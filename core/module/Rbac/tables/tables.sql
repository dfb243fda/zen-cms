CREATE TABLE `#__permission_resources` (
	`resource` varchar(55) NOT NULL,
	`privelege` varchar(55) NOT NULL,
	`name` varchar(255) NOT NULL,
	`is_active` tinyint(4) NOT NULL DEFAULT '1',
	`module` varchar(55) NOT NULL,
	KEY `module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `#__roles` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(55) NOT NULL,
	`parent` int(11) NOT NULL DEFAULT '0',
	`unauthorized` tinyint(1) NOT NULL DEFAULT '0',
	`sorting` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE `#__role_permissions` (
	`resource` varchar(55) NOT NULL,
	`privelege` varchar(55) NOT NULL,
	`role` int(11) NOT NULL,
	`is_allowed` tinyint(1) NOT NULL DEFAULT '1',
	KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `#__user_role_linker` (
	`user_id` int(11) unsigned NOT NULL,
	`role_id` int(11) NOT NULL,
	PRIMARY KEY  (`user_id`,`role_id`),
	KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# ALTER TABLE `#__permission_resources`
#	ADD CONSTRAINT `#__permission_resources_ibfk_1` FOREIGN KEY (`module`) REFERENCES `#__modules` (`module`) ON DELETE CASCADE ON UPDATE CASCADE;


# ALTER TABLE `#__role_permissions`
#	ADD CONSTRAINT `#__role_permissions_ibfk_1` FOREIGN KEY (`role`) REFERENCES `#__roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
