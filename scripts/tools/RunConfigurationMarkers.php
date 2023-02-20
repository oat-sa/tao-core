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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools;

use common_ext_ExtensionsManager;
use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\configurationMarkers\ConfigurationMarkers;
use oat\oatbox\service\ServiceManager;
use oat\generis\model\DependencyInjection\ContainerStarter;

/**
 * Usage
 * php index.php 'oat\tao\scripts\tools\RunConfigurationMarkers' -s path/to/seed.json
 * php index.php 'oat\tao\scripts\tools\RunConfigurationMarkers' -s path/to/seed.json -e generis
 */
class RunConfigurationMarkers extends ScriptAction
{
    use ServiceLocatorAwareTrait;

    private const OPTION_SELECT_EXTENSION_ID = 'select-extension-id';
    private const OPTION_SEED_FILE_PATH = 'seed-path';
    private Report $report;

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
        $this->report = Report::createInfo('Starting.');
        if ($this->hasOption(self::OPTION_SEED_FILE_PATH) === false) {
            return Report::createError(sprintf('Option %s is mandatory.', self::OPTION_SEED_FILE_PATH));
        }

        $filePath = $this->getOption(self::OPTION_SEED_FILE_PATH);
        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            $this->report->add(Report::createError('Empty seed file or wrong file path, aborting.'));

            return $this->report;
        }
        $parameters = json_decode($fileContents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError(
                sprintf(
                    'Json file malformed, last json error : %s , message: %s',
                    json_last_error(),
                    json_last_error_msg()
                )
            );
            $this->report->add(
                Report::createError('Json file contains errors see logs for details, aborting.')
            );

            return $this->report;
        }

        $parameters = $this->getConfigurationMarkers()->replaceMarkers($parameters);

        foreach ($parameters['configuration'] as $extensionId => $configs) {
            $processed = $this->processExtension($extensionId, $configs);
            if ($processed) {
                $reportMessage = Report::createSuccess(
                    sprintf('Extension %s processed successfully.', $extensionId)
                );
            } else {
                $reportMessage = Report::createError(sprintf('Failed to process extension %s .', $extensionId));
            }
            $this->report->add($reportMessage);
        }

        return $this->report;
    }

    private function processExtension(string $extensionId, array $configs): bool
    {
        $extensionManager = $this->getExtensionManager();
        $selectedExtension = $this->getOption(self::OPTION_SELECT_EXTENSION_ID);
        if ($selectedExtension !== null && $selectedExtension !== $extensionId) {
            $this->report->add(Report::createInfo(sprintf('Skipping extension %s .', $extensionId)));

            return true;
        }
        if ($extensionManager->isInstalled($extensionId)) {
            $installedExtension = $extensionManager->getExtensionById($extensionId);
        } else {
            $this->report->add(
                Report::createError(sprintf('Extension %s is not installed, aborting.', $extensionId))
            );

            return false;
        }
        foreach ($configs as $key => $config) {
            $this->updateConfiguration($key, $config, $installedExtension);
        }

        return true;
    }

    private function updateConfiguration(string $key, array $config, \common_ext_Extension $installedExtension): void
    {
        if (isset($config['type']) && $config['type'] !== 'configurableService') {
            return;
        }

        if ($installedExtension->hasConfig($key) === false) {
            $this->report->add(
                Report::createError(
                    sprintf('Extension: %s has no config key: %s', $installedExtension->getName(), $key)
                )
            );

            return;
        }
        try {
            $installedExtension->setConfig($key, $config);
        } catch (\common_exception_Error $e) {
            $this->logError(sprintf('Exception throw: %e', $e->getMessage()));
            $this->report->add(Report::createError(
                sprintf(
                    'Config key %s for extension %s cannot be set check logs for errors. Aborting',
                    $key,
                    $installedExtension->getName()
                )
            ));
        }

        $this->report->add(
            Report::createSuccess(
                sprintf('Configuration of extension %s completed successfully.', $installedExtension->getName())
            )
        );
    }

    private function getExtensionManager(): common_ext_ExtensionsManager
    {
        return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }

    private function getConfigurationMarkers(): ConfigurationMarkers
    {
        $container = $this->getServiceLocator()->getContainer();
        return $container->get(ConfigurationMarkers::class);
    }
}
