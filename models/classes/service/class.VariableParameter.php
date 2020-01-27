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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

use \oat\generis\model\data\Ontology;
use oat\tao\model\WfEngineOntology;

/**
 * Represents a tao service parameter that
 * is linked to a process variable
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 */
class tao_models_classes_service_VariableParameter
extends tao_models_classes_service_Parameter
{
    /**
     * @var core_kernel_classes_Resource
     */
	private $variable;
	
	/**
	 * Instantiates an new variable parameter
	 * 
	 * @param core_kernel_classes_Resource $definition
	 * @param core_kernel_classes_Resource $variable
	 */
	public function __construct(core_kernel_classes_Resource $definition, core_kernel_classes_Resource $variable) {
	    parent::__construct($definition);
	    $this->variable = $variable;
	}
	
	/**
	 * Returns the variable proividing the value
	 * for this parameter
	 * 
	 * @return core_kernel_classes_Resource
	 */
	public function getVariable() {
	    return $this->variable;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tao_models_classes_service_Parameter::serialize()
	 */
	public function toOntology(Ontology $model) {
	    $serviceCallClass = $model->getClass(WfEngineOntology::CLASS_URI_ACTUAL_PARAMETER);
	    $resource = $serviceCallClass->createInstanceWithProperties(array(
			WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_FORMAL_PARAMETER    => $this->getDefinition(),
			WfEngineOntology::PROPERTY_ACTUAL_PARAMETER_PROCESS_VARIABLE    => $this->variable
	    ));
	    return $resource;
	}

	public function jsonSerialize()
	{
	    return [
	        'def' => $this->getDefinition()->getUri(),
	        'proc' => $this->getVariable()->getUri()
	    ];
	}
}
