<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php 

$userService = core_kernel_users_Service::singleton();
$userService->login(SYS_USER_LOGIN, SYS_USER_PASS, new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole'));


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