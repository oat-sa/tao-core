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
/**
 * Change the item folder structure add the language dimension.
 * It scans the $path directory to look for item folders. It adds
 * a language folder corresponding to the platform default language
 * and copy back the old item's into it.
 */
function tao22_updateItems($path){
    
    if (($items = @scandir($path)) !== false){
        foreach ($items as $i){
            $itemPath = $path . '/' . $i;
            if (is_dir($itemPath) && is_readable($itemPath) && $i[0] !== '.'){

                $tmpDir = $itemPath . '.bak';
                if (!file_exists($itemPath . '/' . DEFAULT_LANG) && mkdir($tmpDir, 0777, true)){

                    // For any resource in the old item, copy to language
                    // folder.
                    $tmpDir = $tmpDir . '/' . DEFAULT_LANG;
                    mkdir($tmpDir);

                    if (($resources = @scandir($itemPath)) !== false){
                        foreach ($resources as $r){
                            $resourcePath = $itemPath . '/' . $r;
                            if (@is_readable($resourcePath) && $r[0] !== '.'){
                                tao_helpers_File::udpdateCopy($resourcePath, $tmpDir . '/' . $r, true);
                            }
                        }
                    }

                    tao_helpers_File::remove($itemPath, true);
                    tao_helpers_File::udpdateCopy($itemPath . '.bak', $itemPath, true);
                    tao_helpers_File::remove($itemPath . '.bak', true);
                }
            }
        }
    }
}

// Add language tags to item content triples.
$itemClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOItem.rdf#Item');
$itemContentProperty = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent');
$items = $itemClass->getInstances();
foreach ($items as $i){
	common_Logger::d('Try update language for item . ' . $i);
    // Get the value and reset it to set its language tag.    
    try{ 
	    $itemContent = $i->getUniquePropertyValue($itemContentProperty)->getUri();
	    $i->removePropertyValues($itemContentProperty);
	    $i->setPropertyValueByLg($itemContentProperty, $itemContent, DEFAULT_LANG);
    }
    catch (common_exception_EmptyProperty $e){
    	common_Logger::i('No content found for item . ' . $i . ' skipped' );
    }
    catch (common_Exception $e){
    	common_Logger::e('Fail to update language for item : ' . $i . 'exception raise  ' . $e->getMessage() );
    }
    common_Logger::d('Success update language for item . ' . $i);
}
                    
// Change item folder structure to add the language dimension.
// 1. update /taoItems/data
tao22_updateItems(ROOT_PATH . '/taoItems/data');

// 2. update /taoDelivery/compiled
$compiledPath = ROOT_PATH . '/taoDelivery/compiled';

if (($deliveries = @scandir($compiledPath)) !== false){
    foreach ($deliveries as $d){
        $deliveryPath = $compiledPath . '/' . $d;
        if ($d[0] !== '.' && is_dir($deliveryPath) && is_readable($deliveryPath)){
            if (($tests = @scandir($deliveryPath)) !== false){
                foreach ($tests as $t){
                    $testPath = $deliveryPath. '/' . $t;
                    if ($t[0] !== '.' && is_dir($testPath) && is_readable($testPath)){
                        tao22_updateItems($testPath);
                    }
                }
            }
        }
    }
}

?>