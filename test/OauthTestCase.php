<?php
require_once dirname(__FILE__) . '/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * @author Jerome Bogaerts, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class OauthTestCase extends UnitTestCase {
    
	/**
	 * @var core_kernel_classes_Resource
	 */
	private $credentials;
	
    public function setUp()
    {		
        parent::setUp();
		TaoTestRunner::initTest();
		$class = new core_kernel_classes_Class(	CLASS_OAUTH_CONSUMER);
		$this->credentials = $class->createInstanceWithProperties(array(
			RDFS_LABEL				=> 'test_credentials',
			PROPERTY_OAUTH_KEY		=> 'testcase_12345',
			PROPERTY_OAUTH_SECRET	=> 'secret_12345'
		));
	}
	
	public function tearDown() {
		parent::tearDown();
		$this->credentials->delete();
	}
	
	public function testValidation(){
		// @todo implement curl bassed test
	}
}
?>