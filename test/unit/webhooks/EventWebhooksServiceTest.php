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
use oat\tao\model\webhooks\WebhookConfigRepositoryInterface;
use oat\tao\model\webhooks\WebhookSerializableInterface;
use oat\tao\model\webhooks\WebhookTaskMetadata;
use oat\tao\model\webhooks\WebhookTaskServiceInterface;
use Psr\Log\LoggerInterface;

class EventWebhooksServiceTest extends TestCase
{
    /** @var EventManager|\PHPUnit_Framework_MockObject_MockObject */
    private $eventManagerMock;

    /** @var WebhookConfigRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $whConfigRepositoryMock;

    /** @var WebhookTaskServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $whTaskServiceMock;

    /** @var string[] */
    private $whRepositoryData = [];

    protected function setUp()
    {
        $this->eventManagerMock = $this->createMock(EventManager::class);

        $this->whConfigRepositoryMock = $this->createMock(WebhookConfigRepositoryInterface::class);

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

        $service = $this->getService();
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

        $service = $this->getService([
            $eventName => true
        ]);
        $this->assertTrue($service->isEventRegistered($eventName));
        $service->unregisterEvent($eventName, $this->eventManagerMock);
        $this->assertFalse($service->isEventRegistered($eventName));
    }

    public function testHandleEventPositive() {
        $eventName = 'TestEvent';

        /** @var Event|WebhookSerializableInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        /** @noinspection PhpParamsInspection */
        $event = $this->createMock([Event::class, WebhookSerializableInterface::class]);
        $event->method('getName')->willReturn($eventName);
        $event->method('serializeForWebhook')->willReturn(['d' => 2]);

        $this->whRepositoryData = [
            $eventName => ['wh1', 'wh2']
        ];

        $prepareCheckMetadataCallback = function ($whId) use ($eventName) {
            return $this->callback(static function (WebhookTaskMetadata $whMetadata) use ($eventName, $whId) {
                return $whMetadata[WebhookTaskMetadata::EVENT_NAME] === $eventName &&
                    $whMetadata[WebhookTaskMetadata::WEBHOOK_CONFIG_ID] === $whId &&
                    $whMetadata[WebhookTaskMetadata::EVENT_DATA] === ['d' => 2];
                });
        };

        $service = $this->getService([
            $eventName => true
        ]);
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
        /** @var Event|WebhookSerializableInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        /** @noinspection PhpParamsInspection */
        $event = $this->createMock([Event::class, WebhookSerializableInterface::class]);
        $event->method('getName')->willReturn('TestEvent');
        $event->expects($this->never())->method('serializeForWebhook');

        $service = $this->getService([
            'AnotherEvent' => true
        ]);
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeast(1))->method('error');
        $service->setLogger($logger);

        $this->whConfigRepositoryMock->expects($this->never())->method('getWebhookConfigIds');
        $this->whTaskServiceMock->expects($this->never())->method('createTask');

        $service->handleEvent($event);
    }

    public function testHandleEventNotSerializable() {
        /** @var Event|\PHPUnit_Framework_MockObject_MockObject $event */
        /** @noinspection PhpParamsInspection */
        $event = $this->createMock(Event::class);
        $event->method('getName')->willReturn('TestEvent');
        $event->expects($this->never())->method('serializeForWebhook');

        $service = $this->getService([
            'TestEvent' => true
        ]);
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeast(1))->method('error');
        $service->setLogger($logger);

        $this->whConfigRepositoryMock->expects($this->never())->method('getWebhookConfigIds');
        $this->whTaskServiceMock->expects($this->never())->method('createTask');

        $service->handleEvent($event);
    }

    public function testHandleEventNoWebhooks() {
        $eventName = 'TestEvent';

        /** @var Event|WebhookSerializableInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        /** @noinspection PhpParamsInspection */
        $event = $this->createMock([Event::class, WebhookSerializableInterface::class]);
        $event->method('getName')->willReturn($eventName);
        $event->expects($this->never())->method('serializeForWebhook');

        $this->whRepositoryData = [
            $eventName => []
        ];

        $service = $this->getService([
            $eventName => true
        ]);

        $this->whTaskServiceMock
            ->expects($this->never())
            ->method('createTask');

        $service->handleEvent($event);
    }

    /**
     * @param string[] $supportedEvents
     * @return EventWebhooksService
     */
    protected function getService($supportedEvents = [])
    {
        $service = new EventWebhooksService([
            EventWebhooksService::OPTION_SUPPORTED_EVENTS => $supportedEvents
        ]);

        $serviceLocator = $this->getServiceLocatorMock([
            WebhookConfigRepositoryInterface::SERVICE_ID => $this->whConfigRepositoryMock,
            WebhookTaskServiceInterface::SERVICE_ID => $this->whTaskServiceMock
        ]);

        $service->setServiceLocator($serviceLocator);

        return $service;
    }
}
