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

namespace oat\tao\test\unit\model\search;

use oat\generis\test\TestCase;
use oat\tao\model\search\ResultSetMapper;

class ResultSetMapperTest extends TestCase
{
    /** @var ResultSetMapper */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new ResultSetMapper(
            [
                ResultSetMapper::OPTION_STRUCTURE_MAP =>
                    [
                        'default' => [
                            'label' => [
                                'id' => 'label',
                                'label' => __('Label'),
                                'sortable' => false
                            ]
                        ],
                        'results' => [
                            'label' => [
                                'id' => 'label',
                                'label' => __('Label'),
                                'sortable' => false
                            ],
                            'test_taker_name' => [
                                'id' => 'test_taker_name',
                                'label' => __('Test Taker'),
                                'sortable' => false
                            ],
                            'test_taker' => [
                                'id' => 'test_taker',
                                'label' => __('Test Taker'),
                                'sortable' => false
                            ],
                        ]
                    ]
            ]
        );
    }

    public function testGetPromiseModelResults(): void
    {
        $result = $this->subject->getPromiseModel('results');
        $this->assertEquals([
            'label' => [
                'id' => 'label',
                'label' => __('Label'),
                'sortable' => false
            ],
            'test_taker_name' => [
                'id' => 'test_taker_name',
                'label' => __('Test Taker'),
                'sortable' => false
            ],
            'test_taker' => [
                'id' => 'test_taker',
                'label' => __('Test Taker'),
                'sortable' => false
            ],
        ], $result);
    }

    public function testGetPromiseModelDefault(): void
    {
        $result = $this->subject->getPromiseModel('different');
        $this->assertEquals([
            'label' => [
                'id' => 'label',
                'label' => __('Label'),
                'sortable' => false
            ],
        ], $result);
    }

    /**
     * @dataProvider getResultSetData
     */
    public function testGetResultSetModel(array $resultDataSet, string $structure, array $filteredResults): void
    {
        $result = $this->subject->getResultSetModel($resultDataSet, $structure);

        $this->assertEquals($filteredResults, $result);
    }

    public function getResultSetData()
    {
        return [
            'results data set' => [
                [
                    'test_taker_name' => [
                        'id' => 'someId'
                    ],
                    'some_other_key' => [
                        'id' => 'anotherId'
                    ]
                ],
                'results',
                [
                    'test_taker_name' => [
                        'id' => 'someId'
                    ]
                ]
            ],
            'non existing structure' => [
                [
                    'test_taker_name' => [
                        'id' => 'someId'
                    ],
                    'some_other_key' => [
                        'id' => 'anotherId'
                    ],
                    'label' => [
                        'somelabel'
                    ]
                ],
                'nonExisting',
                [
                    'label' => [
                        'somelabel'
                    ]
                ]
            ],
        ];
    }
}
