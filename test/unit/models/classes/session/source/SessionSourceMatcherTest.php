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

namespace oat\tao\test\unit\models\classes\session\source;

use common_session_Session;
use InvalidArgumentException;
use oat\tao\model\session\source\SessionSourceMatcher;
use oat\tao\model\session\source\SessionSourceMatcherInterface;
use PHPUnit\Framework\TestCase;

class SessionSourceMatcherTest extends TestCase
{
    public function testReturnsFalseWhenNoMatchersRegisteredForSource(): void
    {
        $service = new SessionSourceMatcher();

        $this->assertFalse(
            $service->matchesSource('portal', $this->createMock(common_session_Session::class))
        );
    }

    public function testMatchesSourceWhenMatcherReturnsTrue(): void
    {
        $session = $this->createMock(common_session_Session::class);
        $matcher = $this->createMock(SessionSourceMatcherInterface::class);
        $matcher
            ->expects($this->once())
            ->method('matchesSource')
            ->with($session)
            ->willReturn(true);

        $service = new SessionSourceMatcher();
        $service->addSessionSourceMatcher('portal', $matcher);

        $this->assertTrue($service->matchesSource('portal', $session));
    }

    public function testSourceComparisonIsCaseInsensitive(): void
    {
        $session = $this->createMock(common_session_Session::class);
        $matcher = $this->createMock(SessionSourceMatcherInterface::class);
        $matcher->method('matchesSource')->willReturn(true);

        $service = new SessionSourceMatcher();
        $service->addSessionSourceMatcher('PoRtAl', $matcher);

        $this->assertTrue($service->matchesSource('PORTAL', $session));
    }

    public function testThrowsExceptionWhenSameMatcherClassRegisteredTwiceForSource(): void
    {
        $matcher = $this->createMock(SessionSourceMatcherInterface::class);
        $matcher->method('matchesSource')->willReturn(true);

        $service = new SessionSourceMatcher();
        $service->addSessionSourceMatcher('portal', $matcher);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already registered for source "portal"');

        $service->addSessionSourceMatcher('PORTAL', $matcher);
    }

    public function testStopsCheckingMatchersAfterFirstPositiveMatch(): void
    {
        $session = $this->createMock(common_session_Session::class);

        $service = new SessionSourceMatcher();
        $service->addSessionSourceMatcher(
            'portal',
            new class () implements SessionSourceMatcherInterface {
                public function matchesSource(common_session_Session $session): bool
                {
                    return true;
                }
            }
        );
        $service->addSessionSourceMatcher(
            'portal',
            new class () implements SessionSourceMatcherInterface {
                public function matchesSource(common_session_Session $session): bool
                {
                    throw new \RuntimeException('This matcher should not be called');
                }
            }
        );

        $this->assertTrue($service->matchesSource('portal', $session));
    }
}
