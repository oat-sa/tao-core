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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\scripts\tools;

use common_ext_ExtensionsManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\install\utils\ConfigurationMarkers;
use oat\oatbox\service\ServiceManager;
use oat\generis\model\DependencyInjection\ContainerStarter;

/**
 * Usage
 * php index.php 'oat\tao\scripts\tools\RunConfigurationMarkers' -c path/to/seed.json
 * php index.php 'oat\tao\scripts\tools\RunConfigurationMarkers' -c path/to/seed.json -e generis
 */
class RunConfigurationMarkers extends ScriptAction
{
    private const OPTION_SELECT_EXTENSION_ID = 'select-extension-id';
    private const OPTION_SEED_FILE_PATH = 'seed-path';

    protected function provideOptions(): array
    {
        return [
            self::OPTION_SELECT_EXTENSION_ID => [
                'prefix' => 'e',
                'longPrefix' => self::OPTION_SELECT_EXTENSION_ID,
                'description' => 'Select single extensions config to change.',
            ],
            self::OPTION_SEED_FILE_PATH => [
                'prefix' => 's',
                'longPrefix' => self::OPTION_SEED_FILE_PATH,
                'description' => 'Path to seed file to use.',
            ],
        ];
    }

    protected function provideDescription(): string
    {
        return 'Remove secret data from extension\'s config file using markers in seed file';
    }

    protected function run(): Report
    {
        $container = (new ContainerStarter(ServiceManager::getServiceManager()))->getContainer();
        if ($this->hasOption(self::OPTION_SEED_FILE_PATH) === false) {
            return Report::createError(sprintf('Option %s is mandatory.', self::OPTION_SEED_FILE_PATH));
        }

        $filePath = $this->getOption(self::OPTION_SEED_FILE_PATH);
        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            return Report::createError('Empty seed file or wrong file path, aborting.');
        }
        $parameters = json_decode($fileContents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return Report::createError('Json file contains errors, aborting.');
        }

        $markers = new ConfigurationMarkers($_ENV, new \oat\tao\model\EnvPhpSerializableFactory(), $this->getLogger());
        $parameters = $markers->replaceMarkers($parameters);

        $updatedExtensions = [];

        /** @var common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $container->get(common_ext_ExtensionsManager::SERVICE_ID);
        foreach ($parameters['configuration'] as $extensionId => $configs) {
            $selectedExtension = $this->getOption(self::OPTION_SELECT_EXTENSION_ID);
            if ($selectedExtension !== null && $selectedExtension !== $extensionId) {
                continue;
            }
            try {
                $installedExtension = $extensionManager->getExtensionById($extensionId);
            } catch (\common_ext_ExtensionException $e) {
                return Report::createError(sprintf('Extension %s is not installed, aborting.', $extensionId));
            }
            foreach ($configs as $key => $config) {
                if (!(isset($config['type']) && $config['type'] === 'configurableService')) {
                    if ($installedExtension->hasConfig($key)) {
                        try {
                            $installedExtension->setConfig($key, $config);
                            $updatedExtensions[] = $extensionId;
                        } catch (\common_exception_Error $e) {
                            return Report::createError(
                                sprintf('Your config %s/%s cannot be set, aborting', $extensionId, $key)
                            );
                        }
                    }
                }
            }
        }

        return Report::createSuccess(
            sprintf(
                'Configuration of extension(s) %s completed successfully.',
                implode(',', $updatedExtensions)
            )
        );
    }
}
