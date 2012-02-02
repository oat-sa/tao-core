<?php
require_once dirname(__FILE__) . '/TestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * This test case aims at testing the Translation classes of TAO, the reading and
 * the writing processes, ...
 * 
 * IMPORTANT: SAVE THIS FILE AS UTF-8. IT CONTAINS CROSS-CULTURAL CONSTANTS !
 * 
 * @author Jerome Bogaerts <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage test
 */
class TranslationTestCase extends UnitTestCase {
	
	const RAW_PO = '/samples/sample_raw.po';
	const ESCAPING_PO = '/samples/sample_escaping.po';
	const SORTING_PO = '/samples/sample_sort.po';
	const TEMP_PO = 'tao.test.translation.writing';
	const TAO_MANIFEST = '/samples/structures/tao';
	const GROUPS_MANIFEST = '/samples/structures/groups';
	const ITEMS_MANIFEST = '/samples/structures/items';
	const FAKE_ACTIONS = '/samples/fakeSourceCode/actions/';
	const FAKE_VIEWS = '/samples/fakeSourceCode/views/';
	const FAKE_RDF_LOCALE = '/samples/rdf/tao_messages_DE.rdf';
	
	/**
	 * Test of the different classes composing the Translation Model.
	 */
	public function testTranslationModel() {
		
		// en-US (American English) to en-YA (Yoda English) translation units.
		$tu1 = new tao_helpers_translation_TranslationUnit('May the force be with you.',
													  'The force with you may be.');
		$tu2 = new tao_helpers_translation_TranslationUnit('The dark side smells hate.',
													  'Hate the dark side smells.');
		$tu3 = new tao_helpers_translation_TranslationUnit('Leia Organa of Alderaan is beautiful.',
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
		
		$tf = new tao_helpers_translation_TranslationFile('en-US', 'en-YA');
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
		
		$tu4 = new tao_helpers_translation_TranslationUnit('There is another Skywalker.',
														   'Another Skywalker there is.');
		$tf->addTranslationUnit($tu4);
		$tus = $tf->getTranslationUnits();
		$tu4 = $tus[3];
		
		$this->assertTrue($tu4->getTargetLanguage() == 'en-YA');
		
		$newTu = new tao_helpers_translation_TranslationUnit('Lando Calrician is a great pilot.',
															 'Han Solo is a great pilot.');
		$tf->addTranslationUnit($newTu);
		$tus = $tf->getTranslationUnits();
		$tu3 = $tus[2];
		
		$this->assertTrue(count($tus) == 4);
		$this->assertTrue($tu3->getSource() == 'Lando Calrician is a great pilot.');
		$this->assertTrue($tu3->getTarget() == 'Han Solo is a great pilot.');
	}
	
	public function testPOTranslationReading() {
		$po = new tao_helpers_translation_POFileReader(dirname(__FILE__) . self::RAW_PO);
		$po->read();
		$tf = $po->getTranslationFile();
		$tus = $tf->getTranslationUnits();
		
		// Test default values of TranslationFile. PO files does not
		// contain language information AFAIK.
		$this->assertTrue($tf->getSourceLanguage() == 'en-US');
		$this->assertTrue($tf->getTargetLanguage() == 'en-US');
		
		$this->assertTrue(count($tus) == 4);
		$this->assertTrue($tus[0]->getSource() == 'First Try');
		$this->assertTrue($tus[0]->getTarget() == '');
		$this->assertTrue($tus[1]->getSource() == 'Thïs téxt cöntàin$ wéîRd chárâctêrS beçÁuse öf I18N');
		$this->assertTrue($tus[1]->getTarget() == '');
		$this->assertTrue($tus[2]->getSource() == 'This translation will be a very long text');
		$this->assertTrue($tus[2]->getTarget() == '');
		$this->assertTrue($tus[3]->getSource() == 'And this one will contain escaping characters');
		$this->assertTrue($tus[3]->getTarget() == '');
		
		// We can test here the change of file while keeping the same instance
		// of FileReader.
		$po->setFilePath(dirname(__FILE__) . self::ESCAPING_PO);
		$po->read();
		$tf = $po->getTranslationFile();
		$tus = $tf->getTranslationUnits();
		
		$this->assertTrue(count($tus) == 4);
		$this->assertTrue($tus[0]->getSource() == 'The blackboard of Lena is full of "Shakespeare" quotes.');
		$this->assertTrue($tus[0]->getTarget() == 'L\'ardoise de Léna est pleine de citations de "Shakespeare".');
		$this->assertTrue($tus[1]->getSource() == 'Thïs téxt cöntàin$ wéîRd chárâctêrS beçÁuse öf I18N');
		$this->assertTrue($tus[1]->getTarget() == 'Ce téxtê cÖntîEn$ de drÔlés dE çÄrÂctÈres @ cAµ$£ dé l\'I18N');
		$this->assertTrue($tus[2]->getSource() == 'This translation will be a very long text');
		$this->assertTrue($tus[2]->getTarget() == 'C\'est en effet un texte très très long car j\'aime parler. Grâce à ce test, je vais pouvoir vérifier si les msgstr multilignes sont correctement interpretés par ');
		$this->assertTrue($tus[3]->getSource() == 'And this one will contain escaping characters');
		$this->assertTrue($tus[3]->getTarget() == "Alors je vais passer une ligne \net aussi faire des tabulations \t car c'est très cool.");
	}
	
	public function testPOTranslationSorting() {
		$pr = new tao_helpers_translation_POFileReader(dirname(__FILE__) . self::SORTING_PO);
		$pr->read();
		$tf = $pr->getTranslationFile();
		$unsortedTus = $tf->getTranslationUnits();
		
		// Ascending case-insensitive.
		$sortedTus = $tf->sortBySource(tao_helpers_translation_TranslationFile::SORT_ASC);
		$this->assertTrue($sortedTus[0]->getSource() == ' This begins with a white space');
		$this->assertTrue($sortedTus[8]->getSource() == 'kings are great');
		$this->assertTrue($sortedTus[10]->getSource() == 'öhïs téxt cöntàin$ wéîRd chárâctêrS beçÁuse öf I18N');
		
		$sortedTus = $tf->sortBySource(tao_helpers_translation_TranslationFile::SORT_ASC_I);
		$this->assertTrue($sortedTus[3]->getSource() == '12 is also a number');
		$this->assertTrue($sortedTus[6]->getSource() == 'kings are great');
		$this->assertTrue($sortedTus[8]->getSource() == 'Zapata');
		
		$sortedTus = $tf->sortBySource(tao_helpers_translation_TranslationFile::SORT_DESC);
		$this->assertTrue($sortedTus[4]->getSource() == 'Koalas are great');
		$this->assertTrue($sortedTus[7]->getSource() == '12 is also a number');
		$this->assertTrue($sortedTus[9]->getSource() == '- List1');
		
		$sortedTus = $tf->sortBySource(tao_helpers_translation_TranslationFile::SORT_DESC_I);
		$this->assertTrue($sortedTus[2]->getSource() == 'Zapata');
		$this->assertTrue($sortedTus[5]->getSource() == 'Ahloa');
		$this->assertTrue($sortedTus[10]->getSource() == ' This begins with a white space');
	}
	
	
	public function testPOTranslationWriting(){
		// -- First test
		$pr = new tao_helpers_translation_POFileReader(dirname(__FILE__) . self::RAW_PO);
		$pr->read();
		$tf1 = $pr->getTranslationFile();
		
		// We serialize the TranslationFile and read it again to check equivalence.
		$filePath = tempnam('/tmp', self::TEMP_PO); // Will try in the correct folder automatically for Win32 c.f. PHP website.
		$pw = new tao_helpers_translation_POFileWriter($filePath, $tf1);
		$pw->write();
		
		$pr->setFilePath($filePath);
		$pr->read();
		$tf2 = $pr->getTranslationFile();
		
		// We can now compare them.
		$this->assertTrue('' . $tf1 == '' . $tf2);
		unlink($filePath);
		
		// -- Second test
		$pr->setFilePath(dirname(__FILE__) . self::ESCAPING_PO);
		$pr->read();
		$tf1 = $pr->getTranslationFile();
		
		// Serialize and compare later.
		$filePath = tempnam('/tmp', self::TEMP_PO);
		$pw->setFilePath($filePath);
		$pw->setTranslationFile($tf1);
		$pw->write();
		
		$pr->setFilePath($filePath);
		$pr->read();
		$tf2 = $pr->getTranslationFile();
		
		// We compare ...
		$this->assertTrue('' . $tf1 == '' . $tf2);
		unlink($filePath);
	}
	
	public function testJavaScriptTranslationWriting() {
		$jsFilePath = tempnam('/tmp', self::TEMP_PO);
		$pr = new tao_helpers_translation_POFileReader(dirname(__FILE__) . self::RAW_PO);
		$pr->read();
		$tf = $pr->getTranslationFile();
		$tw = new tao_helpers_translation_JSFileWriter($jsFilePath, $tf);
		$tw->write();
		$this->assertTrue(file_exists($jsFilePath));
		unlink($jsFilePath);
		
		$jsFilePath = tempnam('/tmp', self::TEMP_PO);
		$pr->setFilePath(dirname(__FILE__) . self::ESCAPING_PO);
		$pr->read();
		$tf = $pr->getTranslationFile();
		$tw->setFilePath($jsFilePath);
		$tw->setTranslationFile($tf);
		$tw->write();
		$this->assertTrue(file_exists($jsFilePath));
		unlink($jsFilePath);
	}
	
	public function testManifestExtraction() {
		$taoManifestPath = dirname(__FILE__) . self::TAO_MANIFEST;
		$extractor = new tao_helpers_translation_ManifestExtractor(array($taoManifestPath));
		$extractor->extract();
		$tus = $extractor->getTranslationUnits();
		
		$this->assertTrue(count($tus) == 4);
		$this->assertTrue($tus[0]->getSource() == 'Users');
		$this->assertTrue($tus[1]->getSource() == 'Manage users');
		$this->assertTrue($tus[2]->getSource() == 'Add a user');
		$this->assertTrue($tus[3]->getSource() == 'Edit a user');
	}
	
	public function testMultipleManfiestExtraction() {
		$taoManifestPaths = array(dirname(__FILE__) . self::GROUPS_MANIFEST,
								  dirname(__FILE__) . self::ITEMS_MANIFEST);
							  
		$extractor = new tao_helpers_translation_ManifestExtractor($taoManifestPaths);
		$extractor->extract();
		$tus = $extractor->getTranslationUnits();
		$this->assertTrue(count($tus) == 23);
		$this->assertTrue($tus[3]->getSource() == 'search');
		$this->assertTrue($tus[6]->getSource() == 'delete');
		$this->assertTrue($tus[15]->getSource() == 'Items');
		$this->assertTrue($tus[21]->getSource() == 'Full preview');
	}
	
	public function testSourceExtraction() {
		// Test with only PHP Actions.
		$sourceCodePaths = array(dirname(__FILE__) . self::FAKE_ACTIONS);
		$extensions = array('php');
		$extractor = new tao_helpers_translation_SourceCodeExtractor($sourceCodePaths, $extensions);
		$extractor->extract();
		$tus = $extractor->getTranslationUnits();
		$this->assertTrue(count($tus) == 21);
		
		// Complete test.
		$extensions = array('php', 'tpl', 'js');
		$sourceCodePaths = array(dirname(__FILE__) . self::FAKE_ACTIONS,
								 dirname(__FILE__) . self::FAKE_VIEWS);
		$extractor->setFileTypes($extensions);
		$extractor->setPaths($sourceCodePaths);
		$extractor->extract();
		$tus = $extractor->getTranslationUnits();
		$this->assertTrue(count($tus) == 56);
		$this->assertTrue($tus[1]->getSource() == 'Import');
		$this->assertTrue($tus[2]->getSource() == ' Please select the input data format to import ');
		$this->assertTrue($tus[5]->getSource() == 'Please upload a CSV file formated as "defined" %min by %max the options above.');
		$this->assertTrue($tus[8]->getsource() == "Please upload \t an RDF file.\n\n");
	}
	
	public function testRDFTranslationModel() {
		$paths = array(dirname(__FILE__) . self::FAKE_RDF_LOCALE);
		$rdfExtractor = new tao_helpers_translation_RDFExtractor($paths);
		$rdfExtractor->extract();
		$tus = $rdfExtractor->getTranslationUnits();
		
		// Nothing should be extracted because no translatable properties
		// are set in the the extractor instance.
		$this->assertTrue(count($tus) == 0);

		// Add a single translatable property and extract again.
		$rdfExtractor->addTranslatableProperty('http://www.w3.org/2000/01/rdf-schema#label');
		$rdfExtractor->extract();
		$tus = $rdfExtractor->getTranslationUnits();
		$this->assertTrue(count($tus) == 5);
		
		// Add two translatable properties.
		$rdfExtractor->removeTranslatableProperty('http://www.w3.org/2000/01/rdf-schema#label');
		$rdfExtractor->setTranslatableProperties(array('http://www.w3.org/2000/01/rdf-schema#label',
													   'http://www.w3.org/2000/01/rdf-schema#comment'));
		$rdfExtractor->extract();
		$tus = $rdfExtractor->getTranslationUnits();
		$this->assertTrue(count($tus) == 6);
		
		// Test 3 Translation Units at random.
		$this->assertTrue($tus[1]->getSubject() == 'http://www.tao.lu/Ontologies/TAO.rdf#LangDE');
		$this->assertTrue($tus[1]->getPredicate() == 'http://www.w3.org/2000/01/rdf-schema#label');
		$this->assertTrue($tus[1]->getSource() == 'unknown');
		$this->assertTrue($tus[1]->getTarget() == 'Allemand');
		$this->assertTrue($tus[1]->getSourceLanguage() == 'unknown');
		$this->assertTrue($tus[1]->getTargetLanguage() == 'FR');
		$this->assertTrue($tus[2]->getSubject() == 'http://www.tao.lu/Ontologies/TAO.rdf#LangDE');
		$this->assertTrue($tus[2]->getPredicate() == 'http://www.w3.org/2000/01/rdf-schema#label');
		$this->assertTrue($tus[2]->getSource() == 'unknown');
		$this->assertTrue($tus[2]->getTarget() == 'Deutsch');
		$this->assertTrue($tus[2]->getSourceLanguage() == 'unknown');
		$this->assertTrue($tus[2]->getTargetLanguage() == 'DE');
		$this->assertTrue($tus[5]->getSubject() == 'http://www.tao.lu/Ontologies/TAO.rdf#LangDE');
		$this->assertTrue($tus[5]->getPredicate() == 'http://www.w3.org/2000/01/rdf-schema#comment');
		$this->assertTrue($tus[5]->getSource() == 'unknown');
		$this->assertTrue($tus[5]->getTarget() == 'The German language.');
		$this->assertTrue($tus[5]->getSourceLanguage() == 'unknown');
		$this->assertTrue($tus[5]->getTargetLanguage() == 'EN');
	}
}
?>