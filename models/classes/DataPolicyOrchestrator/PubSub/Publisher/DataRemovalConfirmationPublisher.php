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

namespace oat\tao\model\DataPolicyOrchestrator\PubSub\Publisher;

use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use Psr\Log\LoggerInterface;
use Throwable;

class DataRemovalConfirmationPublisher
{
    public function __construct(
        private readonly PubSubClientFactory $pubSubClientFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function publishPayload(string $topicName, DataPolicyMessageInterface $message): void
    {
        if (empty($topicName)) {
            $this->logger->warning('Data policy topic is empty, skipping publish.');

            return;
        }

        try {
            $this->pubSubClientFactory
                ->create()
                ->topic($topicName)
                ->publish(
                    [
                        'data' => json_encode([
                            'header' => ['type' => $topicName],
                            'body' => json_encode($message),
                        ]),
                    ]
                );
        } catch (Throwable $exception) {
            $errorMessage = sprintf(
                'Failed to publish confirmation to topic "%s": %s',
                $topicName,
                $exception->getMessage()
            );
            $this->logger->error($errorMessage);
            throw new DataPolicyException($errorMessage, 400, $exception);
        }
    }
}
