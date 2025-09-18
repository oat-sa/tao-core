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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\actionQueue\implementation;

use oat\tao\model\actionQueue\ActionQueueException;
use oat\tao\model\actionQueue\implementation\InstantActionQueue;
use PHPUnit\Framework\TestCase;
use oat\tao\model\actionQueue\QueuedAction;

class InstantActionQueueTest extends TestCase
{
    /**
     * @var InstantActionQueue
     */
    private $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new InstantActionQueue();
    }

    public function testIsActionEnabledNoActionConfig(): void
    {
        $this->object->setOptions([]);
        $action = $this->createMock(QueuedAction::class);
        $action->method('getId')->willReturn('FAKE_ID');

        $this->expectException(ActionQueueException::class);
        $this->object->isActionEnabled($action);
    }

    /**
     * @param string $actionId
     * @param array $options
     * @param bool $expected
     * @throws ActionQueueException
     * @throws \common_exception_Error
     *
     * @dataProvider dataProviderTestIsActionEnabled
     */
    public function testIsActionEnabled(string $actionId, array $options, bool $expected): void
    {
        $this->object->setOptions($options);
        $action = $this->createMock(QueuedAction::class);
        $action->method('getId')->willReturn($actionId);

        $result = $this->object->isActionEnabled($action);
        static::assertSame($expected, $result, 'Result of checking if action is enabled must be as expected.');
    }

    public function dataProviderTestIsActionEnabled(): array
    {
        return [
            'No restrictions' => [
                'actionId' => 'ACTION_1',
                'options' => [
                    'actions' => [
                        'ACTION_1' => []
                    ]
                ],
                'expected' => false,
            ],
            'One restriction - disabled' => [
                'actionId' => 'ACTION_1',
                'options' => [
                    'actions' => [
                        'ACTION_1' => [
                            'restrictions' => [
                                'restriction_1' => 0
                            ]
                        ]
                    ]
                ],
                'expected' => false,
            ],
            'One restriction - enabled (int)' => [
                'actionId' => 'ACTION_1',
                'options' => [
                    'actions' => [
                        'ACTION_1' => [
                            'restrictions' => [
                                'restriction_1' => 1
                            ]
                        ]
                    ]
                ],
                'expected' => true,
            ],
            'One restriction - enabled (array)' => [
                'actionId' => 'ACTION_1',
                'options' => [
                    'actions' => [
                        'ACTION_1' => [
                            'restrictions' => [
                                'restriction_1' => []
                            ]
                        ]
                    ]
                ],
                'expected' => true,
            ],
            'Multiple restrictions - disabled' => [
                'actionId' => 'ACTION_1',
                'options' => [
                    'actions' => [
                        'ACTION_1' => [
                            'restrictions' => [
                                'restriction_1' => 0,
                                'restriction_2' => 0
                            ]
                        ]
                    ]
                ],
                'expected' => false,
            ],
            'Multiple restrictions - enabled' => [
                'actionId' => 'ACTION_1',
                'options' => [
                    'actions' => [
                        'ACTION_1' => [
                            'restrictions' => [
                                'restriction_1' => 1,
                                'restriction_2' => 0
                            ]
                        ]
                    ]
                ],
                'expected' => true,
            ],
        ];
    }
}
