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
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use oat\tao\model\Observer\GCP\PubSubObserver;
use oat\tao\model\Observer\Log\LoggerObserver;
use Psr\Log\LoggerInterface;
use SplObserver;

class ObserverFactory
{
    /** @var PubSubClientFactory */
    private $pubSubClientFactory;

    /** @var LoggerInterface */
    private $logger;

    /** @var array */
    private $environmentVars;

    public function __construct(
        PubSubClientFactory $pubSubClientFactory,
        LoggerInterface $logger,
        array $environmentVars = null
    ) {
        $this->logger = $logger;
        $this->environmentVars = $environmentVars ?? $_ENV;
        $this->pubSubClientFactory = $pubSubClientFactory;
    }

    public function create(array $config = []): SplObserver
    {
        if (class_exists(PubSubClient::class) && $this->getPubSubTopic()) {
            return new PubSubObserver(
                $this->pubSubClientFactory->create(),
                $this->logger,
                [
                    PubSubObserver::CONFIG_TOPIC => $this->getPubSubTopic(),
                ]
            );
        }

        return new LoggerObserver($this->logger);
    }

    private function getPubSubTopic(): ?string
    {
        return $this->environmentVars['DATA_STORE_STATISTIC_PUB_SUB_TOPIC'] ?? null;
    }
}
