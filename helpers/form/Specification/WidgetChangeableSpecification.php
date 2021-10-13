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

namespace oat\tao\helpers\form\Specification;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\model\Specification\PropertySpecificationInterface;
use tao_helpers_form_elements_Combobox;

class WidgetChangeableSpecification
{
    private const RESTRICTED_TYPES = [
        tao_helpers_form_elements_Combobox::WIDGET_ID,
        SearchDropdown::WIDGET_ID
    ];

    /**
     * @var PropertySpecificationInterface
     */
    private $primaryOrSecondaryPropertySpecification;

    public function __construct(PropertySpecificationInterface $primaryOrSecondaryPropertySpecification)
    {
        $this->primaryOrSecondaryPropertySpecification = $primaryOrSecondaryPropertySpecification;
    }

    public function isSatisfiedBy(string $newWidgetId, core_kernel_classes_Property $property): bool
    {
        if (!$this->primaryOrSecondaryPropertySpecification->isSatisfiedBy($property)) {
            return true;
        }

        if (!$property->getWidget() instanceof core_kernel_classes_Resource) {
            return true;
        }

        if (!in_array($property->getWidget()->getUri(), self::RESTRICTED_TYPES)) {
            return true;
        }

        return in_array($newWidgetId, self::RESTRICTED_TYPES);
    }
}
