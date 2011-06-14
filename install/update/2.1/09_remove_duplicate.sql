DELETE FROM statements WHERE subject='http://www.tao.lu/middleware/Rules.rdf#Predicate' AND
 	predicate = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
	AND object = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property'
	LIMIT 2;
DELETE FROM statements WHERE subject='http://www.tao.lu/middleware/Rules.rdf#Predicate' AND
 	predicate = 'http://www.w3.org/2000/01/rdf-schema#label'
	LIMIT 2;
DELETE FROM statements WHERE subject='http://www.tao.lu/middleware/Rules.rdf#Predicate' AND
 	predicate = 'http://www.w3.org/2000/01/rdf-schema#range'
 	AND object = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property'
	LIMIT 1;
DELETE FROM statements WHERE subject='http://www.tao.lu/middleware/Rules.rdf#Predicate' AND
 	predicate = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget'
 	AND object = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox'
	LIMIT 1;

DELETE FROM statements WHERE subject='http://www.tao.lu/middleware/Rules.rdf#Subject' AND
 	predicate = 'http://www.w3.org/2000/01/rdf-schema#label'
	LIMIT 1;
DELETE FROM statements WHERE subject='http://www.tao.lu/middleware/Rules.rdf#Subject' AND
 	predicate = 'http://www.w3.org/2000/01/rdf-schema#range'
 	AND object = 'http://www.w3.org/2000/01/rdf-schema#Resource'
	LIMIT 1;
DELETE FROM statements WHERE subject='http://www.tao.lu/middleware/Rules.rdf#Subject' AND
 	predicate = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
	AND object = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property'
	LIMIT 1;
