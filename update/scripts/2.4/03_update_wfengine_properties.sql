UPDATE statements SET property = 'http://www.tao.lu/middleware/wfEngine.rdf#PropertyStepNext'
	WHERE property in ('http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesNext','http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNextActivities','http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityCardinalityActivity')

DELETE FROM statements
	WHERE subject in ('http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesNext','http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsNextActivities','http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsPreviousActivities','http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityCardinalityActivity')