<?php
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);


$itemModelProperty 		= new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel');
$itemContentProperty 	= new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent');

$itemClass = new core_kernel_classes_Class(TAO_ITEM_CLASS);
foreach($itemClass->getInstances(true) as $item){
	try{
		$itemModel = $item->getUniquePropertyValue($itemModelProperty);
		
		if($itemModel instanceof core_kernel_classes_Resource){
			if($itemModel->uriResource == 'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs'){
				$itemContent = (string)$item->getUniquePropertyValue($itemContentProperty);
				//update case
				$itemContent = str_replace('rdf:Id', 'rdf:ID', $itemContent);
				
				//update rdf and rdfs namespaces
				if(preg_match("/xmlns:rdf=/", $itemContent) && preg_match("/xmlns:rdfs=/", $itemContent)){
					$itemContent = str_replace('xmlns:rdfs="http://www.w3.org/TR/1999/PR-rdf-schema-19990303#"', 'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"', $itemContent);
					$itemContent = str_replace('xmlns:rdfs=\'http://www.w3.org/TR/1999/PR-rdf-schema-19990303#\'', 'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"', $itemContent);
				}
				elseif(!preg_match("/xmlns:rdf=/", $itemContent) && preg_match("/xmlns:rdfs=/", $itemContent)){
					$itemContent = str_replace('xmlns:rdfs="http://www.w3.org/TR/1999/PR-rdf-schema-19990303#"', 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"', $itemContent);
					$itemContent = str_replace('xmlns:rdfs=\'http://www.w3.org/TR/1999/PR-rdf-schema-19990303#\'', 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"', $itemContent);
				}
				$item->editPropertyValues($itemContentProperty, $itemContent);
			}
		}
	}
	catch(common_Exception $ce){}
}

?>