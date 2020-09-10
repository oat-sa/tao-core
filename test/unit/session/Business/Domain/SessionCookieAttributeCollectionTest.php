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

use oat\generis\test\TestCase;
use oat\tao\model\session\Business\Domain\SessionCookieAttribute;
use oat\tao\model\session\Business\Domain\SessionCookieAttributeCollection;
use oat\tao\model\session\Business\Domain\SessionCookiePathAttribute;

/**
 * @covers \oat\tao\model\session\Business\Domain\SessionCookieAttributeCollection
 */
class SessionCookieAttributeCollectionTest extends TestCase
{
    /**
     * @param string                           $expected
     * @param SessionCookieAttributeCollection $sut
     *
     * @dataProvider dataProvider
     */
    public function testToString(string $expected, SessionCookieAttributeCollection $sut): void
    {
        static::assertEquals($expected, $sut);
    }

    public function dataProvider(): array
    {
        return [
            'Single attribute'    => [
                'expected' => 'test-name=test-value',
                'sut'      => (new SessionCookieAttributeCollection())
                    ->add(new SessionCookieAttribute('test-name', 'test-value')),
            ],
            'Multiple attributes' => [
                'expected' => '/; test-name-0=test-value-0; test-name-1=test-value-1',
                'sut'      => (new SessionCookieAttributeCollection())
                    ->add(new SessionCookiePathAttribute('/'))
                    ->add(new SessionCookieAttribute('test-name-0', 'test-value-0'))
                    ->add(new SessionCookieAttribute('test-name-1', 'test-value-1')),
            ],
        ];
    }
}
