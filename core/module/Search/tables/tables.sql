CREATE TABLE `#__search_object_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `guid` varchar(128) NOT NULL,
    `object_type_id` int(11) NOT NULL,
    `module` varchar(55) NOT NULL,

    UNIQUE KEY `guid` (`guid`,`object_type_id`),
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `#__search_index` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `object_id` int(11) NOT NULL,
	`field_id` int(11) DEFAULT NULL,
    `field_name` varchar(32) DEFAULT NULL,
	`int_val` int(11) DEFAULT NULL,
	`varchar_val` varchar(255) DEFAULT NULL,
	`text_val` mediumtext,
	`float_val` double DEFAULT NULL,
	`object_rel_val` int(11) DEFAULT NULL,
	`page_rel_val` int(11) DEFAULT NULL,
    `guid` varchar(128) NOT NULL,
    `object_name` varchar(255) DEFAULT NULL,
    `url_query` varchar(255) NOT NULL,
    `access` varchar(255) NOT NULL,
    `created_time` int(11) NOT NULL,

    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;