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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\model\taskQueue\Worker;

use oat\generis\test\TestCase;

use common_report_Report as Report;
use oat\tao\model\taskQueue\QueuerInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\Worker\AbstractWorker;

class AbstractWorkerTest extends TestCase
{
    /** @var QueuerInterface */
    private $queue;

    /** @var TaskLogInterface */
    private $taskLog;

    /** @var DummyWorker */
    private $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->queue = $this->createMock(QueuerInterface::class);
        $this->taskLog = $this->createMock(TaskLogInterface::class);

        $this->subject = new DummyWorker($this->queue, $this->taskLog);
    }

    public function testProcessCancelledTask()
    {
       $task = $this->createMock(TaskInterface::class);
       $task
           ->method('getId')
           ->willReturn('id');

       $this->taskLog
           ->expects($this->once())
           ->method('getStatus')
           ->with('id')
           ->willReturn(TaskLogInterface::STATUS_CANCELLED);
       $this->taskLog
           ->expects($this->once())
           ->method('setReport')
           ->with(
               'id',
               Report::createInfo('Task id has been cancelled, message was not processed.'),
               TaskLogInterface::STATUS_CANCELLED
           );

       $this->queue->expects($this->once())->method('acknowledge')->with($task);

       $this->assertEquals(TaskLogInterface::STATUS_CANCELLED, $this->subject->processTask($task));
    }
}

class DummyWorker extends AbstractWorker
{
    public function run()
    {
        return null;
    }
};
