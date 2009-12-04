INSERT INTO `user` (`login`, `password`, `admin`, `usergroup`, `LastName`, `FirstName`, `E_Mail`, `Company`, `Deflg`, `enabled`) VALUES
('demo', 'fe01ce2a7fbac8fafaed7c982a04e229', 1, 'admin', '', '', ' ', ' ', 'EN', 0);


INSERT INTO `models` (`modelID`, `modelURI`, `baseURI`) VALUES
(8, 'http://127.0.0.1/middleware/demo.rdf', 'http://127.0.0.1/middleware/demo.rdf#'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#', 'http://www.tao.lu/Ontologies/TAO.rdf#');

INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(8, 'lg', 'lg', 'lg', 'FR', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'DE', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(8, 'lg', 'lg', 'lg', 'PT', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]');


INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 0x687474703a2f2f7777772e74616f2e6c752f4f6e746f6c6f676965732f67656e657269732e7264662367656e657269735f526573736f75726365, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#label', 0x54414f204f626a656374, 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#comment', 0x416e7920726573736f757263652072656c6174656420746f206574657374696e67, 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 0x687474703a2f2f7777772e77332e6f72672f313939392f30322f32322d7264662d73796e7461782d6e732350726f7065727479, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#label', 0x506c7567696e, 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#comment', 0x506c7567696e, 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#domain', 0x687474703a2f2f7777772e77332e6f72672f323030302f30312f7264662d736368656d6123436c617373, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#range', 0x687474703a2f2f7777772e77332e6f72672f323030302f30312f7264662d736368656d61234c69746572616c, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 0x687474703a2f2f7777772e74616f2e6c752f6461746174797065732f576964676574446566696e6974696f6e732e7264662354657874426f78, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 0x687474703a2f2f7777772e74616f2e6c752f4f6e746f6c6f676965732f67656e657269732e7264662367656e657269735f526573736f75726365, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#label', 0x54414f204f626a656374, 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject', 'http://www.w3.org/2000/01/rdf-schema#comment', 0x416e7920726573736f757263652072656c6174656420746f206574657374696e67, 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 0x687474703a2f2f7777772e77332e6f72672f313939392f30322f32322d7264662d73796e7461782d6e732350726f7065727479, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#label', 0x506c7567696e, 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#comment', 0x506c7567696e, 'EN', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#domain', 0x687474703a2f2f7777772e77332e6f72672f323030302f30312f7264662d736368656d6123436c617373, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.w3.org/2000/01/rdf-schema#range', 0x687474703a2f2f7777772e77332e6f72672f323030302f30312f7264662d736368656d61234c69746572616c, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]'),
(6, 'http://www.tao.lu/Ontologies/TAO.rdf#Plugin', 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget', 0x687474703a2f2f7777772e74616f2e6c752f6461746174797065732f576964676574446566696e6974696f6e732e7264662354657874426f78, '', 'generis', 'yyy[]', 'yy-[]', 'y--[Administrators]');
