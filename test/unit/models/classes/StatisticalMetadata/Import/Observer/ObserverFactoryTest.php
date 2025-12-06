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

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\Import\Processor;

use Google\Cloud\PubSub\PubSubClient;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Observer\GCP\PubSubClientFactory;
use oat\tao\model\Observer\GCP\PubSubObserver;
use oat\tao\model\Observer\Log\LoggerObserver;
use oat\tao\model\StatisticalMetadata\Import\Observer\ObserverFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class ObserverFactoryTest extends TestCase
{
    /** @var ObserverFactory */
    private $sut;

    /** @var MockObject|LoggerInterface */
    private $logger;

    /** @var MockObject|PubSubClientFactory */
    private $pubSubClientFactory;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pubSubClientFactory = $this->createMock(PubSubClientFactory::class);

        $this->sut = new ObserverFactory(
            $this->pubSubClientFactory,
            $this->logger,
            [
                'DATA_STORE_STATISTIC_PUB_SUB_TOPIC' => 'test123'
            ]
        );
    }

    public function testCreate(): void
    {
        if (!class_exists(PubSubClient::class)) {
            $this->markTestSkipped();
        }

        $this->pubSubClientFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->createMock(PubSubClient::class));

        $this->assertInstanceOf(PubSubObserver::class, $this->sut->create());
    }

    public function testCreateWithoutPubSubTopic(): void
    {
        $sut = new ObserverFactory(
            $this->pubSubClientFactory,
            $this->logger,
            []
        );

        $this->assertInstanceOf(LoggerObserver::class, $sut->create());
    }
}
