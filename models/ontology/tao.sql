INSERT INTO `user` (`login`, `password`, `admin`, `usergroup`, `LastName`, `FirstName`, `E_Mail`, `Company`, `Deflg`, `enabled`) VALUES
('demo', 'fe01ce2a7fbac8fafaed7c982a04e229', 1, 'admin', '', '', ' ', ' ', 'EN', 0);


INSERT INTO `models` (`modelID`, `modelURI`, `baseURI`) VALUES
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#', 'http://www.tao.lu/Ontologies/TAO.rdf#');


INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`, `epoch`) VALUES
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#label', 'TAO Object', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Any ressource related to etesting', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#label', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Plugin', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.w3.org/2000/01/rdf-schema#Class', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', '2009-11-04 18:35:18');
