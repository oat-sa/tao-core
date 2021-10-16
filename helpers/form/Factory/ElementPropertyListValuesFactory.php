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

use core_kernel_classes_Resource;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\Specification\ClassSpecificationInterface;
use tao_helpers_form_elements_xhtml_Combobox;
use tao_helpers_Uri;
use tao_models_classes_ListService;

class ElementPropertyListValuesFactory extends AbstractElementPropertyListValuesFactory
{
    public const OPTION_REMOTE_LIST_ATTRIBUTE = 'data-remote-list';

    /** @var tao_models_classes_ListService|null */
    private $listService;

    /** @var ClassSpecificationInterface */
    private $remoteListClassSpecification;

    public function __construct(
        ClassSpecificationInterface $remoteListClassSpecification,
        tao_models_classes_ListService $listService = null
    ) {
        $this->remoteListClassSpecification = $remoteListClassSpecification;
        $this->listService = $listService ?? tao_models_classes_ListService::singleton();
    }

    public function create(ContextInterface $context): tao_helpers_form_elements_xhtml_Combobox
    {
        /** @var int $index */
        $index = $context->getParameter(ElementFactoryContext::PARAM_INDEX);

        /** @var mixed|core_kernel_classes_Resource|null $range */
        $range = $context->getParameter(ElementFactoryContext::PARAM_RANGE);

        $element = $this->createElement($index, 'range_list');
        $element->setDescription(__('List values'));
        $element->addAttribute('class', 'property-template list-template');
        $element->setEmptyOption(' --- ' . __('select') . ' --- ');
        $element->addAttribute(self::PROPERTY_LIST_ATTRIBUTE, true);
        $element->disable();

        $listOptions = [];

        foreach ($this->listService->getLists() as $list) {
            $encodedListUri = tao_helpers_Uri::encode($list->getUri());
            $listOptions[$encodedListUri] = $list->getLabel();

            if ($range instanceof core_kernel_classes_Resource && $range->getUri() === $list->getUri()) {
                $element->setValue($list->getUri());
            }

            if ($this->remoteListClassSpecification->isSatisfiedBy($list)) {
                $element->addOptionAttribute(
                    $encodedListUri,
                    self::OPTION_REMOTE_LIST_ATTRIBUTE,
                    'true'
                );
            }
        }

        $element->setOptions($listOptions);

        return $element;
    }
}
