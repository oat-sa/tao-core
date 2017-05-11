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

use oat\oatbox\service\ConfigurableService;

/**
 * Manage module plugins. Should be overridden to provide the right PluginRegistry instance and a SERVICE_ID constant.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
abstract class AbstractPluginService extends ConfigurableService
{
    /**
     * @var AbstractPluginRegistry
     */
    private $registry;

    /**
     * Registry setter
     * @param AbstractPluginRegistry $registry
     */
    public function setRegistry(AbstractPluginRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Creates a plugin object from data array
     * @param $data
     * @return PluginModule
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
        $plugins = array_map(function ($value) {
            return $this->loadPlugin($value);
        }, $this->registry->getMap());

        return array_filter($plugins, function ($plugin) {
            return !is_null($plugin);
        });
    }

    /**
     * Retrieve the given plugin from the registry
     *
     * @param string $id the identifier of the plugin to retrieve
     * @return PluginModule|null the plugin
     */
    public function getPlugin($id)
    {
        foreach ($this->registry->getMap() as $plugin) {
            if ($plugin['id'] == $id) {
                return $this->loadPlugin($plugin);
            }
        }
        return null;
    }

    /**
     * Load a plugin from the given data
     * @param array $data
     * @return PluginModule|null
     */
    private function loadPlugin(array $data)
    {
        $plugin = null;
        try {
            $plugin = $this->createFromArray($data);
        } catch (\common_exception_InconsistentData $dataException) {
            \common_Logger::w('Got inconsistent plugin data, skipping.');
        }
        return $plugin;
    }

    /**
     * Change the state of a plugin to active
     *
     * @param PluginModule $plugin the plugin to activate
     * @return boolean true if activated
     */
    public function activatePlugin(PluginModule $plugin)
    {
        if (!is_null($plugin)) {
            $plugin->setActive(true);
            return $this->registry->register($plugin);
        }

        return false;
    }

    /**
     * Change the state of a plugin to inactive
     *
     * @param PluginModule $plugin the plugin to deactivate
     * @return boolean true if deactivated
     */
    public function deactivatePlugin(PluginModule $plugin)
    {
        if (!is_null($plugin)) {
            $plugin->setActive(false);
            $this->registry->register($plugin);
        }
    }



    /**
     * Register a list of plugins
     * @param array $plugins
     * @return int The number of registered plugins
     */
    public function registerPlugins(array $plugins)
    {
        $count = 0;
        foreach($plugins as $plugin) {
            if (is_array($plugin)) {
                $plugin = $this->createFromArray($plugin);
            }
            $this->registry->register($plugin);
            $count ++;
        }
        return $count;
    }

    /**
     * Register a list of plugins gathered by categories
     * @param array $plugins
     * @return int The number of registered plugins
     * @throws \common_exception_InvalidArgumentType
     */
    public function registerPluginsByCategories(array $plugins)
    {
        $count = 0;
        foreach($plugins as $categoryPlugins) {
            if (is_array($categoryPlugins)) {
                $count += $this->registerPlugins($categoryPlugins);
            } else {
                throw new \common_exception_InvalidArgumentType(self::class, __FUNCTION__, 0, 'array', $categoryPlugins);
            }
        }
        return $count;
    }
}
