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

use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Handler\DataPolicyHandlerInterface;
use oat\tao\model\DataPolicyOrchestrator\Handler\FullDataRemovalCheckHandlerProxy;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use oat\tao\model\DataPolicyOrchestrator\Model\FullDataRemovalCheckMessage;
use oat\tao\model\DataPolicyOrchestrator\Model\FullDataRemovalConfirmationMessage;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Publisher\DataRemovalConfirmationPublisher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class FullDataRemovalCheckHandlerProxyTest extends TestCase
{
    private LoggerInterface|MockObject $logger;
    private DataRemovalConfirmationPublisher|MockObject $confirmationPublisher;
    private FullDataRemovalCheckHandlerProxy $subject;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->confirmationPublisher = $this->createMock(DataRemovalConfirmationPublisher::class);
        $this->subject = new FullDataRemovalCheckHandlerProxy(
            $this->logger,
            $this->confirmationPublisher,
            'full-removal-confirmation-topic'
        );
    }

    public function testHandleThrowsExceptionWhenNoPolicyHandlersRegistered(): void
    {
        $this->expectException(DataPolicyException::class);
        $this->expectExceptionMessage('No data policy handlers registered for the selected policy "policy-1"');

        $this->subject->handle($this->createCheckMessage());
    }

    public function testHandlePublishesConfirmationAndLogsInfoOnSuccess(): void
    {
        $handler = $this->createMock(DataPolicyHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle');

        $this->subject->addHandler('policy-1', $handler);

        $this->confirmationPublisher
            ->expects($this->once())
            ->method('publishPayload')
            ->with(
                'full-removal-confirmation-topic',
                $this->callback(function (DataPolicyMessageInterface $message): bool {
                    return $message instanceof FullDataRemovalConfirmationMessage
                        && $message->policyId === 'policy-1';
                })
            );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('[Data policy - full data removal] All data was successfully removed');

        $this->logger
            ->expects($this->never())
            ->method('error');

        $this->subject->handle($this->createCheckMessage());
    }

    public function testHandleLogsErrorAndSkipsPublishWhenHandlerFails(): void
    {
        $handler = $this->createMock(DataPolicyHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willThrowException(new RuntimeException('full removal check failed'));

        $this->subject->addHandler('policy-1', $handler);

        $this->confirmationPublisher
            ->expects($this->never())
            ->method('publishPayload');

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('[Data policy - full data removal] Some data was not deleted');

        $this->subject->handle($this->createCheckMessage());
    }

    private function createCheckMessage(array $overrides = []): FullDataRemovalCheckMessage
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
