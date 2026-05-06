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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\Handler;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Handler\UserDataRemovalHandler;
use oat\tao\model\DataPolicyOrchestrator\Model\DataRemovalMessage;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use tao_models_classes_UserService;
use oat\generis\model\user\UserRdf;

class UserDataRemovalHandlerTest extends TestCase
{
    private LoggerInterface|MockObject $logger;
    private Ontology|MockObject $ontology;
    private core_kernel_classes_Class|MockObject $userClass;
    private tao_models_classes_UserService|MockObject $userService;
    private UserDataRemovalHandler $subject;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->ontology = $this->createMock(Ontology::class);
        $this->userClass = $this->createMock(core_kernel_classes_Class::class);
        $this->userService = $this->createMock(tao_models_classes_UserService::class);

        $this->subject = new UserDataRemovalHandler(
            $this->logger,
            $this->ontology,
            $this->userService
        );
    }

    public function testHandleSkipsRemovalWhenUserNotFound(): void
    {
        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with(TaoOntology::CLASS_URI_TAO_USER)
            ->willReturn($this->userClass);

        $this->userClass
            ->expects($this->once())
            ->method('searchInstances')
            ->with(
                [UserRdf::PROPERTY_LOGIN => 'john.doe'],
                ['like' => false, 'recursive' => true]
            )
            ->willReturn([]);

        $this->userService
            ->expects($this->never())
            ->method('removeUser');

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('No user data found'));

        $this->subject->handle($this->createDataRemovalMessage());
    }

    public function testHandleRemovesUserAndLogsSuccess(): void
    {
        $user = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with(TaoOntology::CLASS_URI_TAO_USER)
            ->willReturn($this->userClass);

        $this->userClass
            ->expects($this->once())
            ->method('searchInstances')
            ->with(
                [UserRdf::PROPERTY_LOGIN => 'john.doe'],
                ['like' => false, 'recursive' => true]
            )
            ->willReturn([$user]);

        $this->userService
            ->expects($this->once())
            ->method('removeUser')
            ->with($user)
            ->willReturn(true);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('User data removal completed for login "john.doe": success.'));

        $this->subject->handle($this->createDataRemovalMessage());
    }

    public function testHandleThrowsExceptionWhenUserRemovalFails(): void
    {
        $user = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with(TaoOntology::CLASS_URI_TAO_USER)
            ->willReturn($this->userClass);

        $this->userClass
            ->expects($this->once())
            ->method('searchInstances')
            ->with(
                [UserRdf::PROPERTY_LOGIN => 'john.doe'],
                ['like' => false, 'recursive' => true]
            )
            ->willReturn([$user]);

        $this->userService
            ->expects($this->once())
            ->method('removeUser')
            ->with($user)
            ->willReturn(false);

        $this->expectException(DataPolicyException::class);
        $this->expectExceptionMessage('User data removal failed for login "john.doe".');

        $this->subject->handle($this->createDataRemovalMessage());
    }

    public function testHandleThrowsExceptionWhenMultipleUsersFound(): void
    {
        $firstUser = $this->createMock(core_kernel_classes_Resource::class);
        $secondUser = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with(TaoOntology::CLASS_URI_TAO_USER)
            ->willReturn($this->userClass);

        $this->userClass
            ->expects($this->once())
            ->method('searchInstances')
            ->with(
                [UserRdf::PROPERTY_LOGIN => 'john.doe'],
                ['like' => false, 'recursive' => true]
            )
            ->willReturn([$firstUser, $secondUser]);

        $this->userService
            ->expects($this->never())
            ->method('removeUser');

        $this->expectException(DataPolicyException::class);
        $this->expectExceptionMessage('More than one user was found for login "john.doe".');

        $this->subject->handle($this->createDataRemovalMessage());
    }

    private function createDataRemovalMessage(array $overrides = []): DataRemovalMessage
    {
        return new DataRemovalMessage(array_merge([
            'dataSubjectRawId' => 'john.doe',
            'ownerApp' => 'authoring',
            'policyId' => 'policy-1',
            'policyVersion' => '1',
            'tenantId' => 'tenant-1',
            'uniqueId' => 'uid-1',
            'name' => 'user',
            'storageType' => 'db',
            'metadata' => [],
        ], $overrides));
    }
}
