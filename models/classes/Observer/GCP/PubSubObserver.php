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

namespace oat\tao\model\Observer\GCP;

use ErrorException;
use Google\Cloud\PubSub\PubSubClient;
use Psr\Log\LoggerInterface;
use SplObserver;
use SplSubject;

class PubSubObserver implements SplObserver
{
    public const CONFIG_TOPIC = 'config_topic';

    /** @var string[] */
    private $config;

    /** @var PubSubClient */
    private $pubSubClient;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PubSubClient $pubSubClient, LoggerInterface $logger, array $config = [])
    {
        $this->config = $config;
        $this->pubSubClient = $pubSubClient;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function update(SplSubject $subject)
    {
        if (!isset($this->config[self::CONFIG_TOPIC])) {
            throw new ErrorException(sprintf('Config "%s" is missing', self::CONFIG_TOPIC));
        }

        $messageIds = $this->pubSubClient
            ->topic($this->config[self::CONFIG_TOPIC])
            ->publish(
                [
                    'data' => json_encode($subject),
                ]
            );

        $this->logger->info(
            sprintf(
                'Pub/Sub observer updated for topic "%s", subject "%s", data "%s", messages "%s"',
                $this->config[self::CONFIG_TOPIC],
                get_class($subject),
                substr((string)json_encode($subject), 0, 250) . '...',
                var_export($messageIds, true)
            )
        );
    }
}
