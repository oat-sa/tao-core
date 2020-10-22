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
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\helpers;

trait NoPrivacyTrait
{
    public function getPrivateProperty($object, $propertyName)
    {
        $property = new \ReflectionProperty(get_class($object), $propertyName);
        $property->setAccessible(true);
        $value = $property->getValue($object);
        $property->setAccessible(false);
        return $value;
    }

    public function setPrivateProperty($object, $propertyName, $value)
    {
        $property = new \ReflectionProperty(get_class($object), $propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }

    public function invokePrivateMethod($object, $methodName, $parameters)
    {
        $method = new \ReflectionMethod(get_class($object), $methodName);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $parameters);
        $method->setAccessible(false);
        return $result;
    }
}
