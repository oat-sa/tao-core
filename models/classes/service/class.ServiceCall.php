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

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\WfEngineOntology;

/**
 * Represents a call of an interactive tao service
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class tao_models_classes_service_ServiceCall implements JsonSerializable
{
    use OntologyAwareTrait;
    /**
     * @var core_kernel_classes_Resource
     */
	
	private $serviceDefinitionId = null;

	/**
	 * Input Parameters used to call this service 
	 * 
	 * @var array
	 */
	private $inParameters = array();
	
	/**
	 * Variable parameter to which the outcome of the service is send
	 * 
	 * @var tao_models_classes_service_VariableParameter
	 */
	private $outParameter = null;
	
	/**
	 * Instantiates a new service call
	 * 
	 * @param core_kernel_classes_Resource $serviceDefinition
	 */
	public function __construct($serviceDefinition) {
	    $this->serviceDefinitionId = is_object($serviceDefinition)
	       ? $serviceDefinition->getUri()
	       : $serviceDefinition;
	}
	
	/**
	 * Adds an input parameter
	 * 
	 * @param tao_models_classes_service_Parameter $param
	 */
	public function addInParameter(tao_models_classes_service_Parameter $param) {
	    $this->inParameters[] = $param;
	}
	
	/**
	 * Sets the output parameter, does not except constants
	 * 
	 * @param tao_models_classes_service_VariableParameter $param
	 */
	public function setOutParameter(tao_models_classes_service_VariableParameter $param) {
	    $this->outParameter = $param;
	}
	
	/**
	 * returns the definition of the called service
	 * 
	 * @return core_kernel_classes_Resource
	 */
	public function getServiceDefinitionId() {
	    return $this->serviceDefinitionId;
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
	 * Gets the variables expected to be present to call this service
	 * 
	 * @return array:
	 */
	public function getRequiredVariables() {
	    $variables = array();
	    foreach ($this->inParameters as $param) {
	        if ($param instanceof tao_models_classes_service_VariableParameter) {
	            $variables[] = $param->getVariable();
	        }
	    }
	    return $variables;
	}
	
	/**
	 * Stores a service call in the ontology
	 * 
	 * @return core_kernel_classes_Resource
	 */
	public function toOntology() {
	    $inResources = array();
	    $outResources = is_null($this->outParameter)
	       ? array()
	       : $this->outParameter->toOntology();
	    foreach ($this->inParameters as $param) {
	        $inResources[] = $param->toOntology();
	    }
	    $serviceCallClass = $this->getClass( WfEngineOntology::CLASS_URI_CALL_OF_SERVICES);
	    $resource = $serviceCallClass->createInstanceWithProperties(array(
            OntologyRdfs::RDFS_LABEL => 'serviceCall',
			WfEngineOntology::PROPERTY_CALL_OF_SERVICES_SERVICE_DEFINITION    => $this->serviceDefinitionId,
			WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_IN    => $inResources,
			WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT   => $outResources,
			WfEngineOntology::PROPERTY_CALL_OF_SERVICES_WIDTH                => '100',
			WfEngineOntology::PROPERTY_CALL_OF_SERVICES_HEIGHT               => '100'
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
			WfEngineOntology::PROPERTY_CALL_OF_SERVICES_SERVICE_DEFINITION,
			WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_IN,
			WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT
	    ));
	    $serviceDefUri = current($values[WfEngineOntology::PROPERTY_CALL_OF_SERVICES_SERVICE_DEFINITION]);
	    $serviceCall = new self($serviceDefUri);
	    foreach ($values[WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_IN] as $inRes) {
	        $param = tao_models_classes_service_Parameter::fromResource($inRes);
	        $serviceCall->addInParameter($param);
	    }
	    if (!empty($values[WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT])) {
	        $param = tao_models_classes_service_Parameter::fromResource(current($values[WfEngineOntology::PROPERTY_CALL_OF_SERVICES_ACTUAL_PARAMETER_OUT]));
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
	
	public function jsonSerialize()
	{
	    return array(
	        'service' => $this->serviceDefinitionId,
	        'in' => $this->inParameters,
	        'out' => $this->outParameter
	    );
	}

	public static function fromJson($data) {
	    $call = new self($data['service']);
	    if (!empty($data['out'])) {
	       $call->setOutParameter(tao_models_classes_service_Parameter::fromJson($data['out']));
	    }
	    foreach ($data['in'] as $in) {
	        $call->addInParameter(tao_models_classes_service_Parameter::fromJson($in));
	    }
	    return $call;
	}
}
