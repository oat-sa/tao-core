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
use oat\tao\model\webhooks\WebhookEventsService;
use oat\tao\model\webhooks\WebhookRegistryInterface;
use oat\tao\model\webhooks\WebhookSerializableEventInterface;
use oat\tao\model\webhooks\task\WebhookTaskParams;
use oat\tao\model\webhooks\WebhookTaskServiceInterface;
use Psr\Log\LoggerInterface;

class WebhookEventsServiceTest extends TestCase
{
    /** @var EventManager|\PHPUnit_Framework_MockObject_MockObject */
    private $eventManagerMock;

    /** @var WebhookRegistryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $whConfigRegistryMock;

    /** @var WebhookTaskServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $whTaskServiceMock;

    /** @var string[] */
    private $whRegistryData = [];

    protected function setUp()
    {
        $this->eventManagerMock = $this->createMock(EventManager::class);

        $this->whConfigRegistryMock = $this->createMock(WebhookRegistryInterface::class);

        $this->whConfigRegistryMock
            ->method('getWebhookConfigIds')
            ->willReturnCallback(
                function ($eventName) {
                    return isset($this->whRegistryData[$eventName])
                        ? $this->whRegistryData[$eventName]
                        : [];
                });

        $this->whTaskServiceMock = $this->createMock(WebhookTaskServiceInterface::class);
    }

    public function testRegisterEvent()
    {
        $eventName = 'TestEvent';
        $this->eventManagerMock->expects($this->once())
            ->method('attach')
            ->with($eventName, [WebhookEventsService::SERVICE_ID, 'handleEvent']);

        $service = new WebhookEventsService([
            WebhookEventsService::OPTION_SUPPORTED_EVENTS => []
        ]);

        $service->setServiceLocator($this->getServiceLocatorMock([
            EventManager::SERVICE_ID => $this->eventManagerMock
        ]));

        $this->assertFalse($service->isEventRegistered($eventName));
        $service->registerEvent($eventName);
        $this->assertTrue($service->isEventRegistered($eventName));
    }

    public function testUnregisterEvent()
    {
        $eventName = 'TestEvent';
        $this->eventManagerMock->expects($this->once())
            ->method('detach')
            ->with($eventName, [WebhookEventsService::SERVICE_ID, 'handleEvent']);

        $service = new WebhookEventsService([
            WebhookEventsService::OPTION_SUPPORTED_EVENTS => [
                $eventName => true
            ]
        ]);

        $service->setServiceLocator($this->getServiceLocatorMock([
            EventManager::SERVICE_ID => $this->eventManagerMock
        ]));

        $this->assertTrue($service->isEventRegistered($eventName));
        $service->unregisterEvent($eventName);
        $this->assertFalse($service->isEventRegistered($eventName));
    }

    public function testHandleEventPositive() {
        $eventName = 'TestEvent';
        $whEventName = 'WhTestEvent';

        /** @var WebhookSerializableEventInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->createMock(WebhookSerializableEventInterface::class);
        $event->method('getName')->willReturn($eventName);
        $event->method('getWebhookEventName')->willReturn($whEventName);
        $event->method('serializeForWebhook')->willReturn(['d' => 2]);

        $this->whRegistryData = [
            $eventName => ['wh1', 'wh2']
        ];

        $service = new WebhookEventsService([
            WebhookEventsService::OPTION_SUPPORTED_EVENTS => [
                $eventName => true
            ]
        ]);

        $service->setServiceLocator($this->getServiceLocatorMock([
            WebhookRegistryInterface::SERVICE_ID => $this->whConfigRegistryMock,
            WebhookTaskServiceInterface::SERVICE_ID => $this->whTaskServiceMock
        ]));

        $passedParams = [];
        $this->whTaskServiceMock
            ->expects($this->exactly(2))
            ->method('createTask')
            ->willReturnCallback(static function(WebhookTaskParams $whParams) use (&$passedParams) {
                $passedParams[] = $whParams;
            });

        $timestampStart = time();
        $service->handleEvent($event);
        $timestampEnd = time();

        foreach (['wh1', 'wh2'] as $index => $whId) {
            $whParams = $passedParams[$index];
            $this->assertSame($whParams[WebhookTaskParams::EVENT_NAME], $whEventName);
            $this->assertSame($whParams[WebhookTaskParams::WEBHOOK_CONFIG_ID], $whId);
            $this->assertSame($whParams[WebhookTaskParams::EVENT_DATA], ['d' => 2]);
            $this->assertGreaterThanOrEqual($whParams[WebhookTaskParams::TRIGGERED_TIMESTAMP], $timestampStart);
            $this->assertLessThanOrEqual($whParams[WebhookTaskParams::TRIGGERED_TIMESTAMP], $timestampEnd);
            $this->assertRegExp('/^([a-z0-9]{32})$/', $whParams[WebhookTaskParams::EVENT_ID]);
        }
    }

    public function testHandleEventNotSupportedEvent() {
        /** @var WebhookSerializableEventInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->createMock(WebhookSerializableEventInterface::class);
        $event->method('getName')->willReturn('TestEvent');
        $event->method('getWebhookEventName')->willReturn('WhTestEvent');
        $event->expects($this->never())->method('serializeForWebhook');

        $service = new WebhookEventsService([
            WebhookEventsService::OPTION_SUPPORTED_EVENTS => [
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
        $event->method('getWebhookEventName')->willReturn('WhTestEvent');
        $event->expects($this->never())->method('serializeForWebhook');

        $service = new WebhookEventsService([
            WebhookEventsService::OPTION_SUPPORTED_EVENTS => [
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

        $this->whConfigRegistryMock->expects($this->never())->method('getWebhookConfigIds');

        $service->handleEvent($event);
    }

    public function testHandleEventNoWebhooks() {
        $eventName = 'TestEvent';

        /** @var WebhookSerializableEventInterface|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->createMock(WebhookSerializableEventInterface::class);
        $event->method('getName')->willReturn($eventName);
        $event->expects($this->never())->method('serializeForWebhook');

        $this->whRegistryData = [
            $eventName => []
        ];

        $service = new WebhookEventsService([
            WebhookEventsService::OPTION_SUPPORTED_EVENTS => [
                $eventName => true
            ]
        ]);

        $service->setServiceLocator($this->getServiceLocatorMock([
            WebhookRegistryInterface::SERVICE_ID => $this->whConfigRegistryMock
        ]));

        $service->handleEvent($event);
    }
}
