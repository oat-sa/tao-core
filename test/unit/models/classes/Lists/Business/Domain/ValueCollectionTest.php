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

namespace oat\tao\test\unit\model\Lists\Business\Domain;

use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;

class ValueCollectionTest extends TestCase
{
    /**
     * @param Value[] $values
     *
     * @dataProvider dataProvider
     */
    public function testIteration(array $values): void
    {
        foreach (new ValueCollection(...$values) as $i => $value) {
            $this->assertSame($values[$i], $value);
        }
    }

    /**
     * @param Value[] $values
     *
     * @dataProvider dataProvider
     */
    public function testSerialization(array $values): void
    {
        $this->assertJsonStringEqualsJsonString(
            json_encode($values),
            json_encode(new ValueCollection(...$values))
        );
    }

    public function dataProvider(): array
    {
        return [
            [
                'values' => [
                    new Value('https://example.com#1', '1'),
                    new Value('https://example.com#2', '2'),
                ],
            ],
        ];
    }
}
