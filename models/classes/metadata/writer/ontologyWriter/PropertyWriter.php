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
 * Copyright (c) 2016 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\metadata\writer\ontologyWriter;

use oat\generis\model\OntologyAwareTrait;

/**
 * Class PropertyWriter
 * @package oat\tao\model\metadata\writer\ontologyWriter
 */
class PropertyWriter implements OntologyWriter
{
    use OntologyAwareTrait;

    /**
     * Property used to write a value property in $resource
     *
     * @var \core_kernel_classes_Property
     */
    protected $property;

    /**
     * Resource target by write method
     *
     * @var \core_kernel_classes_Resource
     */
    protected $resource = null;

    /**
     * PropertyWriter constructor.
     *
     * @todo if property exists?
     *
     * @param array $params
     * @throws \Exception
     */
    public function __construct(array $params = [])
    {
        if (!isset($params['propertyUri'])) {
            throw new \Exception();
        }
        $this->property = $this->getProperty($params['propertyUri']);
    }

    /**
     * Validate if value is writable by current writer
     *
     * @param $data
     * @return bool
     */
    public function validate($data)
    {
        return count($data) == 1 && ! empty(array_pop($data));
    }

    /**
     * Write a value to a $resource
     *
     * @todo if write value is false  ? Throw specific exception ?
     *
     * @param \core_kernel_classes_Resource $resource
     * @param $data
     * @return bool
     */
    public function writeValue(\core_kernel_classes_Resource $resource, $data)
    {
        if ($this->validate($data)) {
            $propertyValue = array_pop($data);
            $resource->setPropertyValue($this->property, $propertyValue);
            echo 'Valid property "'. $this->property->getUri() .'" to add to resource "' . $resource->getUri() . '" : ' . $propertyValue . PHP_EOL;
            return true;
        }
        return false;
    }

}