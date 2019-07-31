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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\webhooks;

use oat\generis\test\TestCase;
use oat\oatbox\event\Event;
use oat\oatbox\event\EventManager;
use oat\tao\model\webhooks\EventWebhooksService;
use oat\tao\model\webhooks\EventWebhookConfigRepositoryInterface;
use oat\tao\model\webhooks\WebhookSerializableEventInterface;
use oat\tao\model\webhooks\WebhookTaskMetadata;
use oat\tao\model\webhooks\WebhookTaskServiceInterface;
use Psr\Log\LoggerInterface;

class EventWebhooksServiceTest extends TestCase
{
    /** @var EventManager|\PHPUnit_Framework_MockObject_MockObject */
    private $eventManagerMock;

    /** @var EventWebhookConfigRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $whConfigRepositoryMock;

    /** @var WebhookTaskServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $whTaskServiceMock;

    /** @var string[] */
    private $whRepositoryData = [];

    protected function setUp()
    {
        $this->eventManagerMock = $this->createMock(EventManager::class);

        $this->whConfigRepositoryMock = $this->createMock(EventWebhookConfigRepositoryInterface::class);

        $this->whConfigRepositoryMock
            ->method('getWebhookConfigIds')
            ->willReturnCallback(
                function ($eventName) {
                    return isset($this->whRepositoryData[$eventName])
                        ? $this->whRepositoryData[$eventName]
                        : [];
                });

        $this->whTaskServiceMock = $this->createMock(WebhookTaskServiceInterface::class);
    }

    public function testRegisterEvent()
    {
        $eventName = 'TestEvent';
        $this->eventManagerMock->expects($this->once())
            ->method('attach')
            ->with($eventName, [EventWebhooksService::SERVICE_ID, 'handleEvent']);

        $service = new EventWebhooksService([
            EventWebhooksService::OPTION_SUPPORTED_EVENTS => []
        ]);

        $this->assertFalse($service->isEventRegistered($eventName));
        $service->registerEvent($eventName, $this->eventManagerMock);
        $this->assertTrue($service->isEventRegistered($eventName));
    }

    public function testUnregisterEvent()
    {
        $eventName = 'TestEvent';
        $this->eventManagerMock->expects($this->once())
            ->method('detach')
            ->with($eventName, [EventWebhooksService::SERVICE_ID, 'handleEvent']);

        $service = new EventWebhooksService([
            EventWebhooksService::OPTION_SUPPORTED_EVENTS => [
                $eventName => true
            ]
        ]);

        $this->assertTrue($service->isEventRegistered($eventName));
        $service->unregisterEvent($eventName, $this->eventManagerMock);
        $this->assertFalse($service->isEventRegistered($eventName));
    }

    public function testHandleEventPositive() {
        $eventName = 'TestEvent';

        /** @var WebhookSerializableEventInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->createMock(WebhookSerializableEventInterface::class);
        $event->method('getName')->willReturn($eventName);
        $event->method('serializeForWebhook')->willReturn(['d' => 2]);

        $this->whRepositoryData = [
            $eventName => ['wh1', 'wh2']
        ];

        $service = new EventWebhooksService([
            EventWebhooksService::OPTION_SUPPORTED_EVENTS => [
                $eventName => true
            ]
        ]);

        $service->setServiceLocator($this->getServiceLocatorMock([
            EventWebhookConfigRepositoryInterface::SERVICE_ID => $this->whConfigRepositoryMock,
            WebhookTaskServiceInterface::SERVICE_ID => $this->whTaskServiceMock
        ]));

        $prepareCheckMetadataCallback = function ($whId) use ($eventName) {
            return $this->callback(static function (WebhookTaskMetadata $whMetadata) use ($eventName, $whId) {
                return $whMetadata[WebhookTaskMetadata::EVENT_NAME] === $eventName &&
                    $whMetadata[WebhookTaskMetadata::WEBHOOK_CONFIG_ID] === $whId &&
                    $whMetadata[WebhookTaskMetadata::EVENT_DATA] === ['d' => 2];
            });
        };

        $this->whTaskServiceMock
            ->expects($this->exactly(2))
            ->method('createTask')
            ->withConsecutive(
                $prepareCheckMetadataCallback('wh1'),
                $prepareCheckMetadataCallback('wh2')
                );

        $service->handleEvent($event);
    }

    public function testHandleEventNotSupportedEvent() {
        /** @var WebhookSerializableEventInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->createMock(WebhookSerializableEventInterface::class);
        $event->method('getName')->willReturn('TestEvent');
        $event->expects($this->never())->method('serializeForWebhook');

        $service = new EventWebhooksService([
            EventWebhooksService::OPTION_SUPPORTED_EVENTS => [
                'AnotherEvent' => true
            ]
        ]);

        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeast(1))->method('error');
        $service->setLogger($logger);

        $service->handleEvent($event);
    }

    public function testHandleEventNotSerializable() {
        /** @var Event|\PHPUnit_Framework_MockObject_MockObject $event */
        /** @noinspection PhpParamsInspection */
        $event = $this->createMock(Event::class);
        $event->method('getName')->willReturn('TestEvent');
        $event->expects($this->never())->method('serializeForWebhook');

        $service = new EventWebhooksService([
            EventWebhooksService::OPTION_SUPPORTED_EVENTS => [
                'TestEvent' => true
            ]
        ]);

        $service->setServiceLocator($this->getServiceLocatorMock([
            WebhookTaskServiceInterface::SERVICE_ID => $this->whTaskServiceMock
        ]));

        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeast(1))->method('error');
        $service->setLogger($logger);

        $this->whConfigRepositoryMock->expects($this->never())->method('getWebhookConfigIds');

        $service->handleEvent($event);
    }

    public function testHandleEventNoWebhooks() {
        $eventName = 'TestEvent';

        /** @var WebhookSerializableEventInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->createMock(WebhookSerializableEventInterface::class);
        $event->method('getName')->willReturn($eventName);
        $event->expects($this->never())->method('serializeForWebhook');

        $this->whRepositoryData = [
            $eventName => []
        ];

        $service = new EventWebhooksService([
            EventWebhooksService::OPTION_SUPPORTED_EVENTS => [
                $eventName => true
            ]
        ]);

        $service->setServiceLocator($this->getServiceLocatorMock([
            EventWebhookConfigRepositoryInterface::SERVICE_ID => $this->whConfigRepositoryMock
        ]));

        $service->handleEvent($event);
    }
}
