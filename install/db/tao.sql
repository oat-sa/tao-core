DROP DATABASE IF EXISTS {DATABASE_NAME};
CREATE DATABASE {DATABASE_NAME};
USE {DATABASE_NAME};
SET NAMES UTF8;

DROP TABLE IF EXISTS `_mask`;
CREATE TABLE `_mask` (
  `user` varchar(255) default NULL,
  `Scope` varchar(255) default NULL,
  `Method` varchar(255) default NULL,
  `onAssertPrivileges` longtext,
  `_comment` varchar(255) default NULL,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `extensions`;
CREATE TABLE `extensions` (
  `id` varchar(25) NOT NULL default '',
  `name` varchar(150) default NULL,
  `version` varchar(4) default NULL,
  `loaded` tinyint(1) NOT NULL,
  `loadAtStartUp` tinyint(1) NOT NULL,
  `ghost` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

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
  KEY `idx_statements_subject` (`subject`),
  KEY `idx_statements_predicate` (`predicate`)
) ENGINE=MyISAM AUTO_INCREMENT=4337 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `login` varchar(32) NOT NULL default '',
  `password` varchar(32) default NULL,
  `admin` tinyint(1) NOT NULL default '0',
  `usergroup` varchar(32) default NULL,
  `LastName` varchar(64) default NULL,
  `FirstName` varchar(64) default NULL,
  `E_Mail` varchar(128) default NULL,
  `Company` varchar(128) default NULL,
  `Deflg` char(2) default NULL,
  `Uilg` varchar(2) default NULL,
  `enabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`login`),
  KEY `login` (`login`),
  KEY `usergroup` (`usergroup`),
  KEY `login_2` (`login`),
  KEY `usergroup_2` (`usergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `user` VALUES ('generis','b01a52f727b0810639526fe2c8188331',1,'admin','','','','','','EN',0);
