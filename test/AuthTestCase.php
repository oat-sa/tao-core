<?php
require_once dirname(__FILE__) . '/TestRunner.php';
require_once dirname(__FILE__) . '/../includes/common.php';

/**
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 * @subpackage test
 */
class AuthTestCase extends UnitTestCase {
	
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
	 * tests initialization
	 */
	public function setUp(){		
		$this->testUser['clearPassword'] = $this->testUser['password'];
		$this->testUser['password'] = md5($this->testUser['clearPassword']);
		
		$this->userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
		$this->userService->saveUser($this->testUser);
		
	}
	
	/**
	 * tests clean up
	 */
	public function tearDown(){
		$this->userService->removeUser($this->testUser['login']);
		session_destroy();
	}

	/* !!!
	 * DO NOT ADD OTHER TEST METHODS 
	 * BECAUSE SESSION IS DESTROYED 
	 * AFTER AuthTestCase::testAuth
	 * IN AuthTestCase::tearDown
	 * !!!
	 */

	/**
	 * test the user authentication to TAO and to the API
	 */
	public function testAuth(){
		
		//is the user in the db
		$this->assertFalse(	$this->userService->loginAvailable($this->testUser['login']) );
		
		//no other user session
		$this->assertFalse( tao_models_classes_UserService::isASessionOpened() );
		
		//login user
		$this->assertTrue( $this->userService->loginUser($this->testUser['login'], $this->testUser['clearPassword']) );
		
		//check session
		$this->assertTrue( tao_models_classes_UserService::isASessionOpened() );
		
		$currentUser =  $this->userService->getCurrentUser(Session::getAttribute(tao_models_classes_UserService::LOGIN_KEY));
		
		foreach($currentUser as $key => $value){
			if(is_string($key)){
				if(isset($this->testUser[$key])){
					$this->assertEqual($this->testUser[$key], $value, "check equal $key => $value");
				}
			}
		}
		
		//connect the API
		core_control_FrontController::connect($currentUser['login'], $currentUser['password'], DATABASE_NAME);
		
		//init the languages
		core_kernel_classes_Session::singleton()->defaultLg = $this->userService->getDefaultLanguage();
		core_kernel_classes_Session::singleton()->setLg($this->userService->getUserLanguage($currentUser['login']));
		
		//try to access the API
		$this->assertIsA(new core_kernel_classes_Resource(GENERIS_BOOLEAN), 'core_kernel_classes_Resource');

		//check the languages
		$this->assertEqual($this->userService->getDefaultLanguage(), 'EN');
		$this->assertEqual($this->userService->getUserLanguage($currentUser['login']), 'EN');
		$this->assertEqual(core_kernel_classes_Session::singleton()->getLg(), $this->userService->getUserLanguage($currentUser['login']));
		$this->assertEqual(core_kernel_classes_Session::singleton()->defaultLg, $this->userService->getDefaultLanguage());
	}
}
?>