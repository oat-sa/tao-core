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

namespace oat\tao\model\DataPolicyOrchestrator\PubSub\Listener;

use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Message;
use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Handler\DataPolicyHandlerInterface;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class AbstractDataPolicyListener
{
    private const OWNER_APP = 'authoring';

    public function __construct(
        private readonly PubSubClientFactory $pubSubClientFactory,
        private readonly DataPolicyHandlerInterface $dataPolicyHandlerProxy,
        private readonly LoggerInterface $logger,
        private readonly string $subscriptionName
    ) {
    }

    public function run(int $maxMessages = 10, int $waitSeconds = 5, int $maxIterations = 0): void
    {
        try {
            $pubSubClient = $this->pubSubClientFactory->create();
        } catch (Throwable $exception) {
            $errorMessage = 'Pub/Sub listener failed to initialize client: ' . $exception->getMessage();
            $this->logger->error($errorMessage);
            throw new DataPolicyException($errorMessage, 400);
        }

        $iteration = 0;

        while ($maxIterations === 0 || $iteration < $maxIterations) {
            ++$iteration;

            $this->consumeProcess($pubSubClient, max(1, $maxMessages));

            if ($waitSeconds > 0) {
                sleep($waitSeconds);
            }
        }
    }

    abstract protected function createDataPolicyMessage(array $body): ?DataPolicyMessageInterface;

    private function consumeProcess(PubSubClient $pubSubClient, int $maxMessages): void
    {
        /** @var Subscription $subscription */
        $subscription = $pubSubClient->subscription($this->subscriptionName);
        /** @var Message[] $messages */
        $messages = $subscription->pull([
            'maxMessages' => $maxMessages,
            'returnImmediately' => false,
        ]);

        foreach ($messages as $message) {
            try {
                $parsedMessage = $this->createDataPolicyMessage($this->parseMessageBody($message));

                if (self::OWNER_APP === $parsedMessage->ownerApp) {
                    $this->dataPolicyHandlerProxy->handle($parsedMessage);
                }

                $subscription->acknowledge($message);
            } catch (Throwable $exception) {
                $this->logger->error(
                    sprintf(
                        'Pub/Sub processing failed for subscription "%s": %s',
                        $this->subscriptionName,
                        $exception->getMessage()
                    )
                );
            }
        }
    }

    private function parseMessageBody(Message $message): ?array
    {
        $decodedPayload = json_decode($message->getData(), true);

        if (!is_array($decodedPayload)) {
            throw new DataPolicyException('Invalid message payload', 400);
        }

        $body = $decodedPayload['body'] ?? $decodedPayload;

        if (is_string($body)) {
            $body = json_decode($body, true);
        }

        if (!is_array($body)) {
            throw new DataPolicyException('Invalid message payload', 400);
        }

        return $body;
    }
}
