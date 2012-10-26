<?php
require_once dirname(__FILE__) . '/../TaoTestRunner.php';
require_once  PHPCOVERAGE_HOME. "/CoverageRecorder.php";
require_once PHPCOVERAGE_HOME . "/reporter/HtmlCoverageReporter.php";


//get the test into each extensions
$tests = TaoTestRunner::getTests(array('tao'));

//create the test sutie
$testSuite = new TestSuite('TAO extensions tests');
foreach($tests as $testCase){
	$testSuite->addFile($testCase);
}

//add the reporter regarding the context
if(PHP_SAPI == 'cli'){
	$reporter = new XmlTimeReporter();
}
else{
	$reporter = new HtmlReporter();
}

$includePaths = array( ROOT_PATH.'tao/models',ROOT_PATH.'tao/helpers');
$excludePaths = array();
$covReporter = new HtmlCoverageReporter("Code Coverage Report TAO", "", PHPCOVERAGE_REPORTS."tao/");
$cov = new CoverageRecorder($includePaths, $excludePaths, $covReporter);
//run the unit test suite
$cov->startInstrumentation();
$testSuite->run($reporter);
$cov->stopInstrumentation();

$cov->generateReport();
$covReporter->printTextSummary(PHPCOVERAGE_REPORTS.'tao_coverage.txt');
?>