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

namespace oat\tao\model\DataPolicyOrchestrator\Handler;

use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use oat\tao\model\DataPolicyOrchestrator\Model\FullDataRemovalConfirmationMessage;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Publisher\DataRemovalConfirmationPublisher;
use Psr\Log\LoggerInterface;
use Throwable;

class FullDataRemovalCheckHandlerProxy implements DataPolicyHandlerInterface
{
    /** @var array<string, DataPolicyHandlerInterface[]> */
    private array $handlers = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DataRemovalConfirmationPublisher $confirmationPublisher,
        private readonly string $fullDataRemovalConfirmationTopicName
    ) {
    }

    public function addHandler(string $policyId, DataPolicyHandlerInterface $handler): void
    {
        $this->handlers[$policyId] ??= [];
        $this->handlers[$policyId][] = $handler;
    }

    public function handle(DataPolicyMessageInterface $message): void
    {
        $policyHandlers = $this->handlers[$message->policyId] ?? [];

        if (empty($policyHandlers)) {
            throw new DataPolicyException(
                sprintf('No data policy handlers registered for the selected policy "%s"', $message->policyId)
            );
        }

        try {
            foreach ($policyHandlers as $policyHandler) {
                $policyHandler->handle($message);
            }

            $this->confirmationPublisher->publishPayload(
                $this->fullDataRemovalConfirmationTopicName,
                new FullDataRemovalConfirmationMessage($message->jsonSerialize())
            );

            $this->logger->info('[Data policy - full data removal] All data was successfully removed');
        } catch (Throwable) {
            $this->logger->error('[Data policy - full data removal] Some data was not deleted');
        }
    }
}
