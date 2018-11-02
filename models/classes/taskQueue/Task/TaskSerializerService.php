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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\taskQueue\Task;

use oat\oatbox\action\ActionService;
use oat\oatbox\action\ResolutionException;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class TaskSerializerService extends ConfigurableService
{
    const SERVICE_ID = 'tao/TaskSerializer';

    use LoggerAwareTrait;
    use ServiceLocatorAwareTrait;

    /**
     * @param string $taskJSON
     * @param array $logContext
     * @return null|TaskInterface
     * @throws \Exception
     */
    public function deserialize($taskJSON, array $logContext = [])
    {
        $basicData = json_decode($taskJSON, true);
        $this->assertValidJson($basicData);

        $task = TaskFactory::build($basicData);

        if ($task instanceof CallbackTaskInterface && is_string($task->getCallable())) {
            $this->handleCallbackTask($task, $logContext);
        }

        return $task;
    }

    /**
     * @param TaskInterface $task
     * @return false|string
     */
    public function serialize(TaskInterface $task)
    {
        return json_encode($task);
    }

    /**
     * @param $basicData
     * @throws \Exception
     */
    protected function assertValidJson($basicData)
    {
        if ( ($basicData !== null
                && json_last_error() === JSON_ERROR_NONE
                && isset($basicData[TaskInterface::JSON_TASK_CLASS_NAME_KEY])) === false
        ) {
            throw new \Exception();
        }
    }

    /**
     * @param CallbackTaskInterface $task
     * @param array $logContext
     * @throws \Exception
     */
    protected function handleCallbackTask(CallbackTaskInterface $task, array $logContext)
    {
        try {
            $callable = $this->getActionResolver()->resolve($task->getCallable());

            if ($callable instanceof ServiceLocatorAwareInterface) {
                $callable->setServiceLocator($this->getServiceLocator());
            }

            $task->setCallable($callable);
        } catch (ResolutionException $e) {

            $this->logError('Callable/Action class ' . $task->getCallable() . ' does not exist', $logContext);

            throw new \Exception;
        }
    }

    /**
     * @return ActionService|ConfigurableService|object
     */
    protected function getActionResolver()
    {
        return $this->getServiceLocator()->get(ActionService::SERVICE_ID);
    }
}