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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\DataAccess\Repository;

use InvalidArgumentException;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\helpers\form\elements\xhtml\SearchTextBox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_GenerisFormFactory;
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\Business\Contract\DependsOnPropertyRepositoryInterface;
use oat\tao\model\Lists\Business\Contract\ParentPropertyListRepositoryInterface;

class DependsOnPropertyRepository implements DependsOnPropertyRepositoryInterface
{
    public const DEPENDENT_RESTRICTED_TYPES = [
        tao_helpers_form_elements_Combobox::WIDGET_ID,
        SearchDropdown::WIDGET_ID,
        SearchTextBox::WIDGET_ID
    ];

    /** @var core_kernel_classes_Property[] */
    private $properties;

    /** @var PropertySpecificationInterface */
    private $remoteListPropertySpecification;

    /** @var PropertySpecificationInterface */
    private $dependentPropertySpecification;

    /** @var ParentPropertyListRepositoryInterface */
    private $parentPropertyListRepository;

    public function __construct(
        PropertySpecificationInterface $remoteListPropertySpecification,
        PropertySpecificationInterface $dependentPropertySpecification,
        ParentPropertyListRepositoryInterface $parentPropertyListRepository
    ) {
        $this->remoteListPropertySpecification = $remoteListPropertySpecification;
        $this->dependentPropertySpecification = $dependentPropertySpecification;
        $this->parentPropertyListRepository = $parentPropertyListRepository;
    }

    public function withProperties(array $properties)
    {
        $this->properties = $properties;
    }

    public function findAll(array $filter): DependsOnPropertyCollection
    {
        $collection = new DependsOnPropertyCollection();

        if (empty($filter[self::FILTER_PROPERTY]) && empty($filter[self::FILTER_CLASS])) {
            throw new InvalidArgumentException('class or property filter need to be provided');
        }

        /** @var core_kernel_classes_Property $property */
        $property = $filter[self::FILTER_PROPERTY] ?? null;

        if (!$this->isPropertyWidgetAllowed($filter)) {
            return $collection;
        }

        /** @var core_kernel_classes_Class $class */
        $class = $property
            ? ($property->getDomain()->count() > 0 ? $property->getDomain()->get(0) : null)
            : $filter[self::FILTER_CLASS] ?? null;

        if ($class === null || (empty($filter[self::FILTER_LIST_URI]) && $property && !$property->getRange())) {
            return $collection;
        }

        if (isset($filter[self::FILTER_LIST_URI]) && $property && !$property->getRange()) {
            $property = null;
        }

        $listUri = $this->getListUri($filter, $property);

        if (!$listUri) {
            return $collection;
        }

        if ($property && !$this->isRemoteListProperty($property)) {
            return $collection;
        }

        $parentPropertiesUris = $this->parentPropertyListRepository->findAllUris(
            [
                'listUri' => $listUri
            ]
        );

        if (empty($parentPropertiesUris)) {
            return $collection;
        }

        /** @var core_kernel_classes_Property $property */
        foreach ($this->getProperties($class) as $classProperty) {
            if ($property && $this->isPropertyNotSupported($property, $classProperty)) {
                continue;
            }

            if ($this->isParentProperty($classProperty, $parentPropertiesUris)) {
                $collection->append(new DependsOnProperty($classProperty));

                continue;
            }

            if ($property && $this->isSameParentProperty($property, $classProperty)) {
                return $collection;
            }
        }

        return $collection;
    }

    private function getListUri(array $options, core_kernel_classes_Property $property = null): ?string
    {
        if (empty($options['listUri']) && $property && !$property->getRange()) {
            return null;
        }

        return empty($options['listUri']) && $property
            ? $property->getRange()->getUri()
            : ($options['listUri'] ?? null);
    }

    private function isSameParentProperty(
        core_kernel_classes_Property $property,
        core_kernel_classes_Property $classProperty
    ): bool {
        $parentProperty = $classProperty->getDependsOnPropertyCollection()->current();

        return $parentProperty && $property->getUri() === $parentProperty->getUri();
    }

    private function isRemoteListProperty(core_kernel_classes_Property $property): bool
    {
        return $property->getDomain()->count() && $this->remoteListPropertySpecification->isSatisfiedBy($property);
    }

    private function isParentProperty(core_kernel_classes_Property $classProperty, array $parentPropertiesUris): bool
    {
        return !$this->dependentPropertySpecification->isSatisfiedBy($classProperty)
            && in_array($classProperty->getUri(), $parentPropertiesUris, true)
            && $this->isParentPropertyWidgetAllowed($classProperty);
    }

    private function isPropertyNotSupported(
        core_kernel_classes_Property $property,
        core_kernel_classes_Property $classProperty
    ): bool {
        return $property->getUri() === $classProperty->getUri()
            || !$this->remoteListPropertySpecification->isSatisfiedBy($classProperty);
    }

    private function getProperties(core_kernel_classes_Class $class): array
    {
        return $this->properties ?? tao_helpers_form_GenerisFormFactory::getClassProperties($class);
    }

    private function isPropertyWidgetAllowed(array $filter): bool
    {
        /** @var core_kernel_classes_Property $property */
        $property = $filter[self::FILTER_PROPERTY] ?? null;

        $widgetUri = $filter[self::FILTER_PROPERTY_WIDGET_URI] ?? null;
        $widgetUri = $widgetUri ?? ($property && $property->getWidget() ? $property->getWidget()->getUri() : null);

        if ($widgetUri === null) {
            return true;
        }

        return in_array($widgetUri, self::DEPENDENT_RESTRICTED_TYPES, true);
    }

    private function isParentPropertyWidgetAllowed(core_kernel_classes_Property $property): bool
    {
        return $property->getWidget()
            && in_array($property->getWidget()->getUri(), self::DEPENDENT_RESTRICTED_TYPES, true);
    }
}
