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
 * Copyright (c) 2013 (original work) Open Assessment Techonologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

/**
 * Service to manage services and calls to these services
 * 
 * @author Joel Bout, <joel@taotesting.com>
 */
class tao_models_classes_ServicesService extends tao_models_classes_Service
{
    /**
     * Short description of method getCallUrl
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  Resource interactiveService
     * @param  Resource activityExecution
     * @param  array variables
     * @return string
     */
    public function getCallUrl( core_kernel_classes_Resource $serviceCall, $parameters = array())
    {
        $serviceDefinition = $serviceCall->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_CALLOFSERVICES_SERVICEDEFINITION));
        $serviceDefinitionUrl = $serviceDefinition->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_SUPPORTSERVICES_URL));
        if($serviceDefinitionUrl instanceof core_kernel_classes_Literal){
            $serviceUrl = $serviceDefinitionUrl->literal;
        }else if($serviceDefinitionUrl instanceof core_kernel_classes_Resource){
            // hack nescessary since fully qualified urls are considered to be resources 
            $serviceUrl = $serviceDefinitionUrl->getUri();
        } else {
            throw new common_exception_InconsistentData('Invalid service definition url for '.$serviceDefinition->getUri());
        }
        // Remove the parameters because they are only for show, and they are actualy encoded in the variables
        $urlPart = explode('?',$serviceUrl);
        $returnValue = $urlPart[0].'?';
        if(preg_match('/^\//i', $returnValue)){
            //create absolute url (prevent issue when TAO installed on a subfolder
            $returnValue = ROOT_URL.ltrim($returnValue, '/');
        }

        $input 	= $this->getInputValues($serviceCall, $parameters);
        $output	= array();//for later use
    
        foreach ($input as $name => $value){
            $returnValue .= urlencode(trim($name)) . '=' . urlencode(trim($value)) . '&';
        }
    
        return (string) $returnValue;
    }
    

    /**
     * Short description of method getInputValues
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  Resource interactiveService
     * @param  Resource activityExecution
     * @return array
     */
    public function getInputValues( core_kernel_classes_Resource $serviceCall,  $parameters)
    {
        $returnValue = array();
    
        $inParameterCollection = $serviceCall->getPropertyValuesCollection(new core_kernel_classes_Property(PROPERTY_CALLOFSERVICES_ACTUALPARAMETERIN));
        foreach ($inParameterCollection->getIterator() as $inParameter){
            	
            $inValues = $inParameter->getPropertiesValues(array(
                PROPERTY_ACTUALPARAMETER_PROCESSVARIABLE,
                PROPERTY_ACTUALPARAMETER_CONSTANTVALUE,
                PROPERTY_ACTUALPARAMETER_FORMALPARAMETER
            ));
            
            if (count($inValues[PROPERTY_ACTUALPARAMETER_FORMALPARAMETER]) != 1) {
                throw new common_exception_InconsistentData(
                    count($inValues[PROPERTY_ACTUALPARAMETER_FORMALPARAMETER]).' formal parameter values for '.$inParameter->getUri()
                );
            }
            $paramDefinition = current($inValues[PROPERTY_ACTUALPARAMETER_FORMALPARAMETER]);
            
            $paramKey = common_Utils::fullTrim($paramDefinition->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_FORMALPARAMETER_NAME)));
            
            // variable
            if (count($inValues[PROPERTY_ACTUALPARAMETER_PROCESSVARIABLE]) == 1) {
                $variableDefinition = current($inValues[PROPERTY_ACTUALPARAMETER_PROCESSVARIABLE]);
                $code = $variableDefinition->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_PROCESSVARIABLES_CODE));
                if (isset($parameters[$code])) {
                    $returnValue = $parameters[$code]; 
                } else {
                    throw new common_exception_Error('Missing parameter '.$code.' for service call '.$serviceCall->getUri());
                }
                
            // constant
            } elseif (count($inValues[PROPERTY_ACTUALPARAMETER_CONSTANTVALUE]) == 1) {
                
                $inParameterConstant = current($inValues[PROPERTY_ACTUALPARAMETER_CONSTANTVALUE]);
                if($inParameterConstant instanceof core_kernel_classes_Literal){
                    $paramValue = $inParameterConstant->literal;
                }elseif($inParameterConstant instanceof core_kernel_classes_Resource){
                    $paramValue = $inParameterConstant->getUri();//encode??
                } else {
                    throw new common_exception_InconsistentData('Invalid constant value for param '.$inParameter->getUri());
                }
                
            // somthing went wrong
            } else {
                throw new common_exception_InconsistentData('No value for actual in parameter '.$inParameter->getUri());
            }
            
            //assign 
            $returnValue[$paramKey] = $paramValue;
        }
        return (array) $returnValue;
    }
    
}