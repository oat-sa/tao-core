INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`, `epoch`) VALUES
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#List', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#List', 'http://www.w3.org/2000/01/rdf-schema#label', 'List', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#List', 'http://www.w3.org/2000/01/rdf-schema#comment', '', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),

(6, 'http://www.tao.lu/Ontologies/TAO.rdf#level', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#level', 'http://www.w3.org/2000/01/rdf-schema#label', 'level', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#level', 'http://www.w3.org/2000/01/rdf-schema#comment', '', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#level', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/Ontologies/TAO.rdf#List', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#level', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#level', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW())

(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Languages', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://www.tao.lu/Ontologies/TAO.rdf#List', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Languages', 'http://www.w3.org/2000/01/rdf-schema#label', 'Languages', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Languages', 'http://www.w3.org/2000/01/rdf-schema#comment', '', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]', NOW()),

(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAO.rdf#Languages', '',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN', 'http://www.w3.org/2000/01/rdf-schema#label', 'EN', 'EN ',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN', 'http://www.w3.org/2000/01/rdf-schema#comment', 'English', 'EN ',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN', 'http://www.tao.lu/Ontologies/TAO.rdf#level', '1', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),

(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangFR', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAO.rdf#Languages', '',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangFR', 'http://www.w3.org/2000/01/rdf-schema#label', 'FR', 'EN ',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangFR', 'http://www.w3.org/2000/01/rdf-schema#comment', 'French', 'EN ',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangFR', 'http://www.tao.lu/Ontologies/TAO.rdf#level', '2', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),

(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangDE', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAO.rdf#Languages', '',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangDE', 'http://www.w3.org/2000/01/rdf-schema#label', 'DE', 'EN ',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangDE', 'http://www.w3.org/2000/01/rdf-schema#comment', 'German', 'EN ',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangDE', 'http://www.tao.lu/Ontologies/TAO.rdf#level', '3', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),

(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangLU', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/Ontologies/TAO.rdf#Languages', '',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangLU', 'http://www.w3.org/2000/01/rdf-schema#label', 'LU', 'EN ',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangLU', 'http://www.w3.org/2000/01/rdf-schema#comment', 'Luxemburgish', 'EN ',  'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW()),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#LangLU', 'http://www.tao.lu/Ontologies/TAO.rdf#level', '4', '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]',  NOW());
