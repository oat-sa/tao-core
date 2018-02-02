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

namespace oat\tao\model\search\index;

use oat\generis\model\OntologyRdfs;
use oat\oatbox\extension\script\MissingOptionException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\SearchService;
use oat\tao\model\search\SearchTokenGenerator;
use oat\tao\model\TaoOntology;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\tao\model\search\Search;
use oat\tao\model\menu\MenuService;
use oat\generis\model\OntologyAwareTrait;

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
    const INDEX_MAP_PROPERTY = 'indexMap';

    /** @var array */
    private $map;

    /**
     * Run a full reindexing
     * @return int number of resources indexed
     */
    public function runIndexing()
    {
        $iterator = new \core_kernel_classes_ResourceIterator($this->getIndexedClasses());
        $indexIterator = new IndexIterator($iterator);
        $indexIterator->setServiceLocator($this->getServiceLocator());
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
        return $searchService->index($indexIterator);
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @return IndexDocument
     * @throws \common_Exception
     * @throws \common_exception_InconsistentData
     */
    public function createDocumentFromResource(\core_kernel_classes_Resource $resource)
    {
        $tokenGenerator = new SearchTokenGenerator();

        $body = [];
        $indexesProperties = [];
        foreach ($tokenGenerator->generateTokens($resource) as $data) {
            /** @var OntologyIndex $index */
            list($index, $strings) = $data;
            $body[$index->getIdentifier()] = $strings;
            $indexesProperties[$index->getIdentifier()] = $this->getIndexProperties($index);
        }
        $body['type'] = $this->getTypesForResource($resource);
        $document = new IndexDocument(
            $resource->getUri(),
            $body,
            $indexesProperties
        );
        return $document;
    }

    /**
     * @param array $array
     * @return IndexDocument
     * @throws MissingOptionException
     * @throws \common_Exception
     */
    public function createDocumentFromArray($array = [])
    {
        if (!isset($array['id'])) {
            throw new MissingOptionException('Missed id property for the index document');
        }

        if (!isset($array['body'])) {
            throw new MissingOptionException('Missed body property for the index document');
        }
        $body = $array['body'];
        $indexProperties = [];
        if (isset($array['indexProperties'])) {
            $indexProperties = $array['indexProperties'];
        }
        $document = new IndexDocument(
            $array['id'],
            $body,
            $indexProperties
        );
        return $document;
    }

    /**
     * @param OntologyIndex $index
     * @return mixed
     * @throws \common_Exception
     */
    public function getIndexProperties(OntologyIndex $index)
    {
        if (!isset($this->map[$index->getIdentifier()])) {
            $indexProperty = new IndexProperty(
                $index->getIdentifier(),
                $index->isFuzzyMatching(),
                $index->isDefaultSearchable()
            );
            $this->map[$index->getIdentifier()] = $indexProperty;
        }
        return $this->map[$index->getIdentifier()];
    }

    /**
     * @param $resource
     * @return array
     */
    public function getTypesForResource($resource)
    {
        $toDo = array();
        foreach ($resource->getTypes() as $class) {
            $toDo[] = $class->getUri();
        }

        $done = array(OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT);
        $toDo = array_diff($toDo, $done);

        $classes = array();
        while (!empty($toDo)) {
            $class = new \core_kernel_classes_Class(array_pop($toDo));
            $classes[] = $class->getUri();
            foreach ($class->getParentClasses() as $parent) {
                if (!in_array($parent->getUri(), $done)) {
                    $toDo[] = $parent->getUri();
                }
            }
            $done[] = $class->getUri();
        }

        return $classes;
    }

    /**
     * @param $name
     * @return $this
     */
    public function addDefaultIndexMap($name)
    {
        $indexMap = $this->getOption(self::INDEX_MAP_PROPERTY);
        $indexMap[self::INDEX_MAP_PROPERTY_DEFAULT][] = $name;
        $indexMap[self::INDEX_MAP_PROPERTY_DEFAULT] = array_unique($indexMap[self::INDEX_MAP_PROPERTY_DEFAULT]);
        $this->setOption(self::INDEX_MAP_PROPERTY, $indexMap);
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function addFuzzyIndexMap($name)
    {
        $indexMap = $this->getOption(self::INDEX_MAP_PROPERTY);
        $indexMap[self::INDEX_MAP_PROPERTY_FUZZY][] = $name;
        $indexMap[self::INDEX_MAP_PROPERTY_FUZZY] = array_unique($indexMap[self::INDEX_MAP_PROPERTY_FUZZY]);
        $this->setOption(self::INDEX_MAP_PROPERTY, $indexMap);
        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultIndexMap()
    {
        $indexMap = $this->getOption(self::INDEX_MAP_PROPERTY);
        if (isset($indexMap[self::INDEX_MAP_PROPERTY_DEFAULT])) {
            return $indexMap[self::INDEX_MAP_PROPERTY_DEFAULT];
        }
        return [];
    }

    /**
     * @return array
     */
    public function getFuzzyIndexMap()
    {
        $indexMap = $this->getOption(self::INDEX_MAP_PROPERTY);
        if (isset($indexMap[self::INDEX_MAP_PROPERTY_FUZZY])) {
            return $indexMap[self::INDEX_MAP_PROPERTY_FUZZY];
        }
        return [];
    }

    protected function getIndexedClasses()
    {
        $classes = array();
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
