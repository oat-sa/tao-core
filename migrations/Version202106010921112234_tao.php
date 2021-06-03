<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\search\tasks\AddSearchIndexFromArray;
use oat\tao\model\search\tasks\DeleteIndexProperty;
use oat\tao\model\search\tasks\RenameIndexProperties;
use oat\tao\model\search\tasks\UpdateClassInIndex;
use oat\tao\model\search\tasks\UpdateDataAccessControlInIndex;
use oat\tao\model\search\tasks\UpdateResourceInIndex;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202106010921112234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sm = $this->getServiceManager();
        $taskLog = $sm->get(TaskLogInterface::SERVICE_ID);
        $taskLog->setOption(TaskLogInterface::OPTION_TASK_IGNORE_LIST, $this->getIndexationTasks());
        $sm->register(TaskLogInterface::SERVICE_ID, $taskLog);
    }

    public function down(Schema $schema): void
    {
        $sm = $this->getServiceManager();
        $taskLog = $sm->get(TaskLogInterface::SERVICE_ID);
        $taskLog->setOption(TaskLogInterface::OPTION_TASK_IGNORE_LIST, []);
        $sm->register(TaskLogInterface::SERVICE_ID, $taskLog);
    }

    private function getIndexationTasks(): array
    {
        return [
            UpdateResourceInIndex::class,
            UpdateClassInIndex::class,
            DeleteIndexProperty::class,
            RenameIndexProperties::class,
            UpdateDataAccessControlInIndex::class,
            AddSearchIndexFromArray::class,
        ];
    }
}
