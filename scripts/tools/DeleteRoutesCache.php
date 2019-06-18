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
 */

namespace oat\tao\scripts\tools;

use \common_report_Report as Report;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\routing\AnnotationReader\route;
use oat\tao\model\routing\AnnotationReaderService;

/**
 * Deletes cache of "route" annotations of controller classes
 * usage: sudo -u www-data php index.php 'oat\tao\scripts\tools\DeleteRoutesCache'
 */
class DeleteRoutesCache extends ScriptAction
{
    protected function provideOptions()
    {
        return [
            'extension' => [
                'prefix' => 'ext',
                'longPrefix' => 'extension',
                'required' => false,
                'description' => 'Only for specified extension'
            ],
            'controller' => [
                'prefix' => 'c',
                'longPrefix' => 'controller',
                'required' => false,
                'description' => 'Only for specified controller (by full class name)'
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'Deletes routes annotations cache';
    }

    /**
     * @return Report
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    protected function run()
    {
        if ($this->hasOption('controller') && $this->hasOption('extension')) {
            return Report::createFailure('Specify either extension or controller option');
        }

        /** @var \common_ext_ExtensionsManager $extManager */
        $extManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);
        /** @var AnnotationReaderService $annotationReaderService */
        $annotationReaderService = $this->getServiceLocator()->get(AnnotationReaderService::SERVICE_ID);

        $targetMsg = '';
        $controllerClass = null;
        $specifiedExtension = null;

        if ($this->hasOption('controller')) {
            $controllerClass = $this->getOption('controller');
            $targetMsg = " for $controllerClass controller";
        }

        if ($this->hasOption('extension')) {
            $specifiedExtension = $extManager->getExtensionById($this->getOption('extension'));
            if ($specifiedExtension === null) {
                return Report::createFailure('Unknown extension id: '. $this->getOption('extension'));
            }
            $targetMsg = ' for ' . $specifiedExtension->getId() . ' extension';
        }

        $report = Report::createSuccess('Deleting route annotations cache entries' . $targetMsg);

        if ($controllerClass !== null) {
            $removed = $annotationReaderService->clearCache(
                    $controllerClass, AnnotationReaderService::METHODS_PUBLIC, route::class);
            $report->add(\common_report_Report::createSuccess($removed ? '1 key removed' : 'No keys removed'));
            return $report;
        }

        if ($specifiedExtension !== null) {
            $extensions = [$specifiedExtension];
        } else {
            $extensions = $extManager->getInstalledExtensions();
        }

        $keysRemoved = 0;
        foreach ($extensions as $extension) {
            /** @var \common_ext_Extension $extension */
            $dir = $extension->getDir();
            $controllersDirs = ['controller', 'actions'];
            foreach ($controllersDirs as $controllersDir) {
                $controllersDir = $dir . $controllersDir . DIRECTORY_SEPARATOR;
                if (!is_dir($controllersDir)) {
                    continue;
                }
                $controllerFiles = glob($controllersDir . '*.php');
                foreach ($controllerFiles as $controllerFile) {
                    $controllerClasses = $this->getClassesFromFile($controllerFile);
                    foreach ($controllerClasses as $controllerClass) {
                        $removed = $annotationReaderService->clearCache(
                            $controllerClass, AnnotationReaderService::METHODS_PUBLIC, route::class);
                        if ($removed) {
                            $report->add(\common_report_Report::createInfo($controllerClass));
                            ++$keysRemoved;
                        }
                    }
                }
            }
        }

        $report->add(\common_report_Report::createSuccess('Keys removed: ' . $keysRemoved));

        return $report;
    }

    /**
     * @param string $filePath
     * @return string[]
     */
    private function getClassesFromFile($filePath) {
        $phpcode = file_get_contents($filePath);
        $classes = [];
        $namespace = 0;
        $tokens = token_get_all($phpcode);
        $count = count($tokens);
        $dlm = false;
        for ($i = 2; $i < $count; $i++) {
            if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] === 'phpnamespace' || $tokens[$i - 2][1] === 'namespace')) ||
                ($dlm && $tokens[$i - 1][0] === T_NS_SEPARATOR && $tokens[$i][0] === T_STRING)) {
                if (!$dlm) {
                    $namespace = 0;
                }
                if (isset($tokens[$i][1])) {
                    $namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
                    $dlm = true;
                }
            }
            elseif ($dlm && ($tokens[$i][0] !== T_NS_SEPARATOR) && ($tokens[$i][0] !== T_STRING)) {
                $dlm = false;
            }
            if (($tokens[$i - 2][0] === T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] === 'phpclass'))
                && $tokens[$i - 1][0] === T_WHITESPACE && $tokens[$i][0] === T_STRING) {
                $class_name = $tokens[$i][1];
                $joinNamespace = $namespace !== 0 ? $namespace : '';
                $classes[] = $joinNamespace . '\\' . $class_name;
            }
        }
        return $classes;
    }
}
