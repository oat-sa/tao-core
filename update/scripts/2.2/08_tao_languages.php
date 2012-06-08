<?php
core_control_FrontController::connect(SYS_USER_LOGIN, SYS_USER_PASS, DATABASE_NAME);
$session = core_kernel_classes_Session::singleton();
$tmpSessionLg = $session->getDataLanguage();
$session->setDataLanguage($session->defaultLg);

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


// Rollback session language.
$session->setDataLanguage($tmpSessionLg);
?>