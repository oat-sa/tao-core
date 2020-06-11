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
 *
 *
 */

declare(strict_types=1);

namespace oat\tao\model\event;

use core_kernel_classes_Property;
use oat\generis\model\WidgetRdf;
use oat\oatbox\event\Event;
use oat\tao\model\WidgetDefinitions;

class PropertyChangedEvent implements Event
{
    private const EVENT_NAME = self::class;

    /** @var core_kernel_classes_Property */
    private $property;

    /** @var OldProperty */
    private $oldProperty;

    public function __construct(core_kernel_classes_Property $property, OldProperty $oldProperty)
    {
        $this->property = $property;
        $this->oldProperty = $oldProperty;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    /**
     * @return core_kernel_classes_Property
     */
    public function getProperty(): core_kernel_classes_Property
    {
        return $this->property;
    }

    /**
     * @return core_kernel_classes_Property
     */
    public function getOldProperty(): core_kernel_classes_Property
    {
        return $this->oldProperty;
    }

    public function isPropertyChanged(): bool
    {
        if ((string)$this->property->getLabel() !== $this->oldProperty->getLabel()) {
            return true;
        }

        $currentPropertyType = $this->property
            ->getOnePropertyValue(new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET));
        $oldPropertyType = $this->oldProperty->getPropertyType();

        if (null === $currentPropertyType && null === $oldPropertyType) {
            return false;
        }

        if (null !== $currentPropertyType && null === $oldPropertyType
            || null === $currentPropertyType && null !== $oldPropertyType) {
            return true;
        }

        $currentPropertyTypeUri = $currentPropertyType->getUri();

        if ($currentPropertyTypeUri !== $oldPropertyType->getUri()) {
            return true;
        }

        return false;
    }
}