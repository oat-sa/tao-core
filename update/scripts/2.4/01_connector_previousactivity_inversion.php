<?php

$connectorsClass	= new core_kernel_classes_Class(CLASS_CONNECTORS);
$previousProp		= new core_kernel_classes_Property('http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsPreviousActivities');
$connectorProp		= new core_kernel_classes_Property(PROPERTY_STEP_NEXT);

$cardService = wfEngine_models_classes_ActivityCardinalityService::singleton();
$propCardAct = new core_kernel_classes_Property('http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityCardinalityActivity');

foreach ($connectorsClass->getInstances(true) as $connector) {
	foreach ($connector->getPropertiesValues($previousProp) as $step) {
		$step->setPropertyValue($connectorProp, $connector);
		if ($cardService->isCardinality($step)) {
			$act = $step->getUniquePropertyValue($propCardAct);
			$act->setPropertyValue($connectorProp, $step);
			$step->removePropertyValues($propCardAct);
		}
	}
	$connector->removePropertyValues($previousProp);
}

?>