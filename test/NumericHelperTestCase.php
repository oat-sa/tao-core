<?php
require_once dirname(__FILE__) . '/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

class NumericHelperTestCase extends UnitTestCase {
	public function testParseFloat() {
		$this->assertEqual(10, tao_helpers_Numeric::parseFloat("10"));
		$this->assertEqual(10, tao_helpers_Numeric::parseFloat("10g"));
		$this->assertEqual(10.5, tao_helpers_Numeric::parseFloat("10.5"));
		$this->assertEqual(10.5, tao_helpers_Numeric::parseFloat("10,5"));
		$this->assertEqual(1105.5, tao_helpers_Numeric::parseFloat("1.105,5"));
	}
}
?>
