-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeu 17 Décembre 2009 à 14:49
-- Version du serveur: 5.1.36
-- Version de PHP: 5.2.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `taotrans_demo`
--

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `identifier` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(8192) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `cache`
--


-- --------------------------------------------------------

--
-- Structure de la table `extensions`
--

DROP TABLE IF EXISTS `extensions`;
CREATE TABLE IF NOT EXISTS `extensions` (
  `id` varchar(25) NOT NULL,
  `name` varchar(150) NOT NULL,
  `version` varchar(4) NOT NULL,
  `loaded` tinyint(1) NOT NULL,
  `loadAtStartUp` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `extensions`
--

INSERT INTO `extensions` (`id`, `name`, `version`, `loaded`, `loadAtStartUp`) VALUES
('tao', 'tao', '1.0', 1, 1),
('taoGroups', 'Tao Groups', '1.0', 1, 1),
('taoItems', 'Tao Items', '1.0', 1, 1),
('taoResults', 'Tao Results', '1.0', 1, 1),
('taoSubjects', 'Tao Subjects', '1.0', 1, 1),
('taoTests', 'Tao Test', '1.0', 1, 1),
('taoDelivery', 'taoDelivery', '1.0', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `grouplocaluser`
--

DROP TABLE IF EXISTS `grouplocaluser`;
CREATE TABLE IF NOT EXISTS `grouplocaluser` (
  `Name` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`Name`),
  KEY `Name` (`Name`),
  KEY `Name_2` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `grouplocaluser`
--

INSERT INTO `grouplocaluser` (`Name`) VALUES
('admin');

-- --------------------------------------------------------

--
-- Structure de la table `log_actions`
--

DROP TABLE IF EXISTS `log_actions`;
CREATE TABLE IF NOT EXISTS `log_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `model_id` int(11) NOT NULL DEFAULT '0',
  `user` varchar(255) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `descr_id` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(255) DEFAULT NULL,
  `details` longblob,
  PRIMARY KEY (`id`),
  KEY `idx_logactions_modelid` (`model_id`),
  KEY `idx_logactions_parentid` (`parent_id`),
  KEY `idx_logactions_user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;


-- --------------------------------------------------------

--
-- Structure de la table `log_action_descr`
--

DROP TABLE IF EXISTS `log_action_descr`;
CREATE TABLE IF NOT EXISTS `log_action_descr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_logactiondescr_description` (`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `log_action_descr`
--



-- --------------------------------------------------------

--
-- Structure de la table `models`
--

DROP TABLE IF EXISTS `models`;
CREATE TABLE IF NOT EXISTS `models` (
  `modelID` int(11) NOT NULL AUTO_INCREMENT,
  `modelURI` varchar(255) NOT NULL DEFAULT '',
  `baseURI` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`modelID`),
  KEY `idx_models_modelURI` (`modelURI`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Contenu de la table `models`
--

INSERT INTO `models` (`modelID`, `modelURI`, `baseURI`) VALUES
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'),
(5, 'http://www.w3.org/2000/01/rdf-schema#', 'http://www.w3.org/2000/01/rdf-schema#'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#', 'http://www.tao.lu/Ontologies/generis.rdf#'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#', 'http://www.tao.lu/Ontologies/TAO.rdf#'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.tao.lu/Ontologies/TAOResult.rdf#'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#', 'http://www.tao.lu/Ontologies/TAOItem.rdf#'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#', 'http://www.tao.lu/Ontologies/TAOGroup.rdf#'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#', 'http://www.tao.lu/Ontologies/TAOTest.rdf#'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#', 'http://www.tao.lu/Ontologies/TAOSubject.rdf#'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#', 'http://www.tao.lu/Ontologies/TAODelivery.rdf#');

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



-- --------------------------------------------------------

--
-- Structure de la table `statements`
--

DROP TABLE IF EXISTS `statements`;


CREATE TABLE IF NOT EXISTS `statements` (
  `modelID` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `predicate` varchar(255) NOT NULL DEFAULT '',
  `object` longtext,
  `l_language` varchar(255) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author` varchar(255) DEFAULT NULL,
  `stread` varchar(255) NOT NULL DEFAULT 'yyy[]',
  `stedit` varchar(255) NOT NULL DEFAULT 'yy-[]',
  `stdelete` varchar(255) NOT NULL DEFAULT 'y--[Administrators]',
  `epoch` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_statements_modelID` (`modelID`),
  KEY `idx_statements_subject` (`subject`),
  KEY `idx_statements_predicate` (`predicate`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1555 ;

--
-- Contenu de la table `statements`
--

INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.w3.org/2000/01/rdf-schema#label', 'Widget', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Specifies the form interface widget', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.w3.org/2000/01/rdf-schema#label', 'Widget Range Constraint', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.w3.org/2000/01/rdf-schema#comment', 'This property constrains widgets to certain types of ranges', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes', 'http://www.w3.org/2000/01/rdf-schema#label', 'Types of Widget Range Constraints', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of range constraints applicable to widgets', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', 'http://www.w3.org/2000/01/rdf-schema#label', 'resources', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Resources are any description, or any object identified by an URI', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraintTypes', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', 'http://www.w3.org/2000/01/rdf-schema#label', 'literals', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Any string (RDFS typde Literals)', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', 'http://www.w3.org/2000/01/rdf-schema#label', 'Widget Class', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of all possible widgets', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox', 'http://www.w3.org/2000/01/rdf-schema#label', 'Drop down menu', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox', 'http://www.w3.org/2000/01/rdf-schema#comment', 'In drop down menu, one may select 1 to N options', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox', 'http://www.w3.org/2000/01/rdf-schema#label', 'Radio button', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox', 'http://www.w3.org/2000/01/rdf-schema#comment', 'In radio boxes, one may select exactly one option', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox', 'http://www.w3.org/2000/01/rdf-schema#label', 'Check box', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox', 'http://www.w3.org/2000/01/rdf-schema#comment', 'In check boxes, one may select 0 to N options', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ClassTreeView', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ClassTreeView', 'http://www.w3.org/2000/01/rdf-schema#label', 'Class Tree View', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ClassTreeView', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Tree view widget displays the class tree starting from a given class level. the user selects a class', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ClassTreeView', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', 'http://www.w3.org/2000/01/rdf-schema#label', 'Instance Tree View', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Tree view widget displays the class tree starting from a given class level, at each level, the instance of the highlighted class are displayed for user selection', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton', 'http://www.w3.org/2000/01/rdf-schema#label', 'Expand Form', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton', 'http://www.w3.org/2000/01/rdf-schema#comment', 'A button to expand the form of properties of the class the target instance belongs to', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ExpandFormButton', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', 'http://www.w3.org/2000/01/rdf-schema#label', 'A Text Box', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', 'http://www.w3.org/2000/01/rdf-schema#comment', 'A particular text box', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', '1', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox', 'http://www.w3.org/2000/01/rdf-schema#label', 'Hidden Box', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Content is hidden', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', '1', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea', 'http://www.w3.org/2000/01/rdf-schema#label', 'HTMLArea', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea', 'http://www.w3.org/2000/01/rdf-schema#comment', 'An html area', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', '1', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea', 'http://www.w3.org/2000/01/rdf-schema#label', 'A Text Area', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea', 'http://www.w3.org/2000/01/rdf-schema#comment', 'A particular text Area', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox', 'http://www.w3.org/2000/01/rdf-schema#label', 'ListBox', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox', 'http://www.w3.org/2000/01/rdf-schema#comment', 'ListBox', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ListBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#WidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq', 'http://www.w3.org/2000/01/rdf-schema#label', 'Sequence', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Sequence', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#seq', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#rangeConstraint-Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', 'http://www.w3.org/2000/01/rdf-schema#label', 'Text Height', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The heigth of the text box, expressed in number of lines', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextWidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Integer', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/TextBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', '3', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', 'http://www.w3.org/2000/01/rdf-schema#label', 'Text Length', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The length of the text box, expressed in number of characters', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextWidgetClass', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Integer', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', '3', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/2000/01/rdf-schema#label', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', '255', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', '255', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', '10', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textLength', '255', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.w3.org/2000/01/rdf-schema#comment', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(3, 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#textHeight', '10', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2002/07/owl#Ontology', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'http://purl.org/dc/elements/1.1/title', 'The RDF Vocabulary (RDF)', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'http://purl.org/dc/elements/1.1/description', 'This is the RDF Schema for the RDF vocabulary defined in the RDF namespace.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#label', 'type', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The subject is an instance of a class.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'http://www.w3.org/2000/01/rdf-schema#label', 'Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of RDF properties.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement', 'http://www.w3.org/2000/01/rdf-schema#label', 'Statement', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of RDF statements.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject', 'http://www.w3.org/2000/01/rdf-schema#label', 'subject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The subject of the subject RDF statement.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate', 'http://www.w3.org/2000/01/rdf-schema#label', 'predicate', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The predicate of the subject RDF statement.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object', 'http://www.w3.org/2000/01/rdf-schema#label', 'object', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The object of the subject RDF statement.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag', 'http://www.w3.org/2000/01/rdf-schema#label', 'Bag', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of unordered containers.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Bag', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Container', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq', 'http://www.w3.org/2000/01/rdf-schema#label', 'Seq', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of ordered containers.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Container', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt', 'http://www.w3.org/2000/01/rdf-schema#label', 'Alt', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of containers of alternatives.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Alt', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Container', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', 'http://www.w3.org/2000/01/rdf-schema#label', 'value', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Idiomatic property used for structured values.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', 'http://www.w3.org/2000/01/rdf-schema#label', 'List', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of RDF Lists.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil', 'http://www.w3.org/2000/01/rdf-schema#label', 'nil', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The empty list, with no items in it. If the rest of a list is nil then the list has no more items in it.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#first', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#first', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#first', 'http://www.w3.org/2000/01/rdf-schema#label', 'first', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#first', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The first item in the subject RDF list.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#first', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#first', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest', 'http://www.w3.org/2000/01/rdf-schema#label', 'rest', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The rest of the subject RDF list after the first item.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#rest', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#List', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Datatype', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral', 'http://www.w3.org/2000/01/rdf-schema#label', 'XMLLiteral', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of XML literal values.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(4, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.w3.org/2000/01/rdf-schema-more', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2002/07/owl#Ontology', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#', 'http://purl.org/dc/elements/1.1/title', 'The RDF Schema vocabulary (RDFS)', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Resource', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Resource', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Resource', 'http://www.w3.org/2000/01/rdf-schema#label', 'Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Resource', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class resource, everything.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Class', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Class', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Class', 'http://www.w3.org/2000/01/rdf-schema#label', 'Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Class', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of classes.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Class', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#label', 'subClassOf', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The subject is a subclass of a class.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf', 'http://www.w3.org/2000/01/rdf-schema#label', 'subPropertyOf', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The subject is a subproperty of a property.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#comment', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#comment', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#comment', 'http://www.w3.org/2000/01/rdf-schema#label', 'comment', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#comment', 'http://www.w3.org/2000/01/rdf-schema#comment', 'A description of the subject resource.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#comment', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#comment', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#label', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#label', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#label', 'http://www.w3.org/2000/01/rdf-schema#label', 'label', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#label', 'http://www.w3.org/2000/01/rdf-schema#comment', 'A human-readable name for the subject.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#label', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#label', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#label', 'domain', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#comment', 'A domain of the subject property.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#label', 'range', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#comment', 'A range of the subject property.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]');
INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(5, 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.w3.org/2000/01/rdf-schema#label', 'seeAlso', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Further information about the subject resource.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf', 'http://www.w3.org/2000/01/rdf-schema#seeAlso', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#label', 'isDefinedBy', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The defininition of the subject resource.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Literal', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Literal', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Literal', 'http://www.w3.org/2000/01/rdf-schema#label', 'Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Literal', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of literal values, eg. textual strings and integers.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Literal', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Container', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Container', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Container', 'http://www.w3.org/2000/01/rdf-schema#label', 'Container', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Container', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Container', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of RDF containers.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty', 'http://www.w3.org/2000/01/rdf-schema#label', 'ContainerMembershipProperty', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of container membership properties, rdf:_1, rdf:_2, ...,\n                    all of which are sub-properties of ''member''.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#ContainerMembershipProperty', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#member', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#member', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#member', 'http://www.w3.org/2000/01/rdf-schema#label', 'member', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#member', 'http://www.w3.org/2000/01/rdf-schema#comment', 'A member of the subject resource.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#member', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#member', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Datatype', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Datatype', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', 'http://www.w3.org/2000/01/rdf-schema#', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Datatype', 'http://www.w3.org/2000/01/rdf-schema#label', 'Datatype', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Datatype', 'http://www.w3.org/2000/01/rdf-schema#comment', 'The class of RDF datatypes.', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#Datatype', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(5, 'http://www.w3.org/2000/01/rdf-schema#', 'http://www.w3.org/2000/01/rdf-schema#seeAlso', 'http://www.w3.org/2000/01/rdf-schema-more', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Object', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Any ressource related to etesting', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#label', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource', 'http://www.w3.org/2000/01/rdf-schema#label', 'generis_Ressource', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource', 'http://www.w3.org/2000/01/rdf-schema#comment', 'generis_Ressource', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Model', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.w3.org/2000/01/rdf-schema#Resource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Model', 'http://www.w3.org/2000/01/rdf-schema#label', 'Model', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Model', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Model', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#label', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/generis.rdf#Model', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.w3.org/2000/01/rdf-schema#label', 'is_language_dependent', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.w3.org/2000/01/rdf-schema#comment', 'is_language_dependent', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/generis.rdf#Boolean', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'is_language_dependent', 'http://www.tao.lu/Ontologies/generis.rdf#False', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Boolean', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Boolean', 'http://www.w3.org/2000/01/rdf-schema#label', 'Boolean', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#Boolean', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Boolean', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#True', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/generis.rdf#Boolean', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#True', 'http://www.w3.org/2000/01/rdf-schema#label', 'True', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#True', 'http://www.w3.org/2000/01/rdf-schema#comment', 'True', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#False', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/generis.rdf#Boolean', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#False', 'http://www.w3.org/2000/01/rdf-schema#label', 'False', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(7, 'http://www.tao.lu/Ontologies/generis.rdf#False', 'http://www.w3.org/2000/01/rdf-schema#comment', 'False', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'FR', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'DE', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'PT', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Object', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Any ressource related to etesting', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#label', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Object', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Any ressource related to etesting', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#label', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', 'http://www.w3.org/2000/01/rdf-schema#label', 'Result', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Result', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/generis.rdf#Model', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Result Model', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.w3.org/2000/01/rdf-schema#comment', 'TAO Result Model', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'TLAresults', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'uploadresultserver', '', 'demo', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'hypergraph', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/2000/01/rdf-schema#label', 'ResultContent', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/2000/01/rdf-schema#comment', 'ResultContent', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(13, 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', 'http://www.w3.org/2000/01/rdf-schema#label', 'Item', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Item', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', 'http://www.w3.org/2000/01/rdf-schema#label', 'ItemContent', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', 'http://www.w3.org/2000/01/rdf-schema#comment', 'ItemContent', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Authoring', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', 'http://www.w3.org/2000/01/rdf-schema#label', 'ItemModels', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', 'http://www.w3.org/2000/01/rdf-schema#comment', 'ItemModels', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.w3.org/2000/01/rdf-schema#label', 'ItemModel', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.w3.org/2000/01/rdf-schema#comment', 'ItemModel', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'http://www.w3.org/2000/01/rdf-schema#label', 'Runtime<br />', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'http://www.w3.org/2000/01/rdf-schema#comment', '\r\nRuntime', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM', 'http://www.w3.org/2000/01/rdf-schema#label', 'QCM', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM', 'http://www.w3.org/2000/01/rdf-schema#comment', 'QCM', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM', 'SWFFile', 'tao_item.swf', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM', 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'tao_item.swf', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs', 'http://www.w3.org/2000/01/rdf-schema#label', 'Kohs', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Kohs', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs', 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'kohs_passation.swf', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest', 'http://www.w3.org/2000/01/rdf-schema#label', 'C-Test', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest', 'http://www.w3.org/2000/01/rdf-schema#comment', 'C-Test', 'EN ', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest', 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'ctest_item.swf', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.tao.lu/Ontologies/generis.rdf#False', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', '', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Authoring', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.w3.org/2000/01/rdf-schema#label', 'Authoring', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.w3.org/2000/01/rdf-schema#seeAlso', '', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', '  ', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile', 'hawai/hawai.swf', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', '/waterphenix/index.php', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', '', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Hyper Adaptive WorkArea Item', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', 'http://www.w3.org/2000/01/rdf-schema#label', 'HAWAI', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', 'http://www.w3.org/2000/01/rdf-schema#seeAlso', '', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', '  ', 'EN ', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group', 'http://www.w3.org/2000/01/rdf-schema#label', 'Group', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Group', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members', 'http://www.w3.org/2000/01/rdf-schema#label', 'Members', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Members', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Members', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/generis.rdf#Model', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Group Model', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf', 'http://www.w3.org/2000/01/rdf-schema#comment', 'TAO Group Model', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'hypergraph', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests', 'http://www.w3.org/2000/01/rdf-schema#label', 'Tests', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Tests', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(11, 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Tests', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'FR', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'DE', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'PT', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', ' ', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.w3.org/2000/01/rdf-schema#label', 'Active', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.w3.org/2000/01/rdf-schema#seeAlso', '', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.w3.org/2000/01/rdf-schema#comment', 'active / inactive state', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/generis.rdf#Boolean', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', '', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', 'http://www.w3.org/2000/01/rdf-schema#label', 'Test', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Test', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://www.w3.org/2000/01/rdf-schema#label', 'Related Items', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Related Items', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeView', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/generis.rdf#Model', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Test Model', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf', 'http://www.w3.org/2000/01/rdf-schema#comment', 'TAO Test Model', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'hypergraph', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent', 'http://www.w3.org/2000/01/rdf-schema#label', 'TestContent', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent', 'http://www.w3.org/2000/01/rdf-schema#comment', 'TestContent', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Authoring', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject', 'http://www.w3.org/2000/01/rdf-schema#label', 'Testee', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Specifies the form interface widget', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login', 'http://www.w3.org/2000/01/rdf-schema#label', 'Login', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Login', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Login', 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.tao.lu/Ontologies/generis.rdf#False', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password', 'http://www.w3.org/2000/01/rdf-schema#label', 'Password', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Password', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Password', 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.tao.lu/Ontologies/generis.rdf#False', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/generis.rdf#Model', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Subject Model', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf', 'http://www.w3.org/2000/01/rdf-schema#comment', 'TAO Subject Model', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf', 'http://www.tao.lu/Ontologies/generis.rdf#Plugin', 'hypergraph', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(12, 'http://www.tao.lu/Ontologies/TAOSubject.rdf#125256679264748', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOSubject.rdf#1251875577236', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest', 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'C_TestAuth.swf', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]');
INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs', 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'kohs_authoring.swf', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM', 'http://www.tao.lu/Ontologies/TAOItem.rdf#12580164649880', 'QCMauthoring.php', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy', ' ', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.w3.org/2000/01/rdf-schema#label', 'Compiled', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.w3.org/2000/01/rdf-schema#seeAlso', '', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.w3.org/2000/01/rdf-schema#comment', 'compiled state', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/generis.rdf#Boolean', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value', '', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(9, 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent', 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.tao.lu/Ontologies/generis.rdf#True', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(10, 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', 'http://www.tao.lu/Ontologies/generis.rdf#is_language_dependent', 'http://www.tao.lu/Ontologies/generis.rdf#True', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', 'http://www.w3.org/2000/01/rdf-schema#label', 'TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', 'http://www.w3.org/2000/01/rdf-schema#comment', '$comment', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ID_TEST', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ID_TEST', 'http://www.w3.org/2000/01/rdf-schema#label', 'ID_TEST', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ID_TEST', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment ID_TEST', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ID_TEST', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LABEL_TEST', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LABEL_TEST', 'http://www.w3.org/2000/01/rdf-schema#label', 'LABEL_TEST', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LABEL_TEST', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment LABEL_TEST', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LABEL_TEST', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#COMMENT_TEST', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#COMMENT_TEST', 'http://www.w3.org/2000/01/rdf-schema#label', 'COMMENT_TEST', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#COMMENT_TEST', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment COMMENT_TEST', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#COMMENT_TEST', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#HASSCORINGMETHOD_NAME', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#HASSCORINGMETHOD_NAME', 'http://www.w3.org/2000/01/rdf-schema#label', 'HASSCORINGMETHOD_NAME', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#HASSCORINGMETHOD_NAME', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment HASSCORINGMETHOD_NAME', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#HASSCORINGMETHOD_NAME', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SCORE_VALUE', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SCORE_VALUE', 'http://www.w3.org/2000/01/rdf-schema#label', 'SCORE_VALUE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SCORE_VALUE', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment SCORE_VALUE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SCORE_VALUE', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CUMULMODEL_NAME', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CUMULMODEL_NAME', 'http://www.w3.org/2000/01/rdf-schema#label', 'CUMULMODEL_NAME', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CUMULMODEL_NAME', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment CUMULMODEL_NAME', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CUMULMODEL_NAME', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_ID', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_ID', 'http://www.w3.org/2000/01/rdf-schema#label', 'SUBJECT_ID', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_ID', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment SUBJECT_ID', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_ID', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_LABEL', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_LABEL', 'http://www.w3.org/2000/01/rdf-schema#label', 'SUBJECT_LABEL', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_LABEL', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment SUBJECT_LABEL', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_LABEL', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#TESTBEHAVIOR', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#TESTBEHAVIOR', 'http://www.w3.org/2000/01/rdf-schema#label', 'TESTBEHAVIOR', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#TESTBEHAVIOR', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment TESTBEHAVIOR', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#TESTBEHAVIOR', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM', 'http://www.w3.org/2000/01/rdf-schema#label', 'CITEM', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment CITEM', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#TEST_CLASS', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', 'http://www.w3.org/2000/01/rdf-schema#label', 'CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', 'http://www.w3.org/2000/01/rdf-schema#comment', '$comment', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#WEIGHT', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#WEIGHT', 'http://www.w3.org/2000/01/rdf-schema#label', 'WEIGHT', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#WEIGHT', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment WEIGHT', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#WEIGHT', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#MODEL', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#MODEL', 'http://www.w3.org/2000/01/rdf-schema#label', 'MODEL', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#MODEL', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment MODEL', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#MODEL', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#DEFINITIONFILE', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#DEFINITIONFILE', 'http://www.w3.org/2000/01/rdf-schema#label', 'DEFINITIONFILE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#DEFINITIONFILE', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment DEFINITIONFILE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#DEFINITIONFILE', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SEQUENCE', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SEQUENCE', 'http://www.w3.org/2000/01/rdf-schema#label', 'SEQUENCE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SEQUENCE', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment SEQUENCE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SEQUENCE', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ENDORSMENT', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ENDORSMENT', 'http://www.w3.org/2000/01/rdf-schema#label', 'ENDORSMENT', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ENDORSMENT', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment ENDORSMENT', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ENDORSMENT', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMUSAGE', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMUSAGE', 'http://www.w3.org/2000/01/rdf-schema#label', 'ITEMUSAGE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMUSAGE', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment ITEMUSAGE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMUSAGE', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR', 'http://www.w3.org/2000/01/rdf-schema#label', 'ITEMBEHAVIOR', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment ITEMBEHAVIOR', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR_CLASS', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2000/01/rdf-schema#Class', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR_CLASS', 'http://www.w3.org/2000/01/rdf-schema#label', 'ITEMBEHAVIOR_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR_CLASS', 'http://www.w3.org/2000/01/rdf-schema#comment', '$comment', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LISTENERNAME', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LISTENERNAME', 'http://www.w3.org/2000/01/rdf-schema#label', 'LISTENERNAME', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LISTENERNAME', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment LISTENERNAME', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LISTENERNAME', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LISTENERVALUE', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LISTENERVALUE', 'http://www.w3.org/2000/01/rdf-schema#label', 'LISTENERVALUE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LISTENERVALUE', 'http://www.w3.org/2000/01/rdf-schema#comment', 'comment LISTENERVALUE', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#LISTENERVALUE', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR_CLASS', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#CITEM', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://127.0.0.1/middleware/demo.rdf#CITEM_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://127.0.0.1/middleware/demo.rdf#ITEMBEHAVIOR_CLASS', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_ID', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#ID_TEST', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261571812010328500', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.tao.lu/Ontologies/TAOItem.rdf#125933161031263', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261571812010328500', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', '<?xml version="1.0" encoding="UTF-8"?>\n<black:Manifest \n	xmlns:black="http://www.exulis.lu/black.rdfs#" \n	xmlns:rdfs="http://www.w3.org/TR/1999/PR-rdf-schema-19990303#" \n	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" \n	xmlns:tao="http://www.tao.lu/tao.rdfs#" \n	rdf:ID="http://127.0.0.1/middleware/demo.rdf#i1261571812010328500" \n>\n\n	<!-- @see taoItems_models_classes_ItemsService::getAuthoringFile for URI/FILE resolution -->\n	<root reference="http://127.0.0.1/middleware/demo.rdf#i1261571812010328500" />\n\n</black:Manifest>\n', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261571812010328500', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261571812010328500', 'http://www.w3.org/2000/01/rdf-schema#label', 'Hawai Item', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261565165029846800', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261565165029846800', 'http://www.w3.org/2000/01/rdf-schema#label', 'Kohs Item', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261565165029846800', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', '<?xml version="1.0" encoding="UTF-8"?><tao:KOHS xmlns:rdf=''http://www.w3.org/1999/02/22-rdf-syntax-ns#'' rdf:Id="http://127.0.0.1/middleware/demo.rdf#i1261565165029846800" xmlns:tao="http://www.tao.lu/tao.rdfs#" xmlns:rdfs="http://www.w3.org/TR/1999/PR-rdf-schema-19990303#"><tao:MATRIX>152666354</tao:MATRIX></tao:KOHS>', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261565165029846800', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261565291089420300', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261565291089420300', 'http://www.w3.org/2000/01/rdf-schema#label', 'QCM Item', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261565291089420300', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261565291089420300', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', '<?xml version=''1.0'' encoding=''UTF-8'' ?>\r\n<tao:ITEM xmlns:rdf=''http://www.w3.org/1999/02/22-rdf-syntax-ns#'' rdf:ID="http://127.0.0.1/middleware/demo.rdf#i1261565291089420300" xmlns:tao=''http://www.tao.lu/tao.rdfs#''\r\n xmlns:rdfs=''http://www.w3.org/TR/1999/PR-rdf-schema-19990303#''>\r\n<rdfs:LABEL lang="EN">QCM Item</rdfs:LABEL>\r\n<rdfs:COMMENT lang="EN"></rdfs:COMMENT>\r\n\r\n<tao:DISPLAYALLINQUIRIES></tao:DISPLAYALLINQUIRIES>\r\n\r\n<tao:DURATION></tao:DURATION>\r\n\r\n				<tao:ITEMPRESENTATION>\r\n						<xul>\r\n							<stylesheet id="item_stylesheet" src="./item.css"/>\r\n							<box id="itemContainer_box" class="item">\r\n								\r\n								<label id="problem_textbox" left="5" top="5" multiline="true" wrap="true" value="Hello, this is a &lt;i&gt;simple &lt;/i&gt;sample of a &lt;font color=&quot;#ff0000&quot;&gt;QCM &lt;/font&gt;Item. \r\n\r\n&lt;br /&gt;It offer you different set of features to build your Items. &lt;br /&gt;\r\n\r\n&lt;br /&gt;"/>\r\n								\r\n								<box id="inquiryContainer_box" left="5" top="100"/>\r\n								\r\n							</box>\r\n						</xul>\r\n					</tao:ITEMPRESENTATION>\r\n			\r\n<tao:ITEMLISTENERS></tao:ITEMLISTENERS><tao:PROBLEM lang="EN" type="String">Hello, this is a &lt;i&gt;simple &lt;/i&gt;sample of a &lt;font color=&quot;#ff0000&quot;&gt;QCM &lt;/font&gt;Item. \r\n\r\n&lt;br /&gt;It offer you different set of features to build your Items. &lt;br /&gt;\r\n\r\n&lt;br /&gt;</tao:PROBLEM><tao:INQUIRY order="1"><tao:QUESTION lang="EN" type="String">What type of Item is the most recent in TAO ?</tao:QUESTION>\r\n			<tao:INQUIRYDESCRIPTION><tao:PROPOSITIONTYPE>Exclusive Choice</tao:PROPOSITIONTYPE>\r\n			<tao:WIDGET>FLASH Radio Button</tao:WIDGET>\r\n			<tao:PROPLISTENERS><tao:ITEMBEHAVIOR tao:LISTENERNAME="Answered : What type of Item is the most recent in TAO ?" src="#{XPATH(/tao:ITEM/tao:INQUIRY[@order=1]/tao:INQUIRYDESCRIPTION/tao:HASPRESENTATIONLAYER/xul/box/box/radiogroup)}#"/></tao:PROPLISTENERS>\r\n			<tao:ANSWERTYPE>Exclusive Vector</tao:ANSWERTYPE>\r\n			<tao:EVALUATIONRULE>AND.swf</tao:EVALUATIONRULE>\r\n			<tao:HASGUIDE>technicalID.hlp</tao:HASGUIDE>\r\n			\r\n			<tao:HASPRESENTATIONLAYER><xul>\r\n                    <box id="inquiryContainer_box" left="0" top="0">\r\n					<textbox id="question_textbox" wrap="true" style="borderStyle:none" readonly="true" width="700" height="45" left="0" top="0" class="question" value="What type of Item is the most recent in TAO ?" />\r\n            			\r\n						<box id="propositions_box" left="10" top="13">\r\n                        <radiogroup id="propositions_radiogroup">\r\n								<radio id="proposition_1_radio" left="5" top="30" width="1000" selected="false" label="QCM Items"></radio>\r\n								<radio id="proposition_2_radio" left="5" top="50" width="1000" selected="false" label="Khos Items"></radio>\r\n								<radio id="proposition_3_radio" left="5" top="70" width="1000" selected="false" label="C-test Items"></radio>\r\n								<radio id="proposition_4_radio" left="5" top="90" width="1000" selected="false" label="HAWAI Items"></radio></radiogroup></box>\r\n                       \r\n                    </box>\r\n					\r\n                </xul></tao:HASPRESENTATIONLAYER>\r\n			\r\n			\r\n			<tao:LISTPROPOSITION><tao:PROPOSITION lang="EN" type="String" Id="1" order="1" answer="0">QCM Items</tao:PROPOSITION><tao:PROPOSITION lang="EN" type="String" Id="2" order="2" answer="0">Khos Items</tao:PROPOSITION><tao:PROPOSITION lang="EN" type="String" Id="3" order="3" answer="0">C-test Items</tao:PROPOSITION><tao:PROPOSITION lang="EN" type="String" Id="4" order="4" answer="0">HAWAI Items</tao:PROPOSITION></tao:LISTPROPOSITION>\r\n			<tao:HASANSWER>0001</tao:HASANSWER>\r\n			</tao:INQUIRYDESCRIPTION>\r\n			</tao:INQUIRY></tao:ITEM>', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#TestContent', '<?xml version=''1.0'' encoding=''UTF-8'' ?>\r\n				<tao:TEST xmlns:rdf=''http://www.w3.org/1999/02/22-rdf-syntax-ns#'' rdf:ID="http://127.0.0.1/middleware/demo.rdf#i1261572267020194300" xmlns:tao=''http://www.tao.lu/tao.rdfs#'' xmlns:rdfs=''http://www.w3.org/TR/1999/PR-rdf-schema-19990303#''>\r\n					<rdfs:LABEL lang="EN">Test_1</rdfs:LABEL>\r\n					<rdfs:COMMENT lang="EN"></rdfs:COMMENT><tao:TESTPRESENTATION>\r\n			<xul>\r\n				<stylesheet id="test_stylesheet" src="./test.css" />\r\n				<box id="testContainer_box"><box id="itemContainer_box" left="100" top="65"/>\r\n						<button id="prevItem_button" left="130" url="http://www.tao.lu/middleware/itempics/default/left.swf" top="400" label="Back" image="item_previous.jpg" disabled="true" oncommand="tao_test.prevItem"/>\r\n						 <button id="nextItem_button" left="200" url="http://www.tao.lu/middleware/itempics/default/right.swf" top="400" label="Next" image="item_next.jpg" disabled="true" oncommand="tao_test.nextItem"/>\r\n	        	</box>\r\n			</xul>\r\n		</tao:TESTPRESENTATION>	\r\n					<tao:PASSWORD></tao:PASSWORD>\r\n					<tao:DURATION></tao:DURATION>\r\n					<tao:HASSEQUENCEMODE  >SEQUENTIAL</tao:HASSEQUENCEMODE>\r\n					<tao:PREVIEW>FALSE</tao:PREVIEW>\r\n					<tao:REVIEW>FALSE</tao:REVIEW>\r\n					<tao:HOLDABLE>TRUE</tao:HOLDABLE>\r\n					<tao:RESPONSEPATTERN>dichotomic</tao:RESPONSEPATTERN>\r\n					<tao:HASSCORINGMETHOD Qmin="" Qmax="" Qiter="">CLASSICAL RATIO</tao:HASSCORINGMETHOD>\r\n					<tao:TESTLISTENERS></tao:TESTLISTENERS>\r\n					<tao:CUMULMODEL>CLASSICAL</tao:CUMULMODEL><tao:LAUNCH plugin="CLLPlugin"><cll:threshold></cll:threshold><cll:threshold></cll:threshold><cll:threshold></cll:threshold></tao:LAUNCH><tao:CITEM weight="1" Sequence="1" itemModel=''tao_item.swf''  >http://127.0.0.1/middleware/demo.rdf#i1261565291089420300</tao:CITEM><tao:CITEM weight="1" Sequence="2" itemModel=''ctest_item.swf''  >http://127.0.0.1/middleware/demo.rdf#i1261572123013289000</tao:CITEM><tao:CITEM weight="1" Sequence="3" itemModel=''kohs_passation.swf''  >http://127.0.0.1/middleware/demo.rdf#i1261565165029846800</tao:CITEM><tao:CITEM weight="1" Sequence="4" itemModel=''hawai/hawai.swf''  >http://127.0.0.1/middleware/demo.rdf#i1261571812010328500</tao:CITEM></tao:TEST>', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#active', 'http://www.tao.lu/Ontologies/generis.rdf#True', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://127.0.0.1/middleware/demo.rdf#i1261572123013289000', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572123013289000', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572123013289000', 'http://www.w3.org/2000/01/rdf-schema#label', 'CTest Item', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.tao.lu/Ontologies/generis.rdf#True', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572123013289000', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel', 'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572123013289000', 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent', '<?xml version=''1.0'' encoding=''UTF-8''?><tao:ITEM xmlns:rdf=''http://www.w3.org/1999/02/22-rdf-syntax-ns#'' rdf:ID=''http://127.0.0.1/middleware/demo.rdf#i1261572123013289000'' xmlns:tao=''http://www.tao.lu/tao.rdfs'' xmlns:rdfs=''http://www.w3.org/2000/01/rdf-schema#''>\n										<rdfs:LABEL lang=''EN''>CTest Item</rdfs:LABEL>\n										<rdfs:COMMENT lang=''EN''></rdfs:COMMENT><CTInfos><Text>TAO is the french acronym for Tes__________ Ass__________ par Ordin__________ (Computer Based Testing).\rThe TAO fram__________ provides a general and open architecture for computer-assisted test development and delivery, with the potential to respond to the whole range of evaluation needs.\rThe TAO platform provides t__________ all the act__________ of the entire computer-based assessment process a comprehensive set of functio__________ enabling the creation, the manag__________ and the delivery of electronic assess__________</Text><Timer>30</Timer><Words>Testing,AssistÃ©,Ordinateur,framework,to,actors,functionalities,management,,assessments.</Words><Ports>ting,istÃ©,ateur,ework,o,ors,nalities,ement,,ments.</Ports><Coords>304/21/110,453/21/110,48/46/110,126/71/110,250/146/110,451/146/110,80/196/110,481/196/110,319/221/110</Coords><Undo></Undo></CTInfos></tao:ITEM>', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', 'EN', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.w3.org/2000/01/rdf-schema#label', 'Test_1', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://127.0.0.1/middleware/demo.rdf#i1261571812010328500', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://127.0.0.1/middleware/demo.rdf#i1261565165029846800', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems', 'http://127.0.0.1/middleware/demo.rdf#i1261565291089420300', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', '', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(8, 'http://127.0.0.1/middleware/demo.rdf#i1261572267020194300', 'http://www.tao.lu/Ontologies/TAOTest.rdf#compiled', 'http://www.tao.lu/Ontologies/generis.rdf#True', '', 'tao', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]');

-- --------------------------------------------------------

--
-- Structure de la table `subscribee`
--

DROP TABLE IF EXISTS `subscribee`;
CREATE TABLE IF NOT EXISTS `subscribee` (
  `Login` varchar(32) NOT NULL DEFAULT '',
  `Password` varchar(32) NOT NULL DEFAULT '',
  `URL` varchar(255) NOT NULL DEFAULT '',
  `Type` varchar(255) NOT NULL,
  `IdSub` int(32) NOT NULL AUTO_INCREMENT,
  `DatabaseName` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`IdSub`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `subscribee`
--


-- --------------------------------------------------------

--
-- Structure de la table `subscriber`
--

DROP TABLE IF EXISTS `subscriber`;
CREATE TABLE IF NOT EXISTS `subscriber` (
  `Id` int(32) NOT NULL AUTO_INCREMENT,
  `Login` varchar(32) NOT NULL DEFAULT '',
  `Password` varchar(32) NOT NULL DEFAULT '',
  `LastVisit` varchar(32) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `ismember` int(32) NOT NULL DEFAULT '0',
  `DatabaseName` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Contenu de la table `subscriber`
--

INSERT INTO `subscriber` (`Id`, `Login`, `Password`, `LastVisit`, `enabled`, `ismember`, `DatabaseName`) VALUES
(21, '47072', 'e3d23b257cd19c27ca38fb7a8eeb9cd1', '', 1, 1, ''),
(22, '29200', 'fec73528cd9681706631f08c0f166dae', '', 1, 1, ''),
(25, '56078', 'b4fcb370c237271d1e9453614862944f', '', 1, 1, ''),
(24, '22100', '0b47657d6bcf28d3ea29ccea75dec4bc', '', 1, 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `subscribersgroup`
--

DROP TABLE IF EXISTS `subscribersgroup`;
CREATE TABLE IF NOT EXISTS `subscribersgroup` (
  `ID` int(32) NOT NULL AUTO_INCREMENT,
  `subgroupof` int(32) NOT NULL DEFAULT '0',
  `Name` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Contenu de la table `subscribersgroup`
--

INSERT INTO `subscribersgroup` (`ID`, `subgroupof`, `Name`) VALUES
(1, 0, 'ROOT'),
(2, 1, 'ROOTA'),
(3, 1, 'ROOTB'),
(4, 2, 'C'),
(5, 3, 'D');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `login` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `usergroup` varchar(32) NOT NULL DEFAULT '',
  `LastName` varchar(64) NOT NULL DEFAULT '',
  `FirstName` varchar(64) NOT NULL DEFAULT '',
  `E_Mail` varchar(128) NOT NULL DEFAULT '',
  `Company` varchar(128) NOT NULL DEFAULT '',
  `Deflg` char(2) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`login`),
  KEY `login` (`login`),
  KEY `usergroup` (`usergroup`),
  KEY `login_2` (`login`),
  KEY `usergroup_2` (`usergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `user`
--

INSERT INTO `user` (`login`, `password`, `admin`, `usergroup`, `LastName`, `FirstName`, `E_Mail`, `Company`, `Deflg`, `enabled`) VALUES
('generis', 'b01a52f727b0810639526fe2c8188331', 1, 'admin', '', '', '', '', '', 0);

-- --------------------------------------------------------

--
-- Structure de la table `_mask`
--

DROP TABLE IF EXISTS `_mask`;
CREATE TABLE IF NOT EXISTS `_mask` (
  `user` varchar(255) NOT NULL DEFAULT '',
  `Scope` varchar(255) NOT NULL DEFAULT '',
  `Method` varchar(255) NOT NULL DEFAULT '',
  `onAssertPrivileges` longtext NOT NULL,
  `_comment` varchar(255) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `_mask`
--

