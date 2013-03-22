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