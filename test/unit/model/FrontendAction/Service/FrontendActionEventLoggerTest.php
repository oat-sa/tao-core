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

namespace oat\tao\test\unit\model\FrontendAction\Service;

use InvalidArgumentException;
use oat\oatbox\event\Event;
use oat\oatbox\event\EventManager;
use oat\tao\model\FrontendAction\Service\FrontendActionEventLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendActionEventLoggerTest extends TestCase
{
    private EventManager|MockObject $eventManager;
    private FrontendActionEventLogger $service;

    protected function setUp(): void
    {
        $this->eventManager = $this->createMock(EventManager::class);
        $this->service = new FrontendActionEventLogger($this->eventManager);
    }

    public function testLogActionTriggersMappedEvent(): void
    {
        $action = 'itemPrintAttempt';
        $payload = ['resourceUri' => 'http://example.com/item/1'];
        $eventClass = $this->createEventClass();

        $this->service->addActionEvent($action, $eventClass);

        $this->eventManager
            ->expects($this->once())
            ->method('trigger')
            ->with(
                $this->callback(
                    static function (Event $event) use ($eventClass, $payload): bool {
                        return is_a($event, $eventClass)
                            && $event->payload === $payload;
                    }
                )
            );

        $this->service->logAction($action, $payload);
    }

    public function testAddActionEventThrowsExceptionWhenActionAlreadyRegistered(): void
    {
        $action = 'itemPrintAttempt';
        $eventClass = $this->createEventClass();
        $this->service->addActionEvent($action, $eventClass);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An event already registered for action "itemPrintAttempt"');

        $this->service->addActionEvent($action, $eventClass);
    }

    public function testAddActionEventThrowsExceptionWhenClassIsNotEvent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Event class "%s" must implement "%s"',
                \stdClass::class,
                Event::class
            )
        );

        $this->service->addActionEvent('invalid', \stdClass::class);
    }

    public function testLogActionThrowsExceptionWhenActionIsNotRegistered(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An event for action "unknownAction" does not exist');

        $this->service->logAction('unknownAction', 'payload');
    }

    private function createEventClass(): string
    {
        return get_class(new class ('payload') implements Event {
            public function __construct(public readonly mixed $payload)
            {
            }

            public function getName(): string
            {
                return self::class;
            }
        });
    }
}
