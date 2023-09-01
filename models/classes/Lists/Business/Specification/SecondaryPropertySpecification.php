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

namespace oat\tao\model\Lists\Business\Specification;

use core_kernel_classes_Property;
use oat\tao\model\Context\ContextInterface;

class SecondaryPropertySpecification
{
    /** @var DependentPropertySpecification */
    private $dependentPropertySpecification;

    public function __construct(DependentPropertySpecification $dependentPropertySpecification)
    {
        $this->dependentPropertySpecification = $dependentPropertySpecification;
    }

    public function isSatisfiedBy(ContextInterface $context): bool
    {
        /** @var int $index */
        $index = $context->getParameter(PropertySpecificationContext::PARAM_FORM_INDEX);

        /** @var core_kernel_classes_Property $property */
        $property = $context->getParameter(PropertySpecificationContext::PARAM_PROPERTY);

        /** @var array $newData */
        $newData = $context->getParameter(PropertySpecificationContext::PARAM_FORM_DATA);

        $dependsOnProperty = $newData[$index . '_depends-on-property'] ?? null;

        if ($dependsOnProperty === null) {
            return $this->dependentPropertySpecification->isSatisfiedBy($property);
        }

        return !empty(trim($dependsOnProperty));
    }
}
