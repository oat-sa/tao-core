UPDATE `statements` set modelID=14, subject="http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600" WHERE  subject like "%#i1278922897063796600";
UPDATE `statements` set predicate="http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600" WHERE  predicate like "%#i1278922897063796600";
UPDATE `statements` set object="http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600" WHERE  object="%#i1278922897063796600";

INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/middleware/taoqual.rdf#i118588779325312', '', '', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer', 'http://www.w3.org/2000/01/rdf-schema#label', 'Test container', '', '', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer', 'http://www.tao.lu/middleware/taoqual.rdf#i11858886911216', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', '', '', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer', 'http://www.tao.lu/middleware/taoqual.rdf#i118588892919658', 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956260043375900', '', '', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]');
