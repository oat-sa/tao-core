<?php

use oat\tao\model\search\ResultSetMapper;

return new ResultSetMapper(
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
                        'label' => 'Test Taker ID',
                        'sortable' => false
                    ],
                    'label' => [
                        'id' => 'label',
                        'label' => 'Label',
                        'sortable' => false
                    ],
                    'test_taker_name' => [
                        'id' => 'test_taker_name',
                        'label' => 'Test Taker Name',
                        'sortable' => false
                    ],
                    'delivery_execution_start_time' => [
                        'id' => 'delivery_execution_start_time',
                        'label' => 'Start Time',
                        'sortable' => false,
                    ],
                    'delivery' => [
                        'id' => 'delivery',
                        'label' => 'Delivery Uri',
                        'sortable' => false,
                        'visible' => false
                    ],
                ],
            ],
        ]
    ]
);
