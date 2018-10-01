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

namespace oat\tao\test\integration\actionQueue;

use oat\tao\model\actionQueue\implementation\InstantActionQueue;
use oat\tao\model\actionQueue\AbstractQueuedAction;
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\oatbox\service\ServiceManager;
use \core_kernel_users_GenerisUser as GenerisUser;

/**
 * Class InstantActionTest
 * @package oat\tao\test\integration\datatable
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class InstantActionTest extends TaoPhpUnitTestRunner
{

    public function testPerform()
    {
        $user = new GenerisUser(new \core_kernel_classes_Resource(\common_Utils::getNewUri()));
        $actionQueue = $this->getInstance();

        $action = new GetmypidAction();

        $this->assertTrue($actionQueue->perform($action, $user));
        $this->assertEquals(getmypid(), $action->getResult());

        $action->activeActions = 10;
        $this->assertFalse($actionQueue->perform($action, $user));


        $action->activeActions = 9;
        $this->assertTrue($actionQueue->perform($action, $user));
    }

    /**
     * @todo fix test
     */
    public function testGetPosition()
    {
        $actionQueue = $this->getInstance();
        $action = new GetmypidAction();
        $action->activeActions = 10;
        $user_1 = new GenerisUser(new \core_kernel_classes_Resource(\common_Utils::getNewUri()));
        $user_2 = new GenerisUser(new \core_kernel_classes_Resource(\common_Utils::getNewUri()));

        $actionQueue->perform($action, $user_1);
        $this->assertEquals(1, $actionQueue->getPosition($action));

        $actionQueue->perform($action, $user_1);
        $this->assertEquals(1, $actionQueue->getPosition($action));

        $actionQueue->perform($action, $user_2);
        $this->assertEquals(2, $actionQueue->getPosition($action));

        $action->activeActions = 1;

        $actionQueue->perform($action, $user_1);
        $this->assertEquals(1, $actionQueue->getPosition($action));

        $actionQueue->perform($action, $user_2);
        $this->assertEquals(0, $actionQueue->getPosition($action));

        $actionQueue->perform($action, $user_2);
        $this->assertEquals(0, $actionQueue->getPosition($action));
    }

    /**
     * @todo fix test
     */
    public function testClearAbandonedPositions()
    {
        $user_1 = new GenerisUser(new \core_kernel_classes_Resource(\common_Utils::getNewUri()));
        $user_2 = new GenerisUser(new \core_kernel_classes_Resource(\common_Utils::getNewUri()));
        $actionQueue = $this->getInstance();
        $action = new GetmypidAction();
        $action->activeActions = 10;

        $actionQueue->perform($action, $user_1);
        $this->assertEquals(1, $actionQueue->getPosition($action, $user_1));

        sleep(1);
        $actionQueue->perform($action, $user_2);
        $this->assertEquals(2, $actionQueue->getPosition($action, $user_2));

        $actionQueue->clearAbandonedPositions($action);
        $this->assertEquals(1, $actionQueue->getPosition($action, $user_1));

        sleep(1);
        $actionQueue->clearAbandonedPositions($action);
        $this->assertEquals(0, $actionQueue->getPosition($action, $user_2));
    }

    /**
     * @todo fix test
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
                    InstantActionQueue::ACTION_PARAM_LIMIT => 10,
                    InstantActionQueue::ACTION_PARAM_TTL => 1,
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

class GetmypidAction extends AbstractQueuedAction
{
    public $activeActions = 0;

    public function __invoke($params = null)
    {
        return getmypid();
    }

    public function getNumberOfActiveActions()
    {
        return $this->activeActions;
    }
}