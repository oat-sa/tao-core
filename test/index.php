<?php
require_once dirname(__FILE__) . '/TestRunner.php';
require_once dirname(__FILE__) . '/../includes/common.php';

/**
 * Custom reporter
 */
class MyHtmlReporter extends HtmlReporter {
    function paintPass($message) {
        parent::paintPass($message);
        print "<br />\n<span class=\"pass\">Pass:  ";
        $breadcrumbs = $this->getTestList();
        print $breadcrumbs[2]."::".$breadcrumbs[3];
        print"</span><br />\n<span style='font-size:0.8em;'>$message</span><br />\n";
    }
}

/**
 * the list of extensions to test
 * @var array
 */
$testedExtensions = array(
	'tao',
	'taoDelivery',
	'taoGroups',
	'taoItems',
	'taoResults',
	'taoSubjects',
	'taoTests'
);

//get the test into each extensions
$tests = TestRunner::getTests($testedExtensions);

//create the test sutie
$testSuite = new TestSuite('TAO extensions tests<br />('.implode(', ', $testedExtensions).')');
foreach($tests as $testCase){
	$testSuite->addTestFile($testCase);
}    

//add the reporter regarding the context
if(PHP_SAPI == 'cli'){
	$reporter = new TextReporter();
}
else{
	if(isset($_GET['verbose'])){
		$reporter = new MyHtmlReporter();
	}
	else{
		$reporter = new HtmlReporter();
	}
}
//run the unit test suite
$testSuite->run($reporter);

?>