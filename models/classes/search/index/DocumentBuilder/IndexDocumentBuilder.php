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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\index\DocumentBuilder;

use common_Exception;
use common_exception_Error;
use common_exception_InconsistentData;
use common_exception_MissingParameter;
use core_kernel_classes_Container;
use core_kernel_classes_Property;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\data\permission\ReverseRightLookupInterface;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;
use oat\tao\model\search\index\IndexDocument;
use ArrayIterator;
use core_kernel_classes_Resource as Resource;
use Iterator;
use oat\tao\model\search\index\IndexProperty;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\search\SearchTokenGenerator;
use oat\tao\model\service\InjectionAwareService;
use oat\tao\model\TaoOntology;
use tao_helpers_Uri;

class IndexDocumentBuilder extends InjectionAwareService implements IndexDocumentBuilderInterface
{
    use OntologyAwareTrait;

    /** @var array */
    private $map = [];

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

    public function createDocumentFromResource(Resource $resource): IndexDocument
    {
        $tokenizationInfo = $this->getTokenizedResourceBody($resource);

        $body = $tokenizationInfo['body'];
        $indexProperties = $tokenizationInfo['indexProperties'];

        $body['type'] = $this->getTypesForResource($resource);

        return new IndexDocument(
            $resource->getUri(),
            $body,
            $indexProperties,
            $this->getDynamicProperties($resource->getTypes(), $resource),
            $this->getAccessProperties($resource)
        );
    }

    public function createDocumentFromArray(array $resourceData = []): IndexDocument
    {
        if (!isset($resourceData['id'])) {
            throw new common_exception_MissingParameter('id');
        }

        if (!isset($resourceData['body'])) {
            throw new common_exception_MissingParameter('body');
        }

        $resource = new Resource($resourceData['id']);

        if (isset($resourceData['indexProperties'])) {
            $indexProperties = $resourceData['indexProperties'];
        } else {
            $tokenizationInfo = $this->getTokenizedResourceBody($resource);
            $indexProperties = $tokenizationInfo['indexProperties'];
        }

        return new IndexDocument(
            $resourceData['id'],
            $resourceData['body'],
            $indexProperties,
            $this->getDynamicProperties($resource->getTypes(), $resource),
            $this->getAccessProperties($resource)
        );
    }

    /**
     * @return string[]
     * @throws common_exception_Error
     */
    private function getTypesForResource(Resource $resource): array
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
     * @throws common_Exception
     * @throws common_exception_InconsistentData
     */
    private function getTokenizedResourceBody(Resource $resource): array
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
        $body['location'] = implode('/', array_reverse($body['class'] ?? []));
        $body['updated_at'] = (string)$resource->getOnePropertyValue(
            $resource->getProperty(TaoOntology::PROPERTY_UPDATED_AT)
        );

        return [
            'body' => $body,
            'indexProperties' => $indexProperties
        ];
    }

    /**
     * @throws common_Exception
     */
    private function getIndexProperties(OntologyIndex $index): IndexProperty
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

    private function getDynamicProperties(array $classes, Resource $resource): Iterator
    {
        $customProperties = [];
        $customPropertiesCache = [];
        $propertyIndexReferenceFactory = $this->getPropertyIndexReferenceFactory();

        foreach ($classes as $class) {
            $properties = \tao_helpers_form_GenerisFormFactory::getClassProperties(
                $this->getClass($class)
            );

            $properties[OntologyRdfs::RDFS_LABEL] = $this->getProperty(OntologyRdfs::RDFS_LABEL);

            foreach ($properties as $property) {
                $fieldName = $propertyIndexReferenceFactory->create($property);

                if ($fieldName === null) {
                    continue;
                }

                $customPropertiesValues = $resource->getPropertyValuesCollection($property);
                $customProperties[$fieldName][] = array_map(
                    function (core_kernel_classes_Container $property): string {
                        return tao_helpers_Uri::encode(
                            $property instanceof Resource ? $property->getUri() : (string)$property
                        );
                    },
                    $customPropertiesValues->toArray()
                );

                $customPropertiesCache[$fieldName] = $property;
            }
        }

        foreach ($customPropertiesCache as $fieldName => $property) {
            $rawValue = $this->getRawValue($property, $fieldName, $customProperties[$fieldName]);

            if ($rawValue !== null) {
                $customProperties[$propertyIndexReferenceFactory->createRaw($property)][] = $rawValue;
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

    private function getRawValue(core_kernel_classes_Property $property, string $fieldName, array $values): ?array
    {
        if (strpos($fieldName, 'HTMLArea') === 0) {
            $out = [];

            foreach ($values as $value) {
                $out[] = strip_tags((string)current($value));
            }

            return $out;
        }

        if (
            strpos($fieldName, 'RadioBox') === 0 ||
            strpos($fieldName, 'ComboBox') === 0 ||
            strpos($fieldName, 'CheckBox') === 0 ||
            strpos($fieldName, 'SearchTextBox') === 0 ||
            strpos($fieldName, 'SearchDropdown') === 0
        ) {
            $out = [];

            $request = new ValueCollectionSearchRequest();
            $request = $this->getRemoteListPropertySpecification()->isSatisfiedBy($property)
                ? $request->setValueCollectionUri($property->getRange()->getUri())
                : $request->setPropertyUri($property->getUri());

            $list = $this->getValueCollectionService()->findAll(new ValueCollectionSearchInput($request));

            foreach ($values as $value) {
                foreach ($value as $subValue) {
                    $listValue = $list->extractValueByUri(tao_helpers_Uri::decode((string)$subValue));

                    if ($listValue) {
                        $out[] = $listValue->getLabel();
                    }
                }
            }

            return [implode(', ', $out)];
        }

        return null;
    }

    private function getSearchTokenGenerator(): SearchTokenGenerator
    {
        $tokenGenerator = $this->getServiceManager()->getContainer()->get(SearchTokenGenerator::class);

        $this->propagate($tokenGenerator);

        return $tokenGenerator;
    }

    private function getPropertyIndexReferenceFactory(): PropertyIndexReferenceFactory
    {
        return $this->getServiceManager()->getContainer()->get(PropertyIndexReferenceFactory::class);
    }

    private function getValueCollectionService(): ValueCollectionService
    {
        return $this->getServiceManager()->getContainer()->get(ValueCollectionService::SERVICE_ID);
    }

    private function getRemoteListPropertySpecification(): RemoteListPropertySpecification
    {
        return $this->getServiceManager()->getContainer()->get(RemoteListPropertySpecification::class);
    }
}
