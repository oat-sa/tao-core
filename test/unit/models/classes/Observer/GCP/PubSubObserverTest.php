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
 * Copyright (c) 2022 Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Observer\GCP;

use ErrorException;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Topic;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Observer\GCP\PubSubObserver;
use oat\tao\model\Observer\SubjectInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class PubSubObserverTest extends TestCase
{
    /** @var PubSubObserver */
    private $subject;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var PubSubClient|MockObject */
    private $pubSubClient;

    /** @var Topic|MockObject */
    private $pubSubTopic;

    protected function setUp(): void
    {
        if (!class_exists(PubSubClient::class)) {
            return;
        }

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pubSubClient = $this->createMock(PubSubClient::class);
        $this->pubSubTopic = $this->createMock(Topic::class);
        $this->subject = new PubSubObserver(
            $this->pubSubClient,
            $this->logger,
            [
                PubSubObserver::CONFIG_TOPIC => 'my-topic',
            ]
        );
    }

    public function testUpdateSuccessfully(): void
    {
        if (!class_exists(PubSubClient::class)) {
            $this->markTestSkipped();
        }

        $subject = $this->createMock(SubjectInterface::class);

        $this->logger
            ->expects($this->once())
            ->method('info');

        $this->pubSubClient
            ->expects($this->once())
            ->method('topic')
            ->with('my-topic')
            ->willReturn($this->pubSubTopic);

        $this->pubSubTopic
            ->expects($this->once())
            ->method('publish')
            ->with(
                [
                    'data' => json_encode($subject),
                ]
            )
            ->willReturn(
                [
                    'messageIds' => [1, 2, 3]
                ]
            );

        $this->subject->update($subject);
    }

    public function testUpdateWithMissConfigurationThrowsException(): void
    {
        if (!class_exists(PubSubClient::class)) {
            $this->markTestSkipped();
        }

        $this->expectException(ErrorException::class);

        (new PubSubObserver($this->pubSubClient, $this->logger))->update($this->createMock(SubjectInterface::class));
    }
}
