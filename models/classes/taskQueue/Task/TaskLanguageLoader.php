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
 * Copyright (c) 2022 Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\model\taskQueue\Task;

use common_ext_Extension;
use common_ext_ExtensionException;
use common_ext_ExtensionsManager;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\UserLanguageServiceInterface;
use ReflectionClass;
use tao_helpers_I18n;
use Throwable;

class TaskLanguageLoader extends ConfigurableService implements TaskLanguageLoaderInterface
{
    public function loadTranslations(TaskInterface $task): void
    {
        try {
            tao_helpers_I18n::init(
                $extension = $this->getTaskExtension($task),
                $this->getServiceLocator()->get(UserLanguageServiceInterface::SERVICE_ID)->getDefaultLanguage()
            );

            $message = sprintf('Translations loaded for extension "%s"', $extension);
        } catch (Throwable $e) {
            $message = sprintf('Unable to load translations for task "%s".', $task->getId());
        }

        $this->getServiceLocator()->get(LoggerService::SERVICE_ID)->notice($message);
    }

    /**
     * @throws common_ext_ExtensionException
     */
    private function getTaskExtension(TaskInterface $task): common_ext_Extension
    {
        $reflectionClass = new ReflectionClass($task);
        $namespace = $reflectionClass->getNamespaceName();
        $extensionId = explode('\\', $namespace)[1] ?? null;

        return $this->getServiceLocator()
            ->get(common_ext_ExtensionsManager::SERVICE_ID)
            ->getExtensionById($extensionId);
    }
}
