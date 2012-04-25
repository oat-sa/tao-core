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
        $expectedUriPrefix = 'http://www.tao.lu/Ontologies/TAO.rdf#Lang';
        if(false !== ($locales = scandir($taoLocalesDir))){
            foreach ($locales as $l){
                $localePath = $taoLocalesDir . '/' . $l;
                if ($l[0] !== '.' && is_dir($localePath) && is_readable($localePath)){
                    $langPath = $localePath . '/lang.rdf';
                    
                    if (file_exists($langPath)){
                        $lgResource = new core_kernel_classes_Resource($expectedUriPrefix . $l);
                        
                        // Check for this language in Ontology.
                        $kbLangs = $lgResource->getPropertyValues(new core_kernel_classes_Property(RDF_VALUE));
                        $this->assertEqual(count($kbLangs), 1, "Number of languages retrieved for language '${l}' is '" . count($kbLangs) . "'.");
                        
                        // Check if the language has the correct URI.
                        if ($kbLangs[0] instanceof core_kernel_classes_Resource){
                            $this->assertTrue($kbLangs[0]->uriResource == $expectedUriPrefix . $l, "Malformed URI scheme for language resource '${l}'.");
                        }
                    }
                }
            }    
        }
    }
}
?>