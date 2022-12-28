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
 * Copyright (c) 2017-2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\model\taskQueue\TaskLog\Decorator;

use common_Logger;
use oat\oatbox\user\User;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\taoBackOffice\controller\Redirector;

class RedirectUrlEntityDecorator extends TaskLogEntityDecorator
{
    private TaskLogInterface $taskLogService;
    private User $user;

    public function __construct(EntityInterface $entity, TaskLogInterface $taskLogService, User $user)
    {
        parent::__construct($entity);
        $this->taskLogService = $taskLogService;
        $this->user = $user;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Add 'redirectUrl' to the result if the task has been processed
     * and it is not an export or delete task
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $deniedCategories = [
            TaskLogInterface::CATEGORY_DELETE,
            TaskLogInterface::CATEGORY_EXPORT,
            TaskLogInterface::CATEGORY_UNKNOWN,
            TaskLogInterface::CATEGORY_UNRELATED_RESOURCE,
        ];

        if (
            !in_array($this->taskLogService->getCategoryForTask($this->getTaskName()), $deniedCategories) &&
             ($this->getStatus()->isCompleted() || $this->getStatus()->isArchived())
        ) {
            $user = $this->user;
            $params = [
                'taskId' => $this->getId()
            ];
            $hasAccess = $this->hasAccess(
                $user,
                Redirector::class,
                'redirectTaskToInstance',
                $params
            );
            if ($hasAccess) {
                $data = array_merge($data, [
                        'redirectUrl' => _url(
                            'redirectTaskToInstance',
                            'Redirector',
                            'taoBackOffice',
                            $params
                        )
                    ]
                );
            } else {
                common_Logger::w(
                    'User \'' . $user->getIdentifier() . '\' does not have access to redirectTaskToInstance'
                );
            }
        }

        return $data;
    }

    protected function hasAccess($user, $class, $method, $parameters): bool
    {
        return AclProxy::hasAccess($user, $class, $method, $parameters);
    }
}
