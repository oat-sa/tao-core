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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\modules;

use oat\oatbox\service\ConfigurableService;

/**
 * Manage module modules. Should be overridden to provide the right ModuleRegistry instance and a SERVICE_ID constant.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
abstract class AbstractModuleService extends ConfigurableService
{
    /**
     * @var AbstractModuleRegistry
     */
    private $registry;

    /**
     * Registry setter
     * @param AbstractModuleRegistry $registry
     */
    public function setRegistry(AbstractModuleRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Creates a module object from data array
     * @param $data
     * @return DynamicModule
     * @throws \common_exception_InconsistentData
     */
    protected function createFromArray($data)
    {
        return DynamicModule::fromArray($data);
    }

    /**
     * Retrieve the list of all available modules (from the registry)
     *
     * @return DynamicModule[] the available modules
     */
    public function getAllModules()
    {
        $modules = array_map(function ($value) {
            return $this->loadModule($value);
        }, $this->registry->getMap());

        return array_filter($modules, function ($module) {
            return !is_null($module);
        });
    }

    /**
     * Retrieve the given module from the registry
     *
     * @param string $id the identifier of the module to retrieve
     * @return DynamicModule|null the module
     */
    public function getModule($id)
    {
        foreach ($this->registry->getMap() as $module) {
            if ($module['id'] == $id) {
                return $this->loadModule($module);
            }
        }
        return null;
    }

    /**
     * Load a module from the given data
     * @param array $data
     * @return DynamicModule|null
     */
    private function loadModule(array $data)
    {
        $module = null;
        try {
            $module = $this->createFromArray($data);
        } catch (\common_exception_InconsistentData $dataException) {
            \common_Logger::w('Got inconsistent module data, skipping.');
        }
        return $module;
    }

    /**
     * Change the state of a module to active
     *
     * @param DynamicModule $module the module to activate
     * @return boolean true if activated
     */
    public function activateModule(DynamicModule $module)
    {
        if (!is_null($module)) {
            $module->setActive(true);
            return $this->registry->register($module);
        }

        return false;
    }

    /**
     * Change the state of a module to inactive
     *
     * @param DynamicModule $module the module to deactivate
     * @return boolean true if deactivated
     */
    public function deactivateModule(DynamicModule $module)
    {
        if (!is_null($module)) {
            $module->setActive(false);
            return $this->registry->register($module);
        }
        
        return false;
    }

    /**
     * Register a list of modules
     * @param array $modules
     * @return int The number of registered modules
     * @throws \common_exception_InconsistentData
     */
    public function registerModules(array $modules)
    {
        $count = 0;
        foreach($modules as $module) {
            if (is_array($module)) {
                $module = $this->createFromArray($module);
            }
            $this->registry->register($module);
            $count ++;
        }
        return $count;
    }

    /**
     * Register a list of modules gathered by categories
     * @param array $modules
     * @return int The number of registered modules
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_InvalidArgumentType
     */
    public function registerModulesByCategories(array $modules)
    {
        $count = 0;
        foreach($modules as $categoryModules) {
            if (is_array($categoryModules)) {
                $count += $this->registerModules($categoryModules);
            } else {
                throw new \common_exception_InvalidArgumentType(self::class, __FUNCTION__, 0, 'array', $categoryModules);
            }
        }
        return $count;
    }
}
