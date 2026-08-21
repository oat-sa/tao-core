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

namespace oat\tao\model\FrontendAction\Service;

use InvalidArgumentException;
use oat\oatbox\event\Event;
use oat\oatbox\event\EventManager;

class FrontendActionEventLogger
{
    private array $actionEventMap = [];

    public function __construct(private readonly EventManager $eventManager)
    {
    }

    public function addActionEvent(string $action, string $eventClass): void
    {
        if (isset($this->actionEventMap[$action])) {
            throw new InvalidArgumentException(sprintf('An event already registered for action "%s"', $action));
        }

        if (!is_subclass_of($eventClass, Event::class)) {
            throw new InvalidArgumentException(
                sprintf('Event class "%s" must implement "%s"', $eventClass, Event::class)
            );
        }

        $this->actionEventMap[$action] = $eventClass;
    }

    public function logAction(string $action, mixed $data): void
    {
        $eventClass = $this->actionEventMap[$action]
            ?? throw new InvalidArgumentException(sprintf('An event for action "%s" does not exist', $action));

        $this->eventManager->trigger(new $eventClass($data));
    }
}
