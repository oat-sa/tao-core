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

namespace oat\tao\test\integration\actionSerializer;

use oat\generis\test\TestCase;
use oat\tao\model\actionSerializer\RdsActionSerializer;
use oat\oatbox\service\ServiceManager;

/**
 * Class RdsActionSerializerTest
 * @package oat\tao\test\integration\actionSerializer
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RdsActionSerializerTest extends TestCase
{

    public function testLock()
    {
        $actionId1 = 'action_1';
        $actionId2 = 'action_2';
        $sleep = 3;
        $this->getInstance();
        $time = time();
        $pipe1 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId1 . ' ' . $sleep , 'w');
        $pipe2 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId1 . ' ' . $sleep , 'w');
        $pipe3 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId1 . ' ' . $sleep , 'w');
        $pipe4 = popen('php ' . __DIR__ . DIRECTORY_SEPARATOR . 'test_action.php ' . $actionId2 . ' ' . $sleep , 'w');
        pclose($pipe1);
        pclose($pipe2);
        pclose($pipe3);
        pclose($pipe4);
        $this->assertTrue((time() - $time) >= ($sleep*3));
        $this->assertTrue((time() - $time) < ($sleep*4));
    }

    /**
     * @return RdsActionSerializer
     * @throws \common_Exception
     */
    public function getInstance()
    {
        $service = new RdsActionSerializer([
            RdsActionSerializer::OPTION_PERSISTENCE => 'default'
        ]);
        $persistence = ServiceManager::getServiceManager()
            ->get(\common_persistence_Manager::SERVICE_ID)
            ->getPersistenceById('default');
        RdsActionSerializer::install($persistence);
        $service->setServiceLocator(ServiceManager::getServiceManager());
        return $service;
    }

}