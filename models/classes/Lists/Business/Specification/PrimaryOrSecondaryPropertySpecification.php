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
use oat\tao\model\Specification\PropertySpecificationInterface;

class PrimaryOrSecondaryPropertySpecification implements PropertySpecificationInterface
{
    /** @var bool[] */
    private $cache = [];

    /** @var PropertySpecificationInterface */
    private $primaryPropertySpecification;

    /** @var PropertySpecificationInterface */
    private $secondaryPropertySpecification;

    public function __construct(
        PropertySpecificationInterface $primaryPropertySpecification,
        PropertySpecificationInterface $secondaryPropertySpecification
    ) {
        $this->primaryPropertySpecification = $primaryPropertySpecification;
        $this->secondaryPropertySpecification = $secondaryPropertySpecification;
    }

    public function isSatisfiedBy(core_kernel_classes_Property $property): bool
    {
        if (!array_key_exists($property->getUri(), $this->cache)) {
            $this->cache[$property->getUri()] = $this->secondaryPropertySpecification->isSatisfiedBy($property)
                || $this->primaryPropertySpecification->isSatisfiedBy($property);
        }

        return $this->cache[$property->getUri()];
    }
}
