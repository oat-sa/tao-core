<?php

use oat\tao\model\search\ResultSetMapper;

return new ResultSetMapper(
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
