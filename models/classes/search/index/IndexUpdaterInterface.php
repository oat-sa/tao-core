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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\search\index;

use Throwable;

interface IndexUpdaterInterface
{
    public const SERVICE_ID = 'tao/IndexUpdater';

    /**
     * @param array $properties
     *
     * @throws Throwable
     */
    public function updatePropertiesName(array $properties): void;

    /**
     * @param array $property
     *
     * @throws Throwable
     */
    public function deleteProperty(array $property): void;

    /**
     * @param string $typeOrId
     * @param array $parentClasses
     * @param string $propertyName
     * @param array $value
     */
    public function updatePropertyValue(string $typeOrId, array $parentClasses, string $propertyName, array $value): void;

    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasClassSupport(string $class): bool;
}
