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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\TaskLog\Decorator;

use oat\oatbox\user\User;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\taoBackOffice\controller\Redirector;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class RedirectUrlEntityDecorator extends TaskLogEntityDecorator
{
    /**
     * @var TaskLogInterface
     */
    private $taskLogService;

    /**
     * @var User
     */
    private $user;

    /**
     * RedirectUrlEntityDecorator constructor.
     * @param EntityInterface $entity
     * @param TaskLogInterface $taskLogService
     * @param User $user
     */
    public function __construct(EntityInterface $entity, TaskLogInterface $taskLogService, User $user)
    {
        parent::__construct($entity);
        $this->taskLogService = $taskLogService;
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Add 'redirectUrl' to the result if the task has been processed
     * and it is not an export or delete task
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();

        $deniedCategories = [
            TaskLogInterface::CATEGORY_DELETE,
            TaskLogInterface::CATEGORY_EXPORT,
            TaskLogInterface::CATEGORY_UNKNOWN
        ];

        if ( !in_array($this->taskLogService->getCategoryForTask($this->getTaskName()), $deniedCategories) &&
             ($this->getStatus()->isCompleted() || $this->getStatus()->isArchived()) ) {

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
                    'redirectUrl' => _url('redirectTaskToInstance', 'Redirector', 'taoBackOffice', $params)
                ]);
            } else {
                \common_Logger::w('User \''.$user->getIdentifier().'\' does not have access to redirectTaskToInstance');
            }
        }

        return $data;
    }

    /**
     * Check the ACL
     *
     * @param $user
     * @param $class
     * @param $method
     * @param $parameters
     * @return bool
     */
    protected function hasAccess($user, $class, $method, $parameters)
    {
        return AclProxy::hasAccess($user, $class, $method, $parameters);
    }
}
