<?php
$todefine = array(
	'TAO_OBJECT_CLASS' => 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group',
	'RDFS_LABEL' => 'http://www.w3.org/2000/01/rdf-schema#label'
);
foreach($todefine as $constName => $constValue){
	if(!defined($constName)){
		define($constName, $constValue);
	}
}
unset($todefine);
?>