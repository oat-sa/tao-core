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
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use core_kernel_classes_Property;

// @TODO Use redis to to avoid memory issues
class ClassMetadataMapper
{
    private $map = [];

    public function add(
        core_kernel_classes_Property $originalProperty,
        core_kernel_classes_Property $clonedProperty
    ): void {
        $this->map[$clonedProperty->getUri()] = $originalProperty->getUri();
    }

    public function get(string $propertyUri): ?string
    {
        return $this->map[$propertyUri] ?? null;
    }
}
