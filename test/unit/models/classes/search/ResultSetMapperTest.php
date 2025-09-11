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
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\search;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\ResultSetMapper;

class ResultSetMapperTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ResultSetMapper $subject;
    private AdvancedSearchChecker|MockObject $advancedSearchChecker;

    protected function setUp(): void
    {
        $this->subject = new ResultSetMapper(
            [
                ResultSetMapper::OPTION_STRUCTURE_MAP => [
                    'default' => [
                        'label' => [
                            'id' => 'label',
                            'label' => __('Label'),
                            'sortable' => false
                        ]
                    ],
                    'results' => [
                        'default' => null,
                        'advanced' => [
                            'test_taker' => [
                                'id' => 'test_taker',
                                'label' => 'Test Taker',
                                'sortable' => false
                            ],
                            'label' => [
                                'id' => 'label',
                                'label' => 'Label',
                                'sortable' => false
                            ],
                            'test_taker_name' => [
                                'id' => 'test_taker_name',
                                'label' => 'Test Taker',
                                'sortable' => false
                            ],
                        ],
                    ],
                ]
            ]
        );

        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);

        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    AdvancedSearchChecker::class => $this->advancedSearchChecker
                ]
            )
        );
    }

    /**
     * @dataProvider getScenariosData
     */
    public function testGetPromiseModelResults(
        array $expectedResult,
        string $mappedField,
        bool $elasticSearchEnabled
    ): void {
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn($elasticSearchEnabled);

        $result = $this->subject->map($mappedField);
        $this->assertEquals($expectedResult, $result);
    }

    public function getScenariosData()
    {
        return [
            'result search with elastic search enabled' => [
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
                    ],
                ],
                'results',
                true,
            ],
            'result search with elastic search disabled' => [
                [
                    'label' => [
                        'id' => 'label',
                        'label' => __('Label'),
                        'sortable' => false
                    ],
                ],
                'results',
                false,
            ],
            'different search with elastic search enabled' => [
                [
                    'label' => [
                        'id' => 'label',
                        'label' => __('Label'),
                        'sortable' => false
                    ],
                ],
                'different',
                true,
            ]
        ];
    }
}
