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

namespace oat\tao\helpers\form\Factory;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\tao\model\Specification\ClassSpecificationInterface;
use oat\tao\model\Specification\PropertySpecificationInterface;
use tao_helpers_form_elements_xhtml_Combobox;
use tao_helpers_form_FormFactory;
use tao_helpers_Uri;
use tao_models_classes_ListService;

class ElementPropertyListValuesFactory
{
    /** @var PropertySpecificationInterface */
    private $primaryPropertySpecification;

    /** @var tao_helpers_form_elements_xhtml_Combobox */
    private $element;

    /** @var tao_models_classes_ListService|null */
    private $listService;

    /** @var ClassSpecificationInterface */
    private $remoteListClassSpecification;

    public function __construct(
        PropertySpecificationInterface $primaryPropertySpecification,
        ClassSpecificationInterface $remoteListClassSpecification,
        tao_models_classes_ListService $listService = null
    ) {
        $this->primaryPropertySpecification = $primaryPropertySpecification;
        $this->remoteListClassSpecification = $remoteListClassSpecification;
        $this->listService = $listService ?? tao_models_classes_ListService::singleton();
    }

    public function withElement(tao_helpers_form_elements_xhtml_Combobox $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function create(
        int $index,
        core_kernel_classes_Resource $range = null
    ): tao_helpers_form_elements_xhtml_Combobox {
        $element = $this->createBasic($index, 'range_list', 'property-template list-template');
        $element->disable();

        $listOptions = [];

        foreach ($this->listService->getLists() as $list) {
            $encodedListUri = tao_helpers_Uri::encode($list->getUri());
            $listOptions[$encodedListUri] = $list->getLabel();

            if (null !== $range && $range->getUri() === $list->getUri()) {
                $element->setValue($list->getUri());
            }

            if ($this->remoteListClassSpecification->isSatisfiedBy($list)) {
                $element->addOptionAttribute(
                    $encodedListUri,
                    'data-remote-list',
                    'true'
                );
            }
        }

        $element->setOptions($listOptions);

        return $element;
    }

    public function createEmpty(
        core_kernel_classes_Property $property,
        array $newData,
        bool $checkRange,
        int $index
    ): tao_helpers_form_elements_xhtml_Combobox {
        $element = $this->createBasic($index, 'range', 'property-listvalues property');

        if (
            $this->becameSecondaryProperty($index, $newData)
            || $this->primaryPropertySpecification->isSatisfiedBy($property)
        ) {
            $element->disable();
            $element->addAttribute(
                'data-force-disabled',
                'true'
            );
            $element->addAttribute(
                'data-disabled-message',
                __('The field "List" is disabled because the property is part of a dependency')
            );
        }

        if ($checkRange) {
            $element->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        }

        return $element;
    }

    private function createBasic(int $index, string $suffix, string $classes): tao_helpers_form_elements_xhtml_Combobox
    {
        $element = $this->createElement($index, $suffix);
        $element->setDescription(__('List values'));
        $element->addAttribute('class', $classes);
        $element->setEmptyOption(' --- ' . __('select') . ' --- ');

        return $element;
    }

    private function createElement(int $index, string $suffix): tao_helpers_form_elements_xhtml_Combobox
    {
        return $this->element ?? tao_helpers_form_FormFactory::getElement("{$index}_$suffix", 'Combobox');
    }

    private function becameSecondaryProperty(int $index, array $newData): bool
    {
        return !empty(trim($newData[$index . '_depends-on-property'] ?? ''));
    }
}
