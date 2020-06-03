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

use ArrayIterator;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use Iterator;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\WidgetRdf;
use oat\oatbox\extension\script\MissingOptionException;
use oat\oatbox\service\ConfigurableService;
use oat\tao\elasticsearch\SlugCreator;
use oat\tao\model\menu\MenuService;
use oat\tao\model\resources\ResourceIterator;
use oat\tao\model\search\Search;
use oat\tao\model\search\SearchTokenGenerator;
use oat\tao\model\TaoOntology;
use oat\tao\model\WidgetDefinitions;
use tao_helpers_form_GenerisFormFactory;
use tao_helpers_Slug;

/**
 * Class IndexService
 * @package oat\tao\model\search\index
 */
class IndexService extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'tao/IndexService';
    const ALLOWED_DYNAMIC_TYPES = [
        WidgetDefinitions::PROPERTY_TEXTBOX,
        WidgetDefinitions::PROPERTY_TEXTAREA,
        WidgetDefinitions::PROPERTY_HTMLAREA,
        WidgetDefinitions::PROPERTY_CHECKBOX,
        WidgetDefinitions::PROPERTY_COMBOBOX,
        WidgetDefinitions::PROPERTY_RADIOBOX,
    ];

    /** @var array */
    private $map;

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
        $this->logInfo('starting indexation');
        $result = $searchService->index($indexIterator);
        $this->logDebug($result . ' resources have been indexed by ' . static::class);
        return $result;
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

        return new IndexDocument(
            $resource->getUri(),
            $body,
            $indexesProperties,
            $this->getDynamicProperties($body['type'], $resource)
        );
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
        $toDo = [];
        foreach ($resource->getTypes() as $class) {
            $toDo[] = $class->getUri();
        }

        $done = [OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT];
        $toDo = array_diff($toDo, $done);

        $classes = [];
        while (!empty($toDo)) {
            $class = new core_kernel_classes_Class(array_pop($toDo));
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

    private function getDynamicProperties(array $classes, core_kernel_classes_Resource $resource): Iterator
    {
        $customProperties = [];

        foreach ($classes as $class) {
            $properties = tao_helpers_form_GenerisFormFactory::getClassProperties(
                new core_kernel_classes_Class($class)
            );

            foreach ($properties as $property) {
                /** @var core_kernel_classes_Resource $propertyType |null */
                $propertyType = $property->getOnePropertyValue(
                    new core_kernel_classes_Property(
                        WidgetRdf::PROPERTY_WIDGET
                    )
                );
                if (null === $propertyType) {
                    continue;
                }

                $propertyTypeUri = $propertyType->getUri();

                if (!in_array($propertyTypeUri, self::ALLOWED_DYNAMIC_TYPES)) {
                    continue;
                }

                $propertyTypeArray = explode('#', $propertyTypeUri, 2);
                $propertyTypeId = end($propertyTypeArray);
                $customPropertyLabel = $property->getLabel();

                if (false === $propertyTypeId) {
                    continue;
                }

                $fieldName = $propertyTypeId . '_' . tao_helpers_Slug::create($customPropertyLabel);
                $propertyValue = $resource->getOnePropertyValue($property);

                if (null === $propertyValue) {
                    continue;
                }

                $customProperties[$fieldName] = (string)$propertyValue;

                if ($propertyTypeUri === WidgetDefinitions::PROPERTY_COMBOBOX
                    || $propertyTypeUri === WidgetDefinitions::PROPERTY_RADIOBOX) {
                    $customProperties[$fieldName] = (string)$propertyValue->getLabel();
                }

                if ($propertyTypeUri === WidgetDefinitions::PROPERTY_CHECKBOX) {
                    $customPropertiesValues = $resource->getPropertyValues($property);
                    $customProperties[$fieldName] = array_map(
                        function (string $propertyValue): string {
                            return (new core_kernel_classes_Property($propertyValue))->getLabel();
                        },
                        $customPropertiesValues
                    );
                }
            }
        }

        return new ArrayIterator($customProperties);
    }
}
