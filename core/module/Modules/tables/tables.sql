CREATE TABLE `#__modules` (
	`module` varchar(55) NOT NULL DEFAULT '',
	`is_active` tinyint(1) NOT NULL,
	`is_required` tinyint(1) NOT NULL,
    `version` varchar(55) NOT NULL,
	`sorting` int(11) NOT NULL DEFAULT '0',    
	PRIMARY KEY  (`module`),
	UNIQUE KEY `uniq1` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8