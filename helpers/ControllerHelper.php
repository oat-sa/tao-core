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

use oat\controllerMap\parser\Factory;
/**
 * Utility class that focuses on he controllers.
 *
 * @author Joel Bout <joel@taotesting.com>
 * @package tao
 */
class ControllerHelper
{
    public static function getControllers($extensionId) {
        $factory = new Factory();
        
        $controllerClasses = array();
        foreach ($factory->getControllers($extensionId) as $controller) {
            $controllerClasses[] = $controller->getClassName();
        }
        return $controllerClasses;
    }
    
    /**
     * Get the list of actions for a controller
     * 
     * @param unknown $controllerClassName
     * @return array
     */
    public static function getActions($controllerClassName) {
        $factory = new Factory();
        $desc =  $factory->getControllerDescription($controllerClassName);
        
        $actions = array();
        foreach ($desc->getActions() as $action) {
            $actions[] = $action->getName();
        }
        return $actions;
    }
    
    public static function getActionDescription($controllerClassName, $actionName) {
        $factory = new Factory();
        return $factory->getActionDescription($controllerClassName, $actionName);
    }
    
    
}