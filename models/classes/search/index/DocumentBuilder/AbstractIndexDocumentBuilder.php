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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\index\DocumentBuilder;

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\WidgetRdf;
use oat\tao\model\search\index\IndexDocument;
use ArrayIterator;
use core_kernel_classes_Literal as Literal;
use core_kernel_classes_Resource;
use Iterator;
use oat\tao\model\WidgetDefinitions;

abstract class AbstractIndexDocumentBuilder implements IndexDocumentBuilderInterface
{
    use OntologyAwareTrait;
    const ALLOWED_DYNAMIC_TYPES = [
        WidgetDefinitions::PROPERTY_TEXTBOX,
        WidgetDefinitions::PROPERTY_TEXTAREA,
        WidgetDefinitions::PROPERTY_HTMLAREA,
        WidgetDefinitions::PROPERTY_CHECKBOX,
        WidgetDefinitions::PROPERTY_COMBOBOX,
        WidgetDefinitions::PROPERTY_RADIOBOX,
    ];
    
    /**
     * {@inheritdoc}
     */
    public function createDocumentFromArray(array $resource = [], string $rootResourceType = ""): IndexDocument
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
        
        if ($rootResourceType) {
            $body['type'] = $rootResourceType;
        }
        
        if (!is_array($body['type'])) {
            $body['type'] = [$body['type']];
        }
    
        $document = new IndexDocument(
            $resource['id'],
            $body,
            $indexProperties
        );
    
        return $document;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDynamicProperties(array $classes, core_kernel_classes_Resource $resource): Iterator
    {
        $customProperties = [];
        
        foreach ($classes as $class) {
            $properties = \tao_helpers_form_GenerisFormFactory::getClassProperties(
                $this->getClass($class)
            );
            
            foreach ($properties as $property) {
                /** @var core_kernel_classes_Resource $propertyType |null */
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
                $customPropertyLabel = $property->getLabel();
                
                if (false === $propertyTypeId) {
                    continue;
                }
                
                $fieldName = $propertyTypeId . '_' . \tao_helpers_Slug::create($customPropertyLabel);
                $propertyValue = $resource->getOnePropertyValue($property);
                
                if (null === $propertyValue) {
                    continue;
                }
                
                if ($propertyValue instanceof Literal) {
                    $customProperties[$fieldName][] = (string)$propertyValue;
                    $customProperties[$fieldName] = array_unique($customProperties[$fieldName]);
                    continue;
                }
                
                $customPropertiesValues = $resource->getPropertyValues($property);
                $customProperties[$fieldName] = array_map(
                    function (string $propertyValue): string {
                        return $this->getProperty($propertyValue)->getLabel();
                    },
                    $customPropertiesValues
                );
            }
        }
        
        return new ArrayIterator($customProperties);
    }
}
