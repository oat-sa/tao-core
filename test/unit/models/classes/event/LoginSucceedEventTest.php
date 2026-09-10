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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\event;

use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\session\source\SessionSource;
use PHPUnit\Framework\TestCase;

class LoginSucceedEventTest extends TestCase
{
    public function testJsonSerializeUsesInternalBackofficeSourceByDefault(): void
    {
        $event = new LoginSucceedEvent('john.doe');

        $this->assertSame(
            [
                'login' => 'john.doe',
                'source' => SessionSource::INTERNAL_BACKOFFICE->value,
            ],
            $event->jsonSerialize()
        );
    }

    public function testJsonSerializeUsesProvidedSource(): void
    {
        $event = new LoginSucceedEvent('john.doe', SessionSource::EXTERNAL_PORTAL->value);

        $this->assertSame(
            [
                'login' => 'john.doe',
                'source' => SessionSource::EXTERNAL_PORTAL->value,
            ],
            $event->jsonSerialize()
        );
    }

    public function testGetNameAndGetTime(): void
    {
        $event = new LoginSucceedEvent('john.doe');

        $this->assertSame(LoginSucceedEvent::class, $event->getName());
        $this->assertIsInt($event->getTime());
        $this->assertGreaterThan(0, $event->getTime());
    }
}
