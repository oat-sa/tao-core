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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\session\Business\Domain;

use PHPUnit\Framework\TestCase;
use oat\tao\model\session\Business\Domain\SessionCookieAttribute;

/**
 * @covers \oat\tao\model\session\Business\Domain\SessionCookieAttribute
 */
class SessionCookieAttributeTest extends TestCase
{
    private const TEST_NAME  = 'test-name';
    private const TEST_VALUE = 'test-value';

    public function testToString(): void
    {
        static::assertEquals(
            sprintf('%s=%s', self::TEST_NAME, self::TEST_VALUE),
            new SessionCookieAttribute(self::TEST_NAME, self::TEST_VALUE)
        );
    }
}
