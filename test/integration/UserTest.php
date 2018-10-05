<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

namespace oat\tao\test\integration;

use oat\generis\model\GenerisRdf;
use oat\generis\model\user\PasswordConstraintsService;
use oat\tao\model\TaoOntology;
use oat\generis\test\GenerisPhpUnitTestRunner;
use tao_models_classes_UserService;
use core_kernel_classes_Resource;
use core_kernel_users_Service;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Literal;
use ReflectionClass;

/**
 * Test the user management 
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package tao
 
 */
class UserTest extends GenerisPhpUnitTestRunner {
	
	/**
	 * @var tao_models_classes_UserService
	 */
	protected $userService;
	
	/**
	 * @var array user data set
	 */
	protected $testUserData = array(
		GenerisRdf::PROPERTY_USER_LOGIN		=> 	'tjdoe',
		GenerisRdf::PROPERTY_USER_PASSWORD	=>	'test123',
		GenerisRdf::PROPERTY_USER_LASTNAME	=>	'Doe',
		GenerisRdf::PROPERTY_USER_FIRSTNAME	=>	'John',
		GenerisRdf::PROPERTY_USER_MAIL		=>	'jdoe@tao.lu',
		GenerisRdf::PROPERTY_USER_UILG		=>	'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
		GenerisRdf::PROPERTY_USER_ROLES		=>  'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole'
	);
	
	/**
	 * @var array user data set with special chars
	 */
	protected $testUserUtf8Data = array(
		GenerisRdf::PROPERTY_USER_LOGIN		=> 	'f.lecé',
		GenerisRdf::PROPERTY_USER_PASSWORD	=>	'6crète!',
		GenerisRdf::PROPERTY_USER_LASTNAME	=>	'Lecéfranc',
		GenerisRdf::PROPERTY_USER_FIRSTNAME	=>	'François',
		GenerisRdf::PROPERTY_USER_MAIL		=>	'f.lecé@tao.lu',
		GenerisRdf::PROPERTY_USER_UILG		=>	'http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR',
		GenerisRdf::PROPERTY_USER_ROLES		=>  'http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole'
	);
	
	/**
	 * @var core_kernel_classes_Resource
	 */
	protected $testUser;
	
	/**
	 * @var core_kernel_classes_Resource
	 */
	protected $testUserUtf8;
	
	/**
	 * tests initialization
	 */
	public function setUp(){
        $this->userService = tao_models_classes_UserService::singleton();
		$this->testUserData[GenerisRdf::PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt($this->testUserData[GenerisRdf::PROPERTY_USER_PASSWORD]);
		$this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt($this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_PASSWORD]);
	}

	/**
	 * Test the user service implementation
	 * @see tao_models_classes_ServiceFactory::get
	 */
	public function testService()
    {
        $this->assertInstanceOf(tao_models_classes_UserService::class, $this->userService);
	}

	/**
	 * Test user insertion
	 * @see tao_models_classes_UserService::saveUser
	 */
	public function testAddUser(){

		//insert it
		$this->assertTrue($this->userService->loginAvailable($this->testUserData[GenerisRdf::PROPERTY_USER_LOGIN]));
		$tmclass = new core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
		$this->testUser = $tmclass->createInstance();
		$this->assertNotNull($this->testUser);
		$this->assertTrue($this->testUser->exists());
		$result = $this->userService->bindProperties($this->testUser, $this->testUserData);
		$this->assertNotNull($result);
		$this->assertNotEquals($result,false);
		$this->assertFalse($this->userService->loginAvailable($this->testUserData[GenerisRdf::PROPERTY_USER_LOGIN]));
		
		//check inserted data
		$this->testUser = $this->getUserByLogin($this->testUserData[GenerisRdf::PROPERTY_USER_LOGIN]);
		$this->assertInstanceOf( core_kernel_classes_Resource::class, $this->testUser );
		foreach($this->testUserData as $prop => $value){
            $p = new core_kernel_classes_Property($prop);
            $v = $this->testUser->getUniquePropertyValue($p);
            $v = ($v instanceof core_kernel_classes_Literal) ? $v->literal : $v->getUri();
            $this->assertEquals($value, $v);
		}
	}
	
