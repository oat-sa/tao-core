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

use oat\oatbox\service\ConfigurableService;

/**
 * Manage module providers. Should be overridden to provide the right ProviderRegistry instance and a SERVICE_ID constant.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
abstract class AbstractProviderService extends ConfigurableService
{
    /**
     * @var AbstractProviderRegistry
     */
    private $registry;

    /**
     * Registry setter
     * @param AbstractProviderRegistry $registry
     */
    public function setRegistry(AbstractProviderRegistry $registry)
    {
        $this->registry = $registry;
    }

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
        $providers = array_map(function ($value) {
            return $this->loadProvider($value);
        }, $this->registry->getMap());

        return array_filter($providers, function ($provider) {
            return !is_null($provider);
        });
    }

    /**
     * Retrieves the given provider from the registry
     *
     * @param string $id the identifier of the provider to retrieve
     * @return ProviderModule|null the provider
     */
    public function getProvider($id)
    {
        foreach ($this->registry->getMap() as $provider) {
            if ($provider['id'] == $id) {
                return $this->loadProvider($provider);
            }
        }
        return null;
    }

    /**
     * Loads a provider from the given data
     * @param array $data
     * @return ProviderModule|null
     */
    private function loadProvider(array $data)
    {
        $provider = null;
        try {
            $provider = $this->createFromArray($data);
        } catch (\common_exception_InconsistentData $dataException) {
            \common_Logger::w('Got inconsistent provider data, skipping.');
        }
        return $provider;
    }

    /**
     * Changes the state of a provider to active
     *
     * @param ProviderModule $provider the provider to activate
     * @return boolean true if activated
     */
    public function activateProvider(ProviderModule $provider)
    {
        if (!is_null($provider)) {
            $provider->setActive(true);
            return $this->registry->register($provider);
        }

        return false;
    }

    /**
     * Changes the state of a provider to inactive
     *
     * @param ProviderModule $provider the provider to deactivate
     * @return boolean true if deactivated
     */
    public function deactivateProvider(ProviderModule $provider)
    {
        if (!is_null($provider)) {
            $provider->setActive(false);
            return $this->registry->register($provider);
        }

        return false;
    }

    /**
     * Registers a list of providers
     * @param array $providers
     * @return int The number of registered providers
     * @throws \common_exception_InconsistentData
     */
    public function registerProviders(array $providers)
    {
        $count = 0;
        foreach ($providers as $provider) {
            if (is_array($provider)) {
                $provider = $this->createFromArray($provider);
            }
            $this->registry->register($provider);
            $count++;
        }
        return $count;
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
        $count = 0;
        foreach ($providers as $categoryProviders) {
            if (is_array($categoryProviders)) {
                $count += $this->registerProviders($categoryProviders);
            } else {
                throw new \common_exception_InvalidArgumentType(self::class, __FUNCTION__, 0, 'array', $categoryProviders);
            }
        }
        return $count;
    }
}
