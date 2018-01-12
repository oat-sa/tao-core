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
use oat\tao\model\search\document\IndexDocument;
use oat\tao\model\search\Index;
use oat\tao\model\search\SearchService;
use oat\tao\model\search\SearchTokenGenerator;


class OntologyDataProvider extends ConfigurableService implements DataProvider
{
    const SEARCH_DATA_PROVIDER_NAME = 'ontology';
    const SERVICE_ID = 'tao/SearchOntology';

    protected $tokenGenerator;
    protected $map;

    public function getIndexPrefix()
    {
        return 'documents';
    }

    /**
     * OntologyDataProvider constructor.
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->tokenGenerator = new SearchTokenGenerator();
    }

    /**
     * @param       $id
     * @param array $customData
     * @return bool
     * @throws \common_exception_InconsistentData
     */
    public function addIndex($id, $customData = [])
    {
        $resource = new \core_kernel_classes_Resource($id);
        $resourceType = $this->getRootClass($resource);
        if ($resourceType) {
            $index = $this->getIndexName($resourceType);
            $body = $this->prepareBody($resource);
            $body = array_merge($body, $customData);
            $document = new IndexDocument($resource->getUri(), $resource->getUri(), $index, self::SERVICE_ID, $resourceType->getUri(), 'document', $body);
            SearchService::getSearchImplementation()->index($document);
            return true;
        }
        return false;
    }

    /**
     * @param $result
     * @return mixed
     */
    public function getResults($result)
    {
        return $result;
    }

    /**
     * @param null $resourceTraversable
     * @return array|mixed
     * @throws \common_exception_InconsistentData
     */
    public function prepareDataForIndex($resourceTraversable = null)
    {
        $data = [];
        while ($resourceTraversable->valid()) {
            /** @var \core_kernel_classes_Resource $resource */
            $resource = $resourceTraversable->current();
            $resourceType = $this->getRootClass($resource);
            if ($resourceType) {
                $body = $this->prepareBody($resource);
                $index = $this->getIndexName($resourceType);
                $document = new IndexDocument($resource->getUri(), $resource->getUri(), $index, self::SERVICE_ID, $resourceType->getUri(), 'document', $body);
                $data[] = $document;
            }

            $resourceTraversable->next();

        }
        return $data;
    }

    public function needIndex(\core_kernel_classes_Resource $resource)
    {
        $types = $resource->getTypes();
        $classes = current($types)->getParentClasses(true);
        $classes = array_merge($classes, $types);
        $resourcesIndexed = $this->getOption('indexesMap');
        $compare = array_intersect_key($resourcesIndexed, $classes);
        if ($compare) {
            return true;
        }
        return false;
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @return mixed|string
     */
    protected function getIndexName(\core_kernel_classes_Resource $resource)
    {
        $label = $resource->getLabel();
        $index = str_replace(' ', '_', strtolower(trim($label)));
        $prefix = $this->getIndexPrefix(). '-';
        $index = $prefix . $index;
        return $index;
    }

    /**
     * @param $resource
     * @return array
     * @throws \common_exception_InconsistentData
     */
    protected function prepareBody($resource)
    {
        $body = [];
        foreach ($this->tokenGenerator->generateTokens($resource) as $dataResource) {
            list($key, $strings) = $dataResource;
            $body[$this->getIndexId($key)] = array_pop($strings);
        }
        return $body;
    }

    /**
     * @param Index $index
     * @return mixed
     */
    protected function getIndexId(Index $index) {

        if (!isset($this->map[$index->getIdentifier()])) {
            $this->map[$index->getIdentifier()] = $index->getIdentifier();
        }
        return $this->map[$index->getIdentifier()];
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @return \core_kernel_classes_Resource
     */
    protected function getRootClass(\core_kernel_classes_Resource $resource)
    {
        $types = $resource->getTypes();
        $indexesMap = $this->getOption(self::INDEXES_MAP_OPTION);

        $classes = current($types)->getParentClasses(true);
        $classes = array_merge($classes, $types);
        $compare = array_intersect_key($indexesMap, $classes);
        if ($compare) {
            $uri = current(array_keys($compare));
            $resourceType = new \core_kernel_classes_Resource($uri);
            return $resourceType;
        }
        return null;
    }
}
