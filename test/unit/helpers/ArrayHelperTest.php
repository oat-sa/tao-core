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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\helpers;

use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
    /**
     * @dataProvider arrayProvider
     */
    public function testArrayUnique($testArray, $expectedArray)
    {
        $result = \tao_helpers_Array::array_unique($testArray);
        $this->assertEquals($expectedArray, $result);
    }

    public function arrayProvider()
    {
        $objectA = new myFakeObject(1, 2, 3);
        $objectB = new myFakeObject(4, 5, 6);
        $objectC = new myFakeObject('abc', 'def', 'ghi');
        $objectD = new myFakeObject('plop', 'test', 'foo');
        $objectDPrime = new myFakeObject('plop', 'test', 'foo');
        return [
            [[$objectA, $objectB, $objectA, $objectD], [$objectA, $objectB, 3 => $objectD]],
            [[$objectA, 3 => $objectB, $objectD, $objectDPrime], [$objectA, 3 => $objectB, $objectD]],
            [
                [$objectA, $objectB, $objectC, $objectD, $objectA, $objectB, $objectC, $objectD],
                [$objectA, $objectB, $objectC, $objectD]
            ],
            [[], []],
            [[$objectC, $objectC, $objectC, $objectC], [$objectC]],
            [[2 => $objectC, 3 => $objectC, 56 => $objectC, 42 => $objectC], [2 => $objectC]],
            [
                ['aaa' => $objectA, 'bbb' => $objectB, 'ccc' => $objectC, 42 => $objectC],
                ['aaa' => $objectA, 'bbb' => $objectB, 'ccc' => $objectC]
            ],
        ];
    }

    /**
     * @dataProvider containsOnlyValueProvider
     */
    public function testContainsOnlyValue($value, array $container, $strict, $exceptAtIndex, $expectedValue)
    {
        $this->assertSame(
            $expectedValue,
            \tao_helpers_Array::containsOnlyValue($value, $container, $strict, $exceptAtIndex)
        );
    }

    public function containsOnlyValueProvider()
    {
        return [
            [1, [1, 1, 1], true, [], true],
            [1, [1, 1, '1'], true, [], false],
            [1, [1, 1, '1'], false, [], true],
            [1, [1, 1, '1'], true, [2], true],
            [1, ['1', 1, '1'], true, [2], false],
            [1, ['1', 1, '1'], true, [0, 2], true],
            [1, [], true, [], false],
            [0, [1, 2, 3], true, [], false],
            [0, [1, 2, 3], false, [], false],
            [0, [1, 2, 3], false, [0, 1, 2], false],
            [[1, 2, 3], [1, 2, 3], false, [], false],
            [[1, 2, 3], [1, 2, 3], true, [], false],
            [[1, 2, 3], [1, 2, 3], false, [0, 1, 2], false]
        ];
    }

    /**
     * @dataProvider arrayContainsOnlyValueProvider
     */
    public function testArraysContainOnlyValue(
        array $containers,
        $value,
        $exceptNContainers,
        array $exceptAtIndex,
        $expectedInvalidContainers,
        $expectedValidContainers,
        $expected
    ) {
        $invalidContainers = [];
        $validContainers = [];
        $this->assertSame(
            $expected,
            \tao_helpers_Array::arraysContainOnlyValue(
                $containers,
                $value,
                $exceptNContainers,
                $exceptAtIndex,
                $invalidContainers,
                $validContainers
            )
        );
        $this->assertEquals($expectedInvalidContainers, $invalidContainers);
        $this->assertEquals($expectedValidContainers, $validContainers);
    }

    public function arrayContainsOnlyValueProvider()
    {
        return [
            [
                [
                    ['1', '1', '1'],
                    ['1', '1', '1']
                ], '1', 0, [], [], [0, 1], true
            ],

            [
                [
                    ['1', '1', '1'],
                    ['1', '1', '2']
                ], '1', 0, [], [1], [0], false
            ],

            [
                [
                    ['1', '1', '2'],
                    ['1', '1', '1'],
                    ['2', '1', '1']
                ], '1', 0, [], [0, 2], [1], false
            ],

            [
                [
                    ['1', '2', '1'],
                    ['1', '1', '1']
                ], '1', 0, [], [0], [1], false
            ],

            [
                [
                    ['1', '2', '1'],
                    ['1', '1', '1']
                ], '1', 0, [1], [], [0, 1], true
            ],

            [
                [
                    ['4', '5', '6'],
                    ['1', '8', '8'],
                    ['2', '8', '8']
                ], '8', 1, [0], [0], [1, 2], true
            ],

            [
                [
                    ['1', '8', '8'],
                    ['4', '5', '6'],
                    ['2', '8', '8']
                ], '8', 1, [0], [1], [0, 2], true
            ],

            [
                [
                    ['1', '8', '8'],
                    ['2', '8', '8'],
                    ['4', '5', '6'],
                ], '8', 0, [0], [2], [0, 1], false
            ],

            [
                [
                    ['1', '8', '8'],
                    ['2', '8', '8'],
                    ['4', '5', '6'],
                    ['4', '5', '6'],
                ], '8', 2, [0], [2, 3], [0, 1], true
            ],

            [
                [
                    ['1', '2', '8', '8'],
                    ['2', '3', '8', '8'],
                    ['4', '0', '5', '6'],
                    ['4', '0', '5', '6'],
                ], '8', 2, [0, 1], [2, 3], [0, 1], true
            ],

            [
                [
                    ['1', '2', '8', '8'],
                    ['2', '3', '4', '8'],
                    ['4', '0', '5', '6'],
                    ['4', '0', '5', '6'],
                ], '8', 2, [0, 1], [1, 2, 3], [0], false
            ],

            [
                [], '8', 2, [0, 1], [], [], false
            ],

            [
                [
                    ['1', '2', '8', '8']
                ], '7', 1, [], [0], [], true
            ],

            [
                [
                    ['8', '8', '8', '8'],
                    ['8', '8', '8', '8']
                ], '8', 1, [], [], [0, 1], false
            ],

            [
                [
                    ['8', '8', '8', '8'],
                    ['8', '8', '8', '8']
                ], '8', 0, [], [], [0, 1], true
            ],

            [
                [
                    ['8', '8', '8', '8'],
                    ['8', '8', '8', '8']
                ], '8', -1, [], [], [0, 1], true
            ],

            [
                [
                    ['8', '8', '8', '8'],
                    ['8', '8', '8', '8']
                ], '8', 2, [], [], [0, 1], false
            ],

            [
                [
                    ['8', '8', '8', '8'],
                    ['8', '8', '8', '8']
                ], '8', 0, [0, 1, 2, 3], [0, 1], [], false
            ],

            [
                [
                    ['8', '8', '8', '8'],
                    ['8', '8', '8', '8']
                ], '8', 1, [], [], [0, 1], false
            ],

            [
                [
                    ['1', '2', '8', '8', '8', '8'],
                    ['1', '2', '8', '8', '8', '8']
                ], '8', 1, [0, 1], [], [0, 1], false
            ],

            [
                [
                    ['abc', 'def', '8', '8', '8', '8'],
                    ['abc', 'def', '8', '8', '8', '8'],
                    ['abc', 'def', '1', '2', '3', '4'],
                ], '8', 0, [0, 1], [2], [0, 1], false
            ],

            [
                [
                    ['abc', 'def', '8', '8', '8', '8'],
                    ['abc', 'def', '1', '2', '3', '4'],
                    ['abc', 'def', '8', '8', '8', '8'],
                    ['abc', 'def', '5', '6', '7', '8'],
                ], '8', 0, [0, 1], [1, 3], [0, 2], false
            ]
        ];
    }

    /**
     * @dataProvider minArrayCountValuesProvider
     */
    public function testMinArrayCountValues($values, array $arrays, $expected, $returnAll = false)
    {
        $this->assertSame($expected, \tao_helpers_Array::minArrayCountValues($values, $arrays, $returnAll));
    }

    public function minArrayCountValuesProvider()
    {
        return [
            [3, [], false],
            [[], [], false],
            [null, [], false],
            [false, [], false],
            [[], [3], false],

            [
                3,
                [
                    [1, 2, 3]
                ], 0
            ],

            [
                3,
                [
                    [1, 2, 3],
                    [3, 3, 3]
                ], 0
            ],

            [
                3,
                [
                    [3, 3, 3],
                    [1, 2, 3]
                ], 1
            ],

            [
                [8, 9],
                [
                    [1, 2, 3, 4, 8, 8, 9],
                    [1, 2, 3, 4, 5, 6, 7],
                    [1, 2, 3, 4, 8, 8, 9],
                    [1, 2, 3, 4, 8, 8, 9, 9]
                ], 1
            ],

            [
                [8, 9],
                [
                    [1, 2, 3, 4, 8, 8, 9],
                    [1, 2, 3, 4, 5, 6, 7],
                    [1, 2, 3, 4, 5, 6, 7],
                    [1, 2, 3, 4, 8, 8, 9],
                    [1, 2, 3, 4, 8, 8, 9, 9]
                ], 1
            ],

            [
                ['8', '9'],
                [
                    ['1', '2', '3', '4', '8', '8', '9'],
                    ['1', '2', '3', '4', '5', '6', '7'],
                    ['1', '2', '3', '4', '5', '6', '7'],
                    ['1', '2', '3', '4', '8', '8', '9'],
                    ['1', '2', '3', '4', '8', '8', '9', '9']
                ], 1
            ],

            [
                ['8', '9'],
                [
                    ['1', '2', '3', '4', '8', '8', '9'],
                    [1, 2, 3, 4, 5, 6, 7],
                    ['1', '2', '3', '4', '5', '6', '7'],
                    ['1', '2', '3', '4', '8', '8', '9'],
                    ['1', '2', '3', '4', '8', '8', '9', '9']
                ], 1
            ],

            [
                [8, 9],
                [
                    ['1', '2', '3', '4', '8', '8', '9'],
                    [1, 2, 3, 4, 5, 6, 7],
                    ['1', '2', '3', '4', '5', '6', '7'],
                    ['1', '2', '3', '4', '8', '8', '9'],
                    ['1', '2', '3', '4', '8', '8', '9', '9']
                ], 1
            ],

            [
                ['8', '9'],
                [
                    [1, 2, 3, 4, 8, 8, 9],
                    ['1', '2', '3', '4', '5', '6', '7'],
                    [1, 2, 3, 4, 5, 6, 7],
                    [1, 2, 3, 4, 8, 8, 9],
                    [1, 2, 3, 4, 8, 8, 9, 9]
                ], 1
            ],

            [
                [8, 9],
                [
                    'a' => [1, 2, 3, 4, 8, 8, 9],
                    'b' => [1, 2, 3, 4, 5, 6, 7],
                    'c' => [1, 2, 3, 4, 5, 6, 7],
                    'd' => [1, 2, 3, 4, 8, 8, 9],
                    'e' => [1, 2, 3, 4, 8, 8, 9, 9]
                ], 'b'
            ],

            [
                [8, 9],
                [
                    'a' => [],
                    'b' => [],
                    'c' => [],
                    'd' => [1, 2, 3, 4, 8, 8, 9],
                    'e' => [1, 2, 3, 4, 8, 8, 9, 9]
                ], 'a'
            ],

            [
                [8, 9],
                [
                    'a' => [],
                    'b' => [],
                    'c' => [],
                    'd' => [1, 2, 3, 4, 8, 8, 9],
                    'e' => [1, 2, 3, 4, 8, 8, 9, 9]
                ], ['a', 'b', 'c'], true
            ],

            [
                [8, 9],
                [
                    'a' => [],
                    'b' => [],
                    'c' => [],
                    'd' => [1, 2, 3, 4, 8, 8, 9],
                    'e' => []
                ], ['a', 'b', 'c', 'e'], true
            ],

            [
                [8, 9],
                [
                    [1, 2, 3, 4, 8, 8, 9],
                    [1, 2, 3, 4, 5, 6, 7],
                    [1, 2, 3, 4, 5, 6, 7],
                    [1, 2, 3, 4, 8, 8, 9],
                    [1, 2, 3, 4, 8, 8, 9]
                ], [1, 2], true
            ],

            [
                ['88', '99'],
                [
                    [
                        '4',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '4',
                        '3',
                        '3',
                        '11',
                        '4',
                        '1',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '2',
                        '3',
                        '6',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '3',
                        '6',
                        '3',
                        '4',
                        '4'
                    ]
                ],
                1
            ],

            [
                ['88', '99'],
                [
                    [
                        '4',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '4',
                        '3',
                        '3',
                        '11',
                        '4',
                        '1',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '2',
                        '3',
                        '6',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '3',
                        '6',
                        '3',
                        '4',
                        '4'
                    ]
                ],
                [1],
                true
            ],

            [
                ['88', '99'],
                [
                    [
                        '4',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '4',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '4',
                        '3',
                        '3',
                        '11',
                        '4',
                        '1',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '2',
                        '3',
                        '6',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '3',
                        '6',
                        '3',
                        '4',
                        '4'
                    ]
                ],
                2
            ],

            [
                ['88', '99'],
                [
                    [
                        '4',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '4',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '4',
                        '3',
                        '3',
                        '11',
                        '4',
                        '1',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '2',
                        '3',
                        '6',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '3',
                        '6',
                        '3',
                        '4',
                        '4'
                    ]
                ],
                [2],
                true
            ],

            [
                ['88', '99'],
                [
                    [
                        '4',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '4',
                        '3',
                        '3',
                        '11',
                        '4',
                        '1',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '2',
                        '3',
                        '6',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '3',
                        '6',
                        '3',
                        '4',
                        '4'
                    ],
                    [
                        '4',
                        '3',
                        '3',
                        '11',
                        '4',
                        '1',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '2',
                        '3',
                        '6',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '3',
                        '6',
                        '3',
                        '4',
                        '4'
                    ]
                ],
                1
            ],

            [
                ['88', '99'],
                [
                    [
                        '4',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '4',
                        '3',
                        '3',
                        '11',
                        '4',
                        '1',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '2',
                        '3',
                        '6',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '3',
                        '6',
                        '3',
                        '4',
                        '4'
                    ],
                    [
                        '4',
                        '3',
                        '3',
                        '11',
                        '4',
                        '1',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '2',
                        '3',
                        '6',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '1',
                        '3',
                        '6',
                        '3',
                        '4',
                        '4'
                    ]
                ],
                [1, 2],
                true
            ],

            [
                ['88', '99'],
                [
                    [
                        '1',
                        '99',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '2',
                        '3',
                        '2',
                        '7',
                        '4',
                        '2',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '1',
                        '3',
                        '3',
                        '1',
                        '1',
                        '1',
                        '2',
                        '1',
                        '1',
                        '3',
                        '2',
                        '6',
                        '2',
                        '5'
                    ],
                ],
                1
            ],

            [
                ['88', '99'],
                [
                    [
                        '1',
                        '99',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88',
                        '88'
                    ],
                    [
                        '2',
                        '3',
                        '2',
                        '7',
                        '4',
                        '2',
                        '1',
                        '1',
                        '1',
                        '88',
                        '1',
                        '1',
                        '3',
                        '3',
                        '1',
                        '1',
                        '1',
                        '2',
                        '1',
                        '1',
                        '3',
                        '2',
                        '6',
                        '2',
                        '5'
                    ],
                ], [1], true
            ]
        ];
    }

    /**
     * @dataProvider countConsistentColumnsProvider
     */
    public function testCountConsistentColumns(
        array $matrix,
        array $ignoreValues,
        $expected,
        $emptyIsConsistent = false
    ) {
        $this->assertSame(
            $expected,
            \tao_helpers_Array::countConsistentColumns($matrix, $ignoreValues, $emptyIsConsistent)
        );
    }

    public function countConsistentColumnsProvider()
    {
        return [
            [[[]], [], 0],
            [[], [], 0],
            [[null, 1, 2, 3], [], 0],

            [
                [
                    [1, 2, 3]
                ], [], 3
            ],

            [
                [
                    [1, 2, 3]
                ], [], 3, true
            ],

            [
                [
                    [1, 2, 3]
                ], [8], 3
            ],

            [
                [
                    [1, 2, 3]
                ], [8], 3, true
            ],

            [
                [
                    [1, 2, 3]
                ], [8, 9], 3
            ],

            [
                [
                    [1, 2, 3]
                ], [8, 9], 3, true
            ],

            [
                [
                    [1, 2, 3],
                    [1, 2, 3]
                ], [], 3
            ],

            [
                [
                    [1, 2, 3],
                    [1, 2, 3]
                ], [], 3, true
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    [1, 2, 3, 0, 4, 3],
                    [1, 2, 2, 8, 4, 9]
                ], [8, 9], 5
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    [1, 2, 3, 0, 4, 3],
                    [1, 2, 2, 8, 4, 9]
                ], [8, 9], 5, true
            ],

            [
                [
                    [1, 2, 1, 66, 4, 3],
                    [1, 2, 3, 66, 4, 3],
                    [1, 2, 2, 8, 4, 9]
                ], [8, 9], 5
            ],

            [
                [
                    [1, 2, 1, 66, 4, 3],
                    [1, 2, 3, 66, 4, 3],
                    [1, 2, 2, 8, 4, 9]
                ], [8, 9], 5, true
            ],

            [
                [
                    [1, 2, 1, 0],
                    [1, 2, 3, 0, 4],
                    [1, 2, 2, 8, 4, 9]
                ], [8, 9], 3
            ],

            [
                [
                    [1, 2, 1, 0],
                    [1, 2, 3, 0, 4],
                    [1, 2, 2, 8, 4, 9]
                ], [8, 9], 3, true
            ],

            [
                [
                    [1, 2, 1, 1],
                    [1, 2, 3, 0, 4],
                    [1, 2, 2, 8, 4, 9]
                ], [8, 9], 2
            ],

            [
                [
                    [1, 2, 1, 1],
                    [1, 2, 3, 0, 4],
                    [1, 2, 2, 8, 4, 9]
                ], [8, 9], 2, true
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    [1, 2, 3, 0, 4, 3],
                    [1, 2, 2, 8, 4, 9]
                ], [8], 4
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    [1, 2, 3, 0, 4, 3],
                    [1, 2, 2, 8, 4, 9]
                ], [8], 4, true
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    [1, 2, 3, 0, 4, 3],
                    [1, 2, 2, 8, 4, 9]
                ], [], 3
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    [1, 2, 3, 0, 4, 3],
                    [1, 2, 2, 8, 4, 9]
                ], [], 3, true
            ],

            [
                [
                    [1, 8, 1, 0, 4, 3],
                    [1, 8, 3, 8, 4, 3],
                    [1, 8, 2, 8, 4, 9]
                ], [8, 9], 5, true
            ],

            [
                [
                    [8, 9, 8, 9, 8, 9],
                    [9, 8, 9, 9, 9, 8],
                    [8, 9, 8, 9, 8, 9]
                ], [8, 9], 0
            ],

            [
                [
                    [8, 9, 8, 9, 8, 9],
                    [9, 8, 9, 9, 9, 8],
                    [8, 9, 8, 9, 8, 9]
                ], [8, 9], 6, true
            ],

            [
                [
                    [8, 9, 8, 9, 8, 9],
                    null,
                    [8, 9, 8, 9, 8, 9]
                ], [8, 9], false
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    3,
                    [1, 2, 2, 8, 4, 9]
                ], [], false
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    3,
                    [1, 2, 2, 8, 4, 9]
                ], [], false, false
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    ['2', 'null'],
                    [1, 2, 2, 8, 4, 9]
                ], [], false
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    ['2', 'null'],
                    [1, 2, 2, 8, 4, 9]
                ], [], false, false
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    ['2', '3', '3', '5', '6', '7'],
                    [1, 2, 2, 8, 4, 9]
                ], [], 0
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    ['2', '3', '3', '5', '6', '7'],
                    [1, 2, 2, 8, 4, 9]
                ], [], 0, false
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    [null, null, null, null, null, null],
                    [1, 2, 2, 8, 4, 9]
                ], [], 0
            ],

            [
                [
                    [1, 2, 1, 0, 4, 3],
                    [null, null, null, null, null, null],
                    [1, 2, 2, 8, 4, 9]
                ], [], 0, false
            ]
        ];
    }
}

class myFakeObject
{
    private $a;
    private $b;
    private $c;

    public function __construct($a, $b, $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    public function __equals($object)
    {

        if ($object instanceof myFakeObject) {
            if (
                $this->a === $object->getA()
                && $this->b === $object->getB()
                && $this->c === $object->getC()
            ) {
                return true;
            }
        }
        return false;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }

    public function getC()
    {
        return $this->c;
    }
}
