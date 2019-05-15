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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\test\integration\mutex;

use oat\generis\test\TestCase;
use oat\tao\model\mutex\LockService;
use oat\oatbox\service\ServiceManager;

/**
 * Class LockServiceTest
 * @package oat\tao\test\integration\mutex
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class LockServiceTest extends TestCase
{

    public function testLock()
    {
        $actionId1 = 'action_1';
        $actionId2 = 'action_2';
        $sleep = 3;
        $this->getInstance();
        $time = time();
        $pipe1 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId1 . ' ' . $sleep . ' 0', 'w');
        $pipe2 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId1 . ' ' . $sleep . ' 0', 'w');
        $pipe3 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId1 . ' ' . $sleep . ' 0', 'w');
        $pipe4 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId2 . ' ' . $sleep . ' 0', 'w');
        pclose($pipe1);
        pclose($pipe2);
        pclose($pipe3);
        pclose($pipe4);
        $this->assertTrue((time() - $time) >= ($sleep*3));
        $this->assertTrue((time() - $time) < ($sleep*4));
    }

    public function testLockTimeout()
    {
        $actionId1 = 'action_1';
        $sleep = 5;
        $timeout = 2;
        $time = time();
        $pipe1 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId1 . ' ' . $sleep . ' ' . $timeout, 'w');
        $pipe2 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId1 . ' ' . $sleep . ' ' . $timeout, 'w');
        pclose($pipe1);
        pclose($pipe2);

        /**
         * Process A:
         * acquireLock      timeout              close
         *      |      2 sec   |    3 sec          |
         *      ------------------------------------
         *
         * Process B:
         * acquireLock       start                              close
         *      |      wait    |    5 sec                         |
         *                     ------------------------------------
         *
         * Total time:
         * |   2 + 5 = 7 seconds                                  |
         * --------------------------------------------------------
         */

        $consumedTime = time() - $time;

        $this->assertTrue($consumedTime > $sleep);
        $this->assertTrue($consumedTime < ($sleep * 2));
    }

    /**
     * @return LockService
     * @throws \common_exception_NotImplemented
     */
    public function getInstance()
    {
        $service = new LockService([
            LockService::OPTION_PERSISTENCE => 'default'
        ]);
        $service->setServiceLocator(ServiceManager::getServiceManager());
        $service->install();
        return $service;
    }

}