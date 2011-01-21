DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/Ontologies/TAODelivery.rdf#SubjectCache' LIMIT 5;
DELETE FROM `statements` WHERE `subject` LIKE 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ExcludedGroups' LIMIT 2;

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringModeSimple" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1268049036038811802";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringModeSimple" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1268049036038811802";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringModeSimple" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1268049036038811802";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringModeAdvanced" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1268049036038811803";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringModeAdvanced" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1268049036038811803";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringModeAdvanced" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1268049036038811803";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamTestUri" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956260043375900";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamTestUri" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956260043375900";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamTestUri" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956260043375900";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamItemUri" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956259074862500";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamItemUri" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956259074862500";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamItemUri" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956259074862500";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamDeliveryUri" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956261000699400";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamDeliveryUri" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956261000699400";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamDeliveryUri" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956261000699400";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceItemRunner" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956262090045500";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceItemRunner" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956262090045500";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceItemRunner" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1288956262090045500";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#WebServiceUrlParam" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808482066572900";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#WebServiceUrlParam" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808482066572900";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#WebServiceUrlParam" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808482066572900";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#WebServiceUrl" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483007436700";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#WebServiceUrl" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483007436700";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#WebServiceUrl" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483007436700";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamHeight" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483039359300";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamHeight" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483039359300";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamHeight" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483039359300";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamWidth" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483044286100";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamWidth" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483044286100";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamWidth" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483044286100";

UPDATE statements SET subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceWebService" WHERE subject = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483090501100";
UPDATE statements SET predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceWebService" WHERE predicate = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483090501100";
UPDATE statements SET object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceWebService" WHERE object = "http://www.tao.lu/Ontologies/TAODelivery.rdf#i1289808483090501100";

