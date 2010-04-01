<?php 

 function getListData($exclude = array()){ 
	$data = array();
	$taoObjectClass = new core_kernel_classes_Class(TAO_OBJECT_CLASS);
	foreach($taoObjectClass->getSubClasses(false)  as $subClass){
		if(in_array($subClass->uriResource, $exclude)){
			continue;
		}
		array_push($data, $subClass);
	}
	return $data;
}
	
$oldLists = getListData(array(
				TAO_GROUP_CLASS,
				TAO_ITEM_CLASS,
				TAO_ITEM_MODEL_CLASS,
				TAO_RESULT_CLASS,
				TAO_SUBJECT_CLASS,
				TAO_TEST_CLASS,
				TAO_DELIVERY_CLASS,
				TAO_DELIVERY_CAMPAIGN_CLASS,
				TAO_DELIVERY_RESULTSERVER_CLASS,
				TAO_DELIVERY_HISTORY_CLASS
			)
		);
	
$typeProperty = new core_kernel_classes_Property(RDFS_TYPE);
$newListClass = new core_kernel_classes_Class(TAO_LIST_CLASS);
$levelProperty = new core_kernel_classes_Property(TAO_LIST_LEVEL_PROP);
foreach($oldLists as $list){
	$list->editPropertyValues($typeProperty, $newListClass);
	$level = 1;
	foreach($list->getInstances() as $element){
		$element->setPropertyValue($levelProperty, $level);
		$level++;
	}
}
 
?>