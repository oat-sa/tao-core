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
use oat\tao\model\messaging\MessagingService;
use oat\tao\model\messaging\Message;
use oat\tao\model\messaging\transportStrategy\FileSink;

include_once dirname(__FILE__) . '/../../includes/raw_start.php';

/**
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package tao
 */
class MessagingServiceTest extends TaoPhpUnitTestRunner
{

    /**
     * @var tao_models_classes_UserService
     */
    protected $userService = null;
    
    /**
     * @var MessagingService
     */
    protected $messagingService = null;
    
    /**
     * @var Message
     */
    protected $message = null;
    
    /**
     * @var string Message content
     */
    protected $messageBody = "Lorem Ipsum is simply dummy text of the printing and typesetting industry";

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
     * tests initialization
     */
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();
        $this->userService = tao_models_classes_UserService::singleton();
        $this->messagingService = MessagingService::singleton();

        $class = new core_kernel_classes_Class(CLASS_GENERIS_USER);
        $this->testUser = $class->createInstance();
        $this->assertNotNull($this->testUser);
        $this->userService->bindProperties($this->testUser, $this->testUserData);
        
        $generisUser = new \core_kernel_users_GenerisUser($this->testUser);
        
        $this->message = new Message();
        $this->message->setTo($generisUser);
        $this->message->setBody($this->messageBody);
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
        $testfolder = tao_helpers_File::createTempDir();
        $filePath = $testfolder . 'message.html';
        
        $transporter = new FileSink();
        $transporter->setFilePath($filePath);
        
        $this->messagingService->setTransport($transporter);
        
        $result = $this->messagingService->send($this->message);
        
        $this->assertTrue($result);
        $this->assertFileExists($filePath);
        
        $messageContent = file_get_contents($filePath);
        
        $this->assertContains($this->messageBody, $messageContent);
        
        tao_helpers_File::delTree($testfolder);
        $this->assertFalse(is_dir($testfolder));
    }
}
