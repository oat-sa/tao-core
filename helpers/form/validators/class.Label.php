<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/validators/class.Label.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 25.06.2010, 12:13:17 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The validators enable you to perform a validation callback on a form element.
 * It's provide a model of validation and must be overriden.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.Validator.php');

/* user defined includes */
// section 127-0-1-1-112f6ae0:1296e7712be:-8000:000000000000200A-includes begin
// section 127-0-1-1-112f6ae0:1296e7712be:-8000:000000000000200A-includes end

/* user defined constants */
// section 127-0-1-1-112f6ae0:1296e7712be:-8000:000000000000200A-constants begin
// section 127-0-1-1-112f6ae0:1296e7712be:-8000:000000000000200A-constants end

/**
 * Short description of class tao_helpers_form_validators_Label
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_Label
    extends tao_helpers_form_Validator
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-112f6ae0:1296e7712be:-8000:000000000000200C begin
        
    	parent::__construct($options);
    	
    	if(isset($this->options['uri'])){
    		$this->message = __("Label already used");
    	}
    	
        // section 127-0-1-1-112f6ae0:1296e7712be:-8000:000000000000200C end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    public function evaluate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-112f6ae0:1296e7712be:-8000:0000000000002010 begin
        
        $returnValue = true;
     
        if(isset($this->options['class'])){
        	$clazz = $this->options['class'];
        	foreach($clazz->getInstances() as $instance){
        		if(isset($this->options['uri'])){
        			if($instance->uriResource == $this->options['uri']){
        				continue;
        			}
        		}
        		if($instance->getLabel() == $this->getValue()){
        			$returnValue = false;
        			 break;
        		}
        	}
        }
        else if(isset($this->options['uri'])){
        	
        	
        	$apiSearch = new core_kernel_impl_ApiSearchI();
			$options = array(
				'lang' => core_kernel_classes_Session::singleton()->getLg(),
				'like' => false
			);
			
			$resource = new core_kernel_classes_Resource($this->options['uri']);
			if($resource->isClass()){
				$classes = $resource->getPropertyValues(new core_kernel_classes_Property(RDF_SUBCLASSOF));
			}
			else if($resource->isProperty()){
				$classes = $resource->getPropertyValues(new core_kernel_classes_Property(RDF_DOMAIN));
			}
			else{
				$classes = array();
				foreach($resource->getType() as $type){
					$classes[] = $type->uriResource;
				}
			}
			foreach($classes as $classUri){
				
				$clazz = new core_kernel_classes_Class($classUri);
				
				if($resource->isClass()){
					$matchingResources = $apiSearch->searchInstances(array(RDFS_LABEL => $this->getValue()), null, $options);
					$checkResources = $clazz->getSubClasses(true);
					foreach($matchingResources as $matchingResource){
						if(array_key_exists($matchingResource->uriResource, $checkResources) && $matchingResource->uriResource != $this->options['uri']){
							$returnValue = false;
							break;
						}
					}
				}
				else if($resource->isProperty()){
					$matchingResources = $apiSearch->searchInstances(array(RDFS_LABEL => $this->getValue()), null, $options);
					$checkResources = $clazz->getProperties(true);
					foreach($matchingResources as $matchingResource){
						if(array_key_exists($matchingResource->uriResource, $checkResources)  && $matchingResource->uriResource != $this->options['uri']){
							$returnValue = false;
							break;
						}
					}
				}
				else{
					$matchingResources = $apiSearch->searchInstances(array(RDFS_LABEL => $this->getValue()), $clazz, $options);
					if(count($matchingResources) > 0){
						foreach($matchingResources as  $matchingResource){
							if($matchingResource->uriResource != $this->options['uri']){
								$returnValue = false;
								break;
							}
						}
					}
				}
			}
        }
        
        // section 127-0-1-1-112f6ae0:1296e7712be:-8000:0000000000002010 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Label */

?>