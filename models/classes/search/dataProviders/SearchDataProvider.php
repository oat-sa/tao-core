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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\search\dataProviders;

use oat\oatbox\service\ConfigurableService;

/**
 * Class SearchDataProvider
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @package oat\tao\model\search\dataProviders
 */
class SearchDataProvider extends ConfigurableService
{
    const SERVICE_ID = 'tao/SearchDataProvider';
    const PROVIDERS_OPTION = 'providers';

    /**
     * @return array
     */
    public function getAllDataProviders()
    {
        $providers = [];
        foreach ($this->getOption(self::PROVIDERS_OPTION) as $provider) {
            $providers[] = $this->getServiceLocator()->get($provider);
        }
        return $providers;
    }

    /**
     * @param $resourceTraversable
     * @return array
     */
    public function prepareAllDataForIndex($resourceTraversable)
    {
        $providers = $this->getAllDataProviders();

        $indexesData = [];
        /** @var DataProvider $provider */
        foreach ($providers as $provider) {
            $data = $provider->prepareDataForIndex($resourceTraversable);
            $indexesData = array_merge($indexesData, $data);
        }
        return $indexesData;
    }

    /**
     * @return array
     */
    public function getAllIndexesMap()
    {
        $providers = $this->getAllDataProviders();
        $indexesMaps = [];
        /** @var DataProvider|ConfigurableService $provider */
        foreach ($providers as $provider) {
            $indexesMap = $provider->getOption(DataProvider::INDEXES_MAP_OPTION);
            $indexesMaps = array_merge($indexesMaps, $indexesMap);
        }
        return $indexesMaps;
    }

    /**
     * @param $rootClass
     * @return array
     */
    public function getOptionsByClass($rootClass)
    {
        $providers = $this->getAllDataProviders();
        /** @var DataProvider|ConfigurableService $provider */
        foreach ($providers as $provider) {
            $indexesMap = $provider->getOption(DataProvider::INDEXES_MAP_OPTION);
            if (isset($indexesMap[$rootClass])) {
                return $indexesMap[$rootClass];
            }
        }
        return [];
    }

    /**
     * @param $rootClass
     * @return \core_kernel_classes_Class
     */
    public function getSearchClass($rootClass)
    {
        $providers = $this->getAllDataProviders();
        if ($rootClass instanceof \core_kernel_classes_Class) {
            $rootClass = $rootClass->getUri();
        }
        $searchClass = (string) $rootClass;
        /** @var DataProvider|ConfigurableService $provider */
        foreach ($providers as $provider) {
            $indexesMap = $provider->getOption(DataProvider::INDEXES_MAP_OPTION);
            if (isset($indexesMap[$rootClass])) {
                $classData = $indexesMap[$rootClass];
                if (isset($classData[DataProvider::SEARCH_CLASS_OPTION])) {
                    $searchClass = $classData[DataProvider::SEARCH_CLASS_OPTION];
                }
            }
        }

        return new \core_kernel_classes_Class($searchClass);
    }
}
