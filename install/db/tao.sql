DROP DATABASE IF EXISTS {DATABASE_NAME};
CREATE DATABASE {DATABASE_NAME};
USE {DATABASE_NAME};
SET NAMES UTF8;

DROP TABLE IF EXISTS `extensions`;
CREATE TABLE `extensions` (
  `id` varchar(25) NOT NULL default '',
  `name` varchar(150) default NULL,
  `version` varchar(4) default NULL,
  `loaded` tinyint(1) NOT NULL,
  `loadAtStartUp` tinyint(1) NOT NULL,
  `ghost` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `extensions` VALUES 
('generis','generis','2.0',1,1,0),
('tao','tao','2.0',1,1,0),
('taoGroups','Tao Groups','2.0',1,1,0),
('taoItems','Tao Items','2.0',1,1,0),
('taoResults','Tao Results','2.0',1,1,0),
('taoSubjects','Tao Subjects','2.0',1,1,0),
('taoTests','Tao Test','2.0',1,1,0),
('taoDelivery','taoDelivery','2.0',1,1,0),
('wfEngine','Workflow Engine Extension','2.0',1,1,0);

DROP TABLE IF EXISTS `models`;
CREATE TABLE `models` (
  `modelID` int(11) NOT NULL auto_increment,
  `modelURI` varchar(255) default NULL,
  `baseURI` varchar(255) default NULL,
  PRIMARY KEY  (`modelID`),
  KEY `idx_models_modelURI` (`modelURI`)
)DEFAULT CHARSET=utf8;

INSERT INTO `models` VALUES 
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#','http://www.w3.org/1999/02/22-rdf-syntax-ns#'),
(5,'http://www.w3.org/2000/01/rdf-schema#','http://www.w3.org/2000/01/rdf-schema#'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#','http://www.tao.lu/Ontologies/generis.rdf#'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#','http://www.tao.lu/Ontologies/TAO.rdf#'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf','http://www.tao.lu/Ontologies/TAOResult.rdf#'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#','http://www.tao.lu/Ontologies/TAOItem.rdf#'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#','http://www.tao.lu/Ontologies/TAOGroup.rdf#'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#','http://www.tao.lu/Ontologies/TAOTest.rdf#'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#','http://www.tao.lu/Ontologies/TAOSubject.rdf#'),
(14,'http://www.tao.lu/Ontologies/TAODelivery.rdf#','http://www.tao.lu/Ontologies/TAODelivery.rdf#'),
(15,'http://www.tao.lu/middleware/wfEngine.rdf#','http://www.tao.lu/middleware/wfEngine.rdf#'),
(17,'http://www.tao.lu/middleware/Rules.rdf#','http://www.tao.lu/middleware/Rules.rdf#');

DROP TABLE IF EXISTS `statements`;
CREATE TABLE `statements` (
  `modelID` int(11) NOT NULL default '0',
  `subject` varchar(255) default NULL,
  `predicate` varchar(255) default NULL,
  `object` longtext,
  `l_language` varchar(255) default NULL,
  `id` int(11) NOT NULL auto_increment,
  `author` varchar(255) default NULL,
  `stread` varchar(255) default NULL,
  `stedit` varchar(255) default NULL,
  `stdelete` varchar(255) default NULL,
  `epoch` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `idx_statements_modelID` (`modelID`),
  KEY k_sp (subject(164), predicate(164)),
  KEY k_po (predicate(164), object(164))
)DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `class_to_table`;
CREATE TABLE `class_to_table` (
	`id` int NOT NULL auto_increment,
	`uri` VARCHAR(255) NOT NULL,
	`table` VARCHAR(64) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_class_to_table_uri` (`uri`) 
)DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `resource_to_table`;
CREATE TABLE `resource_to_table` (
	`id` int NOT NULL auto_increment,
	`uri` VARCHAR(255) NOT NULL,
	`table` VARCHAR(64) NOT NULL,
	PRIMARY KEY (`id`) ,
	KEY `idx_resource_to_table_uri` (`uri`)
)DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `resource_has_class`;
CREATE TABLE `resource_has_class` (
	`resource_id` int NOT NULL,
	`class_id` int NOT NULL,
	PRIMARY KEY (`resource_id`, `class_id`) 
)DEFAULT CHARSET=utf8;
