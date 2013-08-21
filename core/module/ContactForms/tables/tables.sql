CREATE TABLE `#__contact_forms` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(128) NOT NULL,
	`template` text,
    `object_id` int(11) NOT NULL,

    `recipient` varchar(128) NOT NULL,
    `sender` varchar(255) NOT NULL,
    `subject` varchar(255) NOT NULL,
    `mail_template` text,

    `use_recipient2` tinyint(1) NOT NULL,

    `recipient2` varchar(128) NOT NULL,
    `sender2` varchar(255) NOT NULL,
    `subject2` varchar(255) NOT NULL,
    `mail_template2` text,

	PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__contact_forms_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(128) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachments` text,
  `status` tinyint(1) NOT NULL,
  `created_time` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;