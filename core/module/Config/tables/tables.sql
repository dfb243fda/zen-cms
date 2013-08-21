CREATE TABLE `#__config` (
	`entry_namespace` varchar(64) NOT NULL DEFAULT '',
	`entry_key` varchar(64) NOT NULL DEFAULT '',
	`entry_value` text NOT NULL,
	PRIMARY KEY  (`entry_namespace`,`entry_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;