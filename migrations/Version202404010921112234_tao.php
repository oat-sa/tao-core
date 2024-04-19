<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\scripts\install\SetUpQueueTasks;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202404010921112234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add task UpdateTestResourceInIndex to list of ignored in UI';
    }

    public function up(Schema $schema): void
    {
        $serviceManager = $this->getServiceManager();
        $taskLog = $serviceManager->get(TaskLogInterface::SERVICE_ID);
        $taskLog->setOption(TaskLogInterface::OPTION_TASK_IGNORE_LIST, $this->getIndexationTasks());
        $serviceManager->register(TaskLogInterface::SERVICE_ID, $taskLog);
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(__CLASS__ . ' cannot be reversed');
    }

    private function getIndexationTasks(): array
    {
        return SetUpQueueTasks::QUEUE_TASK_IGNORE;
    }
}
