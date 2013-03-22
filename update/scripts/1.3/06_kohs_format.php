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