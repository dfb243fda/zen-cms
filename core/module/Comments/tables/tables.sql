CREATE TABLE `#__commented_objects` (
	`object_id` int(11) NOT NULL,

    UNIQUE KEY `object_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
	`object_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `text` text,

    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
