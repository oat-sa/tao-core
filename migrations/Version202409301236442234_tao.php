<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202409301236442234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update taoTaskLog with default batch size';
    }

    public function up(Schema $schema): void
    {
        $serviceManager = $this->getServiceManager();
        $taskLog = $serviceManager->get(TaskLogInterface::SERVICE_ID);
        $taskLog->setOption(TaskLogInterface::OPTION_DEFAULT_BATCH_SIZE, TaskLogInterface::DEFAULT_BATCH_SIZE);
        $serviceManager->register(TaskLogInterface::SERVICE_ID, $taskLog);
    }

    public function down(Schema $schema): void
    {
        $serviceManager = $this->getServiceManager();
        $taskLog = $serviceManager->get(TaskLogInterface::SERVICE_ID);
        $options = $taskLog->getOptions();
        if (isset($options[TaskLogInterface::OPTION_DEFAULT_BATCH_SIZE])) {
            unset($options[TaskLogInterface::OPTION_DEFAULT_BATCH_SIZE]);
        }
        $taskLog->setOptions($options);
        $serviceManager->register(TaskLogInterface::SERVICE_ID, $taskLog);
    }
}
