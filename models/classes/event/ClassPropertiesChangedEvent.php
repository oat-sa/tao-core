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

namespace oat\tao\model\event;

use oat\oatbox\event\Event;

class ClassPropertiesChangedEvent implements Event
{
    /**
     * @var array[{property: \core_kernel_classes_Property, oldProperty: \oat\tao\model\dto\OldProperty}]
     *
     * list of properties that were changed, with the following structure:
     *  $properties = [
     *      [
     *          'class' => (\core_kernel_classes_Class), current class where changes were made
     *          'property' => (\core_kernel_classes_Property), this is the current property
     *          'oldProperty' => (\oat\tao\model\dto\OldProperty) this is a DTO object representing the old values
     *      ],
     *      ...
     *  ];
     *
     */
    private $properties;

    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    public function getName(): string
    {
        return self::class;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}
