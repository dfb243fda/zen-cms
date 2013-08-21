
CREATE TABLE `#__domains` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `host` varchar(255) NOT NULL,
    `is_default` tinyint(1) NOT NULL,
    `default_lang_id` int(11) NOT NULL,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__domain_mirrors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `host` varchar(255) NOT NULL,
    `rel` int(11) NOT NULL,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `#__pages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `parent_id` int(11) NOT NULL DEFAULT '0',
    `alias` varchar(50) NOT NULL DEFAULT '',
    `template` int(11) NOT NULL,
    `page_type_id` int(11) NOT NULL,
    `access` varchar(255) NOT NULL DEFAULT '',
    `non_access_url` varchar(255) NOT NULL DEFAULT '',
    `sorting` int(11) NOT NULL DEFAULT '0',
    `object_id` int(11) NOT NULL,
    `domain_id` int(11) NOT NULL,
    `is_default` tinyint(1) NOT NULL,
    `lang_id` int(11) NOT NULL,
    `is_403` tinyint(1) NOT NULL DEFAULT '0',
    `is_404` tinyint(1) NOT NULL DEFAULT '0',
    `is_active` tinyint(1) NOT NULL,
    `is_deleted` tinyint(1) NOT NULL,
    PRIMARY KEY  (`id`),
    KEY `lang_id` (`lang_id`),
    KEY `domain_id` (`domain_id`),
    KEY `object_id` (`object_id`),
    KEY `template` (`template`),
    KEY `page_type_id` (`page_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `#__pages_content` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_id` int(11) NOT NULL DEFAULT '0',
    `marker` int(11) NOT NULL,
    `page_content_type_id` int(11) NOT NULL,
    `access` varchar(255) NOT NULL DEFAULT '',
    `sorting` int(11) unsigned NOT NULL DEFAULT '0',
    `object_id` int(11) NOT NULL,
    `template` int(11) NOT NULL,
    `is_active` tinyint(1) NOT NULL,
    `is_deleted` tinyint(1) NOT NULL,
    PRIMARY KEY  (`id`),
    KEY `object_id` (`object_id`),
    KEY `template` (`template`),
    KEY `marker` (`marker`),
    KEY `page_id` (`page_id`),
    KEY `page_content_type_id` (`page_content_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__page_content_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(64) NOT NULL,
    `module` varchar(32) NOT NULL,
    `method` varchar(32) NOT NULL,
    `service` varchar(128) NOT NULL,

    UNIQUE KEY `page_content_types_uk1` (`module`,`method`),
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `#__page_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(64) NOT NULL,
    `module` varchar(32) NOT NULL,
    `method` varchar(32) NOT NULL,
    `service` varchar(128) NOT NULL,

    UNIQUE KEY `page_types_uk1` (`module`,`method`),
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




# ALTER TABLE `#__domain_mirrors`
# ADD CONSTRAINT `#__domain_mirrors_ibfk_1` FOREIGN KEY (`rel`) REFERENCES `#__domains` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;



# ALTER TABLE `#__pages`
# ADD CONSTRAINT `#__pages_ibfk_1` FOREIGN KEY (`object_id`) REFERENCES `#__objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
# ADD CONSTRAINT `#__pages_ibfk_2` FOREIGN KEY (`domain_id`) REFERENCES `#__domains` (`id`),
# ADD CONSTRAINT `#__pages_ibfk_3` FOREIGN KEY (`template`) REFERENCES `#__templates` (`id`),
# ADD CONSTRAINT `#__pages_ibfk_4` FOREIGN KEY (`lang_id`) REFERENCES `#__langs` (`id`),
# ADD CONSTRAINT `#__pages_ibfk_5` FOREIGN KEY (`page_type_id`) REFERENCES `#__page_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;



# ALTER TABLE `#__pages_content`
# ADD CONSTRAINT `#__pages_content_ibfk_1` FOREIGN KEY (`marker`) REFERENCES `#__template_markers` (`id`),
# ADD CONSTRAINT `#__pages_content_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `#__pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
# ADD CONSTRAINT `#__pages_content_ibfk_3` FOREIGN KEY (`object_id`) REFERENCES `#__objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
# ADD CONSTRAINT `#__pages_content_ibfk_4` FOREIGN KEY (`page_content_type_id`) REFERENCES `#__page_content_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
# ADD CONSTRAINT `#__pages_content_ibfk_5` FOREIGN KEY (`template`) REFERENCES `#__templates` (`id`);

