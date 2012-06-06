<?php

$deliveryClass	= new core_kernel_classes_Class(TAO_DELIVERY_CLASS);
$property		= new core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP);
$oldServer		= new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAODelivery.rdf#i1267866417009087900');
$newServer		= new core_kernel_classes_Resource(TAO_DELIVERY_DEFAULT_RESULT_SERVER);

// find deliveries linked to the old DeliveryServer
foreach ($deliveryClass->getInstances(true) as $delivery) {
	$current = $delivery->getUniquePropertyValue($property);
	if ($current->getUri() == 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1267866417009087900') {
		// freplace the DeliveryServer
		$delivery->editPropertyValues($property, $newServer);
	}
}

// Delete the old DeliveryServer
$oldServer->delete();

?>