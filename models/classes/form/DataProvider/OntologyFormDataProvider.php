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
 * Copyright (c) 2023-2024 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\form\DataProvider;

use core_kernel_classes_Class;
use core_kernel_classes_Literal;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\tao\model\Language\Business\Specification\LanguageClassSpecification;
use oat\tao\model\Language\Filter\LanguageAllowedFilter;
use oat\tao\model\Language\Service\LanguageListElementSortService;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\TaoOntology;
use tao_helpers_form_elements_Readonly;
use tao_helpers_form_GenerisFormFactory;
use tao_helpers_Uri;

class OntologyFormDataProvider implements FormDataProviderInterface
{
    use OntologyAwareTrait;

    private LanguageClassSpecification $languageClassSpecification;
    private LanguageListElementSortService $languageListElementSortService;
    private ValueCollectionService $valueCollectionService;
    private LanguageAllowedFilter $languageAllowedFilter;

    public function __construct(
        LanguageClassSpecification $languageClassSpecification,
        LanguageListElementSortService $languageListElementSortService,
        ValueCollectionService $valueCollectionService,
        LanguageAllowedFilter $languageAllowedFilter
    ) {
        $this->languageClassSpecification = $languageClassSpecification;
        $this->languageListElementSortService = $languageListElementSortService;
        $this->valueCollectionService = $valueCollectionService;
        $this->languageAllowedFilter = $languageAllowedFilter;
    }

    public function preloadFormData(string $classUri, string $topClassUri, string $elementUri, string $language): void
    {
        // Not implemented for Ontology Data Provider
    }

    public function getClassProperties(\core_kernel_classes_Class $class, \core_kernel_classes_Class $topClass): array
    {
        return tao_helpers_form_GenerisFormFactory::getClassProperties($class, $topClass);
    }

    public function getDataToFeedProperty(core_kernel_classes_Property $property): array
    {
        return [
            $property->getWidget(),
            $property->getRange(),
            $property->getDomain(),
        ];
    }

    public function getDescriptionFromTranslatedPropertyLabel(
        core_kernel_classes_Property $property,
        string $language
    ): ?string {
        $propertyLabel = current(
            $property->getPropertyValues(
                $this->getProperty(OntologyRdfs::RDFS_LABEL),
                ['lg' => $language, 'one' => true]
            )
        );

        if (empty($propertyLabel)) {
            $propertyLabel = $property->getLabel();
        }

        if (empty(trim($propertyLabel))) {
            return str_replace(LOCAL_NAMESPACE, '', $property->getUri());
        }

        return $propertyLabel;
    }

    public function getPropertyListElementOptions(
        core_kernel_classes_Property $property,
        ?core_kernel_classes_Property $parentProperty,
        $instance
    ): array {
        $options = [];
        $values = $this->getListValues($instance, $property, $parentProperty);

        if ($this->languageClassSpecification->isSatisfiedBy($property->getRange())) {
            $values = $this->languageListElementSortService->getSortedListCollectionValues(
                $this->languageAllowedFilter->filterByValueCollection($values)
            );
        }

        foreach ($values as $value) {
            $encodedUri = tao_helpers_Uri::encode($value->getUri());
            $options[$encodedUri] = [$encodedUri, $value->getLabel()];
        }

        return $options;
    }

    public function getPropertyNotListElementOptions(core_kernel_classes_Property $property): array
    {
        $options = [];

        foreach ($property->getRange()->getInstances(true) as $rangeInstance) {
            $level = $rangeInstance->getOnePropertyValue(
                new core_kernel_classes_Property(TaoOntology::PROPERTY_LIST_LEVEL)
            );
            if (null === $level) {
                $encodedUri = tao_helpers_Uri::encode($rangeInstance->getUri());
                $options[$encodedUri] = [$encodedUri, $rangeInstance->getLabel()];
            } else {
                $level = ($level instanceof core_kernel_classes_Resource)
                    ? $level->getUri()
                    : (string)$level;
                $options[$level] = [
                    tao_helpers_Uri::encode($rangeInstance->getUri()),
                    $rangeInstance->getLabel()
                ];
            }
        }

        ksort($options);

        return $options;
    }

    public function getPropertyValidators(core_kernel_classes_Property $property): array
    {
        return ValidationRuleRegistry::getRegistry()->getValidators($property);
    }

    public function getPropertyInstanceValues(core_kernel_classes_Property $property, $instance, $element): array
    {
        $output = [];
        $values = $instance->getPropertyValuesCollection($property);

        foreach ($values as $value) {
            if ($value instanceof core_kernel_classes_Resource) {
                $elementValue = $element instanceof tao_helpers_form_elements_Readonly
                    ? $value->getLabel()
                    : $value->getUri();
                $elementValueUri = $value->getUri();
            } elseif ($value instanceof core_kernel_classes_Literal) {
                $elementValue = (string)$value;
                $elementValueUri = $elementValue;
            } else {
                continue;
            }

            if ($this->isPropertyList($property)) {
                $searchRequest = new ValueCollectionSearchRequest();
                $searchRequest->setValueCollectionUri($property->getRange()->getUri());
                $searchRequest->setUris($elementValueUri);
                $valueCollection = $this->valueCollectionService->findAll(
                    new ValueCollectionSearchInput($searchRequest)
                );

                foreach ($valueCollection as $v) {
                    $output[] = [$v->getUri(), $v->getLabel()];
                }
            } else {
                $output[] = [$elementValue];
            }
        }

        return $output;
    }

    public function isPropertyList($property): bool
    {
        if ($property->getRange() === null) {
            return false;
        }

        if (!$property->getRange()->isClass()) {
            return false;
        }

        return $property->getRange()->isSubClassOf(
            new core_kernel_classes_Class(TaoOntology::CLASS_URI_LIST)
        );
    }

    public function getPropertyGUIOrder(core_kernel_classes_Property $property): array
    {
        $guiOrderProperty = new core_kernel_classes_Property(TaoOntology::PROPERTY_GUI_ORDER);

        return $property->getPropertyValues($guiOrderProperty);
    }

    //--------------END OF PUBLIC INTERFACE-------------

    private function getListValues(
        $instance,
        core_kernel_classes_Property $property,
        core_kernel_classes_Property $parentProperty = null
    ): ValueCollection {
        $searchRequest = (new ValueCollectionSearchRequest())->setValueCollectionUri($property->getRange()->getUri());

        if ($instance instanceof core_kernel_classes_Resource) {
            $selectedValue = $instance->getOnePropertyValue($property);

            if ($selectedValue instanceof core_kernel_classes_Literal && !empty($selectedValue->literal)) {
                $searchRequest->setSelectedValues($selectedValue->literal);
            }

            if ($parentProperty) {
                $parentPropertyValues = [];

                foreach ($instance->getPropertyValuesCollection($parentProperty) as $parentPropertyValue) {
                    $parentPropertyValues[] = (string)$parentPropertyValue;
                }
                $searchRequest->setPropertyUri($property->getUri());
                $searchRequest->setParentListValues(...$parentPropertyValues);
            }
        }

        return $this->valueCollectionService->findAll(new ValueCollectionSearchInput($searchRequest));
    }
}
