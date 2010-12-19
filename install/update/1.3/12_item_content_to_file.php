<?php
/*  Move previous item content to a file*/
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);

require_once(dirname(__FILE__) . '/../../../../tao/helpers/class.Uri.php');
require_once(dirname(__FILE__) . '/../../../../tao/helpers/class.File.php');

$itemModelProperty 		= new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOItem.rdf#ItemModel');
$itemContentProperty 	= new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent');
$itemClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOItem.rdf#Item');

$folder = dirname(__FILE__) . '/../../../../taoItems/data/';

foreach($itemClass->getInstances(true) as $item){
	try{
		$itemModel = $item->getUniquePropertyValue($itemModelProperty);
		
		if($itemModel instanceof core_kernel_classes_Resource){
			
			$itemContent = $item->getOnePropertyValue($itemContentProperty);
			
			try{
				if(core_kernel_classes_File::isFile($itemContent)){
					continue;
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