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

namespace oat\tao\test\unit\models\classes\Observer\Log;

use PHPUnit\Framework\TestCase;
use oat\tao\model\Observer\Subject;
use ReflectionProperty;
use SplObserver;

class SubjectTest extends TestCase
{
    /** @var Subject */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Subject(['data' => 'value']);
    }

    public function testNotify(): void
    {
        $observer = $this->createMock(SplObserver::class);

        $observer->expects($this->once())
            ->method('update')
            ->with($this->subject);

        $this->subject->attach($observer);

        $this->subject->notify();
    }

    public function testAttachAndDetach(): void
    {
        $observer1 = $this->createMock(SplObserver::class);
        $observer2 = $this->createMock(SplObserver::class);

        $this->subject->attach($observer1);
        $this->subject->attach($observer2);

        $property = new ReflectionProperty($this->subject, 'observers');
        $property->setAccessible(true);

        $observers = array_values($property->getValue($this->subject));

        $this->assertCount(2, $observers);
        $this->assertSame($observer1, $observers[0]);
        $this->assertSame($observer2, $observers[1]);

        $this->subject->detach($observer1);

        $observers = array_values($property->getValue($this->subject));

        $this->assertCount(1, $observers);
        $this->assertSame($observer2, $observers[0]);
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame('{"data":"value"}', json_encode($this->subject));
    }
}
