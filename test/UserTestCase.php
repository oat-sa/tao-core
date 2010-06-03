<?php
require_once dirname(__FILE__) . '/TestRunner.php';
require_once dirname(__FILE__) . '/../includes/common.php';

/**
 * Test the user management 
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class UserTestCase extends UnitTestCase {
	
	/**
	 * @var tao_models_classes_UserService
	 */
	protected $userService = null;
	
	/**
	 * @var array user data set
	 */
	protected $testUserData = array(
		PROPERTY_USER_LOGIN		=> 	'tjdoe',
		PROPERTY_USER_PASSWORD	=>	'test123',
		PROPERTY_USER_LASTNAME	=>	'Doe',
		PROPERTY_USER_FIRTNAME	=>	'John',
		PROPERTY_USER_MAIL		=>	'jdoe@tao.lu',
		PROPERTY_USER_DEFLG		=>	'EN',
		PROPERTY_USER_UILG		=>	'EN'
	);
	
	/**
	 * @var array user data set with special chars
	 */
	protected $testUserUtf8Data = array(
		PROPERTY_USER_LOGIN		=> 	'f.lecé',
		PROPERTY_USER_PASSWORD	=>	'6crète!',
		PROPERTY_USER_LASTNAME	=>	'Lecéfranc',
		PROPERTY_USER_FIRTNAME	=>	'François',
		PROPERTY_USER_MAIL		=>	'f.lecé@tao.lu',
		PROPERTY_USER_DEFLG		=>	'EN',
		PROPERTY_USER_UILG		=>	'FR'
	);
	
	/**
	 * @var core_kernel_classes_Resource
	 */
	protected $testUser = null;
	
	/**
	 * @var core_kernel_classes_Resource
	 */
	protected $testUserUtf8 = null;
	
	/**
	 * tests initialization
	 */
	public function setUp(){		
		TestRunner::initTest();
		
		$this->testUserData[PROPERTY_USER_PASSWORD] = md5($this->testUserData[PROPERTY_USER_PASSWORD]);
		$this->testUserUtf8Data[PROPERTY_USER_PASSWORD] = md5($this->testUserUtf8Data[PROPERTY_USER_PASSWORD]);
	}
	
	/**
	 * Test the user service implementation
	 * @see tao_models_classes_ServiceFactory::get
	 */
	public function testService(){
		$userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
		$this->assertIsA($userService, 'tao_models_classes_Service');
		$this->assertIsA($userService, 'tao_models_classes_UserService');
		
		$this->userService = $userService;
	}

	/**
	 * Test user insertion
	 * @see tao_models_classes_UserService::saveUser
	 */
	public function testAddUser(){

		//insert it
		$this->assertTrue(	$this->userService->loginAvailable($this->testUserData[PROPERTY_USER_LOGIN]));
		$this->assertTrue(	$this->userService->saveUser($this->testUser, $this->testUserData) );
		$this->assertFalse(	$this->userService->loginAvailable($this->testUserData[PROPERTY_USER_LOGIN]));
		
		//check inserted data
		$this->testUser = $this->userService->getOneUser($this->testUserData[PROPERTY_USER_LOGIN]);
		$this->assertIsA($this->testUser, 'core_kernel_classes_Resource');
		foreach($this->testUserData as $prop => $value){
			try{
				$this->assertEqual($value, $this->testUser->getUniquePropertyValue(new core_kernel_classes_Property($prop)));
			}
			catch(common_Exception $ce){ 
				$this->fail($ce);
			}
		}
	}
	
	/**
	 * Test user insertion with special chars
	 * @see tao_models_classes_UserService::saveUser
	 */
	public function testAddUtf8User(){
		
	//insert it
		$this->assertTrue(	$this->userService->loginAvailable($this->testUserUtf8Data[PROPERTY_USER_LOGIN]));
		$this->assertTrue(	$this->userService->saveUser($this->testUserUtf8, $this->testUserUtf8Data) );
		$this->assertFalse(	$this->userService->loginAvailable($this->testUserUtf8Data[PROPERTY_USER_LOGIN]));
		
		//check inserted data
		$this->testUserUtf8 = $this->userService->getOneUser($this->testUserUtf8Data[PROPERTY_USER_LOGIN]);
		$this->assertIsA($this->testUserUtf8, 'core_kernel_classes_Resource');
		foreach($this->testUserUtf8Data as $prop => $value){
			try{
				$this->assertEqual($value, $this->testUserUtf8->getUniquePropertyValue(new core_kernel_classes_Property($prop)));
			}
			catch(common_Exception $ce){ 
				$this->fail($ce);
			}
		}
	}
	
	/**
	 * Test user removing
	 * @see tao_models_classes_UserService::removeUser
	 */
	public function testDelete(){
		$this->testUser = $this->userService->getOneUser($this->testUserData[PROPERTY_USER_LOGIN]);
		$this->assertIsA($this->testUser, 'core_kernel_classes_Resource');
		$this->assertTrue($this->userService->removeUser($this->testUser));
		$this->assertTrue($this->userService->loginAvailable($this->testUserData[PROPERTY_USER_LOGIN]));
		
		
		$this->testUserUtf8 = $this->userService->getOneUser($this->testUserUtf8Data[PROPERTY_USER_LOGIN]);
		$this->assertIsA($this->testUserUtf8, 'core_kernel_classes_Resource');
		$this->assertTrue($this->userService->removeUser($this->testUserUtf8));
		$this->assertTrue($this->userService->loginAvailable($this->testUserUtf8Data[PROPERTY_USER_LOGIN]));
	}
}
?>