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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Translation\Event;

use JsonSerializable;
use oat\oatbox\event\Event;
use oat\tao\model\Translation\Event\TranslationActionEvent;
use PHPUnit\Framework\TestCase;

class TranslationActionEventTest extends TestCase
{
    public function testEvent(): void
    {
        $sut = new TranslationActionEvent(
            'someAction',
            'item',
            'originalResourceId',
            'translationResourceId',
            'locale'
        );

        $this->assertInstanceOf(Event::class, $sut);
        $this->assertInstanceOf(JsonSerializable::class, $sut);
        $this->assertEquals(
            [
                'action' => 'someAction',
                'type' => 'item',
                'originalResourceId' => 'originalResourceId',
                'translationResourceId' => 'translationResourceId',
                'locale' => 'locale',
                'data' => []
            ],
            $sut->jsonSerialize()
        );
    }
}
