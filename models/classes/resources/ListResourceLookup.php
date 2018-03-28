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

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\Search;

/**
 * Look up resources and format them as a flat list
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class ListResourceLookup extends ConfigurableService implements ResourceLookup
{

    use OntologyAwareTrait;

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
        if (count($propertyFilters) == 1 && isset($propertyFilters[OntologyRdfs::RDFS_LABEL])) {
            /** @var Search $searchService */
            $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
            /** @var ResultSet $result */
            $result = $searchService->query(current($propertyFilters), $rootClass, $offset, $limit);
            $count = $result->getTotalCount();

            $nodes = [];
            while ($result->valid()) {
                $resource = $this->getResource($result->current());
                $data = $this->getResourceData($resource);
                if ($data) {
                    $nodes[] = $data;
                }
                $result->next();
            }
        } else {
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
                $data = $this->getResourceData($resource);
                if ($data) {
                    $nodes[] = $data;
                }
            }
        }

        return [
            'total'  => $count,
            'offset' => $offset,
            'limit'  => $limit,
            'nodes'  => $nodes
        ];
    }

    private function getResourceData($resource)
    {
        $data = false;
        if(!is_null($resource) && $resource->exists()) {
            $resourceTypes = array_keys($resource->getTypes());
             $data = [
                'uri'        => $resource->getUri(),
                'classUri'   => $resourceTypes[0],
                'label'      => $resource->getLabel(),
                'type'       => 'instance'
            ];
        }
        return $data;
    }

    public function getClasses(\core_kernel_classes_Class $rootClass, array $selectedUris = [], array $propertyFilters = [], $offset = 0, $limit = 30)
    {
        return [];
    }
}
