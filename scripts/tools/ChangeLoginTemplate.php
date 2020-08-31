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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools;

use Throwable;
use common_report_Report as Report;
use oat\oatbox\extension\script\ScriptAction;
use common_ext_ExtensionsManager as ExtensionsManager;

/**
 * Class ChangeLoginTemplate
 *
 * @package oat\tao\scripts\tools
 *
 * @example php index.php "oat\tao\scripts\tools\ChangeLoginTemplate" -p blocks\login.tpl -e tao
 */
class ChangeLoginTemplate extends ScriptAction
{
    /**
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'path' => [
                'prefix' => 'p',
                'longPrefix' => 'path',
                'required' => true,
                'description' => 'Relative path to the template.'
            ],
            'extension' => [
                'prefix' => 'e',
                'longPrefix' => 'extension',
                'required' => true,
                'description' => 'Extension to receive the template.'
            ],
        ];
    }

    /**
     * @return string
     */
    protected function provideDescription()
    {
        return 'Use this script to change login template.';
    }

    /**
     * @return Report
     */
    protected function run()
    {
        $path = $this->getOption('path');
        $ext = $this->getOption('extension');

        try {
            /** @var ExtensionsManager $extensionManager */
            $extensionManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
            $extension = $extensionManager->getExtensionById('tao');

            $config = $extension->getConfig('login');
            $config['template'] = [$path, $ext];

            $extension->setConfig('login', $config);
        } catch (Throwable $exception) {
            $script = sprintf('php index.php "%s" -p %s -e %s', self::class, $path, $ext);

            return new Report(
                Report::TYPE_WARNING,
                sprintf(
                    'Login template was not configured (%s). Fix the error and execute this script manually: %s',
                    $exception->getMessage(),
                    $script
                )
            );
        }

        return new Report(Report::TYPE_SUCCESS, 'Login template was successfully configured.');
    }
}
