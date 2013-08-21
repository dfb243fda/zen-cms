CREATE TABLE `#__users` (
	`user_id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(255) DEFAULT NULL,
	`email` varchar(255) DEFAULT NULL,
	`display_name` varchar(50) DEFAULT NULL,
	`password` varchar(128) NOT NULL,
	`state` smallint(6) DEFAULT NULL,
    `object_id` int(11) NOT NULL,
	PRIMARY KEY  (`user_id`),
	UNIQUE KEY `username` (`username`),
	UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;