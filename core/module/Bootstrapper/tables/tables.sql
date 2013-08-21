CREATE TABLE `#__langs` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`prefix` varchar(8) NOT NULL,
	`title` varchar(64) NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `#__logs` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`timestamp` varchar(32) NOT NULL,
	`type` int(11) NOT NULL,
	`message` text NOT NULL,
	`uri` varchar(255) NOT NULL DEFAULT '',
	`client_ip` varchar(128) NOT NULL DEFAULT '',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`errno` int(11) NOT NULL DEFAULT '0',
	`line` int(11) NOT NULL DEFAULT '0',
	`file` varchar(255) NOT NULL DEFAULT '',
	`class` varchar(255) DEFAULT '',
	`function` varchar(255) DEFAULT '',
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;