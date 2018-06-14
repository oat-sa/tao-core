# Task Queue

> This article describes the mechanism of a SYNC Queue. If you need Async Queue, install `oat-sa/extension-tao-task-queue`.

## Components

The task queue system is built on the following components.

### Queue Dispatcher component

The Dispatcher can hold multiple Queue instances to achieve the priority mechanism. Each Queue is associated to a certain priority level.
There is an option for setting the default Queue. If it is not set, the first queue will be used as default.

This is the _**main service**_ to be used for interacting with the queue system.

Tasks can be linked to a specific Queue, meaning they will always be published into that Queue. This can be achieved by two ways:
- adding the task full name and the queue to OPTION_TASK_TO_QUEUE_ASSOCIATIONS
- using `\oat\tao\model\taskQueue\Task\QueueAssociableInterface` in your Action/Task. You will have the freedom inside of your action in runtime 
to decide which queue you want your task to be published to. The parameters used by your action are passed to that method.

This can be achieved by adding the task full name and the queue to OPTION_TASK_TO_QUEUE_ASSOCIATIONS

_Note_: 
>If there is no queue defined for a task than it will be published into the default Queue.

There can be set only one Queue as well. In that case every task will be published into that one.

### Queue component

Queue can work with different types of queue brokers, here only one is implemented:
- **InMemoryQueueBroker** which accomplishes the Sync Queue mechanism. Tasks will be executing straightaway after adding them into the queue.

_Note_: 
> If you need Async Queue, install `oat-sa/extension-tao-task-queue` which brings extra type of brokers.

#### Weight
A Queue can have a weight. If multiple Queues are in use, this weight will be used for randomly select a Queue to be consumed. 
For example, if QueueA has weight of 1 and QueueB has weight of 2, then QueueB has about a 66% chance of being selected.

### Worker component

There is a simple implementation of a worker - called `OneTimeWorker` - to consume a queue only time, aka. running the latest task. 

After processing the given task, the worker saves the generated report for the task through the Task Log.

### Task Log component

It's responsible for managing the lifecycle of Tasks. Can be accessed as a service. 
It stores the statuses, the generated report and some other useful metadata of the tasks.
To access those data, you can use the `search` function which takes a `TaskLogFilter` as an argument
or `getDataTablePayload` method if you want to handle the standard datatable request.

Its main duty is preventing of running the same task by multiple workers at the same time. 

It can also have multiple brokers extending TaskLogBrokerInterface to store the data in different type of storage system. 
Currently we have **RdsTaskLogBroker** which uses RDS.


## Service setup examples

### Sync Queue settings

Basic solution with only one Queue which uses InMemoryQueueBroker.

```php
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Queue;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\Queue\Broker\InMemoryQueueBroker;

$queueService = new QueueDispatcher(array(
    QueueDispatcherInterface::OPTION_QUEUES => [
        new Queue('queue', new InMemoryQueueBroker()),
    ],
    QueueDispatcherInterface::OPTION_TASK_LOG     => TaskLogInterface::SERVICE_ID,
    QueueDispatcherInterface::OPTION_TASK_TO_QUEUE_ASSOCIATIONS => []
));

$this->getServiceManager()->register(QueueDispatcherInterface::SERVICE_ID, $queueService);
```

### Task Log settings
```php
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\TaskLog\Broker\RdsTaskLogBroker;

$taskLogService = new TaskLog([
    TaskLogInterface::OPTION_TASK_LOG_BROKER => new RdsTaskLogBroker('default', 'task_log')
]);
$this->getServiceManager()->register(TaskLogInterface::SERVICE_ID, $taskLogService);
```

If the task log container has not been created yet:
```php
try {
    $taskLogService->createContainer();
} catch (\Exception $e) {
    return \common_report_Report::createFailure('Creating task log container failed');
}
```

## Usage examples

- Getting the queue service as usual:

```php
$queueService = $this->getServiceManager()->get(\oat\tao\model\taskQueue\QueueDispatcherInterface::SERVICE_ID);
```

### Working with Task

There is two ways to create and publish a task.