	/**
	 * Test user insertion with special chars
	 */
	public function testAddUtf8User(){
		
		$this->assertTrue($this->userService->loginAvailable($this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_LOGIN]));
		$tmclass = new core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
		$this->testUserUtf8 = $tmclass->createInstance();
		$this->assertNotNull($this->testUserUtf8);
		$this->assertTrue($this->testUserUtf8->exists());
		$result = $this->userService->bindProperties($this->testUserUtf8, $this->testUserUtf8Data);
		$this->assertNotNull($result);
		$this->assertNotEquals($result,false);
		$this->assertFalse($this->userService->loginAvailable($this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_LOGIN]));
		
		//check inserted data
		$this->testUserUtf8 = $this->getUserByLogin($this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_LOGIN]);
		$this->assertInstanceOf( core_kernel_classes_Resource::class, $this->testUserUtf8 );
		foreach($this->testUserUtf8Data as $prop => $value){
            $p = new core_kernel_classes_Property($prop);
            $v = $this->testUserUtf8->getUniquePropertyValue($p);
            $v = ($v instanceof core_kernel_classes_Literal) ? $v->literal : $v->getUri();
            $this->assertEquals($value, $v);
		}
	}
	
	public function testLoginAvailability(){
		$user = $this->getUserByLogin($this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_LOGIN]);
		$loginProperty = new core_kernel_classes_Property(GenerisRdf::PROPERTY_USER_LOGIN);
		
		$this->assertTrue(!empty($user));
		$this->assertFalse($this->userService->loginAvailable($this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_LOGIN]));
		
		// Test to cover issue #2135
		$this->assertTrue($this->userService->loginAvailable('my new user'));
		$user->editPropertyValues($loginProperty, 'my new user');
		$this->assertTrue($this->userService->loginExists('my new user'));
		$this->assertFalse($this->userService->loginAvailable('my new user'));
		
		$user->EditPropertyValues($loginProperty, $this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_LOGIN]);
	}

	/**
	 * Test user removing
	 * @see tao_models_classes_UserService::removeUser
	 */
	public function testDelete(){
		$this->testUser = $this->getUserByLogin($this->testUserData[GenerisRdf::PROPERTY_USER_LOGIN]);
		$this->assertInstanceOf( core_kernel_classes_Resource::class, $this->testUser );
		$this->assertTrue($this->userService->removeUser($this->testUser));
		$this->assertTrue($this->userService->loginAvailable($this->testUserData[GenerisRdf::PROPERTY_USER_LOGIN]));
		
		
		$this->testUserUtf8 = $this->getUserByLogin($this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_LOGIN]);
		$this->assertInstanceOf( core_kernel_classes_Resource::class, $this->testUserUtf8 );
		$this->assertTrue($this->userService->removeUser($this->testUserUtf8));
		$this->assertTrue($this->userService->loginAvailable($this->testUserUtf8Data[GenerisRdf::PROPERTY_USER_LOGIN]));
	}
	
	protected function getUserByLogin($login) {
        $class = new core_kernel_classes_Class(GenerisRdf::CLASS_GENERIS_USER);
        $users = $class->searchInstances(
            array(GenerisRdf::PROPERTY_USER_LOGIN => $login),
            array('like' => false, 'recursive' => true)
        );

        $this->assertEquals(1, count($users));
        return current($users);
    }

	public function testPasswordConstraints()
	{

		$foo = self::getMethod( 'register' );

		$foo->invokeArgs( PasswordConstraintsService::singleton(), array( array( 'length' => 20 ) ) );
		$this->assertFalse( PasswordConstraintsService::singleton()->validate( 'a2asdjKISj319(*^^#' ) );

		$foo->invokeArgs( PasswordConstraintsService::singleton(), array( array( 'upper' => false, 'length' => 2 ) ) );
		$this->assertTrue( PasswordConstraintsService::singleton()->validate( 'a2asdjj319(*^^#' ) );


		$foo->invokeArgs( PasswordConstraintsService::singleton(), array( array( 'upper' => true, 'length' => 20 ) ) );
		$this->assertFalse( PasswordConstraintsService::singleton()->validate( 'a2asRdjj319(*^^#' ) );

	}

	protected static function getMethod( $name )
	{
		$class  = new ReflectionClass( 'oat\generis\model\user\PasswordConstraintsService' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}


	/**
	 * Clearing up ( in case if delete test was not executed )
	 */
	public function __destruct()
	{
		if ($this->testUser) {
			$this->userService->removeUser( $this->testUser );
		}

		if ($this->testUserUtf8) {
			$this->userService->removeUser( $this->testUserUtf8 );
		}
	}

	public static function tearDownAfterClass()
	{
		$register = self::getMethod( 'register' );
		$config   = self::getMethod( 'getConfig' )->invokeArgs( PasswordConstraintsService::singleton(), array() );

		$register->invokeArgs( PasswordConstraintsService::singleton(), array( $config ) );
	}
}