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

namespace oat\tao\helpers\form;

use common_Logger;
use tao_helpers_Uri;
use tao_helpers_Context;
use core_kernel_classes_Class;
use oat\tao\model\TaoOntology;
use core_kernel_classes_Literal;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;
use oat\generis\model\OntologyRdfs;
use Psr\Container\ContainerInterface;
use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\form\elements\TreeAware;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use tao_helpers_form_elements_AsyncFile as AsyncFile;
use tao_helpers_form_elements_Authoring as Authoring;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Business\Contract\ListElementSorterInterface;
use tao_helpers_form_elements_GenerisAsyncFile as GenerisAsyncFile;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Language\Business\Specification\LanguageClassSpecification;
use oat\tao\model\Language\Service\LanguageListElementSortService;

class ElementMapFactory extends ConfigurableService
{
    public const SERVICE_ID = 'tao/ElementMapFactory';

    /** @var core_kernel_classes_Resource */
    private $instance;

    public function withInstance(core_kernel_classes_Resource $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    public function create(core_kernel_classes_Property $property): ?tao_helpers_form_FormElement
    {
        // Create the element from the right widget
        $property->feed();

        $widgetResource = $property->getWidget();
        if (null === $widgetResource) {
            return null;
        }

        $widgetUri   = $widgetResource->getUri();
        $propertyUri = $property->getUri();
        // Authoring widget is not used in standalone mode
        if (
            $widgetUri === Authoring::WIDGET_ID
            && tao_helpers_Context::check('STANDALONE_MODE')
        ) {
            return null;
        }

        // Horrible hack to fix file widget
        if ($widgetUri === AsyncFile::WIDGET_ID) {
            $widgetResource = new core_kernel_classes_Resource(GenerisAsyncFile::WIDGET_ID);
        }

        $element = tao_helpers_form_FormFactory::getElementByWidget(
            tao_helpers_Uri::encode($propertyUri),
            $widgetResource
        );

        if (null === $element) {
            return null;
        }

        $isListsDependencyEnabled = $this->getFeatureFlagChecker()->isEnabled(
            FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED
        );

        $parentProperty = null;

        if ($isListsDependencyEnabled) {
            $parentProperty = $this->getParentProperty($property);

            if ($parentProperty) {
                $element->addAttribute('data-depends-on-property', tao_helpers_Uri::encode($parentProperty->getUri()));
            }
        }

        if ($element->getWidget() !== $widgetUri) {
            common_Logger::w(sprintf(
                'Widget definition differs from implementation: %s != %s',
                $element->getWidget(),
                $widgetUri
            ));

            return null;
        }

        // Use the property label as element description
        $propDesc = (trim($property->getLabel()) !== '')
            ? $property->getLabel()
            : str_replace(LOCAL_NAMESPACE, '', $propertyUri);

        $element->setDescription($propDesc);

        if (method_exists($element, 'setOptions')) {
            // Multi elements use the property range as options
            $range = $property->getRange();

            if ($range !== null) {
                if ($element instanceof TreeAware) {
                    $options = $element->rangeToTree(
                        $propertyUri === OntologyRdfs::RDFS_RANGE
                            ? new core_kernel_classes_Class(OntologyRdfs::RDFS_RESOURCE)
                            : $range
                    );
                } else {
                    $options = [];

                    if ($this->isList($range)) {
                        $values = $this->getListValues($property, $range, $parentProperty);

                        if ($this->getLanguageClassSpecification()->isSatisfiedBy($range))
                        {
                            $values = $this->getLanguageListElementSortService()->getSortedListCollectionValues($values);
                        }

                        foreach ($values as $value) {
                            $encodedUri = tao_helpers_Uri::encode($value->getUri());
                            $options[$encodedUri] = [$encodedUri, $value->getLabel()];
                        }
                    } else {
                        foreach ($range->getInstances(true) as $rangeInstance) {
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
                    }

                    foreach ($options as [$uri, $label]) {
                        $options[$uri] = $label;
                    }

                    // Set the default value to an empty space
                    if (method_exists($element, 'setEmptyOption')) {
                        $element->setEmptyOption(' ');
                    }
                }

                // Complete the options listing
                $element->setOptions($options);
            }
        }

        foreach (ValidationRuleRegistry::getRegistry()->getValidators($property) as $validator) {
            $element->addValidator($validator);
        }

        return $element;
    }

    private function isList($range): bool
    {
        if (!$range->isClass()) {
            return false;
        }

        return $range->isSubClassOf(
            new core_kernel_classes_Class(TaoOntology::CLASS_URI_LIST)
        );
    }

    private function getParentProperty(core_kernel_classes_Property $property): ?core_kernel_classes_Property
    {
        $collection = $property->getDependsOnPropertyCollection();

        return $collection->offsetExists(0)
            ? $collection->offsetGet(0)
            : null;
    }

    private function getListValues(
        core_kernel_classes_Property $property,
        core_kernel_classes_Resource $range,
        core_kernel_classes_Property $parentProperty = null
    ): ValueCollection {
        $searchRequest = (new ValueCollectionSearchRequest())->setValueCollectionUri($range->getUri());

        if ($this->instance instanceof core_kernel_classes_Resource) {
            $selectedValue = $this->instance->getOnePropertyValue($property);

            if ($selectedValue instanceof core_kernel_classes_Literal && !empty($selectedValue->literal)) {
                $searchRequest->setSelectedValues($selectedValue->literal);
            }

            if ($parentProperty) {
                $parentPropertyValues = [];

                foreach ($this->instance->getPropertyValuesCollection($parentProperty) as $parentPropertyValue) {
                    $parentPropertyValues[] = (string)$parentPropertyValue;
                }
                $searchRequest->setPropertyUri($property->getUri());
                $searchRequest->setParentListValues(...$parentPropertyValues);
            }
        }

        return $this->getValueCollectionService()->findAll(new ValueCollectionSearchInput($searchRequest));
    }

    private function getValueCollectionService(): ValueCollectionService
    {
        return $this->getContainer()->get(ValueCollectionService::class);
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getContainer()->get(FeatureFlagChecker::class);
    }

    private function getLanguageClassSpecification(): LanguageClassSpecification
    {
        return $this->getContainer()->get(LanguageClassSpecification::class);
    }

    private function getLanguageListElementSortService(): ListElementSorterInterface
    {
        return $this->getContainer()->get(LanguageListElementSortService::class);
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getServiceManager()->getContainer();
    }
}
