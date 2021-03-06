CREATE TABLE `#__users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`login` varchar(255) DEFAULT NULL,
	`email` varchar(255) DEFAULT NULL,
	`display_name` varchar(50) DEFAULT NULL,
	`password` varchar(128) DEFAULT NULL,
	`state` smallint(6) DEFAULT NULL,
    `object_id` int(11) NOT NULL,
    `loginza_id` varchar(255) DEFAULT NULL,
    `loginza_provider` varchar(255) DEFAULT NULL,
    `loginza_data` text,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `login` (`login`),
	UNIQUE KEY `email` (`email`),
    UNIQUE KEY `loginza_id` (`loginza_id`),
    UNIQUE KEY `loginza_provider` (`loginza_provider`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;