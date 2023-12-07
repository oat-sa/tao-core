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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\form\DataProvider;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use oat\generis\model\data\Ontology;
use oat\generis\model\kernel\persistence\DataProvider\form\DTO\FormDTO;
use oat\generis\model\kernel\persistence\DataProvider\form\FormDTOProviderInterface;
use oat\tao\helpers\form\ValidationRuleRegistry;
use tao_helpers_Uri;

class BulkFormDataProvider implements FormDataProviderInterface
{
    private ?FormDTO $formData = null;

    private FormDTOProviderInterface $formDTOProvider;
    private Ontology $ontology;

    public function __construct(Ontology $ontology, FormDTOProviderInterface $formDTOProvider)
    {
        $this->formDTOProvider = $formDTOProvider;
        $this->ontology = $ontology;
    }

    public function preloadFormData(string $classUri, string $topClassUri, string $elementUri, string $language): void
    {
        $this->formData = $this->formDTOProvider->get($classUri, $topClassUri, $elementUri, $language);
    }

    public function getClassProperties(core_kernel_classes_Class $class, core_kernel_classes_Class $topClass): array
    {
        $classProperties = [];
        foreach ($this->getFormData()->getProperties() as $formProperty) {
            $property = $this->ontology->getProperty($formProperty->getPropertyUri());
            $classProperties[$formProperty->getPropertyUri()] = $property;
        }

        return $classProperties;
    }

    public function getDataToFeedProperty(core_kernel_classes_Property $property): array
    {
        $property = $this->getFormData()->getProperty($property->getUri());

        return [
            $property->getWidgetUri(),
            $property->getRangeUri(),
            $property->getClassUri(),
        ];
    }

    public function getDescriptionFromTranslatedPropertyLabel(
        core_kernel_classes_Property $property,
        string $language
    ): ?string {
        return $this->getFormData()->getProperty($property->getUri())->getLabel();
    }

    public function getPropertyListElementOptions(
        core_kernel_classes_Property $property,
        ?core_kernel_classes_Property $parentProperty,
        $instance
    ): array {
        $options = [];
        foreach ($this->getFormData()->getProperty($property->getUri())->getOptions() as $option) {
            $encodedUri = tao_helpers_Uri::encode($option->getUri());
            $options[$encodedUri] = [$encodedUri, $option->getLabel()];
        }

        return $options;
    }

    public function getPropertyNotListElementOptions(core_kernel_classes_Property $property): array
    {
        $options = [];
        foreach ($this->getFormData()->getProperty($property->getUri())->getOptions() as $option) {
            if ($option->getLevel() === null) {
                $encodedUri = tao_helpers_Uri::encode($option->getUri());
                $options[$encodedUri] = [$encodedUri, $option->getLabel()];
            } else {
                $options[$option->getLevel()] = [
                    tao_helpers_Uri::encode($option->getUri()),
                    $option->getLabel()
                ];
            }
        }

        ksort($options);

        return $options;
    }

    public function getPropertyValidators(core_kernel_classes_Property $property): array
    {
        $validators = [];
        foreach ($this->getFormData()->getProperty($property->getUri())->getValidationRule() as $validatorId) {
            $validators[] = ValidationRuleRegistry::getRegistry()->get($validatorId);
        }

        return $validators;
    }

    public function getPropertyInstanceValues(core_kernel_classes_Property $property, $instance, $element): array
    {
        $values = $this->getFormData()->getProperty($property->getUri())->getValue();
        $output = [];
        foreach ($values as $value) {
            if ($this->isPropertyList($property)) {
                $output[] = [
                    $value,
                    $this->getFormData()->getProperty($property->getUri())->getOption($value)->getLabel()
                ];
            } else {
                $output[] = [$value];
                $element->setValue($value);
            }
        }

        return $output;
    }

    public function isPropertyList(core_kernel_classes_Property $property): bool
    {
        return $this->getFormData()->getProperty($property->getUri())->isList();
    }

    public function getPropertyGUIOrder(core_kernel_classes_Property $property): array
    {
        $order = $this->getFormData()->getProperty($property->getUri())->getGuiOrder();

        return $order !== null ? [$order] : [];
    }

    //---------------END OF PUBLIC INTERFACE----------------

    private function getFormData(): FormDTO
    {
        if ($this->formData === null) {
            throw new DataProviderException('Form data was not loaded');
        }

        return $this->formData;
    }
}
