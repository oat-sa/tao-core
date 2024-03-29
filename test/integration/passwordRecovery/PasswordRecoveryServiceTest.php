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
 */

use oat\generis\model\GenerisRdf;
use oat\tao\model\messaging\Message;
use oat\tao\model\messaging\MessagingService;
use oat\tao\model\user\TaoRoles;
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\tao\model\passwordRecovery\PasswordRecoveryService;
use Prophecy\Prediction\CallTimesPrediction;
use Prophecy\Argument;

/**
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package tao
 */
class PasswordRecoveryServiceTest extends TaoPhpUnitTestRunner
{
    /**
     * @var core_kernel_classes_Resource
     */
    protected $testUser = null;

    public function setUp(): void
    {
        TaoPhpUnitTestRunner::initTest();
        $this->testUser = $this->createUser();
    }

    public function tearDown(): void
    {
        if (!is_null($this->testUser)) {
            $this->testUser->delete();
        }
    }

    /**
     * @param MessagingService $messagingService
     * @return PasswordRecoveryService
     */
    protected function getPasswordRecoveryService($messagingService)
    {
        $passwordRecoveryService = PasswordRecoveryService::singleton();
        $refObject = new ReflectionObject($passwordRecoveryService);
        $refProperty = $refObject->getProperty('messagingSerivce');
        $refProperty->setAccessible(true);
        $refProperty->setValue($passwordRecoveryService, $messagingService);

        return $passwordRecoveryService;
    }

    protected function createUser()
    {
        $class = new core_kernel_classes_Class(GenerisRdf::CLASS_GENERIS_USER);

        return $class->createInstanceWithProperties([
            GenerisRdf::PROPERTY_USER_LOGIN => 'john.doe',
            GenerisRdf::PROPERTY_USER_PASSWORD => core_kernel_users_Service::getPasswordHash()->encrypt('secure'),
            GenerisRdf::PROPERTY_USER_LASTNAME => 'Doe',
            GenerisRdf::PROPERTY_USER_FIRSTNAME => 'John',
            GenerisRdf::PROPERTY_USER_MAIL => 'jonhdoe@tao.lu',
            GenerisRdf::PROPERTY_USER_UILG => 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
            GenerisRdf::PROPERTY_USER_ROLES => TaoRoles::BACK_OFFICE
        ]);
    }

    public function testSendMail()
    {
        $messagingProphecy = $this->prophesize('oat\tao\model\messaging\MessagingService');
        $messagingProphecy->isAvailable()->willReturn(true);
        $messagingProphecy->isAvailable()->should(new CallTimesPrediction(1));
        $user = $this->testUser;
        $messagingProphecy->send(Argument::type(Message::class))->will(function ($args) use ($user) {
            $message = $args[0];
            $tokenProperty = new core_kernel_classes_Property(
                PasswordRecoveryService::PROPERTY_PASSWORD_RECOVERY_TOKEN
            );
            $token = (string) $user->getOnePropertyValue($tokenProperty);
            if (is_null($token) || strpos($message->getBody(), $token) == false) {
                throw new Exception('Token not found in body');
            }
            return true;
        });

        $messagingProphecy->send(Argument::type(Message::class))->should(new CallTimesPrediction(1));

        $passwordRecoveryService = $this->getPasswordRecoveryService($messagingProphecy->reveal());

        $generisUser = new core_kernel_users_GenerisUser($this->testUser);
        $this->assertEmpty($generisUser->getPropertyValues(PasswordRecoveryService::PROPERTY_PASSWORD_RECOVERY_TOKEN));
        $generisUser->refresh();

        $this->assertTrue($passwordRecoveryService->sendMail($this->testUser));

        $passwordRecoveryToken = current(
            $generisUser->getPropertyValues(PasswordRecoveryService::PROPERTY_PASSWORD_RECOVERY_TOKEN)
        );

        $this->assertNotEmpty($passwordRecoveryToken);

        $messagingProphecy->checkProphecyMethodsPredictions();
    }
}
