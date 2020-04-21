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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\accessControl\func;

use oat\tao\model\routing\Resolver;
use common_http_Request;
use common_exception_Error;
use oat\oatbox\service\ServiceManager;

/**
 */
class FuncHelper
{

    /**
     * Get the controller className from extension and controller shortname
     * @param string $extension
     * @param string $shortname
     * @return string the full class name (as defined in PHP)
     * @throws \ResolverException::
     */
    public static function getClassName($extension, $shortName)
    {
        $url = _url('index', $shortName, $extension);
        return self::getClassNameByUrl($url);
    }

    /**
     * Helps you to get the name of the class for a given URL. The controller class name is used in privileges definition.
     * @param string $url
     * @throws \ResolverException
     * @return string the className
     */
    public static function getClassNameByUrl($url)
    {
        $class = null;
        if (!empty($url)) {
            try {
                $route = new Resolver(new common_http_Request($url));
                $route->setServiceLocator(ServiceManager::getServiceManager());
                $class = $route->getControllerClass();
            } catch (\ResolverException $re) {
                throw new common_exception_Error('The url "' . $url . '" could not be mapped to a controller : ' . $re->getMessage());
            }
        }
        if (is_null($class)) {
            throw new common_exception_Error('The url "' . $url . '" could not be mapped to a controller');
        }
        return $class;
    }

    /**
     * @param $controllerClass
     * @return mixed
     * @throws \common_exception_Error
     */
    public static function getExtensionFromController($controllerClass)
    {
        if (strpos($controllerClass, '\\') === false) {
            $parts = explode('_', $controllerClass);
            if (count($parts) === 3) {
                return $parts[0];
            } else {
                throw new \common_exception_Error('Unknown controller ' . $controllerClass);
            }
        } else {
            foreach (\common_ext_ExtensionsManager::singleton()->getEnabledExtensions() as $ext) {
                foreach ($ext->getManifest()->getRoutes() as $routePrefix => $route) {
                    if (is_array($route) && array_key_exists('class', $route)) {
                        $route = $route['class']::getControllerPrefix() ?: $route;
                    }
                    if (is_string($route) && substr($controllerClass, 0, strlen($route)) === $route) {
                        return $ext->getId();
                    }
                }
            }
            throw new \common_exception_Error('Unknown controller ' . $controllerClass);
        }
    }
}
