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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * Validator to ensure a property value is unique
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */

use oat\oatbox\validator\ExtendedValidatorInterface;

class tao_helpers_form_validators_Unique extends tao_helpers_form_Validator implements ExtendedValidatorInterface
{
    const PROPERTY_PARAM = 'property';

    const CURRENT_ENTITY_ID_PARAM = 'currentEntityId';

    /**
     * (non-PHPdoc)
     * @see tao_helpers_form_Validator::getDefaultMessage()
     */
    protected function getDefaultMessage()
    {
        return __('The value for the property "%s" must be unique.', $this->getProperty()->getLabel());
    }

    /**
     * @inheritDoc
     */
    public function populateAdditionValues(array $elements, tao_helpers_form_FormElement $currentElement)
    {
        $this->setOption(self::PROPERTY_PARAM, tao_helpers_Uri::decode($currentElement->getName()));

        foreach ($elements as $element) {
            if ($element->getName() == 'id') {
                $this->setOption(self::CURRENT_ENTITY_ID_PARAM, $element->getValue());
            }
        }
    }

    /**
     * @return core_kernel_classes_Property
     * @throws common_exception_Error
     */
    protected function getProperty()
    {
        if (!$this->hasOption(self::PROPERTY_PARAM)) {
            throw new common_exception_Error('Property not set');
        }

        return ($this->getOption(self::PROPERTY_PARAM) instanceof core_kernel_classes_Property)
            ? $this->getOption(self::PROPERTY_PARAM)
            : new core_kernel_classes_Property($this->getOption('property'));
    }

    /**
     * @param mixed $values
     * @return bool
     * @throws common_Exception
     */
    public function evaluate($values)
    {
        $domain = $this->getProperty()->getDomain();

        foreach ($domain as $class) {

            $resources = $class->searchInstances(
                [$this->getProperty()->getUri() => $values],
                ['recursive' => true, 'like' => false]
            );

            if ($this->getOption(self::CURRENT_ENTITY_ID_PARAM)) {
                $resources = array_filter(
                    $resources,
                    function (core_kernel_classes_Resource $resource) {
                        return $resource->getUri() !== $this->getOption(self::CURRENT_ENTITY_ID_PARAM);
                    }
                );
            }
            if (count($resources) > 0) {
                return false;
            }
        }
        return true;
    }
}
