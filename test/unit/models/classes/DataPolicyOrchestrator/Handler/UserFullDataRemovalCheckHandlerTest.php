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
use oat\tao\model\DataPolicyOrchestrator\Handler\UserFullDataRemovalCheckHandler;
use oat\tao\model\DataPolicyOrchestrator\Model\FullDataRemovalCheckMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserFullDataRemovalCheckHandlerTest extends TestCase
{
    private Ontology|MockObject $ontology;
    private core_kernel_classes_Class|MockObject $userClass;
    private UserFullDataRemovalCheckHandler $subject;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->userClass = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->method('getClass')
            ->willReturn($this->userClass);

        $this->subject = new UserFullDataRemovalCheckHandler($this->ontology);
    }

    public function testHandleDoesNotThrowWhenUserNotFound(): void
    {
        $this->userClass
            ->expects($this->once())
            ->method('searchInstances')
            ->willReturn([]);

        $this->subject->handle($this->createMessage());

        $this->addToAssertionCount(1);
    }

    public function testHandleThrowsExceptionWhenUserStillExists(): void
    {
        $user = $this->createMock(core_kernel_classes_Resource::class);

        $this->userClass
            ->expects($this->once())
            ->method('searchInstances')
            ->willReturn([$user]);

        $this->expectException(DataPolicyException::class);
        $this->expectExceptionMessage('[Data policy - full data removal] User "john.doe" still exists');

        $this->subject->handle($this->createMessage());
    }

    private function createMessage(array $overrides = []): FullDataRemovalCheckMessage
    {
        return new FullDataRemovalCheckMessage(array_merge([
            'dataSubjectRawId' => 'john.doe',
            'ownerApp' => 'authoring',
            'tenantId' => 'tenant-1',
            'policyId' => 'policy-1',
            'policyVersion' => '1',
        ], $overrides));
    }
}
