<?php
require_once dirname(__FILE__) . '/TestRunner.php';

//get the test into each extensions
$tests = TestRunner::getTests(array('tao'));

//create the test sutie
$testSuite = new TestSuite('TAO extensions tests');
foreach($tests as $testCase){
	$testSuite->addTestFile($testCase);
}    

//add the reporter regarding the context
if(PHP_SAPI == 'cli'){
	$reporter = new XmlTimeReporter();
}
else{
	$reporter = new HtmlReporter();
}
//run the unit test suite
$testSuite->run($reporter);
?>