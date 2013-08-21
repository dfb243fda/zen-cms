CREATE TABLE `#__sessions` (
	`id` char(32) NOT NULL DEFAULT '',
	`name` varchar(255) NOT NULL,
	`modified` int(11) DEFAULT NULL,
	`lifetime` int(11) DEFAULT NULL,
	`data` text,
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8