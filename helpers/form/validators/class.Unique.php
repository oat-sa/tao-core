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
 * Short description of class tao_helpers_form_validators_Unique
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 
 */
class tao_helpers_form_validators_Unique
    extends tao_helpers_form_Validator
{
    protected function getDefaultMessage()
    {
        return __('Entity with such field already present');
    }

    /**
     * Short description of method evaluate
     *
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  mixed $values
     * @return boolean
     * @throws common_exception_Error
     */
    public function evaluate($values)
    {
		$result = true;

        if( !array_key_exists('resourceClass', $this->options) ){
            throw new common_exception_Error('Resource class not set');
        }

        if( !array_key_exists('property', $this->options) ){
            throw new common_exception_Error('Property not set');
        }

        /** @var core_kernel_classes_Class $resource */
        $resource = $this->options['resourceClass'];
        if( !( $this->options['resourceClass'] instanceof core_kernel_classes_Class ) ){
            throw new common_exception_Error('Resource class is invalid');
        }

        $recursiveParent = (array_key_exists('recursiveParent', $this->options) ? $this->options['recursiveParent'] : true);

        /** @var string $property */
        $property = $this->options['property'];

		$parentClasses = $resource->getParentClasses($recursiveParent);

		if (is_array($parentClasses)) {
			$veryParentClass = end($parentClasses);
			if ($veryParentClass) {
				$resources = $veryParentClass->searchInstances(array($property => $values,), array('recursive' => $recursiveParent));
				$result = (count($resources) === 0);
			}
		}

		return $result;
    }

}
