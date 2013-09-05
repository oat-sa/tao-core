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

/**
 * Represents a call of an interactive tao service
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_service
 */
class tao_models_classes_service_ServiceCall
{
    /**
     * @var core_kernel_classes_Resource
     */
	private $serviceDefinition = null;
	
	private $inParameters = array();
	
	private $outParameter = null;
	
	public function __construct(core_kernel_classes_Resource $serviceDefinition) {
	    $this->serviceDefinition = $serviceDefinition;
	}
	
	public function addInParameter(tao_models_classes_service_Parameter $param) {
	    $this->inParameters[] = $param;
	}
	
	public function setOutParameter(tao_models_classes_service_VariableParameter $param) {
	    $this->outParameter = $param;
	}
	
	/**
	 * returns the definition of the called service
	 * 
	 * @return core_kernel_classes_Resource
	 */
	public function getServiceDefinition() {
	    return $this->serviceDefinition;
	}
	
	/**
	 * returns the call parameters
	 * 
	 * @return array:
	 */
	public function getInParameters() {
	    return $this->inParameters;
	}
	
	/**
	 * Serialises a service call into the ontology
	 * 
	 * @return core_kernel_classes_Resource
	 */
	public function serialize() {
	    $inResources = array();
	    $outResources = is_null($this->outParameter)
	       ? array()
	       : $this->outParameter->serialize();
	    foreach ($this->inParameters as $param) {
	        $inResources[] = $param->serialize();
	    }
	    $serviceCallClass = new core_kernel_classes_Class(CLASS_CALLOFSERVICES);
	    $resource = $serviceCallClass->createInstanceWithProperties(array(
	        RDFS_LABEL => 'serviceCall',
            PROPERTY_CALLOFSERVICES_SERVICEDEFINITION    => $this->serviceDefinition,
            PROPERTY_CALLOFSERVICES_ACTUALPARAMETERIN    => $inResources,
            PROPERTY_CALLOFSERVICES_ACTUALPARAMETEROUT   => $outResources,
	        PROPERTY_CALLOFSERVICES_WIDTH                => '100',
	        PROPERTY_CALLOFSERVICES_HEIGHT               => '100'
        ));
	         
	    return $resource; 
	}
	
	/**
	 * Builds a service call from it's serialized form
	 * 
	 * @param core_kernel_classes_Resource $resource
	 * @return tao_models_classes_service_ServiceCall
	 */
	public static function fromResource(core_kernel_classes_Resource $resource) {
	    $values = $resource->getPropertiesValues(array(
	        PROPERTY_CALLOFSERVICES_SERVICEDEFINITION,
	        PROPERTY_CALLOFSERVICES_ACTUALPARAMETERIN,
	        PROPERTY_CALLOFSERVICES_ACTUALPARAMETEROUT
	    ));
	    $serviceDefUri = current($values[PROPERTY_CALLOFSERVICES_SERVICEDEFINITION]);
	    $serviceCall = new self(new core_kernel_classes_Resource($serviceDefUri));
	    foreach ($values[PROPERTY_CALLOFSERVICES_ACTUALPARAMETERIN] as $inRes) {
	        $param = tao_models_classes_service_Parameter::fromResource($inRes);
	        $serviceCall->addInParameter($param);
	    }
	    if (!empty($values[PROPERTY_CALLOFSERVICES_ACTUALPARAMETEROUT])) {
	        $param = tao_models_classes_service_Parameter::fromResource(current($values[PROPERTY_CALLOFSERVICES_ACTUALPARAMETEROUT]));
	        $serviceCall->setOutParameter($param);
	    }
	    return $serviceCall;
	}
	
	/**
	 * Serialize the current serivceCall object to a string
	 * 
	 * @return string
	 */
	public function serializeToString() {
	    return serialize($this);
	}
	
	/**
	 * Unserialize the string to a serivceCall object
	 * 
	 * @param string $string
	 * @return tao_models_classes_service_ServiceCall
	 */
	public static function fromString($string) {
	    return unserialize($string);
	}
	
}
