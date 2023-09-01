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

namespace oat\tao\model\preview;

use oat\oatbox\AbstractRegistry;
use oat\tao\model\modules\DynamicModule;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\service\InjectionAwareService;

/**
 * Class PreviewerRegistryService
 *
 * @package oat\tao\model\preview
 */
class PreviewerRegistryService extends InjectionAwareService implements PreviewerRegistryServiceInterface
{
    private const PREVIEWERS_KEY = 'previewers';
    private const PLUGINS_KEY = 'plugins';

    /** @var string */
    private $registryEntryKey;

    /** @var AbstractRegistry */
    private $registry;

    /**
     * PreviewerRegistryService constructor.
     *
     * @param string $registryEntryKey
     */
    public function __construct(string $registryEntryKey)
    {
        parent::__construct();

        $this->registryEntryKey = $registryEntryKey;
    }

    /**
     * @return AbstractRegistry
     */
    public function getRegistry(): AbstractRegistry
    {
        if (!isset($this->registry)) {
            $this->registry = ClientLibConfigRegistry::getRegistry();
        }

        return $this->registry;
    }

    /**
     * @param AbstractRegistry $registry
     */
    public function setRegistry(AbstractRegistry $registry): void
    {
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function getAdapters(): array
    {
        $registry = $this->getRegistry();

        if ($registry->isRegistered($this->registryEntryKey)) {
            $config = $registry->get($this->registryEntryKey);
        }

        return $config[self::PREVIEWERS_KEY] ?? [];
    }

    /**
     * @param DynamicModule $module
     *
     * @return bool
     */
    public function registerAdapter(DynamicModule $module): bool
    {
        if ($module === null || empty($module->getModule())) {
            return false;
        }

        $registry = $this->getRegistry();

        if ($registry->isRegistered($this->registryEntryKey)) {
            $config = $registry->get($this->registryEntryKey);
        }

        $config[self::PREVIEWERS_KEY][$module->getModule()] = $module->toArray();
        $registry->set($this->registryEntryKey, $config);

        return true;
    }

    /**
     * @param string $moduleId
     *
     * @return bool
     */
    public function unregisterAdapter(string $moduleId): bool
    {
        $registry = $this->getRegistry();

        if ($registry->isRegistered($this->registryEntryKey)) {
            $config = $registry->get($this->registryEntryKey);
        }

        if (isset($config[self::PREVIEWERS_KEY][$moduleId])) {
            unset($config[self::PREVIEWERS_KEY][$moduleId]);
            $registry->set($this->registryEntryKey, $config);

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getPlugins(): array
    {
        $registry = $this->getRegistry();

        if ($registry->isRegistered($this->registryEntryKey)) {
            $config = $registry->get($this->registryEntryKey);
        }

        return $config[self::PLUGINS_KEY] ?? [];
    }

    /**
     * @param DynamicModule $module
     *
     * @return bool
     */
    public function registerPlugin(DynamicModule $module): bool
    {
        if ($module === null || empty($module->getModule())) {
            return false;
        }

        $this->unregisterPlugin($module->getModule());

        $registry = $this->getRegistry();

        if ($registry->isRegistered($this->registryEntryKey)) {
            $config = $registry->get($this->registryEntryKey);
        }

        $config[self::PLUGINS_KEY][] = $module->toArray();
        $registry->set($this->registryEntryKey, $config);

        return true;
    }

    /**
     * @param string $module
     *
     * @return bool
     */
    public function unregisterPlugin(string $module): bool
    {
        $registry = $this->getRegistry();

        if ($registry->isRegistered($this->registryEntryKey)) {
            $config = $registry->get($this->registryEntryKey);
        }

        $result = false;

        if (isset($config[self::PLUGINS_KEY])) {
            $config[self::PLUGINS_KEY] = array_filter(
                $config[self::PLUGINS_KEY],
                static function (array $plugin) use ($module, &$result): bool {
                    $result = $plugin['module'] === $module;

                    return !$result;
                }
            );
            $registry->set($this->registryEntryKey, $config);
        }

        return $result;
    }
}
