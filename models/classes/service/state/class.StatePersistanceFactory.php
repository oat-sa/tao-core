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
 * Factory for the service state persistence
 *
 * @access public
 * @author @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_service_state
 */
class tao_models_classes_service_state_StatePersistanceFactory
{
    const CONFIG_KEY_IMPLEMENTATION = 'serviceStateImplementation';
    
    public static function getPersistanceFromConfig() {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $val = $ext->getConfig(self::CONFIG_KEY_IMPLEMENTATION);
        if (is_array($val)) {
            $class = $val['class'];
            return $class::restore($val['config']);
        } else {
            throw new common_exception_Error('No implementation found for '.__CLASS__);
        }
  	}
  	
    public static function storePersistanceToConfig(tao_models_classes_service_state_StatePersistence $implementation) {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $data = array(
        	'class' => get_class($implementation),
            'config' => $implementation->getConfiguration() 
        );
        $ext->setConfig(self::CONFIG_KEY_IMPLEMENTATION, $data);
  	}
}