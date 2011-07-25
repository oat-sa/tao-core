DELETE FROM statements WHERE subject = 'http://www.tao.lu/Ontologies/TAOTest.rdf';
DELETE FROM statements WHERE subject = 'http://www.tao.lu/Ontologies/TAOTest.rdf#RelatedItems';

UPDATE statements SET subject = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringModeSimple' WHERE subject = 'http://www.tao.lu/Ontologies/TAOTest.rdf#i1268049036038811802';
UPDATE statements SET predicate = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringModeSimple' WHERE predicate = 'http://www.tao.lu/Ontologies/TAOTest.rdf#i1268049036038811802';
UPDATE statements SET object = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringModeSimple' WHERE object = 'http://www.tao.lu/Ontologies/TAOTest.rdf#i1268049036038811802';

UPDATE statements SET subject = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringModeAdvanced' WHERE subject = 'http://www.tao.lu/Ontologies/TAOTest.rdf#i1268049036038811803';
UPDATE statements SET predicate = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringModeAdvanced' WHERE predicate = 'http://www.tao.lu/Ontologies/TAOTest.rdf#i1268049036038811803';
UPDATE statements SET object = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringModeAdvanced' WHERE object = 'http://www.tao.lu/Ontologies/TAOTest.rdf#i1268049036038811803';


