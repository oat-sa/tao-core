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
error_reporting(E_ALL);

$dbWarpper = core_kernel_classes_DbWrapper::singleton();


//get all instance of deliveries:
$deliveryMainClass = new core_kernel_classes_Class(TAO_DELIVERY_CLASS);

$propAuthoringMode = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringMode');
$updatedDelivery = array();
foreach($deliveryMainClass->getInstances(true) as $delivery){
	//for each of them, check if an authoring mode is set:
	$authoringMode = $delivery->getOnePropertyValue($propAuthoringMode);
	if(is_null($authoringMode)){
		//if not, set it to simple mode:
		$delivery->setPropertyValue($propAuthoringMode, 'http://www.tao.lu/Ontologies/TAODelivery.rdf#i1268049036038811802');//TAO_DELIVERY_SIMPLEMODE
		$updatedDelivery[$delivery->uriResource] = $delivery;
	}
}

echo 'done';

?>