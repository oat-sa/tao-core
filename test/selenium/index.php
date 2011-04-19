<?php
error_reporting(E_ALL);
# include TestRunner
require 'PHPUnit/Autoload.php';


//create the test sutie
$testSuite = new PHPUnit_Framework_TestSuite('TAO Functional Test Suite (tao extension)');

$testSuite->addTestFile(dirname(__FILE__) . '/SeleniumBackendMainTestCase.php');

if (PHP_SAPI == 'cli') {
	PHPUnit_TextUI_TestRunner::run($testSuite);
}
else {
	// Do not output anything before the report in Server mode.
	// We only want the HTML.
	ob_start();
	
	$reportFileName = tempnam(sys_get_temp_dir(), 'tao');
	PHPUnit_TextUI_TestRunner::run($testSuite, array('junitLogfile' => $reportFileName,
													 'verbose', false));
	
	// Get the report and transform the XML to an HTML readable report.
	$xmlDoc = new DOMDocument();
	$xslDoc = new DOMDocument();
	
	$xmlDoc->load($reportFileName);
	$xslDoc->load(dirname(__FILE__) . '/includes/phpunit-noframes.xsl');
	
	$xsl = new XSLTProcessor();
	$xsl->importStyleSheet($xslDoc);
	
	ob_clean();
	
	echo $xsl->transformToXML($xmlDoc);
	unlink($reportFileName);
}
?>