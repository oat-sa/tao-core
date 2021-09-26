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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\dto;

use core_kernel_classes_Resource;
use oat\generis\model\resource\DependsOnPropertyCollection;

class OldProperty
{
    /** @var string */
    private $label;

    /** @var core_kernel_classes_Resource|null */
    private $propertyType;

    /** @var string|null */
    private $rangeUri;

    /** @var array */
    private $validationRules;

    /** @var DependsOnPropertyCollection|null */
    private $dependsOnPropertyCollection;

    public function __construct(
        string $label,
        ?core_kernel_classes_Resource $propertyType,
        string $rangeUri = null,
        array $validationRules = [],
        DependsOnPropertyCollection $dependsOnPropertyCollection = null
    ) {
        $this->label = $label;
        $this->propertyType = $propertyType;
        $this->rangeUri = $rangeUri;
        $this->validationRules = $validationRules;
        $this->dependsOnPropertyCollection = $dependsOnPropertyCollection;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getPropertyType(): ?core_kernel_classes_Resource
    {
        return $this->propertyType;
    }

    public function getRangeUri(): ?string
    {
        return $this->rangeUri;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getDependsOnPropertyCollection(): ?DependsOnPropertyCollection
    {
        return $this->dependsOnPropertyCollection;
    }
}
