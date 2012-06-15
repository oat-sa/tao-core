<?php

$langClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
$langInstances = $langClass->getInstances();
$valueProperty = new core_kernel_classes_Property(RDF_VALUE);

foreach ($langInstances as $lang){
    $tmpCode = trim($lang->getLabel());
    $tmpName = trim($lang->getComment());
    $lang->delete();
    
    $newLang = core_kernel_classes_ClassFactory::createInstance($langClass,
                                                                $tmpName,
                                                                $tmpName,
                                                                'http://www.tao.lu/Ontologies/TAO.rdf#Lang' . $tmpCode);
    $newLang->setPropertyValue($valueProperty, $tmpCode);
}

?>