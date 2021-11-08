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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */
declare(strict_types=1);

namespace oat\tao\scripts\tools\e2e;

use common_ext_Extension;
use common_ext_ExtensionsManager;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\e2e\models\E2eConfigDriver;
use stdClass;

class PrepareEnvironment extends ScriptAction
{

    public function getConfigPath(string $configEnv = ''): string
    {
        return sprintf('%s/tao/views/cypress/envs/env%s.json', ROOT_PATH, $configEnv);
    }

    protected function provideUsage(): array
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Type this option to see the parameters.'
        ];
    }

    protected function provideOptions()
    {
        return [
            'adminPassword' => [
                'prefix' => 'p',
                'longPrefix' => 'adminPassword',
                'required' => true,
                'description' => 'Specify admin password'
            ],
            'adminAccount' => [
                'prefix' => 'u',
                'longPrefix' => 'adminAccount',
                'defaultValue' => 'admin',
                'description' => 'Specify admin account name'
            ],
            'extensionList' => [
                'prefix' => 'e',
                'longPrefix' => 'extensionList',
                'required' => false,
                'default' => 'tao',
                'description' => 'Specific list of extension involved for E2E configuring'
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Automate setup of E2E environments';
    }

    protected function run()
    {
        $report = Report::createInfo('E2E environment configuration setup');
        $actions = $this->getActionsList();

        $this->buildConfigFoundation($this->getConfigPath());
        foreach ($actions as $action) {
            $action = $this->propagate(new $action);
            $report->add(($action)([]));
        }
        return $report;
    }

    /**
     * @return AbstractAction[]
     */
    private function getActionsList(): array
    {
        $actions = [];
        $extensionList = $this->getExtensions();
        foreach ($extensionList as $extension) {
            $actions = array_merge($actions, $extension->getManifest()->getE2ePrerequisiteActions());
        }
        return $actions;
    }

    /**
     * @return common_ext_Extension[]
     */
    private function getExtensions(): array
    {
        $result = [];
        foreach (explode(',', $this->getOption('extensionList')) as $extensionName) {
            $result[] = $this->getExtensionManager()->getExtensionById(trim($extensionName));
        }
        return $result;
    }

    private function buildConfigFoundation(string $configPath): void
    {
        $config = new stdClass();
        $config->baseUrl = ROOT_URL;
        $config->adminUser = $this->getOption('adminAccount');
        $config->adminPass = $this->getOption('adminPassword');

        $this->getConfigDriver()->setConfigPath($configPath)->append($config);
    }

    private function getConfigDriver(): E2eConfigDriver
    {
        return new E2eConfigDriver();
    }

    private function getExtensionManager(): common_ext_ExtensionsManager
    {
        return $this->getServiceLocator()->getContainer()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }
}
