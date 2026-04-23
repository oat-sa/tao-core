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

use oat\tao\model\DataPolicyOrchestrator\Config\ConfirmationStatus;
use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessage;
use oat\tao\model\DataPolicyOrchestrator\PubSub\Publisher\DataRemovalConfirmationPublisher;
use Psr\Log\LoggerInterface;
use Throwable;

class DataRemovalHandlerProxy implements DataPolicyHandlerInterface
{
    /** @var array<string, DataPolicyHandlerInterface[]> */
    private array $handlers = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DataRemovalConfirmationPublisher $confirmationPublisher,
        private readonly string $dataRemovalConfirmationTopicName
    ) {
    }

    public function addHandler(string $policyId, DataPolicyHandlerInterface $handler): void
    {
        $this->handlers[$policyId] ??= [];
        $this->handlers[$policyId][] = $handler;
    }

    public function handle(DataPolicyMessage $message): void
    {
        $policyHandlers = $this->handlers[$message->policyId] ?? [];

        if (empty($policyHandlers)) {
            throw new DataPolicyException(
                sprintf('No data policy handlers registered for the selected policy "%s"', $message->policyId)
            );
        }

        $errors = [];

        foreach ($policyHandlers as $policyHandler) {
            try {
                $policyHandler->handle($message);
            } catch (Throwable $e) {
                $this->logger->error(sprintf('[Data policy - removal] %s', $e->getMessage()));
                $errors[] = $e;
            }
        }

        $this->confirmationPublisher->publishPayload(
            $this->dataRemovalConfirmationTopicName,
            $message->toMessage([
                'status' => ConfirmationStatus::byErrors($errors),
                'errors' => $errors,
            ])
        );
    }
}
