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
class tao_helpers_form_validators_Unique
    extends tao_helpers_form_Validator
{
    /**
     * (non-PHPdoc)
     * @see tao_helpers_form_Validator::getDefaultMessage()
     */
    protected function getDefaultMessage()
    {
        return __('Entity with such field already present');
    }

    /**
     * (non-PHPdoc)
     * @see tao_helpers_form_Validator::evaluate()
     */
    public function evaluate($values)
    {
        if (!array_key_exists('property', $this->options)) {
            throw new common_exception_Error('Property not set');
        }
        
        $property = new core_kernel_classes_Property($this->options['property']);
        $domain = $property->getDomain();
        foreach ($domain as $class) {
            $resources = $class->searchInstances(array($property->getUri() => $values), array('recursive' => true, 'like' => false));
            if (count($resources) > 0) {
                return false;
            }
        }
        return true;
    }

}
