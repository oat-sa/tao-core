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