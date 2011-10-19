<?php
require_once dirname(__FILE__) . '/TestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * This test case aims at testing the Translation classes of TAO, the reading and
 * the writing processes, ...
 * 
 * @author Jerome Bogaerts <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage test
 */
class TranslationTestCase extends UnitTestCase {
	
	/**
	 * Test of the different classes composing the Translation Model.
	 */
	public function testTranslationModel() {
		
		// en-US (American English) to en-YA (Yoda English) translation units.
		$tu1 = new tao_models_classes_TranslationUnit('May the force be with you.',
													  'The force with you may be.');
		$tu2 = new tao_models_classes_TranslationUnit('The dark side smells hate.',
													  'Hate the dark side smells.');
		$tu3 = new tao_models_classes_TranslationUnit('Leia Organa of Alderaan is beautiful.',
													  'Beautiful Leia Organa of Alderaan is.');
		
		// Default source and target languages of translation units is en-US.
		$this->assertTrue($tu1->getSourceLanguage() == 'en-US');
		$this->assertTrue($tu2->getTargetLanguage() == 'en-US');
		
		$tu1->setSourceLanguage('en-US');
		$tu1->setTargetLanguage('en-YA');
		$tu2->setSourceLanguage('en-US');
		$tu2->setTargetLanguage('en-YA');
		$tu3->setSourceLanguage('en-US');
		$tu3->setTargetLanguage('en-YA');
		
		// Test source and target languages assignment at TranslationUnit level.
		$this->assertTrue($tu2->getSourceLanguage() == 'en-US');
		$this->assertTrue($tu3->getTargetLanguage() == 'en-YA');
		
		$tf = new tao_models_classes_TranslationFile('en-US', 'en-YA');
		$this->assertTrue($tf->getSourceLanguage() == 'en-US');
		$this->assertTrue($tf->getTargetLanguage() == 'en-YA');
		
		$tf->addTranslationUnit($tu1);
		$tf->addTranslationUnit($tu2);
		$tf->addTranslationUnit($tu3);
		
		$tus = $tf->getTranslationUnits();
		
		$this->assertTrue($tu1 == $tus[0]);
		$this->assertTrue($tu2 == $tus[1]);
		$this->assertTrue($tu3 == $tus[2]);
		
		$this->assertTrue($tu1->getSource() == 'May the force be with you.');
		$this->assertTrue($tu2->getTarget() == 'Hate the dark side smells.');
		
		$tu3->setSource('Lando Calrician is a great pilot.');
		$tu3->setTarget('A great pilot Lando Calrician is.');
		
		$this->assertTrue($tu3->getSource() == 'Lando Calrician is a great pilot.');
		$this->assertTrue($tu3->getTarget() == 'A great pilot Lando Calrician is.');
	}
	
	public function testRawTranslationReading() {
		$po = new tao_models_classes_POFileReader(dirname(__FILE__) . '/samples/sample_raw.po');
		$po->read();
		$tf = $po->getTranslationFile();
		$tus = $tf->getTranslationUnits();
	}
}
?>