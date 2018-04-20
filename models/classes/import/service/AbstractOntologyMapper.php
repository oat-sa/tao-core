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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\import\service;

use oat\oatbox\service\ConfigurableService;

abstract class AbstractOntologyMapper extends ConfigurableService implements ImportMapper
{
    /** @var array */
    protected $propertiesMapped = [];

    /**
     * @param $property
     * @param $value
     * @return mixed
     */
    abstract protected function formatValue($property, $value);

    /**
     * @param array $data
     * @return $this|UserMapper
     * @throws MandatoryFieldException
     */
    public function map(array $data = [])
    {
        $schema = $this->getOption(static::OPTION_SCHEMA);
        $mandatoryFields = isset($schema[static::OPTION_SCHEMA_MANDATORY]) ? $schema[static::OPTION_SCHEMA_MANDATORY] : [];

        foreach ($mandatoryFields as $key => $propertyKey) {
            if (!isset($data[$key])) {
                throw new MandatoryFieldException('Mandatory field "' . $key . '" should exists.');
            }
            if (empty($data[$key])) {
                throw new MandatoryFieldException('Mandatory field "' . $key . '" should not be empty.');
            }

            $this->propertiesMapped[$propertyKey] = $this->formatValue($propertyKey, $data[$key]);
        }

        $optionalFields = isset($schema[static::OPTION_SCHEMA_OPTIONAL]) ? $schema[static::OPTION_SCHEMA_OPTIONAL] : [];

        foreach ($optionalFields as $key => $propertyKey) {
            if (!isset($data[$key]) || empty($data[$key])) {
                continue;
            }

            $this->propertiesMapped[$propertyKey] = $this->formatValue($propertyKey, $data[$key]);
        }

        return $this;
    }


    /**
     * @param array $extraProperties
     * @return array|mixed
     */
    public function combine(array $extraProperties)
    {
        $this->propertiesMapped = array_merge($this->propertiesMapped, $extraProperties);

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->propertiesMapped);
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->propertiesMapped;
    }
}