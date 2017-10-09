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

namespace oat\tao\test\actionQueue;

use oat\tao\model\actionQueue\implementation\InstantActionQueue;
use oat\tao\model\actionQueue\AbstractAction;
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\oatbox\service\ServiceManager;

/**
 * Class InstantActionTest
 * @package oat\tao\test\datatable
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class InstantActionTest extends TaoPhpUnitTestRunner
{

    public function testPerform()
    {
        $actionQueue = $this->getInstance();

        $action = new GetmypidAction();

        $this->assertTrue($actionQueue->perform($action));
        $this->assertEquals(getmypid(), $action->getResult());

        $action->activeActions = 10;
        $this->assertFalse($actionQueue->perform($action));


        $action->activeActions = 9;
        $this->assertTrue($actionQueue->perform($action));
    }

    public function testGetPosition()
    {
        $actionQueue = $this->getInstance();
        $action = new GetmypidAction();
        $action->activeActions = 10;
        $actionQueue->perform($action);

        $this->assertEquals(1, $actionQueue->getPosition($action));

        $actionQueue->perform($action);
        $this->assertEquals(2, $actionQueue->getPosition($action));

        $action->activeActions = 1;

        $actionQueue->perform($action);
        $this->assertEquals(1, $actionQueue->getPosition($action));

        $actionQueue->perform($action);
        $this->assertEquals(0, $actionQueue->getPosition($action));

        $actionQueue->perform($action);
        $this->assertEquals(0, $actionQueue->getPosition($action));
    }

    /**
     * @expectedException \oat\tao\model\actionQueue\ActionQueueException
     */
    public function testPerformException()
    {
        $actionQueue = $this->getInstance();
        $actionQueue->setOption(InstantActionQueue::OPTION_ACTIONS, []);
        $action = new GetmypidAction();
        $actionQueue->perform($action);
    }

    /**
     * @return InstantActionQueue
     */
    protected function getInstance()
    {
        $result = new InstantActionQueue([
            InstantActionQueue::OPTION_PERSISTENCE => 'action_queue',
            InstantActionQueue::OPTION_ACTIONS => [
                GetmypidAction::class => [
                    InstantActionQueue::ACTION_PARAM_LIMIT => 10
                ]
            ]
        ]);

        $persistenceManager = new \common_persistence_Manager([
            \common_persistence_Manager::OPTION_PERSISTENCES => [
                'action_queue' => [
                    'driver' => 'no_storage' //in memory storage
                ]
            ]
        ]);
        $config = new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver());
        $config->set(\common_persistence_Manager::SERVICE_ID, $persistenceManager);
        $serviceManager = new ServiceManager($config);
        $result->setServiceManager($serviceManager);
        return $result;
    }

}

class GetmypidAction extends AbstractAction
{
    public $activeActions = 0;

    public function __invoke($params = null)
    {
        return getmypid();
    }

    public function getId()
    {
        return self::class;
    }

    public function getNumberOfActiveActions()
    {
        return $this->activeActions;
    }
}