- **First option**: creating a task class extending \oat\taoTaskQueue\model\AbstractTask. It's a new way, use it if you like it and if you don't need the possibility to run your task as an Action from CLI.
```php
<?php

use \common_report_Report as Report;
use oat\tao\model\taskQueue\Task\AbstractTask;

class MyFirstTask extends AbstractTask
{
    // constants for the param keys
    const PARAM_TEST_URI = 'test_uri';
    const PARAM_DELIVERY_URI = 'delivery_uri';

    /**
     * As usual, the magic happens here.
     * It needs to return a Report object. 
     */
    public function __invoke()
    {
        // you get the parameter using getParameter() with the required key
        if (!$this->getParameter(self::PARAM_TEST_URI) || !$this->getDeliveryUri()) {
            return Report::createFailure('Missing parameters');
        }

        $report = Report::createSuccess();
        $report->setMessage("I worked with Test ". $this->getParameter(self::PARAM_TEST_URI) ." and Delivery ". $this->getDeliveryUri());

        return $report;
    }

    /**
     * You can create a custom setter for your parameter.
     *
     * @param $uri
     */
    public function setDeliveryUri($uri)
    {
        // doing some validation
        // if it's a valid delivery
        $this->setParameter(self::PARAM_DELIVERY_URI, $uri);
    }

    /**
     * You can create a custom getter for your parameter.
     *
     * @return mixed
     */
    public function getDeliveryUri()
    {
        return $this->getParameter(self::PARAM_DELIVERY_URI);
    }
}
```

Then you can initiate your class and setting the required parameters and finally publish it:
```php
$myTask = new MyFirstTask();
$myTask->setParameter(MyFirstTask::PARAM_TEST_URI, 'http://taotesting.com/tao.rdf#i1496838551505670');
$myTask->setDeliveryUri('http://taotesting.com/tao.rdf#i1496838551505110');

if ($queue->enqueue($myTask, 'Label for the task')) {
    echo "Successfully published";
}
```

- **Second option**: Using Command/Action objects which implement \oat\oatbox\action\Action. This is the usual old way and more preferable because we can run those actions from CLI if needed.

```php
$task = $queue->createTask(new RegeneratePayload(), array($delivery->getUri()), 'Fancy Label');
if ($task->isEnqueued()) {
    echo "Successfully published";
}
```

As you can see, nothing has changed here. It is the same like before. The magic is behind of the createTask() method. Look into it if you dare...

Anyway, the main thing here is that a wrapper class called \oat\taoTaskQueue\model\CallbackTask is used to wrap your Action object and make it consumable for the queue system.

If you want to specify the queue the task should be published to, implement `\oat\taoTaskQueue\model\QueueAssociableInterface` in your action or
use `$queueService->linkTaskToQueue(CompileDelivery::class, 'queue_name');` in your updater script.

If you want to use the task id or even the entire task object inside of your Action class, you can use `\oat\taoTaskQueue\model\Task\TaskAwareInterface` 
and `\oat\taoTaskQueue\model\Task\TaskAwareTrait`.

If you want to define parent-child relation between tasks, you can use `\oat\taoTaskQueue\model\Task\ChildTaskAwareInterface` and `\oat\taoTaskQueue\model\Task\ChildTaskAwareTrait`.
Simply create the new child task as usual inside of your task and save the child id calling `$this->addChildId($childTaskId);` 
The worker is able to recognise whether the task has children or not and it sets the status of the parent task accordingly.
Once every child task has been processed, the status of the parent task will be set
 - to `completed`: if all children have completed status.
 - to `failed`: if there is at least one failed child.
 
A specific interface named `\oat\taoTaskQueue\model\Task\RemoteTaskSynchroniserInterface` can be used if you want to synchronise the status of your task
with the status of a remote task. The worker can recognise this interface and run this task until it receives `completed` or `failed` status. 

#### Working with Task Log component

Mostly, it can be used when the queue is used as Sync Queue and you want to get the status and the report for a task:

```php
/** @var \oat\taoTaskQueue\model\TaskLogInterface $taskLogService */
$taskLogService = $this->getServiceManager()->get(\oat\taoTaskQueue\model\TaskLogInterface::SERVICE_ID);

// checking the status for STATUS_COMPLETED can prevent working with a null report if InMemoryQueueBroker not used anymore.
if ($task->isEnqueued() && $taskLogService->getStatus($task->getId()) == TaskLogInterface::STATUS_COMPLETED) {
    $report = $taskLogService->getReport($task->getId());
}
```

Or we can just simply check if the current queue is a sync one so we have a report surely.

```php
if ($queueService->isSync()) {
    $report = $taskLogService->getReport($task->getId());
}
```

Let's use `search` for gaining some task log entities.

```php
use \oat\taoTaskQueue\model\TaskLog\TaskLogFilter;
use \oat\taoTaskQueue\model\TaskLogBroker\TaskLogBrokerInterface;

$filter = (new TaskLogFilter())
    ->addAvailableFilters(\common_session_SessionManager::getSession()->getUserUri())
    ->eq(TaskLogBrokerInterface::COLUMN_TASK_NAME, GenerateBillingReport::class)
    ->in(TaskLogBrokerInterface::COLUMN_STATUS, [TaskLogInterface::STATUS_ENQUEUED, TaskLogInterface::STATUS_DEQUEUED, TaskLogInterface::STATUS_RUNNING]);

    
$scheduledTasks = $taskLogService->search($filter);    
```