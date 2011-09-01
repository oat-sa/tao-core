DELETE FROM  statements WHERE subject =	'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM' AND predicate = 'SWFFile' AND object = 'tao_item.swf';

UPDATE statements SET subject = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemRuntime' WHERE subject = 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile';
UPDATE statements SET predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemRuntime' WHERE predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile';
UPDATE statements SET object = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemRuntime' WHERE object = 'http://www.tao.lu/Ontologies/TAOItem.rdf#SWFFile';

UPDATE statements SET subject = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemAuthoring' WHERE subject = 'http://www.tao.lu/Ontologies/TAOItem.rdf#i12580164649880';
UPDATE statements SET predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemAuthoring' WHERE predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#i12580164649880';
UPDATE statements SET object = 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemAuthoring' WHERE object = 'http://www.tao.lu/Ontologies/TAOItem.rdf#i12580164649880';

UPDATE statements SET subject = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Hawai' WHERE subject = 'http://www.tao.lu/Ontologies/TAOItem.rdf#i125933161031263';
UPDATE statements SET predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Hawai' WHERE predicate = 'http://www.tao.lu/Ontologies/TAOItem.rdf#i125933161031263';
UPDATE statements SET object = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Hawai' WHERE object = 'http://www.tao.lu/Ontologies/TAOItem.rdf#i125933161031263';