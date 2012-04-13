<?php
require_once dirname(__FILE__) . '/TestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * 
 * @author Jerome Bogaerts, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class InstallTestCase extends UnitTestCase {
	
	/**
	 * Test the Installation Model Creator.
	 */
	public function testModelCreator() {
		$extensionManager = common_ext_ExtensionsManager::singleton();
		$extensions = $extensionManager->getInstalledExtensions();
		
		$files = tao_install_utils_ModelCreator::getTranslationModelsFromExtension($extensions['tao']);
		$ns = 'http://www.tao.lu/Ontologies/TAO.rdf';
		$this->assertTrue(is_array($files));
		//$this->assertTrue(array_key_exists($ns, $files));
		//$this->assertTrue(count($files) == 1);
		
		// - Test the existence of language descriptions.
		$taoNs = 'http://www.tao.lu/Ontologies/TAO.rdf#';
		$langs = tao_install_utils_ModelCreator::getLanguageModels();
        $this->assertTrue(isset($langs[$taoNs]), "No language descriptions available for model '${taoNs}'.");
        // We should have at least english described.
        $enFound = false;
        $languageDescriptionFiles = $langs[$taoNs];
        foreach ($languageDescriptionFiles as $f){
            if(preg_match('/locales\/EN\/lang.rdf/i', $f)){
                $enFound = true;
                break;
            }
        }
        
        $this->assertTrue($enFound, "English language description not found for model '${taoNs}'.");
	}
}
?>