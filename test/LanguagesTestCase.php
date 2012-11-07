<?php
require_once dirname(__FILE__) . '/TaoTestRunner.php';
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
    
    /**
     * Test the language usages property. The usages property defines
     * in which context a language can take place e.g. GUI, data, ...
     * 
     * @author Jerome Bogaerts, <taosupport@tudor.lu>
     */
    public function testLanguageUsages(){
    	// The English locale should always exists and should
    	// be available in any known language usage.
    	$lgUsageProperty = new core_kernel_classes_Property(PROPERTY_LANGUAGE_USAGES);
    	$this->assertIsA($lgUsageProperty, 'core_kernel_classes_Property');
    	$this->assertEqual($lgUsageProperty->getUri(), PROPERTY_LANGUAGE_USAGES);
    	$usagePropertyRange = $lgUsageProperty->getRange();
    	$this->assertIsA($usagePropertyRange, 'core_kernel_classes_Class');
    	$this->assertEqual($usagePropertyRange->getUri(), CLASS_LANGUAGES_USAGES);
    	
    	$instancePrefix = 'http://www.tao.lu/Ontologies/TAO.rdf#Lang';
    	$targetLanguageCode = 'EN';
    	$valueProperty = new core_kernel_classes_Property(RDF_VALUE);
    	
    	$englishLanguage = new core_kernel_classes_Resource($instancePrefix . $targetLanguageCode);
    	$this->assertIsA($englishLanguage, 'core_kernel_classes_Resource');
    	$this->assertTrue(in_array('EN', $englishLanguage->getPropertyValues($valueProperty)));
    	
    	$usages = $englishLanguage->getPropertyValues($lgUsageProperty);
    	$this->assertTrue(count($usages) >= 2);
    	$this->assertTrue(in_array(INSTANCE_LANGUAGE_USAGE_GUI, $usages));
    	$this->assertTrue(in_array(INSTANCE_LANGUAGE_USAGE_DATA, $usages));
    }
}
?>