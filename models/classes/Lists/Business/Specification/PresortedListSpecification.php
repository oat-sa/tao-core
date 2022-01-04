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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

namespace oat\tao\model\Lists\Business\Specification;

use oat\tao\model\Specification\PropertySpecificationInterface;
use oat\tao\model\TaoOntology;
use core_kernel_classes_Property;
use tao_helpers_form_elements_Radiobox;
use tao_helpers_form_elements_Combobox;
use tao_helpers_form_elements_Checkbox;
use core_kernel_classes_Class;


/**
 * Presorted lists are lists that are considered "already sorted", so UI
 * components can skip sorting the options for them.
 *
 * @todo Unit tests
 */
class PresortedListSpecification implements PropertySpecificationInterface
{
    /** @var core_kernel_classes_Class */
    private $rootListClass;

    public function __construct(core_kernel_classes_Class $rootListClass = null)
    {
        $this->rootListClass = $rootListClass ??
            new core_kernel_classes_Class(TaoOntology::CLASS_URI_LIST);
    }

    public function isSatisfiedBy(core_kernel_classes_Property $property): bool
    {
        $property->feed();

        if (!$this->isList($property->getRange())) {
            return false;
        }

        $widgetResource = $property->getWidget();
        if (null === $widgetResource) {
            return false;
        }

        return in_array($widgetResource->getUri(), $this->getPresortedWidgetTypes());
    }

    protected function getPresortedWidgetTypes(): array
    {
        return [
            // Used by Single choice lists with Radio buttons (aka "list")
            tao_helpers_form_elements_Radiobox::WIDGET_ID,

            // Used by Single choice lists with Drop downs (aka "longlist")
            tao_helpers_form_elements_Combobox::WIDGET_ID,

            // Used by Multiple choice lists with Check box (aka "multilist")
            tao_helpers_form_elements_Checkbox::WIDGET_ID
        ];
    }

    private function isList($resource): bool
    {
        if (!$resource instanceof core_kernel_classes_Class) {
            return false;
        }

        return $resource->isClass() && $resource->isSubClassOf($this->rootListClass);
    }
}
