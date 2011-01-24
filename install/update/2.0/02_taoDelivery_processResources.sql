/*delete if the old process var exists in the local namespace, then re-add it to the correct one*/
DELETE from `statements` WHERE  `subject` like "%#i1267544223024059902" LIMIT 6;
INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/middleware/taoqual.rdf#i118589004639950', 'EN', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600', 'http://www.w3.org/2000/01/rdf-schema#label', 'delivery', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600', 'http://www.w3.org/2000/01/rdf-schema#domain', 'http://www.tao.lu/middleware/taoqual.rdf#Token', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600', 'http://www.w3.org/2000/01/rdf-schema#range', 'http://www.w3.org/2000/01/rdf-schema#Literal', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600', 'http://www.tao.lu/middleware/taoqual.rdf#code', 'delivery', '', 'generis', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]');

/*need fo updating to http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600 because of the many references to this process var*/
UPDATE `statements` set `predicate`="http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600" WHERE  `predicate` like "%#i1267544223024059902";
UPDATE `statements` set `object`="http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600" WHERE  `object`="%#i1267544223024059902";

/*update to the right uri*/
UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ProcessVarDelivery" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ProcessVarDelivery" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ProcessVarDelivery" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1278922897063796600";

/*add the new service test container uri*/
INSERT INTO `statements` (`modelID`, `subject`, `predicate`, `object`, `l_language`, `author`, `stread`, `stedit`, `stdelete`) VALUES
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.tao.lu/middleware/taoqual.rdf#i118588779325312', '', '', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer', 'http://www.w3.org/2000/01/rdf-schema#label', 'Test container', '', '', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer', 'http://www.tao.lu/middleware/taoqual.rdf#i11858886911216', 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test', '', '', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]'),
(14, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer', 'http://www.tao.lu/middleware/taoqual.rdf#i118588892919658', 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956260043375900', '', '', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]', 'yyy[admin,administrators,authors]');
