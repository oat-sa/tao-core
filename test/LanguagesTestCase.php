<?php
require_once dirname(__FILE__) . '/TestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * Aims at testing languages/locales in TAO.
 * 
 * @author Jerome Bogaerts, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class LanguagesTestCase extends UnitTestCase {
    
    /**
     * Test the existence of language definition resources in the knowledge base
     * regarding what we found in the tao/locales directory.
     * 
     * @author Jerome Bogaerts, <taosupport@tudor.lu>
     */
    public function testLanguagesExistence(){
        // Check for lang.rdf in /tao locales and query the KB to see if it exists or not.
        $languageClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
        $taoLocalesDir = ROOT_PATH . '/tao/locales';
        if(false !== ($locales = scandir($taoLocalesDir))){
            foreach ($locales as $l){
                $localePath = $taoLocalesDir . '/' . $l;
                if ($l[0] !== '.' && is_dir($localePath) && is_readable($localePath)){
                    $langPath = $localePath . '/lang.rdf';
                    
                    if (file_exists($langPath)){
                        // Check for this language in Ontology.
                        $kbLangs = $languageClass->searchInstances(array(RDF_VALUE => $l), array('like' => false));
                        $this->assertEqual(count($kbLangs), 1);
                    }
                }
            }    
        }
    }
}
?>