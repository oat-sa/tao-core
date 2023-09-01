<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\task\UnrelatedResourceImportByHandler;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202202171146482234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Associate TaskQueue category for unrelated resources';
    }

    public function up(Schema $schema): void
    {
        /** @var TaskLogInterface|ConfigurableService $taskLog */
        $taskLog = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

        $taskLog->linkTaskToCategory(
            UnrelatedResourceImportByHandler::class,
            TaskLogInterface::CATEGORY_UNRELATED_RESOURCE
        );

        $this->registerService(TaskLogInterface::SERVICE_ID, $taskLog);
    }

    public function down(Schema $schema): void
    {
        /** @var TaskLogInterface|ConfigurableService $taskLog */
        $taskLog = $this->getServiceManager()->get(TaskLogInterface::SERVICE_ID);

        $associations = $taskLog->getOption(TaskLogInterface::OPTION_TASK_TO_CATEGORY_ASSOCIATIONS, []);

        unset($associations[UnrelatedResourceImportByHandler::class]);

        $taskLog->setOption(TaskLogInterface::OPTION_TASK_TO_CATEGORY_ASSOCIATIONS, $associations);

        $this->registerService(TaskLogInterface::SERVICE_ID, $taskLog);
    }
}
