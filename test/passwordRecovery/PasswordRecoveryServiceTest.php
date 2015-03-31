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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *               
 */
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\passwordRecovery\PasswordRecoveryService;
use oat\tao\model\messaging\transportStrategy\FileSink;

include_once dirname(__FILE__) . '/../../includes/raw_start.php';

/**
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package tao
 */
class PasswordRecoveryServiceTest extends TaoPhpUnitTestRunner
{

    /**
     * @var tao_models_classes_UserService
     */
    protected $userService = null;
    
    /**
     * @var PasswordRecoveryService
     */
    protected $passwordRecoveryService = null;

    /**
     * @var array user data set
     */
    protected $testUserData = array(
        PROPERTY_USER_LOGIN => 'john.doe',
        PROPERTY_USER_PASSWORD => 'secure',
        PROPERTY_USER_LASTNAME => 'Doe',
        PROPERTY_USER_FIRSTNAME => 'John',
        PROPERTY_USER_MAIL => 'jonhdoe@tao.lu',
        PROPERTY_USER_DEFLG => 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
        PROPERTY_USER_UILG => 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
        PROPERTY_USER_ROLES => INSTANCE_ROLE_BACKOFFICE
    );

    /**
     * @var core_kernel_classes_Resource
     */
    protected $testUser = null;

    /**
     * Password value before encryption
     * 
     * @var string
     */
    private $clearPassword = '';

    /**
     * tests initialization
     */
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();
        $this->userService = tao_models_classes_UserService::singleton();
        $this->passwordRecoveryService = PasswordRecoveryService::singleton();

        $this->clearPassword = $this->testUserData[PROPERTY_USER_PASSWORD];
        $this->testUserData[PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt($this->testUserData[PROPERTY_USER_PASSWORD]);

        $class = new core_kernel_classes_Class(CLASS_GENERIS_USER);
        $this->testUser = $class->createInstance();
        $this->assertNotNull($this->testUser);
        $this->userService->bindProperties($this->testUser, $this->testUserData);
    }

    /**
     * tests clean up
     */
    public function tearDown()
    {
        if (!is_null($this->userService)) {
            $this->userService->removeUser($this->testUser);
        }
    }

    public function testSendMail()
    {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR.'message.html';
        $transporter = new FileSink();
        $transporter->setFilePath($filePath);
        $generisUser = new \core_kernel_users_GenerisUser($this->testUser);
        
        $this->assertEmpty($generisUser->getPropertyValues(PasswordRecoveryService::PROPERTY_PASSWORD_RECOVERY_TOKEN));
        $generisUser->refresh();
        
        $this->passwordRecoveryService->getMessagingService()->setTransport($transporter);
        
        $result = $this->passwordRecoveryService->sendMail($this->testUser);
        $this->assertTrue($result);
        $this->assertFileExists($filePath);
        
        $messageContent = file_get_contents($filePath);
        
        $this->assertContains($this->testUserData[PROPERTY_USER_FIRSTNAME], $messageContent);
        
        $passwordRecoveryToken = current($generisUser->getPropertyValues(PasswordRecoveryService::PROPERTY_PASSWORD_RECOVERY_TOKEN));
        $this->assertNotEmpty($passwordRecoveryToken);
        
        $this->assertContains($passwordRecoveryToken, $messageContent);
        
        unlink($filePath);
    }
}
