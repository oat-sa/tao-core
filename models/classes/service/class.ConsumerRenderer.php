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
class tao_models_classes_service_ConsumerRenderer extends Renderer
{
    private static $renderer_id = 0;
    
    public static function fromResource(core_kernel_classes_Resource $serviceCallResource, $serviceCallId, $customParams = array()) {
        $serviceCall = tao_models_classes_service_ServiceCall::fromResource($serviceCallResource);
        return new self($serviceCall, $serviceCallId, $customParams);
    }
    
    public function __construct(tao_models_classes_service_ServiceCall $serviceCall, $serviceCallId, $customParams = array()) {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        parent::__construct($ext->getConstant('DIR_VIEWS').'templates'.DIRECTORY_SEPARATOR.'serviceRunner.tpl', array(
            'renderId' => self::$renderer_id++,
            'serviceApi' => tao_helpers_ServiceJavascripts::getServiceApi($serviceCall, $serviceCallId, $customParams)
        ));
    }
}