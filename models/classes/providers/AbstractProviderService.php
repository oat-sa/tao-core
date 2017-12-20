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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\providers;

use oat\tao\model\modules\AbstractModuleService;

/**
 * Manage module providers. Should be overridden to provide the right ProviderRegistry instance and a SERVICE_ID constant.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
abstract class AbstractProviderService extends AbstractModuleService
{
    /**
     * Creates a provider object from data array
     * @param $data
     * @return ProviderModule
     * @throws \common_exception_InconsistentData
     */
    protected function createFromArray($data)
    {
        return ProviderModule::fromArray($data);
    }

    /**
     * Retrieves the list of all available providers (from the registry)
     *
     * @return ProviderModule[] the available providers
     */
    public function getAllProviders()
    {
        return parent::getAllModules();
    }

    /**
     * Retrieves the given provider from the registry
     *
     * @param string $id the identifier of the provider to retrieve
     * @return ProviderModule|null the provider
     */
    public function getProvider($id)
    {
        return parent::getModule($id);
    }

    /**
     * Changes the state of a provider to active
     *
     * @param ProviderModule $provider the provider to activate
     * @return boolean true if activated
     */
    public function activateProvider(ProviderModule $provider)
    {
        return parent::activateModule($provider);
    }

    /**
     * Changes the state of a provider to inactive
     *
     * @param ProviderModule $provider the provider to deactivate
     * @return boolean true if deactivated
     */
    public function deactivateProvider(ProviderModule $provider)
    {
        return parent::deactivateModule($provider);
    }

    /**
     * Registers a list of providers
     * @param array $providers
     * @return int The number of registered providers
     * @throws \common_exception_InconsistentData
     */
    public function registerProviders(array $providers)
    {
        return $this->registerModules($providers);
    }

    /**
     * Registers a list of providers gathered by categories
     * @param array $providers
     * @return int The number of registered providers
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_InvalidArgumentType
     */
    public function registerProvidersByCategories(array $providers)
    {
        return $this->registerModulesByCategories($providers);
    }
}
