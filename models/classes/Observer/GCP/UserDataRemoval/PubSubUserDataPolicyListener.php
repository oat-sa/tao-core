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

namespace oat\tao\model\Observer\GCP\UserDataRemoval;

use ErrorException;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use Psr\Log\LoggerInterface;
use Throwable;

class PubSubUserDataPolicyListener
{
    private const OWNER_APP = 'backoffice';
    private const ALLOWED_POLICY_IDS = [
        'remove-deactivated-administrative-profile',
        'remove-deactivated-administrative-user-peripheral-data',
    ];

    /** @var array<string, UserDataPolicyHandlerInterface> */
    private array $handlersBySubscription = [];

    public function __construct(
        private readonly PubSubClientFactory $pubSubClientFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function addHandler(string $subscriptionName, UserDataPolicyHandlerInterface $handler): self
    {
        if ($subscriptionName === '') {
            $this->logger->warning('Pub/Sub listener handler skipped: empty subscription name.');

            return $this;
        }

        $this->handlersBySubscription[$subscriptionName] = $handler;

        return $this;
    }

    public function run(int $maxMessages = 10, int $waitSeconds = 5, int $maxIterations = 0): void
    {
        if ($this->handlersBySubscription === []) {
            $this->logger->warning('Pub/Sub listener skipped: no subscription is configured.');

            return;
        }

        try {
            $pubSubClient = $this->pubSubClientFactory->create();
        } catch (ErrorException | Throwable $exception) {
            $this->logger->error('Pub/Sub listener failed to initialize client: ' . $exception->getMessage());

            return;
        }

        $iteration = 0;

        while ($maxIterations === 0 || $iteration < $maxIterations) {
            ++$iteration;

            foreach ($this->handlersBySubscription as $subscriptionName => $handler) {
                $this->consumeProcess(
                    $pubSubClient,
                    $subscriptionName,
                    $handler,
                    max(1, $maxMessages)
                );
            }

            if ($waitSeconds > 0) {
                sleep($waitSeconds);
            }
        }
    }

    private function consumeProcess(
        $pubSubClient,
        string $subscriptionName,
        UserDataPolicyHandlerInterface $handler,
        int $maxMessages
    ): void {
        $subscription = $pubSubClient->subscription($subscriptionName);
        $messages = $subscription->pull(
            [
                'maxMessages' => $maxMessages,
                'returnImmediately' => false,
            ]
        );

        foreach ($messages as $message) {
            try {
                $parsedMessage = UserDataPolicyMessage::fromPubSubPayload((string) $message->data());

                if ($parsedMessage === null) {
                    $this->logger->warning(sprintf('Pub/Sub message from "%s" has no dataSubjectRawId.', $subscriptionName));
                    $subscription->acknowledge($message);

                    continue;
                }

                if (!$this->isSupportedMessage($parsedMessage)) {
                    $this->logger->debug(
                        sprintf(
                            'Pub/Sub message from "%s" skipped due to unsupported ownerApp/policyId.',
                            $subscriptionName
                        ),
                        [
                            'ownerApp' => $parsedMessage->getOwnerApp(),
                            'policyId' => $parsedMessage->getPolicyId(),
                            'dataSubjectRawId' => $parsedMessage->getDataSubjectRawId(),
                        ]
                    );
                    $subscription->acknowledge($message);

                    continue;
                }

                $handler->handle($parsedMessage);
                $subscription->acknowledge($message);
            } catch (Throwable $exception) {
                $this->logger->error(
                    sprintf(
                        'Pub/Sub processing failed for subscription "%s": %s',
                        $subscriptionName,
                        $exception->getMessage()
                    )
                );
            }
        }
    }

    private function isSupportedMessage(UserDataPolicyMessage $message): bool
    {
        return $message->getOwnerApp() === self::OWNER_APP
            && in_array($message->getPolicyId(), self::ALLOWED_POLICY_IDS, true);
    }
}
