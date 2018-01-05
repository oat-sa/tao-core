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
use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metadata\exception\writer\MetadataWriterException;

/**
 * Class PropertyWriter
 * Writer to write one value to a property
 *
 * @author Camille Moyon
 * @package oat\tao\model\metadata\writer\ontologyWriter
 */
class PropertyWriter extends ConfigurableService implements OntologyWriter
{
    use OntologyAwareTrait;

    const PROPERTY_KEY = 'propertyUri';

    /**
     * PropertyWriter constructor.
     * Check if property config key is set and if property exists
     *
     * @param array $params
     * @throws InconsistencyConfigException
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);

        if (! $this->hasOption(self::PROPERTY_KEY)) {
            throw new InconsistencyConfigException('Unable to find config key "' . self::PROPERTY_KEY . '" as property to write.');
        }

        if (! $this->getProperty($this->getOption(self::PROPERTY_KEY))->exists()) {
            throw new InconsistencyConfigException('Config key "' . self::PROPERTY_KEY . '" found, ' .
                'but property "' . $this->getOption(self::PROPERTY_KEY) . '" does not exist.');
        }
    }

    /**
     * Validate if value is writable by current writer
     * Validation is handle by property validators
     *
     * @param $data
     * @return bool
     * @throws MetadataWriterException
     */
    public function validate($data)
    {
        try {
            /** @var \tao_helpers_form_Validator[] $validators */
            $validators = ValidationRuleRegistry::getRegistry()->getValidators($this->getPropertyToWrite());
        } catch (\common_exception_NotFound $e) {
            throw new MetadataWriterException($e->getMessage());
        }

        $validated = true;
        foreach ($validators as $validator) {
            if (! $validator->evaluate($data)) {
                $validated = false;
                \common_Logger::d('Unable to validate value for property "' . $this->getPropertyToWrite()->getUri() . '"' .
                    ' against validator "' . $validator->getName(). '" : "' . $validator->getMessage() . '".');
            }
        }
        return $validated;
    }

    /**
     * Write a value to a $resource
     *
     * @param \core_kernel_classes_Resource $resource
     * @param $data
     * @param bool $dryrun
     * @return bool
     * @throws MetadataWriterException
     */
    public function write(\core_kernel_classes_Resource $resource, $data, $dryrun = false)
    {
        $propertyValue = $this->format($data);

        if ($this->validate($propertyValue)) {
            if (! $dryrun) {
                if (! $resource->editPropertyValues($this->getPropertyToWrite(), $propertyValue)) {
                    throw new MetadataWriterException(
                        'A problem has occurred during writing property "' . $this->getPropertyToWrite()->getUri() . '".'
                    );
                }
            }
            \common_Logger::d('Valid property "'. $this->getPropertyToWrite()->getUri() .'" ' .
                'to add to resource "' . $resource->getUri() . '" : ' . $propertyValue);
            return true;
        }

        throw new MetadataWriterException(
            'Writer "' . __CLASS__ . '" cannot validate value for property "' . $this->getPropertyToWrite()->getUri() . '".'
        );
    }

    /**
     * Get the property to be written
     *
     * @return \core_kernel_classes_Property
     */
    protected function getPropertyToWrite()
    {
        return $this->getProperty($this->getOption(self::PROPERTY_KEY));
    }

    /**
     * Format data to be written
     *
     * @param array $data
     * @return mixed
     */
    public function format(array $data)
    {
        return array_pop($data);
    }

}