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

namespace oat\tao\helpers\form\Decorator;

use tao_helpers_Uri;
use tao_helpers_form_Form;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use tao_helpers_form_FormElement;
use oat\generis\model\data\Ontology;
use tao_helpers_form_GenerisFormFactory;
use oat\tao\helpers\form\elements\ElementValue;
use oat\tao\helpers\form\elements\AbstractSearchElement;

class ElementDecorator
{
    /** @var Ontology */
    private $ontology;

    /** @var tao_helpers_form_FormElement */
    private $element;

    /** @var tao_helpers_form_Form */
    private $form;

    /** @var array */
    private $cache = [];

    public function __construct(
        Ontology $ontology,
        tao_helpers_form_Form $form,
        tao_helpers_form_FormElement $element
    ) {
        $this->ontology = $ontology;
        $this->form = $form;
        $this->element = $element;
    }

    public function getFormData(): array
    {
        return $this->form->getValues();
    }

    public function getName(): string
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $this->cache[__METHOD__] = $this->element->getName();
        }

        return $this->cache[__METHOD__];
    }

    public function getIndex(): int
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $this->cache[__METHOD__] = (int) (explode('_', $this->getName())[0] ?? 0);
        }

        return $this->cache[__METHOD__];
    }

    public function getProperty(): ?core_kernel_classes_Property
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $propertyUri = $this->getFormData()[$this->getIndex() . '_uri']
                ?? tao_helpers_Uri::decode($this->getName());

            $property = $this->ontology->getProperty($propertyUri);

            $this->cache[__METHOD__] = $property->exists()
                ? $property
                : null;
        }

        return $this->cache[__METHOD__];
    }

    public function getRangeClass(): ?core_kernel_classes_Class
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $uri = $this->getFormData()[$this->getIndex() . '_range'] ?? null;
            $range = empty($uri)
                ? null
                : $this->ontology->getClass(tao_helpers_Uri::decode($uri));

            $this->cache[__METHOD__] = $range ?? $this->getProperty()->getRange();
        }

        return $this->cache[__METHOD__];
    }

    public function getCurrentWidgetUri(): ?string
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $property = $this->getProperty();

            $this->cache[__METHOD__] = $property->getWidget() instanceof core_kernel_classes_Resource
                ? $property->getWidget()->getUri()
                : null;
        }

        return $this->cache[__METHOD__];
    }

    public function getNewWidgetUri(): ?string
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $this->cache[__METHOD__] = tao_helpers_form_GenerisFormFactory::getWidgetUriById(
                (string)$this->element->getRawValue()
            );
        }

        return $this->cache[__METHOD__];
    }

    public function getListValues(): array
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $listValues = array_filter(explode(',', $this->element->getInputValue() ?? ''));

            if (empty($listValues)) {
                $listValues = $this->element instanceof AbstractSearchElement
                    ? $this->element->getValues()
                    : [$this->element->getRawValue()];
            }

            $this->cache[__METHOD__] = $this->transformListValuesToUris($listValues);
        }

        return $this->cache[__METHOD__];
    }

    /**
     * @return ElementDecorator[]
     */
    public function getParentElementsDecorators(): array
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            if ($this->getProperty() === null) {
                return [];
            }

            $parentDecorators = [];

            foreach ($this->getProperty()->getDependsOnPropertyCollection() as $parentProperty) {
                $parentElement = $this->form->getElement(tao_helpers_Uri::encode($parentProperty->getUri()));

                if ($parentElement !== null) {
                    $parentDecorators[] = new self($this->ontology, $this->form, $parentElement);
                }
            }

            $this->cache[__METHOD__] = $parentDecorators;
        }

        return $this->cache[__METHOD__];
    }

    /**
     * @param string|array $values
     *
     * @return string[]
     */
    private function transformListValuesToUris($values): array
    {
        if (is_string($values)) {
            $values = [trim($values)];
        }

        if (!is_array($values)) {
            return [];
        }

        $uris = [];

        foreach ($values as $value) {
            $uri = $value instanceof ElementValue
                ? $value->getUri()
                : trim($value ?? '');

            $uris[tao_helpers_Uri::encode($uri)] = tao_helpers_Uri::decode($uri);
        }

        return array_filter($uris);
    }
}
