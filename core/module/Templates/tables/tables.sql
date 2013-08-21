CREATE TABLE `#__templates` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(32) NOT NULL,
	`title` varchar(64) NOT NULL,
	`type` enum('page_template','content_template') NOT NULL,
	`module` varchar(55) NOT NULL,
	`method` varchar(32) NOT NULL,
	`is_default` tinyint(1) NOT NULL DEFAULT '0',

    UNIQUE KEY `templates_uk1` (`name`,`type`,`module`,`method`),
	PRIMARY KEY  (`id`),
	KEY `module` (`module`)    
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE `#__template_markers` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(32) NOT NULL,
	`title` varchar(64) NOT NULL,
	`template_id` int(11) NOT NULL,
	PRIMARY KEY  (`id`),
	KEY `template_id` (`template_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

# ALTER TABLE `#__templates`
#	ADD CONSTRAINT `#__templates_ibfk_1` FOREIGN KEY (`module`) REFERENCES `#__modules` (`module`) ON DELETE CASCADE ON UPDATE CASCADE;

# ALTER TABLE `#__template_markers`
#	ADD CONSTRAINT `#__template_markers_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `#__templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
