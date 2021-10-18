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

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use tao_helpers_form_Form;
use tao_helpers_form_FormElement;
use tao_helpers_form_GenerisFormFactory as tao_helpers_form_GenerisFormFactoryAlias;
use tao_helpers_Uri;

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

    public function getIndex(): int
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $this->cache[__METHOD__] = (int)(explode('_', $this->element->getName())[0] ?? 0);
        }

        return $this->cache[__METHOD__];
    }

    public function getProperty(): ?core_kernel_classes_Property
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $propertyUri = $this->getFormData()[$this->getIndex() . '_uri'] ?? null;

            $this->cache[__METHOD__] = $propertyUri === null
                ? null
                : $this->ontology->getProperty($propertyUri);
        }

        return $this->cache[__METHOD__];
    }

    public function getClassByInputValue(): ?core_kernel_classes_Class
    {
        if (!array_key_exists(__METHOD__, $this->cache)) {
            $this->cache[__METHOD__] = empty($this->element->getRawValue())
                ? null
                : $this->ontology->getClass(tao_helpers_Uri::decode((string)$this->element->getRawValue()));
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
            $this->cache[__METHOD__] = tao_helpers_form_GenerisFormFactoryAlias::getWidgetUriById(
                (string)$this->element->getInputValue()
            );
        }

        return $this->cache[__METHOD__];
    }
}
