DELETE FROM `statements`
WHERE 
	predicate = 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent'
	OR subject = 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent'
	