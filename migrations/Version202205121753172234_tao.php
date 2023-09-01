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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\task\CopyClassTask;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202205121753172234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return sprintf('Add "%s" to "%s" group.', CopyClassTask::class, TaskLogInterface::CATEGORY_COPY);
    }

    public function up(Schema $schema): void
    {
        $taskLog = $this->getTaskLog();

        $this->getTaskLog()->linkTaskToCategory(CopyClassTask::class, TaskLogInterface::CATEGORY_COPY);

        $this->registerService(TaskLogInterface::SERVICE_ID, $taskLog);
    }

    public function down(Schema $schema): void
    {
        $taskLog = $this->getTaskLog();

        $taskToCategoryAssociations = $taskLog->getOption(
            TaskLogInterface::OPTION_TASK_TO_CATEGORY_ASSOCIATIONS,
            []
        );
        unset($taskToCategoryAssociations[CopyClassTask::class]);
        $taskLog->setOption(TaskLogInterface::OPTION_TASK_TO_CATEGORY_ASSOCIATIONS, $taskToCategoryAssociations);

        $this->registerService(TaskLogInterface::SERVICE_ID, $taskLog);
    }

    /**
     * @return TaskLogInterface|TaskLog
     */
    private function getTaskLog(): TaskLogInterface
    {
        return $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
    }
}
