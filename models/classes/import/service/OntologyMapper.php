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
use common_report_Report as Report;

class OntologyMapper extends ConfigurableService implements ImportMapperInterface
{
    /** @var array */
    protected $propertiesMapped = [];

    /** @var Report */
    protected $report;

    /**
     * @param $property
     * @param $value
     * @return mixed
     */
    protected function formatValue($property, $value)
    {
        return $value;
    }

    /**
     * @param array $data
     * @return ImportMapperInterface
     * @throws MandatoryFieldException
     * @throws \Exception
     */
    public function map(array $data = [])
    {
        $this->report = Report::createInfo();

        $schema = $this->getOption(static::OPTION_SCHEMA);
        $this->buildMandatoryProperties($data, $schema);
        $this->buildOptionalProperties($data, $schema);

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

    /**
     * @inheritdoc
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param array $data
     * @param array $schema
     * @throws MandatoryFieldException
     * @throws RdsResourceNotFoundException
     * @throws \common_exception_Error
     */
    protected function buildMandatoryProperties(array $data, array $schema)
    {
        $mandatoryFields = isset($schema[static::OPTION_SCHEMA_MANDATORY]) ? $schema[static::OPTION_SCHEMA_MANDATORY] : [];

        foreach ($mandatoryFields as $key => $propertyKey) {
            if (!isset($data[$key])) {
                throw new MandatoryFieldException('Mandatory field "' . $key . '" should exists.');
            }

            if (empty($data[$key])) {
                throw new MandatoryFieldException('Mandatory field "' . $key . '" should not be empty.');
            }

            $this->addValue($propertyKey, $data[$key]);
        }
    }

    /**
     * @param array $data
     * @param array $schema
     * @throws RdsResourceNotFoundException
     * @throws \common_exception_Error
     */
    protected function buildOptionalProperties(array $data, array $schema)
    {
        $optionalFields = isset($schema[static::OPTION_SCHEMA_OPTIONAL]) ? $schema[static::OPTION_SCHEMA_OPTIONAL] : [];

        foreach ($optionalFields as $key => $propertyKey) {
            if (!isset($data[$key]) || $data[$key] === '') {
                continue;
            }

            $this->addValue($propertyKey, $data[$key]);
        }
    }

    /**
     * @param string $propertyKey
     * @param string $value
     * @throws RdsResourceNotFoundException
     * @throws \common_exception_Error
     */
    protected function addValue($propertyKey, $value)
    {
        if (is_array($propertyKey) && count($propertyKey) === 1){
            $valueMapper = reset($propertyKey);
            if ($valueMapper instanceof ImportValueMapperInterface){
                $propertyKey = key($propertyKey);
                $this->propagate($valueMapper);
                $this->propertiesMapped[$propertyKey] = $valueMapper->map($value);
                $this->report->add($valueMapper->getReport());
            }
        } else {
            $this->propertiesMapped[$propertyKey] = $this->formatValue($propertyKey, $value);
            $successMessage = 'Property mapped with success: '. $propertyKey . ':';
            $successMessage .= is_array($value) ? implode(',', $value) : $value;
            $this->report->add(Report::createSuccess($successMessage));
        }
    }
}