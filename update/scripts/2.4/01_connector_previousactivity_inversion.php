<?php

$connectorsClass	= new core_kernel_classes_Class(CLASS_CONNECTORS);
$previousProp		= new core_kernel_classes_Property('http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsPreviousActivities');
$connectorProp		= new core_kernel_classes_Property(PROPERTY_STEP_NEXT);

foreach ($connectorsClass->getInstances(true) as $connector) {
	foreach ($connector->getPropertiesValues($previousProp) as $activity) {
		$activity->setPropertyValue($connectorProp, $connector);
	}
	$connector->removePropertyValues($previousProp);
}

?>