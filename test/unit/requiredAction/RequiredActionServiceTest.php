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

namespace oat\tao\test\unit\requiredAction;

use PHPUnit\Framework\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\requiredAction\implementation\RequiredActionRedirectUrlPart;
use oat\tao\model\requiredAction\implementation\RequiredActionService;
use oat\tao\model\requiredAction\implementation\TimeRule;

class RequiredActionServiceTest extends TestCase
{
    public function testAttachAction()
    {
        $serviceManager = $this->getMockServiceManager();
        $requiredActionService = new RequiredActionService();
        $requiredActionService->setServiceLocator($serviceManager);

        $requiredActionService->attachAction($this->getTestRequiredAction());
        $requiredAction = $requiredActionService->getRequiredAction('testRequiredAction');
        $this->assertEquals($requiredAction->getName(), 'testRequiredAction');
    }


    public function testDetachAction()
    {
        $serviceManager = $this->getMockServiceManager();
        $requiredActionService = new RequiredActionService();
        $requiredActionService->setServiceLocator($serviceManager);

        $requiredActionService->attachAction($this->getTestRequiredAction());
        $requiredAction = $requiredActionService->getRequiredAction('testRequiredAction');
        $this->assertEquals($requiredAction->getName(), 'testRequiredAction');

        $requiredActionService->detachAction('testRequiredAction');
        $requiredAction = $requiredActionService->getRequiredAction('testRequiredAction');
        $this->assertNull($requiredAction);
    }

    private function getTestRequiredAction()
    {
        return new RequiredActionRedirectUrlPart(
            'testRequiredAction',
            [
                new TimeRule(),
            ],
            []
        );
    }

    private function getPersistenceManager()
    {
        return new \common_persistence_Manager([
            'persistences' => [
                'test' => [
                    'driver' => 'no_storage'
                ],
            ]
        ]);
    }

    private function getMockServiceManager()
    {
        $config = new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver());
        $config->set(\common_persistence_Manager::SERVICE_ID, $this->getPersistenceManager());
        return new ServiceManager($config);
    }
}
