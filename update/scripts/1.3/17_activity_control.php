<?php
$controlProperty = new core_kernel_classes_Property('http://www.tao.lu/middleware/taoqual.rdf#ActivityControl');

$activityClazz = new core_kernel_classes_Class('http://www.tao.lu/middleware/taoqual.rdf#i118588757437650');
foreach($activityClazz->getInstances(true) as $instance){
	$instance->setPropertyValue($controlProperty, 'http://www.tao.lu/middleware/taoqual.rdf#ForwardControl');
	$instance->setPropertyValue($controlProperty, 'http://www.tao.lu/middleware/taoqual.rdf#BackwardControl');
}
?>