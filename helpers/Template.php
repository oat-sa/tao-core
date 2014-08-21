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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *               
 * 
 */

namespace oat\tao\helpers;

class Template {
        
    /**
     * Expects a relative url to the image as path
     * if extension name is omitted the current extension is used
     * 
     * @param string $path
     * @param string $extensionName
     * @return string
     */
    public static function img($path, $extensionName = null) {
        if (is_null($extensionName)) {
            $extensionName = \Context::getInstance()->getExtensionName();
        }
        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionName);
        return $ext->getConstant('BASE_WWW').'img/'.$path;
    }
    
    /**
     * Expects a relative url to the css as path
     * if extension name is omitted the current extension is used
     * 
     * @param string $path
     * @param string $extensionName
     * @return string
     */
    public static function css($path, $extensionName = null) {
        if (is_null($extensionName)) {
            $extensionName = \Context::getInstance()->getExtensionName();
        }
        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionName);
        return $ext->getConstant('BASE_WWW').'css/'.$path;
    }
    
    /**
     * Expects a relative url to the java script as path
     * if extension name is omitted the current extension is used
     * 
     * @param string $path
     * @param string $extensionName
     * @return string
     */
    public static function js($path, $extensionName = null) {
        if (is_null($extensionName)) {
            $extensionName = \Context::getInstance()->getExtensionName();
        }
        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionName);
        return $ext->getConstant('BASE_WWW').'js/'.$path;
    }
    
    /**
     * Expects a relative url to the template that is to be included as path
     * if extension name is omitted the current extension is used
     *
     * @param string $path
     * @param string $extensionName
     * @return string
     */
    public static function inc($path, $extensionName = null) {
        if (!is_null($extensionName) && $extensionName != \Context::getInstance()->getExtensionName()) {
            // template is within diffrent extension, change context
            $formerContext = \Context::getInstance()->getExtensionName();
            \Context::getInstance()->setExtensionName($extensionName);
        }
        
        $absPath = self::getTemplate($path, $extensionName);
        if (file_exists($absPath)) {
            try {
                include($absPath);
            } catch (\Exception $e) {
                // restore context before rethrowing exception
                if (isset($formerContext)) {
                    \Context::getInstance()->setExtensionName($formerContext);
                }
                throw $e;
            }
        } else {
            \common_Logger::w('Failed to include "'.$absPath.'" in template');
        }
        // restore context
        if (isset($formerContext)) {
            \Context::getInstance()->setExtensionName($formerContext);
        }
    }
    
    public static function getTemplate($path, $extensionName = null) {
        $extensionName = is_null($extensionName) ? \Context::getInstance()->getExtensionName() : $extensionName;
        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionName);
        return $ext->getConstant('DIR_VIEWS').'templates'.DIRECTORY_SEPARATOR.$path;
    }
}
