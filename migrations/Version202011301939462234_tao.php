<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\search\ResultSetMapper;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202011301939462234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register ResultSetMapper';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(ResultSetMapper::SERVICE_ID, new ResultSetMapper(
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
        ));
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(ResultSetMapper::SERVICE_ID);
    }
}
