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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\index\DocumentBuilder;

use core_kernel_classes_Container;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\data\permission\ReverseRightLookupInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\WidgetRdf;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use oat\tao\model\search\index\IndexDocument;
use ArrayIterator;
use core_kernel_classes_Resource as Resource;
use Iterator;
use oat\tao\model\search\index\IndexProperty;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\search\SearchTokenGenerator;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\TaoOntology;
use tao_helpers_form_elements_Checkbox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Htmlarea;
use tao_helpers_form_elements_Radiobox;
use tao_helpers_form_elements_Textarea;
use tao_helpers_form_elements_Textbox;
use tao_helpers_Uri;

class IndexDocumentBuilder extends InjectionAwareService implements IndexDocumentBuilderInterface
{
    use OntologyAwareTrait;

    /** @var array */
    private $map = [];

    public const ALLOWED_DYNAMIC_TYPES = [
        tao_helpers_form_elements_Textbox::WIDGET_ID,
        tao_helpers_form_elements_Textarea::WIDGET_ID,
        tao_helpers_form_elements_Htmlarea::WIDGET_ID,
        tao_helpers_form_elements_Checkbox::WIDGET_ID,
        tao_helpers_form_elements_Combobox::WIDGET_ID,
        tao_helpers_form_elements_Radiobox::WIDGET_ID,
        SearchTextBox::WIDGET_ID,
        SearchDropdown::WIDGET_ID,
    ];

    private const ROOT_CLASSES = [
        TaoOntology::CLASS_URI_ITEM,
        TaoOntology::CLASS_URI_TEST,
        TaoOntology::CLASS_URI_ASSEMBLED_DELIVERY,
        TaoOntology::CLASS_URI_DELIVERY,
        TaoOntology::CLASS_URI_GROUP,
        TaoOntology::CLASS_URI_ITEM,
        TaoOntology::CLASS_URI_RESULT,
        TaoOntology::CLASS_URI_SUBJECT,
        TaoOntology::CLASS_URI_TEST,
    ];

    /**
     * {@inheritdoc}
     */
    public function createDocumentFromResource(Resource $resource): IndexDocument
    {
        $tokenizationInfo = $this->getTokenizedResourceBody($resource);

        $body = $tokenizationInfo['body'];
        $indexProperties = $tokenizationInfo['indexProperties'];

        $body['type'] = $this->getTypesForResource($resource);
        $dynamicProperties = $this->getDynamicProperties($resource->getTypes(), $resource);
        $accessProperties = $this->getAccessProperties($resource);

        return new IndexDocument(
            $resource->getUri(),
            $body,
            $indexProperties,
            $dynamicProperties,
            $accessProperties
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createDocumentFromArray(array $resource = []): IndexDocument
    {
        if (!isset($resource['id'])) {
            throw new \common_exception_MissingParameter('id');
        }

        if (!isset($resource['body'])) {
            throw new \common_exception_MissingParameter('body');
        }

        $body = $resource['body'];
        $indexProperties = [];

        if (isset($resource['indexProperties'])) {
            $indexProperties = $resource['indexProperties'];
        }

        $document = new IndexDocument(
            $resource['id'],
            $body,
            $indexProperties
        );

        return $document;
    }

    /**
     * @return string[]
     * @throws \common_exception_Error
     */
    protected function getTypesForResource(Resource $resource): array
    {
        $toDo = [];
        foreach ($resource->getTypes() as $class) {
            $toDo[] = $class->getUri();
        }

        $done = [OntologyRdfs::RDFS_RESOURCE, TaoOntology::CLASS_URI_OBJECT];
        $toDo = array_diff($toDo, $done);

        $classes = [];
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
     * Get the array of properties to be indexed
     *
     * @throws \common_Exception
     * @throws \common_exception_InconsistentData
     */
    protected function getTokenizedResourceBody(Resource $resource): array
    {
        $tokenGenerator = $this->getSearchTokenGenerator();

        $body = [];
        $indexProperties = [];

        foreach ($tokenGenerator->generateTokens($resource) as $data) {
            /** @var OntologyIndex $index */
            [$index, $strings] = $data;
            $body[$index->getIdentifier()] = $strings;
            $indexProperties[$index->getIdentifier()] = $this->getIndexProperties($index);
        }

        $body['parent_classes'] = $this->getParentClasses($resource->getTypes());

        $result = [
            'body' => $body,
            'indexProperties' => $indexProperties
        ];

        return $result;
    }

    /**
     * Get the list of index properties for indexation
     * @throws \common_Exception
     */
    protected function getIndexProperties(OntologyIndex $index): IndexProperty
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
     * Get the dynamic properties for indexation
     * @throws \core_kernel_persistence_Exception
     */
    protected function getDynamicProperties(array $classes, Resource $resource): Iterator
    {
        $customProperties = [];

        foreach ($classes as $class) {
            $properties = \tao_helpers_form_GenerisFormFactory::getClassProperties(
                $this->getClass($class)
            );

            $properties[OntologyRdfs::RDFS_LABEL] = $this->getProperty(OntologyRdfs::RDFS_LABEL);

            foreach ($properties as $property) {
                /** @var Resource $propertyType |null */
                $propertyType = $property->getOnePropertyValue(
                    $this->getProperty(
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

                if (false === $propertyTypeId) {
                    continue;
                }

                $fieldName = $propertyTypeId . '_' . tao_helpers_Uri::encode($property->getUri());

                $customPropertiesValues = $resource->getPropertyValuesCollection($property);
                $customProperties[$fieldName][] = array_map(
                    function (core_kernel_classes_Container $property): string {
                        return tao_helpers_Uri::encode(
                            $property instanceof Resource ? $property->getUri() : (string)$property
                        );
                    },
                    $customPropertiesValues->toArray()
                );
            }
        }

        $customProperties = $this->normalizeAndFilterUniqueValues($customProperties);

        return new ArrayIterator($customProperties);
    }

    private function getAccessProperties(Resource $resource): ?Iterator
    {
        $permissionProvider = $this->getServiceLocator()->get(PermissionInterface::SERVICE_ID);

        if (!$permissionProvider instanceof ReverseRightLookupInterface) {
            return null;
        }

        $accessRights = $permissionProvider->getResourceAccessData($resource->getUri());
        $accessRightsURIs = ['read_access' => array_keys($accessRights)];

        return new ArrayIterator($accessRightsURIs);
    }

    private function getParentClasses(array $types, string $path = ''): string
    {
        foreach ($types as $type) {
            $path = $type->getUri() . $path;
            if (!$this->isRootClass($type->getUri())) {
                $path = ';' . $path;
                $path = $this->getParentClasses($type->getParentClasses(), $path);
            }
        }

        return $path;
    }

    private function isRootClass(string $uri): bool
    {
        return in_array($uri, self::ROOT_CLASSES);
    }

    private function normalizeAndFilterUniqueValues(array $customProperties): array
    {
        foreach ($customProperties as $fieldName => $value) {
            $customProperties[$fieldName] = array_unique(array_merge(...(array_values($value))));
        }
        return array_filter($customProperties);
    }

    private function getSearchTokenGenerator(): SearchTokenGenerator
    {
        $tokenGenerator = $this->getServiceLocator()->get(SearchTokenGenerator::class);
        $this->propagate($tokenGenerator);
        return $tokenGenerator;
    }
}
