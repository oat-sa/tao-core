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
 */

use oat\tao\model\TaskQueueActionTrait;

/**
 * Rest API controller for task queue
 *
 * Class tao_actions_TaskQueue
 * @package oat\tao\controller\api
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 *
 * @deprecated Please use the new endpoint in taoTaskQueue extension
 */
class tao_actions_TaskQueue extends \tao_actions_RestController
{

    use TaskQueueActionTrait;

    const TASK_ID_PARAM = 'id';

    /**
     * Get task data by identifier
     */
    public function get()
    {
        try {
            if (!$this->hasRequestParameter(self::TASK_ID_PARAM)) {
                throw new \common_exception_MissingParameter(self::TASK_ID_PARAM, $this->getRequestURI());
            }

            $taskId = $this->getRequestParameter(self::TASK_ID_PARAM);

            /** @var \common_ext_ExtensionsManager $extensionManager */
            $extensionManager = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID);

            // get data from the new task queue
            if ($extensionManager->isInstalled('taoTaskQueue')) {
                /** @var \oat\taoTaskQueue\model\TaskLogInterface $taskLog */
                $taskLog = $this->getServiceManager()->get(\oat\taoTaskQueue\model\TaskLogInterface::SERVICE_ID);

                $filter = (new \oat\taoTaskQueue\model\TaskLog\TaskLogFilter())
                    ->eq(\oat\taoTaskQueue\model\TaskLogBroker\TaskLogBrokerInterface::COLUMN_ID, $taskId);

                $collection = $taskLog->search($filter);

                if ($collection->isEmpty()) {
                    // if we don't have the task in the new queue, try to load the data from the old one
                    $data = $this->getTaskData($taskId);
                } else {
                    $entity = $collection->first();
                    $status = (string)$entity->getStatus();

                    if ($entity->getStatus()->isInProgress()) {
                        $status = \oat\oatbox\task\Task::STATUS_RUNNING;
                    } elseif ($entity->getStatus()->isCompleted() || $entity->getStatus()->isFailed()) {
                        $status = \oat\oatbox\task\Task::STATUS_FINISHED;
                    }

                    $data['id'] = $entity->getId();
                    $data['status'] = $status;
                    //convert to array for xml response.
                    $data['report'] = json_decode(json_encode($entity->getReport()));
                }
            } else {
                // load data from the old queue
                $data = $this->getTaskData($taskId);
            }

            $this->returnSuccess($data);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }
}
