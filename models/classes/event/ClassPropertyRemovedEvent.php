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

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use JsonSerializable;
use oat\oatbox\event\Event;

/**
 * ClassPropertyRemovedEvent
 *
 * @package oat\tao\model\event
 */
class ClassPropertyRemovedEvent implements Event, JsonSerializable
{
    /** @var core_kernel_classes_Class */
    private $class;

    /** @var core_kernel_classes_Property */
    private $property;

    public function __construct(core_kernel_classes_Class $class, core_kernel_classes_Property $property)
    {
        $this->class = $class;
        $this->property = $property;
    }

    public function getName(): string
    {
        return get_class($this);
    }

    public function jsonSerialize()
    {
        return [
            'class_uri' => $this->class->getUri(),
            'class_label' => $this->class->getLabel(),
            'property_uri' => $this->property->getUri(),
            'property_label' => $this->property->getLabel()
        ];
    }
}
