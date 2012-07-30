UPDATE `statements`
SET object = 'http://www.tao.lu/Ontologies/taoFuncACL.rdf#m_taoResults_SaSResults'
WHERE 
	predicate = 'http://www.tao.lu/Ontologies/taoFuncACL.rdf#grantAccessModule'
	AND object = 'http://www.tao.lu/Ontologies/taoFuncACL.rdf#m_taoDelivery_SaSResults'