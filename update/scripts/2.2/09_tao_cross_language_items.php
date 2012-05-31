<?php
/**
 * Change the item folder structure add the language dimension.
 * It scans the $path directory to look for item folders. It adds
 * a language folder corresponding to the platform default language
 * and copy back the old item's into it.
 */
function tao22_updateItems($path){
    
    $session = core_kernel_classes_Session::singleton();
    
    if (($items = @scandir($path)) !== false){
        foreach ($items as $i){
            $itemPath = $path . '/' . $i;
            if (is_dir($itemPath) && is_readable($itemPath) && $i[0] !== '.'){
                $tmpDir = $itemPath . '.bak/' . $session->defaultLg;
                if (@mkdir($tmpDir, 0770, true)){
                    // For any resource in the old item, copy to language
                    // folder.
                    if (($resources = @scandir($itemPath)) !== false){
                        foreach ($resources as $r){
                            $resourcePath = $itemPath . '/' . $r;
                            if (file_exists($resourcePath) && is_readable($resourcePath)){
                                tao_helpers_File::copy($resourcePath, $tmpDir, true);
                            }
                        }
                    }

                    tao_helpers_File::remove($itemPath, true);
                    rename($tmpDir, $itemPath);
                }
            }
        }
    }
}

// Add language tags to item content triples.
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);
$session = core_kernel_classes_Session::singleton();
$itemClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAOItem.rdf#Item');
$itemContentProperty = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOItem.rdf#ItemContent');
$items = $itemClass->getInstances();
foreach ($items as $i){
    // Get the value and reset it to set its language tag.    
    $itemContent = '' . $i->getUniquePropertyValue($itemContentProperty);
    $i->removePropertyValues($itemContentProperty);
    $i->setPropertyValueByLg($itemContentProperty, $itemContent, $session->defaultLg);
}
                    
// Change item folder structure to add the language dimension.
// 1. update /taoItems/data
tao22_updateItems(ROOT_PATH . '/taoItems');

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