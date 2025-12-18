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

use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;

class ValueCollectionTest extends TestCase
{
    /**
     * @param string $uri
     *
     * @dataProvider provideValues
     */
    public function testAccessors(string $uri): void
    {
        $this->assertSame($uri, (new ValueCollection($uri))->getUri());
    }

    /**
     * @param string  $uri
     * @param Value[] $values
     *
     * @dataProvider provideValues
     */
    public function testExtractValueByUri(string $uri, array $values): void
    {
        $sut = new ValueCollection($uri, ...$values);

        foreach ($this->getUniqueValues(...$values) as $value) {
            $this->assertSame($value, $sut->extractValueByUri($value->getUri()));
        }
    }

    /**
     * @param ValueCollection $expected
     * @param ValueCollection $sut
     * @param Value           $value
     *
     * @dataProvider provideValueCollections
     */
    public function testAddValue(ValueCollection $expected, ValueCollection $sut, Value $value): void
    {
        $this->assertEquals($expected, $sut->addValue($value));
    }

    /**
     * @param string  $uri
     * @param Value[] $values
     *
     * @dataProvider provideValues
     */
    public function testIteration(string $uri, array $values): void
    {
        $values = $this->getUniqueValues(...$values);

        foreach (new ValueCollection($uri, ...$values) as $i => $value) {
            $this->assertSame($values[$i], $value);
        }
    }

    /**
     * @param string  $uri
     * @param Value[] $values
     *
     * @dataProvider provideValues
     */
    public function testSerialization(string $uri, array $values): void
    {
        $this->assertJsonStringEqualsJsonString(
            json_encode($this->getUniqueValues(...$values)),
            json_encode(new ValueCollection($uri, ...$values))
        );
    }

    public function testHasDuplicates(): void
    {
        $value1 = new Value(null, 'http://example.com#1', '1');
        $value2 = new Value(null, 'http://example.com#2', '2');
        $value3 = new Value(null, '', 'value 3 - empty uri');
        $value4 = new Value(null, '', 'value 4 - empty uri');
        $sut    = new ValueCollection(null, $value1, $value2, $value3, $value4);

        $this->assertFalse($sut->hasDuplicates());

        $value1->setUri(md5($value2->getUri()));
        $this->assertFalse($sut->hasDuplicates());

        $value1->setUri($value2->getUri());
        $this->assertTrue($sut->hasDuplicates());
    }

    public function testGetDuplicates(): void
    {
        $value1 = new Value(null, 'http://example.com#1', '1');
        $value2 = new Value(null, 'http://example.com#2', '2');
        $value3 = new Value(null, '', 'value 3 - empty uri');
        $value4 = new Value(null, '', 'value 4 - empty uri');
        $sut    = new ValueCollection(null, $value1, $value2, $value3, $value4);

        $this->assertSame(0, $sut->getDuplicatedValues()->count());
    }

    public function testRemoveValueByUri(): void
    {
        $sut = new ValueCollection(
            null,
            ...[
                new Value(null, 'http://example.com#1', '1'),
                new Value(null, 'http://example.com#2', '2')
            ]
        );

        $sut->removeValueByUri('http://example.com#1');

        $this->assertSame(['http://example.com#2'], $sut->getUris());
    }

    /**
     * @param Value ...$values
     *
     * @return Value[]
     */
    private function getUniqueValues(Value ...$values): array
    {
        return array_values(
            array_combine(
                array_map(
                    static function (Value $value) {
                        return $value->getUri();
                    },
                    $values
                ),
                $values
            )
        );
    }

    public function provideValues(): array
    {
        return [
            'Unique values'     => [
                'uri'    => 'http://example.com#1',
                'values' => [
                    new Value(1, 'https://example.com#1', '1'),
                    new Value(2, 'https://example.com#2', '2'),
                ],
            ],
            'Duplicated values' => [
                'uri'    => 'http://example.com#2',
                'values' => [
                    new Value(1, 'https://example.com#1', '1'),
                    new Value(2, 'https://example.com#2', '2'),
                ],
            ],
        ];
    }

    public function provideValueCollections(): array
    {
        return [
            'Add to empty collection'                       => [
                'expected' => new ValueCollection(
                    null,
                    new Value(1, 'https://example.com#1', '1')
                ),
                'sut'      => new ValueCollection(),
                'value'    => new Value(1, 'https://example.com#1', '1'),
            ],
            'Add unique value to collection'                => [
                'expected' => new ValueCollection(
                    null,
                    new Value(1, 'https://example.com#1', '1'),
                    new Value(2, 'https://example.com#2', '2')
                ),
                'sut'      => new ValueCollection(
                    null,
                    new Value(1, 'https://example.com#1', '1')
                ),
                'value'    => new Value(2, 'https://example.com#2', '2'),
            ],
            'Overwrite value in collection'                 => [
                'expected' => new ValueCollection(
                    null,
                    new Value(1, 'https://example.com#1', '1'),
                    new Value(1, 'https://example.com#1', '2')
                ),
                'sut'      => new ValueCollection(
                    null,
                    new Value(1, 'https://example.com#1', '1')
                ),
                'value'    => new Value(1, 'https://example.com#1', '2'),
            ],
            'Add empty label value to empty collection'     => [
                'expected' => new ValueCollection(
                    null,
                    new Value(1, 'https://example.com#1', __('Element') . ' 1')
                ),
                'sut'      => new ValueCollection(),
                'value'    => new Value(1, 'https://example.com#1', ''),
            ],
            'Add empty label value to non-empty collection' => [
                'expected' => new ValueCollection(
                    null,
                    new Value(1, 'https://example.com#1', '1'),
                    new Value(2, 'https://example.com#2', __('Element') . ' 2')
                ),
                'sut'      => new ValueCollection(
                    null,
                    new Value(1, 'https://example.com#1', '1')
                ),
                'value'    => new Value(2, 'https://example.com#2', ''),
            ],
        ];
    }
}
