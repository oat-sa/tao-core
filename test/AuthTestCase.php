<?php
require_once dirname(__FILE__) . '/TestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

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
	protected $testUserData = array(
		PROPERTY_USER_LOGIN		=> 	'jane.doe',
		PROPERTY_USER_PASSWORD	=>	'p34@word',
		PROPERTY_USER_LASTNAME	=>	'Doe',
		PROPERTY_USER_FIRTNAME	=>	'Jane',
		PROPERTY_USER_MAIL		=>	'jane.doe@tao.lu',
		PROPERTY_USER_DEFLG		=>	'EN',
		PROPERTY_USER_UILG		=>	'EN'
	);
	
	/**
	 * @var core_kernel_classes_Resource
	 */
	protected $testUser = null;
	
	/**
	 * @var string
	 */
	private $clearPassword = '';
	
	
	/**
	 * tests initialization
	 */
	public function setUp(){		
		TestRunner::initTest();
		
		$this->clearPassword = $this->testUserData[PROPERTY_USER_PASSWORD];
		$this->testUserData[PROPERTY_USER_PASSWORD] = md5($this->testUserData[PROPERTY_USER_PASSWORD]);
		
		$this->userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
		$this->userService->saveUser($this->testUser, $this->testUserData);
	}
	
	/**
	 * tests clean up
	 */
	public function tearDown(){
		$this->userService->removeUser($this->testUser);
		if(core_kernel_users_Service::singleton()->isASessionOpened()){
			core_kernel_users_Service::singleton()->logout();
		}
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
		$this->assertFalse(	$this->userService->loginAvailable($this->testUserData[PROPERTY_USER_LOGIN]) );
		
		if(core_kernel_users_Service::singleton()->isASessionOpened()){
			core_kernel_users_Service::singleton()->logout();
		}
	
		//no other user session
		$this->assertFalse( core_kernel_users_Service::singleton()->isASessionOpened() );

		//check user login
		$this->assertTrue( $this->userService->loginUser($this->testUserData[PROPERTY_USER_LOGIN], md5($this->clearPassword)) );
		
		//connect user
		$this->assertTrue( $this->userService->connectCurrentUser() );
			
		
		//check session
		$this->assertTrue( core_kernel_users_Service::singleton()->isASessionOpened() );
		
		
		$currentUser =  $this->userService->getCurrentUser();
		$this->assertIsA($currentUser, 'core_kernel_classes_Resource');
		foreach($this->testUserData as $prop => $value){
			try{
				$this->assertEqual($value, $currentUser->getUniquePropertyValue(new core_kernel_classes_Property($prop)));
			}
			catch(common_Exception $ce){ 
				$this->fail($ce);
			}
		}
		
	}
}
?>