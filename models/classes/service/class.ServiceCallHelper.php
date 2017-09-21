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
 * 
 */

use oat\tao\model\WfEngineOntology;

/**
 * Service to manage services and calls to these services
 * 
 * @author Joel Bout, <joel@taotesting.com>
 */
class tao_models_classes_service_ServiceCallHelper
{
    const CACHE_PREFIX_URL = 'tao_service_url_';
    
    const CACHE_PREFIX_PARAM_NAME = 'tao_service_param_';
    
    public static function getBaseUrl($serviceDefinitionId) {
        
        try {
            $url = common_cache_FileCache::singleton()->get(self::CACHE_PREFIX_URL.urlencode($serviceDefinitionId));
        } catch (common_cache_NotFoundException $e) {

            $serviceDefinition = new core_kernel_classes_Resource($serviceDefinitionId);
            $serviceDefinitionUrl = $serviceDefinition->getOnePropertyValue(new core_kernel_classes_Property(WfEngineOntology::PROPERTY_SUPPORT_SERVICES_URL));

            $serviceUrl = ($serviceDefinitionUrl instanceof core_kernel_classes_Resource) ?
                // hack nescessary since fully qualified urls are considered to be resources
                $serviceUrl = $serviceDefinitionUrl->getUri():
                $serviceUrl = $serviceDefinitionUrl->literal; // instanceof Literal

            // Remove the parameters because they are only for show, and they are actualy encoded in the variables
            $urlPart = explode('?',$serviceUrl);
            $url = $urlPart[0];

            common_cache_FileCache::singleton()->put($url, self::CACHE_PREFIX_URL.urlencode($serviceDefinitionId));
        }
        if ($url[0] == '/') {
            //create absolute url (prevent issue when TAO installed on a subfolder
            $url = ROOT_URL.ltrim($url, '/');
        }
        return $url;
    }
    
    public static function getInputValues(tao_models_classes_service_ServiceCall $serviceCall, $callParameters) {
        $returnValue = array();
        foreach ($serviceCall->getInParameters() as $param) {
            $paramKey = self::getParamName($param->getDefinition());
            switch (get_class($param)) {
            	case 'tao_models_classes_service_ConstantParameter' :
            	    $returnValue[$paramKey] = $param->getValue();
            	    break;
            	case 'tao_models_classes_service_VariableParameter' :
            	    $variableCode = (string)$param->getVariable()->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_PROCESSVARIABLES_CODE));
            	    if (isset($callParameters[$variableCode])) {
            	        $returnValue[$paramKey] = $callParameters[$variableCode];
            	    } else {
            	        common_Logger::w('No variable '.$variableCode.' provided for paramter '.$paramKey);
            	    }
            	    break;
            	default:
            	    throw new common_exception_Error('Unknown class of parameter: '.get_class($param));
            }
        }
        return $returnValue;
    }
    
    protected static function getParamName(core_kernel_classes_Resource $paramDefinition) {
        try {
            $paramKey = common_cache_FileCache::singleton()->get(self::CACHE_PREFIX_PARAM_NAME.urlencode($paramDefinition->getUri()));
        } catch (common_cache_NotFoundException $e) {
            $paramKey = common_Utils::fullTrim($paramDefinition->getUniquePropertyValue(new core_kernel_classes_Property(WfEngineOntology::PROPERTY_FORMAL_PARAMETER_NAME)));
            common_cache_FileCache::singleton()->put($paramKey, self::CACHE_PREFIX_PARAM_NAME.urlencode($paramDefinition->getUri()));
        }
        return $paramKey;
        
    }
}