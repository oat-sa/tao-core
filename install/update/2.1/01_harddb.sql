CREATE TABLE `class_to_table`(
	`id` int NOT NULL auto_increment,
	`uri` VARCHAR(255) NOT NULL,
	`table` VARCHAR(64) NOT NULL,
	`topClass` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_class_to_table_uri` (`uri`)
)DEFAULT CHARSET=utf8;

CREATE TABLE `resource_to_table`(
	`id` int NOT NULL auto_increment,
	`uri` VARCHAR(255) NOT NULL,
	`table` VARCHAR(64) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_resource_to_table_uri` (`uri`)
)DEFAULT CHARSET=utf8;

CREATE TABLE `resource_has_class`(
	`resource_id` int NOT NULL,
	`class_id` int NOT NULL,
	PRIMARY KEY (`resource_id`, `class_id`)
)DEFAULT CHARSET=utf8;
