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
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Service;

use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Service\RemoteSourceJsonPathParser;

/**
 * @property RemoteSourceJsonPathParser sut
 */
class RemoteSourceJsonPathParserTest extends TestCase
{
    protected function setUp(): void
    {
        $this->sut = new RemoteSourceJsonPathParser();
    }

    public function testIterate(): void
    {
        $json = [
            'phoneNumbers' =>
                [
                    [
                        'type'   => 'iPhone',
                        'number' => '0123-4567-8888',
                    ],
                    [
                        'type'   => 'home',
                        'number' => '0123-4567-8910',
                    ],
                ],
        ];

        $values = iterator_to_array(
            $this->sut->iterate($json, '$.phoneNumbers[:].type', '$.phoneNumbers[:].number')
        );

        $this->assertEquals(new Value(null, 'iPhone', '0123-4567-8888'), $values[0]);
        $this->assertEquals(new Value(null, 'home', '0123-4567-8910'), $values[1]);
    }
}
