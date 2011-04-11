<?php

# include TestRunner
require 'PHPUnit/Autoload.php';


//create the test sutie
$testSuite = new PHPUnit_Framework_TestSuite('TAO Functional Test Suite (tao extension)');

$testSuite->addTestFile(dirname(__FILE__) . '/SeleniumBackendMainTestCase.php');

PHPUnit_TextUI_TestRunner::run($testSuite);
?>