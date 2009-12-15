-- MySQL dump 10.11
--
-- Host: localhost    Database: taotrans_demo
-- ------------------------------------------------------
-- Server version	5.0.67-0ubuntu6

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `taotrans_demo`
--

/*!40000 DROP DATABASE IF EXISTS `taotrans_demo`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `taotrans_demo` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `taotrans_demo`;

--
-- Table structure for table `_mask`
--

DROP TABLE IF EXISTS `_mask`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `_mask` (
  `user` varchar(255) NOT NULL default '',
  `Scope` varchar(255) NOT NULL default '',
  `Method` varchar(255) NOT NULL default '',
  `onAssertPrivileges` longtext NOT NULL,
  `_comment` varchar(255) NOT NULL default '',
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `_mask`
--

LOCK TABLES `_mask` WRITE;
/*!40000 ALTER TABLE `_mask` DISABLE KEYS */;
/*!40000 ALTER TABLE `_mask` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `cache` (
  `identifier` varchar(512) character set utf8 collate utf8_unicode_ci NOT NULL,
  `value` varchar(8192) character set utf8 collate utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extensions`
--

DROP TABLE IF EXISTS `extensions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `extensions` (
  `id` varchar(25) NOT NULL,
  `name` varchar(150) NOT NULL,
  `version` varchar(4) NOT NULL,
  `loaded` tinyint(1) NOT NULL,
  `loadAtStartUp` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `extensions`
--

LOCK TABLES `extensions` WRITE;
/*!40000 ALTER TABLE `extensions` DISABLE KEYS */;
INSERT INTO `extensions` VALUES 
('tao','tao','1.0',1,1),
('taoGroups','Tao Groups','1.0',1,1),
('taoItems','Tao Items','1.0',1,1),
('taoResults','Tao Results','1.0',1,1),
('taoSubjects','Tao Subjects','1.0',1,1),
('taoTests','Tao Test','1.0',1,1),
('taoDelivery','taoDelivery','1.0',1,1);
/*!40000 ALTER TABLE `extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grouplocaluser`
--

DROP TABLE IF EXISTS `grouplocaluser`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `grouplocaluser` (
  `Name` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`Name`),
  KEY `Name` (`Name`),
  KEY `Name_2` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `grouplocaluser`
--

LOCK TABLES `grouplocaluser` WRITE;
/*!40000 ALTER TABLE `grouplocaluser` DISABLE KEYS */;
INSERT INTO `grouplocaluser` VALUES 
('admin');
/*!40000 ALTER TABLE `grouplocaluser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_action_descr`
--

DROP TABLE IF EXISTS `log_action_descr`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `log_action_descr` (
  `id` int(11) NOT NULL auto_increment,
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `idx_logactiondescr_description` (`description`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `log_action_descr`
--

LOCK TABLES `log_action_descr` WRITE;
/*!40000 ALTER TABLE `log_action_descr` DISABLE KEYS */;
INSERT INTO `log_action_descr` VALUES 
(1,'Statement added');
/*!40000 ALTER TABLE `log_action_descr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_actions`
--

DROP TABLE IF EXISTS `log_actions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `log_actions` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default NULL,
  `model_id` int(11) NOT NULL default '0',
  `user` varchar(255) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `descr_id` int(11) NOT NULL default '0',
  `subject` varchar(255) default NULL,
  `details` longblob,
  PRIMARY KEY  (`id`),
  KEY `idx_logactions_modelid` (`model_id`),
  KEY `idx_logactions_parentid` (`parent_id`),
  KEY `idx_logactions_user` (`user`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `log_actions`
--

LOCK TABLES `log_actions` WRITE;
/*!40000 ALTER TABLE `log_actions` DISABLE KEYS */;
INSERT INTO `log_actions` VALUES 
(1,NULL,2,'Admin','2005-07-12 10:38:01',1,'',''),
(2,NULL,2,'Admin','2005-07-12 10:38:01',1,'',''),
(3,NULL,2,'Admin','2005-07-12 10:38:02',1,'',''),
(4,NULL,2,'Admin','2005-07-12 10:38:02',1,'',''),
(5,NULL,2,'Admin','2005-07-12 10:38:02',1,'',''),
(6,NULL,2,'Admin','2005-07-12 10:38:02',1,'',''),
(7,NULL,2,'Admin','2005-07-12 10:38:02',1,'',''),
(8,NULL,2,'Admin','2005-07-12 10:38:02',1,'',''),
(9,NULL,2,'','2005-07-13 09:36:48',1,'',''),
(10,NULL,2,'','2005-07-13 09:36:49',1,'',''),
(11,NULL,2,'','2005-07-13 09:52:48',1,'',''),
(12,NULL,2,'','2005-07-13 09:52:48',1,'',''),
(13,NULL,2,'','2005-07-13 15:05:44',1,'',''),
(14,NULL,2,'','2005-07-13 15:05:44',1,'',''),
(15,NULL,2,'','2005-07-13 15:05:44',1,'',''),
(16,NULL,2,'','2005-07-13 15:05:44',1,'','');
/*!40000 ALTER TABLE `log_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `models` (
  `modelID` int(11) NOT NULL auto_increment,
  `modelURI` varchar(255) NOT NULL default '',
  `baseURI` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`modelID`),
  KEY `idx_models_modelURI` (`modelURI`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `models`
--

LOCK TABLES `models` WRITE;
/*!40000 ALTER TABLE `models` DISABLE KEYS */;
INSERT INTO `models` VALUES 
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#','http://www.w3.org/1999/02/22-rdf-syntax-ns#'),
(5,'http://www.w3.org/2000/01/rdf-schema#','http://www.w3.org/2000/01/rdf-schema#'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#','http://www.tao.lu/Ontologies/generis.rdf#'),
(8,'http://127.0.0.1/middleware/demo.rdf','http://127.0.0.1/middleware/demo.rdf#'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#','http://www.tao.lu/Ontologies/TAO.rdf#'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf','http://www.tao.lu/Ontologies/TAOResult.rdf#'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#','http://www.tao.lu/Ontologies/TAOItem.rdf#'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#','http://www.tao.lu/Ontologies/TAOGroup.rdf#'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#','http://www.tao.lu/Ontologies/TAOTest.rdf#'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#','http://www.tao.lu/Ontologies/TAOSubject.rdf#'),
(14,'http://www.tao.lu/Ontologies/TAODelivery.rdf#','http://www.tao.lu/Ontologies/TAODelivery.rdf#');
/*!40000 ALTER TABLE `models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `settings` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES 
('NameSpace','http://127.0.0.1/middleware/demo.rdf'),
('Deflg','EN'),
('Timeout','99'),
('Moduletype','resource');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statements`
--

DROP TABLE IF EXISTS `statements`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `statements` (
  `modelID` int(11) NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `predicate` varchar(255) NOT NULL default '',
  `object` longtext,
  `l_language` varchar(255) default NULL,
  `id` int(11) NOT NULL auto_increment,
  `author` varchar(255) default NULL,
  `stread` varchar(255) NOT NULL default 'yyy[]',
  `stedit` varchar(255) NOT NULL default 'yy-[]',
  `stdelete` varchar(255) NOT NULL default 'y--[Administrators]',
  `epoch` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `idx_statements_modelID` (`modelID`),
  KEY `idx_statements_subject` (`subject`),
  KEY `idx_statements_predicate` (`predicate`)
) ENGINE=MyISAM AUTO_INCREMENT=1253 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `statements`
--

LOCK TABLES `statements` WRITE;
/*!40000 ALTER TABLE `statements` DISABLE KEYS */;
INSERT INTO `statements` VALUES 
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',1,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.w3.org/2000/01/rdf-schema#label','Widget','en',2,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.w3.org/2000/01/rdf-schema#comment','Specifies the form interface widget','en',3,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',4,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',5,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox','',6,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',7,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.w3.org/2000/01/rdf-schema#label','Widget Range Constraint','en',8,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.w3.org/2000/01/rdf-schema#comment','This property constrains widgets to certain types of ranges','en',9,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',10,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes','',11,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox','',12,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',13,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes','http://www.w3.org/2000/01/rdf-schema#label','Types of Widget Range Constraints','en',14,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes','http://www.w3.org/2000/01/rdf-schema#comment','The class of range constraints applicable to widgets','en',15,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes','',16,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','http://www.w3.org/2000/01/rdf-schema#label','resources','en',17,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','http://www.w3.org/2000/01/rdf-schema#comment','Resources are any description, or any object identified by an URI','en',18,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes','',19,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','http://www.w3.org/2000/01/rdf-schema#label','literals','en',20,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','http://www.w3.org/2000/01/rdf-schema#comment','Any string (RDFS typde Literals)','en',21,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',22,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','http://www.w3.org/2000/01/rdf-schema#label','Widget Class','en',23,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','http://www.w3.org/2000/01/rdf-schema#comment','The class of all possible widgets','en',24,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',25,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox','http://www.w3.org/2000/01/rdf-schema#label','Drop down menu','en',26,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox','http://www.w3.org/2000/01/rdf-schema#comment','In drop down menu, one may select 1 to N options','en',27,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','',28,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',29,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox','http://www.w3.org/2000/01/rdf-schema#label','Radio button','en',30,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox','http://www.w3.org/2000/01/rdf-schema#comment','In radio boxes, one may select exactly one option','en',31,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','',32,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',33,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox','http://www.w3.org/2000/01/rdf-schema#label','Check box','en',34,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox','http://www.w3.org/2000/01/rdf-schema#comment','In check boxes, one may select 0 to N options','en',35,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','',36,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ClassTreeView','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',37,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ClassTreeView','http://www.w3.org/2000/01/rdf-schema#label','Class Tree View','en',38,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ClassTreeView','http://www.w3.org/2000/01/rdf-schema#comment','Tree view widget displays the class tree starting from a given class level. the user selects a class','en',39,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ClassTreeView','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','',40,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',41,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','http://www.w3.org/2000/01/rdf-schema#label','Instance Tree View','en',42,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','http://www.w3.org/2000/01/rdf-schema#comment','Tree view widget displays the class tree starting from a given class level, at each level, the instance of the highlighted class are displayed for user selection','en',43,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','',44,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',45,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton','http://www.w3.org/2000/01/rdf-schema#label','Expand Form','en',46,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton','http://www.w3.org/2000/01/rdf-schema#comment','A button to expand the form of properties of the class the target instance belongs to','en',47,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource','',48,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',49,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','http://www.w3.org/2000/01/rdf-schema#label','A Text Box','en',50,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','http://www.w3.org/2000/01/rdf-schema#comment','A particular text box','en',51,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','',52,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','1','',53,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',54,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox','http://www.w3.org/2000/01/rdf-schema#label','Hidden Box','en',55,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox','http://www.w3.org/2000/01/rdf-schema#comment','Content is hidden','en',56,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','',57,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','1','',58,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',59,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','http://www.w3.org/2000/01/rdf-schema#label','HTMLArea','en',60,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','http://www.w3.org/2000/01/rdf-schema#comment','An html area','en',61,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','',62,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','1','',63,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',64,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea','http://www.w3.org/2000/01/rdf-schema#label','A Text Area','en',65,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea','http://www.w3.org/2000/01/rdf-schema#comment','A particular text Area','en',66,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','',67,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',68,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox','http://www.w3.org/2000/01/rdf-schema#label','ListBox','en',69,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox','http://www.w3.org/2000/01/rdf-schema#comment','ListBox','en',70,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','',71,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass','',72,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq','http://www.w3.org/2000/01/rdf-schema#label','Sequence','en',73,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq','http://www.w3.org/2000/01/rdf-schema#comment','Sequence','en',74,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal','',75,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',76,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','http://www.w3.org/2000/01/rdf-schema#label','Text Height','en',77,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','http://www.w3.org/2000/01/rdf-schema#comment','The heigth of the text box, expressed in number of lines','en',78,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextWidgetClass','',79,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Integer','',80,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/TextBox','',81,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/TextBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','3','',82,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',83,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','http://www.w3.org/2000/01/rdf-schema#label','Text Length','en',84,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','http://www.w3.org/2000/01/rdf-schema#comment','The length of the text box, expressed in number of characters','en',85,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextWidgetClass','',86,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Integer','',87,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',88,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','3','',89,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','',90,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','',91,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/2000/01/rdf-schema#subPropertyOf','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','',92,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','',93,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','',94,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/2000/01/rdf-schema#label','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','',95,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','255','',96,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',97,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','255','',98,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/1999/02/22-rdf-syntax-ns#value','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',99,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea','',100,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','10','',101,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength','255','',102,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.w3.org/2000/01/rdf-schema#comment','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','',103,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(3,'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight','10','',104,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),


(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2002/07/owl#Ontology','',105,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#','http://purl.org/dc/elements/1.1/title','The RDF Vocabulary (RDF)','',106,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#','http://purl.org/dc/elements/1.1/description','This is the RDF Schema for the RDF vocabulary defined in the RDF namespace.','',107,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',108,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',109,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#label','type','',110,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#comment','The subject is an instance of a class.','',111,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Class','',112,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Resource','',113,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',114,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',115,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','http://www.w3.org/2000/01/rdf-schema#label','Property','',116,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','http://www.w3.org/2000/01/rdf-schema#comment','The class of RDF properties.','',117,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Resource','',118,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',119,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',120,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement','http://www.w3.org/2000/01/rdf-schema#label','Statement','',121,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Resource','',122,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement','http://www.w3.org/2000/01/rdf-schema#comment','The class of RDF statements.','',123,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',124,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',125,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject','http://www.w3.org/2000/01/rdf-schema#label','subject','',126,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject','http://www.w3.org/2000/01/rdf-schema#comment','The subject of the subject RDF statement.','',127,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement','',128,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Resource','',129,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',130,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',131,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate','http://www.w3.org/2000/01/rdf-schema#label','predicate','',132,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate','http://www.w3.org/2000/01/rdf-schema#comment','The predicate of the subject RDF statement.','',133,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement','',134,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Resource','',135,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#object','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',136,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#object','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',137,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#object','http://www.w3.org/2000/01/rdf-schema#label','object','',138,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#object','http://www.w3.org/2000/01/rdf-schema#comment','The object of the subject RDF statement.','',139,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#object','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement','',140,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#object','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Resource','',141,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',142,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',143,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag','http://www.w3.org/2000/01/rdf-schema#label','Bag','',144,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag','http://www.w3.org/2000/01/rdf-schema#comment','The class of unordered containers.','',145,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Container','',146,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',147,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',148,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq','http://www.w3.org/2000/01/rdf-schema#label','Seq','',149,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq','http://www.w3.org/2000/01/rdf-schema#comment','The class of ordered containers.','',150,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Container','',151,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',152,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',153,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt','http://www.w3.org/2000/01/rdf-schema#label','Alt','',154,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt','http://www.w3.org/2000/01/rdf-schema#comment','The class of containers of alternatives.','',155,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Container','',156,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#value','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',157,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#value','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',158,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#value','http://www.w3.org/2000/01/rdf-schema#label','value','',159,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#value','http://www.w3.org/2000/01/rdf-schema#comment','Idiomatic property used for structured values.','',160,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#value','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Resource','',161,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#value','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Resource','',162,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#List','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',163,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#List','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',164,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#List','http://www.w3.org/2000/01/rdf-schema#label','List','',165,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#List','http://www.w3.org/2000/01/rdf-schema#comment','The class of RDF Lists.','',166,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#List','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Resource','',167,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#List','',168,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',169,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil','http://www.w3.org/2000/01/rdf-schema#label','nil','',170,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil','http://www.w3.org/2000/01/rdf-schema#comment','The empty list, with no items in it. If the rest of a list is nil then the list has no more items in it.','',171,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#first','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',172,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#first','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',173,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#first','http://www.w3.org/2000/01/rdf-schema#label','first','',174,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#first','http://www.w3.org/2000/01/rdf-schema#comment','The first item in the subject RDF list.','',175,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#first','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#List','',176,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#first','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Resource','',177,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',178,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',179,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest','http://www.w3.org/2000/01/rdf-schema#label','rest','',180,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest','http://www.w3.org/2000/01/rdf-schema#comment','The rest of the subject RDF list after the first item.','',181,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#List','',182,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/1999/02/22-rdf-syntax-ns#List','',183,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Datatype','',184,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Literal','',185,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#','',186,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral','http://www.w3.org/2000/01/rdf-schema#label','XMLLiteral','',187,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral','http://www.w3.org/2000/01/rdf-schema#comment','The class of XML literal values.','',188,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(4,'http://www.w3.org/1999/02/22-rdf-syntax-ns#','http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.w3.org/2000/01/rdf-schema-more','',189,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),


(5,'http://www.w3.org/2000/01/rdf-schema#','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2002/07/owl#Ontology','',190,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#','http://purl.org/dc/elements/1.1/title','The RDF Schema vocabulary (RDFS)','',191,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Resource','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',192,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Resource','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',193,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Resource','http://www.w3.org/2000/01/rdf-schema#label','Resource','',194,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Resource','http://www.w3.org/2000/01/rdf-schema#comment','The class resource, everything.','',195,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Class','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',196,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Class','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',197,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Class','http://www.w3.org/2000/01/rdf-schema#label','Class','',198,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Class','http://www.w3.org/2000/01/rdf-schema#comment','The class of classes.','',199,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Class','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Resource','',200,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',201,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',202,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#label','subClassOf','',203,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#comment','The subject is a subclass of a class.','',204,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Class','',205,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Class','',206,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subPropertyOf','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',207,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subPropertyOf','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',208,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subPropertyOf','http://www.w3.org/2000/01/rdf-schema#label','subPropertyOf','',209,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subPropertyOf','http://www.w3.org/2000/01/rdf-schema#comment','The subject is a subproperty of a property.','',210,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subPropertyOf','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',211,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#subPropertyOf','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',212,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#comment','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',213,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#comment','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',214,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#comment','http://www.w3.org/2000/01/rdf-schema#label','comment','',215,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#comment','http://www.w3.org/2000/01/rdf-schema#comment','A description of the subject resource.','',216,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#comment','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Resource','',217,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#comment','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',218,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#label','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',219,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#label','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',220,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#label','http://www.w3.org/2000/01/rdf-schema#label','label','',221,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#label','http://www.w3.org/2000/01/rdf-schema#comment','A human-readable name for the subject.','',222,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#label','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Resource','',223,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#label','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',224,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',225,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',226,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#label','domain','',227,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#comment','A domain of the subject property.','',228,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Class','',229,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',230,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',231,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',232,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#label','range','',233,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#comment','A range of the subject property.','',234,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Class','',235,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',236,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',237,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',238,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.w3.org/2000/01/rdf-schema#label','seeAlso','',239,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.w3.org/2000/01/rdf-schema#comment','Further information about the subject resource.','',240,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Resource','',241,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Resource','',242,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',243,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',244,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#subPropertyOf','http://www.w3.org/2000/01/rdf-schema#seeAlso','',245,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#label','isDefinedBy','',246,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#comment','The defininition of the subject resource.','',247,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Resource','',248,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Resource','',249,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Literal','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',250,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Literal','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',251,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Literal','http://www.w3.org/2000/01/rdf-schema#label','Literal','',252,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Literal','http://www.w3.org/2000/01/rdf-schema#comment','The class of literal values, eg. textual strings and integers.','',253,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Literal','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Resource','',254,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Container','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',255,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Container','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',256,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Container','http://www.w3.org/2000/01/rdf-schema#label','Container','',257,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Container','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Resource','',258,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Container','http://www.w3.org/2000/01/rdf-schema#comment','The class of RDF containers.','',259,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',260,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',261,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty','http://www.w3.org/2000/01/rdf-schema#label','ContainerMembershipProperty','',262,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty','http://www.w3.org/2000/01/rdf-schema#comment','The class of container membership properties, rdf:_1, rdf:_2, ...,\n                    all of which are sub-properties of \'member\'.','',263,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',264,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#member','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',265,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#member','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',266,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#member','http://www.w3.org/2000/01/rdf-schema#label','member','',267,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#member','http://www.w3.org/2000/01/rdf-schema#comment','A member of the subject resource.','',268,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#member','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Resource','',269,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#member','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Resource','',270,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Datatype','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/2000/01/rdf-schema#Class','',271,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Datatype','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','http://www.w3.org/2000/01/rdf-schema#','',272,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Datatype','http://www.w3.org/2000/01/rdf-schema#label','Datatype','',273,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Datatype','http://www.w3.org/2000/01/rdf-schema#comment','The class of RDF datatypes.','',274,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#Datatype','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Class','',275,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(5,'http://www.w3.org/2000/01/rdf-schema#','http://www.w3.org/2000/01/rdf-schema#seeAlso','http://www.w3.org/2000/01/rdf-schema-more','',276,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),


(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource','',277,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#label','TAO Object','EN',278,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#comment','Any ressource related to etesting','EN',279,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',280,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#label','Plugin','EN',281,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#comment','Plugin','EN',282,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Class','',283,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',284,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',285,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),


(7,'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Resource','',286,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource','http://www.w3.org/2000/01/rdf-schema#label','generis_Ressource','EN',287,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource','http://www.w3.org/2000/01/rdf-schema#comment','generis_Ressource','EN',288,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Model','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.w3.org/2000/01/rdf-schema#Resource','',289,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Model','http://www.w3.org/2000/01/rdf-schema#label','Model','EN',290,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Model','http://www.w3.org/2000/01/rdf-schema#comment','Model','EN',291,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Plugin','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',292,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#label','Plugin','EN',293,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#comment','Plugin','EN',294,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/generis.rdf#Model','',295,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',296,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Plugin','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',297,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',298,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.w3.org/2000/01/rdf-schema#label','is_language_dependent','EN',299,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.w3.org/2000/01/rdf-schema#comment','is_language_dependent','EN',300,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',301,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/Ontologies/generis.rdf#Boolean','',302,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','is_language_dependent','http://www.tao.lu/Ontologies/generis.rdf#False','',303,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox','',304,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Boolean','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource','',305,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Boolean','http://www.w3.org/2000/01/rdf-schema#label','Boolean','EN',306,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#Boolean','http://www.w3.org/2000/01/rdf-schema#comment','Boolean','EN',307,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#True','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/generis.rdf#Boolean','',308,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#True','http://www.w3.org/2000/01/rdf-schema#label','True','EN',309,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#True','http://www.w3.org/2000/01/rdf-schema#comment','True','EN',310,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#False','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/generis.rdf#Boolean','',311,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#False','http://www.w3.org/2000/01/rdf-schema#label','False','EN',312,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),
(7,'http://www.tao.lu/Ontologies/generis.rdf#False','http://www.w3.org/2000/01/rdf-schema#comment','False','EN',313,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:25'),


(8,'lg','lg','lg','FR',314,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(8,'lg','lg','lg','EN',315,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(8,'lg','lg','lg','DE',316,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(8,'lg','lg','lg','PT',317,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),


(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource','',318,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#label','TAO Object','EN',319,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#comment','Any ressource related to etesting','EN',320,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',321,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#label','Plugin','EN',322,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#comment','Plugin','EN',323,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Class','',324,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',325,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',326,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource','',327,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#label','TAO Object','EN',328,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','http://www.w3.org/2000/01/rdf-schema#comment','Any ressource related to etesting','EN',329,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',330,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#label','Plugin','EN',331,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#comment','Plugin','EN',332,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#domain','http://www.w3.org/2000/01/rdf-schema#Class','',333,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',334,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),
(6,'http://www.tao.lu/Ontologies/TAO.rdf#Plugin','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',335,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:57:53'),


(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#Result','http://www.w3.org/2000/01/rdf-schema#label','Result','EN',343,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#Result','http://www.w3.org/2000/01/rdf-schema#comment','Result','EN',344,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#Result','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','',345,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/generis.rdf#Model','',346,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf','http://www.w3.org/2000/01/rdf-schema#label','TAO Result Model','EN',347,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf','http://www.w3.org/2000/01/rdf-schema#comment','TAO Result Model','EN',348,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf','http://www.tao.lu/Ontologies/generis.rdf#Plugin','TLAresults','',349,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf','http://www.tao.lu/Ontologies/generis.rdf#Plugin','uploadresultserver','',350,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf','http://www.tao.lu/Ontologies/generis.rdf#Plugin','hypergraph','',351,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',352,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent','http://www.w3.org/2000/01/rdf-schema#label','ResultContent','EN',353,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent','http://www.w3.org/2000/01/rdf-schema#comment','ResultContent','EN',354,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOResult.rdf#Result','',355,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',356,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),
(13,'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',357,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:11'),


(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Item','http://www.w3.org/2000/01/rdf-schema#label','Item','EN',392,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Item','http://www.w3.org/2000/01/rdf-schema#comment','Item','EN',393,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Item','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','',394,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',395,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent','http://www.w3.org/2000/01/rdf-schema#label','ItemContent','EN',396,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent','http://www.w3.org/2000/01/rdf-schema#comment','ItemContent','EN',397,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOItem.rdf#Item','',398,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',399,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Authoring','',400,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','http://www.w3.org/2000/01/rdf-schema#label','ItemModels','EN',401,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','http://www.w3.org/2000/01/rdf-schema#comment','ItemModels','EN',402,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','',403,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',404,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel','http://www.w3.org/2000/01/rdf-schema#label','ItemModel','EN ',405,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel','http://www.w3.org/2000/01/rdf-schema#comment','ItemModel','EN ',406,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOItem.rdf#Item','',407,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','',408,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox','',409,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-14 13:53:46'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',410,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','http://www.w3.org/2000/01/rdf-schema#label','Runtime<br />','EN ',411,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','http://www.w3.org/2000/01/rdf-schema#comment','\r\nRuntime','EN ',412,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','',413,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',414,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',415,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','',416,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM','http://www.w3.org/2000/01/rdf-schema#label','QCM','EN ',417,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM','http://www.w3.org/2000/01/rdf-schema#comment','QCM','EN ',418,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM','SWFFile','tao_item.swf','',419,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM','http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','tao_item.swf','',420,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','',421,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs','http://www.w3.org/2000/01/rdf-schema#label','Kohs','EN ',422,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs','http://www.w3.org/2000/01/rdf-schema#comment','Kohs','EN ',423,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs','http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','kohs_passation.swf','',424,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Campus','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','',425,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Campus','http://www.w3.org/2000/01/rdf-schema#label','Campus','EN ',426,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Campus','http://www.w3.org/2000/01/rdf-schema#comment','Campus','EN ',427,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Campus','http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','campus_item.swf','',428,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','',429,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest','http://www.w3.org/2000/01/rdf-schema#label','C-Test','EN ',430,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest','http://www.w3.org/2000/01/rdf-schema#comment','C-Test','EN ',431,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest','http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','ctest_item.swf','',432,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',433,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','',434,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',435,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',436,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.tao.lu/Ontologies/generis.rdf#False','',437,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.w3.org/1999/02/22-rdf-syntax-ns#value','','EN ',438,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.w3.org/2000/01/rdf-schema#comment','Authoring','EN ',439,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.w3.org/2000/01/rdf-schema#label','Authoring','EN ',440,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.w3.org/2000/01/rdf-schema#seeAlso','','EN ',441,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','  ','EN ',442,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels','',443,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263','http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile','','EN ',444,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263','http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','/waterphenix/index.php','EN ',445,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263','http://www.w3.org/1999/02/22-rdf-syntax-ns#value','','EN ',446,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263','http://www.w3.org/2000/01/rdf-schema#comment','&nbsp;','EN ',447,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263','http://www.w3.org/2000/01/rdf-schema#label','Custom','EN ',448,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263','http://www.w3.org/2000/01/rdf-schema#seeAlso','','EN ',449,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263','http://www.w3.org/2000/01/rdf-schema#isDefinedBy','  ','EN ',450,'demo','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 15:59:26'),


(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group','http://www.w3.org/2000/01/rdf-schema#label','Group','EN',463,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group','http://www.w3.org/2000/01/rdf-schema#comment','Group','EN',464,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','',465,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',466,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members','http://www.w3.org/2000/01/rdf-schema#label','Members','EN',467,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members','http://www.w3.org/2000/01/rdf-schema#comment','Members','EN',468,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOGroup.rdf#Group','',469,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject','',470,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','',471,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/generis.rdf#Model','',472,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf','http://www.w3.org/2000/01/rdf-schema#label','TAO Group Model','EN',473,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf','http://www.w3.org/2000/01/rdf-schema#comment','TAO Group Model','EN',474,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf','http://www.tao.lu/Ontologies/generis.rdf#Plugin','hypergraph','',475,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',476,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests','http://www.w3.org/2000/01/rdf-schema#label','Tests','EN',477,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests','http://www.w3.org/2000/01/rdf-schema#comment','Tests','EN',478,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOGroup.rdf#Group','',479,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/Ontologies/TAOTest.rdf#Test','',480,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),
(11,'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','',481,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 15:59:53'),


(8,'lg','lg','lg','FR',482,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(8,'lg','lg','lg','EN',483,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(8,'lg','lg','lg','DE',484,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(8,'lg','lg','lg','PT',485,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),


(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.w3.org/2000/01/rdf-schema#isDefinedBy',' ','EN',494,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.w3.org/2000/01/rdf-schema#label','Active','EN',495,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.w3.org/2000/01/rdf-schema#seeAlso','','EN',496,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.w3.org/2000/01/rdf-schema#comment','active / inactive state','EN',497,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOTest.rdf#Test','',498,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox','',499,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',500,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/Ontologies/generis.rdf#Boolean','',502,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#active','http://www.w3.org/1999/02/22-rdf-syntax-ns#value','','EN',503,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-14 13:53:00'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#Test','http://www.w3.org/2000/01/rdf-schema#label','Test','EN',504,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#Test','http://www.w3.org/2000/01/rdf-schema#comment','Test','EN',505,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#Test','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','',506,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',507,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems','http://www.w3.org/2000/01/rdf-schema#label','Related Items','EN',508,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems','http://www.w3.org/2000/01/rdf-schema#comment','Related Items','EN',509,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOTest.rdf#Test','',510,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/Ontologies/TAOItem.rdf#Item','',511,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView','',512,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/generis.rdf#Model','',513,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf','http://www.w3.org/2000/01/rdf-schema#label','TAO Test Model','EN',514,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf','http://www.w3.org/2000/01/rdf-schema#comment','TAO Test Model','EN',515,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf','http://www.tao.lu/Ontologies/generis.rdf#Plugin','hypergraph','',516,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',517,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent','http://www.w3.org/2000/01/rdf-schema#label','TestContent','EN',518,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent','http://www.w3.org/2000/01/rdf-schema#comment','TestContent','EN',519,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOTest.rdf#Test','',520,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',521,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TAuthoring','',522,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:11'),


(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject','http://www.w3.org/2000/01/rdf-schema#label','Testee','EN',559,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-15 14:36:43'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject','http://www.w3.org/2000/01/rdf-schema#comment','Specifies the form interface widget','EN',560,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject','http://www.w3.org/2000/01/rdf-schema#subClassOf','http://www.tao.lu/Ontologies/TAO.rdf#TAOObject','',561,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',562,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login','http://www.w3.org/2000/01/rdf-schema#label','Login','EN',563,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login','http://www.w3.org/2000/01/rdf-schema#comment','Login','EN',564,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject','',565,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',566,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox','',567,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login','http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.tao.lu/Ontologies/generis.rdf#False','',568,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',569,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password','http://www.w3.org/2000/01/rdf-schema#label','Password','EN',570,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password','http://www.w3.org/2000/01/rdf-schema#comment','Password','EN',571,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject','',572,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password','http://www.w3.org/2000/01/rdf-schema#range','http://www.w3.org/2000/01/rdf-schema#Literal','',573,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox','',574,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password','http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent','http://www.tao.lu/Ontologies/generis.rdf#False','',575,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/generis.rdf#Model','',576,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf','http://www.w3.org/2000/01/rdf-schema#label','TAO Subject Model','EN',577,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf','http://www.w3.org/2000/01/rdf-schema#comment','TAO Subject Model','EN',578,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf','http://www.tao.lu/Ontologies/generis.rdf#Plugin','hypergraph','',579,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-04 16:00:36'),
(12,'http://www.tao.lu/Ontologies/TAOSubject.rdf#125256679264748','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.tao.lu/Ontologies/TAOSubject.rdf#1251875577236','',620,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:36'),


(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest','http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','C_TestAuth.swf','EN',1213,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-14 11:51:50'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs','http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','kohs_authoring.swf','EN',1214,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-14 11:51:50'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM','http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','QCMauthoring.php','EN',1215,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-14 11:52:52'),
(10,'http://www.tao.lu/Ontologies/TAOItem.rdf#Campus','http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880','Campus.php','EN',1216,'generis','yyy[]','yy-[]','y--[Administrators]','2009-12-14 11:52:52'),


(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.w3.org/2000/01/rdf-schema#isDefinedBy',' ','EN',1217,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.w3.org/2000/01/rdf-schema#label','Compiled','EN',1218,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.w3.org/2000/01/rdf-schema#seeAlso','','EN',1219,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.w3.org/2000/01/rdf-schema#comment','compiled state','EN',1220,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.w3.org/2000/01/rdf-schema#domain','http://www.tao.lu/Ontologies/TAOTest.rdf#Test','',1221,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget','http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox','',1222,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.w3.org/1999/02/22-rdf-syntax-ns#type','http://www.w3.org/1999/02/22-rdf-syntax-ns#Property','',1223,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.w3.org/2000/01/rdf-schema#range','http://www.tao.lu/Ontologies/generis.rdf#Boolean','',1224,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11'),
(9,'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled','http://www.w3.org/1999/02/22-rdf-syntax-ns#value','','EN',1225,'generis','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','2009-12-04 16:00:11');
/*!40000 ALTER TABLE `statements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscribee`
--

DROP TABLE IF EXISTS `subscribee`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `subscribee` (
  `Login` varchar(32) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  `URL` varchar(255) NOT NULL default '',
  `Type` varchar(255) NOT NULL,
  `IdSub` int(32) NOT NULL auto_increment,
  `DatabaseName` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`IdSub`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `subscribee`
--

LOCK TABLES `subscribee` WRITE;
/*!40000 ALTER TABLE `subscribee` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscribee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriber`
--

DROP TABLE IF EXISTS `subscriber`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `subscriber` (
  `Id` int(32) NOT NULL auto_increment,
  `Login` varchar(32) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  `LastVisit` varchar(32) NOT NULL default '',
  `enabled` tinyint(1) NOT NULL default '0',
  `ismember` int(32) NOT NULL default '0',
  `DatabaseName` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `subscriber`
--

LOCK TABLES `subscriber` WRITE;
/*!40000 ALTER TABLE `subscriber` DISABLE KEYS */;
INSERT INTO `subscriber` VALUES 
(21,'47072','e3d23b257cd19c27ca38fb7a8eeb9cd1','',1,1,''),
(22,'29200','fec73528cd9681706631f08c0f166dae','',1,1,''),
(25,'56078','b4fcb370c237271d1e9453614862944f','',1,1,''),
(24,'22100','0b47657d6bcf28d3ea29ccea75dec4bc','',1,1,'');
/*!40000 ALTER TABLE `subscriber` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscribersgroup`
--

DROP TABLE IF EXISTS `subscribersgroup`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `subscribersgroup` (
  `ID` int(32) NOT NULL auto_increment,
  `subgroupof` int(32) NOT NULL default '0',
  `Name` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `subscribersgroup`
--

LOCK TABLES `subscribersgroup` WRITE;
/*!40000 ALTER TABLE `subscribersgroup` DISABLE KEYS */;
INSERT INTO `subscribersgroup` VALUES 
(1,0,'ROOT'),
(2,1,'ROOTA'),
(3,1,'ROOTB'),
(4,2,'C'),
(5,3,'D');
/*!40000 ALTER TABLE `subscribersgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user` (
  `login` varchar(32) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `admin` tinyint(1) NOT NULL default '0',
  `usergroup` varchar(32) NOT NULL default '',
  `LastName` varchar(64) NOT NULL default '',
  `FirstName` varchar(64) NOT NULL default '',
  `E_Mail` varchar(128) NOT NULL default '',
  `Company` varchar(128) NOT NULL default '',
  `Deflg` char(2) NOT NULL default '',
  `enabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`login`),
  KEY `login` (`login`),
  KEY `usergroup` (`usergroup`),
  KEY `login_2` (`login`),
  KEY `usergroup_2` (`usergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES 
('demo','fe01ce2a7fbac8fafaed7c982a04e229',1,'admin','','',' ',' ','EN',0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-12-15 14:46:11
