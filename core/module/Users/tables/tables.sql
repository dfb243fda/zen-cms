CREATE TABLE `#__users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_name` varchar(255) DEFAULT NULL,
	`email` varchar(255) DEFAULT NULL,
	`display_name` varchar(50) DEFAULT NULL,
	`password` varchar(128) NOT NULL,
	`state` smallint(6) DEFAULT NULL,
    `object_id` int(11) NOT NULL,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `user_name` (`user_name`),
	UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;