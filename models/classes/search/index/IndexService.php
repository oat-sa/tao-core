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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\search\index;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\Search;
use oat\tao\model\menu\MenuService;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\resources\ResourceIterator;

/**
 * Class IndexService
 * @package oat\tao\model\search\index
 */
class IndexService extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'tao/IndexService';
    const INDEX_MAP_PROPERTY_DEFAULT = 'default';
    const INDEX_MAP_PROPERTY_FUZZY = 'fuzzy';

    const OPTION_DOCUMENT_BUILDER_FACTORY = 'documentBuilderFactory';

    /**
     * Run a full reindexing
     * @return boolean
     * @throws
     */
    public function runIndexing()
    {
        $iterator = $this->getResourceIterator();
        $indexIterator = new IndexIterator($iterator);
        $indexIterator->setServiceLocator($this->getServiceLocator());
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
        $result = $searchService->index($indexIterator);
        $this->logDebug($result . ' resources have been indexed by ' . static::class);
        return $result;
    }

    /**
     * Returns a factory to get the IndexDocument Builder
     *
     * return mixed
     */
    public function getDocumentBuilderFactory(): AbstractDocumentBuilderFactory
    {
        $factory = $this->getOption(self::OPTION_DOCUMENT_BUILDER_FACTORY);
        return $factory;
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @return IndexDocument|null
     * @throws \common_Exception
     * @throws \common_exception_InconsistentData
     *
     * @deprecated should be GenerisDocumentBuilderFactory::createDocumentFromResource instead
     */
    public function createDocumentFromResource(\core_kernel_classes_Resource $resource): ?IndexDocument
    {
        $resourceTypes = $resource->getTypes();
        $resourceType = current(array_keys($resourceTypes)) ?: "";

        $document = $this->getDocumentBuilderFactory()->getDocumentBuilderByResourceType($resourceType)->createDocumentFromResource($resource);

        return $document;
    }

    /**
     * @param array $array
     * @return IndexDocument
     * @throws \common_exception_MissingParameter
     * @throws \common_Exception
     *
     * @deprecated should be GenerisDocumentBuilderFactory::createDocumentFromArray instead
     */
    public function createDocumentFromArray($array = []): ?IndexDocument
    {
        if (!isset($array['body'])) {
            throw new \common_exception_MissingParameter('body');
        }
        if (!isset($array['body']['type'])) {
            throw new \common_exception_MissingParameter('body[type]');
        }

        $document = $this->getDocumentBuilderFactory()->getDocumentBuilderByResourceType($array['body']['type'])->createDocumentFromArray($array);

        return $document;
    }

    /**
     * @return \Iterator
     */
    protected function getResourceIterator()
    {
        $iterator = new ResourceIterator($this->getIndexedClasses());
        $iterator->setServiceLocator($this->getServiceLocator());
        return $iterator;
    }

    protected function getIndexedClasses()
    {
        $classes = [];
        foreach (MenuService::getAllPerspectives() as $perspective) {
            foreach ($perspective->getChildren() as $structure) {
                foreach ($structure->getTrees() as $tree) {
                    $rootNode = $tree->get('rootNode');
                    if (!empty($rootNode)) {
                        $classes[$rootNode] = $this->getClass($rootNode);
                    }
                }
            }
        }
        return array_values($classes);
    }
}
