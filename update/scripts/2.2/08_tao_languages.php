<?php

$langClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
$langInstances = $langClass->getInstances();
$valueProperty = new core_kernel_classes_Property(RDF_VALUE);

// Put old languages in the local namespace.
$dbWrapper = core_kernel_classes_DbWrapper::singleton();
$dbWrapper->execSql("UPDATE statements SET modelID = 8 WHERE subject LIKE 'http://www.tao.lu/Ontologies/TAO.rdf#Lang%'");

if ($dbWrapper->getAffectedRows() > 0){
    // Transform language instances to make them compliant with TAO 2.2.
    foreach ($langInstances as $lang){
        $tmpCode = trim($lang->getLabel());
        $tmpName = trim($lang->getComment());
        
        if ($lang->delete()){
            $newLang = core_kernel_classes_ClassFactory::createInstance($langClass,
                                                                        $tmpName,
                                                                        $tmpName,
                                                                        'http://www.tao.lu/Ontologies/TAO.rdf#Lang' . $tmpCode);
            $newLang->setPropertyValue($valueProperty, $tmpCode);
        }
        else{
            common_Logger::e("Unable to delete language '${tmpCode}' from ontology.", array('UPDATE'));
        }
    }  
}
else{
    common_Logger::e("Unable to transfer language instances from tao model to the local one.", array('UPDATE'));
}

?>