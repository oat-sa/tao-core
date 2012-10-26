<?php
// THIS FILE MUST BE UTF-8 encoded to get the TestCase working !!!
// PLEASE BE CAREFULL.
require_once dirname(__FILE__) . '/../../tao/test/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

class AdaptersTestCase extends UnitTestCase {

	const CSV_FILE_USERS_HEADER_UNICODE = '/samples/csv/users1-header.csv';
	const CSV_FILE_USERS_NO_HEADER_UNICODE = '/samples/csv/users1-no-header.csv';
	
	public function testGenerisAdapterCsv() {
		// First test: instantiate a generis CSV adapter and load a file.
		// Let the default options rule the adapter.
		
	}
	
	public function testCsvFileParsing() {
		// + Subtest 1: Unicode CSV file with header row.
		// --------------------------------------------------------------------------------
		$path = dirname(__FILE__) . self::CSV_FILE_USERS_HEADER_UNICODE;
		$csvFile = new tao_helpers_data_CsvFile();
		$csvFile->load($path);
		
		// - test column mapping.
		$expectedMapping = array('label', 'First Name', 'Last Name',
								 'Login', 'Mail', 'password', 'UserUILg');
		$this->assertEqual($expectedMapping, $csvFile->getColumnMapping(), 'The column mapping should be ' . var_export($expectedMapping, true) . '.');
		$this->assertEqual($csvFile->count(), 16, 'The CSV file contains ' . $csvFile->count() . ' rows instead of 16.');
		$this->assertEqual($csvFile->getColumnCount(), 7, 'The CSV file contains ' . $csvFile->getColumnCount() . ' columns instead of 7.');
		
		// - test some row retrievals.
		$expectedRow = array('TAO Jérôme Bogaerts',
							 'Jérôme',
							 'Bogaerts',
							 'jbogaerts',
							 'jerome.bogaerts@tudor.lu',
							 'jbogaerts',
							 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN');
		$this->assertEqual($csvFile->getRow(0), $expectedRow);
		
		$expectedRow = array('label' => 'TAO Isabelle Jars',
							 'First Name' => 'Isabelle',
							 'Last Name' => 'Jars',
							 'Login' => 'ijars',
							 'Mail' => 'isabelle.jars@tudor.lu',
							 'password' => 'ijars',
							 'UserUILg' => 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN');
		$this->assertEqual($csvFile->getRow(4, true), $expectedRow);
		
		
		// + Subtest 2: Unicode CSV file withouth header row.
		// --------------------------------------------------------------------------------
		$path = dirname(__FILE__) . self::CSV_FILE_USERS_NO_HEADER_UNICODE;
		$csvFile = new tao_helpers_data_CsvFile($options = array('first_row_column_names' => false));
		$csvFile->load($path);
		
		// - test column mapping.
		$expectedMapping = array();
		$this->assertEqual($expectedMapping, $csvFile->getColumnMapping(), 'The column mapping should be ' . var_export($expectedMapping, true) . '.');
		$this->assertEqual($csvFile->count(), 16, 'The CSV file contains ' . $csvFile->count() . ' rows instead of 16.');
		$this->assertEqual($csvFile->getColumnCount(), 7, 'The CSV file contains ' . $csvFile->getColumnCount() . ' columns instead of 7.');
		
		// - test some row retrievals.
		$expectedRow = array('TAO Jérôme Bogaerts',
							 'Jérôme',
							 'Bogaerts',
							 'jbogaerts',
							 'jerome.bogaerts@tudor.lu',
							 'jbogaerts',
							 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN');
		$this->assertEqual($csvFile->getRow(0), $expectedRow);
		
		$expectedRow = array('TAO Matteo Mellis',
							 'Matteo',
							 'Mellis',
							 'mmellis',
							 'matteo.mellis@tudor.lu',
							 'mmellis',
							 'http://www.tao.lu/Ontologies/TAO.rdf#LangEN');
		$this->assertEqual($csvFile->getRow(15), $expectedRow);
	}
}
?>
