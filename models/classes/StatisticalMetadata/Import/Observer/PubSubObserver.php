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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\StatisticalMetadata\Import\Observer;

use Google\Cloud\PubSub\PubSubClient;
use Psr\Log\LoggerInterface;
use SplObserver;
use SplSubject;

class PubSubObserver implements SplObserver
{
    /** @var string */
    private $topic;

    /** @var PubSubClient */
    private $pubSubClient;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(string $topic, PubSubClient $pubSubClient, LoggerInterface $logger)
    {
        $this->topic = $topic;
        $this->pubSubClient = $pubSubClient;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function update(SplSubject $subject)
    {
        $messageIds = $this->pubSubClient->topic($this->topic)->publish(
            [
                'data' => json_encode($subject),
            ]
        );

        $this->logger->info(
            sprintf(
                'Pub/Sub messages "%s" send for Statistical data for "%s"',
                var_export($messageIds, true),
                get_class($subject)
            )
        );
    }
}
