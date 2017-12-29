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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\tao\model\resources;

use oat\oatbox\service\ConfigurableService;

/**
 * Look up resources and format them as a flat list
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class ListResourceLookup extends ConfigurableService implements ResourceLookup
{

    const SERVICE_ID = 'tao/ListResourceLookup';

    /**
     * Retrieve Resources for the given parameters as a list
     *
     * @param \core_kernel_classes_Class $rootClass       the resources class
     * @param array                      $propertyFilters propUri/propValue to search resources
     * @param string[]                   $selectedUris    the resources to open
     * @param int                        $offset          for paging
     * @param int                        $limit           for paging
     * @return array the resources
     */
    public function getResources(\core_kernel_classes_Class $rootClass, array $selectedUris = [], array $propertyFilters = [], $offset = 0, $limit = 30)
    {
        $options = [
            'recursive' => true,
            'like'      => true,
            'limit'     => $limit,
            'offset'    => $offset
        ];

        $count = $rootClass->countInstances($propertyFilters, $options);
        $resources = $rootClass->searchInstances($propertyFilters, $options);

        $nodes = [];
        foreach($resources as $resource){

            if(!is_null($resource)){
                $resourceTypes = array_keys($resource->getTypes());
                $nodes[] = [
                    'uri'        => $resource->getUri(),
                    'classUri'   => $resourceTypes[0],
                    'label'      => $resource->getLabel(),
                    'type'       => 'instance'
                ];
            }
        }

        return [
            'total'  => $count,
            'offset' => $offset,
            'limit'  => $limit,
            'nodes'  => $nodes
        ];
    }
}
