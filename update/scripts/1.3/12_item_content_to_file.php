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
/*  Move previous item content to a file*/
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);

require_once(dirname(__FILE__) . '/../../../../tao/helpers/class.Uri.php');
require_once(dirname(__FILE__) . '/../../../../tao/helpers/class.File.php');

$itemModelProperty 		= new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel');
$itemContentProperty 	= new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent');
$itemClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOItem.rdf#Item');

$folder = realpath(dirname(__FILE__) . '/../../../../taoItems/data/').'/';

foreach($itemClass->getInstances(true) as $item){
	try{
		$itemModel = $item->getUniquePropertyValue($itemModelProperty);
		
		if($itemModel instanceof core_kernel_classes_Resource){
			
			$itemContent = $item->getOnePropertyValue($itemContentProperty);
			
			try{
				if($itemContent instanceof core_kernel_classes_Resource){
					if(core_kernel_classes_File::isFile($itemContent)){
						continue;
					}
				}
			}
			catch(Exception $e){}
			
			if(!is_null($itemContent)){
				$itemShortUri = substr($item->uriResource, strpos($item->uriResource, '#') + 1);
				switch($itemModel->uriResource){
					case 'http://www.tao.lu/Ontologies/TAOItem.rdf#XHTML':
						$itemFile = $folder . $itemShortUri . '/index.html';
						break;
					case 'http://www.tao.lu/Ontologies/TAOItem.rdf#i125933161031263':
						$itemFile = $folder . $itemShortUri . '/black.xml';
						break;
					case 'http://www.tao.lu/Ontologies/TAOItem.rdf#QTI':
						$itemFile = $folder . $itemShortUri . '/qti.xml';
						break;
					case 'http://www.tao.lu/Ontologies/TAOItem.rdf#QCM':
						$itemFile = $folder . $itemShortUri . '/qcm.xml';
						break;
					case 'http://www.tao.lu/Ontologies/TAOItem.rdf#kohs':
						$itemFile = $folder . $itemShortUri . '/kohs.xml';
						break;
					case 'http://www.tao.lu/Ontologies/TAOItem.rdf#Ctest':
						$itemFile = $folder . $itemShortUri . '/ctest.xml';
						break;
					case 'http://www.tao.lu/Ontologies/TAOItem.rdf#campus':
						$itemFile = $folder . $itemShortUri . '/campus.xml';
						break;
				}
				if(!is_dir(dirname($itemFile))){
					mkdir(dirname($itemFile));
				}
				if($itemModel->uriResource == 'http://www.tao.lu/Ontologies/TAOItem.rdf#i125933161031263'){
					$itemContent = '';
					$hawaiFile = $folder . '/' .tao_helpers_Uri::encode($item->uriResource).'.xml';
					if(file_exists($hawaiFile)){
						tao_helpers_File::move($hawaiFile, dirname($itemFile).'/black.xml');
					}
					$hawaiTmp = $folder . '/tmp_' .tao_helpers_Uri::encode($item->uriResource).'.xml';
					if(file_exists($hawaiTmp)){
						tao_helpers_File::move($hawaiTmp, dirname($itemFile).'/tmp_black.xml');
					}
				}
				else{
					file_put_contents($itemFile, $itemContent);
				}
				$file = core_kernel_classes_File::create(basename($itemFile), dirname($itemFile).'/');
				$item->editPropertyValues($itemContentProperty, $file->uriResource);
			}
		}
	}
	catch(common_Exception $ce){}
}
?>