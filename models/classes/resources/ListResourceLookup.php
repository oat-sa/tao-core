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

use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\ResultSet;
use oat\tao\model\search\Search;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\security\SignatureGenerator;

/**
 * Look up resources and format them as a flat list
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class ListResourceLookup extends ConfigurableService implements ResourceLookup
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/ListResourceLookup';

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
    public function getResources(
        \core_kernel_classes_Class $rootClass,
        array $selectedUris = [],
        array $propertyFilters = [],
        $offset = 0,
        $limit = 30
    ) {
        // Searching by label parameter will utilize fulltext search
        if (count($propertyFilters) == 1 && isset($propertyFilters[OntologyRdfs::RDFS_LABEL])) {
            $searchString = current($propertyFilters);
            return $this->searchByString($searchString, $rootClass, $offset, $limit);
        } else {
            return $this->searchByProperties($propertyFilters, $rootClass, $offset, $limit);
        }
    }

    /**
     * Search using an advanced search string
     * @param string $searchString
     * @param \core_kernel_classes_Class $rootClass
     * @param int $offset
     * @param int $limit
     * @return array
     */
    private function searchByString($searchString, $rootClass, $offset, $limit)
    {
        /** @var Search $searchService */
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
        /** @var ResultSet $result */
        $result = $searchService->query($searchString, $rootClass, $offset, $limit);
        $count = $result->getTotalCount();

        $ids = [];
        foreach ($result as $item) {
            $ids[] = $item['id'] ?? $item;
        }

        return $this->format($ids, $count, $offset, $limit);
    }

    /**
     * Search using properties
     * @param string $searchString
     * @param \core_kernel_classes_Class $rootClass
     * @param int $offset
     * @param int $limit
     * @return array
     */
    private function searchByProperties($propertyFilters, $rootClass, $offset, $limit)
    {
        // for searching by properties will be used RDF search
        $options = [
            'recursive' => true,
            'like'      => true,
            'limit'     => $limit,
            'offset'    => $offset
        ];
        $count = $rootClass->countInstances($propertyFilters, $options);
        $resources = $rootClass->searchInstances($propertyFilters, $options);
        return $this->format($resources, $count, $offset, $limit);
    }

    /**
     * Format the results according to the needs of ListLookup
     * @param array $result
     * @param int $count
     * @param int $offset
     * @param int $limit
     * @return array
     */
    private function format($result, $count, $offset, $limit)
    {
        $nodes = [];
        foreach ($result as $item) {
            $resource = $this->getResource($item);
            $data = $this->getResourceData($resource);
            if ($data) {
                $nodes[] = $data;
            }
        }
        return [
            'total'  => $count,
            'offset' => $offset,
            'limit'  => $limit,
            'nodes'  => $nodes
        ];
    }

    /**
     * Preparing resource to be used in the ListLookup
     * @param $resource
     * @return array|bool
     */
    private function getResourceData($resource)
    {
        $data = false;
        if (!is_null($resource) && $resource->exists()) {
            $resourceTypes = array_keys($resource->getTypes());
            $data = [
                'uri'        => $resource->getUri(),
                'classUri'   => $resourceTypes[0],
                'label'      => $resource->getLabel(),
                'type'       => 'instance',
                'signature'  => $this->getSignatureGenerator()->generate($resource->getUri()),
            ];
            $parentClassUri = $this->getParentClassUri($resource);
            if ($parentClassUri !== null) {
                $data['classSignature'] = $this->getSignatureGenerator()->generate($parentClassUri);
            }
        }
        return $data;
    }

    public function getClasses(
        \core_kernel_classes_Class $rootClass,
        array $selectedUris = [],
        array $propertyFilters = [],
        $offset = 0,
        $limit = 30
    ) {
        return [];
    }

    private function getSignatureGenerator(): SignatureGenerator
    {
        return $this->getServiceManager()->get(SignatureGenerator::class);
    }

    private function getParentClassUri(core_kernel_classes_Resource $resource): ?string
    {
        $parentClassUri = $resource->getOnePropertyValue($resource->getProperty(OntologyRdf::RDF_TYPE));
        return $parentClassUri !== null
            ? $parentClassUri->getUri()
            : null;
    }
}
