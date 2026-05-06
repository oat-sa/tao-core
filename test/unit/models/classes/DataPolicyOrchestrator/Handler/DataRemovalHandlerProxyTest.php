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
use oat\tao\model\DataPolicyOrchestrator\Handler\DataRemovalHandlerProxy;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use oat\tao\model\DataPolicyOrchestrator\Model\DataRemovalConfirmationMessage;
use oat\tao\model\DataPolicyOrchestrator\Model\DataRemovalMessage;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Publisher\DataRemovalConfirmationPublisher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class DataRemovalHandlerProxyTest extends TestCase
{
    private LoggerInterface|MockObject $logger;
    private DataRemovalConfirmationPublisher|MockObject $confirmationPublisher;
    private DataRemovalHandlerProxy $subject;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->confirmationPublisher = $this->createMock(DataRemovalConfirmationPublisher::class);
        $this->subject = new DataRemovalHandlerProxy(
            $this->logger,
            $this->confirmationPublisher,
            'confirmation-topic'
        );
    }

    public function testHandleThrowsExceptionWhenNoPolicyHandlersRegistered(): void
    {
        $this->expectException(DataPolicyException::class);
        $this->expectExceptionMessage('No data policy handlers registered for the selected policy "policy-1"');

        $this->confirmationPublisher
            ->expects($this->never())
            ->method('publishPayload');

        $this->subject->handle($this->createDataRemovalMessage(['policyId' => 'policy-1']));
    }

    public function testHandleAggregatesErrorsAndPublishesConfirmation(): void
    {
        $failingHandler = $this->createMock(DataPolicyHandlerInterface::class);
        $failingHandler
            ->expects($this->once())
            ->method('handle')
            ->willThrowException(new RuntimeException('first handler failed'));

        $successfulHandler = $this->createMock(DataPolicyHandlerInterface::class);
        $successfulHandler
            ->expects($this->once())
            ->method('handle');

        $this->subject->addHandler('policy-1', $failingHandler);
        $this->subject->addHandler('policy-1', $successfulHandler);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('first handler failed'));

        $this->confirmationPublisher
            ->expects($this->once())
            ->method('publishPayload')
            ->with(
                'confirmation-topic',
                $this->callback(function (DataPolicyMessageInterface $message): bool {
                    if (!$message instanceof DataRemovalConfirmationMessage) {
                        return false;
                    }

                    return $message->policyId === 'policy-1'
                        && $message->errors === ['first handler failed']
                        && $message->status === 'failed';
                })
            );

        $this->subject->handle($this->createDataRemovalMessage(['policyId' => 'policy-1']));
    }

    public function testHandleRunsAllHandlersAndPublishesSuccessfulConfirmation(): void
    {
        $firstHandler = $this->createMock(DataPolicyHandlerInterface::class);
        $firstHandler
            ->expects($this->once())
            ->method('handle');

        $secondHandler = $this->createMock(DataPolicyHandlerInterface::class);
        $secondHandler
            ->expects($this->once())
            ->method('handle');

        $this->subject->addHandler('policy-1', $firstHandler);
        $this->subject->addHandler('policy-1', $secondHandler);

        $this->logger
            ->expects($this->never())
            ->method('error');

        $this->confirmationPublisher
            ->expects($this->once())
            ->method('publishPayload')
            ->with(
                'confirmation-topic',
                $this->callback(function (DataPolicyMessageInterface $message): bool {
                    if (!$message instanceof DataRemovalConfirmationMessage) {
                        return false;
                    }

                    return $message->policyId === 'policy-1'
                        && $message->errors === []
                        && $message->status === 'removed';
                })
            );

        $this->subject->handle($this->createDataRemovalMessage(['policyId' => 'policy-1']));
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
