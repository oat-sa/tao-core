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
	protected $testUser = array(
		'login'		=> 	'jdoe',
		'password'	=>	'test123',
		'LastName'	=>	'Doe',
		'FirstName'	=>	'John',
		'E_Mail'	=>	'jdoe@tao.lu',
		'Company'	=>	'TAO inc',
		'Deflg'		=>	'EN',
		'Uilg'		=>	'EN'
	);
	
	/**
	 * @var array user data set with special chars
	 */
	protected $testUserUtf8 = array(
		'login'		=> 	'f.lecé',
		'password'	=>	'6crète!',
		'LastName'	=>	'Lecéfranc',
		'FirstName'	=>	'François',
		'E_Mail'	=>	'',
		'Company'	=>	'tao© &Co',
		'Deflg'		=>	'EN',
		'Uilg'		=>	'FR'
	);
	
	/**
	 * tests initialization
	 */
	public function setUp(){		
		TestRunner::initTest();
		
		$this->testUser['password'] = md5($this->testUser['password']);
		$this->testUserUtf8['password'] = md5($this->testUserUtf8['password']);
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
		$this->assertTrue(	$this->userService->loginAvailable($this->testUser['login']) );
		$this->assertTrue(	$this->userService->saveUser($this->testUser) );
		$this->assertFalse(	$this->userService->loginAvailable($this->testUser['login']) );
		
		//check inserted data
		$insertedUser = $this->userService->getOneUser($this->testUser['login']);
		$this->assertIsA($insertedUser, 'array');
		foreach($this->testUser as $key => $value){
			$this->assertTrue(isset($insertedUser[$key]));
			$this->assertEqual($insertedUser[$key], $value);
		}
	}
	
	/**
	 * Test user insertion with special chars
	 * @see tao_models_classes_UserService::saveUser
	 */
	public function testAddUtf8User(){
		
		//insert it
		$this->assertTrue(	$this->userService->loginAvailable($this->testUserUtf8['login']) );
		$this->assertTrue(	$this->userService->saveUser($this->testUserUtf8) );
		$this->assertFalse(	$this->userService->loginAvailable($this->testUserUtf8['login']) );
		
		//check inserted data
		$insertedUser = $this->userService->getOneUser($this->testUserUtf8['login']);
		$this->assertIsA($insertedUser, 'array');
		$this->assertTrue(isset($insertedUser['login']));
		$this->assertEqual($insertedUser['login'], 		$this->testUserUtf8['login']);
		$this->assertEqual($insertedUser['LastName'], 	$this->testUserUtf8['LastName']);
		$this->assertEqual($insertedUser['FirstName'], 	$this->testUserUtf8['FirstName']);
		$this->assertEqual($insertedUser['Company'], 	$this->testUserUtf8['Company']);
	}
	
	/**
	 * Test user removing
	 * @see tao_models_classes_UserService::removeUser
	 */
	public function testDelete(){
		$insertedUser = $this->userService->getOneUser($this->testUser['login']);
		if(is_array($insertedUser)){
			if(isset($insertedUser['login'])){
				$this->assertTrue($this->userService->removeUser($insertedUser['login']));
				$this->assertTrue($this->userService->loginAvailable($this->testUser['login']));
			}
		}
		
		$insertedUser = $this->userService->getOneUser($this->testUserUtf8['login']);
		if(is_array($insertedUser)){
			if(isset($insertedUser['login'])){
				$this->assertTrue($this->userService->removeUser($insertedUser['login']));
				$this->assertTrue($this->userService->loginAvailable($this->testUserUtf8['login']));
			}
		}
	}
}
?>