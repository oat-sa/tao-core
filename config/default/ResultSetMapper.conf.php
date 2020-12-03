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
                'label' => [
                    'id' => 'label',
                    'label' => __('Label'),
                    'sortable' => false
                ],
                'test_taker_name' => [
                    'id' => 'test_taker_name',
                    'label' => __('Test Taker Name'),
                    'sortable' => false
                ],
                'test_taker' => [
                    'id' => 'test_taker',
                    'label' => __('Test Taker ID'),
                    'sortable' => false
                ],
                'delivery' => [
                    'id' => 'delivery',
                    'label' => __('Delivery Uri'),
                    'sortable' => false,
                    'visible' => false
                ],
                'delivery_execution_start_time' => [
                    'id' => 'delivery_execution_start_time',
                    'label' => __('Start Time'),
                    'sortable' => false,
                ],
            ]
        ]
    ]
);
