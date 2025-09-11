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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\search;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\search\ResultSetFilter;
use oat\tao\model\search\ResultSetMapper;
use PHPUnit\Framework\MockObject\MockObject;

class ResultSetFilterTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ResultSetFilter $subject;
    private ResultSetMapper|MockObject $resultSetMapperMock;

    protected function setUp(): void
    {
        $this->resultSetMapperMock = $this->createMock(ResultSetMapper::class);

        $this->subject = new ResultSetFilter();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    ResultSetMapper::SERVICE_ID => $this->resultSetMapperMock
                ]
            )
        );
    }

    /**
     * @dataProvider getResultSetData
     */
    public function testFilter(array $resultDataSet, string $structure, array $filteredResults, array $map): void
    {
        $this->resultSetMapperMock
            ->method('map')
            ->willReturn($map);

        $result = $this->subject->filter($resultDataSet, $structure);

        $this->assertEquals($filteredResults, $result);
    }

    public function getResultSetData(): array
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
                ],
                [
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
                ],
                'default' => [
                    'label' => [
                        'id' => 'label',
                        'label' => __('Label'),
                        'sortable' => false
                    ]
                ]
            ],
        ];
    }
}
