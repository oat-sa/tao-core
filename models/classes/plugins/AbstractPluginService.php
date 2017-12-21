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

namespace oat\tao\model\plugins;

use oat\tao\model\modules\AbstractModuleService;

/**
 * Manage module plugins. Should be overridden to provide the right PluginRegistry instance and a SERVICE_ID constant.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
abstract class AbstractPluginService extends AbstractModuleService
{
    /**
     * Creates a plugin object from data array
     * @param $data
     * @return PluginModule
     * @throws \common_exception_InconsistentData
     */
    protected function createFromArray($data)
    {
        return PluginModule::fromArray($data);
    }

    /**
     * Retrieve the list of all available plugins (from the registry)
     *
     * @return PluginModule[] the available plugins
     */
    public function getAllPlugins()
    {
        return parent::getAllModules();
    }

    /**
     * Retrieve the given plugin from the registry
     *
     * @param string $id the identifier of the plugin to retrieve
     * @return PluginModule|null the plugin
     */
    public function getPlugin($id)
    {
        return parent::getModule($id);
    }

    /**
     * Change the state of a plugin to active
     *
     * @param PluginModule $plugin the plugin to activate
     * @return boolean true if activated
     */
    public function activatePlugin(PluginModule $plugin)
    {
        return parent::activateModule($plugin);
    }

    /**
     * Change the state of a plugin to inactive
     *
     * @param PluginModule $plugin the plugin to deactivate
     * @return boolean true if deactivated
     */
    public function deactivatePlugin(PluginModule $plugin)
    {
        return parent::deactivateModule($plugin);
    }

    /**
     * Register a list of plugins
     * @param array $plugins
     * @return int The number of registered plugins
     * @throws \common_exception_InconsistentData
     */
    public function registerPlugins(array $plugins)
    {
        return parent::registerModules($plugins);
    }

    /**
     * Register a list of plugins gathered by categories
     * @param array $plugins
     * @return int The number of registered plugins
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_InvalidArgumentType
     */
    public function registerPluginsByCategories(array $plugins)
    {
        return parent::registerModulesByCategories($plugins);
    }
}
