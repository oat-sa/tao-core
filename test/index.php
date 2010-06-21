<?php
require_once dirname(__FILE__) . '/TestRunner.php';
require_once dirname(__FILE__) . '/../includes/common.php';

class XmlTimeReporter extends XmlReporter {
  var $pre;

  function paintMethodStart($test_name) {
    $this->pre = microtime();
    parent::paintMethodStart($test_name);
  }

  function paintMethodEnd($test_name) {
    $post = microtime();
    if ($this->pre != null) {
      $duration = $post - $this->pre;
      // how can post time be less than pre?  assuming zero if this happens..
      if ($post < $this->pre) $duration = 0;
      print $this->_getIndent(1);
      print "<time>$duration</time>\n";
    }
    parent::paintMethodEnd($test_name);
    $this->pre = null;
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
	$reporter = new XmlTimeReporter();
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