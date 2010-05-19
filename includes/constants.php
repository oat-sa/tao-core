<?php
/**
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */

$todefine = array(
	'TAO_OBJECT_CLASS' 				=> 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject',
	'TAO_GROUP_CLASS' 				=> 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group',
	'TAO_ITEM_CLASS' 				=> 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
	'TAO_ITEM_MODEL_CLASS' 			=> 'http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModels',
	'TAO_RESULT_CLASS' 				=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result',
	'TAO_SUBJECT_CLASS' 			=> 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject',
	'TAO_TEST_CLASS' 				=> 'http://www.tao.lu/Ontologies/TAOTest.rdf#Test',
	'TAO_DELIVERY_CLASS' 			=> 'http://www.tao.lu/Ontologies/TAODelivery.rdf#Delivery',
	'TAO_DELIVERY_CAMPAIGN_CLASS'	=> 'http://www.tao.lu/Ontologies/TAODelivery.rdf#DeliveryCampaign',	
	'TAO_DELIVERY_RESULTSERVER_CLASS'	=> 'http://www.tao.lu/Ontologies/TAODelivery.rdf#DeliveryResultServer',
	'TAO_DELIVERY_HISTORY_CLASS'	=> 'http://www.tao.lu/Ontologies/TAODelivery.rdf#History',
	'RDFS_LABEL'					=> 'http://www.w3.org/2000/01/rdf-schema#label',
	'RDFS_CLASS'					=> 'http://www.w3.org/2000/01/rdf-schema#Class',
	'RDFS_TYPE'						=> 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
	'GENERIS_RESOURCE'				=> 'http://www.tao.lu/Ontologies/generis.rdf#generis_Ressource',
	'GENERIS_BOOLEAN'				=> 'http://www.tao.lu/Ontologies/generis.rdf#Boolean',
	'INSTANCE_BOOLEAN_TRUE'			=> 'http://www.tao.lu/Ontologies/generis.rdf#True',
	'INSTANCE_BOOLEAN_FALSE'		=> 'http://www.tao.lu/Ontologies/generis.rdf#False',
	'TAO_LIST_CLASS'				=> 'http://www.tao.lu/Ontologies/TAO.rdf#List',
	'TAO_LIST_LEVEL_PROP'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#level',
	'INSTANCE_ROLE_TAOMANAGER'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole',
	'INSTANCE_ROLE_BACKOFFICE'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#BackOffice',
	'INSTANCE_ROLE_FRONTOFFICE'		=> 'http://www.tao.lu/Ontologies/TAO.rdf#FrontOffice',
	'INSTANCE_ROLE_SUBJECT'			=> 'http://www.tao.lu/Ontologies/TAO.rdf#TaoSubjectRole'
	
);
foreach($todefine as $constName => $constValue){
	if(!defined($constName)){
		define($constName, $constValue);
	}
}
unset($todefine);
?>