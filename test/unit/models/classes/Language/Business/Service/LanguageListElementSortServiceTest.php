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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Language\Business\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\Language\Service\LanguageListElementSortService;
use oat\tao\model\Lists\Business\Contract\ListElementSorterComparatorInterface;
use oat\tao\model\Lists\Business\Contract\ListElementSorterInterface;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;

class LanguageListElementSortServiceTest extends TestCase
{
    /** @var ListElementSorterInterface */
    private $sut;

    /** @var ListElementSorterComparatorInterface|MockObject */
    private $sortingComparatorMock;

    /**
     * @before
     */
    public function init(): void
    {
        $this->sortingComparatorMock = $this->createMock(ListElementSorterComparatorInterface::class);

        $this->sut = new LanguageListElementSortService(
            $this->sortingComparatorMock
        );
    }

    /**
     * @param Value[] $initial
     * @param Value[] $expected
     * @param int $comparatorResponse -1|0|1
     *
     * @dataProvider provideValues
     */
    public function testGetSortedListCollectionValues(array $initial, array $expected, int $comparatorResponse): void
    {
        $collection = new ValueCollection('https://example.com#List', ...$initial);

        $this->sortingComparatorMock->expects($this->atLeast(count($initial) - 1))
            ->method('__invoke')->willReturn($comparatorResponse);

        $actual = $this->sut->getSortedListCollectionValues($collection);

        $this->assertEquals($actual, $expected);
    }

    public function provideValues(): array
    {
        $value1 = new Value(1, 'https://example.com#1', '1');
        $value2 = new Value(2, 'https://example.com#2', '2');
        return [
            'equal' => [
                'initial' => [
                    $value1,
                    $value2,
                ],
                'expected' => [
                    $value1,
                    $value2,
                ],
                'comparatorResponse' => 0
            ],
            'reversed' => [
                'initial' => [
                    $value1,
                    $value2,
                ],
                'expected' => [
                    $value2,
                    $value1,
                ],
                'comparatorResponse' => 1
            ],
            'untouched' => [
                'initial' => [
                    $value2,
                    $value1,
                ],
                'expected' => [
                    $value2,
                    $value1,
                ],
                'comparatorResponse' => -1
            ],
            'oneValue' => [
                'initial' => [
                    $value2,
                ],
                'expected' => [
                    $value2,
                ],
                'comparatorResponse' => 1
            ],
        ];
    }
}
